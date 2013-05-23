<?php 


require_once 'Zend/Db/Table/Abstract.php';

class Db_Table_Wopts_Events extends Zend_Db_Table_Abstract
{
	/**
	 * The default table name 
	 */
    protected $_name = 'wopts_events';
    
    
    public function __construct($config){
        parent::__construct($config);
        $this->getAdapter()->query("SET NAMES utf8");
    }
    
    
    public function insert(array $data)
    {
        return parent::insert($data);
    }
    
    
    
    
    public function fetchAllWoptsEvents()
    {
    	$select = $this->select();
    	$select->order("wishoption ASC");
    	$rows = $this->fetchAll($select);
    	//whishoption soll Schlüssel des Ergebnisarrays sein, es folgt eine Sammlung der events
    	//Bsp.: 5 => array(1,4,14)
    	
    	$resArray = array();
    	foreach($rows as $row){
    	  $resArray[$row['wishoption']][] = $row['event'];
    	}
    	//returns table record
    	return $resArray;
    }
    
    public function fetchWithWopt($wopt){
        $select = $this->select();
        $select->where("wishoption = ?", $wopt);

        return $this->fetchAll($select);
    }
    
    public function fetchWithEvent($event){
        $select = $this->select();
        $select->where("event = ?", $event);
        return $this->fetchAll($select);
    }
    
    
}