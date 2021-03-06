<?php /*
* AnswerType table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEPassword extends DBEntity
{

    const passwordID = "passwordID";
    const customerID = "customerID";
    const username = "username";
    const password = "password";
    const notes = "notes";
    const level = "level";
    const URL = 'URL';
    const archivedAt = 'archivedAt';
    const archivedBy = 'archivedBy';
    const serviceID = 'serviceID';
    const encrypted = 'encrypted';
    const salesPassword = 'salesPassword';

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
        $this->setTableName("password");
        $this->addColumn(
            self::passwordID,
            DA_ID,
            DA_NOT_NULL,
            "pas_passwordno"
        );
        $this->addColumn(
            self::customerID,
            DA_ID,
            DA_NOT_NULL,
            "pas_custno"
        );
        $this->addColumn(
            self::username,
            DA_STRING,
            DA_ALLOW_NULL,
            "pas_username"
        );
        $this->addColumn(
            self::password,
            DA_STRING,
            DA_ALLOW_NULL,
            "pas_password"
        );
        $this->addColumn(
            self::notes,
            DA_STRING,
            DA_ALLOW_NULL,
            "pas_notes"
        );

        $this->addColumn(
            self::level,
            DA_INTEGER,
            DA_NOT_NULL
        );

        $this->addColumn(
            self::URL,
            DA_STRING,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::archivedAt,
            DA_DATETIME,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::archivedBy,
            DA_STRING,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::serviceID,
            DA_ID,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::encrypted,
            DA_INTEGER,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::salesPassword,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            false
        );

        $this->setAddColumnsOff();
        $this->setPK(0);
    }

    public function getRowsByCustomerIDAndPasswordLevel($customerID,
                                                        $passwordLevel,
                                                        $archived = false
    )
    {
        $this->setMethodName('getRowsByCustomerIDAndPasswordLevel');
        if ($customerID == '') {
            $this->raiseError('customerID not set');
        }
        if ($passwordLevel == '') {
            $this->raiseError('passwordLevel not set');
        }

        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::customerID) . "=" . $customerID .
            " AND " . $this->getDBColumnName(self::level) . " <= " . $passwordLevel .
            " and (" . $this->getDBColumnName(self::archivedBy) . ' is null or  ' . $this->getDBColumnName(
                self::archivedBy
            ) . ' = "" )'
        );
        return (parent::getRows());
    }

    public function getArchivedRowsByCustomerID($customerID)
    {
        $this->setMethodName('getArchivedRowsByCustomerID');
        if ($customerID == '') {
            $this->raiseError('customer ID not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::customerID) . " = " . $customerID .
            " and " . $this->getDBColumnName(self::archivedBy) . ' is not null and  ' . $this->getDBColumnName(
                self::archivedBy
            ) . ' <> "" '
        );
        return (parent::getRows());
    }

    public function getLocalPCCNCAdminPasswordByCustomerID(int $customerID)
    {
        $this->setMethodName('getLocalPCCNCAdminPasswordByCustomerID');
        if (!$customerID) {
            throw new Exception("Customer ID Required");
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::customerID) . " = " . $customerID .
            " and " . $this->getDBColumnName(self::serviceID) . ' = 24  and ' . $this->getDBColumnName(
                self::archivedBy
            ) . ' is null '
        );
        $this->getRows();
        $this->fetchFirst();
        $this->resetQueryString();
    }

    public function getOffice365PasswordByCustomerID(int $customerID)
    {
        $this->getPasswordItemByCustomerIdAndServiceId($customerID, 10);
    }

    public function getPasswordItemByCustomerIdAndServiceId(int $customerId, int $passwordServiceId)
    {
        $this->setMethodName(__FUNCTION__);
        if (!$customerId) {
            throw new UnexpectedValueException("Customer ID Required");
        }

        if (!$passwordServiceId) {
            throw new UnexpectedValueException("Password Service ID must be provided");
        }

        $this->setQueryString(
            "SELECT {$this->getDBColumnNamesAsString()} FROM {$this->getTableName()} WHERE 
                              {$this->getDBColumnName(self::customerID)} = {$customerId} and 
                              {$this->getDBColumnName(self::serviceID)} = {$passwordServiceId}  and {$this->getDBColumnName(
                self::archivedBy
            )} is null"
        );
        $this->getRows();
        $this->fetchFirst();
        $this->resetQueryString();
    }

    public function getAutomatedFullAssetListPasswordItem()
    {
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::customerID) . " = 282 and " . $this->getDBColumnName(
                self::serviceID
            ) . ' = 26'
        );
        $this->getRows();
        $this->fetchFirst();
        $this->resetQueryString();
    }
}

?>
