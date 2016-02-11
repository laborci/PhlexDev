<?php namespace CustomTag\www;

use Phlex\Kraft\CustomTag;
use Phlex\Kraft\Response\HtmlView;

class Box extends CustomTag{

	protected $closer = 'www/box-closer';
	protected function createView($args) {
		return HtmlView::factory('www/box-begin', $args);
	}
}