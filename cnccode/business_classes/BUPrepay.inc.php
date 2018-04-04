<?php
/**
 * Call activity business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_gc"] . "/Controller.inc.php");
require_once($cfg ["path_dbe"] . "/DBECustomerCallActivity.inc.php");
require_once($cfg ["path_dbe"] . "/DBECustomerCallActivityMonth.inc.php");
require_once($cfg ["path_dbe"] . "/DBECurrentActivity.inc.php");
require_once($cfg ["path_dbe"] . "/DBECallActivity.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJCallActivity.php");
require_once($cfg ["path_dbe"] . "/DBEProblem.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJProblem.inc.php");
require_once($cfg ["path_dbe"] . "/DBECallActivitySearch.inc.php");
require_once($cfg ["path_dbe"] . "/DBECallDocument.inc.php");
require_once($cfg ["path_dbe"] . "/DBECallActType.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJCallActType.php");
require_once($cfg ["path_dbe"] . "/DBEProject.inc.php");
require_once($cfg ["path_bu"] . "/BUCustomerNew.inc.php");
require_once($cfg ["path_bu"] . "/BUSite.inc.php");
require_once($cfg ["path_bu"] . "/BUHeader.inc.php");
require_once($cfg ["path_bu"] . "/BUSalesOrder.inc.php");
require_once($cfg ["path_bu"] . "/BUContact.inc.php");
require_once($cfg ["path_bu"] . "/BUProblemSLA.inc.php");
require_once($cfg ["path_func"] . "/activity.inc.php");
require_once($cfg ["path_dbe"] . "/DBEUser.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJUser.inc.php");
require_once($cfg ["path_dbe"] . "/DBESiteNew.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");

class BUPrepay extends Business
{
    /**
     *
     * @var DBEProblem
     */
    private $dbeProblem = '';
    private $dbeUser = '';
    private $buCustomer = '';
    private $dsHeader = '';
    private $dsData = '';
    private $updateFlag = false;

    /**
     * Constructor
     * @access Public
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeJCallActivity = new DBEJCallActivity ($this);
        $this->dbeUser = new DBEUser ($this);
        $buHeader = new BUHeader ($this);
        $buHeader->getHeader($this->dsHeader);
        $this->dsHeader->fetchNext();
        $this->buCustomer = new BUCustomer ($this);
    }

    function initialiseExportDataset(&$dsData)
    {
        $this->setMethodName('initialiseExportDataset');
        $dsData = new DSForm ($this);
        $dsData->addColumn('endDate', DA_DATE, DA_ALLOW_NULL);
        $dsData->addColumn('previewRun', DA_YN_FLAG, DA_ALLOW_NULL);
        $dsData->setUpdateModeUpdate();
        $dsData->setValue('previewRun', 'Y');
        $dsData->post();

    }

    function exportPrePayActivities($dsData, $updateFlag = false)
    {

        $this->setMethodName('exportPrePayActivities');

        $this->dsData = $dsData;
        $this->updateFlag = $updateFlag;

        $dsResults = new DataSet ($this);
        $dsResults->addColumn('customerName', DA_DATE, DA_ALLOW_NULL);
        $dsResults->addColumn('previousBalance', DA_FLOAT, DA_ALLOW_NULL);
        $dsResults->addColumn('currentBalance', DA_FLOAT, DA_ALLOW_NULL);
        $dsResults->addColumn('expiryDate', DA_STRING, DA_ALLOW_NULL);
        $dsResults->addColumn('topUp', DA_FLOAT, DA_ALLOW_NULL);
        $dsResults->addColumn('contacts', DA_STRING, DA_ALLOW_NULL);
        $dsResults->addColumn('contractType', DA_STRING, DA_ALLOW_NULL);
        $dsResults->addColumn('webFileLink', DA_STRING, DA_ALLOW_NULL); // link to statement


        $dbeVat = new DBEVat($this);
        $dbeVat->getRow();
        $vatCode = $this->dsHeader->getValue('stdVATCode');
        $this->standardVatRate = $dbeVat->getValue((integer)$vatCode[1]); // use second part of code as column no

        $db = new dbSweetcode (); // database connection for query


        /* get a list of valid support customer items */
        $queryString = "
    SELECT
      cui_cuino
		FROM
      custitem
			JOIN customer ON customer.cus_custno = custitem.cui_custno
		WHERE
      cui_itemno = " . $this->dsHeader->getValue('gscItemID') .
            " AND cui_expiry_date >= '" . $this->dsData->getValue('endDate') . "'" .
            " AND cui_desp_date <= '" . $this->dsData->getValue('endDate') . "'" . // and the contract has started
            " AND cui_expiry_date >= now()" . // and is not expired
            " AND	cus_custno <> " . CONFIG_SALES_STOCK_CUSTOMERID .
            " AND	renewalStatus  <> 'D'";


        $db->query($queryString);
        while ($db->next_record()) {
            $validContracts [$db->Record ['cui_cuino']] = 0; // initialise to no activity

        }

