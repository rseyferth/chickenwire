<?php

	namespace ChickenWire\Serialization;

	class Json extends Serializer
	{

		static $includeRoot = false;
		static $type = "json";

		public function toString()
		{
			return json_encode($this->serialized);
		}


	}


?>