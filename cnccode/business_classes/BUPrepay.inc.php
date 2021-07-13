<?php
/**
 * Call activity business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Data\DBEItem;
use CNCLTD\Email\AttachmentCollection;

global $cfg;
require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_gc"] . "/Controller.inc.php");
require_once($cfg ["path_dbe"] . "/DBECallActivity.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJCallActivity.php");
require_once($cfg ["path_dbe"] . "/DBEProblem.inc.php");
require_once($cfg ["path_dbe"] . "/DBECallActivitySearch.inc.php");
require_once($cfg ["path_dbe"] . "/DBECallDocument.inc.php");
require_once($cfg ["path_dbe"] . "/DBECallActType.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJCallActType.php");
require_once($cfg ["path_dbe"] . "/DBEProject.inc.php");
require_once($cfg ["path_bu"] . "/BUCustomer.inc.php");
require_once($cfg ["path_bu"] . "/BUSite.inc.php");
require_once($cfg ["path_bu"] . "/BUHeader.inc.php");
require_once($cfg ["path_bu"] . "/BUSalesOrder.inc.php");
require_once($cfg ["path_bu"] . "/BUContact.inc.php");
require_once($cfg ["path_bu"] . "/BUProblemSLA.inc.php");
require_once($cfg ["path_func"] . "/activity.inc.php");
require_once($cfg ["path_dbe"] . "/DBEUser.inc.php");
require_once($cfg ["path_dbe"] . "/DBEJUser.inc.php");
require_once($cfg ["path_dbe"] . "/DBESite.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");
require_once($cfg['path_bu'] . '/BUExpense.inc.php');

class BUPrepay extends Business
{

    const exportDataSetEndDate    = "endDate";
    const exportDataSetPreviewRun = "previewRun";

    const exportPrePayCustomerName    = "customerName";
    const exportPrePayPreviousBalance = "previousBalance";
    const exportPrePayCurrentBalance  = "currentBalance";
    const exportPrePayExpiryDate      = "expiryDate";
    const exportPrePayTopUp           = "topUp";
    const exportPrePayContacts        = "contacts";
    const exportPrePayContractType    = "contractType";
    const exportPrePayWebFileLink     = "webFileLink";
    /**
     * @var DBEJCallActivity
     */
    public $dbeJCallActivity;
    /** @var DBEUser */
    private $dbeUser;
    /** @var BUCustomer */
    private $buCustomer;
    /** @var DataSet|DBEHeader */
    private $dsHeader;
    /** @var DSForm */
    private $dsData;
    private $updateFlag = false;
    /**
     * @var bool|float|int|string
     */
    private $standardVatRate;
    private $totalCost;
    /** @var Template */
    private $template;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeJCallActivity = new DBEJCallActivity ($this);
        $this->dbeUser          = new DBEUser ($this);
        $buHeader               = new BUHeader ($this);
        $this->dsHeader         = new DataSet($this);
        $buHeader->getHeader($this->dsHeader);
        $this->buCustomer = new BUCustomer ($this);
    }

    function initialiseExportDataset(&$dsData)
    {
        $this->setMethodName('initialiseExportDataset');
        $dsData = new DSForm ($this);
        $dsData->addColumn(self::exportDataSetEndDate, DA_DATE, DA_ALLOW_NULL);
        $dsData->addColumn(self::exportDataSetPreviewRun, DA_YN_FLAG, DA_ALLOW_NULL);
        $dsData->setUpdateModeUpdate();
        $dsData->setValue(self::exportDataSetPreviewRun, 'Y');
        $dsData->post();

    }

    /**
     * @param $dsData
     * @param bool $updateFlag
     * @return bool|DataSet
     * @throws Exception
     */
    function exportPrePayActivities($dsData, $updateFlag = false)
    {

        $this->setMethodName('exportPrePayActivities');
        $this->dsData     = $dsData;
        $this->updateFlag = $updateFlag;
        $dsResults        = new DataSet ($this);
        $dsResults->addColumn(self::exportPrePayCustomerName, DA_STRING, DA_ALLOW_NULL);
        $dsResults->addColumn(self::exportPrePayPreviousBalance, DA_FLOAT, DA_ALLOW_NULL);
        $dsResults->addColumn(self::exportPrePayCurrentBalance, DA_FLOAT, DA_ALLOW_NULL);
        $dsResults->addColumn(self::exportPrePayExpiryDate, DA_STRING, DA_ALLOW_NULL);
        $dsResults->addColumn(self::exportPrePayTopUp, DA_FLOAT, DA_ALLOW_NULL);
        $dsResults->addColumn(self::exportPrePayContacts, DA_STRING, DA_ALLOW_NULL);
        $dsResults->addColumn(self::exportPrePayContractType, DA_STRING, DA_ALLOW_NULL);
        $dsResults->addColumn(self::exportPrePayWebFileLink, DA_STRING, DA_ALLOW_NULL); // link to statement
        $dbeVat = new DBEVat($this);
        $dbeVat->getRow();
        $vatCode               = $this->dsHeader->getValue(DBEHeader::stdVATCode);
        $this->standardVatRate = $dbeVat->getValue((integer)$vatCode[1]); // use second part of code as column no
        $db                    = new dbSweetcode (); // database connection for query
        /* get a list of valid support customer items */
        $queryString = "
    SELECT
      cui_cuino
		FROM
      custitem
			JOIN customer ON customer.cus_custno = custitem.cui_custno
		WHERE
      cui_itemno = " . $this->dsHeader->getValue(
                DBEHeader::gscItemID
            ) . " AND cui_expiry_date >= '" . $this->dsData->getValue(
                self::exportDataSetEndDate
            ) . "'" . " AND cui_desp_date <= '" . $this->dsData->getValue(
                self::exportDataSetEndDate
            ) . "'" . // and the contract has started
            " AND cui_expiry_date >= now()" . // and is not expired
            " AND	cus_custno <> " . CONFIG_SALES_STOCK_CUSTOMERID . " AND	renewalStatus  <> 'D'";
        $db->query($queryString);
        while ($db->next_record()) {
            $validContracts [$db->Record ['cui_cuino']] = 0; // initialise to no activity
        }
