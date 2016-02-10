<?php namespace CustomTag\www;

use Phlex\Kraft\CustomTag;
use Phlex\Kraft\Response\HtmlResponse;

class Box extends CustomTag{

	protected $closer = 'www/box-closer';
	protected function createView($args) {
		return HtmlResponse::factory('www/box-begin', $args);
	}
}