<?php
/**
 * Customer Review Meeting Controller Class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerReviewMeeting.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerNew.inc.php');
require_once($cfg ['path_bu'] . '/BUContact.inc.php');
require_once($cfg ['path_bu'] . '/BUServiceDeskReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerSrAnalysisReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerItem.inc.php');
require_once($cfg ['path_bu'] . '/BUActivity.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTCustomerReviewMeeting extends CTCNC
{

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $this->buCustomerReviewMeeting = new BUCustomerReviewMeeting ($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {

            case 'generatePdf':
                $this->generatePdf();
                break;

            default:
                $this->search();
                break;
        }
    }

    function search()
    {
        global $cfg;

        $this->setMethodName('search');

        $dsSearchForm = new DSForm ($this);
        $dsResults = new DataSet ($this);

        $this->buCustomerReviewMeeting->initialiseSearchForm($dsSearchForm);
        $this->setTemplateFiles(array('CustomerReviewMeeting' => 'CustomerReviewMeeting.inc'));

        if (isset($_REQUEST ['searchForm'])) {

            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                /*
                generate default contents of edit box

                */
                $customerId = $dsSearchForm->getValue('customerID');
                $buCustomerItem = new BUCustomerItem($this);

                $buCustomer = new BUCustomer($this);

                $buActivity = new BUActivity($this);

                $buServiceDeskReport = new BUServiceDeskReport($this);

                $buCustomerSrAnalysisReport = new BUCustomerSrAnalysisReport($this);

                $buContact = new BUContact($this);

                /** @var DBECustomer $dsCustomer */
                $dsCustomer = null;

                $buCustomer->getCustomerByID($dsSearchForm->getValue('customerID'), $dsCustomer);


                $textTemplate = new Template ($GLOBALS ["cfg"] ["path_templates"], "remove");

                $textTemplate->set_file('page', 'CustomerReviewMeetingText.inc.html');

                $textTemplate->set_var(
                    array(
                        'customerName' => $dsCustomer->getValue('name'),
                        'meetingDate' => self::dateYMDtoDMY($dsSearchForm->getValue('meetingDate')),
                        'slaP1' => $dsCustomer->getValue('slaP1'),
                        'slaP2' => $dsCustomer->getValue('slaP2'),
                        'slaP3' => $dsCustomer->getValue('slaP3'),
                        'slaP4' => $dsCustomer->getValue('slaP4'),
                        'slaP5' => $dsCustomer->getValue('slaP5')
                    )
                );


                $buContact->getSupportContacts($dsSupportContact, $dsSearchForm->getValue('customerID'));

                $supportContacts = [];

                $duplicates = [];
                $userMap = [];

                while ($dsSupportContact->fetchNext()) {

                    $firstName = $dsSupportContact->getValue('firstName');
                    $lastName = $dsSupportContact->getValue('lastName');
                    $userId = $dsSupportContact->getValue('contactID');
                    $key = strtolower($firstName . $lastName);
                    if (isset($userMap[$key])) {

                        if (!isset($duplicates[$userMap[$key]['id']])) {
                            $duplicates[$userMap[$key]['id']] = $userMap[$key];
                        }

                        $duplicates[$userId] = [
                            "firstName" => $firstName,
                            "lastName" => $lastName,
                            "id" => $userId,
                            "customerId" => $customerId
                        ];
                    } else {
                        $userMap[$key] = [
                            "firstName" => $firstName,
                            "lastName" => $lastName,
                            "id" => $userId,
                            "customerId" => $customerId
                        ];
                    }


                    $supportContacts[] = [
                        "firstName" => $firstName,
                        "lastName" => $lastName
                    ];
                }

                if (count($duplicates)) {
                    // send email to sales@cnc-ltd.co.uk with the list of duplicates
                    $buMail = new BUMail($this);

                    $senderEmail = CONFIG_SUPPORT_EMAIL;

                    $senderName = 'CNC Support Department';

                    $toEmail = 'sales@cnc-ltd.co.uk';

                    $template = new Template($cfg["path_templates"], "remove");
                    $template->set_file('page', 'CustomerReviewMeetingContactDuplicates.html');

                    $template->set_var('customerName', $dsCustomer->getValue('name'));

                    $template->set_block('page', 'contactBlock', 'contacts');

                    foreach ($duplicates as $key => $row) {

                        $template->set_var(
                            array(
                                'contactID' => $row['id'],
                                'contactFirstName' => $row['firstName'],
                                'contactLastName' => $row['lastName'],

                            )
                        );

                        $template->parse('contacts', 'contactBlock', true);
                    }

                    $template->parse('output', 'page', true);

                    $body = $template->get_var('output');

                    $subject = 'Possible duplicated customer contacts';

                    $hdrs = array(
                        'From' => $senderEmail,
                        'Subject' => $subject,
                        'Date' => date("r"),
                        'Content-Type' => 'text/html; charset=UTF-8'
                    );

                    $buMail->mime->setHTMLBody($body);

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
                        $body,
                        true
                    );

                }
                // we need to know how many contacts are there
                $supportContactInfo = "";


                for ($i = 0; $i < count($supportContacts); $i += 4) {
                    $supportContactInfo .= "<tr>";

                    for ($j = 0; $j < 4; $j++) {
                        if (isset($supportContacts[$i + $j])) {
                            $supportContactInfo .= "<td>" . $supportContacts[$i + $j]['firstName'] . ' ' . $supportContacts[$i + $j]['lastName'] . "</td>";
                        }
                    }
                }

                $textTemplate->set_var("supportContactInfo", $supportContactInfo);

                $contractsTemplate = new Template ($GLOBALS ["cfg"] ["path_templates"], "remove");
                $contractsTemplate->set_file('contracts', 'CustomerReviewMeetingContractsSection.html');

                $contractsTemplate->set_var("serverContract", $this->getServerCareContractBody($customerId, count($supportContacts)));
                $contractsTemplate->set_var("serviceDeskContract", $this->getServiceDeskContractBody($customerId));
                $contractsTemplate->set_var('prepayContract', $this->getPrepayContractBody($customerId));

                $contractsTemplate->parse('output', 'contracts', true);

                $contractsBody = $contractsTemplate->get_var('output');

                $textTemplate->set_var('contracts', $contractsBody);

                $textTemplate->set_var('24HourFlag', $dsCustomer->getValue("support24HourFlag") == 'N' ? "Do you require 24x7 cover?" : null);

                $textTemplate->set_block('page', 'srStatsBlock', 'stats');

                $textTemplate->set_var('p1Incidents', $this->getP1IncidentsBody($customerId));

                $textTemplate->set_var('startersAndLeavers', $this->getStartersAndLeaversBody($customerId));


                $textTemplate->set_var('thirdPartyServerAccess', $this->getThirdPartyServerAccessBody($customerId));

                $results = $buCustomerSrAnalysisReport->getResultsByPeriodRange(
                    $dsSearchForm->getValue('customerID'),
                    $dsSearchForm->getValue('startYearMonth'),
                    $dsSearchForm->getValue('endYearMonth')
                );

                foreach ($results as $key => $row) {

                    $textTemplate->set_var(
                        array(
                            'monthName' => $row['monthName'],
                            'year' => $row['year'],
                            'period' => $row['period'],
                            'scP1to3Count' => $row['serverCareCount1And3'],
                            'scP1to3ResponseHours' => number_format($row['serverCareHoursResponded'], 1),
                            'scP4Count' => $row['serverCareCount4'],
                            'sdP1to3Count' => $row['serviceDeskCount1And3'] + $row['prepayCount1And3'],
                            'sdP1to3ResponseHours' => number_format($row['serviceDeskHoursResponded'] + $row['prepayHoursResponded'], 1),
                            'sdP4Count' => $row['serviceDeskCount4'] + $row['prepayCount4'],
                            'otherP1to3Count' => $row['otherCount1And3'],
                            'otherP1to3ResponseHours' => number_format($row['otherHoursResponded'], 1),
                            'otherP1to3FixHours' => number_format($row['otherHoursFix'], 1),
                            'otherP4Count' => $row['otherCount4'],
                            'totalP1to3Count' => $row['otherCount1And3'] + $row['serviceDeskCount1And3'] + $row['serverCareCount1And3'],
                            'totalP4Count' => $row['otherCount4'] + $row['serviceDeskCount4'] + $row['serverCareCount4']
                        )
                    );

                    $textTemplate->parse('stats', 'srStatsBlock', true);
                }
                /*
                End SR Performance Statistics
                */
                $textTemplate->set_block('page', 'serverBlock', 'servers');

                $buCustomerItem->getServersByCustomerID($dsSearchForm->getValue('customerID'), $dsServer);

                while ($dsServer->fetchNext()) {

                    if ($dsServer->getValue('sOrderDate') != '0000-00-00') {
                        $purchaseDate = self::dateYMDtoDMY($dsServer->getValue('sOrderDate'));
                    } else {
                        $purchaseDate = '';
                    }

                    $textTemplate->set_var(
                        array(
                            'itemDescription' => $dsServer->getValue('itemDescription'),
                            'serialNo' => $dsServer->getValue('serialNo'),
                            'serverName' => $dsServer->getValue('serverName'),
                            'purchaseDate' => $purchaseDate,
                        )
                    );

                    $textTemplate->parse('servers', 'serverBlock', true);

                } // end while

                $textTemplate->set_block('page', 'managementReviewBlock', 'reviews');

                $buActivity->getManagementReviewsInPeriod(
                    $dsSearchForm->getValue('customerID'),
                    $dsSearchForm->getValue('startYearMonth'),
                    $dsSearchForm->getValue('endYearMonth'),
                    $dsReviews
                );

                $itemNo = 0;

                while ($dsReviews->fetchNext()) {

                    $itemNo++;

                    $urlServiceRequest =
                        $this->buildLink(
                            'Activity.php',
                            array(
                                'action' => 'displayLastActivity',
                                'problemID' => $dsReviews->getValue('problemID')
                            )
                        );

                    $textTemplate->set_var(
                        array(
                            'reviewHeading' => 'Review Item ' . $itemNo . '. SR no ' . $dsReviews->getValue('problemID'),
                            'urlServiceRequest' => $urlServiceRequest,
                            'managementReviewText' => $dsReviews->getValue('managementReviewReason'),
                        )
                    );

                    $textTemplate->parse('reviews', 'managementReviewBlock', true);

                } // end while

                $buServiceDeskReport->setStartPeriod($dsSearchForm->getValue('startYearMonth'));
                $buServiceDeskReport->setEndPeriod($dsSearchForm->getValue('endYearMonth'));
                $buServiceDeskReport->customerID = $dsSearchForm->getValue('customerID');

                $srCountByUser = $buServiceDeskReport->getIncidentsGroupedByUser();

                $textTemplate->set_block('page', 'userBlock', 'users');

                while ($row = $srCountByUser->fetch_object()) {

                    $textTemplate->set_var(
                        array(
                            'srUserName' => $row->name,
                            'srCount' => $row->count
                        )
                    );

                    $textTemplate->parse('users', 'userBlock', true);
                }

                $srCountByRootCause = $buServiceDeskReport->getIncidentsGroupedByRootCause();

                $textTemplate->set_block('page', 'rootCauseBlock', 'rootCauses');

                while ($row = $srCountByRootCause->fetch_object()) {

                    $textTemplate->set_var(
                        array(
                            'srRootCauseDescription' => $row->rootCauseDescription,
                            'srCount' => $row->count
                        )
                    );

                    $textTemplate->parse('rootCauses', 'rootCauseBlock', true);

                }
                $buHeader = new BUHeader($this);
                $buHeader->getHeader($dsHeader);
                $textTemplate->set_var('customerReviewMeetingText', $dsHeader->getValue(DBEHeader::customerReviewMeetingText));

                $textTemplate->parse('output', 'page', true);

                $meetingText = $textTemplate->get_var('output');
            }

        } else {
            if ($_REQUEST['customerID']) {
                $dsSearchForm->setValue('customerID', $_REQUEST['customerID']);
                $dsSearchForm->setValue('startYearMonth', $_REQUEST['startYearMonth']);
                $dsSearchForm->setValue('endYearMonth', $_REQUEST['endYearMonth']);
                $dsSearchForm->setValue('meetingDate', $_REQUEST['meetingDateYmd']);
                $meetingText = $_REQUEST['meetingText'];
            }
        }

        $urlCustomerPopup = $this->buildLink(CTCNC_PAGE_CUSTOMER, array('action' => CTCNC_ACT_DISP_CUST_POPUP, 'htmlFmt' => CT_HTML_FMT_POPUP));

        $urlSubmit = $this->buildLink($_SERVER ['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));

        $urlGeneratePdf =
            $this->buildLink(
                $_SERVER ['PHP_SELF'],
                array(
                    'action' => 'generatePdf'
                )
            );

        $this->setPageTitle('Customer Review Meeting');

        if ($dsSearchForm->getValue('customerID') != 0) {
            $buCustomer = new BUCustomer ($this);
            $buCustomer->getCustomerByID($dsSearchForm->getValue('customerID'), $dsCustomer);
            $customerString = $dsCustomer->getValue('name');
        }

        $this->template->set_var(
            array(
                'customerID' => $dsSearchForm->getValue('customerID'),
                'customerIDMessage' => $dsSearchForm->getMessage('customerID'),
                'customerString' => $customerString,
                'startYearMonth' => $dsSearchForm->getValue('startYearMonth'),
                'startYearMonthMessage' => $dsSearchForm->getMessage('startYearMonth'),
                'endYearMonth' => $dsSearchForm->getValue('endYearMonth'),
                'endYearMonthMessage' => $dsSearchForm->getMessage('endYearMonth'),
                'meetingDate' => self::dateYMDtoDMY($dsSearchForm->getValue('meetingDate')),
                'meetingDateYmd' => $dsSearchForm->getValue('meetingDate'),
                'urlCustomerPopup' => $urlCustomerPopup,
                'meetingText' => $meetingText,
                'urlSubmit' => $urlSubmit,
                'urlGeneratePdf' => $urlGeneratePdf,
            )
        );

        $this->template->parse('CONTENTS', 'CustomerReviewMeeting', true);
        $this->parsePage();
    }

    private function getServerCareContractBody($customerId, $supportContactsCount)
    {
        $BUCustomerItem = new BUCustomerItem($this);
        /** @var DataSet $datasetContracts */
        $datasetContracts = null;
        $BUCustomerItem->getServerCareValidContractsByCustomerID($customerId, $datasetContracts);

        $serverCareContract = null;

        if (!$datasetContracts->rowCount()) {
            return $serverCareContractBody = "Server Care: None";
        }
        $datasetContracts->fetchNext();
        $serverCareItemID = $datasetContracts->getValue("customerItemID");
        $serverCareContractsTemplate = new Template ($GLOBALS ["cfg"] ["path_templates"], "remove");

        $serverCareContractsTemplate->set_file('serverCareContracts', 'CustomerReviewMeetingServerCare.html');
        $serverCareContractsTemplate->set_var("contractDescription", $datasetContracts->getValue('itemDescription'));
        $serverCareContractsTemplate->set_var("nextInvoice", $datasetContracts->getValue('invoiceFromDate') . " - " . $datasetContracts->getValue('invoiceToDate'));
        $serverCareContractsTemplate->set_var('usersCount', $supportContactsCount);

        $serverCareContractsTemplate->set_block('serverCareContracts', 'contractItemsBlock', 'items');
        /** @var DataSet $items */
        $items = null;

        $BUCustomerItem->getCustomerItemsByContractID($serverCareItemID, $items);

        while ($items->fetchNext()) {

            $description = $items->getValue('itemDescription');

            if ($items->getValue('serverName')) {
                $description .= '(' . $items->getValue('serverName') . ')';
            }

            $serverCareContractsTemplate->set_var(
                [
                    'name' => $description,
                    'serialNumber' => $items->getValue('serialNo'),
                ]
            );

            $serverCareContractsTemplate->parse('items', 'contractItemsBlock', true);
        }

        $serverCareContractsTemplate->parse('output', 'serverCareContracts', true);

        return $serverCareContractsTemplate->get_var('output');

    }

    /**
     * Create PDF reports and save to disk
     *
     */

    function generatePdf()
    {

        $this->buCustomerReviewMeeting->generateAgendaPdf(
            $_REQUEST['customerID'],
            $_REQUEST['meetingText'],
            $_REQUEST['meetingDateYmd']
        );

//        $this->buCustomerReviewMeeting->generateSalesPdf(
//            $_REQUEST['customerID'],
//            $_REQUEST['startYearMonth'],
//            $_REQUEST['endYearMonth'],
//            $_REQUEST['meetingDateYmd']
//        );

//        $this->search();  // redisplays text

    }

    private function getServiceDeskContractBody($customerId)
    {
        $BUCustomerItem = new BUCustomerItem($this);
        /** @var DataSet $datasetContracts */
        $datasetContracts = null;
        $BUCustomerItem->getServiceDeskValidContractsByCustomerID($customerId, $datasetContracts);

        if (!$datasetContracts->rowCount()) {
            return "User Support Contract: None";
        }
        $datasetContracts->fetchNext();
        $users = $datasetContracts->getValue('users');
        $description = $datasetContracts->getValue('itemDescription');
        $invoicePeriod = $datasetContracts->getValue('invoiceFromDate') . " - " . $datasetContracts->getValue('invoiceToDate');
        return "<p>User Support Contract: $description for $users users</p><p>next Invoice: $invoicePeriod</p>";
    }

    private function getPrepayContractBody($customerId)
    {
        $BUCustomerItem = new BUCustomerItem($this);
        /** @var DataSet $datasetContracts */
        $datasetContracts = null;
        $BUCustomerItem->getPrepayContractByCustomerID($customerId, $datasetContracts);
        if (!$datasetContracts->rowCount()) {
            return "Pre-Pay Contract: T&M User Support Only";
        }
        $datasetContracts->fetchNext();
        $invoicePeriod = $datasetContracts->getValue('invoiceFromDate') . " - " . $datasetContracts->getValue('invoiceToDate');
        return "<p>Pre-Pay Contract: Pre-Pay Contract</p><p>next Invoice: $invoicePeriod</p>";
    }

    private function getP1IncidentsBody($customerId)
    {
        $dbeProblem = new DBEJProblem($this);

        $dbeProblem->getP1byCustomerIdLast30Days($customerId);


        if (!$dbeProblem->rowCount()) {
            return "None";
        }

        $p1IncidentsTemplate = new Template ($GLOBALS ["cfg"] ["path_templates"], "remove");

        $p1IncidentsTemplate->set_file('p1Incidents', 'CustomerReviewMeetingP1Incidents.html');

        $p1IncidentsTemplate->set_block('p1Incidents', 'incidentsBlock', 'items');

        while ($dbeProblem->fetchNext()) {

            $dateRaised = $dbeProblem->getValue(DBEJProblem::dateRaised);
            $dateFixed = $dbeProblem->getValue(DBEJProblem::fixedDate);
            $slaResponse = $dbeProblem->getValue(DBEJProblem::slaResponseHours);
            $respondedHours = $dbeProblem->getValue(DBEJProblem::respondedHours);

            $p1IncidentsTemplate->set_var(
                [
                    "id" => $dbeProblem->getValue(DBEJProblem::problemID),
                    "summary" => $dbeProblem->getValue(DBEJProblem::reason),
                    "outcome" => $dbeProblem->getValue(DBEJProblem::lastReason),
                    "SLA" => $respondedHours > $slaResponse ? "Not Achieved" : "Achieved",
                ]
            );

            $p1IncidentsTemplate->parse('items', 'incidentsBlock', true);
        }

        $p1IncidentsTemplate->parse('output', 'p1Incidents', true);

        return $p1IncidentsTemplate->get_var('output');
    }

    private function getStartersAndLeaversBody($customerId)
    {
        $starterSR = new DBEJProblem($this);
        $starterSR->getStartersSRByCustomerIDLast12Months($customerId);

        $leaverSR = new DBEJProblem($this);
        $leaverSR->getLeaversSRByCustomerIDLast12Months($customerId);

        if (!$starterSR->rowCount() && !$leaverSR->rowCount()) {
            return "None";
        }

        $startersAndLeaversTemplate = new Template ($GLOBALS ["cfg"] ["path_templates"], "remove");

        $startersAndLeaversTemplate->set_file('startersAndLeavers', 'CustomerReviewMeetingStartersAndLeavers.html');

        $startersAndLeaversTemplate->set_block('startersAndLeavers', 'startersBlock', 'items');

        if (!$starterSR->rowCount()) {
            $startersAndLeaversTemplate->parse('items', 'startersBlock', true);
        } else {
            $startersAndLeaversTemplate->set_var('startersQty', $starterSR->rowCount());
            $workingHours = 0;
            while ($starterSR->fetchNext()) {
                $workingHours += $starterSR->getValue(DBEJProblem::workingHours);
            }
            $avgHours = $workingHours / $starterSR->rowCount();
            $startersAndLeaversTemplate->set_var('startersAvgHours', round($avgHours, 2));
            $startersAndLeaversTemplate->set_var('startersAvgMinutes', round($avgHours * 60, 2));

            $startersAndLeaversTemplate->parse('items', 'startersBlock', true);
        }

        $startersAndLeaversTemplate->set_block('startersAndLeavers', 'leaversBlock', 'leaversItems');

        if (!$leaverSR->rowCount()) {
            $startersAndLeaversTemplate->parse('leaversItems', 'leaversBlock', true);
        } else {
            $startersAndLeaversTemplate->set_var('leaversQty', $leaverSR->rowCount());
            $workingHours = 0;
            while ($leaverSR->fetchNext()) {
                $workingHours += $leaverSR->getValue(DBEJProblem::workingHours);
            }
            $avgHours = $workingHours / $leaverSR->rowCount();
            $startersAndLeaversTemplate->set_var('leaversAvgHours', round($avgHours, 2));
            $startersAndLeaversTemplate->set_var('leaversAvgMinutes', round($avgHours * 60, 2));

            $startersAndLeaversTemplate->parse('leaversItems', 'leaversBlock', true);
        }


        $startersAndLeaversTemplate->parse('output', 'startersAndLeavers', true);

        return $startersAndLeaversTemplate->get_var('output');
    }

    private function getThirdPartyServerAccessBody($customerId)
    {
        $BUCustomerItem = new BUCustomerItem($this);
        /** @var DataSet $datasetContracts */
        $datasetContracts = null;
        $BUCustomerItem->getServerCareValidContractsByCustomerID($customerId, $datasetContracts);

        $thirdPartyServerAccess = null;

        if ($datasetContracts->rowCount()) {
            $BUCustomerItem->getServerWatchContractByCustomerID($customerId, $datasetServerWatch);
            if (!$datasetServerWatch->rowCount()) {
                $thirdPartyServerAccess = "<h2>Third-Party Server Access";
            }

        }
        return $thirdPartyServerAccess;
    }

} // end of class
?>