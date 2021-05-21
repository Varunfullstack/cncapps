<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 30/05/2018
 * Time: 12:14
 */
global $cfg;
require_once($cfg["path_dbe"] . "/DBESite.inc.php");

class DBEJSite extends DBESite
{
    const invContactName = "invContactName";
    const delContactName = "delContactName";

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
            self::invContactName,
            DA_STRING,
            DA_ALLOW_NULL,
            "CONCAT(icontact.con_first_name,' ',icontact.con_last_name)"
        );
        $this->addColumn(
            self::delContactName,
            DA_STRING,
            DA_ALLOW_NULL,
            "CONCAT(dcontact.con_first_name,' ',dcontact.con_last_name)"
        );
        $this->setAddColumnsOff();
    }

    /**
     * Get rows by description match
     * @access public
     * @param $desc
     * @param string $activeFlag
     * @return bool Success
     */
    function getRowsByDescMatch($desc, $activeFlag = 'Y')
    {
        $this->setMethodName("getRowsByDescMatch");
        $ret = FALSE;
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " LEFT JOIN contact AS icontact" . " ON " . $this->getDBColumnName(
                DBESite::invoiceContactID
            ) . " = icontact.con_contno" . " LEFT JOIN contact AS dcontact" . " ON " . $this->getDBColumnName(
                DBESite::deliverContactID
            ) . " = dcontact.con_contno" . " WHERE (" . $this->getDBColumnName(
                DBESite::add1
            ) . " LIKE '%" . $desc . "%'" . " OR " . $this->getDBColumnName(
                DBESite::town
            ) . " LIKE '%" . $desc . "%'" . " OR " . $this->getDBColumnName(
                DBESite::postcode
            ) . " LIKE '%" . $desc . "%')" . " AND " . $this->getDBColumnName(
                DBESite::customerID
            ) . "=" . $this->getFormattedValue(DBESite::customerID);
        if ($activeFlag == 'Y') {
            $queryString .= ' AND add_active_flag = "Y"';
        }
        $queryString .= ' ORDER BY ' . $this->getDBColumnName(DBESite::add1);
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }

    function getRow($pkValue = null)
    {
        $this->setMethodName("getRow");
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " LEFT JOIN contact AS icontact" . " ON " . $this->getDBColumnName(
                DBESite::invoiceContactID
            ) . " = icontact.con_contno" . " LEFT JOIN contact AS dcontact" . " ON " . $this->getDBColumnName(
                DBESite::deliverContactID
            ) . " = dcontact.con_contno" . " WHERE " . $this->getDBColumnName(
                DBESite::customerID
            ) . "=" . $this->getFormattedValue(DBESite::customerID) . " AND " . $this->getDBColumnName(
                DBESite::siteNo
            ) . "=" . $this->getFormattedValue(DBEJSite::siteNo)
        );
        $ret = (parent::getRow());
        return $ret;
    }

    function getRowsByColumn($column, $activeFlag = 'Y')
    {
        $this->setMethodName("getRowsByColumn");
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " LEFT JOIN contact AS icontact" . " ON " . $this->getDBColumnName(
                DBESite::invoiceContactID
            ) . " = icontact.con_contno" . " LEFT JOIN contact AS dcontact" . " ON " . $this->getDBColumnName(
                DBESite::deliverContactID
            ) . " = dcontact.con_contno" . " WHERE " . $this->getDBColumnName($column) . "=" . $this->getFormattedValue(
                DBESite::customerID
            );
        if ($activeFlag == 'Y') {
            $queryString .= ' AND add_active_flag = "Y"';
        }
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }
}