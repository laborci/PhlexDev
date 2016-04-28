<?php namespace Phlex\RedFox

/**
 * Adatbázis view-n vagy táblán alapuló ORM osztály.
 * Az adatok mentését nem valósítja meg
 * @package Entity
 */
abstract class EntityView{

	/**
	 * Az objektumok ID alapú lekérdezésekor ebbe a tömmbe saját cache-t generál
	 * @var array
	 */
	protected static $objectCache = array();

	/**
	 * Elmenti az adott objektumot adatbázisba
	 * - az objektum id mezője alapján automatikusan hívja az update metódust
	 * @return int the saved objects id
	 */
	public function save(&$errors = array()) { return $this->update(); }

	/**
	 * Módosítja az objektumot az adatbázisban
	 * @return bool|string
	 */
	protected function update(){
		if (!$this->id) return false;
		$data = $this->getFields();
		unset($data['id']);	// TODO: constanct mező lesz, szóval idővel ez a sor kivehető!
		foreach (static::$model['fields'] as $fieldName => $field) if ($field['visibility'] == 'constant' || $field['visibility'] == 'privateConstant') unset($data[$fieldName]);
		$rows = static::$db->update(static::$model['table'], $data, intval($this->id));
		if ($rows === false) return false;
		static::memcacheDelete($this->id);
		new ModelUpdated(static::$modelName, $this->id, $this);
		return $this->id;
	}

	public function __toString() {
		$field = static::$model['toStringField'];
		return $this->$field;
	}

	public static function getList($valueField = null, $filter = null) {
		if ($valueField === null) $valueField = static::$model['toStringField'];

		if (is_array($valueField)) {
			$valueField = implode('`, `', $valueField);
			$request = static::select("id AS __KEY__, `".$valueField."` FROM `".static::$model['table']."` ");
		} else $request = static::select("id AS __KEY__, `".$valueField."` AS __VALUE__ FROM `".static::$model['table']."`");

		$request->Where(DBFilter::Filter($filter));
		$request->Asc("`".$valueField."`");
		return $request->GetData();
	}


	/**
	 * @ignore
	 */
	public static function staticInitialize(){
		if(get_called_class() != 'Entity' && get_called_class() != 'EntityView'){
			static::$db = Resource::db(static::$model['database']);
			static::$mc = static::$model['memcache'] ? Resource::mc(static::$model['memcache']) : false;
		}
	}

	/**
	 * A Language translate szolgáltatását hívja
	 * @param $fieldName
	 * @param $value
	 * @return string
	 */
	private static function translateField($fieldName, $value){
		if(trim($value) === '') return '';
		return Language::translate(static::$modelName.'_'.$fieldName.'_'.$value, $value);
	}

	public function __call($method, $args){
		if($method == 'translateField'){
			$fieldName = $args[0];
			return static::translateField($fieldName, $this->$fieldName);
		}elseif(substr($method, 0, 9) == 'translate' and strlen($method)>9){
			$fieldName = lcfirst(substr($method, 9));
			return static::translateField($fieldName, $this->$fieldName);
		}else{
			throw new Exception("Call undefined public method ".$method." of class ".get_called_class());
		}
	}

	public static function __callStatic($method, $args){
		if($method == 'translateField'){ // translateField
			return static::translateField($args[0], $args[1]);
		}if(substr($method, 0, 9) == 'translate' and strlen($method)>9){ // translate
			$fieldName = lcfirst(substr($method, 9));
			return static::translateField($fieldName, $args[0]);
		}else	if(substr($method, 0, 8) == 'getAllBy'){ // getAllBy
			$fieldName = lcfirst(substr($method, 8));
			if(array_key_exists($fieldName, static::$model['fields'])){
				$items = static::getAll("SELECT * FROM `".static::$model['table']."` WHERE ".$fieldName.'=$1', $args[0]);
				return $items;
			}else{
				throw new Exception("Call for magic method getAllBy for undefined field: ".$fieldName." of class ".get_called_class());
			}
		}elseif(substr($method, 0, 5) == 'getBy'){ // getBy
			$fieldName = lcfirst(substr($method, 5));
			if(array_key_exists($fieldName, static::$model['fields'])){
				$item = static::get("SELECT * FROM `".static::$model['table']."` WHERE ".static::$db->escapeSQLEntity($fieldName).'=$1', $args[0]);
				return $item;
			}else{
				throw new Exception("Call for magic method getBy for undefined field: ".$fieldName." of class ".get_called_class());
			}
		}else{
			throw new Exception("Call undefined static method ".$method." of class ".get_called_class());
		}
	}

