<?php

/**
 * This class is an abstraction of the Char data type
 * @author Tasso
 */
class Frogg_Db_Type{

	public $name;
	public $type;
	public $size;
	public $default_value = false;
	public $index = false;
	public $on_update = false;
	
	public function __construct($name, $type,$size=32,$default=false,$index=false, $on_update=false){
		$this->name 		= $name;
		$this->type 		= $type;
		$this->size 		= $size;
		$this->default_value= $default;
		$this->index		= $index;
		$this->on_update	= $on_update;
	}
	
	public static function get($name, $type,$size=32,$default=false,$index=false, $on_update=false){
		return new self($name, $type, $size, $default, $index, $on_update);
	}
	
	public function type($type){
		$this->type = $type;
		return $this;
	}
	public function size($size){
		$this->size = $size;
		return $this;
	}
	public function defaultValue($value){
		$this->default_value = $value;
		return $this;
	}
	public function index($index){
		$this->index = $index;
		return $this;
	}
	public function onUpdate($bool){
		$this->on_update = $bool;
		return $this;
	}
	public function __toString(){
		if($this->default_value===false){
			if($this->type=='timestamp'){
				$default = 'DEFAULT CURRENT_TIMESTAMP ';
			} else {
				$default = 'NOT NULL ';
			}
		} else {
			if($this->default_value=='null'){
				$default = 'DEFAULT NULL ';
			} else {
				if($this->type=='int' || $this->type=='decimal'){
					$default = 'NOT NULL DEFAULT \''.$this->default_value.'\' ';
				} else {
					$default = '';
				}
			}
		}
		switch($this->index){
			case 'primary': $index = ', PRIMARY KEY `'.$this->name.'` (`'.$this->name.'`)'; break;
			case 'unique':  $index = ', UNIQUE KEY `'.$this->name.'` (`'.$this->name.'`)'; break;
			default: $index = '';
		}
		if($this->type!='timestamp'){
			$size = '('.$this->size.') ';
		} else {
			$size = ' ';
		}
		if($this->on_update){
			$update = 'ON UPDATE CURRENT_TIMESTAMP ';
		} else {
			$update = ' ';
		}
		return '`'.$this->name.'` '.$this->type.$size.$default.$update.$index;
	}
}
