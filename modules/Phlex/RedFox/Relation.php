<?php
/**
 * Author: Laborci Gergely
 * Copyright: 365 Media Ltd. (www.365media.hu)
 * Created: 14/05/16 22:45
 */

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
	 * @param $reference  IntegerField
	 * @param $repository EntityRepository
	 */
	function __construct($name, $reference, $repository) {
		$this->repository = $repository;
		$this->reference = $reference;
	}

	/**
	 * Gets related object
	 * @param $object
	 * @return mixed
	 */
	function getRelatedObject($object) {
		$reference = $this->reference->getFieldName();
		return $this->repository->get($object->$reference);
	}

	/**
	 * Sets related object
	 * @param Entity $object
	 * @param Entity $related
	 */
	function setRelatedObject($object, $related) {
		if ($this->repository->checkInstance($related)) {
			$fieldName = $this->name;
			$reference = $this->reference->getFieldName();
			$object->$fieldName = $related;
			$object->$reference = $related->getId();
		}
	}
}