<?php

class Model_Basic {

    protected $_WishoptionsTable;
    protected $_Wopts_EventsTable;
    protected $_UserTable;
    protected $_KvTable;
    protected $_ParticipantsTable;
    protected $_ShiftsTable;

    /**
     * returns wishes of a user as a unsorted array
     *
     * @param integer $ID
     */
    public function getUserWishesByID($ID) {
        $table = $this->getUserTable();
        $wishes = null;
        $userdata = $table->getUserWishes($ID);
        if ($userdata['wish1'] != 0) {
            $wishes[] = array('ID' => $userdata['wish1'], 'name' => $userdata['wish1_name']);
        }
        if ($userdata['wish2'] != 0) {
            $wishes[] = array('ID' => $userdata['wish2'], 'name' => $userdata['wish2_name']);
        }
        if ($userdata['wish3'] != 0) {
            $wishes[] = array('ID' => $userdata['wish3'], 'name' => $userdata['wish3_name']);
        }

        return $wishes;
    }

    /**
     *
     * delete wish of a user
     *
     * @param integer $UserID
     * @param integer $WishID
     * @return integer count of deleted wishes (should be 1 or 0)
     */
    public function deleteUserWishByIDs($UserID, $WishID) {

        $table = $this->getUserTable();
        $wishdata = $table->getUserWishes($UserID);

        if ($wishdata['wish1'] == $WishID) {
            $resultdata['wish1'] = 0;
        } else {
            $resultdata['wish1'] = $wishdata['wish1'];
            if ($wishdata['wish2'] == $WishID) {
                $resultdata['wish2'] = 0;
            } else {
                $resultdata['wish2'] = $wishdata['wish2'];
                if ($wishdata['wish3'] == $WishID) {
                    $resultdata['wish3'] = 0;
                } else {
                    $resultdata['wish3'] = $wishdata['wish3'];

                    //@TODO: Fehler ausgebeben!
                }
            }
        }

        return $table->updateUserWishes($UserID, $resultdata);
    }

    public function addUserWishByIDs($UserID, $WishID) {

        $table = $this->getUserTable();
        $wishdata = $table->getUserWishes($UserID);

        // find a empty wish to save wish and check if this wish isn't there already
        $emptySlot = 0;
        $existsAlready = false;

        if ($wishdata['wish1'] == $WishID) {
            $existsAlready = true;
        } elseif ($wishdata['wish1'] == 0) {
            $emptySlot = 1;
        }

        if ($wishdata['wish2'] == $WishID) {
            $existsAlready = true;
        } elseif ($wishdata['wish2'] == 0) {
            $emptySlot = 2;
        }

        if ($wishdata['wish3'] == $WishID) {
            $existsAlready = true;
        } elseif ($wishdata['wish3'] == 0) {
            $emptySlot = 3;
        }

        if ($existsAlready == false && $emptySlot != 0) {
            $updatedata['wish' . $emptySlot] = $WishID;
            return $table->updateUserWishes($UserID, $updatedata);
        } else {
            //@TODO: Überlegen: Fehlermedlung zurück geben?
            return 0;
        }
    }

    public function getUserDataByID($ID) {
        $table = $this->getUserTable();

        return $table->getUserDataByID($ID);
    }

    public function updateStaff(array $data) {

        $table = $this->getUserTable();

        return $table->updateStaff($data);
    }

    //read Options for KVs
    public function getKvs() {

        $table = $this->getKvTable();

        return $table->fetchAllKvs()->toArray();
    }

    public function getWishoptions() {
        $res = Array();
        
        $wo_table = $this->getWishoptionsTable();
        $we_table = $this->getWopts_EventsTable();
        $shifts_table = $this->getShiftsTable();
        $wishoptions = $wo_table->fetchAllWishoptions()->toArray();
        //Bedarfszahlen durch Zuordnungen auslesen
        $wopts_events = $we_table->fetchAllWoptsEvents();
        $reqstaffOfEvent = $shifts_table->getEventsReqstaff();
        //@TODO: Bereits zugeordnete Mitarbeiter abziehen!
        foreach ($wishoptions as $wishoption) {
            if ($wishoption['reqstaff'] != 0) {
                $wishoption['demand'] = $wishoption['reqstaff'];
            } elseif (isset($wopts_events[$wishoption['ID']])) {
                $wishoption['demand'] = 0;
                foreach ($wopts_events[$wishoption['ID']] as $event) {
                    $wishoption['demand'] += $reqstaffOfEvent[$event];
                }
            } else {
                $wishoption['demand'] = 0;
            }
            $res[] = $wishoption;
        }

        return $res;
    }

    public function getParticipantDataByMA($ID) {
        $table = $this->getParticipantsTable();
        $participants = $table->getParticipantDataByMA($ID);

        return $participants;
    }

    public function updateParticipantByMA(array $data) {

        $table = $this->getParticipantsTable();
        return $table->updateParticipantByMA($data);
    }

    public function deleteParticipantsByMA($MA) {

        $table = $this->getParticipantsTable();


        return $table->deleteParticipantsByMA($MA);
    }

    public function saveParticipant(array $data) {

        $table = $this->getParticipantsTable();

        $res = $table->insert($data);
        if ($res == null) {
            return null;
        } else {
            $data['ID'] = $res;
            return $data;
        }
    }

    private function getWishoptionsTable() {
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

    private function getWopts_EventsTable() {
        if (null === $this->_Wopts_EventsTable) {
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_Wopts_Events.php';

            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_Wopts_EventsTable = new Db_Table_Wopts_Events(array('db' => $db));
        }
        return $this->_Wopts_EventsTable;
    }

    private function getUserTable() {
        if (null === $this->_UserTable) {
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_User.php';

            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_UserTable = new Db_Table_User(array('db' => $db));
        }
        return $this->_UserTable;
    }

    private function getKvTable() {
        if (null === $this->_KvTable) {
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_Kv.php';
            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_KvTable = new Db_Table_Kv(array('db' => $db));
        }
        return $this->_KvTable;
    }

    private function getParticipantsTable() {
        if (null === $this->_ParticipantsTable) {
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_Participants.php';

            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_ParticipantsTable = new Db_Table_Participants(array('db' => $db));
        }
        return $this->_ParticipantsTable;
    }

    private function getEventsTable() {
        if (null === $this->_EventsTable) {
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_Events.php';

            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_EventsTable = new Db_Table_Events(array('db' => $db));
        }
        return $this->_EventsTable;
    }

    private function getShiftsTable() {
        if (null === $this->_ShiftsTable) {
            require_once APPLICATION_PATH . '/models/DbTables/Db_Table_Shifts.php';

            $registry = Zend_Registry::getInstance();
            $db = $registry->db;
            $this->_ShiftsTable = new Db_Table_Shifts(array('db' => $db));
        }
        return $this->_ShiftsTable;
    }

}
