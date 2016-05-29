<?php namespace Phlex\RedFox\Model;

use Phlex\Database\Access;
use Phlex\ResourceManager;

abstract class Model {

	/**
	 * @return static
	 */
	public static function instance(){
		if(static::$__instance === null) static::$__instance = new static();
		return static::$__instance;
	}
}
