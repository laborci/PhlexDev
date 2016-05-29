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

#region instance
	protected static $__instance;

	/**
	 * @return static
	 */
	public static function instance() {
		if (static::$__instance === null) static::$__instance = new static();
		return static::$__instance;
	}
#endregion	

	/** @var string */
	protected $database;
	/** @var string */
	protected $table;
	/** @var Access */
	private $DBAccess = null;
	/** @var array */
	private $cache = array();

#region abstract methods

	/**
	 * @param array $data
	 * @return Entity
	 */
	abstract protected function createInstance($data);

	/**
	 * @param Entity $object
	 * @return bool
	 */
	abstract public function checkInstance($object);
#endregion	

	/**
	 * @return \Phlex\Database\Access
	 */
	function getDBAccess() {
		if ($this->DBAccess == null) $this->DBAccess = ResourceManager::db($this->database);
		return $this->DBAccess;
	}

	/**
	 * @param int $id
	 * @return Entity
	 */
	public function get($id) {
		if (array_key_exists($id, $this->cache)) return $this->cache[$id];
		$this->cache[$id] = $this->createInstance($this->getDBAccess()->getRowById($this->table, $id));
		return $this->cache[$id];
	}
	
	public function save($object){
		
	}
}