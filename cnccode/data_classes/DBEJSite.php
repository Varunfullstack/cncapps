<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 30/05/2018
 * Time: 12:14
 */

require_once($cfg["path_dbe"] . "/DBESite.inc.php");

class DBEJSite extends DBESite
{
    const InvContactName = "InvContactName";
    const DelContactName = "DelContactName";

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
        $this->addColumn(self::InvContactName,
                         DA_STRING,
                         DA_ALLOW_NULL,
                         "CONCAT(icontact.con_first_name,' ',icontact.con_last_name)");
        $this->addColumn(self::DelContactName,
                         DA_STRING,
                         DA_ALLOW_NULL,
                         "CONCAT(dcontact.con_first_name,' ',dcontact.con_last_name)");
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

        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN contact AS icontact" .
            " ON " . $this->getDBColumnName(DBESite::InvoiceContactID) . " = icontact.con_contno" .
            " LEFT JOIN contact AS dcontact" .
            " ON " . $this->getDBColumnName(DBESite::DeliverContactID) . " = dcontact.con_contno" .
            " WHERE (" . $this->getDBColumnName(DBESite::Add1) . " LIKE '%" . $desc . "%'" .
            " OR " . $this->getDBColumnName(DBESite::Town) . " LIKE '%" . $desc . "%'" .
            " OR " . $this->getDBColumnName(DBESite::Postcode) . " LIKE '%" . $desc . "%')" .
            " AND " . $this->getDBColumnName(DBESite::CustomerID) . "=" . $this->getFormattedValue(DBESite::CustomerID);

        if ($activeFlag == 'Y') {
            $queryString .= ' AND add_active_flag = "Y"';
        }

        $queryString .= ' ORDER BY ' . $this->getDBColumnName(DBESite::Add1);

        $this->setQueryString($queryString);

        $ret = (parent::getRows());

        return $ret;
    }

    function getRow()
    {
        $this->setMethodName("getRow");
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN contact AS icontact" .
            " ON " . $this->getDBColumnName(DBESite::InvoiceContactID) . " = icontact.con_contno" .
            " LEFT JOIN contact AS dcontact" .
            " ON " . $this->getDBColumnName(DBESite::DeliverContactID) . " = dcontact.con_contno" .
            " WHERE " . $this->getDBColumnName(DBESite::CustomerID) . "=" . $this->getFormattedValue(DBESite::CustomerID) .
            " AND " . $this->getDBColumnName(DBESite::SiteNo) . "=" . $this->getFormattedValue(DBEJSite::SiteNo)
        );
        $ret = (parent::getRow());
        return $ret;
    }

    function getRowsByColumn($column, $activeFlag = 'Y')
    {
        $this->setMethodName("getRowsByColumn");

        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN contact AS icontact" .
            " ON " . $this->getDBColumnName(DBESite::InvoiceContactID) . " = icontact.con_contno" .
            " LEFT JOIN contact AS dcontact" .
            " ON " . $this->getDBColumnName(DBESite::DeliverContactID) . " = dcontact.con_contno" .
            " WHERE " . $this->getDBColumnName($column) . "=" . $this->getFormattedValue(DBESite::CustomerID);

        if ($activeFlag == 'Y') {
            $queryString .= ' AND add_active_flag = "Y"';
        }

        $this->setQueryString($queryString);

        $ret = (parent::getRows());
        return $ret;
    }
}