<?php

	use ChickenWire\Auth\Auth;

	Auth::add("BMK", array(

		"model" => "\BMK\Models\BMKUser",
		"type" => Auth::BLOWFISH,
		"loginAction" => "\BMK\Controllers\SessionController::login",
		"rotateSalt" => true

	));
	

?>