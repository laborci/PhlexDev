<?php namespace Phlex\RedFox\Model;

class Model implements \ArrayAccess{

	protected $fields = array();
	protected $database;
	protected $table;

	public function __construct($database, $table) {
		$this->database = $database;
		$this->table = $table;
	}

	public function offsetExists($offset) { return array_key_exists($offset, $this->fields); }
	public function offsetGet($offset) { return $this->fields[$offset]; }
	public function offsetSet($offset, $value) { $this->fields[$value]; }
	public function offsetUnset($offset) { unset($this->fields[$offset]); }

}
