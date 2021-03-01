<?php /*
* Supplier table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBESupplier extends DBEntity
{
    const supplierID   = "supplierID";
    const name         = "name";
    const add1         = "add1";
    const add2         = "add2";
    const town         = "town";
    const county       = "county";
    const postcode     = "postcode";
    const phone        = "phone";
    const fax          = "fax";
    const webSiteURL   = "webSiteURL";
    const payMethodID  = "payMethodID";
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
            self::cncAccountNo,
            DA_STRING,
            DA_ALLOW_NULL,
            "sup_cnc_accno"
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}