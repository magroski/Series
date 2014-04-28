<?php

class Frogg_Social_Facebook{
	
	private	$obj;
	private	$item_id;
	
	public function __construct($app_id, $app_secret, $access_token=false) {
		$this->obj = new Frogg_Social_Facebook_Facebook(array('appId' => $app_id, 'secret' =>$app_secret));

		if($access_token){
			$this->obj->setAccessToken($access_token);
		}
	}
	
	/**
	 * Get some information about the connected user, or 0
	 * if the Facebook user is not connected.
	 *
	 * @return associative array with the available information.
	 */
	public function getUserProfile() {
		try {
			$tmp = $this->obj->api('/me');
		} catch (FacebookApiException $e) {
			error_log($e);
		}
		return $tmp;
	}
	
	/**
	 * Get the UID of the connected user, or 0
	 * if the Facebook user is not connected.
	 * May be used to validate the access token
	 *
	 * @return string the UID if available.
	 */
	public function getUserId() {
		return $this->obj->getUser();
	}
	
	/**
	 * 
	 * Sends an Image to the user profile
	 * @param string $image Image path, relative to 'public_html'   
	 * @param string $description Description for the image that will be sent to the user profile
	 */
	public function sendImage($image,$description='') {
		$usuario = $this->getUserProfile();
		
		$this->obj->setFileUploadSupport(true);
		
		$photo_details = array(
			'message'=> utf8_encode($description), 
			'image'  => '@'.realpath($image)
		);
		
		try {
			var_dump($this->obj->api('/'.$usuario['id'].'/photos', 'post', $photo_details));
		} catch (FacebookApiException $e) {
			error_log($e);
		}
	}
}
	
	