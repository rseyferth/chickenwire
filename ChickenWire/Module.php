<?php

	namespace ChickenWire;

	use \ChickenWire\Util\Str;

	/**
	 * Module class containing information on loaded modules
	 *
	 * A module is a seperate set of models, views and controllers contained
	 * within your application. Modules have the same structure as an Application,
	 * with the exception of the settings for how the module behaves inside the
	 * Application. These settings can either be placed inside a Module.php file
	 * in the module's root directory, for example:
	 *
	 * <b>Module.php</b>
	 * <code>
	 * 	$module->namespace = "SomeModule";
	 * </code>
	 *
	 * This file will be automatically loaded when the module is loaded. You can optionally also
	 * configure the module inline, through the load() method, like:
	 *
	 * <code>
	 * Module::load("SomeModule");		// No extra configuration
	 * Module::load("SomeModule", array(
	 * 	"namespace" => "SomeModulesNamespace"		// Overriding default settings
	 * ));
	 * </code>
	 *
	 * When neither of the configuration options is used, the values will be guessed as follows:
	 *
	 * <table border="1" cellpadding="3">
	 * <thead>
	 * 	<tr>
	 * 		<th>Setting</th>
	 * 		<th>Guess</th>
	 * 		<th>Example for SomeModule</th>
	 * 		<th>Description</th>
	 * 	</tr>
	 * </thead>
	 * <tbody>
	 * 	<tr>
	 * 		<td>namespace</td>
	 * 		<td>The name of the Module</td>
	 * 		<td>SomeModule</td>
	 * 		<td>The PHP namespace for your module.</td>
	 * 	</tr>
	 * 	<tr>
	 * 		<td>path</td>
	 * 		<td>MODULE_PATH + name of the module</td>
	 * 		<td>/srv/www/htdocs/Modules/SomeModule</td>
	 * 		<td>The full root path of the module.</td>
	 * 	</tr>
	 * 	<tr>
	 * 		<td>urlPrefix</td>
	 * 		<td>/ + slugified name of the module</td>
	 * 		<td>/somemodule</td>
	 * 		<td>The prefix for all routes defined in this module. You can also enter an empty string, so the routes will be the same as the application.</td>
	 * 	</tr>
	 * </table>
	 *
	 * When you use autoLoadModules (see Application), all subdirectories in the Modules/ directory
	 * will be automatically loaded, with no extra configuration (except the Module.php config file). It will
	 * assume the name is the same as the directory name.
	 *
	 * @see  ChickenWire\Application
	 * 
	 * @package ChickenWire
	 */
	class Module extends Core\MagicObject {

		/**
		 * Get the Module that is currently loading its configuration files.
		 * @return \ChickenWire\Model|false The Module that is currently loading configuration files, or false when no Module is loading config.
		 */
		public static function getConfiguringModule()
		{
			return self::$_configuringModule;
		}
		protected static $_configuringModule = false;

		protected static $_propRead = array("name", "path", "namespace", "urlPrefix");

		protected static $_modules = array();

		public static function &all() {
			return self::$_modules;
		}


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
			$this->_urlPrefix = rtrim($settings['urlPrefix'], '/ ');

			// Load configuration
			$this->_loadConfig();

		}

		protected function _loadConfig() {

			// A config dir?
			$configDir = $this->_path . "/Config";
			if (!file_exists($configDir) || !is_dir($configDir)) return;

			// Start route-with
			self::$_configuringModule = $this;

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
			self::$_configuringModule = false;

		
		}


	}


?>