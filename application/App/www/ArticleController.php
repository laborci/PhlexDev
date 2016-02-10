<?php namespace App\www;

use Phlex\Kraft\Response\HtmlResponse;

class ArticleController extends LayoutController{

	public function articlePage(){
		$view = $this->getLayoutView();
		$view['pageTitle'] = 'This is page';
		$view['content'] = $this->getArticleContent($this->request->get->getParsedAsInt('id'));
		$view['someSet'] = array('key1'=>'value1', 'key2'=>'value2');
		return $view;
	}

	public function articleComment($args){
		$view = HtmlResponse::factory('www/comments', $args);
		return $view;
	}

	public function someData(){
		return $this->articlePage();
	}

	protected function getArticleContent($id){
		$view = HtmlResponse::factory('www/article');
		$view['title'] = 'Article Title';
		$view['body'] = 'Article Body';
		return $view;
	}

	protected function getFooterView() {
		$view = HtmlResponse::factory('www/footer_alt');
		//$view->setRenderer(array($this, 'renderFooter'));
		$view['footer'] = 'NEW FOOTER*!';
		return $view;
	}

	public function renderFooter($view){
		echo 'HELLO FÚTER '.$view['footer'];
	}
}


