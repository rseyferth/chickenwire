<?php

	namespace ChickenWire\Util;

	use \ChickenWire\Route;


	/**
	 * The Url Helper class
	 *
	 * This class is used to create url's to Model instances,
	 * using the routing configuration to resolve.
	 *
	 *
	 * @package ChickenWire
	 */
	class Url extends \ChickenWire\Core\Singleton
	{


		static $_instance;



		private static $_modelMap;
		private static $_modelNames;
		private static $_modelNamesPlural;


		/**
		 * Is the given URL a full url?
		 * @param  [type]  $url [description]
		 * @return boolean      [description]
		 */
		public static function isFullUrl($url) {

			return preg_match('/^[a-z]{3-6}:\/\//', $url);

		}


		public function __call($name, $arguments)
		{

			// Model names known?
			if (!isset(self::$_modelMap)) {
			
				// In which module are we now? Models are indexed by module/application, so we keep those url's seperate
				$modelPrefix = is_null(Route::current()->module) ? 'App' : Route::current()->module->namespace;
			
				// Loop through Route model map
				self::$_modelMap = array();
				$regex = '/^' . preg_quote($modelPrefix) . '\/(.+)/';
				foreach (Route::$modelMap as $modelName => $routes) {

					// Right prefix?
					if (preg_match($regex, $modelName, $matches)) {

						// We've also matched the modelname (without module/app prefix), so let's put that in the map
						self::$_modelMap[$matches[1]] = $routes;
					}

				}

				// Map keys to new array
				self::$_modelNames = array_keys(self::$_modelMap);
				
			}

			// Create regex operator for all model names
			$regexModels = '(?<model>' . implode("|", self::$_modelNames) . (count($arguments) > 0 ? '|' : '') . ')';

			// Create regex for actions
			$regexAction = '(?<action>show|new|add|edit|destroy|delete|index|create|update)';
			
			// Is it a {action}{Model}?
			if (preg_match('/^' . $regexAction . $regexModels . '$/', $name, $matches)) {

				// Delete? That becomes destroy... And new becomes add... (php reserved keywords)
				$action = str_replace(
					array('delete', 'new', 'create', 'update'), 
					array('destroy', 'add', 'index', 'show'), 
					$matches['action']);
				
				// Model not given?
				if (empty($matches['model'])) {
					$model = Str::removeNamespace(get_class($arguments[count($arguments) - 1]));
				} else {
					$model = $matches['model'];
				}
				
				// Loop through model's routes to see if there's a match on the action
				foreach (self::$_modelMap[$model] as $route) {
					
					// Match?
					if ($route->action == $action) {

						// Argument given?
						$url = count($arguments) > 0 ? $route->replaceFields($arguments) : $route->pattern;

						// Done.
						return \ChickenWire\Application::getConfiguration()->webPath . $url;

					}

				}

			}

			// Was it just the plural of a model class?
			foreach (self::$_modelNames as $modelName) {

				// Pluralize.
				$plural = Str::pluralize($modelName);

				// Same a command?
				if (lcfirst($plural) == $name) {

					// Try to find the index route then.
					foreach (self::$_modelMap[$modelName] as $route) {

						// Index?
						if ($route->action == 'index') {

							// Argument given?
							$url = count($arguments) > 0 ? $route->replaceFields($arguments) : $route->pattern;

							// Done.
							return \ChickenWire\Application::getConfiguration()->webPath . $url;

						}

					}

				}
				

			}

			
		}

		public function __get($name) {

			// Try it as a method without arguments
			return $this->$name();

		}



		

	}

?>