<?php /*
* Porhead table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEPorhead extends DBEntity
{

    const porheadID = "porheadID";
    const type = "type";
    const supplierID        = "supplierID";
    const supplierContactId = "contactID";
    const date              = "date";
    const ordheadID = "ordheadID";
    const supplierRef = "supplierRef";
    const directDeliveryFlag = "directDeliveryFlag";
    const payMethodID = "payMethodID";
    const invoices = "invoices";
    const printedFlag = "printedFlag";
    const userID = "userID";
    const vatCode = "vatCode";
    const vatRate = "vatRate";
    const locationID = "locationID";
    const orderUserID = "orderUserID";
    const orderDate = "orderDate";
    const requiredBy = "requiredBy";
    const deliveryConfirmedFlag = 'deliveryConfirmedFlag';
    const completionNotifiedFlag = 'completionNotifiedFlag';

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
        $this->setTableName("Porhead");
        $this->addColumn(
            self::porheadID,
            DA_ID,
            DA_NOT_NULL,
            "poh_porno"
        );
        $this->addColumn(
            self::type,
            DA_STRING,
            DA_NOT_NULL,
            "poh_type"
        );
        $this->addColumn(
            self::supplierID,
            DA_ID,
            DA_NOT_NULL,
            "poh_suppno"
        );
        $this->addColumn(
            self::supplierContactId,
            DA_ID,
            DA_NOT_NULL,
            "poh_contno"
        );
        $this->addColumn(
            self::date,
            DA_DATE,
            DA_NOT_NULL,
            "poh_date"
        );
        $this->addColumn(
            self::ordheadID,
            DA_ID,
            DA_ALLOW_NULL,
            "poh_ordno"
        );
        $this->addColumn(
            self::supplierRef,
            DA_STRING,
            DA_ALLOW_NULL,
            "poh_supp_ref"
        );
        $this->addColumn(
            self::directDeliveryFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "poh_direct_del"
        );
        $this->addColumn(
            self::payMethodID,
            DA_ID,
            DA_NOT_NULL,
            "poh_payno"
        );
        $this->addColumn(
            self::invoices,
            DA_STRING,
            DA_ALLOW_NULL,
            "poh_invoices"
        ); // what is this?
        $this->addColumn(
            self::printedFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "poh_printed"
        ); // redundant
        $this->addColumn(
            self::userID,
            DA_ID,
            DA_NOT_NULL,
            "poh_consno"
        );
        $this->addColumn(
            self::vatCode,
            DA_STRING,
            DA_NOT_NULL,
            "poh_vat_code"
        );
        $this->addColumn(
            self::vatRate,
            DA_FLOAT,
            DA_NOT_NULL,
            "poh_vat_rate"
        );
        $this->addColumn(
            self::locationID,
            DA_ID,
            DA_ALLOW_NULL,
            "poh_locno"
        );
        $this->addColumn(
            self::orderUserID,
            DA_ID,
            DA_ALLOW_NULL,
            "poh_ord_consno"
        );
        $this->addColumn(
            self::orderDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "poh_ord_date"
        );

        $this->addColumn(
            self::requiredBy,
            DA_DATE,
            DA_ALLOW_NULL,
            "poh_required_by"
        );

        $this->addColumn(
            self::deliveryConfirmedFlag,
            DA_YN,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::completionNotifiedFlag,
            DA_YN,
            DA_ALLOW_NULL
        );

        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function countNonAuthorisedRowsBySO($ordheadID)
    {
        $this->setMethodName('countNonAuthorisedRowsBySO');
        $this->setQueryString(
            "SELECT COUNT(*)" .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::ordheadID) . "=" . $ordheadID .
            " AND " . $this->getDBColumnName(self::type) . "<>'A'"
        );
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $this->resetQueryString();
                return ($this->getDBColumnValue(0));
            }
        }
    }

    /*
    * non-direct delivery rows by sales orderID
    */
    function countNonDirectRowsBySO($ordheadID)
    {
        $this->setMethodName('countNonDirectRowsBySO');
        $this->setQueryString(
            "SELECT COUNT(*)" .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::ordheadID) . "=" . $ordheadID .
            " AND " . $this->getDBColumnName(self::directDeliveryFlag) . "='N'"
        );
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $this->resetQueryString();
                return ($this->getDBColumnValue(0));
            }
        }
    }

    /*
    * Count Purchase Orders that have not been receieved for given sales order no
    */
    function countNonReceievedRowsByOrdheadID($ordheadID)
    {
        $this->setMethodName('countNonReceievedRowsByOrdheadID');
        $this->setQueryString(
            "SELECT COUNT(*)" .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::ordheadID) . "=" . $ordheadID .
            " AND " . $this->getDBColumnName(self::type) . " IN('I','P')"
        );
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $this->resetQueryString();
                return ($this->getDBColumnValue(0));
            }
        }
    }
}

?>