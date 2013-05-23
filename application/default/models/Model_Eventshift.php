<?php

class Model_Eventshift
{

    protected $_EventsTable;
    protected $_ShiftsTable;
    protected $_UsersTable;
    protected $_LocationsTable;
    protected $_LinksTable;


    public function insertEvent($data)
    {

        $table = $this->getEventsTable();

        return $table->insert($data);
    }


    public function insertShift($data)
    {
        $table = $this->getShiftsTable();

        return $table->insert($data);
    }


    public function updateEvent(array $data)
    {
        $table = $this->getEventsTable();
        return $table->updateEvent($data);
    }


    public function updateShift(array $data)
    {
        $table = $this->getShiftsTable();

        return $table->updateShift($data);
    }


    public function getAllEvents()
    {
        $table = $this->getEventsTable();

        return $table->getAllEvents()->toArray();
    }


    public function getAllShiftsTime()
    {
        $table = $this->getShiftsTable();
        return $table->fetchAllShiftsTime()->toArray();
    }


    public function getShiftsByEventid($id)
    {
        $table = $this->getShiftsTable();
        return $table->getShiftsByEventid($id)->toArray();
    }


    public function countLinksForShift($id)
    {
        $table = $this->getLinksTable();

        return $table->countLinksForShift($id);
    }


    public function insertLink($data)
    {
        $table = $this->getLinksTable();
        return $table->insert($data);
    }

    public function delLink($data)
    {
        $table = $this->getLinksTable();
        return $table->deleteLink($data);
    }

    public function getAllEventsShifts()
    {

        $table = $this->getEventsTable();

        return $table->getAllEventsShifts();
    }


    public function getEventByID($id)
    {
        $table = $this->getEventsTable();

        return $table->getEventByID($id)->toArray();
    }


    public function getShiftByID($id)
    {
        $table = $this->getShiftsTable();

        return $table->getShiftByID($id)->toArray();
    }

    public function getEventsByleadid($leadid)
    {
        $table = $this->getEventsTable();

        return $table->getEventsByleadid($leadid)->toArray();
    }


    public function checkTimeForUser($userID, $shiftID, $start, $end)
    {

        $ShiftsTable = $this->getShiftsTable();
        $userShifts = $ShiftsTable->getShiftsOfUser($userID);
        $possible = true;

        foreach ($userShifts as $userShift) {
            if ($userShift['ID'] != $shiftID) {
                if (($start > $userShift['start'] && $start < $userShift['end'])
                    || ($end > $userShift['start'] && $end < $userShift['end'])
                )
                    $possible = false;
            }
        }
        return $possible;
    }


    public function getTimePosUsers($id, $remfull = "false", $onlyempty = "false")
    {

        $posUsers = $this->getAllPosUsers($id, $remfull, $onlyempty);
        $shift = $this->getShiftByID($id);

        $ShiftsTable = $this->getShiftsTable();

        foreach ($posUsers as $posUser) {
            $userShifts = $ShiftsTable->getShiftsOfUser($posUser['ID']);
            $possible = true;

            foreach ($userShifts as $userShift) {
                if ($shift['start'] < $userShift['end'] && $shift['end'] > $userShift['start'])
                    $possible = false;
            }

            if ($possible)
                $output[] = $posUser;
        }

        return $output;
    }


    public function getAllPosUsers($id, $remfull = "false", $onlyempty = "false")
    {

        $UsersTable = $this->getUsersTable();
        $allUsers = $UsersTable->getAllUsers()->toArray();

        $LinksTable = $this->getLinksTable();
        $allLinks = $LinksTable->getUserIDsOfShift($id)->toArray();
        $output = Array();

        foreach ($allUsers as $user) {
            $added = false;
            $full = false;
            foreach ($allLinks as $link) {
                if ($user['ID'] == $link['userid'])
                    $added = true;
            }

            if ($remfull == "true") {
                if ($user['locked'] == 1)
                    $full = true;
            }

            if (!$added && !$full)
                $output[] = $user;
        }
        return $output;
    }


