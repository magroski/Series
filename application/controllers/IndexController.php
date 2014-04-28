<?php

class IndexController extends Project_Controller{
	
	private $offline = false;
	
	public function indexAction(){
		if($this->offline){ $this->_helper->layout()->setLayout('full'); $this->renderScript('index/offline.phtml'); return; }
	}
	
	public function seriesAction(){
		$series_dao = new Application_Model_SeriesDAO();
		$series 	= $series_dao->getSeries(50);
		
		$this->view->series = $series;
	}
	
	public function infiniteScrollAction(){
		$this->_helper->layout()->setLayout('empty');
		$page = $this->_request->getParam('page');
		$series_dao = new Application_Model_SeriesDAO();
		$series 	= $series_dao->getSeries(50,$page);
		
		$this->view->series = $series;
	}
	
	public function seriesInfoAction(){
		$permalink 	= $this->_request->getParam('permalink');
		$season 	= $this->_request->getParam('s');
		$episode 	= $this->_request->getParam('e');
		if(empty($permalink)){ $this->_redirect($this->view->url(array(),'series',true)); }
		
		$series		= new Application_Model_Series();
		if(!$series->loadField('permalink', Frogg_Db_Sql::escapeString($permalink))){ $this->_redirect($this->view->url(array(),'series',true)); }
		
		if(is_numeric($season)){  $this->view->season  = $season;  } else { $this->view->season	 = 0; }
		if(is_numeric($episode)){ $this->view->episode = $episode; } else { $this->view->episode = 0; }
		
		$series_dao = new Application_Model_SeriesDAO();
		$episodes 	= $series_dao->getEpisodes($series->id);

		$this->view->series   = $series;
		$this->view->episodes = $episodes;
		
		if($this->isLogged){
			$watch_dao  = new Application_Model_WatchingDAO();
			$watches    = $watch_dao->isWatching($this->session->getUser(),$series->id);
			$this->view->watches = $watches;
		}
	}
	
    public function dbAction(){
    	$model = new Application_Model_NewSeries();
		Frogg_DAO::createDB($model);
		
		for ($i = 1 ; $i < 40100 ; $i++){
			$series = new Application_Model_NewSeries($i,Application_Model_NewSeries::UNREAD);
			$series->save();
		}
		
		$model = new Application_Model_NewSeriesControl();
		Frogg_DAO::createDB($model);
		$model->newseries_id = 1;
		$model->save();
    	
		$model = new Application_Model_Release();
		Frogg_DAO::createDB($model);
		$model = new Application_Model_ReleaseControl();
		Frogg_DAO::createDB($model);
		$model->release_id = 1;
		$model->save();
		
		$model = new Application_Model_Scheduled();
		Frogg_DAO::createDB($model);
		$model = new Application_Model_ScheduledControl();
		Frogg_DAO::createDB($model);
		$model->scheduled_id = 1;
		$model->save();
		
    	$model = new Application_Model_Series();
		Frogg_DAO::createDB($model);
		
    	$model = new Application_Model_SeriesBucket();
		Frogg_DAO::createDB($model);
		
		$model = new Application_Model_User();
		Frogg_DAO::createDB($model);
		
		$model = new Application_Model_Watching();
		Frogg_DAO::createDB($model);
		
    	die('DB created');
    }

}