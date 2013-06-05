<?php

	namespace ChickenWire\Serialization;

	abstract class Serializer
	{

		static function get($type) {

			// Create classname from it
			$className = "\\ChickenWire\\Serialization\\" . ucfirst(strtolower($type));
			return new $className();

		}


		public function serialize($input, $toString = false)
		{

			// Result
			$result = null;

			// Is it an array?
			if (is_array($input)) {



			} else {

				// Is it Iserializable

			}


		}

	}


?>