<?php /*
* ordlinesco table join to ordheadsco table for new ID
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEJOrdlineSCO extends DBEntity
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
        $this->setTableName("ordline_sco");
        $this->addColumn("lineType", DA_STRING, DA_NOT_NULL, "odl_type");
        $this->addColumn("ordheadID", DA_ID, DA_NOT_NULL, "odl_ordno");
        $this->addColumn("sequenceNo", DA_INTEGER, DA_NOT_NULL, "odl_item_no");
        $this->addColumn("customerID", DA_ID, DA_NOT_NULL, "odl_custno");
        $this->addColumn("itemID", DA_ID, DA_ALLOW_NULL, "odl_itemno");
        $this->addColumn("stockcat", DA_STRING, DA_ALLOW_NULL, "odl_stockcat");
        $this->addColumn("description", DA_STRING, DA_ALLOW_NULL, "odl_desc");
        $this->addColumn("qtyOrdered", DA_FLOAT, DA_ALLOW_NULL, "odl_qty_ord");
        $this->addColumn("qtyDespatched", DA_FLOAT, DA_ALLOW_NULL, "odl_qty_desp");
        $this->addColumn("qtyLastDespatched", DA_FLOAT, DA_ALLOW_NULL, "odl_qty_last_desp");
        $this->addColumn("supplierID", DA_ID, DA_ALLOW_NULL, "odl_suppno");
        $this->addColumn("curUnitCost", DA_FLOAT, DA_ALLOW_NULL, "odl_d_unit");
        $this->addColumn("curTotalCost", DA_FLOAT, DA_ALLOW_NULL, "odl_d_total");
        $this->addColumn("curUnitSale", DA_FLOAT, DA_ALLOW_NULL, "odl_e_unit");
        $this->addColumn("curTotalSale", DA_FLOAT, DA_ALLOW_NULL, "odl_e_total");
        $this->addColumn("newOrdheadID", DA_ID, DA_ALLOW_NULL);
        $this->addColumn("processedFlag", DA_BOOLEAN, DA_ALLOW_NULL);
        $this->setAddColumnsOff();
    }

    function getNonProcessedRows()
    {
        $this->setMethodName('getNonProcessedRows');
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " INNER JOIN ordhead_sco ON " . $this->getDBColumnName("ordheadID") . "=odh_ordno" .
            " WHERE " . $this->getDBColumnName("processedFlag") . " = 0"
        );
        return (parent::runQuery());
    }
}

?>