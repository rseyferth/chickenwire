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
	 * ==Authentication==
	 * If your actions require a user to be logged in, you can define an Auth object
	 * in your configuration, and specify its name in your controllers. See Auth for
	 * more information.
	 * 
	 * <code>
	 * // Authentication required for all actions
	 * static $requiresAuth = "BMK";			
	 *
	 * // No authentication required for *index* and *show* actions
	 * static $requiresAuth = array("BMK",
	 * 	"except" => array("index", "show")		
	 * );
	 *
	 * // Authentication only required for the *secret* action
	 * static $requiresAuth = array("BMK",
	 * 	"only" => array("secret")				
	 * );
	 * </code>
	 *
	 * ==Content negotiation==
	 * [...]
	 *
	 * @see \ChickenWire\Auth\Auth
	 * @see \ChickenWire\Route
	 * 
	 * @package ChickenWire
	 */
	class Controller extends Core\MagicObject
	{

		/**
		 * Configurator for content negotiation
		 * @var string|array
		 */
		static $respondsTo;

		/**
		 * Configurator for authentication
		 * @var string|array
		 */
		static $requiresAuth;


		/**
		 * Configurator for layouts
		 * @var string|boolean
		 */
		static $layout;


		private static $_instance;


		/**
		 * The current Request
		 * @var \ChickenWire\Request
		 */
		protected $request;

		/**
		 * Authentication object that was used to validate the current user. This is only set when
		 * authentication was need for the current action.,
		 * @var \ChickenWire\Util\Auth
		 */
		protected $auth;


		private $_renderedContent;

		private $_renderingTemplate;
		private $_buffering;

		private $_rendered;

		/**
		 * The content type of the response the Controller will send.
		 * This will be null until content negotiation is complete.
		 * 
		 * @var \ChickenWire\Util\Mime
		 */
		protected $contentType;


		private $_actionRespondsTo;

		private $_layout;


		/**
		 * Create a new Controller instance.
		 *
		 * Controllers are automatically created by the Application.
		 * 
		 * @param \ChickenWire\Request 	The Request to handle
		 * @param bool                 	Whether to automatically execute the appropriate action.
		 */
		public function __construct(\ChickenWire\Request $request, $execute = true) {

			// Store instance
			self::$_instance = $this;

			// Default values
			$this->_rendered = false;
			$this->contentType = null;
			$this->_renderedContent = array();
			$this->_renderingTemplate = false;
			$this->_layout = null;
			$this->_buffering = false;

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

				// Show result
				$this->_finish();
				

			} else {

				throw new \Exception("There is no method '" . $action . "' on " . get_class($this), 1);				

			}


		}

		/**
		 * When the action has completed, the finish method will 
		 * tie up loose ends
		 * 
		 * @return void
		 */
		private function _finish()
		{

			// Check layout path
			$layoutPath = !is_null($this->request->route->module) ? $this->request->route->module->path . '/Layouts' : LAYOUT_PATH;

			// Layout set?
			if (!isset($this->_layout)) {

				// Layout in controller?
				if (!is_null(static::$layout)) {

					// Use that.
					$this->_layout = $layoutPath . '/' . static::$layout;

				} elseif (!is_null($this->request->route->module) && !is_null($this->request->route->module->defaultLayout)) {

					// Use that with module info
					$this->_layout = $layoutPath . '/' . $this->request->route->module->defaultLayout;
					

				} else {

					// Use app's default.
					$this->_layout = LAYOUT_PATH . '/' . Application::getConfiguration()->defaultLayout;

				}

			}

			// No layout?
			if ($this->_layout === false) {

				// Just output the content then.
				echo $this->_renderedContent['main'];

			} else {

				// Guess the extension
				$layoutFile = $this->_guessExtension($this->_layout);
				
				// Not found?
				if ($layoutFile === false) {
					throw new \Exception("Couldn't find layout for " . $this->_layout, 1);					
				}
				$this->_layout = $layoutFile;

				// Create and render the layout
				$layout = new Layout($this->_layout, $this->_renderedContent);

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
			$this->_actionRespondsTo = $respondsTo;


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
		 * Render output
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
		 * @param  array 	Render options
		 * @return void
		 */
		protected function render($options = null)
		{

			// Figure out the options
			if ($this->_renderingTemplate) {
				$this->_interpretOptionsWithinView($options);
			} else {
				$this->_interpretOptions($options);
			}
			
			
			// Nothing?
			if (array_key_exists("nothing", $options) && $options['nothing'] == true) {
				return;
			}


			// Buffered rendering?
			if ((array_key_exists("template", $options) || array_key_exists("file", $options) || array_key_exists("text", $options)) && 
					$this->_buffering == false) {

				// Start buffering
				$this->_buffering = true;
				ob_start();

				// Render template?
				if (array_key_exists("template", $options)) {
					$this->_renderTemplate($options);
				}

				// Render file?
				elseif (array_key_exists("file", $options)) {
					$this->_renderFile($options);				
				}

				// Render inline?
				elseif (array_key_exists("text", $options)) {
					echo $options['text'];
				}

				// Get the rendered content
				$this->_buffering = false;
				$content = ob_get_contents();
				ob_end_clean();

				// Store in rendered content
				if (!array_key_exists("main", $this->_renderedContent)) {
					$this->_renderedContent['main'] = $content;
				} else {
					$this->_renderedContent['main'] .= $content;
				}
				return;

			}

			// Partial?
			if (array_key_exists("partial", $options)) {

				// Guess the extension
				$partial = $this->_guessExtension($options['partial']);
				if ($partial === false) {
					throw new \Exception("Could not find partial for " . $options['partial'], 1);					
				}
				$options['partial'] = $partial;

				// Collection?
				if (array_key_exists("collection", $options)) {

					// Loop!
					foreach($options['collection'] as $item) {

						// Set item to local 
						if (!array_key_exists('locals', $options)) {
							$options['locals'] = array();
						}
						$options['locals'][$options['as']] = $item;

						$this->_renderPartial($options);
					}

				} else {

					// Just the once.
					$this->_renderPartial($options);

				}

			}

			// Json?
			if (array_key_exists("json", $options)) {
				$this->_renderSerialized("json", $options);
			}

			// Xml?
			if (array_key_exists("xml", $options)) {
				$this->_renderSerialized("xml", $options);
			}

		}


		private function _renderTemplate($options)
		{

			// We start out with nothing
			$filename = null;
			$contentType = null;
			$this->_renderingTemplate = true;

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

					// Couldn't find anything
					throw new \Exception("Unable to find a view for current request, using " . $options['template'], 1);
				
				}

				// Filename found
				$options['template'] = $filename;


			}

			// Send the content type
			$this->contentType = Mime::byExtension(Str::getContentExtension($options['template']));
			Http::sendMimeType($this->contentType);

			// We've rendered
			$this->_rendered = true;

			// Let's render it :)
			require $options['template'];

		}

		private function _renderPartial($options)
		{

			// Loop through locals
			if (array_key_exists("locals", $options)) {
				foreach ($options['locals'] as $key => $value) {
					$$key = $value;
				}
			}

			// Show the file
			require $options['partial'];
			

		}

		private function _renderFile($options)
		{

			// Does the file already have an extension?
			if (!preg_match('/\.([a-z]{2,5})$/', $options['file'], $extension)) {

				// Guess it. (without .php suffix of course)
				$file = $this->_guessExtension($options['file'], '');

				// No?
				if ($file === false) {

					throw new \Exception("Unable to find a file for current request, using " . $options['file'], 1);

				}

				// Yes.
				$options['file'] = $file;

			} 

			// Send the content type
			$this->contentType = Mime::byExtension(Str::getContentExtension($options['file']));
			Http::sendMimeType($this->contentType);

			// Include the file.
			$content = file_get_contents($options['file']);
			echo $content;

		}

		private function _renderSerialized($type, $options)
		{

			// Get data
			$data = $options[$type];

			// Can we find a mime type for it?
			$this->contentType = Mime::byExtension($type);
			
			// An array?
			if (is_array($data)) {

				// 

			}

			// Try to serialize
			$method = "to_" . strtolower($type);
			$response = $data->$method();

			// Header.
			Http::sendMimeType($this->contentType);

			// Ouptu
			echo $response;
			

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

					// Known content type?
					if (Str::getContentExtension($file))

					// Starts with our template and ends with .php?
					if (is_file($dir . '/' . $file) 
							&& preg_match('/^' . preg_quote($searchFor) . '\.([a-z]+)' . preg_quote($suffix) . '$/', $file)
							&& Mime::byExtension(Str::getContentExtension($file)) !== false) {

						// Well, we found a file with an extension, so let's serve it
						return $dir . '/' . $file;

					}
					
				}
				

			}

			// Nothing :(
			return false;

		}


		/**
		 * Interpret the options given to a render call inside a view.
		 * @param  array    The options to interpret by reference.
		 * @return void
		 */
		private function _interpretOptionsWithinView(&$options)
		{

			// Nut'n?
			if (is_null($options)) {
				throw new \Exception("Cannot execute an empty render inside a view.", 1);				
			}

			// A simple string?
			if (is_string($options)) {
				$options = array("partial" => $options);

			}

			// Not an array?
			if (!is_array($options)) {
				$options = array($options);
			}

			// Empty?
			if (count($options) == 0) {

				// Render nothing.
				$options = array("nothing" => true);
				return;

			}
			// Determine my view path
			$viewPath = !is_null($this->request->route->module) ? $this->request->route->module->path . "/Views/" : VIEW_PATH . "/";

			// Model instances (or empty array)?
			if (isset($options[0]) && is_object($options[0]) && is_subclass_of($options[0], "\\ChickenWire\\Model")) {

				// Get class name
				$modelClass = $options[0]->getClass();
				
				// Do a partial render for the record set
				$options = array(
					"partial" => strtolower($modelClass),
					"collection" => $options
				);

			}

			// Partial?
			if (array_key_exists("partial", $options)) {

				// An object?
				if (array_key_exists("object", $options)) {
					$options['collection'] = array($options['object']);
					unset($options['object']);
				}

				// Collection?
				if (array_key_exists("collection", $options) && !array_key_exists("as", $options)) {

					/// Get class name
					$modelClass = $options['collection'][0]->getClass();
					$options['as'] = strtolower($modelClass);

				}

				// A slashed location?
				if (strstr($options['partial'], '/')) {


				} else {

					// Does this route have a model?
					if (is_null($this->request->route->models)) {
						throw new \Exception("This route does not have a model linked to it, so you have to define a template, instead of an action.", 1);
					}
					
					// Remove namespace
					$model = $this->request->route->models[count($this->request->route->models) - 1];
					$model = Str::removeNamespace($model);

					// Convert it to a full template				
					$options['partial'] = Str::pluralize($model) . "/" . $options['partial'];
					
				}

				// Add _ to filename
				if (!preg_match('/\/_[a-z]+$/', $options['partial'])) {
					
					// Add _
					$options['partial'] = substr($options['partial'], 0, strrpos($options['partial'], "/")) . '/_' . substr($options['partial'], strrpos($options['partial'], "/") + 1);

				}

				// Add view path
				$options['partial'] = $viewPath . $options['partial'];

			}


			// Any other options..?

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

			// Determine my view path
			$viewPath = !is_null($this->request->route->module) ? $this->request->route->module->path . "/Views/" : VIEW_PATH . "/";

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

				// Use module path
				$options['template'] = $viewPath . trim($options['template'], '/ ');

			}

			// Do we have a file render?
			if (array_key_exists("file", $options)) {

				// Is it already a full path?
				if (preg_match('/^\//', $options['file'])) {

					// Fine...


				} 

				// Any path defined?
				elseif (preg_match('/\//', $options['file'])) {

					// Add the application/module view path to it.
					$options['file'] = $viewPath . trim($options['file'], '/ ');

				} 

				// No '/' at all... Let's use the view path then...
				else {

					// Does this route have a model?
					if (is_null($this->request->route->models)) {
						throw new \Exception("This route does not have a model linked to it, so you have to define a path for your file. The path cannot be guessed.", 1);
					}
					
					// Remove namespace
					$model = $this->request->route->models[count($this->request->route->models) - 1];
					$model = Str::removeNamespace($model);

					// Convert it to a full template				
					$options['file'] = $viewPath . Str::pluralize($model) . "/" . $options['file'];
					
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
			if (!isset(static::$requiresAuth) || is_null(static::$requiresAuth) || empty(static::$requiresAuth)) {
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
		 * @param  string  The Uri to redirect to
		 * @param  string  The HTTP status code to use
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
				$fullModel = $model;

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
						$varName = Application::$inflector->variablize(Str::removeNamespace($model));
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

		/**
		 * Getter for the Request parameters
		 * @return \ChickenWire\Core\Store
		 */
		protected function __get_params() {
			return $this->request->params;
		}

		/**
		 * Getter for the Request Route 
		 * 
		 * @return \ChickenWire\Route
		 */
		protected function __get_route() {
			return $this->request->route;
		}

		/**
		 * Getter for the Html helper instance
		 * @return \ChickenWire\Util\Html
		 */
		protected function __get_html() {
			return \ChickenWire\Util\Html::instance();
		}

		/**
		 * Getter for the Url helper instance
		 * @return \ChickenWire\Util\Url
		 */
		protected function __get_url() {
			return \ChickenWire\Util\Url::instance();
		}


		/**
		 * Send a 404 error to the client
		 * @return void
		 */
		protected function show404() 
		{

			//@TODO Real implementation...
			Util\Http::sendStatus(404);
			echo ('Page cannot be found');

		}


	}




?>