<?php

	namespace ChickenWire\Util;


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

		/**
		 * Is the given URL a full url?
		 * @param  [type]  $url [description]
		 * @return boolean      [description]
		 */
		public static function isFullUrl($url) {

			return preg_match('/^[a-z]{3-6}:\/\//', $url);

		}


		/**
		 * Get the url for the Show for the given Model instance(s)
		 *
		 * When you pass this function 1 Model instance, it will look up the
		 * show url for that instance:
		 *
		 * <code>
		 * $user = User::find(2);
		 * $url = Url::show($user);
		 * // Output: /users/2
		 * </code>
		 *
		 * When you pass more than 1 Model instance, it will look for a nested resource:
		 *
		 * <code>
		 * $client = Client::find(1);
		 * $project = $client->projects[2];
		 * $url = Url::show($client, $project);
		 * // Output: /clients/1/projects/13
		 * </code>
		 *
		 * If, considering the above example, projects are always accessed through
		 * clients (so there is no /projects/ route), you wil get the same result by
		 * doing:
		 *
		 * <code>
		 * $url = Url::show($project);
		 * // Output: /clients/1/projects/13
		 * </code>
		 *
		 * If however, there are multiple resource-routings for the Model, it will
		 * choose the simplest route.
		 * 
		 * @return string|false The url.  An exception will be thrown if it could not be resolved.
		 */
		public function show()
		{

			// Loop through routes to find a corresponding one
			$route = $this->_findRoute('show', func_get_args());

			// Anything?
			if ($route === false) return false;

			// Do a replacement on the fields
			$mainModel = func_get_arg(func_num_args() - 1);
			$url = $route->pattern;
			foreach ($route->patternVariables as $var) {

				// Look up
				$varName = $var[0] == '#' ? substr($var, 1) : $var;
				$value = $mainModel->$varName;

				// Int?
				if ($var[0] == '#') {
					$value = intval($value);
				}

				// Replace!
				$url = str_replace('{' . $var . '}', strval($value), $url);


			}
			// Done.
			return $url;


		}

		/**
		 * Find a route for the given action and model(s)
		 * @param  string $action Action to look for
		 * @param  array $models Array of Model instances
		 * @return Route|false         The route that was found, or false when not able to resolve.
		 */
		private function _findRoute($action, $models) {

			// Loop through models and collect the classnames
			$classes = array();
			foreach ($models as $model) {
				$classes[] = get_class($model);
			}
			$classesSerialized = serialize($classes);

			// Loop through routes
			$routes = \ChickenWire\Route::all();
			foreach($routes as $route) {

				// Any models?
				if (is_null($route->models)) continue;

				// Exactly the same?
				if ($route->action == $action && serialize($route->models) == $classesSerialized) {
					
					return $route;

				}

				// More checking..?

			}

			// Nothing found
			return false;

		}



		

	}

?>