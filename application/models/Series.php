<?php

class Application_Model_Series extends Frogg_Model{
	
	public $id;
	public $series_id;
	public $season_id;
	public $rage_id;
	public $name;
	public $status;
	public $runtime;
	public $aliases;
	public $link;
	public $link_720p;
	public $link_1080p;
	public $release;
	public $order; //Novo seriado, order = 0 | Nova temporada, order = X00.000 | Novo episódio, order = X00.00Y	
	public $airdate;
	public $permalink;
	public $image;
	public $timestamp;
	
	public function getDB() {
		$properties_meta = array(
			'series_id'		=> Frogg_Db_Type::get('series_id', 'int', 11),
			'season_id'		=> Frogg_Db_Type::get('season_id', 'int', 11),
			'rage_id'		=> Frogg_Db_Type::get('rage_id', 'int', 11),
			'name'			=> Frogg_Db_Type::get('name', 'varchar', 256),
			'status'		=> Frogg_Db_Type::get('status', 'int', 2),
			'runtime'		=> Frogg_Db_Type::get('runtime', 'int', 3),
			'aliases'		=> Frogg_Db_Type::get('aliases', 'varchar', 1024),
			'link'			=> Frogg_Db_Type::get('link', 'varchar', 512),
			'link_720p'		=> Frogg_Db_Type::get('link_720p', 'varchar', 512),
			'link_1080p'	=> Frogg_Db_Type::get('link_1080p', 'varchar', 512),
			'release'		=> Frogg_Db_Type::get('release', 'varchar', 256),
			'order'			=> Frogg_Db_Type::get('order', 'int', 11),
			'airdate'		=> Frogg_Db_Type::get('airdate', 'int', 11),
			'permalink'		=> Frogg_Db_Type::get('permalink', 'varchar', 256),
			'image'			=> Frogg_Db_Type::get('image', 'varchar', 256),
			'timestamp'		=> Frogg_Db_Type::get('timestamp', 'int', 11)
		);
		return $properties_meta; 
	}
	
	public function status(){
		switch ($this->status) {
			case NEW_SERIES: 		return 'Série Nova';
			case RETURNING_SERIES: 	return 'Em Exibição';
			case CANCELED_SERIES: 	return 'Terminada/Cancelada';
		}
	}
	
	public function image(){
		if(!empty($this->image)){
			return HOST.'i/'.$this->image; 
		} else {
			return HOST.'i/default.png';
		}
	}
	
	public function jsonObj(){
		$obj = new stdClass();
		$obj->id = $this->id;
		$obj->name = $this->name;
		$obj->airdate = $this->airdate;
		return $obj;	
	}
	
}