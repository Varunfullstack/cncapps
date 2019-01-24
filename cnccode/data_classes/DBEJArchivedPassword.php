<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 24/01/2019
 * Time: 16:30
 */

require_once($cfg["path_dbe"] . "/DBEPassword.inc.php");

class DBEJArchivedPassword extends DBEPassword
{
    const customerName = "customerName";
    const serviceName = "serviceName";

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
            self::customerName,
            DA_STRING,
            DA_ALLOW_NULL,
            'customer.cus_name'
        );
        $this->addColumn(
            self::serviceName,
            DA_STRING,
            DA_ALLOW_NULL,
            'passwordService.description'
        );
        $this->setAddColumnsOff();
    }

    private function getBasicQuery()
    {
        return "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() . ' LEFT JOIN customer ON ' . $this->getTableName(
            ) . '.' . $this->getDBColumnName(self::customerID) . '= customer.cus_custno ' .
            ' left join passwordService on ' . $this->getTableName() . "." . $this->getDBColumnName(
                self::serviceID
            ) . " =  passwordService.passwordServiceID ";

    }

    /**
     * Return all rows from DB
     * @access public
     * @return bool Success
     */
    function getRows($passwordLevel)
    {
        $this->setMethodName("getRows");
        $this->setQueryString(
            $this->getBasicQuery() . " WHERE " . $this->getDBColumnName(self::level) . " <= " . $passwordLevel .
            " and " . $this->getDBColumnName(self::archivedBy) . ' is not null and  ' . $this->getDBColumnName(
                self::archivedBy
            ) . ' <> "" '
        );
        return (parent::getRows());
    }

    function getRow($id,
                    $passwordLevel
    )
    {
        $this->setMethodName("getRow");
        $this->setQueryString(
            $this->getBasicQuery() . " WHERE " . $this->getDBColumnName(self::level) . " <= " . $passwordLevel .
            " and " . $this->getDBColumnName(self::archivedBy) . ' is not null and  ' . $this->getDBColumnName(
                self::archivedBy
            ) . ' <> "" and ' . $this->getPKWhere()
        );
        return (parent::getRow());
    }
}