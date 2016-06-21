<?php
//TODO: this class seems unneccessary

namespace Phlex\RedFox;


use Phlex\RedFox\Model\Field\IntegerField;


class Relation {

	/**
	 * @var EntityRepository
	 */
	protected $repository;
	/**
	 * @var \Phlex\RedFox\Model\Field\IntegerField
	 */
	protected $reference;
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * Returns the field name
	 * @return string
	 */
	public function getFieldName() {
		return $this->name;
	}

	/**
	 * Relation constructor.
	 * @param $name
	 * @param $reference  IntegerField
	 * @param $repository EntityRepository
	 */
	function __construct($name, $reference, $repository) {
		$this->repository = $repository;
		$this->reference = $reference;
	}
}