<?php

/**
 * Author: Laborci Gergely
 * Copyright: 365 Media Ltd. (www.365media.hu)
 * Created: 02/05/16 21:48
 */

namespace Phlex\RedFox\Model\Field;
use Phlex\RedFox\Model\Converter;
use Phlex\RedFox\Model\Field;

class DateField extends Field implements Converter{
	protected function typevalidator($value){
		return 0;
	}


	public function convertRead($value) {
		// TODO: Implement convertRead() method.
	}

	public function convertWrite($value) {
		// TODO: Implement convertWrite() method.
	}
}