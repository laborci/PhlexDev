<?php namespace Phlex\Kraft;

use Phlex\Request\Request;

class Controller {

	protected $request;

	public static function factory(){
		return new static();
	}

	private function __construct(){
		$this->request = Request::getCurrent();
	}

}