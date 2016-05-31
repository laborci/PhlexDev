<?php namespace Phlex\RedFox;


use Phlex\RedFox\Model\Converter;
use Phlex\RedFox\Model\Field;
use Phlex\RedFox\Model\Model;


abstract class Entity {

	/** @var EntityRepository */
	protected $_repository;
	
	/** @var integer */
	protected $id = null;

#region abstract methods
	
	/** @return Model */
	abstract public function getModel();

	#endregion
	
	public function __construct(EntityRepository $repository) {
		$this->_repository = $repository;
	}

	public function __toString() { return $this->id; }

#region data transfer

	/**
	 * Fills a blank object with data. Should not be used!
	 * @param array $data
	 * @return static
	 */
	public function _dataIn($data) {
		$model = $this->getModel();

		foreach ($data as $key => $value) {
			if (property_exists($model, $key) && $model->$key instanceof Field) {
				if ($model->$key instanceof Converter) {
					$this->$key = $model->$key->convertRead($value);
				} else {
					$this->$key = $value;
				}
			}
		}
	}

	/**
	 * Colects the object data to an array. Should not be used!
	 * @return array
	 */
	public function _dataOut() {
		$model = $this->getModel();
		$data = array();
		return $data;
	}

	#endregion

#region getters / setters

	/** @param \Phlex\RedFox\EntityRepository $repository */
	public function setRepository(EntityRepository $repository){ $this->_repository = $repository; }

	/** @return \Phlex\RedFox\EntityRepository */
	public function getRepository(){	return $this->_repository; }

	/** @return int */
	public function getId() { return $this->id; }

	// Common getters
	public function __get($propertyName) {

		if (method_exists($this, '__get' . ucfirst($propertyName))) {
			$methodName = '__get' . ucfirst($propertyName);
			return $this->$methodName();
		}

		$model = $this->getModel();
		if (property_exists($model, $propertyName)) {
			$property = $model->$propertyName;
			if ($propertyName instanceof Relation) {
				/** @var $property Relation */
				return $property->getRelatedObject($this);
			}
			if ($model->$propertyName instanceof Field) {
				/** @var $property Field */
				if ($property->access & Field::READ) return $this->$propertyName;
			}
		}
		return null;
	}

	// Common setter
	public function __set($propertyName, $value) {
		if (method_exists($this, '__set' . ucfirst($propertyName))) {
			$methodName = '__set' . ucfirst($propertyName);
			$this->$methodName($value);
			return;
		}
		$model = $this->getModel();
		if (property_exists($model, $propertyName)) {
			$property = $model->$propertyName;
			if ($propertyName instanceof Relation) {
				/** @var $property Relation */
				$property->setRelatedObject($this, $value);
				return;
			}
			if ($model->$propertyName instanceof Field) {
				/** @var $property Field */
				if ($property->access & Field::WRITE_ONCE) {
					if ($this->$propertyName == null) $this->$propertyName = $value;
					return;
				}
				if ($property->access & Field::WRITE) {
					$this->$propertyName = $value;
					return;
				}
			}
		}
	}

	#endregion
}