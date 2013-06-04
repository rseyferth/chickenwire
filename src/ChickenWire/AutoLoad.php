<?php

	namespace ChickenWire;

	class AutoLoad
	{

		static $namespaceMap = array();
	
	}	


	/**
	 * @ignore
	 * @param  [type] $namespace [description]
	 * @param  [type] $path      [description]
	 * @return [type]            [description]
	 */
	function autoLoadNamespace($namespace, $path) {

		// Set it!
		$namespaceMap = AutoLoad::$namespaceMap;
		$namespaceMap[rtrim($namespace, '\\ ')] = rtrim($path, '/ ');

	}



	/**
	 * @ignore
	 */
	function autoLoad($class) {

		// In the custom namespace mapping?
		$namespaceMap = AutoLoad::$namespaceMap;
		foreach ($namespaceMap as $ns => $path) {

			// Does the namespace match?
			if (preg_match('/^' . preg_quote($ns) . '/', $class)) {
				
				// Try class
				$filename = $path . preg_replace("/\\\/", '/', substr($class, strlen($ns))) . '.php';
				if (file_exists($filename)) {
					require $filename;
					return true;
				}

			}

		}
var_dump($class);
		// Look in my parent dir
		$filename = dirname(__DIR__) . '/' . preg_replace("/\\\/", '/', $class) . '.php';
		if (file_exists($filename)) {
			require $filename;
			return true;
		}

		// Check in loaded modules
		$namespaces = explode("\\", $class);
		foreach (Module::all() as $module) {

			// Does the namespace fit?
			if ($namespaces[0] == $module->namespace) {
				
				// And the rest?
				$moduleClass = substr($class, strlen($namespaces[0]) + 1);
				$filename = $module->path . '/' . preg_replace("/\\\/", '/', $moduleClass) . '.php';
				if (file_exists($filename)) {
					require $filename;
					return true;
				}

			}


		}

		
	}

	/**
	 * @ignore
	 */
	function initAutoLoad() {


		/*// Include composer's autoloading
		require_once APP_ROOT . '/vendor/autoload.php';*/

		// Register my autoloading function (prepending)
		spl_autoload_register('ChickenWire\autoLoad', true, true);


	}
	initAutoLoad();


?>