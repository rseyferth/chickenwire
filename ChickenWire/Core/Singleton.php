<?php

	namespace ChickenWire\Core;

	/**
	 * Simple implementation of the singleton pattern.
	 */

	abstract class Singleton
	{

		protected function __construct() {}

		/**
		 * Retrieve the instance (or create it, if it's not already created)
		 * @return mixed The instance of this class.
		 */
		public static function instance() {

			// Not created yet?
			if (!static::$_instance) {
				$class = get_called_class();
				static::$_instance = new $class;
			}			

			// Return it
			return static::$_instance;

		}



		
	}


?>