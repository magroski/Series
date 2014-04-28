<?php

class SearchController extends Project_Controller{
	
    public function indexAction(){
    	$key = $this->_request->getParam('q');
		if( is_null($key) || $key=='' ){
    		$this->_redirect('/');
    	}
    	
    	$generator 	= new Frogg_Permalink($key,'');
    	$permalink	= $generator->slug();
    	    			
    	$this->_redirect($this->view->url(array('permalink' => $permalink),'searchPermalink',true));
    }
    
    public function permalinkAction(){
    	$permalink = $this->_request->getParam('permalink');
    	if( is_null($permalink) || $permalink=='' ){
    		$this->_redirect('/');
    	}
    	
    	$page = 1;
    	if( !is_null($this->_request->getParam('page')) ){
    		$page = $this->_request->getParam('page');
    	}
    	
		$search = str_replace('-',' ',$permalink);
    	$tmp = new Application_Model_Search();
		
        $this->view->permalink 	= $permalink;
        $this->view->series 	= $tmp->searchKey($search,$page);
        if(empty($this->view->series)){ $search = $search.' | Nenhum resultado encontrado';}
        $this->view->search		= $search;
        $this->view->description= 'Resultados de busca de '.(ucwords($search));
        $this->view->og    = '- Busca por '.(ucwords($search));
    	$this->view->og_url= 'busca/'.$permalink;
        
    	$this->view->headTitle()->prepend((ucwords($search).$this->pageTitle($page)));
    	if($page>1){
			$this->view->og_url .= '/'.$page;
    		$this->view->headTitle()->append('Página '.$page);
		}
    }
    
}