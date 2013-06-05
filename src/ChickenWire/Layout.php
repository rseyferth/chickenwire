<?php

	namespace ChickenWire;

	use ChickenWire\Util\Mime;

	class Layout
	{

		private $_content;

		public function __construct($layout, $content)
		{

			// Localize
			$this->_content = $content;

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