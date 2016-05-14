<?php
namespace Phlex;
use Phlex\Database\Access;

/**
 * Author: Laborci Gergely
 * Copyright: 365 Media Ltd. (www.365media.hu)
 * Created: 14/05/16 20:14
 */
class ResourceManager {

	static protected $databaseConnections = array();
	
	static function db($name){
		if(array_key_exists($name, static::$databaseConnections) === false){
			$env = Env\Environment::instance();
			static::$databaseConnections[$name] = new Access($env['database'][$name]);
		} 
		return static::$databaseConnections[$name];
	}
}