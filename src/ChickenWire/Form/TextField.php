<?php

	namespace ChickenWire\Form;

	use \HtmlObject\Input;

	class TextField extends Field
	{

		static $defaultOptions = array(
			"type" => "text",
			"value" => ""
		);

		public function getElement()
		{

			// Create the tag
			$this->html['value'] = $this->value;
			$this->html['id'] = $this->id;
			$input = new Input($this->type, $this->name, null, $this->html);

			// Render
			return $input;

		}


	}

?>