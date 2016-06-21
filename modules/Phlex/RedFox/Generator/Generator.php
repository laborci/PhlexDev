<?php namespace Phlex\RedFox\Generator;


use Phlex\Database\Exception;
use Phlex\ResourceManager;


class Generator {

	protected $types = array(
		'tinyint'    => array('name' => 'integer', 'max' => 255),
		'smallint'   => array('name' => 'integer', 'max' => 65535),
		'mediumint'  => array('name' => 'integer', 'max' => 16777215),
		'int'        => array('name' => 'integer', 'max' => 4294967295),
		'bigint'     => array('name' => 'integer', 'max' => 18446744073709551615),
		'char'       => array('name' => 'string'),
		'varchar'    => array('name' => 'string'),
		'tinytext'   => array('name' => 'string'),
		'text'       => array('name' => 'string'),
		'mediumtext' => array('name' => 'string'),
		'longtext'   => array('name' => 'string'),
		'float'      => array('name' => 'float'),
		'double'     => array('name' => 'float'),
		'date'       => array('name' => 'date'),
		'datetime'   => array('name' => 'datetime'),
		'timestamp'  => array('name' => 'timestamp'),
		'enum'       => array('name' => 'enum'),
		'set'        => array('name' => 'set'),
	);

	public function add($database, $table, $entity = null) {
		if($entity == null) $entity = $this->underscoreToCamelCase($table, true);
		$infoFile = getenv('root') . '/env/entities/' . $entity . '.info.json';
		file_put_contents(
			$infoFile,
			preg_replace('/^    |\G    /m', "\t", json_encode(
				array(
					"database-alias" => $database,
					"table" => $table
				), JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES))
		);

		$this->refreshEntity($entity);

	}

	protected function refreshEntity($entity) {
		$infoFile = getenv('root') . '/env/entities/' . $entity . '.info.json';
		$entityFile = getenv('root') . '/env/entities/' . $entity . '.json';

		$basicInfo = json_decode(file_get_contents($infoFile), true);
		$table = $basicInfo['table'];
		$database = $basicInfo['database-alias'];

		$tableInfo = $this->getTableInfo($database, $table);

		file_put_contents(
			$infoFile,
			preg_replace('/^    |\G    /m', "\t", json_encode($tableInfo, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES))
		);

		if(!file_exists($entityFile)) {
			file_put_contents(
				$entityFile,
				preg_replace('/^    |\G    /m', "\t", json_encode(
					array(
						"?"      => "",
						"insert" => true,
						"update" => true,
						"delete" => true,
						"fields" => array()
					), JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES))
			);
		}

		$entityInfo = json_decode(file_get_contents($entityFile), true);
		$error = json_last_error();
		if($error !== JSON_ERROR_NONE){
			echo "JSON ERROR while parsing file: ".$entityFile."\n";
			die();
		}
		$entityInfo['?'] = "Entity " . $entity . " on " . $tableInfo['database'] . "(" . $tableInfo['database-alias'] . ") / " . $tableInfo['table'] . " " . $tableInfo['type'];

		foreach($tableInfo['fields'] as $name => $field) if($name != 'id') {
			$entityInfo['fields'][$name]['?'] =
				$field['columntype'] .
				((array_key_exists('reference', $field)) ? (' -> ' . $field['reference']['database'] . '/' . $field['reference']['table'] . '/' . $field['reference']['field']) : ('')) .
				' as ';
			switch($field['type']) {
				case 'integer':
					$entityInfo['fields'][$name]['?'] .= $field['type'] . '(' . $field['min'] . '-' . $field['max'] . ')';
					break;
				case 'string':
					$entityInfo['fields'][$name]['?'] .= $field['type'] . '(' . $field['maxLength'] . ')';
					break;
				default:
					$entityInfo['fields'][$name]['?'] .= $field['type'];
					break;
			}
			if(!array_key_exists('access', $entityInfo['fields'][$name])) $entityInfo['fields'][$name]['access'] = 'public';
			if(array_key_exists('reference', $field) && !array_key_exists('reference', $entityInfo['fields'][$name])) {
				$entityInfo['fields'][$name]['reference'] = '? ' . ucfirst($field['reference']['table'] . ' as ' . ((substr($name, -2) == 'Id') ? (substr($name, 0, -2)) : ('refField')));
			}
			if($field['type'] == 'string' && !array_key_exists('serialization', $entityInfo['fields'][$name])) {
				$entityInfo['fields'][$name]['serialization'] = false;
			}
			if(!array_key_exists('default', $entityInfo['fields'][$name])) {
				$entityInfo['fields'][$name]['default'] = null;
			}
		}

		file_put_contents(
			$entityFile,
			preg_replace('/^    |\G    /m', "\t", json_encode($entityInfo, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES))
		);

	}

