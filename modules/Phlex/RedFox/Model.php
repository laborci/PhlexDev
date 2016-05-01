<?php namespace Phlex\RedFox;

/*

{
	"Article": "default/Article",
	"User": "default/User"
}



 */

abstract class Entity{
	protected static $model;

	/**
	 * @return Model
	 */
	public static function getModel(){
		if(static::$model === null) static::buildModel();
		return static::$model;
	}

	public static function polishModel($model){return $model;}

}

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

		$model['title'] = new StringField('title', false);
		$model['title']->maxLength = 255;

		$model['lead'] = new StringField('lead', false);
		$model['lead']->maxLength = 65536;

		$model['type'] = new EnumField('type', false);
		$model['enum']->values = array('news', 'article', 'feature', 'blogpost');

		$model['publishDate'] = new DateTimeField('publishDate', false);

		$model['authorId'] = new IntegerField('authorId', true);
		$model['authorId']->min = 0;
		$model['authorId']->max = 65536;
		$model['authorId']->referenceTo('user');

		static::$model = static::polishModel($model);
	}

}



class Article extends ArticleBase{

	public $title;
	public $lead;

	/**
	 * @param Model $model
	 *
	 * @return mixed
	 */
	public static function polishModel($model){

		$model['publishDate']->addValidator(
			//DateTimeValidator::InTheFuture()
		);
		$model['publishDate']->default = function(){ return time(); };

		return $model;
	}


	
}


class Model implements \ArrayAccess{

	protected $fields = array();
	protected $database;
	protected $table;

	public function __construct($database, $table) {
		$this->database = $database;
		$this->table = $table;
	}

	public function offsetExists($offset) { return array_key_exists($offset, $this->fields); }
	public function offsetGet($offset) { return $this->fields[$offset]; }
	public function offsetSet($offset, $value) { $this->fields[$value]; }
	public function offsetUnset($offset) { unset($this->fields[$offset]); }

}


abstract class ModelField {

	protected $name; // db field name
	protected $validators = array();



	protected $type; // varchar, int, enum, text, etc



	protected $null; // null allowed
	protected $defaultValue; // can be scalar or callable

	protected $size; // for int, varchar, etc
	protected $modifier; // for unsigned
	protected $options; // for enum or set


	public function addValidator($validator){
		$this->validators[] = $validator;
	}


	protected function validate($value){
		if($value === null) return $this->null;

	}

	public function __construct($name) {

	}

	public function __get($varName) {
		switch ($varName) {
			case 'name':
				return $this->name;
				break;
			case 'type':
				return $this->type;
				break;
		}
	}
}

class SetField extends ModelField{
	protected $options;

}

class EnumField extends ModelField{
	protected $options;

}

class StringField extends ModelField{

}

class JsonField extends StringField{

}

class DateField extends ModelField{

}

class DateTimeField extends ModelField{

}