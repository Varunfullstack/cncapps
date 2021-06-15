<?php /**
 * Call activity type business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Data\DBConnect;

global $cfg;
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBECallActType.inc.php");
require_once($cfg["path_dbe"] . "/DBEJCallActType.php");
require_once($cfg["path_dbe"] . "/DBECallActivity.inc.php");

class BUActivityType extends Business
{
    /** @var DBECallActType|DataSet */
    public $dbeCallActType;
    /**
     * @var DBEJCallActType
     */
    public $dbeJCallActType;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeCallActType  = new DBECallActType($this);
        $this->dbeJCallActType = new DBEJCallActType($this);                // join to item table
    }

    /**
     * @param $dsData
     * @return bool
     */
    function updateActivityType(&$dsData)
    {
        $this->setMethodName('updateActivityType');
        $this->updateDataAccessObject($dsData, $this->dbeCallActType);
        return TRUE;
    }

    /**
     * @param $ID
     * @param $dsResults
     * @return bool
     */
    function getActivityTypeByID($ID, &$dsResults)
    {
        $this->dbeJCallActType->setPKValue($ID);
        $this->dbeJCallActType->getRow();
        return ($this->getData($this->dbeJCallActType, $dsResults));
    }

    /**
     * @param $dsResults
     * @return bool
     */
    function getAllTypes(&$dsResults)
    {
        $this->dbeCallActType->getRows();
        return ($this->getData($this->dbeCallActType, $dsResults));
    }

    /**
     * @param $ID
     * @return bool
     */
    function deleteActivityType($ID)
    {
        $this->setMethodName('deleteActivityType');
        if ($this->canDeleteActivityType($ID)) {
            return $this->dbeCallActType->deleteRow($ID);
        } else {
            return FALSE;
        }
    }

    /**
     *    canDeleteActivityType
     * Only allowed if type has no activities
     * @param $ID
     * @return bool
     */
    function canDeleteActivityType($ID)
    {
        $dbeCallActivity = new DBECallActivity($this);
        // validate no activities of this type
        $dbeCallActivity->setValue(DBECallactivity::callActTypeID, $ID);
        return $dbeCallActivity->countRowsByColumn(DBECallactivity::callActTypeID) < 1;
    }

    /**
     * @param $Id int
     * @return boolean
     * check if activity type in expenses or not
     */
    public static function hasExpenses($Id)
    {
        if (!isset($Id)) return false;
        $query  = "SELECT   COUNT(*) total FROM expensetypeactivityavailability WHERE activityTypeID=$Id";
        $result = DBConnect::fetchOne($query);
        if ($result["total"] > 0) return true; else return false;
    }
}
