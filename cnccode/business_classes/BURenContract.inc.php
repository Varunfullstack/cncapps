<?php /**
 * Contract renewal business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
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
    var $dbeRenContract = "";
    var $dbeJRenContract = "";
    var $buCustomerItem = "";

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeRenContract = new DBECustomerItem($this);
        $this->dbeJRenContract = new DBEJRenContract ($this);
        $this->buCustomerItem = new BUCustomerItem($this);
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

    function deleteRenContract($ID)
    {
        $this->setMethodName('deleteRenContract');
        if ($this->canDeleteRenContract($ID)) {
            return $this->dbeRenContract->deleteRow($ID);
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
        $this->dbeJRenContract->getRenewalsDueRows();

        $buMail = new BUMail($this);
        $senderEmail = CONFIG_SALES_EMAIL;

        $hdrs =
            array(
                'From'         => $senderEmail,
                'To'           => $toEmail,
                'Subject'      => 'Contract Renewals Due Today',
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
            <?php while ($this->dbeJRenContract->fetchNext()) { ?>
                <tr>
                    <td><?php echo $this->dbeJRenContract->getValue('customerName') ?></td>
                    <td><?php echo $this->dbeJRenContract->getValue('itemDescription') ?></td>
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

        $this->dbeJRenContract->getRenewalsDueRows();

        $dsRenContract = new DSForm($this);
        $dsRenContract->replicate($this->dbeJRenContract);

        $dbeJCustomerItem = new DBEJCustomerItem ($this);

        $dbeCustomer = new DBECustomer ($this);

        $dbeOrdline = new DBEOrdline ($this);

        $dsOrdhead = null;
        $dsOrdline = new DataSet($this);

        $previousCustomerID = 99999;

        $generateInvoice = false;
        $generatedOrder = false;
        echo "<div> Contract Renewals - START </div>";
        while ($dsRenContract->fetchNext()) {
            $generatedOrder = false;
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
                if (strpos(
                        $dbeJCustomerItem->getValue(DBEJCustomerItem::itemDescription),
                        'SSL'
                    ) !== false) {
                    $isSslCertificate = true;
                } else {
                    $isSslCertificate = false;
                }

                if (
                    $previousCustomerID != $dbeJCustomerItem->getValue(DBEJCustomerItem::customerID) ||
                    $isSslCertificate ||
                    (
                        !$generateInvoice &&
                        $dsRenContract->getValue(DBECustomerItem::autoGenerateContractInvoice) === 'Y'
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
                    $generatedOrder = true;
                    $line = -1;    // initialise sales order line seq

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
                if ($dsRenContract->getValue(DBEJRenContract::notes)) {

                    $line++;

                    $dbeOrdline->setValue(
                        DBEOrdline::description,
                        $dsRenContract->getValue(DBEJRenContract::notes)
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
                    $dsItem->getValue('stockcat')
                );

                $dbeOrdline->setValue(
                    DBEOrdline::renewalCustomerItemID,
                    $dsRenContract->getValue('customerItemID')
                );
                $dbeOrdline->setValue(
                    DBEOrdline::ordheadID,
                    $dsOrdhead->getValue('ordheadID')
                );
                $dbeOrdline->setValue(
                    DBEOrdline::customerID,
                    $dsOrdhead->getValue('customerID')
                );
                $dbeOrdline->setValue(
                    DBEOrdline::itemID,
                    $dbeJCustomerItem->getValue('itemID')
                );
                $dbeOrdline->setValue(
                    DBEOrdline::description,
                    $dbeJCustomerItem->getValue('itemDescription')
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
                    ($dbeJCustomerItem->getValue(DBECustomerItem::curUnitSale) / 12) *
                    $dsRenContract->getValue(DBECustomerItem::invoicePeriodMonths)
                );
                $dbeOrdline->setValue(
                    DBEOrdline::curUnitCost,
                    ($dbeJCustomerItem->getValue(DBECustomerItem::curUnitCost) / 12) *
                    $dsRenContract->getValue(DBECustomerItem::invoicePeriodMonths)
                );

                $dbeOrdline->insertRow();

                // period comment line
                $line++;
                $description = $dsRenContract->getValue('invoiceFromDate') . ' to ' . $dsRenContract->getValue(
                        'invoiceToDate'
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
                    $dsOrdhead->getValue('ordheadID')
                );
                $dbeOrdline->setValue(
                    DBEOrdline::customerID,
                    $dsOrdhead->getValue('customerID')
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

                // SSL Installation charge
                if ($isSslCertificate) {
                    $line++;
                    $description = 'Installation Charge';
                    $dbeOrdline->setValue(
                        DBEOrdline::lineType,
                        'I'
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
                        CONFIG_CONSULTANCY_DAY_LABOUR_ITEMID
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
                        DBEOrdline::sequenceNo,
                        $line
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
                        35.00
                    );
                    $dbeOrdline->setValue(
                        DBEOrdline::curUnitCost,
                        0
                    );
                    $dbeOrdline->insertRow();


                    $dsInput = new DSForm($this);
                    $dsInput->addColumn(
                        'etaDate',
                        DA_DATE,
                        DA_ALLOW_NULL
                    );
                    $dsInput->addColumn(
                        'serviceRequestCustomerItemID',
                        DA_INTEGER,
                        DA_ALLOW_NULL
                    );
                    $dsInput->addColumn(
                        'serviceRequestPriority',
                        DA_INTEGER,
                        DA_ALLOW_NULL
                    );
                    $dsInput->addColumn(
                        'serviceRequestText',
                        DA_STRING,
                        DA_ALLOW_NULL
                    );

                    $dsInput->setValue(
                        'etaDate',
                        date('Y-m-d')
                    );

                    $internalNotes = $dsRenContract->getValue(DBEJRenContract::internalNotes);
                    $internalNotes = nl2br($internalNotes);

                    $renContractId = $dsRenContract->getValue(DBEJRenContract::customerItemID);

                    $serviceRequestText = <<<HEREDOC
                        <p>$internalNotes</p>
                        <p>Please update SSL contract item internal notes with the servers that have the SSL installed 
                        onto: <a href="http://cncapps/RenContract.php?action=edit&ID=$renContractId">Contract</a></p> 
                        <p>Please check that the above SSL Certificate is still required before renewing</p>
                        <p style="color: red">PLEASE RENEW FOR 2 YEARS</p>
HEREDOC;

                    $dsInput->setValue(
                        'serviceRequestText',
                        $serviceRequestText
                    );
                    $dsInput->setValue(
                        'serviceRequestCustomerItemID',
                        null
                    );
                    $dsInput->setValue(
                        'serviceRequestPriority',
                        5
                    );

                    $buActivity->createSalesServiceRequest(
                        $dsOrdhead->getValue(DBEOrdhead::ordheadID),
                        $dsInput
                    );


                }

                /**
                 * add customer items linked to this contract as a comment lines
                 */
                $this->buCustomerItem->getCustomerItemsByContractID(
                    $dsRenContract->getValue(DBEJRenContract::customerItemID),
                    $dsLinkedItems
                );
                while ($dsLinkedItems->fetchNext()) {
                    $line++;

                    $description = $dsLinkedItems->getValue(DBEJCustomerItem::itemDescription);
                    if ($dsLinkedItems->getValue(DBEJCustomerItem::serverName)) {
                        $description .= ' (' . $dsLinkedItems->getValue(DBEJCustomerItem::serverName) . ')';
                    }
                    if ($dsLinkedItems->getValue(DBEJCustomerItem::serialNo)) {
                        $description .= ' ' . $dsLinkedItems->getValue(DBEJCustomerItem::serialNo);
                    }

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
                }// end while linked items


                /*
                 * Update total months invoiced on renewal record
                 */

                $this->dbeRenContract->getRow($dsRenContract->getValue(DBEJRenContract::customerItemID));

                $this->dbeRenContract->setValue(
                    DBEJRenContract::totalInvoiceMonths,
                    $dsRenContract->getValue(DBEJRenContract::totalInvoiceMonths) +
                    $dsRenContract->getValue(DBEJRenContract::invoicePeriodMonths)
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

            ?>
            <div>Creating invoice for previous Sales Order: <?= $dsOrdhead->getValue(DBEOrdhead::ordheadID) ?></div>
            <?php
        }

        echo "<div> Contract Renewals - END </div>";
    }


    function generateServerCareActivities($dsContract)
    {
        $dbeWarranty = new DBEWarranty($this);

        /*
         * Create a problem thread
         */

        $dbeProblem = new DBEProblem ($this);
        $dbeProblem->setValue(
            'customerID',
            $dsContract->getValue('customerID')
        );
        $dbeProblem->insertRow();
        $problemID = $dbeProblem->getPKValue();
        /*
        *	Create a support renewal checklist activity
        */

        $template = new Template (
            $GLOBALS ["cfg"] ["path_templates"],
            "remove"
        );
        $template->set_file(
            array(
                'page' => 'SupportRenewalChecklist.inc.html'
            )
        );

        $template->set_var(
            array(
                'contractName' => $dsContract->getValue('itemDescription'),
                'expiryDate'   => $dsContract->getValue('expiryDate')
            )
        );

        $sql =
            "
	    SELECT
        server.cui_serial AS serialNo,
	      item.itm_desc AS serverType,
	      server.cui_cust_ref AS serverName,
	      DATE_ADD( server.cui_desp_date, INTERVAL cnt_years YEARS ) AS warrantyExpiryDate
	    FROM
	      cncp1.custitem AS SERVER
	      INNER JOIN cncp1.item 
	        ON (item.itm_itemno = server.cui_itemno)
	      JOIN contract AS warranty 
	        ON warranty.cnt_contno = itm_contno
	    WHERE
	      item.itm_itemtypeno =16
	      AND server.cui_cust_ref is not null
	      AND server.cui_contract_cuino = " . $dsContract->getValue(
                'customerItemID'
            ); // server name indicates this is a server

        $results = $this->db->query($sql);
