<?php
/**
 * Author: Laborci Gergely
 * Copyright: 365 Media Ltd. (www.365media.hu)
 * Created: 14/05/16 22:23
 */

namespace Phlex\RedFox;


use Phlex\Database\Access;
use Phlex\ResourceManager;

abstract class EntityRepository {

	protected $database;
	protected $table;

	/**
	 * @var Access
	 */
	private $DBAccess = null;

	/**
	 * @return \Phlex\Database\Access
	 */
	function getDBAccess(){
		if($this->DBAccess == null) $this->DBAccess = ResourceManager::db($this->database);
		return $this->DBAccess;
	}


	/**
	 * @param $data
	 *
	 * @return Entity
	 */
	abstract protected function createInstance($data);


	protected static $__instance;

	/**
	 * @return static
	 */
	public static function instance() {
		if (static::$__instance === null) static::$__instance = new static();
		return static::$__instance;
	}

	/**
	 * @param $id
	 *
	 * @return Entity
	 */
	public function get($id) {
		return $this->createInstance( $this->getDBAccess()->getRowById($this->table, $id) );
	}
}