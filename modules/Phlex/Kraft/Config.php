<?php namespace Phlex\Kraft;

class Configo {

	public $pathSource;
	public $pathTemplate;

	public $extSource = '.kraft.php';
	public $extSourcePair = '.kraft-pair.php';
	public $extTemplate = '.php';
	public $extTemplatePairStart = '.start.php';
	public $extTemplatePairEnd = '.end.php';

	static private $instance;
	private function __construct(){}
	/**
	 * @return static
	 */
	static function getInstance(){
		if(static::$instance === null) static::$instance = new static();
		return static::$instance;
	}

} 