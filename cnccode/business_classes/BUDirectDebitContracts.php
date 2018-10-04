<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 28/09/2018
 * Time: 14:47
 */

require_once($cfg["path_gc"] . "/Business.inc.php");

require_once($cfg["path_dbe"] . "/DBEDirectDebitContracts.php");
require_once($cfg['path_bu'] . '/BUMail.inc.php');
require_once($cfg['path_bu'] . '/BUSalesOrder.inc.php');

class BUDirectDebitContracts extends Business
{

    private $dbeDirectDebitContracts;

    public function __construct($owner)
    {
        parent::__construct($owner);

        $this->dbeDirectDebitContracts = new DBEDirectDebitContracts($this);
    }

    function emailRenewalsSalesOrdersDue($toEmail = CONFIG_SALES_MANAGER_EMAIL
    )
    {
        $this->dbeDirectDebitContracts->getRenewalsDueRows();

        $buMail = new BUMail($this);

        $senderEmail = CONFIG_SALES_EMAIL;

        $hdrs =
            array(
                'From'         => $senderEmail,
                'To'           => $toEmail,
                'Subject'      => 'Broadband Renewals Due Today',
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
            <?php while ($this->dbeDirectDebitContracts->fetchNext()) { ?>
                <tr>
                    <td><?php echo $this->dbeDirectDebitContracts->getValue(
                            DBEDirectDebitContracts::customerName
                        ) ?></td>
                    <td><?php echo $this->dbeDirectDebitContracts->getValue(
                            DBEDirectDebitContracts::itemDescription
                        ) ?></td>
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
        $buActivity = new BUActivity ($this);
        $this->dbeDirectDebitContracts->getRenewalsDueRows();

        $dbeJCustomerItem = new DBEJCustomerItem ($this);

        $dbeOrdline = new DBEOrdline ($this);

        $dbeCustomer = new DBECustomer ($this);

        $previousCustomerID = 99999;
        $dsOrdhead = null;
        $generatedOrder = false;
        while ($this->dbeDirectDebitContracts->fetchNext()) {
            ?>
            <div>
                <div>
                    contract number: <?= $this->dbeDirectDebitContracts->getValue(DBEDirectDebitContracts::customerItemID) ?>
                </div>
            <?php

            if ($dbeJCustomerItem->getRow(
                $this->dbeDirectDebitContracts->getValue(DBEDirectDebitContracts::customerItemID)
            )) {
                ?>
                 <div>
                    customer:<?= $dbeJCustomerItem->getValue('customerID')?>
                </div>
                <?php

                if (
                    $previousCustomerID != $dbeJCustomerItem->getValue('customerID')
                ) {

                     ?>
                 <div>
                    We have a new customer
                </div>
                <?php
                    /*
                     * Create an invoice from each sales order (unless this is the first iteration)
                     */
                    if ($dsOrdhead) {
                           ?>
                    <div>
                        We have a previous order, create an invoice from it!
                    </div>
                    <?php
                        /*
                         * Finalise previous sales order and create an invoice
                         */
                        $buSalesOrder->setStatusCompleted($dsOrdhead->getValue('ordheadID'));

                        $buSalesOrder->getOrderByOrdheadID(
                            $dsOrdhead->getValue('ordheadID'),
                            $dsOrdhead,
                            $dsOrdline
                        );
                        $buInvoice->createInvoiceFromOrder(
                            $dsOrdhead,
                            $dsOrdline
                        );
                    }

                    ?>
                        <div>
                        generate a new order!!!
                        </div>
                    <?php
                    /*
                     *  create new sales order header
                     */
                    $dbeCustomer->getRow($dbeJCustomerItem->getValue('customerID'));
                    $this->getData(
                        $dbeCustomer,
                        $dsCustomer
                    );
                    $buSalesOrder->initialiseOrder(
                        $dsOrdhead,
                        $dsOrdline,
                        $dsCustomer,
                        true,
                        $this->dbeDirectDebitContracts->getValue(DBEDirectDebitContracts::transactionType)
                    );
                    $generatedOrder = true;
                    $line = -1;    // initialise sales order line seq
                       ?>
                 <div>
                    We have a new order with id : <?= $dsOrdhead->getValue(DBEOrdhead::ordheadID) ?>
                </div>
                <?php
                }

                if ($dbeJCustomerItem->getValue(DBECustomerItem::officialOrderNumber)) {

                    $custPORef = $dbeJCustomerItem->getValue(DBECustomerItem::officialOrderNumber);
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

                $line++;


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

                switch ($dbeJCustomerItem->getValue(DBEDirectDebitContracts::renewalTypeID)) {
                    case CONFIG_HOSTING_RENEWAL_TYPE_ID:
                        $dbeOrdline->setValue(
                            'description',
                            $dbeJCustomerItem->getValue('notes')
                        );

                        $dbeOrdline->insertRow();
                        break;
                    case CONFIG_BROADBAND_RENEWAL_TYPE_ID:
                        /*
                *  Phone number comment line
                */
                        if ($this->dbeDirectDebitContracts->getValue(DBEDirectDebitContracts::adslPhone)) {
                            $description = $this->dbeDirectDebitContracts->getValue(
                                    DBEDirectDebitContracts::adslPhone
                                ) . '. ';
                            $dbeOrdline->setValue(
                                'description',
                                $description
                            );
                            $dbeOrdline->insertRow();
                        }
                        break;
                    case CONFIG_CONTRACT_RENEWAL_TYPE_ID:
                        break;
                    default:

                        $dbeOrdline->setValue(
                            'description',
                            $description
                        );
                }


                // item line
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
                    $dbeJCustomerItem->getValue(DBEJCustomerItem::customerItemID)
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
                    1
                ); // default 1

                switch ($dbeJCustomerItem->getValue(DBEDirectDebitContracts::renewalTypeID)) {
                    case CONFIG_CONTRACT_RENEWAL_TYPE_ID:
                    case CONFIG_HOSTING_RENEWAL_TYPE_ID:
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
                            ($dbeJCustomerItem->getValue(DBEDirectDebitContracts::curUnitSale) / 12) *
                            $this->dbeDirectDebitContracts->getValue(DBEDirectDebitContracts::invoicePeriodMonths)
                        );
                        $dbeOrdline->setValue(
                            'curUnitCost',
                            ($dbeJCustomerItem->getValue(DBEDirectDebitContracts::curUnitCost) / 12) *
                            $this->dbeDirectDebitContracts->getValue(
                                DBEDirectDebitContracts::invoicePeriodMonths
                            )
                        );
                        break;
                    case CONFIG_BROADBAND_RENEWAL_TYPE_ID:
                        $dbeOrdline->setValue(
                            'qtyDespatched',
                            1
                        );
                        $dbeOrdline->setValue(
                            'qtyLastDespatched',
                            1
                        );
                        $dbeOrdline->setValue(
                            'curUnitSale',
                            $this->dbeDirectDebitContracts->getValue(DBEDirectDebitContracts::salePricePerMonth) *
                            $this->dbeDirectDebitContracts->getValue(DBEDirectDebitContracts::invoicePeriodMonths)
                        );
                        $dbeOrdline->setValue(
                            'curUnitCost',
                            $this->dbeDirectDebitContracts->getValue(DBEDirectDebitContracts::costPricePerMonth) *
                            $this->dbeDirectDebitContracts->getValue(DBEDirectDebitContracts::invoicePeriodMonths)
                        );
                        break;

                }


                $dbeOrdline->insertRow();

                // period comment line
                $line++;
                $description = $this->dbeDirectDebitContracts->getValue(DBEDirectDebitContracts::invoiceFromDate) .
                    ' to ' .
                    $this->dbeDirectDebitContracts->getValue(DBEDirectDebitContracts::invoiceToDate);
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

                if ($dbeJCustomerItem->getValue(DBECustomerItem::renQuotationTypeID) == CONFIG_CONTRACT_RENEWAL_TYPE_ID) {
                    /**
                     * add customer items linked to this contract as a comment lines
                     */
                    $buCustomerItem = new BUCustomerItem($this);

                    $buCustomerItem->getCustomerItemsByContractID(
                        $dbeJCustomerItem->getValue(DBECustomerItem::customerItemID),
                        $dsLinkedItems
                    );
                    while ($dsLinkedItems->fetchNext()) {
                        $line++;

                        $description = $dsLinkedItems->getValue('itemDescription');
                        if ($dsLinkedItems->getValue('serverName')) {
                            $description .= ' (' . $dsLinkedItems->getValue('serverName') . ')';
                        }
                        if ($dsLinkedItems->getValue('serialNo')) {
                            $description .= ' ' . $dsLinkedItems->getValue('serialNo');
                        }

                        $dbeOrdline->setValue(
                            'description',
                            $description
                        );
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
                    }// end while linked items
                }

                /*
                 * Update total months invoiced on renewal record
                 */
                $dbeCustomerItem = new DBECustomerItem($this);
                $dbeCustomerItem->getRow(
                    $this->dbeDirectDebitContracts->getValue(DBEDirectDebitContracts::customerItemID)
                );
                $dbeCustomerItem->setValue(
                    DBECustomerItem::totalInvoiceMonths,
                    $this->dbeDirectDebitContracts->getValue(DBEDirectDebitContracts::totalInvoiceMonths) +
                    $this->dbeDirectDebitContracts->getValue(DBEDirectDebitContracts::invoicePeriodMonths)
                );

                $dbeCustomerItem->setValue(
                    DBECustomerItem::transactionType,
                    '17'
                );

                $dbeCustomerItem->updateRow();

                $previousCustomerID = $dbeJCustomerItem->getValue('customerID');
            }
        }
        /*
         * Finalise last sales order and create an invoice
         */
        if ($generatedOrder) {
            ?>
                        <div>
                        We have finished going through all the items and we have an order from which we have to generate an invoice
                        </div>
                    <?php
            $buSalesOrder->setStatusCompleted($dsOrdhead->getValue('ordheadID'));

            $buSalesOrder->getOrderByOrdheadID(
                $dsOrdhead->getValue('ordheadID'),
                $dsOrdhead,
                $dsOrdline
            );

            $invHeadId =$buInvoice->createInvoiceFromOrder(
                $dsOrdhead,
                $dsOrdline
            );
            ?>
                        <div>
                        Generated invoice with id <?= $invHeadId?>
                        </div>
                    <?php
        }
    }
}