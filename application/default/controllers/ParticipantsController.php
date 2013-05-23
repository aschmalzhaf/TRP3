<?php

/**
 * 
 * 
 * @author
 * @version 
 */
require_once 'Zend/Controller/Action.php';

class ParticipantsController extends Zend_Controller_Action {

    //vars
    protected $_model;

    public function init() {
        
    }

    public function indexAction() {
        
    }

    public function viewlist1Action() {

        //Stehen in der viewlistAction() nochmals		
        $achtzehn_jahr = 1994;
        $achtzehn_monat = 6;
        $achtzehn_tag = 22;

        //set title
        $this->view->title = "Teilnehmer anzeigen";

        if ($this->getRequest()->isPost()) {

            //get request data
            $preparams = $this->getRequest()->getParams();
            $params = Array();

            //change charset
            while (($key = key($preparams)) != null) {
                $params [$key] = current($preparams);
                next($preparams);
            }

            //Build up data arrays
            $cols = Array();
            $where = Array();
            $whereVals = Array();
            $cols_names = Array();
            $i = 0;
            $where_i = 0;

            //add ID as mandatory
            $cols [] = "ID";
            $cols_names ['ID'] = "ID";

            //$cols [] = "geb";
            //$cols_names ['geb'] = "Geburtstag";
            //handle the rest
            while (current($params) !== false) {
                if (substr(key($params), 0, 3) == "co_") {

                    if (current($params) != "") {
                        $cols [] = substr(key($params), 3);
                        $cols_names [substr(key($params), 3)] = current($params);
                    }
                } elseif (substr(key($params), 0, 3) == "wh_") {

                    //data for refill of selection-fields on screen

                    $whereVals [substr(key($params), 3)] = current($params);

                    if (current($params) != "") {

                        $where [$where_i++] [substr(key($params), 3)] = str_replace('*', '%', current($params));
                    }
                }

                next($params);
                $i++;
            }

            $akt_zeile = 0;

            //SELECT data from Model
            $model = $this->_getModel();

            $res = $model->getAnmeldeliste($where);

            $tns = $res['data'];
            //decide if excel or html should be used...
            //EXCEL
            if (isset($params ['excel'])) {

                //$this->_helper->layout->disableLayout();
                //$this->_helper->viewRenderer->setNoRender(true);

                $col_letters = Array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ');

                //EXCEL Daten 
                require("../Excel/PHPExcel.php");

                //$workbook = new PHPExcel();
                $workbook = PHPExcel_IOFactory::load('excel/templates/anmeldungen.xls');
                $worksheet = $workbook->setActiveSheetIndex(0);
                //$worksheet->setTitle("Anmeldungen");
                //Variablen
                $begin_zeile = 4;
                $akt_zeile = $begin_zeile;
                $akt_spalte = 0;
                $sum = array(); //Spaltenindex des Summe
                //Header einfügen
                foreach ($cols_names as $col) {
                    //if($col!= "geb"){
                    //	$zelle = $col_letters[$akt_spalte]."".$akt_zeile;
                    //	$worksheet->setCellValue($zelle, $col);
                    //	$akt_spalte=$akt_spalte+1;
                    //}
                }

                //Participants-content
                $akt_zeile = $akt_zeile + 1;
                foreach ($tns as $tn) {

                    $akt_spalte = 0;
                    foreach ($cols as $col) {
                        //if($col!= "geb"){
                        $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                        $worksheet->setCellValue($zelle, $tn [$col]);

                        if ($col == "Vorname") {

                            if ($tn ['geb'] != "") {
                                $geb = explode('.', $tn ['geb']);
                                if ($geb[2] < 1992) {
                                    
                                } else {
                                    if ($geb[2] == 1992 && $geb[1] < 6) {
                                        
                                    } else {
                                        if ($geb[2] == 1992 && $geb[1] == 6 && $geb[0] < 25) {
                                            
                                        } else {
                                            $worksheet->getStyle($zelle)->getFont()->setBold(true);
                                        }
                                    }
                                }
                            } else {
                                
                            }
                        }


                        $akt_spalte = $akt_spalte + 1;
                        //}
                    }
                    $akt_zeile = $akt_zeile + 1;
                }

                //Summen Zeile
                $akt_zeile = $akt_zeile + 3;
                $akt_spalte = 0;
                foreach ($cols as $col) {
                    if ($col != "ID" && $col != "Name" && $col != "Vorname" && $col != "Ort" && $col != "geb") {
                        $zellen = $col_letters[$akt_spalte] . "" . ($begin_zeile + 1) . ":" . $col_letters[$akt_spalte] . "" . ($akt_zeile - 1);
                        $formel = "=SUM(" . $zellen . ")";
                        $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                        $worksheet->setCellValue($zelle, $formel);
                        $sum[$col] = $akt_spalte;
                    }

                    if ($col != "geb")
                        $akt_spalte = $akt_spalte + 1; //Geburtstag ist die 2. Spalte, die aber nicht angezeigt wird.
                }

                //GesamtSummen Teennight und Sa auf So

                $tn_ges = "=" . $col_letters[$sum['Teennight']] . "" . $akt_zeile . "+" . $col_letters[$sum['Teennightplus']] . "" . $akt_zeile;
                $sa_so_ges = "=" . $col_letters[$sum['SaSo']] . "" . $akt_zeile;

                $akt_zeile = $akt_zeile + 2;

                $zelle = $col_letters[4] . "" . $akt_zeile;
                $worksheet->setCellValue($zelle, 'Teennight gesamt');

                $zelle = $col_letters[5] . "" . $akt_zeile;
                $worksheet->setCellValue($zelle, $tn_ges);

                $akt_zeile = $akt_zeile + 1;

                $zelle = $col_letters[4] . "" . $akt_zeile;
                $worksheet->setCellValue($zelle, 'Sa. auf So.');

                $zelle = $col_letters[5] . "" . $akt_zeile;
                $worksheet->setCellValue($zelle, $sa_so_ges);
                //ID Spalte unsichtbar machen
                $worksheet->getColumnDimension('A')->setVisible(false); //ID
                $worksheet->getColumnDimension('J')->setVisible(false); //Fr-So
                $worksheet->getColumnDimension('K')->setVisible(false); //Sa-So
                $worksheet->getColumnDimension('L')->setVisible(false); //Fr-Sa TG
                $worksheet->getColumnDimension('Q')->setVisible(false); //Sonntag Früstück
                $worksheet->getColumnDimension('R')->setVisible(false); //Sonntag TG
                //druckbereich festlegen
                $zellen = "B1:" . $col_letters[21] . "" . $akt_zeile;

                //Zeile auf jeder Seite wiederholen
                $worksheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(3, 4);

                //Infozeile oben
                $worksheet->setCellValue('B3', 'Fett gedruckte Vornamen = unter 18 Jahren  |  Unterschrift mit X = Unterschrift vorhanden  |  Voranmeldung mit x = günstiger Preis');



                //und das ganze speichern
                //$writer=PHPExcel_IOFactory::createWriter($workbook,"Excel5");
                $writer = PHPExcel_IOFactory::createWriter($workbook, "Excel2007");
                $writer->save("excel/download/anmeldungen.xlsx");

                //echo "<a href=\"../excel/download/anmeldungen.xls\">download Anmeldungen.xls</a>";
                echo "<a href=\"../public/excel/download/anmeldungen.xlsx\">download Anmeldungen.xlsx</a>";


                //CSV	
            } elseif (isset($params ['csv'])) {

                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender(true);


                header('Content-type: 	text/csv');
                header('Content-Disposition: attachment; filename="daten.csv"');


                //create headings
                $i = 0;
                foreach ($cols_names as $col) {
                    $i++;
                    echo "\"" . $col . "\";";
                }
                echo "\r\n";

                //create data
                $i = 1;
                foreach ($tns as $tn) {
                    $i++;
                    $j = 0;
                    foreach ($cols as $col) {
                        $j++;
                        $tempstring = str_replace("\n", "\\n", $tn[$col]);
                        $tempstring = str_replace("\r", "\\r", $tempstring);
                        $tempstring = str_replace("\"", "\"\"", $tempstring);
                        echo "\"" . $tempstring . "\";";
                    }
                    echo "\r\n";
                }



                //HTML
            } else {

                //get AddedParticipants-Table
                //table-header
                $html = "<table id=\"stafftab\" class=\"sortable\"><tr>";
                foreach ($cols_names as $col) {
                    //if($col!="geb"){
                    $html .= "<th>" . $col . "</th>";
                    //}
                }
                $html .= "<th> </th>";
                $html .= "</tr>";

                $sum = array();
                //Participants-content
                foreach ($tns as $tn) {

                    $html .= "<tr>";

                    foreach ($cols as $col) {
                        //if($col!="geb"){
                        if (!isset($sum[$col]))
                            $sum[$col] = 0;

                        switch ($col) {
                            case "Vorname" :

                                if ($tn ['geb'] != "") {
                                    $geb = explode('.', $tn ['geb']);
                                    if ($geb[2] < $achtzehn_jahr) {
                                        $html .= "<td>" . $tn [$col] . "</td>";
                                    } else {
                                        if ($geb[2] == $achtzehn_jahr && $geb[1] < $achtzehn_monat) {
                                            $html .= "<td>" . $tn [$col] . "</td>";
                                        } else {
                                            if ($geb[2] == $achtzehn_jahr && $geb[1] == $achtzehn_monat && $geb[0] < $achtzehn_tag) {
                                                $html .= "<td>" . $tn [$col] . "</td>";
                                            } else {
                                                $html .= "<td><b>" . $tn [$col] . "</b></td>";
                                            }
                                        }
                                    }
                                } else {
                                    $html .= "<td><b>" . $tn [$col] . "</b></td>";
                                }
                                break;

                            case "mail" :
                                $html .= "<td title=\"" . $tn ['mail'] . "\"><a href=\"mailto:" . $tn ['mail'] . "\">" . substr($tn ['mail'], 0, 14) . "</a></td>";
                                break;

                            case "intnote" :
                                $html .= "<td title=\"" . $tn ['intnote'] . "\">" . substr($tn ['intnote'], 0, 15) . "</td>";
                                break;

                            default :
                                $html .= "<td>" . $tn [$col] . "</td>";
                                $sum[$col] = $sum[$col] + $tn [$col];
                                break;
                        }
                        //} 
                    }

                    $html .= "<td><a href=\"/Participants/participantdatacheck/" . $tn ['ID'] . "\" target=\"_blank\"><img src=\"/images/bearb.gif\"\\></a></td>";
                    $html .= "</tr>";
                }
                //Summen hinzufügen
                $html .= "<tr>";
                foreach ($cols as $col) {
                    if ($col == "ID") {
                        $html .= "<td></td>";
                    } elseif ($col == "Name") {
                        $html .= "<td><b>Summe: </b></td>";
                    } elseif ($col == "Vorname") {
                        $html .= "<td></td>";
                    } elseif ($col == "Ort") {
                        $html .= "<td></td>";
                    } elseif ($col == "voranmeldung") {
                        $html .= "<td></td>";
                    } elseif ($col == "geb") {
                        $html .= "<td></td>";
                    } else {
                        $html .= "<td><b>" . $sum [$col] . "</b></td>";
                    }
                }
                $html .= "<td></td></tr>";

                //table footer
                $html .= "</table>";
                $this->view->table = $html;
            }

            //hand over more information to view
            $this->view->cols = $cols;
            $this->view->whereVals = $whereVals;
            $this->view->sql_count = $res ['count'];
            //$this->view->sql_string = $res ['sql'];
        }
    }

