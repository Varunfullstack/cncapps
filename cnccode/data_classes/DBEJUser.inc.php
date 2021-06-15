<?php /*
* User table join to userExt
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_dbe"] . "/DBEUser.inc.php");

class DBEJUser extends DBEUser
{

    const signatureFilename = "signatureFilename";
    const jobTitle          = "jobTitle";
    const firstName         = "firstName";
    const lastName          = "lastName";
    const activeFlag        = "activeFlag";
    const teamLevel         = "teamLevel";

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
        $this->setAddColumnsOn();
        $this->addColumn(
            self::signatureFilename,
            DA_STRING,
            DA_ALLOW_NULL,
            "consultant.signatureFilename"
        );
        $this->addColumn(
            self::jobTitle,
            DA_STRING,
            DA_NOT_NULL,
            "consultant.jobTitle"
        );
        $this->addColumn(
            self::firstName,
            DA_STRING,
            DA_NOT_NULL,
            "consultant.firstName"
        );
        $this->addColumn(
            self::lastName,
            DA_STRING,
            DA_NOT_NULL,
            "consultant.lastName"
        );
        $this->addColumn(
            self::activeFlag,
            DA_YN,
            DA_NOT_NULL,
            "consultant.activeFlag"
        );
        $this->addColumn(
            self::teamLevel,
            DA_YN,
            DA_NOT_NULL,
            "team.level"
        );
        $this->setAddColumnsOff();
    }

    /**
     * Return all rows from DB
     * @access public
     * @return bool Success
     */
    function getRows($sortColumn = '', $orderDirection = '')
    {
        $this->setMethodName("getRows");
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . ' LEFT JOIN userext ON ' . $this->getTableName() . '.' . $this->getPKDBName(
            ) . '=userext.userID' . ' WHERE activeFlag = "Y"'
        );
        return (parent::getRows());
    }

    function getRow($pkValue = null)
    {
        $this->setMethodName("getRow");
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . ' LEFT JOIN userext ON ' . $this->getTableName() . '.' . $this->getPKDBName(
            ) . '=userext.userID ' . ' LEFT JOIN team ON  ' . $this->getTableName(
            ) . '.' . self::teamID . '=team.teamID ' . " WHERE " . $this->getPKWhere()
        );
        return (parent::getRow());
    }
}

?>