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


		public static function getClosureParams($closure)
		{

			// Already a method?
			if ($closure instanceof \Closure) {
				$refl = new \ReflectionObject($closure);
				$reflMethod = $refl->getMethod('__invoke');
			} else {
				$reflMethod = $closure;	
			}			
			return $reflMethod->getParameters();

		}

		public static function getClosureMethod(\Closure $closure)
		{
			$refl = new \ReflectionObject($closure);
			return $refl->getMethod('__invoke');
		}


		public static function invokeClosure(\Closure $closure, $arguments = array(), $arguments2 = null, $arguments3 = null)
		{

			// Check the number of arguments the closure expects
			$method = self::getClosureMethod($closure);
			$nrParams = $method->getNumberOfParameters();

			// Check which of the given arguments arrays will fit (pseudo-overloading methods)
			if ($nrParams == count($arguments)) {
				$args = $arguments;
			} elseif (!is_null($arguments2) && $nrParams == count($arguments2)) {
				$args = $arguments2;
			} elseif (!is_null($arguments3) && $nrParams == count($arguments3)) {
				$args = $arguments3;
			} else {
				throw new \Exception("The provided callback could not be called with any of the available arguments.", 1);				
			}

			// Call it!
			return $method->invokeArgs($closure, $args);

		}


	}

?>