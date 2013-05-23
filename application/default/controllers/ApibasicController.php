<?php

/**
 * Dieser Controller dient Aufrufen von Systemfunktionen, die nicht mit UI gerufen werden,
 * sondern als plain-html oder JSON (bevorzugt).
 *
 * @author Alexander Schmalzhaf
 * @version 1
 */
require_once 'Zend/Controller/Action.php';
require_once APPLICATION_PATH . '/helpers/WishTagDecorator.php';

class ApibasicController extends Zend_Controller_Action {

    //vars
    protected $_model;

    public function init() {
        //UI-Funktionen abschalten
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }


    public function updateaccountAction() {

        $forchange = $this->getRequest()->getParams();
        $data = Array();

        //modify data
        unset($forchange ['controller']);
        unset($forchange['action']);
        unset($forchange ['module']);
        $forchange ['pwd'] = "";

        // userID
        $session = new Zend_Session_Namespace();
        $data['ID'] = (int) $session->user['ID'];

        $data[$forchange['field']] = $forchange['newvalue'];

        if (isset($data ['birthday']) && $data ['birthday'] != "") {
            $data ['birthday'] = substr($data['birthday'], 6, 4) . "-" . substr($data['birthday'], 3, 2) . "-" . substr($data['birthday'], 0, 2);
        } else {
            $data ['birthday'] = null;
        }

        //save Data
        $result = $this->_getModel()->updateStaff($data);

        //Meldung ans UI absetzen
        $msg = Array();
        if ($result == 0) {
            $msg['shorttext'] = "Daten nicht geändert";
            $msg['longtext'] = "Daten wurden nicht geändert.";
            $msg['cat'] = "info";
        } elseif ($result == 1) {
            $msg['shorttext'] = "Daten geändert";
            $msg['longtext'] = "Dein Datensatz wurde geändert.";
            $msg['cat'] = "success";
        } else {
            $msg['shorttext'] = "Fehler beim Speichern";
            $msg['longtext'] = "Irgendwas ging schief. Nochmals probieren, bitte. ";
            $msg['cat'] = "warning";
        }
        $msg['new'] = "true";
        $this->_helper->FlashMessenger->addMessage($this->_helper->json->encodeJson($msg));
    }

    function updatejufadataAction() {

        $data = Array();
        $forchange = $this->getRequest()->getParams();

        // userID
        $session = new Zend_Session_Namespace();
        $data['MA'] = (int) $session->user['ID'];
        $data['lastchanged'] = date("d.m.Y, H:i:s");

        $data[$forchange['field']] = $forchange['newvalue'];


        //Jufa-Besuch an- oder abgewählt - generell löschen oder erzeugen
        if (isset($data['jufa']) and $data['jufa'] == "1") {
            unset($data['jufa']);
            $data['addedby'] = $data['MA'];
            $data['datum'] = date("d.m.Y, H:i:s");
            $result = $this->_getModel()->saveParticipant($data);
            if ($result != null) {
                $result = 1;
            }
        } elseif (isset($data['jufa']) and $data['jufa'] == "0") {
            $result = $this->_getModel()->deleteParticipantsByMA($data['MA']);
        } else {

            $result = $this->_getModel()->updateParticipantByMA($data);
        }

        $msg = Array();
        if ($result == 0) {
            $msg['shorttext'] = "JuFa-Anmeldung nicht geändert";
            $msg['longtext'] = "Die Anmeldung zum JuFa hat sich nicht geändert";
            $msg['cat'] = "info";
        } elseif ($result == 1) {
            $msg['shorttext'] = "JuFa Anmeldung geändert";
            $msg['longtext'] = "Deine Daten wurden geändert.";
            $msg['cat'] = "success";
        } else {
            $msg['shorttext'] = "Fehler beim Speichern";
            $msg['longtext'] = "Ein unbekannter Fehler ist aufgetaucht. Probiers nochmal, andernfalls meld dich beim Admin.";
            $msg['cat'] = "warning";
        }
        $msg['new'] = "true";
        $this->_helper->FlashMessenger->addMessage($this->_helper->json->encodeJson($msg));
    }

    //---------------------------------------------------------------------------//
    //    Methoden für Wünsche
    //---------------------------------------------------------------------------//

    /**
     * add a wish via json object / ajax call
     */
    public function addwishjsonAction() {
        $session = new Zend_Session_Namespace ();
        $model = $this->_getModel();
        $changes = $model->addUserWishByIDs($session->user ['ID'], $this->getRequest()->getParam('ID'));

        $msg = Array();
        if ($changes == 1) {
            $msg['shorttext'] = "Wunsch hinzugefügt";
            $msg['longtext'] = "Super! Du hast einen neuen Wunsch eingetragen!";
            $msg['cat'] = "success";
        } else {
            $msg['shorttext'] = "Wunsch nicht hinzugefügt";
            $msg['longtext'] = "Schon 3 Wünsche oder Wunsch schon in der Liste...?";
            $msg['cat'] = "warning";
        }

        $msg['new'] = "true";
        $this->_helper->FlashMessenger->addMessage($this->_helper->json->encodeJson($msg));
    }

    /**
     * Function to receive wishes of user as json object
     */
    public function getwishesjsonAction() {
        // get wishes of current user
        $session = new Zend_Session_Namespace ();
        $model = $this->_getModel();
        $wishes = $model->getUserWishesByID($session->user ['ID']);

        // send array
        $this->_helper->json->sendJson($wishes);
    }

    /**
     * remove a wish via json object / ajax call
     */
    public function deletewishjsonAction() {
        // get userdata
        $session = new Zend_Session_Namespace ();
        $model = $this->_getModel();
        $changes = $model->deleteUserWishByIDs($session->user ['ID'], $this->getRequest()->getParam('ID'));

        $msg = Array();
        if ($changes == 1) {
            $msg['shorttext'] = "Wunsch gelöscht";
            $msg['longtext'] = "Jetzt hat's Platz für neue Wünsche...";
            $msg['cat'] = "success";
        } else {
            $msg['shorttext'] = "Wunsch nicht gelöscht";
            $msg['longtext'] = "Irgendwas ging schief... Probiers nochmal!";
            $msg['cat'] = "warning";
        }

        $msg['new'] = "true";
        $this->_helper->FlashMessenger->addMessage($this->_helper->json->encodeJson($msg));
    }

    public function getwishdescriptionsjsonAction() {
        // load wishoptions
        $model = $this->_getModel();
        $wishoptions = $model->getWishoptions();
        foreach ($wishoptions as $wishoption) {
            $descriptions[] = Array('id' => $wishoption['ID'], 'name' => $wishoption['name'], 'description' => $wishoption['description']);
        }

        $this->_helper->json->sendJson($descriptions);
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