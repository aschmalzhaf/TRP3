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


class ApieventshiftController extends Zend_Controller_Action
{

    //vars
    protected $_model;

    public function init()
    {
        //UI-Funktionen abschalten
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    /**
     * Gibt eine Liste aller Veranstaltungen in alphabetischer Reihenfolge zurück
     * Sinn: Füllen des fast jump panels auf der "Chagenevent"-Seite
     *
     * @return void
     */
    public function getfastjumpeventsAction()
    {
        $model = $this->_getModel ();
        $events = $model->getAllEvents ();
        //Kopiere nur wichtige Daten zur Übertragung ans UI
        foreach ( $events as $event ) {
            unset($resultSingle);
            $resultSingle['ID'] = $event['ID'];
            $resultSingle['name'] = $event['name'];
            $resultSet[] = $resultSingle;
        }
        $this->getHelper('Json')->sendJson($resultSet);

    }



      //get db model
  protected function _getModel() {
    if (null === $this->_model) {
      require_once APPLICATION_PATH . '/models/Model_Eventshift.php';
      $this->_model = new Model_Eventshift ( );
    }
    return $this->_model;
  }

}