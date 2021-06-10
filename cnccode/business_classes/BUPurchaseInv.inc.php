<?php
global $cfg;

use CNCLTD\Data\DBEItem;

require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJPorhead.inc.php");
require_once($cfg ["path_dbe"] . "/DBEPorhead.inc.php");
require_once($cfg ["path_dbe"] . "/DBEPorline.inc.php");
require_once($cfg ["path_dbe"] . "/DBEPorlineTotals.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJPorline.inc.php");
require_once($cfg ['path_dbe'] . '/DBEPurchaseInv.inc.php');
require_once($cfg ['path_dbe'] . '/DBEPaymentTerms.inc.php');
require_once($cfg ["path_dbe"] . "/DBEStockcat.inc.php");
require_once($cfg ["path_dbe"] . "/DBECustomerItem.inc.php");
require_once($cfg ["path_bu"] . "/BUSalesOrder.inc.php");
require_once($cfg ["path_dbe"] . "/DBEWarranty.inc.php");
require_once($cfg ["path_bu"] . "/BUPurchaseOrder.inc.php");
require_once($cfg ["path_bu"] . "/BURenewal.inc.php");
require_once($cfg ["path_func"] . "/Common.inc.php");
require_once($cfg ['path_dbe'] . '/DBEPurchaseInv.inc.php');

