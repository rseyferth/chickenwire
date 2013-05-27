<?php

	namespace ChickenWire;

	/**
	 * Authentication class for user authentication
	 *
	 * This class is used for user authentication. In your configuration files you can
	 * add the Auth objects to the framework through Auth::add(). Then in your controller(s)
	 * you set the static $requiresAuth variable. 
	 *
	 * Configuration example:
	 * <code>
	 * Auth::add("BMK", array(
	 * 	"model" => "\BMK\Models\User",
	 * 	"type" => Auth::SALT,
	 * 	"rotateSalt" => true,
	 * 	"loginAction" => "\BMK\Controllers\SessionController::login"
	 * ));	
	 * </code>
	 *
	 * Controller example:
	 * <code>
	 * class MyController extends \ChickenWire\Controller
	 * {
	 * 	static $requiresAuth = "BMK";
	 *
	 * 	[...]
	 * </code>
	 * For more information on configuring your controllers (specific methods, etc.), see Controller::$requiresAuth.
	 * 
	 * @see Controller::$requiresAuth
	 * @package ChickenWire
	 * 
	 */

	class Auth extends Core\MagicObject
	{

		/**
		 * MD5 authentication 
		 * 
		 * This uses simple one-way hashing, where
		 * the password is stored in the database without a salt, as 
		 * a md5 hash.
		 */
		const MD5 = "md5";

		/**
		 * Salted authentication
		 * 
		 * Salted authentication uses a salt that is stored in the
		 * database next to the encrypted password. This salt is unique
		 * per user and can be changed on each update for added security
		 * The salt is concatenated with the actual password in the hash.
		 */
		const SALT = "salt";


		protected static $_auths = array();

		/**
		 * Add a new authentication to the framework
		 * @param string $name    The reference key for this Auth object
		 * @param array  $options Array with options
		 * @see __construct()
		 * @return Auth The newly created Auth.
		 */
		public static function add($name, array $options) 
		{

			// Already known?
			if (array_key_exists($name, self::$_auths)) {
				throw new \Exception("There is already a Auth defined for '$name'.", 1);				
			}

			// Create it
			$auth = new Auth($name, $options);
			self::$_auths[$name] = $auth;
			return $auth;

		}

		/**
		 * Retrieve a previously defined Auth by its name
		 * @param  string $name Name as used when the Auth was configured
		 * @return Auth 	 The Auth object
		 */
		public static function get($name) 
		{
			if (array_key_exists($name, self::$_auths)) {
				return self::$_auths[$name];
			} else {
				throw new \Exception("There is no Auth defined for '$name'.", 1);				
			}
		}

		/**
		 * Get the array of Auth objects defined (by reference)
		 * @return array (Reference to) array containing all Auth objects that were added through Auth::add().
		 */
		public static function all()
		{
			return self::$_auths;
		}


		protected $_name;
		protected $_model;
		protected $_type;
		protected $_usernameField;
		protected $_passwordField;
		protected $_lastloginField;
		protected $_loginAction;
		protected $_rotateSalt;


		/**
		 * Create a new Auth object
		 *
		 * <b>Note:</b> You should not use this constructor directly, but use Auth::add() instead.
		 *
		 * @param string $name    The name of this authentication
		 * @param array  $options Array of options:
		 *
		 * <ul>
		 * <li><b>model</b> The full classname of the model to use for authentication</li>
		 * <li><b>loginAction</b> The controller and method to call when authentication fails. E.g.: \BMK\Controllers\SessionController::login</li>
		 * <li><b>type</b> (default: Auth::SALT) The type of encryption/authentication to use (Auth::MD5 or Auth::SALT).</li>
		 * <li><b>rotateSalt</b> (default: false) Whether to generate a new random salt and re-encrypt the password, each time a user is validated.</li>
		 * <li><b>usernameField</b>	(default: username) The name of the field in the model that contains the username.
		 * <li><b>passwordField</b>	(default: password) The name of the field in the model that contains the (encrypted) password.
		 * <li><b>lastloginField</b> (default: lastlogin_at) The name of the field in the model that contains the timestamp of the last login. If this field is not found, the last login time will not be stored.
		 * </ul>
		 *
		 */
		public function __construct($name, array $options) {

			// Default options
			$options = array_merge(array(
				"type" => self::SALT,
				"model" => null,
				"loginAction" => null,
				"usernameField" => "username",
				"passwordField" => "password",
				"saltField" => "salt",
				"rotateSalt" => false,
				"lastloginField" => "lastlogin_at"
			), $options);

			// No model?
			if (is_null($options['model'])) {
				throw new \Exception("You cannot create an Auth without linking it to a model.", 1);				
			}

			// No login action?
			if (is_null($options['loginAction'])) {
				throw new \Exception("You cannot create an Auth without defining an action for the login form.", 1);
				
			}

			// Store locally
			$this->_name = $name;
			$this->_model = $options['model'];
			$this->_type = $options['type'];
			$this->_rotateSalt = $options['rotateSalt'];
			$this->_loginAction = $options['loginAction'];
			$this->_usernameField = $options['usernameField'];
			$this->_passwordField = $options['passwordField'];
			$this->_lastloginField = $options['lastloginField'];

		}

		/**
		 * Validate the given username and password and store
		 * the session is authentication is successful.
		 * 
		 * @param  string $username The username for the user to validate.
		 * @param  string $password The unencrypted password for the user to validate.
		 * @param  boolean $showLogin (default: true) Whether to redirect the user to the configured login action when authentication fails.
		 * @return boolean           Whether user validation was successful.
		 */
		public function login($username, $password, $showLogin = true)
		{



		}








	}



?>