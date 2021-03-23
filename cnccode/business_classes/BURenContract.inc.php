<?php /**
 * Contract renewal business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_bu"] . "/BUCustomerItem.inc.php");
require_once($cfg ["path_bu"] . "/BUActivity.inc.php");
require_once($cfg ["path_bu"] . "/BUSalesOrder.inc.php");
require_once($cfg ["path_dbe"] . "/DBECustomerItem.inc.php");
require_once($cfg ["path_dbe"] . "/DBEOrdline.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJRenContract.inc.php");
require_once($cfg ["path_dbe"] . "/DBEWarranty.inc.php");
require_once($cfg ["path_dbe"] . "/DBEProblem.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");

class BURenContract extends Business
{
    const etaDate                       = 'etaDate';
    const serviceRequestCustomerItemID  = 'serviceRequestCustomerItemID';
    const serviceRequestPriority        = 'serviceRequestPriority';
    const SERVICE_REQUEST_INTERNAL_NOTE = 'serviceRequestInternalNote';

    var $dbeRenContract  = "";
    var $dbeJRenContract = "";
    var $buCustomerItem  = "";

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeRenContract  = new DBECustomerItem($this);
        $this->dbeJRenContract = new DBEJRenContract ($this);
        $this->buCustomerItem  = new BUCustomerItem($this);
    }

    function updateRenContract(&$dsData)
    {
        $this->setMethodName('updateRenContract');
        $this->updateDataAccessObject(
            $dsData,
            $this->dbeRenContract
        );
        return TRUE;
    }

    function getRenContractByID($ID,
                                &$dsResults
    )
    {
        $this->dbeJRenContract->setPKValue($ID);
        $this->dbeJRenContract->getRow();
        return ($this->getData(
            $this->dbeJRenContract,
            $dsResults
        ));
    }

    function getAll(&$dsResults,
                    $orderBy = false
    )
    {
        $this->dbeJRenContract->getRows($orderBy);
        return ($this->getData(
            $this->dbeJRenContract,
            $dsResults
        ));
    }

    /**
     * @param $customerID
     * @param DataSet|DBEOrdline $orderLineDS
     * @param $customerItemID
     * @param int $siteNo
     */
    function createNewRenewal($customerID,
                              $orderLineDS,
                              &$customerItemID,
                              $siteNo = 0
    )
    {
        $itemID = $orderLineDS->getValue(DBEOrdline::itemID);
        // create a customer item
        $dbeItem = new DBEItem ($this);
        $dbeItem->getRow($itemID);
        $dbeCustomerItem = new DBECustomerItem ($this);
        $dsCustomerItem  = new DataSet ($this);
        $dsCustomerItem->copyColumnsFrom($dbeCustomerItem);
        $dsCustomerItem->setUpdateModeInsert();
        $dsCustomerItem->setValue(
            DBEJCustomerItem::customerItemID,
            0
        );
        $dsCustomerItem->setValue(
            DBEJCustomerItem::customerID,
            $customerID
        );
        $dsCustomerItem->setValue(
            DBEJCustomerItem::itemID,
            $itemID
        );
        $dsCustomerItem->setValue(
            DBEJCustomerItem::siteNo,
            $siteNo
        );
        $dsCustomerItem->setValue(
            DBEJCustomerItem::curUnitCost,
            $orderLineDS->getValue(DBEOrdline::curUnitCost) * $orderLineDS->getValue(DBEOrdline::qtyOrdered) * 12
        );
        $dsCustomerItem->setValue(
            DBEJCustomerItem::curUnitSale,
            $orderLineDS->getValue(DBEOrdline::curUnitSale) * $orderLineDS->getValue(DBEOrdline::qtyOrdered) * 12
        );
        $dsCustomerItem->setValue(
            DBEJCustomerItem::salePricePerMonth,
            $dbeItem->getValue(DBEItem::curUnitSale)
        );
        $dsCustomerItem->setValue(
            DBEJCustomerItem::costPricePerMonth,
            $dbeItem->getValue(DBEItem::curUnitCost)
        );
        $dsCustomerItem->setValue(DBEJCustomerItem::users, $orderLineDS->getValue(DBEOrdline::qtyOrdered));
        $dsCustomerItem->setValue(DBEJCustomerItem::invoicePeriodMonths, 1);
        $dsCustomerItem->post();
        $buCustomerItem = new BUCustomerItem ($this);
        $buCustomerItem->update($dsCustomerItem);
        $customerItemID = $dsCustomerItem->getPKValue();
        return;
    }

    /**
     * @param string $toEmail
     * @param null $itemBillingCategory
     */
    function emailRenewalsSalesOrdersDue($toEmail = CONFIG_SALES_MANAGER_EMAIL, $itemBillingCategory = null)
    {
        $this->dbeJRenContract->getRenewalsDueRows($itemBillingCategory);
        $buMail      = new BUMail($this);
        $senderEmail = CONFIG_SALES_EMAIL;
        $hdrs        = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => 'Contract Renewals Due Today',
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );
        ob_start(); ?>
        <HTML lang="en">
        <BODY>
        <!--suppress HtmlDeprecatedAttribute -->
        <TABLE border="1">
            <tr>
                <!--suppress HtmlDeprecatedAttribute -->
                <td bgcolor="#999999">Customer</td>
                <!--suppress HtmlDeprecatedAttribute -->
                <td bgcolor="#999999">Service</td>
            </tr>
            <?php while ($this->dbeJRenContract->fetchNext()) { ?>
                <tr>
                    <td><?php echo $this->dbeJRenContract->getValue(DBEJRenContract::customerName) ?></td>
                    <td><?php echo $this->dbeJRenContract->getValue(DBEJRenContract::itemDescription) ?></td>
                </tr>
            <?php } ?>
        </TABLE>
        </BODY>
        </HTML>
        <?php
        $message = ob_get_contents();
        ob_end_clean();
        $buMail->mime->setHTMLBody($message);
        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body        = $buMail->mime->get($mime_params);
        $hdrs        = $buMail->mime->headers($hdrs);
        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );

    }

    /**
     * @param null $itemBillingCategoryID
     * @throws Exception
     */
    function createRenewalsSalesOrders($itemBillingCategoryID = null)
    {
        $buSalesOrder = new BUSalesOrder ($this);
        $buInvoice    = new BUInvoice ($this);
        $buActivity   = new BUActivity ($this);
        $this->dbeJRenContract->getRenewalsDueRows($itemBillingCategoryID);
        $dsRenContract = new DSForm($this);
        $dsRenContract->replicate($this->dbeJRenContract);
        $dbeJCustomerItem = new DBEJCustomerItem ($this);
        $dbeCustomer      = new DBECustomer ($this);
        $dbeOrdline       = new DBEOrdline ($this);
        /** @var DataSet $dsOrdhead */
        $dsOrdhead          = null;
        $dsOrdline          = new DataSet($this);
        $previousCustomerID = 99999;
        $generateInvoice    = false;
        echo "<div> Contract Renewals - START </div>";
        while ($dsRenContract->fetchNext()) {
            ?>
            <div>
                contract number: <?= $dsRenContract->getValue(DBECustomerItem::customerItemID) ?>
            </div>
            <?php
            if ($dbeJCustomerItem->getRow($dsRenContract->getValue(DBECustomerItem::customerItemID))) {

                /*
                 * Group many contracts for same customer under one sales order
         * unless it is an SSL cert in which case it has it's own order
                 */
                $isSslCertificate = strpos(
                    $dbeJCustomerItem->getValue(DBEJCustomerItem::itemDescription),
                    'SSL'
                ) !== false ? true : false;
                if ($previousCustomerID != $dbeJCustomerItem->getValue(
                        DBEJCustomerItem::customerID
                    ) || $isSslCertificate || (!$generateInvoice && $dsRenContract->getValue(
                            DBECustomerItem::autoGenerateContractInvoice
                        ) === 'Y')) {
                    /*
                    If generating invoices and an order has been started
                    */
                    if ($generateInvoice && (bool)$dsOrdhead) {

                        $buSalesOrder->setStatusCompleted($dsOrdhead->getValue(DBEOrdhead::ordheadID));
                        $buSalesOrder->getOrderByOrdheadID(
                            $dsOrdhead->getValue(DBEOrdhead::ordheadID),
                            $dsOrdhead,
                            $dsOrdline
                        );
                        $buInvoice->createInvoiceFromOrder(
                            $dsOrdhead,
                            $dsOrdline
                        );
                        ?>
                        <div>Creating invoice for previous Sales Order: <?= $dsOrdhead->getValue(
                                DBEOrdhead::ordheadID
                            ) ?></div>
                        <?php
                    }
                    /*
                     *  create order header
                     */
                    $dbeCustomer->getRow($dbeJCustomerItem->getValue(DBEJCustomerItem::customerID));
                    $this->getData(
                        $dbeCustomer,
                        $dsCustomer
                    );
                    $buSalesOrder->initialiseOrder(
                        $dsOrdhead,
                        $dsOrdline,
                        $dsCustomer
                    );
                    ?>
                    <div>Creating new Sales Order: <?= $dsOrdhead->getValue(DBEOrdhead::ordheadID) ?></div>

                    <?php
                }
                $generateInvoice = $dsRenContract->getValue(DBECustomerItem::autoGenerateContractInvoice) === 'Y';
                if ($dsRenContract->getValue(DBECustomerItem::officialOrderNumber)) {
                    $custPORef = $dsRenContract->getValue(DBECustomerItem::officialOrderNumber);
                    if ($dsOrdhead->getValue(DBEOrdhead::custPORef)) {
                        $custPORef = $dsOrdhead->getValue(DBEOrdhead::custPORef) . '/' . $custPORef;
                    }
                    $ordHead = new DBEOrdhead($this);
                    $ordHead->getRow($dsOrdhead->getValue(DBEOrdhead::ordheadID));
                    $ordHead->setValue(
                        DBEOrdhead::custPORef,
                        $custPORef
                    );
                    $dsOrdhead->setValue(
                        DBEOrdhead::custPORef,
                        $custPORef
                    );
                    $ordHead->updateRow();
                }
                /**
                 * add notes as a comment line (if they exist)
                 */
                if ($dsRenContract->getValue(DBEJRenContract::notes) && !$itemBillingCategoryID) {
                    $dbeOrdline->setValue(
                        DBEOrdline::description,
                        $dsRenContract->getValue(DBEJRenContract::notes)
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::lineType,
                        'C'
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::isRecurring,
                        $dbeJCustomerItem->getValue(DBEJCustomerItem::reoccurring)
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::renewalCustomerItemID,
                        null
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::ordheadID,
                        $dsOrdhead->getValue(DBEOrdhead::ordheadID)
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::customerID,
                        $dsOrdhead->getValue(DBEOrdhead::customerID)
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::itemID,
                        null
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::supplierID,
                        null
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::lineType,
                        'C'
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::qtyOrdered,
                        0
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::qtyDespatched,
                        0
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::qtyLastDespatched,
                        0
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::curUnitSale,
                        0
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::curUnitCost,
                        0
                    );
                    $dbeOrdline->insertRow();

                } // end notes
                /*
                 * Get stock category from item table
                 */
                $buItem = new BUItem($this);
                $dsItem = new DataSet($this);
                $buItem->getItemByID(
                    $dbeJCustomerItem->getValue(DBEJCustomerItem::itemID),
                    $dsItem
                );
                $dbeOrdline->setValue(
                    DBEOrdline::isRecurring,
                    $dbeJCustomerItem->getValue(DBEJCustomerItem::reoccurring)
                );
                $dbeOrdline->setValue(
                    DBEOrdline::stockcat,
                    $dsItem->getValue(DBEItem::stockcat)
                );
                $dbeOrdline->setValue(
                    DBEOrdline::renewalCustomerItemID,
                    $dsRenContract->getValue(DBEJRenContract::customerItemID)
                );
                $dbeOrdline->setValue(
                    DBEOrdline::ordheadID,
                    $dsOrdhead->getValue(DBEJOrdhead::ordheadID)
                );
                $dbeOrdline->setValue(
                    DBEOrdline::customerID,
                    $dsOrdhead->getValue(DBEJOrdhead::customerID)
                );
                $dbeOrdline->setValue(
                    DBEOrdline::itemID,
                    $dbeJCustomerItem->getValue(DBEJCustomerItem::itemID)
                );
                $dbeOrdline->setValue(
                    DBEOrdline::description,
                    $dbeJCustomerItem->getValue(DBEJCustomerItem::itemDescription)
                );
                $dbeOrdline->setValue(
                    DBEOrdline::supplierID,
                    CONFIG_SALES_STOCK_SUPPLIERID
                );
                $dbeOrdline->setValue(
                    DBEOrdline::lineType,
                    'I'
                );
                $dbeOrdline->setValue(
                    DBEOrdline::qtyOrdered,
                    $dbeJCustomerItem->getValue(DBEJCustomerItem::users)
                );
                $dbeOrdline->setValue(
                    DBEOrdline::qtyDespatched,
                    0
                );
                $dbeOrdline->setValue(
                    DBEOrdline::qtyLastDespatched,
                    0
                );
                $dbeOrdline->setValue(
                    DBEOrdline::curUnitSale,
                    $dbeJCustomerItem->getValue(DBECustomerItem::salePricePerMonth) * $dbeJCustomerItem->getValue(
                        DBECustomerItem::invoicePeriodMonths
                    )
                );
                $dbeOrdline->setValue(
                    DBEOrdline::curUnitCost,
                    $dbeJCustomerItem->getValue(
                        DBECustomerItem::costPricePerMonth
                    ) * $dbeJCustomerItem->getValue(
                        DBECustomerItem::invoicePeriodMonths
                    )
                );
                $dbeOrdline->insertRow();
                // period comment line
                $description = $dsRenContract->getValue(
                        DBEJRenContract::invoiceFromDate
                    ) . ' to ' . $dsRenContract->getValue(
                        DBEJRenContract::invoiceToDate
                    );
                $dbeOrdline->setValue(
                    DBEOrdline::isRecurring,
                    $dbeJCustomerItem->getValue(DBEJCustomerItem::reoccurring)
                );
                $dbeOrdline->setValue(
                    DBEOrdline::lineType,
                    'C'
                );
                $dbeOrdline->setValue(
                    DBEOrdline::renewalCustomerItemID,
                    null
                );
                $dbeOrdline->setValue(
                    DBEOrdline::ordheadID,
                    $dsOrdhead->getValue(DBEJOrdhead::ordheadID)
                );
                $dbeOrdline->setValue(
                    DBEOrdline::customerID,
                    $dsOrdhead->getValue(DBEJOrdhead::customerID)
                );
                $dbeOrdline->setValue(
                    DBEOrdline::itemID,
                    null
                );
                $dbeOrdline->setValue(
                    DBEOrdline::description,
                    $description
                );
                $dbeOrdline->setValue(
                    DBEOrdline::supplierID,
                    null
                );
                $dbeOrdline->setValue(
                    DBEOrdline::lineType,
                    'C'
                );
                $dbeOrdline->setValue(
                    DBEOrdline::qtyOrdered,
                    0
                ); // default 1
                $dbeOrdline->setValue(
                    DBEOrdline::qtyDespatched,
                    0
                );
                $dbeOrdline->setValue(
                    DBEOrdline::qtyLastDespatched,
                    0
                );
                $dbeOrdline->setValue(
                    DBEOrdline::curUnitSale,
                    0
                );
                $dbeOrdline->setValue(
                    DBEOrdline::curUnitCost,
                    0
                );
                $dbeOrdline->insertRow();
                // SSL Installation charge
                if ($isSslCertificate) {

                    $this->addInstallationCharge($dbeOrdline, $dsOrdhead);
                    $this->addSSLCertificateComment($dbeOrdline, $dsOrdhead);
                    $dsInput = new DSForm($this);
                    $dsInput->addColumn(
                        self::etaDate,
                        DA_DATE,
                        DA_ALLOW_NULL
                    );
                    $dsInput->addColumn(
                        self::serviceRequestCustomerItemID,
                        DA_INTEGER,
                        DA_ALLOW_NULL
                    );
                    $dsInput->addColumn(
                        self::serviceRequestPriority,
                        DA_INTEGER,
                        DA_ALLOW_NULL
                    );
                    $dsInput->addColumn(
                        self::SERVICE_REQUEST_INTERNAL_NOTE,
                        DA_STRING,
                        DA_ALLOW_NULL
                    );
                    $dsInput->addColumn(
                        DBEOrdhead::serviceRequestTaskList,
                        DA_STRING,
                        DA_ALLOW_NULL
                    );
                    $dsInput->setValue(
                        self::etaDate,
                        date('Y-m-d')
                    );
                    $internalNotes      = $dsRenContract->getValue(DBEJRenContract::internalNotes);
                    $internalNotes      = nl2br($internalNotes);
                    $renContractId      = $dsRenContract->getValue(DBEJRenContract::customerItemID);
                    $serviceRequestText = '<p>' . $internalNotes . '</p>
                        <p>Please update SSL contract item internal notes with the servers that have the SSL installed 
                        onto: <a href="' . SITE_URL . '/RenContract.php?action=edit&ID=' . $renContractId . '">Contract</a></p> 
                        <p>Please check that the above SSL Certificate is still required before renewing</p>';
                    $dsInput->setValue(
                        self::SERVICE_REQUEST_INTERNAL_NOTE,
                        $serviceRequestText
                    );
                    $dsInput->setValue(
                        self::serviceRequestCustomerItemID,
                        null
                    );
                    $dsInput->setValue(
                        self::serviceRequestPriority,
                        5
                    );
                    $buActivity->createSalesServiceRequest(
                        $dsOrdhead->getValue(DBEOrdhead::ordheadID),
                        $dsInput,
                        false,
                        3,
                    );
                }
                $dsLinkedItems = new DataSet($this);
                /**
                 * add customer items linked to this contract as a comment lines
                 */
                $this->buCustomerItem->getCustomerItemsByContractID(
                    $dsRenContract->getValue(DBEJRenContract::customerItemID),
                    $dsLinkedItems
                );
                while ($dsLinkedItems->fetchNext()) {
                    $description = $dsLinkedItems->getValue(DBEJCustomerItem::itemDescription);
                    if ($dsLinkedItems->getValue(DBEJCustomerItem::serverName)) {
                        $description .= ' (' . $dsLinkedItems->getValue(DBEJCustomerItem::serverName) . ')';
                    }
                    if ($dsLinkedItems->getValue(DBEJCustomerItem::serialNo)) {
                        $description .= ' ' . $dsLinkedItems->getValue(DBEJCustomerItem::serialNo);
                    }
                    $dbeOrdline->setValue(
                        DBEOrdline::isRecurring,
                        $dbeJCustomerItem->getValue(DBEJCustomerItem::reoccurring)
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::description,
                        $description
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::lineType,
                        'C'
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::renewalCustomerItemID,
                        null
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::ordheadID,
                        $dsOrdhead->getValue(DBEOrdhead::ordheadID)
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::customerID,
                        $dsOrdhead->getValue(DBEOrdhead::customerID)
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::itemID,
                        null
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::supplierID,
                        null
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::lineType,
                        'C'
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::qtyOrdered,
                        0
                    ); // default 1
                    $dbeOrdline->setValue(
                        DBEOrdline::qtyDespatched,
                        0
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::qtyLastDespatched,
                        0
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::curUnitSale,
                        0
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::curUnitCost,
                        0
                    );
                    $dbeOrdline->insertRow();
                }// end while linked items
                /*
                 * Update total months invoiced on renewal record
                 */
                $this->dbeRenContract->getRow($dsRenContract->getValue(DBEJRenContract::customerItemID));
                $this->dbeRenContract->setValue(
                    DBEJRenContract::totalInvoiceMonths,
                    $dsRenContract->getValue(DBEJRenContract::totalInvoiceMonths) + $dsRenContract->getValue(
                        DBEJRenContract::invoicePeriodMonths
                    )
                );
                $this->dbeRenContract->setValue(
                    DBECustomerItem::transactionType,
                    '17'
                );
                $this->dbeRenContract->updateRow();
                $previousCustomerID = $dbeJCustomerItem->getValue(DBEJCustomerItem::customerID);
            }

        }
        /*
        Finish off last automatic invoice
        */
        if ($generateInvoice) {

            $buSalesOrder->setStatusCompleted($dsOrdhead->getValue(DBEOrdhead::ordheadID));
            $buSalesOrder->getOrderByOrdheadID(
                $dsOrdhead->getValue(DBEOrdhead::ordheadID),
                $dsOrdhead,
                $dsOrdline
            );
            $buInvoice->createInvoiceFromOrder(
                $dsOrdhead,
                $dsOrdline
            );
            ?>
            <div>Creating invoice for previous Sales Order: <?= $dsOrdhead->getValue(DBEOrdhead::ordheadID) ?></div>
            <?php
        }
        echo "<div> Contract Renewals - END </div>";
    }

    function isCompleted($customerItemID)
    {
        $this->dbeRenContract->getRow($customerItemID);
        if ($this->dbeRenContract->getValue('installationDate') && $this->dbeRenContract->getValue(
                'invoicePeriodMonths'
            )) {
            $ret = true;

        }
        return $ret;

    }

    /**
     * @param DBEOrdline $dbeOrdline
     * @param DataSet $dsOrdhead
     */
    private function addInstallationCharge(DBEOrdline $dbeOrdline, DataSet $dsOrdhead)
    {
        $dbeItem = new DBEItem($this);
        $dbeItem->getRow(CONFIG_CONSULTANCY_HOURLY_LABOUR_ITEMID);
        $description = 'Installation Charge';
        $dbeOrdline->setValue(
            DBEOrdline::lineType,
            'I'
        );
        $dbeOrdline->setValue(
            DBEOrdline::isRecurring,
            0
        );
        $dbeOrdline->setValue(
            DBEOrdline::ordheadID,
            $dsOrdhead->getValue(DBEOrdhead::ordheadID)
        );
        $dbeOrdline->setValue(
            DBEOrdline::customerID,
            $dsOrdhead->getValue(DBEOrdhead::customerID)
        );
        $dbeOrdline->setValue(
            DBEOrdline::itemID,
            $dbeItem->getValue(DBEItem::itemID)
        );
        $dbeOrdline->setValue(
            DBEOrdline::description,
            $description
        );
        $dbeOrdline->setValue(
            DBEOrdline::supplierID,
            CONFIG_SALES_STOCK_SUPPLIERID
        );
        $dbeOrdline->setValue(
            DBEOrdline::qtyOrdered,
            0.5
        );
        $dbeOrdline->setValue(
            DBEOrdline::qtyDespatched,
            0
        );
        $dbeOrdline->setValue(
            DBEOrdline::qtyLastDespatched,
            0
        );
        $dbeOrdline->setValue(
            DBEOrdline::curUnitSale,
            $dbeItem->getValue(DBEItem::curUnitSale)
        );
        $dbeOrdline->setValue(
            DBEOrdline::curUnitCost,
            0
        );
        $dbeOrdline->insertRow();
    }

    private function addSSLCertificateComment(DBEOrdline $dbeOrdline, DataSet $dsOrdhead)
    {
        $description = 'SSL Certificate Renewal';
        $dbeOrdline->setValue(
            DBEOrdline::lineType,
            'C'
        );
        $dbeOrdline->setValue(
            DBEOrdline::isRecurring,
            0
        );
        $dbeOrdline->setValue(
            DBEOrdline::ordheadID,
            $dsOrdhead->getValue(DBEOrdhead::ordheadID)
        );
        $dbeOrdline->setValue(
            DBEOrdline::customerID,
            $dsOrdhead->getValue(DBEOrdhead::customerID)
        );
        $dbeOrdline->setValue(
            DBEOrdline::description,
            $description
        );
        $dbeOrdline->setValue(
            DBEOrdline::supplierID,
            CONFIG_SALES_STOCK_SUPPLIERID
        );
        $dbeOrdline->setValue(
            DBEOrdline::qtyOrdered,
            0
        );
        $dbeOrdline->setValue(
            DBEOrdline::qtyDespatched,
            0
        );
        $dbeOrdline->setValue(
            DBEOrdline::qtyLastDespatched,
            0
        );
        $dbeOrdline->setValue(
            DBEOrdline::curUnitSale,
            0
        );
        $dbeOrdline->setValue(
            DBEOrdline::curUnitCost,
            0
        );
        $dbeOrdline->insertRow();
        $dbeOrdline->moveItemToTop();
    }
}
