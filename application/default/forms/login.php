<?php

class Form_Login extends Zend_Form 
{
	
	public function init()
	{
		
		$this->setMethod("post");
		
		$this->addElement('text', 'usrname', array(
            'label'      => 'Benutzername',
            'required'   => true,
            'filters'    => array('StringTrim')
        ));
        
        $this->addElement('password', 'pwd', array(
        	'label'		=>	'Passwort',
        	'required'	=>	true,
        	'filters'    => array('StringTrim')
        ));
		
         // add the submit button
        $this->addElement('submit', 'submit', array(
            'label'    => 'Login',
        ));
        
		
	}
	
}