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

			// Find fields
			preg_match_all('/%{(?<field>[a-zA-Z\_0-9]+)}/', $value, $matches);
			foreach ($matches['field'] as $field) {
				if (array_key_exists($field, $options)) {
					$value = str_replace('%{' . $field . '}', $options[$field], $value);
				}
			}

			// Find function fields
			preg_match_all('/%{(?<function>[a-zA-Z\_]+)\((?<field>[a-zA-Z\_0-9]+)\)/', $value, $matches);	
			foreach ($matches['function'] as $index => $function) {
				$field = $matches['field'][$index];
				if (array_key_exists($field, $options)) {
					$value = str_replace('%{' . $function . '(' . $field . ')}', $function($options[$field]), $value);
				}
			}

			return $value;

		}


	}



?>