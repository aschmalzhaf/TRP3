<?php

class Model_saveStaff {

	protected $_table;
	
	public function saveStaff(array $data){
		
		$table = $this->getTable();
		
		return $table->insert($data);
	}
	
	
	public function getTable()
    {
        if (null === $this->_table) {
            // since the dbTable is not a library item but an application item,
            // we must require it to use it
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_User.php';
           	
            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_table = new Db_Table_User(array('db' => $db));
        }
        return $this->_table;
    }
	
}
