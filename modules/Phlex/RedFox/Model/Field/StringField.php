<?php namespace Phlex\RedFox\Model\Field;

use Phlex\RedFox\Model\Field;

class StringField extends Field {
	/** @var  integer */
	protected $maxLength;

	/**
	 * StringField constructor.
	 * @param $name
	 * @param $null
	 * @param $maxLength
	 */
	function __construct( $name, $null, $maxLength ) {
		parent::__construct( $name, $null );
		$this->maxLength = $maxLength;
	}

	protected function typevalidator( $value ) {
		if( !is_string( $value ) ) return 1;
		if( strlen( $value ) > $this->maxLength ) return 2;

		return 0;
	}

}