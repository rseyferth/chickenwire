<?php

	use ChickenWire\Route;

	Route::add("/", 
		array(
			"controller" => "ApplicationController",
			"action" => "index",
			"methods" => "GET",
			"ssl" => false,
		));



	Route::resources("Client", array(
		"module" => "BMK",
		"collection" => array(
			"list"
		),
		"member" => array(
			"promote"
		)
	));
	Route::resources(array("Client", "Project"));


?>