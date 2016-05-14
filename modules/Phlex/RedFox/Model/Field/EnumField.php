<?php

/**
 * Author: Laborci Gergely
 * Copyright: 365 Media Ltd. (www.365media.hu)
 * Created: 02/05/16 21:48
 */

namespace Phlex\RedFox\Model\Field;
use Phlex\RedFox\Model\Field;

class EnumField extends Field{

	protected $options;

	public function __construct($name, $null, $options) {
		parent::__construct($name, $null);
		$this->options = $options;
	}


	protected function typevalidator($value) {

		if (!is_string($value)) return 1;
		if (!in_array($value, $this->options)) return 2;

		return 0;
	}

	function getOptions(){
		return $this->options;
	}


}