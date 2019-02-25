<?php /**
 * Contract renewal business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
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
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeRenQuotation = new DBECustomerItem($this);
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

    function deleteRenQuotation($ID)
    {
        $this->setMethodName('deleteRenQuotation');
        if ($this->canDeleteRenQuotation($ID)) {
            return $this->dbeRenQuotation->deleteRow($ID);
        } else {
            return FALSE;
        }
    }

    function createNewRenewal(
        $customerID,
        $siteNo = 0,
        $itemID,
        &$customerItemID,
        $salePrice,
        $costPrice,
        $qty
    )
    {
        // create a customer item
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

        $dsCustomerItem->post();

        $buCustomerItem = new BUCustomerItem ($this);
        $buCustomerItem->update($dsCustomerItem);

        $customerItemID = $dsCustomerItem->getPKValue();

        $this->dbeRenQuotation->getRow($customerItemID);

        $this->dbeRenQuotation->setValue(
            'customerItemID',
            $customerItemID
        );
        $this->dbeRenQuotation->setValue(
            'renQuotationTypeID',
            $renQuotationTypeID
        );
        $this->dbeRenQuotation->setValue(
            'startDate',
            date('Y-m-d')
        );
        $this->dbeRenQuotation->setValue(
            'qty',
            $qty
        );
        $this->dbeRenQuotation->setValue(
            'salePrice',
            $salePrice
        );
        $this->dbeRenQuotation->setValue(
            'costPrice',
            $costPrice
        );
        $this->dbeRenQuotation->setValue(
            'grantNumber',
            ''
        );

        $this->dbeRenQuotation->updateRow();

        return;
    }

    function emailRenewalsQuotationsDue($toEmail = CONFIG_SALES_MANAGER_EMAIL)
    {
        $this->dbeJRenQuotation->getRenewalsDueRows();

        $buMail = new BUMail($this);
        $senderEmail = CONFIG_SALES_EMAIL;

        $hdrs =
            array(
                'From'         => $senderEmail,
                'To'           => $toEmail,
                'Subject'      => 'Quotation Renewals Due Today',
                'Date'         => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );

        ob_start(); ?>
        <HTML>
        <BODY>
        <TABLE border="1">
            <tr>
                <td bgcolor="#999999">Customer</td>
                <td bgcolor="#999999">Service</td>
            </tr>
            <?php while ($this->dbeJRenQuotation->fetchNext()) { ?>
                <tr>
                    <td><?php echo $this->dbeJRenQuotation->getValue('customerName') ?></td>
                    <td><?php echo $this->dbeJRenQuotation->getValue('itemDescription') ?></td>
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

    function emailRecentlyGeneratedQuotes($toEmail = CONFIG_SALES_MANAGER_EMAIL)
    {
        $this->dbeJRenQuotation->getRecentQuotesRows();

        $buMail = new BUMail($this);
        $senderEmail = CONFIG_SALES_EMAIL;

        $hdrs =
            array(
                'From'         => $senderEmail,
                'To'           => $toEmail,
                'Subject'      => 'Quotation Renewals Generated in Past 2 Weeks',
                'Date'         => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );

        ob_start(); ?>
        <HTML>
        <BODY>
        <TABLE border="1">
            <tr>
                <td bgcolor="#999999">Customer</td>
                <td bgcolor="#999999">Service</td>
            </tr>
            <?php while ($this->dbeJRenQuotation->fetchNext()) { ?>
                <tr>
                    <td><?php echo $this->dbeJRenQuotation->getValue('customerName') ?></td>
                    <td><?php echo $this->dbeJRenQuotation->getValue('itemDescription') ?></td>
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

    function createRenewalsQuotations()
    {
        $buSalesOrder = new BUSalesOrder ($this);

        $dbeRenQuotationUpdate = new DBECustomerItem($this);

        $this->dbeJRenQuotation->getRenewalsDueRows();


        $dbeJCustomerItem = new DBEJCustomerItem ($this);

        $dbeOrdline = new DBEOrdline ($this);

        $dbeOrdhead = new DBEOrdhead($this);

        $dbeCustomer = new DBECustomer ($this);

        $previousCustomerID = 99999;
        $previousRenQuotationType = null;

        $custItemsSharingSalesOrder = [];
        $previousOrdHeadID = null;
        while ($this->dbeJRenQuotation->fetchNext()) {
            ?>
            quotation
            <div>
                contract number: <?= $dbeJCustomerItem->getValue(DBECustomerItem::customerItemID) ?>
            </div>
            <?php
            if ($dbeJCustomerItem->getRow($this->dbeJRenQuotation->getValue('customerItemID'))) {
                /*
                 * Group many renewals for same customer under one quote
                 */
                if ($previousCustomerID != $dbeJCustomerItem->getValue(
                        'customerID'
                    ) || $previousRenQuotationType != $dbeJCustomerItem->getValue(
                        DBEJCustomerItem::renQuotationTypeID
                    )) {

                    if ($previousCustomerID != $dbeJCustomerItem->getValue(
                            'customerID'
                        )) {
                        echo "<div>The customer has changed - previous was $previousCustomerID, new is " . $dbeJCustomerItem->getValue(
                                'customerID'
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
                    $dbeCustomer->getRow($dbeJCustomerItem->getValue('customerID'));
                    $this->getData(
                        $dbeCustomer,
                        $dsCustomer
                    );

                    $buSalesOrder->initialiseQuote(
                        $dsOrdhead,
                        $dsOrdline,
                        $dsCustomer
                    );

                    $line = -1;    // initialise sales order line seq

                    $quotationIntroduction = 'Please find detailed below a quote for your ' . $this->dbeJRenQuotation->getValue(
                            'type'
                        ) . ' renewal.';

                    $dbeOrdhead->getRow($dsOrdhead->getValue(DBEOrdhead::ordheadID));

                    echo '<div>The new order id is: ' . $dsOrdhead->getValue(DBEOrdhead::ordheadID) . "</div>";

                    $dbeOrdhead->setValue(
                        'quotationIntroduction',
                        $quotationIntroduction
                    );

                    $dbeOrdhead->setValue(
                        DBEOrdhead::quotationSubject,
                        ucwords(
                            $this->dbeJRenQuotation->getValue(
                                'type'
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
                if ($this->dbeJRenQuotation->getValue('comment')) {
                    $comment = $this->dbeJRenQuotation->getValue('comment');
                } else {
                    $comment = $this->dbeJRenQuotation->getValue('type') . ' Renewal';
                }

                $dbeOrdline->setValue(
                    'lineType',
                    'C'
                );
                $dbeOrdline->setValue(
                    'renewalCustomerItemID',
                    ''
                );
                $dbeOrdline->setValue(
                    'ordheadID',
                    $dsOrdhead->getValue('ordheadID')
                );
                $dbeOrdline->setValue(
                    'customerID',
                    $dsOrdhead->getValue('customerID')
                );
                $dbeOrdline->setValue(
                    'itemID',
                    0
                );
                $dbeOrdline->setValue(
                    'description',
                    $comment
                );
                $dbeOrdline->setValue(
                    'supplierID',
                    ''
                );
                $dbeOrdline->setValue(
                    'sequenceNo',
                    $line
                );
                $dbeOrdline->setValue(
                    'lineType',
                    'C'
                );
                $dbeOrdline->setValue(
                    'qtyOrdered',
                    0
                ); // default 1
                $dbeOrdline->setValue(
                    'qtyDespatched',
                    0
                );
                $dbeOrdline->setValue(
                    'qtyLastDespatched',
                    0
                );
                $dbeOrdline->setValue(
                    'curUnitSale',
                    0
                );
                $dbeOrdline->setValue(
                    'curUnitCost',
                    0
                );

                $dbeOrdline->insertRow();

                $line++;

                /*
                 * Get stock category from item table
                 */
                $buItem = new BUItem($this);
                $buItem->getItemByID(
                    $dbeJCustomerItem->getValue('itemID'),
                    $dsItem
                );
                $dbeOrdline->setValue(
                    'stockcat',
                    $dsItem->getValue('stockcat')
                );

                $dbeOrdline->setValue(
                    'renewalCustomerItemID',
                    $this->dbeJRenQuotation->getValue('customerItemID')
                );
                $dbeOrdline->setValue(
                    'ordheadID',
                    $dsOrdhead->getValue('ordheadID')
                );
                $dbeOrdline->setValue(
                    'customerID',
                    $dsOrdhead->getValue('customerID')
                );
                $dbeOrdline->setValue(
                    'itemID',
                    $dbeJCustomerItem->getValue('itemID')
                );
                $dbeOrdline->setValue(
                    'description',
                    $dbeJCustomerItem->getValue('itemDescription')
                );
                $dbeOrdline->setValue(
                    'supplierID',
                    CONFIG_SALES_STOCK_SUPPLIERID
                );
                $dbeOrdline->setValue(
                    'sequenceNo',
                    $line
                );
                $dbeOrdline->setValue(
                    'lineType',
                    'I'
                );
                $dbeOrdline->setValue(
                    'qtyOrdered',
                    $this->dbeJRenQuotation->getValue('qty')
                );
                $dbeOrdline->setValue(
                    'qtyDespatched',
                    0
                );
                $dbeOrdline->setValue(
                    'curUnitSale',
                    $this->dbeJRenQuotation->getValue('salePrice')
                );
                $dbeOrdline->setValue(
                    'curUnitCost',
                    $this->dbeJRenQuotation->getValue('costPrice')
                );

                $dbeOrdline->insertRow();
                /**
                 * add installation charge
                 */
                if ($this->dbeJRenQuotation->getValue('addInstallationCharge') == 'Y') {
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
                        'stockcat',
                        $dsItem->getValue('stockcat')
                    );

                    $dbeOrdline->setValue(
                        'renewalCustomerItemID',
                        0
                    );
                    $dbeOrdline->setValue(
                        'ordheadID',
                        $dsOrdhead->getValue('ordheadID')
                    );
                    $dbeOrdline->setValue(
                        'customerID',
                        $dsOrdhead->getValue('customerID')
                    );
                    $dbeOrdline->setValue(
                        'itemID',
                        CONFIG_INSTALLATION_ITEMID
                    );
                    $dbeOrdline->setValue(
                        'description',
                        'Installation/Configuration'
                    );
                    $dbeOrdline->setValue(
                        'supplierID',
                        CONFIG_SALES_STOCK_SUPPLIERID
                    );
                    $dbeOrdline->setValue(
                        'sequenceNo',
                        $line
                    );
                    $dbeOrdline->setValue(
                        'lineType',
                        'I'
                    );
                    $dbeOrdline->setValue(
                        'qtyOrdered',
                        1
                    );
                    $dbeOrdline->setValue(
                        'qtyDespatched',
                        0
                    );
                    $dbeOrdline->setValue(
                        'qtyLastDespatched',
                        0
                    );
                    $dbeOrdline->setValue(
                        'curUnitCost',
                        $dsItem->getValue('curUnitCost')
                    );
                    $dbeOrdline->setValue(
                        'curUnitCost',
                        $dsItem->getValue('curUnitSale')
                    );

                    $dbeOrdline->insertRow();
                }


                // period comment line
                $line++;
                $description =
                    $this->dbeJRenQuotation->getValue('grantNumber') . ' ' .
                    ' Expires: ' . $this->dbeJRenQuotation->getValue('nextPeriodStartDate');
                $dbeOrdline->setValue(
                    'lineType',
                    'C'
                );
                $dbeOrdline->setValue(
                    'renewalCustomerItemID',
                    ''
                );
                $dbeOrdline->setValue(
                    'ordheadID',
                    $dsOrdhead->getValue('ordheadID')
                );
                $dbeOrdline->setValue(
                    'customerID',
                    $dsOrdhead->getValue('customerID')
                );
                $dbeOrdline->setValue(
                    'itemID',
                    0
                );
                $dbeOrdline->setValue(
                    'description',
                    $description
                );
                $dbeOrdline->setValue(
                    'supplierID',
                    ''
                );
                $dbeOrdline->setValue(
                    'sequenceNo',
                    $line
                );
                $dbeOrdline->setValue(
                    'lineType',
                    'C'
                );
                $dbeOrdline->setValue(
                    'qtyOrdered',
                    0
                ); // default 1
                $dbeOrdline->setValue(
                    'qtyDespatched',
                    0
                );
                $dbeOrdline->setValue(
                    'qtyLastDespatched',
                    0
                );
                $dbeOrdline->setValue(
                    'curUnitSale',
                    0
                );
                $dbeOrdline->setValue(
                    'curUnitCost',
                    0
                );

                $dbeOrdline->insertRow();
                /*
                 * set generated date
                 */
                $dbeRenQuotationUpdate->setValue(
                    'customerItemID',
                    $this->dbeJRenQuotation->getPKValue()
                );
                $dbeRenQuotationUpdate->getRow();
                $dbeRenQuotationUpdate->setValue(
                    'dateGenerated',
                    date(CONFIG_MYSQL_DATE)
                );
                $dbeRenQuotationUpdate->updateRow();

                $previousCustomerID = $dbeJCustomerItem->getValue(DBEJCustomerItem::customerID);
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
            'customerItemID',
            $customerItemID
        );
        $this->dbeRenQuotation->getRowsByColumn('customerItemID');

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

        if
        (
            $this->dbeRenQuotation->getValue('startDate') != '0000-00-00' AND
            $this->dbeRenQuotation->getValue('startDate') != '' AND
            $this->dbeRenQuotation->getValue('qty') > 0 AND
            $this->dbeRenQuotation->getValue('salePrice') > 0 AND
            $this->dbeRenQuotation->getValue('costPrice') > 0
        ) {
            return true;
        } else {
            return FALSE;
        }

    }
} // End of class
?>