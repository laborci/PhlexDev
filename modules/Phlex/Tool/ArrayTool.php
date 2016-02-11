<?php namespace Phlex\Tool;

class ArrayTool extends \ArrayObject {

	public static function isAssoc($array) { return self::isTravesable($array) && array_diff_key($array, array_keys($array)); }
	
	public static function isNumericIndexed($array) { return self::isTravesable($array) && !self::isAssoc($array); }
	
	public static function isTravesable($array) { return is_array($array) || $array instanceof \Traversable; }

	public static function getIndexOf($needle, $haystack, $strict = false, $getAllIndexes = false) {
		$result = $getAllIndexes === false?array_search($needle, $haystack, $strict):array_keys($haystack, $needle, $strict);
		return $result === false?null:$result;
	}
	
	public static function getArrayFromTraversable($traversable) {
		if (is_array($traversable)) return $traversable;
		else {
			$result = array();
			if ($traversable instanceof \Traversable) foreach ($traversable as $k => $value) $result[$k] = $value;
			return $result;
		}
	}
	
	public static function subsetByKeyPattern($keyGlobPattern, $array) {
		$result = array();
		if ($array) foreach ($array as $key => $value) if (fnmatch($keyGlobPattern, $key)) $result[$key] = $value;
		return $result;
	}
	
	public static function subsetByPrefix($prefix, $array, $preservePrefixInKeys = true) {
		$result = array();
		if ($array) foreach ($array as $key => $value) if (strpos($key, $prefix) === 0) $result[$preservePrefixInKeys?$key:substr($key, mb_strlen($prefix))] = $value;
		return $result;
	}

	public static function extractToObject(array $assocArray, &$context, $onlyIfExists = false) {
		if ($assocArray) foreach ($assocArray as $key => $value) {
			if (is_object($context)) {
				if (!$onlyIfExists || property_exists($context, $key) || (is_array($onlyIfExists) && in_array($key, $onlyIfExists))) $context->$key = $value;
			} else if (is_array($context)) {
				if (!$onlyIfExists || array_key_exists($key, $context) || (is_array($onlyIfExists) && in_array($key, $onlyIfExists))) $context[$key] = $value;
			}
		}
	}

	public static function convertToString($data, $pattern) {
		if (!$data) return $data;

		if (preg_match_all('/\{(.+?)\}/', $pattern, $matches)) {
			for ($i = 0; $i < count($matches[0]); $i++) {
				$field = $matches[1][$i];
				$value = is_object($data)?$data->$field:$data[$field];
				$pattern = str_replace($matches[0][$i], $value, $pattern);
			}
		}
		return $pattern;
	}

	public static function convertSetToString($data, $pattern, $keyField = null) {
		$result = array();
		if (is_string($pattern)) {
			foreach ($data as $item) {
				if ($keyField) {
					$key = is_object($item)?$item->$keyField:$item[$keyField];
					$result[$key] = ArrayTool::convertToString($item, $pattern);
				} else $result[] = ArrayTool::convertToString($item, $pattern);
			}
		}
		return $result;
	}

	public static function recursiveDiff($aArray1, $aArray2) {
		$aReturn = array();
		foreach ($aArray1 as $mKey => $mValue) {
			if (array_key_exists($mKey, $aArray2)) {
				if (is_array($mValue)) {
					$aRecursiveDiff = static::recursiveDiff($mValue, $aArray2[$mKey]);
					if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
				} else {
					if ($mValue != $aArray2[$mKey]) {
						$aReturn[$mKey] = $mValue;
					}
				}
			} else {
				$aReturn[$mKey] = $mValue;
			}
		}
		return $aReturn;
	}
	/*
	public static function createTree(array $source, $parentIdKey = 'parentId', $valueKey = 'value', $childrenKey = 'children') {
		$callback = function ($parentId, $parentIdKey, $valueKey) use (&$callback, &$source, &$childrenKey) {
			$result = array();
			foreach ($source as $key => $item) {
				if ($item[$parentIdKey] == $parentId) {
					$children = $callback($key, $parentIdKey, $value);
					if (is_string($item)) $result[$key] = array($valueKey => $item);
					else $result[$key] = $item;
					if ($children) $result[$key][$childrenKey] = $children;
				}
			}
			return $result;
		};
		return $callback(0, $parentIdKey, $valueKey);
	}
	*/
	
	public static function getValuesForKey($array, $key, $unique = true){
		$result = array();
		if($array) {
			foreach ($array as $item) {
				$result[] = is_object($item) && !($item instanceof \ArrayAccess)?$item->$key:$item[$key];
			}
		}
		if($unique) $result = array_unique($result);
		return $result;
	}
	
	public static function mutateValuesOnKey($array, $key, $mutator, $insertationKey = null) {
		if (static::isTravesable($array) && $array) {
			foreach ($array as $i => $struct) {
				$array[$insertationKey !== null?$insertationKey:$i] = call_user_func($mutator, is_object($struct) && !($struct instanceof \ArrayAccess)?$struct->$key:$struct[$key]);
			}
		}
		return $array;
	}
	
}
