<?php namespace App\www;

use Phlex\Kraft\Response\PageResponse;
use Phlex\Kraft\Response\HtmlResponse;

abstract class LayoutController extends \Phlex\Kraft\Controller{

	protected function getLayoutView(){
		$view = PageResponse::factory('www/layout');
		$view['pageTitle'] = 'Das ist page';
		$view['footer'] = $this->getFooterView();
		return $view;
	}

	protected function getFooterView(){
		$view = HtmlResponse::factory('www/footer');
		$view['footer'] = 'ich bin footer';
		return $view;
	}

}