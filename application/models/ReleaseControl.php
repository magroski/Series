<?php

class Application_Model_ReleaseControl extends Frogg_Model{
	
	public $id;
	public $release_id;
	
	public function getDB() {
		$properties_meta = array(
			'release_id' => Frogg_Db_Type::get('release_id', 'int', 11)
		);
		return $properties_meta; 
	}
	
	public function nextRelease(){
		$release = new Application_Model_Release();
		$sql = new Frogg_Db_Sql("
			SELECT *
			FROM  `release`
			WHERE id > $this->release_id
			LIMIT 0,1
		");
		if(!$sql->rows()){
			$sql = new Frogg_Db_Sql("
				SELECT *
				FROM release
				ORDER BY id ASC
				LIMIT 0,1
			");
		}
		$row = $sql->fetch();
		$release->loadData($row);
		$this->release_id = $release->id;
		$this->update();
		return $release;
	}

}