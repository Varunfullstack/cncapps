<?php /*
* Porline totals query
* returns count of OS values by order
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEPorlineTotals extends DBEntity
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
        $this->setTableName("Porline");
        $this->addColumn("porheadID", DA_ID, DA_NOT_NULL, "pol_porno");
        $this->addColumn("qtyOrdered", DA_FLOAT, DA_NOT_NULL, "SUM(pol_qty_ord)");
        $this->addColumn("qtyReceived", DA_FLOAT, DA_ALLOW_NULL, "SUM(pol_qty_rec)");
        $this->addColumn("qtyInvoiced", DA_FLOAT, DA_ALLOW_NULL, "SUM(pol_qty_inv)");
        $this->setAddColumnsOff();
    }

    /**
     * current order totals
     */
    function getRow()
    {
        $this->setQueryString(
            "SELECT " .
            $this->getDBColumnName('porheadID') . "," .
            $this->getDBColumnName('qtyOrdered') . "," .
            $this->getDBColumnName('qtyReceived') . "," .
            $this->getDBColumnName('qtyInvoiced') .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName('porheadID') . "=" . $this->getFormattedValue('porheadID') .
            " GROUP BY " . $this->getDBColumnName('porheadID')
        );
        return (parent::getRow());
    }
}

?>