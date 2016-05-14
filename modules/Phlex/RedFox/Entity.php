<?php namespace Phlex\RedFox;

use Phlex\RedFox\Model\Field;
use Phlex\RedFox\Model\Model;

abstract class Entity {

	public static function decorateModel($model){}

	/**
	 * @return Model
	 */
	public function model(){return null;}

	public function __get($propertyName) {
		
		if (method_exists($this, '__get' . ucfirst($propertyName))) {
			$methodName = '__get' . ucfirst($propertyName);
			return $this->$methodName();
		}

		$model = $this->model();
		if(property_exists($model, $propertyName)){
			$property = $model->$propertyName;
			if($model->$propertyName instanceof Relation){
				/** @var $property Relation */
				return $property->getRelatedObject($this);
			}

			if($model->$propertyName instanceof Field){
				/** @var $property Field */
				if( $property->access & Field::READ ) return $this->$propertyName;
			}
		}
		return null;
	}
	
	public function __set($propertyName, $value){
		if (method_exists($this, '__set' . ucfirst($propertyName))) {
			$methodName = '__set' . ucfirst($propertyName);
			$this->$methodName($value);
			return;
		}

		$model = $this->model();
		if(property_exists($model, $propertyName)){
			$property = $model->$propertyName;
			
			if($model->$propertyName instanceof Relation){
				trigger_error('HEY, RELATION CANT BE SET!');
			}

			if($model->$propertyName instanceof Field){
				/** @var $property Field */
				
				if( $property->access & Field::WRITE_ONCE ){
					if($this->$propertyName == null)	$this->$propertyName = $value;
					return;
				}
				if( $property->access & Field::WRITE ){
					$this->$propertyName = $value;
					return;
				}
			}
		}
		
	}
}