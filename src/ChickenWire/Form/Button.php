<?php

	namespace ChickenWire\Form;


	use \HtmlObject\Input;

	class Button extends Field
	{

		static $mandatoryOptions = array();
		static $defaultOptions = array(
			"type" => "button",
			"name" => "",
			"html" => array()
		);

		static $defaultTemplate = <<<EOD
%field%
EOD;


		public function getElement()
		{

			// Create input
			$input = new Input($this->type, $this->name, $this->value, $this->html);

			// Done.
			return $input;

		}

	}


?>