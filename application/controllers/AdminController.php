<?php

class AdminController extends Project_Controller{
	
	protected $accessLevel = 10;
	
	public function init(){
		$this->_helper->layout()->setLayout('admin');
		switch ($this->_request->getActionName()) {
			case 'index':$this->accessLevel = 0; break;
			default:;break;
		}
	}
	
	public function indexAction() {
		$this->_helper->layout()->setLayout('full'); 
    	if($this->_request->isPost()){
	    	$login 	  = $this->_request->getParam('login');
	    	$password = $this->_request->getParam('password');
	    	if(Frogg_Validator::validate(V_LOGIN, $login)
	    	&& (!$this->session->isLoggedAdmin())
	    	&& Frogg_Validator::validate(V_PASS, $password)){
	    		$sql = new Frogg_Db_Sql("SELECT `id` FROM `admin` WHERE `login`='".Frogg_Db_Sql::escapeString($login)."' AND `password`='".sha1($password)."'");
	    		if($sql->rows()){
	    			$this->session->logInAdmin();
	    		}
	    	}
    	}
    	if($this->session->isLoggedAdmin()){
	    	$this->_redirect($this->view->url(array(),'adminPanel',true));
    	}
    }
    
	public function painelAction(){
		$series_dao 		= new Application_Model_SeriesDAO();
		$series 			= $series_dao->getSeries();
		$this->view->series = $series;
	}
	
	public function editInfoAction(){
		$permalink = $this->_request->getParam('permalink');
		$series = new Application_Model_Series();
		$series->loadField('permalink', $permalink);
		if($this->_request->isPost()){
			$image = new Frogg_Image('imagem');
			$img_name = $image->saveFixedWidth(300);
			$series->image = $img_name;
			$series->update();
			$this->_redirect($this->view->url(array(),'adminPanel',true));
		}
		$this->view->series = $series;
	}
    
    
}