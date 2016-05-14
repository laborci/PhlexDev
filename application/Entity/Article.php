<?php namespace Entity;

use Entity\Base\Article as Base;
use Entity\Base\ArticleModel;
use Phlex\RedFox\Model\Field;
use Phlex\RedFox\Relation;

/**
 * Class Article
 *
 * @package Entity
 * @property-read Article $author
 * @property-read string $title
 * @property \DateTime $publishDate
 */
class Article extends Base{

	public $lead;
	public $data;

	/** @param $model ArticleModel */
	public static function decorateModel($model){
		$model->authorId->setMin(50);
		$model->author = new Relation( 'authorId', \Entity\ArticleRepository::instance() );

		$model->publishDate->setDefault( function(){ return time(); } );
		$model->publishDate->access = Field::READ + Field::WRITE_ONCE;

		$model->title->access = Field::READ;

		$model->data = new Field\JsonStringField( $model->data );
	}


}