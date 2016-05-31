<?php namespace Entity;

use EntityBase\ArticleModelBase;
use Phlex\RedFox\Model\Field;
use Phlex\RedFox\Model\Field\JsonStringField;
use Phlex\RedFox\Relation;


class ArticleModel extends ArticleModelBase{

	public function __construct() {
		parent::__construct();
		$this->authorId->setMin(50);
		$this->author = new Relation('author', $this->authorId, ArticleRepository::instance() );
		$this->publishDate->setDefault( function(){ return time(); } );
		$this->publishDate->access = Field::READ + Field::WRITE_ONCE;
		$this->title->access = Field::READ;
		$this->data = new JsonStringField( $this->data );
	}

}