<?php

/**
 *
 *
 * @author
 * @version
 */
require_once 'Zend/Controller/Action.php';

class StaffController extends Zend_Controller_Action {

    //vars
    protected $_model;

    public function init() {
    }

    public function indexAction() {

    }

    /**
     * Holt die Daten aus der datenbank entpsrechend der übergebenen Parameter und liefert
     * ein JSON-Array der Ergebnisse zurück.
     * Aufruf erfolgt als AJAX-Aufruf.
     * 
     * @TODO Interface bauen, das sowohl die Datenbeschaffung "getsearchresult" als auch die 
     * Spaltenbeschaffung (nächste Methode) enthält und als standard implementiert. 
     * Das Interface muss dann immer für Tabellen implementiert werden.
     * 
     * @version 2012.1
     * @author Alexander Schmalzhaf
     */
    public function getsearchresultAction() 
    {

        $res = $this->getsearchresultService();

        $this->_helper->json->sendJson($res);
    }

    public function getsearchresultService() {
        //get request data
        $params = $this->getRequest()->getParams();
        //Build up data arrays
        $where = Array();

        //Spalten:
        $res ['columns'] = array_merge(Array("ID"), $params['columns']);

        //Daten vom Model holen
        $model = $this->_getModel();

        //vars: $cols, $where, $sort, $range
        $where = $params['parameter'];

        //Where aufräumen: leere Parameter weglöschen
        $y = count($where); //muss vorher geschehen, weil Arraylänge sich ändert
        for ($i = 0; $i < $y; $i++) {
            foreach ($where[$i] as $key => $value) {
                if(trim($value) == ""){
                    unset($where[$i]);
                }    
            }
        }

        $selectResult = $model->getGenericStaffData($res ['columns'], $where, null);
        $res['data'] = $selectResult['data'];

        return $res;
    }

