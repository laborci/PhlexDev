<?php namespace Entity\Base;
use Entity\Article;
use \Phlex\RedFox\Entity;
use Phlex\ResourceManager;

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


/**
 * Class ArticleBase
 * @package Entity\Base
 */

abstract class ArticleBase extends Entity{

	
	protected $publishDate;
	protected $title;
	protected $lead;
	protected $authorId;

	const DATABASE_NAME = 'default';
	const TABLE = 'article';

	/**
	 * @return Access
	 */
	public function getDBAccess(){ return ResourceManager::db(static::DATABASE_NAME); }
	public static function model(){ return ArticleModel::instance(); }
	protected function __getAuthor(){
		if($this->authorId) return static::model()->authorId->getRelatedObject($this->authorId);
		return null;
	}

	
}