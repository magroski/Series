<?php
class Frogg_Log{
	
	private $handle;
	private $time_flag;
	
	public function __construct($path, $time_flag = true){
		$this->handle = fopen($path, 'a');
		$this->time_flag = $time_flag;
	}
	
	public function log($message, $level){
		$time = '';
		if ($this->time_flag){
			$time = new Frogg_Time_Time();
			$time = $time->format('d/m/Y H:i:s');
		}
		fwrite($this->handle, $time.$level.$message."\n");
	}
	
	public function info($message){ $this->log($message, "[INFO] ");}
	public function warn($message){ $this->log($message, "[WARN] ");}
	public function err($message) {	$this->log($message, "[ERR] ");	}
	public function ok($message)  {	$this->log($message, "[OK] "); 	}
	public function fatal($message){$this->log($message, "[FATAL] "); 	}
	
	public function close(){
		fclose($this->handle);
	}

}