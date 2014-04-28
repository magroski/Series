<?php

/**
 * MySQL SGDB access class.
 * This class is an interface used to pass information to the SGDB class
 * It can be used to do SQL querys, count result rows and to sanitize data via escape_string
 * @author lucas
 */
class Frogg_Db_Sql{
	private $result;
	private static $db;

	/**
	 * Method used to create SQL querys.
	 * @param string $sql String containing the SQL query to be sent to the SGDB
	 */
	public function __construct( $sql ){		
		self::$db = Frogg_Db_MySQL::create();
		$this->result = self::$db->sql( $sql );			
	}
	
	/**
	 * Method used to obtain the query result
	 */
	public function getResult(){
		return $this->result;
	}

	/**
	 * Method used to count the query result rows.
	 */
	public function rows(){
		return self::$db->rows($this->result);
	}
	
	/**
	 * Method used to obtain the next result row
	 */
	public function fetch(){
		return self::$db->fetch($this->result);
	}

	/**
	 * Method used to sanitize data to be sent to the DB
	 * @param string $string Data to be sanitized
	 */
	public static function escapeString( $string ){
		self::$db = Frogg_Db_MySQL::create();
		return self::$db->escapeString( $string );
	}

	/**
	 * Method used to obtain the id from the last insert
	 */
	public function insertId(){
		return self::$db->insertId();
	}
}
