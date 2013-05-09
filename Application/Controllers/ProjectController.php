<?php

	namespace Application\Controllers;

	use Application\Models\Client;
	use Application\Models\Project;

	class ProjectController extends \ChickenWire\Controller
	{

		public function index()
		{

			var_dump($this->client->projects);

		}

		public function add()
		{

			$project = new Project();
			$project->name = "Testproject";
			$project->client_id = $this->client->id;
			$project->save();

		}

		public function show()
		{

			var_dump($this->project->client->name);

		}


	}



?>