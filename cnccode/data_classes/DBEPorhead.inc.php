<?php /*
* Porhead table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEPorhead extends DBEntity
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
        $this->setTableName("Porhead");
        $this->addColumn("porheadID", DA_ID, DA_NOT_NULL, "poh_porno");
        $this->addColumn("type", DA_STRING, DA_NOT_NULL, "poh_type");
        $this->addColumn("supplierID", DA_ID, DA_NOT_NULL, "poh_suppno");
        $this->addColumn("contactID", DA_ID, DA_NOT_NULL, "poh_contno");
        $this->addColumn("date", DA_DATE, DA_NOT_NULL, "poh_date");
        $this->addColumn("ordheadID", DA_ID, DA_ALLOW_NULL, "poh_ordno");
        $this->addColumn("supplierRef", DA_STRING, DA_ALLOW_NULL, "poh_supp_ref");
        $this->addColumn("directDeliveryFlag", DA_YN, DA_ALLOW_NULL, "poh_direct_del");
        $this->addColumn("payMethodID", DA_ID, DA_NOT_NULL, "poh_payno");
        $this->addColumn("invoices", DA_STRING, DA_ALLOW_NULL, "poh_invoices"); // what is this?
        $this->addColumn("printedFlag", DA_YN, DA_ALLOW_NULL, "poh_printed"); // redundant
        $this->addColumn("userID", DA_ID, DA_NOT_NULL, "poh_consno");
        $this->addColumn("vatCode", DA_STRING, DA_NOT_NULL, "poh_vat_code");
        $this->addColumn("vatRate", DA_FLOAT, DA_NOT_NULL, "poh_vat_rate");
        $this->addColumn("locationID", DA_ID, DA_ALLOW_NULL, "poh_locno");
        $this->addColumn("orderUserID", DA_ID, DA_ALLOW_NULL, "poh_ord_consno");
        $this->addColumn("orderDate", DA_DATE, DA_ALLOW_NULL, "poh_ord_date");
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function countNonAuthorisedRowsBySO($ordheadID)
    {
        $this->setMethodName('countNonAuthorisedRowsBySO');
        $this->setQueryString(
            "SELECT COUNT(*)" .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName('ordheadID') . "=" . $ordheadID .
            " AND " . $this->getDBColumnName('type') . "<>'A'"
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
            " WHERE " . $this->getDBColumnName('ordheadID') . "=" . $ordheadID .
            " AND " . $this->getDBColumnName('directDeliveryFlag') . "='N'"
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
            " WHERE " . $this->getDBColumnName('ordheadID') . "=" . $ordheadID .
            " AND " . $this->getDBColumnName('type') . " IN('I','P')"
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