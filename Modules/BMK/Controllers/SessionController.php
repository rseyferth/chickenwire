<?php

	namespace BMK\Controllers;

	use \BMK\Models\BMKUser;
	use \ChickenWire\Auth\Auth;

	use \ChickenWire\Form\Form;

	class SessionController extends \ChickenWire\Controller
	{

		static $requiresAuth = array("BMK",
			"only" => "delete"
		);

		public function add()
		{

			// Create the form... (maybe we should create a view? :) 
			$form = Auth::get("BMK")->createLoginForm();

			$form->textField("username");
			$form->passwordField("password");

			$form->submitButton(array(
				"value" => "Login"
			));

			echo $form->render();

			$this->render(array("nothing" => true));

		}

		public function create()
		{

			// Validate
			$auth = Auth::get("BMK");
			$result = $auth->login(
				$this->params->getString("username"),
				$this->params->getString("password")
			);

			// Success?
			if ($result->success) {

				// Redirect to last page.
				$this->redirectTo($auth->lastPage);

			} else {
				
				var_dump($result);
				$this->add();

			}
			

		}

		public function delete()
		{

			// Destroy my session.
			$this->auth->logout();
			echo ('Logged out.');

		}



	}

?>