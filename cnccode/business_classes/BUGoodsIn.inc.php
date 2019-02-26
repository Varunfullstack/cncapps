<?php
/**
 * PurchaseOrder business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BUSalesOrder.inc.php");
require_once($cfg["path_bu"] . "/BUPurchaseOrder.inc.php");
require_once($cfg["path_dbe"] . "/DBEItem.inc.php");
require_once($cfg["path_dbe"] . "/DBEPorhead.inc.php");
require_once($cfg["path_dbe"] . "/DBEWarranty.inc.php");
require_once($cfg["path_dbe"] . "/DBEPorline.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomerItem.inc.php");

class BUGoodsIn extends Business
{
    /** @var BUSalesOrder */
    public $buSalesOrder;
    var $dbePorhead = '';
    var $dbeItem = '';
    var $dsOrdhead = '';

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeItem = new DBEItem($this);        // ref to class var
    }

    function search($supplierID,
                    $porheadID,
                    $ordheadID,
                    $supplierRef,
                    $type,
                    $lineText,
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
            if ($dsResults->getValue('directDeliveryFlag' == 'Y')) {
                $ret = FALSE;
            } else {
                $ret = TRUE;
            }
        } else {
            $dbeJPorhead->getRowsBySearchCriteria(
                $supplierID,
                $ordheadID,
                $type,
                $supplierRef,
                $lineText,
                '',
                '',
                '',
                'GI'
            );
            $dbeJPorhead->initialise();
            $dsResults = $dbeJPorhead;
        }
        return $ret;
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
        $dbeItem = &$this->dbeItem;        // ref to class var
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
                    $dsReceive->setValue(
                        'renew',
                        FALSE
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
                $dsReceive->setValue(
                    'renew',
                    FALSE
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
     * @param Integer $customerID CustomerID
     * @param DataSet dsPorline Purchase order lines
     * @param DataSet dsReceieve Result set of receive lines with correct default values
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
                $dsCustomerItem = new DataSet($this);
                $dsCustomerItem->replicate($dbeCustomerItem);

                $dsCustomerItem->initialise();

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
                    if ($dsCustomerItem->fetchNext()) {
                        $dsReceive->setValue(
                            'serialNo',
                            $dsCustomerItem->getValue('serialNo')
                        );
                        $dsReceive->setValue(
                            'warrantyID',
                            $dsCustomerItem->getValue('warrantyID')
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
                            'renew',
                            FALSE
                        );
                        $dsReceive->setValue(
                            'customerItemID',
                            $dsCustomerItem->getValue('customerItemID')
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
                        $dsReceive->setValue(
                            'renew',
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
                $dsReceive->setValue(
                    'renew',
                    FALSE
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
        $dsReceive->addColumn(
            'renew',
            DA_INTEGER,
            DA_ALLOW_NULL
        );
    }

    function getAllWarranties(& $dsWarranty)
    {
        $this->setMethodName('getAllWarranties');
        $dbeWarranty = new DBEWarranty($this);
        $dbeWarranty->getRows('description');
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
        $this->dbePorhead = new DBEPorhead($this);
        $dbeItem = &$this->dbeItem;        // ref to class var
        $buPurchaseOrder = new BUPurchaseOrder($this);
        $buPurchaseOrder->getHeaderByID(
            $porheadID,
            $dsPorhead
        );
        $this->buSalesOrder = new BUSalesOrder($this);
        $this->buSalesOrder->getOrdheadByID(
            $dsPorhead->getValue('ordheadID'),
            $this->dsOrdhead
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
        $dsOrdhead = &$this->dsOrdhead;
        $dbePorline = new DBEPorline($this);
        $dbeItem = &$this->dbeItem;
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
                    'despatchDate',
                    date('Y-m-d')
                );
                $dbeCustomerItem->setValue(
                    'curUnitSale',
                    ''
                );    // redundant I think
                $dbeCustomerItem->setValue(
                    'curUnitCost',
                    ''
                );    // redundant
                $stockcat = $dbeItem->getValue('stockcat');
                if (($stockcat == 'M') or ($stockcat == 'R')) {                        // cnc support contract
                    $dbeCustomerItem->setValue(
                        'expiryDate',
                        date(
                            'Y-m-d',
                            strtotime('+ 1 year')
                        )
                    );
                } else if ($dsGoodsIn->getValue('renew') == TRUE) {
                    // bug 245: Add warranty years to current date to calculate expiry date.
                    $dbeWarranty = new DBEWarranty($this);
                    $dbeWarranty->getRow($dsGoodsIn->getValue('warrantyID'));
                    $dbeCustomerItem->setValue(
                        'expiryDate',
                        date(
                            'Y-m-d',
                            strtotime('+ ' . $dbeWarranty->getValue('years') . ' year')
                        )
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
            $newQtyRecieved = $dbePorline->getValue('qtyReceived') + $dsGoodsIn->getValue('qtyToReceive');
            $dbePorline->setValue(
                'qtyReceived',
                $newQtyRecieved
            );
            if ($newQtyRecieved == $dbePorline->getValue(
                    'qtyOrdered'
                )) {    // if line fully recieved then set to expected date blank
                $dbePorline->setValue(
                    'expectedDate',
                    null
                );
            }
            $dbePorline->updateRow();
            // update status on purchase order header
            $this->dbePorhead->getRow($porheadID);
            $dbePorline->setValue(
                'porheadID',
                $porheadID
            );
            if (($dbePorline->countOutstandingRows() == 0)) {
                if ($dsPorhead->getValue(DBEPorhead::completionNotifiedFlag) != 'Y') {
                    $this->buSalesOrder->notifyPurchaseOrderCompletion($dsPorhead->getValue(DBEPorhead::porheadID));
                }
                $this->dbePorhead->setValue(
                    'type',
                    'C'
                );
            } else {
                $this->dbePorhead->setValue(
                    'type',
                    'P'
                );
            }
            $this->dbePorhead->updateRow();
            /*
            If the customer is an internal stock location then update the appropriate stock level
            */
            if ($dsOrdhead->getValue('customerID') == CONFIG_SALES_STOCK_CUSTOMERID) {
                $dbeItem->getRow($dsGoodsIn->getValue('itemID'));
                $newValue = $dbeItem->getValue('salesStockQty') + $dsGoodsIn->getValue('qtyToReceive');
                $dbeItem->updateSalesStockQty($newValue);
            } else if ($dsOrdhead->getValue('customerID') == CONFIG_MAINT_STOCK_CUSTOMERID) {
                $dbeItem->getRow($dsGoodsIn->getValue('itemID'));
                $newValue = $dbeItem->getValue('maintStockQty') + $dsGoodsIn->getValue('qtyToReceive');
                $dbeItem->updateMaintStockQty($newValue);
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
                              DataSet &$dsPorhead,
                              $userID,
                              & $dsGoodsIn
    )
    {
        $this->setMethodName('receiveFromStock');
        $dsOrdhead = &$this->dsOrdhead; // ref to class var
        $dbeItem = &$this->dbeItem;            // ref to class var
        $dbePorline = new DBEPorline($this);
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
                    'serialNo',
                    $dsGoodsIn->getValue('serialNo')
                );
                $dbeCustomerItem->setValue(
                    'ordheadID',
                    $dsPorhead->getValue('ordheadID')
                );

                // If purchase from stock then do not update the original purchase order info
                if (!common_isAnInternalStockSupplier($dsPorhead->getValue('supplierID'))) {
                    $dbeCustomerItem->setValue(
                        'porheadID',
                        $porheadID
                    );
                }

                $dbeCustomerItem->setValue(
                    'sOrderDate',
                    $dsOrdhead->getValue('date')
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

            // there is no purchase invoice authorisation for stock suppliers so we update the invoiced qty here
            $dbePorline->setValue(
                'qtyInvoiced',
                $dbePorline->getValue('qtyInvoiced') + $dsGoodsIn->getValue('qtyToReceive')
            ); // not really required but hey!
            $dbePorline->updateRow();

            // update status on purchase order header
            $this->dbePorhead->getRow($porheadID);
            $dbePorline->setValue(
                'porheadID',
                $porheadID
            );
            if (($dbePorline->countOutstandingRows() == 0)) {
                if ($dsPorhead->getValue(DBEPorhead::completionNotifiedFlag) != 'Y') {
                    $this->buSalesOrder->notifyPurchaseOrderCompletion($dsPorhead->getValue(DBEPorhead::porheadID));
                }
                $this->dbePorhead->setValue(
                    'type',
                    'A'
                );
            } else {
                $this->dbePorhead->setValue(
                    'type',
                    'P'
                );
            }
            $this->dbePorhead->updateRow();

            //	Reduce appropriate stock level
            $dbeItem->getRow($dsGoodsIn->getValue('itemID'));
            if ($dsPorhead->getValue('supplierID') == CONFIG_SALES_STOCK_SUPPLIERID) {
                // sales stock
                $newValue = $dbeItem->getValue('salesStockQty') - $dsGoodsIn->getValue('qtyToReceive');
                $dbeItem->updateSalesStockQty($newValue);
            } else {
                // maint stock
                $newValue = $dbeItem->getValue('maintStockQty') - $dsGoodsIn->getValue('qtyToReceive');
                $dbeItem->updateMaintStockQty($newValue);
            }

            /*
            If the customer is an internal stock location then increase the appropriate stock level
            */
            if ($dsOrdhead->getValue('customerID') == CONFIG_SALES_STOCK_CUSTOMERID) {
                $newValue = $dbeItem->getValue('salesStockQty') + $dsGoodsIn->getValue('qtyToReceive');
                $dbeItem->updateSalesStockQty($newValue);
            } else if ($dsOrdhead->getValue('customerID') == CONFIG_MAINT_STOCK_CUSTOMERID) {
                $newValue = $dbeItem->getValue('maintStockQty') + $dsGoodsIn->getValue('qtyToReceive');
                $dbeItem->updateMaintStockQty($newValue);
            }
        }//dsGoodsIn->fetchNext()
    }

    function updateSalesOrderStatus()
    {
        /*
        If customer is a stock location and all purchase orders for this sales order are now authorised then
        set sales order	status to completed.
        */
        if (
            (common_isAnInternalStockLocation($this->dsOrdhead->getValue('customerID'))) &
            ($this->dbePorhead->countNonAuthorisedRowsBySO($this->dsPorhead->getValue('ordheadID')) == 0)
        ) {
            $this->buSalesOrder->setStatusCompleted($this->dsPorhead->getValue('ordheadID'));
        }
    }
}// End of class
?>