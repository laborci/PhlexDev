<?php namespace Entity\Base;

use Entity\ArticleRepository;
use \Phlex\RedFox\Entity;
use Phlex\ResourceManager;


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
	public function model(){ return ArticleModel::instance(); }

}