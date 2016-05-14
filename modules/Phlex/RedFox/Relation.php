<?php
/**
 * Author: Laborci Gergely
 * Copyright: 365 Media Ltd. (www.365media.hu)
 * Created: 14/05/16 22:45
 */

namespace Phlex\RedFox;


use Phlex\RedFox\Model\Field\IntegerField;

class Relation{
	/**
	 * @var EntityRepository
	 */
	protected $repository;
	protected $reference;

	/**
	 * Relation constructor.
	 *
	 * @param $reference IntegerField
	 * @param $repository EntityRepository
	 */
	function __construct($reference, $repository) {
		$this->repository = $repository;
		$this->reference = $reference->getFieldName();
	}

	/**
	 * @param $object
	 *
	 * @return mixed
	 */
	function getRelatedObject($object){
		$reference = $this->reference;
		return $this->repository->get($object->$reference);
	}
}