<?php

	namespace ChickenWire\Util;

	class Arry
	{

		public static function mergeStatic($className, $propName, $recursiveMerge = true)
		{

			// Collection of arrays
			$arrays = array();

			// Find the class
			$reflClass = new \ReflectionClass($className);
			
			// Then loop parents
			do {
				$arrays[] = $reflClass->getStaticPropertyValue($propName, array());
			} while (false !== ($reflClass = $reflClass->getParentClass()));

			// Merge it!
			$arrays = array_reverse($arrays, true);
			if ($recursiveMerge) {
				$merged = call_user_func_array(array(__CLASS__, "mergeRecursiveDistinct"), $arrays);	
			} else {
				$merged = call_user_func_array("array_merge", $arrays);	
			}
			return $merged;

		}

		public static function Contains($needle, array $haystack, $caseSensitive = true)
		{

			// Case sensitive?
			if ($caseSensitive) {
				return in_array($needle, $haystack);
			}

			// Loop through it
			foreach ($haystack as $value) {
				if (strtolower($needle) == strtolower($value)) {
					return true;
				}
			}
			return false;

		}


		public static function &mergeRecursiveDistinct()
		{
			$aArrays = func_get_args();
			$aMerged = $aArrays[0];
	
			for($i = 1; $i < count($aArrays); $i++)
			{
				if (is_array($aArrays[$i]))
				{
					foreach ($aArrays[$i] as $key => $val)
					{
						if (is_array($aArrays[$i][$key]))
						{
							$aMerged[$key] = is_array($aMerged[$key]) ? self::mergeRecursiveDistinct($aMerged[$key], $aArrays[$i][$key]) : $aArrays[$i][$key];
						}
						else
						{
							$aMerged[$key] = $val;
						}
					}
				}
			}
	
			return $aMerged;
		}

	}






?>