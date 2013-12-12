<?php

	namespace ChickenWire;

	use ChickenTools\Http;
	use ChickenWire\Core\Mime;
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
	 * **defaultLayout**
	 * <code>
	 * $config->defaultLayout = "application";
	 * </code>
	 * The default layout to use for all controllers. You can override this in both Controllers and Modules.
	 * 
	 * **defaultOutputMime**
	 * <code>
	 * $config->defaultOutputMime = \ChickenWire\Core\Mime::HTML;
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
	 * **staticThroughChickenWire**
	 * <code>
	 * $config->staticThroughChickenWire = false;
	 * </code>
	 * If you can't configure your .htaccess to serve the static files in the Public/ directories properly, you can set this to true, and ChickenWire
	 * will serve the static files.
	 *
	 * **systemLocales**
	 * <code>
	 * $config->systemLocales = array(
	 * 	"nl" => "nl_NL.UTF-8",
	 * 	"en" => "en_US.UTF-8"
	 * );	
	 * </code>
	 * To enable the use of `strftime`'s localization options, you'll need to match ChickenWire's locales to the system locales. E.g. Ubuntu/Debian's dpkg-reconfigure locales.
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
	 * @see  \ChickenWire\Core\Mime
	 * @see  \ChickenWire\Util\CsrfGuard	 
	 * 
	 * @package ChickenWire
	 */
	class Application extends \ChickenTools\Singleton
	{

		static $_instance;

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
			"webPath" => "/",
			"httpPort" => null,
			"sslPort" => null,

			"enableCsrfGuard" => true,
			"htmlSelfClosingSlash" => true,

			"applicationNamespace" => "Application",

			"defaultOutputMime" => Mime::HTML,
			"defaultCharset" => "utf-8",

			"defaultLayout" => "application",

			"timezone" => "",
			"autoLoadModules" => false,

			"allowExtensionForDefaultMime" => false,
			"treatExtensionAsMimeType" => true,

			"sessionCookieExpireTime" => 3600,
			"sessionRegenerateId" => false,

			"staticThroughChickenWire" => false,

			"systemLocales" => []

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

			// Start the session
			session_start();

			// Create local inflector
			static::$inflector = Inflector::instance();

			// Create configuration
			$this->_configure();

			// Configure session :)
			session_set_cookie_params($this->config->sessionCookieExpireTime);
			if ($this->config->sessionRegenerateId) {
				session_regenerate_id();
			}
			
			// Apply Application's namespace
			AutoLoad::autoLoadNamespace($this->config->applicationNamespace, APP_PATH);

			// Auto load modules?
			if ($this->config->autoLoadModules == true) {
				$this->_loadModules();
			}

			// Create request
			$this->_request = new Request();
			
			// Check if static file exists?
			if ($this->config->staticThroughChickenWire && $this->_request->rawUri != '') {
			
				// In application?
				$filename = PUBLIC_PATH . $this->_request->rawUri;
				if (file_exists($filename)) {
					
					// Mime available?
					$mime = Mime::byFile($filename);
					
					// Send header
					Http::sendMimeType($mime);
					echo file_get_contents($filename);
					die;

				}

				// In modules
				foreach (Module::all() as $module) {

					// In this module?
					$regex = '/^' . preg_quote($module->urlPrefix, '/') . '\/(?<url>.+)$/';
					if (preg_match($regex, $this->_request->rawUri, $matches)) {

						// Does the file exist?
						$filename = $module->path . "/Public/" . $matches['url'];
						if (file_exists($filename)) {
							
							// Mime available?
							$mime = Mime::byFile($filename);
							
							// Send header
							Http::sendMimeType($mime);
							echo file_get_contents($filename);
							die;

						}

					} 

				}

			}

			// Try to route!
			$this->_matchRoute();

		}


		protected function _matchRoute()
		{


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
			define("LAYOUT_PATH", APP_PATH . "/Layouts");
			define("MODEL_PATH", APP_PATH . "/Models");
			define("VIEW_PATH", APP_PATH . "/Views");
			define("PUBLIC_PATH", APP_PATH . "/Public");
			define("MODULE_PATH", APP_ROOT . "/Modules");

			define("MODEL_NS", $this->config->applicationNamespace . "\\Models");

			// Include all php files in the config directory
			$dh = opendir(CONFIG_PATH);
			$configFiles = scandir(CONFIG_PATH, SCANDIR_SORT_ASCENDING);
			foreach ($configFiles as $file) {

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
				$config->setModelDirectory(MODEL_PATH);

				// Apply connections
				$config->setConnections($dbConnections);

				// Set default to my environment
				$config->setDefaultConnection($environment);

			});

			// Set the timezone
			if ($this->config->timezone == '') {
				throw new \Exception("You need to specify the timezone in the Application configuration.", 1);				
			}
			date_default_timezone_set($this->config->timezone);

			// Set self closing slash
			\HtmlObject\Traits\Tag::$useSelfClosingSlash = $this->config->htmlSelfClosingSlash;

			// Set the webPath
			$this->config->webPath = rtrim($this->config->webPath, ' /');

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