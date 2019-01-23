<?php /*
* AnswerType table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEPassword extends DBEntity
{

    const passwordID = "passwordID";
    const customerID = "customerID";
    const username = "username";
    const service = "service";
    const password = "password";
    const notes = "notes";
    const level = "level";
    const URL = 'URL';
    const archivedAt = 'archivedAt';
    const archivedBy = 'archivedBy';
    const serviceID = 'serviceID';

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
            self::service,
            DA_STRING,
            DA_ALLOW_NULL,
            "pas_service"
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
            DA_INTEGER,
            DA_ALLOW_NULL,
            "pas_service"
        );

        $this->setAddColumnsOff();
        $this->setPK(0);
    }

    public function getRowsByCustomerIDAndPasswordLevel($customerID,
                                                        $passwordLevel
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
            " and " . $this->getDBColumnName(self::archivedBy) . ' is null '
        );
        return (parent::getRows());
    }

    public function getArchivedRowsByPasswordLevel($passwordLevel)
    {
        $this->setMethodName('getRowsByCustomerIDAndPasswordLevel');
        if ($passwordLevel == '') {
            $this->raiseError('passwordLevel not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::level) . " <= " . $passwordLevel .
            " and " . $this->getDBColumnName(self::archivedBy) . ' is not null '
        );
        return (parent::getRows());
    }
}

?>
