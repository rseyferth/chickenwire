<?php

	namespace ChickenWire;

	use \ChickenWire\Auth\Auth;
	use \ChickenWire\Util\Http;
	use \ChickenWire\Util\Mime;
	use \ChickenWire\Util\Str;

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

		static $respondsTo;

		static $requiresAuth = null;

		protected $request;
		protected $auth;

		private $_rendered;

		/**
		 * The content type of the response the Controller will send.
		 * This will be null until content negotiation is complete.
		 * 
		 * @var Mime
		 */
		protected $contentType;

		protected $actionRespondsTo;



		public function __construct(\ChickenWire\Request $request, $execute = true) {

			// Default values
			$this->_rendered = false;
			$this->contentType = null;

			// No responds to set?
			if (!isset(static::$respondsTo)) {

				// Use default mime type
				self::$respondsTo = Application::getConfiguration()->defaultOutputMime;

			}

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

				// Check responds to
				if ($this->_checkIfActionReponds() == false) {
					return;
				}

				// Call to action
				$this->_callToAction($reflection);

				

			} else {

				throw new \Exception("There is no method '" . $action . "' on " . get_class($this), 1);				

			}


		}

		private function _checkIfActionReponds()
		{

			// Parse respondsTo
			$respondsTo = array();
			if (is_string(static::$respondsTo)) {

				// Just one type
				$respondsTo[] = static::$respondsTo;

			} elseif (is_array(static::$respondsTo)) {

				// Multiple types
				foreach (static::$respondsTo as $key => $value) {

					// Was it configured?
					if (is_numeric($key)) {

						// Nope, just the type
						$respondsTo[] = $value;

					} else { 

						// The key was the type, now look at the specs
						if (is_array($value)) {

							// Is current action in the 'except' clause?
							if (array_key_exists('except', $value)) {

								// String or array?
								if (is_string($value['except']) && $value['except'] == $this->route->action) {
									continue;
								} elseif (is_array($value['except']) && in_array($this->route->action, $value['except'])) {
									continue;
								}

							}

							// Is current action in the 'only' clause?
							if (array_key_exists('only', $value)) {

								// String or array?
								if (is_string($value['only']) && $value['only'] !== $this->route->action) {
									continue;
								} elseif (is_array($value['only']) && !in_array($this->route->action, $value['only'])) {
									continue;
								}

							}

							// All is good... add it.
							$respondsTo[] = $key;

						}

					}


				}

			}

			// Store respondsTo for later
			$this->actionRespondsTo = $respondsTo;


			// Now look at accepted types!
			foreach ($this->request->preferredContent as $accept) {
				
				// Match?
				if (in_array($accept->type, $respondsTo)) {

					// We have a match!
					return true;

				}
			}

			// We don't provide that type around here...
			Http::sendStatus(406);
			die("Content-type not allowed");


		}

		/**
		 * Render
		 *
		 * This should be elaborated on of course:
		 *
		 * render :edit
		 * render :action => :edit
		 * render 'edit'
		 * render 'edit.html.erb'
		 * render :action => 'edit'
		 * render :action => 'edit.html.erb'
		 * render 'books/edit'
		 * render 'books/edit.html.erb'
		 * render :template => 'books/edit'
		 * render :template => 'books/edit.html.erb'
		 * render '/path/to/rails/app/views/books/edit'
		 * render '/path/to/rails/app/views/books/edit.html.erb'
		 * render :file => '/path/to/rails/app/views/books/edit'
		 * render :file => '/path/to/rails/app/views/books/edit.html.erb'
		 *
		 * Calls to the render method generally accept four options:
		 * 
		 *	:content_type
		 *	:layout
		 *	:status
		 *	:location
		 * 
		 * @param  [type] $options [description]
		 * @return [type]          [description]
		 */
		protected function render($options = null)
		{

			// We've rendered
			$this->_rendered = true;

			// Figure out the options
			$this->_interpretOptions($options);
			
			// Nothing?
			if (array_key_exists("nothing", $options) && $options['nothing'] == true) {
				return;
			}

			// Render template?
			if (array_key_exists("template", $options)) {
				$this->_renderTemplate($options);
				return;
			}

		}

		private function _renderTemplate($options)
		{

			// We start out with nothing
			$filename = null;
			$contentType = null;

			/**
			 * This is for a template that already have a file extension.
			 *
			 * >> See if the request accepts that file type
			 */
			if (preg_match('/\.([a-z]{2,5})(\.php)?$/', $options['template'], $extension)) {

				// Get mime type
				$mime = Mime::byExtension($extension[1]);
				if ($mime === false) {
					throw new \Exception("The extension of the view you are trying to render is unknown to ChickenWire: " . $extension[1], 1);					
				}

				// Loop through accepted types to see if this is acceptable.
				foreach ($this->request->preferredContent as $accept) {

					// Same?
					if ($accept->type == $mime->type) {

						// Use it!
						$contentType = $mime;
						break;

					}

				}
				
				// None found?
				if (is_null($contentType)) {

					// That means we're trying to render a template that is not accepted by the request...
					Http::sendStatus(406);
					die("Can't find right type of content...");
					
				}

				// Does it already have a .php?
				if (count($extension) < 3) {

					// Add it then...
					$options['template'] .= ".php";

				}

				// Does the file exists..?
				if (!file_exists($options['template'])) {
					throw new \Exception("View not found: " . $options['template'], 1);					
				}

			} 

			/**
			 * This is for a template without a file extension
			 *
			 * >> Guess file extension based on the request's preferred content
			 */
			else {

				// Go guess the filename!
				$filename = $this->_guessExtension($options['template']);

				// Nothing found?
				if ($filename === false) {

					// Was HTML or All a possibility?
					throw new \Exception("Unable to find a view for current request, using " . $options['template'], 1);
					return false;

				}

				// Filename found
				$options['template'] = $filename;


			}

			// Send the content type
			$this->contentType = Mime::byExtension(Str::getContentExtension($options['template']));
			Http::sendMimeType($this->contentType);


			// Let's render it :)
			require $options['template'];

		}

		/**
		 * Guess a file extension for the given extensionless filename, based
		 * on the request headers, or chosen content type.
		 * 
		 * @param  string $filename The filename without extension to complete. This already needs to be complete path.
		 * @param  string $suffix   (default '.php') Suffix to put at then end of every filename to try.
		 * @return string|false     The completed filename, when a suitable file was found, or false if not.
		 */
		private function _guessExtension($filename, $suffix = '.php')
		{


			// Content type chosen?
			$possibleExtensions = array();
			$allAccepted = false;
			if (!is_null($this->contentType)) {

				// Get extensions
				$possibleExtensions = $this->contentType->getExtensions();

			} else {

				// Collect extensions for all types
				foreach ($this->request->preferredContent as $type) {
					$possibleExtensions = array_merge($possibleExtensions, $type->getExtensions());
					if ($type->type == Mime::ALL) {
						$allAccepted = true;
					}
				}

			}

			// See if any of 'em exist
			foreach ($possibleExtensions as $ext) {
				
				// Something there?
				$tryFilename = $filename . ".$ext$suffix";
				if (file_exists($tryFilename)) {
					return $tryFilename;
				}

			}

			// Nothing found... Was */* one of accepted headers?
			if ($allAccepted) {

				// Check dir
				$dir = dirname($filename);
				if (!file_exists($dir) || !is_dir($dir)) {
					return false;
				}

				// File part
				$searchFor = substr($filename, strlen($dir) + 1);

				// Look in the directory				
				$dh = opendir($dir);
				while (false !== ($file = readdir($dh))) {

					// Starts with our template and ends with .php?
					if (is_file($dir . '/' . $file) && preg_match('/^' . preg_quote($searchFor) . '(.*)\.php$/', $file)) {

						// Well, we found a file with an extension, so let's serve it
						return $dir . '/' . $file;

					}
					
				}
				

			}

			// Nothing :(
			return false;

		}



		private function _interpretOptions(&$options)
		{

			// A null?
			if (is_null($options)) {

				// Then we use the current route's action
				$options = $this->request->route->action;

			}

			// Is it a single string?
			if (is_string($options)) {

				// No slashes at al?
				if (!preg_match('/\//', $options)) {

					// That means it's the action
					$options = array('action' => $options);
					
				// Starts with a slash?
				} elseif ($options[0] == '/') {

					// Then it's a file
					$options = array('file' => $options);
					
				// Then there's one or more slashes in the middle
				} else {

					// Then it's a template from another resource
					$options = array('template' => $options);
					
				}

			} elseif (is_array($options)) {

				// @todo: Probably some validation.


			} else {

				throw new \Exception("The 'render' method takes either a string or an array as an argument.", 1);

			}

			// Did we end up with an action?
			if (array_key_exists("action", $options)) {

				// Does this route have a model?
				if (is_null($this->request->route->models)) {
					throw new \Exception("This route does not have a model linked to it, so you have to define a template, instead of an action.", 1);
				}
				
				// Remove namespace
				$model = $this->request->route->models[count($this->request->route->models) - 1];
				$model = Str::removeNamespace($model);

				// Convert it to a full template				
				$options['template'] = Str::pluralize($model) . "/" . $options['action'];
				unset($options['action']);

			}

			// And now... maybe a template?
			if (array_key_exists("template", $options)) {

				// Add the application/module view path to it.
				if (!is_null($this->request->route->module)) {

					// Use module path
					$options['template'] = $this->request->route->module->path . "/Views/" . trim($options['template'], '/ ');

				} else {

					// Use application view path
					$options['template'] = VIEW_PATH . "/" . trim($options['template'], '/ ');

				}


			}

			// Check default settings
			$options = array_merge(array(
				"contentType" => null,
				"status" => 200,
				"layout" => null
			), $options);

		}


		private function _callToAction(\ReflectionMethod $action)
		{

			// Call action
			$action->invoke($this);

			// Have we rendered?
			if ($this->_rendered == false) {

				// Do we have a model available to determine a view by?
				if (!is_null($this->request->route->models)) {

					// Try to render my action
					$this->render($this->request->route->action);

				}

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

				// Is it an array?
				if (!is_array($auth['only'])) {
					$auth['only'] = array($auth['only']);
				}

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

				// Store last page :)
				$this->auth->rememberPage($this->request->uri);

				// Redirect to login page
				if (!is_null($this->auth->loginAction)) {
					$this->_invokeAuthLoginAction();
				} else {
					$this->redirectTo($this->auth->loginUri);
				}
				return false;

			} else {

				// We're in!
				return true;

			}


		}

		/**
		 * Send a redirect header to the given location
		 * @param  string $uri The Uri to redirect to
		 * @param  string $statusCode 	(default: 302) The HTTP status code to use
		 * @return void     
		 */
		protected function redirectTo($uri, $statusCode = 302)
		{

			// Send header
			Http::sendStatus($statusCode);
			Http::redirect($uri);

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

				// Send not auth!
				Http::sendStatus(401);
				

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

		protected function __get_params() {
			return $this->request->params;
		}
		protected function __get_route() {
			return $this->request->route;
		}

		protected function __get_html() {
			return \ChickenWire\Util\Html::instance();
		}
		protected function __get_url() {
			return \ChickenWire\Util\Url::instance();
		}



		protected function show404() 
		{

			//@TODO Real implementation...
			Util\Http::sendStatus(404);
			echo ('Page cannot be found');

		}


	}




?>