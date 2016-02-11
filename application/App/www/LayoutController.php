<?php namespace App\www;

use Phlex\Kraft\Response\PageView;
use Phlex\Kraft\Response\HtmlView;

abstract class LayoutController extends \Phlex\Kraft\Controller{

	protected function getLayoutView(){
		$view = PageView::factory('www/layout');
		$view['pageTitle'] = 'Das ist page';
		$view['footer'] = $this->getFooterView();
		return $view;
	}

	protected function getFooterView(){
		$view = HtmlView::factory('www/footer');
		$view['footer'] = 'ich bin footer';
		return $view;
	}

}