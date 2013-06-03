<?php

	namespace Application\Models;

	use \ChickenWire\Util\Str;

	class Client extends \ChickenWire\Model
	{

		static $has_many = array('projects');


		protected function get_idSlug()
		{

			return $this->read_attribute('id') . "-" . Str::slugify($this->read_attribute('name'));

		}


	}


?>