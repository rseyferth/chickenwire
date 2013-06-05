<?php

	namespace ChickenWire\Serialization;

	class Xml extends Serializer
	{

		static $includeRoot = true;

		private $writer;
		
		public function toString()
		{

			// Is the root an array of items?
			if (count($this->serialized) > 0 && array_key_exists(0, $this->serialized)) {

				// Get class of first item
				$itemName = key($this->serialized[0]);
				$root = \ChickenWire\Util\Str::pluralize($itemName);

			} else {
				$root = null;
			}
			
			// Create XML writer
			$this->writer = new \XmlWriter();
			$this->writer->openMemory();
			if (!is_null($root)) $this->writer->startElement($root);
			$this->write($this->serialized);
			if (!is_null($root)) $this->writer->endElement();
			$this->writer->endDocument();
			$xml = $this->writer->outputMemory(true);



//			var_dump($this->serialized);
			return $xml;


			//return json_encode($this->serialized);
		}

		private function write($data, $tag=null)
		{
			foreach ($data as $attr => $value)
			{
				if ($tag != null)
					$attr = $tag;


				if (is_int($attr)) {
					$this->write($value);
					continue;
				}

				if (is_array($value) || is_object($value))
				{
					if (!is_int(key($value)))
					{
						$this->writer->startElement($attr);
						$this->write($value);
						$this->writer->endElement();
					}
					else
						$this->write($value, $attr);

					continue;
				}

				$this->writer->writeElement($attr, $value);
			}
		}	


	}


?>