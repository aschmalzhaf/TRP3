<?php

/**
 * {0}
 *
 * @author
 * @version
 */
require_once 'Zend/Controller/Action.php';
require_once APPLICATION_PATH . '/helpers/WishTagDecorator.php';

class BasicController extends Zend_Controller_Action {

    //vars
    protected $_model;

    public function init() {
        $session = new Zend_Session_Namespace ();
        if($session->user ['role'] == "2"){
            $this->_helper->layout->setLayout('main_MA');
        }
    }

    public function indexAction() {
        //set title
        $this->view->title = 'Start';
    }


    public function changeaccountAction() {
        //set title
        $this->view->title = 'Benutzerkonto ändern';

        $model = $this->_getModel();
        $session = new Zend_Session_Namespace ();

        //get User data
        $usrdata = $model->getUserDataByID($session->user ['ID']);
        //get participantdata
        $participantdata = $model->getParticipantDataByMA($session->user ['ID']);
        if ($participantdata != false)
            $participantdata['jufa'] = "jufa";

        //get kv options
        $kvs = $model->getKvs();
        $html = "<option value=\"0\"></option>";
        foreach ($kvs as $kv) {
            if ($kv['ID'] == $usrdata['kv']) {
                $html .= "<option value=\"" . $kv['ID'] . "\" selected>" . $kv['name'] . "</option>";
            } else {
                $html .= "<option value=\"" . $kv['ID'] . "\">" . $kv['name'] . "</option>";
            }
        }
        $this->view->kvopts = $html;
        unset($html);

        //change birthday
        if ($usrdata ['birthday'] != null) {
            $usrdata ['birthday'] = substr($usrdata['birthday'], 8, 2) . "." . substr($usrdata['birthday'], 5, 2) . "." . substr($usrdata['birthday'], 0, 4);
            ;
        } else {
            $usrdata ['birthday'] = "";
        }


        //give data to view
        $this->view->participantdata = $participantdata;
        $this->view->usrdata = $usrdata;
    }

    public function changepasswordAction() {
        //set title
        $this->view->title = 'Passwort ändern';
    }

    public function updatepasswordAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ($this->getRequest()->isPost()) {

            //get Model
            $model = $this->_getModel();
            //get request data
            $data = $this->getRequest()->getParams();

            //check equality again
            if ($data['pwd'] == $data['confirm_pwd']) {

                //unset unnecessary array fields
                unset($data ['controller']);
                unset($data ['action']);
                unset($data ['module']);
                unset($data ['confirm_pwd']);

                //hash and salt password
                $registry = Zend_Registry::getInstance();
                $data['pwd'] = md5($data['pwd'] . $registry->config->salt);

                //save password
                $session = new Zend_Session_Namespace ();
                $data['ID'] = $session->user['ID'];
                $result = $model->updateStaff($data);

                $msg = Array();

                if ($result == 0) {
                    $msg['shorttext'] = "Passwort nicht geändert";
                    $msg['longtext'] = "Das Passwort wurde nicht geändert. Nix gespeichert.";
                    $msg['cat'] = "info"; // success, info, warning
                } elseif ($result == 1) {
                    $msg['shorttext'] = "Passwort geändert";
                    $msg['longtext'] = "Das Passwort wurde geändert und gespeichert.";
                    $msg['cat'] = "success"; // success, info, warning
                } else {
                    $msg['shorttext'] = "Fehler";
                    $msg['longtext'] = "Fehler beim Ändern oder Speichern. Nochmals probieren, bitte. Danke.";
                    $msg['cat'] = "warning"; // success, info, warning
                }

                $msg['new'] = "true";
                $this->_helper->FlashMessenger->addMessage($this->_helper->json->encodeJson($msg));

                $this->_helper->redirector('index', 'Basic');
            } else {

                $msg = Array();
                $msg['shorttext'] = "Passwörter nicht gleich";
                $msg['longtext'] = "Die zwei eingegebenen Passwörter müssen übereinstimmen!";
                $msg['cat'] = "warning";
                $msg['new'] = "true";
                $this->_helper->FlashMessenger->addMessage($this->_helper->json->encodeJson($msg));

                $this->_helper->redirector('changepassword', 'Basic');
            }
        }
    }

    /**
     * Shows a List of all posiblities which can be wished by a person
     *
     */
    public function wishlistAction() {
        //set title
        $this->view->title = 'Wunschliste';

        $session = new Zend_Session_Namespace ();


        //get User Data
        $model = $this->_getModel();
        $usrdata = $model->getUserDataByID($session->user ['ID']);

        //check if user is blocked
        if ($usrdata ['blocked'] == 0) {

            //to control output on view
            $this->view->blocked = 0;

            //show also sjob
            $this->view->sjob_name = $usrdata['sjob_name'];


            $wishoptions = $model->getWishoptions();
            if(sizeof($wishoptions) > 0){
             foreach ($wishoptions as $woption) {

                $tagsAll[] = Array('title' => $woption['name'], 'weight' => $woption['demand'],
                    'params' => array('ID' => $woption['ID'], 'name' => 'AS Text'));

                $tagsCats[$woption['category']][] = Array('title' => $woption['name'], 'weight' => $woption['demand'],
                    'params' => array('ID' => $woption['ID']));
            }

            // Create Tag Clouds
            $tagDecorator = new Zend_Tag_Cloud_Decorator_WishTag();

            $clouds['alle'] = new Zend_Tag_Cloud(array(
                'tags' => $tagsAll, 'tagDecorator' => $tagDecorator));

            foreach ($tagsCats as $catName => $tagsCat) {
                $clouds[$catName] = new Zend_Tag_Cloud(array(
                    'tags' => $tagsCat, 'tagDecorator' => $tagDecorator));
            }
            //...and send to view
            $this->view->wishClouds = $clouds;
        }



    } else {
        $addedby = $model->getUserDataByID($session->user ['addedby']);
        $this->view->addedby_name = $addedby['fname'] . " " . $addedby['lname'];
        $this->view->addedby_email = $addedby['email'];
        $this->view->blocked = 1;
        $this->view->sjob_name = $usrdata['sjob_name'];
    }
}


    //get db model
protected function _getModel() {
    if (null === $this->_model) {
        require_once APPLICATION_PATH . '/models/Model_Basic.php';
        $this->_model = new Model_Basic ();
    }
    return $this->_model;
}

}