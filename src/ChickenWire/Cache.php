<?php

	namespace ChickenWire;

	class Cache {

		protected static $memcache = null;

		public static function initialize() {

			// Already done?
			if (!is_null(static::$memcache)) return;
			
			// Get config
			$config = Application::getConfiguration();

			// Connect to memcache
			static::$memcache = new \Memcache;
			static::$memcache->connect($config->memcacheHost, $config->memcachePort);

		}

		public static function get($key, $defaultValue) {

			static::initialize();

			// Get the key
			$value = static::$memcache->get($key);
			return $value;

		}

		public static function set($key, $value, $expire = 0)  {

			static::initialize();

			// Get the key
			return static::$memcache->set($key, $value, null, $expire);

		}





	}