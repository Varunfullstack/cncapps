<?php /**
 * Staff Availability business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEStaffAvailable.inc.php");
require_once($cfg["path_dbe"] . "/DBEJStaffAvailable.inc.php");

class BUStaffAvailable extends Business
{
    var $dbeStaffAvailable = "";

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeStaffAvailable = new DBEStaffAvailable($this);
    }

    function updateStaffAvailable($staffAvailable)
    {
        $this->setMethodName('updateStaffAvailable');

        $dbeStaffAvailable = new DBEStaffAvailable($this);

        foreach ($staffAvailable as $key => $value) {

            $dbeStaffAvailable->getRow($value['staffAvailableID']);
            $dbeStaffAvailable->setValue('am', $value['am']);
            $dbeStaffAvailable->setValue('pm', $value['pm']);
            $dbeStaffAvailable->updateRow();

        }

        return TRUE;
    }

    /**
     * Get all users
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getAllStaffAvailable(&$dsResults)
    {
        $this->setMethodName('getAllStaffAvailable');
        $dbeStaffAvailable = new DBEJStaffAvailable($this);
        $dbeStaffAvailable->getRowsToday();
        return ($this->getData($dbeStaffAvailable, $dsResults));
    }

    /**
     * Get one record
     * @parameter integer $staffAvailableID user
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getStaffAvailableByID($staffAvailableID, &$dsResults)
    {
        $this->setMethodName('getStaffAvailableByID');
        $dbeStaffAvailable = new DBEStaffAvailable($this);
        return ($this->getDatasetByPK($staffAvailableID, $dbeStaffAvailable, $dsResults));
    }

    /**
     *    Create a set of staff available records for today's date
     */
    function createRecordsForToday()
    {

        $dbeUser = new DBEUser($this);
        $dbeStaffAvailable = new DBEStaffAvailable($this);

        $dbeUser->getRowsInGroup(PHPLIB_PERM_TECHNICAL);

        while ($dbeUser->fetchNext()) {

            if (!$dbeStaffAvailable->getUserRecordForToday($dbeUser->getValue('userID'))) {
                $dbeStaffAvailable->setValue('userID', $dbeUser->getValue('userID'));
                $dbeStaffAvailable->setValue('date', date(CONFIG_MYSQL_DATE));
                $dbeStaffAvailable->setValue('am', 0.5);
                $dbeStaffAvailable->setValue('pm', 0.5);

                $dbeStaffAvailable->insertRow();
            }
        }
    }
}// End of class
?>