<?php

class Model_Wishoptions{
	
	protected $_WishoptionsTable;
	protected $_EventsTable;
	protected $_ShiftsTable;
	protected $_Wopts_EventsTable;
	
	
    public function getWishoptions(){
		$table = $this->getWishoptionsTable();
		
		$wishoptions = $table->fetchAllWishoptions()->toArray();
	    foreach($wishoptions as $wishoption){
		    $res[] = $wishoption;
		}
		return $res;
	}
	
	public function getWishoption($ID){
	    $table = $this->getWishoptionsTable();
	    
	    return $table->fetchWishoption($ID)->toArray();
	}
	
	public function saveWishoption($data){
	    $table = $this->getWishoptionsTable();
	    $id = $data['ID'];
	    unset($data['ID']);
	    return $table->update($data, Array("ID = ".$id));
	    
	}
	
	
	public function getEvents(){
	    
	    $EventsTable = $this->getEventsTable();
	    $ShiftsTable = $this->getShiftsTable();
	    
	    $events = $EventsTable->getEventsNames()->toArray();
	    
	    $eventReqstaffs = $ShiftsTable->getEventReqstaff()->toArray();
	    
	    foreach($eventReqstaffs as $eventReqstaff){
	        $reqstaff[$eventReqstaff['eventid']] = $eventReqstaff['reqstaff'];
	    }
	    
	    foreach($events as $event){
	        if(isset($reqstaff[$event['ID']])){
	            $event['reqstaff'] = $reqstaff[$event['ID']];
	        }else{
	            $event['reqstaff'] = 0;
	        }
	        $resEvents[] = $event;
	    }
	    return $resEvents; 
	}
	
	
	public function getWoptEventLinks(){
	    $table = $this->getWopts_EventsTable();
	    return $table->fetchAllWoptsEvents()->toArray();
	}
	
	
	public function addWoptEventLink($data){
	    $table = $this->getWopts_EventsTable();
	    return $table->insert($data);
	}
	
	public function deleteWoptEventLink($data){
	    $table = $this->getWopts_EventsTable();
	    
	    return $table->delete($data);
	}
	
	
	private function getWishoptionsTable()
    {
        if (null === $this->_WishoptionsTable) {
            // since the dbTable is not a library item but an application item,
            // we must require it to use it
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_Wishoptions.php';
           	
            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_WishoptionsTable = new Db_Table_Wishes(array('db' => $db));
        }
        return $this->_WishoptionsTable;
    }
    
    private function getEventsTable()
    {
        if (null === $this->_EventsTable) {
            // since the dbTable is not a library item but an application item,
            // we must require it to use it
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_Events.php';
           	
            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_EventsTable = new Db_Table_Events(array('db' => $db));
        }
        return $this->_EventsTable;
    }
	
    private function getShiftsTable()
    {
        if (null === $this->_ShiftsTable) {
            // since the dbTable is not a library item but an application item,
            // we must require it to use it
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_Shifts.php';
           	
            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_ShiftsTable = new Db_Table_Shifts(array('db' => $db));
        }
        return $this->_ShiftsTable;
    }
    
    private function getWopts_EventsTable()
    {
        if (null === $this->_Wopts_EventsTable) {
            // since the dbTable is not a library item but an application item,
            // we must require it to use it
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_Wopts_Events.php';
           	
            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_Wopts_EventsTable = new Db_Table_Wopts_Events(array('db' => $db));
        }
        return $this->_Wopts_EventsTable;
    }
	
	
	
	
	
}