<?php namespace Phlex\Kraft;

use Phlex\Kraft\Response\HtmlResponse;

abstract class CustomTag {

	/**
	 * @var HtmlResponse
	 */
	protected $view = null;
	/**
	 * @var HtmlResponse
	 */
	protected $parentView = null;
	/**
	 * @var string
	 */
	protected $closer = false;
	protected static $stack = array();

	protected function __construct($parentView) {
		$this->parentView = $parentView;
	}

	/**
	 * @param $args
	 *
	 * @return HtmlResponse
	 */
	abstract protected function createView($args);

	public static function factory(array $args = array(), $parentView){
		$ct = new static($parentView);
		$ct->view = $ct->createView($args);
		if($ct->closer) array_push(static::$stack, $ct);
		$ct->view->setParent($parentView);
		return $ct;
	}

	/**
	 * @return CustomTag
	 */
	public static function pullFromStack(){
		return array_pop(static::$stack);
	}

	public function renderTag(){
		echo $this->view;
	}

	public function renderCloseTag(){
		echo $this->view->setTemplate($this->closer);
	}
}
