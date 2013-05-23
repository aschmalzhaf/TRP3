<?php

require_once 'Zend/Controller/Action.php';
require_once '../application/default/helpers/DynamicTable.php';

/**
 *
 *
 * @author
 * @version
 */
class CaptteamController extends Zend_Controller_Action {

    //vars
    protected $_model;
    protected $_redirector;

    public function init() {
        $this->_redirector = $this->_helper->getHelper('Redirector');
    }

    /**
     *
     *
     *
     */
    public function indexAction() {

        //set title
        $this->view->title = "Mitarbeiter verwalten (TNT)";

        //Lade Model
        $model = $this->_getModel();
        $session = new Zend_Session_Namespace();

        //sjob Daten laden und aufbauen
        //@TODO Abbilden über ein Popup des Bootstrap-Frameworks
        $html = "<table id=\"stafftab\">";
        $i = 1;
        $sjobs = $model->getSJobOpts();
        foreach ($sjobs as $sjob) {
            if ($i == 1) {
                $html_start = "<tr><td>";
                $html_end = "</td>";
                $i = 2;
            } elseif ($i == 2) {
                $html_start = "<td>";
                $html_end = "</td>";
                $i = 3;
            } elseif ($i == 3) {
                $html_start = "<td>";
                $html_end = "</td></tr>\n";
                $i = 1;
            }
            $html .= $html_start;
            $html .= "<a href=\"#\" class=\"sjobvaluebutton\" id=\"" . $sjob['ID'] . "\">" . $sjob['name'] . "</a>";
            $html .= $html_end;
        }

        if ($i == 1) {
            $html .= "<td></td><td></td></tr></table>\n";
        } elseif ($i == 2) {
            $html .= "<td></td></tr></table>\n";
        } elseif ($i == 3) {
            $html .= "</table>\n";
        }
        $this->view->sjobopts = $html;
        unset($html);

        //get kv options
        $kvs = $model->getKvs();
        $html = "<option value=\"0\"></option>";
        foreach ($kvs as $kv) {
            $html .= "<option value=\"" . $kv['ID'] . "\">" . $kv['name'] . "</option>";
        }
        $this->view->kvopts = $html;
        unset($html);

        //get role options
        $roles = $model->getUserRoles();
        $html = "";
        foreach ($roles as $role) {
            if ($role['name'] == "Mitarbeiter") {
                $html .= "<option value=\"" . $role['ID'] . "\" selected>" . $role['name'] . "</option>";
            } else {
                $html .= "<option value=\"" . $role['ID'] . "\">" . $role['name'] . "</option>";
            }
        }
        $this->view->roleopts = $html;
        unset($html);

        //get AddedStaff-Table
        //table-header
        //build columns
        $col = Array();
        $col['id'] = 'ID';
        $col['title'] = 'Nr.';
        $col['type'] = 'num';
        $cols[] = $col;

        unset($col);
        $col['id'] = 'fname';
        $col['title'] = 'Vorname';
        $col['type'] = 'string';
        $cols[] = $col;

        unset($col);
        $col['id'] = 'lname';
        $col['title'] = 'Nachname';
        $col['type'] = 'string';
        $cols[] = $col;

        unset($col);
        $col['id'] = 'mphone';
        $col['title'] = 'Handy';
        $col['type'] = 'string';
        $cols[] = $col;

        unset($col);
        $col['id'] = 'email';
        $col['title'] = 'E-Mail';
        $col['type'] = 'email';
        $cols[] = $col;

        unset($col);
        $col['id'] = 'city';
        $col['title'] = 'Ort';
        $col['type'] = 'string';
        $cols[] = $col;

        unset($col);
        $col['id'] = 'role_name';
        $col['title'] = 'Rolle';
        $col['type'] = 'string';
        $cols[] = $col;

        unset($col);
        $col['id'] = 'sjob_name';
        $col['title'] = 'Spezialaufgabe';
        $col['type'] = 'string';
        $cols[] = $col;

        unset($col);
        $col['id'] = 'intnote';
        $col['title'] = 'Notiz';
        $col['type'] = 'text';
        $cols[] = $col;

        unset($col);
        $col['id'] = 'lastchanged';
        $col['title'] = 'letzte Änderung';
        $col['type'] = 'string';
        $cols[] = $col;

        unset($col);
        $col['id'] = 'link';
        $col['title'] = 'Link';
        $col['type'] = 'link';
        $cols[] = $col;


        //staff-content
        //@todo: Daten was, wenn $AddedStaff == null?
        $AddedStaff = $model->fetchAddedStaff($session->user['ID']);
        $data = Array();
        foreach ($AddedStaff as $staff) {
            $staff['link'] = "<a href=\"/Captteam/staffdatacheck/" . $staff['ID'] . "\"><img src=\"/images/bearb.gif\"\\></a>";
            $data[] = $staff;
        }

        //create and save table
        $table = new DynamicTable($cols, $data, Array('id' => 'stafftab'));
        $this->view->entries = $table->getHtmlString();
    }

