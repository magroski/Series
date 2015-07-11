<?php

class UserController extends Project_Controller{
	
	protected $accessLevel = 5;
	
	public function homeAction(){
		$watching_dao = new Application_Model_WatchingDAO();
		$my_series 	  = $watching_dao->seriesWatchedByUser($this->session->getUser());
		$this->view->my_series = $my_series;
		if(!empty($my_series)){
			$this->view->eager_json = $this->preLoadJson($my_series[0]->id);
		}
    }
    
	public function calendarAction(){
		$period 		= $this->_request->getParam('period');
		if($period!='0' && !preg_match("/^(\d\d)-(\d\d\d\d)$/", $period)){ $period = 0; }
		$watching_dao 	= new Application_Model_WatchingDAO();
		$my_series 		= $watching_dao->seriesWatchedByUser($this->session->getUser());
		$series_id 		= array();
		foreach ($my_series as $series) {
			array_push($series_id, $series->id);
		}
		$series_id 	= implode(',', $series_id);
		
		$series_dao = new Application_Model_SeriesDAO();
		$calendar 	= $series_dao->myCalendar($series_id,$period);
		
		$episodes_id = array();
		foreach ($calendar as $day => $episodes){
			foreach($episodes as $episode){
				$episodes_id[] = $episode->id;
			}
		}
		$seen_episodes = $watching_dao->filterByEpisodesList($this->session->getUser(),$episodes_id);
		
		$this->view->period   		= $period;
		$this->view->calendar 		= $calendar;
		$this->view->seen_episodes 	= $seen_episodes;
    }
    
    private function preLoadJson($id){
		$series = new Application_Model_Series();
		if($series->load($id)){
			$series_dao = new Application_Model_SeriesDAO();
			$episodes   = $series_dao->getEpisodes($id);
			
			$first 		= true; $season_num = 0; $json_ar	= array();
			foreach ($episodes as $episode) {
				if($episode->season_id == 0){
					$json_ar[$season_num++][0] = $episode->jsonObj();
				}
			}
			
			$season_episodes = array();
			$season_num = 0; $is_series = true;
			foreach ($episodes as $episode) {
				if($episode->season_id != 0){
					$is_series = false;
					array_push($season_episodes, $episode->jsonObj());
				} else if($episode->season_id == 0 && !$is_series){
					$json_ar[$season_num++][1] = $season_episodes;
					$season_episodes = array();
				}
			}
			$json_ar[$season_num][1] = $season_episodes;
			$obj = new stdClass();
			$obj->name 		= $series->name;
			$obj->image 	= $series->image();
			$obj->runtime 	= $series->runtime.' minutos';
			$obj->status 	= $series->status();
			$json_ar = array('episodes'=>$json_ar,'series'=>$obj);
			return json_encode($json_ar);
		}
		return json_encode(array());
    }

}