	function __set($propertyName, $value){
		// standard setter
		if (method_exists($this, '__set'.ucfirst($propertyName))){
			$methodName = '__set'.ucfirst($propertyName);
			static::$methodName($value);
		}else if(!array_key_exists($propertyName, static::$model['fields']) || static::$model['fields'][$propertyName]['visibility'] == 'public') $this->$propertyName = $value; // undefined property setter
	}
	function __get($propertyName){
		if (!$propertyName) throw new InvalidArgumentException('EntityView::__get what?');

		// standard getter
		if (method_exists($this, '__get'.ucfirst($propertyName))){
			$methodName = '__get'.ucfirst($propertyName);
			return $this->$methodName();
		}
		// foreignKey object getter
		if (!property_exists($this, $propertyName) && property_exists($this, $propertyName.'Id')){
			$class = '';
			if ($propertyName == 'parent') $class = get_called_class();
			else if (isset(static::$model['fields'][$propertyName.'Id']['foreignKey'])) {
				$class = static::$model['fields'][$propertyName.'Id']['foreignKey'] === true?ucfirst($propertyName):static::$model['fields'][$propertyName.'Id']['foreignKey'];
			} else if (class_exists(ucfirst($propertyName), true)) $class = ucfirst($propertyName);

			if ($class) {
				$idField = $propertyName.'Id';
				$this->$propertyName = $this->$idField?$class::get($this->$idField):null;
				return $this->$propertyName;
			}
		}

		if (strpos($propertyName, 'translate') === 0 && property_exists($this, $prop = lcfirst(substr($propertyName, 9)))) {
			return static::translateField($prop, $this->$prop);
		}
		// no default return
		//return $this->$propertyName;
	}

	public static function validator(){ return (is_object(static::$__validator)) ? static::$__validator : static::$__validator = static::constructValidator(); }
	protected static function constructValidator(){ return new PropertyValidator(); }
	public function validate(&$errors = array()){
		if(!is_array($errors)) $errors = array();
		$isValid = true;
		$isValid = static::validator()->testAll($this, $errors) and $isValid;
		if(func_num_args()>1){
			$validators = func_get_args();
			array_shift($validators);
			foreach ($validators as $validator) {
				$isValid = $validator->testAll($this, $errors) and $isValid;
			}
		}
		return $isValid;
	}

	/**
	 * Készít egy új entitás objektumot.
	 * Amennyiben egy tömbben kulcs érték párokat adunk át, az új objektum mezőit azokkal feltölti
	 * @param $record array $param (optional) db record
	 * @return Entity
	 */
	public static function create(array $record = null){
		$object = new static();
		$object->id = null;
		if($record !== null) $object->setFields($record);
		return $object;
	}

	/**
	 * Új entitás objektumok tömbjét hozza létre, az átadott rekordok alapján
	 * @param array $param db recordset
	 * @return array<Entity>
	 */
	public static function createAll(array $arrayOfRecords){
		$objects = array();
		if($arrayOfRecords) foreach($arrayOfRecords as $key => $record) $objects[$key] = static::create($record);
		return $objects;
	}

	/**
	 * Beállítja az objektum tulajdonságait a megadott asszociatív tömbben érkező értékekkel
	 * @param array $record
	 */
	public function setProperties($record){foreach($record as $key=>$value) $this->__set($key, $value);}

	/**
	 * Visszaadja az objektumot tömbbé konvertálva (get_object_vars)
	 * @return array
	 */
	public function getProperties($allowProtected = false){
		if ($allowProtected === true) return get_object_vars($this);
		else return $array = (array)$this;
	}

	/**
	 * Visszaadja az objektum mezőit
	 * @return array
	 */
	public function getRecord(){
		$record = array();
		foreach(static::$model['fields'] as $fieldName=>$fieldProperties){
			if(property_exists ($this, $fieldName)) $record[$fieldName] = $this->$fieldName;
			else $record[$fieldName] = null;
		}
		return $record;
	}

