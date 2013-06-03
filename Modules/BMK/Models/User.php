<?php

	namespace BMK\Models;

	use \ChickenWire\Auth\Auth;

	class User extends \ChickenWire\Model
	{

		static $table_name = 'bmkusers';

		static $authModel = true;


	}


?>