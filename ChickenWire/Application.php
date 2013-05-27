<?php

	namespace ChickenWire;

	use ChickenWire\Util\Http;
	use ActiveRecord\Inflector;


	/**
	 * ChickenWire Application Class
	 *
	 * The is the main Application class that handles the request
	 * and outputs the requested content.
	 * 
	 * Configuration of the application can be done through files in your 
	 * /Application/Config directory, or any of the Modules/ Config directories).
	 * Any PHP file in those directories will be loaded upon booting of the 
	 * application.
	 *
	 * Possible application settings are:
	 * <table border="1" cellpadding="3">
	 * <thead>
	 * 	<tr>
	 * 		<th>Property</th>
	 * 		<th>Default value</th>
	 * 		<th>Description</th>
	 * 	</tr>
	 * </thead>
	 * <tbody>
	 * 	<tr>
	 * 		<td>applicationNamespace</td>
	 * 		<td>"Application"</td>
	 * 		<td>The PHP namespace for your Application.</td>
	 * 	</tr>
	 * 	<tr>
	 * 		<td>autoLoadModules</td>
	 * 		<td>false</td>
	 * 		<td>Whether to automatically load all modules that are found in your /Modules/ directory. If you leave this on false, you'll have to load each module individually, through Module::load - this allows for more configuration options.</td>
	 * 	</tr>
	 * 	<tr>
	 *  	<td>database</td>
	 *  	<td></td>
	 *  	<td>The ActiveRecord database connection to use.</td>
	 * 	</tr>
	 * 	<tr>
	 *  	<td>httpPort</td>
	 *  	<td>80</td>
	 *  	<td>The port for HTTP requests (only specify when it deviates from the default port 80, otherwise the port number will be added to all generated urls)</td>
	 * 	</tr>
	 * 	<tr>
	 *  	<td>sslPort</td>
	 *  	<td>443</td>
	 *  	<td>The port for HTTPS requests (only specify when it deviates from the default port 443, otherwise the port number will be added to all generated urls)</td>
	 * 	</tr>
	 * 	<tr>
	 *  	<td>timezone</td>
	 *  	<td></td>
	 *  	<td>The timezone used for date/time functions. This needs to be a valid PHP timezone ({@link http://php.net/manual/en/timezones.php}), for example Europe/Amsterdam.</td>
	 * 	</tr>
	 * 	<tr>
	 *  	<td>webPath</td>
	 *  	<td>/</td>
	 *  	<td>Root of the application as seen from the webserver (e.g. /my-application/ for http://www.my-domain.com/my-application/).</td>
	 * 	</tr>
	 * </tbody>
	 * </table>
	 *
	 * Each of these settings can be defined specifically for each environment, or
	 * for all environments at once (see Configuration). 
	 * 
	 * @see  \ChickenWire\Core\Configuration
	 * @see  \ChickenWire\Module
	 * 
	 * @package ChickenWire
	 */
	class Application extends Core\Singleton
	{

		/**
		 * The default inflector used throughout the Application
		 * @var \ActiveRecord\Inflector
		 */
		public static $inflector;

		/**
		 * Boot up the ChickenWire application
		 * @return void
		 */
		public static function boot()
		{

			// Boot on instance
			return Application::instance()->_boot();
			
		}

		/**
		 * Get the ChickenWire application configuration object
		 * @return \ChickenWire\Core\Configuration The ChickenWire Configuration object
		 */
		public static function getConfiguration() {
			return Application::instance()->config;
		}




		protected static $_instance;

		/**
		 * The default settings for the Application. These can be overridden in your config files.
		 * 
		 * @var array
		 */
		public static $defaultSettings = array(
			"webPath" => null,			// 
			"httpPort" => null,			// 
			"sslPort" => null, 			// 

			"applicationNamespace" => "Application",

			"timezone" => "",
			"autoLoadModules" => false
		);


		/**
		 * The Configuration object for the Application
		 * 
		 * @var \ChickenWire\Core\Configuration
		 */
		public $config;

		protected $_request;
		protected $_route;
		protected $_controller;

		/**
		 * Boot up the application (internal function)
		 * @return void
		 */
		protected function _boot() {

			// Create local inflector
			static::$inflector = Inflector::instance();

			// Create configuration
			$this->_configure();

			// Auto load modules?
			if ($this->config->autoLoadModules == true) {
				$this->_loadModules();
			}

			// Create request
			$this->_request = new Request();
			
			// Get route
			$this->_route = Route::match($this->_request, $httpStatus, $urlParams);
			
			// Success?
			if ($httpStatus === 200) {

				// Store url params and the route in request 
				$this->_request->setUrlParams($urlParams);
				$this->_request->route = $this->_route;

				// Load the controller
				$controllerName = $this->_route->controllerClass;
				$this->_controller = new $controllerName($this->_request);
				
			} else {

				//@TODO Error processing
				Http::sendStatus($httpStatus);
				echo ("HTTP Error: " . $httpStatus);

			}
			
		}


		/**
		 * Load and apply configuration files
		 * @return void
		 */
		protected function _configure() {

			// Create my settings object
			$this->config = new Core\Configuration();
			
			// Define some paths
			define("CHICKENWIRE_PATH", dirname(__FILE__));
			define("APP_ROOT", dirname(__DIR__));
			define("APP_PATH", APP_ROOT . "/Application");
			define("CONFIG_PATH", APP_PATH . "/Config");
			define("CONTROLLER_PATH", APP_PATH . "/Controllers");
			define("MODEL_PATH", APP_PATH . "/Models");
			define("VIEW_PATH", APP_PATH . "/Views");
			define("MODULE_PATH", APP_ROOT . "/Modules");

			// Include all php files in the config directory
			$dh = opendir(CONFIG_PATH);
			while (false !== ($file = readdir($dh))) {

				// PHP?
				if (preg_match("/\.php$/", $file)) {

					// Load it
					$this->config->load(CONFIG_PATH . '/' . $file);
					
				}

			}

			// Initalize database configuration
			$dbConnections = $this->config->allFor("database");
			$environment = $this->config->environment;
			\ActiveRecord\Config::initialize(function($config) use ($dbConnections, $environment) {

				// Set model path
				$config->set_model_directory(MODEL_PATH);

				// Apply connections
				$config->set_connections($dbConnections);

				// Set default to my environment
				$config->set_default_connection($environment);

			});

			// Set the timezone
			if ($this->config->timezone == '') {
				throw new \Exception("You need to specify the timezone in the Application configuration.", 1);				
			}
			date_default_timezone_set($this->config->timezone);

		}

		protected function _loadModules() 
		{

			// Check module directories
			$dh = opendir(MODULE_PATH);
			while (false !== ($filename = readdir($dh))) {

				// Directory?
				if (is_dir(MODULE_PATH . "/" . $filename) && !preg_match('/^\./', $filename)) {

					// Load the module
					Module::load($filename);

				}

			}

		}


	}


?>