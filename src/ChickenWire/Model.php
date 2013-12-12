<?php

	namespace ChickenWire;

	use \ChickenTools\Str;

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
	class Model extends \ActiveRecord\Model implements \ChickenWire\Serialization\ISerializable
	{

		/**
		 * Configurator for authentication
		 * @var string
		 */
		static $authModel;


		
		/**
		 * The field to use when a generic function wants to show the title of the record.
		 * @var string
		 */
		static $titleColumn;



		private static $_cwInitialized = [];
		

		/**
		 * The Auth instance (if this Model was used for authentication)
		 * @var \ChickenWire\Util\Auth
		 */
		protected static $auth;


		protected static $asJsonOptions = array();
		protected static $asXmlOptions = array();



		/**
		 * Retrieve the Table instance for this Model
		 * @return \ActiveRecord\Table
		 */
		static function table()
		{

			// Do the basics
			$table = parent::table();

			// Check if this class was already initialized for ChickenWire
			$c = get_called_class();
			if (!in_array($c, self::$_cwInitialized)) {
			
				// Initialize 
				static::_initializeCW($table);				
				array_push(self::$_cwInitialized, $c);
				
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
				$table->callback->register("beforeSave", function (\ActiveRecord\Model $model) { 
					$model->_setAuthValues();
				});

			}

			
		}


		/**
		 * Apply authentication values to the record (before save)
		 */
		private function _setAuthValues()
		{

			// Get dirty fields
			$dirty = $this->dirtyAttributes();

			// Get auth object
			$auth = self::$auth;
			
			// Salt empty?
			if ($auth->useSalt && $this->readAttribute($auth->saltField) == '') {
				
				// Generate a salt!
				$this->setAttributes(array(
					$auth->saltField => Str::random($auth->saltLength)
				));

			}

			// Password dirty?
			if (array_key_exists($auth->passwordField, $dirty)) {
				
				// Salt it?
				$password  = ($auth->useSalt ? $this->readAttribute($auth->saltField) : "") . $this->readAttribute($auth->passwordField);
				
				// What sort of encryption then?
				switch ($auth->type) {
					case Auth::MD5:

						// Hash password using md5
						$this->setAttributes(array(
							$auth->passwordField => md5($password)
						));
						break;
					
					case Auth::BLOWFISH:
						
						// Create the hasher
						$hasher = new \Hautelook\Phpass\PasswordHash(8, false);
						$passwordHashed = $hasher->HashPassword($password);

						// Blowfish!
						$this->setAttributes(array(
							$auth->passwordField => $passwordHashed			
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
			$this->setAttributes(array(
				$auth->saltField => $salt,
				$auth->passwordField => $password
			));

		}



		


		public function getClass($namespaced = false)
		{

			$class = get_class($this);
			if (!$namespaced) $class = Str::removeNamespace($class);
			return $class;

		}


		public function asObject($format, $options = array())
		{

			// Check default options
			$defaultOptions = \ChickenTools\Arry::mergeStatic(get_called_class(), "as" . ucfirst($format) . "Options");
			$options = \ChickenTools\Arry::mergeRecursiveDistinct($options, $defaultOptions);

			// Get attributes
			$obj = $this->attributes();

			// Except?
			if (array_key_exists("except", $options)) {
				$except = is_array($options['except']) ? $options['except'] : array($options['except']);
				foreach ($except as $key) {
					unset($obj[$key]);
				}
			}

			// Only?
			if (array_key_exists("only", $options)) {
				$only = is_array($options['only']) ? $options['only'] : array($options['only']);
				$value = [];
				foreach ($only as $key) {
					$value[$key] = $obj[$key];
				}
				$obj = $value;
			}

			// Methods?
			if (array_key_exists("methods", $options)) {
				$methods = is_array($options['methods']) ? $options['methods'] : array($options['methods']);
				foreach ($methods as $method) {
					$obj[$method] = $this->$method();
				}
			}

			// Includes?
			if (array_key_exists("include", $options)) {
				$includes = is_array($options['include']) ? $options['include'] : array($options['include']);
				foreach ($includes as $key => $inc) {

					if (is_numeric($key)) {
						$subOptions = [];
					} else {
						$subOptions = $inc;
						$inc = $key;
					}
					
					// Get included value
					$value = $this->$inc;

					// An array or an instance?
					if (is_array($value)) {
						$list = [];
						foreach ($value as $model) {
							$list[] = $model->asObject($format, $subOptions);
						}
						$value = $list;
					} elseif (is_subclass_of($value, '\\ChickenWire\\Model')) {

						// Apply sub-options to model
						$value = $value->asObject($format, $subOptions);

					} 


					$obj[$inc] = $value;					
				}
			}


			// Include root?
			if (array_key_exists("includeRoot", $options) && $options['includeRoot'] == true) {
				return array(strtolower($this->getClass()) => $obj);
			} else {
				return $obj;
			}

		}



		public function humanAttributeName($attribute)
		{

			// Try the i18n
			$key = "activerecord.attributes." . strtolower($this->getClass() . "." . $attribute);
			$label = I18n::translate($key);
			
			return $label;

		}

		public function title()
		{

			// Field set?
			if (static::$titleColumn)
			{
				return $this->{static::$titleColumn};
			}

			// Guest it
			if ($this->hasAttribute("title")) return $this->title;
			if ($this->hasAttribute("name")) return $this->name;
			if ($this->hasAttribute("fullname")) return $this->fullname;
			return "Could not guess title column for " . $this->getClass();

		}




	}




?>