    public function getsearchresultcsvAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . date("Y-m-d") . '_TN-Mitarbeiterliste.xlsx"');

        $res = Array();
        $res = $this->getsearchresultService();
        $session = new Zend_Session_Namespace ();

        // Neues Excel generieren
        require_once('PHPExcel/PHPExcel.php');
        $excelFile = new PHPExcel();

        // Set properties
        $excelFile->getProperties()->setCreator("Südwestdeutscher EC Verband");
        $excelFile->getProperties()->setLastModifiedBy($session->user ['fname'] . " " . $session->user ['lname']);
        $excelFile->getProperties()->setTitle("Mitarbeiterliste");
        $excelFile->getProperties()->setSubject("Teennight (generiert von TRP)");
        $excelFile->getProperties()->setDescription("Diese Liste enthält personenbezogene Daten von Mitarbeitern.");

        //Daten setzen
        $excelFile->setActiveSheetIndex(0);
        $excelFile->getActiveSheet()->setTitle('Mitarbeiter');

        //**Spaltennamen
        $i = 0;
        foreach ($res['columns'] as $col) {
            $excelFile->getActiveSheet()->getCellByColumnAndRow($i,1)->setValueExplicit($col,PHPExcel_Cell_DataType::TYPE_STRING2);
            $i++;
        }

        $j = 2;
        foreach ($res['data'] as $staff) {
            $i = 0;
            foreach ($res['columns'] as $col) {
                $excelFile->getActiveSheet()->getCellByColumnAndRow($i,$j)->setValueExplicit($staff[$col],PHPExcel_Cell_DataType::TYPE_STRING2);
                $i++;
            }
            $j++;
        }

        // Datei an den PHP Output Stream schicken
        $objWriter = new PHPExcel_Writer_Excel2007($excelFile);
        $objWriter->save('php://output');
    }

    public function getpossiblecolumnsAction() {
        //Gibt JSON Object-Array zurück:
        //[{"ID":"fnmae","columnname":"Vorname", "selected":"true"},...]
        $columns = Array();
        //Vorname
        $column['ID'] = "fname";
        $column['columnname'] = "Vorname";
        $column['selected'] = "true";
        $columns[] = $column;
        //lanme Nachname
        $column['ID'] = "lname";
        $column['columnname'] = "Nachname";
        $column['selected'] = "true";
        $columns[] = $column;
        //mphone Handynummer
        $column['ID'] = "mphone";
        $column['columnname'] = "Handy";
        $column['selected'] = "true";
        $columns[] = $column;
        //phone Telefonnummer
        $column['ID'] = "phone";
        $column['columnname'] = "Telefon";
        $column['selected'] = "false";
        $columns[] = $column;
        //birthday Geburtstag
        $column['ID'] = "birthday";
        $column['columnname'] = "Geburtstag";
        $column['selected'] = "true";
        $columns[] = $column;
        //usrnote Ben. Notiz
        $column['ID'] = "usrnote";
        $column['columnname'] = "Notiz (Benutzer)";
        $column['selected'] = "true";
        $columns[] = $column;
        //exp Erfahrungen
        $column['ID'] = "exp";
        $column['columnname'] = "Erfahrungen";
        $column['selected'] = "false";
        $columns[] = $column;
        //intnote Interne Notiz
        $column['ID'] = "intnote";
        $column['columnname'] = "int. Notiz";
        $column['selected'] = "false";
        $columns[] = $column;
        //city Ort
        $column['ID'] = "city";
        $column['columnname'] = "Ort";
        $column['selected'] = "true";
        $columns[] = $column;
        //street Straße
        $column['ID'] = "street";
        $column['columnname'] = "Straße";
        $column['selected'] = "false";
        $columns[] = $column;
        //kv KV-Nummer
        $column['ID'] = "kv";
        $column['columnname'] = "KV ID";
        $column['selected'] = "false";
        $columns[] = $column;
        //kv_name KV
        $column['ID'] = "kv_name";
        $column['columnname'] = "KV";
        $column['selected'] = "false";
        $columns[] = $column;
        //email E-Mail-Adresse
        $column['ID'] = "email";
        $column['columnname'] = "Mail";
        $column['selected'] = "true";
        $columns[] = $column;
        // addedby TNT-ID
        $column['ID'] = "addedby";
        $column['columnname'] = "TNT ID";
        $column['selected'] = "false";
        $columns[] = $column;
        //addedby_lname TNT NN
        $column['ID'] = "addedby_lname";
        $column['columnname'] = "TNT Name";
        $column['selected'] = "false";
        $columns[] = $column;
        //usrname Benutzername
        $column['ID'] = "usrname";
        $column['columnname'] = "Benutzer";
        $column['selected'] = "false";
        $columns[] = $column;
        //role Rolle
        $column['ID'] = "role";
        $column['columnname'] = "Rolle";
        $column['selected'] = "false";
        $columns[] = $column;
        //sjob spezielle Aufgabe ID
        $column['ID'] = "sjob";
        $column['columnname'] = "S-Aufgabe ID";
        $column['selected'] = "true";
        $columns[] = $column;
        //sjob_name spezielle Aufgabe
        $column['ID'] = "sjob_name";
        $column['columnname'] = "S-Aufgabe";
        $column['selected'] = "true";
        $columns[] = $column;
        //blocked Nacht geblockt
        $column['ID'] = "blocked";
        $column['columnname'] = "Nacht geblockt";
        $column['selected'] = "false";
        $columns[] = $column;
        //wishes Wünsche IDs
        $column['ID'] = "wishes";
        $column['columnname'] = "Wünsche IDs";
        $column['selected'] = "false";
        $columns[] = $column;
        //wishes_names Wünsche
        $column['ID'] = "wishes_names";
        $column['columnname'] = "Wünsche";
        $column['selected'] = "false";
        $columns[] = $column;
        //lastchanged letzte Änderung
        $column['ID'] = "lastchanged";
        $column['columnname'] = "letzte Änderung";
        $column['selected'] = "true";
        $columns[] = $column;
        //locked voll gebucht
        $column['ID'] = "locked";
        $column['columnname'] = "voll gebucht";
        $column['selected'] = "false";
        $columns[] = $column;
        //bus Bussle
        $column['ID'] = "bus";
        $column['columnname'] = "Bussle";
        $column['selected'] = "false";
        $columns[] = $column;
        //climbing Klettern
        $column['ID'] = "climbing";
        $column['columnname'] = "Klettern";
        $column['selected'] = "false";
        $columns[] = $column;
        //lifeguard Rettungsschwimmer
        $column['ID'] = "lifeguard";
        $column['columnname'] = "Rettungsschwimmer";
        $column['selected'] = "false";
        $columns[] = $column;
        //medic Sanitäter
        $column['ID'] = "medic";
        $column['columnname'] = "Sanni";
        $column['selected'] = "false";
        $columns[] = $column;

        //Daten senden
        $this->_helper->json->sendJson($columns);
    }

    /**
     *
     * 
     */
    public function viewlistAction() {
        //set title
        $this->view->title = "Mitarbeiter anzeigen";

        //Parameter mit auf das UI geben; für vorgefüllte Suchfelder (Suche wird automatisch gestartet)
        //Vorsicht: wenn Parameter nicht von außen kommt, leer auf's UI senden
        $prefillValues[] = Array();
        $requestParams = $this->getRequest()->getParams();
        if(isset($requestParams['ID'])){
            $prefillValues['ID'] = $requestParams['ID'];
        }else{
            $prefillValues['ID'] = "";
        };
        if(isset($requestParams['fname'])){
            $prefillValues['fname'] = $requestParams['fname'];
        }else{
            $prefillValues['fname'] = "";
        };
        if(isset($requestParams['lname'])){
            $prefillValues['lname'] = $requestParams['lname'];
        }else{
            $prefillValues['lname'] = "";
        };
        if(isset($requestParams['addedby'])){
            $prefillValues['addedby'] = $requestParams['addedby'];
        }else{
            $prefillValues['addedby'] = "";
        };
        if(isset($requestParams['city'])){
            $prefillValues['city'] = $requestParams['city'];
        }else{
            $prefillValues['city'] = "";
        };
        if(isset($requestParams['kv'])){
            $prefillValues['kv'] = $requestParams['kv'];
        }else{
            $prefillValues['kv'] = "";
        };
        if(isset($requestParams['locked'])){
            $prefillValues['locked'] = $requestParams['locked'];
        }else{
            $prefillValues['locked'] = "";
        };

        $this->view->prefillValues = $prefillValues;
    }

//get db model
    protected function _getModel() {
        if (null === $this->_model) {
            require_once APPLICATION_PATH . '/models/Model_Staff.php';
            $this->_model = new Model_Staff ();
        }
        return $this->_model;
    }

}
