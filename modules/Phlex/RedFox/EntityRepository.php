<?php
/**
 * Author: Laborci Gergely
 * Copyright: 365 Media Ltd. (www.365media.hu)
 * Created: 14/05/16 22:23
 */

namespace Phlex\RedFox;


use Phlex\Database\Access;
use Phlex\ResourceManager;

abstract class EntityRepository {



	/**
	 * @var Access
	 */
	private $DBAccess;
	protected function __construct() {
		$this->db = ResourceManager::db(static::$database);
	}

	/**
	 * @return \Phlex\Database\Access
	 */
	function getDBAccess(){
		return $this->DBAccess;
	}


	protected static $__instance;

	/**
	 * @return static
	 */
	public static function instance() {
		if (static::$__instance === null) static::$__instance = new static();
		return static::$__instance;
	}

	/**
	 * @param $id
	 *
	 * @return static
	 */
	public function get($id) {

	}
}