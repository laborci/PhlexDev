<?php namespace Phlex\Config;

class Environment implements \ArrayAccess{

	protected $env;

	protected static $instance = null;
	public static function instance(){
		if(static::$instance === null) static::$instance = new static();
		return static::$instance;
	}

	protected function __construct() {
		$path = getenv('root') . '/.conf/';
		$this->env = $this->loadCfg($path.'config.php');
	}

	protected function loadCfg($cfg){
		return include $cfg;
	}

	public function offsetExists ( $offset ){ return array_key_exists($offset, $this->env); }
	public function offsetGet ( $offset ){ if($this->offsetExists($offset)) return $this->env[$offset]; else return null; }
	public function offsetSet ( $offset , $value ){ }
	public function offsetUnset ( $offset ){ }



}