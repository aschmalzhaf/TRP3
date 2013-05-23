<?php

/**
 * Dieser Controller dient Aufrufen von Systemfunktionen, die nicht mit UI gerufen werden,
 * sondern als plain-html oder JSON (bevorzugt).
 *
 * @author Alexander Schmalzhaf
 * @version 1
 */
require_once 'Zend/Controller/Action.php';

class ApicaptteamController extends Zend_Controller_Action {

    //vars
    protected $_model;

    public function init() {
        //UI-Funktionen abschalten
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    /**
     *
     */
    public function updatestaffAction() {

        $forchange = $this->getRequest()->getParams();
        $data = Array();

        //modify data
        unset($forchange ['controller']);
        unset($forchange['action']);
        unset($forchange ['module']);
        $forchange ['pwd'] = "";

        //Daten-Array aufbauen
        $data[$forchange['field']] = $forchange['newvalue'];
        $data['ID'] = $forchange['ID'];
        // userID
        $session = new Zend_Session_Namespace();
        //$data['ID'] = (int) $session->user['ID'];
        //Geburtstag
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
            $msg['longtext'] = "Der Datensatz wurde nicht geändert";
            $msg['cat'] = "info";
        } elseif ($result == 1) {
            $msg['shorttext'] = "Datensatz geändert";
            $msg['longtext'] = "Die Daten wurden geändert";
            $msg['cat'] = "success";
        } else {
            $msg['shorttext'] = "Fehler beim Speichern";
            $msg['longtext'] = "Bitte überprüfe deine Änderungen, ob sie gespeichert wurden.";
            $msg['cat'] = "warning";
        }
        $msg['new'] = "true";
        $this->_helper->FlashMessenger->addMessage($this->_helper->json->encodeJson($msg));
    }



        /**
         * TO BE IMPLEMENTED 
         */
        public function deletestaffAction() {


            if ($data['mode'] == "delete") {
                //Userdaten cachen, für die Infomail, falls gewünscht
                if ($informviamail == "true")
                    $usrdata = $this->_model->getUserDataByID($data['ID']);

                $result = $model->deleteUser($data['ID']);
                $msg = Array();
                if ($result == 0) {
                    $msg['shorttext'] = "Nichts geändert";
                    $msg['longtext'] = "Nichts geändert";
                    $msg['cat'] = "info";
                } elseif ($result == 1) {
                    if ($informviamail == "true") {
                        $this->mailToUser($usrdata, 2);
                        $msg['shorttext'] = "Mitarbeiter gelöscht und informiert";
                        $msg['longtext'] = "Du hast einen Mitarbteiter gelöscht. Dieser wurde per Mail darüber informiert.";
                        $msg['cat'] = "success";
                    } else {
                        $msg['shorttext'] = "Mitarbeiter gelöscht";
                        $msg['longtext'] = "Du hast einen Mitarbteiter gelöscht. Dieser wurde NICHT informiert.";
                        $msg['cat'] = "success";
                    }
                } else {
                    $msg['shorttext'] = "Fehler";
                    $msg['longtext'] = "Irgendwas ging schief. Probier's nochmal...";
                    $msg['cat'] = "warning";
                }

                $msg['new'] = "true";
                $this->_helper->FlashMessenger->addMessage($this->_helper->json->encodeJson($msg));
            }
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
            require_once APPLICATION_PATH . '/models/Model_Captteam.php';
            $this->_model = new Model_Captteam ();
        }
        return $this->_model;
    }

}