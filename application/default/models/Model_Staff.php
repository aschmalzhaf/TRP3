<?php

class Model_Staff{

    protected $_UserTable;
    
    
    
    public function getGenericStaffData($cols, $where, $sort, $range = Array(0, 0)){

        $table = $this->getUserTable();
        
        //Umsetzen des Suchparameters "wishes" auf die tatsächlichen Spalten wish1, wish2, wish3
        if(($id = array_search('wishes',$cols)) != null){
            unset($cols[$id]);
            $cols[] = 'wish1';
            $cols[] = 'wish2';
            $cols[] = 'wish3';
        }
        
        //Umsetzen der Wunschnamen (siehe oben)
        if(($id = array_search('wishes_names',$cols)) != null){
            unset($cols[$id]);
            $cols[] = 'wish1_name';
            $cols[] = 'wish2_name';
            $cols[] = 'wish3_name';
        }
        
        //Platzhalter (Asterisk/star = *) ersetzen und Whitespaces wegtrimmen
        function trim_and_replace_star(&$value)
        {
            $value = trim($value);
            $value = str_replace("%","",$value);
            $value = str_replace("*","%",$value);
        }
        array_walk_recursive($where, 'trim_and_replace_star');

        $genUserData = $table->getGenericUserData($cols, $where, $sort, $range);
        
        $genUsers = $genUserData['data'];
        
        $genUsersOut = Array();
        
        foreach($genUsers as $genUser){
            if(isset($genUser['birthday'])){
                $genUser['birthday'] = substr($genUser['birthday'],8,2).".".substr($genUser['birthday'],5,2).".".substr($genUser['birthday'],0,4);
            }
            $genUser['wishes'] = "";
            if(isset($genUser['wish1'])||isset($genUser['wish2'])||isset($genUser['wish3']))
                $genUser['wishes'] = $genUser['wish1']." ".$genUser['wish2']." ".$genUser['wish3'];
            
            $genUser['wishes_names'] = "";
            if(isset($genUser['wish1_name'])||isset($genUser['wish2_name'])||isset($genUser['wish3_name']))
                $genUser['wishes_names'] = $genUser['wish1_name']." ".$genUser['wish2_name']." ".$genUser['wish3_name'];
            
            $genUsersOut[] = $genUser;
        }
        
        $genUserData['data'] = $genUsersOut;
        return $genUserData;
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
    
    
}