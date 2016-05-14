<?php namespace Phlex\RedFox;

abstract class Entity {

	public static function decorateModel($model){}

	public function __get($propertyName) {
		if (method_exists($this, '__get' . ucfirst($propertyName))) {
			$methodName = '__get' . ucfirst($propertyName);
			return $this->$methodName();
		}
		return null;
	}


}