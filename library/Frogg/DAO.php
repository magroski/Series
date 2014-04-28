<?php
/**
 * Abstract class that represents the basic DAO Object of the system
 */

class Frogg_DAO{

	const ERROR = -1;
	const ALL   = -1;
	
	protected static $_use_flag				= true;	
	protected static $_flag_type			= null;	#array('name'=>...,'type'=>...,'size'=>...)
	protected static $_flag_value			= null;	#Flag value
	protected static $_flag_original_value	= null; #used to restore the flag_value to its original value
	
	protected static function getModelDB(Frogg_Model $obj){
		$db = $obj->getDB();
		if(static::$_flag_type!=null){
			array_push($db, Frogg_Db_Type::get(static::$_flag_type['name'], static::$_flag_type['type'], static::$_flag_type['size']));
		}
		return $db;
	}
	
	public static function flagCondition($table=false){
		if(static::$_use_flag && static::$_flag_type!=null && static::$_flag_value!=null ){
			return static::createFlagCondition($table);
		}
		return '';
	}
	
	public static function createFlagCondition($table=false){
		if($table){
			return 'AND `'.$table.'`.`'.static::$_flag_type['name'].'` = '.static::$_flag_value;
		}
		return 'AND `'.static::$_flag_type['name'].'` = '.static::$_flag_value;
	}

	public static function getFlagValue(){
		return static::$_flag_value;
	}

	public static function setFlagValue($value){
		static::$_flag_value = $value;
	}
	
	public static function restoreFlagValue(){
		static::$_flag_value = static::$_flag_original_value;
	}
	
	public static function restoreDefaults(){
		static::useFlag();
		static::$_flag_value = static::$_flag_original_value;
	}
	
	public static function ignoreFlag(){
		static::$_use_flag = false;
	}
	
	public static function useFlag(){
		static::$_use_flag = true;
	}
	
