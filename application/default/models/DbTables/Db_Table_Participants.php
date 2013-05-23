<?php

/**
 * {0}
 *  
 * @author 001
 * @version 
 */
require_once 'Zend/Db/Table/Abstract.php';

class Db_Table_Participants extends Zend_Db_Table_Abstract {

    /**
     * The default table name 
     */
    protected $_name = 'participants';

    public function __construct($config) {
        parent::__construct($config);
        $this->getAdapter()->query("SET NAMES utf8");
    }

    public function insert(array $data) {

        function trim_value(&$value)
        {
            $value = trim($value);
        }
        array_walk_recursive($data, 'trim_value');
        return parent::insert($data);
    }

    public function updateParticipants(array $data) {

        $id = $data['ID'];

        unset($data['ID']);

        $where[] = "ID = '" . $id . "'";

        return $this->update($data, $where);
    }

    public function updateParticipantByMA(array $data) {

        $MA = $data['MA'];

        unset($data['MA']);

        $where[] = "MA = '" . $MA . "'";

        return $this->update($data, $where);
    }

    public function getGenericParticipantsData($cols, $where, $sort, $range) {
        //Desc
        //$cols -> Array of columns, which should be selected
        //$where -> Array of all where-cond.
        //$sort -> Array with col and direction to sort the data
        //$range -> array with lower and upper bound to select only a certain ammount of data

        $select = $this->select();
        //$select->setIntegrityCheck(false);
        //COLUMNS - get join-cols
        $joincolnames = Array("Name", "Vorname", "anzahl", "plz", "Ort", "Strasse", "tel", "geb", "art", "schlafen", "samstag", "unterschrift", "kommentar", "mail", "lastchanged", "datum", "addedby", "MA");

        //JOINS
        foreach ($joincolnames as $joincolname) {
            $key = array_search($joincolname, $cols);

            $joincols[] = $cols[$key];
            //if($key!=FALSE)
            //	unset($cols[$key]);
        }

        $select->from(Array('participants' => 'participants'), $cols);

        //WHERES
        foreach ($where as $wherecond) {
            reset($wherecond);
            //$select->where(key($wherecond)." = ?",current($wherecond));
            if (key($wherecond) == "anzahl")
                $select->where(key($wherecond) . ' > ' . $this->_db->quote(current($wherecond)));
            else
                $select->where(key($wherecond) . ' LIKE ' . $this->_db->quote(current($wherecond)));
        }

        //Sort
        $select->order($sort);


        $rows = $this->fetchAll($select);
        $res['count'] = $rows->count();
        $res['data'] = $rows->toArray();
        $res['sql'] = $select->__toString();

        return $res;
    }

    public function getParticipantsDataByID($ID) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(Array('participants' => 'participants'), array("ID", "Name", "Vorname", "anzahl", "plz", "Ort", "Strasse", "tel", "geb", "art", "schlafen", "samstag", "unterschrift", "kommentar", "mail", "lastchanged", "datum", "addedby", "MA"));


        $select->where('participants.ID = ?', $ID);

        $row = $this->fetchRow($select);

        //returns table record
        if ($row != NULL) {
            return $row->toArray();
        } else {
            return NULL;
        }
    }

    public function getParticipantDataByMA($ID) {
        $select = $this->select(); 
        //$select->setIntegrityCheck(false);
        $select->from(Array('participants' => 'participants'), array("ID", "Name", "Vorname", "anzahl", "plz", "Ort", "Strasse", "tel", "geb", "art", "schlafen", "samstag", "unterschrift", "kommentar", "mail", "lastchanged", "datum", "addedby", "MA"));
        

        $select->where('participants.MA = ?', $ID);

        $row = $this->fetchRow($select);

        //Gib Eintrag als Array zurück
        if ($row != NULL) {
            return $row->toArray();
        } else {
            return NULL;
        }
    }

    //delete User
    public function deleteParticipants($ID) {

        return $this->delete("ID = $ID");
    }

    public function deleteParticipantsByMA($MA) {

        return $this->delete("MA = $MA");
    }

    public function fetchsignedParticipants($unterschrift) {
        $select = $this->select();
        $select->setIntegrityCheck(false);

        $select->from('Participants', array("ID", "Name", "Vorname", "anzahl", "plz", "Ort", "Strasse", "tel", "geb", "art", "schlafen", "samstag", "unterschrift", "kommentar", "mail", "lastchanged", "datum", "addedby", "MA"));
        $select->where('unterschrift = ?', $unterschrift);

        $rows = $this->fetchAll($select);

        //returns table record
        return $rows->toArray();
    }


    public function getAllParticipants() {


        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from('participants');  //Alle Userdaten holen
        $select->order(Array('Vorname ASC', 'Name ASC'));

        //JOINS
        //join for MA
        $select->joinLeft('user', 'participants.MA = user.ID', Array('Name1' => 'lname', 'Vorname1' => 'fname', 'Ort1' => 'city', 'strasse1' => 'street', 'tel1' => 'phone', 'geb1' => 'birthday', 'mail1' => 'email'));




        $rows = $this->fetchAll($select);

        //returns table record
        return $rows;
    }

    public function getAllParticipantsWhere($where) {


        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from('participants');  //Alle Userdaten holen
        $select->order(Array('Vorname ASC', 'Name ASC'));

        //JOINS
        //join for MA
        $select->joinLeft('user', 'participants.MA = user.ID', Array('Name1' => 'lname', 'Vorname1' => 'fname', 'Ort1' => 'city', 'strasse1' => 'street', 'tel1' => 'phone', 'geb1' => 'birthday', 'mail1' => 'email', 'geb1' => 'birthday'));


        foreach ($where as $wherecond) {
            reset($wherecond);
            //$select->where(key($wherecond)." = ?",current($wherecond));
            $select->where(key($wherecond) . ' LIKE ' . $this->_db->quote(current($wherecond)));
        }

        $rows = $this->fetchAll($select);

        //returns table record
        return $rows;
    }

}
