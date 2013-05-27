<?php

	use ChickenWire\Auth;

	Auth::add("BMK", array(

		"model" => "\BMK\Models\User",
		"type" => Auth::SALT,
		"loginAction" => "\BMK\Controllers\SessionController::login"

	));
	

?>