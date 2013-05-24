<?php

	namespace ChickenWire;

	use \ChickenWire\Util\Str;

	class Module extends Core\MagicObject {

		protected static $_propAccessible = array("name", "path", "namespace", "urlPrefix");

		protected static $_modules = array();

		public static function load($name, $options = array()) {

			// Module already loaded?
			if (array_key_exists($name, self::$_modules)) {
				throw new \Exception("A module with the name $name has already been loaded.", 1);
			}

			// Create and add
			$module = new Module($name, $options);
			self::$_modules[$name] =  $module;

		}

		protected $_name;
		protected $_path;

		protected $_namespace;
		protected $_urlPrefix;

		public function __construct($name, $options) {

			// Localize
			$this->_name = $name;

			// Default settings
			$defaultOptions = array(
				"namespace" => $name,
				"urlPrefix" => '/' . Str::slugify($name)
			);

			// Path given?
			if (array_key_exists("path", $options)) {
				$this->_path = $options['path'];
			} else {
				$this->_path = MODULE_PATH . "/" . $name;
			}
			rtrim($this->_path, " /");

			// Module config file found?
			if (file_exists($this->_path . "/Module.php")) {
				
				// Load the file with $module as config object
				$module = new \stdClass();
				require $this->_path . "/Module.php";

				// Convert back to array
				$module = get_object_vars($module);

			} else {
				$module = array();
			}

			// Combine all settings into one array (default => module-settings => constructor options)
			$settings = array_merge($defaultOptions, $module, $options);
			
			// Store settings
			$this->_namespace = $settings['namespace'];
			$this->_urlPrefix = $settings['urlPrefix'];

			// Load configuration
			$this->_loadConfig();

		}

		protected function _loadConfig() {

			// A config dir?
			$configDir = $this->_path . "/Config";
			if (!file_exists($configDir) || !is_dir($configDir)) return;

			// Start route-with
			Route::withModule($this);

			// Loop the files
			$dh = opendir($configDir);
			$config = Application::getConfiguration();
			while (false !== ($file = readdir($dh))) {

				// PHP?
				if (preg_match("/\.php$/", $file)) {

					// Load it
					$config->load($configDir . '/' . $file);
					
				}


			}
 
			// Done!
			Route::endWith();

		
		}


	}


?>