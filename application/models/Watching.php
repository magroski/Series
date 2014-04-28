<?php

class Application_Model_Watching extends Frogg_Model{
	
	public $id;
	public $user_id;
	public $series_id; //Global Series ID
	public $season_id; //Season ID
	public $episode_id; //Single episode, season or series ID
	
	public function getDB() {
		$properties_meta = array(
			'user_id'	=> Frogg_Db_Type::get('user_id', 'int', 11),
			'series_id'	=> Frogg_Db_Type::get('series_id', 'int', 11),
			'season_id'	=> Frogg_Db_Type::get('season_id', 'int', 11),
			'episode_id'=> Frogg_Db_Type::get('episode_id', 'int', 11)
		);
		return $properties_meta; 
	}
	
}