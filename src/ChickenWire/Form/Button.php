<?php

	namespace ChickenWire\Form;


	use \HtmlObject\Input;

	class Button extends Field
	{

		static $mandatoryOptions = array();
		static $defaultOptions = array(
			"type" => "button",
			"name" => ""
		);

		static $defaultTemplate = <<<EOD
%field%
EOD;


		public function getElement()
		{

			// Create input
			$input = new Input($this->type, $this->name, $this->value);

			// Done.
			return $input;

		}

	}


?>