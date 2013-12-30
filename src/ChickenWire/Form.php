<?php

	namespace ChickenWire;

	use \ChickenWire\Application;
	use \ChickenWire\Util\Str;
	use \ChickenTools\Browser;

	use \HtmlObject\Element;
	use \HtmlObject\Input;
	use \HtmlObject\Traits\Tag;

	class Form extends \ChickenWire\Core\MagicObject
	{

		public static $fieldNamespaces = array('\\ChickenWire\\Form');

		public static function addNamespace($ns)
		{
			static::$fieldNamespaces[] = $ns;
		}



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
				"labels" => "before",
				"html" => array()
			), $options);

		}

	
		public function open()
		{

			// Check if method was one of the non-supported ones
			$method = strtolower($this->_settings['method']);
			$realMethod = null;
			if ($method == 'put' || $method == 'delete') {
				$realMethod = $method;
				$method = "post";
			}
			
			// Create dom doc
			$form = Element::form(null, $this->_settings['html']);
			$form->setAttribute("accept-charset", 'UTF-8');
			$form->setAttribute("method", $method);
			$form->setAttribute("action", $this->action);			
			echo $form->open();

			// Create meta div
			$metaDiv = Element::div()->style("margin:0;padding:0;");
			
			
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
			echo ($metaDiv);

		}
		public function close()
		{

			echo ('</form>');

		}

		public function add(\ChickenWire\Form\Field $field)
		{

			// Append it!
			$this->_fields[] = $field;

		}

		public function checkBoxPlus() {

			// Interpet options
			$options = $this->interpretOptions(func_get_args());

			// Add checkbox
			$hiddenField = $this->hiddenField([
				"name" => $options['name'],
				"value" => 0
			]);
			
			// And the checkbox itself
			$checkbox = $this->checkBox($options);
			
		}

		private function interpretOptions($arguments) {

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

			return $options;

		}


		public function __call($name, $arguments)
		{
			
			// Interpet options
			$options = $this->interpretOptions($arguments);

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

						// Strip off array selectors
						$arraySelectors = preg_match_all('/([^\[]+)(\[.*\])/', $options['name'], $matches, PREG_SET_ORDER);
						$arrayAdd = '';
						if (count($matches) > 0) {
							$name = $matches[0][1];
							$arrayAdd = $matches[0][2];
						} else {
							$name = $options['name'];
						}
						
						// Apply name.
						$fieldName = $name;
						$options['name'] = $this->_settings['record']->getClass()  . '[' . $name . ']' . $arrayAdd;

						// Set value
						if (!array_key_exists('value', $options)) {
							$options['value'] = $this->_settings['record']->$fieldName;
						}

						// Array?
						if (is_array($options['value']) && $arrayAdd != '') {

							// Use the array value
							preg_match_all('/\[(\d+)\]/', $arrayAdd, $arrayValues, PREG_SET_ORDER);
							foreach ($arrayValues as $aV) {
								if (array_key_exists($aV[1], $options['value'])) {
									$options['value'] = $options['value'][$aV[1]];
								} else {
									$options['value'] = null;
									break;
								}
							}

						}
					
						// Check if label was passed
						if (!array_key_exists("label", $options)) {

							// Look it up in the i18n dictionary
							$options['label'] = $this->_settings['record']->humanAttributeName($fieldName);

						}

					}

					// Instantiate
					$field = new $className($options);
					$this->add($field);
					echo $field->render();
					return true;

				}
				
			}

			throw new \Exception("No Field class found for $className", 1);
			
			

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