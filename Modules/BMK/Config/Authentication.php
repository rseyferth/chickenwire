<?php

	use ChickenWire\Auth\Auth;

	Auth::add("BMK", array(

		"model" => "\BMK\Models\BMKUser",
		"type" => Auth::BLOWFISH,
		"useSalt" => true,
		"loginAction" => "\BMK\Controllers\SessionController::add",
		"loginUri" => "/login",
		"rotateSalt" => true

	));
	

?>