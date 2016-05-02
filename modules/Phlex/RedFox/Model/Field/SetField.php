<?php

/**
 * Author: Laborci Gergely
 * Copyright: 365 Media Ltd. (www.365media.hu)
 * Created: 02/05/16 21:48
 */

namespace Phlex\RedFox\Model\Field;
use Phlex\RedFox\Model\Field;

class SetField extends Field{
	protected $options;

	/**
	 * @param array $value
	 *
	 * @return int
	 */
	protected function typevalidator($value) {
		if(!is_array($value)) return 1;
		if(!in_array($value, $this->options)) return 2;
		return 0;
	}


}