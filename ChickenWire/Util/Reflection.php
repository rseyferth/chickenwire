<?php

	namespace ChickenWire\Util;

	class Reflection
	{

		/**
		 * Get the class of the caller through backtrace
		 * @return string|false The name of the class, or false when failed
		 */
		public static function getCallingClass() {

			// Get the trace
			$trace = debug_backtrace();

			// Not enough?
			if (count($trace) < 2 || !array_key_exists('class', $trace[2])) {
				return false;
			}

			// Return class for 2nd
			return $trace[2]['class'];

		}

	}

?>