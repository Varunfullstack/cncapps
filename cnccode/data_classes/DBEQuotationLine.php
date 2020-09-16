<?php
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEQuotationLine extends DBEntity
{

    const id = "id";
    const quotationID = "quotationID";
    const sequenceNo = "sequenceNo";
    const lineType = "lineType";
    const ordheadID = "ordheadID";
    const customerID = "customerID";
    const itemID = "itemID";
    const stockcat = "stockcat";
    const description = "description";
    const qtyOrdered = "qtyOrdered";
    const qtyDespatched = "qtyDespatched";
    const qtyLastDespatched = "qtyLastDespatched";
    const supplierID = "supplierID";
    const curUnitCost = "curUnitCost";
    const curTotalCost = "curTotalCost";
    const curUnitSale = "curUnitSale";
    const curTotalSale = "curTotalSale";
    const renewalCustomerItemID = "renewalCustomerItemID";
    const isRecurring = "isRecurring";

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
        $this->setTableName("quotationlines");
        $this->addColumn(
            self::id,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::lineType,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::quotationID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::ordheadID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::sequenceNo,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::customerID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::itemID,
            DA_ID,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::stockcat,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::qtyOrdered,
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::qtyDespatched,
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::qtyLastDespatched,
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::supplierID,
            DA_ID,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::curUnitCost,
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::curTotalCost,
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::curUnitSale,
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::curTotalSale,
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::renewalCustomerItemID,
            DA_ID,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::isRecurring,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            false
        );
        $this->setAddColumnsOff();
        $this->setPK(0);
    }

}