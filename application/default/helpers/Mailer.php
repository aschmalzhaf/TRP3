<?php

class Mailer {
	
	public static function sendmail($toadr, $toname, $subject, $HTMLBody, $prio) {
		
		//get data from registry
		$registry = Zend_Registry::getInstance();
		
		try {
			
			$config = array (	'auth' => 'login', 
								'username' => $registry->config->mail->smtp->usr, 	
								'password' => $registry->config->mail->smtp->pwd );
			
			$transport = new Zend_Mail_Transport_Smtp ( $registry->config->mail->smtp->host, $config );
			
			$mail = new Zend_Mail ( );
			
			$mail->setFrom ( $registry->config->mail->fromadr, $registry->config->mail->fromname );
			
			$mail->addTo ( $toadr, $toname );
			
			//send Logmail, if Adress is given
			if($registry->config->mail->logadr != ""){
				$mail->addBcc ( $registry->config->mail->logadr, $registry->config->mail->logname );
			}
			
			//set priority
			if(isset($prio)){
				$mail->addHeader ( 'X-Priority', $prio );
			}
			
			$mail->setBodyHTML ( $HTMLBody );
			$mail->setSubject ( $subject );
			$mail->send ( $transport );
		
		} catch ( Zend_Exception $e ) {
			
			$config = array (	'auth' => 'login', 
								'username' => $registry->config->mail->smtp->usr, 	
								'password' => $registry->config->mail->smtp->pwd );
			
			$transport = new Zend_Mail_Transport_Smtp ( $registry->config->mail->smtp->host, $config );
			
			$adminmail = new Zend_Mail ( );
			
			$adminmail->setFrom ( $registry->config->mail->fromadr, $registry->config->mail->fromname );
			
			$adminmail->addTo ( $registry->config->mail->adminadr, $registry->config->mail->adminname );
			
			$adminmail->addHeader ( 'X-Priority', '1' );
			$adminmail->setBodyHTML ( "Hallo<br>Es gab einen Fehler:<br>" . $e );
			$adminmail->setSubject ( "TRP: Fehlermeldung" );
			$adminmail->send ( $transport );
			
			throw new Exception($e);
		
		}
	
	}
}