<?php namespace Phlex\Database;

use PDO;
use PDOException;

class Access {

	public $dbServerData;
	/**
	 * @var \PDO
	 */
	public $db;
	public $charSet;
	/**
	 * @var bool
	 */
	public static $debug = false;

	/**
	 * Creates a new DB handler to the specified database
	 * @param array $dbServerData keys: user; password; server; database; name; connectionString
	 * @param string $charSet charset of the DB connection
	 */
	function __construct($dbServerData, $charSet = 'utf8') {
		$this->charSet = $charSet;
		$this->dbServerData = $dbServerData;
		$this->connect();
	}

	/**
	 * Establishes the connection (creates PDO instance).
	 * Debug::send('DB Error', ...) called if error occures.
	 * @return boolean true on success, false on error
	 */
	function connect() {
		try {
			$this->db = new PDO(
				'mysql:host='.$this->dbServerData['server'].';dbname='.$this->dbServerData['database'].';charset='.$this->charSet,
				$this->dbServerData['user'],
				$this->dbServerData['password']
			);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->db->prepare("SET CHARACTER SET ?")->execute(array($this->charSet));
			//$this->db->prepare("SET NAME ?")->execute(array($this->charSet)); UNKNOWN SYSTEM VARIABLE 'NAME' ERRNO: HY000

			return true;
		} catch (PDOException $ex) {
			Debug::send('DB Error', array('errno' => $ex->getCode(), 'message' => $ex->getMessage(), 'trace' => $ex->getTrace()));
			return false;
		}
	}

	/**
	 * Imports the given variables into the SQL statements' $# placeholders, and exetutes the query.
	 * The # means the number of the parameter starting at value 1.
	 * @param string $sql the SQL statement with optional $# placeholders
	 * @param mixed $sqlParam optional param list to import into the SQL statements' $# placeholders
	 * @return \PDOStatement
	 */
	function query($sql, $sqlParam = null) {
		$oArgs = func_get_args();
		if (func_num_args() > 1) $sql = $this->buildSQL($sql, array_slice($oArgs, 1));
		return $this->runCommand($sql, 'query');
	}

	/**
	 * Executes a pure SQL statement.
	 * @param string $sql SQL statement (needs to be properly escaped)
	 * @param string $method type of SQL method for debug purposes
	 * @return \PDOStatement result set as PDOStatement (if any) or false
	 * @throws DBException if the execution fails
	 */
	private function runCommand($sql, $method='runCommand'){
		if (self::$debug === true) Debug::send('DB Query', $method, $sql);

		try { $result = $this->db->query($sql, PDO::FETCH_ASSOC); }
		catch (PDOException $ex) { throw new Exception($ex->getMessage(), $ex->getCode(), null, $sql, $ex); }

		return $result?$result:false;
	}

	/**
	 * An alias of getValue:
	 * Returns a field according to the given SQL statement. Can contain $# placeholders.
	 * @param string $sql the SQL statement with optional $# placeholders
	 * @param mixed $sqlParam optional param list to import into the SQL statements' $# placeholders
	 * @return mixed the value
	 */
	function getField($sql, $sqlParam = null){
		$oArgs = func_get_args();
		if (func_num_args() > 1) $sql = $this->buildSQL($sql, array_slice($oArgs, 1));
		return $this->getValue($sql);
	}

	/**
	 * Returns a field according to the given SQL statement. Can contain $# placeholders.
	 * @param string $sql the SQL statement with optional $# placeholders
	 * @param mixed $sqlParam optional param list to import into the SQL statements' $# placeholders
	 * @return mixed the value
	 */
	function getValue($sql, $sqlParam = null){
		$oArgs = func_get_args();
		if (func_num_args() > 1) $sql = $this->buildSQL($sql, array_slice($oArgs, 1));
		$row = $this->getRow($sql);
		if ($row) return reset($row);
		return null;
	}

	/**
	 * An alias of getFirstRow:
	 * Returns the first matching row according to the given SQL statement. Can contain $# placeholders
	 * @param string $sql the SQL statement with optional $# placeholders
	 * @param mixed $sqlParam optional param list to import into the SQL statements' $# placeholders
	 * @return array the row
	 */
	function getRow($sql, $sqlParam = null){
		$oArgs = func_get_args();
		if (func_num_args() > 1) $sql = $this->buildSQL($sql, array_slice($oArgs, 1));
		if (stripos($sql, ' LIMIT ') === false) $sql .= " LIMIT 1";
		return $this->getFirstRow($sql);
	}

