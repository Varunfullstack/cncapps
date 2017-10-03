<?php /*
* Supplier table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBESupplier extends DBEntity
{
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
        $this->setTableName("Supplier");
        $this->addColumn("supplierID", DA_ID, DA_NOT_NULL, "sup_suppno");
        $this->addColumn("name", DA_STRING, DA_NOT_NULL, "sup_name");
        $this->addColumn("add1", DA_STRING, DA_NOT_NULL, "sup_add1");
        $this->addColumn("add2", DA_STRING, DA_ALLOW_NULL, "sup_add2");
        $this->addColumn("town", DA_STRING, DA_NOT_NULL, "sup_town");
        $this->addColumn("county", DA_STRING, DA_ALLOW_NULL, "sup_county");
        $this->addColumn("postcode", DA_STRING, DA_NOT_NULL, "sup_postcode");
        $this->addColumn("phone", DA_STRING, DA_ALLOW_NULL, "sup_phone");
        $this->addColumn("fax", DA_STRING, DA_ALLOW_NULL, "sup_fax");
        $this->addColumn("webSiteURL", DA_STRING, DA_ALLOW_NULL, "sup_web_site_url");
        $this->addColumn("payMethodID", DA_ID, DA_NOT_NULL, "sup_payno");
        $this->addColumn("creditLimit", DA_STRING, DA_ALLOW_NULL, "sup_credit_limit");
        $this->addColumn("scopeID", DA_ID, DA_NOT_NULL, "sup_scopeno");
        $this->addColumn("contactID", DA_ID, DA_NOT_NULL, "sup_contno");
        $this->addColumn("cncAccountNo", DA_STRING, DA_ALLOW_NULL, "sup_cnc_accno");
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

}

/**
 * table join to contact for name
 */
class DBEJSupplier extends DBESupplier
{
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
        $this->addColumn("contactName", DA_STRING, DA_ALLOW_NULL, "CONCAT(contact.con_first_name,' ',contact.con_last_name)");
        $this->setAddColumnsOff();
    }

    /**
     * Get rows by name match
     * @access public
     * @return bool Success
     */
    function getRowsByNameMatch($name, $address)
    {
        $this->setMethodName("getRowsByNameMatch");
        $ret = FALSE;

        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN contact" .
            " ON " . $this->getDBColumnName('contactID') . " = contact.con_contno" .
            " WHERE 1=1";

        if ($name != '') {
            $queryString .=
                " AND " . $this->getDBColumnName('name') . " LIKE '%" . $name . "%'";
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
            " ORDER BY " . $this->getDBColumnName('name');

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
            " LEFT JOIN contact" .
            " ON " . $this->getDBColumnName('contactID') . " = contact.con_contno" .
            " WHERE " . $this->getDBColumnName('supplierID') . "=" . $this->getFormattedValue('supplierID')
        );
        $ret = (parent::getRow());
        return $ret;
    }
}

?>