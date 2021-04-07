<?php /*
* Item type table access
* @authors Karim Ahmed
* @access public
*/
global $cfg;

use CNCLTD\SortableDBE;

require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBEItemType extends DBCNCEntity
{

    use SortableDBE;

    const itemTypeID             = "itemTypeID";
    const description            = "description";
    const stockcat               = "stockcat";
    const reoccurring            = "reoccurring";
    const active                 = "active";
    const showInCustomerReview   = "showInCustomerReview";
    const sortOrder              = "sortOrder";
    const allowGlobalPriceUpdate = "allowGlobalPriceUpdate";
    const showStockLevels        = "showStockLevels";

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
        $this->addColumn(
            self::sortOrder,
            DA_FLOAT,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::allowGlobalPriceUpdate,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            false
        );
        $this->addColumn(
            self::showStockLevels,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            false
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getCustomerReviewRows($arbitrarySort = false)
    {
        $statement = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " where " . $this->getDBColumnName(
                self::active
            ) . " and " . $this->getDBColumnName(self::showInCustomerReview);
        if ($arbitrarySort) {
            $statement .= " order by sortOrder";
        } else {
            $statement .= " order by " . $this->getDBColumnName(self::description);
        }
        $this->setQueryString($statement);
        $ret = (parent::getRows());
    }

    function getRowsByDescription($description)
    {
        global $db;
        $escapedDescription = mysqli_real_escape_string($db->link_id(), $description);
        $statement          = "SELECT {$this->getDBColumnNamesAsString()} as distinctDescription  FROM {$this->getTableName()} where  {$this->getDBColumnName(self::description)} like '%{$escapedDescription}%' order by {$this->getDBColumnName(self::description)}";
        $this->setQueryString($statement);
        $ret = (parent::getRows());
    }

    protected function getSortOrderForItem($id)
    {
        $this->getRow($id);
        return $this->getValue(DBEItemType::sortOrder);
    }

    protected function getSortOrderColumnName()
    {
        return $this->getDBColumnName(DBEItemType::sortOrder);
    }

    protected function getDB()
    {
        global $db;
        return $db;
    }
}

?>