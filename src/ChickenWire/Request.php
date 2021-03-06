<?php

	namespace ChickenWire;

	use \ChickenWire\Core\Mime;

	/**
	 * Request class
	 */
	class Request extends Core\MagicObject
	{

		
		protected static $_propRead = array('uri', 'rawUri', 'method', 'route', 'format', 'preferredContent');

		protected $_uri;
		protected $_rawUri;
		protected $_method;
		protected $_extension;

		protected $_queryParams;
		protected $_urlParams = array();
		protected $_rawRequestParams = null;
		protected $_requestParams;

		protected $_queryParamsStore = null;
		protected $_urlParamsStore = null;
		protected $_requestParamsStore = null;

		protected $_route;

		protected $_format;

		protected $_browser;

		protected $_preferredContent;
		
		public $params;


		public function __construct() 
		{

			// Get app config
			$config = Application::getConfiguration();

			// Parse request Uri from full SERVER request uri
			$uri = $config->webPath ? substr($_SERVER['REQUEST_URI'], strlen($config->webPath)) : $_SERVER['REQUEST_URI'];

			// Remove query string
			$queryIndex = strpos($uri, '?');
			if ($queryIndex > -1) $uri = substr($uri, 0, $queryIndex);

			// Localize
			$this->_uri = rtrim($uri, '/ ');
			$this->_rawUri = $this->_uri;

			// Check method
			$this->_method = strtolower($_SERVER['REQUEST_METHOD']);
			if ($this->_method == 'post' && array_key_exists("_method", $_POST) && 
					($_POST['_method'] == 'put' || $_POST['_method'] == 'delete')) {

				// Another method given.
				$this->_method = $_POST['_method'];
				unset($_POST['_method']);

			}
			
			// Store query parameters
			$this->_queryParams = $_GET;

			// Check if it's POST or raw data
			if ($this->isPost()) {

				// Use POST params
				$this->_requestParams = $_POST;

			} else {

				// Parse RAW data
				$this->_rawRequestParams = file_get_contents("php://input");
				parse_str($this->_rawRequestParams, $this->_requestParams);

			}

			// Need to check Csrf?
			if (!$this->isGet() && $config->enableCsrfGuard) {
				
				// Check csrfName and csrfToken
				if (!array_key_exists("csrfName", $this->_requestParams) || !array_key_exists("csrfToken", $this->_requestParams) ||
						!\ChickenWire\Util\CsrfGuard::validate($this->_requestParams['csrfName'], $this->_requestParams['csrfToken'])) {

					// Failed!
					\ChickenWire\Util\Http::sendStatus(400);
					echo ("CSRF Failed!");
					die;

				}

			}

			// Combine all params into a nice store.
			$this->params = new Core\Store(
				$this->_urlParams,
				$this->_requestParams,
				$this->_queryParams);
			$this->params->setReadOnly(true);

			// Parse preferred content
			$this->_parsePreferredContent($config);

		}

		/**
		 * Read HTTP accept header (and optionally the request file extension) to
		 * determine which content types the request prefers.
		 * 
		 * @return void
		 */
		private function _parsePreferredContent(Core\Configuration $config)
		{

			// Start empty
			$this->_preferredContent = array();
			$seenTypes = array();

			// Use request extension as mime type?
			if ($config->treatExtensionAsMimeType) {

				// Check my extension
				if (preg_match('/\.([a-z]{2,5})$/', $this->_uri, $extension)) {
					
					// Now match the extension to a mime type
					$mimeExt = Mime::byExtension($extension[1]);

					// Not a known extension?
					if ($mimeExt === false) {

						// Then we just treat is as part of the url...


					// The default output type, while that's not allowed?
					} elseif ($mimeExt->type == $config->defaultOutputMime && !$config->allowExtensionForDefaultMime) {

						// Just leave it as part as url as well.

					} else {

						// Remove it from the uri!
						$this->_uri = substr($this->uri, 0, -strlen($extension[0]));
					
						// Store extension
						$this->_extension = $extension[1];

						// Add to preferred content
						$this->_preferredContent[] = $mimeExt;
						$seenTypes[] = $mimeExt->type;

						// That's it then.
						return;

					}


				}

			}

			// Now parse the HTTP_ACCEPT headers
			// e.g.: 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
			preg_match_all('/(?<type>[a-z\/\+\*]+)(;q=(?<quality>[0-9\.]+))?/', $_SERVER['HTTP_ACCEPT'], $matches);
			$types = $matches['type'];
			$qualities = $matches['quality'];

			// Now loop backwards through qualities, to fill up empty quality fields with last value
			$lastValue = 0;
			for ($q = count($qualities) - 1; $q >= 0; $q--) {

				// Has value?
				if (empty($qualities[$q])) {
					$qualities[$q] = $lastValue;
				} else {
					$lastValue = $qualities[$q];
				}

			}

			// Loop through it and map it!
			foreach ($types as $index => $type) {

				// Map to mime!
				$mime = Mime::byContentType($type, $qualities[$index]);
				
				// False or already known?
				if ($mime === false || in_array($mime->type, $seenTypes)) continue;

				// Store it!
				$this->_preferredContent[] = $mime;
				$seenTypes[] = $mime->type;

			}

		}

		public function accepts($mime)
		{

			// Check mime
			if (is_object($mime)) $mime = $mime->type;

			// Loop through it
			foreach ($this->_preferredContent as $prefMime) {
				if ($prefMime->type == $mime) return true;
			}
			return false;

		}

		public function setUrlParams($params) {

			// Store it
			$this->_urlParams = $params;

		}


		public function isGet()
		{
			return $this->_method === 'get';
		}
		public function isPost() 
		{
			return $this->_method === 'post';
		}
		public function isPut() 
		{
			return $this->_method === 'put';
		}
		public function isDelete() 
		{
			return $this->_method === 'delete';
		}


		protected function __get_raw() {
			return $this->_rawRequestParams;
		}

		protected function __get_urlParams() {
			if (is_null($this->_urlParamsStore)) {
				$this->_urlParamsStore = new Core\Store($this->_urlParams);
			}
			return $this->_urlParamsStore;
		}

		protected function __get_queryParams() {
			if (is_null($this->queryParamsStore)) {
				$this->_queryParamsStore = new Core\Store($this->_queryParams);
			}
			return $this->_queryParamsStore;
		}

		public function __get_requestParams() {
			if (is_null($this->_requestParamsStore)) {
				$this->_requestParamsStore = new Core\Store($this->_requestParams);
			}
			return $this->_requestParamsStore;
		}

		public function __get_browser() {
			if (is_null($this->_browser)) {
				$this->_browser = new \ChickenTools\Browser();
			}
			return $this->_browser;

		}


	}


?>