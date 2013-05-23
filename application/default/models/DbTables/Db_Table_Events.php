<?php


require_once 'Zend/Db/Table/Abstract.php';

class Db_Table_Events extends Zend_Db_Table_Abstract
{
  /**
   * The default table name
   */
  protected $_name = 'events';


  public function __construct($config){
    parent::__construct($config);
    $this->getAdapter()->query("SET NAMES utf8");
  }

  public function insert(array $data)
  {
    function trim_value(&$value)
    {
      $value = trim($value);
    }
    array_walk_recursive($data, 'trim_value');
    return parent::insert($data);
  }


  public function getAllEvents()
  {
    $select = $this->select();
    $select->setIntegrityCheck(false);
    
    $cols = Array('ID','name', 'note');
    $select->from(Array('events'=>'events'),$cols);
    
    $select->joinLeft(Array('locations'=>'locations'),'events.locationid = locations.ID',
      Array('location_name'=>'name','location_note'=>'note','color'=>'color'));

    $select->joinLeft(Array('user'=>'user'),'user.ID = events.leadid',
      Array('user_id'=>'ID','user_fname'=>'fname','user_lname'=>'lname'));
    
    $select->order('name ASC');
    
    return $this->fetchAll($select);
    
  }


  public function getEventsNames(){
    $select = $this->select();
    $select->from('events',Array('ID', 'name'));

    return $this->fetchAll($select);
  }


  public function delEvent($id){
    return $this->delete("ID = ".$id);
  }



  public function getEventByID($id){
   
    $select = $this->select();
    $select->setIntegrityCheck(false);
    
    $cols = Array('ID','name','locationid','leadid','note','horelevant');
    $select->from(Array('events'=>'events'),$cols);
    
    $select->joinLeft(Array('locations'=>'locations'),'events.locationid = locations.ID',
      Array('location_name'=>'name','location_note'=>'note','color'=>'color'));

    $select->joinLeft(Array('lead'=>'user'),'lead.ID = events.leadid',
      Array('lead_id'=>'ID','lead_fname'=>'fname','lead_lname'=>'lname','lead_mphone'=>'mphone'));
    

    $select->where('events.ID = ?',$id);
    $rows = $this->fetchRow($select);
    
    //returns table record
    return $rows;
    
  }



  public function getEventsByleadid($leadid){
   
    $select = $this->select();
    $select->setIntegrityCheck(false);
    
    $cols = Array('ID','name','locationid','note');
    $select->from(Array('events'=>'events'),$cols);
    
    $select->joinLeft(Array('locations'=>'locations'),'events.locationid = locations.ID',
      Array('location_name'=>'name','location_note'=>'note','color'=>'color'));

    

    $select->where('events.leadid = ?',$leadid);
    $rows = $this->fetchAll($select);
    //returns table record
    return $rows;
    
  }

  public function updateEvent(array $data){
   
    $id = $data['ID'];

    unset($data['ID']);
    if($data['horelevant'] == "true"){
      $data['horelevant'] = 1;
    }else{
      $data['horelevant'] = 0;
    }
    $where[] = "ID = '".$id."'";
      //print_r($data);die;
    return $this->update($data,$where);
  }


}





