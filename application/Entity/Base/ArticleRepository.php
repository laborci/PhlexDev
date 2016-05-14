<?php namespace Entity\Base;

use Entity\Article;
use Phlex\RedFox\EntityRepository;
use Phlex\RedFox\Model\Field;


/**
 * Class ArticleRepository
 *
 * @package Entity\Base
 * @method Article get(int $id)
 */
class ArticleRepository extends EntityRepository{

	protected static $__instance = null;

	protected function __construct() {
		$this->database = 'app';
		$this->table = 'article';
	}

	/**
	 * @param $data
	 *
	 * @return static
	 */
	protected function createInstance($data){
		return Article::instantiate($data);
	}

}