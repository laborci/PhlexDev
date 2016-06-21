<?php namespace Phlex\RedFox\Model\Field;

use Phlex\RedFox\Model\Field;

class FloatField extends Field{
	protected $min;
	protected $max;

	public function __construct($name, $null, $min, $max) {
		parent::__construct($name, $null);
		$this->min = $min;
		$this->max = $max;
	}

	public function setMax($max){
		if($max>=$this->min && $max<=$this->max) $this->max = $max;
	}
	public function setMin($min){
		if($min>=$this->min && $min<=$this->max) $this->min = $min;
	}


	function typevalidator($value) {
		if( !is_numeric($value) ) return 1;
		if( $value<$this->min || $value>$this->max ) return 2;
		return 0;
	}


}