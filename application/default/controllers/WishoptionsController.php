<?php

/**
 * {0}
 * 
 * @author
 * @version 
 */
require_once 'Zend/Controller/Action.php';

class WishoptionsController extends Zend_Controller_Action {

    //vars
    protected $_model;

    public function init() {
        
    }

    public function indexAction() {
        //set title
        $this->view->title = 'Wünsche und Optionen bearbeiten';

        //last steps
        $model = $this->_getModel();
        $this->view->events = $model->getEvents();
    }

    public function getwishoptionsjsonAction() {
        $model = $this->_getModel();
        $wishoptions = $model->getWishoptions();

        // send array
        $this->_helper->json->sendJson($wishoptions);
    }

    public function getwishoptionjsonAction() {
        $model = $this->_getModel();
        $wishoption = $model->getWishoption($this->getRequest()->getParam("ID"));

        // send array
        $this->_helper->json->sendJson($wishoption);
    }

    public function savewishoptionjsonAction() {
        $model = $this->_getModel();
        $data = $this->getRequest()->getParams();
        unset($data['controller']);
        unset($data['action']);
        unset($data['module']);
        $res = $model->saveWishoption($data);
        $this->_helper->json->sendJson($res);
    }

    public function getlinksjsonAction() {
        $model = $this->_getModel();
        $links = $model->getWoptEventLinks();
        $this->_helper->json->sendJson($links);
    }

    public function addlinkjsonAction() {
        $data['event'] = $this->getRequest()->getParam('event');
        $data['wishoption'] = $this->getRequest()->getParam('wishoption');
        $model = $this->_getModel();
        $res = $model->addWoptEventLink($data);
        $this->_helper->json->sendJson($res);
    }

    public function deletelinkjsonAction() {
        $data['event'] = $this->getRequest()->getParam('event');
        $data['wishoption'] = $this->getRequest()->getParam('wishoption');
        $model = $this->_getModel();
        $res = $model->deleteWoptEventLink($data);
        $this->_helper->json->sendJson($res);
    }

    //get db model
    protected function _getModel() {
        if (null === $this->_model) {
            require_once APPLICATION_PATH . '/models/Model_Wishoptions.php';
            $this->_model = new Model_Wishoptions ( );
        }
        return $this->_model;
    }

}

