<?php namespace Phlex\RedFox\Model\Field;

use Phlex\RedFox\Model\Converter;
use Phlex\RedFox\Model\Field;

class DateTimeField extends Field implements Converter{

	protected function typevalidator($value) {
		return 0;
	}

	public function convertRead($value) {
		// TODO: Implement convertRead() method.
	}

	public function convertWrite($value) {
		// TODO: Implement convertWrite() method.
	}
}