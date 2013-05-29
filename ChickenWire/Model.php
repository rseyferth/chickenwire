<?php

	namespace ChickenWire;

	use \ChickenWire\Util\Str;
	use \ChickenWire\Auth\Auth;

	/**
	 * The ChickenWire Model class
	 *
	 * This extends the ActiveRecord Model, adding a few extra features. 
	 *
	 *
	 * <h3>Authentication model</h3>
	 * To create a Model for authentication, using the Auth class, you can use
	 * the configurator $authModel. A simple example:
	 *
	 * <code>
	 * class User extends \ChickenWire\Model
	 * {
	 * 	static $authModel = \ChickenWire\Auth\Auth::BLOWFISH;
	 * }
	 * </code>
	 * Or, even simpler:
	 * <code>
	 * static $authModel = true;		// Blowfish is the default encryption
	 * </code>
	 *
	 * You can also specify extra options, using an array instead:
	 *
	 * <code>
	 * class User extends \ChickenWire\Model
	 * {
	 * 	static $authModel = array(
	 * 		"type" => \ChickenWire\Auth\Auth::BLOWFISH,
	 * 		"passwordField" => "userpassword"
	 * 	);
	 * }
	 * </code>
	 *
	 * The following options are available:
	 *
	 * <table border="1" cellpadding="3">
	 * <thead>
	 * 	<tr>
	 * 		<th>Option</th>
	 * 		<th>Default value</th>
	 * 		<th>Description</th>
	 * 	</tr>
	 * </thead>
	 * <tbody>
	 * 	<tr>
	 * 		<td>passwordField</td>
	 * 		<td>"password"</td>
	 * 		<td>The database column name for the password.</td>
	 * 	</tr>
	 * 	<tr>
	 * 		<td>salt</td>
	 * 		<td><i>null</i></td>
	 * 		<td>The salt to use for all passwords. When you leave this <i>null</i> a random salt will be generated.</td>
	 * 	</tr>
	 * 	<tr>
	 * 		<td>saltField</td>
	 * 		<td>"salt"</td>
	 * 		<td>The database column name for the salt.</td>
	 * 	</tr>
	 * 	<tr>
	 * 		<td>saltLength</td>
	 * 		<td>16</td>
	 * 		<td>The length of generated salts.</td>
	 * 	</tr>
	 * 	<tr>
	 * 		<td>type</td>
	 * 		<td></td>
	 * 		<td>The encryption type (Auth::BLOWFISH or Auth::MD5). For more information see Auth.</td>
	 * 	</tr> 	
	 * </tbody>
	 * </table>
	 *
	 * 
	 * <b>Note:</b> See Auth for a complete picture.
	 * 
	 *
	 * @see  \ChickenWire\Auth\Auth
	 *
	 * @package ChickenWire
	 */
	class Model extends \ActiveRecord\Model 
	{

		static $authModel; 


		private static $_cwInitialized = false;

		private static $_authSettings = null;


		static function table()
		{

			// Do the basics
			$table = parent::table();

			// ChickenWire features initialized?
			if (self::$_cwInitialized == false) {
				self::_initializeCW($table);
				self::$_cwInitialized = true;
			}

			// Done.
			return $table;

		}

		/**
		 * Initialize ChickenWire featues
		 * @param  \ActiveRecord\Table 	$table  The Table object to work with 
		 * @return void
		 */
		private static function _initializeCW($table)
		{

			// Is it an auth model?
			if (isset(static::$authModel) && static::$authModel !== false) {

				// Register auth save callback
				$table->callback->register("before_save", function (\ActiveRecord\Model $model) { $model->_setAuthValues(); });

			}

		}



		/**
		 * Read the $authModel settings, and apply default settings, etc.
		 * @return void
		 */
		private static function _interpretAuthSettings()
		{

			// Set at all?
			if (!isset(static::$authModel)) {
				throw new \Exception("The \$authModel settings was not set!", 1);				
			}


			// Check my auth type
			$settings = static::$authModel;
			if (is_bool($settings)) {

				// Use default AUTH type (Blowfish)
				$settings = array(
					"type" => Auth::BLOWFISH
				);

			}

			// String type?
			if (is_string($settings)) {

				// Interpret as type
				if ($settings == Auth::MD5) {
					$settings = array(
						"type" => Auth::MD5
					);
				} else {
					$settings = array(
						"type" => Auth::BLOWFISH
					);
				}

			}

			// Array?
			if (!is_array($settings)) {
				throw new \Exception("The \$authModel property is not set to a valid setting.", 1);				
			}

			// Merge with default options
			$settings = array_merge($settings, array(
				"passwordField" => "password",
				"saltField" => "salt",
				"salt" => null,
				"saltLength" => 16
			));

			// Store it
			self::$_authSettings = $settings;

		}



		/**
		 * Apply authentication values to the record (before save)
		 */
		private function _setAuthValues()
		{

			// Auth settings checked?
			if (is_null(self::$_authSettings)) {
				self::_interpretAuthSettings();
			}
			$settings = self::$_authSettings;

			// Get dirty fields
			$dirty = $this->dirty_attributes();

			
			// Salt empty?
			if ($settings['type'] == Auth::BLOWFISH && $this->read_attribute($settings['saltField']) == '') {
				
				// Generate a salt!
				$this->set_attributes(array(
					$settings['saltField'] => Str::random($settings['saltLength'])
				));

			}

			// Password dirty?
			if (array_key_exists($settings['passwordField'], $dirty)) {
				
				// What sort of encryption then?
				switch ($settings['type']) {
					case Auth::MD5:

						// Hash password using md5
						$this->set_attributes(array(
							$settings['passwordField'] => md5($this->read_attribute($settings['passwordField']))
						));
						break;
					
					case Auth::BLOWFISH:
						
						// Blowfish!
						$this->set_attributes(array(
							$settings['passwordField'] => 
								Str::encryptPassword(
									$this->read_attribute($settings['passwordField']), 
									$this->read_attribute($settings['saltField']))							
						));
						break;
				}

			}

		}

		/**
		 * Re-encrypt the password
		 * @param  string $password The plain text password to re-encrypt
		 * @param  string $salt     (default: '') Optional salt to encrypt with. If you leave this empty, a random salt will be generated.
		 * @return void
		 */
		public function resaltPassword($password, $salt = '')
		{

			// Auth settings checked?
			if (is_null(self::$_authSettings)) {
				self::_interpretAuthSettings();
			}
			$settings = self::$_authSettings;

			// Apply the values to the record
			$this->set_attributes(array(
				$settings['saltField'] => $salt,
				$settings['passwordField'] => $password
			));

		}


	}




?>