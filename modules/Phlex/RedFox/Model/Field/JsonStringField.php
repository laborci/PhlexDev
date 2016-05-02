<?php

/**
 * Author: Laborci Gergely
 * Copyright: 365 Media Ltd. (www.365media.hu)
 * Created: 02/05/16 21:48
 */

namespace Phlex\RedFox\Model\Field;
use Phlex\RedFox\Model\Field;

class JsonStringField extends Field{
	protected $maxLenght;

	protected function typevalidator($value) {
		return 0;
	}

	protected function packValue($value) {
		return json_encode($value);
	}

	protected function unpackValue($value) {
		return json_decode($value, true);
	}


}