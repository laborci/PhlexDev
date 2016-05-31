<?php namespace EntityBase;

use Entity\Article;

use Phlex\RedFox\Model\Field\DateTimeField;
use Phlex\RedFox\Model\Field\EnumField;
use Phlex\RedFox\Model\Field\IntegerField;
use Phlex\RedFox\Model\Field\StringField;
use Phlex\RedFox\Model\Model;

// use Phlex\RedFox\Relation;

class ArticleModelBase extends Model{
	protected static $__instance = null;

	/**
	 * @var \Phlex\RedFox\Model\Field\StringField
	 */
	public $title;

	/**
	 * @var \Phlex\RedFox\Model\Field\StringField
	 */
	public $lead;

	/**
	 * @var \Phlex\RedFox\Model\Field\EnumField
	 */
	public $type;

	/**
	 * @var \Phlex\RedFox\Model\Field\DateTimeField
	 */
	public $publishDate;

	/**
	 * @var \Phlex\RedFox\Model\Field\IntegerField
	 */
	public $authorId;

	/**
	 * @var \Phlex\RedFox\Model\Field\StringField
	 */
	public $data;

	protected function __construct() {
		$this->title = new StringField('title', false, 255);
		$this->lead = new StringField('lead', false, 65536);
		$this->type = new EnumField('type', false, array('news', 'article', 'feature', 'blogpost'));
		$this->publishDate = new DateTimeField('publishDate', false);
		$this->authorId = new IntegerField('authorId', true, 0, 65536);
		$this->data = new StringField('data', false, 0, 65536);

		if(method_exists('Entity\\Article', 'decorateModel'))	Article::decorateModel($this);

	}
	
	
	
}
