<?php namespace Phlex\Kraft\Parser;

abstract class Parser {

	protected $fileName;
	protected $errorPrefix;

	public function parseSource($source, $file = '') {
		$this->fileName = $file;

		return $this->parse($source);
	}

	protected function error($message) {
		$message = $this->errorPrefix . ' - ' . $message . ' in ' . $this->fileName . "\n";
		trigger_error($message, E_USER_WARNING);
	}

	protected function parseVar($var) {
		$firstLetter = substr($var, 0, 1);
		if ($firstLetter == '.') {
			$parts = explode('.', substr($var, 1));
			$index = '';
			foreach ($parts as $part) $index .= "['" . $part . "']";
			return '$this' . $index;
		}
		return $var;
	}

	protected function parseKraftAttribute($key, $value) {
		if (substr($key, -4) == ':php') {
			$key = substr($key, 0, -4);
			$value = $value;
		} elseif (substr($key, -7) == ':string') {
			$key = substr($key, 0, -7);
			$value = '"' . $value . '"';
		} elseif ($value[0] == '.') {
			$value = $this->parseVar($value);
		} elseif ($value[0] == '$') {
			$value = $value;
		} else {
			$value = '"' . $value . '"';
		}

		return array('key' => $key, 'value' => $value);
	}

	protected function parseAttributesString($string) {
		$pattern = "/\s*([\w\d-_:]+)\s*=\s*\"((?:[^\"\\\\]|\\\\.)*)\"/msi";
		$num_of_attrs = preg_match_all($pattern, $string, $matches);
		$attributes = array();
		if ($num_of_attrs) $attributes = array_combine($matches[1], $matches[2]);

		for ($i = 0; $i < $num_of_attrs; $i++) $string = str_replace($matches[0][$i], '', $string);
		$pattern = '/[\w\d-_:]+/';
		preg_match_all($pattern, $string, $matches);
		foreach ($matches[0] as $simple) if (!isset($attributes[$simple])) $attributes[$simple] = true;

		return $attributes;
	}


	protected function findTagsByNameSpace($namespace, $source) {
		$pattern = '(\<(/?)(' . $namespace . '):([\w\d-_.:]+)((\s+[\w\d-_:]+(\s*=\s*"((\\"|.)*?)")*)*)\s*(/?)\>)mi';
		// 0 - full
		// 1 - closermarker
		// 2 - namespace
		// 3 - tag
		// 4 - attributes
		// 9 - singlemarker
		$num_of_tags = preg_match_all($pattern, $source, $matches);
		$tags = array();
		for ($i = 0; $i < $num_of_tags; $i++) {
			$type = 'begin';
			if($matches[9][$i]) $type = 'single';
			if($matches[1][$i]) $type = 'close';

			array_push($tags, new Tag(
				$matches[2][$i],
				$matches[3][$i],
				$type,
				$this->parseAttributesString($matches[4][$i]),
				$matches[0][$i]));
		}

		return $tags;
	}

	protected abstract function parse($source);

}