<?php

class Application_Model_SeriesDAO extends Frogg_DAO{
	
	public function myCalendar($series_id,$period){
		$today = new Frogg_Time_Time(time());
		if($period){
			$period = explode('-', $period);
			$today 	= new Frogg_Time_Time($period[1].'-'.$period[0].'-01');
		}
		$first_day 	= new Frogg_Time_Time($today->getYear().'-'.$today->getMonth().'-01');
		$first_day  = $first_day->getUnixTstamp();
		
		$days_in_month  = date('t',mktime(0,0,0,$today->getMonth(),1,$today->getYear()));
		$last_day 		= new Frogg_Time_Time($today->getYear().'-'.$today->getMonth().'-'.$days_in_month);
		$last_day  		= $last_day->getUnixTstamp();
		
		//var_dump('SELECT * FROM `series` WHERE `series_id` IN ('.$series_id.') AND (`airdate` BETWEEN '.$first_day.' AND '.$last_day.')');die;
		
		$calendar	= array();
		$sql	 	= new Frogg_Db_Sql('SELECT * FROM `series` WHERE `series_id` IN ('.$series_id.') AND (`airdate` BETWEEN '.$first_day.' AND '.$last_day.')');
		if($sql->rows()){
			while($row=$sql->fetch()){
				$episode = new Application_Model_Series();
				$episode->loadData($row);
				$air_date = new Frogg_Time_Time($row['airdate']);
				if(empty($calendar[$air_date->getDayNoZero()])){
					$calendar[$air_date->getDayNoZero()] = array();
				}
				array_push($calendar[$air_date->getDayNoZero()], $episode);
			}
		}
		return $calendar;
	}
	
	public function isSeries($id){
		return $this->count('WHERE `id` = '.$id.' AND `series_id` = 0');
	}
	
	public function isSeason($id){
		return $this->count('WHERE `id` = '.$id.' AND `series_id` != 0 AND `season_id` = 0');
	}
	
	public function getSeries($amount = Frogg_DAO::ALL, $page = 1){
		$results = $this->search($amount,$page,'WHERE `series_id` = 0','*','ORDER BY `image` DESC');
		return $results;
	}
	
	public function searchByRageId($rage_id){
		$results = $this->search(1,1,'WHERE `rage_id` ='.$rage_id);
		if(!empty($results)){
			return $results[0];
		}
		return false;
	}
	
	public function getEpisodes($series_id){
		$results = $this->search(Frogg_DAO::ALL,1,'WHERE `series_id` = '.$series_id,'*','ORDER BY `order` ASC');
		return $results;
	}
	
	public function getSeasonEpisodes($season_id){
		$results = $this->search(Frogg_DAO::ALL,1,'WHERE `season_id` = '.$season_id,'*','ORDER BY `order` ASC');
		return $results;
	}
	
	public function getSeasonEpisodesId($season_id){
		$sql 	 = new Frogg_Db_Sql('SELECT `id` FROM `series` WHERE `season_id` = '.$season_id);
		$results = array();
		if($sql->rows()){
			while($row=$sql->fetch()){
				array_push($results, $row['id']);
			}
		}
		return $results;
	}
	
	public function getByRelease($release_name){
		$results = $this->search(1,1,'WHERE `release` = "'.$release_name.'"');
		if(!empty($results)){
			return $results[0];
		}
		return false;		
	}
	
}