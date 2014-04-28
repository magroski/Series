<?php

/*
 * Torrent release-friendly episode (or season) name to be searched in torrentz.eu
 */
class Application_Model_Release extends Frogg_Model{
	
	public $id;
	public $id_original;
	public $release;
	public $timestamp;
	
	public function getDB() {
		$properties_meta = array(
			'id_original'	=> Frogg_Db_Type::get('id_original', 'int', 11),
			'release'		=> Frogg_Db_Type::get('release', 'varchar', 255, '', 'unique'),
			'timestamp'		=> Frogg_Db_Type::get('timestamp', 'int', 11)
		);
		return $properties_meta; 
	}
	
}