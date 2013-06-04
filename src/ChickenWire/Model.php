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
	 * ##Authentication model##
	 * To create a Model for authentication, using the Auth class, you can use
	 * the configurator $authModel:
	 *
	 * <code>
	 * static $authModel = true;
	 * </code>
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

		/**
		 * Configurator for authentication
		 * @var string
		 */
		static $authModel; 


		private static $_cwInitialized = false;
		

		/**
		 * The Auth instance (if this Model was used for authentication)
		 * @var \ChickenWire\Util\Auth
		 */
		protected static $auth;



		/**
		 * Retrieve the Table instance for this Model
		 * @return \ActiveRecord\Table
		 */
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
		 * @param  \ActiveRecord\Table   The Table object to work with 
		 * @return void
		 */
		private static function _initializeCW($table)
		{

			// Is it an auth model?
			if (isset(static::$authModel) && static::$authModel !== false) {

				// Get my full class name
				$myClass = trim(get_called_class(), '\\ ');
				
				// Look up the auth object
				$myAuth = null;
				foreach (Auth::all() as $auth) {
					if (trim($auth->model, '\\ ') == $myClass) {
						$myAuth = $auth;
						break;
					}

				}
				
				// Still null?
				if (is_null($myAuth)) {

					// Not good...
					throw new \Exception("There was no Auth defined with model '" . $myClass . "'.", 1);					

				}

				// Store it
				self::$auth = $myAuth;

				// Register auth save callback
				$table->callback->register("before_save", function (\ActiveRecord\Model $model) { $model->_setAuthValues(); });

			}

		}


		/**
		 * Apply authentication values to the record (before save)
		 */
		private function _setAuthValues()
		{

			// Get dirty fields
			$dirty = $this->dirty_attributes();

			// Get auth object
			$auth = self::$auth;
			
			// Salt empty?
			if ($auth->useSalt && $this->read_attribute($auth->saltField) == '') {
				
				// Generate a salt!
				$this->set_attributes(array(
					$auth->saltField => Str::random($auth->saltLength)
				));

			}

			// Password dirty?
			if (array_key_exists($auth->passwordField, $dirty)) {
				
				// What sort of encryption then?
				switch ($auth->type) {
					case Auth::MD5:

						// Hash password using md5
						$this->set_attributes(array(
							$auth->passwordField => md5(
								($auth->useSalt ? $this->read_attribute($auth->saltField) : "") . 
								$this->read_attribute($auth->passwordField)
							)
						));
						break;
					
					case Auth::BLOWFISH:
						
						// Blowfish!
						$this->set_attributes(array(
							$auth->passwordField => 
								Str::blowFish(
									$this->read_attribute($auth->passwordField), 
									$auth->useSalt ? $this->read_attribute($auth->saltField) : "")							
						));
						break;
				}

			}

		}

		/**
		 * Re-encrypt the password
		 * @param  string 	The plain text password to re-encrypt
		 * @param  string 	Optional salt to encrypt with. If you leave this empty, a random salt will be generated.
		 * @return void
		 */
		public function resaltPassword($password, $salt = '')
		{

			// No salt used..?
			$auth = self::$auth;
			if ($auth->useSalt == false) {
				throw new \Exception("This Auth does not use salting.", 1);				
			}

			// Apply the values to the record
			$this->set_attributes(array(
				$auth->saltField => $salt,
				$auth->passwordField => $password
			));

		}



		/**
		 * Check if given attribute exists
		 * @param  string  Attribute name
		 * @return boolean           True or false
		 */
		public function attribute_exists($attrName)
		{

			// In default attributes
			if (array_key_exists($attrName, $this->attributes())) return true;

			// A getter available?
			if (method_exists($this, "get_$attrName")) return true;			

			return false;


		}


		public function getClass($namespaced = false)
		{

			$class = get_class($this);
			if (!$namespaced) $class = Str::removeNamespace($class);
			return $class;

		}


	}




?>