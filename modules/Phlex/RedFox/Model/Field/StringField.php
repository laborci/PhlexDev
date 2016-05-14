<?php

/**
 * Author: Laborci Gergely
 * Copyright: 365 Media Ltd. (www.365media.hu)
 * Created: 02/05/16 21:48
 */

namespace Phlex\RedFox\Model\Field;
use Phlex\RedFox\Model\Field;

class StringField extends Field{
	protected $maxLength;

	function __construct($name, $null, $maxLength) {
		parent::__construct($name, $null);
		$this->maxLength = $maxLength;
	}

	protected function typevalidator($value) {
		if(!is_string($value)) return 1;
		if(strlen($value)>$this->maxLenght) return 2;
		return 0;
	}

}