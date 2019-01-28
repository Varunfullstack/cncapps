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
     * @return void
     * @param  void
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
        $this->db->query("select max(sortOrder) as maxSortOrder from starterLeaverQuestion");
        $this->db->next_record(MYSQLI_ASSOC);
        return $this->db->Record['maxSortOrder'] + 1;
    }
}