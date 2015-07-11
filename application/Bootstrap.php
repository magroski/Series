<?php

require APPLICATION_PATH . '/../config/conf.php';

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initDoctype() {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->doctype('XHTML1_STRICT'); 
    }
    
	protected function _initRouter() {		
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/routes.ini', 'production');
		$this->bootstrap('FrontController');
		$router = new Zend_Controller_Router_Rewrite();
		$router->addConfig($config, 'routes');
		$router =  $this->getResource('FrontController')->setRouter($router);		
    }    

    protected function _initPlaceholders() {
        $this->bootstrap('View');
        $view = $this->getResource('View');        
        $view->addHelperPath('../Application/views/helpers/', 'Application_View_Helper');
        $view->description = 'O melhor lugar para acompanhar as suas séries favoritas!';
        $view->headTitle('Seriando')->setSeparator(' | ');
        $view->headLink()->prependStylesheet(HOST.'css/ionicons.min.css');
        $view->headLink()->prependStylesheet(HOST.'css/jquery.pagewalkthrough.css');
        $view->headLink()->prependStylesheet(HOST.'css/style.css');
        $view->headLink()->prependStylesheet(HOST.'css/flat-ui.css');
        $view->headLink()->prependStylesheet(HOST.'css/bootstrap.css');
        
        $view->headScript()->prependFile(HOST.'js/jquery.pagewalkthrough-1.1.0.min.js');
        $view->headScript()->prependFile(HOST.'js/jquery-1.7.2.min.js');
        
    }
}