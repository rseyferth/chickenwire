<?php

	namespace BMK\Controllers;

	use \ChickenWire\Util\Mime;
	use \Application\Models\Client;

	class ClientController extends \ChickenWire\Controller
	{

		static $respondsTo = array(
			Mime::HTML,	Mime::JSON
		);

		static $requiresAuth = "BMK";

		public function index() {
			
			// Get all clients
			$this->clients = Client::all();

			//$this->render();	
			$this->render();

		}

		public function show()
		{

			
		}

		public function add()
		{
			$this->client = new Client();
		}

		public function create()
		{

			$form = ($this->request->requestParams->getArray("Client"));
			$client = new Client($form);
			$client->save();

			$this->render(array("json" => $client));


			$this->render(array("nothing" => true));

		}

		public function edit()
		{
			$this->render("add");
		}



	}


?>