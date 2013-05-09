<?php

	namespace ChickenWire\Util;

	class Http 
	{

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

		public static function sendStatus($code, $httpVersion = null)
		{

			// Check http version
			if (is_null($httpVersion)) $httpVersion = $_SERVER['SERVER_PROTOCOL'];

			// Get code text
			$codeText = self::$statusCodes[$code];

			// Send the header
			header($httpVersion . ' ' . $code . ' ' . $codeText);

		}


	}


?>