    /**
     *
     */
    public function gettableAction() {

        $this->_helper->layout->disableLayout();

        //load Model
        $model = $this->_getModel();
        $session = new Zend_Session_Namespace();

        //get AddedStaff-Table
        //table-header
        $html = "<table id=\"stafftab\" class=\"sortable\"><tr>";
        $html .= "<th class=\"sorttable_numeric\">Nr</th>";
        $html .= "<th>Vorname</th>";
        $html .= "<th>Nachname</th>";
        $html .= "<th>Handy</th>";
        $html .= "<th>eMail</th>";
        $html .= "<th>Ort</th>";
        $html .= "<th>Rolle</th>";
        $html .= "<th>Spezielle Aufgabe</th>";
        $html .= "<th title=\"interne Notiz\">int. Notiz</th>";
        $html .= "<th>Letzte Änderung</th>";
        $html .= "<th> </th>";
        $html .= "</tr>";
        //staff-content
        $AddedStaff = $model->fetchAddedStaff($session->user['ID']);
        foreach ($AddedStaff as $staff) {
            $html .= "<tr>";
            $html .= "<td>" . $staff['ID'] . "</td>";
            $html .= "<td>" . $staff['fname'] . "</td>";
            $html .= "<td>" . $staff['lname'] . "</td>";
            $html .= "<td>" . $staff['mphone'] . "</td>";
            $html .= "<td title=\"" . $staff['email'] . "\"><a href=\"mailto:" . $staff['email'] . "\">" . substr($staff['email'], 0, 14) . "</a></td>";
            $html .= "<td>" . $staff['city'] . "</td>";
            $html .= "<td>" . $staff['role_name'] . "</td>";
            $html .= "<td>" . $staff['sjob_name'] . "</td>";
            $html .= "<td title=\"" . $staff['intnote'] . "\">" . substr($staff['intnote'], 0, 15) . "</td>";
            $html .= "<td>" . $staff['lastchanged'] . "</td>";
            $html .= "<td><a href=\"/Captteam/staffdatacheck/" . $staff['ID'] . "\"><img src=\"/images/bearb.gif\"\\></a></td>";
            $html .= "</tr>";
        }
        //table footer
        $html .= "</table>";
        $this->view->entries = $html;
    }

    /**
     *
     */
    public function emailcheckAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $model = $this->_getModel();

        $res = $model->mailexists($this->getRequest()->getParam('mailadress'));

