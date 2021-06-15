<?php
global $cfg;

use CNCLTD\Data\DBEItem;
use CNCLTD\Supplier\infra\MySQLSupplierRepository;
use CNCLTD\Supplier\Supplier;
use CNCLTD\Supplier\SupplierId;

require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BUSalesOrder.inc.php");
require_once($cfg["path_bu"] . "/BUHeader.inc.php");
require_once($cfg["path_dbe"] . "/DBEJPorhead.inc.php");
require_once($cfg["path_dbe"] . "/DBEPorhead.inc.php");
require_once($cfg["path_dbe"] . "/DBEPorline.inc.php");
require_once($cfg["path_dbe"] . "/DBEJPorline.inc.php");
require_once($cfg["path_dbe"] . "/DBEWarranty.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomerItem.inc.php");

class BUPurchaseOrder extends Business
{
    /** @var DataSet|DBEOrdhead */
    public $dsOrdhead;
    /** @var DataSet|DBEOrdline */
    public $dsOrdline;
    /** @var DBEPorhead */
    public $dbePorhead;
    /** @var DBEPorline */
    public $dbePorline;
    public $counter = 0;
    /**
     * @var DBEJPorhead
     */
    public $dbeJPorhead;
    /**
     * @var DBEJPorline
     */
    public $dbeJPorline;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbePorhead  = new DBEPorhead($this);
        $this->dbePorline  = new DBEPorline($this);
        $this->dbeJPorline = new DBEJPorline($this);
        $this->dbeJPorhead = new DBEJPorhead($this);
    }

    function createPOsFromSO($ordheadID,
                             $userID,
                             DateTime $requiredByDate = null
    )
    {
        $dsOrdhead = &$this->dsOrdhead;
        $dsOrdline = &$this->dsOrdline;
        $this->setMethodName('createPOsFromSO');
        if ($ordheadID == '') {
            $this->raiseError('ordheadID not passed');
        }
        if ($userID == '') {
            $this->raiseError('userID not passed');
        }
        $buSalesOrder = new BUSalesOrder($this);
        if (!$buSalesOrder->getOrdheadByID(
            $ordheadID,
            $dsOrdhead
        )) {
            $this->raiseError('sales order not found');
        }
        if ($buSalesOrder->countPurchaseOrders($dsOrdhead->getValue(DBEOrdhead::ordheadID)) > 0) {
            $this->raiseError('There are already purchase orders for this sales order!');
        }
        $buSalesOrder->getOrderItemsForPO(
            $ordheadID,
            $dsOrdline
        );
        // generate one PO for each distinct supplier ID found
        $lastSupplierID  = null;
        $supplierRepo    = new MySQLSupplierRepository();
        $this->counter   = 0;
        $purchaseOrderId = null;
        while ($dsOrdline->fetchNext()) {
            // new supplier so create PO header
            var_dump($dsOrdline->getValue(DBEJOrdline::supplierID));
            if ($dsOrdline->getValue(DBEJOrdline::supplierID) != $lastSupplierID) {

                $supplier        = $supplierRepo->getById(
                    new SupplierId($dsOrdline->getValue(DBEJOrdline::supplierID))
                );
                $this->counter   = 1;
                $purchaseOrderId = $this->insertPOHeader(
                    $userID,
                    $supplier,
                    $requiredByDate
                );
            }
            $this->insertPOLine($purchaseOrderId, $this->counter);
            $lastSupplierID = $dsOrdline->getValue(DBEJOrdline::supplierID);
            $this->counter++;
        }
    }

    /*
    * Create new purchase order against given sales order/supplier
    */
    function insertPOHeader($userID,
                            Supplier $supplier,
                            DateTime $requiredByDate = null
    )
    {
        $dsOrdhead  = &$this->dsOrdhead;
        $dbePorhead = &$this->dbePorhead;
        $buHeader   = new BUHeader($this);
        $dbePorhead->setValue(
            DBEPorhead::porheadID,
            0
        ); // new PK
        $dbePorhead->setValue(
            DBEPorhead::ordheadID,
            $dsOrdhead->getValue(DBEOrdhead::ordheadID)
        );
        $dbePorhead->setValue(
            DBEPorhead::date,
            date('Y-m-d')
        );
        $dbePorhead->setValue(
            DBEPorhead::supplierID,
            $supplier->id()->value()
        );
        $dbePorhead->setValue(
            DBEPorhead::userID,
            $userID
        );
        $dbePorhead->setValue(
            DBEPorhead::directDeliveryFlag,
            'N'
        );
        $dbePorhead->setValue(
            DBEPorhead::type,
            'I'
        );
        $dbePorhead->setValue(
            DBEPorhead::printedFlag,
            'N'
        );
        if ($requiredByDate) {
            $dbePorhead->setValue(
                DBEPorhead::requiredBy,
                $requiredByDate->format('Y-m-d')
            );
        }
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);
        $dsHeader->fetchNext();
        $vatCode = $dsHeader->getValue(DBEHeader::stdVATCode);
        $dbePorhead->setValue(
            DBEPorhead::vatCode,
            $vatCode
        );
        $dbeVat = new DBEVat($this);
        $dbeVat->getRow();
        $vatRate = $dbeVat->getValue((integer)$vatCode[1]); // get 2nd part of code and use as column no
        $dbePorhead->setValue(
            DBEPorhead::vatRate,
            $vatRate
        );
        $dbePorhead->setValue(
            DBEPorhead::supplierRef,
            null
        );
        $dbePorhead->setValue(
            DBEPorhead::supplierContactId,
            $supplier->mainContact()->id()->value()
        );
        $dbePorhead->setValue(
            DBEPorhead::invoices,
            null
        ); // sales invoices (not sure if required now)
        $dbePorhead->setValue(
            DBEPorhead::payMethodID,
            $supplier->paymentMethodId()->value()
        ); // default
        $dbePorhead->setValue(
            DBEPorhead::locationID,
            null
        ); // not stock
        $dbePorhead->insertRow();
        return ($dbePorhead->getPKValue());
    }

    function insertPOLine($purchaseOrderId, $lineNumber)
    {
        $dsOrdline  = &$this->dsOrdline;
        $dbePorline = &$this->dbePorline;
        var_dump($purchaseOrderId);
        if (!$purchaseOrderId) {
            throw new Exception('Purchase Order Id is null!!');
        }
        $dbePorline->setValue(
            DBEPorline::porheadID,
            $purchaseOrderId
        );
        $dbePorline->setValue(
            DBEPorline::sequenceNo,
            $lineNumber
        );
        $dbePorline->setValue(
            DBEPorline::itemID,
            $dsOrdline->getValue(DBEJOrdline::itemID)
        );
        $dbePorline->setValue(
            DBEPorline::qtyOrdered,
            $dsOrdline->getValue(DBEJOrdline::qtyOrdered)
        );
        $dbePorline->setValue(
            DBEPorline::qtyReceived,
            0
        );
        $dbePorline->setValue(
            DBEPorline::qtyInvoiced,
            0
        );
        $dbePorline->setValue(
            DBEPorline::curUnitCost,
            $dsOrdline->getValue(DBEJOrdline::curUnitCost)
        );
        $dbePorline->setValue(
            DBEPorline::stockcat,
            $dsOrdline->getValue(DBEJOrdline::stockcat)
        );
        $dbePorline->insertRow();
    }

    function createNewPO($ordheadID,
                         $supplierID,
                         $userID
    )
    {
        $dsOrdhead = &$this->dsOrdhead;
        $this->setMethodName('createNewPO');
        if ($ordheadID == '') {
            $this->raiseError('ordheadID not passed');
        }
        if ($userID == '') {
            $this->raiseError('userID not passed');
        }
        if ($supplierID == '') {
            $this->raiseError('supplierID not passed');
        }
        $buSalesOrder = new BUSalesOrder($this);
        if (!$buSalesOrder->getOrdheadByID(
            $ordheadID,
            $dsOrdhead
        )) {
            $this->raiseError('sales order not found');
        }
        $supplierRepo = new MySQLSupplierRepository();
        $supplier     = $supplierRepo->getById(new SupplierId($supplierID));
        return $this->insertPOHeader($userID, $supplier);
    }

    function search($supplierID,
                    $porheadID,
                    $ordheadID,
                    $supplierRef,
                    $type,
                    $lineText,
                    $partNo,
                    $fromDate,
                    $toDate,
                    &$dsResults
    )
    {
        $this->setMethodName('search');
        $dbeJPorhead = new DBEJPorhead($this);
        if ($porheadID != '') {
            return ($this->getDatasetByPK(
                $porheadID,
                $dbeJPorhead,
                $dsResults
            ));
        }
        $dbeJPorhead->getRowsBySearchCriteria(
            trim($supplierID),
            trim($ordheadID),
            trim($type),
            trim($supplierRef),
            trim($lineText),
            trim($partNo),
            trim($fromDate),
            trim($toDate)
        );
        $dbeJPorhead->initialise();
        $dsResults = $dbeJPorhead;
        return true;
    }

    function getOrderByID($porheadID,
                          &$dsPorhead,
                          &$dsPorline
    )
    {
        if ($porheadID == '') {
            $this->raiseError('porheadID not passed');
        }
        $dbeJPorhead = new DBEJPorhead($this);
        if (!$this->getDatasetByPK(
            $porheadID,
            $dbeJPorhead,
            $dsPorhead
        )) {
            $this->raiseError('Order not found');
        }
        $dbeJPorline = new DBEJPorline($this);
        $dbeJPorline->setValue(
            DBEJPorline::porheadID,
            $porheadID
        );
        $dbeJPorline->getRowsByColumn(DBEJPorline::porheadID);
        $this->getData(
            $dbeJPorline,
            $dsPorline
        );
        return TRUE;
    }

    function getOrderHeaderByID($porheadID,
                                &$dsPorhead
    )
    {
        $this->setMethodName('getOrderHeaderByID');
        if ($porheadID == '') {
            $this->raiseError('porheadID not passed');
        }
        $dbeJPorhead = new DBEJPorhead($this);
        return ($this->getDatasetByPK(
            $porheadID,
            $dbeJPorhead,
            $dsPorhead
        ));
    }

    function getLinesByID($porheadID,
                          &$dsPorline
    )
    {
        $this->setMethodName('getLinesByID');
        if ($porheadID == '') {
            $this->raiseError('porheadID not passed');
        }
        $dbeJPorline = new DBEJPorline($this);
        $dbeJPorline->setValue(
            DBEJPorline::porheadID,
            $porheadID
        );
        $dbeJPorline->getRowsByColumn(DBEJPorline::porheadID);
        $this->getData(
            $dbeJPorline,
            $dsPorline
        );
        return TRUE;
    }

    function getOrdlineByIDSeqNo($porheadID,
                                 $sequenceNo,
                                 &$dsPorline
    )
    {
        $this->setMethodName('getOrdlineByIDSeqNo');
        if ($porheadID == '') {
            $this->raiseError('order ID not passed');
        }
        if ($sequenceNo == '') {
            $this->raiseError('sequenceNo not passed');
        }
        $dbeJPorline = new DBEJPorline($this);
        $dbeJPorline->setValue(
            DBEJPorline::porheadID,
            $porheadID
        );
        $dbeJPorline->setValue(
            DBEJPorline::sequenceNo,
            $sequenceNo
        );
        $dbeJPorline->getRowByPorheadIDSequenceNo();
        return ($this->getData(
            $dbeJPorline,
            $dsPorline
        ));
    }

    /**
     * Initialise new ordline dataset row
     * This DOES NOT change the database
     * @parameter dateset $dsPorline
     * @access public
     * @param $porheadID
     * @param $sequenceNo
     * @param $dsPorline
     */
    function initialiseNewOrdline($porheadID,
                                  &$dsPorline
    )
    {
        $this->setMethodName('initialiseNewOrdline');
        if ($porheadID == '') {
            $this->raiseError('porheadID not passed');
        }
        $dbeJPorline = new DBEJPorline($this);
        $dsPorline   = new DSForm($this);
        $dsPorline->copyColumnsFrom($dbeJPorline);
        $dsPorline->setAllowEmpty('itemID');
        $dsPorline->setUpdateModeInsert();
        $dsPorline->setValue(
            DBEJPorline::porheadID,
            $porheadID
        );
        $dsPorline->setValue(
            DBEJPorline::itemID,
            ''
        );
        $dsPorline->setValue(
            DBEJPorline::sequenceNo,
            $dbeJPorline->getNextSequenceForPurchaseOrder($porheadID)
        );
        $dsPorline->setValue(
            DBEJPorline::qtyOrdered,
            1
        );    // default 1
        $dsPorline->setValue(
            DBEJPorline::qtyReceived,
            0
        );
        $dsPorline->setValue(
            DBEJPorline::qtyInvoiced,
            0
        );
        $dsPorline->setValue(
            DBEJPorline::curUnitCost,
            0
        );
        $dsPorline->setValue(
            DBEJPorline::expectedDate,
            date(
                'Y-m-d',
                strtotime('+ 3 days')
            )
        ); // today + 3 days
        $dsPorline->post();
    }

    /**
     * Insert new ordline dataset row
     * This changes the database
     * @param DataSet|DBEPorline $dsPorline
     * @return void : Success
     * @access public
     */
    function insertNewOrderLine(&$dsPorline)
    {
        $this->setMethodName('insertNewOrdline');
//count rows
        $dsPorline->fetchNext();
        $dbePorline = new DBEPorline($this);
        $dbePorline->setValue(
            DBEPorline::porheadID,
            $dsPorline->getValue(DBEJPorline::porheadID)
        );
        if ($dbePorline->countRowsByColumn(DBEPorline::porheadID) > 0) {
            // shuffle down existing rows before inserting new one
            $dbePorline->setValue(
                DBEPorline::porheadID,
                $dsPorline->getValue(DBEJPorline::porheadID)
            );
            $dbePorline->setValue(
                DBEPorline::sequenceNo,
                $dsPorline->getValue(DBEJPorline::sequenceNo)
            );
            $dbePorline->shuffleRowsDown();
        }
        $this->updateOrderLine(
            $dsPorline,
            "I"
        );
    }

    /**
     * @param DataSet|DBEPorline $dsPorline
     * @param string $action
     */
    function updateOrderLine(&$dsPorline,
                             $action = "U"
    )
    {
        $this->setMethodName('updateOrderLine');
        $dbePorhead = new DBEPorhead($this);
        $dbePorhead->setPKValue($dsPorline->getValue(DBEJPorline::porheadID));
        if (!$dbePorhead->getRow()) {
            $this->raiseError('order header not found');
        }
        // ordline fields
        $dbePorline = new DBEPorline($this);
        $dbePorline->setValue(
            DBEPorline::qtyOrdered,
            $dsPorline->getValue(DBEJPorline::qtyOrdered)
        );
        $dbePorline->setValue(
            DBEPorline::curUnitCost,
            $dsPorline->getValue(DBEJPorline::curUnitCost)
        );
        $dbePorline->setValue(
            DBEPorline::porheadID,
            $dsPorline->getValue(DBEJPorline::porheadID)
        );
        $dbePorline->setValue(
            DBEPorline::sequenceNo,
            $dsPorline->getValue(DBEJPorline::sequenceNo)
        );
        $dbePorline->setValue(
            DBEPorline::qtyInvoiced,
            0
        );
        $dbePorline->setValue(
            DBEPorline::qtyReceived,
            0
        );
        $dbePorline->setValue(
            DBEPorline::itemID,
            $dsPorline->getValue(DBEJPorline::itemID)
        );
        $dbePorline->setValue(
            DBEPorline::expectedDate,
            $dsPorline->getValue(DBEJPorline::expectedDate)
        );
        $buItem = new BUItem($this);
        $dsItem = new DataSet($this);
        if ($buItem->getItemByID(
            $dsPorline->getValue(DBEJPorline::itemID),
            $dsItem
        )) {
            $dbePorline->setValue(
                DBEPorline::stockcat,
                $dsItem->getValue(DBEItem::stockcat)
            );
        }
        if ($action == "U") {
            $dbePorline->updateRow();
        } else {
            $dbePorline->insertRow();
        }
    }

    function moveOrderLineUp($porheadID,
                             $sequenceNo
    )
    {
        if ($porheadID == '') {
            $this->raiseError('porheadID not passed');
        }
        if ($sequenceNo == '') {
            $this->raiseError('sequenceNo not passed');
        }
        if ($sequenceNo == 1) {
            return;
        }
        $dbePorline = new DBEPorline($this);
        $dbePorline->setValue(
            DBEPorline::porheadID,
            $porheadID
        );
        $dbePorline->setValue(
            DBEPorline::sequenceNo,
            $sequenceNo
        );
        $dbePorline->moveRow('UP');
    }

    function moveOrderLineDown($porheadID,
                               $sequenceNo
    )
    {
        if ($porheadID == '') {
            $this->raiseError('porheadID not passed');
        }
        if ($sequenceNo == '') {
            $this->raiseError('sequenceNo not passed');
        }
        $dbePorline = new DBEPorline($this);
        $dbePorline->setValue(
            DBEPorline::porheadID,
            $porheadID
        );
        $dbePorline->setValue(
            DBEPorline::sequenceNo,
            $sequenceNo
        );
        $dbePorline->moveRow('DOWN');
    }

    function deleteOrderLine($porheadID,
                             $sequenceNo
    )
    {
        if ($porheadID == '') {
            $this->raiseError('porheadID not passed');
        }
        if ($sequenceNo == '') {
            $this->raiseError('sequenceNo not passed');
        }
        $dbePorline = new DBEPorline($this);
        $dbePorline->setValue(
            DBEPorline::porheadID,
            $porheadID
        );
        $dbePorline->setValue(
            DBEPorline::sequenceNo,
            $sequenceNo
        );
        $dbePorline->deleteRow();
        $dbePorline->setValue(
            DBEPorline::porheadID,
            $porheadID
        );
        $dbePorline->setValue(
            DBEPorline::sequenceNo,
            $sequenceNo
        );
        $dbePorline->shuffleRowsUp();
    }

    function updateHeader(DataSet $dsPorhead)
    {
        $this->setMethodName(' updateHeader');
        // it is VERY annoying that empty checkboxes pass no value
        if ($dsPorhead->getValue(DBEJPorhead::directDeliveryFlag) == '') {
            $dsPorhead->setUpdateModeUpdate();
            $dsPorhead->setValue(
                DBEJPorhead::directDeliveryFlag,
                'N'
            );
            $dsPorhead->post();
        }
        if ($dsPorhead->getValue(DBEJPorhead::deliveryConfirmedFlag) == '') {
            $dsPorhead->setUpdateModeUpdate();
            $dsPorhead->setValue(
                DBEJPorhead::deliveryConfirmedFlag,
                'N'
            );
            $dsPorhead->post();
        }
        $this->dbePorhead->getRow($dsPorhead->getValue(DBEJPorhead::porheadID)); //existing values
        $this->dbePorhead->setValue(
            DBEPorhead::supplierID,
            $dsPorhead->getValue(DBEJPorhead::supplierID)
        );
        $this->dbePorhead->setValue(
            DBEPorhead::supplierContactId,
            $dsPorhead->getValue(DBEJPorhead::supplierContactId)
        );
        $this->dbePorhead->setValue(
            DBEPorhead::supplierRef,
            $dsPorhead->getValue(DBEJPorhead::supplierRef)
        );
        $this->dbePorhead->setValue(
            DBEPorhead::ordheadID,
            $dsPorhead->getValue(DBEJPorhead::ordheadID)
        );
        $this->dbePorhead->setValue(
            DBEPorhead::payMethodID,
            $dsPorhead->getValue(DBEJPorhead::payMethodID)
        );
        $this->dbePorhead->setValue(
            DBEPorhead::directDeliveryFlag,
            $dsPorhead->getValue(DBEJPorhead::directDeliveryFlag)
        );
        $this->dbePorhead->setValue(
            DBEPorhead::deliveryConfirmedFlag,
            $dsPorhead->getValue(DBEJPorhead::deliveryConfirmedFlag)
        );
        $this->dbePorhead->setValue(
            DBEPorhead::requiredBy,
            $dsPorhead->getValue(DBEPorhead::requiredBy)
        );
        $this->dbePorhead->updateRow();
        return TRUE;
    }

    function deleteOrder($porheadID)
    {
        $this->setMethodName(' updateHeader');
        if ($porheadID == '') {
            $this->raiseError('porheadID not passed');
        }
        $dbePorhead = &$this->dbePorhead;
        $dbePorline = &$this->dbePorline;
        if (!$dbePorhead->getRow($porheadID)) {
            $this->raiseError('order not found');
        }
        $dbePorhead->deleteRow();
        $dbePorline->setValue(
            DBEPorline::porheadID,
            $porheadID
        );
        return ($dbePorline->deleteRowsByOrdheadID());
    }

    function setOrderedFields($porheadID,
                              $userID
    )
    {
        $this->setMethodName('setOrderedFields');
        $this->dbePorhead->getRow($porheadID);
        $this->dbePorhead->setValue(
            DBEPorhead::orderUserID,
            $userID
        );
        $this->dbePorhead->setValue(
            DBEPorhead::orderDate,
            date('Y-m-d')
        );
        $this->dbePorhead->updateRow();
        return TRUE;
    }

    /**
     * get purchase order header dataset
     * @param Integer $porheadID Key
     * @param Dataset $dsResults result dataset
     * @return Boolean success
     */
    function getHeaderByID($porheadID,
                           &$dsResults
    )
    {
        $this->setMethodName('getHeaderByID');
        if ($porheadID == '') {
            $this->raiseError('porheadID not passed');
        }
        return $this->getDatasetByPK(
            $porheadID,
            $this->dbeJPorhead,
            $dsResults
        );
    }

}
