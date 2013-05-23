<?php

/**
 * {0}
 *  
 * @author 001
 * @version 
 */
	
require_once 'Zend/Db/Table/Abstract.php';

class Db_Table_Usr_Roles extends Zend_Db_Table_Abstract
{
	/**
	 * The default table name 
	 */
    protected $_name = 'usr_roles';
    
    public function __construct($config){
        parent::__construct($config);
        $this->getAdapter()->query("SET NAMES utf8");
    }
    
    
    //select role
    public function getRoleByID($id){
    	
    	$select = $this->select();
    	$select->where('ID = ?',$id);
    	
    	$row = $this->fetchRow($select);
    	

    	if($row !== NULL){
    		$rowArray = $row->toArray();
    		return $rowArray;
    	}else{
    		return NULL;
    	}
    }
    
    public function getAllRoles(){

    	return $this->fetchAll();
    }
    
}
