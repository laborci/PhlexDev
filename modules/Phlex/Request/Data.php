<?php namespace Phlex\Request;

use Phlex\Tool\StringTool;

class Data extends \ArrayObject {
		
	const MATCH_GLOB = 0;
	const MATCH_REGEX = 1;

	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ACCESSOR METHODS ARE RETURNING NULL IF THE OFFSET DOES NOT EXIST AND FALSE IF THE SANITIZER/UNSERIALIZER FAILS [FALSE RESULT CAN BE AMBIGUOUS!!!] //
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	function __construct($array = null) {
		parent::__construct(is_array($array) || is_object($array)?$array:array());
	}
	
	function getValues() { return parent::getArrayCopy(); }
	
	function getSubsetByKeyPattern($keyGlobPattern) {
		return new static($this->arraySubsetByKeyPattern($keyGlobPattern, $this->getValues()));
	}
	
	function getSubsetByPrefix($prefix, $preservePrefixInKeys = true) {
		return new static($this->arraySubsetByPrefix($prefix, $this->getValues(), $preservePrefixInKeys));
	}
	
	function getValidated($key, $validator, $options = null) {
		if (!$this->has($key)) return null;
		return filter_var($this->getKey($key), $validator, $options);
	}
	
	function has($key) { return $this->offsetExists($key); }
	function getKey($key) { return parent::offsetGet($key); }
	
	function getAsBoolean($key) { return $this->has($key)?StringTool::parseBool($this->getKey($key)):null; }
	function getId($key) { return $this->getInt($key, 1); }
	function getJSONDecoded($key, $assoc = true) {
		if (!$this->has($key)) return null;
		$struct = json_decode($this->getKey($key), $assoc);
		return $struct !== null?$struct:false;
	}


	function getMatched($key, $pattern, $patternType = Data::MATCH_REGEX, $globFlags = 0) { return $patternType === static::MATCH_REGEX?$this->getRegexMatched($key, $pattern):$this->getGlobMatched($key, $pattern, $globFlags); }
	function getRegexMatched($key, $pattern) { return $this->has($key)?(preg_match($pattern, $this->getKey($key))?$this->getKey($key):false):null; }
	function getGlobMatched($key, $pattern, $globFlags = 0) { return $this->has($key)?(fnmatch($pattern, $this->getKey($key), $globFlags)?$this->getKey($key):false):null; }
	
	function getBoolean($key, $flag = null) { return $this->getValidated($key, FILTER_VALIDATE_BOOLEAN, $flag); }
	function getEmail($key) { return $this->getValidated($key, FILTER_VALIDATE_EMAIL); }
	function getFloat($key, $flag = null) { return $this->getValidated($key, FILTER_VALIDATE_FLOAT, $flag); }
	function getInt($key, $minRange = null, $maxRange = null) {
		$options = array('options' => array());
		if ($minRange) $options['options']['min_range'] = $minRange;
		if ($maxRange) $options['options']['max_range'] = $maxRange;
		if (!$options['options']) $options = null;
		return $this->getValidated($key, FILTER_VALIDATE_INT, $options);
	}
	function getIP($key, $flag = null) { return $this->getValidated($key, FILTER_VALIDATE_IP, $flag); }
	function getMAC($key) { return $this->getValidated($key, FILTER_VALIDATE_MAC); }
	function getRegExp($key) { return $this->getValidated($key, FILTER_VALIDATE_REGEXP); }
	function getURL($key, $flag = null) { return $this->getValidated($key, FILTER_VALIDATE_URL, $flag); }
	function getDate($key) { return $this->has($key)?($this->isValidDateString($this->getKey($key))?$this->getKey($key):false):null; }
	function getDateTime($key) { return $this->has($key)?($this->isValidDateTimeString($this->getKey($key))?$this->getKey($key):false):null; }
	
	function getEmailized($key) { return $this->getValidated($key, FILTER_SANITIZE_EMAIL); }
	function getEncoded($key, $flag = null) { return $this->getValidated($key, FILTER_SANITIZE_ENCODED, $flag); }
	function getWithoutFullSpecialChars($key, $flag = null) { return $this->getValidated($key, FILTER_SANITIZE_FULL_SPECIAL_CHARS, $flag); }
	function getWithSlashes($key) { return $this->getValidated($key, FILTER_SANITIZE_MAGIC_QUOTES); }
	function getParsedAsFloat($key, $flag = null) { return $this->getValidated($key, FILTER_SANITIZE_NUMBER_FLOAT, $flag); }
	function getParsedAsInt($key) { return $this->getValidated($key, FILTER_SANITIZE_NUMBER_INT); }
	function getWithoutSpecialChars($key, $flag = null) { return $this->getValidated($key, FILTER_SANITIZE_SPECIAL_CHARS, $flag); }
	function getAsPlainString($key, $flag = null) { return $this->getValidated($key, FILTER_SANITIZE_STRING, $flag); }
	function getStripped($key, $trim = false) {
		$result = $this->getValidated($key, FILTER_SANITIZE_STRIPPED);
		return $result && $trim?trim($result):$result;
	}
	function getTrimmed($key) { return $this->has($key)?trim($this->getKey($key)):null; }
	function getParsedAsURL($key) { return $this->getValidated($key, FILTER_SANITIZE_URL); }
	
 	function getEnum($key, $allowedValueArrayOrValue1, $value2 = null) {
		$value = $this->getKey($key);
		if (!$value && $value !== 0) return null;
		
		if (is_array($allowedValueArrayOrValue1)) {
			$params = func_get_args();
			$params = array_splice($params, 2);
			$params = array_merge($allowedValueArrayOrValue1, $params);
		} else {
			$params = func_get_args();
			$params = array_splice($params, 1);
		}
		
		return in_array($value, $params)?$value:false;
	}
	
	function offsetGet($key){
		$val =$this->getKey($key);
		if(is_array($val)) return Data($val);
		return $this->getStripped($key, true);
	}



	protected function isValidDateTimeString($strToCheck) {
		if (!is_string($strToCheck) || !preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}(:\d{2})?$/', $strToCheck)) return false;
		return !preg_match('/^0000-00-00\s+00:00(:00)?$/', $strToCheck);
	}

	protected function isValidDateString($strToCheck) {
		if (!is_string($strToCheck) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $strToCheck)) return false;
		return !preg_match('/^0000-00-00$/', $strToCheck);
	}

	protected function arraySubsetByKeyPattern($keyGlobPattern, $array) {
		$result = array();
		if ($array) foreach ($array as $key => $value) if (fnmatch($keyGlobPattern, $key)) $result[$key] = $value;
		return $result;
	}

	protected function arraySubsetByPrefix($prefix, $array, $preservePrefixInKeys = true) {
		$result = array();
		if ($array) foreach ($array as $key => $value) if (strpos($key, $prefix) === 0) $result[$preservePrefixInKeys?$key:substr($key, mb_strlen($prefix))] = $value;
		return $result;
	}
}