//		$this->buCustomerItem->getServersByCustomerID( $dsContract->getValue( 'customerID'), $dsServer );

        $template->set_block(
            'SupportRenewalChecklist',
            'serverBlock',
            'servers'
        );

        while ($row = $results->fetch_object()) {

            echo $row->serverName . "<BR/>";

            $template->set_var(
                array(
                    'serverSerialNo'    => $row->serialNo,
                    'serverName'        => $row->serverName,
                    'serverDescription' => $row->serverType
                )

            );

            $template->parse(
                'servers',
                'serverBlock',
                true
            );

        }
        /*
         * Now the warranties
         */
        $template->set_block(
            'SupportRenewalChecklist',
            'warrantyBlock',
            'warranties'
        );

        $results = $this->db->query($sql);

        while ($row = $results->fetch_object()) {

            $expiryDate = date(
                'd/m/Y',
                strtotime(
                    '+' . $dbeWarranty->getValue('years') . ' years',
                    strtotime($dsServer->getValue('despatchDate'))
                )
            );

            $template->set_var(
                array(
                    'warrantySerialNo'   => $row->serialNo,
                    'warrantyServerName' => $row->serverName,
                    'warrantyExpiryDate' => $expiryDate
                )

            );

            $template->parse(
                'warranties',
                'warrantyBlock',
                true
            );


        }

        $template->parse(
            'output',
            'page',
            false
        );

        $reason = $template->get_var('output');

        $buActivity = new BUActivity($this);
        $callActivityID = $buActivity->createActivityFromCustomerID(
            $dsContract->getValue(DBEJContract::customerID),
            CONFIG_HEALTHCHECK_ACTIVITY_USER_ID
        );

        $dbeCustomerItem = new DBECustomerItem ($this);
        $dbeCallActivity = new DBECallActivity ($this);
        $dbeCallActivity->getRow($callActivityID);
        $dbeCallActivity->setValue(
            DBECallActivity::callActTypeID,
            CONFIG_SERVER_HEALTH_CHECK_CHECKLIST_ACTIVITY_TYPE_ID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::contractCustomerItemID,
            $dbeCustomerItem->getValue(DBECustomerItem::customerItemID)
        );
        $dbeCallActivity->setValue(
            DBECallActivity::problemID,
            $dbeProblem->getPKValue()
        );
        $dbeCallActivity->setValue(
            DBECallActivity::startTime,
            '09:00'
        );
        $dbeCallActivity->setValue(
            DBECallActivity::endTime,
            null
        );
        $dbeCallActivity->setValue(
            DBECallActivity::status,
            'O'
        );
        $dbeCallActivity->setValue(
            DBECallActivity::reason,
            $reason
        );
        $dbeCallActivity->setValue(
            DBECallActivity::curValue,
            0
        );
        $dbeCallActivity->updateRow();
    }

    function emailSalesOrderNotification($customerName,
                                         $ordheadID
    ) // GL
    {
        $toEmail = false;
        $senderEmail = CONFIG_SALES_EMAIL;

        $buMail = new BUMail($this);

        $hdrs =
            array(
                'From'         => $senderEmail,
                'Subject'      => 'ServiceDesk renewal sales order created for ' . $customerName,
                'Date'         => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );


        ob_start(); ?>
        <HTML>
        <BODY>
        <TABLE border="1">
            <tr>
                <td><A HREF="/SalesOrder.php?ordheadID=<?php echo $ordheadID ?>">Open Order</A></td>
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
            $body,
            true
        );

    } // end send email to technical manager

    function isCompleted($customerItemID)
    {
        $this->dbeRenContract->getRow($customerItemID);

        if
        (
            $this->dbeRenContract->getValue('installationDate') &&
            $this->dbeRenContract->getValue('invoicePeriodMonths')
        ) {
            $ret = true;

        }

        return $ret;

    }
} // End of class
?>