<?php /*
* Porline table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEPorline extends DBEntity
{
    const porheadID = "porheadID";
    const sequenceNo = "sequenceNo";
    const itemID = "itemID";
    const qtyOrdered = "qtyOrdered";
    const qtyReceived = "qtyReceived";
    const qtyInvoiced = "qtyInvoiced";
    const curUnitCost = "curUnitCost";
    const stockcat = "stockcat";
    const expectedDate = "expectedDate";

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
        $this->addColumn(
            self::porheadID,
            DA_ID,
            DA_NOT_NULL,
            "pol_porno"
        );
        $this->addColumn(
            self::sequenceNo,
            DA_INTEGER,
            DA_NOT_NULL,
            "pol_lineno"
        );
        $this->addColumn(
            self::itemID,
            DA_ID,
            DA_NOT_NULL,
            "pol_itemno"
        );
        $this->addColumn(
            self::qtyOrdered,
            DA_FLOAT,
            DA_NOT_NULL,
            "pol_qty_ord"
        );
        $this->addColumn(
            self::qtyReceived,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "pol_qty_rec"
        );
        $this->addColumn(
            self::qtyInvoiced,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "pol_qty_inv"
        );
        $this->addColumn(
            self::curUnitCost,
            DA_FLOAT,
            DA_NOT_NULL,
            "pol_cost"
        );
        $this->addColumn(
            self::stockcat,
            DA_STRING,
            DA_NOT_NULL,
            "pol_stockcat"
        );
        $this->addColumn(
            self::expectedDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "pol_exp_date"
        );
        $this->setAddColumnsOff();
    }

    function deleteRowsByOrdheadID()
    {
        $this->setMethodName('deleteRowsByOrdheadID');
        if ($this->getValue(self::porheadID) == '') {
            $this->raiseError('porheadID not set');
        }
        $this->setQueryString(
            "DELETE FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::porheadID) . ' = ' . $this->getValue(self::porheadID)
        );
        $ret = ($this->runQuery());
        $this->resetQueryString();
        return $ret;
    }

    function getPKWhere()
    {
        return (
            $this->getDBColumnName(self::porheadID) . ' = ' . $this->getValue(self::porheadID) .
            " AND " . $this->getDBColumnName(self::sequenceNo) . ' = ' . $this->getValue(self::sequenceNo)
        );
    }

    /**
     * Shuffle down rows ready for insertion of new one
     * @access public
     * @return bool
     */
    function shuffleRowsDown()
    {
        $this->setMethodName("shuffleRowsDown");
        if ($this->getValue(self::porheadID) == '') {
            $this->raiseError('porheadID not set');
        }
        if ($this->getValue(self::sequenceNo) == '') {
            $this->raiseError('sequenceNo not set');
        }

        $this->setQueryString(
            'UPDATE ' . $this->getTableName() .
            ' SET ' . $this->getDBColumnName(self::sequenceNo) . '=' . $this->getDBColumnName(self::sequenceNo) . '+1' .
            ' WHERE ' . $this->getDBColumnName(self::porheadID) . ' = ' . $this->getFormattedValue(self::porheadID) .
            ' AND ' . $this->getDBColumnName(self::sequenceNo) . ' >= ' . $this->getFormattedValue(self::sequenceNo)
        );
        $ret = $this->runQuery();
        $this->resetQueryString();
        return $ret;
    }

    /**
     * Shuffle up rows after deletion
     * @access public
     * @return bool
     */
    function shuffleRowsUp()
    {
        $this->setMethodName("shuffleRowsUp");
        if ($this->getValue(self::porheadID) == '') {
            $this->raiseError('porheadID not set');
        }
        if ($this->getValue(self::sequenceNo) == '') {
            $this->raiseError('sequenceNo not set');
        }
        $this->setQueryString(
            'UPDATE ' . $this->getTableName() .
            ' SET ' . $this->getDBColumnName(self::sequenceNo) . '=' . $this->getDBColumnName(self::sequenceNo) . '-1' .
            ' WHERE ' . $this->getDBColumnName(self::porheadID) . ' = ' . $this->getValue(self::porheadID) .
            ' AND ' . $this->getDBColumnName(self::sequenceNo) . ' >= ' . $this->getValue(self::sequenceNo)
        );
        $ret = $this->runQuery();
        $this->resetQueryString();
        return $ret;
    }

    /**
     * Move given row down in the sequence
     * Swaps sequence numbers of 2 rows
     * @access public
     * @param string $direction
     * @return bool
     */
    function moveRow($direction = 'UP')
    {
        $this->setMethodName("moveRow");
        if ($this->getValue(self::porheadID) == '') {
            $this->raiseError('porheadID not set');
        }
        if ($this->getValue(self::sequenceNo) == '') {
            $this->raiseError('sequenceNo not set');
        }
        // current row into temporary buffer row: sequenceNo = -99
        $this->setQueryString(
            'UPDATE ' . $this->getTableName() .
            ' SET ' . $this->getDBColumnName(self::sequenceNo) . ' = -99' .
            ' WHERE ' . $this->getDBColumnName(self::porheadID) . ' = ' . $this->getValue(self::porheadID) .
            ' AND ' . $this->getDBColumnName(self::sequenceNo) . ' = ' . $this->getValue(self::sequenceNo)
        );
        $this->runQuery();
        $this->resetQueryString();
        // Move row next to this one
        if ($direction == 'UP') {
            $sequenceNo = $this->getValue(self::sequenceNo) - 1;
        } else {            // down
            $sequenceNo = $this->getValue(self::sequenceNo) + 1;
        }
        $this->setQueryString(
            'UPDATE ' . $this->getTableName() .
            ' SET ' . $this->getDBColumnName(self::sequenceNo) . ' = ' . $this->getValue(self::sequenceNo) .
            ' WHERE ' . $this->getDBColumnName(self::porheadID) . ' = ' . $this->getValue(self::porheadID) .
            ' AND ' . $this->getDBColumnName(self::sequenceNo) . ' = ' . $sequenceNo
        );
        $this->runQuery();
        $this->resetQueryString();
        // Move current row from temp
        $this->setQueryString(
            'UPDATE ' . $this->getTableName() .
            ' SET ' . $this->getDBColumnName(self::sequenceNo) . ' = ' . $sequenceNo .
            ' WHERE ' . $this->getDBColumnName(self::porheadID) . ' = ' . $this->getValue(self::porheadID) .
            ' AND ' . $this->getDBColumnName(self::sequenceNo) . ' = -99'
        );
        $ret = $this->runQuery();
        $this->resetQueryString();
        return $ret;
    }


}
