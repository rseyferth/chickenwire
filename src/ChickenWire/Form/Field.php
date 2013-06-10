<?php

	namespace ChickenWire\Form;

	use \ChickenTools\Arry;

	abstract class Field
	{

		public static function addTemplate($key, $template) {
			self::$templates[$key] = $template;
		}
		public static $template = "";

		static $templates = array();


		static $defaultOptions = array(
			'html' => array()
		);
		static $mandatoryOptions = array('name');

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

			// Loop and set options
			foreach ($options as $option => $value) {
				if (isset($this->$option)) {
					throw new \Exception("'$option' is not a valid option for " . get_called_class(), 1);					
				}
				$this->$option = $value;
			}

		}

		abstract public function getElement();

	}

?>