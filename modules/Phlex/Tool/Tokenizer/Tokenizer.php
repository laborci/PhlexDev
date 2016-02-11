<?php namespace Phlex\Tool\Tokenizer;

use Phlex\Tool\StringTool;

abstract class Tokenizer {
	public $string;
	public $pointer;
	/**
	 * @var Token
	 */
	public $stateObject;
	public $state;
	public $items = array();

	function __construct($string){
		$this->string = $string;
		$this->pointer = 0;
		$this->stateObject = $this->createStartToken();
		$this->stateObject->t = $this;
		$this->pointer += $this->stateObject->shiftPointerBy;
		$this->state = StringTool::getPart('\\', get_class($this->stateObject), -1);
	}

	function createStartToken() {
		throw new Exception("Unimplemented method `Tokenizer::createStartToken`. ");
		return null;
	}

	function run() {
		$string = '';
		while($this->pointer < strlen($this->string)){
			$result = $this->stateObject->transition();

			if($result){
				$this->stateObject->evaluate($string);
				$this->stateObject = $result;
				$this->stateObject->t = $this;
				$this->pointer += $this->stateObject->shiftPointerBy;
				$this->state = $this->state = \Phlex\Tool\StringTool::getPart('\\', get_class($this->stateObject), -1);
				$string = '';
			}else{
				$string .= $this->string{$this->pointer};
				$this->pointer++;
			}
		}
		return $this->items;
	}

	function lookForPattern($pattern){
		if (preg_match($pattern, substr($this->string, $this->pointer), $m)) return strlen($m[0]);
		return false;
	}
	
	function lookForString($string){ return $string == substr($this->string, $this->pointer, strlen($string)); }

	function lookForChar($char, $relativeOffset = 0) { return $this->string{($this->pointer + $relativeOffset)} == $char{0}; }
	
	function getChar($relativeOffset = 0) { return $this->string{($this->pointer + $relativeOffset)}; }
	
}  // End of class Tokenizer