//        $dbUpdate = new dbSweetcode (); // database connection for update query
//        $dbeCallActivity = new DBECallActivity ($this); // for update of status
        /*
        Bring out a list of PrePay Service Requests to be included in the statement run
        */
        $queryString = "SELECT
        pro_problemno,
        pro_custno AS custno,
        DATE_FORMAT(pro_date_raised, '%d/%m/%Y') AS requestDate,
        cus_name,
        customer.gscTopUpAmount,
        cui_cuino,
        ity_desc,
        cui_expiry_date,
        curGSCBalance,
       callactivity.caa_callactivityno as activityId,
       caa_starttime,
       caa_endtime
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
          
      WHERE itm_itemno = " . $this->dsHeader->getValue(
                DBEHeader::gscItemID
            ) .              // Activity logged against PrePay contract
            " AND (DATE(pro_fixed_date) <= '" . $this->dsData->getValue(
                self::exportDataSetEndDate
            ) . "' or pro_fixed_date is null) " .   // Request was raised before run date
            " AND cui_desp_date <= '" . $this->dsData->getValue(
                self::exportDataSetEndDate
            ) . "'" .     // Contract had started before run date
            " AND cui_expiry_date >= NOW() " .                                      // Contract not expired
            " AND pro_custno <> " . CONFIG_SALES_STOCK_CUSTOMERID .                 // Not CNC sales stock customer
            " AND renewalStatus <> 'D' " .                                          // Contract renewal not declined
            " AND caa_callacttypeno NOT IN( " .                                     // Activity type not engineer travel or proactive
            CONFIG_ENGINEER_TRAVEL_ACTIVITY_TYPE_ID . "," . CONFIG_PROACTIVE_SUPPORT_ACTIVITY_TYPE_ID . ")" . " AND pro_status = 'C'" .                                                // Service Request completed
            " AND caa_status = 'C'" .                                                // Activity completed
            " AND
          ( caa_starttime <> caa_endtime OR curValue <> 0 )" .                   // time was logged or this is a value (e.g. topUp)
            " GROUP BY pro_problemno 
      ORDER BY pro_custno, pro_problemno, pro_date_raised";
        $db->query($queryString);
        $ret       = FALSE; // indicates there were no statements to export
        $buContact = new BUContact ($this);
        // ensure all customers have at least one statement contact
        $last_custno    = '9999';
        $htmlFileHandle = null;
        $buExpense      = new BUExpense($this);
        while ($db->next_record()) {
            if ($db->Record ['custno'] != $last_custno) {
                if ($last_custno != '9999') {
                    $dsStatementContact = new DataSet($this);
                    $buContact->getGSCContactByCustomerID($db->Record ['custno'], $dsStatementContact);
                    if (!$dsStatementContact->rowCount()) {
                        $this->raiseError(
                            'Customer ' . $db->Record ['cus_name'] . ' needs at least one Pre-pay statement contact.'
                        );
                        exit ();
                    }
                }
            }
            $last_custno = $db->Record ['custno'];
        }
        $db->query($queryString);
        $last_custno    = '9999';
        $filepath       = null;
        $date           = DateTime::createFromFormat(
            DATE_MYSQL_DATE,
            $this->dsData->getValue(self::exportDataSetEndDate)
        );
        $csvFileName    = SAGE_EXPORT_DIR . '/PrePayOOH' . (new DateTime())->format('d-m-Y') . '.csv';
        $csvFileHandler = fopen($csvFileName, "w");
        fputcsv(
            $csvFileHandler,
            ["customerName", "serviceRequestId", "activityId", "date", "startTime", "endTime", "overtime"]
        );
        fclose($csvFileHandler);
        while ($db->next_record()) {

            $validContracts [$db->Record ['cui_cuino']] = 1; // flag contract as having activity
            $ret                                        = TRUE; // there was at least one statement to export
            // new customer so create new html file
            if ($db->Record ['custno'] != $last_custno) {

                if ($last_custno != '9999') {

                    $topUpValue = $this->doTopUp($lastRecord);
                    $newBalance = $lastRecord ['curGSCBalance'] + $this->totalCost;
                    $this->template->set_var(
                        array(
                            'totalCost'        => common_numberFormat($this->totalCost),
                            'previousBalance'  => common_numberFormat($lastRecord ['curGSCBalance']),
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
                        $topUpValue,
                        $date->format(DATE_MYSQL_DATE)
                    );
                    $dsStatementContact->initialise();
                    if ($this->updateFlag) {
                        $fileName = $filepath . '.html';
                        $this->sendStatement(
                            $fileName,
                            $last_custno,
                            $dsStatementContact,
                            $newBalance,
                            $this->dsData->getValue(self::exportDataSetEndDate),
                            $topUpValue
                        );

                    }

                } // end if ( $last_custno != '9999' )
                $this->totalCost = 0; // reset cost
                $filepath        = SAGE_EXPORT_DIR . '/PP_' . substr(
                        $db->Record ['cus_name'],
                        0,
                        20
                    ) . $date->format('Y-m-d');
                $htmlFileHandle  = fopen($filepath . '.html', 'wb');
                if (!$htmlFileHandle) {
                    print_r(error_get_last());
                    $this->raiseError("Unable to open html file " . $filepath);
                }
                // set up new html file template
                $this->template = new Template ($GLOBALS ["cfg"] ["path_templates"], "remove");
                $this->template->set_file('page', 'PrepayReport.inc.html');
                // get GSC contact record
                $buContact->getGSCContactByCustomerID($db->Record ['custno'], $dsStatementContact);
                $dsSite = new DataSet($this);
                $buContact->getGSCContactByCustomerID($db->Record ['custno'], $dsStatementContact);
                if (!$dsStatementContact->rowCount()) {
                    $this->raiseError(
                        'Customer ' . $db->Record ['cus_name'] . ' needs at least one Pre-pay statement contact.'
                    );
                    exit ();
                }
                $this->buCustomer->getSiteByCustomerIDSiteNo(
                    $dsStatementContact->getValue(DBEContact::customerID),
                    $dsStatementContact->getValue(DBEContact::siteNo),
                    $dsSite
                );
                // Set header fields
                $this->template->set_var(
                    array(
                        'companyName'   => $db->Record ['cus_name'],
                        'customerRef'   => $db->Record ['cui_cuino'],
                        'statementDate' => Controller::dateYMDtoDMY(
                            $this->dsData->getValue(self::exportDataSetEndDate)
                        ),
                        'add1'          => $dsSite->getValue(DBESite::add1),
                        'add2'          => $dsSite->getValue(DBESite::add2),
                        'add3'          => $dsSite->getValue(DBESite::add3),
                        'town'          => $dsSite->getValue(DBESite::town),
                        'county'        => $dsSite->getValue(DBESite::county),
                        'postcode'      => $dsSite->getValue(DBESite::postcode),
                        'cnc_name'      => $this->dsHeader->getValue(DBEHeader::name),
                        'cnc_add1'      => $this->dsHeader->getValue(DBEHeader::add1),
                        'cnc_add2'      => $this->dsHeader->getValue(DBEHeader::add2),
                        'cnc_add3'      => $this->dsHeader->getValue(DBEHeader::add3),
                        'cnc_town'      => $this->dsHeader->getValue(DBEHeader::town),
                        'cnc_county'    => $this->dsHeader->getValue(DBEHeader::county),
                        'cnc_postcode'  => $this->dsHeader->getValue(DBEHeader::postcode),
                        'cnc_phone'     => $this->dsHeader->getValue(DBEHeader::phone)
                    )
                );
                $this->template->set_block('page', 'lineBlock', 'lines');
                $last_custno = $db->Record ['custno'];
                $ret         = TRUE; // indicates there were statements to export
            }
            $lastRecord = $db->Record;
            $this->getActivitiesByServiceRequest($db->Record);
        }
        //close file
        if ($ret == TRUE) {
            $topUpValue = $this->doTopUp($lastRecord);
            $newBalance = $lastRecord ['curGSCBalance'] + $this->totalCost;
            $this->template->set_var(
                array(
                    'totalCost'        => common_numberFormat($this->totalCost),
                    'previousBalance'  => common_numberFormat($lastRecord ['curGSCBalance']),
                    'remainingBalance' => common_numberFormat($newBalance)
                )
            );
            $this->template->parse('output', 'page', true);
            fwrite($htmlFileHandle, $this->template->get_var('output'));
            fclose($htmlFileHandle);
            $this->postRowToSummaryFile(
                $lastRecord,
                $dsResults,
                $dsStatementContact,
                $newBalance,
                $topUpValue,
                $date->format(DATE_MYSQL_DATE)
            );
            if ($this->updateFlag) {
                $dsStatementContact->initialise();
                $this->sendStatement(
                    $filepath . '.html',
                    $last_custno,
                    $dsStatementContact,
                    $newBalance,
                    $this->dsData->getValue(self::exportDataSetEndDate),
                    $topUpValue
                );
            }
        }
        /*
    Now produce statements for contracts that had no activity
*/
        $this->totalCost = 0; // there is no balance of activity cost
        reset($validContracts);
        foreach ($validContracts as $key => $value) {
            if ($value == 0) {

                $ret         = true;
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
                $db->query($queryString);
                $db->next_record();
                // get GSC contact record
                $buContact->getGSCContactByCustomerID($db->Record ['custno'], $dsStatementContact);
                if (!$dsStatementContact->rowCount()) {
                    $this->raiseError(
                        'Customer ' . $db->Record ['cus_name'] . ' needs at least one Pre-pay statement contact.'
                    );
                    exit ();
                }
                $this->buCustomer->getSiteByCustomerIDSiteNo(
                    $dsStatementContact->getValue(DBEContact::customerID),
                    $dsStatementContact->getValue(DBEContact::siteNo),
                    $dsSite
                );
                // set up new html file template
                $filepath       = SAGE_EXPORT_DIR . '/PP_' . substr(
                        $db->Record ['cus_name'],
                        0,
                        20
                    ) . $date->format('Y-m-d');
                $htmlFileHandle = fopen($filepath . '.html', 'wb');
                if (!$htmlFileHandle) {
                    print_r(error_get_last());
                    $this->raiseError("Unable to open html file " . $filepath);
                }
                $this->template = new Template ($GLOBALS ["cfg"] ["path_templates"], "remove");
                $this->template->set_file('page', 'PrepayReport.inc.html');
                // Set header fields
                $this->template->set_var(
                    array(
                        'companyName'   => $db->Record ['cus_name'],
                        'customerRef'   => $key,
                        'startDate'     => Controller::dateYMDtoDMY($db->Record ['cui_desp_date']),
                        'endDate'       => Controller::dateYMDtoDMY($db->Record ['cui_expiry_date']),
                        'statementDate' => Controller::dateYMDtoDMY(
                            $this->dsData->getValue(self::exportDataSetEndDate)
                        ),
                        'add1'          => $dsSite->getValue(DBESite::add1),
                        'add2'          => $dsSite->getValue(DBESite::add2),
                        'add3'          => $dsSite->getValue(DBESite::add3),
                        'town'          => $dsSite->getValue(DBESite::town),
                        'county'        => $dsSite->getValue(DBESite::county),
                        'postcode'      => $dsSite->getValue(DBESite::postcode),
                        'cnc_name'      => $this->dsHeader->getValue(DBEHeader::name),
                        'cnc_add1'      => $this->dsHeader->getValue(DBEHeader::add1),
                        'cnc_add2'      => $this->dsHeader->getValue(DBEHeader::add2),
                        'cnc_add3'      => $this->dsHeader->getValue(DBEHeader::add3),
                        'cnc_town'      => $this->dsHeader->getValue(DBEHeader::town),
                        'cnc_county'    => $this->dsHeader->getValue(DBEHeader::county),
                        'cnc_postcode'  => $this->dsHeader->getValue(DBEHeader::postcode),
                        'cnc_phone'     => $this->dsHeader->getValue(DBEHeader::phone)
                    )
                );
                $this->template->set_block('page', 'lineBlock', 'lines');
                $this->template->set_var(
                    array(
                        'requestDate'            => '',
                        'requestRef'             => '',
                        'requestHours'           => '',
                        'requestValue'           => '',
                        'requestCustomerContact' => '',
                        'requestDetails'         => 'No service requests logged in this period'
                    )
                );
                $this->template->parse('lines', 'lineBlock', true);
                $this->totalCost += $value;
                $this->template->set_var(
                    array(
                        'totalCost'        => 0,
                        'previousBalance'  => common_numberFormat($db->Record ['curGSCBalance']),
                        'remainingBalance' => common_numberFormat($db->Record ['curGSCBalance'])
                    )
                );
                $this->template->parse('output', 'page', true);
                fwrite($htmlFileHandle, $this->template->get_var('output'));
                fclose($htmlFileHandle);
                $dsStatementContact->initialise();
                $topUpValue = $this->doTopUp($db->Record);
                $this->postRowToSummaryFile(
                    $db->Record,
                    $dsResults,
                    $dsStatementContact,
                    $db->Record ['curGSCBalance'],
                    $topUpValue,
                    $date->format(DATE_MYSQL_DATE)
                );
                if ($this->updateFlag) {
                    $this->sendStatement(
                        $filepath . '.html',
                        $db->Record['cus_custno'],
                        $dsStatementContact,
                        $db->Record ['curGSCBalance'],
                        $this->dsData->getValue(self::exportDataSetEndDate),
                        $topUpValue
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

    function doTopUp(&$Record)
    {
        $newBalance = $Record ['curGSCBalance'] + $this->totalCost;
        // generate top-up call and activity if required
        if ($this->updateFlag) {
            $dbeCustomerItem = new DBECustomerItem ($this);
            $dbeCustomerItem->getRow($Record ['cui_cuino']);
            $dbeCustomerItem->setValue(DBECustomerItem::curGSCBalance, $newBalance);
            $dbeCustomerItem->updateRow();
        }
        if ($newBalance >= 100) {
            return 0;
        }
        if ($newBalance < 0) {
            // value of the top-up activity is the GSC item price plus amount required to clear balance
            $topUpValue = (0 - $newBalance) + $Record ['gscTopUpAmount'];
        } else {
            $topUpValue = $Record ['gscTopUpAmount']; // just the top-up amount
        }
        // 	Create sales order
        if ($this->updateFlag) {
            $this->createTopUpSalesOrder($Record, $topUpValue);
        }
        return $topUpValue;
    }

    /*
          work out whether a top-up is required and if so then generate one
          We generate a top-up T&M call so that this can later be amended and/or checked and used to generate a sales
          order for the top-up amount.
          This call will now appear on
      */
    function createTopUpSalesOrder(&$Record, $topUpValue)
    {
        $this->setMethodName('createTopUpSalesOrder');
        $this->buCustomer->getCustomerByID($Record ['custno'], $dsCustomer);
        // create sales order header with correct field values
        $buSalesOrder = new BUSalesOrder ($this);
        $dsOrdhead    = new DataSet($this);
        $buSalesOrder->initialiseOrder($dsOrdhead, $dbeOrdline, $dsCustomer);
        $dsOrdhead->setUpdateModeUpdate();
        $dsOrdhead->setvalue(DBEJOrdhead::custPORef, 'Top Up');
        $dsOrdhead->setvalue(DBEJOrdhead::addItem, 'N');
        $dsOrdhead->setvalue(DBEJOrdhead::partInvoice, 'N');
        $dsOrdhead->setvalue(DBEJOrdhead::paymentTermsID, CONFIG_PAYMENT_TERMS_30_DAYS);
        $dsOrdhead->post();
        $buSalesOrder->updateHeader(
            $dsOrdhead->getValue(DBEJOrdhead::ordheadID),
            $dsOrdhead->getValue(DBEJOrdhead::custPORef),
            $dsOrdhead->getValue(DBEJOrdhead::paymentTermsID),
            $dsOrdhead->getValue(DBEJOrdhead::partInvoice),
            $dsOrdhead->getValue(DBEJOrdhead::addItem)
        );
        $ordheadID  = $dsOrdhead->getValue(DBEJOrdhead::ordheadID);
        $sequenceNo = 1;
        // get topUp item details
        $dbeItem = new DBEItem ($this);
        $dbeItem->getRow(CONFIG_DEF_PREPAY_TOPUP_ITEMID);
        // create order line
        $dbeOrdline = new DBEOrdline ($this);
        $dbeOrdline->setValue(DBEJOrdline::ordheadID, $ordheadID);
        $dbeOrdline->setValue(DBEJOrdline::sequenceNo, $sequenceNo);
        $dbeOrdline->setValue(DBEJOrdline::customerID, $Record ['custno']);
        $dbeOrdline->setValue(DBEJOrdline::qtyDespatched, 0);
        $dbeOrdline->setValue(DBEJOrdline::qtyLastDespatched, 0);
        $dbeOrdline->setValue(DBEJOrdline::supplierID, CONFIG_SALES_STOCK_SUPPLIERID);
        $dbeOrdline->setValue(DBEJOrdline::lineType, 'I');
        $dbeOrdline->setValue(DBEJOrdline::sequenceNo, $sequenceNo);
        $dbeOrdline->setValue(DBEJOrdline::stockcat, 'R');
        $dbeOrdline->setValue(DBEJOrdline::itemID, CONFIG_DEF_PREPAY_TOPUP_ITEMID);
        $dbeOrdline->setValue(DBEJOrdline::qtyOrdered, 1);
        $dbeOrdline->setValue(DBEJOrdline::curUnitCost, 0);
        $dbeOrdline->setValue(DBEJOrdline::curTotalCost, 0);
        $dbeOrdline->setValue(DBEJOrdline::curUnitSale, $topUpValue);
        $dbeOrdline->setValue(DBEJOrdline::curTotalSale, $topUpValue);
        $dbeOrdline->setValue(DBEJOrdline::description, $dbeItem->getValue(DBEItem::description));
        $dbeOrdline->insertRow();
        return $dsOrdhead->getValue(DBEJOrdhead::ordheadID);
    }

    /**
     * @param $Record
     * @param DataSet $dsResults
     * @param DataSet|DBEContact $dsStatementContact
     * @param $newBalance
     * @param $topUpAmount
     * @param $endDate
     */
    function postRowToSummaryFile(&$Record, &$dsResults, &$dsStatementContact, $newBalance, $topUpAmount, $endDate)
    {
        $contacts = '';
        while ($dsStatementContact->fetchNext()) {
            $contacts .= $dsStatementContact->getValue(DBEContact::firstName) . ' ' . $dsStatementContact->getValue(
                    DBEContact::lastName
                );
        }
        $webFileLink = 'export/PP_' . substr($Record ['cus_name'], 0, 20) . $endDate . '.html';
        $dsResults->setUpdateModeInsert();
        $dsResults->setValue(self::exportPrePayCustomerName, $Record['cus_name']);
        $dsResults->setValue(self::exportPrePayPreviousBalance, $Record ['curGSCBalance']);
        $dsResults->setValue(self::exportPrePayCurrentBalance, common_numberFormat($newBalance));
        $dsResults->setValue(self::exportPrePayExpiryDate, Controller::dateYMDtoDMY($Record ['cui_expiry_date']));
        $dsResults->setValue(self::exportPrePayTopUp, common_numberFormat($topUpAmount));
        $dsResults->setValue(self::exportPrePayContacts, $contacts);
        $dsResults->setValue(self::exportPrePayContractType, $Record ['ity_desc']);
        $dsResults->setValue(self::exportPrePayWebFileLink, $webFileLink);
        $dsResults->post();
    }    // end function

    /**
     * @param $statementFilepath
     * @param $custno
     * @param DataSet|DBEContact $dsContact
     * @param $balance
     * @param $date
     * @param $topUpValue
     */
    function sendStatement($statementFilepath, $custno, &$dsContact, $balance, $date, $topUpValue)
    {
        $buMail      = new BUMail($this);
        $senderEmail = CONFIG_SALES_EMAIL;
        $subject     = 'Pre-Pay Contract Statement: ' . Controller::dateYMDtoDMY($date);
        $attachments = new AttachmentCollection();
        $attachments->add($statementFilepath, 'text/html', null, true);
        while ($dsContact->fetchNext()) {
            global $twig;
            $body    = $twig->render(
                '@customerFacing/PrePayInformation/PrePayInformation.html.twig',
                [
                    "contactFirstName" => $dsContact->getValue(DBEContact::firstName),
                    "balance"          => $balance,
                    "topUpAmount"      => $topUpValue * (1 + ($this->standardVatRate / 100))
                ]
            );
            $toEmail = "{$dsContact->getValue(DBEContact::firstName)} {$dsContact->getValue(DBEContact::lastName)}<{$dsContact->getValue(DBEContact::email)}>";
            $buMail->sendEmailWithAttachments($body, $subject, $toEmail, $attachments, $senderEmail);
        }
        $this->persistPrePayStatement($statementFilepath, $custno, $balance);
    }

    function persistPrePayStatement($filename, $custno, $balance)
    {
        $db         = $GLOBALS['db'];
        $fileString = mysqli_real_escape_string($db->link_id(), file_get_contents($filename));
        $sql        = "INSERT INTO
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

    /*
        Create sales order for top-up
    */
    function getActivitiesByServiceRequest($serviceRequestRecord)
    {

        $db          = new dbSweetcode (); // database connection for query
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
          con_last_name,
          cus_name,
          pro_problemno
        FROM
          callactivity
          JOIN problem ON pro_problemno = caa_problemno
          JOIN callacttype ON cat_callacttypeno=caa_callacttypeno
          JOIN custitem ON pro_contract_cuino = cui_cuino
          JOIN item ON cui_itemno = itm_itemno
          JOIN itemtype ON ity_itemtypeno = itm_itemtypeno
          JOIN contact ON caa_contno = con_contno
          join customer on problem.pro_custno = customer.cus_custno
        WHERE
          caa_problemno = " . $serviceRequestRecord['pro_problemno'] . " AND itm_itemno = " . $this->dsHeader->getValue(
                DBEHeader::gscItemID
            ) . " AND caa_endtime IS NOT NULL and caa_endtime <> ''
          AND caa_status = 'C'
          AND caa_callacttypeno NOT IN( " .                                     // Activity type not engineer travel or proactive
            CONFIG_ENGINEER_TRAVEL_ACTIVITY_TYPE_ID . "," . CONFIG_PROACTIVE_SUPPORT_ACTIVITY_TYPE_ID . ")
        ORDER BY caa_date, caa_starttime";
        $db->query($queryString);
        $firstActivity            = true;
        $requestValue             = 0;
        $requestHours             = 0;
        $dbeCallActivity          = null;
        $reason                   = null;
        $customerContact          = null;
        $prepayOvertimeActivities = [];
        $buExpense                = new BUExpense($this);
        while ($db->next_record()) {

            $overtime = $buExpense->calculateOvertime($db->Record['caa_callactivityno']);
            if ($overtime) {
                $prepayOvertimeActivities[] = [
                    "customerName"     => $db->Record['cus_name'],
                    "serviceRequestId" => $db->Record['pro_problemno'],
                    "activityId"       => $db->Record['caa_callactivityno'],
                    "date"             => DateTime::createFromFormat(DATE_MYSQL_DATE, $db->Record['caa_date'])->format(
                        'd/m/Y'
                    ),
                    "startTime"        => $db->Record['caa_starttime'],
                    "endTime"          => $db->Record['caa_endtime'],
                    "overtime"         => round($overtime, 1)
                ];
            }
            if ($db->Record ['curValueFlag'] == 'Y') { // This is a monetary value activity such as top-up or adjustment
                $requestValue += $db->Record ['curValue'];

            } else {
                /*
                If this is travel then apply maximum hours to customer site
                */
                if ($db->Record ['travelFlag'] == 'Y') {
                    $dsSite = new DataSet($this);
                    $this->buCustomer->getSiteByCustomerIDSiteNo(
                        $serviceRequestRecord['custno'],
                        $db->Record['caa_siteno'],
                        $dsSite
                    );
                    $max_hours = $dsSite->getValue(DBESite::maxTravelHours);

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
                    $this->dsHeader,
                    $normalHours,
                    $beforeHours,
                    $afterHours,
                    $overtimeRate,
                    $normalRate
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
                $reason          = substr(strip_tags($db->Record ['reason']), 0, 130);
                $reason          = str_replace("\r\n", "", $reason);
                $reason          = str_replace("\"", "", $reason);
                $customerContact = trim($db->Record ['con_first_name']) . ' ' . trim($db->Record ['con_last_name']);
                $firstActivity   = false;
            }
            if ($this->updateFlag) {
                if (!$dbeCallActivity) {
                    $dbeCallActivity = new DBECallActivity($this);
                }
                // update status on call activity to Authorised and statement date to today
                $dbeCallActivity->getRow($db->Record ['caa_callactivityno']);
                $dbeCallActivity->setValue(DBECallActivity::status, 'A');
                $dbeCallActivity->setValue(DBECallActivity::statementYearMonth, date('Y-m'));
                $dbeCallActivity->updateRow();
            }

        } // end while $db->next_record
        if (count($prepayOvertimeActivities)) {
            $csvFileName    = SAGE_EXPORT_DIR . '/PrePayOOH' . (new DateTime())->format('d-m-Y') . '.csv';
            $csvFileHandler = fopen($csvFileName, "a");
            foreach ($prepayOvertimeActivities as $prepayOvertimeActivity) {
                fputcsv($csvFileHandler, array_values($prepayOvertimeActivity));
            }
            fclose($csvFileHandler);
        }
        $this->postRowToPrePayExportFile(
            $serviceRequestRecord,
            $reason,
            $customerContact,
            $requestHours,
            $requestValue
        );


    }

    function postRowToPrePayExportFile(&$serviceRequestRecord,
                                       $reason,
                                       $customerContact,
                                       $requestHours,
                                       $requestValue
    )
    {


        $this->template->set_var(
            array(
                'requestDate'            => $serviceRequestRecord ['requestDate'],
                'requestDetails'         => $reason,
                'requestRef'             => $serviceRequestRecord['pro_problemno'],
                'requestHours'           => common_numberFormat($requestHours),
                'requestCustomerContact' => $customerContact,
                'requestValue'           => common_numberFormat($requestValue)
            )
        );
        $this->template->parse('lines', 'lineBlock', true);
        $this->totalCost += $requestValue;
    }

    function generateOvertimePrepayCSV(DateTimeInterface $date)
    {
        $query = "SELECT
  caa_date as dateSubmitted,
  DATE_FORMAT(caa_date, '%w') AS `weekday`,
  caa_callactivityno as activityId,
  caa_problemno as serviceRequestId,
  time_to_sec(caa_starttime) as activityStartTimeSeconds,
  time_to_sec(caa_endtime) as activityEndTimeSeconds,
  consultant.cns_name as staffName,
  consultant.cns_helpdesk_flag = 'Y' as helpdeskUser,
  time_to_sec(overtimeStartTime) as overtimeStartSeconds,
  time_to_sec(overtimeStartTime) as overtimeEndSeconds,
  consultant.`cns_consno` AS userId,
  project.`description` AS projectDescription,
  project.`projectID` AS projectId,
  approver.cns_name as approverName,
  IF(
    callactivity.`overtimeApprovedBy` is not null,
    \"Approved\",
    IF(
      callactivity.`overtimeDeniedReason` is not null,
      \"Denied\",
      \"Pending\"
    )
  ) AS `status`,
  callactivity.`overtimeApprovedDate` as approvedDate,
       callactivity.caa_consno = ? as isSelf,
       ((SELECT
        1
      FROM
        consultant globalApprovers
      WHERE globalApprovers.globalExpenseApprover
        AND globalApprovers.cns_consno = ?) = 1 or consultant.`expenseApproverID` = ?) as isApprover
FROM
  callactivity
  JOIN problem
    ON pro_problemno = caa_problemno
  JOIN callacttype
    ON caa_callacttypeno = cat_callacttypeno
  JOIN customer
    ON pro_custno = cus_custno
  JOIN consultant
    ON caa_consno = cns_consno
  left join consultant approver
    ON approver.`cns_consno` = callactivity.`overtimeApprovedBy`
  join headert
    on headert.`headerID` = 1
  left join project
    on project.`projectID` = problem.`pro_projectno`
WHERE 
      (caa_status = 'C'
    OR caa_status = 'A')
  AND caa_ot_exp_flag = 'N'
  and submitAsOvertime
  AND (caa_endtime <> caa_starttime)
  AND callacttype.engineerOvertimeFlag = 'Y'
  AND (
    callactivity.`caa_consno` = ?
    OR consultant.`expenseApproverID` = ?
    OR (
      (SELECT
        1
      FROM
        consultant globalApprovers
      WHERE globalApprovers.globalExpenseApprover
        AND globalApprovers.cns_consno = ?) = 1
    )
  )";
    }

}
