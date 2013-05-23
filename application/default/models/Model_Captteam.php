<?php

class Model_Captteam {

    protected $_UserTable;
    protected $_SjobsTable;
    protected $_UserRoleTable;
    protected $_KvTable;
    protected $_ShiftsTable;
    protected $_EventsTable;
    protected $_LinksTable;
    protected $_MailtemplatesTable;

    public function fetchAddedStaff($ID) {
        $table = $this->getUserTable();

        $staffs = $table->fetchAddedStaff($ID);
        $returnstaffs = Array();
        foreach ($staffs as $staff) {
            //change last changed
            if ($staff['lastchanged'] != null) {
                $staff['lastchanged'] = date("d.m.y, H:i", $staff['lastchanged']) . " h";
            } else {
                $staff['lastchanged'] = "";
            }
            $returnstaffs[] = $staff;
        }


        return $returnstaffs;
    }

    public function saveStaff(array $data) {

        $table = $this->getUserTable();

        //create username
        $replacePairs = array('Ä' => 'AE', 'Ö' => 'OE', 'Ü' => 'UE', 'ä' => 'AE', 'ö' => 'OE', 'ü' => 'UE', 'ß' => 'SS');
        $i = 0;
        do {
            if ($i > strlen($data['fname'])) {
                $pos_usrname = substr($data['fname'], 0, $i) . $data['lname'] . rand(1000, 2000);
            } else {
                $pos_usrname = substr($data['fname'], 0, $i) . $data['lname'];
            }

            $pos_usrname = strtr(strtoupper($pos_usrname), $replacePairs);
            $i++;
        } while ($table->UsrnameExists($pos_usrname));

        $data['usrname'] = $pos_usrname;


        $res = $table->insert($data);
        if ($res == null) {
            return null;
        } else {
            $data['ID'] = $res;
            return $data;
        }
    }

    public function updateStaff(array $data) {

        $table = $this->getUserTable();


        return $table->updateStaff($data);
    }

    //check Mail-Adress
    public function mailexists($data) {

        $table = $this->getUserTable();

        return $table->mailexists($data);
    }

    //read Options for sjob
    public function getSJobOpts() {

        $table = $this->getSjobsTable();

        return $table->fetchAllSjobs()->toArray();
    }

    //read Options for KVs
    public function getKvs() {

        $table = $this->getKvTable();

        return $table->fetchAllKvs()->toArray();
    }

    public function getUserDataByID($ID) {

        $table = $this->getUserTable();

        return $table->getUserDataByID($ID);
    }

    public function getUserRoles() {

        $table = $this->getUserRoleTable();

        return $table->getAllRoles()->toArray();
    }

    public function deleteUser($ID) {

        $table = $this->getUserTable();

        return $table->deleteUser($ID);
    }

    public function getShiftsOfUser($id) {

        $table = $this->getShiftsTable();

        return $table->getShiftsOfUser($id);
    }

    public function getAllPosShifts($id, $clearfull) {
        $ShiftsTable = $this->getShiftsTable();
        $allShifts = $ShiftsTable->fetchAllShiftsUT()->toArray();

        $LinksTable = $this->getLinksTable();
        $allLinks = $LinksTable->getShiftsOfUserID($id)->toArray();
        $output = Array();

        foreach ($allShifts as $shift) {
            $added = false;
            $full = false;
            foreach ($allLinks as $link) {
                if ($shift['ID'] == $link['shiftid'])
                    $added = true;
            }


            if ($clearfull == "true") {
                $count = $LinksTable->countLinksForShift($shift['ID']);
                if ($shift['reqstaff'] == $count) {
                    $full = true;
                }
            }

            if (!$added && !$full)
                $output[] = $shift;
        }
        return $output;
    }

