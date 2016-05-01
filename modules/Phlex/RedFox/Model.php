<?php namespace Phlex\RedFox;

/*

{
	"Article": "default/Article",
	"User": "default/User"
}



 */

class Entity{


}

class ArticleBase extends Entity{


	protected static $model;

	protected $publishDate;
	protected $title;
	protected $lead;
	protected $authorId;
	protected $author;


	/**
	 * @return Model
	 */
	public static function getModel(){
		if(static::$model === null) static::buildModel();
		return static::$model;
	}

	public static function buildModel(){
		$model = new Model('default', 'Article');

		$model['title'] = new StringField('title', false);
		$model['lead']->addValidator(
			StringValidator::Max(255)
		);

		$model['lead'] = new StringField('lead', false);
		$model['lead']->addValidator(
			StringValidator::Max(65000)
		);

		$model['publishDate'] = new DateTimeField('publishDate', false);

		$model['authorId'] = new IntegerField('authorId', true);
		$model['authorId']->addValidator(
			IntegerValidator::Type(4, IntegerValidator::SIGNED)
		);


		static::$model = static::extendModel($model);
	}

	// - - - - - - - - - - - do not modify lines above this line - - - - - - - - - - - - - - - - - - -

	public static function extendModel($model){
		$model['authorId']->reference('\App\Entity\User');

		$model[publishDate]->addValidator(
			DateTimeValidator::InTheFuture()
		);
		$model['publishDate']->default = function(){ return time(); };

		return $model;
	}


	protected $author;

	protected function __getAuthor(){

	}

	protected function setAuthor($author){

	}
}

class Article extends ArticleBase{

	public $title;
	public $lead;

}


class Model implements \ArrayAccess{

	protected $fields = array();
	protected $database;
	protected $table;

	public function __construct($database, $table) {
		$this->database = $database;
		$this->table = $table;
	}

	public function offsetExists($offset) {
		return array_key_exists($offset, $this->fields);
	}

	public function offsetGet($offset) {
		return $this->fields[$offset]
	}

	public function offsetSet($offset, $value) {
		$this->fields[$value];
	}

	public function offsetUnset($offset) {
		unset($this->fields[$offset]);
	}


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




	protected function validate($value){

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

class StringField extends ModelField{

}