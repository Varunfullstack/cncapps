<?php /*
* Invline table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEInvline extends DBEntity
{
    const invheadID = "invheadID";
    const sequenceNo = "sequenceNo";
    const ordSequenceNo = "ordSequenceNo";
    const lineType = "lineType";
    const itemID = "itemID";
    const description = "description";
    const qty = "qty";
    const curUnitSale = "curUnitSale";
    const curUnitCost = "curUnitCost";
    const stockcat = "stockcat";

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
        $this->setTableName("invline");
        $this->addColumn(
            self::invheadID,
            DA_ID,
            DA_NOT_NULL,
            "inl_invno"
        );
        $this->addColumn(
            self::sequenceNo,
            DA_INTEGER,
            DA_NOT_NULL,
            "inl_line_no"
        );
        $this->addColumn(
            self::ordSequenceNo,
            DA_ID,
            DA_ALLOW_NULL,
            "inl_ord_line_no"
        );
        $this->addColumn(
            self::lineType,
            DA_STRING,
            DA_NOT_NULL,
            "inl_line_type"
        );
        $this->addColumn(
            self::itemID,
            DA_ID,
            DA_ALLOW_NULL,
            "inl_itemno"
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_ALLOW_NULL,
            "inl_desc"
        );
        $this->addColumn(
            self::qty,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "inl_qty"
        );
        $this->addColumn(
            self::curUnitSale,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "inl_unit_price"
        );
        $this->addColumn(
            self::curUnitCost,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "inl_cost_price"
        );
        $this->addColumn(
            self::stockcat,
            DA_STRING,
            DA_ALLOW_NULL,
            "inl_stockcat"
        );
        $this->setAddColumnsOff();
    }

    function deleteRowsByInvheadID()
    {
        $this->setMethodName('deleteRowsByInvheadID');
        if ($this->getValue(self::invheadID) == '') {
            $this->raiseError('invheadID not set');
        }
        $this->setQueryString(
            "DELETE FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::invheadID) . ' = ' . $this->getFormattedValue(self::invheadID)
        );
        $ret = ($this->runQuery());
        $this->resetQueryString();
        return $ret;
    }

    function getPKWhere()
    {
        return (
            $this->getDBColumnName(self::invheadID) . ' = ' . $this->getFormattedValue(self::invheadID) .
            " AND " . $this->getDBColumnName(self::sequenceNo) . ' = ' . $this->getFormattedValue(self::sequenceNo)
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
        if ($this->getValue(self::invheadID) == '') {
            $this->raiseError('InvheadID not set');
        }
        if ($this->getValue(self::sequenceNo) == '') {
            $this->raiseError('sequenceNo not set');
        }
        $this->setQueryString(
            'UPDATE ' . $this->getTableName() .
            ' SET ' . $this->getDBColumnName(self::sequenceNo) . '=' . $this->getDBColumnName(self::sequenceNo) . '+1' .
            ' WHERE ' . $this->getDBColumnName(self::invheadID) . ' = ' . $this->getFormattedValue(self::invheadID) .
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
        if ($this->getValue(self::invheadID) == '') {
            $this->raiseError('InvheadID not set');
        }
        if ($this->getValue(self::sequenceNo) == '') {
            $this->raiseError('sequenceNo not set');
        }
        $this->setQueryString(
            'UPDATE ' . $this->getTableName() .
            ' SET ' . $this->getDBColumnName(self::sequenceNo) . '=' . $this->getDBColumnName(self::sequenceNo) . '-1' .
            ' WHERE ' . $this->getDBColumnName(self::invheadID) . ' = ' . $this->getFormattedValue(self::invheadID) .
            ' AND ' . $this->getDBColumnName(self::sequenceNo) . ' >= ' . $this->getFormattedValue(self::sequenceNo)
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
        if ($this->getValue(self::invheadID) == '') {
            $this->raiseError('InvheadID not set');
        }
        if ($this->getValue(self::sequenceNo) == '') {
            $this->raiseError('sequenceNo not set');
        }
        // current row into temporary buffer row: sequenceNo = -99
        $this->setQueryString(
            'UPDATE ' . $this->getTableName() .
            ' SET ' . $this->getDBColumnName(self::sequenceNo) . ' = -99' .
            ' WHERE ' . $this->getDBColumnName(self::invheadID) . ' = ' . $this->getFormattedValue(self::invheadID) .
            ' AND ' . $this->getDBColumnName(self::sequenceNo) . ' = ' . $this->getFormattedValue(self::sequenceNo)
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
            ' SET ' . $this->getDBColumnName(self::sequenceNo) . ' = ' . $this->getFormattedValue(self::sequenceNo) .
            ' WHERE ' . $this->getDBColumnName(self::invheadID) . ' = ' . $this->getFormattedValue(self::invheadID) .
            ' AND ' . $this->getDBColumnName(self::sequenceNo) . ' = ' . $sequenceNo
        );
        $this->runQuery();
        $this->resetQueryString();
        // Move current row from temp
        $this->setQueryString(
            'UPDATE ' . $this->getTableName() .
            ' SET ' . $this->getDBColumnName(self::sequenceNo) . ' = ' . $sequenceNo .
            ' WHERE ' . $this->getDBColumnName(self::invheadID) . ' = ' . $this->getFormattedValue(self::invheadID) .
            ' AND ' . $this->getDBColumnName(self::sequenceNo) . ' = -99'
        );
        $ret = $this->runQuery();
        $this->resetQueryString();
        return $ret;
    }

    /**
     * get maximum sequence no for this order
     * @access public
     * @param $invheadID
     * @return bool Success
     */
    function getMaxSequenceNo($invheadID)
    {
        $this->setMethodName("getMaxSequenceNo");
        if ($invheadID == '') {
            $this->raiseError('invheadID not passed');
            return FALSE;
        }
        $this->setQueryString(
            "SELECT MAX(" . $this->getDBColumnName(self::sequenceNo) . ")" .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::invheadID) . "=" . $invheadID
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