<?php namespace CustomTag\www;

use App\www\ArticleController;
use Phlex\Kraft\Response\PageView;

class Comment extends \Phlex\Kraft\CustomTag{

	protected $closer = false;

	protected function createView($args) {
		$view =  ArticleController::factory()->articleComment($args);
		PageView::addServerVar('key', 'value');
		return $view;
	}
}
