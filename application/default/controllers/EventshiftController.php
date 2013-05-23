<?php

/**
 * {0}
 *
 * @author
 * @version
 */
require_once 'Zend/Controller/Action.php';

class EventshiftController extends Zend_Controller_Action {

    //vars
    protected $_model;

    public function init() {
        
    }

    //show events, shifts and addpanel
    public function indexAction() {
        //set title
        $this->view->title = 'Veranstaltungen';

        //get Model
        $model = $this->_getModel();

        //build up lead options
        $html = "<table id=\"leadtab\">";
        $i = 1;
        $users = $model->getAllUsers();
        foreach ($users as $user) {
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
            $html .= "<a href=\"#\" class=\"leadvaluebutton\" id=\"" . $user ['ID'] . "\">" . $user ['fname'] . " " . $user ['lname'] . "</a>";
            $html .= $html_end;
        }
        if ($i == 1) {
            $html .= "<td></td><td></td></tr></table>\n";
        } elseif ($i == 2) {
            $html .= "<td></td></tr></table>\n";
        } elseif ($i == 3) {
            $html .= "</table>\n";
        }
        $this->view->leadopts = $html;
        unset($html);

        //build up location options
        $html = "<table id=\"locationtab\">";
        $i = 1;
        $locations = $model->getAllLocations();
        foreach ($locations as $location) {
            if ($i == 1) {
                $html_start = "<tr><td style=\"background-color: " . $location ['color'] . "\">";
                $html_end = "</td>";
                $i = 2;
            } elseif ($i == 2) {
                $html_start = "<td style=\"background-color: " . $location ['color'] . "\">";
                $html_end = "</td>";
                $i = 3;
            } elseif ($i == 3) {
                $html_start = "<td style=\"background-color: " . $location ['color'] . "\">";
                $html_end = "</td></tr>\n";
                $i = 1;
            }
            $html .= $html_start;
            $html .= "<a href=\"#\" class=\"locationvaluebutton\" id=\"" . $location ['ID'] . "\">" . $location ['name'] . "</a>";
            $html .= $html_end;
        }
        if ($i == 1) {
            $html .= "<td></td><td></td></tr></table>\n";
        } elseif ($i == 2) {
            $html .= "<td></td></tr></table>\n";
        } elseif ($i == 3) {
            $html .= "</table>\n";
        }
        $this->view->locationopts = $html;
        unset($html);

        //
        //save data
        if ($this->getRequest()->isPost()) {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            $data = $this->getRequest()->getParams();

            //delete wast'd data
            unset($data ['controller']);
            unset($data ['action']);
            unset($data ['module']);

            //get Model
            $model = $this->_getModel();

            //save...
            $res = $model->insertEvent($data);
            if ($res == null) {
                echo "Fehler beim Speichern!";
                return;
            } else {
                echo $res;
            }
        }
    }

    public function geteventsAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $model = $this->_getModel();

        $data = $this->getRequest()->getParams();

        //delete wast'd data
        unset($data ['controller']);
        unset($data ['action']);
        unset($data ['module']);


