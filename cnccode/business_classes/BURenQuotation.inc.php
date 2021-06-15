<?php /**
 * Contract renewal business class
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
require_once($cfg ["path_dbe"] . "/DBEJRenQuotation.inc.php");
require_once($cfg ["path_dbe"] . "/DBERenQuotationType.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");

class BURenQuotation extends Business
{
    /** @var DBECustomerItem */
    public $dbeRenQuotation;
    /** @var  DBEJRenQuotation */
    public $dbeJRenQuotation;


    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeRenQuotation  = new DBECustomerItem($this);
        $this->dbeJRenQuotation = new DBEJRenQuotation ($this);
    }

    function updateRenQuotation(&$dsData)
    {
        $this->setMethodName('updateRenQuotation');
        $this->updateDataAccessObject(
            $dsData,
            $this->dbeRenQuotation
        );
        return TRUE;
    }

    function getRenQuotationByID($ID,
                                 &$dsResults
    )
    {
        $this->dbeJRenQuotation->setPKValue($ID);
        $this->dbeJRenQuotation->getRow();
        return ($this->getData(
            $this->dbeJRenQuotation,
            $dsResults
        ));
    }

    function getAll(&$dsResults,
                    $orderBy = false
    )
    {
        $this->dbeJRenQuotation->getRows($orderBy);
        return ($this->getData(
            $this->dbeJRenQuotation,
            $dsResults
        ));
    }

    function createNewRenewal($customerID,
                              $itemID,
                              &$customerItemID,
                              $salePrice,
                              $costPrice,
                              $qty,
                              $siteNo = 0
    )
    {
        // create a customer item
        $dbeCustomerItem = new DBECustomerItem ($this);
        $dsCustomerItem  = new DataSet ($this);
        $dsCustomerItem->copyColumnsFrom($dbeCustomerItem);
        $dsCustomerItem->setUpdateModeInsert();
        $dsCustomerItem->setValue(
            DBECustomerItem::customerItemID,
            null
        );
        $dsCustomerItem->setValue(
            DBECustomerItem::customerID,
            $customerID
        );
        $dsCustomerItem->setValue(
            DBECustomerItem::itemID,
            $itemID
        );
        $dsCustomerItem->setValue(
            DBECustomerItem::siteNo,
            $siteNo
        );
        $dsCustomerItem->post();
        $buCustomerItem = new BUCustomerItem ($this);
        $buCustomerItem->update($dsCustomerItem);
        $customerItemID = $dsCustomerItem->getPKValue();
        $this->dbeRenQuotation->getRow($customerItemID);
        $this->dbeRenQuotation->setValue(
            DBEJRenQuotation::customerItemID,
            $customerItemID
        );
        $this->dbeRenQuotation->setValue(
            DBEJRenQuotation::startDate,
            date('Y-m-d')
        );
        $this->dbeRenQuotation->setValue(
            DBEJRenQuotation::qty,
            $qty
        );
        $this->dbeRenQuotation->setValue(
            DBEJRenQuotation::salePrice,
            $salePrice
        );
        $this->dbeRenQuotation->setValue(
            DBEJRenQuotation::costPrice,
            $costPrice
        );
        $this->dbeRenQuotation->setValue(
            DBEJRenQuotation::grantNumber,
            null
        );
        $this->dbeRenQuotation->updateRow();
        return;
    }

    function emailRenewalsQuotationsDue($toEmail = CONFIG_SALES_MANAGER_EMAIL)
    {
        $this->dbeJRenQuotation->getRenewalsDueRows();
        $buMail      = new BUMail($this);
        $senderEmail = CONFIG_SALES_EMAIL;
        $hdrs        = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => 'Quotation Renewals Due Today',
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
            </tr>
            <?php while ($this->dbeJRenQuotation->fetchNext()) { ?>
                <tr>
                    <td><?php echo $this->dbeJRenQuotation->getValue(DBEJRenQuotation::customerName) ?></td>
                    <td><?php echo $this->dbeJRenQuotation->getValue(DBEJRenQuotation::itemDescription) ?></td>
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

    function emailRecentlyGeneratedQuotes($toEmail = CONFIG_SALES_MANAGER_EMAIL)
    {
        $this->dbeJRenQuotation->getRecentQuotesRows();
        $buMail      = new BUMail($this);
        $senderEmail = CONFIG_SALES_EMAIL;
        $hdrs        = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => 'Quotation Renewals Generated in Past 2 Weeks',
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
            </tr>
            <?php while ($this->dbeJRenQuotation->fetchNext()) { ?>
                <tr>
                    <td><?php echo $this->dbeJRenQuotation->getValue(DBEJRenQuotation::customerName) ?></td>
                    <td><?php echo $this->dbeJRenQuotation->getValue(DBEJRenQuotation::itemDescription) ?></td>
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

    function createRenewalsQuotations()
    {
        $buSalesOrder          = new BUSalesOrder ($this);
        $dbeRenQuotationUpdate = new DBECustomerItem($this);
        $this->dbeJRenQuotation->getRenewalsDueRows();
        $dbeJCustomerItem           = new DBEJCustomerItem ($this);
        $dbeOrdline                 = new DBEOrdline ($this);
        $dbeOrdhead                 = new DBEOrdhead($this);
        $dbeCustomer                = new DBECustomer ($this);
        $previousCustomerID         = 99999;
        $previousRenQuotationType   = null;
        $custItemsSharingSalesOrder = [];
        $previousOrdHeadID          = null;
        $line                       = 0;
        $dsOrdhead                  = new DataSet($this);
        while ($this->dbeJRenQuotation->fetchNext()) {
            ?>
            quotation
            <div>
                contract number: <?= $dbeJCustomerItem->getValue(DBECustomerItem::customerItemID) ?>
            </div>
            <?php
            if ($dbeJCustomerItem->getRow($this->dbeJRenQuotation->getValue(DBEJRenQuotation::customerItemID))) {
                /*
                 * Group many renewals for same customer under one quote
                 */
                if ($previousCustomerID != $dbeJCustomerItem->getValue(
                        DBEJCustomerItem::customerID
                    ) || $previousRenQuotationType != $dbeJCustomerItem->getValue(
                        DBEJCustomerItem::renQuotationTypeID
                    )) {

                    if ($previousCustomerID != $dbeJCustomerItem->getValue(
                            DBEJCustomerItem::customerID
                        )) {
                        echo "<div>The customer has changed - previous was $previousCustomerID, new is " . $dbeJCustomerItem->getValue(
                                DBEJCustomerItem::customerID
                            ) . "</div>";
                    } else {
                        echo "<div>The renQuotationType has changed - previous was $previousRenQuotationType, new is " . $dbeJCustomerItem->getValue(
                                DBEJCustomerItem::renQuotationTypeID
                            ) . "</div>";
                    }
                    echo "<div>Creating a new Sales Order</div>";
                    /*
                     *  create order header
                     */
                    $dbeCustomer->getRow($dbeJCustomerItem->getValue(DBEJCustomerItem::customerID));
                    $this->getData(
                        $dbeCustomer,
                        $dsCustomer
                    );
                    $dsOrdline = new DataSet($this);
                    $buSalesOrder->initialiseQuote(
                        $dsOrdhead,
                        $dsOrdline,
                        $dsCustomer
                    );
                    $line                  = -1;    // initialise sales order line seq
                    $quotationIntroduction = 'Please find detailed below a quote for your ' . $this->dbeJRenQuotation->getValue(
                            DBEJRenQuotation::type
                        ) . ' renewal.';
                    $dbeOrdhead->getRow($dsOrdhead->getValue(DBEOrdhead::ordheadID));
                    echo '<div>The new order id is: ' . $dsOrdhead->getValue(DBEOrdhead::ordheadID) . "</div>";
                    $dbeOrdhead->setValue(
                        DBEOrdhead::quotationIntroduction,
                        $quotationIntroduction
                    );
                    $dbeOrdhead->setValue(
                        DBEOrdhead::quotationSubject,
                        ucwords(
                            $this->dbeJRenQuotation->getValue(
                                DBEJRenQuotation::type
                            ) . ' renewal.'
                        )
                    );
                    $dbeOrdhead->updateRow();
                    if ($previousOrdHeadID) {
                        echo '<div>These are the item that are going to be sharing this sales order:</div>';
                        echo '<ul>';
                        foreach ($custItemsSharingSalesOrder as $custItemID) {
                            echo "<li>$custItemID</li>";
                            $dbeRenQuotationUpdate->setValue(
                                DBECustomerItem::customerItemID,
                                $custItemID
                            );
                            $dbeRenQuotationUpdate->getRow();
                            $dbeRenQuotationUpdate->setValue(
                                DBECustomerItem::ordheadID,
                                $previousOrdHeadID
                            );
                            $dbeRenQuotationUpdate->updateRow();
                        }
                        echo '</ul>';
                        $custItemsSharingSalesOrder = [];
                    }
                    $previousOrdHeadID = $dsOrdhead->getValue(DBEOrdhead::ordheadID);
                }
                $custItemsSharingSalesOrder[] = $this->dbeJRenQuotation->getValue(DBEJRenQuotation::customerItemID);
                $line++;
                // renewal type comment line
                if ($this->dbeJRenQuotation->getValue(DBEJRenQuotation::comment)) {
                    $comment = $this->dbeJRenQuotation->getValue(DBEJRenQuotation::comment);
                } else {
                    $comment = $this->dbeJRenQuotation->getValue(DBEJRenQuotation::type) . ' Renewal';
                }
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
                    $comment
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
                    DBEOrdline::renewalCustomerItemID,
                    $this->dbeJRenQuotation->getValue(DBEJRenQuotation::customerItemID)
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
                    $this->dbeJRenQuotation->getValue(DBEJRenQuotation::qty)
                );
                $dbeOrdline->setValue(
                    DBEOrdline::qtyDespatched,
                    0
                );
                $dbeOrdline->setValue(
                    DBEOrdline::curUnitSale,
                    $this->dbeJRenQuotation->getValue(DBEJRenQuotation::salePrice)
                );
                $dbeOrdline->setValue(
                    DBEOrdline::curUnitCost,
                    $this->dbeJRenQuotation->getValue(DBEJRenQuotation::costPrice)
                );
                $dbeOrdline->insertRow();
                /**
                 * add installation charge
                 */
                if ($this->dbeJRenQuotation->getValue(DBEJRenQuotation::addInstallationCharge) == 'Y') {
                    $line++;
                    /*
                     * Get stock category from item table
                     */
                    $buItem = new BUItem($this);
                    $buItem->getItemByID(
                        CONFIG_INSTALLATION_ITEMID,
                        $dsItem
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::stockcat,
                        $dsItem->getValue(DBEItem::stockcat)
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::renewalCustomerItemID,
                        0
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
                        CONFIG_INSTALLATION_ITEMID
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::description,
                        'Installation/Configuration'
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
                        DBEOrdline::curUnitCost,
                        $dsItem->getValue(DBEItem::curUnitCost)
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::curUnitCost,
                        $dsItem->getValue(DBEItem::curUnitSale)
                    );
                    $dbeOrdline->insertRow();
                }
                // period comment line
                $line++;
                $description = $this->dbeJRenQuotation->getValue(
                        DBEJRenQuotation::grantNumber
                    ) . ' ' . ' Expires: ' . $this->dbeJRenQuotation->getValue(DBEJRenQuotation::nextPeriodStartDate);
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
                /*
                 * set generated date
                 */
                $dbeRenQuotationUpdate->setValue(
                    DBECustomerItem::customerItemID,
                    $this->dbeJRenQuotation->getPKValue()
                );
                $dbeRenQuotationUpdate->getRow();
                $dbeRenQuotationUpdate->setValue(
                    DBECustomerItem::dateGenerated,
                    date(DATE_MYSQL_DATE)
                );
                $dbeRenQuotationUpdate->updateRow();
                $previousCustomerID       = $dbeJCustomerItem->getValue(DBEJCustomerItem::customerID);
                $previousRenQuotationType = $dbeJCustomerItem->getValue(DBEJCustomerItem::renQuotationTypeID);
            }
        }
        echo '<div>These are the item that are going to be sharing this sales order:</div>';
        echo '<ul>';
        foreach ($custItemsSharingSalesOrder as $custItemID) {
            echo "<li>$custItemID</li>";
            $dbeRenQuotationUpdate->setValue(
                DBECustomerItem::customerItemID,
                $custItemID
            );
            $dbeRenQuotationUpdate->getRow();
            $dbeRenQuotationUpdate->setValue(
                DBECustomerItem::ordheadID,
                $dsOrdhead->getValue(DBEOrdhead::ordheadID)
            );
            $dbeRenQuotationUpdate->updateRow();
        }
        echo '</ul>';
    }

    function processQuotationRenewal($customerItemID,
                                     $convertToOrder = false
    )
    {
        $this->dbeRenQuotation->setValue(
            DBEJRenQuotation::customerItemID,
            $customerItemID
        );
        $this->dbeRenQuotation->getRowsByColumn(DBEJRenQuotation::customerItemID);
        $dbRenQuotationUpdate = new DBECustomerItem($this);
        if ($this->dbeRenQuotation->fetchNext()) {
            $this->dbeRenQuotation->addYearToStartDate($this->dbeRenQuotation->getPKValue());
            if ($convertToOrder) {
                $dbRenQuotationUpdate->getRow($customerItemID);
                $dbRenQuotationUpdate->setValue(
                    DBECustomerItem::ordheadID,
                    null
                );
                $dbRenQuotationUpdate->setValue(
                    DBECustomerItem::customerItemNotes,
                    null
                );
                $dbRenQuotationUpdate->updateRow();
            }
        }

    }

    function isCompleted($customerItemID)
    {
        $this->dbeRenQuotation->getRow($customerItemID);
        if ($this->dbeRenQuotation->getValue(DBEJRenQuotation::startDate) and $this->dbeRenQuotation->getValue(
                DBEJRenQuotation::qty
            ) > 0 and $this->dbeRenQuotation->getValue(
                DBEJRenQuotation::salePrice
            ) > 0 and $this->dbeRenQuotation->getValue(DBEJRenQuotation::costPrice) > 0) {
            return true;
        } else {
            return FALSE;
        }

    }
} // End of class
?>