	/**
	 * @param string $sql the SQL statement with optional $# placeholders
	 * @param mixed $sqlParam optional param list to import into the SQL statements' $# placeholders
	 * @return array the row
	 */
	function getFirstRow($sql){
		$oArgs = func_get_args();
		if (func_num_args() > 1) $sql = $this->buildSQL($sql, array_slice($oArgs, 1));
		if (!$result = $this->runCommand($sql, 'getRow')) return false;

		$row = $result->fetch(PDO::FETCH_ASSOC);
		$result->closeCursor();
		return $row;
	}

	/**
	 * Returns a row from the specified table having the specified id
	 * @param string $table
	 * @param unsigned $id
	 * @return array
	 */
	function getRowById($table, $id){
		$table = $this->escapeSQLEntity($table);
		$sql = "SELECT * FROM ".$table." WHERE id=".$this->quote($id);
		return $this->getFirstRow($sql);
	}

	/**
	 * An alias of getRows:
	 * Returns the complete result set as associative a pure PHP array. Can contain $# placeholders.
	 * @param string $sql the SQL statement with optional $# placeholders
	 * @param mixed $sqlParam optional param list to import into the SQL statements' $# placeholders
	 * @return array array of the records
	 */
	function getAll($sql, $sqlParam = null){
		$oArgs = func_get_args();
		if (func_num_args() > 1) $sql = $this->buildSQL($sql, array_slice($oArgs, 1));
		return $this->getRows($sql);
	}

	/**
	 * Returns the complete result set as associative a pure PHP array. Can contain $# placeholders.
	 * @param string $sql the SQL statement with optional $# placeholders
	 * @param mixed $sqlParam optional param list to import into the SQL statements' $# placeholders
	 * @return array array of the records or false on error
	 */
	function getRows($sql, $sqlParam = null){
		$oArgs = func_get_args();
		if (func_num_args() > 1) $sql = $this->buildSQL($sql, array_slice($oArgs, 1));

		$rows = array();
		if (!$result = $this->runCommand($sql, 'getAll')) return false;

		if ($result->rowCount()) {
			foreach ($result as $row) {
				if (array_key_exists('__KEY__', $row) && !array_key_exists('__VALUE__', $row)) {
					$key = $row['__KEY__'];
					unset($row['__KEY__']);
					$rows[$key] = $row;
				} else if (array_key_exists('__KEY__', $row) && array_key_exists('__VALUE__', $row)) {
					$rows[$row['__KEY__']] = $row['__VALUE__'];
				} else if (array_key_exists('__VALUE__', $row)) {
					$rows[] = $row['__VALUE__'];
				} else {
					$rows[] = $row;
				}
			}

			if ($row == null) foreach ($result as $row) $rows[] = $row;
			else if ($key != null && $value == null) foreach ($result as $row) $rows[$row[$key]] = $row;
			else if ($key != null && $value != null) foreach ($result as $row) $rows[$row[$key]] = $row[$value];
		}
		$result->closeCursor();
		return $rows;
	}

	/**
	 * Executes an INSERT SQL statement.
	 * @param string $tableName the name of the table
	 * @param array $data1 [!]fieldName => newValue pairs. If fieldName starts with ! and the value is not '' value left unescaped, if the value '' the inserted value will be NULL
	 * @return unsigned the inserted id or false on error or true on inserted id === 0
	 */
	function insert($tableName, $data1){
		$dataList = func_get_args(); array_shift($dataList);
		return $this->insertValues($tableName, $dataList, false);
	}

	/**
	 * Executes an INSERT IGNORE SQL statement.
	 * @param string $tableName the name of the table
	 * @param array $data1 [!]fieldName => newValue pairs. If fieldName starts with ! and the value is not '' value left unescaped, if the value '' the inserted value will be NULL
	 * @return unsigned the inserted id or false on error or true on inserted id === 0
	 */
	function insertIgnore($tableName, $data1){
		$dataList = func_get_args(); array_shift($dataList);
		return $this->insertValues($tableName, $dataList, true);
	}

