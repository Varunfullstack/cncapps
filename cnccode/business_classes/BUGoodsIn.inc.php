<?php
/**
 * PurchaseOrder business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
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
    const receiveDataSetRequireSerialNo = 'requireSerialNo';
    const receiveDataSetSerialNo = 'serialNo';
    const receiveDataSetDescription = 'description';
    const receiveDataSetSequenceNo = 'sequenceNo';
    const receiveDataSetOrderSequenceNo = 'orderSequenceNo';
    const receiveDataSetQtyOrdered = 'qtyOrdered';
    const receiveDataSetQtyReceived = 'qtyReceived';
    const receiveDataSetQtyToReceive = 'qtyToReceive';
    const receiveDataSetWarrantyID = 'warrantyID';
    const receiveDataSetQtyOS = 'qtyOS';
    const receiveDataSetItemID = 'itemID';
    const receiveDataSetCustomerItemID = 'customerItemID';
    const receiveDataSetPartNo = 'partNo';
    const receiveDataSetAllowReceive = 'allowReceive';
    const receiveDataSetRenew = 'renew';


    /** @var BUSalesOrder */
    public $buSalesOrder;
    /** @var DBEPorhead */
    public $dbePorhead;
    public $dbeItem;
    /** @var DataSet|DBEOrdhead */
    public $dsOrdhead;

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

    /**
     * @param DataSet $dsResults
     * @param $supplierID
     * @param $porheadID
     * @param $ordheadID
     * @param $supplierRef
     * @param $type
     * @param $lineText
     * @return bool
     */
    function search(&$dsResults,
                    $supplierID = null,
                    $porheadID = null,
                    $ordheadID = null,
                    $supplierRef = null,
                    $type = null,
                    $lineText = null
    )
    {
        $this->setMethodName('search');
        $dbeJPorhead = new DBEJPorhead($this);
        if ($porheadID) {
            $this->getDatasetByPK(
                $porheadID,
                $dbeJPorhead,
                $dsResults
            );
            return $dsResults->getValue(DBEPorhead::directDeliveryFlag) == 'Y';
        } else {
            $dbeJPorhead->getRowsBySearchCriteria(
                $supplierID,
                $ordheadID,
                $type,
                $supplierRef,
                $lineText,
                null,
                null,
                null,
                'GI'
            );
            $dbeJPorhead->initialise();
            $dsResults = $dbeJPorhead;
        }
        return true;
    }

    /**
     * Return a dataset of despatch qtys for this set of order lines
     *
     * We only need to ask for serial numbers and warranty details if the associated sales order has
     * addItem = "Y"
     * @param DataSet $dsPorline
     * @param DataSet $dsReceive
     * @param String $addCustomerItems Y/N flag to indicate whether we need to ask for serial no
     * and warranty info for adding a customeritem
     * @return bool
     */
    function getInitialReceiveQtys(&$dsPorline,
                                   &$dsReceive,
                                   $addCustomerItems
    )
    {
        $this->setMethodName('getInitialReceiveQtys');
        $this->initialiseReceiveDataset($dsReceive);
        $dbeItem = &$this->dbeItem;        // ref to class var
        $dsPorline->initialise();
        $sequenceNo = 0;

        while ($dsPorline->fetchNext()) {
            $itemID = $dsPorline->getValue(DBEPorline::itemID);
            $qtyOS = $dsPorline->getValue(DBEPorline::qtyOrdered) - $dsPorline->getValue(DBEPorline::qtyReceived);
            // skip if nothing outstanding for this order line
            if ($qtyOS <= 0) {
                continue;
            }
            // else add a line to receipts dataset
            $dbeItem->getRow($itemID);
            // if this item requires a serial number and this purchase order requires customer items adding
            if (($dbeItem->getValue(DBEItem::serialNoFlag) == 'Y') & $addCustomerItems) {
                for ($i = 1; $i <= $qtyOS; $i++) {
                    $sequenceNo++;
                    $dsReceive->setUpdateModeInsert();
                    $dsReceive->setValue(
                        self::receiveDataSetRequireSerialNo,
                        TRUE
                    );
                    $dsReceive->setValue(
                        self::receiveDataSetSerialNo,
                        null
                    );
                    $dsReceive->setValue(
                        self::receiveDataSetDescription,
                        $dsPorline->getValue(DBEJPorline::itemDescription)
                    );
                    $dsReceive->setValue(
                        self::receiveDataSetSequenceNo,
                        $sequenceNo
                    );
                    $dsReceive->setValue(
                        self::receiveDataSetOrderSequenceNo,
                        $dsPorline->getValue(DBEPorline::sequenceNo)
                    );
                    $dsReceive->setValue(
                        self::receiveDataSetQtyOrdered,
                        1
                    );
                    $dsReceive->setValue(
                        self::receiveDataSetQtyReceived,
                        0
                    );
                    $dsReceive->setValue(
                        self::receiveDataSetQtyToReceive,
                        0
                    );
                    $dsReceive->setValue(
                        self::receiveDataSetWarrantyID,
                        $dbeItem->getValue(DBEItem::warrantyID)
                    );
                    $dsReceive->setValue(
                        self::receiveDataSetQtyOS,
                        1
                    );
                    $dsReceive->setValue(
                        self::receiveDataSetItemID,
                        $dbeItem->getValue(DBEItem::itemID)
                    );
                    $dsReceive->setValue(
                        self::receiveDataSetPartNo,
                        $dbeItem->getValue(DBEItem::partNo)
                    );
                    $dsReceive->setValue(
                        self::receiveDataSetAllowReceive,
                        TRUE
                    );
                    $dsReceive->setValue(
                        self::receiveDataSetRenew,
                        FALSE
                    );
                    $dsReceive->post();

                }
            } else {
                $sequenceNo++;
                $dsReceive->setUpdateModeInsert();
                $dsReceive->setValue(
                    self::receiveDataSetRequireSerialNo,
                    FALSE
                );
                $dsReceive->setValue(
                    self::receiveDataSetSerialNo,
                    null
                );
                $dsReceive->setValue(
                    self::receiveDataSetDescription,
                    $dsPorline->getValue(DBEJPorline::itemDescription)
                );
                $dsReceive->setValue(
                    self::receiveDataSetSequenceNo,
                    $sequenceNo
                );
                $dsReceive->setValue(
                    self::receiveDataSetOrderSequenceNo,
                    $dsPorline->getValue(DBEPorline::sequenceNo)
                );
                $dsReceive->setValue(
                    self::receiveDataSetQtyOrdered,
                    $dsPorline->getValue(DBEPorline::qtyOrdered)
                );
                $dsReceive->setValue(
                    self::receiveDataSetQtyReceived,
                    $dsPorline->getValue(DBEPorline::qtyReceived)
                );
                $dsReceive->setValue(
                    self::receiveDataSetQtyToReceive,
                    0
                );
                $dsReceive->setValue(
                    self::receiveDataSetWarrantyID,
                    null
                );
                $dsReceive->setValue(
                    self::receiveDataSetQtyOS,
                    $qtyOS
                );
                $dsReceive->setValue(
                    self::receiveDataSetItemID,
                    $dbeItem->getValue(DBEItem::itemID)
                );
                $dsReceive->setValue(
                    self::receiveDataSetPartNo,
                    $dbeItem->getValue(DBEItem::partNo)
                );
                $dsReceive->setValue(
                    self::receiveDataSetAllowReceive,
                    TRUE
                );
                $dsReceive->setValue(
                    self::receiveDataSetRenew,
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
            self::receiveDataSetRequireSerialNo,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            self::receiveDataSetSerialNo,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            self::receiveDataSetDescription,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            self::receiveDataSetSequenceNo,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            self::receiveDataSetOrderSequenceNo,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            self::receiveDataSetQtyOrdered,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            self::receiveDataSetQtyReceived,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            self::receiveDataSetQtyToReceive,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            self::receiveDataSetWarrantyID,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            self::receiveDataSetQtyOS,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            self::receiveDataSetItemID,
            DA_ID,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            self::receiveDataSetCustomerItemID,
            DA_ID,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            self::receiveDataSetPartNo,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            self::receiveDataSetAllowReceive,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsReceive->addColumn(
            self::receiveDataSetRenew,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
    }

    /**
     * Return a dataset of despatch qtys for this set of order lines where receiving from internal
     * stock location.
     *
     * This method differs from getInitialReceiveQtys() in that , where a customer item exists at that
     * stock location for the item, it's serial number will be pre-determined from the customer item
     * and the warranty will default to that on the customer item.
     *
     * @param Integer $customerID CustomerID
     * @param DataSet $dsPorline Purchase order lines
     * @param DataSet $dsReceive Result set of receive lines with correct default values
     * @return bool
     */
    function getInitialStockReceiveQtys($customerID,
                                        &$dsPorline,
                                        &$dsReceive
    )
    {
        $this->setMethodName('getInitialStockReceiveQtys');
        $this->initialiseReceiveDataset($dsReceive);
        $dbeItem = new DBEItem($this);
        $dbeCustomerItem = new DBECustomerItem($this);
        $dsPorline->initialise();
        $sequenceNo = 0;
        while ($dsPorline->fetchNext()) {
            $itemID = $dsPorline->getValue(DBEJPorline::itemID);
            $qtyOS = $dsPorline->getValue(DBEJPorline::qtyOrdered) - $dsPorline->getValue(DBEJPorline::qtyReceived);
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
            if (($dbeItem->getValue(DBEItem::serialNoFlag) == 'Y')) {
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
                        self::receiveDataSetRequireSerialNo,
                        TRUE
                    );
                    $dsReceive->setValue(
                        self::receiveDataSetDescription,
                        $dsPorline->getValue(DBEJPorline::itemDescription)
                    );
                    $dsReceive->setValue(
                        self::receiveDataSetSequenceNo,
                        $sequenceNo
                    );
                    $dsReceive->setValue(
                        self::receiveDataSetOrderSequenceNo,
                        $dsPorline->getValue(DBEJPorline::sequenceNo)
                    );
                    $dsReceive->setValue(
                        self::receiveDataSetQtyReceived,
                        0
                    );
                    $dsReceive->setValue(
                        self::receiveDataSetQtyToReceive,
                        0
                    );
                    $dsReceive->setValue(
                        self::receiveDataSetQtyOS,
                        1
                    );
                    $dsReceive->setValue(
                        self::receiveDataSetItemID,
                        $dbeItem->getValue(DBEItem::itemID)
                    );
                    $dsReceive->setValue(
                        self::receiveDataSetPartNo,
                        $dbeItem->getValue(DBEItem::partNo)
                    );
                    if ($dsCustomerItem->fetchNext()) {
                        $dsReceive->setValue(
                            self::receiveDataSetSerialNo,
                            $dsCustomerItem->getValue(DBECustomerItem::serialNo)
                        );
                        $dsReceive->setValue(
                            self::receiveDataSetWarrantyID,
                            $dsCustomerItem->getValue(DBECustomerItem::warrantyID)
                        );
                        $dsReceive->setValue(
                            self::receiveDataSetQtyOrdered,
                            1
                        );
                        $dsReceive->setValue(
                            self::receiveDataSetQtyReceived,
                            0
                        );
                        $dsReceive->setValue(
                            self::receiveDataSetQtyToReceive,
                            0
                        );
                        $dsReceive->setValue(
                            self::receiveDataSetQtyOS,
                            1
                        );
                        $dsReceive->setValue(
                            self::receiveDataSetAllowReceive,
                            TRUE
                        );
                        $dsReceive->setValue(
                            self::receiveDataSetRenew,
                            FALSE
                        );
                        $dsReceive->setValue(
                            self::receiveDataSetCustomerItemID,
                            $dsCustomerItem->getValue(DBECustomerItem::customerItemID)
                        );
                        $dsReceive->post();
                    } else {
                        // all the rest are out of stock so include on one line and break our of for loop
                        $dsReceive->setValue(
                            self::receiveDataSetSerialNo,
                            'NOT IN STOCK'
                        );
                        $dsReceive->setValue(
                            self::receiveDataSetCustomerItemID,
                            null
                        );        // indicates disabled
                        $dsReceive->setValue(
                            self::receiveDataSetQtyOrdered,
                            $qtyOS - $i + 1
                        );
                        $dsReceive->setValue(
                            self::receiveDataSetQtyOS,
                            $qtyOS - $i + 1
                        );   // remaining qty are o/s
                        $dsReceive->setValue(
                            self::receiveDataSetWarrantyID,
                            null
                        );
                        $dsReceive->setValue(
                            self::receiveDataSetAllowReceive,
                            FALSE
                        );
                        $dsReceive->setValue(
                            self::receiveDataSetRenew,
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
                    self::receiveDataSetRequireSerialNo,
                    FALSE
                );
                $dsReceive->setValue(
                    self::receiveDataSetSerialNo,
                    null
                );
                $dsReceive->setValue(
                    self::receiveDataSetDescription,
                    $dsPorline->getValue(DBEJPorline::itemDescription)
                );
                $dsReceive->setValue(
                    self::receiveDataSetSequenceNo,
                    $sequenceNo
                );
                $dsReceive->setValue(
                    self::receiveDataSetOrderSequenceNo,
                    $dsPorline->getValue(DBEJPorline::sequenceNo)
                );
                $dsReceive->setValue(
                    self::receiveDataSetQtyOrdered,
                    $dsPorline->getValue(DBEJPorline::qtyOrdered)
                );
                $dsReceive->setValue(
                    self::receiveDataSetQtyReceived,
                    $dsPorline->getValue(DBEJPorline::qtyReceived)
                );
                $dsReceive->setValue(
                    self::receiveDataSetQtyToReceive,
                    0
                );
                $dsReceive->setValue(
                    self::receiveDataSetWarrantyID,
                    null
                );
                $dsReceive->setValue(
                    self::receiveDataSetQtyOS,
                    $qtyOS
                );
                $dsReceive->setValue(
                    self::receiveDataSetItemID,
                    $dbeItem->getValue(DBEItem::itemID)
                );
                $dsReceive->setValue(
                    self::receiveDataSetPartNo,
                    $dbeItem->getValue(DBEItem::partNo)
                );
                $dsReceive->setValue(
                    self::receiveDataSetAllowReceive,
                    TRUE
                );
                $dsReceive->setValue(
                    self::receiveDataSetRenew,
                    FALSE
                );
                $dsReceive->post();
            }
        }
        return TRUE;
    }

    function getAllWarranties(&$dsWarranty)
    {
        $this->setMethodName('getAllWarranties');
        $dbeWarranty = new DBEWarranty($this);
        $dbeWarranty->getRows('description');
        return ($this->getData(
            $dbeWarranty,
            $dsWarranty
        ));
    }

    /**
     * @param DataSet $dsGoodsIn
     * @return bool
     */
    function validateQtys(&$dsGoodsIn)
    {
        $this->setMethodName('validateQtys');
        $ret = TRUE;
        $dsGoodsIn->initialise();
        while ($dsGoodsIn->fetchNext()) {
            if ($dsGoodsIn->getValue(self::receiveDataSetAllowReceive)) {
                if ($dsGoodsIn->getValue(self::receiveDataSetQtyOS) < $dsGoodsIn->getValue(
                        self::receiveDataSetQtyToReceive
                    )) {
                    $ret = FALSE;
                    break;
                }
            }
        }
        return $ret;
    }

    /**
     * @param DataSet $dsGoodsIn
     * @return bool
     */
    function validateSerialNos(&$dsGoodsIn)
    {
        $this->setMethodName('validateSerialNos');
        $ret = TRUE;
        $dsGoodsIn->initialise();
        while ($dsGoodsIn->fetchNext()) {
            if (
                $dsGoodsIn->getValue(self::receiveDataSetQtyToReceive) &&
                !$dsGoodsIn->getValue(self::receiveDataSetSerialNo) &&
                $dsGoodsIn->getValue(self::receiveDataSetAllowReceive) &&
                $dsGoodsIn->getValue(self::receiveDataSetRequireSerialNo)
            ) {
                $ret = FALSE;
                break;
            }
        }
        return $ret;
    }

    /**
     * @param DataSet $dsGoodsIn
     * @return bool
     */
    function validateWarranties(&$dsGoodsIn)
    {
        $this->setMethodName('validateWarranties');
        $ret = TRUE;
        $dsGoodsIn->initialise();
        while ($dsGoodsIn->fetchNext()) {
            if (
                $dsGoodsIn->getValue(self::receiveDataSetQtyToReceive) &&
                !$dsGoodsIn->getValue(self::receiveDataSetWarrantyID) &&
                $dsGoodsIn->getValue(self::receiveDataSetAllowReceive) &&
                $dsGoodsIn->getValue(self::receiveDataSetRequireSerialNo)
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
     * @param $porheadID
     * @param Dataset $dsGoodsIn Dataset of received items
     */
    function receive($porheadID,
                     &$dsGoodsIn
    )
    {
        $this->setMethodName('receive');
        $this->dbePorhead = new DBEPorhead($this);
        $buPurchaseOrder = new BUPurchaseOrder($this);
        $dsPorhead = new DataSet($this);


        $buPurchaseOrder->getHeaderByID(
            $porheadID,
            $dsPorhead
        );
        $this->buSalesOrder = new BUSalesOrder($this);
        $this->buSalesOrder->getOrdheadByID(
            $dsPorhead->getValue(DBEJPorhead::ordheadID),
            $this->dsOrdhead
        );

        if (in_array($dsPorhead->getValue(DBEPorhead::type), ['C', 'A'])) {
            return;
        }
        /*
        If the supplier is an internal stock location call appropriate method with stock customerID otherwise
        use non-stock method.
        */

        if (
            ($dsPorhead->getValue(DBEJPorhead::supplierID) == CONFIG_SALES_STOCK_SUPPLIERID) ||
            ($dsPorhead->getValue(DBEJPorhead::supplierID) == CONFIG_MAINT_STOCK_SUPPLIERID)
        ) {
            $this->receiveFromStock(
                $porheadID,
                $dsPorhead,
                $dsGoodsIn
            );
        } else {
            $this->receiveFromNonStock(
                $porheadID,
                $dsPorhead,
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
     * @param $porheadID
     * @param DataSet $dsPorhead
     * @param Dataset $dsGoodsIn Dataset of received items
     */
    function receiveFromStock($porheadID,
                              DataSet &$dsPorhead,
                              &$dsGoodsIn
    )
    {
        $this->setMethodName('receiveFromStock');
        $dsOrdhead = &$this->dsOrdhead; // ref to class var
        $dbeItem = &$this->dbeItem;            // ref to class var
        $dbePorline = new DBEPorline($this);
        $dbeCustomerItem = null;
        // Must process each item in dataset and update received qtys and possibly create customer item
        $dsGoodsIn->initialise();
        while ($dsGoodsIn->fetchNext()) {
            if (
                ($dsGoodsIn->getValue(self::receiveDataSetQtyToReceive) <= 0) ||
                !$dsGoodsIn->getValue(self::receiveDataSetAllowReceive)
            ) {
                continue;
            }
            // if RequireSerialNo is TRUE then we know this item must have a customerItem
            // so we must update the customerID and Warranty ID accordingly.
            if ($dsGoodsIn->getValue(self::receiveDataSetRequireSerialNo)) {
                if (!is_object($dbeCustomerItem)) {
                    $dbeCustomerItem = new DBECustomerItem($this);
                }
                if (!$dbeCustomerItem->getRow($dsGoodsIn->getValue(self::receiveDataSetCustomerItemID))) {
                    $this->raiseError('customer item not found');
                }
                $dbeCustomerItem->setValue(
                    DBECustomerItem::customerID,
                    $dsOrdhead->getValue(DBEOrdhead::customerID)
                );
                $dbeCustomerItem->setValue(
                    DBECustomerItem::siteNo,
                    $dsOrdhead->getValue(DBEOrdhead::delSiteNo)
                );
                $dbeCustomerItem->setValue(
                    DBECustomerItem::serialNo,
                    $dsGoodsIn->getValue(self::receiveDataSetSerialNo)
                );
                $dbeCustomerItem->setValue(
                    DBECustomerItem::ordheadID,
                    $dsPorhead->getValue(DBEPorhead::ordheadID)
                );

                // If purchase from stock then do not update the original purchase order info
                if (!common_isAnInternalStockSupplier($dsPorhead->getValue(DBEPorhead::supplierID))) {
                    $dbeCustomerItem->setValue(
                        DBECustomerItem::porheadID,
                        $porheadID
                    );
                }

                $dbeCustomerItem->setValue(
                    DBECustomerItem::sOrderDate,
                    $dsOrdhead->getValue(DBEOrdhead::date)
                );
                $dbeCustomerItem->setValue(
                    DBECustomerItem::expiryDate,
                    null
                );
                $dbeCustomerItem->setValue(
                    DBECustomerItem::warrantyID,
                    $dsGoodsIn->getValue(self::receiveDataSetWarrantyID)
                );
                $dbeCustomerItem->updateRow();
            }
            // update received qty on porline
            $dbePorline->setValue(
                DBEPorline::porheadID,
                $porheadID
            );
            $dbePorline->setValue(
                DBEPorline::sequenceNo,
                $dsGoodsIn->getValue(self::receiveDataSetOrderSequenceNo)
            );
            $dbePorline->getRow();
            $newQtyReceived = $dbePorline->getValue(DBEJPorline::qtyReceived) + $dsGoodsIn->getValue(
                    self::receiveDataSetQtyToReceive
                );
            $dbePorline->setValue(
                DBEPorline::qtyReceived,
                $newQtyReceived
            );
            if ($newQtyReceived == $dbePorline->getValue(
                    DBEJPorline::qtyOrdered
                )) {    // if line fully received then set to expected date blank
                $dbePorline->setValue(
                    DBEJPorline::expectedTBC,
                    false
                );
                if (!$dbePorline->getValue(DBEPorline::expectedDate)) {
                    $dbePorline->setValue(DBEPorline::expectedDate, (new DateTime())->format(DATE_MYSQL_DATE));
                }
            }

            // there is no purchase invoice authorisation for stock suppliers so we update the invoiced qty here
            $dbePorline->setValue(
                DBEPorline::qtyInvoiced,
                $dbePorline->getValue(DBEPorline::qtyInvoiced) + $dsGoodsIn->getValue(self::receiveDataSetQtyToReceive)
            ); // not really required but hey!

            $dbePorline->updateRow();

            // update status on purchase order header
            $this->dbePorhead->getRow($porheadID);
            $dbePorline->setValue(
                DBEJPorline::porheadID,
                $porheadID
            );

            $dbeJPorline = new DBEJPorline($this);
            $dbeJPorline->setValue(DBEJPorline::porheadID, $porheadID);

            if (($dbeJPorline->countOutstandingRows() == 0)) {
                if ($dsPorhead->getValue(DBEPorhead::completionNotifiedFlag) != 'Y') {
                    $this->buSalesOrder->notifyPurchaseOrderCompletion($this->dbePorhead,true);
                }
                $this->dbePorhead->setValue(
                    DBEPorhead::type,
                    'A'
                );
            } else {
                $this->dbePorhead->setValue(
                    DBEPorhead::type,
                    'P'
                );
            }
            $this->dbePorhead->updateRow();

            //	Reduce appropriate stock level
            $dbeItem->getRow($dsGoodsIn->getValue(DBEItem::itemID));
            if ($dsPorhead->getValue(DBEPorhead::supplierID) == CONFIG_SALES_STOCK_SUPPLIERID) {
                // sales stock
                $newValue = $dbeItem->getValue(DBEItem::salesStockQty) - $dsGoodsIn->getValue(
                        self::receiveDataSetQtyToReceive
                    );
                $dbeItem->updateSalesStockQty($newValue);
            } else {
                // maint stock
                $newValue = $dbeItem->getValue(DBEItem::maintStockQty) - $dsGoodsIn->getValue(
                        self::receiveDataSetQtyToReceive
                    );
                $dbeItem->updateMaintStockQty($newValue);
            }

            /*
            If the customer is an internal stock location then increase the appropriate stock level
            */
            if ($dsOrdhead->getValue(DBEOrdhead::customerID) == CONFIG_SALES_STOCK_CUSTOMERID) {
                $newValue = $dbeItem->getValue(DBEItem::salesStockQty) + $dsGoodsIn->getValue(
                        self::receiveDataSetQtyToReceive
                    );
                $dbeItem->updateSalesStockQty($newValue);
            } else if ($dsOrdhead->getValue(DBEOrdhead::customerID) == CONFIG_MAINT_STOCK_CUSTOMERID) {
                $newValue = $dbeItem->getValue(DBEItem::maintStockQty) + $dsGoodsIn->getValue(
                        self::receiveDataSetQtyToReceive
                    );
                $dbeItem->updateMaintStockQty($newValue);
            }
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
     * @param $porheadID
     * @param DataSet|DBEPorhead $dsPorhead
     * @param Dataset $dsGoodsIn Dataset of received items
     */
    function receiveFromNonStock($porheadID,
                                 &$dsPorhead,
                                 &$dsGoodsIn
    )
    {
        $this->setMethodName('receiveFromNonStock');
        $dsOrdhead = &$this->dsOrdhead;
        $dbePorline = new DBEPorline($this);
        $dbeItem = &$this->dbeItem;
        $dbeCustomerItem = null;
        // Must process each item in dataset and update received qtys and possibly create customer item
        $dsGoodsIn->initialise();
        while ($dsGoodsIn->fetchNext()) {
            if (
                ($dsGoodsIn->getValue(self::receiveDataSetQtyToReceive) <= 0) ||
                !$dsGoodsIn->getValue(self::receiveDataSetAllowReceive)
            ) {
                continue;
            }
            // if RequireSerialNo is TRUE then we know this is an item related to a sales order and
            // therefore must have a customer item created for it.
            if ($dsGoodsIn->getValue(self::receiveDataSetRequireSerialNo)) {
                if (!is_object($dbeCustomerItem)) {
                    $dbeCustomerItem = new DBECustomerItem($this);
                }
                $dbeCustomerItem->setValue(
                    DBECustomerItem::customerItemID,
                    0
                );
                $dbeCustomerItem->setValue(
                    DBECustomerItem::customerID,
                    $dsOrdhead->getValue(DBEOrdhead::customerID)
                );
                $dbeCustomerItem->setValue(
                    DBECustomerItem::siteNo,
                    $dsOrdhead->getValue(DBEOrdhead::delSiteNo)
                );
                $dbeCustomerItem->setValue(
                    DBECustomerItem::itemID,
                    $dsGoodsIn->getValue(self::receiveDataSetItemID)
                );
                $dbeCustomerItem->setValue(
                    DBECustomerItem::ordheadID,
                    $dsPorhead->getValue(DBEJPorhead::ordheadID)
                );
                $dbeCustomerItem->setValue(
                    DBECustomerItem::porheadID,
                    $porheadID
                );
                $dbeCustomerItem->setValue(
                    DBECustomerItem::sOrderDate,
                    $dsOrdhead->getValue(DBEOrdhead::date)
                );
                $dbeCustomerItem->setValue(
                    DBECustomerItem::despatchDate,
                    date('Y-m-d')
                );
                $dbeCustomerItem->setValue(
                    DBECustomerItem::curUnitSale,
                    null
                );    // redundant I think
                $dbeCustomerItem->setValue(
                    DBECustomerItem::curUnitCost,
                    null
                );    // redundant
                $stockcat = $dbeItem->getValue(DBEItem::stockcat);
                if (($stockcat == 'M') || ($stockcat == 'R')) {                        // cnc support contract
                    $dbeCustomerItem->setValue(
                        DBECustomerItem::expiryDate,
                        date(
                            'Y-m-d',
                            strtotime('+ 1 year')
                        )
                    );
                } else if ($dsGoodsIn->getValue(self::receiveDataSetRenew) == TRUE) {
                    // bug 245: Add warranty years to current date to calculate expiry date.
                    $dbeWarranty = new DBEWarranty($this);
                    $dbeWarranty->getRow($dsGoodsIn->getValue(self::receiveDataSetWarrantyID));
                    $dbeCustomerItem->setValue(
                        DBECustomerItem::expiryDate,
                        date(
                            'Y-m-d',
                            strtotime('+ ' . $dbeWarranty->getValue(DBEWarranty::years) . ' year')
                        )
                    );
                }
                $dbeCustomerItem->setValue(
                    DBECustomerItem::warrantyID,
                    $dsGoodsIn->getValue(self::receiveDataSetWarrantyID)
                );
                $dbeCustomerItem->setValue(
                    DBECustomerItem::serialNo,
                    $dsGoodsIn->getValue(self::receiveDataSetSerialNo)
                );
                $dbeCustomerItem->insertRow();
            }
            // update received qty on porline
            $dbePorline->setValue(
                DBEJPorline::porheadID,
                $porheadID
            );
            $dbePorline->setValue(
                DBEJPorline::sequenceNo,
                $dsGoodsIn->getValue(self::receiveDataSetOrderSequenceNo)
            );
            $dbePorline->getRow();
            $newQtyReceived = $dbePorline->getValue(DBEJPorline::qtyReceived) + $dsGoodsIn->getValue(
                    self::receiveDataSetQtyToReceive
                );
            $dbePorline->setValue(
                DBEPorline::qtyReceived,
                $newQtyReceived
            );
            if ($newQtyReceived == $dbePorline->getValue(
                    DBEJPorline::qtyOrdered
                )) {    // if line fully received then set to expected date blank
                $dbePorline->setValue(
                    DBEJPorline::expectedTBC,
                    false
                );
                if (!$dbePorline->getValue(DBEPorline::expectedDate)) {
                    $dbePorline->setValue(DBEPorline::expectedDate, (new DateTime())->format(DATE_MYSQL_DATE));
                }
            }

            $dbePorline->updateRow();
            // update status on purchase order header
            $this->dbePorhead->getRow($porheadID);
            $dbePorline->setValue(
                DBEJPorline::porheadID,
                $porheadID
            );

            $dbeJPorline = new DBEJPorline($this);
            $dbeJPorline->setValue(DBEJPorline::porheadID, $porheadID);

            if (($dbeJPorline->countOutstandingRows() == 0)) {
                if ($dsPorhead->getValue(DBEPorhead::completionNotifiedFlag) != 'Y') {
                    $this->buSalesOrder->notifyPurchaseOrderCompletion($this->dbePorhead,true);
                }
                $this->dbePorhead->setValue(
                    DBEJPorhead::type,
                    'C'
                );
            } else {
                $this->dbePorhead->setValue(
                    DBEJPorhead::type,
                    'P'
                );
            }
            $this->dbePorhead->updateRow();
            /*
            If the customer is an internal stock location then update the appropriate stock level
            */
            if ($dsOrdhead->getValue(DBEOrdhead::customerID) == CONFIG_SALES_STOCK_CUSTOMERID) {
                $dbeItem->getRow($dsGoodsIn->getValue(self::receiveDataSetItemID));
                $newValue = $dbeItem->getValue(DBEItem::salesStockQty) + $dsGoodsIn->getValue(
                        self::receiveDataSetQtyToReceive
                    );
                $dbeItem->updateSalesStockQty($newValue);
            } else if ($dsOrdhead->getValue(DBEOrdhead::customerID) == CONFIG_MAINT_STOCK_CUSTOMERID) {
                $dbeItem->getRow($dsGoodsIn->getValue(self::receiveDataSetItemID));
                $newValue = $dbeItem->getValue(DBEItem::maintStockQty) + $dsGoodsIn->getValue(
                        self::receiveDataSetQtyToReceive
                    );
                $dbeItem->updateMaintStockQty($newValue);
            }
        }
    }
}