	/**
	 * Visszaadja az objektum mezőit.
	 * A serializálandó mezőket sorosítja, így azok egyből adatbázisba menthetőek.
	 * @return array
	 */
	protected function getFields(){
		$record = array();
		foreach(static::$model['fields'] as $fieldName=>$fieldProperties) if(property_exists($this, $fieldName)){
			if($fieldProperties['serialize'] && $fieldProperties['serialize'] != 'none')
				switch ($fieldProperties['serialize']){
					case 'php': $record[$fieldName] = serialize($this->$fieldName); break;
					case 'json': $record[$fieldName] = json_encode($this->$fieldName); break;
					case 'list': $record[$fieldName] = self::serializeList($this->$fieldName); break;
					case 'assoclist': $record[$fieldName] = self::serializeAssocList($this->$fieldName); break;
				}else $record[$fieldName] = $this->$fieldName;
		}
		return $record;
	}

	/**
	 * Feltölti egy tömb alapján az objektum mezőit.
	 * Csak és kizárólag az Entitásban definiált mezőket tölti fel.
	 * A feltöltés közben a serializált mezőket kicsomagolja.
	 * @param $record
	 */
	protected function setFields($record){
		foreach($record as $key=>$value){
			if (array_key_exists($key, static::$model['fields']) and static::$model['fields'][$key]['serialize'] and static::$model['fields'][$key]['serialize'] != 'none') {
				switch (static::$model['fields'][$key]['serialize']) {
					case 'php': $this->$key = unserialize($value);
						break;
					case 'json': $this->$key = json_decode($value, true);
						break;
					case 'list': $this->$key = self::unserializeList($value);
						break;
					case 'assoclist': $this->$key = self::unserializeAssocList($value);
						break;
					default: $this->$key = $value; break;
				}
			} else {
				$this->$key = $value;
			}
		}
	}

	/**
	 * A megadott kulcsú elemhez létrehoz egy Univerzális azonosítót
	 * @param $id
	 * @return string
	 */
	protected static function getUid($id){
		return get_called_class().'/'.$id;
	}

	/**
	 * Visszaad egy Univerzális azonosítót (EntityClassName/ID) pl: Article/123
	 * @return string
	 */
	public function uid(){
		return get_called_class().'/'.$this->id;
	}

	/**
	 * Univerzális azonosító alapján létrehoz egy objektumot
	 * @param $uid
	 * @param $entityClass
	 * @return null
	 */
	public static function getByUid($uid, &$entityClass) {
		list($className, $id) = explode('/', $uid);
		if($className and $id and is_numeric($id) and class_exists($className)){
			$entityClass = $className;
			return $className::get($id);
		}
		return null;
	}

	/**
	 * Betölt egy objektumot ID vagy SQL alapján
	 * @param mixed $sql numeric id or SQL
	 * @param mixed $sqlParams SQL parameters ...
	 * @return Entity
	 */
	public static function get($sql, $sqlParams = null){
		if(!is_numeric($sql)){
			if($sqlParams !== null){
				$sqlParams = func_get_args(); array_shift($sqlParams);
				$sql = static::insertSqlParams($sql, $sqlParams);
			}
			if($record = static::getData($sql))	return static::create($record);
			else return null;
		}else{
			$id = $sql;
			if(is_array(self::$objectCache) and static::$objectCachable and array_key_exists(static::getUid($id), self::$objectCache)) return self::$objectCache[static::getUid($id)];

			if($record = static::getData($id)){
				$object = static::create($record);
				if(is_array(self::$objectCache) and static::$objectCachable) self::$objectCache[static::getUid($id)] = $object;
				return $object;
			}
			else return null;
		}
	}

	/**
	 * Betölt objektumokat az adatbázisból
	 * Amennyiben az első paraméter tömbb, azt id kulcsokként kezeli és úgy próbálja meg betölteni az objektumokat
	 * @param mixed $sql id array or SQL
	 * @param mixed $sqlParams SQL parameters...
	 * @return array<Entity>
	 */
	public static function getAll($sql = null, $sqlParams = null){

		if(is_array($sql)){ $ids = $sql;
			if(!count($ids)) return array();
			$ids = array_unique($ids);
			$objects = array();
			$idsForSql = array();
			foreach($ids as $id) if(is_numeric($id)){
				if(is_array(self::$objectCache) && static::$objectCachable && array_key_exists(static::getUid($id), self::$objectCache)) $objects[$id] = self::$objectCache[static::getUid($id)];
				else $idsForSql[] = $id;
			}
			if ($idsForSql) {
				$records = static::getAllData($idsForSql);
				if ($records) {
					foreach ($records as $record) {
						$object = static::create($record);
						$objects[$object->id] = $object;
						self::$objectCache[static::getUid($object->id)] = $object;
					}
				}
			}
			return $objects;
		}

		if(is_string($sql) and $sqlParams !== null){
			$sqlParams = func_get_args(); array_shift($sqlParams);
			$sql = static::insertSqlParams($sql, $sqlParams);
		}
		return static::createAll(static::getAllData($sql));
	}


