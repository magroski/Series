<?php

class Frogg_TagContent extends Frogg_Model{
	
	public $id;
	public $tagID;
	public $contentID;

	public $_content;

	public function getDB() {
		$properties_meta = array(
			'tagID' 	=> Frogg_Db_Type::get('tagID', 'int', 11, false),
			'contentID' => Frogg_Db_Type::get('contentID', 'int', 11, false),
		);
		return $properties_meta; 
	}
	
	public static function createDB(Frogg_Model $content){
		$fields = '';
		$issetID = false;
		$properties_meta = array(
			'tagID' 	=> Frogg_Db_Type::get('tagID', 'int', 11, false),
			'contentID' => Frogg_Db_Type::get('contentID', 'int', 11, false),
		);
		
		foreach ($properties_meta as $property) {
			$fields.= $property.',';
			$issetID = $issetID || ($property->name=='id'); 
		}
		$fields = substr($fields, 0, -1);
		$tablename = self::staticGetClass($content);
		$sql = "
		CREATE TABLE IF NOT EXISTS `".$tablename."` ("
		  .(($issetID)?'':'`id` int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`),').
		  "$fields
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		";
		new Frogg_Db_Sql("DROP TABLE ".$tablename);
		new Frogg_Db_Sql($sql);
	}
	
	public function setContent(Frogg_Model $content) {
		$this->_content = $content;
	}
	
	public static function deletePost($content){
		$this->_content = $content;
		$tmp = new self();
		$tags = $tmp->search(Frogg_Model::ALL,1,'WHERE contentID = '.$content->id);
		
		foreach ($tags as $tag){
			$tag = Frogg_Tag::getID($tag->tagID);
			$tag->sub();
		}
		
		$sql = new Frogg_Db_Sql("
			DELETE FROM
				`".$this->getClass($content)."`
			WHERE
				`contentID` = $content->id
		");
	}
	
	public function getClass(){
		return self::staticGetClass($this->_content);
	}
	
	public static function staticGetClass($content){
		return  'FrTC_'.$content->getClass();
	}	
}