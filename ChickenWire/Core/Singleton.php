<?php

	namespace ChickenWire\Core;

	abstract class Singleton
	{

		protected function __construct() {}

		public static function &instance() {

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