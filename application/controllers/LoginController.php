<?php

class LoginController extends Project_Controller{

	public function indexAction(){
		$this->_helper->layout->setLayout('full');
    	if($this->_request->isPost()){
	    	$email 	= $this->_request->getParam('email');
	    	$pass 	= $this->_request->getParam('pass');
	    	if( !$this->session->isLogged() && Frogg_Validator::validate(V_PASS, $pass)){
				$id = Application_Model_User::login($email, $pass);
				if($id){
		    		$this->session->logIn($id);
				}
	    	}
    	}
    	if($this->session->isLogged()){
	    	if(isset($_GET['to']) && strpos($_GET['to'], HOST) !== false) {
		    	$this->_redirect($_GET['to']);
	    	} else {
	    		$this->_redirect($this->view->url(array(),'home',true));
	    	}
    	}
    }
    
	public function loginFbAction(){
    	$token	= $this->_request->getParam('token');
    	$fb = new Frogg_Social_Facebook(343506709097145, '723cf9533998ad83a984024e1dfa2bb0', $token);
    	$profile = $fb->getUserProfile();
    	if($profile==null){
			echo $this->view->url(array(),'register',true);
    		die;
    	}
    	$email = $profile['email'];
    	$user = new Application_Model_User();
    	$user->loadField('email', $email);
    	
    	if($user->id==null){
    		echo $this->view->url(array(),'register',true);
    		die;
    	}
    	
    	$this->session->logIn($user->id);
		echo $this->view->url(array(),'home',true);
    	die;
    }
    
 	public function logoutAction(){
    	$this->session->logOut();
		$this->_redirect('/');
    }

}