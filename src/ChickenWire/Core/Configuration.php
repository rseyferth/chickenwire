<?php

	namespace ChickenWire\Core;

	use ChickenWire\Util\Reflection;
	use ChickenWire\Route;
	use ChickenWire\Module;

	/**
	 * Configuration class for multiple environment configuration
	 *
	 * The Configuration class can be used to create a configuration for
	 * one or more environments (usually development, test, and production). This class
	 * is mainly used by the ChickenWire\Application class, to store all
	 * framework configuration. On construction the Configuration will try to
	 * load default settings from the calling/defined class. For example:
	 *
	 * <code>
	 * class Application
	 * {
	 * 	static $defaultSettings = array(
	 * 		"name" => "Not a real application."
	 * 	);
	 * 	
	 * 	private $_settings;
	 * 	function __construct() 
	 * 	{
	 * 		$this->_settings = new \ChickenWire\Core\Configuration();
	 * 		echo $this->_settings->name;		// output: Not a real application.
	 * 	}
	 * }
	 * </code>
	 *
	 * In the Application's config files the local variable $config is an
	 * instance of this class. 
	 *
	 * Example of a configuration file:
	 * <code>
	 * // Set environment
	 * $config->environment = ($_SERVER['HTTP_HOST'] == 'www.live-domain.com') ? 'production' : 'development';
	 *
	 * // Database (for each environment seperately)
	 * $config->database = array(
	 * 	'development' => 'mysql://root:guess@localhost/[dbname];charset=utf8',
	 * 	'production' => 'mysql://[user]:[pass]@localhost/[dbname]-admin;charset=utf8'
	 * );
	 *
	 * // Set timezone (for all environments)
	 * $config->timezone = "Europe/Amsterdam";
	 * </code>
	 *
	 * @property string $environment The environment we are currently in. This determines which value will be returned when you have configured multiple environments.
	 * 
	 * @package ChickenWire
	 */

	class Configuration
	{

		protected $_envSettings = array('development' => array());
		protected $_environment = 'development';

		protected $_defaults = array();


		/**
		 * Create a new Configuration instance
		 * @param string $class (default: the calling class) The class for which the configuration is meant. When you leave this null it will assume the class you call this constructor from.
		 */
		public function __construct($class = null) {

			// Check if class is given
			if (is_null($class)) {
				$class = Reflection::getCallingClass();
			}

			// Check if class is known
			if ($class !== false) {
				
				// Look for default settings in that class
				$reflClass = new \ReflectionClass($class);
				if (array_key_exists("defaultSettings", $reflClass->getStaticProperties())) {

					// Apply default settings to default environment
					$this->_envSettings[$this->_environment] = $reflClass->getStaticPropertyValue("defaultSettings");
					$this->_defaults = $this->_envSettings[$this->_environment];

				}
			
			} 

		}

		/**
		 * Get the values of the given setting for each environment
		 * @param  string $name The settings to retrieve
		 * @return array       
		 */
		public function allFor($name) {

			// Loop through environments
			$result = array();
			foreach ($this->_envSettings as $env => $settings) {
				$result[$env] = $settings[$name];
			}
			return $result;

		}

		/**
		 * @ignore
		 */
		public function __get($name) {

			// Return it!
			return $name == 'environment' ? $this->_environment : $this->_envSettings[$this->_environment][$name];

		}

		/**
		 * @ignore
		 */
		public function __set($name, $value) {

			// Set environment?
			if ($name == 'environment') {
				$this->_environment = $value;
				return;
			}

			// Multiple settings?
			if (is_array($value)) {

				// Loop and apply key as environment
				foreach ($value as $env => $val) {

					// Is this environment already created?
					if (!array_key_exists($env, $this->_envSettings)) {

						// Create default settings
						$this->_envSettings[$env] = $this->_defaults;

					}

					// Apply!
					$this->_envSettings[$env][$name] = $val;

				}

			} else {

				// Just the one settings (use current environment)
				$this->_envSettings[$this->_environment][$name] = $value;

			}

		}

		/**
		 * Load a PHP configuration file inside the context of this configuration
		 * @param  string $file The PHP file to load
		 * @return void       
		 */
		public function load($file) {

			// Localize the config
			$config = &$this;

			// Include file!
			require_once $file;

		}


	}





?>