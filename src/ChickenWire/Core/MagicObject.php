<?php

	namespace ChickenWire\Core;

	/**
	 * MagicObject class facilitates easy use of getters and setters.
	 *
	 * By extending MagicObject the class will have a __get and __set
	 * function, that allows properties to be made accessible. You
	 * can define static arrays in your class called $_propRead,
	 * $_propWrite, and $propReadWrite, containing your local variables that 
	 * should be made public. All your local private/protected variables
	 * should be prefixed with an underscore.
	 *
	 * You can also define specific getters and setters through __get_prop
	 * and __set_prop.
	 *
	 * For example:
	 * <code>
	 * class Person extends \ChickenWire\Core\MagicObject
	 * {
	 * 		protected static $_propReadWrite = array('firstname');
	 * 		protected static $_propRead = array('lastname');
	 *
	 * 		protected $_firstname;
	 * 		protected $_lastname;
	 *
	 * 		public function __construct($firstname = '', $lastname = '')
	 * 		{
	 * 			$this->_firstname = $firstname;
	 * 			$this->_lastname = $lastname;
	 * 		}
	 *
	 * 		protected function __get_name()
	 * 		{
	 * 			return $this->_firstname . ' ' . $this->_lastname;
	 * 		}
	 * 		protected function __set_name($value)
	 * 		{
	 * 			list($this->_firstname, $this->_lastname) = preg_split('/\ /', $value);
	 * 		}
	 * 
	 * }
	 *
	 * </code> 
	 *
	 * <code>
	 * $john = new Person("John", "Derringer");
	 * echo $john->name;		// output: John Derringer
	 * 
	 * $john->firstname = "Jane";
	 * echo $john->name;		// output: Jane Derringer
	 * </code>
	 *
	 *
	 * @package ChickenWire
	 */
	abstract class MagicObject
	{

		protected static $_propReadWrite = null;
		protected static $_propRead = null;
		protected static $_propWrite = null;

		/**
		 * @ignore
		 */
		public function __get($prop) {

			// Is there a getter function available?
			if (method_exists($this, '__get_' . $prop)) {
				return call_user_func(array($this, '__get_' . $prop));
			}

			// Check if it is in read/write or read array
			if ((!is_null(static::$_propRead) && in_array($prop, static::$_propRead)) ||
				(!is_null(static::$_propReadWrite) && in_array($prop, static::$_propReadWrite))) {

				// Property exists?
				$propLocal = '_' . $prop;
				if (isset($this->$propLocal)) {
					return $this->$propLocal;
				}

			}


		}

		/**
		 * @ignore
		 */
		public function __set($prop, $value) {

			// Is there a setter function available?
			if (method_exists($this, '__set_' . $prop)) {
				return call_user_method_array('__set_' . $prop, $this, array($value));
			}

			// Check if it is in read/write or write array
			if ((!is_null(static::$_propWrite) && in_array($prop, static::$_propWrite)) ||
				(!is_null(static::$_propReadWrite) && in_array($prop, static::$_propReadWrite))) {

				// Property exists?
				$propLocal = '_' . $prop;
				if (property_exists($this, $propLocal)) {
					$this->$propLocal = $value;					
				}
				return;

			}

			// Just set it
			$this->$prop = $value;

		
		}


	}

?>