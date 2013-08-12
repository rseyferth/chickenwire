<?php

	namespace ChickenWire;

	use ChickenWire\Core\Mime;

	class Layout
	{

		private $_content;

		protected $controller;

		protected $config;


		public function __construct($layout, $controller, $content)
		{

			// Localize
			$this->_content = $content;
			$this->controller = $controller;

			// Get config
			$this->config = Application::getConfiguration();


			// Create translation functions
			$t = I18n::translateClosure();
			
			// Load the file
			require $layout;

		}

		protected function yield($block = 'main')
		{

			// Any content?
			if (!array_key_exists($block, $this->_content)) return;

			// Output the content
			echo $this->_content[$block];

		}


	}




?>