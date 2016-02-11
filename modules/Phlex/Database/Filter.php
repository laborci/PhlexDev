<?php namespace Phlex\Database;

class Filter {
	
	private $where = Array();

	/**
	 * @param string $sql
	 * @param mixed $sqlParams
	 *
*@return Filter
	 */
	static function Filter($sql, $sqlParams = null) {
		$filter = new Filter();
		return call_user_func_array(array($filter, 'Where'), func_get_args());
	}
	
	/**
	 * @param bool $cond
	 * @param string $sql
	 * @param mixed $sqlParams
	 *
*@return Filter
	 */
	static function FilterIf($cond, $sql, $sqlParams = null) {
		$filter = new Filter();
		call_user_func_array(array($filter, 'WhereIf'), func_get_args());
		return $filter;
	}
	
	// WHERE
	/**
	 * @param string $type
	 * @param array $args
	 * @return $this
	 */
	protected function addWhere($type, $args){
		$sql = array_shift($args);
		if (!$this->where) $type = 'WHERE';
		else if ($type == 'WHERE') $type = 'AND';
		$this->where[] = static::createFilterSegment($type, $sql, $args);
		return $this;
	}
	
	protected static function createFilterSegment($type, $sql, $args) {
		return array('type' => $type, 'sql' => $sql, 'args' => $args);
	}

	/**
	 * @param string $sql
	 * @param mixed $sqlParams
	 * @return $this
	 */
	function Where($sql, $sqlParams = null){ return $this->addWhere('WHERE', func_get_args()); }
	
	/**
	 * @param bool $cond
	 * @param string $sql
	 * @param mixed $sqlParams
	 * @return $this
	 */
	function WhereIf($cond, $sql, $sqlParams=null){
		$args = func_get_args(); array_shift($args);
		if($cond){
			$this->addWhere('WHERE', $args);
		}
		return $this;
	}

	function __call($name, $args){
		$name = strtoupper($name);
		if($name == 'AND' or $name == 'OR') return $this->addWhere($name, $args);
		else return $this;
	} // OR/AND

	/**
	 * @param bool $cond
	 * @param string $sql
	 * @param mixed $sqlParams
	 * @return $this
	 */
	function AndIf($cond, $sql, $sqlParams=null){
		$args = func_get_args(); array_shift($args);
		if($cond) $this->addWhere('AND', $args);
		return $this;
	}

	/**
	 * @param bool $cond
	 * @param string $sql
	 * @param mixed $sqlParams
	 * @return $this
	 */
	function OrIf($cond, $sql, $sqlParams=null){
		$args = func_get_args(); array_shift($args);
		if($cond) $this->addWhere('OR', $args);
		return $this;
	}

	/**
	 * @return string
	 */
	function GetSql(Access $db) {
		if (!$this->where) return null;
		
		$sql = '';
		foreach ($this->where as $filterSegment) {
			if ($filterSegment['sql'] instanceof Filter) $filterSegment['sql'] = $filterSegment['sql']->GetSql($db);
			else if (is_array($filterSegment['sql'])) $filterSegment['sql'] = static::getSqlFromArray($filterSegment['sql'], $db);
			
			if (trim($filterSegment['sql'])) {
				if ($sql) $sql .= " ".$filterSegment['type']." ";
				$sql .= "(".$db->buildSQL($filterSegment['sql'], $filterSegment['args']).")";
			}
		}
		return $sql;
	}
	
	static function getSqlFromArray(array $filter, Access $db) {
		if (!$filter) return null;
		$sql = array();
		foreach ($filter as $key => $value) {
			if (is_array($value)) $sql[] = $db->buildSQL(" `".$key."` IN ($1) ", $value);
			else $sql[] = $db->buildSQL(" `".$key."` = $1 ", $value);
		}
		return implode(' AND ', $sql);
	}

} // End of class
