<?php
/**
 * Author: Laborci Gergely
 * Copyright: 365 Media Ltd. (www.365media.hu)
 * Created: 14/05/16 22:23
 */

namespace Phlex\RedFox;


abstract class EntityRepository {
	/**
	 * @return static
	 */
	public static function instance(){
		if(static::$__instance === null) static::$__instance = new static();
		return static::$__instance;
	}
	/**
	 * @param $id
	 *
	 * @return static
	 */
	public function get($id){
		return new static();
	}
}