    public function getTimePosShifts($id, $clearfull) {
        $ShiftsTable = $this->getShiftsTable();
        $allShifts = $ShiftsTable->fetchAllShiftsUT()->toArray();

        $LinksTable = $this->getLinksTable();
        $allLinks = $LinksTable->getShiftsOfUserID($id)->toArray();

        $userShifts = Array();
        $posShifts = Array();


        foreach ($allShifts as $shift) {

            foreach ($allLinks as $link) {
                if ($shift['ID'] == $link['shiftid'])
                    $userShifts[] = $shift;
            }
        }

        foreach ($allShifts as $shift) {

            $shiftPossible = true;
            $full = false;


            foreach ($userShifts as $userShift) {

                if ($shift['end'] > $userShift['start'] && $shift['start'] < $userShift['end']
                        || $shift['start'] < $userShift['end'] && $shift['end'] > $userShift['end']) {
                    $shiftPossible = false;
                }
            }


            if ($clearfull == "true") {
                $count = $LinksTable->countLinksForShift($shift['ID']);
                if ($shift['reqstaff'] == $count) {
                    $full = true;
                }
            }

            if ($shiftPossible && !$full) {
                $posShifts[] = $shift;
            }
        }

        return $posShifts;
    }

    public function getTimeStrongPosShifts($id, $clearfull) {
        $ShiftsTable = $this->getShiftsTable();
        $allShifts = $ShiftsTable->fetchAllShiftsUT()->toArray();

        $LinksTable = $this->getLinksTable();
        $allLinks = $LinksTable->getShiftsOfUserID($id)->toArray();

        $userShifts = Array();
        $posShifts = Array();


        foreach ($allShifts as $shift) {

            foreach ($allLinks as $link) {
                if ($shift['ID'] == $link['shiftid'])
                    $userShifts[] = $shift;
            }
        }

        foreach ($allShifts as $shift) {

            $shiftPossible = true;
            $full = false;

            foreach ($userShifts as $userShift) {

                if ($shift['end'] >= $userShift['start'] && $shift['start'] <= $userShift['end']
                        || $shift['start'] <= $userShift['end'] && $shift['end'] >= $userShift['end']) {
                    $shiftPossible = false;
                }
            }


            if ($clearfull == "true") {
                $count = $LinksTable->countLinksForShift($shift['ID']);
                if ($shift['reqstaff'] == $count) {
                    $full = true;
                }
            }


            if ($shiftPossible && !$full) {
                $posShifts[] = $shift;
            }
        }
        return $posShifts;
    }

    public function getEventByID($id) {
        $table = $this->getEventsTable();
        return $table->getEventByID($id)->toArray();
    }


    //*** Methoden für Mail Templates ***//
    public function getMailTemplate($id){
        $table = $this->getMailtemplatesTable();
        return $table->fetchMailtemplate($id)->toArray();
    }


    //*** Tabellen abholen ***//
    public function getUserTable() {
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

    public function getUserRoleTable() {
        if (null === $this->_UserRoleTable) {
            // since the dbTable is not a library item but an application item,
            // we must require it to use it
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_Usr_Roles.php';

            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_UserRoleTable = new Db_Table_Usr_Roles(array('db' => $db));
        }
        return $this->_UserRoleTable;
    }

    public function getSjobsTable() {
        if (null === $this->_SjobsTable) {
            // since the dbTable is not a library item but an application item,
            // we must require it to use it
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_Sjobs.php';

            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_SjobsTable = new Db_Table_Sjobs(array('db' => $db));
        }
        return $this->_SjobsTable;
    }

    public function getKvTable() {
        if (null === $this->_KvTable) {
            // since the dbTable is not a library item but an application item,
            // we must require it to use it
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_Kv.php';

            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_KvTable = new Db_Table_Kv(array('db' => $db));
        }
        return $this->_KvTable;
    }

    public function getShiftsTable() {
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

    public function getEventsTable() {
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

    public function getLinksTable() {
        if (null === $this->_LinksTable) {
            // since the dbTable is not a library item but an application item,
            // we must require it to use it
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_Links.php';

            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_LinksTable = new Db_Table_Links(array('db' => $db));
        }
        return $this->_LinksTable;
    }

    public function getMailtemplatesTable(){
        if (null === $this->_MailtemplatesTable) {
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_Mail_Templates.php';
            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_MailtemplatesTable = new Db_Table_Mail_Templates(array('db' => $db));
        }

        return $this->_MailtemplatesTable;
    }

}