        $this->_helper->json->sendJson(Array('mailexists' => $res));
    }

    /**
     *
     */
    public function savestaffAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ($this->getRequest()->isPost()) {


            $data = $this->getRequest()->getParams();


            //delete technical data
            unset($data['controller']);
            unset($data['action']);
            unset($data['module']);

            //change data
            if ($data['blocked'] == "true") {
                $data['blocked'] = 1;
            } else {
                $data['blocked'] = 0;
            }

            $registry = Zend_Registry::getInstance();
            $salt = $registry->config->salt;
            $data['pwd'] = rand(10000, 32768);
            $pwd = $data['pwd'];
            $data['pwd'] = md5($data['pwd'] . $salt);


            //Information hinzufügen, wer Mitarbeiter angelegt hat
            $session = new Zend_Session_Namespace();
            $data['addedby'] = (int) $session->user['ID'];


            //get Model
            $model = $this->_getModel();



            //Speichern
            $res = $model->saveStaff($data);
            if ($res == null) {
                $msg = Array();
                $msg['shorttext'] = "Fehler beim Speichern";
                $msg['longtext'] = "Mitarbeiter wurde nicht gespeichert! Bitte Seite neu laden und nochmals probieren...";
                $msg['cat'] = "warning";
                $msg['new'] = "true";
                $this->_helper->FlashMessenger->addMessage($this->_helper->json->encodeJson($msg));
                return;
            } else {
                $usrdata = $res;
            }

            //send mail...
            $usrdata['pwd'] = $pwd;
            $this->mailToUser($usrdata, 0);
            //return ID
            $msg = Array();
            $msg['shorttext'] = "Mitarbeiter angelegt";
            $msg['longtext'] = "Mitarbeiter " . $usrdata['usrname'] . " mit ID " . $usrdata['ID'] . " gespeichert";
            $msg['cat'] = "success";
            $msg['new'] = "true";
            $this->_helper->FlashMessenger->addMessage($this->_helper->json->encodeJson($msg));
        }
    }

    /**
     * change user data
     */
    public function staffdatacheckAction() {

        $data = $this->getRequest()->getParams();
        $model = $this->_getModel();
        //get User data
        $usrdata = $model->getUserDataByID($data['ID']);

        //change birthday
        if ($usrdata['birthday'] != null) {
            $usrdata['birthday'] = substr($usrdata['birthday'], 8, 2) . "." . substr($usrdata['birthday'], 5, 2) . "." . substr($usrdata['birthday'], 0, 4);
        } else {
            $usrdata['birthday'] = "";
        }

        //change last changed
        if ($usrdata['lastchanged'] != null) {
            $usrdata['lastchanged'] = date("d.m.Y, H:i:s", $usrdata['lastchanged']) . " Uhr";
        } else {
            $usrdata['lastchanged'] = "";
        }

        //give usrdata to view
        $this->view->usrdata = $usrdata;

        //get sjob options
        $html = "<table id=\"stafftab\">";
        $i = 1;
        $sjobs = $model->getSJobOpts();
        foreach ($sjobs as $sjob) {
            if ($i == 1) {
                $html_start = "<tr><td>";
                $html_end = "</td>";
                $i = 2;
            } elseif ($i == 2) {
                $html_start = "<td>";
                $html_end = "</td>";
                $i = 3;
            } elseif ($i == 3) {
                $html_start = "<td>";
                $html_end = "</td></tr>\n";
                $i = 1;
            }
            $html .= $html_start;
            $html .= "<a href=\"#\" class=\"sjobvaluebutton\" id=\"" . $sjob['ID'] . "\">" . $sjob['name'] . "</a>";
            $html .= $html_end;
        }

        if ($i == 1) {
            $html .= "<td></td><td></td></tr></table>\n";
        } elseif ($i == 2) {
            $html .= "<td></td></tr></table>\n";
        } elseif ($i == 3) {
            $html .= "</table>\n";
        }
        $this->view->sjobopts = $html;
        unset($html);

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


        //get role options
        $roles = $model->getUserRoles();
        $html = "";
        foreach ($roles as $role) {
            if ($role['ID'] == $usrdata['role']) {
                $html .= "<option value=\"" . $role['ID'] . "\" selected>" . $role['name'] . "</option>";
            } else {
                $html .= "<option value=\"" . $role['ID'] . "\">" . $role['name'] . "</option>";
            }
        }
        $this->view->roleopts = $html;
        unset($html);


        //Title
        $this->view->title = $usrdata['fname'] . " " . $usrdata['lname'];
    }

    /**
     *
     */
    public function getposshiftsAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ($this->getRequest()->isPost()) {

            $data = $this->getRequest()->getParams();
            $model = $this->_getModel();

            if ($data['mode'] == "all") {
                $shifts = $model->getAllPosShifts($data['ID'], $data['clearfull']);
            } elseif ($data['mode'] == "time") {
                $shifts = $model->getTimePosShifts($data['ID'], $data['clearfull']);
            } elseif ($data['mode'] == "timestrong") {
                $shifts = $model->getTimeStrongPosShifts($data['ID'], $data['clearfull']);
            }

            $html = "";
            foreach ($shifts as $shift) {
                $event = $model->getEventByID($shift['eventid']);
                $html .= "<option class=\"shiftlist\" value=\"" . $shift['eventid'] . "/" . $shift['ID'] . "\">" . $event['name'] . " | " . $shift['name'] . " | " . date("H:i", $shift ['start']) . " - " . date("H:i", $shift ['end']) . " Uhr" . "</option>";
            }
            $this->view->possibleshifts = $html;
            echo $html;
        }
    }

    /**
     *
     */
    public function getshiftsAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ($this->getRequest()->isPost()) {
            $model = $this->_getModel();
            $data = $this->getRequest()->getParams();

            //get events/shifts of user
            $shifts = $model->getShiftsOfUser($data['ID']);
            $html = "";

            foreach ($shifts as $shift) {
                $event = $model->getEventByID($shift['eventid']);
                $html .= "<option class=\"shiftlist\" value=\"" . $shift['eventid'] . "/" . $shift['ID'] . "\">" . $event['name'] . " | " . $shift['name'] . " | " . date("H:i", $shift ['start']) . " - " . date("H:i", $shift ['end']) . " Uhr" . "</option>";
            }
            echo $html;
        }
    }

    

    /**
     *
     * @param integer $id
     * @param integer $mode
     */
    protected function mailToUser($usrdata, $mode) {




        //Modes:
        // 0 initial Mail
        // 1 update Info
        // 2 delete Info
        //get user data
        $model = $this->_getModel();

        //check mail adress of user
        if ($usrdata['email'] == "") {
            $msg = Array();
            $msg['shorttext'] = "Mail nicht versendet";
            $msg['longtext'] = "eMail nicht versendet: eMail-Adresse von " . $usrdata['usrname'] . " nicht gepflegt!";
            $msg['cat'] = "warning"; // success, info, warning
            $msg['new'] = "true";
            $this->_helper->FlashMessenger->addMessage($this->_helper->json->encodeJson($msg));
            return;
        }


        //get adder data
        $model = $this->_getModel();
        $adderdata = $model->getUserDataByID($usrdata['addedby']);

        //MODE 0 INITIAL MAIL
        if ($mode == 0) {
            //get Mail-Content
            $MailTemplate = $this->_getModel()->getMailTemplate("TN_Neu_2013");
            $HTMLcontent = $MailTemplate['mailcontent'];

            //replace tags
            $HTMLcontent = str_replace("%fname%", utf8_decode($usrdata['fname']), $HTMLcontent);
            $HTMLcontent = str_replace("%lname%", utf8_decode($usrdata['lname']), $HTMLcontent);
            $HTMLcontent = str_replace("%addedby%", utf8_decode($adderdata['fname']) . " " . utf8_decode($adderdata['lname']), $HTMLcontent);
            $HTMLcontent = str_replace("%usrname%", utf8_decode($usrdata['usrname']), $HTMLcontent);
            $HTMLcontent = str_replace("%pwd%", $usrdata['pwd'], $HTMLcontent);

            //set subject
            $subject = utf8_decode($MailTemplate['subject']);


            //MODE 1 UPDATE INFO
        } elseif ($mode == 1) {
            //get Mail-Content
            $HTMLcontent = $MailTemplate = $this->_getModel()->getMailTemplate("TN_Änderung_Daten_2013");

            //replace tags
            $HTMLcontent = str_replace("%fname%", utf8_decode($usrdata['fname']), $HTMLcontent);
            $HTMLcontent = str_replace("%lname%", utf8_decode($usrdata['lname']), $HTMLcontent);
            $HTMLcontent = str_replace("%usrname%", utf8_decode($usrdata['usrname']), $HTMLcontent);

            //set subject
            $subject = utf8_decode($MailTemplate['subject']);
            

            //MODE 2 DELETE INFO
        } elseif ($mode == 2) {
            //get Mail-Content
            $HTMLcontent = $MailTemplate = $this->_getModel()->getMailTemplate("TN_Löschen_2013");

            //replace tags
            $HTMLcontent = str_replace("%fname%", utf8_decode($usrdata['fname']), $HTMLcontent);
            $HTMLcontent = str_replace("%lname%", utf8_decode($usrdata['lname']), $HTMLcontent);
            $HTMLcontent = str_replace("%usrname%", utf8_decode($usrdata['usrname']), $HTMLcontent);

            //set subject
            $subject = utf8_decode($MailTemplate['subject']);
        } else {
            return;
        }

        //set up mailer and send mail
        try {
            require_once ('../application/default/helpers/Mailer.php');

            Mailer::sendmail($usrdata['email'], $usrdata['fname'] . " " . $usrdata['lname'], $subject, $HTMLcontent, 1);
        } catch (Exception $e) {
            echo "Ein eMail-Fehler ist aufgetreten! $e";
        }
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