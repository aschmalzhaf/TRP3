<?php

class IndexController extends Zend_Controller_Action {

    //vars
    protected $_model;

    public function indexAction() {

        $this->_helper->redirector('index', 'Login');
    }

}