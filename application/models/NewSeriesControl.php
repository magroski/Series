<?php

class Application_Model_NewSeriesControl extends Frogg_Model{
	
	public $id;
	public $newseries_id;
	
	public function getDB() {
		$properties_meta = array(
			'newseries_id' => Frogg_Db_Type::get('newseries_id', 'int', 11)
		);
		return $properties_meta; 
	}
	
	public function nextSeries(){
		$new_series = new Application_Model_NewSeries();
		$sql = new Frogg_Db_Sql("
			SELECT *
			FROM  `newseries`
			WHERE id > $this->newseries_id AND
				  `flag` = ".Application_Model_NewSeries::UNREAD."
			LIMIT 0,1
		");
		if(!$sql->rows()){
			$sql = new Frogg_Db_Sql("
				SELECT *
				FROM newseries
				WHERE `flag` = ".Application_Model_NewSeries::UNREAD."
				ORDER BY id ASC
				LIMIT 0,1
			");
			if(!$sql->rows()){
				return false;
			}
		}
		$row = $sql->fetch();
		$new_series->loadData($row);
		$this->newseries_id = $new_series->id;
		$this->update();
		return $new_series;
	}

}