<?php 


require_once 'Zend/Db/Table/Abstract.php';

class Db_Table_Links extends Zend_Db_Table_Abstract
{
	/**
	 * The default table name 
	 */
    protected $_name = 'links';
    
    public function __construct($config){
        parent::__construct($config);
        $this->getAdapter()->query("SET NAMES utf8");
    }
    
    
    public function insert(array $data)
    {
        return parent::insert($data);
    }
    
    
    public function deleteLink($data){
    	
        if(!isset($data['shiftid'])&& !isset($data['userid'])){
            return;
        }elseif(isset($data['shiftid']) && !isset($data['userid'])){
            return $this->delete(Array('shiftid = '.$data['shiftid']));
        }elseif(!isset($data['shiftid']) && isset($data['userid'])){
            return $this->delete(Array('userid = '.$data['userid']));
        }elseif(isset($data['shiftid']) && isset($data['userid'])){
            return $this->delete(Array('shiftid = '.$data['shiftid'],'userid = '.$data['userid']));
        }    
        return;
    }
    
    
	public function getUsersOfShift($id){
    	
    	$select = $this->select();
    	$select->setIntegrityCheck(false);
    	
    	$cols = Array();
    	$select->from(Array('links'=>'links'),$cols);
    	
    	$select->joinLeft(Array('users'=>'user'),'links.userid = users.ID',
    				Array('user_ID'=>'ID','user_fname'=>'fname','user_lname'=>'lname','user_mphone'=>'mphone','user_phone'=>'phone','user_mail'=>'email'));
    				
    				
    	$select->where('links.shiftid = ?',$id);
    	$rows = $this->fetchAll($select);
    	
    	//returns table record
    	return $rows;
    }
    
    
    public function getUserIDsOfShift($id){
    	$select = $this->select();
    	$select->where('shiftid = ?',$id);
    	$rows = $this->fetchAll($select);
    	
    	return $rows;
    }
    
    
     public function getShiftsOfUserID($id){
    	$select = $this->select();
    	$select->where('userid = ?',$id);
    	$rows = $this->fetchAll($select);
    	
    	return $rows;
    }
    
    
	public function countLinksForShift($id){
		
		$select = $this->select();
		$select->where('shiftid = ?', $id);
		
		return $this->fetchAll($select)->count();
	}
	
	
    
}
