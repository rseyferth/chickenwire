<?php

	namespace ChickenWire\I18n;

	class SimpleBackend extends Backend
	{

		protected $_loadPaths;


		protected $_translations;


		protected $_files;

		public function __construct()
		{

			// Empty to start with
			$this->_loadPaths = array();
			$this->_files = array();
			$this->_translations = array();

		}

		public function translations($prefix = '')
		{

			// Any prefix?
			if (strlen($prefix) == 0) return $this->_translations;

			// Trim it.
			$prefix = rtrim($prefix, ' .');

			// Do the lookup
			$keys = $this->_lookup($prefix);
			
			// Anything?
			if ($keys === false) return false;

			// Prepend the array with the prefix again
			$parts = explode('.', $prefix);
			for ($q = count($parts) - 1; $q >= 0; $q--) {
				$keys = array($parts[$q] => $keys);
			}

			// Done
			return $keys;

		}

		public function translate($locale, $key, $options = array())
		{

			// Do the lookup
			$result = $this->_lookup($locale . "." . $key);

			// Already found?
			if ($result !== false) return $this->_processValue($result, $options);

			// Still files to load?
			if (sizeof($this->_files)) {

				// Loop through it to see if map matches
				foreach ($this->_files as $section => $file) {
					
					// Does the pattern match?
					if ($section == $locale . '.' . substr($key, 0, strlen($section) - strlen($locale) - 1)) {
						
						// Load that now
						$this->_loadFile($file);
						unset($this->_files[$section]);

						// Now can it be found?
						$result = $this->_lookup($locale . "." . $key);
						if ($result !== false) return $this->_processValue($result, $options);

					}

				}

			}

			// Still files to load?
			if (sizeof($this->_files)) {

				// Loop through it to see if it at least matches the language
				foreach ($this->_files as $section => $file) {
					
					// Does the pattern match?
					if (substr($section, 0, strlen($locale)) == $locale) {
						
						// Load that now
						$this->_loadFile($file);
						unset($this->_files[$section]);

						// Now can it be found?
						$result = $this->_lookup($locale . "." . $key);
						if ($result !== false) return $this->_processValue($result, $options);

					}

				}

			}

			// Still files to load?
			if (sizeof($this->_files)) {

				// Then just load anything until we found the key...
				foreach ($this->_files as $section => $file) {
					
					// Load that now
					$this->_loadFile($file);
					unset($this->_files[$section]);

					// Now can it be found?
					$result = $this->_lookup($locale . "." . $key);
					if ($result !== false) return $this->_processValue($result, $options);

				}

			}

			// Nothing :(
			return "missing translation: $locale.$key";

		}

		protected function _lookup($key)
		{

			// Look it up.
			$result = \ChickenTools\Arry::traverseKeys($this->_translations, $key);
			return $result ? $result : false;

		}

		protected function _loadFile($filename)
		{

			// Load the yaml file
			$yaml = \ChickenTools\Yaml::load($filename);

			// Merge it
			$this->_translations = array_merge_recursive($yaml, $this->_translations);

		}

		public function localize($locale, $object, $format = null, $options = array())
		{

			return 'lokaal';

		}

		public function addLoadPath($path)
		{

			// Was it an absolute path?
			if ($path[0] != '/') {

				// Inside a module?
				$module = \ChickenWire\Module::getConfiguringModule();
				if ($module === false) {

					// Use app as prefix
					$path = APP_PATH . '/' . $path;

				} else {

					// Use module as prefix
					$path = $module->path . '/' . $path;

				}

			}

			// Trim it.
			$path = rtrim($path, '/ ');

			// Add to load paths
			$this->_loadPaths[] = $path;

			// Read the directory for .yml files
			$dir = \ChickenTools\File::scanDir($path, '/^.*\.(yaml|yml)$/', true, false);
			foreach($dir as $file) {

				// Convert filename to section
				$section = str_replace('/', '.', substr($file, 0, strrpos($file, '.')));

				// Language in there?
				if (preg_match('/\.(?<locale>[a-z]+)$/', $section, $matches)) {

					// Put locale in the front instead (to match the yaml format)
					$section = $matches['locale'] . '.' . substr($section, 0, -1 - strlen($matches['locale']));

				}

				// Make a map
				$this->_files[$section] = $path . '/' . $file;

			}

		}

		/**
		 * Load all locale files 
		 * @return void
		 */
		public function loadAll($prefix = '')
		{

			// Loop through files
			foreach ($this->_files as $section => $file) {

				// Load it?
				if (strlen($prefix) == 0 || 
					$prefix == $section ||
					(strlen($section) > strlen($prefix) && substr($section, 0, strlen($prefix)) == $prefix) ||
					(strlen($section) < strlen($prefix) && substr($prefix, 0, strlen($section)) == $section)) {
				
					// Load file
					$this->_loadFile($file);

					// Remove from queue
					unset($this->_files[$section]);

				}

			}

		}


	}

?>