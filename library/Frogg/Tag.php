<?php
class Frogg_Tag extends Frogg_Model{
	
	public $id;
	public $name;
	public $permalink;
	public $amount;
	
	public $_content;
	
	public final function __construct($tag_name='', Frogg_Model $content=null) {
		$this->preInit();
		$amount = func_num_args();
		if ($amount){
			$this->_content = $content;
			$tag_name = self::sanitize($tag_name);
			$tag_name = trim($tag_name);
			$permalink = new Frogg_Permalink($tag_name, self::staticGetClass($content));
			$permalink = $permalink->slug();
			if($this->exists($permalink)){
				$this->loadField('permalink', $permalink);
			} else {
				$this->id		 = -1;
				$this->name 	 = $tag_name;
				$this->permalink = $permalink;
				$this->amount 	 = 1;
			}
		}
		$this->posInit();
	}
	
	public function getDB() {
		$properties_meta = array(
			'name' 		=> Frogg_Db_Type::get('name', 'varchar', 256),
			'permalink' => Frogg_Db_Type::get('permalink', 'varchar', 256),
			'amount'	=> Frogg_Db_Type::get('amount', 'int', 11)
		);
		return $properties_meta; 
	}
	
	public static function createDB(Frogg_Model $content){
		$fields = '';
		$issetID = false;
		$properties_meta = array(
			'name' 		=> Frogg_Db_Type::get('name', 'varchar', 256),
			'permalink' => Frogg_Db_Type::get('permalink', 'varchar', 256),
			'amount'	=> Frogg_Db_Type::get('amount', 'int', 11)
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
		Frogg_TagContent::createDB($content);
	}
	
	public function setContent(Frogg_Model $content) {
		$this->_content = $content;
	}
	
	public static function removeAllTagsForContent($content){
		$tmp = new Frogg_TagContentDAO($content);
		$tags = $tmp->search(Frogg_DAO::ALL,1,'WHERE contentID = '.$content->id);
		
		foreach ($tags as $tag){
			$tag = Frogg_Tag::getID($content, $tag->tagID);
			$tag->_content = $content;
			$tag->sub();
		}
		$sql = new Frogg_Db_Sql("
			DELETE FROM
				`".Frogg_TagContent::staticGetClass($content)."`
			WHERE
				`contentID` = $content->id
		");
	}
	
	public function exists($permalink){
		$sql = new Frogg_Db_Sql("
			SELECT
				*
			FROM
				`".self::staticGetClass($this->_content)."`
			WHERE
				`permalink` = '$permalink';
		");
	
		return $sql->rows();
	}
	
	public function isLoaded(){
		return $this->id != -1;
	}
	
	public function hasTag(){
		if($this->exists($this->permalink)){
			$sql = new Frogg_Db_Sql("
				SELECT
					*
				FROM
					`".Frogg_TagContent::staticGetClass($this->_content)."`
				WHERE
					`tagID` 	= '$this->id'
				AND `contentID` = '".$this->_content->id."';
			");
			return $sql->rows();
		}
		return false;
	}
	
	public function save(){
		
		if($this->hasTag()) return;
		
		if($this->exists($this->permalink)){
			$sql = new Frogg_Db_Sql("
				UPDATE
					`".self::staticGetClass($this->_content)."`
				SET
					`amount` = `amount` + 1
				WHERE
					`permalink` = '$this->permalink';
			");
		} else {
			$sql = new Frogg_Db_Sql("
				INSERT INTO
					`".self::staticGetClass($this->_content)."`
				VALUES
					(NULL, '$this->name','$this->permalink','$this->amount')
			");
			$sql = new Frogg_Db_Sql("
				SELECT
					`id`,`amount`
				FROM
					`".self::staticGetClass($this->_content)."`
				WHERE
					`permalink` = '$this->permalink'
			");
			
			$row = $sql->fetch();
			$this->id = $row[0];
		}
		
		$tag_content = new Frogg_TagContent($this->id, $this->_content->id, $this->_content);
		$tag_content->save();
		
		return $this->id; 
	}
	
	public function remove(){
		if($this->hasTag()){
			$sql = new Frogg_Db_Sql("
				DELETE FROM
					`".Frogg_TagContent::staticGetClass($this->_content)."`
				WHERE
					`tagID` 	= ".$this->id."
				AND `contentID` = ".$this->_content->id
			);
			
			$this->sub();
		}
	}
	
	public function deleteCascade(){
		$sql = new Frogg_Db_Sql("
			DELETE FROM
				`".Frogg_TagContent::staticGetClass($this->_content)."`
			WHERE
				`tagID` = $this->id
		");
		$this->delete();
	}
	
	public function sub(){
		$this->amount--;
		$this->update();
	}
	
	public static function tagCloud($obj, $amount=50){
		$sql = new Frogg_Db_Sql("
			SELECT
				*
			FROM
				`".self::staticGetClass($obj->_content)."`
			ORBER BY
				`amount` DESC
			LIMIT
				0, $amount
		");
		$lista = array();
		while(($row=$sql->fetch())){
			array_push($lista, $row);
		}
		return $lista;
	}
	
	public static function getID($obj, $id) {
		$tmp = new self();
		$sql = new Frogg_Db_Sql("
			SELECT
				*
			FROM
				`".self::staticGetClass($obj)."`
			WHERE 
				`id` = $id
		");
		
		if($sql->rows()){
			$row = $sql->fetch();
			$tmp->loadData($row);			
			return $tmp;
		}
		return false;
	}
	
	public static function sanitize($tag){
		$tag = str_replace("'", '', $tag);
		$tag = str_replace('"', '', $tag);
		$a = array("/[^a-zA-Z0-9ÂÀÁÄÃâãàáäÊÈÉËêèéëÎÍÌÏîíìïÔÕÒÓÖôõòóöÛÙÚÜûúùüÇç\-_\+ ]/" => '');
		return preg_replace(array_keys($a), array_values($a), $tag);    
	}
	
	public function getClass(){
		return self::staticGetClass($this->_content);
	}
	
	public static function staticGetClass(Frogg_Model $content){
		return  'FrT_'.$content->getClass();
	}
}