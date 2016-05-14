<?php

/**
 * Author: Laborci Gergely
 * Copyright: 365 Media Ltd. (www.365media.hu)
 * Created: 02/05/16 21:48
 */

namespace Phlex\RedFox\Model\Field;
use Phlex\RedFox\Model\Converter;
use Phlex\RedFox\Model\Field;

class JsonStringField extends StringField implements Converter{

	/**
	 * JsonStringField constructor.
	 *
	 * @param $stringField StringField
	 */
	function __construct($stringField) { parent::__construct($stringField->name, $stringField->null, $stringField->maxLength); }

	public function convertRead($value) {
		return json_decode($value, true);
	}

	public function convertWrite($value) {
		return json_encode($value);
	}

	public $aaaa = 1;

}