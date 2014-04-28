<?php

class Frogg_Social_Twitter_Twitter {

	private $consumer_key;
	private $consumer_secret;
	private $oauth_token;
	private $oauth_token_secret;
	
	private $connection;
	
	public function __construct($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL){
		$this->consumer_key 	= $consumer_key;
		$this->consumer_secret 	= $consumer_secret;
		$this->oauth_token	 	= $oauth_token;
		$this->oauth_token_secret = $oauth_token_secret;

		$this->connection = new Frogg_Social_Twitter_TwitterBase($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);
	}
	
	public function publish($msg) {		
		$msgUtf8 = utf8_encode($msg);

		if($this->connection){
			return $this->connection->post('statuses/update', array('status' => $msgUtf8));
		}
	}

}
