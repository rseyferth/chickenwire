<?php

	namespace BMK\Controllers;

	class ClientController extends \ChickenWire\Controller
	{

		static $requiresAuth = "BMK";

		public function index() {

			echo ('Welkom.');

		}



	}


?>