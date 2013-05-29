<?php

	namespace ChickenWire\Auth;

	/**
	 * Authentication result
	 *
	 * The AuthResult class is used as a return value for authentication
	 * validation in Auth. 
	 *
	 * @see  \ChickenWire\Auth\Auth
	 *
	 * @package ChickenWire
	 */
	


	class AuthResult
	{

		const SUCCESS = "SUCCESS";
		const USER_NOT_FOUND = "USER_NOT_FOUND";
		const INCORRECT_PASSWORD = "INCORRECT_PASSWORD";
		const ACCOUNT_SUSPENDED = "ACCOUNT_SUSPENDED";

		private static $_messages = array(
			self::SUCCESS => "Authentication was successful.",
			self::USER_NOT_FOUND => "The user could not be found.",
			self::INCORRECT_PASSWORD => "The given password was incorrect.",
			self::ACCOUNT_SUSPENDED => "The account is suspended."
 		);

		/**
		 * Boolean value indicating authentication success
		 * @var boolean
		 */
		public $success;

		/**
		 * Result code (one of the constants)
		 * @var string
		 */
		public $result;

		/**
		 * Human-readable result
		 * @var string
		 */
		public $message;

		/**
		 * The Model instance for the authenticated user. (Only present on success)
		 * @var \ChickenWire\Model
		 */
		public $user;

		/**
		 * Create a new Authentication Result
		 * 
		 * @param string $result  Result code (one of the constants)
		 * @param \ChickenWire\Model $user    (default: null) The authenticated user's Model instance 
		 * @param string $message (default: "") When empty, a default message will be used for the result.
		 */
		public function __construct($result, \ChickenWire\Model $user = null, $message = "")
		{

			$this->result = $result;
			$this->user = $user;
			$this->message = empty($message) ? self::$_messages[$result] : $message;
			$this->success = $result == self::SUCCESS;

		}



	}


?>