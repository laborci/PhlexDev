<?php namespace Entity;

use Entity\Base\ArticleBase;
use Entity\Base\ArticleModel;
use Phlex\RedFox\Model\Field;
use Phlex\RedFox\Model\Model;
use Phlex\RedFox\Relation;

/**
 * Class Article
 *
 * @package Entity
 * @property-read Article $author
 * @property-read string $title
 */
class Article extends ArticleBase{

	public $lead;
	public $publishDate;

	/**
	 * @param $model ArticleModel
	 *
	 * @return ArticleModel
	 */
	public static function decorateModel($model){
		$model->authorId->setMin(50);
		$model->publishDate->default = function(){ return time(); };
		$model->publishDate->access = Field::READ + Field::WRITE_ONCE;
		$model->title->access = Field::READ;
		$model->author = new Relation( 'authorId', \Entity\ArticleRepository::instance() );

		return $model;
	}


}