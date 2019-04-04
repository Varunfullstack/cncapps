<?php /*
* Supplier table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBESupplier extends DBEntity
{
    const supplierID = "supplierID";
    const name = "name";
    const add1 = "add1";
    const add2 = "add2";
    const town = "town";
    const county = "county";
    const postcode = "postcode";
    const phone = "phone";
    const fax = "fax";
    const webSiteURL = "webSiteURL";
    const payMethodID = "payMethodID";
    const creditLimit = "creditLimit";
    const scopeID = "scopeID";
    const contactID = "contactID";
    const cncAccountNo = "cncAccountNo";

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
        $this->setTableName("Supplier");
        $this->addColumn(
            self::supplierID,
            DA_ID,
            DA_NOT_NULL,
            "sup_suppno"
        );
        $this->addColumn(
            self::name,
            DA_STRING,
            DA_NOT_NULL,
            "sup_name"
        );
        $this->addColumn(
            self::add1,
            DA_STRING,
            DA_NOT_NULL,
            "sup_add1"
        );
        $this->addColumn(
            self::add2,
            DA_STRING,
            DA_ALLOW_NULL,
            "sup_add2"
        );
        $this->addColumn(
            self::town,
            DA_STRING,
            DA_NOT_NULL,
            "sup_town"
        );
        $this->addColumn(
            self::county,
            DA_STRING,
            DA_ALLOW_NULL,
            "sup_county"
        );
        $this->addColumn(
            self::postcode,
            DA_STRING,
            DA_NOT_NULL,
            "sup_postcode"
        );
        $this->addColumn(
            self::phone,
            DA_STRING,
            DA_ALLOW_NULL,
            "sup_phone"
        );
        $this->addColumn(
            self::fax,
            DA_STRING,
            DA_ALLOW_NULL,
            "sup_fax"
        );
        $this->addColumn(
            self::webSiteURL,
            DA_STRING,
            DA_ALLOW_NULL,
            "sup_web_site_url"
        );
        $this->addColumn(
            self::payMethodID,
            DA_ID,
            DA_NOT_NULL,
            "sup_payno"
        );
        $this->addColumn(
            self::creditLimit,
            DA_STRING,
            DA_ALLOW_NULL,
            "sup_credit_limit"
        );
        $this->addColumn(
            self::scopeID,
            DA_ID,
            DA_NOT_NULL,
            "sup_scopeno"
        );
        $this->addColumn(
            self::contactID,
            DA_ID,
            DA_NOT_NULL,
            "sup_contno"
        );
        $this->addColumn(
            self::cncAccountNo,
            DA_STRING,
            DA_ALLOW_NULL,
            "sup_cnc_accno"
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

}

/**
 * table join to contact for name
 */
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
     * @return bool Success
     */
    function getRowsByNameMatch($name,
                                $address
    )
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