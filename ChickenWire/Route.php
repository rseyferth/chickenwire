<?php

	namespace ChickenWire;

	use \ActiveRecord\Inflector;
	use \ChickenWire\Util\Arry;

	class Route extends Core\MagicObject
	{

		protected static $_routes = array();

		protected static $_propReadWrite = array('ssl', 'pattern', 'controller', 'action', 'methods', 'ssl', 'models', 'autoLoad', 'patternVariables', 'module');

		public static function match($pattern, array $options) {

			// With a module?
			$module = Module::getConfiguringModule();
			if ($module !== false && !array_key_exists("module", $options)) {
				$options['module'] = $module;
			}

			// Create and add
			$route = new Route($pattern, $options);
			array_push(self::$_routes, $route);
		}

		
		/**
		 * Route HTTP errors to the given controller
		 * @param  string $controller The name of the Controller class you want to handle HTTP errors
		 * @param  string $pattern    (default: '/') This controller will be used for all HTTP errors occuring within a Url starting with this pattern, so the default value '/'' will catch all requests.
		 * @return void
		 */
		public static function errors($controller, $pattern = '/')
		{
			
		}


		/**
		 * Match the given request on all configured routes and return first match
		 * @param  ChickenWire\Request $request The request to match
		 * @param  Number &$httpStatus This param will be filled with the resulting HTTP status code
		 * @param  Array  &$urlParams	This will be an array containing the matched URL parameters
		 * @return Route|number          The matched, orfalse when no match was found
		 */
		public static function request($request, &$httpStatus, &$urlParams) {

			// We will look for a best match
			$status = 404;
			$foundRoute = null;
			$foundParams = null;

			// Loop through routes
			foreach (self::$_routes as $route) {

				// Do the regex!
				preg_match_all($route->_regexPattern, $request->uri, $matches);

				// Does the route match?
				if (count($matches[0]) == 1) {

					// Is the method correct as well?
					if (Arry::Contains($request->method, $route->methods, false)) {

						//@TODO Check SSL config

						// We have a method and path match!
						$foundRoute = $route;
						$status = 200;

						// Get parameters
						$foundParams = array();
						if (count($matches) > 1) {
							for ($q = 1; $q < count($matches); $q++) {
								$foundParams[] = $matches[$q][0];
							}
						}
						

						// That's all we need
						break;

					} else {

						// Wrong method; is this the best yet?
						if (is_null($foundRoute) || $status !== 200) {

							// This is the best yet...
							$foundRoute = $route;
							$status = 405;

						}

					}

				}

			}

			// Parse params
			if (sizeof($foundParams) > 0) {

				// Look up the param names in the route
				$urlParams = array();
				foreach ($route->_patternVariables as $index =>$varName) {

					// #'ed param?
					$value = $foundParams[$index];
					if (substr($varName, 0, 1) == '#') {
						$value = intval($value);
						$varName = substr($varName, 1);
					}

					$urlParams[$varName] = $value;
				}

			} else {
				$urlParams = array();
			}

			// Store it!
			$httpStatus = $status;
			return $foundRoute;

		}


		public static function resources($modelClass, array $options = array()) {

			// Store model in all routes
			if (!is_array($modelClass)) { $modelClass = array($modelClass); }
			$options['models'] = $modelClass;

			// Default controller?
			if (!array_key_exists('controller', $options)) {
				$options['controller'] = $modelClass[count($modelClass) - 1] . 'Controller';
			}

			// With a module?
			$module = Module::getConfiguringModule();
			if ($module !== false && !array_key_exists("module", $options)) {
				$options['module'] = $module;
			}	

			// Check the pattern
				if (!array_key_exists('pattern', $options)) {

				// Generate pattern automatically
				$pattern = '';
				foreach ($modelClass as $index => $model) {

					// Add pattern for this model
					$pattern .= '/' . Application::$inflector->tableize($model) . '/';

					// Not last?
					if ($index < sizeof($modelClass) - 1) {

						// Add the model-id-variable
						$pattern .= '{#' . Application::$inflector->variablize($model) . "_id}";

					}

				}

			} else {

				// Is it an array?
				$pattern = $options['pattern'];
				if (!is_array($pattern)) {
					$pattern = array($pattern);
				}

				// Not complete?
				if (sizeof($pattern) != sizeof($modelClass)) {
					throw new Exception("If you specify the pattern in a resources mapping, you need to specify a pattern for each Model.", 1);					
				}

				// Loop through models
				$realPattern = '';
				foreach ($modelClass as $index => $model) {

					// Add pattern for this model
					$realPattern .= $pattern[$index];

					// Not last?
					if ($index < sizeof($pattern) - 1) {

						// Add the model-id-variable
						$realPattern .= '{#' . Application::$inflector->variablize($model) . "_id}";

					}

				}

				// Done.
				$pattern = $realPattern;


			}

			// Remove any trailing slashes
			$pattern = rtrim($pattern, '/ ');

			
			// Index
			Route::match($pattern, array_merge($options, array(
				"methods" => "GET",
				"action" => "index"
			)));

			// Add and create
			Route::match($pattern . "/add", array_merge($options, array(
				"methods" => "GET",
				"action" => "add"
			)));
			Route::match($pattern, array_merge($options, array(
				"methods" => "POST",
				"action" => "create"
			)));

			// Show, edit and update
			Route::match($pattern . "/{#id}", array_merge($options, array(
				"methods" => "GET",
				"action" => "show"
			)));
			Route::match($pattern . "/{#id}/edit", array_merge($options, array(
				"methods" => "GET",
				"action" => "edit"
			)));
			Route::match($pattern . "/{#id}", array_merge($options, array(
				"methods" => "PUT",
				"action" => "update"
			)));

			// Delete!
			Route::match($pattern . "/{#id}", array_merge($options, array(
				"methods" => "DELETE",
				"action" => "destroy"
			)));



			// Any more for the collection?
			if (array_key_exists("collection", $options)) {

				// Not an array?
				if (!is_array($options['collection'])) {
					throw new \Exception("The 'collection' option needs to be an array containing one or more Route configurations to add to the collection ", 1);					
				}

				// Loop it
				foreach ($options['collection'] as $coll) {

					// Array or simple?
					if (!is_array($coll)) {
						$collPattern = $pattern . "/" . $coll;
						$coll = array(
							"methods" => array("GET"),
							"action" => $coll
						);
					} else {
						$collPattern = $pattern . "/" . $coll['pattern'];
					}					

					// Add route
					Route::add($collPattern, array_merge($options, $coll));

				}

			}


			// Any more for the member?
			if (array_key_exists("member", $options)) {

				// Not an array?
				if (!is_array($options['member'])) {
					throw new \Exception("The 'member' option needs to be an array containing one or more Route configurations to add to the member ", 1);					
				}

				// Loop it
				foreach ($options['member'] as $coll) {

					// Array or simple?
					if (!is_array($coll)) {
						$collPattern = $pattern . "/{#id}/" . $coll;
						$coll = array(
							"methods" => array("GET"),
							"action" => $coll
						);
					} else {
						$collPattern = $pattern . "/{#id}/" . $coll['pattern'];
					}					

					// Add route
					Route::add($collPattern, array_merge($options, $coll));

				}

			}


			

		}

		public static function &all() {
			return self::$_routes;
		}



		protected $_pattern;
		protected $_controller;
		protected $_action;
		protected $_methods;
		protected $_ssl;

		protected $_models;
		protected $_autoLoad;

		protected $_module;

		protected $_regexPattern;
		protected $_patternVariables;


		public function __construct($pattern, array $options) {

			// To?
			if (array_key_exists('to', $options)) {
				$to = explode("#", $options['to']);
				$options['controller'] = $to[0];
				$options['action'] = $to[1];
			}

			// No controller defined?
			if (!array_key_exists('controller', $options)) {
				throw new \Exception("You cannot have a route without a 'controller' or a 'to' parameter.", 1);
				die;				
			}

			// Localize options
			$this->_pattern = rtrim($pattern, '/ ');
			$this->_controller = $options['controller'];
			$this->_action = array_key_exists("action", $options) ? $options['action'] : 'index';
			$this->_ssl = array_key_exists("ssl", $options) ? $options['ssl'] : 'index';
			$this->_models = array_key_exists("models", $options) ? $options['models'] : null;
			$this->_autoLoad = array_key_exists("autoLoad", $options) ? $options['autoLoad'] : true;
			$this->_module = array_key_exists("module", $options) ? $options['module'] : null;

			// Find module object
			if (is_string($this->_module)) {
				$this->_module = Module::get($this->_module);
			}

			// Parse methods/method
			if (array_key_exists('method', $options)) {
				$this->_methods = array($options['method']);
			} elseif (!array_key_exists('methods', $options)) {
				$this->_methods = array('GET');
			} elseif (is_array($options['methods'])) {
				$this->_methods = $options['methods'];
			} else {
				$this->_methods = preg_split('/\s/', $options['methods']);
			}

			// Prepend module to pattern and controller
			if (!empty($this->_module)) {
				$this->_pattern = $this->_module->urlPrefix . $this->_pattern;
			}
			

			// Look for params in the pattern
			preg_match_all("/({([^}]*)})/", $this->_pattern, $matches);
			$this->_patternVariables = $matches[2];		

			// Create regular expression to match this pattern
			$this->_regexPattern = "/^" . 
							preg_replace(
								array(
									"/({#([^}]*)})/",
									"/({([^}]*)})/",
								),
								array(
									"(\d[a-zA-Z0-9_\-]*)",
									"([a-zA-Z0-9_-]+)"
								),
								str_replace("/", "\\/", $this->_pattern))
						. "$/";

		}

		public function __get_controllerClass() {

			// Part of a module?
			if (is_null($this->_module)) {

				// Use the application
				$namespace = "\\" . Application::getConfiguration()->applicationNamespace . "\\Controllers";

			} else {

				// Use module namespace
				$namespace = "\\" . $this->_module->namespace . "\\Controllers";

			}

			return $namespace . "\\" . $this->_controller;

		}




		public function __toString() {

			$str = implode("/", $this->_methods) . " " . $this->pattern;
			if ($this->_model) {
				$str .= " (" . $this->_model . ")";
			}
			return $str;

		}



	}	
	
	


?>