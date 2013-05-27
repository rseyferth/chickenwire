<?php

	namespace ChickenWire;


	function autoLoad($class) {

		// Look in my parent dir
		$filename = dirname(__DIR__) . '/' . preg_replace("/\\\/", '/', $class) . '.php';
		if (file_exists($filename)) {
			require $filename;
			return;
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
					return;
				}

			}


		}
		

	}


	function initAutoLoad() {

		// Include composer's autoloading first
		require_once dirname(__DIR__) . '/vendor/autoload.php';

		// Register my autoloading function
		spl_autoload_register('ChickenWire\autoLoad');


		


	}
	initAutoLoad();








?>