<?php namespace Phlex\Tool;
class PxRegEx {

	private $pattern;
	public $count;

	function __construct($pattern){
		$this->pattern = $pattern;
	}

	function match($subject, $flags = 0, $offset = 0){
		$ret = preg_match($this->pattern, $subject, &$matches, $flags, $offset);
		if($ret === false){
			$this->error = preg_last_error();
			return false;
		}
		else{
			$this->count = $ret;
			return $matches;
		}
	}

	function matchAll($subject, $flags = PREG_PATTERN_ORDER , $offset = 0){
		$ret = preg_match_all($this->pattern , $subject, $matches, $flags, $offset);
		if($ret === false){
			$this->error = preg_last_error();
			return false;
		}
		else{
			$this->count = $ret;
			return $matches;
		}
	}

	function filter($subject, $replacement, $limit = -1){
		$ret = preg_filter ($this->pattern, $replacement , $subject, $limit, $count);
		$this->count = $count;
		return $ret;
	}

	function replace($subject, $replacement, $limit = -1){
		$ret = preg_replace($this->pattern, $replacement , $subject, $limit, $count);
		$this->count = $count;
		return $ret;
	}

	function replaceCallback($subject, $callback, $limit = -1){
		$ret = preg_replace_callback($this->pattern, $callback , $subject, $limit, $count);
		$this->count = $count;
		return $ret;
	}

	function grep(array $subjects, $flags = 0){
		return preg_grep($this->pattern, $subjects, $flags);
	}

	function split( $subject, $limit = -1, $flags = 0 ){
		return preg_split ($this->pattern,  $subject, $limit, $flags );
	}
}