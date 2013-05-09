<?php

	namespace ChickenWire;

	use ChickenWire\Util\Http;
	use ActiveRecord\Inflector;


	class Application extends Core\Singleton
	{

		public static $inflector;

		public static function boot()
		{

			// Boot on instance
			Application::instance()->_boot();
			
		}

		public static function getConfiguration() {
			return Application::instance()->config;
		}




		protected static $_instance;

		public static $defaultSettings = array(
			"webPath" => null,			// Root of the application as seen from the webserver (e.g. /my-application/ for http://www.my-domain.com/my-application/)
			"httpPort" => null,			// The port for HTTP requests (only specify when it deviates from the default port 80)
			"sslPort" => null, 			// The port for HTTPS requests (only specify when it deviates from the default port 443)

			"applicationNamespace" => "Application",

			"timezone" => ""
		);



		public $config;

		protected $_request;
		protected $_route;
		protected $_controller;

		protected function _boot() {

			// Create local inflector
			static::$inflector = Inflector::instance();

			// Create configuration
			$this->_configure();

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
				$controllerName = "\\" . $this->config->applicationNamespace . "\\Controllers\\" .  $this->_route->controller;
				$this->_controller = new $controllerName($this->_request);
				
			} else {

				//@TODO Error processing
				Http::sendStatus($httpStatus);
				echo ("HTTP Error: " . $httpStatus);

			}
			
		}


		/**
		 * Load and apply configuration files
		 * @return [type] [description]
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



	}


?>