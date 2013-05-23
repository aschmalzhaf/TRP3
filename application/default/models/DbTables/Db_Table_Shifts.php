<?php


require_once 'Zend/Db/Table/Abstract.php';

class Db_Table_Shifts extends Zend_Db_Table_Abstract
{
    /**
     * The default table name
     */
    protected $_name = 'shifts';


    public function __construct($config)
    {
        parent::__construct($config);
        $this->getAdapter()->query("SET NAMES utf8");
    }


    public function insert(array $data)
    {
        unset($data['ID']);
        return parent::insert($data);
    }


    public function updateShift($data)
    {

        $id = $data['ID'];
        unset($data['ID']);

        $where[] = "ID = '" . $id . "'";

        return $this->update($data, $where);
    }


    public function getShiftsByEventid($id)
    {

        $select = $this->select();

        $select->where('shifts.eventid = ?', $id);
        $select->order('start ASC');
        $rows = $this->fetchAll($select);

        //returns table record
        return $rows;

    }


    public function getShiftByID($id)
    {

        $select = $this->select();

        $cols = Array('ID', 'name', 'start' => 'UNIX_TIMESTAMP(start)', 'end' => 'UNIX_TIMESTAMP(end)', 'reqstaff', 'note');
        $select->from(Array('shifts' => 'shifts'), $cols);

        $select->where('shifts.ID = ?', $id);
        $row = $this->fetchRow($select);

        //returns table record
        return $row;
    }


    public function fetchAllShifts()
    {
        $select = $this->select();
        $select->order('name ASC');
        $rows = $this->fetchAll($select);

        //returns table record
        return $rows;
    }

    public function getEventReqstaff()
    {
        $select = $this->select();
        $select->from($this->_name, Array('eventid' => 'eventid', 'reqstaff' => 'SUM(reqstaff)'));
        $select->group('eventid');
        return $this->fetchAll($select);
    }

    //fetch all shifts with UNIX-TIMESTAMP
    public function fetchAllShiftsUT()
    {
        $select = $this->select();
        $select->order('name ASC');
        $select->from($this->_name,
                      Array('ID' => 'ID', 'name' => 'name', 'eventid' => 'eventid', 'reqstaff' => 'reqstaff', 'start' => 'UNIX_TIMESTAMP(start)', 'end' => 'UNIX_TIMESTAMP(end)'));
        $rows = $this->fetchAll($select);

        //returns table record
        return $rows;
    }


    //fetch all shifts with UNIX-TIMESTAMP
    public function fetchAllShiftsTime()
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);

        $select->order('start ASC');

        $select->from($this->_name,
                      Array('ID' => 'ID', 'name' => 'name', 'eventid' => 'eventid', 'reqstaff' => 'reqstaff', 'start' => 'UNIX_TIMESTAMP(start)', 'end' => 'UNIX_TIMESTAMP(end)'));

        //join for event data
        $select->joinLeft('events', 'events.ID = shifts.eventid', Array('event_ID' => 'ID', 'event_name' => 'name'));


        $rows = $this->fetchAll($select);

        //returns table record
        return $rows;
    }


    public function delShift($id)
    {

        return $this->delete("ID = " . $id);
    }


    public function getShiftsOfUser($id)
    {

        $select = $this->select();
        $select->setIntegrityCheck(false);

        $select->from('links',
                      array('userid'));

        $select->where('links.userid = ?', $id);

        //join for shift data
        $select->joinLeft('shifts', 'shifts.ID = links.shiftid', Array('ID' => 'ID', 'name' => 'name', 'eventid' => 'eventid', 'note' => 'note', 'start' => 'UNIX_TIMESTAMP(start)', 'end' => 'UNIX_TIMESTAMP(end)'));

        $select->order("start ASC");

        $rows = $this->fetchAll($select);

        //returns table record
        return $rows->toArray();
    }

    public function getShiftsForHandout()
    {
        $select = $this->select();

        $select->setIntegrityCheck(false);
        $select->from("shifts", array("eventid", "start", "end"));
        $select->joinLeft("events","events.ID = shifts.eventID",array("events.name"));
        $select->joinLeft("locations", "events.locationid = locations.ID",array("location"=>"locations.name"));
        $select->where('events.horelevant = ?', 1);
        $select->order(array("eventid ASC","start ASC"));
        $rows = $this->fetchAll($select);

        //print_r($rows->toArray());die;

        return $rows->toArray();

    }

    public function getEventsReqstaff()
    {
        $result_array = Array();
        $select = $this->select();
        $select->from("shifts", array("eventid", "reqstaff" => "sum(reqstaff)"));
        $select->group("eventid");
        $rows = $this->fetchAll($select);
        foreach ($rows->toArray() as $dataset) {
            $result_array[$dataset['eventid']] = $dataset['reqstaff'];
        }
        // das Ergebnis hat die Form: eventid => reqstaff
        return $result_array;
    }
}