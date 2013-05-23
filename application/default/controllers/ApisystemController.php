<?php

/**
 * Dieser Controller dient Aufrufen von Systemfunktionen, die nicht mit UI gerufen werden,
 * sondern als plain-html oder JSON (bevorzugt).
 *
 * @author Alexander Schmalzhaf
 * @version 2012.1
 */
require_once 'Zend/Controller/Action.php';
require_once APPLICATION_PATH . '/helpers/WishTagDecorator.php';

class ApisystemController extends Zend_Controller_Action {

//vars
    protected $_model;

    public function init() {
        //UI-Funktionen abschalten
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function indexAction() {
//tut (noch) nichts.
    }

    public function setattributesAction() {
        $descriptions[] = Array('name' => $wishoption['name'], 'description' => $wishoption['description']);
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
    
    
    /**
     * Gibt Nachrichten für das UI zurück - formatiert als JSON-Array.
     * Enthaltene Infos/Verwendete Struktur:
     * Kurztext/shorttext (z.B. "Name gespeichert")
     * Langtext/longtext (z.B. "Alexander wurde in Benjamin geändert")
     * Kategorie/cat ("info", "warning", "success")
     * Flag Neu (zum Anzeigen, ob nur Message des Logs)
     * 
     * @TODO noch gibt es keinen Log. Hierfür muss eine weitere Variable in der 
     * Session angelegt werden, die die alten Flash-Messages hält.
     * 
     * @TODO es wird im Moment nur eine (und zwar die erste) Nachricht zurückgegeben.
     * Die JSON-Arrays müssen noch konkatiniert werden.
     * @author Alexander Schmalzhaf
     * @version 2012.1
     */
    public function getmessagesAction() {
        $flashmessages = $this->_helper->FlashMessenger->getMessages();
        foreach ($flashmessages as $flashmsg) {
            $this->_helper->json->sendJson(Zend_Json_Decoder::decode($flashmsg));
        }

    }

}