	public static function createDB(Frogg_Model $obj){
		$fields = '';
		$issetID = false;
		$db = static::getModelDB($obj);
		foreach ($db as $property) {
			$fields.= $property.',';
			$issetID = $issetID || ($property->name=='id'); 
		}
		$fields = substr($fields, 0, -1);
		$tablename = $obj->getClass();
		
		$sql = "
		CREATE TABLE IF NOT EXISTS `".$tablename."` ("
		  .(($issetID)?'':'`id` int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`),').
		  "$fields
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1 ;
		";
		new Frogg_Db_Sql($sql);
	}
	
	public static function insert(Frogg_Model $obj){
		$class = get_class($obj);
		$table = $obj->getClass();
		$ref = new ReflectionClass($class);
		$properties = $ref->getProperties();
		$fields	 = '';
		$values	 = '';
		$issetID = false;
		$db = static::getModelDB($obj);
		
		foreach ($db as $property) {
			$issetID = $issetID || ($property->name=='id'); 
		}
		foreach ($properties as $property) {
			$name = $property->getName();
			if($name=='id' && !$issetID) continue;
			if($name[0]=='_') continue;
			
			$fields .= '`'.$property->getName().'`,';
			if($obj->$name!='CURRENT_TIMESTAMP'){
				$values .= '\''.addslashes($obj->$name).'\',';
			} else {
				$values .= addslashes($obj->$name).',';
			}
		}
		$fields = substr($fields, 0, -1);
		$values = substr($values, 0, -1);
		
		$sql = new Frogg_Db_Sql("
			INSERT INTO 
				`$table` (".(($issetID)?'':'`id`,')." $fields) 
			VALUES 
				(".(($issetID)?'':'NULL,')."  $values);
		");
		
		if($sql->getResult()) {
			$obj->id = $sql->insertId();
			return $obj->id;
		}
		
		return self::ERROR;
	}
	
	public static function update(Frogg_Model $obj){
		$class 		= get_class($obj);
		$table 		= $obj->getClass();
		$ref 		= new ReflectionClass($class);
		$properties = $ref->getProperties();
		$fields_values = ''; 
		
		foreach ($properties as $property) {
			$name = $property->getName();
			if($name=='id')		continue;
			if($name[0]=='_')	continue;
			
			$value = addslashes($obj->$name);
			$fields_values .= "`$name` = '$value',";
		}
		
		$fields_values = substr($fields_values, 0, -1);
		
		new Frogg_Db_Sql("
			UPDATE 
				`$table`
			SET
				 $fields_values
			WHERE
				`id` =$obj->id
			".static::flagCondition()."
			LIMIT
				1;
		");			 
	}
	
	public static function delete(Frogg_Model $obj){
		$class = get_class($obj);
		$table = $obj->getClass();
		
		new Frogg_Db_Sql("
			DELETE FROM
				`$table`
			WHERE 
				`id` = $obj->id
			".static::flagCondition()."
			LIMIT 1
		");
	}
	
	public static function load(Frogg_Model &$obj, $id){
		$class = get_class($obj);
		$table = $obj->getClass();
		
		$sql = new Frogg_Db_Sql("
			SELECT
				*
			FROM
				`$table`
			WHERE 
				`id` = $id
			".static::flagCondition()."
		");
		
		if($sql->rows()){
			$row = $sql->fetch();
			$obj->loadData($row);			
			return true;
		}
		return false;
	}
	
	public static function loadField(Frogg_Model &$obj, $field, $value){
		$class = get_class($obj);
		$table = $obj->getClass();
		$value = addslashes($value);
		
		$sql = new Frogg_Db_Sql("
			SELECT
				*
			FROM
				`$table`
			WHERE 
				`$field` = '$value'
			".static::flagCondition()."
		");
		
		if($sql->rows()){
			$row = $sql->fetch();
			$obj->loadData($row);			
			return true;
		}
		return false;
	}
	
	public static function loadFields(Frogg_Model &$obj, $field, $value, $field_2, $value_2){
		$class = get_class($obj);
		$table = $obj->getClass();
		$value = addslashes($value);
		$value_2 = addslashes($value_2);
		
		$sql = new Frogg_Db_Sql("
			SELECT
				*
			FROM
				`$table`
			WHERE 
				`$field` = '$value'
			AND `$field_2` = '$value_2'
			".static::flagCondition()."
		");
		
		if($sql->rows()){
			$row = $sql->fetch();
			$obj->loadData($row);			
			return true;
		}
		return false;
	}
	
	public static function loadFieldsArray(Frogg_Model &$obj, $array){
		$class = get_class($obj);
		$table = $obj->getClass();
		
		$cond = array();
		foreach($array as $key => $value){
			array_push($cond, "`$key` = '".addslashes($value)."'");
		}
		$cond = implode(' AND ', $cond);
		
		$sql = new Frogg_Db_Sql("
			SELECT
				*
			FROM
				`$table`
			WHERE 
				$cond
			".static::flagCondition()."
		");
		
		if($sql->rows()){
			$row = $sql->fetch();
			$obj->loadData($row);			
			return true;
		}
		return false;
	}
	
	public function getClass(){
		return str_replace('DAO', '', get_class($this));
	}
	
	public function getTable(){
		return strtolower(str_replace('Application_Model_', '',$this->getClass()));
	}
	
	public function count($cond=''){
		$table = $this->getTable();
		
		$sql = new Frogg_Db_Sql("
			SELECT
				*
			FROM
				`$table`
			$cond
			".static::flagCondition()
		);
		
		return $sql->rows();
	}
	
	public function countField($field, $value, $cond='1'){
		$table = $this->getTable();
		
		$sql = new Frogg_Db_Sql("
			SELECT
				count(*)
			FROM
				`$table`
			WHERE 
				`$field` = '$value'
			AND ($cond)
			".static::flagCondition()
		);
		
		$row = $sql->fetch();
		return $row[0];
	}
	
	public function countFields($field, $value, $field_2, $value_2, $cond='1'){
		$table = $this->getTable();
		
		$sql = new Frogg_Db_Sql("
			SELECT
				count(*)
			FROM
				`$table`
			WHERE 
				`$field` = '$value'
			AND `$field_2` = '$value_2'
			AND ($cond)
			".static::flagCondition()
		);
		
		$row = $sql->fetch();
		return $row[0];
	}
	
	public function searchJoin($join_field, $return_field, $return_objectDAO, $amount, $page=1 , $cond='', $order_by='', $offset=0, $keep_id_order=true) {
		$class = $this->getClass();
		$table = $this->getTable();

		$return_class = $return_objectDAO->getClass();
		$return_table = $return_objectDAO->getTable();
		
		if($cond==''){
			$cond = 'WHERE 1';
		}

		
		$sql = new Frogg_Db_Sql("
			SELECT
				`$return_table`.*
			FROM
				`$return_table`
			INNER JOIN 
				`$table`
			ON
				`$return_table`.$return_field = `$table`.$join_field
			$cond
			".static::flagCondition($return_table)."
			$order_by
			".(($amount==self::ALL)?
				'':
			"LIMIT 
				".(($page-1)*$amount + $offset).",$amount")
		);		

		$list = array();
		if($sql->rows()){
			while(($row = $sql->fetch())){
				$tmp = new $return_class();
				$tmp->loadData($row);
				if($keep_id_order){
					$list[$tmp->$return_field] = $tmp;
				} else {
					array_push($list, $tmp);
				}
			}
		}
		
		return $list;
	}
	
	public function search($amount, $page=1, $cond='',$fields='*',$order_by='', $index_by_id=false, $offset = 0) {
		$class = $this->getClass();
		$table = $this->getTable();
		
		if($cond==''){
			$cond = 'WHERE 1';
		}

		$sql = new Frogg_Db_Sql("
			SELECT
				$fields
			FROM
				`$table`
			$cond
			".static::flagCondition()."
			$order_by
			".(($amount==self::ALL)?
				'':
			"LIMIT 
				".(($page-1)*$amount + $offset).",$amount")
		);
		$list = array();
		if($sql->rows()){
			while(($row = $sql->fetch())){
				$tmp = new $class();
				$tmp->loadData($row);
				if($index_by_id){
					$list[$tmp->id] = $tmp;
				} else {
					array_push($list, $tmp);
				}
			}
		}
		return $list;
	}
	
	public function in($id_array, $keep_id_order=true) {
		$class = $this->getClass();
		$table = $this->getTable();
		
		$ids = implode(',', $id_array);
		
		$sql = new Frogg_Db_Sql("
			SELECT
				*
			FROM
				`$table`
			WHERE id IN($ids)
			".static::flagCondition()
		);
		$list = array();
		if($sql->rows()){
			while(($row = $sql->fetch())){
				$tmp = new $class();
				$tmp->loadData($row);
				if($keep_id_order){
					$list[$tmp->id] = $tmp;
				} else {
					array_push($list, $tmp);
				}
			}
			
			if(!$keep_id_order) return $list;
			
			$ordered_list = array();
			foreach ($id_array as $id) {
				if(!isset($list[$id])) continue;
				array_push($ordered_list, $list[$id]);
			}
			$list = $ordered_list;
		}
		return $list;
	}
	
	public function notIn($id_array, $addtional_cond='') {
		$class = $this->getClass();
		$table = $this->getTable();
		
		$ids = implode(',', $id_array);
		
		$sql = new Frogg_Db_Sql("
			SELECT
				*
			FROM
				`$table`
			WHERE id NOT IN($ids)
			$addtional_cond
			".static::flagCondition()
		);
		$list = array();
		if($sql->rows()){
			while(($row = $sql->fetch())){
				$tmp = new $class();
				$tmp->loadData($row);
				array_push($list, $tmp);
			}
		}
		return $list;
	}

	public function countSearch($cond=''){
		$table = $this->getTable();
		
		$sql = new Frogg_Db_Sql("
			SELECT
				*
			FROM
				`$table`
			$cond
			".static::flagCondition()
		);
		return $sql->rows();
	}
	
	public function navBar($amount, $pg=1, $cond='',$fields='*',$order_by='', $max_pages=5){
		$table = $this->getTable();
		
		$sql = new Frogg_Db_Sql("
			SELECT
				$fields
			FROM
				`$table`
			$cond
			".static::flagCondition()."
			$order_by
		");
		
		$total = $sql->rows();

		if($pg<1) $pg = 1;
		$links = array();			
		
		if($total){
			if($pg == 1){
				$links[1] = 1;
			} else {
				if($pg > 1){
					$links['<<'] = 1;
					$links['<'] = $pg-1;
				}
				
				$num_tmp = $pg - ($max_pages+1);
				
				for($i=1;$i<($max_pages+2);$i++){					
					if(($num_tmp+$i) > 0){
						$links[$num_tmp+$i] = $num_tmp+$i;
					}
				}
			}
			
			$last = ceil($total/$amount);
			
			for($i=1; $i<($max_pages+1);$i++){
				if(($pg+$i) <= $last){
					$links[$pg+$i] = $pg+$i;
				} else {
					break;
				}
			}
			
			if($pg < $last){
				$links['>'] = $pg+1;
				$links['>>'] = $last;
			}				
			
		}
		return $links;
	}
}