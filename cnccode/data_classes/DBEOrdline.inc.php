<?php /*
* Ordline table
* @authors Karim Ahmed
* @access public
*/
global $cfg;

use CNCLTD\SortableWithQueryDBE;

require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEOrdline extends DBEntity
{
    use SortableWithQueryDBE;

    const LINE_TYPE_ITEM    = 'I';
    const LINE_TYPE_COMMENT = 'C';

    const id                    = "id";
    const lineType              = "lineType";
    const ordheadID             = "ordheadID";
    const sequenceNo            = "sequenceNo";
    const customerID            = "customerID";
    const itemID                = "itemID";
    const stockcat              = "stockcat";
    const description           = "description";
    const qtyOrdered            = "qtyOrdered";
    const qtyDespatched         = "qtyDespatched";
    const qtyLastDespatched     = "qtyLastDespatched";
    const supplierID            = "supplierID";
    const curUnitCost           = "curUnitCost";
    const curTotalCost          = "curTotalCost";
    const curUnitSale           = "curUnitSale";
    const curTotalSale          = "curTotalSale";
    const renewalCustomerItemID = "renewalCustomerItemID";
    const isRecurring           = "isRecurring";

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
        $this->setTableName("ordline");
        $this->addColumn(
            self::id,
            DA_ID,
            DA_NOT_NULL,
            'odl_ordlineno'
        );
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
        $this->addColumn(
            self::isRecurring,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            false
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function deleteRowsByOrderID($orderId)
    {
        if (!$orderId) {
            $this->raiseError('ordheadID not set');
            return false;
        }
        $this->setQueryString(
            'DELETE FROM ' . $this->getTableName() . ' WHERE ' . $this->getDBColumnName(
                self::ordheadID
            ) . ' = ' . $orderId
        );
        return parent::runQuery();
    }

    /**
     * Shuffle down rows ready for insertion of new one
     * @access public
     * @return bool
     */
    function shuffleRowsDown()
    {
        $this->setMethodName("shuffleRowsDown");
        if (!$this->getValue(self::ordheadID)) {
            $this->raiseError('ordheadID not set');
        }
        if (!$this->getValue(self::sequenceNo)) {
            $this->setValue(
                self::sequenceNo,
                null
            );
        }
        $this->setQueryString(
            'UPDATE ' . $this->getTableName() . ' SET ' . $this->getDBColumnName(
                self::sequenceNo
            ) . '=' . $this->getDBColumnName(self::sequenceNo) . '+1' . ' WHERE ' . $this->getDBColumnName(
                self::ordheadID
            ) . ' = ' . $this->getFormattedValue(self::ordheadID) . ' AND ' . $this->getDBColumnName(
                self::sequenceNo
            ) . ' >= ' . $this->getFormattedValue(self::sequenceNo)
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
        if ($this->getValue(self::ordheadID) == '') {
            $this->raiseError('ordheadID not set');
        }
        if ($this->getValue(self::sequenceNo) == '') {
            $this->setValue(
                self::sequenceNo,
                0
            );
        }
        $this->setQueryString(
            'UPDATE ' . $this->getTableName() . ' SET ' . $this->getDBColumnName(
                self::sequenceNo
            ) . '=' . $this->getDBColumnName(self::sequenceNo) . '-1' . ' WHERE ' . $this->getDBColumnName(
                self::ordheadID
            ) . ' = ' . $this->getValue(self::ordheadID) . ' AND ' . $this->getDBColumnName(
                self::sequenceNo
            ) . ' >= ' . $this->getValue(self::sequenceNo)
        );
        $ret = $this->runQuery();
        $this->resetQueryString();
        return $ret;
    }

    function getSortOrder()
    {
        return $this->getValue(self::sequenceNo);
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
        if (!$this->getValue(self::ordheadID)) {
            $this->raiseError('ordheadID not set');
        }
        if ($this->getValue(self::sequenceNo) == null) {
            $this->setValue(
                self::sequenceNo,
                $this->getNextSortOrder()
            );
        }
        // Move row next to this one
        if ($direction == 'UP') {
            $this->moveItemUp($this->getPKValue());
        } else {            // down
            $this->moveItemDown($this->getPKValue());
        }
        $this->resetQueryString();
    }

    /**
     * get all rows ready for generation of purchase orders
     * @access public
     * @return bool
     */
    function getRowsForPO()
    {
        $this->setMethodName("getRowsForPO");
        if ($this->getValue(self::ordheadID) == '') {
            $this->raiseError('ordheadID not set');
        }
        $this->setQueryString(
            "SELECT DISTINCT  odl_suppno, odl_stockcat, " . "odl_itemno, odl_d_unit, SUM(odl_qty_ord) " . "FROM ordline " . "WHERE odl_ordno = " . $this->getValue(
                self::ordheadID
            ) . " AND odl_type = 'I' " . "GROUP BY odl_suppno, odl_stockcat, " . "odl_itemno, odl_d_unit " . "ORDER BY odl_suppno"
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
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " WHERE odl_ordno = " . $ordheadID . " AND " . $this->getDBColumnName(
                self::sequenceNo
            ) . " = " . $sequenceNo
        );
        return (parent::getRow());
    }

    public function insertRow()
    {
        $this->setValue(self::sequenceNo, $this->getNextSortOrder());
        parent::insertRow();
    }

    function deleteRow($pkValue = '')
    {
        if ($pkValue) {
            $this->getRow($pkValue);
        }
        //we are going to move this to be the end of the list..and then delete it
        $this->moveItemToBottom();
        return parent::deleteRow($pkValue);
    }

    public function getLinesForOrder($ordheadID)
    {
        $this->setQueryString(
            "SELECT {$this->getDBColumnNamesAsString()} FROM {$this->getTableName()} WHERE odl_ordno = {$ordheadID} order by {$this->getDBColumnName(self::isRecurring)} , {$this->getDBColumnName(self::sequenceNo)} "
        );
        $this->getRows();
    }

    protected function getSortOrderColumnName()
    {
        return $this->getDBColumnName(self::sequenceNo);
    }


    protected function getDiscriminatorCondition()
    {
        global $db;
        $isRecurringString = $this->getValue(self::isRecurring) ? '1' : '0';
        return "{$this->getDBColumnName(self::ordheadID)} = '{$this->getValue(self::ordheadID)}' and {$this->getDBColumnName(self::isRecurring)} = {$isRecurringString}";
    }
}