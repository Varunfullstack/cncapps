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
require_once($cfg ['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg ['path_bu'] . '/BUContact.inc.php');
require_once($cfg ['path_bu'] . '/BUServiceDeskReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerSrAnalysisReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerItem.inc.php');
require_once($cfg ['path_bu'] . '/BUActivity.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTCustomerReviewMeeting extends CTCNC
{

    /** @var BUCustomerReviewMeeting */
    private $buCustomerReviewMeeting;

    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
                         $cfg
    )
    {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
        $roles = [
            "sales",
            "accounts",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buCustomerReviewMeeting = new BUCustomerReviewMeeting ($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {

            case 'generatePdf':
                echo json_encode($this->generatePdf());
                break;

            default:
                $this->search();
                break;
        }
    }

    /**
     * Create PDF reports and save to disk
     *
     * @throws Exception
     */

    function generatePdf()
    {

        $text = $this->getParam('html');

        $agendaTemplate = new Template (
            $GLOBALS ["cfg"] ["path_templates"],
            "remove"
        );

        $agendaTemplate->set_file(
            'page',
            'CustomerReviewMeetingAgendaDocument.inc.html'
        );


        $agendaTemplate->set_var(
            [
                'htmlBody' => $text,
                'URL'      => "http://" . $_SERVER['HTTP_HOST'] . '/images/test.png'
            ]
        );

        $agendaTemplate->parse(
            'output',
            'page',
            true
        );


        $html = $agendaTemplate->get_var('output');
        try {
            $this->buCustomerReviewMeeting->generateAgendaPdf(
                $this->getParam('customerID'),
                $html,
                $this->getParam('meetingDateYmd')
            );
        } catch (Exception $exception) {
            http_response_code(500);
            return ["status" => $exception->getMessage(), "description" => "Failed to generate files"];
        }

        $startDate = (DateTime::createFromFormat(
            "m/Y",
            $this->getParam('startYearMonth')
        ))->modify('first day of this month ');
        $endDate = (DateTime::createFromFormat(
            "m/Y",
            $this->getParam('endYearMonth')
        ))->modify('last day of this month');

        $this->buCustomerReviewMeeting->generateSalesPdf(
            $this->getParam('customerID'),
            $startDate,
            $endDate,
            $this->getParam('meetingDateYmd')
        );

        $this->buCustomerReviewMeeting->generateMeetingNotes(
            $this->getParam('customerID'),
            $this->getParam('meetingDateYmd')
        );

        return ["status" => "ok"];
    }

    /**
     * @throws Exception
     */
    function search()
    {
        $this->setMethodName('search');
        $dsSearchForm = new DSForm ($this);
        $this->buCustomerReviewMeeting->initialiseSearchForm($dsSearchForm);
        $this->setTemplateFiles(array('CustomerReviewMeeting' => 'CustomerReviewMeeting.inc'));
        $graphData = null;
        $editableText = null;
        $nonEditableText = null;
        if (isset($_REQUEST ['searchForm'])) {

            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                $startDate = (DateTime::createFromFormat(
                    "m/Y",
                    $dsSearchForm->getValue(BUCustomerReviewMeeting::searchFormStartYearMonth)
                ))->modify('first day of this month ');
                $endDate = (DateTime::createFromFormat(
                    "m/Y",
                    $dsSearchForm->getValue(BUCustomerReviewMeeting::searchFormEndYearMonth)
                ))->modify('last day of this month');

                $customerId = $dsSearchForm->getValue(BUCustomerReviewMeeting::searchFormCustomerID);
                $buCustomer = new BUCustomer($this);
                $buActivity = new BUActivity($this);

                $buServiceDeskReport = new BUServiceDeskReport($this);

                $buCustomerSrAnalysisReport = new BUCustomerSrAnalysisReport($this);

                $buContact = new BUContact($this);

                /** @var DBECustomer|DataSet $dsCustomer */
                $dsCustomer = null;

                $buCustomer->getCustomerByID(
                    $dsSearchForm->getValue(BUCustomerReviewMeeting::searchFormCustomerID),
                    $dsCustomer
                );


                $textTemplate = new Template (
                    $GLOBALS ["cfg"] ["path_templates"],
                    "remove"
                );

                $nonEditableTemplate = new Template (
                    $GLOBALS ["cfg"] ["path_templates"],
                    "remove"
                );

                $nonEditableTemplate->set_file(
                    'page',
                    'CustomerReviewMeetingNonEditable.html'
                );


                $textTemplate->set_file(
                    'page',
                    'CustomerReviewMeetingEditable.html'
                );


                $nonEditableTemplate->set_var(
                    array(
                        'customerName' => $dsCustomer->getValue(DBECustomer::name),
                        'meetingDate'  => self::dateYMDtoDMY(
                            $dsSearchForm->getValue(BUCustomerReviewMeeting::searchFormMeetingDate)
                        ),
                        'slaP1'        => $dsCustomer->getValue(DBECustomer::slaP1),
                        'slaP2'        => $dsCustomer->getValue(DBECustomer::slaP2),
                        'slaP3'        => $dsCustomer->getValue(DBECustomer::slaP3),
                        'slaP4'        => $dsCustomer->getValue(DBECustomer::slaP4),
                        'slaP5'        => $dsCustomer->getValue(DBECustomer::slaP5),
                        "waterMarkURL" => "http://" . $_SERVER['HTTP_HOST'] . '/images/CNC_watermarkActualSize.png'
                    )
                );

                $results = $buCustomerSrAnalysisReport->getResultsByPeriodRange(
                    $dsSearchForm->getValue(BUCustomerReviewMeeting::searchFormCustomerID),
                    $startDate,
                    $endDate
                );

                $supportedUsersData = $this->getSupportedUsersData(
                    $buContact,
                    $customerId,
                    $dsCustomer->getValue(DBECustomer::name)
                );

                $nonEditableTemplate->set_var(
                    [
                        "supportContactInfo" => $supportedUsersData['data'],
                        "supportUsersCount"  => $supportedUsersData['count']
                    ]
                );

                $contractsTemplate = new Template (
                    $GLOBALS ["cfg"] ["path_templates"],
                    "remove"
                );
                $contractsTemplate->set_file(
                    'contracts',
                    'CustomerReviewMeetingContractsSection.html'
                );

                $contractsTemplate->set_var(
                    "serverContract",
                    $this->getServerCareContractBody(
                        $customerId,
                        $supportedUsersData['count']
                    )
                );
                $contractsTemplate->set_var(
                    "serviceDeskContract",
                    $this->getServiceDeskContractBody($customerId)
                );
                $contractsTemplate->set_var(
                    'prepayContract',
                    $this->getPrepayContractBody($customerId)
                );

                $contractsTemplate->set_var(
                    '24HourFlag',
                    $dsCustomer->getValue(DBECustomer::support24HourFlag) == 'N' ? "Do you require 24x7 cover?" : null
                );

                $contractsTemplate->parse(
                    'output',
                    'contracts',
                    true
                );

                $contractsBody = $contractsTemplate->get_var('output');

                $textTemplate->set_var(
                    'contracts',
                    $contractsBody
                );

                $textTemplate->set_var(
                    'p1Incidents',
                    $this->getP1IncidentsBody($customerId)
                );
                $textTemplate->set_var(
                    'startersAndLeavers',
                    $this->getStartersAndLeaversBody(
                        $customerId,
                        $startDate,
                        $endDate
                    )
                );
                $textTemplate->set_var(
                    'thirdPartyServerAccess',
                    $this->getThirdPartyServerAccessBody($customerId)
                );
                $textTemplate->set_var(
                    'reviewMeetingFrequency',
                    $this->getReviewMeetingFrequencyBody($dsCustomer)
                );
                $textTemplate->set_block(
                    'page',
                    'managementReviewBlock',
                    'reviews'
                );
                $dsReviews = new DataSet($this);
                $buActivity->getManagementReviewsInPeriod(
                    $dsSearchForm->getValue(BUCustomerReviewMeeting::searchFormCustomerID),
                    $startDate,
                    $endDate,
                    $dsReviews
                );

                $itemNo = 0;

                while ($dsReviews->fetchNext()) {

                    $itemNo++;

                    $urlServiceRequest =
                        Controller::buildLink(
                            'Activity.php',
                            array(
                                'action'    => 'displayLastActivity',
                                'problemID' => $dsReviews->getValue(DBEProblem::problemID)
                            )
                        );

                    $textTemplate->set_var(
                        array(
                            'reviewHeading'        => 'Review Item ' . $itemNo . '. SR no ' . $dsReviews->getValue(
                                    DBEProblem::problemID
                                ),
                            'urlServiceRequest'    => SITE_URL . '/' . $urlServiceRequest,
                            'managementReviewText' => $dsReviews->getValue(DBEProblem::managementReviewReason),
                        )
                    );

                    $textTemplate->parse(
                        'reviews',
                        'managementReviewBlock',
                        true
                    );

                } // end while

                $buServiceDeskReport->setStartPeriod($startDate);
                $buServiceDeskReport->setEndPeriod($endDate);
                $buServiceDeskReport->customerID = $dsSearchForm->getValue(
                    BUCustomerReviewMeeting::searchFormCustomerID
                );

                $srCountByUser = $buServiceDeskReport->getIncidentsGroupedByUser();

                $nonEditableTemplate->set_block(
                    'page',
                    'userBlock',
                    'users'
                );

                while ($row = $srCountByUser->fetch_object()) {

                    $nonEditableTemplate->set_var(
                        array(
                            'srUserName'    => $row->name,
                            'srCount'       => $row->count,
                            'srHiddenCount' => $row->hiddenCount
                        )
                    );

                    $nonEditableTemplate->parse(
                        'users',
                        'userBlock',
                        true
                    );
                }

                $srCountByRootCause = $buServiceDeskReport->getIncidentsGroupedByRootCause();

                $nonEditableTemplate->set_block(
                    'page',
                    'rootCauseBlock',
                    'rootCauses'
                );

                while ($row = $srCountByRootCause->fetch_object()) {

                    $nonEditableTemplate->set_var(
                        array(
                            'srRootCauseDescription' => $row->rootCauseDescription,
                            'srCount'                => $row->count
                        )
                    );

                    $nonEditableTemplate->parse(
                        'rootCauses',
                        'rootCauseBlock',
                        true
                    );

                }


                $supportedUsersData = $this->getSupportedUsersLevelsCount(
                    $buContact,
                    $customerId,
                    $dsCustomer->getValue(DBECustomer::name)
                );


                $textTemplate->set_var(
                    'mainContacts',
                    $supportedUsersData['data']
                );

                $buHeader = new BUHeader($this);
                $dsHeader = new DataSet($this);
                $buHeader->getHeader($dsHeader);
                $textTemplate->set_var(
                    'customerReviewMeetingText',
                    $dsHeader->getValue(DBEHeader::customerReviewMeetingText)
                );

                $textTemplate->parse(
                    'output',
                    'page',
                    true
                );

                $nonEditableTemplate->parse(
                    'output',
                    'page',
                    true
                );

                $editableText = $textTemplate->get_var('output');

                $nonEditableText = $nonEditableTemplate->get_var('output');
                $graphData = $this->generateCharts(
                    $results,
                    $customerId
                );

            }

        } else {
            if ($this->getParam('customerID')) {
                $dsSearchForm->setValue(
                    BUCustomerReviewMeeting::searchFormCustomerID,
                    $this->getParam('customerID')
                );
                $dsSearchForm->setValue(
                    BUCustomerReviewMeeting::searchFormStartYearMonth,
                    $this->getParam('startYearMonth')
                );
                $dsSearchForm->setValue(
                    BUCustomerReviewMeeting::searchFormEndYearMonth,
                    $this->getParam('endYearMonth')
                );
                $dsSearchForm->setValue(
                    BUCustomerReviewMeeting::searchFormMeetingDate,
                    $this->getParam('meetingDateYmd')
                );
                $nonEditableText = $this->getParam('nonEditableText');
                $editableText = $this->getParam('editableText');

            }
        }

        $urlCustomerPopup = Controller::buildLink(
            CTCNC_PAGE_CUSTOMER,
            array(
                'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );

        $urlSubmit = Controller::buildLink(
            $_SERVER ['PHP_SELF'],
            array('action' => CTCNC_ACT_SEARCH)
        );

        $urlGeneratePdf =
            Controller::buildLink(
                $_SERVER ['PHP_SELF'],
                array(
                    'action' => 'generatePdf'
                )
            );

        $this->setPageTitle('Customer Review Meeting Agenda');
        $customerString = null;
        if ($dsSearchForm->getValue(BUCustomerReviewMeeting::searchFormCustomerID) != 0) {
            $buCustomer = new BUCustomer ($this);
            $buCustomer->getCustomerByID(
                $dsSearchForm->getValue(BUCustomerReviewMeeting::searchFormCustomerID),
                $dsCustomer
            );
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }

        echo "<script> let graphData = " . json_encode(
                $graphData,
                JSON_NUMERIC_CHECK
            ) . "</script>";

        $this->template->set_var(
            array(
                'customerID'            => $dsSearchForm->getValue(BUCustomerReviewMeeting::searchFormCustomerID),
                'customerIDMessage'     => $dsSearchForm->getMessage(BUCustomerReviewMeeting::searchFormCustomerID),
                'customerString'        => $customerString,
                'startYearMonth'        => $dsSearchForm->getValue(BUCustomerReviewMeeting::searchFormStartYearMonth),
                'startYearMonthMessage' => $dsSearchForm->getMessage(BUCustomerReviewMeeting::searchFormStartYearMonth),
                'endYearMonth'          => $dsSearchForm->getValue(BUCustomerReviewMeeting::searchFormEndYearMonth),
                'endYearMonthMessage'   => $dsSearchForm->getMessage(BUCustomerReviewMeeting::searchFormEndYearMonth),
                'meetingDate'           => self::dateYMDtoDMY(
                    $dsSearchForm->getValue(BUCustomerReviewMeeting::searchFormMeetingDate)
                ),
                'meetingDateYmd'        => $dsSearchForm->getValue(BUCustomerReviewMeeting::searchFormMeetingDate),
                'urlCustomerPopup'      => $urlCustomerPopup,
                'editableText'          => $editableText,
                'nonEditableText'       => $nonEditableText,
                'urlSubmit'             => $urlSubmit,
                'urlGeneratePdf'        => $urlGeneratePdf,
            )
        );

        $this->template->parse(
            'CONTENTS',
            'CustomerReviewMeeting',
            true
        );
        $this->parsePage();
    }

    private function getSupportedUsersData(BUContact $buContact,
                                           $customerId,
                                           $customerName
    )
    {
        /** @var DataSet $dsSupportContact */
        $dsSupportContact = null;
        $buContact->getSupportContacts(
            $dsSupportContact,
            $customerId
        );

        $supportContacts = [
            "main"       => [],
            "supervisor" => [],
            "support"    => [],
            "delegate"   => []
        ];

        $duplicates = [];
        $userMap = [];
        $count = 0;
        while ($dsSupportContact->fetchNext()) {

            $firstName = $dsSupportContact->getValue(DBEContact::firstName);
            $lastName = $dsSupportContact->getValue(DBEContact::lastName);
            $userId = $dsSupportContact->getValue(DBEContact::contactID);
            $key = strtolower($firstName . $lastName);
            if (isset($userMap[$key])) {

                if (!isset($duplicates[$userMap[$key]['id']])) {
                    $duplicates[$userMap[$key]['id']] = $userMap[$key];
                }

                $duplicates[$userId] = [
                    "firstName"  => $firstName,
                    "lastName"   => $lastName,
                    "id"         => $userId,
                    "customerId" => $customerId
                ];
            } else {
                $userMap[$key] = [
                    "firstName"  => $firstName,
                    "lastName"   => $lastName,
                    "id"         => $userId,
                    "customerId" => $customerId
                ];
            }


            $supportContacts[$dsSupportContact->getValue(DBEContact::supportLevel)][] = [
                "firstName" => $firstName,
                "lastName"  => $lastName
            ];
            $count++;
        }

        if (count($duplicates)) {
            // send email to sales@cnc-ltd.co.uk with the list of duplicates
            $buMail = new BUMail($this);

            $senderEmail = CONFIG_SUPPORT_EMAIL;

            $senderName = 'CNC Support Department';

            $toEmail = 'sales@cnc-ltd.co.uk';

            $template = new Template(
                $GLOBALS ["cfg"]["path_templates"],
                "remove"
            );
            $template->set_file(
                'page',
                'CustomerReviewMeetingContactDuplicates.html'
            );

            $template->set_var(
                'customerName',
                $customerName
            );

            $template->set_block(
                'page',
                'contactBlock',
                'contacts'
            );

            foreach ($duplicates as $key => $row) {

                $template->set_var(
                    array(
                        'contactID'        => $row['id'],
                        'contactFirstName' => $row['firstName'],
                        'contactLastName'  => $row['lastName'],

                    )
                );

                $template->parse(
                    'contacts',
                    'contactBlock',
                    true
                );
            }

            $template->parse(
                'output',
                'page',
                true
            );

            $body = $template->get_var('output');

            $subject = 'Possible duplicated customer contacts';

            $hdrs = array(
                'From'         => $senderEmail,
                'To'           => $toEmail,
                'Subject'      => $subject,
                'Date'         => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );

            $buMail->mime->setHTMLBody($body);

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

        }

        $sectionTemplate = '<div style="clear: both;margin-bottom: 22px"></div><div style="font-weight: bold; font-size: small;text-align: left; ">{type} Contacts ({count})</div>
    <br>
    <ul class="ul3">
        {contactData}
    </ul>';

        $toReturn = "";
        foreach ($supportContacts as $type => $data) {

            $contactsInfo = "";
            foreach ($data as $contact) {
                $contactsInfo .= "<li>" . $contact['firstName'] . ' ' . $contact['lastName'] . "</li>";
            }
            $currentSection = "" . $sectionTemplate;
            $currentSection = str_replace('{type}', ucfirst($type), $currentSection);
            $currentSection = str_replace('{count}', count($supportContacts[$type]), $currentSection);
            $currentSection = str_replace('{contactData}', $contactsInfo, $currentSection);
            $toReturn .= $currentSection;
        }

        return [
            "data"  => $toReturn,
            "count" => $count
        ];
    }

    private function getServerCareContractBody($customerId,
                                               $supportContactsCount
    )
    {
        $BUCustomerItem = new BUCustomerItem($this);
        /** @var DataSet $datasetContracts */
        $datasetContracts = null;
        $BUCustomerItem->getServerCareValidContractsByCustomerID(
            $customerId,
            $datasetContracts
        );

        $serverCareContract = null;

        if (!$datasetContracts->rowCount()) {
            return $serverCareContractBody = "Server Care: None";
        }
        $datasetContracts->fetchNext();
        $serverCareContractsTemplate = new Template (
            $GLOBALS ["cfg"] ["path_templates"],
            "remove"
        );

        $serverCareContractsTemplate->set_file(
            'serverCareContracts',
            'CustomerReviewMeetingServerCare.html'
        );
        $serverCareContractsTemplate->set_var(
            "contractDescription",
            $datasetContracts->getValue(DBEJCustomerItem::itemDescription)
        );
        $serverCareContractsTemplate->set_var(
            "nextInvoice",
            $datasetContracts->getValue(DBEJCustomerItem::invoiceFromDate) . " - " . $datasetContracts->getValue(
                DBEJCustomerItem::invoiceToDate
            )
        );
        $serverCareContractsTemplate->set_var(
            'usersCount',
            $supportContactsCount
        );

        $serverCareContractsTemplate->set_block(
            'serverCareContracts',
            'contractItemsBlock',
            'items'
        );
        /** @var DataSet $dsServer */
        $dsServer = null;

        $BUCustomerItem->getServersByCustomerID(
            $customerId,
            $dsServer
        );

        while ($dsServer->fetchNext()) {
            $purchaseDate = null;
            if ($dsServer->getValue(DBEJCustomerItem::sOrderDate)) {
                $purchaseDate = self::dateYMDtoDMY($dsServer->getValue(DBEJCustomerItem::sOrderDate));
            }

            $serverCareContractsTemplate->set_var(
                array(
                    'itemDescription' => $dsServer->getValue(DBEJCustomerItem::itemDescription),
                    'serialNo'        => $dsServer->getValue(DBEJCustomerItem::serialNo),
                    'serverName'      => $dsServer->getValue(DBEJCustomerItem::serverName),
                    'purchaseDate'    => $purchaseDate,
                )
            );

            $serverCareContractsTemplate->parse(
                'items',
                'contractItemsBlock',
                true
            );

        } // end while
        $serverCareContractsTemplate->parse(
            'output',
            'serverCareContracts',
            true
        );

        return $serverCareContractsTemplate->get_var('output');

    }

    private function getServiceDeskContractBody($customerId)
    {
        $BUCustomerItem = new BUCustomerItem($this);
        /** @var DataSet $datasetContracts */
        $datasetContracts = null;
        $BUCustomerItem->getServiceDeskValidContractsByCustomerID(
            $customerId,
            $datasetContracts
        );

        if (!$datasetContracts->rowCount()) {
            return $this->getPrepayContractBody($customerId);
        }
        $datasetContracts->fetchNext();
        $users = $datasetContracts->getValue(DBEJCustomerItem::users);
        $description = $datasetContracts->getValue(DBEJCustomerItem::itemDescription);
        $invoicePeriod = $datasetContracts->getValue(
                DBEJCustomerItem::invoiceFromDate
            ) . " - " . $datasetContracts->getValue(
                DBEJCustomerItem::invoiceToDate
            );
        return "<p>User Support Contract: $description for $users users</p><p>Next Invoice: $invoicePeriod</p>";
    }

    private function getPrepayContractBody($customerId)
    {
        $BUCustomerItem = new BUCustomerItem($this);
        /** @var DataSet $datasetContracts */
        $datasetContracts = null;
        $BUCustomerItem->getPrepayContractByCustomerID(
            $customerId,
            $datasetContracts
        );
        if (!$datasetContracts->rowCount()) {
            return "T&M User Support Only";
        }
        $datasetContracts->fetchNext();
        return "<p>Pre-Pay Contract</p>";
    }

    private function getP1IncidentsBody($customerId)
    {
        $dbeProblem = new DBEJProblem($this);

        $dbeProblem->getP1byCustomerIdLast30Days($customerId);


        if (!$dbeProblem->rowCount()) {
            return "None";
        }

        $p1IncidentsTemplate = new Template (
            $GLOBALS ["cfg"] ["path_templates"],
            "remove"
        );

        $p1IncidentsTemplate->set_file(
            'p1Incidents',
            'CustomerReviewMeetingP1Incidents.html'
        );

        $p1IncidentsTemplate->set_block(
            'p1Incidents',
            'incidentsBlock',
            'items'
        );

        while ($dbeProblem->fetchNext()) {

            $dateRaised = $dbeProblem->getValue(DBEJProblem::dateRaised);

            $dateTime = DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $dateRaised
            );

            $dateRaised = $dateTime->format('d/m/Y');
            $slaResponse = $dbeProblem->getValue(DBEJProblem::slaResponseHours);
            $respondedHours = $dbeProblem->getValue(DBEJProblem::respondedHours);

            $p1IncidentsTemplate->set_var(
                [
                    "id"      => $dbeProblem->getValue(DBEJProblem::problemID) . "<br>" . $dateRaised,
                    "summary" => $dbeProblem->getValue(DBEJProblem::reason),
                    "outcome" => $dbeProblem->getValue(DBEJProblem::lastReason),
                    "SLA"     => $respondedHours > $slaResponse ? "Not Achieved" : "Achieved",
                ]
            );

            $p1IncidentsTemplate->parse(
                'items',
                'incidentsBlock',
                true
            );
        }

        $p1IncidentsTemplate->parse(
            'output',
            'p1Incidents',
            true
        );

        return $p1IncidentsTemplate->get_var('output');
    }

    private function getStartersAndLeaversBody($customerId,
                                               DateTimeInterface $startDate,
                                               DateTimeInterface $endDate
    )
    {
        $starterSR = new DBEJProblem($this);
        $starterSR->getStartersSRByCustomerIDInDateRange(
            $customerId,
            $startDate,
            $endDate
        );

        $leaverSR = new DBEJProblem($this);
        $leaverSR->getLeaversSRByCustomerIDInDateRange(
            $customerId,
            $startDate,
            $endDate
        );

        if (!$starterSR->rowCount() && !$leaverSR->rowCount()) {
            return "None";
        }

        $startersAndLeaversTemplate = new Template (
            $GLOBALS ["cfg"] ["path_templates"],
            "remove"
        );

        $startersAndLeaversTemplate->set_file(
            'startersAndLeavers',
            'CustomerReviewMeetingStartersAndLeavers.html'
        );

        $startersAndLeaversTemplate->set_block(
            'startersAndLeavers',
            'startersBlock',
            'items'
        );

        if (!$starterSR->rowCount()) {
            $startersAndLeaversTemplate->parse(
                'items',
                'startersBlock',
                true
            );
        } else {
            $startersAndLeaversTemplate->set_var(
                'startersQty',
                $starterSR->rowCount()
            );
            $workingHours = 0;
            while ($starterSR->fetchNext()) {
                $workingHours += $starterSR->getValue(DBEJProblem::totalActivityDurationHours);
            }
            $avgHours = $workingHours / $starterSR->rowCount();
            $startersAndLeaversTemplate->set_var(
                'startersAvgMinutes',
                round(
                    $avgHours * 60,
                    0
                )
            );

            $startersAndLeaversTemplate->parse(
                'items',
                'startersBlock',
                true
            );
        }

        $startersAndLeaversTemplate->set_block(
            'startersAndLeavers',
            'leaversBlock',
            'leaversItems'
        );

        if (!$leaverSR->rowCount()) {
            $startersAndLeaversTemplate->parse(
                'leaversItems',
                'leaversBlock',
                true
            );
        } else {
            $startersAndLeaversTemplate->set_var(
                'leaversQty',
                $leaverSR->rowCount()
            );
            $workingHours = 0;
            while ($leaverSR->fetchNext()) {
                $workingHours += $leaverSR->getValue(DBEJProblem::totalActivityDurationHours);
            }
            $avgHours = $workingHours / $leaverSR->rowCount();
            $startersAndLeaversTemplate->set_var(
                'leaversAvgMinutes',
                round(
                    $avgHours * 60,
                    0
                )
            );

            $startersAndLeaversTemplate->parse(
                'leaversItems',
                'leaversBlock',
                true
            );
        }


        $startersAndLeaversTemplate->parse(
            'output',
            'startersAndLeavers',
            true
        );

        return $startersAndLeaversTemplate->get_var('output');
    }

    private function getThirdPartyServerAccessBody($customerId)
    {
        $BUCustomerItem = new BUCustomerItem($this);
        /** @var DataSet $datasetContracts */
        $datasetContracts = null;
        $BUCustomerItem->getServerCareValidContractsByCustomerID(
            $customerId,
            $datasetContracts
        );

        $thirdPartyServerAccess = null;

        if ($datasetContracts->rowCount()) {
            $test = new BUCustomerItem($this);
            $datasetServerWatch = new DataSet($this);
            $test->getServerWatchContractByCustomerID(
                $customerId,
                $datasetServerWatch
            );
            if (!$datasetServerWatch->rowCount()) {
                $thirdPartyServerAccess = "<h2>Third-Party Server Access</h2>";
            }

        }
        return $thirdPartyServerAccess;
    }

    /**
     * @param DBECustomer|DataSet $dsCustomer
     * @return string
     */
    private function getReviewMeetingFrequencyBody($dsCustomer)
    {
        $value = $dsCustomer->getValue(DBECustomer::reviewMeetingFrequencyMonths);

        switch ($value) {
            case 1:
                $frequency = 'Monthly';
                break;
            case 2:
                $frequency = 'Two-monthly';
                break;
            case 3:
                $frequency = 'Quarterly';
                break;
            case 6:
                $frequency = 'Six-monthly';
                break;
            case 12:
                $frequency = 'Annually';
                break;

            default:
                $frequency = 'N/A';
        }

        return "<h2>Review Meeting Frequency - " . $frequency . "</h2>";
    }

    private function getSupportedUsersLevelsCount(BUContact $buContact,
                                                  $customerId,
                                                  $customerName
    )
    {
        /** @var DataSet $dsSupportContact */
        $dsSupportContact = null;
        $buContact->getSupportContacts(
            $dsSupportContact,
            $customerId
        );

        $supportContactsCounts = [
            "main"       => 0,
            "supervisor" => 0,
            "support"    => 0,
            "delegate"   => 0,
            "total"      => 0
        ];

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
                    "firstName"  => $firstName,
                    "lastName"   => $lastName,
                    "id"         => $userId,
                    "customerId" => $customerId
                ];
            } else {
                $userMap[$key] = [
                    "firstName"  => $firstName,
                    "lastName"   => $lastName,
                    "id"         => $userId,
                    "customerId" => $customerId
                ];
            }

            if ($dsSupportContact->getValue(DBEContact::supportLevel) == 'none') {
                var_dump($dsSupportContact->getValue(DBEContact::contactID));
            }

            $supportContactsCounts[$dsSupportContact->getValue(DBEContact::supportLevel)]++;
            $supportContactsCounts['total']++;
        }

        if (count($duplicates)) {
            // send email to sales@cnc-ltd.co.uk with the list of duplicates
            $buMail = new BUMail($this);

            $senderEmail = CONFIG_SUPPORT_EMAIL;
            $toEmail = 'sales@cnc-ltd.co.uk';

            $template = new Template(
                $GLOBALS ["cfg"]["path_templates"],
                "remove"
            );
            $template->set_file(
                'page',
                'CustomerReviewMeetingContactDuplicates.html'
            );

            $template->set_var(
                'customerName',
                $customerName
            );

            $template->set_block(
                'page',
                'contactBlock',
                'contacts'
            );

            foreach ($duplicates as $key => $row) {

                $template->set_var(
                    array(
                        'contactID'        => $row['id'],
                        'contactFirstName' => $row['firstName'],
                        'contactLastName'  => $row['lastName'],

                    )
                );

                $template->parse(
                    'contacts',
                    'contactBlock',
                    true
                );
            }

            $template->parse(
                'output',
                'page',
                true
            );

            $body = $template->get_var('output');

            $subject = 'Possible duplicated customer contacts';

            $hdrs = array(
                'From'         => $senderEmail,
                'Subject'      => $subject,
                'Date'         => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );

            $buMail->mime->setHTMLBody($body);

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

        $supportContactInfo = "<table><thead><tr><th>Type</th><th>Qty</th></tr></thead><tbody>";
        $supportContactInfo .= "<tr><td>Main</td><td>$supportContactsCounts[main]</td></tr>";
        $supportContactInfo .= "<tr><td>Supervisor</td><td>$supportContactsCounts[supervisor]</td></tr>";
        $supportContactInfo .= "<tr><td>Support</td><td>$supportContactsCounts[support]</td></tr>";
        $supportContactInfo .= "<tr><td>Delegate</td><td>$supportContactsCounts[delegate]</td></tr>";
        $supportContactInfo .= "<tr><td>Total</td><td>$supportContactsCounts[total]</td></tr>";
        $supportContactInfo .= "</tbody></table>";

        return [
            "data"  => $supportContactInfo,
            "count" => $supportContactsCounts['total']
        ];
    }

    private function generateCharts($data,
                                    $customerId
    )
    {

        $serverCareIncidents = [
            "title"   => "ServerCare Incidents",
            "columns" => ["Dates", "ServerSR", "AvgResponse", "Changes"],
            "data"    => []
        ];

        $serviceDesk = [
            "title"   => "ServiceDesk/Pre-Pay Incidents",
            "columns" => ["Dates", "UserSR", "AvgResponse", "Changes",],
            "data"    => []
        ];

        $otherContracts = [
            "title"   => "Other Contract Incidents",
            "columns" => ["Dates", "OtherSR", "AvgResponse", "Changes",],
            "data"    => []
        ];

        $totalSR = [
            "title"   => "Total SR's",
            "columns" => ["Dates", "P1-3", "P4",],
            "data"    => []
        ];


        foreach ($data as $datum) {


            $row = [
                substr(
                    $datum['monthName'],
                    0,
                    3
                ) . "-" . $datum['year'],
                $datum['serverCareCount1And3'],
                number_format(
                    $datum['serverCareHoursResponded'],
                    1
                ),
                $datum['serverCareCount4']
            ];

            $serverCareIncidents['data'][] = $row;

            $row = [
                substr(
                    $datum['monthName'],
                    0,
                    3
                ) . "-" . $datum['year'],
                $datum['serviceDeskCount1And3'] + $datum['prepayCount1And3'],
                number_format(
                    $datum['serviceDeskHoursResponded'] + $datum['prepayHoursResponded'],
                    1
                ),
                $datum['serviceDeskCount4'] + $datum['prepayCount4'],
            ];

            $serviceDesk['data'][] = $row;

            $row = [
                substr(
                    $datum['monthName'],
                    0,
                    3
                ) . "-" . $datum['year'],
                $datum['otherCount1And3'],
                number_format(
                    $datum['otherHoursResponded'],
                    1
                ),
                $datum['otherCount4'],
            ];

            $otherContracts['data'][] = $row;

            $row = [
                substr(
                    $datum['monthName'],
                    0,
                    3
                ) . "-" . $datum['year'],
                $datum['otherCount1And3'] + $datum['serviceDeskCount1And3'] + $datum['serverCareCount1And3'],
                $datum['otherCount4'] + $datum['serviceDeskCount4'] + $datum['serverCareCount4'],
            ];

            $totalSR['data'][] = $row;
        }
        $BUCustomerItem = new BUCustomerItem($this);
        /** @var DataSet $datasetContracts */
        $datasetContracts = null;
        $BUCustomerItem->getServerCareValidContractsByCustomerID(
            $customerId,
            $datasetContracts
        );

        return [
            "serverCareIncidents" => $serverCareIncidents,
            "serviceDesk"         => $serviceDesk,
            "otherContracts"      => $otherContracts,
            "totalSR"             => $totalSR,
            "renderServerCare"    => !!$datasetContracts->rowCount()
        ];
    }
}
