<?php
/**
 * Author: Laborci Gergely
 * Copyright: 365 Media Ltd. (www.365media.hu)
 * Created: 14/05/16 20:00
 */

namespace Entity\Base;

use Entity\Article;
use Phlex\RedFox\EntityRepository;
use Phlex\RedFox\Model\Field\DateTimeField;
use Phlex\RedFox\Model\Field\EnumField;
use Phlex\RedFox\Model\Field\IntegerField;
use Phlex\RedFox\Model\Field\StringField;
use Phlex\RedFox\Model\Model;
use Phlex\RedFox\Relation;

class ArticleModel extends Model{
	protected static $__instance = null;

	/**
	 * @title StringField
	 */
	public $title;

	/**
	 * @lead StringField
	 */
	public $lead;

	/**
	 * @type EnumField
	 */
	public $type;

	/**
	 * @var DateTimeField
	 */
	public $publishDate;

	/**
	 * @var IntegerField
	 */
	public $authorId;

	protected function __construct() {
		$this->title = new StringField('title', false, 255);
		$this->lead = new StringField('lead', false, 65536);
		$this->type = new EnumField('type', false, array('news', 'article', 'feature', 'blogpost'));
		$this->publishDate = new DateTimeField('publishDate', false);
		$this->authorId = new IntegerField('authorId', true, 0, 65536);

		Article::decorateModel($this);
	}



}
