<?php

	namespace ChickenWire\Util;

	class Url
	{


		/**
		 * Is the given URL a full url?
		 * @param  [type]  $url [description]
		 * @return boolean      [description]
		 */
		public static function isFullUrl($url) {

			return preg_match('/^[a-z]{3-6}:\/\//', $url);

		}


	}

?>