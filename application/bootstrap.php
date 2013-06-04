<?php

//Error Reporting

//ENABLE FOR BOOTSTRAP-ANALYSIS (FOR APPLICATION FAILURES IT IS ACTIVATED BELOW)
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors','on');

date_default_timezone_set('Europe/Paris');

//directory setup and class loading
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application/default'));
set_include_path('.'.PATH_SEPARATOR.'../library/'
    .PATH_SEPARATOR.'../application/default/models');

// Zend Framework Includes
require_once "Zend/Loader/Autoloader.php";
Zend_Loader_Autoloader::getInstance();

//load config.ini to registry
$config = new Zend_Config_Ini('../application/config.ini','general');
$registry = Zend_Registry::getInstance();
$registry->config = $config;

//build up db connection
$registry->db = Zend_Db::factory('Pdo_Mysql', $registry->config->db);


// Get the front controller instance 
$frontController = Zend_Controller_Front::getInstance();
$frontController->setControllerDirectory('../application/default/controllers');

//show errors
if($registry->config->show_errors){
    $frontController->throwExceptions(true);
}else
$frontController->throwExceptions(false);


//Layout...
Zend_Layout::startMvc(array('layoutPath'=>'../application/default/layouts',
    'contentKey'=>'content',
    'layout'    =>'main'));

//Acl Plugin
<<<<<<< HEAD
require_once '../library/TRP/Controller/Plugins/Acl.php';
require_once '../library/TRP/Acl.php';
=======
require_once '../library/TRP2/Controller/Plugins/Acl.php';
require_once '../library/TRP2/Acl.php';
>>>>>>> 57c2a1a740de2b9934938afca866f13a720e6b08

$acl = new Acl();
$frontController->registerPlugin(new Controller_Plugin_Acl($acl));

//Menu Plugin
<<<<<<< HEAD
require_once '../library/TRP/Controller/Plugins/Menus.php';
=======
require_once '../library/TRP2/Controller/Plugins/Menus.php';
>>>>>>> 57c2a1a740de2b9934938afca866f13a720e6b08
$frontController->registerPlugin(new Controller_Plugin_Menus($acl));

//Add Router
$router = $frontController->getRouter();
$router->addRoute(
    'StaffData',
    new Zend_Controller_Router_Route('Captteam/staffdatacheck/:ID',
        array('controller' => 'Captteam', 'action' => 'staffdatacheck')
        )
    );

$router->addRoute(
    'ChangeEvent',
    new Zend_Controller_Router_Route('Eventshift/changeevent/:ID',
        array('controller' => 'Eventshift', 'action' => 'changeevent')
        )
    );

$router->addRoute(
    'ChangeEventShift',
    new Zend_Controller_Router_Route('Eventshift/changeevent/:ID/:shiftID',
        array('controller' => 'Eventshift', 'action' => 'changeevent')
        )
    );

$router->addRoute(
    'GeneratePS',
    new Zend_Controller_Router_Route('Eventshift/generateps/:ID',
        array('controller' => 'Eventshift', 'action' => 'generateps')
        )
    );

$router->addRoute(
    'ParticipantsData',
    new Zend_Controller_Router_Route('Participants/participantdatacheck/:ID',
        array('controller' => 'Participants', 'action' => 'participantdatacheck')
        )
    );

//Session
Zend_Session::start();

//GO GO GO!
$frontController->dispatch();