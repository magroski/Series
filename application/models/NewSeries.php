<?php
/**
 * A series that have been found but isn't indexed yet
 */
class Application_Model_NewSeries extends Frogg_Model{
	
	CONST INVALID = -1;
	CONST UNREAD  = 0;
	CONST READ 	  = 1;
	CONST ERROR   = 2;
	
	public $id;
	public $rage_id;
	public $flag;
	
	public function getDB() {
		$properties_meta = array(
			'rage_id'	=> Frogg_Db_Type::get('rage_id', 'int', 11, false, 'unique'),
			'flag'		=> Frogg_Db_Type::get('flag', 'int', 1)
		);
		return $properties_meta; 
	}
	
}