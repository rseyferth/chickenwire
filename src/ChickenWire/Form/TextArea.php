<?php

	namespace ChickenWire\Form;

	use \HtmlObject\Element;

	class TextArea extends Field
	{

		static $defaultOptions = array(
			"value" => ""
		);

		public function getElement()
		{

			// Create the tag
			$this->html['name'] = $this->name;
			$input = Element::textarea($this->value, $this->html);

			// Render
			return $input;

		}


	}

?>