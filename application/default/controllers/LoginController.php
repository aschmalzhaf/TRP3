<?php

class LoginController extends Zend_Controller_Action {

    //vars
    protected $_model;

    public function init() {
        $this->_helper->layout->setLayout('login');
    }

    public function indexAction() {
        //check, if you're allready logged in...
        $session = new Zend_Session_Namespace();

        if (isset($session->user['usrname'])) {

            $this->_helper->redirector('index', 'Basic');
        }


        $request = $this->getRequest();
        $form = $this->_getLoginForm();

        $this->_helper->layout->setLayout('login');
        $this->view->form = $form;


        // check to see if this action has been POST'ed to
        if ($request->isPost()) {

            // now check to see if the form submitted exists, and
            // if the values passed in are valid for this form
            if ($form->isValid($request->getPost())) {


                $model = $this->_getModel();
                $UsrData = $model->checkData($form->getValues());

                if ($UsrData != null) {
                    $session->user = $UsrData;
                    //redirect to Dashboard - everything's fine
                    $this->_helper->redirector('Index', 'Basic');
                } else {

                    $this->view->result = "Benutzername oder Passwort falsch!";
                }
            } else {
                //Message of invalid form is send automatically
            }
        } else {
            //layout etc is set up above - nothing else to do than wait...
        }
    }

    public function forgotloginAction() {
        //$form    = $this->_getForgottenLoginForm();
        //$this->view->form = $form;
    }

    public function logoutAction() {
        Zend_Session::destroy(true, true);
        //message
        $this->view->result = "ausgeloggt...";

        $this->_helper->redirector('index', 'Login');
    }

    protected function _getLoginForm() {
        require_once APPLICATION_PATH . '/forms/login.php';
        $form = new Form_Login();
        $form->setAction($this->_helper->url('index'));
        return $form;
    }

    protected function _getModel() {
        if (null === $this->_model) {
            require_once APPLICATION_PATH . '/models/Login.php';
            $this->_model = new Model_Login();
        }
        return $this->_model;
    }

}