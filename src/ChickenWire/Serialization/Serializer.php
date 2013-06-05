<?php

	namespace ChickenWire\Serialization;

	abstract class Serializer
	{

		static $includeRoot = false;

		static function get($type) {

			// Create classname from it
			$className = "\\ChickenWire\\Serialization\\" . ucfirst(strtolower($type));
			return new $className();

		}

		public $serialized;


		public function serialize($input)
		{

			// Is it an array?
			if (is_array($input)) {

				// Loop and serialize all
				$result = array();
				foreach ($input as $key => $item) {
					$result[$key] = static::serialize($item, false);
				}


			} elseif (is_object($input) && $input instanceof ISerializable) {

				// Hello
				$result = $input->toObject(array(
					"includeRoot" => static::$includeRoot
				));
			
			} else {

				// Just serialize it as it is
				$result = $input;

			}

			// Store and return
			$this->serialized = $result;
			return $result;


		}

		abstract public function toString();


		public function __toString()
		{
			return $this->toString();
		}

	}


?>