<?php /**
 * Broadband renewal business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Data\DBEItem;

global $cfg;
require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_bu"] . "/BUCustomerItem.inc.php");
require_once($cfg ["path_bu"] . "/BUSalesOrder.inc.php");
require_once($cfg ["path_bu"] . "/BUItem.inc.php");
require_once($cfg ["path_dbe"] . "/DBECustomerItem.inc.php");
require_once($cfg ["path_dbe"] . "/DBEOrdline.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJRenBroadband.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");

class BURenBroadband extends Business
{
    var     $dbeRenBroadband = "";
    private $dbeJRenBroadband;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeRenBroadband  = new DBECustomerItem($this);
        $this->dbeJRenBroadband = new DBEJRenBroadband($this);
    }

    function updateRenBroadband(&$dsData)
    {
        $this->setMethodName('updateRenBroadband');
        $this->updateDataAccessObject(
            $dsData,
            $this->dbeRenBroadband
        );
        return TRUE;
    }

    function getRenBroadbandByID($ID,
                                 &$dsResults
    )
    {
        $this->dbeJRenBroadband->setPKValue($ID);
        $this->dbeJRenBroadband->getRow();
        return ($this->getData(
            $this->dbeJRenBroadband,
            $dsResults
        ));
    }

    function getLeasedLinesToExpire(DataSet $dsResults,
                                    $lowerBound = 59,
                                    $upperBound = 67
    )
    {
        $this->dbeJRenBroadband->getLeasedLinesToExpire(
            $lowerBound,
            $upperBound
        );
        return ($this->getData(
            $this->dbeJRenBroadband,
            $dsResults
        ));
    }

    function getAll(&$dsResults,
                    $orderBy = false
    )
    {
        $this->dbeJRenBroadband->getRows($orderBy);
        return ($this->getData(
            $this->dbeJRenBroadband,
            $dsResults
        ));
    }

    function deleteRenBroadband($ID)
    {
        $this->setMethodName('deleteRenBroadband');
        if ($this->canDeleteRenBroadband($ID)) {
            return $this->dbeRenBroadband->deleteRow($ID);
        } else {
            return FALSE;
        }
    }

    /**
     *    canDeleteRenBroadband
     * Only allowed if type has no activities
     * @param $ID
     * @return bool
     */
    function canDeleteRenBroadband($ID)
    {
        $dbeRenBroadband = new DBEJRenBroadband ($this);
        // validate no activities of this type
        $dbeRenBroadband->setValue(
            DBEJRenBroadband::customerItemID,
            $ID
        );
        if ($dbeRenBroadband->countRowsByColumn(DBEJRenBroadband::customerItemID) < 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function createNewRenewal($customerID,
                              $itemID,
                              &$customerItemID,
                              $siteNo = null
    )
    {
        // create a customer item
        $dbeCustomerItem = new DBECustomerItem ($this);
        $dsCustomerItem  = new DataSet ($this);
        $dsCustomerItem->copyColumnsFrom($dbeCustomerItem);
        $dsCustomerItem->setUpdateModeInsert();
        $dsCustomerItem->setValue(
            DBEJCustomerItem::customerItemID,
            null
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
        $dsCustomerItem->post();
        $buCustomerItem = new BUCustomerItem ($this);
        $buCustomerItem->update($dsCustomerItem);
        $customerItemID = $dsCustomerItem->getPKValue();
        return;

    }

    function emailRenewalsSalesOrdersDue($toEmail = CONFIG_SALES_MANAGER_EMAIL)
    {
        $this->dbeJRenBroadband->getRenewalsDueRows();
        $buMail      = new BUMail($this);
        $senderEmail = CONFIG_SALES_EMAIL;
        $hdrs        = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => 'Broadband Renewals Due Today',
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );
        ob_start(); ?>
        <HTML lang="en">
        <BODY>
        <!--suppress HtmlDeprecatedAttribute -->
        <TABLE bgcolor="#FFFFFF"
               border="1"
        >
            <!--suppress HtmlDeprecatedAttribute -->
            <tr bordercolor="#333333"
                bgcolor="#CCCCCC"
            >
                <td bordercolor="#000000">Customer</td>
                <td>Service</td>
            </tr>
            <?php while ($this->dbeJRenBroadband->fetchNext()) { ?>
                <tr>
                    <td><?php echo $this->dbeJRenBroadband->getValue(DBEJRenBroadband::customerName) ?></td>
                    <td><?php echo $this->dbeJRenBroadband->getValue(DBEJRenBroadband::itemDescription) ?></td>
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


    function createRenewalsSalesOrders()
    {
        $buSalesOrder = new BUSalesOrder ($this);
        $buInvoice    = new BUInvoice ($this);
        $this->dbeJRenBroadband->getRenewalsDueRows();
        $dbeJCustomerItem   = new DBEJCustomerItem ($this);
        $dbeOrdline         = new DBEOrdline ($this);
        $dbeCustomer        = new DBECustomer ($this);
        $previousCustomerID = 99999;
        /** @var DataSet $dsOrdhead */
        $dsOrdhead       = null;
        $generateInvoice = false;
        $generatedOrder  = false;
        $line            = 0;
        $description     = null;
        while ($this->dbeJRenBroadband->fetchNext()) {

            ?>
            broadband
            <div>

                contract number: <?= $dbeJCustomerItem->getValue(DBECustomerItem::customerItemID) ?>
            </div>
            <?php
            $generatedOrder = false;
            if ($dbeJCustomerItem->getRow($this->dbeJRenBroadband->getValue(DBEJRenBroadband::customerItemID))) {

                /*
                 * Group many renewals for same customer under one sales order
                 */
                if ($previousCustomerID != $dbeJCustomerItem->getValue(
                        DBEJCustomerItem::customerID
                    ) || (!$generateInvoice && $this->dbeJRenBroadband->getValue(
                            DBECustomerItem::autoGenerateContractInvoice
                        ) === 'Y')) {
                    /*
                     * Create an invoice from each sales order (unless this is the first iteration)
                     */
                    if ($generateInvoice && $dsOrdhead) {
                        /*
                         * Finalise previous sales order and create an invoice
                         */
                        $buSalesOrder->setStatusCompleted($dsOrdhead->getValue(DBEOrdhead::ordheadID));
                        $buSalesOrder->getOrderByOrdheadID(
                            $dsOrdhead->getValue(DBEOrdhead::ordheadID),
                            $dsOrdhead,
                            $dsOrdline
                        );
                        ?>
                        <div>
                            Generating a new invoice
                        </div>
                        <div>
                            Ord head direct debit is <?= $dsOrdhead->getValue(
                                DBEOrdhead::directDebitFlag
                            ) == "Y" ? 'true' : 'false' ?>
                        </div>
                        <?php
                        $buInvoice->createInvoiceFromOrder(
                            $dsOrdhead,
                            $dsOrdline
                        );
                    }
                    /*
                     *  create new sales order header
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
                    ?>
                    <div>
                        we just created a new order...ID: <?= $dsOrdhead->getValue(DBEOrdhead::ordheadID) ?>
                    </div>
                    <div>

                        Ord head direct debit is <?= $dsOrdhead->getValue(
                            DBEOrdhead::directDebitFlag
                        ) == "Y" ? 'true' : 'false' ?>
                    </div>
                    <?php
                    $line = -1;    // initialise sales order line seq
                }
                $generateInvoice = $this->dbeJRenBroadband->getValue(
                        DBECustomerItem::autoGenerateContractInvoice
                    ) === 'Y';
                $line++;
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
                    DBEOrdline::isRecurring,
                    $dbeJCustomerItem->getValue(DBEJCustomerItem::reoccurring)
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
                /*
                 *  Phone number comment line
                 */
                if ($this->dbeJRenBroadband->getValue(DBEJRenBroadband::adslPhone)) {
                    $description = $this->dbeJRenBroadband->getValue(DBEJRenBroadband::adslPhone) . '. ';
                    $dbeOrdline->setValue(
                        DBEOrdline::description,
                        $description
                    );
                    $dbeOrdline->insertRow();
                }
                // item line
                $line++;
                $dbeOrdline->setValue(
                    DBEOrdline::stockcat,
                    $dsItem->getValue(DBEItem::stockcat)
                );
                $dbeOrdline->setValue(
                    DBEOrdline::renewalCustomerItemID,
                    $this->dbeJRenBroadband->getValue(DBEJRenBroadband::customerItemID)
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
                    DBEOrdline::isRecurring,
                    $dbeJCustomerItem->getValue(DBEJCustomerItem::reoccurring)
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
                    1
                );
                $dbeOrdline->setValue(
                    DBEOrdline::curUnitSale,
                    $this->dbeJRenBroadband->getValue(
                        DBEJRenBroadband::salePricePerMonth
                    ) * $this->dbeJRenBroadband->getValue(
                        DBEJRenBroadband::invoicePeriodMonths
                    )
                );
                $dbeOrdline->setValue(
                    DBEOrdline::curUnitCost,
                    $this->dbeJRenBroadband->getValue(
                        DBEJRenBroadband::costPricePerMonth
                    ) * $this->dbeJRenBroadband->getValue(
                        DBEJRenBroadband::invoicePeriodMonths
                    )
                );
                $dbeOrdline->insertRow();
                // period comment line
                $line++;
                $description = $this->dbeJRenBroadband->getValue(
                        DBEJRenBroadband::invoiceFromDate
                    ) . ' to ' . $this->dbeJRenBroadband->getValue(DBEJRenBroadband::invoiceToDate);
                $dbeOrdline->setValue(
                    DBEOrdline::lineType,
                    'C'
                );
                $dbeOrdline->setValue(
                    DBEOrdline::renewalCustomerItemID,
                    null
                );
                $dbeOrdline->setValue(
                    DBEOrdline::isRecurring,
                    $dbeJCustomerItem->getValue(DBEJCustomerItem::reoccurring)
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
                $this->dbeRenBroadband->getRow($this->dbeJRenBroadband->getValue(DBEJRenBroadband::customerItemID));
                $this->dbeRenBroadband->setValue(
                    DBEJRenBroadband::totalInvoiceMonths,
                    $this->dbeJRenBroadband->getValue(
                        DBEJRenBroadband::totalInvoiceMonths
                    ) + $this->dbeJRenBroadband->getValue(DBEJRenBroadband::invoicePeriodMonths)
                );
                $this->dbeRenBroadband->updateRow();
                $previousCustomerID = $dbeJCustomerItem->getValue(DBEJCustomerItem::customerID);
            }
        }
        /*
         * Finalise last sales order and create an invoice
         */
        if ($generateInvoice && $generatedOrder) {
            ?>
            <div>
                Generating a new invoice
            </div>
            <div>
                Ord head direct debit is <?= $dsOrdhead->getValue(
                    DBEOrdhead::directDebitFlag
                ) == "Y" ? 'true' : 'false' ?>
            </div>
            <?php
            $buSalesOrder->setStatusCompleted($dsOrdhead->getValue(DBEJOrdhead::ordheadID));
            $buSalesOrder->getOrderByOrdheadID(
                $dsOrdhead->getValue(DBEJOrdhead::ordheadID),
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
        $this->dbeRenBroadband->getRow($customerItemID);
        $ret = false;
        if ($this->dbeRenBroadband->getValue(DBEJRenBroadband::installationDate) && $this->dbeRenBroadband->getValue(
                DBEJRenBroadband::invoicePeriodMonths
            )) {
            $ret = true;

        }
        return $ret;

    }

    function sendEmailTo($ID,
                         $emailAddress
    )
    {
        $dbeJRenBroadband = new DBEJRenBroadband($this);
        $dbeJRenBroadband->setValue(
            DBEJRenBroadband::customerItemID,
            $ID
        );
        $dbeJRenBroadband->getRow();
        $buMail      = new BUMail($this);
        $toEmail     = $emailAddress;
        $senderEmail = CONFIG_SALES_EMAIL;
        $hdrs        = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => 'Broadband details',
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );
        ob_start(); ?>

        <HTML lang="en">
        <BODY>
        <TABLE>
            <tr>
                <td>Customer</td>
                <td><?php echo $dbeJRenBroadband->getValue(DBEJRenBroadband::customerName) ?></td>
            </tr>
            <tr>
                <td>Service</td>
                <td><?php echo $dbeJRenBroadband->getValue(DBEJRenBroadband::itemDescription) ?></td>
            </tr>
            <tr>
                <td>ispID</td>
                <td><?php echo $dbeJRenBroadband->getValue(DBEJRenBroadband::ispID) ?></td>
            </tr>

            <tr>
                <td>ADSL Phone</td>
                <td><?php echo $dbeJRenBroadband->getValue(DBEJRenBroadband::adslPhone) ?></td>
            </tr>
            <tr>
                <td>MAC Code</td>
                <td><?php echo $dbeJRenBroadband->getValue(DBEJRenBroadband::macCode) ?></td>
            </tr>
            <tr>
                <td>Reference</td>
                <td><?php echo $dbeJRenBroadband->getValue(DBEJRenBroadband::reference) ?></td>
            </tr>
            <tr>
                <td>Default Gateway</td>
                <td><?php echo $dbeJRenBroadband->getValue(DBEJRenBroadband::defaultGateway) ?></td>
            </tr>
            <tr>
                <td>Network Address</td>
                <td><?php echo $dbeJRenBroadband->getValue(DBEJRenBroadband::networkAddress) ?></td>
            </tr>
            <tr>
                <td>Subnet Mask</td>
                <td><?php echo $dbeJRenBroadband->getValue(DBEJRenBroadband::subnetMask) ?></td>
            </tr>
            <tr>
                <td style="vertical-align:top;">Router IP Address</td>
                <td><?php echo Controller::htmlDisplayText(
                        $dbeJRenBroadband->getValue(DBEJRenBroadband::routerIPAddress),
                        1
                    ) ?></td>
            </tr>
            <tr>
                <td>User Name</td>
                <td><?php echo $dbeJRenBroadband->getValue(DBEJRenBroadband::userName) ?></td>
            </tr>
            <tr>
                <td>Password</td>
                <td><?php echo $dbeJRenBroadband->getValue(DBEJRenBroadband::password) ?></td>
            </tr>
            <tr>
                <td>eta Date</td>
                <td><?php echo $dbeJRenBroadband->getValue(DBEJRenBroadband::etaDate) ?></td>
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
        $body        = $buMail->mime->get($mime_params);
        $hdrs        = $buMail->mime->headers($hdrs);
        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );

    }

    function resetContractExpireNotified()
    {
        global $db;
        $sql = "UPDATE custitem
                SET contractExpireNotified = 0 
                where  DATEDIFF(DATE_ADD(installationDate, INTERVAL initialContractLength MONTH), CURDATE()) >=90";
        $db->query($sql);
    }
} // End of class
?>