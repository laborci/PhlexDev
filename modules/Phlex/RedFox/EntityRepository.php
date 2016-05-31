<?php
/**
 * Author: Laborci Gergely
 * Copyright: 365 Media Ltd. (www.365media.hu)
 * Created: 14/05/16 22:23
 */

namespace Phlex\RedFox;


use Phlex\Database\Access;
use Phlex\Database\Filter;
use Phlex\Database\Request as DBRequest;
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
	abstract protected function createNewEntity();

	/**
	 * @param Entity $object
	 * @return bool
	 */
	abstract public function checkInstance($object);
#endregion	

	/**@return \Phlex\Database\Access */
	function getDBAccess() {
		if ($this->DBAccess == null) $this->DBAccess = ResourceManager::db($this->database);
		return $this->DBAccess;
	}

	/**
	 * Loads an entity by id from the database
	 * @param integer $id
	 * @return Entity
	 */
	public function get($id) {
		if (array_key_exists($id, $this->cache)) return $this->cache[$id];
		$this->cache[$id] = $this->instantiateEntity($this->getDBAccess()->getRowById($this->table, $id));
		return $this->cache[$id];
	}

	/**
	 * @param                        $valueField
	 * @param \Phlex\Database\Filter $filter
	 * @param null                   $order
	 * @return mixed
	 */
	public function getList($valueField, Filter $filter, $order = null){
		$request = new DBRequest($this->getDBAccess());
		if($order === null) $order = array($valueField, 'ASC');
		$request->Select("`id` as __KEY__, `".$valueField."` as __VALUE__ FROM `".$this->table."`")->Where($filter)->Order($order);
		return $request->GetAll();
	}

	/**
	 * @param \Phlex\Database\Filter|null $filter
	 * @return \Phlex\Database\Request
	 */
	public function find(Filter $filter = null){
		$request = new DBRequest($this->getDBAccess());
		$request->from($this->table);
		if($filter) {
			$request->filter($filter);
		}
		return $request;
	}

	/**
	 * Saves the object into database.
	 * @param \Phlex\RedFox\Entity $object
	 * @return integer
	 */
	public function save(Entity $object) {
		if ($this->checkInstance($object)) {
			
			$data = $object->_dataOut();
			
			if($object->getId()){
				//TODO: update
			}else{
				//TODO: insert
				$id = 'new id provided by the database';
				$object->id = $id;
			}
		} else {
			trigger_error(E_USER_ERROR, "Tried to save an " . get_class($object) . "object with " . get_class($this) . "!");
		}
	}

	protected function instantiateEntity(array $data) {
		$item = $this->createNewEntity();
		$item->_dataIn($data);
		return $item;
	}
}