<?php

	namespace ChickenWire\I18n;

	abstract class Backend
	{

		abstract function translations($prefix = '');

		abstract function translate($locale, $key, $options = array());

		abstract function localize($locale, $object, $format = null, $options = array());

		abstract function loadAll($prefix = '');


		protected function _processValue($value, $options)
		{

			// Is the value an array?
			if (is_array($value)) return $value;

			// Any options?
			if (count($options) == 0) return $value;

			// Create replace array
			$find = array(); $replace = array();
			foreach ($options as $key => $val) {
				$find[] = '%{' . $key . '}';
				$replace[] = $val;
			}
			return str_replace($find, $replace, $value);

		}


	}



?>