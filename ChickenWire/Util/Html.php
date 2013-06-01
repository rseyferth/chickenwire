<?php

	namespace ChickenWire\Util;

	class Html extends \ChickenWire\Core\Singleton
	{

		static $_instance;

		public function __construct()
		{

		}


		public function linkTo($target, $caption = null, $attributes = array())
		{

			// Check if target needs to be resolved
			if (is_array($target) || is_object($target)) {

				// Try to get a link
				$target = Url::instance()->show($target);

			}

			// Create element
			$link = new \HtmlObject\Link($target, $caption, $attributes);
			return $link;


		}

		


	}


?>