    public function viewlistAction() {
        //Stehen in der viewlistAction1() nochmals		
        $achtzehn_jahr = 1993;
        $achtzehn_monat = 6;
        $achtzehn_tag = 24;

        $anmeldeschluss = array('21', '06', '2011');

        //set title
        $this->view->title = "Teilnehmer anzeigen";

        if ($this->getRequest()->isPost()) {

            //get request data
            $preparams = $this->getRequest()->getParams();
            $params = Array();

            //change charset
            while (($key = key($preparams)) != null) {
                $params [$key] = current($preparams);
                next($preparams);
            }

            //Anmeldelise mit definierten Spalten
            if (isset($params ['anmeldeliste'])) {


                //SELECT data from Model
                $model = $this->_getModel();

                $col_letters = Array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ');

                //EXCEL Daten 
                require("../Excel/PHPExcel.php");

                //$workbook = new PHPExcel();
                $workbook = PHPExcel_IOFactory::load('excel/templates/anmeldeliste.xls');

                for ($excel_seite = 0; $excel_seite < 1; $excel_seite++) {
                    //Build up data arrays
                    $cols = Array("ID", "unterschrift", "anzahl", "datum", "vorname", "name", "ort", "geb", "art", "schlafen");
                    $cols_names = Array("ID" => "ID", "unterschrift" => "Unterschrift", "anzahl" => "Anzahl", "datum" => "Voranmeldung", "vorname" => "Vorname", "name" => "Nachname", "ort" => "Ort", "geb" => "Geburtsdatum", "art" => "art", "schlafen" => "schlafen");
                    $where = Array("0" => Array("art" => "teennight%"));
                    $whereVals = Array("art");
                    $i = 0;
                    $where_i = 0;

                    //Worksheet auswählen
                    $worksheet = $workbook->setActiveSheetIndex($excel_seite);
                    if ($excel_seite == "0") {
                        $worksheet->setTitle("Anmeldungen Name");
                        $sort = Array("name asc", "vorname asc", "ort asc");
                    } elseif ($excel_seite == "1") {
                        $worksheet->setTitle("Anmeldungen Ort");
                        $sort = Array("ort asc", "name asc", "vorname asc");
                    } elseif ($excel_seite == "2") {
                        $worksheet->setTitle("Gruppen");
                        $sort = Array("name asc", "vorname asc", "ort asc");
                        $where[1] = Array("anzahl" => "1");
                    }





                    //vars: $cols, $where, $sort, $range
                    $res = $model->getGenericParticipantsData($cols, $where, $sort, null);
                    $Participants = $res ['data'];






                    //Variablen
                    $begin_zeile = 1;
                    $akt_zeile = $begin_zeile;
                    $akt_spalte = 0;
                    $sum = array(); //Spaltenindex des Summe
                    //Header einfügen
                    foreach ($cols_names as $col) {
                        if ($col != "art" && $col != "schlafen") {
                            $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                            $worksheet->setCellValue($zelle, $col);
                            $akt_spalte = $akt_spalte + 1;
                        }
                    }
                    //Art hinzufügen
                    $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                    $worksheet->setCellValue($zelle, "Teennight");
                    $akt_spalte = $akt_spalte + 1;
                    $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                    $worksheet->setCellValue($zelle, "Teennight+");
                    $akt_spalte = $akt_spalte + 1;

                    //Übernachtung noch hinzufügen
                    $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                    $worksheet->setCellValue($zelle, "eigenes Zelt");
                    $akt_spalte = $akt_spalte + 1;
                    $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                    $worksheet->setCellValue($zelle, "SWD Zelt");
                    $akt_spalte = $akt_spalte + 1;
                    $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                    $worksheet->setCellValue($zelle, "Massenlager");
                    $akt_spalte = $akt_spalte + 1;
                    $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                    $worksheet->setCellValue($zelle, "Privat");
                    $akt_spalte = $akt_spalte + 1;
                    $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                    $worksheet->setCellValue($zelle, "Zimmer");
                    $akt_spalte = $akt_spalte + 1;
                    $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                    $worksheet->setCellValue($zelle, "Wohnmobil");
                    $akt_spalte = $akt_spalte + 1;
                    //Participants-content
                    $akt_zeile = $akt_zeile + 1;
                    foreach ($Participants as $Participant) {
                        //if( ($excel_seite == 0 || $excel_seite == 1 ) && $Participant ["anzahl"] > "1"){}
                        //else{
                        $akt_spalte = 0;
                        foreach ($cols as $col) {

                            if ($col == "art" || $col == "schlafen") {
                                
                            } elseif ($col == "datum") {  //Anmeldedatum
                                $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;


                                $tmp_dat = explode(',', $Participant['datum']); //Datum und Uhrzeit trennen
                                $anmeldedatum = explode('.', $tmp_dat[0]);

                                if ($anmeldedatum[2] >= $anmeldeschluss[2]) {

                                    if ($anmeldedatum[2] == $anmeldeschluss[2] && $anmeldedatum[1] >= $anmeldeschluss[1]) {

                                        if ($anmeldedatum[1] == $anmeldeschluss[1] && $anmeldedatum[0] > $anmeldeschluss[0]) {
                                            
                                        }else
                                            $worksheet->setCellValue($zelle, "x");
                                    }else
                                        $worksheet->setCellValue($zelle, "x");
                                }else
                                    $worksheet->setCellValue($zelle, "x");


                                $akt_spalte = $akt_spalte + 1;
                            }
                            elseif ($col == "unterschrift") {  //Unterschrift vorhanden in x umwandeln
                                if ($Participant [$col] == "1") {
                                    $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                                    $worksheet->setCellValue($zelle, "x");
                                }
                                $akt_spalte = $akt_spalte + 1;
                            } else {
                                $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                                $worksheet->setCellValue($zelle, $Participant [$col]);
                                $akt_spalte = $akt_spalte + 1;

                                if ($col == "name" || $col == "vorname") {
                                    //Wenn unter 18 Jahren, dann Fett gedruckter Namen
                                    if ($Participant ['geb'] != "") {
                                        $geb = explode('.', $Participant ['geb']);
                                        if ($geb[2] < $achtzehn_jahr) {
                                            
                                        } else {
                                            if ($geb[2] == $achtzehn_jahr && $geb[1] < $achtzehn_monat) {
                                                
                                            } else {
                                                if ($geb[2] == $achtzehn_jahr && $geb[1] == $achtzehn_monat && $geb[0] < $achtzehn_tag) {
                                                    
                                                } else {
                                                    $worksheet->getStyle($zelle)->getFont()->setBold(true);
                                                }
                                            }
                                        }
                                    } else {
                                        $worksheet->getStyle($zelle)->getFont()->setBold(true);
                                    }
                                }
                            }
                        }
                        //Art hinzufügen
                        if ($Participant ["art"] == "teennight") {
                            $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                            $worksheet->setCellValue($zelle, "x");
                            $akt_spalte = $akt_spalte + 1;
                            $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                            $worksheet->setCellValue($zelle, "");
                            $akt_spalte = $akt_spalte + 1;
                        } elseif ($Participant ["art"] == "teennightplus") {
                            $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                            $worksheet->setCellValue($zelle, "");
                            $akt_spalte = $akt_spalte + 1;
                            $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                            $worksheet->setCellValue($zelle, "x");
                            $akt_spalte = $akt_spalte + 1;
                        }
                        //Übernachtung noch hinzufügen
                        $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                        $worksheet->setCellValue($zelle, $Participant ["zelt"]);
                        $akt_spalte = $akt_spalte + 1;
                        $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                        $worksheet->setCellValue($zelle, $Participant ["gzelt"]);
                        $akt_spalte = $akt_spalte + 1;
                        $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                        $worksheet->setCellValue($zelle, $Participant ["masse"]);
                        $akt_spalte = $akt_spalte + 1;
                        $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                        $worksheet->setCellValue($zelle, $Participant ["privat"]);
                        $akt_spalte = $akt_spalte + 1;
                        $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                        $worksheet->setCellValue($zelle, $Participant ["zimmer"]);
                        $akt_spalte = $akt_spalte + 1;
                        $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                        $worksheet->setCellValue($zelle, $Participant ["wohnmobil"]);
                        $akt_spalte = $akt_spalte + 1;

                        //
                        $akt_zeile = $akt_zeile + 1;
                        //}
                    }
                }

                //ID Spalte unsichtbar machen
                //$worksheet->getColumnDimension('A')->setVisible(false);
                //und das ganze speichern
                //$writer=PHPExcel_IOFactory::createWriter($workbook,"Excel5");
                $writer = PHPExcel_IOFactory::createWriter($workbook, "Excel2007");
                $writer->save("excel/download/anmeldeliste.xlsx");

                //echo "<a href=\"../excel/anmeldungen_daten.xls\">download Anmeldungen.xls</a>";
                echo "Download <a href=\"../public/excel/download/anmeldeliste.xlsx\">anmeldeliste.xlsx</a>";
            }//Daten anzeigen, export mit Spalten auswahl
            else {



                //Build up data arrays
                $cols = Array();
                $where = Array();
                $whereVals = Array();
                $cols_names = Array();
                $i = 0;
                $where_i = 0;

                //add ID as mandatory
                $cols [] = "ID";
                $cols_names ['ID'] = "ID";

                //handle the rest
                while (current($params) !== false) {
                    if (substr(key($params), 0, 3) == "co_") {

                        if (current($params) != "") {
                            $cols [] = substr(key($params), 3);
                            $cols_names [substr(key($params), 3)] = current($params);
                        }
                    } elseif (substr(key($params), 0, 3) == "wh_") {

                        //data for refill of selection-fields on screen

                        $whereVals [substr(key($params), 3)] = current($params);

                        if (current($params) != "") {

                            $where [$where_i++] [substr(key($params), 3)] = str_replace('*', '%', current($params));
                        }
                    }

                    next($params);
                    $i++;
                }

                //SELECT data from Model
                $model = $this->_getModel();

                //vars: $cols, $where, $sort, $range
                $res = $model->getGenericParticipantsData($cols, $where, null);
                $Participants = $res ['data'];


                //decide if excel or html should be used...
                //EXCEL
                if (isset($params ['excel'])) {

                    //$this->_helper->layout->disableLayout();
                    //$this->_helper->viewRenderer->setNoRender(true);

                    $col_letters = Array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ');

                    //EXCEL Daten 
                    require("../Excel/PHPExcel.php");

                    //$workbook = new PHPExcel();
                    $workbook = PHPExcel_IOFactory::load('excel/templates/listen.xls');
                    $worksheet = $workbook->setActiveSheetIndex(0);
                    //$worksheet->setTitle("Anmeldungen");
                    //Variablen
                    $begin_zeile = 1;
                    $akt_zeile = $begin_zeile;
                    $akt_spalte = 0;
                    $sum = array(); //Spaltenindex des Summe
                    //Header einfügen
                    foreach ($cols_names as $col) {
                        $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                        $worksheet->setCellValue($zelle, $col);
                        $akt_spalte = $akt_spalte + 1;
                    }

                    //Participants-content
                    $akt_zeile = $akt_zeile + 1;
                    foreach ($Participants as $Participant) {

                        $akt_spalte = 0;
                        foreach ($cols as $col) {

                            $zelle = $col_letters[$akt_spalte] . "" . $akt_zeile;
                            $worksheet->setCellValue($zelle, $Participant [$col]);
                            $akt_spalte = $akt_spalte + 1;
                        }


                        //
                        $akt_zeile = $akt_zeile + 1;
                    }


                    //ID Spalte unsichtbar machen
                    //$worksheet->getColumnDimension('A')->setVisible(false);
                    //und das ganze speichern
                    //$writer=PHPExcel_IOFactory::createWriter($workbook,"Excel5");
                    $writer = PHPExcel_IOFactory::createWriter($workbook, "Excel2007");
                    $writer->save("excel/download/anmeldungen_daten.xlsx");

                    //echo "<a href=\"../excel/anmeldungen_daten.xls\">download Anmeldungen.xls</a>";
                    echo "Download <a href=\"../public/excel/download/anmeldungen_daten.xlsx\">Anmeldungen Daten.xlsx</a>";


                    //CSV	
                } elseif (isset($params ['csv'])) {

                    $this->_helper->layout->disableLayout();
                    $this->_helper->viewRenderer->setNoRender(true);


                    header('Content-type: 	text/csv');
                    header('Content-Disposition: attachment; filename="daten.csv"');


                    //create headings
                    $i = 0;
                    foreach ($cols_names as $col) {
                        $i++;
                        echo "\"" . $col . "\";";
                    }
                    echo "\r\n";

                    //create data
                    $i = 1;
                    foreach ($Participants as $Participant) {
                        $i++;
                        $j = 0;
                        foreach ($cols as $col) {
                            $j++;
                            $tempstring = str_replace("\n", "\\n", $Participant[$col]);
                            $tempstring = str_replace("\r", "\\r", $tempstring);
                            $tempstring = str_replace("\"", "\"\"", $tempstring);
                            echo "\"" . $tempstring . "\";";
                        }
                        echo "\r\n";
                    }



                    //HTML
                } else {

                    //get AddedParticipants-Table
                    //table-header
                    $html = "<table id=\"stafftab\" class=\"sortable\"><tr>";
                    foreach ($cols_names as $col) {
                        $html .= "<th>" . $col . "</th>";
                    }
                    $html .= "<th> </th>";
                    $html .= "</tr>";

                    //Participants-content
                    foreach ($Participants as $Participant) {

                        $html .= "<tr>";

                        foreach ($cols as $col) {

                            switch ($col) {
                                case "vorname" :

                                    if ($Participant ['geb'] != "") {
                                        $geb = explode('.', $Participant ['geb']);
                                        if ($geb[2] < $achtzehn_jahr) {
                                            $html .= "<td>" . $Participant [$col] . "</td>";
                                        } else {
                                            if ($geb[2] == $achtzehn_jahr && $geb[1] < $achtzehn_monat) {
                                                $html .= "<td>" . $Participant [$col] . "</td>";
                                            } else {
                                                if ($geb[2] == $achtzehn_jahr && $geb[1] == $achtzehn_monat && $geb[0] < $achtzehn_tag) {
                                                    $html .= "<td>" . $Participant [$col] . "</td>";
                                                } else {
                                                    $html .= "<td><b>" . $Participant [$col] . "</b></td>";
                                                }
                                            }
                                        }
                                    } else {
                                        $html .= "<td><b>" . $Participant [$col] . "</b></td>";
                                    }
                                    break;

                                case "mail" :
                                    $html .= "<td title=\"" . $Participant ['mail'] . "\"><a href=\"mailto:" . $Participant ['mail'] . "\">" . substr($Participant ['mail'], 0, 14) . "</a></td>";
                                    break;

                                case "kommentar" :
                                    $html .= "<td title=\"" . $Participant ['kommentar'] . "\">" . substr($Participant ['kommentar'], 0, 15) . "</td>";
                                    break;

                                default :
                                    $html .= "<td>" . $Participant [$col] . "</td>";
                                    break;
                            }
                        }

                        $html .= "<td><a href=\"/Participants/participantdatacheck/" . $Participant ['ID'] . "\" target=\"_blank\"><img src=\"/images/bearb.gif\"\\></a></td>";
                        $html .= "</tr>";
                    }

                    //table footer
                    $html .= "</table>";
                    $this->view->table = $html;
                }

                //hand over more information to view
                $this->view->cols = $cols;
                $this->view->whereVals = $whereVals;
                $this->view->sql_count = $res ['count'];
                $this->view->sql_string = $res ['sql'];
            }
        }
    }

//change Participant data
    public function participantdatacheckAction() {

        $data = $this->getRequest()->getParams();

        $model = $this->_getModel();
        //get Participant data
        $Participantdata = $model->getParticipantsDataByID($data['ID']);

        //Wenn es ein TN MA ist:
        if ($Participantdata['MA'] != NULL) {
            $usrdata = $model->getUserDataByID($Participantdata['MA']);
            $Participantdata['Name'] = $usrdata['lname'];
            $Participantdata['Vorname'] = $usrdata['fname'];

            //change birthday
            if ($usrdata ['birthday'] != null) {
                $usrdata ['birthday'] = substr($usrdata['birthday'], 8, 2) . "." . substr($usrdata['birthday'], 5, 2) . "." . substr($usrdata['birthday'], 0, 4);
            } else {
                $usrdata ['birthday'] = "";
            }
            $Participantdata['Strasse'] = $usrdata['street'];
            $Participantdata['geb'] = $usrdata['birthday'];
            $Participantdata['mail'] = $usrdata['email'];
            $Participantdata['Ort'] = $usrdata['city'];
            $Participantdata['tel'] = $usrdata['phone'];
        }
        //change last changed
        $Participantdata['lastchanged'] = $Participantdata['lastchanged'] . " Uhr";

        //give Participantdata to view
        $this->view->Participantdata = $Participantdata;




        //Title
        $this->view->title = $Participantdata['Name'] . " " . $Participantdata['Vorname'] . " (" . $Participantdata['mail'] . ")";
    }

