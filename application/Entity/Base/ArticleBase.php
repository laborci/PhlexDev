<?php namespace Entity\Base;
use \Phlex\RedFox\Entity;

/*

visibility:
   public
	protected
	readonly (protected with autogetter)
	rock (protected / can be write only if current value is null) |

fields: {
	id: {
		name: "id",
		type: numeric,
		visibility: rock
	}
	authorId:{
		name: "authorId",
		type: numeric,
		visibility: public
		refers: User
	}
}

*/


abstract class ArticleBase extends Entity{

	protected $publishDate;
	protected $title;
	protected $lead;
	protected $authorId;
	protected $author;

	protected function __get($name){

	}

	protected function __set($name, $value){

	}

	public static function buildModel(){

		$model['title'] = new \Phlex\RedFox\Model\Field\StringField('title', false);
		$model['title']->maxLength = 255;

		$model['lead'] = new \Phlex\RedFox\Model\Field\StringField('lead', false);
		$model['lead']->maxLength = 65536;

		$model['type'] = new \Phlex\RedFox\Model\Field\EnumField('type', false);
		$model['enum']->values = array('news', 'article', 'feature', 'blogpost');

		$model['publishDate'] = new \Phlex\RedFox\Model\Field\DateTimeField('publishDate', false);

		$model['authorId'] = new \Phlex\RedFox\Model\Field\IntegerField('authorId', true);
		$model['authorId']->min = 0;
		$model['authorId']->max = 65536;
		$model['authorId']->referenceTo('\Entity\User');

		static::$model = static::polishModel($model);
	}

}