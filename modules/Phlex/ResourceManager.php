<?php
namespace Phlex;
use Phlex\Database\Access;
use Phlex\Database\Exception;
use Phlex\Exception\GeneralException;


/**
 * Author: Laborci Gergely
 * Copyright: 365 Media Ltd. (www.365media.hu)
 * Created: 14/05/16 20:14
 */
class ResourceManager {

	static protected $databaseConnections = array();

	/**
	 * @param $name
	 * @return Access
	 */
	static function db($name){
		if(array_key_exists($name, static::$databaseConnections) === false){
			$env = Env\Environment::instance();
			if(!array_key_exists($name, $env['databases'])) throw new GeneralException('"'.$name.'" database not found in Environment', GeneralException::RESOURCE_DB_NOT_FOUND);
			static::$databaseConnections[$name] = new Access($env['databases'][$name]);
		} 
		return static::$databaseConnections[$name];
	}
}