<?php namespace Phlex\RedFox;

abstract class Entity{
	protected static $model;

	/**
	 * @return Model
	 */
	public static function getModel(){
		if(static::$model === null) static::buildModel();
		return static::$model;
	}

	public static function polishModel($model){return $model;}

}