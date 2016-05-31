<?php namespace Phlex\Database;


/**
 * @method Filter and ($sql, $sqlParams = null)
 * @method Filter or ($sql, $sqlParams = null)
 */

class Filter {

	protected function __construct() { }

	protected $where = Array();

	/**
	 * @param string $sql
	 * @param mixed  $sqlParams
	 * @return Filter
	 */
	static function filter($sql, $sqlParams = null) {
		$filter = new Filter();
		return $filter->addWhere('WHERE', func_get_args());
	}

	/**
	 * @param bool   $cond
	 * @param string $sql
	 * @param mixed  $sqlParams
	 * @return Filter
	 */
	static function filterIf($cond, $sql, $sqlParams = null) {
		$filter = new Filter();
		if (!$cond) return $filter;
		$args = func_get_args();
		array_shift($args);
		return $filter->addWhere('WHERE', $args);
	}

	/**
	 * This method handles the or/and calls (these names are reserved keywords)
	 * @param $name
	 * @param $args
	 * @return $this|\Phlex\Database\Filter
	 */
	function __call($name, $args) {
		$name = strtoupper($name);
		if ($name == 'AND' || $name == 'OR') return $this->addWhere($name, $args);
		else return $this;
		//TODO: On else it should fail
	}

	/**
	 * @param bool   $cond
	 * @param string $sql
	 * @param mixed  $sqlParams
	 * @return $this
	 */
	function andIf($cond, $sql, $sqlParams = null) {
		if (!$cond) return $this;
		$args = func_get_args();
		array_shift($args);
		return $this->addWhere('AND', $args);
	}

	/**
	 * @param bool   $cond
	 * @param string $sql
	 * @param mixed  $sqlParams
	 * @return $this
	 */
	function orIf($cond, $sql, $sqlParams = null) {
		if (!$cond) return $this;
		$args = func_get_args();
		array_shift($args);
		return $this->addWhere('OR', $args);
	}

	/**
	 * @param \Phlex\Database\Access $db
	 * @return string
	 */
	public function getSql(Access $db) {
		if (!$this->where) return null;

		$sql = '';
		foreach ($this->where as $filterSegment) {

			if ($filterSegment['sql'] instanceof Filter) $filterSegment['sql'] = $filterSegment['sql']->getSql($db);
			else if (is_array($filterSegment['sql'])) $filterSegment['sql'] = $this->getSqlFromArray($filterSegment['sql'], $db);
			if (trim($filterSegment['sql'])) {
				if ($sql) $sql .= " " . $filterSegment['type'] . " ";
				$sql .= "(" . $db->buildSQL($filterSegment['sql'], $filterSegment['args']) . ")";
			}

		}
		return $sql;
	}

#region Helper methods

	/**
	 * @param array                  $filter
	 * @param \Phlex\Database\Access $db
	 * @return null
	 */
	protected function getSqlFromArray(array $filter, Access $db) {
		if (!$filter) return null;
		$sql = array();
		foreach ($filter as $key => $value) {
			if (is_array($value)) $sql[] = $db->buildSQL(" `" . $key . "` IN ($1) ", $value);
			else $sql[] = $db->buildSQL(" `" . $key . "` = $1 ", $value);
		}
		return implode(' AND ', $sql);
	}

	/**
	 * @param string $type
	 * @param array  $args
	 * @return $this
	 */
	protected function addWhere($type, $args) {
		$sql = array_shift($args);
		if (!$this->where) $type = 'WHERE';
		else if ($type == 'WHERE') $type = 'AND';
		$this->where[] = array('type' => $type, 'sql' => $sql, 'args' => $args);
		return $this;
	}
	#endregion


} // End of class
