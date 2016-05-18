<?php namespace Phlex\RedFox;


use Phlex\RedFox\Model\Converter;
use Phlex\RedFox\Model\Field;
use Phlex\RedFox\Model\Model;


abstract class Entity {

	/** @var integer */
	protected $id = null;

	/**
	 * @return Model
	 */
	public function model() { return null; }

	/**
	 * @return int
	 */
	public function getId() { return $this->id; }

	/**
	 * @param array $data
	 * @return static
	 */
	public static function instantiate($data) {
		$instance = new static();
		$model = $instance->model();

		foreach ($data as $key => $value) {
			if (property_exists($model, $key) && $model->$key instanceof Field) {
				if ($model->$key instanceof Converter) {
					$instance->$key = $model->$key->convertRead($value);
				} else {
					$instance->$key = $value;
				}
			}
		}
		return $instance;
	}

	public static function store() {
		$data = array();

		return $data;
	}

	public function __get($propertyName) {

		if (method_exists($this, '__get' . ucfirst($propertyName))) {
			$methodName = '__get' . ucfirst($propertyName);
			return $this->$methodName();
		}

		$model = $this->model();
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

	public function __set($propertyName, $value) {
		if (method_exists($this, '__set' . ucfirst($propertyName))) {
			$methodName = '__set' . ucfirst($propertyName);
			$this->$methodName($value);
			return;
		}
		$model = $this->model();
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
}