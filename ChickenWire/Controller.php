<?php

	namespace ChickenWire;

	/**
	 * The ChickenWire controller class
	 *
	 * This is the basis for all Controllers in your Application. To create
	 * a new controller simply extend this class. Any public function you
	 * define in your controller can then be used to route to.
	 *
	 * <h3>Configurators</h3>
	 * You can add one or more of the following configurators to your controller
	 * to add features.
	 *
	 * <h4>requiresAuth</h4>
	 * If your actions require a user to be logged in, you can define an Auth object
	 * in your configuration, and specify its name in your controllers. See Auth for
	 * more information.
	 * 
	 * <code>
	 * static $requiresAuth = "BMK";			// Authentication required for all actions
	 * static $requiresAuth = array("BMK", 
	 * 	"except" => array("index", "show")		// No authentication required for <i>index</i> and <i>show</i> actions
	 * );
	 * static $requiresAuth = array("BMK", 
	 * 	"only" => array("secret")				// Authentication only required for the <i>secret</i> action
	 * );
	 * </code>
	 *
	 * @see \ChickenWire\Auth
	 * @see \ChickenWire\Route
	 * 
	 * @package ChickenWire
	 */
	class Controller extends Core\MagicObject
	{

		protected $request;


		public function __construct(\ChickenWire\Request $request) {

			// Localize
			$this->request = $request;


			// Auto loading a model?
			if (!is_null($this->route->models) && $this->route->autoLoad === true) {

				$this->_autoLoadModels();
				
			}


			// Check action
			$action = $this->route->action;
			if (method_exists($this, $action)) {

				// Is it public?
				$reflection = new \ReflectionMethod($this, $action);
				if (!$reflection->isPublic()) {

					throw new \Exception("The method '" . $action . "' on " . get_class($this) . " is not public.", 1);				

				}

				// Call action
				$reflection->invoke($this);

			} else {

				throw new \Exception("There is no method '" . $action . "' on " . get_class($this), 1);				

			}


		}


		private function _autoLoadModels() {

			// Loop models
			foreach ($this->route->models as $index => $model) {

				// Namespace it
				$fullModel = "\\" . Application::getConfiguration()->applicationNamespace . "\\Models\\" . $model;
				
				// Look for the fitting id
				if ($index == sizeof($this->route->models) - 1) {

					// It'll be 'id'
					$field = 'id';

				} else {

					// Prefix with modelname
					$field = strtolower($model) . "_id";

				}

				// Present?
				if ($this->request->urlParams->has($field)) {

					// Get id
					$modelId = $this->request->urlParams->getInt($field);

					// Get the class
					$refl = new \ReflectionClass($fullModel);
					if (!$refl->isSubClassOf("ChickenWire\\Model")) {
						throw new \Exception("The model for the current Route was not a ChickenWire\Model instance.", 1);						
					}
					$findMethod = $refl->getMethod('find');
					
					// Find!
					try {

						// Find the record
						$varName = Application::$inflector->variablize($model);
						$this->$varName = $findMethod->invokeArgs(null, array($modelId));	

					} catch (\ActiveRecord\RecordNotFound $e) {
						
						// The record could not be found => Page does not exist
						$this->show404();
						die;

					}
					

				} else {

					//throw new \Exception("There was no URL-parameter for '" . $field . "'. Reconfigure the Route or set autoLoad to false.", 1);
					continue;
					
				}
				
			}

		}

		public function __get_params() {
			return $this->request->params;
		}
		public function __get_route() {
			return $this->request->route;
		}



		protected function show404() 
		{

			//@TODO Real implementation...
			Util\Http::sendStatus(404);
			echo ('Page cannot be found');

		}


	}




?>