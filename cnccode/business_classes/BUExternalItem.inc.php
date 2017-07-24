<?php /**
 * External Item business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEExternalItem.inc.php");
require_once($cfg["path_dbe"] . "/DBEJExternalItem.inc.php");

class BUExternalItem extends Business
{
    public $dbeExternalItem;
    public $dbeJExternalItem;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeExternalItem = new DBEExternalItem($this);
        $this->dbeJExternalItem = new DBEJExternalItem($this);
    }

    function updateExternalItem(&$dsData)
    {
        $this->setMethodName('updateExternalItem');
        $this->updateDataaccessObject($dsData, $this->dbeExternalItem);
        return TRUE;
    }

    function getExternalItemByID($ID, &$dsResults)
    {
        $this->dbeExternalItem->setPKValue($ID);
        $this->dbeExternalItem->getRow();
        return ($this->getData($this->dbeExternalItem, $dsResults));
    }

    function getExternalItemsByCustomerID($customerID, &$dsResults)
    {
        $this->dbeJExternalItem->getRowsByCustomerID($customerID);
        return ($this->getData($this->dbeJExternalItem, $dsResults));
    }


    function deleteExternalItem($ID)
    {
        $this->setMethodName('deleteExternalItem');
        if ($this->canDelete($ID)) {
            return $this->dbeExternalItem->deleteRow($ID);
        } else {
            return FALSE;
        }
    }

    /**
     *    canDeleteExternalItem
     */
    function canDelete($ID)
    {
        return TRUE;
    }
}// End of class
?>