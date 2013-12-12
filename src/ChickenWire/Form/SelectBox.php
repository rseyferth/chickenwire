<?php

	namespace ChickenWire\Form;

	use \HtmlObject\Element;

	class SelectBox extends Field
	{

		static $defaultOptions = array(
			"value" => "",
			"options" => array(),
			"key" => "id" ,
			"caption" => "title",
			"noSelection" => false
		);

		public function getElement()
		{

			// Create select
			$select = Element::select(null, array("name" => $this->name));

			// No selection?
			if ($this->noSelection != false && is_array($this->noSelection)) {

				// Add it!
				$o = Element::option($this->noSelection[1], array("value" => $this->noSelection[0]));
				$select->addChild($o);

			}

			// Loop through options
			foreach ($this->options as $key => $option) {

				// An array?
				if (is_array($option)) {

					// Get fields
					$value = $option[$this->key];
					$caption = $option[$this->caption];
					
				} elseif (is_object($option)) {

					// Get from object
					$value = $option->{$this->key};
					$caption = $option->{$this->caption};

				} else {

					// Numeric key?
					if (is_numeric($key)) {

						// Just use value and caption as same
						$value = $option;

					} else {

						// Use the key as value
						$value =$key;

					}

					$caption = $option;

				}

				// Create option
				$attr = array("value" => $value);
				if ($value == $this->value) {
					$attr['selected'] = "selected";
				}
				$o = Element::option(
					$caption,
					$attr
				);
				$select->addChild($o);

			}

			return $select;

		}




	}

?>