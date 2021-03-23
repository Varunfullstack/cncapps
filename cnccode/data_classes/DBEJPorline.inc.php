<?php /*
* Porline to item join
* @authors Karim Ahmed
* @access public
*/

use CNCLTD\Exceptions\ColumnOutOfRangeException;

require_once($cfg["path_dbe"] . "/DBEPorline.inc.php");

class DBEJPorline extends DBEPorline
{
    const itemDescription = "itemDescription";
    const partNo = "partNo";
    const excludeFromPOCompletion = "excludeFromPOCompletion";

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
        $this->addColumn(
            self::excludeFromPOCompletion,
            DA_YN,
            DA_NOT_NULL
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
            throw new ColumnOutOfRangeException($column);
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " LEFT JOIN item ON " . $this->getDBColumnName(
                self::itemID
            ) . "=itm_itemno" . " WHERE " . $this->getDBColumnName($ixColumn) . "=" . $this->getFormattedValue(
                $ixColumn
            ) . " ORDER BY " . $this->getDBColumnName(self::sequenceNo)
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
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " LEFT JOIN item ON " . $this->getDBColumnName(
                self::itemID
            ) . "=itm_itemno" . " WHERE " . $this->getDBColumnName(self::porheadID) . "=" . $this->getFormattedValue(
                self::porheadID
            ) . " AND " . $this->getDBColumnName(self::sequenceNo) . "=" . $this->getFormattedValue(self::sequenceNo)
        );
        return (parent::getRow());
    }

    /**
     * Return count of rows that still have items to be received
     */
    function countOutstandingRows()
    {
        if ($this->getValue(self::porheadID) == '') {
            $this->raiseError('porheadID not set');
        }
        $this->setQueryString(
            "SELECT COUNT(*)" . " FROM " . $this->getTableName() . " LEFT JOIN item ON " . $this->getDBColumnName(
                self::itemID
            ) . "= itm_itemno" . " WHERE " . $this->getDBColumnName(self::porheadID) . "=" . $this->getFormattedValue(
                self::porheadID
            ) . " AND " . $this->getDBColumnName(
                self::excludeFromPOCompletion
            ) . " = 'N' " . " AND " . $this->getDBColumnName(self::qtyReceived) . " < " . $this->getDBColumnName(
                self::qtyOrdered
            )
        );
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $this->resetQueryString();
                return ($this->getDBColumnValue(0));
            }
        }
        return 0;
    }
}
