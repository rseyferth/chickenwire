<?php

	namespace BMK\Controllers;

	use \ChickenWire\Util\Mime;
	use \Application\Models\Client;

	class ClientController extends \ChickenWire\Controller
	{

		static $respondsTo = array(
			Mime::HTML,
			Mime::JSON => array("only" => array("index"))
		);

		static $requiresAuth = "BMK";

		public function index() {
			
			// Get all clients
			$this->clients = Client::all();

			//$this->render();	
			$this->render();//"index.html");

		}

		public function show()
		{


		}



	}


?>