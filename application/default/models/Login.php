<?php


Class Model_Login{
	
	protected $_table;
	
	
	//check given user data
	public function checkData(array $data){
		
		$table = $this->getTable();
		
		$UsrData = $table->getUsrDataByUsrname(strtoupper($data['usrname']));
		$registry = Zend_Registry::getInstance();
		if($UsrData['pwd'] == md5($data['pwd'].$registry->config->salt)){
			unset($UsrData['pwd']);
			return $UsrData;
		}else{
			return null;
		}
		
		
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

?>