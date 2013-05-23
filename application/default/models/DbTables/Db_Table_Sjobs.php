<?php 


require_once 'Zend/Db/Table/Abstract.php';

class Db_Table_Sjobs extends Zend_Db_Table_Abstract
{
	/**
	 * The default table name 
	 */
    protected $_name = 'sjobs';
    
    
    public function __construct($config){
        parent::__construct($config);
        $this->getAdapter()->query("SET NAMES utf8");
    }
    
    
    public function insert(array $data)
    {
        return parent::insert($data);
    }
    
    
    public function fetchAllSjobs()
    {
    	$select = $this->select();
    	$select->order('name ASC');
    	$rows = $this->fetchAll($select);
    	
    	//returns table record
    	return $rows;
    }
    
    
}