    public function getStrongTimePosUsers($id, $remfull = "false", $onlyempty = "false")
    {

        $posUsers = $this->getAllPosUsers($id, $remfull, $onlyempty);
        $shift = $this->getShiftByID($id);

        $ShiftsTable = $this->getShiftsTable();

        foreach ($posUsers as $posUser) {
            $userShifts = $ShiftsTable->getShiftsOfUser($posUser['ID']);
            $possible = true;

            foreach ($userShifts as $userShift) {
                if ($shift['start'] <= $userShift['end'] && $shift['end'] >= $userShift['start'])
                    $possible = false;
            }

            if ($possible)
                $output[] = $posUser;
        }

        return $output;
    }


    public function getAllUsers()
    {

        $UsersTable = $this->getUsersTable();

        return $UsersTable->getAllUsers()->toArray();
    }


    public function getAllLocations()
    {
        $table = $this->getLocationsTable();

        return $table->getAllLocations()->toArray();
    }


    public function getUsersOfShift($id)
    {

        $table = $this->getLinksTable();
        return $table->getUsersOfShift($id)->toArray();
    }


    public function delShift($id)
    {

        //delete all links
        $linksTable = $this->getLinksTable();

        $linksTable->deleteLink(Array('shiftid' => $id));

        $shiftsTable = $this->getShiftsTable();

        return $shiftsTable->delShift($id);
    }


    public function delEvent($id)
    {

        //delete shifts and assign users
        $shifts = $this->getShiftsByEventid($id);
        foreach ($shifts as $shift) {
            $this->delShift($shift['ID']);
        }

        $EventsTable = $this->getEventsTable();

        return $EventsTable->delEvent($id);
    }


    public function getShiftsOfUser($id)
    {

        $table = $this->getShiftsTable();

        return $table->getShiftsOfUser($id);
    }

    //@todo
    public function getPosShiftsOfUser($id)
    {

        $table = $this->getShiftsTable();

        return $table->getShiftsOfUser($id);
    }


    public function getUserData($id)
    {

        $table = $this->getUsersTable();

        return $table->getUserDataByID($id);
    }


    public function getHandoutData()
    {
        $table = $this->getShiftsTable();
        $shifts = $table->getShiftsForHandout();

        //Loop für event
        $currentEvent = 0;

        foreach ($shifts as $shift) {
            $sortedSet[$shift['eventid']][] = $shift;
        }

        foreach ($sortedSet as $eventGrp) {
            unset($resShift);
            foreach ($eventGrp as $sshift) {

                if (!isset($resShift)) {
                    $resShift = $sshift;
                }

                if ($resShift['end'] >= $sshift['start']) {
                    //echo $resShift['name'] . " " . $resShift['end'] . "<br>";
                    //echo "sShiftEnde " . $sshift['start'] . "<br>";
                    //echo "<hr>";
                    $resShift['end'] = $sshift['end'];
                } else {
                    $result[] = $resShift;
                    $resShift = $sshift;
                }

            }
            $result[] = $resShift;
        }
        /*
        foreach($result as $res){
                    print_r($res);
            echo "<hr>";
            echo "<br><br>";
        */
    return $result;
    }

protected function getEventsTable()
{
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


protected
function getShiftsTable()
{
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


protected
function getUsersTable()
{
    if (null === $this->_UsersTable) {
        // since the dbTable is not a library item but an application item,
        // we must require it to use it
        require_once APPLICATION_PATH . '/models/DbTables/Db_Table_User.php';

        $registry = Zend_Registry::getInstance();
        $db = $registry->db;
        $this->_UsersTable = new Db_Table_User(array('db' => $db));
    }
    return $this->_UsersTable;
}


protected
function getLocationsTable()
{
    if (null === $this->_LocationsTable) {
        // since the dbTable is not a library item but an application item,
        // we must require it to use it
        require_once APPLICATION_PATH . '/models/DbTables/Db_Table_Locations.php';

        $registry = Zend_Registry::getInstance();
        $db = $registry->db;
        $this->_LocationsTable = new Db_Table_Locations(array('db' => $db));
    }
    return $this->_LocationsTable;
}


protected
function getLinksTable()
{
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

}
