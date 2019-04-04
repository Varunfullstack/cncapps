<?php /**
 * Hosting renewal business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_bu"] . "/BUCustomerItem.inc.php");
require_once($cfg ["path_bu"] . "/BUSalesOrder.inc.php");
require_once($cfg ["path_bu"] . "/BUItem.inc.php");
require_once($cfg ["path_dbe"] . "/DBECustomerItem.inc.php");
require_once($cfg ["path_dbe"] . "/DBEOrdline.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJRenHosting.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");

class BURenHosting extends Business
{
    var $dbeRenHosting = "";
    private $dbeJRenHosting;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeRenHosting = new DBECustomerItem($this);
        $this->dbeJRenHosting = new DBEJRenHosting($this);
    }

    function updateRenHosting(&$dsData)
    {
        $this->setMethodName('updateRenHosting');
        $this->updateDataAccessObject(
            $dsData,
            $this->dbeRenHosting
        );

        return TRUE;
    }

    function getRenHostingByID($ID,
                               &$dsResults
    )
    {
        $this->dbeJRenHosting->setPKValue($ID);
        $this->dbeJRenHosting->getRow();
        return ($this->getData(
            $this->dbeJRenHosting,
            $dsResults
        ));
    }

    function getAll(&$dsResults,
                    $orderBy = false
    )
    {
        $this->dbeJRenHosting->getRows($orderBy);
        return ($this->getData(
            $this->dbeJRenHosting,
            $dsResults
        ));
    }

    function deleteRenHosting($ID)
    {
        $this->setMethodName('deleteRenHosting');
        if ($this->canDeleteRenHosting($ID)) {
            return $this->dbeRenHosting->deleteRow($ID);
        } else {
            return FALSE;
        }
    }

    /**
     *    canDeleteRenHosting
     * Only allowed if type has no activities
     */
    function canDeleteRenHosting($ID)
    {
        $dbeRenHosting = new DBERenHosting ($this);
        // validate no activities of this type
        $dbeRenHosting->setValue(
            'customerItemID',
            $ID
        );
        if ($dbeRenHosting->countRowsByColumn('customerItemID') < 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function createNewRenewal(
        $customerID,
        $siteNo = 0,
        $itemID,
        &$customerItemID
    )
    {
        // create a customer item
        // create a customer item
        $dbeItem = new DBEItem ($this);
        $dbeItem->getRow($itemID);

        $dbeCustomerItem = new DBECustomerItem ($this);

        $dsCustomerItem = new DataSet ($this);

        $dsCustomerItem->copyColumnsFrom($dbeCustomerItem);

        $dsCustomerItem->setUpdateModeInsert();

        $dsCustomerItem->setValue(
            'customerItemID',
            0
        );
        $dsCustomerItem->setValue(
            'customerID',
            $customerID
        );
        $dsCustomerItem->setValue(
            'itemID',
            $itemID
        );
        $dsCustomerItem->setValue(
            'siteNo',
            $siteNo
        );
        $dsCustomerItem->setValue(
            'curUnitCost',
            $dbeItem->getValue('curUnitCost')
        );
        $dsCustomerItem->setValue(
            'curUnitSale',
            $dbeItem->getValue('curUnitSale')
        );

        $dsCustomerItem->post();

        $buCustomerItem = new BUCustomerItem ($this);
        $buCustomerItem->update($dsCustomerItem);

        $customerItemID = $dsCustomerItem->getPKValue();

        return;

    }

    /**
     * @param string $toEmail
     */
    function emailRenewalsSalesOrdersDue($toEmail = CONFIG_SALES_MANAGER_EMAIL
    )
    {
        $this->dbeJRenHosting->getRenewalsDueRows();

        $buMail = new BUMail($this);
        $senderEmail = CONFIG_SALES_EMAIL;

        $hdrs =
            array(
                'From'         => $senderEmail,
                'To'           => $toEmail,
                'Subject'      => 'Hosting Renewals Due Today',
                'Date'         => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );

        ob_start(); ?>
        <HTML>
        <BODY>
        <TABLE border="1"
               bgcolor="#FFFFFF"
        >
            <tr bordercolor="#333333"
                bgcolor="#CCCCCC"
            >
                <td bordercolor="#000000">Customer</td>
                <td>Service</td>
            </tr>
            <?php while ($this->dbeJRenHosting->fetchNext()) { ?>
                <tr>
                    <td><?php echo $this->dbeJRenHosting->getValue('customerName') ?></td>
                    <td><?php echo $this->dbeJRenHosting->getValue('itemDescription') ?></td>
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
        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );

    }

    function createRenewalsSalesOrders()
    {
        $buSalesOrder = new BUSalesOrder ($this);

        $buInvoice = new BUInvoice ($this);

        $this->dbeJRenHosting->getRenewalsDueRows();

        $dbeJCustomerItem = new DBEJCustomerItem ($this);

        $dbeCustomer = new DBECustomer ($this);

        $dbeOrdline = new DBEOrdline ($this);

        $dsOrdhead = null;
        $dsOrdline = new DataSet($this);

        $previousCustomerID = 99999;
        $generateInvoice = false;
        $generatedOrder = false;
        while ($this->dbeJRenHosting->fetchNext()) {
            $generatedOrder = false;
                    ?>
                hosting
                <div>
                    contract number: <?= $dbeJCustomerItem->getValue(DBECustomerItem::customerItemID) ?>
                </div>
            <?php
            if ($dbeJCustomerItem->getRow($this->dbeJRenHosting->getValue(DBEJRenHosting::customerItemID))) {
                /*
                 * Group many contracts for same customer under one sales order
                 */
                if (
                    $previousCustomerID != $dbeJCustomerItem->getValue(DBEJCustomerItem::customerID) ||
                    (
                        !$generateInvoice &&
                        $this->dbeJRenHosting->getValue(DBECustomerItem::autoGenerateContractInvoice) === 'Y'
                    )
                ) {

                    /*
                   If generating invoices and an order has been started
                   */
                    if ($generateInvoice && $dsOrdhead) {

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
                    $generatedOrder = true;
                    $line = -1;  // initialise sales order line seq
                }
                $generateInvoice = $this->dbeJRenHosting->getValue(
                        DBECustomerItem::autoGenerateContractInvoice
                    ) === 'Y';
                /**
                 * add notes as a comment line (if they exist)
                 */
                if ($this->dbeJRenHosting->getValue(DBEJRenHosting::notes)) {

                    $line++;

                    $dbeOrdline->setValue(
                        DBEOrdline::description,
                        $this->dbeJRenHosting->getValue(DBEJRenHosting::notes)
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
                        0
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::supplierID,
                        null
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::sequenceNo,
                        $line
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

                } // end notes


                $line++;
                /*
                 * Get stock category from item table
                 */
                $buItem = new BUItem($this);
                $buItem->getItemByID(
                    $dbeJCustomerItem->getValue(DBEJCustomerItem::itemID),
                    $dsItem
                );
                $dbeOrdline->setValue(
                    DBEOrdline::stockcat,
                    $dsItem->getValue(DBEItem::stockcat)
                );

                $dbeOrdline->setValue(
                    DBEOrdline::renewalCustomerItemID,
                    $this->dbeJRenHosting->getValue(DBEJRenHosting::customerItemID)
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
                    DBEOrdline::sequenceNo,
                    $line
                );
                $dbeOrdline->setValue(
                    DBEOrdline::lineType,
                    'I'
                );
                $dbeOrdline->setValue(
                    DBEOrdline::qtyOrdered,
                    1
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
                    ($dbeJCustomerItem->getValue(DBEJCustomerItem::curUnitSale) / 12) * $this->dbeJRenHosting->getValue(
                        DBEJRenHosting::invoicePeriodMonths
                    )
                );
                $dbeOrdline->setValue(
                    DBEOrdline::curUnitCost,
                    ($dbeJCustomerItem->getValue(DBEJCustomerItem::curUnitCost) / 12) * $this->dbeJRenHosting->getValue(
                        DBEJRenHosting::invoicePeriodMonths
                    )
                );

                $dbeOrdline->insertRow();

                // period comment line
                $line++;
                $description = $this->dbeJRenHosting->getValue(
                        DBEJRenHosting::invoiceFromDate
                    ) . ' to ' . $this->dbeJRenHosting->getValue(DBEJRenHosting::invoiceToDate);
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
                    0
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
                    DBEOrdline::sequenceNo,
                    $line
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


                /*
                 * Update total months invoiced on renewal record
                 */
                $this->dbeRenHosting->getRow($this->dbeJRenHosting->getValue(DBEJRenHosting::customerItemID));
                $this->dbeRenHosting->setValue(
                    DBEJRenHosting::totalInvoiceMonths,
                    $this->dbeJRenHosting->getValue(DBEJRenHosting::totalInvoiceMonths) +
                    $this->dbeJRenHosting->getValue(DBEJRenHosting::invoicePeriodMonths)
                );

                $this->dbeRenHosting->setValue(
                    DBECustomerItem::transactionType,
                    '17'
                );

                $this->dbeRenHosting->updateRow();

                $previousCustomerID = $dbeJCustomerItem->getValue(DBEJCustomerItem::customerID);

            }

        }
        /*
         * Finalise last sales order and create an invoice
         */
        if ($generateInvoice && $generatedOrder) {
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
        }
    }

    function isCompleted($customerItemID)
    {
        $this->dbeRenHosting->getRow($customerItemID);

        $ret = false;

        if
        (
            $this->dbeRenHosting->getValue('installationDate') &&
            $this->dbeRenHosting->getValue('invoicePeriodMonths')
        ) {
            $ret = true;

        }

        return $ret;

    }

    function sendEmailTo($ID,
                         $emailAddress
    )
    {
        $dbeJRenHosting = new DBEJRenHosting($this);
        $dbeJRenHosting->setValue(
            'customerItemID',
            $ID
        );
        $dbeJRenHosting->getRow();

        $buMail = new BUMail($this);

        $toEmail = $emailAddress;
        $senderEmail = CONFIG_SALES_EMAIL;

        $hdrs =
            array(
                'From'         => $senderEmail,
                'To'           => $toEmail,
                'Subject'      => 'Hosting details',
                'Date'         => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );

        ob_start(); ?>

        <HTML>
        <BODY>
        <TABLE>
            <tr>
                <td>Customer</td>
                <td><?php echo $dbeJRenHosting->getValue('customerName') ?></td>
            </tr>
            <tr>
                <td>Service</td>
                <td><?php echo $dbeJRenHosting->getValue('itemDescription') ?></td>
            </tr>
            <tr>
                <td>ispID</td>
                <td><?php echo $dbeJRenHosting->getValue('ispID') ?></td>
            </tr>

            <tr>
                <td>ADSL Phone</td>
                <td><?php echo $dbeJRenHosting->getValue('adslPhone') ?></td>
            </tr>
            <tr>
                <td>MAC Code</td>
                <td><?php echo $dbeJRenHosting->getValue('macCode') ?></td>
            </tr>
            <tr>
                <td>Reference</td>
                <td><?php echo $dbeJRenHosting->getValue('reference') ?></td>
            </tr>
            <tr>
                <td>Default Gateway</td>
                <td><?php echo $dbeJRenHosting->getValue('defaultGateway') ?></td>
            </tr>
            <tr>
                <td>Network Address</td>
                <td><?php echo $dbeJRenHosting->getValue('networkAddress') ?></td>
            </tr>
            <tr>
                <td>Subnet Mask</td>
                <td><?php echo $dbeJRenHosting->getValue('subnetMask') ?></td>
            </tr>
            <tr>
                <td valign="top">Router IP Address</td>
                <td><?php echo Controller::htmlDisplayText(
                        $dbeJRenHosting->getValue('routerIPAddress'),
                        1
                    ) ?></td>
            </tr>
            <tr>
                <td>User Name</td>
                <td><?php echo $dbeJRenHosting->getValue('hostingUserName') ?></td>
            </tr>
            <tr>
                <td>Password</td>
                <td><?php echo $dbeJRenHosting->getValue('password') ?></td>
            </tr>
            <tr>
                <td>eta Date</td>
                <td><?php echo $dbeJRenHosting->getValue('etaDate') ?></td>
            </tr>
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
        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );

    }

} // End of class
?>