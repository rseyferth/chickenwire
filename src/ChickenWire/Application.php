<?php

	namespace ChickenWire;

	use ChickenWire\Util\Http;
	use ChickenWire\Util\Mime;
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
	 * ## Configuration ##
	 * Possible application settings, and their default values, are: 
	 *
	 * **allowExtensionForDefaultMime**
	 * <code>
	 * $config->allowExtensionForDefaultMime = false;
	 * </code>
	 * Whether to allow the extension at the end of the Uri for the defaultOutputMime. For example, if this is set to <i>true</i> requests ending in <i>.html</i> will also be accepted (providing the defaultOutputMime is Mime::HTML).
	 *
	 * **applicationNamespace**
	 * <code>
	 * $config->applicationNamespace = "Application";
	 * </code>
	 * The PHP namespace for your Application.
	 *
	 * **autoLoadModules**
	 * <code>
	 * $config->autoLoadModules = false;
	 * </code>
	 * Whether to automatically load all modules that are found in your /Modules/ directory. If you leave this on false, you'll have to load each module individually, through Module::load - this allows for more configuration options.
	 *
	 * **database**
	 * <code>
	 * // Example code
	 * $config->database = array(
	 *    'development' => 'mysql://root:password@localhost/my_database;charset=utf8',	
	 *    'production' => '[dbtype]://[user]:[pass]@[host]/[dbname];charset=[charset]'
	 * );
	 * </code>
	 * The ActiveRecord database connection to use.
	 *
	 * **defaultOutputMime**
	 * <code>
	 * $config->defaultOutputMime = \ChickenWire\Util\Mime::HTML;
	 * </code>
	 * The default ouptut Mime type that all Controllers will output. Controllers can override this setting by defining a $respondsTo configurator.
	 * 
	 * **enableCsrfGuard**
	 * <code>
	 * $config->enableCsrfGuard = true;
	 * </code>
	 * Whether to require CSRF tokens for each form. Read more on {@link https://www.owasp.org/index.php/Cross-Site_Request_Forgery_(CSRF) Cross-Site Request Forgery}
	 *
	 * **htmlSelfClosingSlash**
	 * <code>
	 * $config->htmlSelfClosingSlash = true;
	 * </code>
	 * Whether to end self-closing HTML tags with a /, for example &lt;br /&gt; or &lt;br&gt;
	 *
	 * **httpPort**
	 * <code>
	 * $config->httpPort = 80;
	 * </code>
	 * The port for HTTP requests (only specify when it deviates from the default port 80, otherwise the port number will be added to all generated urls).
	 *
	 * **sessionCookieExpireTime**
	 * <code>
	 * $config->sessionCookieExpireTime = 3600; // 1 hour
	 * </code>
	 * The expire time for the session cookie in seconds.
	 *
	 * **sessionRegenerateId**
	 * <code>
	 * $config->sessionRegenerateId = false;
	 * </code>
	 * Whether to generate a new PHP session id for each request, to prevent session fixation attacks.
	 * 
	 * **sslPort**
	 * <code>
	 * $config->sslPort = 443;
	 * </code>
	 * The port for HTTPS requests (only specify when it deviates from the default port 443, otherwise the port number will be added to all generated urls).
	 *
	 * **treatExtensionAsMimeType**
	 * <code>
	 * $config->treatExtensionAsMimeType = true;
	 * </code>
	 * Whether to use the request's file extensions in content type negotiation. If you set this to false, only the HTTP_ACCEPT headers will be used for respondTo clauses. When true, the extension will be the first content type the application will try to serve.
	 *
	 * **timezone**
	 * <code>
	 * // Example code
	 * $config->timezone = 'Europe/Amsterdam';
	 * </code>
	 * The timezone used for date/time functions. This needs to be a valid {@link http://php.net/manual/en/timezones.php PHP timezone}, for example "Europe/Amsterdam".</td>
	 * 
	 * **webPath**
	 * <code>
	 * $config->webPath = '/';
	 * </code>
	 * Root of the application as seen from the webserver (e.g. "/my-application/" for http://www.my-domain.com/my-application/).
	 *
	 * Each of these settings can be defined specifically for each environment, or
	 * for all environments at once (see Configuration). 
	 * 
	 * @see  \ChickenWire\Core\Configuration
	 * @see  \ChickenWire\Module
	 * @see  \ChickenWire\Util\Mime
	 * @see  \ChickenWire\Util\CsrfGuard	 
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

		/**
		 * Get the current Request
		 * @return ChickenWire\Request The Request instance
		 */
		public static function getRequest() {
			return Application::instance()->_request;
		}



		/**
		 * The default settings for the Application. These can be overridden in your config files.
		 * 
		 * @var array
		 * @ignore
		 */
		public static $defaultSettings = array(
			"webPath" => null,
			"httpPort" => null,
			"sslPort" => null,

			"enableCsrfGuard" => true,
			"htmlSelfClosingSlash" => true,

			"applicationNamespace" => "Application",

			"defaultOutputMime" => Mime::HTML,
			"defaultCharset" => "UTF-8",

			"timezone" => "",
			"autoLoadModules" => false,

			"allowExtensionForDefaultMime" => false,
			"treatExtensionAsMimeType" => true,

			"sessionCookieExpireTime" => 3600,
			"sessionRegenerateId" => false


		);


		/**
		 * The Configuration object for the Application
		 * 
		 * @var \ChickenWire\Core\Configuration
		 */
		public $config;

		/**
		 * @ignore
		 * @var [type]
		 */
		protected $_request;
		/**
		 * @ignore
		 * @var [type]
		 */
		protected $_route;
		/**
		 * @ignore
		 * @var [type]
		 */
		protected $_controller;

		/**
		 * Boot up the application (internal function)
		 * @return void
		 * @ignore
		 */
		protected function _boot() {

			// Create local inflector
			static::$inflector = Inflector::instance();

			// Create configuration
			$this->_configure();

			// Start session :)
			session_set_cookie_params($this->config->sessionCookieExpireTime);
			if ($this->config->sessionRegenerateId) {
				session_regenerate_id();
			}
			session_start();

			// Auto load modules?
			if ($this->config->autoLoadModules == true) {
				$this->_loadModules();
			}

			// Create request
			$this->_request = new Request();
			
			// Get route
			$this->_route = Route::request($this->_request, $httpStatus, $urlParams);
			
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
		 * @ignore
		 */
		protected function _configure() {

			// Create my settings object
			$this->config = new Core\Configuration();
			
			// Define some paths
			define("CHICKENWIRE_PATH", dirname(__FILE__));
			define("APP_PATH", APP_ROOT . "/Application");
			define("CONFIG_PATH", APP_PATH . "/Config");
			define("CONTROLLER_PATH", APP_PATH . "/Controllers");
			define("MODEL_PATH", APP_PATH . "/Models");
			define("VIEW_PATH", APP_PATH . "/Views");
			define("MODULE_PATH", APP_ROOT . "/Modules");

			define("MODEL_NS", $this->config->applicationNamespace . "\\Models");

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

			// Set self closing slash
			\HtmlObject\Traits\Tag::$useSelfClosingSlash = $this->config->htmlSelfClosingSlash;

		}

		/**
		 * Load all modules in the Modules directory
		 * @return void
		 * @ignore
		 */
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