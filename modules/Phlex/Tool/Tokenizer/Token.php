<?php namespace Phlex\Tool\Tokenizer;

abstract class Token {
	/**
	 * @var \Phlex\Tool\Tokenizer\Tokenizer
	 */
	public $t;
	public $shiftPointerBy;
	function __construct($shiftPointerBy){ $this->shiftPointerBy = $shiftPointerBy; }

	/**
	 * Evaluates string
	 * @param string $string
	 */
	function evaluate($string){ $this->t->items[] = array('type' => $this->state = String::getPart('\\', get_class($this), -1), 'token' => trim($string)); }

	/**
	 * Bekerulesi feltetelek kieretekelese
	 * Hamissal, vagy egy sajat objektumpeldannyal ter vissza
	 * @return mixed
	 */


	/**
	 * @param $tokenizer \Phlex\Tool\Tokenizer\Tokenizer
	 *
	 * @return bool
	 */
	static function test($tokenizer){ return false; }

	/**
	 * Lehetseges celallapotok vizsgalata
	 * Hamissal, vagy egy celallapot objektummal ter vissza
	 * @return mixed
	 */
	function transition(){ return false; }

} // End of class Token