	function insertValues($tableName, $arrayOfValues, $isIgnore) {
		$table = $this->escapeSQLEntity($tableName);

		$fields = array();

		$valueMatrix = array();
		foreach ($arrayOfValues as $data) {
			$values = array();

			if (!$fields) $fields = array_map(function ($row) { return ltrim($row, '!'); }, array_keys($data));
			else if (implode('', $fields) !== implode('', array_map(function ($row) { return ltrim($row, '!'); }, array_keys($data)))) throw new Exception ("Unidentical insertation field list.");

			while (list($key, $val) = each($data)){
				if (substr($key, 0, 1)=='!'){
					$key = substr($key, 1);
					array_push($values, strlen($val) == 0?'NULL':$val);
				} else {
					array_push($values, $this->quote($val));
				}
			}
			$valueMatrix[] = '('.implode(',', $values).')';
		}

		$sql = 'INSERT '.($isIgnore === true?'IGNORE':'').' INTO '.$table.' ('.implode(',', $this->escapeSQLEntities($fields)).') VALUES '.implode(', ', $valueMatrix);
		if (!$result = $this->runCommand($sql, 'insert ignore')) return false;
		$id = $this->db->lastInsertId();	// the important comment is above at insert method... some sources say when INSERT IGNORE does not insert any row lastInsertId gives still the next id... donno.

		if ($id === 0) return true;
		return $id;
	}

	/**
	 * Updates a table with the given data at the specified conditions.
	 * @param string $tableName the name of the table
	 * @param array $data [!]fieldName => newValue pairs. If fieldName starts with ! and the value is not '' value left unescaped, if the value '' the inserted value will be NULL
	 * @param mixed $id of the row or a WHERE statment (excluding WHERE)
	 * @param mixed $sqlParam optional param list to import into the WHERE statements' $# placeholders
	 * @return unsigned the number of the affected rows or false on error
	 */
	function update($tableName, $data, $id, $sqlParam=null){
		$table = $this->escapeSQLEntity($tableName);

		if (!trim($id)) return false;
		else if (is_numeric($id)) $where = "id=".$this->quote($id);
		else {
			$where = $id;
			$oArgs = func_get_args();
			if (func_num_args() > 3) $where = $this->buildSQL($where, array_slice($oArgs, 3));
		}

		$field_value_pairs = array();
		while (list($key, $val) = each($data)) {
			if ($key[0]=='!') {
				$val = (strlen($val) == 0?'NULL':$val);
				array_push($field_value_pairs, '`'.(substr($key, 1).'`='.$val));
			} else {
				array_push($field_value_pairs, '`'.$key.'`='.$this->quote($val));
			}
		}
		$sql = "UPDATE ".$table." SET ".implode(",", $field_value_pairs).(($where)?(" WHERE ".$where):(''));

		if (!$result = $this->runCommand($sql, 'update')) return false;
		return $result->rowCount();
	}

	/**
	 * Deletes the row having the given id from the specified table.
	 * @param string $tableName name of the table
	 * @param mixed $id of the row or a WHERE statment (excluding WHERE)
	 * @param mixed $sqlParam optional param list to import into the WHERE statements' $# placeholders
	 * @return unsigned the number of the affected rows or false on error
	 */
	function delete($tableName, $id, $sqlParam = null){
		$table = $this->escapeSQLEntity($tableName);

		if (is_numeric($id)) $where = " `id` = ".$this->quote($id);
		else if (is_string($id)) {
			$where = $id;
			$oArgs = func_get_args();
			if (func_num_args() > 2) $where = $this->buildSQL($where, array_slice($oArgs, 2));
		} else $where = $id;

		$where = Filter::Filter($where)->GetSql($this);
		if (!trim($where)) return false;

		$sql = "DELETE FROM ".$table." WHERE ".$where;
		if (!$result = $this->runCommand($sql, 'delete')) return false;
		return $result->rowCount();
	}

