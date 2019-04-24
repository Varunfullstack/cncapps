<?php /*
* Porline totals query
* returns count of OS values by order
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEPorlineTotals extends DBEntity
{
    const porheadID = "porheadID";
    const qtyOrdered = "qtyOrdered";
    const qtyReceived = "qtyReceived";
    const qtyInvoiced = "qtyInvoiced";

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
        $this->setTableName("Porline");
        $this->addColumn(self::porheadID, DA_ID, DA_NOT_NULL, "pol_porno");
        $this->addColumn(self::qtyOrdered, DA_FLOAT, DA_NOT_NULL, "SUM(pol_qty_ord)");
        $this->addColumn(self::qtyReceived, DA_FLOAT, DA_ALLOW_NULL, "SUM(pol_qty_rec)");
        $this->addColumn(self::qtyInvoiced, DA_FLOAT, DA_ALLOW_NULL, "SUM(pol_qty_inv)");
        $this->setAddColumnsOff();
    }

    /**
     * current order totals
     * @param $porheadID
     * @return bool
     */
    function getRow($porheadID = null)
    {
        $this->setValue(self::porheadID, $porheadID);

        $this->setQueryString(
            "SELECT " .
            $this->getDBColumnName(self::porheadID) . "," .
            $this->getDBColumnName(self::qtyOrdered) . "," .
            $this->getDBColumnName(self::qtyReceived) . "," .
            $this->getDBColumnName(self::qtyInvoiced) .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::porheadID) . "=" . $this->prepareForSQL(self::porheadID) .
            " GROUP BY " . $this->getDBColumnName(self::porheadID)
        );
        return (parent::getRow());
    }
}