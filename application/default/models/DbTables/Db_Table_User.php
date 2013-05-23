<?php

/**
 * {0}
 *  
 * @author 001
 * @version 
 */

require_once 'Zend/Db/Table/Abstract.php';

class Db_Table_User extends Zend_Db_Table_Abstract
{
    /**
     * The default table name 
     */
  protected $_name = 'user';
  
  
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




  public function updateStaff(array $data){
    $id = $data['ID'];
    if(isset($data['pwd']) && $data['pwd'] == ""){
      unset($data['pwd']);
    }
    unset($data['ID']);

    $where[] = "ID = '".$id."'";
    return $this->update($data,$where);
  }


    //for login-check
  public function getUsrDataByUsrname($data){

    $select = $this->select();

    $select->where('usrname = ?',$data);

    $row = $this->fetchRow($select);

        //returns array or NULL
    if($row !== NULL){
      return $row->toArray();
    }else{
      return NULL;
    }
  }

  public function getGenericUserData($cols, $where, $sort, $range){
        //Desc
        //$cols -> Array of columns, which should be selected
        //$where -> Array of all where-cond.
        //$sort -> Array with col and direction to sort the data
        //$range -> array with lower and upper bound to select only a certain ammount of data

    $select = $this->select();
    $select->setIntegrityCheck(false);

        //COLUMNS - get join-cols
    $joincolnames = Array("role_name","sjob_name","wish1_name","wish2_name","wish3_name","kv_name","addedby_fname","addedby_lname");
    foreach($joincolnames as $joincolname){
      $key = array_search($joincolname,$cols);

      $joincols[] = $cols[$key];
      if($key!=FALSE)
        unset($cols[$key]);
    }

    $select->from(Array('user'=>'user'),$cols); 


        //JOINS
        //join for role-name
    if(($key = array_search("role_name",$joincols))!= false){
      $select->joinLeft('usr_roles','user.role = usr_roles.ID',Array('role_name'=>'name'));
      unset($cols[$key]);
    }

        //join for sjob name
    if(($key = array_search("sjob_name",$joincols))!=false){
      $select->joinLeft(Array('sjobs1'=>'sjobs'),'sjobs1.ID = user.sjob',Array('sjob_name'=>'name'));
      unset($cols[$key]);
    }

        //join for prios
    if(($key = array_search("wish1_name",$joincols))!=false){
      $select->joinLeft(Array('wishoptions2'=>'wishoptions'),'user.wish1 = wishoptions2.ID',Array('wish1_name'=>'name'));
      unset($cols[$key]);
    }
    if(($key = array_search("wish2_name",$joincols))!=false){
      $select->joinLeft(Array('wishoptions3'=>'wishoptions'),'user.wish2 = wishoptions3.ID',Array('wish2_name'=>'name'));
      unset($cols[$key]);
    }
    if(($key = array_search("wish3_name",$joincols))!=false){
      $select->joinLeft(Array('wishoptions4'=>'wishoptions'),'user.wish3 = wishoptions4.ID',Array('wish3_name'=>'name'));
      unset($cols[$key]);
    }

        //join for kv
    if(($key = array_search("kv_name",$joincols))!=false){
      $select->joinLeft('kv','user.kv = kv.ID',Array('kv_name'=>'name'));
      unset($cols[$key]);
    }

        //join for addedby name
    if(($key = array_search("addedby_lname",$joincols))!=false){
      $select->joinLeft(Array('addedby1'=>'user'),'user.addedby = addedby1.ID',Array('addedby_lname'=>'lname'));
      unset($cols[$key]);
    }
    if(($key = array_search("addedby_fname",$joincols))!=false){
      $select->joinLeft(Array('addedby2'=>'user'),'user.addedby = addedby2.ID',Array('addedby_fname'=>'fname'));
      unset($cols[$key]);
    }

        //WHERES
    foreach($where as $wherecond){
      reset($wherecond);
            //$select->where(key($wherecond)." = ?",current($wherecond));
      $select->where('user.'.key($wherecond).' LIKE ' . $this->_db->quote(current($wherecond)));
    }


        //SORT
    $rows = $this->fetchAll($select);
    $res['count'] = $rows->count();
    $res['data'] = $rows->toArray();
    $res['sql'] = $select->__toString();

    return $res;

  }

