<?php /**
 * Call activity type business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBECallActType.inc.php");
require_once($cfg["path_dbe"] . "/DBECallActivity.inc.php");

class BUActivityType extends Business
{
    var $dbeCallActType = "";

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeCallActType = new DBECallActType($this);
        $this->dbeJCallActType = new DBEJCallActType($this);                // join to item table
    }

    function updateActivityType(&$dsData)
    {
        $this->setMethodName('updateActivityType');
        $this->updateDataaccessObject($dsData, $this->dbeCallActType);
        return TRUE;
    }

    function getActivityTypeByID($ID, &$dsResults)
    {
        $this->dbeJCallActType->setPKValue($ID);
        $this->dbeJCallActType->getRow();
        return ($this->getData($this->dbeJCallActType, $dsResults));
    }

    function getAllTypes(&$dsResults)
    {
        $this->dbeCallActType->getRows();
        return ($this->getData($this->dbeCallActType, $dsResults));
    }

    function getAllActiveTypes(&$dsResults)
    {
        $this->dbeCallActType->getActiveRows();
        return ($this->getData($this->dbeCallActType, $dsResults));
    }

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
     */
    function canDeleteActivityType($ID)
    {
        $dbeCallActivity = new DBECallActivity($this);
        // validate no activities of this type
        $dbeCallActivity->setValue('callActTypeID', $ID);
        if ($dbeCallActivity->countRowsByColumn('callActTypeID') < 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}// End of class
?>