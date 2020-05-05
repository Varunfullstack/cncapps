<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 24/01/2019
 * Time: 16:30
 */
global $cfg;
require_once($cfg["path_dbe"] . "/DBEPassword.inc.php");

class DBEJPassword extends DBEPassword
{
    const customerName = "customerName";
    const serviceName = "serviceName";
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
        $this->addColumn(
            self::sortOrder,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "passwordService.sortOrder"
        );
        $this->setAddColumnsOff();
    }

    /**
     * Return all rows from DB
     * @access public
     * @param null $sortColumn
     * @return bool Success
     */
    function getRows($sortColumn = null)
    {
        $this->setMethodName("getRows");
        $query = $this->getBasicQuery();
        $query .= " order by passwordService.onePerCustomer, passwordService.description";

        $this->setQueryString($query);
        return (parent::getRows());
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

    function getRow($id = "")
    {
        $this->setMethodName("getRow");
        $this->setQueryString(
            $this->getBasicQuery() . " WHERE " . $this->getDBColumnName(
                self::archivedBy
            ) . ' is not null and  ' . $this->getDBColumnName(
                self::archivedBy
            ) . ' <> "" and ' . $this->getPKWhere()
        );
        return (parent::getRow());
    }

    function getRowsByCustomerIDAndPasswordLevel($customerID,
                                                 $passwordLevel = null,
                                                 $archived = false,
                                                 $salesPasswordAccess = false
    )
    {
        $query = $this->getBasicQuery() . " where 1=1 ";

        $archivedQuery = " and " . $this->getDBColumnName(
                self::archivedBy
            ) . ' is null ';

        if ($archived) {
            $archivedQuery = " and " . $this->getDBColumnName(
                    self::archivedBy
                ) . ' is not null';
        }

        $query .= $archivedQuery;

        $query .= " and " . $this->getDBColumnName(self::customerID) . " = " . $customerID;

        $passwordLevelQuery = "";
        if (isset($passwordLevel)) {
            $passwordLevelQuery = " and " . $this->getDBColumnName(self::level) . " <= " . $passwordLevel;
        }
        $query .= $passwordLevelQuery;

        if (!$salesPasswordAccess) {
            $query .= " and salesPassword = 0 ";
        }

        $query .= " order by passwordService.passwordServiceID IS NULL, passwordService.sortOrder asc,  passwordService.onePerCustomer DESC ";
        $this->setQueryString($query);
        parent::getRows();
    }

}