	public function refresh() {
		$path = getenv('root') . '/env/entities/';
		$cwd = getcwd();
		chdir($path);
		$files = glob('*.info.json');
		chdir($cwd);
		foreach($files as $file){
			$entity = substr($file, 0, -10);
			$checksum = md5_file($path.$entity.'.json');
			$this->refreshEntity($entity);
			if($checksum != md5_file($path.$entity.'.json')){
				echo '- '.$entity.".json changed\n";
			}
		}
	}

	public function generate() {
		$path = getenv('root') . '/env/entities/';
		$cwd = getcwd();
		chdir($path);
		$files = glob('*.info.json');
		chdir($cwd);
		foreach($files as $file){
			$entity = substr($file, 0, -10);
			$this->refreshEntity($entity);
			EntityGenerator::generate($entity);
		}
	}

	private function getTableInfo($database, $table) {

		$dbAccess = ResourceManager::db($database);

		$tableType = $dbAccess->getValue("SELECT TABLE_TYPE FROM information_schema.tables WHERE TABLE_SCHEMA = $1 AND TABLE_NAME= $2 ", $dbAccess->getDatabaseName(), $table);

		$tableInfo = array(
			"?"              => "Do not modify this file!",
			"database-alias" => $database,
			"database"       => $dbAccess->getDatabaseName(),
			"table"          => $table,
			"type"           => $tableType == 'VIEW' ? 'view' : 'table',
		);

		$fieldlist = $dbAccess->getAll("
			SELECT 
 				COLUMN_NAME,
 				IS_NULLABLE,
 				DATA_TYPE,
 				CHARACTER_MAXIMUM_LENGTH,
 				COLUMN_TYPE
 			FROM information_schema.columns WHERE TABLE_SCHEMA = $1 AND TABLE_NAME= $2 ", $dbAccess->getDatabaseName(), $table);
		$fields = array();
		foreach($fieldlist as $fieldInfo) {

			$field = array(
				'null'       => $fieldInfo['IS_NULLABLE'] == 'YES' ? true : false,
				'datatype'   => $fieldInfo['DATA_TYPE'],
				'columntype' => $fieldInfo['COLUMN_TYPE']
			);

			if(array_key_exists($field['datatype'], $this->types)) {
				$type = $this->types[$field['datatype']];
				$field['type'] = $type['name'];
			} else {
				throw new \Exception('Invalid datatype');
			}

			switch($type['name']) {
				case 'integer':
					if(stripos($fieldInfo['COLUMN_TYPE'], 'unsigned') !== false) {
						$field['min'] = 0;
						$field['max'] = $type['max'];
						$field['signed'] = false;
						$ref = $dbAccess->getRow(
							"
							SELECT
				            REFERENCED_TABLE_SCHEMA as 'database',
				            REFERENCED_TABLE_NAME as 'table',
				            REFERENCED_COLUMN_NAME as 'field'
				         FROM information_schema.key_column_usage WHERE
				         	TABLE_SCHEMA = $1 AND
				         	TABLE_NAME = $2 AND
				         	COLUMN_NAME = $3 AND
				         	REFERENCED_COLUMN_NAME IS NOT NULL", $dbAccess->getDatabaseName(), $table, $fieldInfo['COLUMN_NAME']);
						if($ref) {
							$field['reference'] = $ref;
						}
					} else {
						$field['min'] = ($type['max'] + 1) / -2;
						$field['max'] = $type['max'] + $field['min'];
						$field['signed'] = true;
					}
					break;
				case 'string':
					$field['maxLength'] = $fieldInfo['CHARACTER_MAXIMUM_LENGTH'];
					break;
				case 'enum':
				case 'set':
					$regex = "/'(.*?)'/";
					preg_match_all($regex, $fieldInfo['COLUMN_TYPE'], $enum_array);
					$field['options'] = $enum_array[1];
					break;
				case 'date':
				case 'datetime':
					break;
				case 'float':
					if(stripos($fieldInfo['COLUMN_TYPE'], 'unsigned') !== false) {
						$field['signed'] = false;
					} else {
						$field['signed'] = true;
					}
					break;
				default:
					trigger_error("Unsupported data type", E_USER_ERROR);
					break;
			}
			$fields[$fieldInfo['COLUMN_NAME']] = $field;

		}

		$tableInfo['fields'] = $fields;

		return $tableInfo;
	}

	private function underscoreToCamelCase($string, $first_char_caps = false) {
		if($first_char_caps == true) $string[0] = strtoupper($string[0]);
		$func = create_function('$c', 'return strtoupper($c[1]);');

		return preg_replace_callback('/_([a-z])/', $func, $string);
	}

}
