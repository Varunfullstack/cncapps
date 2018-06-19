<?php /*
* Ordline table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEOrdline extends DBEntity
{
    const lineType = "lineType";
    const ordheadID = "ordheadID";
    const sequenceNo = "sequenceNo";
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
        $this->setTableName("ordline");
        $this->addColumn(
            self::lineType,
            DA_STRING,
            DA_NOT_NULL,
            "odl_type"
        );
        $this->addColumn(
            self::ordheadID,
            DA_ID,
            DA_NOT_NULL,
            "odl_ordno"
        );
        $this->addColumn(
            self::sequenceNo,
            DA_INTEGER,
            DA_NOT_NULL,
            "odl_item_no"
        );
        $this->addColumn(
            self::customerID,
            DA_ID,
            DA_NOT_NULL,
            "odl_custno"
        );
        $this->addColumn(
            self::itemID,
            DA_ID,
            DA_ALLOW_NULL,
            "odl_itemno"
        );
        $this->addColumn(
            self::stockcat,
            DA_STRING,
            DA_ALLOW_NULL,
            "odl_stockcat"
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_ALLOW_NULL,
            "odl_desc"
        );
        $this->addColumn(
            self::qtyOrdered,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "odl_qty_ord"
        );
        $this->addColumn(
            self::qtyDespatched,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "odl_qty_desp"
        );
        $this->addColumn(
            self::qtyLastDespatched,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "odl_qty_last_desp"
        );
        $this->addColumn(
            self::supplierID,
            DA_ID,
            DA_ALLOW_NULL,
            "odl_suppno"
        );
        $this->addColumn(
            self::curUnitCost,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "odl_d_unit"
        );
        $this->addColumn(
            self::curTotalCost,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "odl_d_total"
        );
        $this->addColumn(
            self::curUnitSale,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "odl_e_unit"
        );
        $this->addColumn(
            self::curTotalSale,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "odl_e_total"
        );
        $this->addColumn(
            self::renewalCustomerItemID,
            DA_ID,
            DA_ALLOW_NULL,
            "odl_renewal_cuino"
        );
        $this->setAddColumnsOff();
    }

    function deleteRowsByOrderID()
    {
        if ($this->getValue('ordheadID') == '') {
            $this->raiseError('ordheadID not set');
        }
        $this->setQueryString(
            'DELETE FROM ' . $this->getTableName() . ' WHERE ' . $this->getDBColumnName(
                'ordheadID'
            ) . ' = ' . $this->getValue('ordheadID')
        );
        return (parent::runQuery());
    }

    function getPKWhere()
    {
        return (
            $this->getDBColumnName('ordheadID') . ' = ' . $this->getValue('ordheadID') .
            " AND " . $this->getDBColumnName('sequenceNo') . ' = ' . $this->getValue('sequenceNo')
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
        if ($this->getValue('ordheadID') == '') {
            $this->raiseError('ordheadID not set');
        }
        if ($this->getValue('sequenceNo') == '') {
            $this->setValue(
                'sequenceNo',
                0
            );
        }
        $sequenceNo = $this->getDBColumnName('sequenceNo');
        $ordheadID = $this->getDBColumnName('ordheadID');
        $this->setQueryString(
            'UPDATE ' . $this->getTableName() .
            ' SET ' . $this->getDBColumnName('sequenceNo') . '=' . $this->getDBColumnName('sequenceNo') . '+1' .
            ' WHERE ' . $this->getDBColumnName('ordheadID') . ' = ' . $this->getFormattedValue('ordheadID') .
            ' AND ' . $this->getDBColumnName('sequenceNo') . ' >= ' . $this->getFormattedValue('sequenceNo')
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
        if ($this->getValue('ordheadID') == '') {
            $this->raiseError('ordheadID not set');
        }
        if ($this->getValue('sequenceNo') == '') {
            $this->setValue(
                'sequenceNo',
                0
            );
        }
        $this->setQueryString(
            'UPDATE ' . $this->getTableName() .
            ' SET ' . $this->getDBColumnName('sequenceNo') . '=' . $this->getDBColumnName('sequenceNo') . '-1' .
            ' WHERE ' . $this->getDBColumnName('ordheadID') . ' = ' . $this->getValue('ordheadID') .
            ' AND ' . $this->getDBColumnName('sequenceNo') . ' >= ' . $this->getValue('sequenceNo')
        );
        $ret = $this->runQuery();
        $this->resetQueryString();
        return $ret;
    }

    /**
     * Move given row down in the sequence
     * Swaps sequence numbers of 2 rows
     * @access public
     * @return bool
     */
    function moveRow($direction = 'UP')
    {
        $this->setMethodName("moveRow");
        if ($this->getValue('ordheadID') == '') {
            $this->raiseError('ordheadID not set');
        }
        if ($this->getValue('sequenceNo') == '') {
            $this->setValue(
                'sequenceNo',
                0
            );
        }
        // current row into temporary buffer row: sequenceNo = -99
        $this->setQueryString(
            'UPDATE ' . $this->getTableName() .
            ' SET ' . $this->getDBColumnName('sequenceNo') . ' = -99' .
            ' WHERE ' . $this->getDBColumnName('ordheadID') . ' = ' . $this->getValue('ordheadID') .
            ' AND ' . $this->getDBColumnName('sequenceNo') . ' = ' . $this->getValue('sequenceNo')
        );
        $ret = $this->runQuery();
        $this->resetQueryString();
        // Move row next to this one
        if ($direction == 'UP') {
            $sequenceNo = $this->getValue('sequenceNo') - 1;
        } else {            // down
            $sequenceNo = $this->getValue('sequenceNo') + 1;
        }
        $this->setQueryString(
            'UPDATE ' . $this->getTableName() .
            ' SET ' . $this->getDBColumnName('sequenceNo') . ' = ' . $this->getValue('sequenceNo') .
            ' WHERE ' . $this->getDBColumnName('ordheadID') . ' = ' . $this->getValue('ordheadID') .
            ' AND ' . $this->getDBColumnName('sequenceNo') . ' = ' . $sequenceNo
        );
        $ret = $this->runQuery();
        $this->resetQueryString();
        // Move current row from temp
        $this->setQueryString(
            'UPDATE ' . $this->getTableName() .
            ' SET ' . $this->getDBColumnName('sequenceNo') . ' = ' . $sequenceNo .
            ' WHERE ' . $this->getDBColumnName('ordheadID') . ' = ' . $this->getValue('ordheadID') .
            ' AND ' . $this->getDBColumnName('sequenceNo') . ' = -99'
        );
        $ret = $this->runQuery();
        $this->resetQueryString();
        return $ret;
    }

    /**
     * get all rows ready for generation of purchase orders
     * @access public
     * @return bool
     */
    function getRowsForPO()
    {
        $this->setMethodName("getRowsForPO");
        if ($this->getValue('ordheadID') == '') {
            $this->raiseError('ordheadID not set');
        }
        $this->setQueryString(
            "SELECT DISTINCT  odl_suppno, odl_stockcat, " .
            "odl_itemno, odl_d_unit, SUM(odl_qty_ord) " .
            "FROM ordline " .
            "WHERE odl_ordno = " . $this->getValue('ordheadID') .
            " AND odl_type = 'I' " .
            "GROUP BY odl_suppno, odl_stockcat, " .
            "odl_itemno, odl_d_unit " .
            "ORDER BY odl_suppno"
        );
        return (parent::getRows());
    }

    function getRowBySequence($ordheadID,
                              $sequenceNo
    )
    {
        $this->setMethodName("getRow");

        if ($ordheadID == '') {
            $this->raiseError('ordheadID not set');
        }

        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE odl_ordno = " . $ordheadID .
            " AND " . $this->getDBColumnName('sequenceNo') . " = " . $sequenceNo
        );

        return (parent::getRow());
    }
}

?>