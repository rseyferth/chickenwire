<?php

	use ChickenWire\Route;

	Route::add("/", 
		array(
			"controller" => "ApplicationController",
			"action" => "index",
			"methods" => "GET",
			"ssl" => false,
		));

	Route::add("/page/{pagename}", 
		array(
			"controller" => "ApplicationController",
			"action" => "page",
			"methods" => "GET",
			"ssl" => false,
		));

	Route::resources("/people", "Person", array(
		"controller" => "PersonController"
	));

	Route::add("/deel/{#nummer}/sub/{gedeelte}", array(
		"controller" => "djkfhsd",
		"action" => "index"
	));


?>