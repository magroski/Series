<?php
##ACESS
defined('V_LOGIN') 		|| define('V_LOGIN', "/^([a-zA-Z0-9\\-_.+]{1,25})$/");
defined('V_EMAIL') 		|| define('V_EMAIL', "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/");
defined('V_PASS') 		|| define('V_PASS', "/^([A-Z,a-z,0-9,.,_,-,@,#,&]{3,40})$/");
defined('V_SHA1') 		|| define('V_SHA1', "/^[0-9a-f]{5,40}$/");

##APPLICATION DATA
defined('V_ID') 		|| define('V_ID', "/^[0-9]{1,20}$/");
defined('V_BOOLEAN') 	|| define('V_BOOLEAN', "/^[0-1]{1,1}$/");
defined('V_TITLE') 		|| define('V_TITLE', '/^([A-Z0-9a-z\-ºª,.!?:;@#%&*ÂÀÁÄÃâãàáäÊÈÉËêèéëÎÍÌÏîíìïÔÕÒÓÖôõòóöÛÙÚÜûúùüÇç\[\]+\\\\\'" ])*$/');
defined('V_LINK') 		|| define('V_LINK', '%^(?:(?:https?|ftp)://)?(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu');
defined('V_PERMALINK') 	|| define('V_PERMALINK', "/[a-zA-Z0-9\\-_.,+]/");

##USER DATA
defined('V_NAME') 		|| define('V_NAME', "/^[A-Za-zÂÀÁÄÃâãàáäÊÈÉËêèéëÎÍÌÏîíìïÔÕÒÓÖôõòóöÛÙÚÜûúùüÇç. ]*$/");
defined('V_MOBILE') 	|| define('V_MOBILE', "/^[0-9]{8,11}$/");
defined('V_SEX') 		|| define('V_SEX', "/^([mfMF]{1,1})$/");
defined('V_STATE_BR') 	|| define('V_STATE_BR', "/^(GO|MT|MS|DF|AM|AC|RO|RR|AP|TO|PA|MA|PI|CE|RN|PB|PE|SE|AL|RS|SC|PR|ES|BA|SP|MG|RJ|XX)$/");
defined('V_CEP') 		|| define('V_CEP_BR', "/^([0-9]{8,8})$/");

##SPECIFIC DATA
defined('V_TWITTER') 	|| define('V_TWITTER', "/^((http:\/\/)|(https:\/\/))?(www\.)?twitter\.com\/(#!\/)?([A-Za-z0-9]){1,15}$/");
defined('V_FACEBOOK') 	|| define('V_FACEBOOK', "/^((http:\/\/)|(https:\/\/))?(www\.)?facebook\.com\/([A-Z,a-z,0-9,@,#,!,&,*,+,:,?,_,=,.,\/]|[,]|[-])+$/");
defined('V_YOUTUBE') 	|| define('V_YOUTUBE', "/^((http:\/\/)|(https:\/\/))?(www\.)?((youtube\.com)|(youtu\.be))\/([A-Z,a-z,0-9,@,#,!,&,*,+,:,?,_,=,.,\/]|[,]|[-])+$/");

/**
 * This class is used to validate (not sanitize) data inputs from the system and/or user.
 * Some of the most used regular expressions are defined above.
 */
class Frogg_Validator{
	
	/**
	 * This function is used to validate a variable via a regular expression
	 * @param string $regex A regular expression. You can use one of the defined above or one of your own creation
	 * @param string $var String variable to be validated
	 */
	public static function validate($regex, $var){
		return preg_match($regex, $var);
	}
	
	/**
	 * This function chekcs whether a given string is UTF-8
	 * @param string $string String to be checked
	 */
	public static function isUTF8($string){
    	return mb_detect_encoding($string, 'UTF-8', true)=='UTF-8'; 
	}
	
	/**
	 * This function is used to sanitize a given string
	 * @param string $string String variable to be sanitized
	 */
	public static function sanitize($string){
    	return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES);
	}
	
	/**
	 * 
	 * Remove the protocol and the last '/' from a given URL
	 * @param string $url URL to be sanitized
	 */
	public static function sanitizeUrl($url){
		$url_no_protocol = $url;
	    if ( stristr($url, "https://") ){
			$tmp = explode("https://", $url);
			$url_no_protocol = $tmp[1];
		} else if ( stristr($url, "http://") ){
			$tmp = explode("http://", $url);
			$url_no_protocol = $tmp[1];
		}
			
		if ($url_no_protocol == "" ){ return false; }
		
		return rtrim($url_no_protocol, '/');
	}
	
	/**
	 * This function is used to validate a CPF (Cadastro de Pessoa Fï¿½sica) number.
	 * @param string $cpf CPF as string containing only numbers (no points or score)
	 */
	public static function valCPF($cpf) {
		$cpf = str_replace('.', '', str_replace('-', '', $cpf));
	    $cpf = str_pad(ereg_replace('[^0-9]', '', $cpf), 11, '0', STR_PAD_LEFT);
		
	    if (strlen($cpf) != 11 || $cpf == '00000000000' || $cpf == '11111111111' || $cpf == '22222222222' || $cpf == '33333333333' || $cpf == '44444444444' || $cpf == '55555555555' || $cpf == '66666666666' || $cpf == '77777777777' || $cpf == '88888888888' || $cpf == '99999999999'){
			return false;
	    } else {
	        for ($t = 9; $t < 11; $t++) {
	            for ($d = 0, $c = 0; $c < $t; $c++) {
	                $d += $cpf{$c} * (($t + 1) - $c);
	            }
	            $d = ((10 * $d) % 11) % 10;
	            if ($cpf{$c} != $d) {
	                return false;
	            }
	        }
	        return true;
	    }
	}
}