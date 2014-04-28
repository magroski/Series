<?php

/*
 * Searchable micro-version of series 
 */
class Application_Model_SeriesBucket extends Frogg_Model{
	
	public $id;
	public $name;
	public $permalink;
	
	public function getDB() {
		$properties_meta = array(
			'name'			=> Frogg_Db_Type::get('name', 'varchar', 256),
			'permalink'		=> Frogg_Db_Type::get('permalink', 'varchar', 256)
		);
		return $properties_meta; 
	}
	
}