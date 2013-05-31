<?php

	use ChickenWire\Route;

	Route::match("/", 
		array(
			"to" => "ApplicationController#index",
			"methods" => "GET",
			"ssl" => false,
		));




	Route::resources("Project");


?>