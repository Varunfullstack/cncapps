<?php /*
* Porline to item join
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBEPorline.inc.php");

class DBEJPorline extends DBEPorline
{
    const itemDescription = "itemDescription";
    const partNo = "partNo";

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
        $this->setAddColumnsOn();
        $this->addColumn(
            self::itemDescription,
            DA_STRING,
            DA_ALLOW_NULL,
            "itm_desc"
        );
        $this->addColumn(
            self::partNo,
            DA_STRING,
            DA_ALLOW_NULL,
            "itm_unit_of_sale"
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    /**
     * Return rows from DB by column value
     * @access public
     * @param $column
     * @param $sortColumn
     * @return bool Success
     */
    function getRowsByColumn($column, $sortColumn = null)
    {
        $this->setMethodName("getRowsByColumn");
        if ($column == '') {
            $this->raiseError('Column not passed');
            return FALSE;
        }
        $ixColumn = $this->columnExists($column);
        if ($ixColumn == DA_OUT_OF_RANGE) {
            $this->raiseError("Column " . $column . " out of range");
            return DA_OUT_OF_RANGE;
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN item ON " . $this->getDBColumnName(self::itemID) . "=itm_itemno" .
            " WHERE " . $this->getDBColumnName($ixColumn) . "=" . $this->getFormattedValue($ixColumn) .
            " ORDER BY " . $this->getDBColumnName(self::sequenceNo)
        );
        return ($this->getRows());
    }

    function getRowByPorheadIDSequenceNo()
    {
        if ($this->getValue(self::porheadID) == '') {
            $this->raiseError('porheadID not set');
        }
        if ($this->getValue(self::sequenceNo) == '') {
            $this->raiseError('sequenceNo not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN item ON " . $this->getDBColumnName(self::itemID) . "=itm_itemno" .
            " WHERE " . $this->getDBColumnName(self::porheadID) . "=" . $this->getFormattedValue(self::porheadID) .
            " AND " . $this->getDBColumnName(self::sequenceNo) . "=" . $this->getFormattedValue(self::sequenceNo)
        );
        return (parent::getRow());
    }
}
