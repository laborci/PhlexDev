<?php namespace Entity\Base;

use Entity\ArticleRepository;
use Phlex\Database\Access;
use Phlex\RedFox\Entity;


/**
 * Class ArticleBase
 * @package Entity\Base
 */

abstract class ArticleBase extends Entity{
	protected $publishDate;
	protected $title;
	protected $lead;
	protected $authorId;

	/**
	 * @return Access
	 */
	public function getDBAccess(){ return ArticleRepository::instance()->getDBAccess(); }

	/**
	 * @return ArticleModel
	 */
	public function model(){ return ArticleModel::instance(); }

}