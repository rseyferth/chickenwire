<?php

	namespace ChickenWire;


	class Application extends Core\Singleton
	{

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
			"sslPort" => null 			// The port for HTTPS requests (only specify when it deviates from the default port 443)
		);



		public $config;

		protected function _boot() {

			// Create configuration
			$this->_configure();

			// Create request
			$request = new Request();
			
			// Get route
			$route = Route::match($request, $httpStatus, $urlParams);
			
			// Success?
			if ($httpStatus === 200) {

				// Store url params in request
				$request->urlParams = $urlParams;

				echo ("MATCH: " . $route);

				var_dump($request->params);

			} else {

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

		}



	}


?>