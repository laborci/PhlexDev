<?php namespace Phlex\RedFox\Model\Field;

use Phlex\RedFox\Model\Converter;
use Phlex\RedFox\Model\Field;

class DateTimeField extends Field implements Converter{

	protected function typevalidator($value) {
		return 0;
	}

	/**
	 * @param $value string
	 * @return \DateTime
	 */
	public function convertRead($value) {
		return \DateTime::createFromFormat('Y-m-d H:i:s', $value);
	}

	/**
	 * @param $value \DateTime
	 * @return string
	 */
	public function convertWrite($value) {
		return $value->format('Y-m-d H:i:s');
	}
}