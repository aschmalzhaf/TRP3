<?php

/**
 * {0}
 * 
 * @author
 * @version 
 */
require_once 'Zend/Controller/Action.php';

class CustomizingController extends Zend_Controller_Action {

    //vars
    protected $_model;

    public function init() {
        
    }

    public function indexAction() {
        //set title
        $this->view->title = 'Start';
    }

    public function maintainlocationsAction() {
        //set title
        $this->view->title = 'Orte pflegen';
    }

    //get db model
    protected function _getModel() {
        if (null === $this->_model) {
            require_once APPLICATION_PATH . '/models/Model_Customizing.php';
            $this->_model = new Model_Customizing ( );
        }
        return $this->_model;
    }

}

