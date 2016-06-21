<?php namespace Phlex\RedFox\Model\Field;

use Phlex\RedFox\Model\Converter;
use Phlex\RedFox\Model\Field;

class JsonStringField extends StringField implements Converter {

	/**
	 * JsonStringField constructor.
	 * @param $stringField StringField
	 */
	function __construct( $stringField ) { parent::__construct( $stringField->name, $stringField->null, $stringField->maxLength ); }

	public function convertRead( $value ) { return json_decode( $value, true ); }

	public function convertWrite( $value ) { return json_encode( $value ); }

	protected function typevalidator( $value ) { return 0; }

}