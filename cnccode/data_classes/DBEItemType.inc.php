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
    const sortOrder = "sortOrder";


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
            DA_INTEGER,
            DA_NOT_NULL
        );

        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getCustomerReviewRows($arbitrarySort = false)
    {
        $statement =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() . " where " . $this->getDBColumnName(
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

    public function moveItemToTop($itemTypeId)
    {
        if ($this->isFirst($itemTypeId)) {
            return;
        }
        $this->getRow($itemTypeId);
        $this->swapPlaces($this->getValue(DBEItemType::sortOrder), 1);
    }

    private function isFirst($itemTypeId)
    {
        $this->getRow($itemTypeId);
        return $this->getValue(DBEItemType::sortOrder) <= 1;
    }

    private function swapPlaces($oldOrderId, $newOrderId)
    {
        $query = "UPDATE
  {$this->tableName}
SET
  {$this->getDBColumnName(self::sortOrder)} =
  CASE
    WHEN {$this->getDBColumnName(self::sortOrder)} = $oldOrderId
    THEN $newOrderId
    WHEN $newOrderId < $oldOrderId
    AND {$this->getDBColumnName(self::sortOrder)} < $oldOrderId
    THEN {$this->getDBColumnName(self::sortOrder)} + 1
    WHEN $newOrderId > $oldOrderId
    AND {$this->getDBColumnName(self::sortOrder)} > $oldOrderId
    THEN {$this->getDBColumnName(self::sortOrder)} - 1
    ELSE {$this->getDBColumnName(self::sortOrder)}
  END
WHERE {$this->getDBColumnName(self::sortOrder)} BETWEEN LEAST($newOrderId, $oldOrderId)
    AND GREATEST($newOrderId, $oldOrderId)";

        $this->setQueryString($query);
        $this->runQuery();

    }

    public function moveItemToBottom($itemTypeId)
    {
        if ($this->isLast($itemTypeId)) {
            return;
        }
        $this->getRow($itemTypeId);
        $this->swapPlaces($this->getValue(DBEItemType::sortOrder), $this->getMaxSortOrder());
    }

    private function isLast($itemTypeId)
    {
        $this->getRow($itemTypeId);
        return $this->getValue(DBEItemType::sortOrder) >= $this->getMaxSortOrder();
    }

    public function getMaxSortOrder()
    {
        $query = "select max({$this->getDBColumnName(self::sortOrder)}) as maxSortOrder from {$this->tableName}";
        $this->db->query($query);

        $this->db->next_record(MYSQLI_ASSOC);
        return $this->db->Record['maxSortOrder'];
    }

    public function moveItemUp($itemTypeId)
    {
        if ($this->isFirst($itemTypeId)) {
            return;
        }
        $this->getRow($itemTypeId);
        $this->swapPlaces($this->getValue(DBEItemType::sortOrder), $this->getValue(DBEItemType::sortOrder) - 1);
    }

    public function moveItemDown($itemTypeId)
    {
        if ($this->isLast($itemTypeId)) {
            return;
        }
        $this->getRow($itemTypeId);
        $this->swapPlaces($this->getValue(DBEItemType::sortOrder), $this->getValue(DBEItemType::sortOrder) + 1);
    }

    public function getNextSortOrder()
    {
        return $this->getMaxSortOrder() + 1;
    }
}

?>