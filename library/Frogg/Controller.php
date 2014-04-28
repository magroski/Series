<?php
/**
 * 
 * Generic controller used to abstract generic controller functionalities
 * @author Tasso
 */
class Frogg_Controller extends Zend_Controller_Action {

	protected $session;
	protected $accessLevel = 0;
	protected $usesSession = false;
	
	/**	 
	 * Interface for initiating code execution
	 * @see Zend_Controller_Action::init()
	 */
	public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        parent::__construct($request, $response, $invokeArgs);
        
        $this->preSession();
        
        if($this->usesSession){
    		$this->session = $this->getSession();
    		if($this->session->isLogged()){
    			$this->loggedBoot();
    		} else {
    			$this->unloggedBoot();    		
    		}
    		if($this->checkPermission()){
    			$this->permissionAccepted(); 
    		} else {
    			$this->permissionDenied(); 
    		}
    	}
    	
        $this->postSession();
    	
   	 	//set_language($this->getLanguage());
    	date_default_timezone_set($this->getTimezone());
		new Frogg_Db_Sql("SET `time_zone` = '".date('P')."'");
    }
    
    /**
     * 
     * Defines what should be done before the session is created
     */
    public function preSession(){}
    
    /**
     * 
     * Defines what should be done after the session is created
     */
    public function postSession(){}
    
    /**
     * 
     * Defines what should be done when the user is logged on the system
     */
    public function loggedBoot(){}
    
    /**
     * 
     * Defines what should be done when the user is not logged on the system
     */
    public function unloggedBoot(){}
    
    /**
     * 
     * Checks whether or not the current user has the permission to access this page
     */
    public function checkPermission(){
    	return $this->session->getAccessLevel() >= $this->accessLevel;
    }
    
    /**
     * 
     * Defines what should be done when the permission to this page is accepted
     */
    public function permissionAccepted(){}
    
    /**
     * 
     * Defines what should be done when the permission to this page is denied
     */
	public function permissionDenied(){
    	$this->_redirect(HOST.'login?to='.HOST.$_SERVER['REDIRECT_URL']);
    }
    
    /**
     * @return the language that should be used by the Frogg library 
     */
    public function getLanguage(){
    	return 'pt_BR';
    }
    
    /**
     * @return the timezone that should be used by the Frogg library 
     */
    public function getTimezone(){
    	return 'America/Sao_Paulo';
    }
    
	/**
     * @param $layout the layout file name on the layouts/scripts folder
     */
    public function setLayout($layout){
    	$this->_helper->layout()->setLayout($layout);
    }
    
    /**
     * @return the timezone that should be used by the Frogg library 
     */
    public function getSession(){
    	return new Frogg_Session();
    }

}