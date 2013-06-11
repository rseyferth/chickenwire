<?php

	namespace ChickenWire\Form;

	use \ChickenWire\Application;
	use \ChickenWire\Util\Str;
	use \ChickenTools\Browser;

	use \HtmlObject\Element;
	use \HtmlObject\Input;
	use \HtmlObject\Traits\Tag;

	class Form extends \ChickenWire\Core\MagicObject
	{

		public static $fieldNamespaces = array('\\ChickenWire\\Form');


		protected $_fields;

		protected $_csrfName;
		protected $_csrfToken;

		protected $_settings;


		public function __construct($options = array())
		{

			// Default values
			$this->_fields = array();

			// Default options
			$this->_settings = array_merge(array(
				"action" => null,
				"method" => "get",
				"auth" => null,
				"record" => null,
				"labels" => "before"
			), $options);

		}

	
		public function render()
		{

			// Check if method was one of the non-supported ones
			$method = strtolower($this->_settings['method']);
			$realMethod = null;
			if ($method == 'put' || $method == 'delete') {
				$realMethod = $method;
				$method = "post";
			}
			
			// Create dom doc
			$form = Element::form();
			$form->setAttribute("accept-charset", 'UTF-8');
			$form->setAttribute("method", $method);
			$form->setAttribute("action", $this->action);
			
			// Create meta div
			$metaDiv = Element::div()->style("margin:0;padding:0;");
			$form->addChild($metaDiv);
			
			
			// Real method?
			if (!is_null($realMethod)) {

				// Add method input
				$inputMethod = Input::hidden("_method", $realMethod);
				$metaDiv->addChild($inputMethod);

			}

			// CSRF enabled?
			if (Application::getConfiguration()->enableCsrfGuard == true) {

				// Generate authToken
				\ChickenWire\Util\CsrfGuard::register($this->_csrfName, $this->_csrfToken);
				
				// Add input
				$inputCsrfName = Input::hidden("csrfName", $this->_csrfName);
				$inputCsrfToken = Input::hidden("csrfToken", $this->_csrfToken);
				$metaDiv->addChild($inputCsrfName);
				$metaDiv->addChild($inputCsrfToken);
				
			}

			// Check if we have an UTF8 defying browser
			if (Application::getRequest()->browser->hasBug(Browser::BUG_UTF8_FORM)) {

				// Add fixing input
				$inputUTF8 = Input::hidden("utf8", "&#x2713;");
				$metaDiv->addChild($inputUTF8);

			}

			// Render all fields
			foreach ($this->_fields as $field) {
				$form->addChild($field->getElement());
			}

			// Output!
			return $form->render();


		}

		public function add(\ChickenWire\Form\Field $field)
		{

			// Append it!
			$this->_fields[] = $field;

		}


		public function __call($name, $arguments)
		{
			
			// Check options
			if (sizeof($arguments) == 0) {
				$options = array();
			} else {

				// Not an array??!
				if (is_string($arguments[0])) {

					// Use argument as name param
					$options = array(
						"name" =>  $arguments[0]
					);

				} elseif (is_array($arguments[0])) {

					// Just use it
					$options = $arguments[0];	

				} else {

					// Not possible!
					throw new \Exception("The first argument to '$name' needs to be an array.", 1);
					

				}
				
			}

			// UC first to make it a class name
			$name = ucfirst($name);
			
			// Loop through namespaces to check if it exists
			foreach (self::$fieldNamespaces as $ns) {

				// Does this exist?
				$className = $ns . '\\' . $name;
				if (class_exists($className)) {
					
					// Pass on the record
					$options = array_merge(array(
							"partOfModel" => true
						), $options);
					if (!is_null($this->_settings['record']) && $options['partOfModel'] === true && array_key_exists("name", $options)) {

						// Apply name.
						$fieldName = $options['name'];
						$options['name'] = $this->_settings['record']->getClass()  . '[' . $options['name'] . ']';

						// Set value
						$options['value'] = $this->_settings['record']->$fieldName;
						
					}

					// Instantiate
					$field = new $className($options);
					$this->add($field);
					return $field;

				}
				
			}

			return false;

		}

		public function __toString()
		{
			return $this->render();
		}



		protected function __get_action()
		{

			// Specifically set in settings?
			if (!is_null($this->_settings['action'])) {

				// Get action
				$action = \ChickenWire\Application::getConfiguration()->webPath . $this->_settings['action'];
				return $action;

			}

			// Nothing found :(
			return "";

		}

	}


?>