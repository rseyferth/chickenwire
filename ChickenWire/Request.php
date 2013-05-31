<?php

	namespace ChickenWire;

	use \ChickenWire\Util\Mime;

	class Request extends Core\MagicObject
	{

		
		protected static $_propRead = array('uri', 'method', 'route', 'format');

		protected $_uri;
		protected $_method;

		protected $_queryParams;
		protected $_urlParams = array();
		protected $_requestParams;

		protected $_queryParamsStore = null;
		protected $_urlParamsStore = null;
		protected $_requestParamsStore = null;

		protected $_route;

		protected $_format;

		protected $_browser;
		
		public $params;


		

		public function __construct() 
		{

			//@TODO: CONTENT TYPE NEGOTIATION
			/*var_dump(Mime::$contentTypeMap);
			var_dump($_SERVER['HTTP_ACCEPT']);*/

			// Get app config
			$config = Application::getConfiguration();

			// Parse request Uri from full SERVER request uri
			$uri = $config->webPath ? substr($_SERVER['REQUEST_URI'], strlen($config->webPath)) : $_SERVER['REQUEST_URI'];

			// Localize
			$this->_uri = rtrim($uri, '/ ');

			// Check method
			//@TODO Implement fake-PUT/DELETE requests through ajax (like in Ruby on Rails...)
			$this->_method = $_SERVER['REQUEST_METHOD'];

			// Store query parameters
			$this->_queryParams = $_GET;

			// Check if it's POST or raw data
			if ($this->isPost()) {

				// Use POST params
				$this->_requestParams = $_POST;

			} else {

				// Parse RAW data
				parse_str(file_get_contents("php://input"), $this->_requestParams);

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

		}

		public function setUrlParams($params) {

			// Store it
			$this->_urlParams = $params;

		}


		public function isGet()
		{
			return $this->_method === 'GET';
		}
		public function isPost() 
		{
			return $this->_method === 'POST';
		}
		public function isPut() 
		{
			return $this->_method === 'PUT';
		}
		public function isDelete() 
		{
			return $this->_method === 'DELETE';
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
				$this->_browser = new \ChickenWire\Util\Browser();
			}
			return $this->_browser;

		}


	}


?>