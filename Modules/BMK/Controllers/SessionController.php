<?php

	namespace BMK\Controllers;

	use \BMK\Models\BMKUser;
	use \ChickenWire\Auth\Auth;

	class SessionController extends \ChickenWire\Controller
	{

		public function login()
		{

			/*$ruben = BMKUser::find(1);
			$ruben->resaltPassword("1395.nl");
			$ruben->Save();*/

			$auth = Auth::get("BMK");
			$loginResult = $auth->login("ruben.seyferth", "1395.nl");
			echo ('INGELOGD.');

		}



	}

?>