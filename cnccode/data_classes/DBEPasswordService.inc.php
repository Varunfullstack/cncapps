<?php
/*
* Future Action table holds rows for internal email reminders to do stuff
* rows deleted as email sent
* @authors Karim Ahmed
* @access public
*/
global $cfg;

use CNCLTD\SortableDBE;

require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEPasswordService extends DBEntity
{
    use SortableDBE;

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
            DA_FLOAT,
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


    protected function getSortOrderForItem($id)
    {
        $this->getRow($id);
        return $this->getValue(self::sortOrder);
    }

    protected function getSortOrderColumnName()
    {
        return $this->getDBColumnName(self::sortOrder);
    }

    protected function getDB()
    {
        global $db;
        return $db;
    }
}

?>