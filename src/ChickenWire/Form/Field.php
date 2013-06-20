<?php

	namespace ChickenWire\Form;

	use \ChickenTools\Arry;
	use \HtmlObject\Element;

	abstract class Field
	{

		public static function setTemplate($keys, $template) {
			if (!is_array($keys)) $keys = array($keys);
			foreach ($keys as $key) {
				self::$templates[$key] = $template;
			}
		}
		
		static $templates = array();
		static $defaultTemplate =  <<<EOD
<div class="formitem">
	%label%
	<div class="field">
		%field%
	</div>
</div>
EOD;


		static $defaultOptions = array(
			'html' => array()
		);
		static $mandatoryOptions = array('name');


		public $label;
		public $id;


		public function __construct(array $options = array())
		{

			// Is there an unnamed option?
			if (array_key_exists(0, $options) && !array_key_exists("name", $options)) {
				$options['name'] = $options[0];
				unset($options[0]);
			}


			// Check my mandatory options
			if (isset(static::$mandatoryOptions)) {

				// All options fixed?
				foreach (static::$mandatoryOptions as $option) {
					if (!array_key_exists($option, $options)) {
						throw new \Exception(get_called_class() . " needs at least the following options: " . implode(', ', static::$mandatoryOptions), 1);
						
					}
				}

			}

			// Merge with default options
			$options = Arry::mergeRecursiveDistinct(Arry::mergeStatic(get_called_class(), "defaultOptions"), $options);

			// Default id?
			if (!array_key_exists("id", $options)) {
				$options['id'] = $options['name'];
			}

			// Loop and set options
			foreach ($options as $option => $value) {
				if (isset($this->$option)) {
					throw new \Exception("'$option' is not a valid option for " . get_called_class(), 1);					
				}
				$this->$option = $value;
			}

		}

		public function template()
		{

			// Find template
			$fieldType = \ChickenTools\Str::removeNamespace(get_class($this));

			// Do I have a specific template set?
			if (array_key_exists($fieldType, Field::$templates)) {
				return Field::$templates[$fieldType];
			}

			// A default template
			if (!is_null(static::$defaultTemplate)) return static::$defaultTemplate;

			// Nothing.
			return 'No template for ' . get_class($this);

		}

		public function render()
		{

			// Label?
			if ($this->label) {
				$label = Element::label($this->label, array("for" => $this->id))->render();					
			} else {
				$label = '';
			}
			
			// Create html
			$html = $this->template();
			$html = str_replace('%label%', $label, $html);
			$html = str_replace('%field%', $this->getElement()->render(), $html);

			// Add field
			return $html;

		}

		abstract public function getElement();

	}

?>