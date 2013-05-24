<?php

	namespace ChickenWire\Core;

	use ChickenWire\Util\Reflection;
	use ChickenWire\Route;
	use ChickenWire\Module;

	class Configuration
	{

		protected $_envSettings = array('development' => array());
		protected $_environment = 'development';

		protected $_defaults = array();


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


		public function __get($name) {

			// Return it!
			return $name == 'environment' ? $this->_environment : $this->_envSettings[$this->_environment][$name];

		}

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