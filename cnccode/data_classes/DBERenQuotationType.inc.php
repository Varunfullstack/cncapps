<?php /*
* Item type table access
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBERenQuotationType extends DBCNCEntity
{
    const renQuotationTypeID = "renQuotationTypeID";
    const description = "description";
    const addInstallationCharge = "addInstallationCharge";

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
        $this->setTableName("renquotationtype");
        $this->addColumn(self::renQuotationTypeID, DA_ID, DA_NOT_NULL);
        $this->addColumn(self::description, DA_STRING, DA_NOT_NULL);
        $this->addColumn(self::addInstallationCharge, DA_YN, DA_NOT_NULL);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}
