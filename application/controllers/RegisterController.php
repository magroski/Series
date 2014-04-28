<?php

class RegisterController extends Project_Controller{
	
	public function indexAction(){
    	$recaptcha	= new Application_Model_Helper_ReCaptcha();
    	if($this->_request->isPost()){
    		$name 	  	= $this->_request->getParam('name');
    		$email 	  	= $this->_request->getParam('email');
			$password 	= $this->_request->getParam('password');
			$password2	= $this->_request->getParam('password2');
    		$challenge 	= $this->_request->getParam('recaptcha_challenge_field');
    		$response   = $this->_request->getParam('recaptcha_response_field');
    		if ($response != ''){
				$result	  = $recaptcha->verify( $challenge, $response );
    			if(Frogg_Validator::validate(V_NAME, $name) && Frogg_Validator::validate(V_PASS,  $password) && $password == $password2 && $result->isValid()){
			    	$user = new Application_Model_User();
	    			if(!$user->loadField('email', $email)){
	    				$user 	 = new Application_Model_User($name,$email,sha1($password),'',time());
	    				$user->permalink = $user->permalinkFor('name');
	    				$user_id = $user->save();
						$this->session->logIn($user_id);
						
						$series_dao = new Application_Model_WatchingDAO();
						$series_dao->watchSeries(513225, $this->session->getUser()); //Começa a acompanhar X
						
						$this->_redirect($this->view->url(array(),'home',true));
	    			}
	    		}
    		}
    	}
    	$this->view->recaptcha = $recaptcha;
    	$this->view->description = '';
    	$this->view->description = 'Página de Cadastro';
    	$this->_helper->layout()->setLayout('full');
    	$this->view->headTitle()->prepend('Cadastro');
    }
    
	public function registerFbAction(){
    	$token	= $this->_request->getParam('token');
    	$fb = new Frogg_Social_Facebook(FB_APP_ID, FB_APP_SECRET, $token);
    	$profile = $fb->getUserProfile();
    	if($profile==null){
			echo $this->view->url(array(),'register',true);
    		die;
    	}
		$user = new Application_Model_User();
		if(!$user->loadField('email', $profile['email'])){
			$user 	= new Application_Model_User($profile['name'], $profile['email'], '', '', time());
			$user->permalink = $user->permalinkFor('name');
			$id 	= $user->save();
			$this->session->logIn($id);
			
			$series_dao = new Application_Model_WatchingDAO();
			$series_dao->watchSeries(513225, $this->session->getUser()); //Começa a acompanhar x
			
			echo $this->view->url(array(),'home',true);
			die;
		} else {
			$this->session->logIn($user->id);
			echo $this->view->url(array(),'home',true);
			die;
		}
    }

}