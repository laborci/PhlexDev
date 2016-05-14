<?php
/**
 * Author: Laborci Gergely
 * Copyright: 365 Media Ltd. (www.365media.hu)
 * Created: 02/05/16 21:46
 */

namespace Phlex\RedFox\Model;


abstract class Field {

	const READ = 1;
	const WRITE = 2;
	const WRITE_ONCE = 4;

	public $access = 0;

	protected $name; // db field name
	protected $validators = array();
	protected $null; // null allowed

	protected $default; // can be scalar or callable

	public function setDefault($value){
		$this->default = $value;
	}

	public function addValidator($validator){
		$this->validators[] = $validator;
	}

	protected function validate($value){
		if($value === null) return $this->null;
		$this->typevalidator($value);
		// TODO: call all the validators
	}

	abstract protected function typevalidator($value);
	
	protected function packValue($value) { return $value; }
	protected function unpackValue($value) { return $value; }

	public function __construct($name, $null) {
		$this->name = $name;
		$this->null = $null;
	}

	/*public function __get($varName) {
		switch ($varName) {
			case 'name':
				return $this->name;
				break;
		}
	}*/
}