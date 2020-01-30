<?php /*
* Item type table access
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBEItemType extends DBCNCEntity
{

    const itemTypeID = "itemTypeID";
    const description = "description";
    const stockcat = "stockcat";
    const reoccurring = "reoccurring";
    const active = "active";
    const showInCustomerReview = "showInCustomerReview";


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
            self::reoccurring,
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
        $this->addColumn(
            self::showInCustomerReview,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            true
        );

        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getCustomerReviewRows()
    {
        $statement =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() . " where " . $this->getDBColumnName(
                self::active
            ) . " and " . $this->getDBColumnName(self::showInCustomerReview) . " order by " . $this->getDBColumnName(
                self::description
            );
        $this->setQueryString($statement);
        $ret = (parent::getRows());
    }
}

?>