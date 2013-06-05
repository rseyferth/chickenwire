<?php

	namespace ChickenWire\Serialization;

	class Xml extends Serializer
	{

		static $includeRoot = true;

		private $writer;
		
		public function toString()
		{

			//return '<xml></xml>';
			
			// Create XML writer
			$this->writer = new \XmlWriter();
			$this->writer->openMemory();
			$this->writer->startDocument('1.0', 'UTF-8');
			$this->writer->startElement("listing");
			$this->write($this->serialized);
			$this->writer->endElement();
			$this->writer->endDocument();
			$xml = $this->writer->outputMemory(true);

			if (@$this->options['skip_instruct'] == true)
				$xml = preg_replace('/<\?xml version.*?\?>/','',$xml);



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