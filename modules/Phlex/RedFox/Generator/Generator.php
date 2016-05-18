<?php namespace Phlex\RedFox\Generator;


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
		'time'       => array('name' => 'time'),
		'date'       => array('name' => 'date'),
		'datetime'   => array('name' => 'datetime'),
		'timestamp'  => array('name' => 'timestamp'),
		'enum'       => array('name' => 'enum'),
		'set'        => array('name' => 'set'),
	);

	public function add($database, $table, $entity = null) {

		if ($entity == null) $entity = $this->underscoreToCamelCase($table, true);

		$tableInfo = array(
				'entity' => $entity,
				'write'  => true
			) + $this->getTableInfo($database, $table);

		print_r($tableInfo);

		file_put_contents(getenv('root') . '/env/entities/' . $entity . '.json',
		                  preg_replace('/^    |\G    /m', "\t", json_encode($tableInfo, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES))
		);

	}

	private function getTableInfo($database, $table) {

		$dba = ResourceManager::db($database);

		$tableType = $dba->getValue("SELECT TABLE_TYPE FROM information_schema.tables WHERE TABLE_SCHEMA = $1 AND TABLE_NAME= $2 ", $dba->getDatabaseName(), $table);

		$tableInfo = array(
			"database" => $database,
			"table"    => $table,
			"type"     => $tableType == 'VIEW' ? 'view' : 'table'
		);

		$fieldlist = $dba->getAll("
			SELECT 
 				COLUMN_NAME,
 				IS_NULLABLE,
 				DATA_TYPE,
 				CHARACTER_MAXIMUM_LENGTH,
 				COLUMN_TYPE
 			FROM information_schema.columns WHERE TABLE_SCHEMA = $1 AND TABLE_NAME= $2 ", $dba->getDatabaseName(), $table);
		$fields = array();
		foreach ($fieldlist as $fieldInfo) {

			$field = array(
				'null'     => $fieldInfo['IS_NULLABLE'] == 'YES' ? true : false,
				'datatype' => $fieldInfo['DATA_TYPE']
			);

			if (array_key_exists($field['datatype'], $this->types)) {
				$type = $this->types[$field['datatype']];
				$field['type'] = $type['name'];
			} else {
				throw new \Exception('Invalid datatype');
			}

			switch ($type['name']) {
				case 'integer':
					if (stripos($fieldInfo['COLUMN_TYPE'], 'unsigned') !== false) {
						$field['min'] = 0;
						$field['max'] = $type['max'];
						$field['signed'] = false;
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
				case 'time':
				case 'datetime':
					break;
				case 'float':
					if (stripos($fieldInfo['COLUMN_TYPE'], 'unsigned') !== false) {
						$field['signed'] = false;
					} else {
						$field['signed'] = true;
					}
					break;
			}
			$fields[$fieldInfo['COLUMN_NAME']]["db"] = $field;
			if ($fieldInfo['COLUMN_NAME'] == 'id') {
				$fields[$fieldInfo['COLUMN_NAME']]["write"] = false;
			} else {
				$fields[$fieldInfo['COLUMN_NAME']]["write"] = true;
				if ($fields[$fieldInfo['COLUMN_NAME']]["db"]["datatype"] == 'int' && $fields[$fieldInfo['COLUMN_NAME']]["db"]["signed"] == false) {
					$fields[$fieldInfo['COLUMN_NAME']]["relation"] = null;
				}
			}
			if ($fields[$fieldInfo['COLUMN_NAME']]["db"]["type"] == 'string') {
				$fields[$fieldInfo['COLUMN_NAME']]["serialization"] = null;
			}

		}

		$tableInfo['fields'] = $fields;
		return $tableInfo;
	}

	private function underscoreToCamelCase($string, $first_char_caps = false) {
		if ($first_char_caps == true) {
			$string[0] = strtoupper($string[0]);
		}
		$func = create_function('$c', 'return strtoupper($c[1]);');
		return preg_replace_callback('/_([a-z])/', $func, $string);
	}
}
