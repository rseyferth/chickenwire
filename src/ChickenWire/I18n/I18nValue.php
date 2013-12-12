<?php

	namespace ChickenWire\I18n;

	class I18nValue extends \ArrayObject
	{

		function __construct()
		{

			parent::__construct();

			$locales = \ChickenWire\I18n::getAvailableLocales();
			foreach ($locales as $locale) {
				$this[$locale] = '';
			}
			

		}

		public function __toString()
		{

			$curLocale = \ChickenWire\I18n::getLocale();
			if (array_key_exists($curLocale, $this)) {
				return $this[$curLocale];
			}
			return "No translation for '$curLocale'";
			

		}

	}

?>