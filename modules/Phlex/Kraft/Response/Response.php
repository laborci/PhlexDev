<?php namespace Phlex\Kraft\Response;

abstract class Response implements \ArrayAccess{

	protected $data = array();
	protected $parent;

	protected function __construct() { }

	public static function factory($data = array()){
		$response = new static();
		$response->data = $data;
		return $response;
	}

	public function __toString() { return $this->render(); }

	function answer(){ echo $this->render(); }

	function render(){
		ob_start();
		var_dump($this->data);
		return ob_get_clean();
	}

	public function setParent($parent){
		$this->parent = $parent;
		return $this;
	}

	public function offsetExists ( $offset ){ return array_key_exists($offset, $this->data); }
	public function offsetGet ( $offset ){ if($this->offsetExists($offset)) return $this->data[$offset]; else trigger_error('Response view data not set: "'.$offset.'"', E_USER_NOTICE); }
	public function offsetSet ( $offset , $value ){
		$this->data[$offset] = $value;
		if($value instanceof Response) $value->parent = $this;
	}
	public function offsetUnset ( $offset ){ unset($this->data[$offset]); }
}