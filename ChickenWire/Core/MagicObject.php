<?php

	namespace ChickenWire\Core;

	abstract class MagicObject
	{

		protected static $_propAccessible = null;

		public function __get($prop) {

			// Is there a getter function available?
			if (method_exists($this, '__get_' . $prop)) {
				return call_user_method('__get_' . $prop, $this);
			}

			// Check if it is in accessable
			if (!is_null(static::$_propAccessible) && in_array($prop, static::$_propAccessible)) {

				// Property exists?
				$propLocal = '_' . $prop;
				if (isset($this->$propLocal)) {
					return $this->$propLocal;
				}

			}


		}

		public function __set($prop, $value) {

			// Is there a setter function available?
			if (method_exists($this, '__set_' . $prop)) {
				return call_user_method_array('__set_' . $prop, $this, array($value));
			}

			// Check if it is in accessable
			if (!is_null(static::$_propAccessible) && in_array($prop, static::$_propAccessible)) {

				// Property exists?
				$propLocal = '_' . $prop;
				if (isset($this->$propLocal)) {
					$this->$propLocal = $value;
				}

			}

		
		}


	}

?>