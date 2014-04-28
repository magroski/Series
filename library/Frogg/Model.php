<?php
abstract class Frogg_Model{	
	
	public function __construct() {
		$this->preInit();
		$amount = func_num_args();
		if ($amount==1){
			$this->load(func_get_arg(0)+0);
		} elseif ($amount>1){
			$class 		  = get_class($this);
			$ref 		  = new ReflectionClass($class);
			$properties   = $ref->getProperties();
			$i = 0;
			foreach ($properties as $property) {
				$name = $property->getName();
				if($name=='id') continue;
				$this->$name = func_get_arg($i++);
				if($i>=$amount) break;
			}
		}
		$this->posInit();
	}
	
	public function preInit(){}
	public function posInit(){}
	
	public function getClass(){
		return  strtolower(str_replace('Application_Model_', '', get_class($this)));
	}
	
	public function getDB(){
		return array();
	}
	
	public function permalinkFor($attribute, $column='permalink', $limit=false){
		$tmp = new Frogg_Permalink($this->$attribute, $this->getClass(), $column);
		return $tmp->create($limit);
	}
	
	public function permalinkForString($string, $limit=false){
		$tmp = new Frogg_Permalink($string, $this->getClass());
		return $tmp->create($limit);
	}
	
	protected function preSave() {}
	protected function postSave(){}
	public function save() {
		$this->preSave();
		$this->id = Frogg_DAO::insert($this);
		$this->postSave();
		return $this->id;
	}
	
	public function update() {
		return Frogg_DAO::update($this);
	}
	
	public function load($id) {
		return Frogg_DAO::load($this,$id);
	}
	
	public function loadField($field, $value) {
		return Frogg_DAO::loadField($this, $field, $value);
	}
	
	public function loadFields($field, $value, $field_2, $value_2) {
		return Frogg_DAO::loadFields($this, $field, $value, $field_2, $value_2);
	}
	
	public function loadFieldsArray($array) {
		return Frogg_DAO::loadFieldsArray($this, $array);
	}
	
	public function loadData($row){
		$class = get_class($this);
		$ref 		 = new ReflectionClass($class);
		$properties  = $ref->getProperties();
		foreach ($properties as $property) {
			$name = $property->getName();
			if(isset($row[$name])){
				$this->$name = stripslashes(str_replace('\\','\\\\',$row[$name]));
			}
		}
	}
	
	public function delete() {
		Frogg_DAO::delete($this);
	}
	
	public function deleteCascade() {
		$this->delete();
	}
	
	public static function deleteAll($models){
		foreach ($models as $model) {
			$model->deleteCascade();
		}
	}
	
}