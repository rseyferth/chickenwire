<?php

	namespace ChickenWire\Core;

	class Store 
	{


		protected $_sources = array();
		protected $_readOnly = false;

		public function __construct(&$source1, &$source2 = null, &$source3 = null, &$source4 = null, &$source5 = null, &$source6 = null) 
		{

			// Loop through number of given arguments
			for ($q = 1; $q <= func_num_args(); $q++) {
				$sourceName = 'source' . $q;
				$this->_sources[] =& $$sourceName;
			}

		}

		public function __get($prop) {
			return $this->get($prop);
		}
		public function __isset($prop) {
			foreach ($this->_sources as $source) { 

				// Has prop?
				if (array_key_exists($prop, $source)) {
					return true;
				}

			}
			return false;
		}

		
		public function get($prop) {

			// Loop through sources to find it
			foreach ($this->_sources as $source) { 

				// Has prop?
				if (array_key_exists($prop, $source)) {
					return $source[$prop];
				}

			}

			// Not found
			throw new \Exception("There is no property '" . $prop . "' in this Store", 1);		

		}

		/**
		 * Check if given property is found in the Store
		 * @param  string  $prop Name of the property
		 * @return boolean       True if property is found, false if it does not exist
		 */
		public function has($prop) {

			// Loop through sources to find it
			foreach ($this->_sources as $source) { 

				// Has prop?
				if (array_key_exists($prop, $source)) {
					return true;
				}

			}

			return false;

		}

		public function getArray($prop) {

			// Get property
			$val = $this->get($prop);

			// Parse as a string
			if (is_array($val)) {
				return $val;
			} else {
				return null;
			}

		}

		public function getString($prop) {

			// Get property
			$val = $this->get($prop);

			// Parse as a string
			if (is_string($val)) {
				return strval($val);
			} else {
				return null;
			}

		}

		/**
		 * Get int value from the Store
		 * @param  $string $prop Property name
		 * @return int       Property value, or null when value was not a number.
		 */
		public function getInt($prop) {

			// Get property
			$val = $this->get($prop);

			// Numeric?
			if (is_numeric($val)) {

				return intval($val);

			} else {

				// Not found
				return null;

			}

		}

		/**
		 * Get float value from the Store
		 * @param  $string $prop Property name
		 * @return float       Property value, or null when value was not a number.
		 */
		public function getFloat($prop) {

			// Get property
			$val = $this->get($prop);

			// Numeric?
			if (is_numeric($val)) {

				return floatval($val);

			} else {

				// Not found
				return null;

			}

		}

		/**
		 * Get double value (actually an alias of getFloat, because float and double are 
		 * identical in PHP)
		 * @param  $string $prop Property name
		 * @return float       Property value, or null when value was not a number.
		 */
		public function getDouble($prop) {
			return $this->getFloat($prop);
		}


		/**
		 * Get boolean value from the Store
		 * @param  $string $prop Property name
		 * @return bool       Property value, or null when value was not a boolean.
		 */
		public function getBool($prop) {

			// Get property
			$val = $this->get($prop);

			// String/numeric bool?
			if ($val === 'false') { 
				$val = false; 
			} elseif ($val === 'true') {
				$val = true;
			} elseif ($val === 1 || $val === '1') {
				$val = true;
			} elseif ($val === 0 || $val === '0') {
				$val = false;
			}




			// Boolean?
			if (is_bool($val))  {
				return $val;
			} else {
				return null;
			}

		}

		/**
		 * Make the store read only so the original sources cannot be changed
		 * @param bool $readOnly True for read-only, false for write access
		 */
		public function setReadOnly($readOnly) {
			if (is_bool($readOnly)) {
				$this->_readOnly = $readOnly;
			}
		}


	}


?>