<?php

	namespace ChickenWire\Util;

	class CsrfGuard
	{

		public static function register(&$name, &$token)
		{

			// Read/create CSRF session
			if (!array_key_exists("ChickenWireCsrfGuard", $_SESSION)) {
				$_SESSION['ChickenWireCsrfGuard'] = array("count" => 0);
			}


			// Count up!
			$count = ++$_SESSION['ChickenWireCsrfGuard']['count'];

			// Generate name
			if (is_null($name) || empty($name)) {
				$name = Str::random(12) . '/' . $count;
			}

			// Generate token
			$token = hash("sha512", mt_rand(0, mt_getrandmax()));

			// Add to session
			$_SESSION['ChickenWireCsrfGuard'][$name] = $token;

		}

		
		public static function validate($name, $token) 
		{

			// Anything?
			if (!array_key_exists("ChickenWireCsrfGuard", $_SESSION)) {
				return false;
			}

			// Read the session
			$tokens = $_SESSION['ChickenWireCsrfGuard'];
			if (array_key_exists($name, $tokens)) {

				// Does the token match?
				if ($tokens[$name] != $token) {
					return false;
				}

				// It matches; now remove it from the session
				unset($_SESSION['ChickenWireCsrfGuard'][$name]);
				return true;

			} else {

				// No such name found...
				return false;
			}


		}



	}


?>