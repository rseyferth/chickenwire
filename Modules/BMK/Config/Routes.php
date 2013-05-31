<?php

	use \ChickenWire\Route;
	
	Route::resources("Client");


	Route::match("/login", 
		array(
			"controller" => "SessionController",
			"action" => "add"
		));
	Route::match("/login", 
		array(
			"controller" => "SessionController",
			"action" => "create",
			"method" => "post"
		));

	Route::match("/logout", 
		array(
			"controller" => "SessionController",
			"action" => "delete"
		));


	Route::errors("ErrorController");	// @TODO Implement this!

?>