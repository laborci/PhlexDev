<?php namespace Phlex\Database;

/**
 * Class Request
 */
class Request {

	/** @var \Phlex\Database\Access */
	protected $dbAccess;
	/** @var string */
	protected $select = '*';
	/** @var string */
	protected $keyField = null;
	/** @var  string */
	protected $from = null;
	/** @var  Filter */
	protected $where = null;
	/** @var array */
	protected $order = Array();
	/** @var  RequestConverter */
	protected $converterDelegate;

	public function __construct(Access $db, $converterDelegate = null) {
		$this->dbAccess = $db;
		$this->converterDelegate = $converterDelegate;
	}

#region initializers
	/**
	 * @param string $sql list of fields to retrieve
	 * @param string $sqlParams
	 * @return $this
	 */
	public function select($sql, $sqlParams = null) {
		$args = func_get_args();
		array_shift($args);
		$this->select = $this->dbAccess->buildSQL($sql . ' ', $args);
		return $this;
	}

	/**
	 * @param      $sql
	 * @param null $sqlParams
	 * @return $this
	 */
	public function key($sql, $sqlParams = null) {
		$args = func_get_args();
		array_shift($args);
		$this->keyField = $this->dbAccess->buildSQL($sql . ' ', $args);
		return $this;
	}

	/**
	 * @param string $sql mostly the table name
	 * @param string $sqlParams
	 * @return $this
	 */
	public function from($sql, $sqlParams = null) {
		$args = func_get_args();
		array_shift($args);
		$this->from = $this->dbAccess->buildSQL($sql . ' ', $args);
		return $this;
	}

	/**
	 * @param \Phlex\Database\Filter $filter
	 * @return $this
	 */
	public function where(Filter $filter) {
		$this->where = $filter;
		return $this;
	}
	#endregion

#region order
	/**
	 * @param string $field
	 * @return $this
	 */
	function asc($field) { return $this->Order($field . ' ASC'); }

	/**
	 * @param string $field
	 * @return $this
	 */
	function desc($field) { return $this->Order($field . ' DESC'); }

	/**
	 * @param string|array $param if array should look like this {'myField'=>'Asc', ...}
	 * @return $this
	 */
	function order($param) {
		if (is_array($param)) foreach ($param as $field => $dir) $this->order[] = $field . ' ' . $dir;
		else $this->order[] = $param;
		return $this;
	}

	/**
	 * @param $cond
	 * @param $field
	 * @param mixed [$sqlParams]
	 * @return $this
	 */
	function ascIf($cond, $field, $sqlParams = null) {
		if ($cond) {
			$args = func_get_args();
			array_shift($args);
			array_shift($args);
			$this->Order($this->dbAccess->buildSQL($field . ' ASC', $args));
		}
		return $this;
	}

	/**
	 * @param $cond
	 * @param $field
	 * @param mixed [$sqlParams]
	 * @return $this
	 */
	function descIf($cond, $field, $sqlParams = null) {
		if ($cond) {
			$args = func_get_args();
			array_shift($args);
			array_shift($args);
			$this->Order($this->dbAccess->buildSQL($field . ' DESC', $args));
		}
		return $this;
	}

	/**
	 * @param              $cond
	 * @param string|array $param if array should look like this {'myField'=>'Asc', ...}
	 * @param              mixed  [$sqlParams]
	 * @return $this
	 */
	function orderIf($cond, $param, $sqlParams = null) {
		if ($cond) {
			$args = func_get_args();
			array_shift($args);
			array_shift($args);
			if (is_array($param)) foreach ($param as $field => $dir) $this->order[] = $this->dbAccess->buildSQL($field, $args) . ' ' . $dir;
			else $this->order[] = $this->dbAccess->buildSQL($param, $args);
		}
		return $this;
	}

	#endregion

	/**
	 * Returns all, or limited elements (convertable)
	 * @param null $limit
	 * @param int  $offset
	 * @return mixed
	 */
	public function getAll($limit = null, $offset = 0) {
		$data = $this->getData($limit, $offset);
		if($this->converterDelegate) $data = $this->converterDelegate->DBRequestConvert($data, true);
		return $data;
	}

	/**
	 * Returns one element
	 * @return null
	 */
	public function get() {
		$data = $this->getData(1, 0);
		if ($data) {
			$data = array_shift($data);
			if($this->converterDelegate) $data = $this->converterDelegate->DBRequestConvert($data, false);
			return $data;
		} else return null;
	}

	/**
	 * Returns all, or limited elements (without convertion)
	 * @param null $limit
	 * @param int  $offset
	 * @return mixed
	 */
	public function getData($limit = null, $offset = 0) {
		$sql = $this->getSql();
		if ($limit) $sql .= ' LIMIT ' . $offset . ', ' . $limit;
		return $this->dbAccess->getRows($sql);
	}

	/**
	 * @param $pageSize
	 * @param $page
	 * @param $count
	 * @return mixed
	 */
	public function getPaged($pageSize, $page, &$count) {
		$data = $this->getPagedData($pageSize, $page, $count);
		if($this->converterDelegate) $data = $this->converterDelegate->DBRequestConvert($data, true);
		return $data;
	}

	/**
	 * @param $pageSize
	 * @param $page
	 * @param $count
	 * @return mixed
	 */
	public function getPagedData($pageSize, $page, &$count) {
		$pageSize = abs(intval($pageSize));
		$page = abs(intval($page));
		$count = $this->count();
		if (!$count) return array();
		$pages = ceil($count / $pageSize);

		if ($page > $pages) $page = $pages;

		return $this->getData($pageSize, $pageSize * ($page - 1));
	}

	/**
	 * @return string
	 */
	public function getSql() {
		return
			'SELECT ' .
			$this->select . ' ' .
			(($this->keyField) ? (', ' . $this->keyField.' AS __KEY__ ') : ('')) .
			'FROM '.$this->from . ' ' .
			(($this->where != null) ? ($this->where->getSql($this->dbAccess) . ' ') : ('') ).
			((count($this->order)) ? (' ORDER BY ' . join(', ', $this->order)) : (''));
	}


	// iPagableObject
	/**
	 * @return mixed
	 */
	public function getItemCount() { return $this->count(); }

	/**
	 * @param $pageSize
	 * @param $page
	 * @return mixed
	 */
	public function getPage($pageSize, $page) { return $this->getPaged($pageSize, $page, $count); }

	/**
	 * @return mixed
	 */
	private function count() {
		$sql = $this->getSql();
		$sql = preg_replace('/^\s*SELECT(.+?)FROM/', 'SELECT COUNT(1) FROM', $sql);
		$sql = explode('ORDER BY', $sql);
		return $this->dbAccess->getValue($sql[0]);
	}
} // End of class
