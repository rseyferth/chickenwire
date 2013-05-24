<?php

	use \ChickenWire\Route;
	
	Route::resources("Client", array(
		"collection" => array(
			"list"
		),
		"member" => array(
			"promote"
		)
	));

?>