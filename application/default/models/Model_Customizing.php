<?php

class Model_Customizing{
	
	protected $_WishesTable;
	protected $_UserTable;
	protected $_KvTable;
	
	
	public function getWishesTable()
    {
        if (null === $this->_WishesTable) {
            // since the dbTable is not a library item but an application item,
            // we must require it to use it
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_Wishes.php';
           	
            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_WishesTable = new Db_Table_Wishes(array('db' => $db));
        }
        return $this->_WishesTable;
    }
	
    
	public function getUserTable()
    {
        if (null === $this->_UserTable) {
            // since the dbTable is not a library item but an application item,
            // we must require it to use it
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_User.php';
           	
            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_UserTable = new Db_Table_User(array('db' => $db));
        }
        return $this->_UserTable;
    }
    
	public function getKvTable()
    {
        if (null === $this->_KvTable) {
            // since the dbTable is not a library item but an application item,
            // we must require it to use it
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_Kv.php';
           	
            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_KvTable = new Db_Table_Kv(array('db' => $db));
        }
        return $this->_KvTable;
    }
	
}