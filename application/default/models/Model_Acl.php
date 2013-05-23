<?php

class Model_Acl{
	
	
	protected $_table;
	
	

    //return Role
	function getRoleByID($id){
		
		$table = $this->getTable();
		$role = $table->getRoleByID($id);
		if($role !== NULL){
			return $role['name'];
		}else{
			return null;
		}
	}
	
	
	
	
	
	public function getTable()
    {
        if (null === $this->_table) {
            // since the dbTable is not a library item but an application item,
            // we must require it to use it
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_Usr_Roles.php';
           	
            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_table = new Db_Table_Usr_Roles(array('db' => $db));
        }
        return $this->_table;
    }


}
