<?php

	namespace ChickenWire\Form;

	use \HtmlObject\Input;

	class HiddenField extends Field
	{

		
		static $defaultTemplate =  <<<EOD
%field%

EOD;

		static $defaultOptions = array(
			"type" => "hidden",
			"value" => ""
		);

		public function getElement()
		{

			// Create the tag
			$this->html['value'] = $this->value;
			$input = new Input('hidden', $this->name, null, $this->html);

			// Render
			return $input;

		}


	}

?>