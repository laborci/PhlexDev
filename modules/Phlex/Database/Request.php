<?php namespace Phlex\Database;

/**
 * Class DBRequest
 * @method Request And(string $sql, null $sqlParams)
 * @method Request Or(string $sql, null $sqlParams)
 */
class Request{
	private $where = Array();
	private $select;
	private $db;
	private $order = Array();
	protected $converterDelegate;

	function __construct(Access $db, $converterDelegate = null) {
		$this->db = $db;
		$this->converterDelegate = $converterDelegate;
	}

	function __invoke($sql, $sqlParams = null) {
		return call_user_func_array(array($this, 'Select'), func_get_args());
	}

	/**
	 * @param $sql
	 * @return $this
	 * @throws Exception
	 */
	function Select($sql, $sqlParams = null){
		if($this->select) trigger_error('Phlex Database Request too many selects', E_USER_ERROR);
		$args = func_get_args(); array_shift($args);
		$this->select = $this->db->buildSQL('SELECT '.$sql.' ', $args);
		return $this;
	}

	// WHERE
	/**
	 * @param $type
	 * @param $args
	 * @return $this
	 */
	function addWhere($type, $args){
		$sql = array_shift($args);
		if ($sql instanceof Filter) $sql = $sql->GetSql($this->db);
		else $sql = $this->db->buildSQL($sql, $args);
		if (!$this->where) $type = 'WHERE';
		else if ($type == 'WHERE') $type = 'AND';
		if ($sql) $this->where[] = ' '.$type.' ('.$sql.') ';
		return $this;
	}

	/**
	 * @param $sql
	 * @param null $sqlParams
	 * @return $this
	 */
	function Where($sql, $sqlParams = null){return $this->addWhere('WHERE', func_get_args());}
	
	/**
	 * @param $cond
	 * @param $sql
	 * @param null $sqlParams
	 * @return $this
	 */
	function WhereIf($cond, $sql, $sqlParams=null){
		$args = func_get_args(); array_shift($args);
		if($cond) $this->addWhere('WHERE', $args);
		return $this;
	}

	function __call($name, $args){
		$name = strtoupper($name);
		if($name == 'AND' or $name == 'OR') return $this->addWhere($name, $args);
		else return $this;
	} // OR/AND

	/**
	 * @param $cond
	 * @param $sql
	 * @param null $sqlParams
	 * @return $this
	 */
	function AndIf($cond, $sql, $sqlParams=null){
		$args = func_get_args(); array_shift($args);
		if($cond) $this->addWhere('AND', $args);
		return $this;
	}

	/**
	 * @param $cond
	 * @param $sql
	 * @param null $sqlParams
	 * @return $this
	 */
	function OrIf($cond, $sql, $sqlParams=null){
		$args = func_get_args(); array_shift($args);
		if($cond) $this->addWhere('OR', $args);
		return $this;
	}

	// ORDER
	/**
	 * @param $field
	 * @return $this
	 */
	function Asc($field){return $this->Order($field.' ASC');}

	/**
	 * @param $field
	 * @return $this
	 */
	function Desc($field){return $this->Order($field.' DESC');}

	/**
	 * @param $param
	 * @return $this
	 */
	function Order($param){
		if(is_array($param)) foreach($param as $field=>$dir) $this->order[] = $field.' '.$dir;
		else $this->order[] = $param;
		return $this;
	}
	
	/**
	 * @param $cond
	 * @param $field
	 * @param mixed [$sqlParams]
	 * @return $this
	 */
	function AscIf($cond, $field, $sqlParams=null){
		if ($cond) {
			$args = func_get_args(); array_shift($args); array_shift($args);
			$this->Order($this->db->buildSQL($field.' ASC', $args));
		}
		return $this;
	}

	/**
	 * @param $cond
	 * @param $field
	 * @param mixed [$sqlParams]
	 * @return $this
	 */
	function DescIf($cond, $field, $sqlParams=null){
		if ($cond) {
			$args = func_get_args(); array_shift($args); array_shift($args);
			$this->Order($this->db->buildSQL($field.' DESC', $args));
		}
		return $this;
	}

	/**
	 * @param $cond
	 * @param $param
	 * @param mixed [$sqlParams]
	 * @return $this
	 */
	function OrderIf($cond, $param, $sqlParams=null){
		if ($cond) {
			$args = func_get_args(); array_shift($args); array_shift($args);
			if(is_array($param)) foreach($param as $field=>$dir) $this->order[] = $this->db->buildSQL($field, $args).' '.$dir;
			else $this->order[] = $this->db->buildSQL($param, $args);
		}
		return $this;
	}

	// GET
	/**
	 * @param null $limit
	 * @param int $offset
	 * @return mixed
	 */
	function GetAll($limit = null, $offset = 0){
		$data = $this->GetData($limit, $offset);
		if ($this->converterDelegate && method_exists($this->converterDelegate, 'convertAll')) {
			$converterDelegate = $this->converterDelegate;
			return $converterDelegate::convertAll($data);
		}
		return $data;
	}

	/**
	 * @return null
	 */
	function Get(){
		$data = $this->GetData(1, 0);
		if ($data) {
			$data = array_shift($data);
			if ($this->converterDelegate && method_exists($this->converterDelegate, 'convertAll')) {
				$converterDelegate = $this->converterDelegate;
				return $converterDelegate::convert($data);
			}
			return $data;
		} else return null;
	}

	/**
	 * @param null $limit
	 * @param int $offset
	 * @return mixed
	 */
	function GetData($limit = null, $offset = 0){
		$sql = $this->GetSql();
		if($limit) $sql .= ' LIMIT '.$offset.', '.$limit;
		return $this->db->getRows($sql);
	}

	/**
	 * @return mixed
	 */
	private function Count(){
		$sql = $this->GetSql();
		$sql = preg_replace('/^\s*SELECT(.+?)FROM/', 'SELECT COUNT(1) FROM', $sql);
		$sql = explode('ORDER BY', $sql);
		return $this->db->getValue($sql[0]);
	}

	/**
	 * @param $pageSize
	 * @param $page
	 * @param $count
	 * @return mixed
	 */
	function GetPaged($pageSize, $page, &$count){
		$data = $this->GetPagedData($pageSize, $page, $count);
		if ($this->converterDelegate && method_exists($this->converterDelegate, 'convertAll')) {
			$converterDelegate = $this->converterDelegate;
			return $converterDelegate::convertAll($data);
		}
		return $data;
	}

	/**
	 * @param $pageSize
	 * @param $page
	 * @param $count
	 * @return mixed
	 */
	function GetPagedData($pageSize, $page, &$count){
		$pageSize = abs(intval($pageSize));
		$page = abs(intval($page));
		$count = $this->Count();
		if(!$count) return array();
		$pages = ceil($count/$pageSize);

		if($page>$pages) $page = $pages;

		return $this->GetData($pageSize, $pageSize*($page-1));
	}

	/**
	 * @return string
	 */
	function GetSql(){
		return
			$this->select.' '.
			join('', $this->where).' '.
			((count($this->order))?(' ORDER BY '.join(', ',$this->order)):(''));
	}


	// iPagableObject
	/**
	 * @return mixed
	 */
	public function getItemCount() { return $this->Count(); }

	/**
	 * @param $pageSize
	 * @param $page
	 * @return mixed
	 */
	public function getPage($pageSize, $page) { return $this->GetPaged($pageSize, $page, $count); }
} // End of class