  //@TODO: Feherl! Verwechselt sjobs und whishlistoption?
  public function getUserDataByID($ID)
  {
    $select = $this->select();
    $select->setIntegrityCheck(false);
    $select->from(Array('user'=>'user'),
      array("ID", "fname", "lname", "mphone","phone", 
        "birthday", "intnote", "usrnote", "exp", "city", "street", "kv", "email", "addedby", 
        "usrname","pwd", "role","sjob","bus", "climbing", "lifeguard", "medic", "blocked",
        "wish1", "wish2", "wish3","lastchanged"=>"UNIX_TIMESTAMP(lastchanged)","locked")); 

        //join for role-name
    $select->joinLeft('usr_roles','usr_roles.ID = user.role',Array('role_name'=>'name'));

        //join for sjob name
    $select->joinLeft(Array('sjobs1'=>'sjobs'),'sjobs1.ID = user.sjob',Array('sjob_name'=>'name'));

        //join for prios
    $select->joinLeft(Array('woptions1'=>'wishoptions'),'woptions1.ID = user.wish1',Array('wish1_name'=>'name'));
    $select->joinLeft(Array('woptions2'=>'wishoptions'),'woptions2.ID = user.wish2',Array('wish2_name'=>'name'));
    $select->joinLeft(Array('woptions3'=>'wishoptions'),'woptions3.ID = user.wish3',Array('wish3_name'=>'name'));

        //join for kv
    $select->joinLeft('kv','kv.ID = user.kv',Array('kv_name'=>'name'));

    $select->where('user.ID = ?',$ID);

    $row = $this->fetchRow($select);

        //returns table record
    if($row != NULL){
      return $row->toArray();
    }else{
      return NULL;
    }

  }


    //delete User
  public function deleteUser($ID){

    return $this->delete("ID = $ID");

  }

  public function mailexists($mailadress){

    $select = $this->select();

    $select->where('email = ?',$mailadress);

    $res = $this->fetchRow($select);

    if(count($res) == 0){
      return FALSE;
    }else{
      return TRUE;
    }
  }


  public function fetchAddedStaff($ID)
  {
    $select = $this->select();
    $select->setIntegrityCheck(false);

    $select->from('user',
      array("ID", "fname", "lname", "birthday", 
        "mphone", "email", "addedby", "kv", "city", "role", "intnote","sjob",
        "lastchanged"=>"UNIX_TIMESTAMP(lastchanged)")); 
    $select->where('addedby = ?',$ID);

        //join for role-name
    $select->joinLeft('usr_roles','usr_roles.ID = user.role',Array('role_name'=>'name'));

        //join for sjob name
    $select->joinLeft('sjobs','sjobs.ID = user.sjob',Array('sjob_name'=>'name'));

        //join for kv
    $select->joinLeft('kv','kv.ID = user.kv',Array('kv_name'=>'name'));

    $rows = $this->fetchAll($select);

        //returns table record

    return $rows->toArray();

  }


  public function UsrnameExists($usrname){

    $select = $this->select();
    $select->where('usrname = ?',$usrname);

    if($this->fetchRow($select) == null){
      return false;
    }else{
      return true;
    }
  }

  public function getUserWishes($ID){

    $select = $this->select();
    $select->setIntegrityCheck(false);

    $select->from(array('user' => 'user'),
        array("wish1", "wish2", "wish3")); 

        //join for prios
    $select->joinLeft(Array('woptions2'=>'wishoptions'),'woptions2.ID = user.wish1',Array('wish1_name'=>'name'));
    $select->joinLeft(Array('woptions3'=>'wishoptions'),'woptions3.ID = user.wish2',Array('wish2_name'=>'name'));
    $select->joinLeft(Array('woptions4'=>'wishoptions'),'woptions4.ID = user.wish3',Array('wish3_name'=>'name'));

    $select->where('user.ID = ?',$ID);

    return $this->fetchRow($select)->toArray();



  }

    //@TODO: Möglicherweise nicht notwendig (UpdateStaff...?)
  public function updateUserWishes($UserID, $wishes){

    $where[] = "ID = '".$UserID."'";



    return $this->update($wishes,$where);

  }




  public function getAllUsers(){


    $select = $this->select();
    $select->setIntegrityCheck(false);

        $select->from('user');  //Alle Userdaten holen
        $select->order(Array('fname ASC', 'lname ASC'));

        
        //join for kv
        $select->joinLeft('kv','kv.ID = user.kv',Array('kv_name'=>'name'));
        
        $rows = $this->fetchAll($select);

        //returns table record
        return $rows;

        
        
    }
    
    
  }
