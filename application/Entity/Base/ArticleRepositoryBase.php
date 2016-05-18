<?php namespace Entity\Base;


use Entity\Article;
use Phlex\RedFox\Entity;
use Phlex\RedFox\EntityRepository;
use Phlex\RedFox\Model\Field;


/**
 * Class ArticleRepositoryBase
 *
 * @package Entity\Base
 * @method Article get(int $id)
 */
class ArticleRepositoryBase extends EntityRepository {

	protected static $__instance = null;

	protected function __construct() {
		$this->database = 'app';
		$this->table = 'article';
	}

	/**
	 * @param array $data
	 * @return static
	 */
	protected function createInstance($data) { return Article::instantiate($data); }

	/**
	 * @param Entity $object
	 * @return bool
	 */
	public function checkInstance($object) { return $object instanceof Article; }

}