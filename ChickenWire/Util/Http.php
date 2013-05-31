<?php

	namespace ChickenWire\Util;

	/**
	 * HTTP Helper class
	 *
	 * This class has some shortcuts and enums to help with handling
	 * HTTP requests.
	 *
	 * @package ChickenWire
	 */
	class Http 
	{

		/**
		 * A list of HTTP status codes and their default text messages.
		 * @var array
		 */
		public static $statusCodes = array(
			200 => "OK",
			201 => "Created",
			202 => "Accepted",
			203 => "Non-Authoritative Information",
			204 => "No Content",
			205 => "Reset Content",
			206 => "Partial Content",

			300 => "Multiple Choices",
			301 => "Moved Permanently",
			302 => "Moved Temporarily",
			304 => "Not Modified",
			305 => "Use Proxy",
			307 => "Temporary Redirect",

			400 => "Bad Request",
			401 => "Unauthorized",
			402 => "Payment Required",
			403 => "Forbidden",
			404 => "Not Found",
			405 => "Method Not Allowed",
			406 => "Not Acceptable",
			408 => "Request Timeout",
			409 => "Conflict",
			410 => "Gone",
			411 => "Length Required",
			412 => "Precondition Failed",
			
			500 => "Internal Server Error", 
			501 => "Not Implemented",
			502 => "Bad Gateway",
			503 => "Service Unavailable",
			504 => "Gateway Timeout"
		);

		/**
		 * Send a HTTP status header
		 * @param  int $code        The HTTP status code
		 * @param  string $httpVersion (default: null) The HTTP version to use in the header. If you leave this null, the version from the request will be used.
		 * @return void
		 */
		public static function sendStatus($code, $httpVersion = null)
		{

			// Check http version
			if (is_null($httpVersion)) $httpVersion = $_SERVER['SERVER_PROTOCOL'];

			// Get code text
			$codeText = self::$statusCodes[$code];

			// Send the header
			header($httpVersion . ' ' . $code . ' ' . $codeText);

		}

		/**
		 * Redirect the client to given Uri
		 * @param  string $uri         The Uri to redirect to
		 * @return void
		 */
		public static function redirect($uri) {

			// Send location
			header('Location: ' . $uri);

		}


	}


?>