	/**
	 * Imports the given values into the SQL statements $# placeholders
	 * @param string $sql the SQL statement. Can have $# placeholders.
	 * @param mixed $args array of values to import or the first element of the values in the functions param list. Array values will be quoted and imploded with , characters
	 * @return string the built/translated SQL statement
	 */
	function buildSQL($sql, $args) {
		$oArgs = func_get_args();
		if (!is_array($args)) $args = array_slice($oArgs, 1);
		if ($args) {
			foreach($args as $key=>$value){
				if (is_array($value)) {
					$array = array();
					foreach ($value as $item) $array[] = $this->quote($item);
					$args[$key] = join(',', $array);
				} else $args[$key] = $this->quote($value);
			}
		}

		for ($i = count($args); $i > 0; --$i) $sql = str_replace('$'.$i, $args[$i - 1], $sql);

		return $sql;
	}

	// ESCAPE AND QUOTE FUNCTIONS

	/**
	 * Quotes the specified value.
	 * @param string $str value to quote
	 * @param boolean $addQuoteMarks if it's true result will be enclosed into ' (apos) characters
	 * @return string the quoted value or the string NULL if the $str === null
	 */
	function quote($str, $addQuoteMarks = true){
		return $str === null?'NULL':($addQuoteMarks?$this->db->quote($str):trim($this->db->quote($str), "'"));
	}

	/**
	 * Quotes the values of the given array
	 * @param array $array of values need to be quoted
	 * @param boolean $addQuoteMarks if it's true result elements will be enclosed into ' (apos) characters
	 * @return array array of the quoted elements. null elements are translated to NULL strings.
	 */
	function quoteArray($array, $addQuoteMarks = true){
		if ($array) foreach($array as $key => $value) $array[$key] = $this->quote($value, $addQuoteMarks);
		return $array;
	}

	/**
	 * Escapes a DB object/entity with ` (backtick) character
	 * @param string $string The entity needs to be quoted
	 * @return string The quoted object name
	 */
	function escapeSQLEntity($string){ return '`'.trim($string, '`').'`'; }
	function escapeSQLEntities(array $arrayOfStrings){
		foreach ($arrayOfStrings as $i => $string) $arrayOfStrings[$i] = '`'.trim($string, '`').'`';
		return $arrayOfStrings;
	}

	// END OF ESCAPE AND QUOTE FUNCTIONS



	// TRANSACTION HANDLING

	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
	 * Initiates a transaction
	 * @link http://php.net/manual/en/pdo.begintransaction.php
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	function beginTransaction() { return $this->db->beginTransaction(); }

	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
	 * Commits a transaction
	 * @link http://php.net/manual/en/pdo.commit.php
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	function commit() { return $this->db->commit(); }

	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
	 * Rolls back a transaction
	 * @link http://php.net/manual/en/pdo.rollback.php
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	function rollBack() { return $this->db->rollBack(); }

	/**
	 * (PHP 5 &gt;= 5.3.3, Bundled pdo_pgsql)<br/>
	 * Checks if inside a transaction
	 * @link http://php.net/manual/en/pdo.intransaction.php
	 * @return bool <b>TRUE</b> if a transaction is currently active, and <b>FALSE</b> if not.
	 */
	function inTransaction () { return $this->db->inTransaction(); }

	// END OF TRANSACTION HANDLING



	// STRUCTURE INFO AND MANIPULATIONS

	/**
	 * Returns the possible enum values of the speicified field
	 * @param string $tableName the name of the table
	 * @param string $field the name of the enum field
	 * @return array the enum options
	 */
	function getEnumValues($tableName, $field){
		$table = $this->escapeSQLEntity($tableName);

		$sql = "SHOW COLUMNS FROM $table LIKE ".$this->quote($field);
		$result = $this->query($sql);
		if (!$result) throw new DBException('error getting enum field ', 'cannotReadEnumOptions');
		$row = $result->fetch(PDO::FETCH_NUM);
		$regex = "/'(.*?)'/";
		preg_match_all($regex, $row[1], $enum_array);
		$enum_fields = $enum_array[1];
		return $enum_fields;
	}

	/**
	 * Creates or delets the specified table depending on the $condition param.
	 * @param boolean $condition true: creates the table; false: drops the table
	 * @param string $table the name of the table
	 * @param string $properties properties of the table to create with
	 */
	function toggleTable($condition, $table, $properties) {
		if ($condition) $this->addTable($table, $properties);
		else $this->delTable($table);
	}

	/**
	 * Renames the specified table
	 * @param string $from original name
	 * @param string $to new name
	 * @return boolean
	 */
	function renameTable($from, $to) {
		if ($this->tableExists($from) && (strtolower($from) == strtolower($to) || !$this->tableExists($to))) return $this->query("RENAME TABLE ".$this->escapeSQLEntity($from)." TO ".$this->escapeSQLEntity($to));
		return false;
	}

