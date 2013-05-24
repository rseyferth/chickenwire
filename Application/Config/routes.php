<?php

	use ChickenWire\Route;

	Route::add("/", 
		array(
			"controller" => "ApplicationController",
			"action" => "index",
			"methods" => "GET",
			"ssl" => false,
		));



	Route::resources(array("Client", "Project"));


?>