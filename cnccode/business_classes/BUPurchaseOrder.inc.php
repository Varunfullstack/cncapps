<?php
/**
 * PurchaseOrder business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BUSalesOrder.inc.php");
require_once($cfg["path_bu"] . "/BUSupplier.inc.php");
require_once($cfg["path_bu"] . "/BUHeader.inc.php");
require_once($cfg["path_dbe"] . "/DBEJPorhead.inc.php");
require_once($cfg["path_dbe"] . "/DBEPorhead.inc.php");
require_once($cfg["path_dbe"] . "/DBEPorline.inc.php");
require_once($cfg["path_dbe"] . "/DBEJPorline.inc.php");
require_once($cfg["path_dbe"] . "/DBEWarranty.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomerItem.inc.php");

class BUPurchaseOrder extends Business
{
    var $dsSupplier = '';
    var $dsOrdhead = '';
    var $dsOrdline = '';
    var $dbePorhead = '';
    var $dbePorline = '';
    var $counter = 0;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbePorhead = new DBEPorhead($this);
        $this->dbePorline = new DBEPorline($this);
        $this->dbeJPorline = new DBEJPorline($this);
        $this->dbeJPorhead = new DBEJPorhead($this);
    }

    function createPOsFromSO($ordheadID,
                             $userID
    )
    {
        $dsOrdhead = &$this->dsOrdhead;
        $dsOrdline = &$this->dsOrdline;
        $dsSupplier = &$this->dsSupplier;
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

        if ($buSalesOrder->countPurchaseOrders($dsOrdhead->getValue('ordheadID')) > 0) {
            $this->raiseError('There are already purchase orders for this sales order!');
        }

        $buSalesOrder->getOrderItemsForPO(
            $ordheadID,
            $dsOrdline
        );
        // generate one PO for each distinct supplier ID found
        $lastSupplierID = '0';
        $buSupplier = new BUSupplier($this);
        $this->counter = 0;
        while ($dsOrdline->fetchNext()) {
            // new supplier so create PO header
            if ($dsOrdline->getValue('supplierID') != $lastSupplierID) {
                $buSupplier->getSupplierByID(
                    $dsOrdline->getValue('supplierID'),
                    $dsSupplier
                );
                $this->counter = 1;
                $this->insertPOHeader($userID);
            }
            $this->insertPOLine();
            $lastSupplierID = $dsOrdline->getValue('supplierID');
            $this->counter++;
        }
    }

    /*
    * Create new purchase order against given sales order/supplier
    */
    function createNewPO($ordheadID,
                         $supplierID,
                         $userID
    )
    {
        $dsOrdhead = &$this->dsOrdhead;
        $dsOrdline = &$this->dsOrdline;
        $dsSupplier = &$this->dsSupplier;
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
        $buSupplier = new BUSupplier($this);
        $buSupplier->getSupplierByID(
            $supplierID,
            $dsSupplier
        );
        return ($this->insertPOHeader($userID));
    }

    function insertPOHeader($userID)
    {
        $dsOrdhead = &$this->dsOrdhead;
        $dsOrdline = &$this->dsOrdline;
        $dsSupplier = &$this->dsSupplier;
        $dbePorhead = &$this->dbePorhead;
        $buHeader = new BUHeader($this);
        $dbePorhead->setValue(
            'porheadID',
            0
        ); // new PK
        $dbePorhead->setValue(
            'ordheadID',
            $dsOrdhead->getValue('ordheadID')
        );
        $dbePorhead->setValue(
            'date',
            date('Y-m-d')
        );
        $dbePorhead->setValue(
            'supplierID',
            $dsSupplier->getValue('supplierID')
        );
        $dbePorhead->setValue(
            'userID',
            $userID
        );
        $dbePorhead->setValue(
            'directDeliveryFlag',
            'N'
        );
        $dbePorhead->setValue(
            'type',
            'I'
        );
        $dbePorhead->setValue(
            'printedFlag',
            'N'
        );
        $buHeader->getHeader($dsHeader);
        $dsHeader->fetchNext();
        $vatCode = $dsHeader->getValue('stdVATCode');
        $dbePorhead->setValue(
            'vatCode',
            $vatCode
        );
        $dbeVat = new DBEVat($this);
        $dbeVat->getRow();
        $vatRate = $dbeVat->getValue((integer)$vatCode[1]); // get 2nd part of code and use as column no
        $dbePorhead->setValue(
            'vatRate',
            $vatRate
        );
        $dbePorhead->setValue(
            'supplierRef',
            null
        );
        $dbePorhead->setValue(
            'contactID',
            $dsSupplier->getValue('contactID')
        );
        $dbePorhead->setValue(
            'invoices',
            null
        ); // sales invoices (not sure if required now)
        $dbePorhead->setValue(
            'payMethodID',
            $dsSupplier->getValue('payMethodID')
        ); // default
        $dbePorhead->setValue(
            'locationID',
            null
        ); // not stock
        $dbePorhead->insertRow();
        return ($dbePorhead->getPKValue());
    }

    function insertPOLine()
    {
        $dsOrdhead = &$this->dsOrdhead;
        $dsOrdline = &$this->dsOrdline;
        $dbePorline = &$this->dbePorline;
        $dbePorline->setValue(
            'porheadID',
            $this->dbePorhead->getValue('porheadID')
        );
        $dbePorline->setValue(
            'sequenceNo',
            $this->counter
        );
        $dbePorline->setValue(
            'itemID',
            $dsOrdline->getValue('itemID')
        );
        $dbePorline->setValue(
            'qtyOrdered',
            $dsOrdline->getValue('qtyOrdered')
        );
        $dbePorline->setValue(
            'qtyReceived',
            0
        );
        $dbePorline->setValue(
            'qtyInvoiced',
            0
        );
        $dbePorline->setValue(
            'curUnitCost',
            $dsOrdline->getValue('curUnitCost')
        );
        $dbePorline->setValue(
            'stockcat',
            $dsOrdline->getValue('stockcat')
        );
        $dbePorline->setValue(
            'expectedDate',
            date(
                'Y-m-d',
                strtotime('+ 3 days')
            )
        ); // today + 3 days
        $dbePorline->insertRow();
    }

    function search(
        $supplierID,
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
            $ret = ($this->getDatasetByPK(
                $porheadID,
                $dbeJPorhead,
                $dsResults
            ));
        } else {
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
        }
        return $ret;
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
            'porheadID',
            $porheadID
        );
        $dbeJPorline->getRowsByColumn('porheadID');
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
            'porheadID',
            $porheadID
        );
        $dbeJPorline->getRowsByColumn('porheadID');
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
        $ret = FALSE;
        if ($porheadID == '') {
            $this->raiseError('order ID not passed');
        }
        if ($sequenceNo == '') {
            $this->raiseError('sequenceNo not passed');
        }
        $dbeJPorline = new DBEJPorline($this);
        $dbeJPorline->setValue(
            'porheadID',
            $porheadID
        );
        $dbeJPorline->setValue(
            'sequenceNo',
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
     * @return bool : Success
     * @access public
     */
    function initialiseNewOrdline($porheadID,
                                  $sequenceNo,
                                  &$dsPorline
    )
    {
        $this->setMethodName('initialiseNewOrdline');
        if ($porheadID == '') {
            $this->raiseError('porheadID not passed');
        }
        if ($sequenceNo == '') {
            $this->raiseError('sequenceNo not passed');
        }
        $dbeJPorline = new DBEJPorline($this);
        $dsPorline = new DSForm($this);
        $dsPorline->copyColumnsFrom($dbeJPorline);
        $dsPorline->setAllowEmpty('itemID');
        $dsPorline->setUpdateModeInsert();
        $dsPorline->setValue(
            'porheadID',
            $porheadID
        );
        $dsPorline->setValue(
            'itemID',
            ''
        );
        $dsPorline->setValue(
            'sequenceNo',
            $sequenceNo
        );
        $dsPorline->setValue(
            'qtyOrdered',
            1
        );    // default 1
        $dsPorline->setValue(
            'qtyReceived',
            0
        );
        $dsPorline->setValue(
            'qtyInvoiced',
            0
        );
        $dsPorline->setValue(
            'curUnitCost',
            0
        );
        $dsPorline->setValue(
            'expectedDate',
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
     * @parameter dateset $dsOrdline
     * @return bool : Success
     * @access public
     */
    function insertNewOrderLine(&$dsPorline)
    {
        $this->setMethodName('insertNewOrdline');
//count rows
        $dsPorline->fetchNext();
        $dbePorline = new DBEPorline($this);
        $dbePorline->setValue(
            'porheadID',
            $dsPorline->getValue('porheadID')
        );
        if ($dbePorline->countRowsByColumn('porheadID') > 0) {
            // shuffle down existing rows before inserting new one
            $dbePorline->setValue(
                'porheadID',
                $dsPorline->getValue('porheadID')
            );
            $dbePorline->setValue(
                'sequenceNo',
                $dsPorline->getValue('sequenceNo')
            );
            $dbePorline->shuffleRowsDown();
        }
        $ret = ($this->updateOrderLine(
            $dsPorline,
            "I"
        ));
    }

    /* Update order line
    * @parameter Dataset $dsPorline
    * @return bool : Success
    * @access public
    */
    function updateOrderLine(&$dsPorline,
                             $action = "U"
    )
    {
        $this->setMethodName('updateOrderLine');
        $dbePorhead = new DBEPorhead($this);
        $dbePorhead->setPKValue($dsPorline->getValue('porheadID'));
        if (!$dbePorhead->getRow()) {
            $this->raiseError('order header not found');
        }
        // ordline fields
        $dbePorline = new DBEPorline($this);
        $dbePorline->setValue(
            'qtyOrdered',
            $dsPorline->getValue('qtyOrdered')
        );
        $dbePorline->setValue(
            'curUnitCost',
            $dsPorline->getValue('curUnitCost')
        );
        $dbePorline->setValue(
            'porheadID',
            $dsPorline->getValue('porheadID')
        );
        $dbePorline->setValue(
            'sequenceNo',
            $dsPorline->getValue('sequenceNo')
        );
        $dbePorline->setValue(
            'qtyInvoiced',
            0
        );
        $dbePorline->setValue(
            'qtyReceived',
            0
        );
        $dbePorline->setValue(
            'itemID',
            $dsPorline->getValue('itemID')
        );
        $dbePorline->setValue(
            'expectedDate',
            $dsPorline->getValue('expectedDate')
        );
        $buItem = new BUItem($this);
        if ($buItem->getItemByID(
            $dsPorline->getValue('itemID'),
            $dsItem
        )) {
            $dbePorline->setValue(
                'stockcat',
                $dsItem->getValue('stockcat')
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
            'porheadID',
            $porheadID
        );
        $dbePorline->setValue(
            'sequenceNo',
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
            'porheadID',
            $porheadID
        );
        $dbePorline->setValue(
            'sequenceNo',
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
            'porheadID',
            $porheadID
        );
        $dbePorline->setValue(
            'sequenceNo',
            $sequenceNo
        );
        $dbePorline->deleteRow();
        $dbePorline->setValue(
            'porheadID',
            $porheadID
        );
        $dbePorline->setValue(
            'sequenceNo',
            $sequenceNo
        );
        $dbePorline->shuffleRowsUp();
    }

    function updateHeader($dsPorhead)
    {
        $this->setMethodName(' updateHeader');
        // it is VERY annoying that empty checkboxes pass no value
        if ($dsPorhead->getValue('directDeliveryFlag') == '') {
            $dsPorhead->setUpdateModeUpdate();
            $dsPorhead->setValue(
                'directDeliveryFlag',
                'N'
            );
            $dsPorhead->post();
        }
        $this->dbePorhead->getRow($dsPorhead->getValue('porheadID')); //existing values
        $this->dbePorhead->setValue(
            'supplierID',
            $dsPorhead->getValue('supplierID')
        );
        $this->dbePorhead->setValue(
            'contactID',
            $dsPorhead->getValue('contactID')
        );
        $this->dbePorhead->setValue(
            'supplierRef',
            $dsPorhead->getValue('supplierRef')
        );
        $this->dbePorhead->setValue(
            'ordheadID',
            $dsPorhead->getValue('ordheadID')
        );
        $this->dbePorhead->setValue(
            'payMethodID',
            $dsPorhead->getValue('payMethodID')
        );
        $this->dbePorhead->setValue(
            'directDeliveryFlag',
            $dsPorhead->getValue('directDeliveryFlag')
        );
        $this->dbePorhead->setValue(
            DBEPorhead::requiredBY,
            $dsPorhead->getValue(DBEPorhead::requiredBY)
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
            'porheadID',
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
            'orderUserID',
            $userID
        );
        $this->dbePorhead->setValue(
            'orderDate',
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

    /**
     * Return a dataset of despatch qtys for this set of order lines
     *
     * We only need to ask for serial numbers and warranty details if the associated sales order has
     * addItem = "Y"
     * @param DataSet dsPorline Purchase order lines
     * @param DataSet dsReceieve Result set of receive lines with correct default values
     * @param String $addCustomerItems Y/N flag to indicate whether we need to ask for serial no
     * and warranty info for adding a customeritem
     */
    function getInitialReceieveQtys(&$dsPorline,
                                    &$dsReceive,
                                    $addCustomerItems
    )
    {
        $this->setMethodName('getInitialReceieveQtys');
        $this->initialiseReceiveDataset($dsReceive);
        $dbeItem = new DBEItem($this);
        $dsPorline->initialise();
        $sequenceNo = 0;
        while ($dsPorline->fetchNext()) {
            $itemID = $dsPorline->getValue('itemID');
            $qtyOS = $dsPorline->getValue('qtyOrdered') - $dsPorline->getValue('qtyReceived');
            // skip if nothing outstanding for this order line
            if ($qtyOS <= 0) {
                continue;
            }
            // else add a line to receipts dataset
            $dbeItem->getRow($itemID);
            // if this item requires a serial number and this purchase order requires customer items adding
            if (($dbeItem->getValue('serialNoFlag') == 'Y') & $addCustomerItems) {
                for ($i = 1; $i <= $qtyOS; $i++) {
                    $sequenceNo++;
                    $dsReceive->setUpdateModeInsert();
                    $dsReceive->setValue(
                        'requireSerialNo',
                        TRUE
                    );
                    $dsReceive->setValue(
                        'serialNo',
                        ''
                    );
                    $dsReceive->setValue(
                        'description',
                        $dsPorline->getValue('itemDescription')
                    );
                    $dsReceive->setValue(
                        'sequenceNo',
                        $sequenceNo
                    );
                    $dsReceive->setValue(
                        'orderSequenceNo',
                        $dsPorline->getValue('sequenceNo')
                    );
                    $dsReceive->setValue(
                        'qtyOrdered',
                        1
                    );
                    $dsReceive->setValue(
                        'qtyReceived',
                        0
                    );
                    $dsReceive->setValue(
                        'qtyToReceive',
                        0
                    );
                    $dsReceive->setValue(
                        'warrantyID',
                        $dbeItem->getValue('warrantyID')
                    );
                    $dsReceive->setValue(
                        'qtyOS',
                        1
                    );
                    $dsReceive->setValue(
                        'itemID',
                        $dbeItem->getValue('itemID')
                    );
                    $dsReceive->setValue(
                        'partNo',
                        $dbeItem->getValue('partNo')
                    );
                    $dsReceive->setValue(
                        'allowReceive',
                        TRUE
                    );
                    $dsReceive->post();
                }
            } else {
                $sequenceNo++;
                $dsReceive->setUpdateModeInsert();
                $dsReceive->setValue(
                    'requireSerialNo',
                    FALSE
                );
                $dsReceive->setValue(
                    'serialNo',
                    ''
                );
                $dsReceive->setValue(
                    'description',
                    $dsPorline->getValue('itemDescription')
                );
                $dsReceive->setValue(
                    'sequenceNo',
                    $sequenceNo
                );
                $dsReceive->setValue(
                    'orderSequenceNo',
                    $dsPorline->getValue('sequenceNo')
                );
                $dsReceive->setValue(
                    'qtyOrdered',
                    $dsPorline->getValue('qtyOrdered')
                );
                $dsReceive->setValue(
                    'qtyReceived',
                    $dsPorline->getValue('qtyReceived')
                );
                $dsReceive->setValue(
                    'qtyToReceive',
                    0
                );
                $dsReceive->setValue(
                    'warrantyID',
                    ''
                );
                $dsReceive->setValue(
                    'qtyOS',
                    $qtyOS
                );
                $dsReceive->setValue(
                    'itemID',
                    $dbeItem->getValue('itemID')
                );
                $dsReceive->setValue(
                    'partNo',
                    $dbeItem->getValue('partNo')
                );
                $dsReceive->setValue(
                    'allowReceive',
                    TRUE
                );
                $dsReceive->post();
            }
        }
        return TRUE;
    }

    /**
     * Return a dataset of despatch qtys for this set of order lines where receiveing from internal
     * stock location.
     *
     * This method differs from getInitialReceieveQtys() in that , where a customer item exists at that
     * stock location for the item, it's serial number will be pre-determined from the customer item
     * and the warranty will default to that on the customer item.
     *
     * will already by hard-coded in the result-set
     * @param DataSet dsPorline Purchase order lines
     * @param DataSet dsReceieve Result set of receive lines with correct default values
     * @param String $addCustomerItems Y/N flag to indicate whether we need to ask for serial no
     * and warranty info for adding a customeritem
     */
    function getInitialStockReceieveQtys($customerID,
                                         &$dsPorline,
                                         &$dsReceive
    )
    {
        $this->setMethodName('getInitialStockReceieveQtys');
        $this->initialiseReceiveDataset($dsReceive);
        $dbeItem = new DBEItem($this);
        $dbeCustomerItem = new DBECustomerItem($this);
        $dsPorline->initialise();
        $sequenceNo = 0;
        while ($dsPorline->fetchNext()) {
            $itemID = $dsPorline->getValue('itemID');
            $qtyOS = $dsPorline->getValue('qtyOrdered') - $dsPorline->getValue('qtyReceived');
            // skip if nothing outstanding for this order line
            if ($qtyOS <= 0) {
                continue;
            }
            // else add a line to receipts dataset
            $dbeItem->getRow($itemID);
            /*
            * if this item requires a serial number then we need to split out the individual
            * qtys onto lines and try to get a customer item row for the item/stock customer.
            * if we get one then use it's s/n and warranty.
            * If we can't find a customer item then we simply display the remaining qty on one
            * line. The fact that no customer item ID will exist indicates that the program
            * can not receive against this item.
            */
            if (($dbeItem->getValue('serialNoFlag') == 'Y')) {
                // get all customer items at this stock customer
                $dbeCustomerItem->getRowsByCustomerAndItemID(
                    $customerID,
                    $itemID
                );
                for ($i = 1; $i <= $qtyOS; $i++) {
                    $sequenceNo++;
                    $dsReceive->setUpdateModeInsert();
                    $dsReceive->setValue(
                        'requireSerialNo',
                        TRUE
                    );
                    $dsReceive->setValue(
                        'description',
                        $dsPorline->getValue('itemDescription')
                    );
                    $dsReceive->setValue(
                        'sequenceNo',
                        $sequenceNo
                    );
                    $dsReceive->setValue(
                        'orderSequenceNo',
                        $dsPorline->getValue('sequenceNo')
                    );
                    $dsReceive->setValue(
                        'qtyReceived',
                        0
                    );
                    $dsReceive->setValue(
                        'qtyToReceive',
                        0
                    );
                    $dsReceive->setValue(
                        'qtyOS',
                        1
                    );
                    $dsReceive->setValue(
                        'itemID',
                        $dbeItem->getValue('itemID')
                    );
                    $dsReceive->setValue(
                        'partNo',
                        $dbeItem->getValue('partNo')
                    );
                    if ($dbeCustomerItem->fetchNext()) {
                        $dsReceive->setValue(
                            'serialNo',
                            $dbeCustomerItem->getValue('serialNo')
                        );
                        $dsReceive->setValue(
                            'warrantyID',
                            $dbeCustomerItem->getValue('warrantyID')
                        );
                        $dsReceive->setValue(
                            'qtyOrdered',
                            1
                        );
                        $dsReceive->setValue(
                            'qtyReceived',
                            0
                        );
                        $dsReceive->setValue(
                            'qtyToReceive',
                            0
                        );
                        $dsReceive->setValue(
                            'qtyOS',
                            1
                        );
                        $dsReceive->setValue(
                            'allowReceive',
                            TRUE
                        );
                        $dsReceive->setValue(
                            'customerItemID',
                            $dbeCustomerItem->getPKValue()
                        );
                        $dsReceive->post();
                    } else {
                        // all the rest are out of stock so include on one line and break our of for loop
                        $dsReceive->setValue(
                            'serialNo',
                            'NOT IN STOCK'
                        );
                        $dsReceive->setValue(
                            'customerItemID',
                            null
                        );        // indicates disabled
                        $dsReceive->setValue(
                            'qtyOrdered',
                            $qtyOS - $i + 1
                        );
                        $dsReceive->setValue(
                            'qtyOS',
                            $qtyOS - $i + 1
                        );   // remaining qty are o/s
                        $dsReceive->setValue(
                            'warrantyID',
                            null
                        );
                        $dsReceive->setValue(
                            'allowReceive',
                            FALSE
                        );
                        $dsReceive->post();
                        break;
                    }
                }
            } else {
                $sequenceNo++;
                $dsReceive->setUpdateModeInsert();
                $dsReceive->setValue(
                    'requireSerialNo',
                    FALSE
                );
                $dsReceive->setValue(
                    'serialNo',
                    ''
                );
                $dsReceive->setValue(
                    'description',
                    $dsPorline->getValue('itemDescription')
                );
                $dsReceive->setValue(
                    'sequenceNo',
                    $sequenceNo
                );
                $dsReceive->setValue(
                    'orderSequenceNo',
                    $dsPorline->getValue('sequenceNo')
                );
                $dsReceive->setValue(
                    'qtyOrdered',
                    $dsPorline->getValue('qtyOrdered')
                );
                $dsReceive->setValue(
                    'qtyReceived',
                    $dsPorline->getValue('qtyReceived')
                );
                $dsReceive->setValue(
                    'qtyToReceive',
                    0
                );
                $dsReceive->setValue(
                    'warrantyID',
                    ''
                );
                $dsReceive->setValue(
                    'qtyOS',
                    $qtyOS
                );
                $dsReceive->setValue(
                    'itemID',
                    $dbeItem->getValue('itemID')
                );
                $dsReceive->setValue(
                    'partNo',
                    $dbeItem->getValue('partNo')
                );
                $dsReceive->setValue(
                    'allowReceive',
                    TRUE
                );
                $dsReceive->post();
            }
        }
        return TRUE;
    }

    function initialiseReceiveDataset(&$dsReceive)
    {
        $this->setMethodName('initialiseReceiveDataset');
        $dsReceive = new DataSet($this);
        $dsReceive->addColumn(
            'requireSerialNo',
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            'serialNo',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            'description',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            'sequenceNo',
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            'orderSequenceNo',
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            'qtyOrdered',
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            'qtyReceived',
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            'qtyToReceive',
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            'warrantyID',
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            'qtyOS',
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            'itemID',
            DA_ID,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            'customerItemID',
            DA_ID,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            'partNo',
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            'allowReceive',
            DA_INTEGER,
            DA_ALLOW_NULL
        );
    }

    function getAllWarranties(& $dsWarranty)
    {
        $this->setMethodName('getAllWarranties');
        $dbeWarranty = new DBEWarranty($this);
        $dbeWarranty->getRows();
        return ($this->getData(
            $dbeWarranty,
            $dsWarranty
        ));
    }

    function validateQtys(&$dsGoodsIn)
    {
        $this->setMethodName('validateQtys');
        $ret = TRUE;
        $dsGoodsIn->initialise();
        while ($dsGoodsIn->fetchNext()) {
            if ($dsGoodsIn->getValue('allowReceive')) {
                if ($dsGoodsIn->getValue('qtyOS') < $dsGoodsIn->getValue('qtyToReceive')) {
                    $ret = FALSE;
                    break;
                }
            }
        }
        return $ret;
    }

    function validateSerialNos(&$dsGoodsIn)
    {
        $this->setMethodName('validateSerialNos');
        $ret = TRUE;
        $dsGoodsIn->initialise();
        while ($dsGoodsIn->fetchNext()) {
            if (
                ($dsGoodsIn->getValue('qtyToReceive') > 0) &
                ($dsGoodsIn->getValue('serialNo') == '') &
                ($dsGoodsIn->getValue('allowReceive')) &
                ($dsGoodsIn->getValue('requireSerialNo'))
            ) {
                $ret = FALSE;
                break;
            }
        }
        return $ret;
    }

    function validateWarranties(&$dsGoodsIn)
    {
        $this->setMethodName('validateWarranties');
        $ret = TRUE;
        $dsGoodsIn->initialise();
        while ($dsGoodsIn->fetchNext()) {
            if (
                ($dsGoodsIn->getValue('qtyToReceive') > 0) &
                ($dsGoodsIn->getValue('warrantyID') == '') &
                ($dsGoodsIn->getValue('allowReceive')) &
                ($dsGoodsIn->getValue('requireSerialNo'))
            ) {
                $ret = FALSE;
                break;
            }
        }
        return $ret;
    }

    /**
     * Receive selected items and qtys.
     * 1) Update stock levels on item
     * 2) Create customer items (where appropriate)
     *
     * Some of the customer item fields in the old system were being filled in depending upon the delivery
     * method selected (direct, hand, etc). Because we are creating customer items at goods in we
     * don't have some of the info to hand.
     *
     * @param Integer porheadID purchase order number
     * @param Dataset $dsGoodsIn Dataset of recieved items
     */
    function receive($porheadID,
                     $userID,
                     & $dsGoodsIn
    )
    {
        $this->setMethodName('receive');
        $this->getHeaderByID(
            $porheadID,
            $dsPorhead
        );
        /*
        If the supplier is an internal stock location call appropriate method with stock customerID otherwise
        use non-stock method.
        */
        if (
            ($dsPorhead->getValue('supplierID') == CONFIG_SALES_STOCK_SUPPLIERID) OR
            ($dsPorhead->getValue('supplierID') == CONFIG_MAINT_STOCK_SUPPLIERID)
        ) {
            $this->receiveFromStock(
                $porheadID,
                $dsPorhead,
                $userID,
                $dsGoodsIn
            );
        } else {
            $this->receiveFromNonStock(
                $porheadID,
                $dsPorhead,
                $userID,
                $dsGoodsIn
            );
        }
    }

    /**
     * Receive selected items and qtys.
     * 1) Update stock levels on item
     * 2) Create customer items (where appropriate)
     *
     * Some of the customer item fields in the old system were being filled in depending upon the delivery
     * method selected (direct, hand, etc). Because we are creating customer items at goods in we
     * don't have some of the info to hand.
     *
     * @param Integer porheadID purchase order number
     * @param Dataset $dsGoodsIn Dataset of recieved items
     */
    function receiveFromNonStock($porheadID,
                                 &$dsPorhead,
                                 $userID,
                                 & $dsGoodsIn
    )
    {
        $this->setMethodName('receiveFromNonStock');
        if ($dsPorhead->getValue('ordheadID') != 0) {
            $buSalesOrder = new BUSalesOrder($this);
            $buSalesOrder->getOrdheadByID(
                $dsPorhead->getValue('ordheadID'),
                $dsOrdhead
            );
        }
        $dbePorline = new DBEPorline($this);
        $dbePorhead = new DBEPorhead($this);
        $dbeItem = new DBEItem($this);
        // Must process each item in dataset and update received qtys and possibly create customer item
        $dsGoodsIn->initialise();
        while ($dsGoodsIn->fetchNext()) {
            if (
                ($dsGoodsIn->getValue('qtyToReceive') <= 0) OR
                ($dsGoodsIn->getValue('allowReceive') == FALSE)
            ) {
                continue;
            }
            // if RequireSerialNo is TRUE then we know this is an item related to a sales order and
            // therefore must have a customer item created for it.
            if ($dsGoodsIn->getValue('requireSerialNo')) {
                if (!is_object($dbeCustomerItem)) {
                    $dbeCustomerItem = new DBECustomerItem($this);
                }
                $dbeCustomerItem->setValue(
                    'customerItemID',
                    0
                );
                $dbeCustomerItem->setValue(
                    'customerID',
                    $dsOrdhead->getValue('customerID')
                );
                $dbeCustomerItem->setValue(
                    'siteNo',
                    $dsOrdhead->getValue('delSiteNo')
                );
                $dbeCustomerItem->setValue(
                    'itemID',
                    $dsGoodsIn->getValue('itemID')
                );
                $dbeCustomerItem->setValue(
                    'userID',
                    $userID
                );
                $dbeCustomerItem->setValue(
                    'custItemRef',
                    $dsGoodsIn->getValue('description')
                );
                $dbeCustomerItem->setValue(
                    'contactID',
                    $dsOrdhead->getValue('delContactID')
                );
                $dbeCustomerItem->setValue(
                    'despatchDate',
                    date('Y-m-d')
                );
                $dbeCustomerItem->setValue(
                    'ordheadID',
                    $dsPorhead->getValue('ordheadID')
                );
                $dbeCustomerItem->setValue(
                    'porheadID',
                    $porheadID
                );
                $dbeCustomerItem->setValue(
                    'sOrderDate',
                    $dsOrdhead->getValue('date')
                );
                $dbeCustomerItem->setValue(
                    'curUnitSale',
                    ''
                );    // redundant I think
                $dbeCustomerItem->setValue(
                    'curUnitCost',
                    ''
                );    // redundant
                $dbeCustomerItem->setValue(
                    'custPORef',
                    $dsOrdhead->getValue('custPORef')
                );
                $stockcat = $dbeItem->getValue('stockcat');
                if (($stockcat == 'M') or ($stockcat == 'R')) {
                    $dbeCustomerItem->setValue(
                        'expiryDate',
                        date(
                            'Y-m-d',
                            strtotime('+ 1 year')
                        )
                    );
                } else {
                    $dbeCustomerItem->setValue(
                        'expiryDate',
                        null
                    );
                }
                $dbeCustomerItem->setValue(
                    'warrantyID',
                    $dsGoodsIn->getValue('warrantyID')
                );
                $dbeCustomerItem->setValue(
                    'serialNo',
                    $dsGoodsIn->getValue('serialNo')
                );
                $dbeCustomerItem->insertRow();
            }
            // update recieved qty on porline
            $dbePorline->setValue(
                'porheadID',
                $porheadID
            );
            $dbePorline->setValue(
                'sequenceNo',
                $dsGoodsIn->getValue('orderSequenceNo')
            );
            $dbePorline->getRow();
            $dbePorline->setValue(
                'qtyReceived',
                $dbePorline->getValue('qtyReceived') + $dsGoodsIn->getValue('qtyToReceive')
            );
            $dbePorline->updateRow();
            // update status on purchase order header
            $dbePorhead->getRow($porheadID);
            $dbePorline->setValue(
                'porheadID',
                $porheadID
            );
            if (($dbePorline->countOutstandingRows() == 0)) {
                $dbePorhead->setValue(
                    'type',
                    'C'
                );
            } else {
                $dbePorhead->setValue(
                    'type',
                    'P'
                );
            }
            $dbePorhead->updateRow();
            /*
            If the customer is an internal stock location then update the appropriate stock level
            */
            if ($dsOrdhead->getValue('customerID') == CONFIG_SALES_STOCK_CUSTOMERID) {
                $dbeItem->getRow($dsGoodsIn->getValue('itemID'));
                $dbeItem->setValue(
                    'salesStockQty',
                    $dbeItem->getValue('salesStockQty') + $dsGoodsIn->getValue('qtyToReceive')
                );
                $dbeItem->updateRow();
            } else if ($dsOrdhead->getValue('customerID') == CONFIG_MAINT_STOCK_CUSTOMERID) {
                $dbeItem->getRow($dsGoodsIn->getValue('itemID'));
                $dbeItem->setValue(
                    'maintStockQty',
                    $dbeItem->getValue('maintStockQty') + $dsGoodsIn->getValue('qtyToReceive')
                );
                $dbeItem->updateRow();
            }
        }//dsGoodsIn->fetchNext()
    }

    /**
     * Receive selected items and qtys.
     * 1) Update stock levels on item
     * 2) Create customer items (where appropriate)
     *
     * Some of the customer item fields in the old system were being filled in depending upon the delivery
     * method selected (direct, hand, etc). Because we are creating customer items at goods in we
     * don't have some of the info to hand.
     *
     * @param Integer porheadID purchase order number
     * @param Dataset $dsGoodsIn Dataset of recieved items
     */
    function receiveFromStock($porheadID,
                              &$dsPorhead,
                              $userID,
                              & $dsGoodsIn
    )
    {
        $this->setMethodName('receiveFromStock');
        if ($dsPorhead->getValue('ordheadID') != 0) {
            $buSalesOrder = new BUSalesOrder($this);
            $buSalesOrder->getOrdheadByID(
                $dsPorhead->getValue('ordheadID'),
                $dsOrdhead
            );
        }
        $dbePorline = new DBEPorline($this);
        $dbePorhead = new DBEPorhead($this);
        $dbeItem = new DBEItem($this);
        // Must process each item in dataset and update received qtys and possibly create customer item
        $dsGoodsIn->initialise();
        while ($dsGoodsIn->fetchNext()) {
            if (
                ($dsGoodsIn->getValue('qtyToReceive') <= 0) OR
                ($dsGoodsIn->getValue('allowReceive') == FALSE)
            ) {
                continue;
            }
            // if RequireSerialNo is TRUE then we know this item must have a customerItem
            // so we must update the customerID and Warranty ID accordingly.
            if ($dsGoodsIn->getValue('requireSerialNo')) {
                if (!is_object($dbeCustomerItem)) {
                    $dbeCustomerItem = new DBECustomerItem($this);
                }
                if (!$dbeCustomerItem->getRow($dsGoodsIn->getValue('customerItemID'))) {
                    $this->raiseError('customer item not found');
                }
                $dbeCustomerItem->setValue(
                    'customerID',
                    $dsOrdhead->getValue('customerID')
                );
                $dbeCustomerItem->setValue(
                    'siteNo',
                    $dsOrdhead->getValue('delSiteNo')
                );
                $dbeCustomerItem->setValue(
                    'userID',
                    $userID
                );
                $dbeCustomerItem->setValue(
                    'contactID',
                    $dsOrdhead->getValue('delContactID')
                );
                $dbeCustomerItem->setValue(
                    'despatchDate',
                    date('Y-m-d')
                );
                $dbeCustomerItem->setValue(
                    'ordheadID',
                    $dsPorhead->getValue('ordheadID')
                );
                $dbeCustomerItem->setValue(
                    'porheadID',
                    $porheadID
                );
                $dbeCustomerItem->setValue(
                    'sOrderDate',
                    $dsOrdhead->getValue('date')
                );
                $dbeCustomerItem->setValue(
                    'custPORef',
                    $dsOrdhead->getValue('custPORef')
                );
                $dbeCustomerItem->setValue(
                    'expiryDate',
                    null
                );
                $dbeCustomerItem->setValue(
                    'warrantyID',
                    $dsGoodsIn->getValue('warrantyID')
                );
                $dbeCustomerItem->updateRow();
            }
            // update recieved qty on porline
            $dbePorline->setValue(
                'porheadID',
                $porheadID
            );
            $dbePorline->setValue(
                'sequenceNo',
                $dsGoodsIn->getValue('orderSequenceNo')
            );
            $dbePorline->getRow();
            $dbePorline->setValue(
                'qtyReceived',
                $dbePorline->getValue('qtyReceived') + $dsGoodsIn->getValue('qtyToReceive')
            );
            $dbePorline->updateRow();
            // update status on purchase order header
            $dbePorhead->getRow($porheadID);
            $dbePorline->setValue(
                'porheadID',
                $porheadID
            );
            if (($dbePorline->countOutstandingRows() == 0)) {
                $dbePorhead->setValue(
                    'type',
                    'C'
                );
            } else {
                $dbePorhead->setValue(
                    'type',
                    'P'
                );
            }
            $dbePorhead->updateRow();

            /*
            reduce appropriate supplier stock level
            */
            if ($dsPorhead->getValue('supplierID') == CONFIG_SALES_STOCK_SUPPLIERID) {
                // sales stock
                $dbeItem->getRow($dsGoodsIn->getValue('itemID'));
                $dbeItem->setValue(
                    'salesStockQty',
                    $dbeItem->getValue('salesStockQty') - $dsGoodsIn->getValue('qtyToReceive')
                );
                $dbeItem->updateRow();
            } else {
                // maint stock
                $dbeItem->getRow($dsGoodsIn->getValue('itemID'));
                $dbeItem->setValue(
                    'maintStockQty',
                    $dbeItem->getValue('maintStockQty') - $dsGoodsIn->getValue('qtyToReceive')
                );
                $dbeItem->updateRow();
            }

            /*
            If the customer is an internal stock location then increase the appropriate stock level
            */
            if ($dsOrdhead->getValue('customerID') == CONFIG_SALES_STOCK_CUSTOMERID) {
                $dbeItem->getRow($dsGoodsIn->getValue('itemID'));
                $dbeItem->setValue(
                    'salesStockQty',
                    $dbeItem->getValue('salesStockQty') + $dsGoodsIn->getValue('qtyToReceive')
                );
                $dbeItem->updateRow();
            } else if ($dsOrdhead->getValue('customerID') == CONFIG_MAINT_STOCK_CUSTOMERID) {
                $dbeItem->getRow($dsGoodsIn->getValue('itemID'));
                $dbeItem->setValue(
                    'maintStockQty',
                    $dbeItem->getValue('maintStockQty') + $dsGoodsIn->getValue('qtyToReceive')
                );
                $dbeItem->updateRow();
            }
        }//dsGoodsIn->fetchNext()
    }
}// End of class
?>