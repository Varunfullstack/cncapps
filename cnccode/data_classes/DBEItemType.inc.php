<?php /*
* Item type table access
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBEItemType extends DBCNCEntity
{

    const itemTypeID = "itemTypeID";
    const description = "description";
    const stockcat = "stockcat";
    const reocurring = "reocurring";
    const active = "active";


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
        $this->setTableName("itemtype");
        $this->addColumn(
            self::itemTypeID,
            DA_ID,
            DA_NOT_NULL,
            "ity_itemtypeno"
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL,
            "ity_desc"
        );
        $this->addColumn(
            self::stockcat,
            DA_STRING,
            DA_NOT_NULL,
            "ity_stockcat"
        );
        $this->addColumn(
            self::reocurring,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            false
        );
        $this->addColumn(
            self::active,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            true
        );

        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>