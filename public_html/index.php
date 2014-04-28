<?php
// Define application host
defined('HOST')
    || define('HOST', 'http://'.$_SERVER['SERVER_NAME'].'/');
    
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$namespaces = array('namespace' => 'Frogg', 'basePath' 	=> realpath(APPLICATION_PATH . '/../library/Frogg'));
$application->setAutoloaderNamespaces($namespaces);
$namespaces = array('namespace' => 'Project', 'basePath' 	=> realpath(APPLICATION_PATH . '/../library/Project'));
$application->setAutoloaderNamespaces($namespaces);
$application->bootstrap()
            ->run();