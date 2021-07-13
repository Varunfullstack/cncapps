<?php
/*
* Future Action table holds rows for internal email reminders to do stuff
* rows deleted as email sent
* @authors Karim Ahmed
* @access public
*/

use CNCLTD\Sortable;

require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEQuotationTemplate extends DBEntity implements Sortable
{

    const id = "id";
    const description = "description";
    const sortOrder = "sortOrder";
    const linkedSalesOrderId = "linkedSalesOrderId";

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
        $this->setTableName("quotationTemplate");
        $this->addColumn(
            self::id,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::linkedSalesOrderId,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::sortOrder,
            DA_INTEGER,
            DA_ALLOW_NULL
        );

        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    public function moveItemToTop($itemId)
    {
        $currentItemSortOrder = $this->getItemSortOrder($itemId);
        if ($currentItemSortOrder == 0) {
            return;
        }

        $items = $this->getItemsBelowSortOrder($currentItemSortOrder);

        $query = "update " . $this->tableName . " set sortOrder = case id when $itemId then 0 ";

        foreach ($items as $item) {
            $query .= " when " . $item[self::id] . " then sortOrder + 1 ";
        }
        $query .= " else sortOrder end ";
        $this->setQueryString($query);
        $this->runQuery();
    }

    private function getItemSortOrder($itemId)
    {
        $result = $this->db->query(
            "select " . $this->getDBColumnName(
                self::sortOrder
            ) . " from " . $this->tableName . " where " . $this->getDBColumnName(self::id)
            . " = $itemId"
        );
        return (int)$result->fetch_row()[0];
    }

    private function getItemsBelowSortOrder($sortOrder)
    {
        $query = "select " . $this->getDBColumnNamesAsString(
            ) . " from " . $this->tableName . " where " . $this->getDBColumnName(
                self::sortOrder
            ) . " < $sortOrder  order by sortOrder ";
        $result = $this->db->query($query);

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getNextSortOrder()
    {
        return $this->getMaxSortOrder() + 1;
    }

    private function getMaxSortOrder()
    {
        $this->db->query(
            "select max(" . $this->getDBColumnName(self::sortOrder) . ") as maxSortOrder from  " . $this->tableName
        );
        $this->db->next_record(MYSQLI_ASSOC);
        return $this->db->Record['maxSortOrder'];
    }

    public function moveItemToBottom($itemId)
    {
        $currentItemSortOrder = $this->getItemSortOrder($itemId);
        $getMax = $this->getMaxSortOrder();
        if ($currentItemSortOrder == $getMax) {
            return;
        }

        $items = $this->getItemsAboveSortOrder($currentItemSortOrder);

        $query = "update " . $this->tableName . " set sortOrder = case id when $itemId then $getMax";

        foreach ($items as $item) {
            $query .= " when " . $item[self::id] . " then sortOrder - 1 ";
        }
        $query .= " else sortOrder end ";
        $this->setQueryString($query);
        $this->runQuery();
    }

    private function getItemsAboveSortOrder($sortOrder)
    {
        $result = $this->db->query(
            "select " . $this->getDBColumnNamesAsString(
            ) . " from " . $this->tableName . " where " . $this->getDBColumnName(
                self::sortOrder
            ) . " > $sortOrder  order by sortOrder"
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function moveItemUp($itemId)
    {


        $query = "UPDATE
  " . $this->tableName . " 
SET
  " . $this->getDBColumnName(self::sortOrder) . " =
  CASE
    " . $this->getDBColumnName(self::id) . "
    WHEN $itemId
    THEN " . $this->getDBColumnName(self::sortOrder) . " - 1
    ELSE IF(
      " . $this->getDBColumnName(self::id) . " =
      (SELECT
        b." . $this->getDBColumnName(self::id) . "
      FROM
        (SELECT
          *
        FROM
          " . $this->tableName . " 
        WHERE " . $this->getDBColumnName(self::id) . " <> $itemId) b
      WHERE b." . $this->getDBColumnName(self::sortOrder) . " =
        (SELECT
          c." . $this->getDBColumnName(self::sortOrder) . "
        FROM
          (SELECT
            " . $this->getDBColumnName(self::sortOrder) . " - 1 AS " . $this->getDBColumnName(self::sortOrder) . ",
            " . $this->getDBColumnName(self::id) . "
          FROM
            " . $this->tableName . " ) c
        WHERE c." . $this->getDBColumnName(self::id) . " = $itemId)),
      " . $this->getDBColumnName(self::sortOrder) . " + 1,
      " . $this->getDBColumnName(self::sortOrder) . "
    )
  END";
        $this->setQueryString($query);
        $this->runQuery();
    }

    public function moveItemDown($itemId)
    {

        $query = "UPDATE
  " . $this->tableName . " 
SET
  " . $this->getDBColumnName(self::sortOrder) . " =
  CASE
    " . $this->getDBColumnName(self::id) . "
    WHEN $itemId
    THEN " . $this->getDBColumnName(self::sortOrder) . " + 1
    ELSE IF(
      " . $this->getDBColumnName(self::id) . " =
      (SELECT
        b." . $this->getDBColumnName(self::id) . "
      FROM
        (SELECT
          *
        FROM
          " . $this->tableName . " 
        WHERE " . $this->getDBColumnName(self::id) . " <> $itemId) b
      WHERE b." . $this->getDBColumnName(self::sortOrder) . " =
        (SELECT
          c." . $this->getDBColumnName(self::sortOrder) . "
        FROM
          (SELECT
            " . $this->getDBColumnName(self::sortOrder) . " + 1 AS " . $this->getDBColumnName(self::sortOrder) . ",
            " . $this->getDBColumnName(self::id) . "
          FROM
            " . $this->tableName . " ) c
        WHERE c." . $this->getDBColumnName(self::id) . " = $itemId)),
      " . $this->getDBColumnName(self::sortOrder) . " - 1,
      " . $this->getDBColumnName(self::sortOrder) . "
    )
  END";
        $this->setQueryString($query);
        $this->runQuery();
    }

    public function getRows($sortColumn = '', $orderDirection = null)
    {
        if (!$sortColumn) {
            $sortColumn = self::sortOrder;
        }
        return parent::getRows($sortColumn, $orderDirection); // TODO: Change the autogenerated stub
    }

    /**
     * Get rows by description match
     * @access public
     * @param $description
     * @return bool Success
     */
    function getRowsByDescriptionMatch($description)
    {
        $this->setValue(self::description, $description);
        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE 1=1";
        $queryString .=
            " AND " . $this->getDBColumnName(self::description) . " like '%" . $this->getValue(
                self::description
            ) . "%' ORDER BY " . $this->getDBColumnName(self::description) .
            " LIMIT 0,200";
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }

}