	/// GET RELATED

	/**
	 * Visszaadja azokat az objektumokat, melyek ID-ja szerepel a paraméterként
	 * átvett elemek a relationKey tulajdonságában, vagy tömb indexén.
	 * @param array<Entity, Array> $array elemek
	 * @param string $relationKey kapcsolat kulcs
	 * @return array<Entity>
	 */
	public static function getRelatedTo($objectArray, $relationKey){

		if(is_object($objectArray))$objectArray = array($objectArray);
		$records = static::getRelatedDataTo($objectArray, $relationKey);
		$objects = array();
		foreach($records as $key => $record){
			$objects[$key] = static::create($record);
		}

		return $objects;
	}

	/**
	 * Legyűjti azokat az objektumokat, melyek ID-ja szerepel a paraméterként
	 * átvett elemek a relationKey tulajdonságában és bővíti azokkal az elemeket az
	 * insertionKey tulajdonságba.
	 * @param array<Entity> $objects elemek
	 * @param string $relationKey kapcsolat kulcs
	 * @param string $insertionKey a beszúrás kulcsa
	 */
	public static function appendRelatedTo(&$objectArray, $relationKey, $insertionKey){
		if(is_object($objectArray)) $objectArray = array($objectArray);

		$objects = static::getRelatedTo($objectArray, $relationKey);

		foreach($objectArray as &$object){
			if(is_array($object->$relationKey)){
				$object->$insertionKey = array();
				foreach($object->$relationKey as $key)if(is_numeric($key)){
					$object->{$insertionKey}[$key] = $objects[$key];
				}
			}else $object->$insertionKey = $objects[$object->$relationKey];
		}
	}

	public static function getRelatedDataTo($objectArray, $relationKey){

		if(is_object($objectArray))$objectArray = array($objectArray);

		$idsTmp = array();
		$ids = array();
		if(!count($objectArray)) return array();
		foreach($objectArray as $item) $idsTmp[] = $item->$relationKey;

		foreach($idsTmp as $tmpId){
			if(is_array($tmpId)){ foreach($tmpId as $_tmpId) if(is_numeric($_tmpId)) $ids[] = $_tmpId; }
			else if(is_numeric($tmpId)) $ids[] = $tmpId;
		}
		if(!count($ids)) return array();
		$ids = array_unique($ids);
		return static::getAllData($ids);
	}



	// GETDATA

	public static function getData($sql, $params = null){
		if(is_numeric($sql)){ $id = $sql;
			$record = static::memcacheGet($id);
			if(!$record) $record = static::$db->getRow("SELECT * FROM `".static::$model['table']."` WHERE id=$1", $id);
			if($record) static::memcacheSet($record);
			return $record;
		}elseif(is_string($sql)){
			if($params !== null){
				$params = func_get_args(); array_shift($params);
				$sql = static::insertSqlParams($sql, $params);
			}
			return static::$db->getRow($sql);
		}else throw new Exception("Entity GetData Wrong parameter ".gettype($sql)." instead of integer or string");
	}

	public static function getAllData($sql=null, $params = null){
		if($sql === null){
			$sql =  "SELECT * FROM `".static::$model['table']."` ORDER BY id";
			$params = null;
		}

		if(is_array($sql)){ $ids = $sql;
			if(!count($ids)) return array();
			$ids = array_unique($ids);
			$records = array();
			$idsForSql = array();
			foreach($ids as $id) if(is_numeric($id)){
				$record = static::memcacheGet($id);
				if($record) $records[$id] = $record;
				else $idsForSql[] = $id;
			}
			if(count($idsForSql)){
				$recordFromDB = static::$db->getRows("SELECT * FROM `".static::$model['table']."` WHERE id in ($1)", $idsForSql);
				foreach($recordFromDB as $record){
					$records[$record['id']] = $record;
					static::memcacheSet($record);
				}
			}
			return $records;
		}elseif(is_string($sql)){
			if($params !== null){
				$params = func_get_args(); array_shift($params);
				$sql = static::insertSqlParams($sql, $params);
			}
			return static::$db->getRows($sql);
		}else throw new Exception("Entity GetAllData Wrong parameter ".gettype($sql)." instead of array(of numerics) or string");
	}



