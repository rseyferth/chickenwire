<?php

	namespace ChickenWire;

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
				static::$_backend = new I18n\SimpleBackend();

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
		 * Localize the given object and return the localized value (usually a string)
		 * @param  mixed  $object  An object that supports localization (\ActiveRecord\DateTime)
		 * @param  mixed  $format  (default = null) Optional formatting options appertaining to the given $object
		 * @param  array  $options (default = []) Optional additional options
		 * @return mixed 	The localized value
		 */
		public static function localize($object, $format = null, $options = array())
		{

			return static::getBackend()->localize(static::getLocale(), $object, $format, $options);

		}


		public static function date($object, $format = 'LL')
		{
			return static::getBackend()->date(static::getLocale(), $object, $format);
		}

		/**
		 * Set the current locale
		 * @param string     The locale to use
		 */
		public static function setLocale($locale)
		{
			static::$_locale = $locale;

			// Apply to PHP locale
			$sysLocales = \ChickenWire\Application::getConfiguration()->systemLocales;
			if (array_key_exists($locale, $sysLocales)) {
				setlocale(LC_ALL, $sysLocales[$locale]);
			}

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
			
			// Apply to PHP locale
			if (is_null(static::$_locale)) {
				$sysLocales = \ChickenWire\Application::getConfiguration()->systemLocales;
				if (array_key_exists($locale, $sysLocales)) {
					setlocale(LC_ALL, $sysLocales[$locale]);
				}				
			}
		}

		/**
		 * Get the default locale
		 * @return string
		 */
		public static function getDefaultLocale()
		{
			return static::$_defaultLocale;
		}



		/**
		 * Get an array containing all i18n keys for all locales
		 * @param string    A prefix filter for the keys to load, e.g.: nl.bmk.
		 * @return array 
		 */
		public static function getAll($prefix = '')
		{

			// Make backend load all
			$backend = static::getBackend();
			$backend->loadAll($prefix);

			// Now return requested translations
			return $backend->translations($prefix);

		}




		public static function translateClosure()
		{
			$t = function($key, $options = array()) {
					return I18n::translate($key, $options);
				};
			return $t;
		}


		/**
		 * Parse a float value with localization in mind
		 * @param  string $floatString The string representation of a floating number
		 * @return float              The float value
		 */
		public static function parseFloat($floatString){ 
		    $LocaleInfo = localeconv(); 
		    $floatString = str_replace($LocaleInfo["mon_thousands_sep"] , "", $floatString); 
		    $floatString = str_replace($LocaleInfo["mon_decimal_point"] , ".", $floatString); 
		    return floatval($floatString); 
		} 


	}

?>