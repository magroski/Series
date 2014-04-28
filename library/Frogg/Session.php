<?php
/**
 * Generic object used to abstract session functionalities
 * @author Tasso
 */
class Frogg_Session {
	
	public function __construct(){
		session_start();
		$this->init();		
	}
	
	public function init(){}
	public function isLogged(){
		return false;
	}
	
	public function logIn($params){}
	public function logOut(){}
	public function getUser(){
		return false;
	}
	public function getAccessLevel(){
		return 5;
	}
	public function setDelay($name,$delay){
		$_SESSION[$name.'_time'] = time()+$delay;
	}
	public function checkDelay($name){
		if(isset($_SESSION[$name.'_time']) && $_SESSION[$name.'_time']>time()){
			return false;
		}
		return true;
	}

}