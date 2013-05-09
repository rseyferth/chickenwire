<?php

	namespace Application\Controllers;

	use Application\Models\Client;

	class ClientController extends \ChickenWire\Controller
	{

		public function index()
		{

			echo ("Index");

		}

		public function add()
		{

			$client = new Client();
			$client->name = "MediaCreatives";
			$client->save();

		}

		public function show()
		{

			var_dump($this->client->name);

			var_dump($this->client->projects);


		}


	}



?>