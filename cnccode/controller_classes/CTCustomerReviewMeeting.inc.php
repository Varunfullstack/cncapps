<?php
/**
 * Customer Review Meeting Controller Class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use Twig\Environment;
use Twig\TwigFilter;
use Twig\TwigFunction;

global $cfg;
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerReviewMeeting.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg ['path_bu'] . '/BUContact.inc.php');
require_once($cfg ['path_bu'] . '/BUServiceDeskReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerSrAnalysisReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerItem.inc.php');
require_once($cfg ['path_bu'] . '/BUActivity.inc.php');
require_once($cfg ['path_bu'] . '/BURenewal.inc.php');
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
        $roles = ACCOUNT_MANAGEMENT_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(405);
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
                'URL'      => SITE_URL . '/images/test.png'
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
        $diskSpaceReport = null;
        $serviceDeskContractBody="";
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

                $reportRangeDate = $startDate->format('F Y') . " to " . $endDate->format('F Y');

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

                $textTemplate->setVar('reportDate', $reportRangeDate);

                $becameCustomerDateString = $dsCustomer->getValue(DBECustomer::becameCustomerDate);
                $becameCustomerDateFormatted = null;
                $years = null;
                if ($becameCustomerDateString) {
                    $becameCustomerDate = DateTime::createFromFormat(DATE_MYSQL_DATE, $becameCustomerDateString);
                    $becameCustomerDateFormatted = $becameCustomerDate->format('d/m/Y');
                    $today = new DateTime();
                    $years = $today->diff($becameCustomerDate)->y;
                }

                $accountManagerDS = new DBEUser($tthis);
                $accountManagerDS->getRow($dsCustomer->getValue(DBECustomer::accountManagerUserID));

                $primaryContact = $buCustomer->getPrimaryContact($customerId);

                $primaryContactName = null;
                if ($primaryContact) {
                    $primaryContactName = $primaryContact->getValue(
                            DBEContact::firstName
                        ) . " " . $primaryContact->getValue(DBEContact::lastName);
                }

                $lastReviewMeetingDateFormatted = null;
                $lastReviewMeetingDate = null;
                if ($dsCustomer->getValue(DBECustomer::lastReviewMeetingDate)) {
                    $lastReviewMeetingDate = DateTime::createFromFormat(
                        DATE_MYSQL_DATE,
                        $dsCustomer->getValue(
                            DBECustomer::lastReviewMeetingDate
                        )
                    );
                    $lastReviewMeetingDateFormatted = $lastReviewMeetingDate->format('d/m/Y');
                }

                // get customer stats
                $stats=$this->getCustomerStats($customerId,$nonEditableTemplate,$endDate);
                $haveServiceDesk=false;
                //$buCustomer->hasServiceDesk($dsSearchForm->getValue(BUCustomerReviewMeeting::searchFormCustomerID));
                $serviceDeskContractBody=$this->getServiceDeskContractBody($customerId,$haveServiceDesk);
                //echo $stats; exit;
                $nonEditableTemplate->set_var(
                    array(
                        'customerName'           => $dsCustomer->getValue(DBECustomer::name),
                        'becameCustomerDate'     => $becameCustomerDateFormatted,
                        'becameCustomerYears'    => $years,
                        'accountManagerName'     => $accountManagerDS->getValue(DBEUser::name),
                        'directDebitSetup'       => $dsCustomer->getValue(
                            DBECustomer::accountNumber
                        ) && $dsCustomer->getValue(DBECustomer::sortCode) ? 'Yes' : 'No',
                        'keyCustomerContactName' => $primaryContactName,
                        'lastReviewMeetingDate'  => $lastReviewMeetingDateFormatted,
                        'lastReviewMeetingClass' => $dsCustomer->getValue(
                            DBECustomer::reviewMeetingBooked
                        ) ? 'class="performance-green"' : null,
                        'reviewMeetingFrequency' => $this->getReviewMeetingFrequencyValue($dsCustomer),
                        'siteURL'                => SITE_URL,
                        'meetingDate'            => self::dateYMDtoDMY(
                            $dsSearchForm->getValue(BUCustomerReviewMeeting::searchFormMeetingDate)
                        ),
                        'slaP1'                  => $dsCustomer->getValue(DBECustomer::slaP1),
                        'slaP2'                  => $dsCustomer->getValue(DBECustomer::slaP2),
                        'slaP3'                  => $dsCustomer->getValue(DBECustomer::slaP3),
                        'slaP4'                  => $dsCustomer->getValue(DBECustomer::slaP4),
                        'slaP5'                  => $dsCustomer->getValue(DBECustomer::slaP5),
                        "waterMarkURL"           => SITE_URL . '/images/CNC_watermarkActualSize.png',
                        'reportDate'             => $reportRangeDate,
                        'hasServiceDesk'        => $haveServiceDesk 
                    )
                );

                $historicStartDate = (clone $endDate)->sub(new DateInterval('P3Y'));
                if (isset($becameCustomerDate) && $becameCustomerDate > $historicStartDate) {
                    $historicStartDate = $becameCustomerDate;
                }
                $historicData = $buCustomerSrAnalysisReport->getResultsByPeriodRange(
                    $customerId,
                    $historicStartDate,
                    $endDate
                );

                $supportedUsersData = $this->getSupportedUsersData(
                    $customerId,
                    $dsCustomer->getValue(DBECustomer::name)
                );
                $buRenewal = new BURenewal($this);
                $items = $buRenewal->getRenewalsAndExternalItemsByCustomer(
                    $customerId,
                    $this,
                    true
                );

                usort(
                    $items,
                    function ($a,
                              $b
                    ) {
                        return $a['itemTypeDescription'] <=> $b['itemTypeDescription'];
                    }
                );

                $nonEditableTemplate->set_block(
                    'page',
                    'itemBlock',
                    'items'
                );

                $totalSalePrice = 0;
                $dbeItemType = new DBEItemType($this);
                $dbeItemType->getCustomerReviewRows();

                $itemTypes = [];

                while ($dbeItemType->fetchNext()) {
                    $itemTypes[$dbeItemType->getValue(DBEItemType::description)] = [];
                    $itemsCopy = $items;
                    foreach ($itemsCopy as $index => $item) {
                        if ($item['itemTypeDescription'] != $dbeItemType->getValue(DBEItemType::description)) {
                            continue;
                        }
                        $itemTypes[$dbeItemType->getValue(DBEItemType::description)][] = $item;
                        unset ($items[$index]);
                    }

                }


                foreach ($itemTypes as $typeName => $itemTypeContainer) {

                    $itemTypeHeader = '<tr><td colspan="5"><h3>' . $typeName . '</h3></td></tr>';

                    $nonEditableTemplate->set_var(
                        array(
                            'itemTypeHeader' => $itemTypeHeader
                        )
                    );

                    if (!count($itemTypeContainer)) {
                        $nonEditableTemplate->set_var(
                            array(
                                'description'        => "No Services provided",
                                'notes'              => null,
                                'salePrice'          => null,
                                'quantity'           => null,
                                'coveredItemsString' => null,
                                'itemClass'          => null,
                                'customerID'         => null,
                            )
                        );
                        $nonEditableTemplate->parse(
                            'items',
                            'itemBlock',
                            true
                        );
                    } else {
                        $removeHeader = false;
                        foreach ($itemTypeContainer as $item) {
                            if ($item['description'] == 'Customer Account Management') {
                                continue;
                            }
                            $coveredItemsString = null;

                            if ($removeHeader) {
                                $nonEditableTemplate->set_var(
                                    array(
                                        'itemTypeHeader' => ''
                                    )
                                );
                            }

                            if (count($item['coveredItems']) > 0) {
                                foreach ($item['coveredItems'] as $coveredItem) {
                                    $coveredItemsString .= '<br/>' . $coveredItem;
                                    $nonEditableTemplate->set_var(
                                        array(
                                            'coveredItemsString' => $coveredItemsString
                                        )
                                    );
                                }
                            }
                            $itemClass = 'externalItem';
                            $salePrice = null;

                            if (!is_null($item['customerItemID'])) {
                                $formatter = new NumberFormatter('en_GB', NumberFormatter::CURRENCY);
                                $itemClass = null;
                                $salePrice = $formatter->formatCurrency($item['salePrice'], 'GBP');
                                $totalSalePrice += $item['salePrice'];
                            }

                            $nonEditableTemplate->set_var(
                                array(
                                    'notes'              => $item['notes'],
                                    'description'        => Controller::htmlDisplayText($item['description']),
                                    'salePrice'          => $salePrice,
                                    'quantity'           => $item['units'] ? $item['units'] : "",
                                    'coveredItemsString' => $coveredItemsString,
                                    'itemClass'          => $itemClass,
                                    'customerID'         => $customerId,
                                    'directDebit'        => $item['directDebit'] ? 'Yes' : null
                                )
                            );
                            $nonEditableTemplate->parse(
                                'items',
                                'itemBlock',
                                true
                            );
                            if (!$removeHeader) {
                                $removeHeader = true;
                            }
                        }

                    }

                }


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
                    $serviceDeskContractBody
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
                    'startersPCInstallation',
                    $this->getStarterWithPCInstallation(
                        $customerId,
                        $startDate,
                        $endDate
                    )
                );
                // $textTemplate->set_var(
                //     'reviewMeetingFrequency',
                //     $this->getReviewMeetingFrequencyBody($dsCustomer)
                // );
                $textTemplate->set_block(
                    'page',
                    'managementReviewBlock',
                    'reviews'
                );
                $dsReviews = new DataSet($this);

                $buActivity->getManagementReviewsInPeriod(
                    $dsSearchForm->getValue(BUCustomerReviewMeeting::searchFormCustomerID),
                    $lastReviewMeetingDate ? $lastReviewMeetingDate : $startDate,
                    new DateTime(),
                    $dsReviews
                );

                $itemNo = 0;

                while ($dsReviews->fetchNext()) {

                    $itemNo++;

                    $urlServiceRequest =
                        Controller::buildLink(
                            'SRActivity.php',
                            array(
                                'serviceRequestId' => $dsReviews->getValue(DBEProblem::problemID)
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
                    $inactiveMark = $row->active ? null : ' *';
                    $nonEditableTemplate->set_var(
                        array(
                            'srUserName'    => "{$row->name}{$inactiveMark}",
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

                $buHeader = new BUHeader($this);
                $dsHeader = new DataSet($this);
                $buHeader->getHeader($dsHeader);

                $dsn = 'mysql:host=' . LABTECH_DB_HOST . ';dbname=' . LABTECH_DB_NAME;
                $options = [
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                ];
                try {
                    $labtechDB = new PDO(
                        $dsn,
                        LABTECH_DB_USERNAME,
                        LABTECH_DB_PASSWORD,
                        $options
                    );
                } catch (\Exception $exception) {
                    throw new \MongoDB\Driver\Exception\ConnectionException('Unable to connect to labtech');
                }


                $statement = $labtechDB->prepare(
                    'SELECT computers.name as agentName,letter as driveLetter,size as driveSize, free as driveFreeSpace, free/size  as freePercent FROM  drives 
JOIN computers ON computers.`ComputerID` = drives.`ComputerID`
JOIN clients ON computers.`ClientID` = clients.`ClientID`
WHERE INTERNAL = 1 AND missing=0 AND os LIKE \'%server%\' and size >= 1024 AND clients.`ExternalID` = ?'
                );
                $statement->execute([$customerId]);

                $diskSpaceData = $statement->fetchAll(PDO::FETCH_ASSOC);
                /** @var $twig Environment */
                global $twig;
                $twig->addFilter(
                    new TwigFilter(
                        'freePercentage',
                        function ($string) use ($dsHeader) {
                            $pctValue = $string * 100;
                            return number_format($pctValue) . "%";
                        }
                    )
                );
                $twig->addFilter(
                    new TwigFilter(
                        'MB2GB',
                        function ($string) {
                            if (!$string) {
                                return null;
                            }
                            return number_format($string / 1024, 0, '', '') . 'GB';
                        }
                    )
                );
                $twig->addFunction(
                    new TwigFunction(
                        'getFreeSpaceClass',
                        function ($item) use ($dsHeader) {
                            $threshold = $dsHeader->getValue(DBEHeader::otherDriveFreeSpaceWarningPercentageThreshold);
                            if ($item['driveLetter'] === 'C') {
                                $threshold = $dsHeader->getValue(DBEHeader::cDriveFreeSpaceWarningPercentageThreshold);
                            }
                            $pctValue = $item['freePercent'] * 100;
                            $colorStyle = null;
                            if ($pctValue <= $threshold) {
                                $colorStyle = 'style="color: red "';
                            }
                            return "$colorStyle";
                        },
                        ['is_safe' => ['all', "html"], 'pre_escaped' => 'html']
                    )
                );
                $diskSpaceReport = '';
                if (count($diskSpaceData)) {
                    $diskSpaceReport = $twig->render(
                        '@internal/customerReviewMeeting/diskSpaceReportSection.html.twig',
                        ["driveSpaceItems" => $diskSpaceData]
                    );
                }

                $nonEditableTemplate->setVar('diskSpaceSection', $diskSpaceReport);

                $nonEditableTemplate->parse(
                    'output',
                    'page',
                    true
                );                
                $customStartDate=new DateTime($endDate->format('Y-m-d'));
                $customStartDate->modify("-3 month");
                $buCustomer=new BUCustomer($this);
                $firstTimeFixReport=$buCustomer->getFirstTimeFixSummary($customerId,$customStartDate,$endDate);
                $raiseTypeSummary=$buCustomer->getProblemRaisedTypeSummary($customerId,$customStartDate,$endDate);
                 $nonEditableText = $nonEditableTemplate->get_var('output');
                $results = $buCustomerSrAnalysisReport->getResultsByPeriodRange(
                    $customerId,
                    $startDate,
                    $endDate
                );
                $results["firstTimeFix"]= $firstTimeFixReport["firstTimeFix"];
                $results["attemptedFirstTimeFix"]= $firstTimeFixReport["attemptedFirstTimeFix"];
                $results["raiseTypeSummary"]=$raiseTypeSummary;
                $graphData = $this->generateCharts(
                    $results,
                    $customerId,
                    $historicData
                );

                $supportedUsersData = $this->getSupportedUsersLevelsCount(
                    $buContact,
                    $customerId,
                    $dsCustomer->getValue(DBECustomer::name)
                );


                $textTemplate->set_var(
                    'mainContacts',
                    $supportedUsersData['data']
                );

                $textTemplate->set_var(
                    'customerReviewMeetingText',
                    $dsHeader->getValue(DBEHeader::customerReviewMeetingText)
                );

                $textTemplate->parse(
                    'output',
                    'page',
                    true
                );
                $editableText = $textTemplate->get_var('output');
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
                'meetingDate'           => $dsSearchForm->getValue(BUCustomerReviewMeeting::searchFormMeetingDate),
                'meetingDateYmd'        => $dsSearchForm->getValue(BUCustomerReviewMeeting::searchFormMeetingDate),
                'urlCustomerPopup'      => $urlCustomerPopup,
                'editableText'          => $editableText,
                'nonEditableText'       => $nonEditableText,
                'diskSpaceReport'       => $diskSpaceReport,
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

    /**
     * @param DBECustomer|DataSet $dsCustomer
     * @return string
     */
    private function getReviewMeetingFrequencyValue($dsCustomer)
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
        return $frequency;
    }

    private function getSupportedUsersData($customerId,
                                           $customerName
    )
    {
        /** @var DBEContact $dsSupportContact */
        $dsSupportContact = new DBEContact($this);
        $dsSupportContact->getRowsByCustomerID($customerId);

        $supportContacts = [
            "main"             => [],
            "supervisor"       => [],
            "support"          => [],
            "delegate"         => [],
            "no support level" => []
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

            if ($dsSupportContact->getValue(DBEContact::supportLevel)) {
                $supportContacts[$dsSupportContact->getValue(DBEContact::supportLevel)][] = [
                    "firstName" => $firstName,
                    "lastName"  => $lastName
                ];
            } else {
                $supportContacts['no support level'][] = [
                    "firstName" => $firstName,
                    "lastName"  => $lastName
                ];
            }

            $count++;
        }

        if (count($duplicates)) {

            $buMail = new BUMail($this);
            $senderEmail = CONFIG_SUPPORT_EMAIL;
            $toEmail = 'sales@' . CONFIG_PUBLIC_DOMAIN;

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
                $body
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
            $currentSection = str_replace('{type}', ucwords($type), $currentSection);
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
        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRow($customerId);
        $serverCareContractsTemplate->set_var(
            [
                'usersCount' => $supportContactsCount,
                "mailboxes"  => $dbeCustomer->getValue(DBECustomer::licensedOffice365Users),
                'pcs'        => $dbeCustomer->getValue(DBECustomer::noOfPCs)
            ]
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

    private function getServiceDeskContractBody($customerId,&$haveServiceDesk )
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
        if(strpos($description,"ServiceDesk") >0)
            $haveServiceDesk=true;
        else
            $haveServiceDesk=false;
        $invoicePeriod = $datasetContracts->getValue(
                DBEJCustomerItem::invoiceFromDate
            ) . " - " . $datasetContracts->getValue(
                DBEJCustomerItem::invoiceToDate
            );
            //<p>Next Invoice: $invoicePeriod</p>
        return "<p>User Support Contract: $description for $users users</p>";
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

    /**
     * @param DBECustomer|DataSet $dsCustomer
     * @return string
     */
    private function getReviewMeetingFrequencyBody($dsCustomer)
    {
        $frequency = $this->getReviewMeetingFrequencyValue($dsCustomer);
        return "<h2>Review Meeting Frequency - " . $frequency . "</h2>";
    }

    private function generateCharts($data,
                                    $customerId,
                                    $historicData
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
            "title"   => "Total SRs",
            "columns" => ["Dates", "P1-3", "P4",],
            "data"    => []
        ];

        $historicTotalSR = [
            "title"   => "Historic Total SRs",
            "columns" => ["Dates", "P1-3", "P4"],
            "data"    => []
        ];
        
        $firstTimeFixRequests = [
            "title"   => "Qualifying First Time Fix Requests",
            "columns" => ["Attempted", "Achieved"],
            "data"    => [["Attempted",$data["attemptedFirstTimeFix"]],["Achieved",$data["firstTimeFix"]]]
        ];

        $sourceOfRequests = [
            "title"   => "Source of Requests (%)",
            "columns" => ["Title", "Value" ],
            "data"    => []
        ];
        foreach($data["raiseTypeSummary"] as $item)
        {
            $sourceOfRequests["data"] []=[$item["description"],$item["total"]];
        }
        //$data["raiseTypeSummary"]
        //echo json_encode($sourceOfRequests); exit;
        foreach ($historicData as $datum) {
            $row = [
                substr(
                    $datum['monthName'],
                    0,
                    3
                ) . "-" . $datum['year'],
                $datum['otherCount1And3'] + $datum['serviceDeskCount1And3'] + $datum['serverCareCount1And3'] + $datum['prepayCount1And3'],
                $datum['otherCount4'] + $datum['serviceDeskCount4'] + $datum['serverCareCount4'] + $datum['prepayCount4'],
            ];

            $historicTotalSR['data'][] = $row;
        }

/*
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
                $datum['otherCount1And3'] + $datum['serviceDeskCount1And3'] + $datum['serverCareCount1And3'] + $datum['prepayCount1And3'],
                $datum['otherCount4'] + $datum['serviceDeskCount4'] + $datum['serverCareCount4'] + $datum['prepayCount4'],
            ];

            $totalSR['data'][] = $row;
        }*/
        $BUCustomerItem = new BUCustomerItem($this);
        /** @var DataSet $datasetContracts */
        $datasetContracts = null;
        $BUCustomerItem->getServerCareValidContractsByCustomerID(
            $customerId,
            $datasetContracts
        );

        return [
            // "serverCareIncidents" => $serverCareIncidents,
            // "serviceDesk"         => $serviceDesk,
            // "otherContracts"      => $otherContracts,
            "totalSR"             => $totalSR,
            'historicTotalSR'     => $historicTotalSR,
            "renderServerCare"    => !!$datasetContracts->rowCount(),
            "firstTimeFixRequests" =>  $firstTimeFixRequests,
            "sourceOfRequests"    =>$sourceOfRequests
        ];
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
            "furlough"   => 0,
            "none"       => 0,
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

            if (!$dsSupportContact->getValue(DBEContact::supportLevel)) {
                $supportContactsCounts['none']++;
            } else {
                $supportContactsCounts[$dsSupportContact->getValue(DBEContact::supportLevel)]++;
            }
            $supportContactsCounts['total']++;
        }

        if (count($duplicates)) {

            $buMail = new BUMail($this);

            $senderEmail = CONFIG_SUPPORT_EMAIL;
            $toEmail = 'sales@' . CONFIG_PUBLIC_DOMAIN;

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
        $supportContactInfo .= "<tr><td>Furlough</td><td>$supportContactsCounts[furlough]</td></tr>";
        $supportContactInfo .= "<tr><td>No Level</td><td>$supportContactsCounts[none]</td></tr>";
        $supportContactInfo .= "<tr><td>Total</td><td>$supportContactsCounts[total]</td></tr>";
        $supportContactInfo .= "</tbody></table>";

        return [
            "data"  => $supportContactInfo,
            "count" => $supportContactsCounts['total']
        ];
    }
    private function getCustomerStats($customerID,$template, $endDate){
        $startDate=new DateTime($endDate->format('Y-m-d'));
        $start=  $startDate->modify("-3 month")->format('Y-m-d');
        $end= $endDate->format('Y-m-d');
       // echo $start.' '.$end; exit;       
        $ch = curl_init();
        $dbeCustomer=new DBECustomer($this);
        $dbeCustomer->getRow($customerID);
        $penaltiesAgreed =$dbeCustomer->getValue(DBECustomer::slaP1PenaltiesAgreed)||
        $dbeCustomer->getValue(DBECustomer::slaP2PenaltiesAgreed)||
        $dbeCustomer->getValue(DBECustomer::slaP3PenaltiesAgreed);
        // set url
        curl_setopt($ch, CURLOPT_URL, "https://".$_SERVER['HTTP_HOST']."/internal-api/customerStats/$customerID?breakDown=true&startDate=$start&endDate=$end");

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $template->set_block(
            'page',
            'customerSLABlock',
            'customerSLA'
        );
        
        
        // $output contains the output string
        $output = curl_exec($ch);
        $items=json_decode($output, true);
        $keys=[
            "Total SRs Raised"=>["key"=>"raised","sum"=>true],
            "Response SLA"=>["key"=>"sla","sum"=>true],
            "Average Response Time"=>["key"=>"responseTime","sum"=>false],
            "% of Response SLAs Met"=>["key"=>"slaMet","sum"=>false],
            ($penaltiesAgreed?"Fix SLA":"Fix OLA")=>["key"=>"fixSLA","sum"=>true],
            "Average Fix Time (Awaiting CNC)"=>["key"=>"avgTimeAwaitingCNC","sum"=>false],
            "Average Time from Initial to Fixed"=>["key"=>"avgTimeFromRaiseToFixHours","sum"=>false],

        ];
        foreach($keys as $key=>$value)
        { 
            $column=$value["key"];
            $sum=(($items[0][$column]??0)+
            ($items[1][$column]??0)+
            ($items[2][$column]??0)+
            ($items[3][$column]??0));
            $allValue=$sum;
            if(!$value["sum"])
                $allValue=$sum>0?$sum/count($items):0;
            ;
            $template->set_var(
                array(
                    'description'       => $key,
                    'p1Value'           =>round($items[0][$column]??0,2),
                    'p2Value'           =>round($items[1][$column]??0,2),
                    'p3Value'           =>round($items[2][$column]??0,2),
                    'p4Value'           =>round($items[3][$column]??0,2),
                    'allValue'          =>round($allValue,2),
                 )
            );
            $template->parse(
                'customerSLA',
                'customerSLABlock',
                true
            );
        }
       
        
        return  $output;
        //return json_decode($output, true);
    }
    private function getStarterWithPCInstallation(
        $customerId,
        DateTimeInterface $startDate,
        DateTimeInterface $endDate
    ) {
        $starterSR = new DBEJProblem($this);
        $starterSR->getStartersSRWithPCInstallationRootCause(
            $customerId,
            $startDate,
            $endDate
        );
        

        if (!$starterSR->rowCount()) {
            return "None";
        }

        $startersPCInstallationTemplate = new Template(
            $GLOBALS["cfg"]["path_templates"],
            "remove"
        );

        $startersPCInstallationTemplate->set_file(
            'startersPCInstallation',
            'CustomerReviewMeetingStartersPCInstallation.html'
        );

        $startersPCInstallationTemplate->set_block(
            'startersPCInstallation',
            'startersPCBlock',
            'items'
        );
      
        if (!$starterSR->rowCount()) {
            $startersPCInstallationTemplate->parse(
                'items',
                'startersPCBlock',
                true
            );
        } else {
            $startersPCInstallationTemplate->set_var(
                'startersQty',
                $starterSR->rowCount()
            );
            $workingHours = 0;
            while ($starterSR->fetchNext()) {
                $workingHours += $starterSR->getValue(DBEJProblem::totalActivityDurationHours);
            }
            $avgHours = $workingHours / $starterSR->rowCount();
            $startersPCInstallationTemplate->set_var(
                'startersAvgMinutes',
                round(
                    $avgHours * 60,
                    0
                )
            );

            $startersPCInstallationTemplate->parse(
                'items',
                'startersPCBlock',
                true
            );
        }

       

        $startersPCInstallationTemplate->parse(
            'output',
            'startersPCInstallation',
            true
        );

        return $startersPCInstallationTemplate->get_var('output');
    }
}
