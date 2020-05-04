<?php
/*
* Future Action table holds rows for internal email reminders to do stuff
* rows deleted as email sent
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEPasswordService extends DBEntity
{

    const passwordServiceID = "passwordServiceID";
    const description = "description";
    const onePerCustomer = "onePerCustomer";
    const sortOrder = "sortOrder";
    const defaultLevel = "defaultLevel";

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
        $this->setTableName("passwordService");
        $this->addColumn(
            self::passwordServiceID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::onePerCustomer,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            false
        );
        $this->addColumn(
            self::sortOrder,
            DA_INTEGER,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::defaultLevel,
            DA_INTEGER,
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

    public function getNextSortOrder()
    {
        $this->db->query("select max(sortOrder) as maxSortOrder from passwordService");
        $this->db->next_record(MYSQLI_ASSOC);
        return $this->db->Record['maxSortOrder'] + 1;
    }

    public function getNotInUseServices($customerID,
                                        $excludedPasswordID = null
    )
    {
        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName(
            ) . " LEFT JOIN PASSWORD ON passwordservice.passwordServiceID = password.`serviceID` AND password.`pas_custno` = $customerID " . ($excludedPasswordID ? " and password.pas_passwordno <> $excludedPasswordID" : '') .
            "  AND(password.archivedBy = '' OR password.archivedBy IS NULL) WHERE(passwordService . onePerCustomer = 0 OR password . `pas_passwordno` IS NULL) 
 GROUP BY passwordServiceID order by description asc
";
        $this->setQueryString($queryString);
        return $this->getRows();
    }


}

?>