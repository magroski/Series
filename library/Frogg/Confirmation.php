<?php
/**
 * Generic object used to manage code generation for actions that require user confirmation
 * @author Tasso
 */
class Frogg_Confirmation{
	
	public static $user;
	public static $key;
	public static $expiration;
	public static $code;
	
	public static function createCode($user, $key, $days = 2){
		self::$user = $user;
		self::$key = $key;
		self::$expiration = time() + ($days * Frogg_Time_Time::DAY);
		self::$code = self::privateCreateCode($user, $key, self::$expiration);
	}
	
	public static function checkCode($user, $key, $expiration, $code){
		return ($code==self::privateCreateCode($user, $key, $expiration));
	}
	
	public static function privateCreateCode($user, $key, $expiration){
		return substr((sha1($expiration.'ggo'.$key.'Fr'.$user).sha1($expiration.'ogg'.$user.'rF'.$key)), 13,40);
	}

}