	// InsertSqlParams
	/**
	 * @ignore
	 * @param $sql
	 * @param $params
	 * @return mixed
	 */
	protected static function insertSqlParams($sql, $params){
		return static::$db->buildSQL($sql, $params);
	}

	// EntityDBRequest

	/**
	 * Inicializál egy új EntityDBRequest objektumot
	 * @param $where
	 * @param null $sqlParams
	 * @return DBRequest
	 */
	public static function find($where, $sqlParams = null){
		$request = static::select("* FROM `".static::$model['table']."`")->addWhere('WHERE', func_get_args());
		return $request;
	}

	/**
	 * Inicializál egy új EntityDBRequest objektumot
	 * @param $select
	 * @return DBRequest
	 */
	public static function select($select, $sqlParams = null){
		$request = new DBRequest(static::$db, get_called_class());
		return call_user_func_array(array($request, 'select'), func_get_args());
	}

	/**
	 * Converts record into entity object
	 * @param array $item
	 * @return static
	 */
	public static function convert($data) { return static::create($data); }

	/**
	 * Converts records into entity objects
	 * @param array[] $items
	 * @return static[]
	 */
	public static function convertAll(array $data) { return static::createAll($data); }

	// MEMCACHE

	protected static function getMemcacheKey($id){
		return Config::$appName.'_EM_'.static::$model['table'].$id;
	}
	protected static function memcacheGet($id){
		if(!static::$mc) return false;
		//echo 'try to load from memcache('.$id.') :';
		$data = static::$mc->get(static::getMemcacheKey($id));
		if($data) echo ' success'."\n";
		else  echo ' failed'."\n";
		return $data;
	}
	protected static function memcacheSet($record){
		if(!static::$mc or !array_key_exists('id', $record)) return false;
		//echo 'saving to memcache ('.$record['id'].'):'."\n";
		return static::$mc->set(static::getMemcacheKey($record['id']), $record);
	}
	protected static function memcacheDelete($id){
		if(!static::$mc) return false;
		return static::$mc->delete(static::getMemcacheKey($id));
	}

	// SERIALIZE

	protected static function serializeList($data){
		$str = '';
		if(is_array($data)) foreach($data as $item) if($item) $str .= '<'.htmlspecialchars($item).'>';
		return $str;
	}
	protected static function unserializeList($str){
		$array = array();
		$data = explode('<', str_replace('>','',substr($str,1)));
		foreach($data as $item) if($item) $array[] = htmlspecialchars_decode($item);
		return $array;
	}
	protected static function serializeAssocList($data){
		$str = '';
		foreach($data as $key=>$value) $str .= '<'.$key.'>'.$value.'</'.$key.'>';
		return $str;
	}
	protected static function unserializeAssocList($data){
		$array = array();
		$count = preg_match_all('/<(.*?)>(.*?)<\/(.*?)>/s', $data, $result);
		for($i = 0; $i < $count; $i++) $array[$result[1][$i]] = $result[2][$i];
		return $array;
	}

	public function getModel() { return static::$model; }

	public function deleteFromCache(){
		static::memcacheDelete($this->id);
		if(is_array(self::$objectCache) and static::$objectCachable) unset(self::$objectCache[static::getUid($id)]);
	}

	public function getAttachmentHandler() { throw new Exception('Not implemented! (View `'.get_called_class().'` must have it\'s own attachment handler factory method!)'); }
	public function getAttachmentReader() { return new EntityAttachmentReader($this->attachmentCollections); }

	public function getCommenter() { throw new Exception('Not implemented! (View `'.get_called_class().'` must have it\'s own commenter factory method!)'); }

	/**
	 *
	 * @return \DBHierarchy|null
	 */
	public static function getHierarchyManager() {
		if (!static::isOrdered()) return null;
		return new DBHierarchy(static::$db, static::$model['table'], static::isTree());
	}

	public static function isOrdered() { return static::fieldExists('ordinal'); }
	public static function isTree() { return static::fieldExists('parentId'); }

	public static function fieldExists($fieldName) { return array_key_exists($fieldName, static::$model['fields']); }
	public function hasField($fieldName) { return array_key_exists($fieldName, static::$model['fields']); }
}
