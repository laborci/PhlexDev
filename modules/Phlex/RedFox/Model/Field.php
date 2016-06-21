<?php namespace Phlex\RedFox\Model;

abstract class Field {

	const PERM_READ = 1;
	const PERM_WRITE = 2;
	const PERM_READ_WRITE = 3;
	const PERM_WRITE_NULL = 4;
	const PERM_READ_WRITE_NULL = 5;

	/**  @var int */
	public $access = 0;

	/** @var string */
	protected $name;

	/** @var array  */
	protected $validators = array();

	/** @var bool  */
	protected $null;

	/** @var  mixed (even callable) */
	protected $default;

	/**
	 * Field constructor.
	 * @param $name string
	 * @param $null boolean
	 */
	public function __construct($name, $null) {
		$this->name = $name;
		$this->null = $null;
	}

	public function getFieldName() { return $this->name; }

	public function setDefault($value){ $this->default = $value; }

	public function addValidator($validator){ $this->validators[] = $validator; }

	protected function validate($value){
		if($value === null) return $this->null;
		$this->typevalidator($value);
		// TODO: call all the validators
	}

	/**
	 * Makes some basic validation
	 * @param $value
	 * @return integer 0 stands for valid, anything else means error
	 */
	abstract protected function typevalidator($value);
}