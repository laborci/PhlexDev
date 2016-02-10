<?php namespace Phlex\Kraft\Parser;

class Tag {

	public $tag;
	public $attributes;
	public $source;
	public $inner;
	public $namespace;
	public $type;

	function __construct($namespace='', $tag='tag', $type, $attributes=array(), $source=null, $inner = null){
		$this->namespace = $namespace;
		$this->tag = $tag;
		$this->attributes = $attributes;
		$this->source = $source;
		$this->inner = $inner;
		$this->type = $type;
	}

	function getAttributesString($defaults=array(), $ignore=array()){
		$attrs = array_merge($defaults, $this->attributes);
		if($ignore)foreach($ignore as $ignoreKey){
			unset($attrs[$ignoreKey]);
		}
		$attrsString = '';
		foreach($attrs as $key=>$value){
			$attrsString.= (' '.$key.($value === true ? '' : '="'.$value.'"'));
		}
		return trim($attrsString);
	}

}