    public function updateparticipantsAction() {

        //basic settings
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);


        if ($this->getRequest()->isPost()) {

            //get Model
            $model = $this->_getModel();
            //get request data
            $data = $this->getRequest()->getParams();

            if ($data['samstag'] == "Seminar: Band") {
                $data['samstag'] = $data['samstag'] . ": Bandname: " . $data['sa_band_name'] . ": Instrumente: " . $data['sa_band_instr'];
            }

            //change last changed
            $data['lastchanged'] = date("d.m.Y, H:i:s");

            unset($data['sa_band_name']);
            unset($data['sa_band_instr']);

            if ($data['mode'] == "update") {
                unset($data['controller']);
                unset($data['action']);
                unset($data['module']);
                unset($data['mode']);


                //save Data
                $result = $model->updateParticipants($data);

                //Nachricht ans UI schicken
                $msg = Array();
                if ($result == 0) {
                    $msg['shorttext'] = "Daten nicht geändert";
                    $msg['longtext'] = "Daten wurden nicht geändert.";
                    $msg['cat'] = "info";
                } elseif ($result == 1) {
                    $msg['shorttext'] = "Datensatz geändert";
                    $msg['longtext'] = "Daten wurden geändert und gespeichert.";
                    $msg['cat'] = "success";
                } else {
                    $msg['shorttext'] = "Fehler beim Speichern";
                    $msg['longtext'] = "Probiers einfach nochmal. Danke!";
                    $msg['cat'] = "info";
                }

                $msg['new'] = "true";
                $this->_helper->FlashMessenger->addMessage($this->_helper->json->encodeJson($msg));

                $this->_helper->redirector('viewlist', 'Participants');
            } elseif ($data['mode'] == "delete") {

                $result = $model->deleteParticipants($data['ID']);
                $msg = Array();
                if ($result == 0) {
                    $msg['shorttext'] = "Nichts geändert";
                    $msg['longtext'] = "Nix ist passiert.";
                    $msg['cat'] = "info";
                } elseif ($result == 1) {
                    $msg['shorttext'] = "Teilnehmer gelöscht";
                    $msg['longtext'] = "Ein Teilnehmer wurde aus der Datenbank gelöscht.";
                    $msg['cat'] = "success";
                } else {
                    $msg['shorttext'] = "Fehler";
                    $msg['longtext'] = "Irgendwas ging schief. Einfach nochmal probieren. Danke!";
                    $msg['cat'] = "warning";
                }

                $msg['new'] = "true";
                $this->_helper->FlashMessenger->addMessage($this->_helper->json->encodeJson($msg));

                $this->_helper->redirector('viewlist', 'Participants');
            } else {
                
            }
        }
    }

    public function statistikAction() {

        //set title
        $this->view->title = "Anmeldestatistik Teennight/Teennightplus";
        //SELECT data from Model
        $model = $this->_getModel();


        $statistik_tage = array(array('17', '06', '2012', '20', '00'),
            array('18', '06', '2012', '14', '00'),
            array('19', '06', '2012', '11', '00'),
            array('19', '06', '2012', '20', '00'),
            array('20', '06', '2012', '00', '00'),
            array('20', '06', '2012', '11', '00'),
            array('20', '06', '2012', '20', '00'),
            array('21', '06', '2012', '11', '00'),
            array('21', '06', '2012', '20', '00'),
            array('22', '06', '2012', '11', '00'),
            array('22', '06', '2012', '18', '00'));
        $data = $model->getStatistik($statistik_tage);

        echo "Gesamt Anmeldungen: <br>";
        echo "<table border='1' cellpadding='2' cellspacing='0'><tr><td></td><td><b>So 20:00</b></td><td><b>Mo 14:00</b></td><td><b>Di 11:00</b></td><td><b>Di 20:00</b></td><td><b>Mi 00:00</b></td><td><b>Mi 11:00</b></td><td><b>Mi 20:00</b></td><td><b>Do 11:00</b></td><td><b>Do 20:00</b></td><td><b>Fr 11:00</b></td><td><b>Fr 18h</b></td></tr>";
        echo "<tr><td>2009</td><td></td><td>270</td><td>391</td><td>767</td><td></td><td></td><td>808</td><td>815</td><td>877</td><td>879</td><td>882</td></tr>";
        echo "<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
        echo "<tr><td>2010 TN</td><td></td><td>259</td><td>376</td><td>560</td><td></td><td>665</td><td>672</td><td>685</td><td>707</td><td>716</td><td>717</td></tr>";
        echo "<tr><td>2010 TN+</td><td></td><td>51</td><td>61</td><td>66</td><td></td><td>90</td><td>94</td><td>94</td><td>98</td><td>102</td><td>103</td></tr>";
        echo "<tr><td>2010 Gesamt</td><td></td><td>310</td><td>437</td><td>626</td><td></td><td>755</td><td>766</td><td>779</td><td>805</td><td>818</td><td>820</td></tr>";
         echo "<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
        echo "<tr><td>2011 TN</td><td>183</td><td>220</td><td>377</td><td>471</td><td>527</td><td>527</td><td>549</td><td>558</td><td>581</td><td>588</td><td>621</td></tr>";
        echo "<tr><td>2011 TN+</td><td>47</td><td>58</td><td>88</td><td>104</td><td>108</td><td>108</td><td>108</td><td>108</td><td>108</td><td>111</td><td>113</td></tr>";
        echo "<tr><td>2011 Gesamt</td><td>230</td><td>278</td><td>465</td><td>575</td><td>635</td><td>635</td><td>657</td><td>666</td><td>689</td><td>699</td><td>734</td></tr>";
        echo "<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
        echo "<tr><td><b>2012 Teennight</b></td>";
        for ($i = 0; $i < count($statistik_tage); $i++) {
            echo "<td>" . $data[0][$i] . "</td>";
        }
        echo "<tr>";
        echo "<tr><td><b>2012 Teennight+</b></td>";
        for ($i = 0; $i < count($statistik_tage); $i++) {
            echo "<td>" . $data[1][$i] . "</td>";
        }
        echo "<tr>";
        echo "<tr><td><b>2012 Gesamt</b></td>";
        for ($i = 0; $i < count($statistik_tage); $i++) {
            $ges = $data[0][$i] + $data[1][$i];
            echo "<td><b>" . $ges . "</b></td>";
        }
        echo "<tr>";
        echo "</table>";
    }

    public function addAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        if ($this->getRequest()->isPost()) {



            $data = $this->getRequest()->getParams();

            if ($data['art'] == "tg_sa") {
                $data['schlafen'] = "keine";
            }
            if ($data['art'] == "tg_so") {
                $data['schlafen'] = "keine";
                $data['samstag'] = "nichts";
            }

            if ($data['samstag'] == "Seminar: Band") {
                $data['samstag'] = $data['samstag'] . ": Bandname: " . $data['sa_band_name'] . ": Instrumente: " . $data['sa_band_instr'];
            }
            unset($data['sa_band_name']);
            unset($data['sa_band_instr']);
            $data['datum'] = $datum = date("d.m.Y, H:i:s");


            //add Adder userID
            $session = new Zend_Session_Namespace();
            $data['addedby'] = (int) $session->user['ID'];


            //delete wast'd data
            unset($data['controller']);
            unset($data['action']);
            unset($data['module']);


            //get Model
            $model = $this->_getModel();


            //save...
            $res = $model->saveParticipant($data);
            if ($res == null) {
                echo "Fehler beim Speichern!";
                return;
            } else {
                $Participantdata = $res;
            }

            //return ID
            echo "Participant " . $Participantdata['Vorname'] . " " . $Participantdata['Name'] . " mit ID " . $Participantdata['ID'] . " gespeichert";
            $this->_helper->redirector('index', 'Participants');
        }
    }

    //get db model
    protected function _getModel() {
        if (null === $this->_model) {
            require_once APPLICATION_PATH . '/models/Model_Participants.php';
            $this->_model = new Model_Participants ( );
        }
        return $this->_model;
    }

}
