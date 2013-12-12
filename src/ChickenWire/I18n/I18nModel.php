<?php

	namespace ChickenWire\I18n;
	

	class I18nModel extends \ChickenWire\Model
	{


		static $i18nColumnSuffix = "_i18n";

		private static $_i18nInitialized = [];


		


		/**
		 * Retrieve the Table instance for this Model
		 * @return \ActiveRecord\Table
		 */
		static function table()
		{

			// Do the basics
			$table = parent::table();

			// Check if this class was already initialized for ChickenWire
			$c = get_called_class();
			if (!in_array($c, self::$_i18nInitialized)) {
			
				// Initialize 
				static::_initializeI18n($table);				
				array_push(self::$_i18nInitialized, $c);
				
			}


			// Done.
			return $table;

		}

		private static function _initializeI18n($table)
		{

			// Add a before save i18n thing
			$table->callback->register("beforeSave", function (\ActiveRecord\Model $model) { 
				$model->_storeI18nValues();
			});


		}



		public function &__get($name)
		{

			// Is there a field with the prefix added?
			if (parent::hasAttribute($name . static::$i18nColumnSuffix)) {

				// Get the i18n value
				$i18n = $this->_getI18nArray($name);
				return $i18n;

			} else {
				return parent::__get($name);
			}


		}

		public function __set($name, $value)
		{


			// Is there a field with the prefix added?
			if (parent::hasAttribute($name . static::$i18nColumnSuffix)) {

				// Get the i18n value
				$i18n = $this->_getI18nArray($name);
			
				// Value is an array or a single item?
				if (is_array($value)) {

					// Loop through locales
					foreach($value as $loc => $v) {
						if (!array_key_exists($loc, $i18n)) {

							// INVALID LOCALE
							throw new \Exception("Cannot set value for unknown locale '$loc'.", 1);							
							continue;
						}

						// Apply
						$i18n[$loc] = $v;

					}

				} else {
					// Use that value for the current locale
					$i18n[\ChickenWire\I18n::getLocale()] = $value;

				}

				// Store it
				$this->i18nValues[$name] = $i18n;


			} else {
				return parent::__set($name, $value);
			}

		}




		private $i18nValues;

		private function &_getI18nArray($name) {

			// Array inited?
			if (is_null($this->i18nValues)) $this->i18nValues = [];

			// My value initialized?
			if (array_key_exists($name, $this->i18nValues)) {

				// Already parsed. Return as is.
				return $this->i18nValues[$name];

			}

			// Create empty locale array
			$i18n = new I18nValue();			
			
			// Parse data
			$value = $this->readAttribute($name . static::$i18nColumnSuffix);
			if (null !== ($values = json_decode($value))) {
				foreach ($values as $loc => $v) {
					$i18n[$loc] = $v;
				}
			}


			// Store
			$this->i18nValues[$name] = $i18n;

			return $this->i18nValues[$name];


		}

		private function _storeI18nValues() {

			// Loop through i18n values
			foreach ($this->i18nValues as $key => $value)  {
				$this->{$key . static::$i18nColumnSuffix} = json_encode($value);
			}

		}

		public function hasAttribute($name)
		{

			// Check parent
			if (parent::hasAttribute($name) === true) return true;

			// Now check i18n columns
			if (parent::hasAttribute($name . static::$i18nColumnSuffix)) {
				return true;
			}

			// Nope.
			return false;
			

		}

	


	}

?>