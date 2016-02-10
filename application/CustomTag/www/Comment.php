<?php namespace CustomTag\www;

use App\www\ArticleController;
use Phlex\Kraft\Response\PageResponse;

class Comment extends \Phlex\Kraft\CustomTag{

	protected $closer = false;

	protected function createView($args) {
		$view =  ArticleController::factory()->articleComment($args);
		PageResponse::addServerVar('key', 'value');
		return $view;
	}
}