        //get all
        if ($data ['mode'] == "all") {
            $html = "";

            $events = $model->getAllEvents();
            $aLetter = "a";
            $html .= "<div style=\"float: left;\"><a name=\"num\">&nbsp;</a></div>";
            foreach ($events as $event) {

                //set anchor


                if (ord(strtolower(substr($event['name'], 0, 1))) == ord("a")) {
                    $html .= "<div style=\"float: left;\"><a name=\"" . $aLetter . "\">&nbsp;</a></div>";
                } elseif (ord(strtolower(substr($event['name'], 0, 1))) > ord("a")) {
                    while (strtolower(substr($event['name'], 0, 1)) != $aLetter) {
                        $aLetter = chr(ord($aLetter) + 1);
                        $html .= "<div style=\"float: left;\"><a name=\"" . $aLetter . "\">&nbsp;</a></div>";
                    }
                }


                $html .= "<div class=\"eventbox\" style=\"background-color: " . $event ['color'] . "\">";

                $html .= "<a href=\"/Eventshift/changeevent/" . $event ['ID'] . "\"><img src=\"/images/bearb.gif\"></a>";
                $html .= "<span class=\"eventheading\"><strong> " . $event ['name'] . "</strong> (" . $event ['location_name'] . ")</span> ";

                $html .= $event ['user_id'] . " " . $event ['user_fname'] . " " . $event ['user_lname'] . " ";

                $html .= "<br>";
                $shifts = $model->getShiftsByEventid($event ['ID']);

                foreach ($shifts as $shift) {

                    $countlinks = $model->countLinksForShift($shift ['ID']);

                    //change dates/times
                    $start = substr($shift ['start'], 11, 5);
                    $end = substr($shift ['end'], 11, 5);


                    $html .= "<div class=\"shiftbox\">";
                    $html .= "<a href=\"/Eventshift/changeevent/" . $event ['ID'] . "/" . $shift['ID'] . "\"><img src=\"/images/bearb.gif\"></a> ";
                    $html .= "<strong>" . $shift ['name'] . "</strong>";
                    $html .= "<table class=\"shiftdeflist\">";

                    $html .= "<tr><td class=\"shifttabheading\">Start</td><td>" . $start . " Uhr</td>";
                    $html .= "<td class=\"shifttabheading\">Ende</td><td>" . $end . " Uhr</td>";
                    if ($countlinks < $shift ['reqstaff']) {
                        $html .= "<td class=\"shifttabheading\">MA-Bedarf</td><td><span style=\"color: red; font-weight: bold;\">" . $shift ['reqstaff'] . " (" . $countlinks . ")</span></td>";
                    } elseif ($shift ['reqstaff'] == null) {
                        $html .= "<td class=\"shifttabheading\">MA-Bedarf</td><td><strong>keiner</strong> (" . $countlinks . ")</td>";
                    } else {
                        $html .= "<td class=\"shifttabheading\">MA-Bedarf</td><td>" . $shift ['reqstaff'] . " (" . $countlinks . ")</td>";
                    }
                    $html .= "</table>";
                    $html .= "</div>";
                }
                $html .= "</div>";
            }

            while (ord($aLetter) < ord("z")) {
                $aLetter = chr(ord($aLetter) + 1);
                $html .= "<div style=\"float: left;\"><a name=\"" . $aLetter . "\">&nbsp;</a></div>";
            }


            echo $html;
        } elseif ($data['mode'] == "notfull") {
            $html = "";

            $events = $model->getAllEvents();
            foreach ($events as $event) {

                $eventhtml = "<div class=\"eventbox\" style=\"background-color: " . $event ['color'] . "\">";

                $eventhtml .= "<a href=\"/Eventshift/changeevent/" . $event ['ID'] . "\"><img src=\"/images/bearb.gif\"\\></a>";
                $eventhtml .= "<span class=\"eventheading\"><strong> " . $event ['name'] . "</strong> (" . $event ['location_name'] . ")</span> ";

                $eventhtml .= $event ['user_id'] . " " . $event ['user_fname'] . " " . $event ['user_lname'] . " ";

                $eventhtml .= "<br>";

                $shifts = $model->getShiftsByEventid($event ['ID']);
                $shifthtml = "";
                foreach ($shifts as $shift) {

                    $countlinks = $model->countLinksForShift($shift ['ID']);

                    if ($shift['reqstaff'] != $countlinks) {
                        //change dates/times
                        $start = substr($shift ['start'], 11, 5);
                        $end = substr($shift ['end'], 11, 5);

                        $shifthtml .= "<div class=\"shiftbox\">";
                        $shifthtml .= "<a href=\"/Eventshift/changeevent/" . $event ['ID'] . "/" . $shift['ID'] . "\"><img src=\"/images/bearb.gif\"></a> ";
                        $shifthtml .= "<strong>" . $shift ['name'] . "</strong>";
                        $shifthtml .= "<table class=\"shiftdeflist\">";

                        $shifthtml .= "<tr><td class=\"shifttabheading\">Start</td><td>" . $start . " Uhr</td></tr>";
                        $shifthtml .= "<tr><td class=\"shifttabheading\">Ende</td><td>" . $end . " Uhr</td></tr>";
                        if ($countlinks < $shift ['reqstaff']) {
                            $shifthtml .= "<tr><td class=\"shifttabheading\">MA-Bedarf</td><td><span style=\"color: red; font-weight: bold;\">" . $shift ['reqstaff'] . " (" . $countlinks . ")</span></td>";
                        } elseif ($shift ['reqstaff'] == null) {
                            $shifthtml .= "<tr><td class=\"shifttabheading\">MA-Bedarf</td><td><strong>keiner</strong> (" . $countlinks . ")</td>";
                        } else {
                            $shifthtml .= "<tr><td class=\"shifttabheading\">MA-Bedarf</td><td>" . $shift ['reqstaff'] . " (" . $countlinks . ")</td>";
                        }

                        $shifthtml .= "</table>";
                        $shifthtml .= "</div>\n";
                    }
                }
                if ($shifthtml != "") {
                    $eventhtml .= $shifthtml . "</div>\n";
                    $html .= $eventhtml;
                }
            }

            echo $html;
        }
    }

    public function changeeventAction() {

        if (!$this->getRequest()->isPost()) {

            //set title
            $this->view->title = 'Veranstaltung bearbeiten';

            $data = $this->getRequest()->getParams();

            $model = $this->_getModel();
            //get eventdata for view
            $eventdata = $model->getEventByID($data ['ID']);
            $this->view->eventdata = $eventdata;
            if (isset($data['shiftID']))
                $this->view->selshift = $data['shiftID'];
            //build up lead options
            $html = "<table id=\"leadtab\">";
            $i = 1;
            $users = $model->getAllUsers();
            foreach ($users as $user) {
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
                $html .= "<a href=\"#\" class=\"leadvaluebutton\" id=\"" . $user ['ID'] . "\">" . $user ['fname'] . " " . $user ['lname'] . "</a>";
                $html .= $html_end;
            }
            if ($i == 1) {
                $html .= "<td></td><td></td></tr></table>\n";
            } elseif ($i == 2) {
                $html .= "<td></td></tr></table>\n";
            } elseif ($i == 3) {
                $html .= "</table>\n";
            }
            $this->view->leadopts = $html;
            unset($html);

            //build up location options
            $html = "<table id=\"locationtab\">";
            $i = 1;
            $locations = $model->getAllLocations();
            foreach ($locations as $location) {
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
                $html .= "<a href=\"#\" class=\"locationvaluebutton\" id=\"" . $location ['ID'] . "\">" . $location ['name'] . "</a>";
                $html .= $html_end;
            }
            if ($i == 1) {
                $html .= "<td></td><td></td></tr></table>\n";
            } elseif ($i == 2) {
                $html .= "<td></td></tr></table>\n";
            } elseif ($i == 3) {
                $html .= "</table>\n";
            }
            $this->view->locationopts = $html;
            unset($html);

            //give standard-date to view
            $registry = Zend_Registry::getInstance();
            $this->view->standarddate = $registry->config->standard_date;
        }
        //
        //save data
        if ($this->getRequest()->isPost()) {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            $data = $this->getRequest()->getParams();

            //delete or change?
            //change
            if (!isset($data ['delevent'])) {

                //delete wast'd data
                unset($data ['controller']);
                unset($data ['action']);
                unset($data ['module']);

                //get Model
                $model = $this->_getModel();

                //save...
                $res = $model->updateEvent($data);
                if ($res == null) {
                    echo "Fehler beim Speichern!";
                    return;
                } else {
                    echo $res . " Datensatz ge&auml;ndert";
                }
            } else {

                //get Model
                $model = $this->_getModel();

                //Meldung auf das UI ausgeben 
                $result = $model->delEvent($data['form_ID']);
                $msg = Array();
                if ($result == 0) {
                    $msg['shorttext'] = "Nichts geändert";
                    $msg['longtext'] = "Keine Daten geändert oder gespeichert.";
                    $msg['cat'] = "info";
                } elseif ($result == 1) {
                    $msg['shorttext'] = "Veranstaltung gelöscht";
                    $msg['longtext'] = "Die Veranstaltung wurde gelöscht.";
                    $msg['cat'] = "success";
                } else {
                    $msg['shorttext'] = "Fehler beim Löschen";
                    $msg['longtext'] = "Irgendwas ging schief. Probiers nochmal. Danke!";
                    $msg['cat'] = "warning";
                }

                $msg['new'] = "true";
                $this->_helper->FlashMessenger->addMessage($this->_helper->json->encodeJson($msg));

                $this->_helper->redirector('index', 'Eventshift');
            }
        }
    }

    public function getshiftsAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getParams();
            $model = $this->_getModel();
            $shifts = $model->getShiftsByEventid($data ['ID']);

            foreach ($shifts as $shift) {
                $start = substr($shift ['start'], 11, 5) . "h " . substr($shift ['start'], 8, 2) . "." . substr($shift ['start'], 5, 2) . ".";
                $end = substr($shift ['end'], 11, 5) . "h " . substr($shift ['end'], 8, 2) . "." . substr($shift ['end'], 5, 2) . ".";
                echo "<option value=\"" . $shift ['ID'] . "\">" . $shift ['name'] . " | " . $start . " | " . $end . "</option>";
            }

            echo "<option value=\"-1\">NEU...</option>";
        }
    }

    public function getshiftdataAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        //@TODO Userabfrage einfügen
        //... eigentlich nicht notwendig, Session wird im Plugin geprüft.
        //Überlegen, was machbar, wenn Prüfung schief läuft. Im Moment: Login-Screen wird ausgeliefert
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getParams();


            if (!isset($data ['ID'])) {
                echo "keine ID";
                die;
            }


            $model = $this->_getModel();
            $shift = $model->getShiftByID($data ['ID']);

            //change charset
            $shift['name'] = $shift['name'];
            $shift['reqstaff'] = $shift['reqstaff'];
            $shift['note'] = $shift['note'];

            //change date/time
            $shift ['startdate'] = date("d.m.Y", $shift ['start']);
            $shift ['starttime'] = date("H:i", $shift ['start']);
            $shift ['enddate'] = date("d.m.Y", $shift ['end']);
            $shift ['endtime'] = date("H:i", $shift ['end']);
            unset($shift ['start']);
            unset($shift ['end']);

            echo json_encode($shift);
        }
    }

    public function delshiftAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getParams();
            $model = $this->_getModel();
            $res = $model->delShift($data ['ID']);

            if ($res == 0) {
                echo "Nicht gelöscht!";
            } elseif ($res == 1) {
                echo "1 Datensatz gel&ouml;scht!";
            } else {
                echo "Ergebnis unbekannt - Rückgabewert: " . $res;
            }
        }
    }

    public function checkshiftnewtimeAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ($this->getRequest()->isPost()) {

            $data = $this->getRequest()->getParams();

            unset($data ['controller']);
            unset($data ['action']);
            unset($data ['module']);

            $data ['userID'] = $data ['userID'];
            $data ['shiftID'] = $data ['shiftID'];
            $data ['startdate'] = $data ['startdate'];
            $data ['starttime'] = $data ['starttime'];
            $data ['enddate'] = $data ['enddate'];
            $data ['endtime'] = $data ['endtime'];

            $start = mktime(substr($data ['starttime'], 0, 2), substr($data ['starttime'], 3, 2), 0, substr($data ['startdate'], 3, 2), substr($data ['startdate'], 0, 2), substr($data ['startdate'], 6, 4));

            $end = mktime(substr($data ['endtime'], 0, 2), substr($data ['endtime'], 3, 2), 0, substr($data ['enddate'], 3, 2), substr($data ['enddate'], 0, 2), substr($data ['enddate'], 6, 4));

            $model = $this->_getModel();

            if ($model->checkTimeForUser($data ['userID'], $data ['shiftID'], $start, $end)) {
                echo "true";
            } else {
                echo "false";
            }
        }
    }

    public function saveshiftAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);


        if ($this->getRequest()->isPost()) {

            $data = $this->getRequest()->getParams();

            unset($data ['controller']);
            unset($data ['action']);
            unset($data ['module']);

            //Leerzeichen am Anfang und Ende entfernen
            $data ['name'] = trim($data ['name']);
            $data ['note'] = trim($data ['note']);

            //change to null
            if ($data ['reqstaff'] == "") {
                $data ['reqstaff'] = null;
            }

            //is start before end?
            $start = (int) substr($data ['startdate'], 6, 4) . substr($data ['startdate'], 3, 2) . substr($data ['startdate'], 0, 2) . substr($data ['starttime'], 0, 2) . substr($data ['starttime'], 3, 2);
            $end = (int) substr($data ['enddate'], 6, 4) . substr($data ['enddate'], 3, 2) . substr($data ['enddate'], 0, 2) . substr($data ['endtime'], 0, 2) . substr($data ['endtime'], 3, 2);

            if ($start > $end) {
                $return['text'] = "Ende liegt vor Beginn! Nicht gespeichert!";
                echo json_encode($return);
            } elseif ($start == $end) {
                $return['text'] = "Ende = Beginn: Keine Zeitspanne! Nicht gespeichert!";
                echo json_encode($return);
            } else {
                //change date...
                $data ['start'] = substr($data ['startdate'], 6, 4) . "-" . substr($data ['startdate'], 3, 2) . "-" . substr($data ['startdate'], 0, 2) . " " . substr($data ['starttime'], 0, 2) . ":" . substr($data ['starttime'], 3, 2) . ":00";

                $data ['end'] = substr($data ['enddate'], 6, 4) . "-" . substr($data ['enddate'], 3, 2) . "-" . substr($data ['enddate'], 0, 2) . " " . substr($data ['endtime'], 0, 2) . ":" . substr($data ['endtime'], 3, 2) . ":00";

                unset($data ['startdate']);
                unset($data ['starttime']);
                unset($data ['enddate']);
                unset($data ['endtime']);

                $model = $this->_getModel();
                //check if update or insert is neccessary
                if ($data ['ID'] != "") {
                    $res = $model->updateShift($data);

                    $return['ID'] = $data['ID'];

                    if ($res == 0) {
                        $return['text'] = "Nichts ge&auml;ndert!";
                    } elseif ($res == 1) {
                        $return['text'] = "1 Datensatz ge&auml;ndert!";
                    } else {
                        $return['text'] = "Neuer Datensatz mit ID " . $res;
                    }

                    echo json_encode($return);
                } else {
                    $res = $model->insertShift($data);

                    $return['ID'] = $res;

                    if ($res == 0) {
                        $return['text'] = "Nichts ge&auml;ndert!";
                    } elseif ($res == 1) {
                        $return['text'] = "1 Datensatz ge&auml;ndert!";
                    } else {
                        $return['text'] = "Neuer Datensatz mit ID " . $res;
                    }

                    echo json_encode($return);
                }
            }
        }
    }

    public function getusersAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ($this->getRequest()->isPost()) {

            $data = $this->getRequest()->getParams();
            $model = $this->_getModel();
            if ($data ['selection'] == "added") {
                $users = $model->getUsersOfShift($data ['ID']);
                foreach ($users as $user) {
                    echo "<option value=\"" . $user ['user_ID'] . "\">" . $user ['user_fname'] . " " . $user ['user_lname'] . "</option>";
                }
            } elseif ($data ['selection'] == "all") {
                $users = $model->getAllPosUsers($data['ID'], $data['remfull']);
                foreach ($users as $user) {
                    echo "<option class=\"posuser\" value=\"" . $user ['ID'] . "\">" . $user ['fname'] . " " . $user ['lname'] . "</option>";
                }
            } elseif ($data ['selection'] == "time") {
                $users = $model->getTimePosUsers($data['ID'], $data['remfull']);
                foreach ($users as $user) {
                    echo "<option value=\"" . $user ['ID'] . "\">" . $user ['fname'] . " " . $user ['lname'] . "</option>";
                }
            } elseif ($data ['selection'] == "timestrong") {
                $users = $model->getStrongTimePosUsers($data['ID'], $data['remfull']);
                foreach ($users as $user) {
                    echo "<option value=\"" . $user ['ID'] . "\">" . $user ['fname'] . " " . $user ['lname'] . "</option>";
                }
            } else {
                echo "<option>Noch nicht implementiert</option>";
            }
        }
    }

    //save and delete links
    public function changelinkAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if ($this->getRequest()->isPost()) {

            $data = $this->getRequest()->getParams();
            $model = $this->_getModel();

            unset($data ['controller']);
            unset($data ['action']);
            unset($data ['module']);
            if ($data ['mode'] == "insert") {
                //check for überschneidungen
                $posUsers = $model->getTimePosUsers($data['shiftid']);
                $possible = false;
                foreach ($posUsers as $posUser) {
                    if ($posUser['ID'] == $data['userid']) {
                        $possible = true;
                    }
                }
                if ($possible) {
                    unset($data ['mode']);
                    echo $model->insertLink($data);
                } else {
                    echo "error";
                }
            } elseif ($data ['mode'] == "insertanyway") {
                unset($data ['mode']);
                echo $model->insertLink($data);
            } elseif ($data ['mode'] == "delete") {
                unset($data ['mode']);
                echo $model->delLink($data);
            } else {
                echo "Error: keine Funktion";
            }
        }
    }

    //generate process slips for complete staff
    public function generatepsAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        //get data
        $data = $this->getRequest()->getParams();
        $model = $this->_getModel();
        $user = $model->getUserData($data['ID']);


        //set up page
        $pdfdoc = new Zend_Pdf();
        $pdfPage = $pdfdoc->newPage(Zend_Pdf_Page::SIZE_A4);

        //colors
        $colorBlack = new Zend_Pdf_Color_Html('black');
        $colorGrey = new Zend_Pdf_Color_Html('#CCCCCC');
        $colorDarkgrey = new Zend_Pdf_Color_Html('#AAAAAA');
        $colorWhite = new Zend_Pdf_Color_Html('white');
        $image = Zend_Pdf_Image::imageWithPath('images/tnlogo.jpg');

        //logo
        $pdfPage->drawImage($image, 390, 800, 550, 830);

        //title
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
        $pdfPage->setFont($font, 18);
        $string = $user['fname'] . " " . $user['lname'];
        $pdfPage->drawText($string, 72, 770, 'UTF-8');
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
        $pdfPage->setFont($font, 18);
        $pdfPage->setFillColor($colorDarkgrey);
        $string = ' || JOBÜBERSICHT';
        $pdfPage->drawText($string, 385, 770, 'UTF-8');
        $pdfPage->drawLine(60, 760, 540, 760);

        //print time
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $pdfPage->setFont($font, 7);
        $pdfPage->drawText('erzeugt: ' . Date("d.m.Y H:i:s") . ' Uhr', 430, 752, 'UTF-8');


        //write User's Basic data
        //ID
        $pdfPage->setFillColor($colorBlack);
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText('ID:', 72, 750, 'UTF-8');
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText($user['ID'], 120, 750, 'UTF-8');
        //mail adress
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText('eMail:', 72, 740, 'UTF-8');
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText($user['email'], 120, 740, 'UTF-8');
        //mobile phone
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText('Handy-Nr:', 72, 730, 'UTF-8');
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText($user['mphone'], 120, 730, 'UTF-8');
        //phone
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText('Telefon:', 72, 720, 'UTF-8');
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText($user['phone'], 120, 720, 'UTF-8');
        //steet / H.No.
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText('Straße:', 72, 710, 'UTF-8');
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText($user['street'], 120, 710, 'UTF-8');
        //city
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText('Ort:', 72, 700, 'UTF-8');
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText($user['city'], 120, 700, 'UTF-8');
        //KV
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText('KV:', 72, 690, 'UTF-8');
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText($user['kv_name'], 120, 690, 'UTF-8');
        //birthday
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText('Geburstag:', 72, 680, 'UTF-8');
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $pdfPage->setFont($font, 8);
        $string = substr($user['birthday'], 8, 2) . "." . substr($user['birthday'], 5, 2) . "." . substr($user['birthday'], 0, 4);
        $pdfPage->drawText($string, 120, 680, 'UTF-8');

        //horitontal line
        $pdfPage->drawLine(60, 670, 540, 670);


        //EVENTS/SHIFTS heading
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $pdfPage->setFont($font, 12);
        $pdfPage->drawText('Deine Veranstaltungen:', 72, 650, 'UTF-8');

        //build up events/shifts
        $y = 630;

        //get shifts
        $shifts = $model->getShiftsOfUser($user['ID']);

        $shiftcounter = 0;
        foreach ($shifts as $shift) {

            $shiftcounter++;

            //if more than 9, create new page and header...
            if ($shiftcounter % 9 == 0) {
                $pdfdoc->pages[] = $pdfPage;
                $pdfPage = $pdfdoc->newPage(Zend_Pdf_Page::SIZE_A4);

                //logo
                $pdfPage->drawImage($image, 390, 800, 550, 830);

                //title
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
                $pdfPage->setFont($font, 18);
                $string = $user['fname'] . " " . $user['lname'];
                $pdfPage->drawText($string, 72, 780, 'UTF-8');
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
                $pdfPage->setFont($font, 18);
                $pdfPage->setFillColor($colorDarkgrey);
                $string = ' || JOBÜBERSICHT';
                $pdfPage->drawText($string, 385, 780, 'UTF-8');
                $pdfPage->drawLine(60, 770, 540, 770);
                $pdfPage->setFillColor($colorBlack);
                $string = 'SEITE 2';
                $pdfPage->drawText($string, 72, 750, 'UTF-8');

                //print time
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $pdfPage->setFont($font, 7);
                $pdfPage->drawText('erzeugt: ' . Date("d.m.Y h:i:s") . ' Uhr', 430, 762, 'UTF-8');

                $y = 700;
            }

            $event = $model->getEventByID($shift['eventid']);

            //time box
            $pdfPage->setLineColor($colorGrey);
            $pdfPage->setFillColor($colorGrey);
            $pdfPage->drawRectangle(72, $y - 60, 142, $y, Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);

            //time text
            $pdfPage->setFillColor($colorBlack);
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
            $pdfPage->setFont($font, 12);
            $pdfPage->drawText(date("H:i", $shift['start']) . " Uhr", 75, $y - 15, 'UTF-8');
            $pdfPage->drawText(' - ', 75, $y - 30, 'UTF-8');
            $pdfPage->drawText(date("H:i", $shift['end']) . " Uhr", 75, $y - 45, 'UTF-8');

            //info box
            $pdfPage->setLineColor($colorGrey);
            $pdfPage->setFillColor($colorGrey);
            $pdfPage->drawRectangle(145, $y - 60, 520, $y, Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);

            //info text
            //eventID and shiftID
            //event and shift
            $pdfPage->setFillColor($colorBlack);
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
            $pdfPage->setFont($font, 14);
            $pdfPage->drawText($event['name'] . " || " . $shift['name'], 147, $y - 17, 'UTF-8');
            //location
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
            $pdfPage->setFont($font, 11);
            $pdfPage->drawText('Location: ', 147, $y - 30, 'UTF-8');
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $pdfPage->setFont($font, 11);
            $pdfPage->drawText($event['location_name'], 230, $y - 30, 'UTF-8');
            //lead
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
            $pdfPage->setFont($font, 11);
            $pdfPage->drawText('Verantwortlich: ', 147, $y - 42, 'UTF-8');
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $pdfPage->setFont($font, 11);
            $pdfPage->drawText($event['lead_fname'] . " " . $event['lead_lname'], 230, $y - 42, 'UTF-8');
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $pdfPage->setFont($font, 11);
            $pdfPage->drawText('(' . $event['lead_mphone'] . ')', 230, $y - 54, 'UTF-8');

            //info / note
            if ($shift['note'] != "") {
                //box
                $pdfPage->setLineColor($colorGrey);
                $pdfPage->setFillColor($colorGrey);
                $pdfPage->drawRectangle(375, $y - 60, 520, $y, Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);
                $pdfPage->setLineColor($colorBlack);
                $pdfPage->setFillColor($colorBlack);
                $pdfPage->drawRectangle(380, $y - 10, 520, $y, Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);

                //heading
                $pdfPage->setFillColor($colorWhite);
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                $pdfPage->setFont($font, 7);
                $pdfPage->drawText('Hinweis: ', 382, $y - 8, 'UTF-8');

                //text
                $stringWTags = wordwrap($shift['note'], 35, "\0", true);
                $stringArr = explode("\0", $stringWTags);
                $i = 0;

                while (isset($stringArr[$i]) && $i < 6) {
                    $pdfPage->setFillColor($colorBlack);
                    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                    $pdfPage->setFont($font, 6);
                    if ($i == 5) {
                        $stringArr[$i] = $stringArr[$i] . "...";
                    }
                    $pdfPage->drawText($stringArr[$i], 382, $y - 18 - $i * 8, 'UTF-8');

                    $i++;
                }
            }


            $y = $y - 67;
        }


        $pdfdoc->pages[] = $pdfPage;


        //set Header and release document
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="downloaded.pdf"');
        echo $pdfdoc->render();
    }

    //complete overview for "round the clock" team
    public function overviewAction() {

        if ($this->getRequest()->isPost()) {

            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            $data = $this->getRequest()->getParams();
            $model = $this->_getModel();

            unset($data ['controller']);
            unset($data ['action']);
            unset($data ['module']);

            //colors
            $colorBlack = new Zend_Pdf_Color_Html('black');
            $colorGrey = new Zend_Pdf_Color_Html('#CCCCCC');
            $colorWhite = new Zend_Pdf_Color_Html('white');
            $image = Zend_Pdf_Image::imageWithPath('images/tnlogo.jpg');

            //set up page
            $pdfdoc = new Zend_Pdf();
            $pdfPage = $pdfdoc->newPage(Zend_Pdf_Page::SIZE_A4);

            //logo
            $pdfPage->drawImage($image, 390, 805, 550, 835);

            //print header
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
            $pdfPage->setFont($font, 20);
            $pdfPage->setFillColor($colorBlack);
            $pdfPage->drawText("Veranstaltungs-Übersicht", 55, 805, 'UTF-8');

            //get shift data
            $shifts = $model->getAllShiftsTime();


            $y = 800;
            $timeSec = 0;
            $page = 1;

            //print time
            $pdfPage->setFillColor($colorBlack);
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $pdfPage->setFont($font, 7);
            $pdfPage->drawText('erzeugt: ' . Date("d.m.Y H:i:s") . ' Uhr', 55, 827, 'UTF-8');

            foreach ($shifts as $shift) {


                //write Time Block if neccessary
                if (date("H:i", $timeSec) != date("H:i", $shift['start'])) {

                    $timeSec = $shift['start'];

                    $y = $y - 20;

                    //time box
                    $pdfPage->setLineColor($colorBlack);
                    $pdfPage->setFillColor($colorBlack);
                    $pdfPage->drawRectangle(60, $y - 20, 550, $y, Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);

                    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                    $pdfPage->setFont($font, 18);
                    $pdfPage->setFillColor($colorWhite);
                    $string = "Ab " . date("H:i", $shift['start']) . " Uhr";
                    $pdfPage->drawText($string, 76, $y - 16, 'UTF-8');

                    $y = $y - 4;
                }

                $y = $y - 20;

                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $pdfPage->setFont($font, 12);

                //grey box
                $pdfPage->setLineColor($colorGrey);
                $pdfPage->setFillColor($colorGrey);
                $pdfPage->drawRectangle(84, $y - 15, 550, $y, Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);

                $pdfPage->setFillColor($colorBlack);
                $string = date("H:i", $shift['start']) . " - " . date("H:i", $shift['end']) . ": " . $shift['event_name'] . " || " . $shift['name'];
                $pdfPage->drawText($string, 85, $y - 12, 'UTF-8');

                $y = $y - 2;

                $users = $model->getUsersOfShift($shift['ID']);

                foreach ($users as $user) {

                    $y = $y - 16;

                    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                    $pdfPage->setFont($font, 14);

                    $pdfPage->setFillColor($colorBlack);

                    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                    $pdfPage->setFont($font, 14);
                    $string = $user['user_fname'] . " " . $user['user_lname'];
                    if ($user['user_mphone'] != "") {
                        $string .= " (" . $user['user_mphone'] . ")";
                    }
                    $pdfPage->drawText($string, 120, $y - 15, 'UTF-8');

                    $y = $y - 2;

                    //change page
                    if ($y <= 80) {

                        //print page number
                        $pdfPage->setFillColor($colorGrey);
                        $pdfPage->drawText("Seite " . $page, 300, 30, 'UTF-8');
                        $page++;

                        //create page
                        $pdfdoc->pages[] = $pdfPage;
                        $pdfPage = $pdfdoc->newPage(Zend_Pdf_Page::SIZE_A4);
                        $y = 800;

                        //logo
                        $pdfPage->drawImage($image, 390, 805, 550, 835);

                        //repeat shift head
                        //grey box
                        $pdfPage->setLineColor($colorGrey);
                        $pdfPage->setFillColor($colorGrey);
                        $pdfPage->drawRectangle(84, $y - 15, 550, $y, Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);

                        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                        $pdfPage->setFont($font, 12);

                        $pdfPage->setFillColor($colorBlack);
                        $string = "WEITER: " . date("H:i", $shift['start']) . " - " . date("H:i", $shift['end']) . ": " . $shift['event_name'] . " || " . $shift['name'];
                        $pdfPage->drawText($string, 85, $y - 12, 'UTF-8');

                        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                        $pdfPage->setFont($font, 14);

                        $y = $y - 2;
                    }
                }

                $y = $y - 10;


                if ($y <= 80) {

                    //print page number
                    $pdfPage->setFillColor($colorGrey);
                    $pdfPage->drawText("Seite " . $page, 300, 30, 'UTF-8');
                    $page++;

                    //create page
                    $pdfdoc->pages[] = $pdfPage;
                    $pdfPage = $pdfdoc->newPage(Zend_Pdf_Page::SIZE_A4);
                    $y = 800;

                    //logo
                    $pdfPage->drawImage($image, 390, 800, 550, 830);
                }
            }

            $pdfdoc->pages[] = $pdfPage;


            //set Header and release document
            header('Content-type: application/pdf');
            header('Content-Disposition: attachment; filename="downloaded.pdf"');
            echo $pdfdoc->render();
        }
    }

    //Lists for event leads
    public function eventoverviewAction() {
        if ($this->getRequest()->isPost()) {

            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            $data = $this->getRequest()->getParams();
            $model = $this->_getModel();
            unset($data ['controller']);
            unset($data ['action']);
            unset($data ['module']);

            //colors
            $colorBlack = new Zend_Pdf_Color_Html('black');
            $colorGrey = new Zend_Pdf_Color_Html('#CCCCCC');
            $colorWhite = new Zend_Pdf_Color_Html('white');
            $image = Zend_Pdf_Image::imageWithPath('images/tnlogo.jpg');

            //set up page
            $pdfdoc = new Zend_Pdf();
            $pdfPage = $pdfdoc->newPage(Zend_Pdf_Page::SIZE_A4);

            //logo
            $pdfPage->drawImage($image, 390, 800, 550, 830);

            //get event data
            $event = $model->getEventByID($data['ID']);

            //print time
            $pdfPage->setFillColor($colorBlack);
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $pdfPage->setFont($font, 7);
            $pdfPage->drawText('erzeugt: ' . Date("d.m.Y H:i:s") . ' Uhr', 55, 822, 'UTF-8');

            //write event head
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
            $pdfPage->setFont($font, 23);
            $pdfPage->setFillColor($colorBlack);
            $pdfPage->drawText($event['name'], 60, 790, 'UTF-8');

            $y = 795;
            $page = 1;

            $shifts = $model->getShiftsByEventid($event['ID']);


            foreach ($shifts as $shift) {
                $y = $y - 20;

                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $pdfPage->setFont($font, 12);

                //grey box
                $pdfPage->setLineColor($colorGrey);
                $pdfPage->setFillColor($colorGrey);
                $pdfPage->drawRectangle(84, $y - 15, 550, $y, Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);

                $pdfPage->setFillColor($colorBlack);
                $start = substr($shift['start'], 11, 5);
                $end = substr($shift['end'], 11, 5);
                $string = $start . " Uhr - " . $end . " Uhr ";
                $pdfPage->drawText($string, 85, $y - 12, 'UTF-8');
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
                $pdfPage->setFont($font, 12);
                $string = $shift['name'];
                $pdfPage->drawText($string, 220, $y - 12, 'UTF-8');
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
                $pdfPage->setFont($font, 12);
                $string = "Ben. MAs: " . $shift['reqstaff'];
                $pdfPage->drawText($string, 450, $y - 12, 'UTF-8');

                $y = $y - 2;

                $users = $model->getUsersOfShift($shift['ID']);
                $usrcount = 1;
                foreach ($users as $user) {

                    $y = $y - 16;

                    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                    $pdfPage->setFont($font, 14);

                    $pdfPage->setFillColor($colorBlack);

                    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                    $pdfPage->setFont($font, 14);
                    $pdfPage->drawText($usrcount++, 120, $y - 15, 'UTF-8');
                    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                    $pdfPage->setFont($font, 14);
                    $string = $user['user_fname'] . " " . $user['user_lname'];
                    if ($user['user_mphone'] != "") {
                        $string .= " (" . $user['user_mphone'] . ")";
                    }
                    $pdfPage->drawText($string, 140, $y - 15, 'UTF-8');

                    $y = $y - 2;

                    //change page
                    if ($y <= 80) {

                        //print page number
                        $pdfPage->setFillColor($colorGrey);
                        $pdfPage->drawText("Seite " . $page, 300, 30, 'UTF-8');
                        $page++;

                        //create page
                        $pdfdoc->pages[] = $pdfPage;
                        $pdfPage = $pdfdoc->newPage(Zend_Pdf_Page::SIZE_A4);
                        $y = 800;

                        //logo
                        $pdfPage->drawImage($image, 390, 805, 550, 835);

                        //repeat shift head
                        //grey box
                        $pdfPage->setLineColor($colorGrey);
                        $pdfPage->setFillColor($colorGrey);
                        $pdfPage->drawRectangle(84, $y - 15, 550, $y, Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);

                        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                        $pdfPage->setFont($font, 12);

                        $pdfPage->setFillColor($colorBlack);
                        $string = "WEITER: " . date("H:i", $shift['start']) . " - " . date("H:i", $shift['end']) . ": " . $shift['event_name'] . " || " . $shift['name'];
                        $pdfPage->drawText($string, 85, $y - 12, 'UTF-8');

                        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                        $pdfPage->setFont($font, 14);

                        $y = $y - 2;
                    }

                    if ($y <= 80) {

                        //print page number
                        $pdfPage->setFillColor($colorGrey);
                        $pdfPage->drawText("Seite " . $page, 300, 30, 'UTF-8');
                        $page++;

                        //create page
                        $pdfdoc->pages[] = $pdfPage;
                        $pdfPage = $pdfdoc->newPage(Zend_Pdf_Page::SIZE_A4);
                        $y = 800;

                        //logo
                        $pdfPage->drawImage($image, 390, 805, 550, 835);
                    }
                }

                if ($y <= 80) {

                    //print page number
                    $pdfPage->setFillColor($colorGrey);
                    $pdfPage->drawText("Seite " . $page, 300, 30, 'UTF-8');
                    $page++;

                    //create page
                    $pdfdoc->pages[] = $pdfPage;
                    $pdfPage = $pdfdoc->newPage(Zend_Pdf_Page::SIZE_A4);
                    $y = 800;

                    //logo
                    $pdfPage->drawImage($image, 390, 805, 550, 835);
                }
            }
            $pdfdoc->pages[] = $pdfPage;

            //set Header and release document
            header('Content-type: application/pdf');
            header('Content-Disposition: attachment; filename="downloaded.pdf"');
            echo $pdfdoc->render();
        }
    }

    //walklist selection
    public function walklistAction() {
        //set title
        $this->view->title = 'Laufzettel erstellen';
    }

    //generate PDF for complete staff
    public function generatewalklistAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        //get data
        $data = $this->getRequest()->getParams();
        $model = $this->_getModel();

        //Alle User anzeigen oder nur die mit Schichten
        if (isset($data['user_check_all'])) {
            $data['user_check_all'] = 1;
        } else {
            $data['user_check_all'] = 0;
        }

        //set up page
        $pdfdoc = new Zend_Pdf();


        $users = $model->getAllUsers();


        //colors
        $colorBlack = new Zend_Pdf_Color_Html('black');
        $colorGrey = new Zend_Pdf_Color_Html('#CCCCCC');
        $colorDarkgrey = new Zend_Pdf_Color_Html('#AAAAAA');
        $colorWhite = new Zend_Pdf_Color_Html('white');
        $image = Zend_Pdf_Image::imageWithPath('images/tnlogo.jpg');

        foreach ($users as $user) {

            //get shifts
            $shifts = $model->getShiftsOfUser($user['ID']);

            //Wenn user_check_all, dann alle User anzeigen, ansonsten nur die, mit Schichten
            if (count($shifts) > 0 | $data['user_check_all'] == 1) {

                $pdfPage = $pdfdoc->newPage(Zend_Pdf_Page::SIZE_A4);


                //logo
                $pdfPage->drawImage($image, 390, 800, 550, 830);

                //title
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
                $pdfPage->setFont($font, 18);
                $string = $user['fname'] . " " . $user['lname'];
                $pdfPage->drawText($string, 72, 780, 'UTF-8');
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
                $pdfPage->setFont($font, 18);
                $pdfPage->setFillColor($colorDarkgrey);
                $string = ' || JOBÜBERSICHT';
                $pdfPage->drawText($string, 385, 780, 'UTF-8');
                $pdfPage->drawLine(60, 760, 540, 760);

                //print time
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $pdfPage->setFont($font, 7);
                $pdfPage->drawText('erzeugt: ' . Date("d.m.Y H:i:s") . ' Uhr', 430, 752, 'UTF-8');


                //write User's Basic data
                //ID
                $pdfPage->setFillColor($colorBlack);
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                $pdfPage->setFont($font, 8);
                $pdfPage->drawText('ID:', 72, 750, 'UTF-8');
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $pdfPage->setFont($font, 8);
                $pdfPage->drawText($user['ID'], 120, 750, 'UTF-8');
                //mail adress
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                $pdfPage->setFont($font, 8);
                $pdfPage->drawText('eMail:', 72, 740, 'UTF-8');
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $pdfPage->setFont($font, 8);
                $pdfPage->drawText($user['email'], 120, 740, 'UTF-8');
                //mobile phone
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                $pdfPage->setFont($font, 8);
                $pdfPage->drawText('Handy-Nr:', 72, 730, 'UTF-8');
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $pdfPage->setFont($font, 8);
                $pdfPage->drawText($user['mphone'], 120, 730, 'UTF-8');
                //phone
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                $pdfPage->setFont($font, 8);
                $pdfPage->drawText('Telefon:', 72, 720, 'UTF-8');
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $pdfPage->setFont($font, 8);
                $pdfPage->drawText($user['phone'], 120, 720, 'UTF-8');
                //steet / H.No.
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                $pdfPage->setFont($font, 8);
                $pdfPage->drawText('Straße:', 72, 710, 'UTF-8');
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $pdfPage->setFont($font, 8);
                $pdfPage->drawText($user['street'], 120, 710, 'UTF-8');
                //city
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                $pdfPage->setFont($font, 8);
                $pdfPage->drawText('Ort:', 72, 700, 'UTF-8');
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $pdfPage->setFont($font, 8);
                $pdfPage->drawText($user['city'], 120, 700, 'UTF-8');
                //KV
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                $pdfPage->setFont($font, 8);
                $pdfPage->drawText('KV:', 72, 690, 'UTF-8');
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);

                $pdfPage->setFont($font, 8);
                $pdfPage->drawText($user['kv_name'], 120, 690, 'UTF-8');
                //birthday
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                $pdfPage->setFont($font, 8);
                $pdfPage->drawText('Geburstag:', 72, 680, 'UTF-8');
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $pdfPage->setFont($font, 8);
                $string = substr($user['birthday'], 8, 2) . "." . substr($user['birthday'], 5, 2) . "." . substr($user['birthday'], 0, 4);
                $pdfPage->drawText($string, 120, 680, 'UTF-8');

                //horitontal line
                $pdfPage->drawLine(60, 670, 540, 670);


                //EVENTS/SHIFTS heading
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                $pdfPage->setFont($font, 12);
                $pdfPage->drawText('Deine Veranstaltungen:', 72, 650, 'UTF-8');

                //build up events/shifts
                $y = 630;


                $shiftcounter = 0;
                foreach ($shifts as $shift) {

                    $shiftcounter++;

                    //if more than 8, create new page and header...
                    if ($shiftcounter % 8 == 0) {
                        $pdfdoc->pages[] = $pdfPage;
                        $pdfPage = $pdfdoc->newPage(Zend_Pdf_Page::SIZE_A4);

                        //logo
                        $pdfPage->drawImage($image, 390, 800, 550, 830);

                        //title
                        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
                        $pdfPage->setFont($font, 18);
                        $string = $user['fname'] . " " . $user['lname'];
                        $pdfPage->drawText($string, 72, 780, 'UTF-8');
                        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
                        $pdfPage->setFont($font, 18);
                        $pdfPage->setFillColor($colorDarkgrey);
                        $string = ' || JOBÜBERSICHT';
                        $pdfPage->drawText($string, 385, 780, 'UTF-8');
                        $pdfPage->drawLine(60, 770, 540, 770);
                        $pdfPage->setFillColor($colorBlack);
                        $string = 'SEITE 2';
                        $pdfPage->drawText($string, 72, 750, 'UTF-8');

                        //print time
                        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                        $pdfPage->setFont($font, 7);
                        $pdfPage->drawText('erzeugt: ' . Date("d.m.Y H:i:s") . ' Uhr', 430, 762, 'UTF-8');

                        $y = 700;
                    }

                    $event = $model->getEventByID($shift['eventid']);

                    //time box
                    $pdfPage->setLineColor($colorGrey);
                    $pdfPage->setFillColor($colorGrey);
                    $pdfPage->drawRectangle(72, $y - 72, 142, $y, Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);

                    //time text
                    $pdfPage->setFillColor($colorBlack);
                    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                    $pdfPage->setFont($font, 12);
                    $pdfPage->drawText(date("H:i", $shift['start']) . " Uhr", 75, $y - 15, 'UTF-8');
                    $pdfPage->drawText(' - ', 75, $y - 30, 'UTF-8');
                    $pdfPage->drawText(date("H:i", $shift['end']) . " Uhr", 75, $y - 45, 'UTF-8');

                    //info box
                    $pdfPage->setLineColor($colorGrey);
                    $pdfPage->setFillColor($colorGrey);
                    $pdfPage->drawRectangle(145, $y - 72, 520, $y, Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);

                    //info text
                    //eventID and shiftID
                    //info / note
                    if ($shift['note'] != "") {
                        //box
                        $pdfPage->setLineColor($colorGrey);
                        $pdfPage->setFillColor($colorGrey);
                        $pdfPage->drawRectangle(375, $y - 60, 520, $y, Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);
                        $pdfPage->setLineColor($colorBlack);
                        $pdfPage->setFillColor($colorBlack);
                        $pdfPage->drawRectangle(380, $y - 10, 520, $y, Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);

                        //heading
                        $pdfPage->setFillColor($colorWhite);
                        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                        $pdfPage->setFont($font, 7);
                        $pdfPage->drawText('Hinweis: ', 382, $y - 8, 'UTF-8');

                        //text
                        $stringWTags = wordwrap($shift['note'], 35, "\0", true);
                        $stringArr = explode("\0", $stringWTags);
                        $i = 0;

                        while (isset($stringArr[$i]) && $i < 6) {
                            $pdfPage->setFillColor($colorBlack);
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                            $pdfPage->setFont($font, 6);
                            if ($i == 5) {
                                $stringArr[$i] = $stringArr[$i] . "...";
                            }
                            $pdfPage->drawText($stringArr[$i], 382, $y - 18 - $i * 8, 'UTF-8');

                            $i++;
                        }
                    }

                    //event and shift
                    $pdfPage->setFillColor($colorBlack);
                    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                    $pdfPage->setFont($font, 14);
                    $pdfPage->drawText($event['name'], 147, $y - 17, 'UTF-8');
                    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                    $pdfPage->setFont($font, 13);
                    $pdfPage->drawText($shift['name'], 155, $y - 30, 'UTF-8');
                    //location
                    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
                    $pdfPage->setFont($font, 11);
                    $pdfPage->drawText('Location: ', 147, $y - 43, 'UTF-8');
                    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                    $pdfPage->setFont($font, 11);
                    $pdfPage->drawText($event['location_name'], 230, $y - 43, 'UTF-8');
                    //lead
                    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
                    $pdfPage->setFont($font, 11);
                    $pdfPage->drawText('Verantwortlich: ', 147, $y - 55, 'UTF-8');
                    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                    $pdfPage->setFont($font, 11);
                    $pdfPage->drawText($event['lead_fname'] . " " . $event['lead_lname'], 230, $y - 55, 'UTF-8');
                    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                    $pdfPage->setFont($font, 11);
                    $pdfPage->drawText('(' . $event['lead_mphone'] . ')', 230, $y - 67, 'UTF-8');


                    $y = $y - 80;
                }

                $pdfdoc->pages[] = $pdfPage;
            }
        }


        //set Header and release document
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="downloaded.pdf"');
        echo $pdfdoc->render();
    }

    //Verantwortlichkeits Liste
    public function responsiblelistAction() {
        //set title
        $this->view->title = 'Verantwortlichen Liste erstellen';

        //get data
        $data = $this->getRequest()->getParams();
        $model = $this->_getModel();

        $users = $model->getAllUsers();

        echo "<form action=/Eventshift/generateresponsiblelist method=POST>";
        echo "<select name=responsible_user id=form_phone>";

        foreach ($users as $user) {
            echo "<option value=" . $user['ID'] . ">" . $user['fname'] . " " . $user['lname'] . "</option>";
        }

        echo "</select><br>";

        echo "	<div class=inputbox>
				<input type=submit name=pdf value=PDF-Export>
			</div>";


        echo "</form>";
    }

    //generate PDF
    public function generateresponsiblelistAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        //get data
        $data = $this->getRequest()->getParams();
        $model = $this->_getModel();


        //set up page
        $pdfdoc = new Zend_Pdf();

        $user = $model->getUserData($data['responsible_user']);
        $events = $model->getEventsByleadid($data['responsible_user']);


        //colors
        $colorBlack = new Zend_Pdf_Color_Html('black');
        $colorGrey = new Zend_Pdf_Color_Html('#CCCCCC');
        $colorDarkgrey = new Zend_Pdf_Color_Html('#AAAAAA');
        $colorWhite = new Zend_Pdf_Color_Html('white');
        $image = Zend_Pdf_Image::imageWithPath('images/tnlogo.jpg');


        $pdfPage = $pdfdoc->newPage(Zend_Pdf_Page::SIZE_A4);


        //logo
        $pdfPage->drawImage($image, 390, 800, 550, 830);

        //title
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
        $pdfPage->setFont($font, 18);
        $string = $user['fname'] . " " . $user['lname'];
        $pdfPage->drawText($string, 72, 780, 'UTF-8');
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
        $pdfPage->setFont($font, 18);
        $pdfPage->setFillColor($colorDarkgrey);
        $string = ' || Verantwortlich';
        $pdfPage->drawText($string, 385, 780, 'UTF-8');
        $pdfPage->drawLine(60, 760, 540, 760);

        //print time
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $pdfPage->setFont($font, 7);
        $pdfPage->drawText('erzeugt: ' . Date("d.m.Y H:i:s") . ' Uhr', 430, 752, 'UTF-8');


        //write User's Basic data
        //ID
        $pdfPage->setFillColor($colorBlack);
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText('ID:', 72, 750, 'UTF-8');
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText($user['ID'], 120, 750, 'UTF-8');
        //mail adress
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText('eMail:', 72, 740, 'UTF-8');
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText($user['email'], 120, 740, 'UTF-8');
        //mobile phone
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText('Handy-Nr:', 72, 730, 'UTF-8');
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText($user['mphone'], 120, 730, 'UTF-8');
        //phone
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText('Telefon:', 72, 720, 'UTF-8');
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText($user['phone'], 120, 720, 'UTF-8');
        //steet / H.No.
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText('Straße:', 72, 710, 'UTF-8');
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText($user['street'], 120, 710, 'UTF-8');
        //city
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText('Ort:', 72, 700, 'UTF-8');
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText($user['city'], 120, 700, 'UTF-8');
        //KV
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText('KV:', 72, 690, 'UTF-8');
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);

        $pdfPage->setFont($font, 8);
        $pdfPage->drawText($user['kv_name'], 120, 690, 'UTF-8');
        //birthday
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $pdfPage->setFont($font, 8);
        $pdfPage->drawText('Geburstag:', 72, 680, 'UTF-8');
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $pdfPage->setFont($font, 8);
        $string = substr($user['birthday'], 8, 2) . "." . substr($user['birthday'], 5, 2) . "." . substr($user['birthday'], 0, 4);
        $pdfPage->drawText($string, 120, 680, 'UTF-8');

        //horitontal line
        $pdfPage->drawLine(60, 670, 540, 670);

        //build up events/shifts
        $y = 630;


        //EVENTS/SHIFTS heading
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $pdfPage->setFont($font, 12);
        $pdfPage->drawText('Du bist verantwortlich für:', 72, 650, 'UTF-8');

        $eventcounter = 0;
        $seite = 1;

        foreach ($events as $event) {

            $eventcounter++;

            //if more than 9, create new page and header...
            if ($y < 150) {
                $pdfdoc->pages[] = $pdfPage;
                $pdfPage = $pdfdoc->newPage(Zend_Pdf_Page::SIZE_A4);

                //logo
                $pdfPage->drawImage($image, 390, 800, 550, 830);

                //title
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
                $pdfPage->setFont($font, 18);
                $string = $user['fname'] . " " . $user['lname'];
                $pdfPage->drawText($string, 72, 780, 'UTF-8');
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
                $pdfPage->setFont($font, 18);
                $pdfPage->setFillColor($colorDarkgrey);
                $string = ' || JOBÜBERSICHT';
                $pdfPage->drawText($string, 385, 780, 'UTF-8');
                $pdfPage->drawLine(60, 770, 540, 770);
                $pdfPage->setFillColor($colorBlack);
                $seite = $seite + 1;
                $string = 'SEITE ' . $seite;
                $pdfPage->drawText($string, 72, 750, 'UTF-8');

                //print time
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $pdfPage->setFont($font, 7);
                $pdfPage->drawText('erzeugt: ' . Date("d.m.Y H:i:s") . ' Uhr', 430, 762, 'UTF-8');

                $y = 700;
            }

            //time box
            //$pdfPage->setLineColor($colorGrey);
            //$pdfPage->setFillColor($colorGrey);
            //$pdfPage->drawRectangle(72,$y-60,142,$y,Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);
            //time text
            //$pdfPage->setFillColor($colorBlack);
            //$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
            //$pdfPage->setFont($font, 12);
            //info box
            $pdfPage->setLineColor($colorGrey);
            $pdfPage->setFillColor($colorGrey);
            $pdfPage->drawRectangle(70, $y - 5, 520, $y, Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);


            //info text
            //eventID and shiftID
            //event and shift
            $pdfPage->setFillColor($colorBlack);
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
            $pdfPage->setFont($font, 14);
            $pdfPage->drawText($event['name'], 70, $y - 17, 'UTF-8');
            //location
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
            $pdfPage->setFont($font, 11);
            $pdfPage->drawText('Location: ', 70, $y - 30, 'UTF-8');
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $pdfPage->setFont($font, 11);
            $pdfPage->drawText($event['location_name'], 160, $y - 30, 'UTF-8');

            //@todo: schichten
            $shifts = $model->getShiftsByEventid($event['ID']);
            $y1 = 0;
            foreach ($shifts as $shift) {
                $start = explode(" ", $shift['start']);
                $ende = explode(" ", $shift['end']);
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
                $pdfPage->setFont($font, 11);
                $pdfPage->drawText($start[1] . " Uhr- " . $ende[1] . " Uhr", 70, $y - $y1 - 40, 'UTF-8');

                $usr_shifts = $model->getUsersOfShift($shift['ID']);
                foreach ($usr_shifts as $usr_shift) {
                    $y1 = $y1 + 12;
                    $pdfPage->drawText($usr_shift['user_fname'] . " " . $usr_shift['user_lname'], 80, $y - $y1 - 40, 'UTF-8');
                    if ($usr_shift['user_mphone'] != "") {
                        $pdfPage->drawText($usr_shift['user_mphone'], 200, $y - $y1 - 40, 'UTF-8');
                    }
                    else
                        $pdfPage->drawText($usr_shift['user_phone'], 200, $y - $y1 - 40, 'UTF-8');
                    $pdfPage->drawText($usr_shift['user_mail'], 330, $y - $y1 - 40, 'UTF-8');
                }

                //bei links tabelle
                $y1 = $y1 + 11;
            }


            $y = $y - $y1 - 40;
        }
        $pdfdoc->pages[] = $pdfPage;
        //set Header and release document
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="downloaded.pdf"');
        echo $pdfdoc->render();
    }

    //generate shift Tabelle html
    public function generateshifttableAction() {

        //get data
        $data = $this->getRequest()->getParams();
        $model = $this->_getModel();


        //Locations
        $locations = $model->getAllLocations();
        //Events
        $events = $model->getAllEvents();

        echo "<table style=\"border:solid 1px #000000; cellpadding:0px; cellspacing:0px; empty-cells:show; border-collapse:collapse;\">";
        echo "<tr>";
        echo "<td bgcolor=\"#000000\"><font color=\"#FFFFFF\">Raum</font></td>";
        echo "<td bgcolor=\"#000000\"><font color=\"#FFFFFF\">Programmpunkt</font></td>";
        echo "<td bgcolor=\"#000000\"><font color=\"#FFFFFF\">Ansprechpartner</font></td>";
        echo "<td bgcolor=\"#000000\"><font color=\"#FFFFFF\">Benötigte Mitarbeiter</font></td>";
        echo "<td bgcolor=\"#000000\"><font color=\"#FFFFFF\">gebuchte Mitarbeiter</font></td>";
        echo "<td bgcolor=\"#000000\"><font color=\"#FFFFFF\">Differenz Mitarbeiter</font></td>";
        echo "<td bgcolor=\"#000000\"><font color=\"#FFFFFF\">Schichten</font></td>";
        $starttime = 20;
        $endtime = 21;
        for ($i = 0; $i < 15; $i++) {
            echo "<td bgcolor=\"#000000\"  style=\"border:solid 1px #FFFFFF;border-left:solid 2px #FFFFFF; \" colspan=\"4\"><font color=\"#FFFFFF\" size=\"-3\">" . $starttime . ":00- " . $endtime . ":00</font></td>";
            $starttime = $endtime;
            $endtime = $endtime + 1;
            if ($endtime >= 24)
                $endtime = 0;
        }

        echo "</tr>";
        foreach ($locations as $location) {
            echo "<tr>";
            echo "<td bgcolor=\"" . $location ['color'] . "\" colspan=\"67\">" . $location ['name'] . "</td>";
            echo "</tr>";


            foreach ($events as $event) {

                if ($event['location_name'] == $location['name']) {


                    $shifts = $model->getShiftsByEventid($event['ID']);
                    $shift_event = "";
                    $reqstaff = 0;
                    $staff = 0;
                    $start_h = 0;
                    $start_m = 0;
                    $end_h = 0;
                    $end_m = 0;

                    for ($i = 0; $i < 60; $i++)
                        $shift_mark[$i] = 0;
                    foreach ($shifts as $shift) {

                        $start_h = 0;
                        $start_m = 0;
                        $end_h = 0;
                        $end_m = 0;
                        $anfang = 0;
                        $ende = 0;
                        $shift_event .= $shift['name'] . "::" . $shift['start'] . ":" . $shift['end'] . "   ";
                        //benötigte MA
                        $reqstaff = $reqstaff + $shift['reqstaff'];
                        $user_shifts = $model->getUsersOfShift($shift['ID']);
                        //vorhandene MA
                        $staff = $staff + count($user_shifts);
                        //Schichten 2009-06-26 20:30:00
                        $shiftstart_tmp = explode(' ', $shift['start']);
                        $shiftstart = explode(':', $shiftstart_tmp['1']);

                        if ($shiftstart[0] > 18)
                            $anfang = ($shiftstart[0] - 20) * 4 + ($shiftstart[1] - $shiftstart[1] % 15) / 15;
                        else
                            $anfang = 16 + ($shiftstart[0]) * 4 + ($shiftstart[1] - $shiftstart[1] % 15) / 15;
                        $shiftend_tmp = explode(' ', $shift['end']);
                        $shiftend = explode(':', $shiftend_tmp['1']);
                        if ($shiftend[0] > 18)
                            $ende = ($shiftend[0] - 20) * 4 + ($shiftend[1] - $shiftend[1] % 15) / 15;
                        else
                            $ende = 16 + ($shiftend[0]) * 4 + ($shiftend[1] - $shiftend[1] % 15) / 15;
                        for ($i = $anfang; $i < $ende; $i++)
                            $shift_mark[$i] = 1;
                    }
                    $diff_stuff = $reqstaff - $staff;
                    echo "<tr>";
                    echo "<td bgcolor=\"" . $location ['color'] . "\" width=\"20px\"></td>";
                    echo "<td>" . $event['name'] . "</td>";
                    echo "<td>" . $event['user_lname'] . "</td>";
                    echo "<td>" . $reqstaff . "</td>";
                    echo "<td>" . $staff . "</td>";
                    echo "<td ";
                    if ($diff_stuff > 0)
                        echo "bgcolor=\"#FF0000\"";
                    echo ">" . $diff_stuff . "</td>";
                    echo "<td>" . $shift_event . "</td>";
                    for ($i = 0; $i < 60; $i++) {

                        echo "<td style=\"border:solid 1px #000000;";
                        if (($i % 4) == 0)
                            echo " border-left:solid 2px #000000;";
                        if ($shift_mark[$i] == 1)
                            echo " background:" . $location ['color'] . ";";
                        echo "\" width=\"25px\"></td>";
                    }
                    echo "</tr>";
                }
            }
        }
        echo "</table>";
    }

    //Exportfunktion für die Handout
    public function exportforhandoutAction() {
        $data = $this->getRequest()->getParams();
        $model = $this->_getModel();

        $shifts = $model->getHandoutData();

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="daten.csv"');

        //create data
        foreach ($shifts as $shift) {
            echo "\"" . utf8_decode($shift["start"]) . "\";";
            echo "\"" . utf8_decode($shift["end"]) . "\";";
            echo "\"" . utf8_decode($shift["name"]) . "\";";
            echo "\"" . utf8_decode($shift["location"]) . "\";";

            echo "\r\n";
        }
    }

    //export shift Tabelle excel ueber PHP EXCEL
    public function exportshifttableAction() {
        //locale Timoutzeit auf 60sek erhoeht
        //get data
        $data = $this->getRequest()->getParams();
        $model = $this->_getModel();

        //Locations
        $locations = $model->getAllLocations();
        //Events
        $events = $model->getAllEvents();

        //Variablen
        $time = time(); //Zur Zeitberechnung
        $zeit_begin = 20; //Um wie viel Uhr sollder Schichtplan beginnen
        $zeit_stunden = 15; //Wie viele Stunden
        $akt_zeile = 2;
        $event_zeile = 0;
        $location_zeile = 0;
        $begin_zelle_schichten = 12;
        $masterplan = array();
        //$masterplan[zeile][spalten]
        //	0     1	  2      3          4    5                6          7               8       9          10    11           12-
        //	id, raum, farbe, eventname, kat, ansprechpartner, ma bedarf, ma bedarf ist, MA diff, anmerkung, name, anmerkungen, schichtuebersicht
        //Daten holen
        foreach ($locations as $location) {

            //schwarze Zeile
            //for($i=0;$i<$begin_zelle_schichten+$zeit_stunden*4;$i++) $masterplan[$akt_zeile][$i]=0;
            //$masterplan[$akt_zeile][1]="black_row";
            //$akt_zeile=$akt_zeile+1;
            for ($i = 0; $i < $begin_zelle_schichten + $zeit_stunden * 4; $i++)
                $masterplan[$akt_zeile][$i] = 0;

            $location_zeile = $akt_zeile;

            $masterplan[$akt_zeile][0] = $location ['ID'];
            $masterplan[$akt_zeile][1] = $location ['name'];
            $color = preg_split('//', $location['color'], -1, PREG_SPLIT_NO_EMPTY);
            $masterplan[$akt_zeile][2] = "FF" . $color[1] . "" . $color[2] . "" . $color[3] . "" . $color[4] . "" . $color[5] . "" . $color[6];


            foreach ($events as $event) {

                if ($event['location_name'] == $location['name']) {

                    $shifts = $model->getShiftsByEventid($event['ID']);

                    $akt_zeile = $akt_zeile + 1;
                    for ($i = 0; $i < $begin_zelle_schichten + $zeit_stunden * 4; $i++)
                        $masterplan[$akt_zeile][$i] = 0;
                    $event_zeile = $akt_zeile;

                    $masterplan[$akt_zeile][0] = $masterplan[$location_zeile][0];
                    $masterplan[$akt_zeile][1] = $masterplan[$location_zeile][1];
                    $masterplan[$akt_zeile][2] = $masterplan[$location_zeile][2];

                    $masterplan[$akt_zeile][3] = $event['name']; //Eventname
                    $masterplan[$akt_zeile][9] = $event['note']; //Eventnotiz
                    $masterplan[$akt_zeile][5] = $event['user_fname'] . " " . $event['user_lname']; //Event verantwortlicher

                    $reqstaff = 0;
                    $staff = 0;

                    foreach ($shifts as $shift) {

                        for ($i = 0; $i < $zeit_stunden * 4; $i++)
                            $shift_mark[$i] = 0; //Schichtmarkierung leeren

                        $start_h = 0;
                        $start_m = 0;
                        $end_h = 0;
                        $end_m = 0;
                        $anfang = 0;
                        $ende = 0;


                        $akt_zeile = $akt_zeile + 1;
                        for ($i = 0; $i < $begin_zelle_schichten + $zeit_stunden * 4; $i++)
                            $masterplan[$akt_zeile][$i] = 0;

                        $masterplan[$akt_zeile][0] = $masterplan[$location_zeile][0];
                        $masterplan[$akt_zeile][1] = $masterplan[$location_zeile][1];
                        $masterplan[$akt_zeile][2] = $masterplan[$location_zeile][2];
                        $masterplan[$akt_zeile][3] = $masterplan[$event_zeile][3];
                        $masterplan[$akt_zeile][5] = $masterplan[$event_zeile][5];

                        $masterplan[$akt_zeile][10] = $shift['name'];

                        //benötigte MA
                        $regstaff_shift = $shift['reqstaff'];
                        $reqstaff = $reqstaff + $regstaff_shift;
                        $masterplan[$akt_zeile][6] = $regstaff_shift;

                        //vorhandene MA
                        $user_shifts = $model->getUsersOfShift($shift['ID']);
                        $user_shifts = count($user_shifts);
                        $staff = $staff + $user_shifts;
                        $masterplan[$akt_zeile][7] = $user_shifts;

                        //Differenz MA
                        $staff_diff = $regstaff_shift - $user_shifts;
                        $masterplan[$akt_zeile][8] = $staff_diff;

                        //Schichtanmerkung
                        $masterplan[$akt_zeile][9] = $shift['note'];

                        //Schichtzeiten 2009-06-26 20:30:00
                        $shiftstart_tmp = explode(' ', $shift['start']);
                        $shiftstart = explode(':', $shiftstart_tmp['1']);
                        $shiftend_tmp = explode(' ', $shift['end']);
                        $shiftend = explode(':', $shiftend_tmp['1']);
                        //Schichtanfang
                        if ($shiftstart[0] >= $zeit_begin - 4)
                            $anfang = ($shiftstart[0] - $zeit_begin) * 4 + ($shiftstart[1] - $shiftstart[1] % 15) / 15;
                        else
                            $anfang = (24 - $zeit_begin) * 4 + ($shiftstart[0]) * 4 + ($shiftstart[1] - $shiftstart[1] % 15) / 15;

                        //Schichtende
                        if ($shiftend[0] >= $zeit_begin)
                            $ende = ($shiftend[0] - $zeit_begin) * 4 + ($shiftend[1] - $shiftend[1] % 15) / 15;
                        else
                            $ende = (24 - $zeit_begin) * 4 + ($shiftend[0]) * 4 + ($shiftend[1] - $shiftend[1] % 15) / 15;

                        for ($i = 0; $i < $zeit_stunden * 4; $i++)
                            if ($i >= $anfang && $i < $ende) {
                                $masterplan[$akt_zeile][$i + $begin_zelle_schichten] = 1;
                                $masterplan[$event_zeile][$i + $begin_zelle_schichten] = 1;
                            } else {
                                $masterplan[$akt_zeile][$i + $begin_zelle_schichten] = 0;
                                //$masterplan[$event_zeile][$i]=$masterplan[$event_zeile][$i];
                            }
                    }
                    //foreach ( $shifts as $shift )

                    $masterplan[$event_zeile][6] = $reqstaff; //Gesamt MA benötigt
                    $masterplan[$event_zeile][7] = $staff; //gesamt MA
                    $masterplan[$event_zeile][8] = $reqstaff - $staff; //gesamt MA diff
                }
                //if($event['location_name']==$location['name'])
            }
            //foreach ( $events as $event )

            $akt_zeile = $akt_zeile + 1;
        }
        //foreach ( $locations as $location )

        echo "Zeit für Daten:" . (time() - $time) . "<br>";

        //EXCEL Daten
        require("../Excel/PHPExcel.php");

        //$workbook = new PHPExcel();
        $workbook = PHPExcel_IOFactory::load('excel/templates/masterplan.xls');


        // Das worksheet anwaehlen und Namen aendern
        $worksheet = $workbook->setActiveSheetIndex(0);
        $worksheet->setTitle("Raum-Aktionen-Schichten 2010");

        $col_names = Array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ');

        $akt_zeile = 1;


        //Formatierung
        $format_header = array(
            'font' => array(
                'bold' => true,
                'color' => array('argb' => 'FFFFFFFF',),),
            'alignment' => array('wrap' => true,),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('argb' => 'FF000000',),),
        );
        $format_black_row = array(
            'font' => array(
                'color' => array('argb' => 'FFFFFFFF',),
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('argb' => 'FF000000',),
            ),
        );

        $location = "";
        $location_color = "";
        $location_zeile = "1";
        $event = "";
        $event_zeile = "2";

        foreach ($masterplan as $master) {
            $akt_zeile = $akt_zeile + 1;

            if ($master[0] != $location) {

                if ($akt_zeile > 1) {
                    if ($akt_zeile > 2 && $event_zeile < ($akt_zeile - 1)) {
                        //für das letzte Event der vorherigen Schicht
                        //Zellen verbinden senkrecht
                        $zellen = $col_names[2] . "" . $event_zeile . ":" . $col_names[2] . "" . ($akt_zeile - 1);
                        $worksheet->mergeCells($zellen); //Zellen verbinden
                        //Zellen verbinden senkrecht
                        $zellen = $col_names[3] . "" . $event_zeile . ":" . $col_names[3] . "" . ($akt_zeile - 1);
                        $worksheet->mergeCells($zellen); //Zellen verbinden
                        //Zellen verbinden senkrecht
                        $zellen = $col_names[4] . "" . $event_zeile . ":" . $col_names[4] . "" . ($akt_zeile - 1);
                        $worksheet->mergeCells($zellen); //Zellen verbinden
                        //Zellen verbinden senkrecht
                        $zellen = $col_names[5] . "" . $event_zeile . ":" . $col_names[5] . "" . ($akt_zeile - 1);
                        $worksheet->mergeCells($zellen); //Zellen verbinden
                        //Zellen verbinden senkrecht
                        $zellen = $col_names[6] . "" . $event_zeile . ":" . $col_names[6] . "" . ($akt_zeile - 1);
                        $worksheet->mergeCells($zellen); //Zellen verbinden

                        $zellen = "C" . $event_zeile . ":" . $col_names[$begin_zelle_schichten + $zeit_stunden * 4] . "" . $event_zeile;
                        $worksheet->getStyle($zellen)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    }

                    if ($akt_zeile > 2) {
                        //Schwarze Zeile
                        for ($i = 0; $i < $begin_zelle_schichten + 1; $i++) {
                            $zellen = $col_names[$i] . "" . $akt_zeile;
                            //$worksheet->getStyle($zellen)->applyFromArray($format_black_row);
                            $worksheet->getStyle($zellen)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                            $worksheet->getStyle($zellen)->getFill()->getStartColor()->setARGB('FF000000');
                            $worksheet->getStyle($zellen)->getFont()->getColor()->setARGB('FFFFFFFF');
                            $worksheet->setCellValue($zellen, "=" . $col_names[$i] . "1");
                        }
                        for ($i = 0; $i < $zeit_stunden * 4; $i++) {
                            if (($i % 4) == 0) {
                                $zellen = $col_names[$begin_zelle_schichten + $i + 1] . "" . $akt_zeile . ":" . $col_names[$begin_zelle_schichten + $i + 3 + 1] . "" . $akt_zeile;
                                $worksheet->mergeCells($zellen);
                                $worksheet->getStyle($zellen)->applyFromArray($format_black_row);
                                $zellen = $col_names[$begin_zelle_schichten + $i + 1] . "" . $akt_zeile;
                                $worksheet->setCellValue($zellen, "=" . $col_names[$begin_zelle_schichten + $i + 1] . "1");
                            }
                        }
                        //$zellen = $col_names[$i+1]."".$akt_zeile;
                        //$zellen = "B".$akt_zeile.":".$col_names[$begin_zelle_schichten+($zeit_stunden)*4-1+1]."".$akt_zeile;
                        //$worksheet->mergeCells($zellen);
                        //$worksheet->getStyle($zellen)->applyFromArray($format_black_row);
                    } else {
                        $zellen = "B" . $akt_zeile . ":" . $col_names[$begin_zelle_schichten + ($zeit_stunden) * 4 - 1 + 1] . "" . $akt_zeile;
                        $worksheet->mergeCells($zellen);
                        $worksheet->getStyle($zellen)->applyFromArray($format_black_row);
                    }


                    //Zellen verbinden senkrecht  farbigeLocation
                    $zellen = $col_names[1] . "" . $location_zeile . ":" . $col_names[1] . "" . ($akt_zeile - 1);
                    $worksheet->mergeCells($zellen); //Zellen verbinden

                    $zellen = $col_names[1] . "" . $location_zeile;
                    $worksheet->getStyle($zellen)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $worksheet->getStyle($zellen)->getFill()->getStartColor()->setARGB($location_color);


                    $akt_zeile = $akt_zeile + 1; //Neue Location

                    $location = $master[0];
                    $location_zeile = $akt_zeile;
                    $location_color = $master[2];
                    $event_zeile = $location_zeile + 1;
                    $event = "";

                    $zellen = $col_names[1] . "" . $akt_zeile;
                    $worksheet->setCellValue($zellen, $master[1]);

                    //Zellen verbinden waagerecht
                    $zellen = "C" . $location_zeile . ":" . $col_names[$begin_zelle_schichten - 1 + 1] . "" . $location_zeile;
                    $worksheet->mergeCells($zellen); //Zellen verbinden
                    $worksheet->getStyle($zellen)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $worksheet->getStyle($zellen)->getFill()->getStartColor()->setARGB($location_color);
                }
            } else {

                if ($master[3] != $event) {
                    //Eventzeile
                    if ($event_zeile != $akt_zeile) {
                        //Zellen verbinden senkrecht
                        $zellen = $col_names[2] . "" . $event_zeile . ":" . $col_names[2] . "" . ($akt_zeile - 1);
                        $worksheet->mergeCells($zellen); //Zellen verbinden
                        //Zellen verbinden senkrecht
                        $zellen = $col_names[3] . "" . $event_zeile . ":" . $col_names[3] . "" . ($akt_zeile - 1);
                        $worksheet->mergeCells($zellen); //Zellen verbinden
                        //Zellen verbinden senkrecht
                        $zellen = $col_names[4] . "" . $event_zeile . ":" . $col_names[4] . "" . ($akt_zeile - 1);
                        $worksheet->mergeCells($zellen); //Zellen verbinden
                        //Zellen verbinden senkrecht
                        $zellen = $col_names[5] . "" . $event_zeile . ":" . $col_names[5] . "" . ($akt_zeile - 1);
                        $worksheet->mergeCells($zellen); //Zellen verbinden
                        //Zellen verbinden senkrecht
                        $zellen = $col_names[6] . "" . $event_zeile . ":" . $col_names[6] . "" . ($akt_zeile - 1);
                        $worksheet->mergeCells($zellen); //Zellen verbinden

                        $zellen = "C" . $event_zeile . ":" . $col_names[$begin_zelle_schichten + $zeit_stunden * 4] . "" . $event_zeile;
                        $worksheet->getStyle($zellen)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    }
                    $event_zeile = $akt_zeile;
                    $event = $master[3];

                    $zellen = $col_names[2] . "" . $akt_zeile;
                    $worksheet->setCellValue($zellen, $master[3]);
                    if ($master[4] != "0") {
                        $zellen = $col_names[3] . "" . $akt_zeile;
                        $worksheet->setCellValue($zellen, $master[4]);
                    }
                    if ($master[5] != "0") {
                        $zellen = $col_names[4] . "" . $akt_zeile;
                        $worksheet->setCellValue($zellen, $master[5]);
                    }
                    $zellen = $col_names[5] . "" . $akt_zeile;
                    $worksheet->setCellValue($zellen, $master[6]);
                    $zellen = $col_names[6] . "" . $akt_zeile;
                    $worksheet->setCellValue($zellen, $master[7]);
                    $ma_diff = $master[6] - $master[7];
                    $zellen = $col_names[9] . "" . $akt_zeile;
                    $worksheet->setCellValue($zellen, $ma_diff);
                    if ($ma_diff > 0)
                        $worksheet->getStyle($zellen)->getFont()->getColor()->setARGB('FFFF0000');
                    $zellen = $col_names[10] . "" . $akt_zeile;
                    $worksheet->setCellValue($zellen, $master[9]);
                }
                else {
                    //Einzelne Schichten
                    $zellen = $col_names[5] . "" . $akt_zeile;
                    $worksheet->setCellValue($zellen, $master[6]);
                    $zellen = $col_names[6] . "" . $akt_zeile;
                    $worksheet->setCellValue($zellen, $master[7]);
                    $ma_diff = $master[6] - $master[7];
                    $zellen = $col_names[9] . "" . $akt_zeile;
                    $worksheet->setCellValue($zellen, $ma_diff);
                    $zellen = $col_names[12] . "" . $akt_zeile;
                    $worksheet->setCellValue($zellen, $master[9]);
                    $zellen = $col_names[11] . "" . $akt_zeile;
                    $worksheet->setCellValue($zellen, $master[10]);
                    $worksheet->getRowDimension($akt_zeile)->setVisible(false);
                }


                //Schichtplan
                $schicht_tmp = 0;
                for ($i = $begin_zelle_schichten; $i < $begin_zelle_schichten + $zeit_stunden * 4; $i++) {
                    $zellen = $col_names[$i + 1] . "" . $akt_zeile;

                    if ($master[$i] != "0") {

                        $worksheet->getStyle($zellen)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                        $worksheet->getStyle($zellen)->getFill()->getStartColor()->setARGB($location_color);
                        //$worksheet->setCellValue($zellen, $master[$i]);print

                        if ($i % 4 == 0) {
                            //$zellen = $col_names[$begin_zelle_schichten+$i+1]."1:".$col_names[$begin_zelle_schichten+$i+1]."".$akt_zeile;
                            //$zellen = "N1:N".$akt_zeile;
                            $worksheet->getStyle($zellen)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                        }
                    } else {
                        if ($i % 4 == 0) {
                            $worksheet->getStyle($zellen)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                        }
                    }

                    $schicht_tmp = $master[$i];
                }
            }
        }

        //Zellen verbinden senkrecht  farbigeLocation für das letztes event
        $location_color = $master[2];
        $zellen = $col_names[1] . "" . $location_zeile . ":" . $col_names[1] . "" . ($akt_zeile);
        $worksheet->mergeCells($zellen); //Zellen verbinden
        $worksheet->getStyle($zellen)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $worksheet->getStyle($zellen)->getFill()->getStartColor()->setARGB($location_color);

        //druckbereich festlegen
        $zellen = "B1:" . $col_names[$begin_zelle_schichten + $zeit_stunden * 4] . "" . $akt_zeile;
        //echo $zellen;
        $worksheet->getPageSetup()->setPrintArea($zellen);
        //und das ganze speichern
        $writer = PHPExcel_IOFactory::createWriter($workbook, "Excel5");
        //$writer=PHPExcel_IOFactory::createWriter($workbook,"Excel2007");
        $writer->save("excel/test.xls");

        $time = time() - $time;
        echo "Zeit komplett:" . ($time) . "<br>";
        echo "<a href=\"../excel/test.xls\">download XLS</a>";
    }

    //get db model
    protected function _getModel() {
        if (null === $this->_model) {
            require_once APPLICATION_PATH . '/models/Model_Eventshift.php';
            $this->_model = new Model_Eventshift ();
        }
        return $this->_model;
    }

}

