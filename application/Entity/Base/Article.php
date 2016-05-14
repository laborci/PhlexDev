<?php namespace Entity\Base;

use Entity\ArticleRepository;
use \Phlex\RedFox\Entity;
use Phlex\ResourceManager;


/**
 * Class Article
 * @package Entity\Base
 */

abstract class Article extends Entity{
	protected $publishDate;
	protected $title;
	protected $lead;
	protected $authorId;

	/**
	 * @return Access
	 */
	public function getDBAccess(){ return ArticleRepository::instance()->getDBAccess(); }
	public function model(){ return ArticleModel::instance(); }

}



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
