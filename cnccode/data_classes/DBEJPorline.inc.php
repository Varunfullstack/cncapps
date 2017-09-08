<?php /*
* Porline to item join
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBEPorline.inc.php");

class DBEJPorline extends DBEPorline
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
        $this->setAddColumnsOn();
        $this->addColumn("itemDescription", DA_STRING, DA_ALLOW_NULL, "itm_desc");
        $this->addColumn("partNo", DA_STRING, DA_ALLOW_NULL, "itm_unit_of_sale");
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    /**
     * Return rows from DB by column value
     * @access public
     * @return bool Success
     */
    function getRowsByColumn($column)
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
            " LEFT JOIN item ON " . $this->getDBColumnName('itemID') . "=itm_itemno" .
            " WHERE " . $this->getDBColumnName($ixColumn) . "=" . $this->getFormattedValue($ixColumn) .
            " ORDER BY " . $this->getDBColumnName('sequenceNo')
        );
        return ($this->getRows());
    }

    function getRowByPorheadIDSequenceNo()
    {
        if ($this->getValue('porheadID') == '') {
            $this->raiseError('porheadID not set');
        }
        if ($this->getValue('sequenceNo') == '') {
            $this->raiseError('sequenceNo not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN item ON " . $this->getDBColumnName('itemID') . "=itm_itemno" .
            " WHERE " . $this->getDBColumnName('porheadID') . "=" . $this->getFormattedValue('porheadID') .
            " AND " . $this->getDBColumnName('sequenceNo') . "=" . $this->getFormattedValue('sequenceNo')
        );
        return (parent::getRow());
    }
}

?>