<?php

require_once($cfg["path_dbe"] . "/DBESupplier.inc.php");

class DBEJSupplier extends DBESupplier
{
    const contactName = "contactName";

    /**
     * calls constructor()
     * @access public
     * @param $owner
     * @internal param $void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setAddColumnsOn();
        $this->addColumn(
            self::contactName,
            DA_STRING,
            DA_ALLOW_NULL,
            "CONCAT(contact.con_first_name,' ',contact.con_last_name)"
        );
        $this->setAddColumnsOff();
    }

    /**
     * Get rows by name match
     * @access public
     * @param $name
     * @param $address
     * @return bool Success
     */
    function getRowsByNameMatch($name,
                                $address
    )
    {
        $this->setMethodName("getRowsByNameMatch");

        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN contact" .
            " ON " . $this->getDBColumnName(self::contactID) . " = contact.con_contno" .
            " WHERE 1=1";

        if ($name != '') {
            $queryString .=
                " AND " . $this->getDBColumnName(self::name) . " LIKE '%" . $name . "%'";
        }

        if ($address != '') {
            $queryString .=
                " AND (sup_town LIKE '%" . $address . "%'" .
                " OR sup_add1 LIKE '%" . $address . "%'" .
                " OR sup_add2 LIKE '%" . $address . "%'" .
                " OR sup_postcode LIKE '" . $address . "%'" .
                " OR sup_county LIKE '%" . $address . "%')";
        }

        $queryString .=
            " ORDER BY " . $this->getDBColumnName(self::name);

        $this->setQueryString($queryString);

        $ret = (parent::getRows());
        return $ret;
    }

    function getRow($supplierID = null)
    {
        $this->setMethodName("getRow");
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN contact" .
            " ON " . $this->getDBColumnName(self::contactID) . " = contact.con_contno" .
            " WHERE " . $this->getDBColumnName(self::supplierID) . "=" . $this->getFormattedValue(self::supplierID)
        );
        $ret = (parent::getRow());
        return $ret;
    }
}