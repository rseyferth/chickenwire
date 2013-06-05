<?php

	namespace ChickenWire\Serialization;

	class Json extends Serializer
	{

		static $includeRoot = false;
		
		public function toString()
		{
			return json_encode($this->serialized);
		}


	}


?>