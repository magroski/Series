<?php

class Application_Model_ScheduledControl extends Frogg_Model{
	
	public $id;
	public $scheduled_id;
	
	public function getDB() {
		$properties_meta = array(
			'scheduled_id' => Frogg_Db_Type::get('scheduled_id', 'int', 11)
		);
		return $properties_meta; 
	}
	
	public function nextSchedule(){
		$scheduled = new Application_Model_Scheduled();
		$sql = new Frogg_Db_Sql("
			SELECT *
			FROM  `scheduled`
			WHERE id > $this->scheduled_id
			AND `read` = 0
			LIMIT 0,1
		");
		if(!$sql->rows()){
			$sql = new Frogg_Db_Sql("
				SELECT *
				FROM scheduled
				WHERE `read` = 0
				ORDER BY id ASC
				LIMIT 0,1
			");
			if(!$sql->rows()){
				return false;
			}
		}
		$row = $sql->fetch();
		$scheduled->loadData($row);
		$this->scheduled_id = $scheduled->id;
		$this->update();
		return $scheduled;
	}

}