class BUPurchaseInv extends Business
{
    const purchaseInvoiceDescription     = "description";
    const purchaseInvoiceSequenceNo      = "sequenceNo";
    const purchaseInvoiceOrderSequenceNo = "orderSequenceNo";
    const purchaseInvoiceQtyOrdered      = "qtyOrdered";
    const purchaseInvoiceQtyOS           = "qtyOS";
    const purchaseInvoiceCurPOUnitCost   = "curPOUnitCost";
    const purchaseInvoiceQtyToInvoice    = "qtyToInvoice";
    const purchaseInvoiceCurInvUnitCost  = "curInvUnitCost";
    const purchaseInvoiceCurInvTotalCost = "curInvTotalCost";
    const purchaseInvoiceCurVAT          = "curVAT";
    const purchaseInvoiceItemID          = "itemID";
    const purchaseInvoicePartNo          = "partNo";
    const purchaseInvoiceSerialNo        = "serialNo";
    const purchaseInvoiceRenew           = "renew";
    const purchaseInvoiceRequireSerialNo = "requireSerialNo";
    const purchaseInvoiceCustomerItemID  = "customerItemID";
    const purchaseInvoiceWarrantyID      = "warrantyID";
    /** @var DBEPorhead */
    public $dbePorhead;
    /** @var DBEPorline */
    public $dbePorline;
    /** @var DBEPurchaseInv */
    public $dbePurchaseInv;
    /** @var DBEItem */
    public $dbeItem;
    /** @var DBEStockcat */
    public $dbeStockcat;
    public $purchaseInvoiceNo;
    public $purchaseInvoiceDate;
    public $buPurchaseOrder;
    public $dbeCustomerItem;
    public $userID;
    /**
     * @var DBEJPorline
     */
    private $dbeJPorline;
    /**
     * @var DBEJPorhead
     */
    private $dbeJPorhead;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbePorhead  = new DBEPorhead ($this);
        $this->dbePorline  = new DBEPorline ($this);
        $this->dbeJPorline = new DBEJPorline ($this);
        $this->dbeJPorhead = new DBEJPorhead ($this);
    }

    /**
     * search for all rows other than authorised or supplier is an internal stock location
     * (Sales or Maint stock)
     * @param $supplierID
     * @param $porheadID
     * @param $supplierRef
     * @param $lineText
     * @param DataSet $dsResults
     * @return bool
     */
    function search($supplierID, $porheadID, $supplierRef, $lineText, &$dsResults)
    {
        $this->setMethodName('search');
        $dbeJPorhead = new DBEJPorhead ($this);
        if ($porheadID) { // get one row
            $dbeJPorhead->setValue(DBEJPorhead::porheadID, $porheadID);
            $dbeJPorhead->getPurchaseInvoiceRow();
            $ret = ($this->getData($dbeJPorhead, $dsResults));
        } else {                                                                        // use search criteria passed
            $ret = $dbeJPorhead->getRowsBySearchCriteria(
                trim($supplierID),
                null,
                null,
                trim($supplierRef),
                trim($lineText),
                null,
                null,
                null,
                'PI'
            );
            $dbeJPorhead->initialise();
            $dsResults = $dbeJPorhead;
        }
        return $ret;
    }

    /**
     * @param DataSet $dsPorhead
     * @param DataSet $dsPorline
     * @param DSForm $dsPurchaseInv
     * @param $addCustomerItems
     */
    function getInitialValues(&$dsPorhead, &$dsPorline, &$dsPurchaseInv, $addCustomerItems)
    {
        $this->initialiseDataset($dsPurchaseInv);
        $dbeItem = new DBEItem ($this);
        $dsPorline->initialise();
        $sequenceNo = 0;
        $dsPorline->initialise();
        while ($dsPorline->fetchNext()) {
            $itemID = $dsPorline->getValue(DBEJPorline::itemID);
            if ($dsPorhead->getValue(DBEJPorhead::directDeliveryFlag) == 'Y') {
                $qtyOS = $dsPorline->getValue(DBEJPorline::qtyOrdered) - $dsPorline->getValue(DBEJPorline::qtyReceived);
            } else {
                $qtyOS = $dsPorline->getValue(DBEJPorline::qtyReceived) - $dsPorline->getValue(
                        DBEJPorline::qtyInvoiced
                    );
            }
            // skip if nothing outstanding for this order line
            if ($qtyOS <= 0) {
                continue;
            }
            $dbeItem->getRow($itemID);
            /*
            if this item requires a serial number and
            this purchase order requires customer items adding
            and delivery method is direct (no goods inwards)
            then put each item on a separate line and prompt for s/n and warranty
            */
            if (($dbeItem->getValue(DBEItem::serialNoFlag) == 'Y') & ($dsPorhead->getValue(
                        DBEJPorhead::directDeliveryFlag
                    ) == 'Y') & $addCustomerItems) {                    // we split each item out onto a separate line
                for ($i = 1; $i <= $qtyOS; $i++) {
                    $sequenceNo++;
                    $dsPurchaseInv->setUpdateModeInsert();
                    $dsPurchaseInv->setValue(
                        self::purchaseInvoiceDescription,
                        $dsPorline->getValue(DBEJPorline::itemDescription)
                    );
                    $dsPurchaseInv->setValue(
                        self::purchaseInvoiceOrderSequenceNo,
                        $dsPorline->getValue(DBEJPorline::sequenceNo)
                    ); // the PO sequence no
                    $dsPurchaseInv->setValue(self::purchaseInvoiceSequenceNo, $sequenceNo); // the line sequence no
                    $dsPurchaseInv->setValue(self::purchaseInvoiceQtyOrdered, 1);
                    $dsPurchaseInv->setValue(self::purchaseInvoiceQtyOS, 1); // not invoiced
                    $dsPurchaseInv->setValue(
                        self::purchaseInvoiceCurPOUnitCost,
                        $dsPorline->getValue(DBEJPorline::curUnitCost)
                    ); // PO unit cost
                    $dsPurchaseInv->setValue(self::purchaseInvoiceQtyToInvoice, 0);
                    $dsPurchaseInv->setValue(
                        self::purchaseInvoiceCurInvUnitCost,
                        $dsPorline->getValue(DBEJPorline::curUnitCost)
                    ); // Invoice cost
                    $dsPurchaseInv->setValue(self::purchaseInvoiceCurInvTotalCost, 0); // Invoice cost
                    $dsPurchaseInv->setValue(self::purchaseInvoiceCurVAT, 0); // VAT
                    $dsPurchaseInv->setValue(self::purchaseInvoiceItemID, $itemID);
                    $dsPurchaseInv->setValue(self::purchaseInvoicePartNo, $dsPorline->getValue(DBEJPorline::partNo));
                    $dsPurchaseInv->setValue(self::purchaseInvoiceSerialNo, '');
                    $dsPurchaseInv->setValue(self::purchaseInvoiceRequireSerialNo, TRUE); // Prompt for SN and warranty
                    $dsPurchaseInv->setValue(self::purchaseInvoiceRenew, FALSE);
                    $dsPurchaseInv->setValue(self::purchaseInvoiceWarrantyID, $dbeItem->getValue(DBEItem::warrantyID));;
                    $dsPurchaseInv->post();
                }
            } else { // no serial no and warranty so lump all together on one line
                $sequenceNo++;
                $dsPurchaseInv->setUpdateModeInsert();
                $dsPurchaseInv->setValue(
                    self::purchaseInvoiceDescription,
                    $dsPorline->getValue(DBEJPorline::itemDescription)
                );
                $dsPurchaseInv->setValue(
                    self::purchaseInvoiceOrderSequenceNo,
                    $dsPorline->getValue(DBEJPorline::sequenceNo)
                ); // the PO sequence no
                $dsPurchaseInv->setValue(self::purchaseInvoiceSequenceNo, $sequenceNo); // the line sequence no
                $dsPurchaseInv->setValue(
                    self::purchaseInvoiceQtyOrdered,
                    $dsPorline->getValue(DBEJPorline::qtyOrdered)
                );
                $dsPurchaseInv->setValue(self::purchaseInvoiceQtyOS, $qtyOS); // not invoiced
                $dsPurchaseInv->setValue(
                    self::purchaseInvoiceCurPOUnitCost,
                    $dsPorline->getValue(DBEJPorline::curUnitCost)
                ); // PO unit cost
                $dsPurchaseInv->setValue(self::purchaseInvoiceQtyToInvoice, 0);
                $dsPurchaseInv->setValue(
                    self::purchaseInvoiceCurInvUnitCost,
                    $dsPorline->getValue(DBEJPorline::curUnitCost)
                ); // Invoice cost
                $dsPurchaseInv->setValue(self::purchaseInvoiceCurInvTotalCost, 0); // Invoice cost
                $dsPurchaseInv->setValue(self::purchaseInvoiceCurVAT, 0); // VAT
                $dsPurchaseInv->setValue(self::purchaseInvoiceItemID, $itemID);
                $dsPurchaseInv->setValue(self::purchaseInvoicePartNo, $dsPorline->getValue(DBEJPorline::partNo));
                $dsPurchaseInv->setValue(self::purchaseInvoiceSerialNo, '');
                $dsPurchaseInv->setValue(self::purchaseInvoiceRequireSerialNo, FALSE); // No SN or warranty
                $dsPurchaseInv->setValue(self::purchaseInvoiceRenew, FALSE);
                $dsPurchaseInv->setValue(self::purchaseInvoiceWarrantyID, '');
                $dsPurchaseInv->post();
            }
        }
    }

    function initialiseDataset(&$dsPurchaseInv)
    {
        $this->setMethodName('initialiseDataset');
        $dsPurchaseInv = new DataSet ($this);
        $dsPurchaseInv->addColumn(self::purchaseInvoiceDescription, DA_STRING, DA_ALLOW_NULL);
        $dsPurchaseInv->addColumn(self::purchaseInvoiceSequenceNo, DA_INTEGER, DA_ALLOW_NULL); // the line sequence no
        $dsPurchaseInv->addColumn(
            self::purchaseInvoiceOrderSequenceNo,
            DA_INTEGER,
            DA_ALLOW_NULL
        ); // the PO sequence no
        $dsPurchaseInv->addColumn(self::purchaseInvoiceQtyOrdered, DA_INTEGER, DA_ALLOW_NULL);
        $dsPurchaseInv->addColumn(self::purchaseInvoiceQtyOS, DA_INTEGER, DA_ALLOW_NULL); // not invoiced
        $dsPurchaseInv->addColumn(self::purchaseInvoiceCurPOUnitCost, DA_FLOAT, DA_ALLOW_NULL); // PO unit cost
        $dsPurchaseInv->addColumn(self::purchaseInvoiceCurPOUnitCost, DA_FLOAT, DA_ALLOW_NULL); // PO unit cost
        $dsPurchaseInv->addColumn(self::purchaseInvoiceQtyToInvoice, DA_INTEGER, DA_ALLOW_NULL);
        $dsPurchaseInv->addColumn(self::purchaseInvoiceCurInvUnitCost, DA_FLOAT, DA_ALLOW_NULL); // Invoice cost
        $dsPurchaseInv->addColumn(self::purchaseInvoiceCurInvTotalCost, DA_FLOAT, DA_ALLOW_NULL); // Invoice cost
        $dsPurchaseInv->addColumn(self::purchaseInvoiceCurVAT, DA_FLOAT, DA_ALLOW_NULL); // VAT amount
        $dsPurchaseInv->addColumn(self::purchaseInvoiceItemID, DA_ID, DA_ALLOW_NULL);
        $dsPurchaseInv->addColumn(self::purchaseInvoicePartNo, DA_INTEGER, DA_ALLOW_NULL);
        $dsPurchaseInv->addColumn(self::purchaseInvoiceSerialNo, DA_STRING, DA_ALLOW_NULL);
        $dsPurchaseInv->addColumn(self::purchaseInvoiceRenew, DA_INTEGER, DA_ALLOW_NULL);
        $dsPurchaseInv->addColumn(self::purchaseInvoiceRequireSerialNo, DA_INTEGER, DA_ALLOW_NULL);
        $dsPurchaseInv->addColumn(self::purchaseInvoiceCustomerItemID, DA_ID, DA_ALLOW_NULL);
        $dsPurchaseInv->addColumn(self::purchaseInvoiceWarrantyID, DA_INTEGER, DA_ALLOW_NULL);
    }

    /**
     * @param DataSet $dsPurchaseInv
     * @return bool
     */
    function validateQtys(&$dsPurchaseInv)
    {
        $this->setMethodName('validateQtys');
        $ret = TRUE;
        $dsPurchaseInv->initialise();
        while ($dsPurchaseInv->fetchNext()) {
            if ($dsPurchaseInv->getValue(self::purchaseInvoiceQtyOS) < $dsPurchaseInv->getValue(
                    self::purchaseInvoiceQtyToInvoice
                )) {
                $ret = FALSE;
                break;
            }
        }
        return $ret;
    }

    /**
     * @param DataSet $dsPurchaseInv
     * @return bool
     */
    function validatePrices(&$dsPurchaseInv)
    {
        $this->setMethodName('validatePrices');
        $ret = TRUE;
        $dsPurchaseInv->initialise();
        while ($dsPurchaseInv->fetchNext()) {
            if (($dsPurchaseInv->getValue(self::purchaseInvoiceCurInvUnitCost) > 99999) or ($dsPurchaseInv->getValue(
                        self::purchaseInvoiceCurInvUnitCost
                    ) < 0)) {
                $ret = FALSE;
                break;
            }
            if (($dsPurchaseInv->getValue(self::purchaseInvoiceCurVAT) > 99999) or ($dsPurchaseInv->getValue(
                        self::purchaseInvoiceCurVAT
                    ) < 0)) {
                $ret = FALSE;
                break;
            }
        }
        return $ret;
    }

    /**
     * @param DataSet $dsPurchaseInv
     * @return bool
     */
    function validateSerialNos(&$dsPurchaseInv)
    {
        $this->setMethodName('validateSerialNos');
        $ret = TRUE;
        $dsPurchaseInv->initialise();
        while ($dsPurchaseInv->fetchNext()) {
            if (($dsPurchaseInv->getValue(self::purchaseInvoiceQtyToInvoice) > 0) & ($dsPurchaseInv->getValue(
                        self::purchaseInvoiceSerialNo
                    ) == '') & ($dsPurchaseInv->getValue(self::purchaseInvoiceRequireSerialNo))) {
                $ret = FALSE;
                break;
            }
        }
        return $ret;
    }

    /**
     * @param DataSet $dsPurchaseInv
     * @return bool
     */
    function validateWarranties(&$dsPurchaseInv)
    {
        $this->setMethodName('validateWarranties');
        $ret = TRUE;
        $dsPurchaseInv->initialise();
        while ($dsPurchaseInv->fetchNext()) {
            if (($dsPurchaseInv->getValue(self::purchaseInvoiceQtyToInvoice) > 0) & ($dsPurchaseInv->getValue(
                        self::purchaseInvoiceWarrantyID
                    ) == '') & ($dsPurchaseInv->getValue(self::purchaseInvoiceRequireSerialNo))) {
                $ret = FALSE;
                break;
            }
        }
        return $ret;
    }

    /**
     * Validate that renewals have been created and minimum information has been entered
     * @param DataSet|DBEOrdline $dsOrdline
     * @return bool|string
     */
    function renewalsNotCompleted($dsOrdline)
    {
        $this->setMethodName('validateRenewals');
        $ret       = false;
        $dbeItem   = new DBEItem($this);
        $buRenewal = new BURenewal($this);
        while ($dsOrdline->fetchNext()) {
            if (!$dsOrdline->getValue(DBEJOrdline::itemID)) {
                continue;
            }
            $dbeItem->getRow($dsOrdline->getValue(DBEJOrdline::itemID));
            if ($dbeItem->getValue(DBEItem::renewalTypeID)) {

                if (!$dsOrdline->getValue(DBEJOrdline::renewalCustomerItemID)) {
                    return 'You have not created all of the renewals';
                } else {
                    $buRenewalObject = $buRenewal->getRenewalBusinessObject(
                        $dbeItem->getValue(DBEItem::renewalTypeID),
                        $page
                    );
                    if (!$buRenewalObject->isCompleted($dsOrdline->getValue(DBEJOrdline::renewalCustomerItemID))) {
                        $ret = 'You have not completed all of the renewal information required';
                    }
                } // end else
            } // end if is a renewal line
        }
        return $ret;
    }

    /**
     * @param $purchaseInvoiceNo
     * @param $porheadID
     * @return bool
     */
    function invoiceNoIsUnique($purchaseInvoiceNo, $porheadID)
    {
        $this->setMethodName('invoiceNoIsUnique');
        $this->buPurchaseOrder = new BUPurchaseOrder ($this);
        $dsPorhead             = new DataSet($this);
        $this->buPurchaseOrder->getHeaderByID(
            $porheadID,
            $dsPorhead
        );
        $dbePurchaseInv = new DBEPurchaseInv ($this);
        return $dbePurchaseInv->countRowsBySupplierInvNo(
                $dsPorhead->getValue(DBEJPorhead::supplierID),
                $purchaseInvoiceNo
            ) == 0;
    }

    /**
     * Update with purchase invoice info.
     * 1) Update stock levels on item
     * 2) Create customer items (where appropriate)
     *
     * Some of the customer item fields in the old system were being filled in depending upon the delivery
     * method selected (direct, hand, etc). Because we are creating customer items at goods in we
     * don't have some of the info to hand.
     *
     * @param $porheadID
     * @param $purchaseInvoiceNo
     * @param $purchaseInvoiceDate
     * @param DSForm $dsPurchaseInv
     * @param $userID
     */
    function update($porheadID, $purchaseInvoiceNo, $purchaseInvoiceDate, &$dsPurchaseInv, $userID)
    {
        $this->setMethodName('update');
        $this->userID              = $userID;
        $this->purchaseInvoiceNo   = $purchaseInvoiceNo;
        $this->purchaseInvoiceDate = $purchaseInvoiceDate;
        $this->buPurchaseOrder     = new BUPurchaseOrder ($this);
        $dsPorhead                 = new DataSet($this);
        $this->buPurchaseOrder->getHeaderByID($porheadID, $dsPorhead);
        $this->dbeItem        = new DBEItem ($this);
        $this->dbeStockcat    = new DBEStockcat ($this);
        $this->dbePurchaseInv = new DBEPurchaseInv ($this);
        $dsOrdhead            = new DataSet($this);
        $ordheadID            = $dsPorhead->getValue(DBEJPorhead::ordheadID);
        $buSalesOrder         = new BUSalesOrder ($this);
        if ($ordheadID) {
            $buSalesOrder->getOrderByOrdheadID($ordheadID, $dsOrdhead, $dsOrdline);
        }
        $this->dbePorline = new DBEPorline ($this);
        $this->dbePorhead = new DBEPorhead ($this);
        /*
         Loop through lines, and for each update the purchase order appropriately
        */
        $dsPurchaseInv->initialise();
        while ($dsPurchaseInv->fetchNext()) {
            if ($dsPurchaseInv->getValue(self::purchaseInvoiceQtyToInvoice) <= 0) {
                continue;
            }
            $this->postPurchaseInvoiceLine($dsPurchaseInv, $dsPorhead, $dsOrdhead);
            $this->updateOrderLineQtys(
                $porheadID,
                $dsPurchaseInv,
                $dsPorhead->getValue(DBEJPorhead::directDeliveryFlag)
            );
            // Unlike in the UNIX system, stock levels don't get updated BUT customer items need to be generated
            // if direct delivery and the requireSerialNo is TRUE
            if (($dsPorhead->getValue(DBEJPorhead::directDeliveryFlag) == 'Y') & ($dsPurchaseInv->getValue(
                        self::purchaseInvoiceRequireSerialNo
                    ) == TRUE)) {
                $this->createCustomerItem($dsPurchaseInv, $dsPorhead, $dsOrdhead);
            }
        } //dsPurchaseInv->fetchNext()
        $this->updatePOStatus($porheadID);
        // if all purchase orders for this sales order are now authorised and sales order status is initial...
        if (($this->dbePorhead->countNonAuthorisedRowsBySO($ordheadID) == 0) & ($dsOrdhead->getValue(
                    DBEJOrdhead::type
                ) == 'I')) {
            // if delivery is NOT an internal stock location
            if (!common_isAnInternalStockLocation($dsOrdhead->getValue(DBEJOrdhead::customerID))) {
                // if all purchase orders for this sales order are direct delivery then create invoices and set sales order status to completed
                if ($this->dbePorhead->countNonDirectRowsBySO($ordheadID) == 0) {
                    $dbePaymentTerms = new DBEPaymentTerms ($this);
                    $dbePaymentTerms->getRow($dsOrdhead->getValue(DBEJOrdhead::paymentTermsID));
                    if ($dbePaymentTerms->getValue(DBEPaymentTerms::automaticInvoiceFlag) == 'Y') {
                        $buInvoice = new BUInvoice ($this);
                        $buInvoice->createInvoiceFromOrder($dsOrdhead, $dsOrdline);
                        $buSalesOrder->setStatusCompleted($ordheadID);
                    }
                }
            } else {
                $buSalesOrder->setStatusCompleted(
                    $ordheadID
                ); // delivery to internal stock location so no invoices were created
            }
        }
    }

    /**
     * Update the purchase order line qty invoiced and qty received, if direct delivery
     * @param $porheadID
     * @param DSForm $dsPurchaseInv
     * @param $directDeliveryFlag
     */
    public function updateOrderLineQtys($porheadID, &$dsPurchaseInv, $directDeliveryFlag)
    {

        // update qtys on porline
        $this->dbePorline->setValue(DBEJPorline::porheadID, $porheadID);
        $this->dbePorline->setValue(
            DBEJPorline::sequenceNo,
            $dsPurchaseInv->getValue(self::purchaseInvoiceOrderSequenceNo)
        );
        $this->dbePorline->getRow();
        $this->dbePorline->setValue(
            DBEJPorline::qtyInvoiced,
            $this->dbePorline->getValue(DBEJPorline::qtyInvoiced) + $dsPurchaseInv->getValue(
                self::purchaseInvoiceQtyToInvoice
            )
        );
        // we update the received qty here for Direct Delivery because there is no goods in process to do it for us
        if ($directDeliveryFlag == 'Y') {
            $this->dbePorline->setValue(
                DBEJPorline::qtyReceived,
                $this->dbePorline->getValue(DBEJPorline::qtyReceived) + $dsPurchaseInv->getValue(
                    self::purchaseInvoiceQtyToInvoice
                )
            );
        }
        $this->dbePorline->updateRow();
    }

    function updatePOStatus($porheadID)
    {
        // update status on purchase order header
        $dbePorhead       = &$this->dbePorhead;
        $dbePorlineTotals = new DBEPorlineTotals ($this);
        $dbePorlineTotals->getRow($porheadID);
        $qtyOrd = $dbePorlineTotals->getValue(DBEPorlineTotals::qtyOrdered);
        $qtyRec = $dbePorlineTotals->getValue(DBEPorlineTotals::qtyReceived);
        $qtyInv = $dbePorlineTotals->getValue(DBEPorlineTotals::qtyInvoiced);
        $dbePorhead->getRow($porheadID);
        if ($qtyRec == 0) {
            $dbePorhead->setValue(DBEJPorhead::type, 'I');
        } elseif ($qtyOrd - $qtyRec > 0) {
            $dbePorhead->setValue(DBEJPorhead::type, 'P');
        } elseif ($qtyRec - $qtyInv == 0) {
            $dbePorhead->setValue(DBEJPorhead::type, 'A');
        } elseif ($qtyOrd - $qtyRec == 0) {
            $dbePorhead->setValue(DBEJPorhead::type, 'C');
        }
        if ($dbePorhead->getValue(DBEJPorhead::type) != 'I') { // no need to update if already Initial
            $dbePorhead->updateRow();
        }
    }

    /**
     * @param DataSet $dsPurchaseInv
     * @param DataSet $dsPorhead
     * @param DataSet $dsOrdhead
     */
    function postPurchaseInvoiceLine(&$dsPurchaseInv, &$dsPorhead, &$dsOrdhead)
    {
        $this->dbeItem->getRow($dsPurchaseInv->getValue(self::purchaseInvoiceItemID));
        $this->dbeStockcat->getRow($this->dbeItem->getValue(DBEItem::stockcat));
        /*
        Depending upon the sales order customerID, see whether the item is
        being purchased for an internal stock location or for a customer
        */
        switch ($dsOrdhead->getValue(DBEJOrdhead::customerID)) {
            case CONFIG_MAINT_STOCK_CUSTOMERID :
                $nominalCode = $this->dbeStockcat->getValue(DBEStockcat::purMaintStk);
                break;
            case CONFIG_SALES_STOCK_CUSTOMERID :
                $nominalCode = $this->dbeStockcat->getValue(DBEStockcat::purSalesStk);
                break;
            case CONFIG_ASSET_STOCK_CUSTOMERID :
                $nominalCode = $this->dbeStockcat->getValue(DBEStockcat::purAsset);
                break;
            case CONFIG_OPERATING_STOCK_CUSTOMERID :
                $nominalCode = $this->dbeStockcat->getValue(DBEStockcat::purOper);
                break;
            default :
                $nominalCode = $this->dbeStockcat->getValue(DBEStockcat::purCust); // purchased for a customer
                break;
        }
        $this->dbePurchaseInv->setValue(DBEPurchaseInv::type, 'PI');
        $this->dbePurchaseInv->setValue(DBEPurchaseInv::date, $this->purchaseInvoiceDate);
        $this->dbePurchaseInv->setValue(DBEPurchaseInv::ref, $this->purchaseInvoiceNo);
        $this->dbePurchaseInv->setValue(DBEPurchaseInv::accRef, $dsPorhead->getValue(DBEJPorhead::supplierID));
        $this->dbePurchaseInv->setValue(DBEPurchaseInv::nomRef, $nominalCode);
        $this->dbePurchaseInv->setValue(DBEPurchaseInv::dept, '0');
        $this->dbePurchaseInv->setValue(
            DBEPurchaseInv::details,
            'P' . str_pad(
                $dsPorhead->getValue(DBEJPorhead::porheadID),
                6,
                '0',
                STR_PAD_LEFT
            )
        );
        $netAmnt = $dsPurchaseInv->getValue(self::purchaseInvoiceQtyToInvoice) * $dsPurchaseInv->getValue(
                self::purchaseInvoiceCurInvUnitCost
            );
        $this->dbePurchaseInv->setValue(DBEPurchaseInv::netAmnt, $netAmnt);
        $this->dbePurchaseInv->setValue(DBEPurchaseInv::taxCode, $dsPorhead->getValue(DBEJPorhead::vatCode));
        $this->dbePurchaseInv->setValue(
            DBEPurchaseInv::taxAmnt,
            $dsPurchaseInv->getValue(self::purchaseInvoiceCurVAT)
        );
        $this->dbePurchaseInv->setValue(DBEPurchaseInv::printed, 'N');
        $this->dbePurchaseInv->insertRow();
    }

    /**
     * @param DataSet $dsPurchaseInv
     * @param DataSet $dsPorhead
     * @param DataSet $dsOrdhead
     */
    function createCustomerItem(&$dsPurchaseInv, &$dsPorhead, &$dsOrdhead)
    {
        $this->setMethodName('createCustomerItem');
        if (!is_object($this->dbeCustomerItem)) {
            $this->dbeCustomerItem = new DBECustomerItem ($this);
        }
        $dbeCustomerItem = &$this->dbeCustomerItem; // easy to use ref!
        $dbeCustomerItem->setValue(DBEJCustomerItem::customerItemID, 0);
        $dbeCustomerItem->setValue(DBEJCustomerItem::customerID, $dsOrdhead->getValue(DBEJOrdhead::customerID));
        $dbeCustomerItem->setValue(DBEJCustomerItem::siteNo, $dsOrdhead->getValue(DBEJOrdhead::delSiteNo));
        $dbeCustomerItem->setValue(DBEJCustomerItem::itemID, $dsPurchaseInv->getValue(self::purchaseInvoiceItemID));
        $dbeCustomerItem->setValue(DBEJCustomerItem::userID, $this->userID);
        $dbeCustomerItem->setValue(DBEJCustomerItem::despatchDate, date('Y-m-d'));
        $dbeCustomerItem->setValue(DBEJCustomerItem::ordheadID, $dsPorhead->getValue(DBEJPorhead::ordheadID));
        $dbeCustomerItem->setValue(DBEJCustomerItem::porheadID, $dsPorhead->getValue(DBEJPorhead::porheadID));
        $dbeCustomerItem->setValue(DBEJCustomerItem::sOrderDate, $dsOrdhead->getValue(DBEJOrdhead::date));
        $dbeCustomerItem->setValue(DBEJCustomerItem::curUnitSale, ''); // redundant I think
        $dbeCustomerItem->setValue(DBEJCustomerItem::curUnitCost, ''); // redundant
        $stockcat = $this->dbeItem->getValue(DBEItem::stockcat);
        if (($stockcat == 'M') or ($stockcat == 'R')) {
            $dbeCustomerItem->setValue(DBEJCustomerItem::expiryDate, date('Y-m-d', strtotime('+ 1 year')));
        } else if ($dsPurchaseInv->getValue(self::purchaseInvoiceRenew) == TRUE) {
            // bug 245: Add warranty years to current date to calculate expiry date.
            $dbeWarranty = new DBEWarranty ($this);
            $dbeWarranty->getRow($dsPurchaseInv->getValue(self::purchaseInvoiceWarrantyID));
            $dbeCustomerItem->setValue(
                DBEJCustomerItem::expiryDate,
                date('Y-m-d', strtotime('+ ' . $dbeWarranty->getValue(DBEWarranty::years) . ' year'))
            );
        }
        $dbeCustomerItem->setValue(
            DBEJCustomerItem::warrantyID,
            $dsPurchaseInv->getValue(self::purchaseInvoiceWarrantyID)
        );
        $dbeCustomerItem->setValue(DBEJCustomerItem::serialNo, $dsPurchaseInv->getValue(self::purchaseInvoiceSerialNo));
        $dbeCustomerItem->insertRow();
    }
}
