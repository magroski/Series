<?php

/*
 * Url containing future episodes to be read and indexed
 */
class Application_Model_Scheduled extends Frogg_Model{
	
	CONST UNREAD = 0;
	CONST READ 	 = 1;
	
	public $id;
	public $rage_id;
	public $link;
	public $read;
	public $timestamp;
	
	public function getDB() {
		$properties_meta = array(
			'rage_id'	=> Frogg_Db_Type::get('rage_id', 'int', 11),
			'link'		=> Frogg_Db_Type::get('link', 'varchar', 255),
			'read'		=> Frogg_Db_Type::get('read', 'int', 1),
			'timestamp'	=> Frogg_Db_Type::get('timestamp', 'int', 11)
		);
		return $properties_meta; 
	}
	
}