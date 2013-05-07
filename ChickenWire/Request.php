<?php

	namespace ChickenWire;

	class Request extends Core\MagicObject
	{

		protected static $_propAccessible = array('uri', 'method', 'urlParams');

		protected $_uri;
		protected $_method;

		protected $_queryParams;
		protected $_urlParams = array();
		protected $_requestParams;

		public $params;

		public function __construct() 
		{

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
				$this->requestParams = $_POST;

			} else {

				// Parse RAW data
				parse_str(file_get_contents("php://input"), $this->_requestParams);

			}

			// Combine all params into a nice store.
			$this->_urlParams = array('test' => array('1', '2', '3'));
			$this->params = new Core\Store(
				$this->_urlParams,
				$this->_requestParams,
				$this->_queryParams);
			$this->params->setReadOnly(true);

			
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




	}


?>