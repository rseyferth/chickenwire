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

			// Check value
			$value = $this->value;
			if (is_array($value)) {
				$value = '[Array: ' . implode(",", array_keys($value)) . ']';
			}

			// Create the tag
			$this->html['name'] = $this->name;
			$input = Element::textarea($value, $this->html);

			// Render
			return $input;

		}


	}

?>