//        $dbUpdate = new dbSweetcode (); // database connection for update query

//        $dbeCallActivity = new DBECallActivity ($this); // for update of status
        /*
        Bring out a list of PrePay Service Requests to be included in the statement run
        */
        $queryString =
            "SELECT
        pro_problemno,
        pro_custno AS custno,
        DATE_FORMAT(pro_date_raised, '%d/%m/%Y') AS requestDate,
        cus_name,
        customer.gscTopUpAmount,
        cui_cuino,
        ity_desc,
        cui_expiry_date,
        curGSCBalance        
                
      FROM callactivity
        JOIN problem
          ON pro_problemno = caa_problemno
        JOIN callacttype
          ON cat_callacttypeno = caa_callacttypeno
        JOIN custitem
          ON pro_contract_cuino = cui_cuino
        JOIN item
          ON itm_itemno = cui_itemno
        JOIN customer
          ON pro_custno = cus_custno
        JOIN itemtype
          ON ity_itemtypeno = itm_itemtypeno
          
      WHERE itm_itemno = " . $this->dsHeader->getValue('gscItemID') .              // Activity logged against PrePay contract
            " AND DATE(pro_fixed_date) <= '" . $this->dsData->getValue('endDate') . "'" .   // Request was raised before run date
            " AND cui_desp_date <= '" . $this->dsData->getValue('endDate') . "'" .     // Contract had started before run date
            " AND cui_expiry_date >= NOW() " .                                      // Contract not expired
            " AND pro_custno <> " . CONFIG_SALES_STOCK_CUSTOMERID .                 // Not CNC sales stock customer
            " AND renewalStatus <> 'D' " .                                          // Contract renewal not declined
            " AND caa_callacttypeno NOT IN( " .                                     // Activity type not engineer travel or proactive
            CONFIG_ENGINEER_TRAVEL_ACTIVITY_TYPE_ID . "," .
            CONFIG_PROACTIVE_SUPPORT_ACTIVITY_TYPE_ID .
            ")" .
            " AND pro_status = 'C'" .                                                // Service Request completed
            " AND caa_status = 'C'" .                                                // Activity completed
            " AND
          ( caa_starttime <> caa_endtime OR curValue <> 0 )" .                   // time was logged or this is a value (e.g. topup)
            " GROUP BY pro_problemno
      ORDER BY pro_custno, pro_problemno, pro_date_raised";

        $db->query($queryString);

        $ret = FALSE; // indicates there were no statements to export

        $buContact = new BUContact ($this);

        // ensure all customers have at least one statement contact
        $last_custno = '9999';

        while ($db->next_record()) {

            if ($db->Record ['custno'] != $last_custno) {
                if ($last_custno != '9999') {
                    $buContact->getGSCContactByCustomerID($db->Record ['custno'], $dsStatementContact);
                    if (!is_object($dsStatementContact)) {
                        $this->raiseError('Customer ' . $db->Record ['cus_name'] . ' needs at least one Pre-pay statement contact.');
                        exit ();
                    }
                }
            }
            $last_custno = $db->Record ['custno'];
        }

        $db->query($queryString);

        $last_custno = '9999';

        while ($db->next_record()) {

            $validContracts [$db->Record ['cui_cuino']] = 1; // flag contract as having activity

            $ret = TRUE; // there was at least one statement to export

            // new customer so create new html file
            if ($db->Record ['custno'] != $last_custno) {

                if ($last_custno != '9999') {

                    $topupValue = $this->doTopUp($lastRecord);
                    $newBalance = $lastRecord ['curGSCBalance'] + $this->totalCost;
                    $this->template->set_var(
                        array(
                            'totalCost' => common_numberFormat($this->totalCost),
                            'previousBalance' => common_numberFormat($lastRecord ['curGSCBalance']),
                            'remainingBalance' => common_numberFormat($newBalance)
                        )
                    );

                    $this->template->parse('output', 'page', true);
                    fwrite($htmlFileHandle, $this->template->get_var('output'));
                    fclose($htmlFileHandle); // close previous html file


                    $this->postRowToSummaryFile(
                        $lastRecord,
                        $dsResults,
                        $dsStatementContact,
                        $newBalance,
                        $topupValue,
                        $this->dsData->getValue('endDate')
                    );

                    $dsStatementContact->initialise();

                    if ($this->updateFlag) {
                        $fileName = $filepath . '.html';

                        $this->sendStatement(
                            $fileName,
                            $last_custno,
                            $dsStatementContact,
                            $newBalance,
                            $this->dsData->getValue('endDate'),
                            $topupValue
                        );

                    }

                } // end if ( $last_custno != '9999' )


                $this->totalCost = 0; // reset cost


                $filepath = SAGE_EXPORT_DIR . '/PP_' . substr($db->Record ['cus_name'],
                                                              0,
                                                              20) . $this->dsData->getValue('endDate');

                $htmlFileHandle = fopen($filepath . '.html', 'wb');
                if (!$htmlFileHandle) {
                    $this->raiseError("Unable to open html file " . $filepath);
                }

                // set up new html file template
                $this->template = new Template ($GLOBALS ["cfg"] ["path_templates"], "remove");
                $this->template->set_file('page', 'PrepayReport.inc.html');
                // get GSC contact record
                $buContact->getGSCContactByCustomerID($db->Record ['custno'], $dsStatementContact);
                $this->buCustomer->getSiteByCustomerIDSiteNo($dsStatementContact->getValue('customerID'),
                                                             $dsStatementContact->getValue('siteNo'),
                                                             $dsSite);

                // Set header fields
                $this->template->set_var(
                    array(
                        'companyName' => $db->Record ['cus_name'],
                        'customerRef' => $db->Record ['cui_cuino'],
                        'statementDate' => Controller::dateYMDtoDMY($this->dsData->getValue('endDate')),
                        'add1' => $dsSite->getValue('add1'),
                        'add2' => $dsSite->getValue('add2'),
                        'add3' => $dsSite->getValue('add3'),
                        'town' => $dsSite->getValue('town'),
                        'county' => $dsSite->getValue('county'),
                        'postcode' => $dsSite->getValue('postcode'),
                        'cnc_name' => $this->dsHeader->getValue('name'),
                        'cnc_add1' => $this->dsHeader->getValue('add1'),
                        'cnc_add2' => $this->dsHeader->getValue('add2'),
                        'cnc_add3' => $this->dsHeader->getValue('add3'),
                        'cnc_town' => $this->dsHeader->getValue('town'),
                        'cnc_county' => $this->dsHeader->getValue('county'),
                        'cnc_postcode' => $this->dsHeader->getValue('postcode'),
                        'cnc_phone' => $this->dsHeader->getValue('phone')
                    )
                );

                $this->template->set_block('page', 'lineBlock', 'lines');

                $last_custno = $db->Record ['custno'];
                $ret = TRUE; // indicates there were statements to export


            } // end if ( $db->Record['custno'] != $last_custno )


            $posted = FALSE;

            /*
            Loop around the activities in this request, totaling the values
            */

            $lastRecord = $db->Record;

            $this->getActivitiesByServiceRequest($db->Record);

        }
        //close file

        if ($ret == TRUE) {
            $topupValue = $this->doTopUp($lastRecord);
            $newBalance = $lastRecord ['curGSCBalance'] + $this->totalCost;
            $this->template->set_var(
                array(
                    'totalCost' => common_numberFormat($this->totalCost),
                    'previousBalance' => common_numberFormat($lastRecord ['curGSCBalance']),
                    'remainingBalance' => common_numberFormat($newBalance)
                )
            );

            $this->template->parse('output', 'page', true);
            fwrite($htmlFileHandle, $this->template->get_var('output'));
            fclose($htmlFileHandle);

            $this->postRowToSummaryFile($lastRecord,
                                        $dsResults,
                                        $dsStatementContact,
                                        $newBalance,
                                        $topupValue,
                                        $this->dsData->getValue('endDate'));

            if ($this->updateFlag) {
                $dsStatementContact->initialise();
                $this->sendStatement($filepath . '.html',
                                     $last_custno,
                                     $dsStatementContact,
                                     $newBalance,
                                     $this->dsData->getValue('endDate'),
                                     $topupValue);
            }
        }

        /*
    Now produce statements for contracts that had no activity
*/
        $this->totalCost = 0; // there is no balance of activity cost
        reset($validContracts);
        foreach ($validContracts as $key => $value) {
            if ($value == 0) {

                $ret = true;

                $queryString = "SELECT
						cus_name,
            cus_custno,
						cui_desp_date,
						cui_expiry_date,
						cui_cuino,
						curGSCBalance,
						cui_custno AS custno,
						gscTopUpAmount,
						ity_desc
					FROM
						custitem
						JOIN customer ON cui_custno = cus_custno
						JOIN item ON cui_itemno = itm_itemno
						JOIN itemtype ON ity_itemtypeno = itm_itemtypeno
					WHERE
						cui_cuino = " . $key . " AND	cus_custno <> 2511" . " AND	renewalStatus  <> 'D'";
// The following code is used when there has been a crash to exclude already processed custs		
//		$queryString .= " AND cus_custno NOT IN( 1000, 823, 820, 520 , 203, 117)";

                $db->query($queryString);
                $db->next_record();
                // get GSC contact record
                $buContact->getGSCContactByCustomerID($db->Record ['custno'], $dsStatementContact);
                $this->buCustomer->getSiteByCustomerIDSiteNo($dsStatementContact->getValue('customerID'),
                                                             $dsStatementContact->getValue('siteNo'),
                                                             $dsSite);

                // set up new html file template
                $filepath = SAGE_EXPORT_DIR . '/PP_' . substr($db->Record ['cus_name'],
                                                              0,
                                                              20) . $this->dsData->getValue('endDate');
                $htmlFileHandle = fopen($filepath . '.html', 'wb');
                if (!$htmlFileHandle) {
                    $this->raiseError("Unable to open html file " . $filepath);
                }
                $this->template = new Template ($GLOBALS ["cfg"] ["path_templates"], "remove");
                $this->template->set_file('page', 'PrepayReport.inc.html');

                // Set header fields
                $this->template->set_var(array('companyName' => $db->Record ['cus_name'], 'customerRef' => $key, 'startDate' => Controller::dateYMDtoDMY($db->Record ['cui_desp_date']), 'endDate' => Controller::dateYMDtoDMY($db->Record ['cui_expiry_date']), 'statementDate' => Controller::dateYMDtoDMY($this->dsData->getValue('endDate')), 'add1' => $dsSite->getValue('add1'), 'add2' => $dsSite->getValue('add2'), 'add3' => $dsSite->getValue('add3'), 'town' => $dsSite->getValue('town'), 'county' => $dsSite->getValue('county'), 'postcode' => $dsSite->getValue('postcode'), 'cnc_name' => $this->dsHeader->getValue('name'), 'cnc_add1' => $this->dsHeader->getValue('add1'), 'cnc_add2' => $this->dsHeader->getValue('add2'), 'cnc_add3' => $this->dsHeader->getValue('add3'), 'cnc_town' => $this->dsHeader->getValue('town'), 'cnc_county' => $this->dsHeader->getValue('county'), 'cnc_postcode' => $this->dsHeader->getValue('postcode'), 'cnc_phone' => $this->dsHeader->getValue('phone')));

                $this->template->set_block('page', 'lineBlock', 'lines');

                $this->template->set_var(
                    array(
                        'requestDate' => '',
                        'requestRef' => '',
                        'requestHours' => '',
                        'requestValue' => '',
                        'requestCustomerContact' => '',
                        'requestDetails' => 'No service requests logged in this period')
                );

                $this->template->parse('lines', 'lineBlock', true);
                $this->totalCost += $value;
                $this->template->set_var(array('totalCost' => 0, 'previousBalance' => common_numberFormat($db->Record ['curGSCBalance']), 'remainingBalance' => common_numberFormat($db->Record ['curGSCBalance'])));
                $this->template->parse('output', 'page', true);
                fwrite($htmlFileHandle, $this->template->get_var('output'));
                fclose($htmlFileHandle);

                $dsStatementContact->initialise();
                $topupValue = $this->doTopUp($db->Record);

                $this->postRowToSummaryFile($db->Record,
                                            $dsResults,
                                            $dsStatementContact,
                                            $db->Record ['curGSCBalance'],
                                            $topupValue,
                                            $this->dsData->getValue('endDate'));

                if ($this->updateFlag) {
                    $this->sendStatement(
                        $filepath . '.html',
                        $db->Record['cus_custno'],
                        $dsStatementContact,
                        $db->Record ['curGSCBalance'],
                        $this->dsData->getValue('endDate'),
                        $topupValue
                    );
                }
            }
        }

        if ($ret) {
            return $dsResults;
        } else {
            return false;
        }
    }

    /*
          work out whether a top-up is required and if so then generate one
          We generate a top-up T&M call so that this can later be amended and/or checked and used to generate a sales
          order for the top-up amount.
          This call will now appear on
      */
    function doTopUp(&$Record)
    {
        $newBalance = $Record ['curGSCBalance'] + $this->totalCost;
        // generate top-up call and activity if required
        if ($this->updateFlag) {
            $dbeCustomerItem = new DBECustomerItem ($this);
            $dbeCustomerItem->getRow($Record ['cui_cuino']);
            $dbeCustomerItem->setValue('curGSCBalance', $newBalance);
            $dbeCustomerItem->updateRow();
        }

        if ($newBalance >= 100) {
            return 0;
        }

        if ($newBalance < 0) {
            // value of the top-up activity is the GSC item price plus amount required to clear balance
            $topupValue = (0 - $newBalance) + $Record ['gscTopUpAmount'];
        } else {
            $topupValue = $Record ['gscTopUpAmount']; // just the top-up amount
        }
        // 	Create sales order
        if ($this->updateFlag) {
            $salesOrderNo = $this->createTopupSalesOrder($Record, $topupValue);
        }

        return $topupValue;
    }

    function getActivitiesByServiceRequest($serviceRequestRecord)
    {

        $db = new dbSweetcode (); // database connection for query

        $queryString = "
      SELECT
          caa_callactivityno,
          caa_date,
          caa_starttime,
          caa_endtime,
          caa_siteno,
          callacttype.curValueFlag,
          callacttype.travelFlag,
          cat_min_hours,
          cat_itemno,
          cat_ooh_multiplier,
          caa_callacttypeno,
          callactivity.curValue,
          cui_desp_date,
          cui_expiry_date,
          cui_cuino,
          itm_sstk_price,
          reason,
          con_first_name,
          con_last_name

        FROM
          callactivity
          JOIN problem ON pro_problemno = caa_problemno
          JOIN callacttype ON cat_callacttypeno=caa_callacttypeno
          JOIN custitem ON pro_contract_cuino = cui_cuino
          JOIN item ON cui_itemno = itm_itemno
          JOIN itemtype ON ity_itemtypeno = itm_itemtypeno
          JOIN contact ON caa_contno = con_contno
        WHERE
          caa_problemno = " . $serviceRequestRecord['pro_problemno'] .
            " AND itm_itemno = " . $this->dsHeader->getValue('gscItemID') . " AND caa_endtime IS NOT NULL
          AND caa_status = 'C'
          AND caa_callacttypeno NOT IN( " .                                     // Activity type not engineer travel or proactive
            CONFIG_ENGINEER_TRAVEL_ACTIVITY_TYPE_ID . "," .
            CONFIG_PROACTIVE_SUPPORT_ACTIVITY_TYPE_ID .
            ")
        ORDER BY caa_date, caa_starttime";

        $db->query($queryString);

        $totalValue = 0;

        $firstActivity = true;

        while ($db->next_record()) {

            if ($db->Record ['curValueFlag'] == 'Y') { // This is a monetary value activity such as top-up or adjustment

                $requestValue += $db->Record ['curValue'];

            } else {
                /*
                If this is travel then apply maximum hours to customer site
                */
                if ($db->Record ['travelFlag'] == 'Y') {

                    $this->buCustomer->getSiteByCustomerIDSiteNo(
                        $serviceRequestRecord['custno'],
                        $db->Record['caa_siteno'],
                        $dsSite
                    );

                    $max_hours = $dsSite->getValue('maxTravelHours');

                } else {

                    $max_hours = 0;

                }
                /*
                Calculate the rates and hours for this activity
                */
                getRatesAndHours(
                    $db->Record ['caa_date'],
                    $db->Record ['caa_starttime'],
                    $db->Record ['caa_endtime'],
                    $db->Record ['cat_min_hours'],
                    $max_hours,
                    $db->Record ['cat_ooh_multiplier'],
                    $db->Record ['cat_itemno'],
                    'Y', // under contract
                    $this->dsHeader,
                    $normalHours,
                    $beforeHours,
                    $afterHours,
                    $overtimeRate,
                    $normalRate,
                    'N'
                );

                /*
                Only add to totals if this is chargeable
                */
                if ($normalRate > 0) {
                    $requestHours += $normalHours + $beforeHours + $afterHours;
                    $requestValue += 0 - ($normalHours * $normalRate);
                    $requestValue += 0 - (($beforeHours + $afterHours) * $overtimeRate);
                }

            } // end if ($db->Record ['curValueFlag'] == 'Y')

            if ($firstActivity) {
                $reason = substr(strip_tags($db->Record ['reason']), 0, 130);
                $reason = str_replace("\r\n", "", $reason);
                $reason = str_replace("\"", "", $reason);
                $customerContact = trim($db->Record ['con_first_name']) . ' ' . trim($db->Record ['con_last_name']);

                $firstActivity = false;
            }

            if ($this->updateFlag) {
                if (!$dbeCallActivity) {
                    $dbeCallActivity = new DBECallActivity($this);
                }
                // update status on call activity to Authorised and statement date to today
                $dbeCallActivity->getRow($db->Record ['caa_callactivityno']);
                $dbeCallActivity->setValue('status', 'A');
                $dbeCallActivity->setValue('statementYearMonth', date('Y-m'));
                $dbeCallActivity->updateRow();
            }

        } // end while $db->next_record

        $this->postRowToPrePayExportFile(
            $serviceRequestRecord,
            $reason,
            $customerContact,
            $requestHours,
            $requestValue
        );


    }    // end function


    function postRowToSummaryFile(&$Record, &$dsResults, &$dsStatementContact, $newBalance, $topupAmount, $endDate)
    {
        $contacts = '';
        while ($dsStatementContact->fetchNext) {
            $contacts .= $dsStatementContact->getValue('firstName') . ' ' . $dsStatementContact->getValue('lastName');
        }
        $webFileLink = 'export/PP_' . substr($Record ['cus_name'], 0, 20) . $endDate . '.html';

        $dsResults->setUpdateModeInsert();
        $dsResults->setValue('customerName', $Record ['cus_name']);
        $dsResults->setValue('previousBalance', $Record ['curGSCBalance']);
        $dsResults->setValue('currentBalance', common_numberFormat($newBalance));
        $dsResults->setValue('expiryDate', Controller::dateYMDtoDMY($Record ['cui_expiry_date']));
        $dsResults->setValue('topUp', common_numberFormat($topupAmount));
        $dsResults->setValue('contacts', $contacts);
        $dsResults->setValue('contractType', $Record ['ity_desc']);
        $dsResults->setValue('webFileLink', $webFileLink);
        $dsResults->post();
    }

    function postRowToPrePayExportFile(
        &$serviceRequestRecord,
        $reason,
        $customerContact,
        $requestHours,
        $requestValue
    )
    {


        $this->template->set_var(
            array(
                'requestDate' => $serviceRequestRecord ['requestDate'],
                'requestDetails' => $reason,
                'requestRef' => $serviceRequestRecord['pro_problemno'],
                'requestHours' => common_numberFormat($requestHours),
                'requestCustomerContact' => $customerContact,
                'requestValue' => common_numberFormat($requestValue)
            )
        );

        $this->template->parse('lines', 'lineBlock', true);

        $this->totalCost += $requestValue;
    }

    /*
        Create sales order for top-up
    */
    function createTopupSalesOrder(&$Record, $topupValue)
    {
        $this->setMethodName('createTopupSalesOrder');

        $this->buCustomer->getCustomerByID($Record ['custno'], $dsCustomer);

        // create sales order header with correct field values
        $buSalesOrder = new BUSalesOrder ($this);
        $buSalesOrder->initialiseOrder($dsOrdhead, $dbeOrdline, $dsCustomer);
        $dsOrdhead->setUpdateModeUpdate();
        $dsOrdhead->setvalue('custPORef', 'Top Up');
        $dsOrdhead->setvalue('addItem', 'N');
        $dsOrdhead->setvalue('partInvoice', 'N');
        $dsOrdhead->setvalue('payMethod', CONFIG_PAYMENT_TERMS_30_DAYS);
        $dsOrdhead->post();
        $buSalesOrder->updateHeader($dsOrdhead->getValue('ordheadID'),
                                    $dsOrdhead->getValue('custPORef'),
                                    $dsOrdhead->getValue('payMethod'),
                                    $dsOrdhead->getValue('partInvoice'),
                                    $dsOrdhead->getValue('addItem'));

        $ordheadID = $dsOrdhead->getValue('ordheadID');
        $sequenceNo = 1;

        // get topup item details
        $dbeItem = new DBEItem ($this);
        $dbeItem->getRow(CONFIG_DEF_PREPAY_TOPUP_ITEMID);

        // create order line
        $dbeOrdline = new DBEOrdline ($this);
        $dbeOrdline->setValue('ordheadID', $ordheadID);
        $dbeOrdline->setValue('sequenceNo', $sequenceNo);
        $dbeOrdline->setValue('customerID', $Record ['custno']);
        $dbeOrdline->setValue('qtyDespatched', 0);
        $dbeOrdline->setValue('qtyLastDespatched', 0);
        $dbeOrdline->setValue('supplierID', CONFIG_SALES_STOCK_SUPPLIERID);
        $dbeOrdline->setValue('lineType', 'I');
        $dbeOrdline->setValue('sequenceNo', $sequenceNo);
        $dbeOrdline->setValue('stockcat', 'R');
        $dbeOrdline->setValue('itemID', CONFIG_DEF_PREPAY_TOPUP_ITEMID);
        $dbeOrdline->setValue('qtyOrdered', 1);
        $dbeOrdline->setValue('curUnitCost', 0);
        $dbeOrdline->setValue('curTotalCost', 0);
        $dbeOrdline->setValue('curUnitSale', $topupValue);
        $dbeOrdline->setValue('curTotalSale', $topupValue);
        $dbeOrdline->setValue('description', $dbeItem->getValue('description'));
        $dbeOrdline->insertRow();
        return $dsOrdhead->getValue('ordheadID');
    }


    function sendStatement($statementFilepath, $custno, &$dsContact, $balance, $date, $topupValue)
    {

        $buMail = new BUMail($this);

        $buMail->mime->addAttachment($statementFilepath, 'text/html');

        $id_user = $GLOBALS ['auth']->is_authenticated();
        $this->dbeUser->getRow($id_user);

        $statementFilename = basename($statementFilepath);
        $senderEmail = CONFIG_SALES_EMAIL;
        $senderName = 'CNC Sales';

        while ($dsContact->fetchNext()) {
            // Send email with attachment
            $message = '<body><p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
            $message .= 'Dear ' . $dsContact->getValue('firstName') . ',';
            $message .= '<o:p></o:p></span></font></p>';
            $message .= '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
            // Temporary:
            $message .= 'Please find attached your latest Pre-Pay Contract statement, on which there
is currently a balance of ';
            $message .= '&pound;' . common_numberFormat($balance) . ' + VAT.';
            $message .= '</p>';

            $message .= '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
            $message .= 'If you have any queries relating to any of the items detailed on this statement, then please notify us within 7 days so that we can make any adjustments if applicable.';
            $message .= '</p>';

            if ($balance <= 100) {
                $message .= '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
                $message .= 'If no response to the contrary is received within 7 days of this statement, then we will automatically raise an invoice for &pound;' . common_numberFormat($topupValue * (1 + ($this->standardVatRate / 100))) . ' Inc VAT.';
                $message .= '</p>';
            }

            $message .= '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
            $message .= 'Are you aware that you can receive up to &pound;500 for the referral of any company made to CNC that results in the purchase of a support contract?  Please call us for further information.';
            $message .= '</p>';

            $message .= common_getHTMLEmailFooter($senderName, $senderEmail);

            $subject = 'Pre-Pay Contract Statement: ' . Controller::dateYMDtoDMY($date);

            $toEmail = $dsContact->getValue('firstName') . ' ' . $dsContact->getValue('lastName') . '<' . $dsContact->getValue('email') . '>';

            // create mime
            $html = '<html>' . $message . '</html>';

            $file = '$statementFilename';

            $hdrs = array(
                'From' => $senderName . " <" . $senderEmail . ">",
                'To' => $toEmail,
                'Subject' => $subject,
                'Content-Type' => 'text/html; charset=UTF-8'
            );

            $buMail->mime->setHTMLBody($html);
            $mime_params = array(
                'text_encoding' => '7bit',
                'text_charset' => 'UTF-8',
                'html_charset' => 'UTF-8',
                'head_charset' => 'UTF-8'
            );
            $body = $buMail->mime->get($mime_params);
            $hdrs = $buMail->mime->headers($hdrs);

            $buMail->putInQueue(
                $senderEmail,
                $toEmail,
                $hdrs,
                $body
            );

        } // end while
        /*
        Upate DB
        */
        $this->save($statementFilepath, $custno, $balance);
    }

    function save($filename, $custno, $balance)
    {
        $db = $GLOBALS['db'];

        $fileString = mysqli_real_escape_string($db->link_id(), file_get_contents($filename));

        $sql =

            "INSERT INTO
        prepaystatement(
          pre_custno,
          pre_date,
          pre_file,
          pre_balance
        )
        VALUES(
          $custno,
          NOW(),
          '$fileString',
          $balance
        )";

        $db->query($sql);

    }

} // End of class
?>
