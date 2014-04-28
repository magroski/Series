<?php

class Application_Model_WatchingDAO extends Frogg_DAO{
	
	public function episodesWatchedByUser($user_id){
		$sql = new Frogg_Db_Sql('SELECT `episode_id` FROM `watching` WHERE `user_id` = '.$user_id);
		$episodes = array();
		if($sql->rows()){
			while($row=$sql->fetch()){
				array_push($episodes, $row['episode_id']);
			}
		}
		return $episodes;
	}
	
	public function seriesWatchedByUser($user_id){
		$results = $this->search(Frogg_DAO::ALL,1,' WHERE `user_id` = '.$user_id,' DISTINCT(series_id)');
		$user_watching = array();
		foreach ($results as $result) {
			array_push($user_watching, new Application_Model_Series($result->series_id));
		}
		return $user_watching;
	}
	
	public function isWatching($user_id, $series_id){
		return $this->count('WHERE `series_id` = '.$series_id.' AND `user_id` = '.$user_id);
	}
	
	public function watchSeries($series_id, $user_id){
		$watching = new Application_Model_Watching($user_id, $series_id, $series_id, $series_id);
		$watching->save();
		/*
		$sql = new Frogg_Db_Sql('SELECT `id`,`season_id` FROM `series` WHERE `series_id` ='.$series_id);
		if($sql->rows()){
			while($row=$sql->fetch()){
				if(!$row['season_id']){$row['season_id']=$row['id'];}
				$watching = new Application_Model_Watching($user_id, $series_id, $row['season_id'],$row['id'], 0);
				$watching->save();
			}
		}
		*/
	}
	
	public function markSeason($user_id, $season_id){
		$sql 	 = new Frogg_Db_Sql('SELECT `id`,`series_id` FROM `series` WHERE `season_id` ='.$season_id);
		$epi_arr = array();
		if($sql->rows()){
			while($row=$sql->fetch()){
				$watching = new Application_Model_Watching($user_id, $row['series_id'], $season_id,$row['id']); //Episodes
				$watching->save();
				array_push($epi_arr, $watching->episode_id);
			}
			$watching = new Application_Model_Watching($user_id, $watching->series_id, $season_id,$season_id); //Season
			$watching->save();
			array_push($epi_arr, $watching->episode_id);
		}
		return $epi_arr;
	}
	
	public function markEpisode($user_id, $id){
		$series = new Application_Model_Series();
		if($series->load($id)){
			$watching = new Application_Model_Watching($user_id,$series->series_id,$series->season_id,$series->id);
			$watching->save();
			$episodes = array($watching->episode_id);
			
			$series_dao = new Application_Model_SeriesDAO();
			$episodes_id= $series_dao->getSeasonEpisodesId($series->season_id);
			$epi_count  = count($episodes_id);
			
			$marked = $this->search(Frogg_DAO::ALL,1,'WHERE `user_id` = '.$user_id.' AND `season_id` = '.$series->season_id);
			$marked_count = count($marked);
			
			if($marked_count==$epi_count){
				$watching = new Application_Model_Watching($user_id,$series->series_id,$series->season_id,$series->season_id);
				$watching->save();
				array_push($episodes, $watching->episode_id);
			}
			
			return $episodes;
		}
		return array();
	}
	
	public function unwatchSeries($series_id, $user_id){
		$sql = new Frogg_Db_Sql('DELETE FROM `watching` WHERE `series_id` ='.$series_id.' AND `user_id` = '.$user_id);
	}
	
	public function unmarkSeason($user_id, $season_id){
		$epi_arr  = array();
		$results  = $this->search(Frogg_DAO::ALL,1,'WHERE `season_id` = '.$season_id.' AND `user_id` = '.$user_id);
		foreach ($results as $watching){
			array_push($epi_arr, $watching->episode_id);
		}
		$sql = new Frogg_Db_Sql('DELETE FROM `watching` WHERE `season_id` = '.$season_id.' AND `user_id` = '.$user_id);
		return $epi_arr;
	}
	
	public function unmarkEpisode($user_id, $id){
		$sql 		= new Frogg_Db_Sql('DELETE FROM `watching` WHERE `episode_id` = '.$id.' AND `user_id` = '.$user_id);
		$episodes   = array($id);
		
		$series = new Application_Model_Series();
		if($series->load($id)){
			$series_dao = new Application_Model_SeriesDAO();
			$episodes_id= $series_dao->getSeasonEpisodesId($series->season_id);
			$epi_count  = count($episodes_id);
				
			$marked = $this->search(Frogg_DAO::ALL,1,'WHERE `user_id` = '.$user_id.' AND `season_id` = '.$series->season_id);
			$marked_count = count($marked);
				
			if($marked_count < ($epi_count+1) ){
				$watching = new Application_Model_Watching();
				if($watching->loadFields('user_id', $user_id, 'episode_id', $series->season_id)){
					array_push($episodes, $series->season_id);
					$watching->delete();
				}
			}
		}
		
		return $episodes;
	}
	
}