<?php /**
 * Call further action business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBESector.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomer.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

class BUSector extends Business
{
    var $dbeSector = "";

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeSector = new DBESector($this);
    }

    function updateSector(&$dsData)
    {
        $this->setMethodName('updateSector');
        $this->updateDataAccessObject($dsData, $this->dbeSector);
        return TRUE;
    }

    function getSectorByID($ID, &$dsResults)
    {
        $this->dbeSector->setPKValue($ID);
        $this->dbeSector->getRow();
        return ($this->getData($this->dbeSector, $dsResults));
    }

    function getAll(&$dsResults)
    {
        $this->dbeSector->getRows('description');
        return ($this->getData($this->dbeSector, $dsResults));
    }

    function deleteSector($ID)
    {
        $this->setMethodName('deleteSector');
        if ($this->canDelete($ID)) {
            return $this->dbeSector->deleteRow($ID);
        } else {
            return FALSE;
        }
    }

    /**
     *    canDeleteSector
     * Only allowed if this further actionhas no future action rows at the moment
     */
    function canDelete($ID)
    {
        $dbeCustomer = new DBECustomer($this);
        // validate no activities of this type
        $dbeCustomer->setValue(DBECustomer::sectorID, $ID);
        if ($dbeCustomer->countRowsByColumn(DBECustomer::sectorID) < 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

}// End of class
?>