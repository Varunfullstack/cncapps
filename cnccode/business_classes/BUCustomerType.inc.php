<?php /**
 * Call further action business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomerType.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomer.inc.php");

class BUCustomerType extends Business
{
    var $dbeCustomerType = "";

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeCustomerType = new DBECustomerType($this);
    }

    function updateCustomerType(&$dsData)
    {
        $this->setMethodName('updateCustomerType');
        $this->updateDataaccessObject($dsData, $this->dbeCustomerType);
        return TRUE;
    }

    function getCustomerTypeByID($ID, &$dsResults)
    {
        $this->dbeCustomerType->setPKValue($ID);
        $this->dbeCustomerType->getRow();
        return ($this->getData($this->dbeCustomerType, $dsResults));
    }

    function getAll(&$dsResults)
    {
        $this->dbeCustomerType->getRows('description');

        return ($this->getData($this->dbeCustomerType, $dsResults));
    }

    function deleteCustomerType($ID)
    {
        $this->setMethodName('deleteCustomerType');
        if ($this->canDelete($ID)) {
            return $this->dbeCustomerType->deleteRow($ID);
        } else {
            return FALSE;
        }
    }

    /**
     *    canDeleteCustomerType
     * Only allowed if this further actionhas no future action rows at the moment
     */
    function canDelete($ID)
    {
        $dbeCustomer = new DBECustomer($this);
        // validate no activities of this type
        $dbeCustomer->setValue(DBECustomer::CustomerTypeID, $ID);
        if ($dbeCustomer->countRowsByColumn(DBECustomer::CustomerTypeID) < 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}// End of class
?>