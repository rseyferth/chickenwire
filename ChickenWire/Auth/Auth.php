<?php

	namespace ChickenWire\Auth;

	use \ChickenWire\Util\Str;

	/**
	 * Authentication class for user authentication
	 *
	 * This class is used for user authentication. In your configuration files you can
	 * add the Auth objects to the framework through Auth::add(). Then in your controller(s)
	 * you set the static $requiresAuth variable. Lastly you'll configure your user Model.
	 *
	 * <h3>Logging in and out</h3>
	 * To create or destroy a session you can use the <b>login</b> and <b>logout</b> methods, 
	 * respectively. 
	 *
	 * You might have a session controller that looked like:
	 *
	 * <code>
	 * class SessionController extends \ChickenWire\Controller
	 * {
	 * 	static $requiresAuth = array("Admin",
	 * 		"only" => "delete"
	 * 	);
	 * 	
	 * 	public function add()
	 * 	{
	 * 		//... Render your login form
	 * 	}
	 * 	public function create()
	 * 	{
	 * 		$auth = \ChickenWire\Auth\Auth::get("Admin");
	 * 		$result = $auth->login($this->params->username, $this->params->password);
	 * 		
	 * 		if ($result->success) {
	 * 			$this->redirect('/admin/', array(
	 * 				'flash' => 'Welcome ' . $result->user->name
	 * 			));
	 * 		} else {
	 * 			//... Show login form again.
	 * 		}
	 * 
	 * 	}
	 * 	public function delete()
	 * 	{
	 * 		$this->auth->logout();
	 * 	}
	 * }</code>
	 *
	 * 
	 * <h3>Configuration</h3>
	 * <h4>Auth configuration</h4>
	 * Configuration example (in your Application/Config/ directory):
	 * <code>
	 * Auth::add("<b>Admin</b>", array(
	 * 	"model" => "\Application\Models\User",
	 * 	"type" => <b>Auth::BLOWFISH</b>,
	 * 	"rotateSalt" => true,
	 * 	"loginAction" => "\Application\Controllers\SessionController::add"
	 * ));	
	 * </code>
	 *
	 * For all options see the constructor.
	 *
	 * <h4>Configure your controller</h4>
	 * To enable authentication for a controller, you can use the configurator $requiresAuth:
	 * <code>
	 * class MyController extends \ChickenWire\Controller
	 * {
	 * 	static $requiresAuth = "<b>Admin</b>";
	 *
	 * 	[...]
	 * </code>
	 * For more information on configuring your controllers (specific methods, etc.), see Controller::$requiresAuth.
	 *
	 * <h4>Create model for Blowfish</h4>
	 * If you choose Auth::BLOWFISH authentication, your Model's table needs to have the following columns: 
	 * 
	 * <ul>
	 * <li><b>username</b> (varchar)</li>
	 * <li><b>password</b> (varchar)</li>
	 * <li><b>salt</b> (varchar)</li>
	 * <li><b>lastlogin_at</b> (timestamp)</li>
	 * </ul>
	 * 
	 * Then simply set the configurator to <i>true</i>:
	 * <code>
	 * class User extends \ChickenWire\Model
	 * {
	 * 	static $authModel = <b>Auth::BLOWFISH</b>;
	 * }
	 * </code>
	 * Or, even simpler:
	 * <code>
	 * static $authModel = true;		// Blowfish is the default encryption
	 * </code>
	 * 
	 * <h4>Create model for MD5</h4>
	 * If you choose Auth::MD5 instead, you'll need the following columns:
	 * 
	 * <ul>
	 * <li><b>username</b> (varchar)</li>
	 * <li><b>password</b> (varchar)</li>
	 * <li><b>lastlogin_at</b> (timestamp)</li>
	 * </ul>
	 *
	 * Then configure your model as follows:
	 * <code>
	 * class User extends \ChickenWire\Model
	 * {
	 * 	static $authModel = Auth::MD5;
	 * }
	 * </code>
	 * 
	 * <b>Note: </b> This is a simple authentication, without salt, and generally deemed insecure.
	 * 
	 * 
	 * @see Controller::$requiresAuth
	 * @package ChickenWire
	 * 
	 */

	class Auth extends \ChickenWire\Core\MagicObject
	{

		/**
		 * MD5 authentication 
		 * 
		 * This uses simple one-way hashing, where
		 * the password is stored in the database without a salt, as 
		 * a md5 hash.
		 */
		const MD5 = "MD5";

		/**
		 * Blowfish authentication with salt
		 * 
		 * Salted authentication uses a salt that is stored in the
		 * database next to the encrypted password. This salt is unique
		 * per user and can be changed on each update for added security
		 * The salt is concatenated with the actual password in the hash.
		 * The password will be encrypted using Blowfish.
		 */
		const BLOWFISH = "BLOWFISH";


		protected static $_auths = array();

		protected static $_propRead = array("loginController", "loginAction", "name", "result", "user");

		protected static $_sessionPrefix = "ChickenWireAuthentication";


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
		protected $_saltField;
		protected $_lastloginField;
		protected $_loginController;
		protected $_loginAction;
		protected $_rotateSalt;

		protected $_reflModel;

		protected $_result;
		protected $_authenticated;
		protected $_user;

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
		 * <li><b>saltField</b>	(default: salt) The name of the field in the model that contains the salt.
		 * <li><b>lastloginField</b> (default: lastlogin_at) The name of the field in the model that contains the timestamp of the last login. If this field is not found, the last login time will not be stored.
		 * </ul>
		 *
		 */
		public function __construct($name, array $options) {

			// Default options
			$options = array_merge(array(
				"type" => self::BLOWFISH,
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

			// Login action propertly setup?
			$regEx = '/^\\\([^:]+)::([a-zA-Z]+)$/';
			if (!preg_match($regEx, $options['loginAction'])) {
				throw new \Exception("The loginAction was not properly formatted. Please use this format: \\Namespace\\ControllerName::methodName.", 1);				
			}
			preg_match_all($regEx, $options['loginAction'], $matches);
			
			// Store action
			$this->_loginController = '\\' . $matches[1][0];
			$this->_loginAction = $matches[2][0];

			// Store locally
			$this->_name = $name;
			$this->_model = $options['model'];
			$this->_type = $options['type'];
			$this->_rotateSalt = $options['rotateSalt'];
			$this->_usernameField = $options['usernameField'];
			$this->_passwordField = $options['passwordField'];
			$this->_saltField = $options['saltField'];
			$this->_lastloginField = $options['lastloginField'];

			// No login yet!
			$this->_authenticated = false;
			$this->_result = null;

		}

		/**
		 * Validate the given username and password and store
		 * the session is authentication is successful.
		 * 
		 * @param  string $username The username for the user to validate.
		 * @param  string $password The unencrypted password for the user to validate.
		 * @return \ChickenWire\Auth\AuthResult           Result object containing information of authentication
		 */
		public function login($username, $password)
		{

			// Get result
			$result = $this->validate($username, $password);

			// Success?
			$this->_result = $result;
			if ($result->success) {

				// Store it in the session
				$storeObject = array(
					'username' => $username,
					'password' => $result->user->read_attribute($this->_passwordField)
				);
				$_SESSION[self::$_sessionPrefix . $this->name] = $storeObject;

				// Store local vars
				$this->_authenticated = true;
				$this->_user = $result->user;

			}

			// Done.
			return $result;
			
		}

		/**
		 * Log out current user
		 * @return boolean Wheter logout was successful
		 */
		public function logout()
		{

			// Am I authenticated?
			if ($this->isAuthenticated() == false) {

				// Cannot logout!
				return false;

			}

			// Destroy my session
			$this->_authenticated = false;
			unset($_SESSION[self::$_sessionPrefix . $this->name]);
			$this->_user = null;
			return true;

		}

		/**
		 * Validate the given username and password.
		 * 
		 * @param  string $username The username for the user to validate.
		 * @param  string $password The unencrypted password for the user to validate.
		 * @return \ChickenWire\Auth\AuthResult           Whether user validation was successful.
		 */
		public function validate($username, $password)
		{

			// Store original password
			$originalPassword = $password;

			// Init model
			$this->_initModel();

			// Find user
			$modelName = $this->_model;
			$user = $modelName::find("first", array($this->_usernameField => $username));

			// Anything?
			if (is_null($user)) {
				return new AuthResult(AuthResult::USER_NOT_FOUND);
			}



			// What type of validation to do?
			switch ($this->_type) {
				case self::MD5:
					$password = md5($password);
					break;
				
				case self::BLOWFISH:
					$password = crypt($user->read_attribute($this->_saltField) . $password, $user->read_attribute($this->_passwordField));
					break;

				default:
					throw new \Exception("Unknown encryption: " . $this->_type, 1);
					break;
			}

			// Validate passwords
			$userPass = $user->read_attribute($this->_passwordField);

			// Same?
			if ($password != $userPass) {
			
				// Nope.
				return new AuthResult(AuthResult::INCORRECT_PASSWORD);

			} else {

				// Rotate the salt?
				if ($this->_rotateSalt && $this->_type == self::BLOWFISH) {

					// Now's the moment to re-encrypt the password, so we can store the newly encrypted password in the session
					$user->resaltPassword($originalPassword);
					$user->Save();

				}

				// All ok!
				return new AuthResult(AuthResult::SUCCESS, $user);

			}


		}

		/**
		 * Check if the current session is authenticated for this Auth instance
		 * @return boolean True if authenticated, false when not authenticated.
		 */
		public function isAuthenticated()
		{

			// Already authenticated?
			if ($this->_authenticated) return true;

			// Check session
			if (array_key_exists(self::$_sessionPrefix . $this->_name, $_SESSION)) {

				// Get credentials
				$cred = $_SESSION[self::$_sessionPrefix . $this->_name];
				
				// Init model
				$this->_initModel();

				// Find user
				$modelName = $this->_model;
				$user = $modelName::find("first", array(
							$this->_usernameField => $cred['username'],
							$this->_passwordField => $cred['password']
						));

				// Anything?
				if (is_null($user)) {
					return false;
				}

				// Set result
				$this->_result = new AuthResult(AuthResult::SUCCESS, $user);
				$this->_authenticated = true;
				$this->_user = $user;
				return true;

			}

			// Not authenticated.
			return false;

		}


		/**
		 * Initialize the model class (reflection)
		 * @return void
		 */
		protected function _initModel()
		{

			// Refl model known?
			if (isset($this->_reflModel)) {
				return;
			}

			// Real thing?
			if (!class_exists($this->_model)) {
				throw new \Exception("The model '$this->_model' could not be found.", 1);				
			}

			// Get the reflection			
			$this->_reflModel = new \ReflectionClass($this->_model);

			// Is it a ChickenWire model?
			if (!$this->_reflModel->isSubClassOf("\\ChickenWire\\Model")) {
				throw new \Exception("To use ChickenWire Authentication your connected Model needs to be a ChickenWire Model.", 1);
			}
				

		}








	}



?>