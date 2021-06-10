<?php /**
 * Domain renewal business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Data\DBEItem;

global $cfg;
require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_bu"] . "/BUCustomerItem.inc.php");
require_once($cfg ["path_bu"] . "/BUSalesOrder.inc.php");
require_once($cfg ["path_dbe"] . "/DBECustomerItem.inc.php");
require_once($cfg ["path_dbe"] . "/DBEOrdline.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJRenDomain.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");

class BURenDomain extends Business
{
    /** @var DBECustomerItem */
    public $dbeRenDomain;
    /** @var DBEJRenDomain */
    private $dbeJRenDomain;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeRenDomain  = new DBECustomerItem ($this);
        $this->dbeJRenDomain = new DBEJRenDomain ($this);
    }

    function updateRenDomain(&$dsData)
    {
        $this->setMethodName('updateRenDomain');
        $this->updateDataAccessObject(
            $dsData,
            $this->dbeRenDomain
        );
        return TRUE;
    }

    function getRenDomainByID($ID,
                              &$dsResults
    )
    {
        $this->dbeJRenDomain->setPKValue($ID);
        $this->dbeJRenDomain->getRow();
        return ($this->getData(
            $this->dbeJRenDomain,
            $dsResults
        ));
    }

    function getAll(&$dsResults,
                    $orderBy = false
    )
    {
        $this->dbeJRenDomain->getRows($orderBy);
        return ($this->getData(
            $this->dbeJRenDomain,
            $dsResults
        ));
    }

    function createNewRenewal($customerID,
                              $itemID,
                              &$customerItemID,
                              $siteNo = 0
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

        // create a renewal
    }

    function emailRenewalsSalesOrdersDue($toEmail = CONFIG_SALES_MANAGER_EMAIL)
    {
        $this->dbeJRenDomain->getRenewalsDueRows();
        $buMail      = new BUMail($this);
        $senderEmail = CONFIG_SALES_EMAIL;
        $hdrs        = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => 'Domain Renewals Due Today',
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );
        ob_start(); ?>
        <HTML lang="en">
        <BODY>
        <!--suppress HtmlDeprecatedAttribute -->
        <TABLE border="1">
            <tr>
                <td style="background-color: #999999">Customer</td>
                <td style="background-color: #999999">Service</td>
                <td style="background-color: #999999">Domain</td>
                <td style="background-color: #999999">Expires</td>
            </tr>
            <?php while ($this->dbeJRenDomain->fetchNext()) { ?>
                <tr>
                    <td><?php echo $this->dbeJRenDomain->getValue(DBEJRenDomain::customerName) ?></td>
                    <td><?php echo $this->dbeJRenDomain->getValue(DBEJRenDomain::itemDescription) ?></td>
                    <td><?php echo $this->dbeJRenDomain->getValue(DBEJRenDomain::notes) ?></td>
                    <td><?php echo $this->dbeJRenDomain->getValue(DBEJRenDomain::invoiceToDate) ?></td>
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
        $this->dbeJRenDomain->getRenewalsDueRows();
        $dbeRenDomainUpdate = new DBECustomerItem($this);
        $dbeJCustomerItem   = new DBEJCustomerItem ($this);
        $dbeCustomer        = new DBECustomer ($this);
        $dbeOrdline         = new DBEOrdline ($this);
        /** @var DataSet $dsOrdhead */
        $dsOrdhead          = null;
        $dsOrdline          = new DataSet($this);
        $line               = 0;
        $previousCustomerID = 99999;
        $generateInvoice    = false;
        $generatedOrder     = false;
        while ($this->dbeJRenDomain->fetchNext()) {
            $generatedOrder = false;
            ?>
            domain
            <div>
                contract number: <?= $dbeJCustomerItem->getValue(DBECustomerItem::customerItemID) ?>
            </div>
            <?php
            if ($dbeJCustomerItem->getRow($this->dbeJRenDomain->getValue(DBEJRenDomain::customerItemID))) {
                /*
                 * Group many domains for same customer under one sales order
                 */
                if ($previousCustomerID != $dbeJCustomerItem->getValue(
                        DBEJCustomerItem::customerID
                    ) || (!$generateInvoice && $this->dbeJRenDomain->getValue(
                            DBECustomerItem::autoGenerateContractInvoice
                        ) === 'Y')) {
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
                    $dsOrdhead = new DataSet($this);
                    $buSalesOrder->initialiseOrder(
                        $dsOrdhead,
                        $dsOrdline,
                        $dsCustomer
                    );
                    $generatedOrder = true;
                    $line           = -1;    // initialise sales order line seq
                }
                $generateInvoice = $this->dbeJRenDomain->getValue(DBECustomerItem::autoGenerateContractInvoice) === 'Y';
                // period comment line
                $line++;
                $description = $this->dbeJRenDomain->getValue(DBEJRenDomain::notes);
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
                    DBEOrdline::stockcat,
                    $dsItem->getValue(DBEItem::stockcat)
                );
                $dbeOrdline->setValue(
                    DBEOrdline::isRecurring,
                    $dbeJCustomerItem->getValue(DBEJCustomerItem::reoccurring)
                );
                $dbeOrdline->setValue(
                    DBEOrdline::renewalCustomerItemID,
                    $this->dbeJRenDomain->getValue(DBEJRenDomain::customerItemID)
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
                    ($dsItem->getValue(DBEItem::curUnitSale) / 12) * $this->dbeJRenDomain->getValue(
                        DBEJRenDomain::invoicePeriodMonths
                    )
                );
                $dbeOrdline->setValue(
                    DBEOrdline::curUnitCost,
                    ($dsItem->getValue(DBEItem::curUnitCost) / 12) * $this->dbeJRenDomain->getValue(
                        DBEJRenDomain::invoicePeriodMonths
                    )
                );
                $dbeOrdline->insertRow();
                // period comment line
                $line++;
                $description = $this->dbeJRenDomain->getValue(
                        DBEJRenDomain::invoiceFromDate
                    ) . ' to ' . $this->dbeJRenDomain->getValue(DBEJRenDomain::invoiceToDate);
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
                 * set generated date
                 */
                $dbeRenDomainUpdate->setValue(
                    DBEJCustomerItem::customerItemID,
                    $this->dbeJRenDomain->getPKValue()
                );
                $dbeRenDomainUpdate->getRow();
                $dbeRenDomainUpdate->setValue(
                    DBEJCustomerItem::totalInvoiceMonths,
                    $this->dbeJRenDomain->getValue(DBEJRenDomain::totalInvoiceMonths) + $this->dbeJRenDomain->getValue(
                        DBEJRenDomain::invoicePeriodMonths
                    )
                );
                $dbeRenDomainUpdate->updateRow();
                $previousCustomerID = $dbeJCustomerItem->getValue(DBEJCustomerItem::customerID);

            }

        }
        /*
       Finish off last automatic invoice
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
        $ID = $this->getRenewalIDByCustomerItemID($customerItemID);
        if ($ID) {
            $this->dbeRenDomain->getRow($ID);
        } else {
            $this->raiseError('Renewal row not found');
        }
        if (!$this->dbeRenDomain->getValue(DBEJRenDomain::installationDate) || !$this->dbeRenDomain->getValue(
                DBEJRenDomain::invoicePeriodMonths
            )) {
            return false;
        }
        return true;
    }

    function getRenewalIDByCustomerItemID($customerItemID)
    {

        $this->dbeRenDomain->setValue(
            DBEJRenDomain::customerItemID,
            $customerItemID
        );
        $this->dbeRenDomain->getRowsByColumn(DBEJRenDomain::customerItemID);
        $this->dbeRenDomain->fetchNext();
        return ($this->dbeRenDomain->getPKValue());

    }
} // End of class
?>