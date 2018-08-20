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
        $this->updateDataaccessObject(
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

    function emailRenewalsSalesOrdersDue($toEmail = CONFIG_SALES_MANAGER_EMAIL)
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

    function createRenewalsSalesOrders($customerItemIDs = false)
    {
        $this->createSalesOrders(
            $customerItemIDs
        );
    }

    function createSalesOrders($customerItemIDs = false)
    {
        $buSalesOrder = new BUSalesOrder ($this);

        $buInvoice = new BUInvoice ($this);
        $buActivity = new BUActivity ($this);

        if ($customerItemIDs) {
            $this->dbeJRenContract->getRenewalsRowsByID($customerItemIDs);
        } else {
            $this->dbeJRenContract->getRenewalsDueRows();
        }

        $dsRenContract = new DSForm($this);
        $dsRenContract->replicate($this->dbeJRenContract);

        $dbeJCustomerItem = new DBEJCustomerItem ($this);

        $dbeCustomer = new DBECustomer ($this);

        $dbeOrdline = new DBEOrdline ($this);

        $previousCustomerID = 99999;

        $dsOrdhead = false;
        $generateInvoice = false;
        while ($dsRenContract->fetchNext()) {
            /* don't process prepay */
            if ($dsRenContract->getValue('itemID') == CONFIG_DEF_PREPAY_ITEMID) {
                continue;
            }

            if ($dbeJCustomerItem->getRow($dsRenContract->getValue('customerItemID'))) {
                /*
                 * Group many contracts for same customer under one sales order
         * unless it is an SSL cert in which case it has it's own order
                 */
                if (strpos(
                        $dbeJCustomerItem->getValue('itemDescription'),
                        'SSL'
                    ) !== false) {
                    $isSslCertificate = true;
                } else {
                    $isSslCertificate = false;
                }

                if (
                    $previousCustomerID != $dbeJCustomerItem->getValue('customerID') OR
                    $isSslCertificate
                ) {
                    /*
                    If generating invoices and an order has been started
                    */
                    if ($generateInvoice && $dsOrdhead) {

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
                    /*
                     *  create order header
                     */
                    $dbeCustomer->getRow($dbeJCustomerItem->getValue('customerID'));
                    $this->getData(
                        $dbeCustomer,
                        $dsCustomer
                    );

                    $buSalesOrder->initialiseOrder(
                        $dsOrdhead,
                        $dsOrdline,
                        $dsCustomer
                    );

                    $line = -1;    // initialise sales order line seq
                }

                $generateInvoice = $dsRenContract->getValue(DBECustomerItem::autoGenerateContractInvoice) === 'Y';

                /**
                 * add notes as a comment line (if they exist)
                 */
                if ($dsRenContract->getValue('notes')) {

                    $line++;

                    $dbeOrdline->setValue(
                        'description',
                        $dsRenContract->getValue('notes')
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
                        'curUnitSale',
                        0
                    );
                    $dbeOrdline->setValue(
                        'curUnitCost',
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
                    $dbeJCustomerItem->getValue('itemID'),
                    $dsItem
                );
                $dbeOrdline->setValue(
                    'stockcat',
                    $dsItem->getValue('stockcat')
                );

                $dbeOrdline->setValue(
                    'renewalCustomerItemID',
                    $dsRenContract->getValue('customerItemID')
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
                    ($dbeJCustomerItem->getValue('curUnitSale') / 12) * $dsRenContract->getValue('invoicePeriodMonths')
                );
                $dbeOrdline->setValue(
                    'curUnitCost',
                    ($dbeJCustomerItem->getValue('curUnitCost') / 12) * $dsRenContract->getValue('invoicePeriodMonths')
                );

                $dbeOrdline->insertRow();

                // period comment line
                $line++;
                $description = $dsRenContract->getValue('invoiceFromDate') . ' to ' . $dsRenContract->getValue(
                        'invoiceToDate'
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

                // SSL Installation charge
                if ($isSslCertificate) {
                    $line++;
                    $description = 'Installation Charge';
                    $dbeOrdline->setValue(
                        'lineType',
                        'I'
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
                        CONFIG_CONSULTANCY_DAY_LABOUR_ITEMID
                    );
                    $dbeOrdline->setValue(
                        'description',
                        $description
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
                        'qtyOrdered',
                        1
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
                        35.00
                    );
                    $dbeOrdline->setValue(
                        'curUnitCost',
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

                    $internalNotes = $dsRenContract->getValue('internalNotes');
                    $internalNotes = nl2br($internalNotes);

                    $renContractId = $dsRenContract->getValue('customerItemID');

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
                        ''
                    );
                    $dsInput->setValue(
                        'serviceRequestPriority',
                        5
                    );

                    $buActivity->createSalesServiceRequest(
                        $dsOrdhead->getValue('ordheadID'),
                        $dsInput
                    );


                }

                /**
                 * add customer items linked to this contract as a comment lines
                 */
                $this->buCustomerItem->getCustomerItemsByContractID(
                    $dsRenContract->getValue('customerItemID'),
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


                /*
                 * Update total months invoiced on renewal record
                 */

                $this->dbeRenContract->getRow($dsRenContract->getValue('customerItemID'));

                $this->dbeRenContract->setValue(
                    'totalInvoiceMonths',
                    $dsRenContract->getValue('totalInvoiceMonths') +
                    $dsRenContract->getValue('invoicePeriodMonths')
                );
                $this->dbeRenContract->updateRow();

                $previousCustomerID = $dbeJCustomerItem->getValue('customerID');
            }

        }
        /*
        Finish off last automatic invoice
        */
        if ($generateInvoice && $dsOrdhead) {

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

        /* there will only be one order in this case */
        if ($renewalIDs) {

            return $dsOrdhead->getValue('ordheadID');
        }

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
	      AND server.cui_cust_ref <> ''
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
            $dsContract->getValue('customerID'),
            CONFIG_HEALTHCHECK_ACTIVITY_USER_ID
        );

        $dbeCustomerItem = new DBECustomerItem ($this);
        $dbeCallActivity = new DBECallActivity ($this);
        $dbeCallActivity->getRow($callActivityID);
        $dbeCallActivity->setValue(
            'callActTypeID',
            CONFIG_SERVER_HEALTH_CHECK_CHECKLIST_ACTIVITY_TYPE_ID
        );
        $dbeCallActivity->setValue(
            'contractCustomerItemID',
            $dbeCustomerItem->getValue('customerItemID')
        );
        $dbeCallActivity->setValue(
            'problemID',
            $dbeProblem->getPKValue()
        );
        $dbeCallActivity->setValue(
            'startTime',
            '09:00'
        );
        $dbeCallActivity->setValue(
            'endTime',
            ''
        );
        $dbeCallActivity->setValue(
            'status',
            'O'
        );
        $dbeCallActivity->setValue(
            'reason',
            $reason
        );
        $dbeCallActivity->setValue(
            'curValue',
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