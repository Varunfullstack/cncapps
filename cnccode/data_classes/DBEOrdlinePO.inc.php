<?php /*
* Ordline purchase order rows
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEOrdlinePO extends DBEntity
{

    const supplierID  = "supplierID";
    const stockcat    = "stockcat";
    const itemID      = "itemID";
    const curUnitCost = "curUnitCost";
    const qtyOrdered  = "qtyOrdered";

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
        $this->setTableName("Ordline");
        $this->addColumn(self::supplierID, DA_ID, DA_ALLOW_NULL, "odl_suppno");
        $this->addColumn(self::stockcat, DA_STRING, DA_ALLOW_NULL, "odl_stockcat");
        $this->addColumn(self::itemID, DA_ID, DA_ALLOW_NULL, "odl_itemno");
        $this->addColumn(self::curUnitCost, DA_FLOAT, DA_ALLOW_NULL, "odl_d_unit");
        $this->addColumn(self::qtyOrdered, DA_FLOAT, DA_ALLOW_NULL, "odl_qty_ord");
        $this->setAddColumnsOff();
    }

    /**
     * get all rows ready for generation of purchase orders
     * @access public
     * @param null $ordheadID
     * @return bool
     */
    function getRowsReadyForGenerationOfPurchaseOrders($ordheadID = null)
    {
        $this->setMethodName("getRows");
        if (!$ordheadID) {
            $this->raiseError('ordheadID not set');
        }
        $this->setQueryString(
            "SELECT DISTINCT  odl_suppno, odl_stockcat, odl_itemno, odl_d_unit, SUM(odl_qty_ord) FROM ordline WHERE odl_ordno = {$ordheadID} AND odl_type = 'I' GROUP BY odl_suppno, odl_stockcat, odl_itemno, odl_d_unit ORDER BY odl_suppno, odl_item_no"
        );
        return (parent::getRows());
    }
}
