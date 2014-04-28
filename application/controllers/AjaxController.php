<?php

class AjaxController extends Project_Controller{
	
	//Desarca um episodio/seriado/serie como visto
	public function unwatchEpisodeAction(){
		if($this->_request->isPost()){
			$id 			= $this->_request->getParam('id');
			$user_id 		= $this->session->getUser();
			$series			= new Application_Model_Series();
			if(is_numeric($id) && $series->load($id)){
				$series_dao 	= new Application_Model_SeriesDAO();
				$watching_dao	= new Application_Model_WatchingDAO();
				if ($series_dao->isSeason($id)){
					$list = $watching_dao->unmarkSeason($user_id, $id);
				} else {
					$list = $watching_dao->unmarkEpisode($user_id, $id);
				}
				echo json_encode($list); die;
			}
		}
		echo json_encode(array());die;
	}
	
	//Marca um episodio/seriado/serie como visto
	public function watchEpisodeAction(){
		if($this->_request->isPost()){
			$id 			= $this->_request->getParam('id');
			$user_id 		= $this->session->getUser();
			$series			= new Application_Model_Series();
			if(is_numeric($id) && $series->load($id)){
				$series_dao 	= new Application_Model_SeriesDAO();
				$watching_dao	= new Application_Model_WatchingDAO();
				if ($series_dao->isSeason($id)){
					$list = $watching_dao->markSeason($user_id, $id);
				} else {
					$list = $watching_dao->markEpisode($user_id, $id);
				}
				echo json_encode($list); die;
			}
		}
		echo json_encode(array());die;
	}
	
	//Envia para a home todos episodios que esse usuario ja viu
	public function loadWatchedAction(){
		if($this->_request->isPost()){
			$user_id 		= $this->session->getUser();
			$watching_dao 	= new Application_Model_WatchingDAO();
			$episodes 	 	= $watching_dao->episodesWatchedByUser($user_id);
			echo json_encode($episodes);die;
		}
		echo json_encode(array());die;
	} 
	
	//Envia para a home as temporadas e episodios do seriado clicado
	public function loadSeasonsAction(){
		if($this->_request->isPost()){
			$id = $this->_request->getParam('id');
			if(is_numeric($id)){
				$series = new Application_Model_Series();
				if($series->load($id)){
					$series_dao = new Application_Model_SeriesDAO();
					$episodes   = $series_dao->getEpisodes($id);
					
					$first 		= true; $season_num = 0; $json_ar	= array();
					$season_episodes = array(); $season_data = array();
					foreach ($episodes as $episode) {
						if($episode->season_id == 0){
							$json_ar[$season_num++][0] = $episode->jsonObj();
						}
					}
					
					$season_num = 0; $first = true;
					foreach ($episodes as $episode) { 
						if($episode->season_id != 0){ 
							$first = false;
							array_push($season_episodes, $episode->jsonObj());
						} else if($episode->season_id == 0 && !$first){
							$json_ar[$season_num++][1] = $season_episodes;
							$season_episodes = array();
						}
					}
					$json_ar[$season_num][1] = $season_episodes;
					$obj = new stdClass();
					$obj->name    = $series->name;
					$obj->image   = $series->image();
					$obj->runtime = $series->runtime.' minutos';
					$obj->status  = $series->status();
					$json_ar = array('episodes'=>$json_ar,'series'=>$obj);
					echo json_encode($json_ar);
					die;
				}
			}
		}
		echo json_encode(array());die;
	}
	
	//Comeca a acompanhar um seriado
	public function watchSeriesAction(){
		if($this->_request->isPost()){
			$id = $this->_request->getParam('id');
			if(is_numeric($id)){
				$series = new Application_Model_Series();
				if($series->load($id)){
					$series_dao = new Application_Model_WatchingDAO();
					$series_dao->watchSeries($id, $this->session->getUser());
					echo 'ok';
					die;
				}
			}
		}
		echo 'invalid';die;
	}
	
	//Para de acompanhar um seriado
	public function unwatchSeriesAction(){
		if($this->_request->isPost()){
			$id = $this->_request->getParam('id');
			if(is_numeric($id)){
				$series = new Application_Model_Series();
				if($series->load($id)){
					$series_dao = new Application_Model_WatchingDAO();
					$series_dao->unwatchSeries($id, $this->session->getUser());
					echo 'ok';
					die;
				}
			}
		}
		echo 'invalid';die;
	}

	public function validateUrlAction(){
		if($this->_request->isPost()){
			$url = $this->_request->getParam('url');
			$url = Frogg_Validator::sanitizeUrl($url);
			if(!Frogg_Validator::validate(V_LINK, $url)){ echo 'invalid'; die; }
			$link = new Application_Model_Link();
			if(!$link->loadField('url', $url)){ echo 'ok'; die; }
		}
		echo 'invalid';die;
	}
	
	public function validateEmailAction(){
		if($this->_request->isPost()){
			$email = $this->_request->getParam('email');
			if(!Frogg_Validator::validate(V_EMAIL, $email)){ echo 'invalid'; die; }
			$user = new Application_Model_User();
			if(!$user->loadField('email', $email)){ echo 'ok'; die; }
		}
		echo 'invalid';die;
	}
	
}