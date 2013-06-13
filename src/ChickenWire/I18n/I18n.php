<?php

	namespace ChickenWire\I18n;

	class I18n
	{

		private static $_backend = null;
		private static $_locale = null;
		private static $_defaultLocale = "en";


		/**
		 * Get the current I18n Backend instance
		 * @return Backend 
		 */
		public static function getBackend()
		{

			// Anything there?
			if (is_null(static::$_backend)) {

				// Create simple backend
				static::$_backend = new SimpleBackend();

			}

			// Give it!
			return static::$_backend;
		
		}

		/**
		 * Set the Backend to use for I18n
		 * @param Backend  The Backend instance to use.
		 * @return void
		 */
		public static function setBackend($backend) 
		{
			static::$_backend = $backend;
		}

		/**
		 * Translate given key(s) using the current Backend and locale.
		 * @param  string|array 	A key (or keys in an array) to lookup and translate
		 * @param  array  			Optional array of options... (to be documented further)
		 * @return string|array 	The translation(s) for given key(s).
		 */
		public static function translate($key, $options = array())
		{
			return static::getBackend()->translate(static::getLocale(), $key, $options);
		}


		/**
		 * Set the current locale
		 * @param string     The locale to use
		 */
		public static function setLocale($locale)
		{
			static::$_locale = $locale;
		}

		/**
		 * Get the current locale. If none is defined, the defaultLocale will be returned.
		 * @return string
		 */
		public static function getLocale()
		{
			if (is_null(static::$_locale)) return static::$_defaultLocale;			
			return static::$_locale;
		}
		
		/**
		 * Set the default locale
		 * @param string 	The default locale
		 */
		public static function setDefaultLocale($locale)
		{
			static::$_defaultLocale = $locale;
		}

		/**
		 * Get the default locale
		 * @return string
		 */
		public static function getDefaultLocale()
		{
			return static::$_defaultLocale;
		}









	}

?>