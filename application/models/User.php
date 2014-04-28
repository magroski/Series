<?php

class Application_Model_User extends Frogg_Model{
	
	public $id;
	public $name;
	public $email;
	public $pass;
	public $permalink;
	public $tstamp;
	
	public function getDB() {
		$properties_meta = array(
			'name' 		=> Frogg_Db_Type::get('name', 'varchar', 256),
			'email' 	=> Frogg_Db_Type::get('email', 'varchar', 255, '', 'unique'),
			'pass' 		=> Frogg_Db_Type::get('pass', 'varchar', 40),
			'permalink' => Frogg_Db_Type::get('permalink', 'varchar', 256),
			'tstamp'	=> Frogg_Db_Type::get('tstamp', 'int', 11)
		);
		return $properties_meta; 
	}
	
	public static function login($email,$password){
		$email 		= Frogg_Db_Sql::escapeString($email);
		$password 	= sha1($password);
		$sql = new Frogg_Db_Sql("
			SELECT id
			FROM user 
			WHERE email = '$email' AND 
				  pass	= '$password'
		");
		if($sql->rows()){
			$row = $sql->fetch();
			return $row[0];
		}
		return false;
	}
	
}