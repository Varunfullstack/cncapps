<?php
/*
* Future Action table holds rows for internal email reminders to do stuff
* rows deleted as email sent
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEQuotationTemplate extends DBEntity
{

    const id = "id";
    const description = "description";
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
        $this->setTableName("quotationTemplate");
        $this->addColumn(
            self::id,
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
        $query = 'update ' . $this->tableName . ' set ' . $this->getDBColumnName(
                self::sortOrder
            ) . ' = case ' . $this->getDBColumnName(
                self::id
            ) . ' when ' . $itemId . ' then 0  else (
      IF(
        ' . $this->getDBColumnName(self::sortOrder) . ' < 
        (SELECT 
         ' . $this->getDBColumnName(self::sortOrder) . '
        FROM
          (SELECT * FROM  ' . $this->tableName . ') test
        WHERE ' . $this->getDBColumnName(self::id) . ' = ' . $itemId . '),
       ' . $this->getDBColumnName(self::sortOrder) . ' + 1,
        ' . $this->getDBColumnName(self::sortOrder) . '
      )
    ) end';
        $this->setQueryString($query);
        $this->runQuery();
    }

    public function moveItemToBottom($itemId)
    {
        $query = 'update ' . $this->tableName . ' set ' . $this->getDBColumnName(
                self::sortOrder
            ) . ' = case ' . $this->getDBColumnName(
                self::id
            ) . ' when ' . $itemId . ' then 
              (SELECT MAX(' . $this->getDBColumnName(
                self::sortOrder
            ) . ') FROM (SELECT * FROM ' . $this->tableName . ') something)
              else (
      IF(
        ' . $this->getDBColumnName(self::sortOrder) . ' > 
        (SELECT 
         ' . $this->getDBColumnName(self::sortOrder) . '
        FROM
          (SELECT * FROM  ' . $this->tableName . ') test
        WHERE ' . $this->getDBColumnName(self::id) . ' = ' . $itemId . '),
       ' . $this->getDBColumnName(self::sortOrder) . ' - 1,
        ' . $this->getDBColumnName(self::sortOrder) . '
      )
    ) end';
        $this->setQueryString($query);
        $this->runQuery();
    }

    public function moveItemUp($itemId)
    {


        $query = "UPDATE  " . $this->tableName . " test2 SET " . $this->getDBColumnName(
                self::sortOrder
            ) . " = CASE " . $this->getDBColumnName(
                self::id
            ) . " WHEN $itemId THEN " . $this->getDBColumnName(
                self::sortOrder
            ) . " - 1 ELSE ( IF(" . $this->getDBColumnName(self::id) . " = (SELECT " . $this->getDBColumnName(
                self::id
            ) . " FROM (SELECT " . $this->getDBColumnName(
                self::id
            ) . " FROM " . $this->tableName . " WHERE " . $this->getDBColumnName(
                self::sortOrder
            ) . " = (SELECT " . $this->getDBColumnName(
                self::sortOrder
            ) . " - 1 FROM " . $this->tableName . " WHERE " . $this->getDBColumnName(
                self::id
            ) . " = $itemId)) a), (SELECT " . $this->getDBColumnName(
                self::sortOrder
            ) . " FROM (SELECT * FROM " . $this->tableName . ") b WHERE " . $this->getDBColumnName(
                self::id
            ) . " = $itemId), " . $this->getDBColumnName(self::sortOrder) . " )) END ";

        $this->setQueryString($query);
        $this->runQuery();
    }

    public function moveItemDown($itemId)
    {
        $query = "UPDATE " . $this->tableName . " test2
SET" . $this->getDBColumnName(self::sortOrder) . " =
      CASE
       " . $this->getDBColumnName(self::id) . "
        WHEN $itemId
          THEN" . $this->getDBColumnName(self::sortOrder) . " + 1
        ELSE (
          IF(
               " . $this->getDBColumnName(self::id) . " =
                (SELECT" . $this->getDBColumnName(self::id) . "
                 FROM (SELECT" . $this->getDBColumnName(self::id) . "
                       FROM" . $this->tableName . "
                       WHERE" . $this->getDBColumnName(self::sortOrder) . " =
                             (SELECT" . $this->getDBColumnName(self::sortOrder) . " + 1
                              FROM" . $this->tableName . "
                              WHERE" . $this->getDBColumnName(self::id) . " = $itemId)) a),
                (SELECT" . $this->getDBColumnName(self::sortOrder) . "
                 FROM (SELECT *
                       FROM" . $this->tableName . ") b
                 WHERE" . $this->getDBColumnName(self::id) . " = $itemId),
               " . $this->getDBColumnName(self::sortOrder) . "
            )
          )
        END ";

        $this->setQueryString($query);
        $this->runQuery();
    }

    public function getNextSortOrder()
    {
        $this->db->query(
            "select max(" . $this->getDBColumnName(self::sortOrder) . ") as maxSortOrder from  " . $this->tableName
        );
        $this->db->next_record(MYSQLI_ASSOC);
        return $this->db->Record['maxSortOrder'] + 1;
    }


}