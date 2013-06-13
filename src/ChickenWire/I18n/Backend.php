<?php

	namespace ChickenWire\I18n;

	abstract class Backend
	{

		abstract function translate($locale, $key, $options = array());

		abstract function localize($locale, $object, $format = null, $options = array());


	}



?>