<?php

	namespace ChickenWire;


	function autoLoad($class) {

		// Look in my parent dir
		$filename = dirname(__DIR__) . '/' . preg_replace("/\\\/", '/', $class) . '.php';
		if (file_exists($filename)) {
			require $filename;
		}

		// Check in loaded modules

	}


	function initAutoLoad() {

		// Include composer's autoloading first
		require_once dirname(__DIR__) . '/vendor/autoload.php';

		// Register my autoloading function
		spl_autoload_register('ChickenWire\autoLoad');


		


	}
	initAutoLoad();








?>