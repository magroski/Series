<?php

class Application_Model_Helper_ReCaptcha extends Zend_Service_ReCaptcha{
	
	protected $_publicKey 	= RECAPTHCA_PUB_KEY;
	protected $_privateKey	= RECAPTHCA_PRIVATE_KEY;
	protected $_options		= array( 'theme' => 'custom',  'lang' => 'pt', 'custom_theme_widget' => 'recaptcha_widget');
	protected $_ip 			= "127.0.0.1";

    public function __construct(){
    	new parent();
    }
	
    public function getHtml(){
    	$options = Zend_Json::encode($this->_options);
		$return = <<<HTML
		<script type="text/javascript">
				  var RecaptchaOptions = {$options};
		</script>
		<div id="recaptcha_widget" style="display:none">
		   <div id="recaptcha_image"></div>
		   <div style="position:relative;top:-50px;left:530px;"><a href="javascript:Recaptcha.reload()"><i class="icon-refresh"></i></a></div>
		   <div class="recaptcha_only_if_incorrect_sol" style="color:red">Incorrect please try again</div>
		   <input type="text" id="recaptcha_response_field" name="recaptcha_response_field" placeholder="Digite as palavras acima" />
		</div>

		<script type="text/javascript" src="http://www.google.com/recaptcha/api/challenge?k={$this->_publicKey}"></script>
		<noscript>
			<iframe src="http://www.google.com/recaptcha/api/noscript?k={$this->_publicKey}" height="300" width="540" frameborder="0"></iframe><br>
			<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
			<input type="hidden" name="recaptcha_response_field" value="manual_challenge" >
		</noscript>
HTML;
		return $return;
    }

}