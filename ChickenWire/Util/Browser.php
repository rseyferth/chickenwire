<?php

	namespace ChickenWire\Util;

	class Browser extends \Ikimea\Browser\Browser
	{

		const BUG_UTF8_FORM = "BUG_UTF8_FORM";


		public function isIE()
		{
			return $this->isBrowser(static::BROWSER_IE);
		}
		public function isFirefox()
		{
			return $this->isBrowser(static::BROWSER_FIREFOX);
		}


		/**
		 * Check whether current browser has the given bug
		 * @param  string  $bug A bug key (one of the BUG_ constants)
		 * @return boolean      Whether the current browser has that bug
		 */
		public function hasBug($bug)
		{
			return $this->isBrowser(static::BROWSER_IE);
		}


	}


?>