	/**
	 * Creates a new table
	 * @param string $table the name of the table
	 * @param string $properties the properties of the table to create with
	 */
	function addTable($table, $properties){
		$this->query("CREATE TABLE IF NOT EXISTS `".$table."` ".$properties);
	}

	/**
	 * Drops a table
	 * @param string $table the name of the table to drop
	 */
	function delTable($table){ $this->query("DROP TABLE IF EXISTS `".$table."`"); }

	/**
	 * Creates a new view
	 * @param string $view the name of the view
	 * @param string $select the select statement of the view to create
	 */
	function addView($view, $select){
		if (!$this->hasTable($view)) {
			$this->query("CREATE VIEW `".$view."` AS ".$select);
		}
	}
	
	/**
	 * Drops a view
	 * @param string $view the name of the view to drop
	 */
	function delView($view){ $this->query("DROP VIEW IF EXISTS `".$view."`"); }

	/**
	 * Returns the type of a table object (table or view)
	 * @param string $table name of the table
	 * @return string
	 */
	function getTableType($table){
		$result = $this->getFirstRow("SHOW FULL TABLES WHERE Tables_in_".$this->dbServerData['database']." = $1", $table);
		return $result['Table_type'];
	}

	/**
	 * Adds or drops a field in/from a table
	 * @param boolean $condition true: creates the field; false: drops the field
	 * @param string $table the name of the table
	 * @param string $field the name of the field
	 * @param string $properties properties of the field will be created
	 */
	function toggleField($condition, $table, $field, $properties){
		if ($condition) $this->addField($table, $field, $properties);
		else $this->delField($table, $field);
	}

	/**
	 * Adds a field to the specified table
	 * @param string $table the name of the table
	 * @param string $field the name of the field
	 * @param string $properties properties of the field will be created
	 */
	function addField($table, $field, $properties){
		if(!$this->hasField($table, $field)) $this->query("ALTER TABLE ".$this->escapeSQLEntity($table)." ADD ".$this->escapeSQLEntity($field)." ".$properties);
	}

	/**
	 * Drops the specified field from the specified table
	 * @param string $table the name of the table
	 * @param string $field the name of the field to drop
	 */
	function delField($table, $field){
		if($this->hasField($table, $field)) $this->query("ALTER TABLE ".$this->escapeSQLEntity($table)." DROP ".$this->escapeSQLEntity($field));
	}

	/**
	 * Returns the list of field names of the given table
	 * @param string $table
	 * @return array<string>
	 */
	function getFieldList($table) {
		$fieldData = $this->getFieldData($table);
		$fields = array();
		foreach ($fieldData as $field) $fields[] = $field['Field'];
		return $fields;
	}

	/**
	 * Returns the detailed information of fields of the given table
	 * @param string $table
	 * @return array<array<string>>
	 */
	function getFieldData($table) {
		return $this->getAll("SHOW FULL COLUMNS FROM ".$this->escapeSQLEntity($table));
	}

	/**
	 * An alias of hasTable
	 * Says that the database has a table named $table
	 * @param string $table name of the table
	 * @return boolean true: table exists; false: table does not exist
	 */
	function tableExists($table){ return $this->hasTable($table); }

	/**
	 * Says that the database has a table named $table
	 * @param string $table name of the table
	 * @return boolean true: table exists; false: table does not exist
	 */
	function hasTable($table){ return $this->getFirstRow("SHOW TABLES LIKE '".$table."'")?true:false; }

	/**
	 * An alias of hasField
	 * Says that the specified table has a field named $field
	 * @param string $table name of the table
	 * @param string $field name of the field
	 * @return boolean true: field exists; false: field does not exist
	 */
	function fieldExists($table, $field){ return $this->hasField($table, $field); }

	/**
	 * Says that the specified table has a field named $field
	 * @param string $table name of the table
	 * @param string $field name of the field
	 * @return boolean true: field exists; false: field does not exist
	 */
	function hasField($table, $field){ return $this->getFirstRow("SHOW FULL COLUMNS FROM `".$table."` WHERE Field = '".$field."'")?true:false; }

}