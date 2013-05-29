<?php

	namespace ChickenWire;

	use \ChickenWire\Auth\Auth;

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

		static $requiresAuth = null;

		protected $request;
		protected $auth;


		public function __construct(\ChickenWire\Request $request, $execute = true) {

			// Localize
			$this->request = $request;

			// Don't execute?
			if ($execute == false) {
				return;
			}

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

				// Do we need authentication?
				if ($this->_checkAuth() == false) {
					return;
				}

				// Call action
				$reflection->invoke($this);

			} else {

				throw new \Exception("There is no method '" . $action . "' on " . get_class($this), 1);				

			}


		}

		/**
		 * Check if current action needs authentication, and if it is validated
		 * @return boolean Whether authentication passed
		 */
		private function _checkAuth()
		{

			// Not needed?
			if (is_null(static::$requiresAuth) || empty(static::$requiresAuth)) {
				return true;
			}

			// Get auth
			$auth = static::$requiresAuth;

			// Is there an except clause?
			if (is_array($auth) && array_key_exists("except", $auth)) {

				// Is my method in it?
				if (in_array($this->route->action, $auth['except'])) {
					return true;
				}

			}

			// Is there an only clause?
			if (is_array($auth) && array_key_exists("only", $auth)) {

				// Is my method in it?
				if (!in_array($this->route->action, $auth['only'])) {
					return true;
				}

			}

			// Get auth name
			$authName = is_array($auth) ? $auth[0] : $auth;

			// Find auth object
			$this->auth = Auth::get($authName);
			
			// Is it authenticated?
			if ($this->auth->isAuthenticated() !== true) {

				// Call login controller action
				$this->_invokeAuthLoginAction();
				return false;

			} else {

				// We're in!
				return true;

			}


		}

		/**
		 * Invoke the Login action for the Controller's Auth object
		 * @return void
		 */
		private function _invokeAuthLoginAction()
		{

			// Try to instatiate the login controller (without executing the request)
			$login = new $this->auth->loginController($this->request, false);
			
			// Does it have the method?
			if (method_exists($login, $this->auth->loginAction)) {

				// Is it public?
				$reflection = new \ReflectionMethod($login, $this->auth->loginAction);
				if (!$reflection->isPublic()) {

					throw new \Exception("The method '" . $this->auth->loginAction . "' on " . get_class($login) . " is not public.", 1);				

				}

				// Call action
				$reflection->invoke($login);

			} else {

				throw new \Exception("There is no method '" . $this->auth->loginAction . "' on " . get_class($login), 1);				

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