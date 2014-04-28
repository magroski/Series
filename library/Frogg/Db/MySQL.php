<?php

/**
 * MySQL DB access class.
 * This class is used to connect to the database using the {@link Frogg_Db_DbAb} configurations.
 * Also, it can be used to do SQL querys and to sanitize data via escape_string
 * @author lucas
 *
 */
class Frogg_Db_MySQL{
	private static $link;
	private static $instance = null;
	private static $host;
	private static $username;
	private static $password;
	private static $dbname;
	
	private function __construct(){
		self::$host		= DB_HOST;
		self::$username	= DB_USER;
		self::$password	= DB_PASS;
		self::$dbname	= DB_NAME;
		self::connect();
	}

	public static function setHost($host){
		self::$host = $host;
	}
	public static function setUser($user){
		self::$username = $user;
	}
	public static function setPassword($pass){
		self::$password = $pass;
	}
	public static function setDbName($dbname){
		self::$dbname = $dbname;
	}
	
	public static function create(){
		if( self::$instance == null ){
			self::$instance = new Frogg_Db_MySQL();
		}
		return self::$instance;
	}

	/**
	 * This method is used to start a connection with the DB
	 */
	public static function connect(){
		if( !self::$link ){
			self::$link  = mysqli_connect( self::$host, self::$username, self::$password );
			mysqli_select_db( self::$link, self::$dbname );
			mysqli_set_charset( self::$link, 'utf8' );
		}
	}
	
	/**
	 * Method used to do SQL querys.
	 * @param string $sql String containing the SQL query to be sent to the DB
	 */
	public function sql( $sql ){
		return mysqli_query( self::$link , $sql );
	}
	
	/**
	 * Method used to count the query result rows.
	 * @param array $result Array containing the query result to be counted 
	 */
	public function rows( $result ){
		$rows = false;
		if(!$result) return false;
		
		$rows = mysqli_num_rows( $result );
		return $rows;
	}
	
	/**
	 * Method used to obtain the next result row
	 * @param array $result Array containing the query result rows
	 */
	public function fetch( $result ){
		$fetch = false;
		if(!$result) return false;
		
		$fetch = mysqli_fetch_array( $result );
		return $fetch;
	}
	
	/**
	 * Method used to sanitize data to be sent to the DB
	 * @param string $string Data to be sanitized
	 */
	public function escapeString( $string ){
		self::connect();
		return mysqli_real_escape_string( self::$link , $string );
	}
	
	/**
	 * Method used to obtain the id from the last insert
	 */
	public function insertId() {
		return mysqli_insert_id(self::$link);
	}
}
