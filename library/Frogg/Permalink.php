<?php
/**
 * 
 * Generic object used to create unique permalinks
 * @author Tasso
 */
class Frogg_Permalink {
	private $prefix;
	private $title;
	private $suffix;
	private $table;
	private $field_name;
			
	/**
	 * 
	 * Default constructor for class Frogg_Permalink
	 * @param string $title The original string from where the permalink will be created
	 * @param string $table The database table where the permalink will be stored
	 * @param string $prefix Default value is ''
	 * @param string $suffix Default value is ''
	 * @param string $field_name Default value is 'permalink'
	 */
	public function __construct($title, $table, $field_name='permalink', $prefix='', $suffix=''){
		$this->title		= $title;
		$this->table		= $table;
		$this->field_name	= $field_name;
		$this->prefix		= $prefix;
		$this->suffix		= $suffix;
	}
	/**
	 * 
	 * Sets one prefix for the permalink 
	 * @param string $prefix The string that will be used as a prefix
	 */
	public function setPrefix($prefix){
		$this->prefix = $prefix;
	}
	
	/**
	 * 
	 * Sets one suffix for the permalink
	 * @param string $suffix The string that will be used as a suffix
	 */
	public function setSuffix($suffix){
		$this->suffix = $suffix;
	}
	
	/**
	 * 
	 * Creates the permalink
	 */
	public function create() {
		$slug = $this->prefix.$this->createSlug($this->title).$this->suffix;
		return $this->getNumeration($slug);
	}
	
	/**
	 * 
	 * Creates the permalink without considering numeration
	 */
	public function slug() {
		return $this->createSlug($this->title);
	}
	
	/**
	 * 
	 * Gets the permalink numeration according to the Database table where it is stored
	 * @param string $slug The slug created by self::createSlug()
	 */
	public function getNumeration($slug) {
		$sql = new Frogg_Db_Sql("
			SELECT 
				`$this->field_name`
			FROM 
				`$this->table`
			WHERE 
				`$this->field_name` = '$slug'
			LIMIT 
				1
		");
		$i = 1;
		$tmp = $slug;
		while($sql->rows()){
			$slug = $tmp.'-'.$i++;
			
			$sql = new Frogg_Db_Sql("
				SELECT 
					`$this->field_name`
				FROM 
					`$this->table`
				WHERE 
					`$this->field_name` = '$slug'
				LIMIT 
					1
			");
			
		}
		
		return $slug;
	}
	
	/**
	 * 
	 * Creates the first version of the Permalink
	 * @param string $title Text that used to create the permalink
	 */
	private function createSlug($title) {
		
    	$title = str_replace(array("&lt;", "&gt;", '&amp;', '&#039;', '&quot;','&lt;', '&gt;'), array("<", ">",'&','\'','"','<','>'), htmlspecialchars_decode($title, ENT_NOQUOTES));		
		$title = html_entity_decode(strtolower($title));
		
		$a = array('/[âãàáä]/'					=>'a',
				   '/[ÊÈÉË]/'					=>'e',
				   '/[êèéë]/'					=>'e',
				   '/[ÎÍÌÏ]/'					=>'i',
				   '/[îíìï]/'					=>'i',
				   '/[ÔÕÒÓÖ]/'					=>'o',
				   '/[ôõòóö]/'					=>'o',
				   '/[ÛÙÚÜ]/'					=>'u',
				   '/[ûúùü]/'					=>'u',
				   '/ç/'						=>'c',
				   '/Ç/'						=>'c',
				   '/ +/'						=>'-',
				   '/ \(/'						=>'-',
				   '/\) /'						=>'-',
				   '/_+/'						=>'-',
				   '/\//'						=>'-',
				   '/^-/'						=>'',
				   '/-$/'						=>'',
				   "/[^a-zA-Z0-9\\-_.+ ]/" 		=>'',
				   '/-+/'						=>'-'
				   );
		return preg_replace(array_keys($a), array_values($a), $title);
	}
	
	public function __toString(){
		return $this->create();
	}
}