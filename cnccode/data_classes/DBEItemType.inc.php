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
            ) . " and " . $this->getDBColumnName(self::showInCustomerReview) . " order by " . $this->getDBColumnName(
                self::description
            );
        if ($arbitrarySort) {
            $statement .= " order by sortOrder";
        }
        $this->setQueryString($statement);
        $ret = (parent::getRows());
    }

    public function moveItemToTop($itemTypeId)
    {
        $query = "update {$this->tableName} set {$this->getDBColumnName(
                self::sortOrder
            )} = case {$this->getDBColumnName(
                self::itemTypeID
            )} when {$itemTypeId} then 0  else (
      IF(
        {$this->getDBColumnName(self::sortOrder)} < 
        (SELECT 
         {$this->getDBColumnName(self::sortOrder)}
        FROM
          (SELECT * FROM  {$this->tableName}) test
        WHERE {$this->getDBColumnName(self::itemTypeID)} = {$itemTypeId}),
       {$this->getDBColumnName(self::sortOrder)} + 1,
        {$this->getDBColumnName(self::sortOrder)}
      )
    ) end";
        $this->setQueryString($query);
        $this->runQuery();
    }

    public function moveItemToBottom($itemTypeId)
    {
        $query = "update {$this->tableName} set {$this->getDBColumnName(self::sortOrder)} = case 
    {$this->getDBColumnName(self::itemTypeID)} when {$itemTypeId} then 
              (SELECT MAX({$this->getDBColumnName(self::sortOrder)}) FROM (SELECT * FROM {$this->tableName}) something)
              else (
      IF(
        {$this->getDBColumnName(self::sortOrder)} > 
        (SELECT 
         {$this->getDBColumnName(self::sortOrder)}
        FROM
          (SELECT * FROM  {$this->tableName}) test
        WHERE {$this->getDBColumnName(self::itemTypeID)} = {$itemTypeId}),
       {$this->getDBColumnName(self::sortOrder)} - 1,
        {$this->getDBColumnName(self::sortOrder)}
      )
    ) end";
        $this->setQueryString($query);
        $this->runQuery();
    }

    public function moveItemUp($itemTypeId)
    {
        $query = "UPDATE
  {$this->tableName}  test2
SET {$this->getDBColumnName(self::sortOrder)} =
      CASE
        {$this->getDBColumnName(self::itemTypeID)}
        WHEN $itemTypeId
          THEN {$this->getDBColumnName(self::sortOrder)} - 1
        ELSE (
          IF(
                {$this->getDBColumnName(self::itemTypeID)} =
                (SELECT {$this->getDBColumnName(self::itemTypeID)}
                 FROM (SELECT {$this->getDBColumnName(self::itemTypeID)}
                       FROM {$this->tableName}
                       WHERE {$this->getDBColumnName(self::sortOrder)} =
                             (SELECT {$this->getDBColumnName(self::sortOrder)} - 1
                              FROM {$this->tableName}
                              WHERE {$this->getDBColumnName(self::itemTypeID)} = $itemTypeId)) a),
                (SELECT {$this->getDBColumnName(self::sortOrder)}
                 FROM (SELECT *
                       FROM {$this->tableName}) b
                 WHERE {$this->getDBColumnName(self::itemTypeID)} = $itemTypeId),
                {$this->getDBColumnName(self::sortOrder)}
            )
          )
        END ";

        $this->setQueryString($query);
        $this->runQuery();
    }

    public function moveItemDown($passwordServiceID)
    {
        $query = "UPDATE
  {$this->tableName} test2
SET {$this->getDBColumnName(self::sortOrder)} =
      CASE
        {$this->getDBColumnName(self::itemTypeID)}
        WHEN $passwordServiceID
          THEN {$this->getDBColumnName(self::sortOrder)} + 1
        ELSE (
          IF(
                {$this->getDBColumnName(self::itemTypeID)} =
                (SELECT {$this->getDBColumnName(self::itemTypeID)}
                 FROM (SELECT {$this->getDBColumnName(self::itemTypeID)}
                       FROM {$this->tableName}
                       WHERE {$this->getDBColumnName(self::sortOrder)} =
                             (SELECT {$this->getDBColumnName(self::sortOrder)} + 1
                              FROM {$this->tableName}
                              WHERE {$this->getDBColumnName(self::itemTypeID)} = $passwordServiceID)) a),
                (SELECT {$this->getDBColumnName(self::sortOrder)}
                 FROM (SELECT *
                       FROM {$this->tableName}) b
                 WHERE {$this->getDBColumnName(self::itemTypeID)} = $passwordServiceID),
                {$this->getDBColumnName(self::sortOrder)}
            )
          )
        END ";

        $this->setQueryString($query);
        $this->runQuery();
    }
}

?>