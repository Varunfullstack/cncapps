<?php /*
* ExternalItem table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBEExternalItem.inc.php");

class DBEJExternalItem extends DBEExternalItem
{
    const itemTypeDescription = 'itemTypeDescription';
    const itemTypeId = 'itemTypeId';

    /**
     * calls constructor()
     * @access public
     * @param void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setAddColumnsOn();
        $this->addColumn(self::itemTypeDescription, DA_STRING, DA_ALLOW_NULL, 'itemtype.ity_desc');
        $this->addColumn(
            self::itemTypeId,
            DA_INTEGER,
            DA_ALLOW_NULL,
            'externalitem.itemTypeID'
        );
        $this->setAddColumnsOff();
    }

    function getRowsByCustomerID($customerID)
    {
        $this->setMethodName("getRowsByCustomerID");
        if ($customerID == '') {
            $this->raiseError('customerID not passed');
        }

        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN itemtype ON itemtype.ity_itemtypeno = externalitem.itemTypeID
			WHERE " . $this->getDBColumnName(self::customerID) . "='" . mysqli_real_escape_string(
                $this->db->link_id(),
                $customerID
            ) . "'";

        $this->setQueryString($queryString);

        return (parent::getRows());
    }

}
