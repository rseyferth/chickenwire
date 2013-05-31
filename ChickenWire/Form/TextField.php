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
			$input = new Input($this->type, $this->name, $this->value, $this->html);

			// Render
			return $input;

		}


	}

?>