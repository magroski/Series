<?php

class Application_Model_Session extends Frogg_Session{
	
	public function init(){}
	
	public function isLogged(){
		return isset($_SESSION['usuario']);
	}
	
	public function logIn($id){
		$_SESSION['usuario'] = $id;
	}
	
	public function logOut(){
		unset($_SESSION['usuario']);
		unset($_SESSION['admin']);
	}
	
	public function getUser(){
		return ($this->isLogged())?$_SESSION['usuario']:(($this->isLoggedAdmin())?$_SESSION['admin']:-1);
	}
	
	public function getAccessLevel(){
		return ($this->isLoggedAdmin())?10:(($this->isLogged())?5:0);
	}
	
	public function isLoggedAdmin(){
		return isset($_SESSION['admin']);
	}
	
	public function logInAdmin(){
		$_SESSION['admin'] = 1;
	}
	
}