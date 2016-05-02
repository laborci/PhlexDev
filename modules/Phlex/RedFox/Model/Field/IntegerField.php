<?php

/**
 * Author: Laborci Gergely
 * Copyright: 365 Media Ltd. (www.365media.hu)
 * Created: 02/05/16 21:48
 */

namespace Phlex\RedFox\Model\Field;
use Phlex\RedFox\Model\Field;

class IntegerField extends Field{
	public $min;
	public $max;
	
	function typevalidator($value) {
		if( !is_numeric($value) ) return 1;
		if( $value<$this->min || $value>$this->max ) return 2;
		return 0;
	}

}