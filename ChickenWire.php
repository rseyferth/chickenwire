<?php

	// Set APP root
	define("APP_ROOT", __DIR__);			

	// Start autoloading of classes
	require 'vendor/autoload.php';
	//require_once 'ChickenWire/AutoLoad.php';

	// Now boot the application
	\ChickenWire\Application::boot();


?>