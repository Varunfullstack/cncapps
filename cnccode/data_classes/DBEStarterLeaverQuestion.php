<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 25/01/2019
 * Time: 10:45
 */

require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEStarterLeaverQuestion extends DBEntity
{
    const questionID = "questionID";
    const customerID = "customerID";
    const formType = "formType";
    const name = "name";
    const type = "type";
    const label = "label";
    const options = "options";
    const multi = "multi";
    const required = "required";
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
        $this->setTableName("starterLeaverQuestion");
        $this->addColumn(
            self::questionID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::customerID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::formType,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::name,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::type,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::label,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::options,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::multi,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::required,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::sortOrder,
            DA_FLOAT,
            DA_NOT_NULL
        );

        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    public function moveItemToTop($passwordServiceID)
    {
        $query = 'update ' . $this->tableName . ' set ' . $this->getDBColumnName(
                self::sortOrder
            ) . ' = case ' . $this->getDBColumnName(
                self::passwordServiceID
            ) . ' when ' . $passwordServiceID . ' then 0  else (
      IF(
        ' . $this->getDBColumnName(self::sortOrder) . ' < 
        (SELECT 
         ' . $this->getDBColumnName(self::sortOrder) . '
        FROM
          (SELECT * FROM  ' . $this->tableName . ') test
        WHERE ' . $this->getDBColumnName(self::passwordServiceID) . ' = ' . $passwordServiceID . '),
       ' . $this->getDBColumnName(self::sortOrder) . ' + 1,
        ' . $this->getDBColumnName(self::sortOrder) . '
      )
    ) end';
        $this->setQueryString($query);
        $this->runQuery();
    }

    public function moveItemToBottom($passwordServiceID)
    {
        $query = 'update ' . $this->tableName . ' set ' . $this->getDBColumnName(
                self::sortOrder
            ) . ' = case ' . $this->getDBColumnName(
                self::passwordServiceID
            ) . ' when ' . $passwordServiceID . ' then 
              (SELECT MAX(sortOrder) FROM (SELECT * FROM passwordService) something)
              else (
      IF(
        ' . $this->getDBColumnName(self::sortOrder) . ' > 
        (SELECT 
         ' . $this->getDBColumnName(self::sortOrder) . '
        FROM
          (SELECT * FROM  ' . $this->tableName . ') test
        WHERE ' . $this->getDBColumnName(self::passwordServiceID) . ' = ' . $passwordServiceID . '),
       ' . $this->getDBColumnName(self::sortOrder) . ' - 1,
        ' . $this->getDBColumnName(self::sortOrder) . '
      )
    ) end';
        $this->setQueryString($query);
        $this->runQuery();
    }

    public function moveItemUp($passwordServiceID)
    {
        $query = "UPDATE
  passwordService test2
SET sortOrder =
      CASE
        passwordServiceID
        WHEN $passwordServiceID
          THEN sortOrder - 1
        ELSE (
          IF(
                passwordServiceID =
                (SELECT passwordServiceID
                 FROM (SELECT passwordServiceID
                       FROM passwordService
                       WHERE sortOrder =
                             (SELECT sortOrder - 1
                              FROM passwordService
                              WHERE passwordServiceID = $passwordServiceID)) a),
                (SELECT sortOrder
                 FROM (SELECT *
                       FROM passwordService) b
                 WHERE passwordServiceID = $passwordServiceID),
                sortOrder
            )
          )
        END ";

        $this->setQueryString($query);
        $this->runQuery();
    }

    public function moveItemDown($passwordServiceID)
    {
        $query = "UPDATE
  passwordService test2
SET sortOrder =
      CASE
        passwordServiceID
        WHEN $passwordServiceID
          THEN sortOrder + 1
        ELSE (
          IF(
                passwordServiceID =
                (SELECT passwordServiceID
                 FROM (SELECT passwordServiceID
                       FROM passwordService
                       WHERE sortOrder =
                             (SELECT sortOrder + 1
                              FROM passwordService
                              WHERE passwordServiceID = $passwordServiceID)) a),
                (SELECT sortOrder
                 FROM (SELECT *
                       FROM passwordService) b
                 WHERE passwordServiceID = $passwordServiceID),
                sortOrder
            )
          )
        END ";

        $this->setQueryString($query);
        $this->runQuery();
    }

    public function getNextSortOrder($customerID)
    {
        $this->db->query(
            "select max(sortOrder) as maxSortOrder from starterLeaverQuestion where customerID = $customerID"
        );
        $this->db->next_record(MYSQLI_ASSOC);
        return $this->db->Record['maxSortOrder'] + 1;
    }

    public function getCustomers()
    {
        $this->db->query(
            "SELECT customerID, customer.cus_name as customerName, sum(starterLeaverQuestion.formType = 'leaver') as leavers, sum(starterLeaverQuestion.formType = 'starter') as starters FROM starterLeaverQuestion LEFT JOIN customer ON starterLeaverQuestion.customerID = customer.`cus_custno` GROUP BY customerID order by customerName"
        );
        $customers = [];

        while ($this->db->next_record(MYSQLI_ASSOC)) {
            $customers[] = $this->db->Record;
        };
        return $customers;
    }

    public function getRowsByCustomerID($customerID,
                                        $sortColumn = '',
                                        $formType = null
    )
    {
        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName();

        if (!$sortColumn) {
            $sortColumn = self::sortOrder;
        }

        $queryString .= " where " . $this->getDBColumnName(self::customerID) . " = " . $customerID;

        $sortColumnNo = ($this->columnExists($sortColumn));

        if ($formType) {
            $queryString .= " and " . $this->getDBColumnName(self::formType) . " = '" . $formType . "'";
        }

        if ($sortColumnNo == DA_OUT_OF_RANGE) {
            $this->raiseError($sortColumn . ' ' . DA_MSG_COLUMN_DOES_NOT_EXIST);
        } else {
            $queryString .= ' ORDER BY ' . $this->getDBColumnName($sortColumnNo);
        }
        $this->queryString = $queryString;
        $this->getRows();
    }
}