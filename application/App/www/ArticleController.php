<?php namespace App\www;

use Entity\Article;
use Entity\Base\ArticleModel;
use Phlex\Kraft\Response\HtmlView;

class ArticleController extends LayoutController {

	public function articlePage() {
		$view = $this->getLayoutView();
		$view['pageTitle'] = 'This is page';
		$view['content'] = $this->getArticleContent($this->request->get->getParsedAsInt('id'));
		$view['someSet'] = array('key1' => 'value1', 'key2' => 'value2');

		$articleModel = ArticleModel::instance();


		$article = new Article();
		$article;
		
		echo '<pre>';
		var_dump(property_exists($articleModel, 'title'));
		var_dump($articleModel->data);
		echo '</pre>';

		return $view;
	}

	public function articleComment($args) {
		$view = HtmlView::factory('www/comments', $args);

		return $view;
	}

	public function someData() {
		return $this->articlePage();
	}

	protected function getArticleContent($id) {
		$view = HtmlView::factory('www/article');
		$view['title'] = 'Article Title';
		$view['body'] = 'Article Body';

		return $view;
	}

	protected function getFooterView() {
		$view = HtmlView::factory('www/footer_alt');
		//$view->setRenderer(array($this, 'renderFooter'));
		$view['footer'] = 'NEW FOOTER*!';

		return $view;
	}

	public function renderFooter($view) {
		echo 'HELLO FÚTER ' . $view['footer'];
	}
}


