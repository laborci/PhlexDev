<?php namespace Phlex\RedFox;


use Phlex\Database\Access;
use Phlex\Database\Filter;
use Phlex\Database\Request as DBRequest;
use Phlex\Database\RequestConverter as DBRequestConverter;
use Phlex\ResourceManager;


abstract class EntityRepository implements DBRequestConverter {

#region instance
	protected static $__instance;

	/**
	 * @return static
	 */
	public static function instance() {
		if(static::$__instance === null) static::$__instance = new static();
		return static::$__instance;
	}
#endregion	

	/** @var string */
	protected $database;
	/** @var string */
	protected $table;
	/** @var Access */
	protected $DBAccess = null;
	/** @var array */
	protected $cache = array();

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
		if($this->DBAccess == null) $this->DBAccess = ResourceManager::db($this->database);
		return $this->DBAccess;
	}

	/**
	 * Loads an entity by id from the database
	 * @param integer $id
	 * @return Entity
	 */
	public function get($id) {
		if(array_key_exists($id, $this->cache)) return $this->cache[$id];
		$this->cache[$id] = $this->instantiateEntity($this->getDBAccess()->getRowById($this->table, $id));
		return $this->cache[$id];
	}

	/**
	 * @param                        $valueField
	 * @param string                 $keyField
	 * @param \Phlex\Database\Filter $filter
	 * @param null|string            $order
	 * @return mixed
	 */
	protected function getKeyValueList($valueField, $keyField = 'id', Filter $filter = null, $order = 'ASC') {
		$request = new DBRequest($this->getDBAccess());
		if($order) $order = array($valueField, 'ASC');
		$valueField = $this->DBAccess->escapeSQLEntity($valueField);
		$table = $this->DBAccess->escapeSQLEntity($this->table);
		$keyField = $this->DBAccess->escapeSQLEntity($keyField);
		$request->select("'.$keyField.' as __KEY__, " . $valueField . " as __VALUE__")->from($table)->where($filter)->orderIf($order, $order);
		return $request->getAll();
	}

	/**
	 * @param \Phlex\Database\Filter|null $filter
	 * @return \Phlex\Database\Request
	 */
	protected function find(Filter $filter = null) {
		$table = $this->DBAccess->escapeSQLEntity($this->table);
		$request = new DBRequest($this->getDBAccess(), $this);
		$request->from($table);
		$request->key('id');
		if($filter !== null) {
			$request->where($filter);
		}
		return $request;
	}

	/**
	 * @param null $filter
	 * @return \Phlex\Database\Request
	 */
	protected function __invoke($filter = null) {
		return $this->find($filter);
	}

	/**
	 * Saves the object into database.
	 * @param \Phlex\RedFox\Entity $object
	 * @return integer
	 */
	public function save(Entity $object) {
		if($this->checkInstance($object)) {
			$data = $object->_dataOut();
			if($object->id) {
				//TODO: update
			} else {
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

	public function DBRequestConvert(array $data, $multiple = false) {
		if(!$multiple) return $this->instantiateEntity($data);
		else {
			$objects = array();
			foreach($data as $key => $record) {
				$objects[$key] = $this->instantiateEntity($record);
			}
		}
		return $objects;
	}
}