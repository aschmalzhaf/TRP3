<?php

class ErrorController extends Zend_Controller_Action {

    public function errorAction() {

        $this->_helper->layout->setLayout('error');

        $this->view->title = "Fehler...";
        $errors = $this->_getParam('error_handler');
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER :

            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION :

                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Seite nicht gefunden';
                break;

            default :

                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                break;
        }

        //send mail to admin
        $registry = Zend_Registry::getInstance();

        //Mailfragmente zusammenbauen
        //User, der den Fehler verursacht hat
        $session = new Zend_Session_Namespace();
        $time = new DateTime();
        $msgpart = "Zeit: " . $time->format('Y-m-d H:i:s') . "<br>";
        $msgpart = $msgpart . "User: " . $session->user['ID'] . " " . $session->user['fname'] . " " . $session->user['lname'] . "<br>";
        $msgpart = $msgpart . "Request: " . print_r($this->getRequest()->getParams(), true) . "<br><br>";
        $msgpart = $msgpart . "SERVER_NAME der Installation: " . $_SERVER['SERVER_NAME'] . "<br><br>";


        try {
            require_once ('../application/default/helpers/Mailer.php');

            Mailer::sendmail($registry->config->mail->adminadr, $registry->config->mail->adminname, "TRP-Fehler", $msgpart . $errors->exception->getMessage(), 1);
        } catch (Exception $e) {
            echo "Fehler beim Senden: " . $e;
        }

        // pass the request to the view
        $this->view->request = $errors->request;
    }

}