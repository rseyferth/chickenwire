<?php

	namespace ChickenWire\I18n;

	use \ChickenWire\Core\Mime;
	use \ChickenWire\I18n;

	class I18nController extends \ChickenWire\Controller
	{

		static $respondsTo = array(Mime::JSON, Mime::XML);
		

		public function all()
		{

			// Check my url params
			$prefix = '';
			if ($this->request->urlParams->has("path")) {

				// Get it
				$filter = str_replace('/', '.', $this->request->urlParams->getString("path"));
				$prefix .= $filter;


			}

			
			// Render it
			$this->_showKeys($prefix);

		}

		public function current()
		{

			// Check my url params
			$prefix = I18n::getLocale();
			if ($this->request->urlParams->has("path")) {

				// Get it
				$filter = str_replace('/', '.', $this->request->urlParams->getString("path"));
				$prefix .= "." . $filter;


			}

			// Render it
			$this->_showKeys($prefix);

		}


		private function _showKeys($prefix)
		{

			// Get keys
			$i18n = I18n::getAll($prefix);

			// Nothing there?
			if ($i18n === false) {
				$this->show404();
				return;
			}

			$this->respondTo(array(
				Mime::JSON => array("json" => $i18n),
				Mime::XML => array("xml" => $i18n)
			));


		}

	}

?>