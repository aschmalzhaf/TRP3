<?php 


require_once 'Zend/Db/Table/Abstract.php';

class Db_Table_Mail_Templates extends Zend_Db_Table_Abstract
{
	/**
	 * The default table name 
	 */
    protected $_name = 'mail_templates';
    
    public function __construct($config){
        parent::__construct($config);
        $this->getAdapter()->query("SET NAMES utf8");
    }
    
    
    public function insert(array $data)
    {
        return parent::insert($data);
    }
    
    
    public function fetchMailtemplate($id){
        $select = $this->select();
        $select->where('ID = ?', $id);

        $rows = $this->fetchRow($select);

        return $rows;
    }

    public function fetchAllMailtemplates(){
        $select = $this->select();
        $rows = $this->fetchAll($select);

        return $rows;
    }
    
    
}