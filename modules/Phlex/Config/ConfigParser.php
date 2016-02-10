<?php namespace Phlex\Config;

class ConfigParser {

	protected $path, $server, $data, $valueParsers = array();

	function __construct($path) {
		$this->path = $path;
		$this->addValueParser(function($value){return ConfigParser::valueParserDBConn($value);});
	}

	function parse($server){
		$this->server = $server;
		$this->data = null;
		return $this->buildConfig();
	}

	function addValueParser($parser){
		$this->valueParsers[] = $parser;
	}

	protected function buildConfig(){
		$this->data = $this->loadJsonFragment('config.json');
		$this->loadIncludes();
		$this->parseCfg();
		return ($this->data);
	}

	protected function loadJsonFragment($fileName){
		$data = json_decode(file_get_contents($this->path.$fileName), true);
		if(file_exists($this->path.$this->server.'/'.$fileName)){
			$data_ext = json_decode(file_get_contents($this->path.$this->server.'/'.$fileName), true);
			$data = static::dataMerge($data, $data_ext);
		}
		return $data;
	}

	protected static function dataMerge( array &$array1, array &$array2 ){
		$merged = $array1;
		foreach ( $array2 as $key => &$value ){
			if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) ){
				$merged [$key] = static::dataMerge ( $merged [$key], $value );
			} else {
				$merged [$key] = $value;
			}
		}
		return $merged;
	}

	protected function loadIncludes(){
		foreach($this->data as $key => $value){
			if($key[0] == '@'){
				unset($this->data[$key]);
				$sub = self::loadJsonFragment($value);
				$this->data[substr($key,1)] = $sub;
			}
		}
	}

	protected function parseCfg(){
		$iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($this->data));
		$cfg = array();

		foreach ($iterator as $key => $value) {
			for ($i = $iterator->getDepth() - 1; $i >= 0; $i--) {
				$key = $iterator->getSubIterator($i)->key() . '.' . $key;
			}
			$cfg[$key] = $value;
		}

		foreach($cfg as $key=>$value) if(is_string($value)){
			if($count = preg_match_all('({{(.*?)}})', $value, $matches)) {
				for($i=0;$i<$count;$i++){
					$cfg[$key] = str_replace($matches[0][$i], $cfg[$matches[1][$i]], $value);
				}
			}

			foreach($this->valueParsers as $valueParser){
				$cfg[$key] = $valueParser($cfg[$key]);
			}

			$temp = &$this->data;
			$exploded = explode('.', $key);
			foreach($exploded as $segment) $temp = &$temp[$segment];
			$temp = $cfg[$key];
			unset($temp);
		}
	}

	static function valueParserDBConn($value){
		if(substr($value, 0, 4) == '@db:'){
			$pattern = "(([\w\d]*):(.*?)@([\w\d\.]*)\/([\w\d]*))";
			preg_match($pattern, trim($value), $matches);
			$database = Array(
				'user'     => $matches[1],
				'password' => $matches[2],
				'server'   => $matches[3],
				'database' => $matches[4],
			);
			return $database;
		}else return $value;
	}

}