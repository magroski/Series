<?php

class ReaderController extends Project_Controller{
	
	/**
	 * Reads new shows data and insert them on database, if they are eligible.
	 * If the shows is invalid (country/classification), it will be marked as INVALID.
	 * If the show is valid, all its episodes will be read and inserted on 'series' table
	 */
    public function indexAction(){
    	$logger 	= new Frogg_Log('/home2/bleachse/public_html/seriando/log/new_series.log');
    	$control    = new Application_Model_NewSeriesControl(1);
    	$series_control = $control->nextSeries();
    	if(!$series_control){ $logger->warn('No new series'); $logger->close(); die();}
    	
    	$xml = new XMLReader();
	    if(!$xml->open('http://services.tvrage.com/feeds/full_show_info.php?sid='.$series_control->rage_id)){
	    	$logger->err('Failed to open input file : '.$series_control->rage_id);
	   		$logger->close();
	        die();
	    }
	    $logger->info('Starting to log new_series_id : '.$series_control->rage_id);
	    $series = new Application_Model_Series();
	    while ($xml->read()){
	    	while($xml->read() && $xml->name != 'name');
	    	if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'name'){
				$series->name = $xml->readString();
	    	}
    		while($xml->read() && $xml->name != 'showid');
    		if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'showid'){
    			$series->rage_id = $xml->readString();
    			$double_check = new Application_Model_Series();
    			$logger->info('Double check [my_id]:[rage_id] '.$series_control->rage_id.':'.$series->rage_id);
    			if($double_check->loadField('rage_id', $series->rage_id)){
    				//This piece of code will probably NEVER run.
    				//Why? NewSeriesControl only fetches UNREAD series AND 'newseries.rage_id' MUST be unique.
    				//If this ever happen: Either you forgot to set 'rage_id' as unique or the DB crapped itself
    				$series_control->flag = Application_Model_NewSeries::ERROR;
    				$series_control->update();
    				$logger->err('Series already registered rage_id:'.$series->rage_id);
    				$logger->close();
    				die;
    			}
    		}
    		while($xml->read() && $xml->name != 'origin_country');
    		if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'origin_country'){
    			if(!$this->allowedCountry($xml->readString())){
    				$series_control->flag = Application_Model_NewSeries::INVALID;
    				$series_control->update();
    				$logger->warn('Series country not allowed rage_id:'.$series->rage_id);
    				$logger->close();
    				die;
    			}
    		}
    		while($xml->read() && $xml->name != 'status');
    		if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'status'){
    			$series->status = $this->parseStatus($xml->readString());
    		}
    		while($xml->read() && $xml->name != 'classification');
    		if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'classification'){
    			if($this->avoidedClassification($xml->readString())){
    				$series_control->flag = Application_Model_NewSeries::INVALID;
    				$series_control->update();
    				$logger->warn('Series classification invalid rage_id:'.$series->rage_id);
    				$logger->close();
    				die;
    			}
    		}
    		while($xml->read() && $xml->name != 'runtime');
    		if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'runtime'){
    			$series->runtime = $xml->readString();
    		}
    		$series->image	   = 'default.png';
    		$series->timestamp = time();
    		$series->permalink = $series->permalinkFor('name');
    		$series->order	   = NEW_SERIES;
	    	if(!$series->rage_id){
	    		$series_control->flag = Application_Model_NewSeries::ERROR;
	    		$series_control->update();
	    		$logger->err('Empty XML');
	    		$logger->close();
	    		die;
	    	}
    		$series_id = $series->save();
    		$logger->ok('Saved');
    		
    		$series_bucket = new Application_Model_SeriesBucket($series->name,$series->permalink);
    		$series_bucket->save();
    		
	    	$has_episodes = $xml->next('Episodelist');
    		if($has_episodes){
    			while($xml->read() && !($xml->nodeType == XMLReader::END_ELEMENT && $xml->name == 'Episodelist')){
    				while($xml->read() && $xml->name != 'Season');//Goes to next <Season>
    				if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'Season'){
		    			$season_num = $xml->getAttribute('no');
		    			$season = new Application_Model_Series();
		    			$season->series_id 	= $series_id;
		    			$season->name 	 	= $season_num.'ª Temporada';
		    			$season->order		= $season_num * 1000; //According to format XXX.000 for seasons 
		    			if($season_num < 10){ $season_num = '0'.$season_num; }
		    			$season->release 	= $series->name.' Season '.$season_num;
		    			$season->timestamp 	= time();
		    			$season_id 			= $season->save();
		    			$release			= new Application_Model_Release($season_id,$season->release,time());
		    			$release->save();
    				}
    				while($xml->read()){ //Season episodes reading
	    				if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'episode'){ //Found new episode
		    				$episode = new Application_Model_Series();
		    				$episode->season_id = $season_id;
		    				$episode->series_id = $series_id;
		    				$episode->timestamp = time();
			    			while($xml->read() && $xml->name != 'seasonnum');
				    		if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'seasonnum'){
				    			$episode_num = $xml->readString();
				    			$episode->release = $series->name.' S'.$season_num.'E'.$episode_num;
				    			$episode->order   = $season->order + ($episode_num*1); //According to format XXX.yyy for episodes
			    			}
			    			while($xml->read() && $xml->name != 'airdate');
				    		if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'airdate'){
				    			$date = new Frogg_Time_Time($xml->readString());
    							$episode->airdate = $date->getUnixTstamp();
			    			}
			    			while($xml->read() && $xml->name != 'title');
				    		if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'title'){
				    			$episode->name = $xml->readString();
			    			}
			    			$episode_id = $episode->save();
			    			$release	= new Application_Model_Release($episode_id,$episode->release,$episode->airdate);
		    				$release->save();
		    				$xml->next('episode');
						} else if($xml->nodeType == XMLReader::END_ELEMENT && $xml->name == 'Season'){ //Found season finale
							break;
						}
    				}//END - Season episodes reading
    			}
    		}
    		$series_control->flag = Application_Model_NewSeries::READ;
    		$series_control->update();
			$logger->ok('Great Success !!');
    		$logger->close();    		
			die;
	    }
    }
    
    /**
     * Reads the full schedule as far as possible and schedule the data to be read [US version]
     */
    public function fullAction(){
    	$logger = new Frogg_Log('/home2/bleachse/public_html/seriando/log/calendar_US.log');
    	$xml = new XMLReader();
    	if(!$xml->open('http://services.tvrage.com/feeds/fullschedule.php?country=US')){
    		$logger->err('Failed to open input file');
    		$logger->close();
    		die;
    	}
    	$logger->info('Starting to index full schedule');
    	$series = new Application_Model_Series();
    	while ($xml->read()){
    		while($xml->read() && $xml->name != 'DAY');//Goes to next <DAY>
    		$timestamp = new Frogg_Time_Time($xml->getAttribute('attr'));
    		$timestamp = $timestamp->getUnixTstamp();
    		while($xml->read()){ //Daily shows reading
    			if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'show'){ //Found new show
    				$episode_name = $xml->getAttribute('name');
    				$show_id = '';
    				while($xml->read() && $xml->name != 'sid'); //Found show id
    				if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'sid'){
    					$show_id = $xml->readString();
    				}
    				while($xml->read() && $xml->name != 'ep'); //Found episode air order
    				if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'ep'){
    					$episode_num = $xml->readString();
    					$scheduled = new Application_Model_Scheduled($show_id,'http://services.tvrage.com/tools/quickinfo.php?show='.urlencode($episode_name).'&ep='.$episode_num,Application_Model_Scheduled::UNREAD,$timestamp);
    					$scheduled->save();
    					$logger->ok('Saved : '.$scheduled->link);
    				}
    			$xml->next('show');
    			} else if($xml->nodeType == XMLReader::END_ELEMENT && $xml->name == 'DAY'){ //Found </DAY>
    				break;
    			}
    		}//END - Daily shows reading
    	}
    	$logger->close();
    	die;
    }
    
    /**
     * Reads the full schedule as far as possible and schedule the data to be read [UK version]
     */
    public function fullUkAction(){
    	$logger = new Frogg_Log('/home2/bleachse/public_html/seriando/log/calendar_UK.log');
    	$xml = new XMLReader();
    	if(!$xml->open('http://services.tvrage.com/feeds/fullschedule.php?country=UK')){
    		$logger->err('Failed to open input file');
    		$logger->close();
    		die;
    	}
    	$logger->info('Starting to index full schedule');
    	$series = new Application_Model_Series();
    	while ($xml->read()){
    		while($xml->read() && $xml->name != 'DAY');//Goes to next <DAY>
    		$timestamp = new Frogg_Time_Time($xml->getAttribute('attr'));
    		$timestamp = $timestamp->getUnixTstamp();
    		while($xml->read()){ //Daily shows reading
    			if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'show'){ //Found new show
    				$episode_name = $xml->getAttribute('name');
    				$show_id = '';
    				while($xml->read() && $xml->name != 'sid'); //Found show id
    				if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'sid'){
    					$show_id = $xml->readString();
    				}
    				while($xml->read() && $xml->name != 'ep'); //Found episode air order
    				if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'ep'){
    					$episode_num = $xml->readString();
    					$scheduled = new Application_Model_Scheduled($show_id,'http://services.tvrage.com/tools/quickinfo.php?show='.urlencode($episode_name).'&ep='.$episode_num,Application_Model_Scheduled::UNREAD,$timestamp);
    					$scheduled->save();
    					$logger->ok('Saved : '.$scheduled->link);
    				}
    			$xml->next('show');
    			} else if($xml->nodeType == XMLReader::END_ELEMENT && $xml->name == 'DAY'){ //Found </DAY>
    				break;
    			}
    		}//END - Daily shows reading
    	}
    	$logger->close();
    	die;
    }
	
	/**
     * Reads the full schedule as far as possible and schedule the data to be read [CANADA version]
     */
    public function fullCaAction(){
    	$logger = new Frogg_Log('/home2/bleachse/public_html/seriando/log/calendar_CA.log');
    	$xml = new XMLReader();
    	if(!$xml->open('http://services.tvrage.com/feeds/fullschedule.php?country=CA')){
    		$logger->err('Failed to open input file');
    		$logger->close();
    		die;
    	}
    	$logger->info('Starting to index full schedule');
    	$series = new Application_Model_Series();
    	while ($xml->read()){
    		while($xml->read() && $xml->name != 'DAY');//Goes to next <DAY>
    		$timestamp = new Frogg_Time_Time($xml->getAttribute('attr'));
    		$timestamp = $timestamp->getUnixTstamp();
    		while($xml->read()){ //Daily shows reading
    			if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'show'){ //Found new show
    				$episode_name = $xml->getAttribute('name');
    				$show_id = '';
    				while($xml->read() && $xml->name != 'sid'); //Found show id
    				if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'sid'){
    					$show_id = $xml->readString();
    				}
    				while($xml->read() && $xml->name != 'ep'); //Found episode air order
    				if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'ep'){
    					$episode_num = $xml->readString();
    					$scheduled = new Application_Model_Scheduled($show_id,'http://services.tvrage.com/tools/quickinfo.php?show='.urlencode($episode_name).'&ep='.$episode_num,Application_Model_Scheduled::UNREAD,$timestamp);
    					$scheduled->save();
    					$logger->ok('Saved : '.$scheduled->link);
    				}
    			$xml->next('show');
    			} else if($xml->nodeType == XMLReader::END_ELEMENT && $xml->name == 'DAY'){ //Found </DAY>
    				break;
    			}
    		}//END - Daily shows reading
    	}
    	$logger->close();
    	die;
    }
    
    /**
     * This function fetches scheduled episodes data and inserts them in the final 'series' table.
     * Also, this function is responsible for updating episodes information, creating new seasons
     * and inserting new series into 'newseries' table
     */
    public function fetchDataAction(){
    	$logger = new Frogg_Log('/home2/bleachse/public_html/seriando/log/fetch.log');
		try {
			for($i = 0 ; $i < 3 ; $i++){
				//Fetch the next scheduled episode to be read
				$control   = new Application_Model_ScheduledControl(1);
				$scheduled = $control->nextSchedule();
				if(!$scheduled){$logger->warn('No more unread episodes'); $logger->close(); die;}
				
				$logger->info('Starting to read episode [id]:[link] : '.$scheduled->id.' : '.$scheduled->link);
				
				//Checking if it's a valid show
				$series_status = new Application_Model_NewSeries();
				if(!$series_status->loadField('rage_id', $scheduled->rage_id)){ //If not found, it's a new show
					$scheduled->read = Application_Model_Scheduled::READ;
					$scheduled->update();
					$new_series = new Application_Model_NewSeries($scheduled->rage_id,Application_Model_NewSeries::UNREAD);
					$new_series->save();
					$logger->warn('NEW SERIES : '.$scheduled->rage_id);
					$logger->close();
					die;
				} else {
					if($series_status->flag == Application_Model_NewSeries::INVALID){
						$scheduled->read = Application_Model_Scheduled::READ;
						$scheduled->update();
						$logger->warn('Invalid Classification : '.$scheduled->rage_id);
						$logger->close();
						die;
					}
					if($series_status->flag == Application_Model_NewSeries::ERROR){ //ERROR means that the XML was previously empty...
						$scheduled->read = Application_Model_Scheduled::READ;
						$scheduled->update();
						$series_status->flag = Application_Model_NewSeries::UNREAD; //... so we put it back on the "to read" queue
						$series_status->update();
						$logger->err('Check series XML : '.$scheduled->rage_id);
						$logger->close();
						die;
					}
					
					if(!($epi_info = file_get_contents($scheduled->link))){$logger->err('Erro no file_get_contents');$logger->close();die;}
					
					//Fetch show ID
					$epi_info = preg_replace('(\r|\n|\t)', '', $epi_info);
					$epi_info = explode('Show ID@', $epi_info);
					$epi_info = explode('Show Name@', $epi_info[1]);
					$rage_id  = $epi_info[0];
					
					//Fetch episode information. Example : 32x89^Pending^02/Jan/2014
					$epi_info = explode('Episode Info@', $epi_info[1]);
					if(count($epi_info)==1){
						$scheduled->read = Application_Model_Scheduled::READ;
						$scheduled->update();
						$logger->err('Episode Info is missing : '.$scheduled->link);
						$logger->close();
						die;
					}
					$epi_info = explode('Episode URL@', $epi_info[1]);
					$epi_data = $epi_info[0];
					$epi_data   = explode('^', $epi_data);
					$season_num = explode('x', $epi_data[0]);
					if($epi_data[2]=="//" || strpos($epi_data[2],'00')!==FALSE){ //Date is unavailable
						$air_date = 0;
					} else {
						$air_date	= DateTime::createFromFormat('d/F/Y',$epi_data[2]);
						$air_date	= new Frogg_Time_Time($air_date->format('Y-m-d'));
						$air_date	= $air_date->getUnixTstamp();;
					}
					$series		= new Application_Model_Series();
					$series->loadField('rage_id', $rage_id);
					$series_dao = new Application_Model_SeriesDAO();
					$episode 	= $series_dao->getByRelease($series->name.' S'.$season_num[0].'E'.$season_num[1]);
					if($episode){
						$episode->name 	  = $epi_data[1];
						$episode->airdate = $air_date;
						$episode->update();
						$logger->ok('Episode updated : '.$episode->id.' - '.$episode->release);
					} else {
						$season_data = $series_dao->getByRelease($series->name.' Season '.$season_num[0]);
						if(!$season_data){
							$season_data = new Application_Model_Series();
							$season_data->series_id = $series->id;
					
							if(strpos($season_num[0],'0')===0){ $season_num[0] = substr($season_num[0],1); } //Removes leading zero
					
							$season_data->order		= $season_num[0] * 1000; //According to format XXX.000 for seasons
							$season_data->name 	 	= $season_num[0].'ª Temporada';
							if($season_num[0] < 10){ $season_num[0] = '0'.$season_num[0]; } //Apply leading zero
							$season_data->release 	= $series->name.' Season '.$season_num[0];
							$season_data->timestamp = time();
							$season_data->save();
							$logger->ok('New season saved : '.$season_data->id.' - '.$season_data->release);
						}
						$new_episode= new Application_Model_Series();
						$new_episode->series_id = $series->id;
						$new_episode->season_id = $season_data->id;
						$new_episode->name      = $epi_data[1];
						$new_episode->release   = $series->name.' S'.$season_num[0].'E'.$season_num[1];
						$new_episode->order		= $season_data->order + ($season_num[1]*1); //According to format XXX.yyy for episodes
						$new_episode->airdate   = $air_date;
						$new_episode->timestamp = time();
						$episode_id = $new_episode->save();
						$logger->ok('Episode saved : '.$episode_id.' - '.$new_episode->release);
					
						$release	= new Application_Model_Release($episode_id,$new_episode->release,$new_episode->airdate);
						$release->save();
						$logger->ok('Release saved');
					}
				}
				$scheduled->read = Application_Model_Scheduled::READ;
				$scheduled->update();
				$logger->ok('Data fetched and updated');
			}
			$logger->close();
			die;
		} catch(Exception $e){
			$logger->fatal($e->getMessage());
			$logger->close();
			die;
		}
    }
    
    //Clean from the schedule table all episodes older than 3 days
    public function cleanScheduleAction(){
    	$actual = new Frogg_Time_Time();
    	$cleaning_day   = $actual->subtract(3*24*60*60);
    	$cleaning_stamp = $cleaning_day->getUnixTstamp();
    	$sql = new Frogg_Db_Sql('DELETE FROM `scheduled` WHERE `timestamp` < '.$cleaning_stamp);
    	die;
    }
    
    //Clean from the schedule table all episodes from invalid series
    public function cleanInvalidScheduleAction(){
    	$sql = new Frogg_Db_Sql('SELECT `rage_id` FROM `newseries` WHERE `flag` = '.Application_Model_NewSeries::INVALID);
    	$ids = array();
    	if($sql->rows()){
    		while($row=$sql->fetch()){
    			array_push($ids, $row['rage_id']);
    		}
    	}
    	$ids = implode(',', $ids);
    	
    	$sql = new Frogg_Db_Sql('DELETE FROM `scheduled` WHERE `rage_id` IN ('.$ids.')');
    	die;
    }
    
    /****************************************************************************************
     ****************************************************************************************
     *								UTILITY FUNCTIONS										*
     ****************************************************************************************
     ****************************************************************************************/
    private function avoidedClassification($classification){
    	$avoided = array('News','Talk Shows','Game Show','Sports','Award Show');
    	return in_array($classification, $avoided);
    }
    
    private function allowedCountry($country){
    	$allowed = array('US','UK','CA');
    	return in_array($country, $allowed);
    }
    
    private function parseStatus($status){
    	switch ($status) {
    		case 'New Series': return NEW_SERIES;
    		case 'Returning Series': return RETURNING_SERIES;
    		case 'Canceled/Ended': return CANCELED_SERIES;
    		default:;break;
    	}
    }
    
    private function getTime($string){
    	$time = explode('[/DAY]', $string);
    	$time = explode(', ', $time[0]);
    	$date = DateTime::createFromFormat('d F Y',$time[1]);
    	$time = new Frogg_Time_Time($date->format('Y-m-d'));
    	return $time->getUnixTstamp();
    }
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	 public function fixAction(){
		for($meuContador = 0; $meuContador < 500 ; $meuContador++){
			$logger 	= new Frogg_Log('/home2/bleachse/public_html/seriando/log/new_series.log');
			$control    = new Application_Model_NewSeriesControl(1);
			$series_control = $control->nextSeries();
			if(!$series_control){ $logger->warn('No new series'); $logger->close(); continue; }
			
			$xml = new XMLReader();
			if(!$xml->open('http://services.tvrage.com/feeds/full_show_info.php?sid='.$series_control->rage_id)){
				$logger->err('Failed to open input file : '.$series_control->rage_id);
				$logger->close();
				continue;
			}
			$logger->info('Starting to log new_series_id : '.$series_control->rage_id);
			$series = new Application_Model_Series();
			while ($xml->read()){
				while($xml->read() && $xml->name != 'name');
				if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'name'){
					$series->name = $xml->readString();
				}
				while($xml->read() && $xml->name != 'showid');
				if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'showid'){
					$series->rage_id = $xml->readString();
					$double_check = new Application_Model_Series();
					$logger->info('Double check [my_id]:[rage_id] '.$series_control->rage_id.':'.$series->rage_id);
					if($double_check->loadField('rage_id', $series->rage_id)){
						$series_control->flag = Application_Model_NewSeries::ERROR;
						$series_control->update();
						$logger->err('Series already registered rage_id:'.$series->rage_id);
						$logger->close();
						continue;
					}
				}
				while($xml->read() && $xml->name != 'origin_country');
				if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'origin_country'){
					if(!$this->allowedCountry($xml->readString())){
						$series_control->flag = Application_Model_NewSeries::INVALID;
						$series_control->update();
						$logger->warn('Series country not allowed rage_id:'.$series->rage_id);
						$logger->close();
						continue;
					}
				}
				while($xml->read() && $xml->name != 'status');
				if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'status'){
					$series->status = $this->parseStatus($xml->readString());
				}
				while($xml->read() && $xml->name != 'classification');
				if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'classification'){
					if($this->avoidedClassification($xml->readString())){
						$series_control->flag = Application_Model_NewSeries::INVALID;
						$series_control->update();
						$logger->warn('Series classification invalid rage_id:'.$series->rage_id);
						$logger->close();
						continue;
					}
				}
				while($xml->read() && $xml->name != 'runtime');
				if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'runtime'){
					$series->runtime = $xml->readString();
				}
				$series->image	   = 'default.png';
				$series->timestamp = time();
				$series->permalink = $series->permalinkFor('name');
				$series->order	   = NEW_SERIES;
				if(!$series->rage_id){
					$series_control->flag = Application_Model_NewSeries::ERROR;
					$series_control->update();
					$logger->err('Empty XML');
					$logger->close();
					continue;
				}
				$series_id = $series->save();
				$logger->ok('Saved');
				
				$series_bucket = new Application_Model_SeriesBucket($series->name,$series->permalink);
				$series_bucket->save();
				
				$has_episodes = $xml->next('Episodelist');
				if($has_episodes){
					while($xml->read() && !($xml->nodeType == XMLReader::END_ELEMENT && $xml->name == 'Episodelist')){
						while($xml->read() && $xml->name != 'Season');//Goes to next <Season>
						if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'Season'){
							$season_num = $xml->getAttribute('no');
							$season = new Application_Model_Series();
							$season->series_id 	= $series_id;
							$season->name 	 	= $season_num.'ª Temporada';
							$season->order		= $season_num * 1000; //According to format XXX.000 for seasons 
							if($season_num < 10){ $season_num = '0'.$season_num; }
							$season->release 	= $series->name.' Season '.$season_num;
							$season->timestamp 	= time();
							$season_id 			= $season->save();
							$release			= new Application_Model_Release($season_id,$season->release,time());
							$release->save();
						}
						while($xml->read()){ //Season episodes reading
							if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'episode'){ //Found new episode
								$episode = new Application_Model_Series();
								$episode->season_id = $season_id;
								$episode->series_id = $series_id;
								$episode->timestamp = time();
								while($xml->read() && $xml->name != 'seasonnum');
								if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'seasonnum'){
									$episode_num = $xml->readString();
									$episode->release = $series->name.' S'.$season_num.'E'.$episode_num;
									$episode->order   = $season->order + ($episode_num*1); //According to format XXX.yyy for episodes
								}
								while($xml->read() && $xml->name != 'airdate');
								if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'airdate'){
									$date = new Frogg_Time_Time($xml->readString());
									$episode->airdate = $date->getUnixTstamp();
								}
								while($xml->read() && $xml->name != 'title');
								if($xml->nodeType == XMLReader::ELEMENT && $xml->name == 'title'){
									$episode->name = $xml->readString();
								}
								$episode_id = $episode->save();
								$release	= new Application_Model_Release($episode_id,$episode->release,$episode->airdate);
								$release->save();
								$xml->next('episode');
							} else if($xml->nodeType == XMLReader::END_ELEMENT && $xml->name == 'Season'){ //Found season finale
								break;
							}
						}//END - Season episodes reading
					}
				}
				$series_control->flag = Application_Model_NewSeries::READ;
				$series_control->update();
				$logger->ok('Great Success !!');
				$logger->close();    		
				continue;
			}
		}
		echo 'contou';
    }

    
}