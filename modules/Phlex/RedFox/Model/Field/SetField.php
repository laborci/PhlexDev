<?php namespace Phlex\RedFox\Model\Field;

use Phlex\RedFox\Model\Field;

class SetField extends Field {
	/** @var  string[] */
	protected $options;

	/**
	 * SetField constructor.
	 * @param $name string
	 * @param $null bool
	 * @param $options string[]
	 */
	public function __construct($name, $null, $options) {
		parent::__construct($name, $null);
		$this->options = $options;
	}


	/**
	 * @return string[]
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * @param array $value
	 * @return int
	 */
	protected function typevalidator($value) {
		if (!is_array($value)) return 1;
		if (!in_array($value, $this->options)) return 2;
		return 0;
	}


}