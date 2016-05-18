<?php namespace Entity;

use Entity\Base\ArticleBase;
use Entity\Base\ArticleModel;
use Phlex\RedFox\Model\Field;
use Phlex\RedFox\Relation;

/**
 * Class Article
 *
 * @package Entity
 * @property-read ArticleBase $author
 * @property-read string      $title
 * @property \DateTime        $publishDate
 */
class Article extends ArticleBase{

	public $lead;
	public $data;
	public $authorId;
	public $type;

	/** @param $model ArticleModel */
	public static function decorateModel($model){
		$model->authorId->setMin(50);
		$model->author = new Relation('author', $model->authorId, \Entity\ArticleRepository::instance() );
		$model->publishDate->setDefault( function(){ return time(); } );
		$model->publishDate->access = Field::READ + Field::WRITE_ONCE;
		$model->title->access = Field::READ;
		$model->data = new Field\JsonStringField( $model->data );
	}


}