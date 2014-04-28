<?php
class Project_Controller extends Frogg_Controller {    

	protected $usesSession = true;
	
    public function loggedBoot(){

    }
    
    public function postSession(){
		$this->view->isLogged 	  = false;
		$this->isLogged 		  = false;
    	if($this->session->isLogged()){
			$this->view->session_user = new Application_Model_User($this->session->getUser());
			$this->view->isLogged 	  = true;
			$this->isLogged 		  = true;
		}
		$this->view->session = $this->session;
    	
    }
    
    protected function pageTitle(){
    	
    }
    
    public function unloggedBoot(){

    }
    
	public function permissionDenied(){
    	$this->_redirect(HOST.'login?to='.HOST.$_SERVER['REDIRECT_URL']);
    }
    
	public function getSession(){
    	return new Application_Model_Session();
    }
}