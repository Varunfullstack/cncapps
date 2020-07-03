<?php /*
* User table join to userExt
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBEUser.inc.php");

class DBEJUser extends DBEUser
{

    const signatureFilename = "signatureFilename";
    const jobTitle = "jobTitle";
    const firstName = "firstName";
    const lastName = "lastName";
    const activeFlag = "activeFlag";
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
        $this->setAddColumnsOn();
        $this->addColumn(
            self::signatureFilename,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::jobTitle,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::firstName,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::lastName,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::activeFlag,
            DA_YN,
            DA_NOT_NULL
        );
       
        




        $this->setAddColumnsOff();
    }

    /**
     * Return all rows from DB
     * @access public
     * @return bool Success
     */
    function getRows()
    {
        $this->setMethodName("getRows");
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() . ' LEFT JOIN userext ON ' . $this->getTableName(
            ) . '.' . $this->getPKDBName() . '=userext.userID' .
            ' WHERE activeFlag = "Y"'
        );
        return (parent::getRows());
    }

    function getRow()
    {
        $this->setMethodName("getRow");
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            ' LEFT JOIN userext ON ' . $this->getTableName() . '.' . $this->getPKDBName() . '=userext.userID' .
            " WHERE " . $this->getPKWhere()
        );
        return (parent::getRow());
    }
}

?>