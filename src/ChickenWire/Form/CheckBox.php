<?php

	namespace ChickenWire\Form;

	use \HtmlObject\Input;

	class CheckBox extends Field
	{

		static $defaultOptions = array(
			"type" => "checkbox",
			"value" => ""
		);

		static $defaultTemplate =  <<<EOD
<div class="formitem">
	<div class="field">
		%field%
		%label%
	</div>
</div>
EOD;

		public function getElement()
		{

			// Create the tag
			if ($this->value === 1 || $this->value === "1" || $this->value === true) {
				$this->html['checked'] = "checked";
			}
			$this->html['id'] = $this->id;
			$input = new Input($this->type, $this->name, null, $this->html);

			// Render
			return $input;

		}


	}

?>