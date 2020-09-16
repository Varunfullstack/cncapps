<?php /*
* ExternalItem table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEExternalItem extends DBEntity
{
    const externalItemID = 'externalItemID';
    const itemTypeID = 'itemTypeID';
    const customerID = 'customerID';
    const description = 'description';
    const notes = 'notes';
    const licenceRenewalDate = 'licenceRenewalDate';


    /**
     * calls constructor()
     * @access public
     * @param void
     * @return void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("externalitem");
        $this->addColumn(self::externalItemID, DA_ID, DA_NOT_NULL);
        $this->addColumn(self::itemTypeID, DA_ID, DA_NOT_NULL);
        $this->addColumn(self::customerID, DA_ID, DA_NOT_NULL);
        $this->addColumn(self::description, DA_STRING, DA_NOT_NULL);
        $this->addColumn(self::notes, DA_MEMO, DA_ALLOW_NULL);
        $this->addColumn(self::licenceRenewalDate, DA_DATE, DA_ALLOW_NULL);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getRowsByCustomerID($customerID)
    {
        $this->setMethodName("getRowsByCustomerID");
        if ($customerID == '') {
            $this->raiseError('customerID not passed');
        }

        //TODO: sort this issue out!!
        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::customerID) . "='" . mysqli_real_escape_string(
                $this->db->link_id(),
                $customerID
            );

        $this->setQueryString($queryString);

        return ($this->getRows());
    }

}
