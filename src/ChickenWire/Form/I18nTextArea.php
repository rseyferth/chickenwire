<?php

	namespace ChickenWire\Form;

	use \HtmlObject\Element;

	class I18nTextArea extends Field
	{

		static $defaultOptions = array(
			"value" => ""
		);

		public function getElement()
		{

			// Check value
			$value = $this->value;

			$div = Element::div(null, [
				"data-widget" => "I18nInput",
				"class" => "i18n-input"
			]);
			$fields = Element::div(null, [
				"class" => "fields"
			]);
			$tabs = Element::ul(null, [
				"class" => "tabs"
			]);
			
			// Get locales
			$locales = \ChickenWire\I18n::getAvailableLocales();
			foreach ($locales as $index => $locale) {

				// Create button
				$tab = Element::li("$locale", [
					"data-locale" => $locale,
					"class" => $index == 0 ? "selected" : ''
				]);
				$tabs->addChild($tab);


				// Create the tag
				$this->html['name'] = $this->name . "[$locale]";
				$this->html['data-locale'] = $locale;
				$this->html['class'] = $index == 0 ? "selected" : '';
				$input = Element::textarea($value[$locale], $this->html);
				$fields->addChild($input);

			}



			// Render
			$div->addChild($fields);
			$div->addChild($tabs);
			return $div;

		}


	}

?>