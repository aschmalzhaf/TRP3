<?php

include_once APPLICATION_PATH.'/models/Model_Acl.php';

class Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{

  protected $_acl = null;

  public function __construct(Zend_Acl $acl)
  {
    $this->_acl = $acl;
  }

  public function preDispatch(Zend_Controller_Request_Abstract $request)
  {
    if(strtolower($request->getControllerName()) != 'login'){
      $session = new Zend_Session_Namespace();
      //alle Anfragen an den Controller "Login" sind zulässig, auch ohne Login
      if($session->user == NULL){
        //not logged in -> go to login!
        $request->setModuleName('default');
        $request->setControllerName('Login');
        $request->setActionName('index');
      }else{
        //logged in - autorization?
        $model = new Model_Acl();
        $role = $model->getRoleByID($session->user['role']);     

        //@TODO: Überlegen, wie man umsetzen kann, dass man auf die angeforderte Seite kommt nach dem login,
        //nicht auf die Basic-Seite
        if (!$this->_acl->has($request->controller) OR !$this->_acl->isAllowed($role, $request->controller, 'view')) {
          //Unterscheiden: Api-Call (das heißt: kein UI!) oder normale Anfrage für eine HTML-Seite?
          if(stripos($request->controller,"api") === 0 ){
            $this->getResponse()->setHttpResponseCode(401);
          }else{
            $request->setModuleName('default');
            $request->setControllerName('Basic');
            $request->setActionName('index');
          }
          
        }  
      }
    }
  }
}