<?php

// build up a standard rights management
//
// RESOURCES:
// Index
// Dashboard
// Captteam
// Staff -> Add
// Staff -> Edit
// Staff -> Delete
// Events -> complete
// Rooms -> complete
// Reports -> complete
//
// RIGHTS:
// none	view	(edit)
//
// ROLES:
// TNT	Admin	Staff


class Acl extends Zend_Acl {

  public function __construct(){

    // ------- RESOURCES -------------

    $this->add(new Zend_Acl_Resource('Apisystem'));
    $this->add(new Zend_Acl_Resource('Apibasic'));
    $this->add(new Zend_Acl_Resource('Apicaptteam'));
    $this->add(new Zend_Acl_Resource('Apieventshift'));
    $this->add(new Zend_Acl_Resource('Basic'));
    $this->add(new Zend_Acl_Resource('Captteam'));
    $this->add(new Zend_Acl_Resource('Customizing'));//@TODO: gebraucht? Prüfen!
    $this->add(new Zend_Acl_Resource('Error'));
    $this->add(new Zend_Acl_Resource('Eventshift'));
    $this->add(new Zend_Acl_Resource('Index'));
    $this->add(new Zend_Acl_Resource('Login'));
    $this->add(new Zend_Acl_Resource('Participants'));
    $this->add(new Zend_Acl_Resource('Reports'));
    $this->add(new Zend_Acl_Resource('Staff'));
    $this->add(new Zend_Acl_Resource('Wishoptions'));
    $this->add(new Zend_Acl_Resource('Rooms'));

    // ------- RIGHTS of ROLES -> RESOURCES ---------

    //Admin
    $this->addRole(new Zend_Acl_Role('Admin'));
    $this->allow('Admin');


    //TNT
    $this->addRole(new Zend_Acl_Role('TNT'));
    
    $this->allow('TNT','Apisystem','view');
    $this->allow('TNT','Apibasic','view');
    $this->allow('TNT','Apicaptteam','view');
    $this->allow('TNT','Apieventshift','view');
    $this->allow('TNT','Index','view');
    $this->allow('TNT','Basic','view');
    $this->allow('TNT','Captteam','view');
    $this->allow('TNT','Staff','view');
    //@TODO: Berechtigungen prüfen -> auseinandersteuern nach sehen und ändern
    $this->allow('TNT','Eventshift','none');
    $this->allow('TNT','Rooms','none');
    $this->allow('TNT','Reports','none');
    $this->allow('TNT','Participants','none');
    $this->allow('TNT','Wishoptions','none');

    //Staff
    $this->addRole(new Zend_Acl_Role('Mitarbeiter'));
    
    $this->allow('Mitarbeiter','Apisystem','view');
    $this->allow('Mitarbeiter','Apibasic','view');
    $this->allow('Mitarbeiter','Apicaptteam','none');
    $this->allow('Mitarbeiter','Apieventshift','view');
    $this->allow('Mitarbeiter','Index','view');
    $this->allow('Mitarbeiter','Basic','view');
    $this->allow('Mitarbeiter','Captteam','none');
    $this->allow('Mitarbeiter','Staff','none');
    $this->allow('Mitarbeiter','Eventshift','none');
    $this->allow('Mitarbeiter','Rooms','none');
    $this->allow('Mitarbeiter','Reports','none');
    $this->allow('Mitarbeiter','Participants','none');
    $this->allow('Mitarbeiter','Wishoptions','none');


    //Verantwortlicher(like Staff)
    $this->addRole(new Zend_Acl_Role('Verantwortlicher'));

    $this->allow('Verantwortlicher','Apisystem','view');
    $this->allow('Verantwortlicher','Apibasic','view');
    $this->allow('Verantwortlicher','Apicaptteam','none');
    $this->allow('Verantwortlicher','Apieventshift','view');
    $this->allow('Verantwortlicher','Index','view');
    $this->allow('Verantwortlicher','Basic','view');
    $this->allow('Verantwortlicher','Staff','view');
    $this->allow('Verantwortlicher','Eventshift','none');
    $this->allow('Verantwortlicher','Rooms','none');
    $this->allow('Verantwortlicher','Reports','none');
    $this->allow('Verantwortlicher','Participants','none');
    $this->allow('Verantwortlicher','Wishoptions','none');

    // JuFa
    $this->addRole(new Zend_Acl_Role('JuFa'));

    $this->allow('JuFa','Apisystem','view');
    $this->allow('JuFa','Apibasic','view');
    $this->allow('JuFa','Apicaptteam','none');
    $this->allow('JuFa','Apieventshift','view');
    $this->allow('JuFa','Index','view');
    $this->allow('JuFa','Basic','view');
    $this->allow('JuFa','Staff','none');
    $this->allow('JuFa','Eventshift','none');
    $this->allow('JuFa','Rooms','none');
    $this->allow('JuFa','Reports','none');
    $this->allow('JuFa','Participants','view');
    $this->allow('JuFa','Wishoptions','none');
  }

}
