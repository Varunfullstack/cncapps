<?php
/**
 * Activity controller class
 * CNC Ltd
 *
 * a change
 * @access public
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUProject.inc.php');
require_once($cfg['path_bu'] . '/BUExpense.inc.php');
require_once($cfg['path_dbe'] . '/DBEWarranty.inc.php');
require_once($cfg['path_dbe'] . '/DBECallActivity.inc.php');
require_once($cfg['path_dbe'] . '/DBEJCallActivity.php');
require_once($cfg['path_dbe'] . '/DBECallDocument.inc.php');
require_once($cfg['path_dbe'] . '/DBECallActType.inc.php');
require_once($cfg['path_dbe'] . '/DBEJCallActType.php');
require_once($cfg['path_dbe'] . '/DBEEscalation.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerItem.inc.php');
require_once($cfg['path_bu'] . '/BUSite.inc.php');
require_once($cfg['path_bu'] . '/BUContact.inc.php');
require_once($cfg['path_bu'] . '/BUSecurityApp.inc.php');
require_once($cfg['path_bu'] . '/BURootCause.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');

require_once("Mail.php");
require_once("Mail/mime.php");
// Actions
define(
    'CTACTIVITY_ACT_DISPLAY_ACTIVITY',
    'displayActivity'
);
define(
    'CTACTIVITY_ACT_EDIT_ACTIVITY',
    'editActivity'
);
define(
    'CTACTIVITY_ACT_UPDATE_ACTIVITY',
    'updateActivity'
);
define(
    'CTACTIVITY_ACT_INSERT_ACTIVITY',
    'insertActivity'
);
define(
    'CTACTIVITY_ACT_CREATE_ACTIVITY',
    'createActivity'
);
define(
    'CTACTIVITY_ACT_CANCEL_EDIT',
    'cancelEdit'
);
define(
    'CTACTIVITY_ACT_CREATE_RESOLVED_ACTIVITY',
    'createResolvedActivity'
);
define(
    'CTACTIVITY_ACT_DELETE_ACTIVITY',
    'deleteCallActivity'
);

define(
    'CTACTIVITY_ACT_CHECK_ACTIVITY',
    'checkActivity'
);
define(
    'CTACTIVITY_ACT_ADD_TO_CALENDAR',
    'addToCalendar'
);
define(
    'CTACTIVITY_ACT_CHANGE_REQUEST_REVIEW',
    'changeRequestReview'
);

define(
    'CTACTIVITY_ACT_TIME_REQUEST_REVIEW',
    'timeRequestReview'
);
define(
    'CTACTIVITY_ACT_UPLOAD_FILE',
    'uploadFile'
);
define(
    'CTACTIVITY_ACT_VIEW_FILE',
    'viewFile'
);
define(
    'CTACTIVITY_ACT_GET_FILE',
    'getFile'
); // get stream
define(
    'CTACTIVITY_ACT_DELETE_FILE',
    'deleteFile'
);
define(
    'CTACTIVITY_ACT_CREATE_SALES_ORDER',
    'createSalesOrder'
);
define(
    'CTACTIVITY_ACT_SKIP_SALES_ORDER',
    'skipSalesOrder'
);
define(
    'CTACTIVITY_ACT_EXPORT_GENERATE',
    'gscExportGenerate'
);
define(
    'CTACTIVITY_ACT_EXPORT_FORM',
    'gscExportForm'
);
define(
    'CTACTIVITY_ACT_SEND_VISIT_EMAIL',
    'sendVisitEmail'
);

define(
    'CTACTIVITY_ACT_CONTRACT_BY_CUSTOMER',
    'contractsForClient'
);

class CTActivity extends CTCNC
{

    const GREEN = '#BDF8BA';
    const CONTENT = '#F4f4f2';
    var $statusArrayCustomer =
        array(
            "A" => "Active",
            "E" => "Ended",
            ""  => "All"
        );
    var $serverGuardArray =
        array(
            ""  => "Please select",
            "Y" => "ServerGuard Related",
            "N" => "Not ServerGuard Related"
        );
    var $arrContractType =
        array(
            "TM"  => "T & M",
            "GSC" => "Pre-pay",
            "O"   => "Other Contract",
            ""    => "All"
        );
    private $dsPrintRange = '';
    /**
     *
     * @var DSForm
     */
    private $dsSearchForm = '';
    /**
     *
     * @var DataSet
     */
    private $dsSearchResults = '';
    /**
     *
     * @var DataSet
     */
    private $sessionKey;
    private $contactID;
    private $userWarned = false;
    /**
     *
     * @var BUActivity
     */
    private $buActivity = '';
    private $statusArray =
        array(
            ""          => "All",
            "INITIAL"   => "Awaiting Initial Response",
            "CUSTOMER"  => "Awaiting Customer",
            "CNC"       => "Awaiting CNC",
            "FIXED"     => "Fixed",
            "COMPLETED" => "Completed",
            "NOT_FIXED" => "Not Fixed",
            "CHECKED_T_AND_M"
                        => "Checked T&M Due Completion",
            "CHECKED_NON_T_AND_M"
                        => "Checked Non-T&M Due Completion",
            "UNCHECKED" => "Unchecked"
        );

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
        $this->buActivity = new BUActivity($this);
        $this->dsSearchForm = new DSForm($this);
        $this->dsSearchResults = new DataSet($this);
        $this->dsCallActivity = new DSForm($this);
        $this->dsCallActivity->copyColumnsFrom($this->buActivity->dbeJCallActivity);
        $this->dsCallActivity->setNull(
            'siteNo',
            DA_ALLOW_NULL
        );
        $this->dsCallActivity->setNull(
            'contactID',
            DA_ALLOW_NULL
        );
        $this->dsCallActivity->setNull(
            'callActTypeID',
            DA_ALLOW_NULL
        );

        $roles = [
            "sales",
            "accounts",
            "technical",
            "supervisor",
            "reports",
            "maintenance",
            "renewals"
        ];

        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
    }


    private function assignContracts()
    {
        $activities = $_REQUEST['callActivityID'];
        $problems = $_REQUEST['problem'];

        $dbeCallActivity = new DBECallActivity($this);
        $dbeProblem = new DBEProblem($this);
        foreach ($activities as $activityID => $rubbish) {
            $dbeCallActivity->getRow($activityID);
            $problemID = $dbeCallActivity->getValue(DBECallActivity::problemID);
            $dbeProblem->getRow($problemID);
            $dbeProblem->setValue(
                DBEProblem::contractCustomerItemID,
                $problems[$problemID]['contract']
            );
            $dbeProblem->updateRow();
        }
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        /*
      Get contactID for session key if passed
      */
        if (isset($_REQUEST['contactID'])) {
            $this->contactID = $_REQUEST['contactID'];
            $this->sessionKey = 'activity_create' . $this->contactID;
            $_SESSION[$this->sessionKey]['contactID'] = $this->contactID;
            $sess[$this->sessionKey]['contactID'] = $this->contactID;
        }

        switch ($_REQUEST['action']) {

            case CTCNC_ACT_SEARCH:
                /* if user has clicked Generate Sales Orders or Skip Sales Orders */
                if ($_REQUEST['Search'] == 'Generate Sales Orders') {
                    $this->createSalesOrder();
//                    $this->assignContracts();
                } elseif ($_REQUEST['Search'] == 'Complete SRs') {
                    $this->completeSRs();
                } else {
                    if ($_REQUEST['Search'] == 'Skip Sales Orders') {
                        $this->assignContracts();
                        $this->skipSalesOrder();
                    } else {
                        $this->search();
                    }
                }
                break;
            case 'displayServiceRequestForContactPopup':
                $this->serviceRequestsForContactPopup();
                break;
            case CTACTIVITY_ACT_DISPLAY_ACTIVITY:
                $this->displayActivity();
                break;
            case 'displayFirstActivity':
                $this->displayFirstActivity();
                break;
            case 'displayLastActivity':
                $this->displayLastActivity();
                break;
            case 'activityCreate1':
                $this->activityCreate1();
                break;
            case 'editValueOnlyServiceRequest':
                $this->editValueOnlyServiceRequest();
                break;
            case 'displayOpenSrs':
                $this->displayOpenSrs();
                break;
            case 'editServiceRequestHeader':
                $this->editServiceRequestHeader();
                break;
            case 'createTravel':
                $this->createTravel();
                break;
            case 'editLinkedSalesOrder':
                $this->editLinkedSalesOrder();
                break;
            case 'problemHistoryPopup':
                $this->problemHistoryPopup();
                break;
            case 'customerProblemPopup':
                $this->customerProblemPopup();
                break;
            case CTACTIVITY_ACT_EDIT_ACTIVITY:
            case CTACTIVITY_ACT_CREATE_ACTIVITY:
            case CTACTIVITY_ACT_CREATE_RESOLVED_ACTIVITY:
                $this->editActivity();
                break;
            case CTACTIVITY_ACT_DELETE_ACTIVITY:
                $this->deleteCallActivity();
                break;
            case CTACTIVITY_ACT_CANCEL_EDIT:
                $this->cancelEdit();
                break;
            case CTACTIVITY_ACT_UPDATE_ACTIVITY:
            case CTACTIVITY_ACT_INSERT_ACTIVITY:
                $this->updateCallActivity();
                break;
            case CTACTIVITY_ACT_CHECK_ACTIVITY:
                $this->checkPermissions(PHPLIB_PERM_SUPERVISOR);
                $this->checkActivity();
                break;
            case 'createFollowOnActivity':
                $this->createFollowOnActivity();
                break;
            case 'createRequestFromCustomerRequest':
                $this->checkPermissions(PHPLIB_PERM_TECHNICAL);
                $this->createRequestFromCustomerRequest();
                break;
            case 'updateRequestFromCustomerRequest':
                $this->checkPermissions(PHPLIB_PERM_TECHNICAL);
                $this->updateRequestFromCustomerRequest();
                break;
            case 'displayServiceRequest':
                $this->displayServiceRequest();
                break;
            case 'setProblemFixed':
                $this->setProblemFixed();
                break;
            case 'setProblemComplete':
                $this->checkPermissions(PHPLIB_PERM_SUPERVISOR);
                $this->setProblemComplete();
                break;
            /*
          case 'reopenProblem':
          $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
          $this->reopenProblem();
          break;
          */
            case 'linkProblems':
                $this->linkProblems();
                break;
            case CTACTIVITY_ACT_EXPORT_FORM:
                $this->checkPermissions(PHPLIB_PERM_ACCOUNTS);
                $this->gscExportForm();
                break;
            case CTACTIVITY_ACT_EXPORT_GENERATE:
                $this->checkPermissions(PHPLIB_PERM_ACCOUNTS);
                $this->gscExportGenerate();
                break;
            case CTACTIVITY_ACT_ADD_TO_CALENDAR:
                $this->addToCalendar();
                break;
            case CTACTIVITY_ACT_SEND_VISIT_EMAIL:
                $this->sendVisitEmail();
                break;
            case CTACTIVITY_ACT_UPLOAD_FILE:
                $this->uploadFile();
                break;
            case CTACTIVITY_ACT_VIEW_FILE:
                $this->viewFile();
                break;

            case CTACTIVITY_ACT_GET_FILE:
                $this->getFile();
                break;

            case CTACTIVITY_ACT_DELETE_FILE:
                $this->deleteFile();
                break;

            case 'autoUpdate':
                $this->autoUpdate();
                break;

            case 'gatherFixedInformation':
                $this->gatherFixedInformation();
                break;

            case 'gatherManagementReviewDetails':
                $this->gatherManagementReviewDetails();
                break;

            case 'allocateAdditionalTime':
                $this->allocateAdditionalTime();
                break;

            case 'requestAdditionalTime':
                $this->requestAdditionalTime();
                break;

            case CTACTIVITY_ACT_CHANGE_REQUEST_REVIEW:

                $this->changeRequestReview();
                break;
            case CTACTIVITY_ACT_TIME_REQUEST_REVIEW;
                $this->timeRequestReview();
                break;
            case 'contractListPopup':
                $this->contractListPopup();
                break;
            case 'managerCommentPopup':
                $this->managerCommentPopup();
                break;
            case 'messageToSales':
                echo json_encode($this->messageToSales());
                break;
            case 'toggleDoNextFlag':
                $this->checkPermissions(PHPLIB_PERM_SUPERVISOR);
                $this->toggleDoNextFlag();
                break;
            case 'toggleCriticalFlag':
                $this->checkPermissions(PHPLIB_PERM_SUPERVISOR);
                $this->toggleCriticalFlag();
                break;
            case 'getServerTime':
                $this->getServerTime();
                break;

            case 'updateHistoricUserTimeLogs':
                $startDateData = @$_REQUEST['startDate'];

                $startDate = new DateTime($startDateData);

                $this->updateHistoricUserTimeLogs($startDate);
                break;
            case 'test':
                $this->buActivity->sendSalesRequestAlertEmail(
                    387378,
                    null
                );
                break;

            case 'toggleMonitoringFlag':
                $this->toggleMonitoringFlag();
                break;

            case 'unhideSR':
                $buUser = new BUUser($this);

                if ($buUser->isSdManager($this->userID)) {
                    $this->unhideSR();
                } else {
                    header('Location: ' . $_SERVER['HTTP_REFERER']);
                }
                break;
            case CTACTIVITY_ACT_CONTRACT_BY_CUSTOMER:
                echo json_encode($this->getContractsForCustomer($_REQUEST['customerID']));
                break;
            case 'sendSalesRequest':
                echo json_encode($this->sendSalesRequest());
                break;
            case 'salesRequestReview':
                $this->salesRequestReview();
                break;
            case 'contactNotes':
                $buCustomer = new BUCustomer($this);
                $phoneHtml = $buCustomer->getContactPhoneForHtml(@$_REQUEST['contactID']);
                echo json_encode(['data' => $this->getContactNotes(), 'phone' => $phoneHtml]);
                break;
            case 'authorisingContacts':
                echo json_encode(['data' => $this->getAuthorisingContacts()]);
                break;
            case CTCNC_ACT_DISPLAY_SEARCH_FORM:
            default:
                $this->displaySearchForm();
                break;

        }
    }

    /**
     * Create sales orders from checked activities
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function createSalesOrder()
    {
        $this->setMethodName('createSalesOrder');
        if (isset($_REQUEST['callActivityID'])) {
            $this->buActivity->createSalesOrdersFromActivities($_REQUEST['callActivityID']);
            $urlNext =
                Controller::buildLink(
                    'SalesOrder.php',
                    array(
                        'action'    => 'search',
                        'orderType' => 'I',
                        'lineText'  => 'consultancy'
                    )
                );

            header('Location: ' . $urlNext);
        }
    }

    function completeSRs()
    {
        $this->setMethodName('completeSRs');
        if (isset($_REQUEST['callActivityID'])) {
            $this->buActivity->completeSRs($_REQUEST['callActivityID']);

            $urlNext =
                Controller::buildLink(
                    'Activity.php',
                    array(
                        'action'                      => 'search',
                        'activity%5B1%5D%5Bstatus%5D' => 'CHECKED_NON_T_AND_M'
                    )
                );

            header('Location: ' . $urlNext);
        }
    } // end function salesRequest

    /**
     * Skip creation of sales order but authorise checked activities of given call
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function skipSalesOrder()
    {
        $this->setMethodName('skipSalesOrder');
        if (isset($_REQUEST['callActivityID'])) {
            $this->buActivity->skipSalesOrdersForActivities($_REQUEST['callActivityID']);
        }
        $this->search();
    } // end function displaySearchForm

    function search()
    {

        $this->setMethodName('search');
        $this->buActivity->initialiseSearchForm($this->dsSearchForm);
        /* Special Case */
        if (isset($_REQUEST['linkedSalesOrderID'])) {
            $this->dsSearchForm->setUpdateModeUpdate();
            $this->dsSearchForm->setValue(
                'linkedSalesOrderID',
                $_REQUEST['linkedSalesOrderID']
            );
            $this->dsSearchForm->setValue(
                'callActTypeID',
                CONFIG_INITIAL_ACTIVITY_TYPE_ID
            );
            $this->dsSearchForm->post();
        } elseif (isset($_REQUEST['activity'])) {
            if (!$this->dsSearchForm->populateFromArray($_REQUEST['activity'])) {
                $this->setFormErrorOn();
                $this->displaySearchForm(); //redisplay with errors
                exit;
            } else {
                if (
                    $this->countParamsSet($_REQUEST['activity']) < 2 AND
                    empty($this->dsSearchForm->getValue('customerID')) AND
                    $this->dsSearchForm->getValue('contractCustomerItemID') == '99' and
                    $this->dsSearchForm->getValue('status') !== 'CHECKED_NON_T_AND_M'
                ) {
                    $this->formErrorMessage = 'you have not selected any filtering criteria for your search, this is not allowed';
                    $this->setFormErrorOn();
                    $this->displaySearchForm();
                    exit;
                }
            }
        } else {
            // default (i.e. called from menu link) is last 7 days
            $this->dsSearchForm->setUpdateModeUpdate();
            $this->dsSearchForm->setValue(
                'fromDate',
                date(
                    'Y-m-d',
                    strtotime("-1 week")
                )
            );
            $this->dsSearchForm->post();
        }

        if ($_REQUEST['CSV']) {
            $limit = false;                        // no row count limit
        } else {
            $limit = true;
        }

        if (isset($_REQUEST['sortColumn'])) {
            if (
                isset($_SESSION['sortColumn']) &&
                $_SESSION['sortColumn'] == $_REQUEST['sortColumn']
            ) {
                if ($_SESSION['sortDirection'] == 'ASC') {
                    $_SESSION['sortDirection'] = 'DESC';
                } else {
                    $_SESSION['sortDirection'] = 'ASC';
                }
            } else {
                $_SESSION['sortColumn'] = $_REQUEST['sortColumn'];
                $_SESSION['sortDirection'] = 'ASC';
            }
        }
        $this->buActivity->search(
            $this->dsSearchForm,
            $this->dsSearchResults,
            $_SESSION['sortColumn'],
            $_SESSION['sortDirection'],
            $limit
        );

        if ($_REQUEST['CSV'] != '') {
            $this->generateCSV();
        } else {
            $this->displaySearchForm(); // show results
        }
        exit;
    }

    /**
     * Display search form
     * @access private
     */
    function displaySearchForm()
    {
        $dsSearchForm = &$this->dsSearchForm; // ref to global
        $dsSearchResults = &$this->dsSearchResults; // ref to global
        $this->setMethodName('displaySearchForm');

        if (!$this->hasPermissions('PHPLIB_PERM_CUSTOMER')) {
            $urlCustomerPopup = Controller::buildLink(
                CTCNC_PAGE_CUSTOMER,
                array(
                    'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

            $urlCreateActivity = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTACTIVITY_ACT_CONTRACT_BY_CUSTOMER
                )
            );
        }// if (!$this->hasPermission('PHPLIB_PERM_CUSTOMER'){

        $fetchContractsURL = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => 'contractsForClient'
            )
        );

        $this->setTemplateFiles(
            'ActivitySearch',
            'ActivitySearch.inc'
        );

        $urlSubmit = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTCNC_ACT_SEARCH
            )
        );
        $this->setPageTitle('Search ' . CONFIG_SERVICE_REQUEST_DESC . 's');
        $dsSearchResults->initialise();
        if ($dsSearchForm->rowCount() == 0) {
            $this->buActivity->initialiseSearchForm($dsSearchForm);
        }
        // this user is a customer contact so read user and customer records
        if ($this->hasPermissions(PHPLIB_PERM_CUSTOMER)) {
            $dbeUser = new DBEUser($this);
            $dbeUser->setValue(
                'userID',
                $this->userID
            );
            $dbeUser->getRow();
            $dsSearchForm->setValue(
                'customerID',
                $dbeUser->getValue('customerID')
            );
        }
        if ($dsSearchForm->getValue('customerID') != 0) {
            $buCustomer = new BUCustomer($this);
            $buCustomer->getCustomerByID(
                $dsSearchForm->getValue('customerID'),
                $dsCustomer
            );
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }

        $this->template->set_var(
            array(
                'formError'                   => $this->formError,
                'customerID'                  => $dsSearchForm->getValue('customerID'),
                'customerString'              => $customerString,
                'problemID'                   => Controller::htmlDisplayText($dsSearchForm->getValue('problemID')),
                'problemIDMessage'            => Controller::htmlDisplayText($dsSearchForm->getMessage('problemID')),
                'callActivityID'              => Controller::htmlDisplayText($dsSearchForm->getValue('callActivityID')),
                'callActivityIDMessage'       => Controller::htmlDisplayText(
                    $dsSearchForm->getMessage('callActivityID')
                ),
                'serviceRequestSpentTime'     => Controller::htmlDisplayText(
                    $dsSearchForm->getValue('serviceRequestSpentTime')
                ),
                'individualActivitySpentTime' => Controller::htmlDisplayText(
                    $dsSearchForm->getValue('individualActivitySpentTime')
                ),
                'activityText'                => Controller::htmlDisplayText($dsSearchForm->getValue('activityText')),
                'fromDate'                    => Controller::dateYMDtoDMY($dsSearchForm->getValue('fromDate')),
                'fromDateMessage'             => $dsSearchForm->getMessage('fromDate'),
                'toDate'                      => Controller::dateYMDtoDMY($dsSearchForm->getValue('toDate')),
                'toDateMessage'               => $dsSearchForm->getMessage('toDate'),
                'rowsFound'                   => $dsSearchResults->rowCount(),
                'urlCreateActivity'           => $urlCreateActivity,
                'urlCustomerPopup'            => $urlCustomerPopup,
                'fetchContractsURL'           => $fetchContractsURL,
                'managementReviewOnlyChecked' => Controller::htmlChecked(
                    $dsSearchForm->getValue('managementReviewOnly')
                ),
                'urlSubmit'                   => $urlSubmit
            )
        );
        // activity status selector
        $this->template->set_block(
            'ActivitySearch',
            'statusBlock',
            'statuss'
        ); // ss avoids naming confict!
        if ($this->hasPermissions(PHPLIB_PERM_CUSTOMER)) {
            $statusArray = &$this->statusArrayCustomer;
        } else {
            $statusArray = &$this->statusArray;
        }

        foreach ($statusArray as $key => $value) {
            $statusSelected = ($dsSearchForm->getValue('status') == $key) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    'statusSelected'    => $statusSelected,
                    'status'            => $key,
                    'statusDescription' => $value
                )
            );
            $this->template->parse(
                'statuss',
                'statusBlock',
                true
            );
        }

        $this->rootCauseDropdown(
            $dsSearchForm->getValue('rootCauseID'),
            'ActivitySearch',
            'rootCauseBlock'
        );

        $this->priorityDropdown(
            $dsSearchForm->getValue('priority'),
            'ActivitySearch',
            'priorityBlock'
        );

        $this->breachedSlaDropdown($dsSearchForm->getValue('breachedSlaOption'));

        //Contract selection
        if ($dsSearchForm->getValue('customerID')) {
            $this->contractDropdown(
                $dsSearchForm->getValue('customerID'),
                $dsSearchForm->getValue('contractCustomerItemID'),
                'ActivitySearch',
                'contractBlock'
            );
        }

        $this->userDropdown(
            $dsSearchForm->getValue('userID'),
            'ActivitySearch'
        );

        $dbeCallActType = new DBECallActType($this);
        $dbeCallActType->setValue(
            'activeFlag',
            'Y'
        );
        $dbeCallActType->getRowsByColumn(
            'activeFlag',
            'description'
        );

        // activity type selector
        $this->template->set_block(
            'ActivitySearch',
            'activityTypeBlock',
            'activityTypes'
        );
        while ($dbeCallActType->fetchNext()) {
            $activityTypeSelected = ($dsSearchForm->getValue('callActTypeID') == $dbeCallActType->getValue(
                    'callActTypeID'
                )) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    'activityTypeSelected'    => $activityTypeSelected,
                    'callActTypeID'           => $dbeCallActType->getValue('callActTypeID'),
                    'activityTypeDescription' => $dbeCallActType->getValue('description')
                )
            );
            $this->template->parse(
                'activityTypes',
                'activityTypeBlock',
                true
            );
        }


        // display results
        $dsSearchResults->initialise();
        if ($dsSearchResults->rowCount() > 0) {
            $this->template->set_block(
                'ActivitySearch',
                'activityBlock',
                'activities'
            );
            $this->template->set_var(
                'txtExpand',
                'show/hide latest actvity'
            );
            $customerNameCol = $dsSearchResults->columnExists('customerName');
            $callActivityIDCol = $dsSearchResults->columnExists('callActivityID');
//            $projectDescriptionCol = $dsSearchResults->columnExists('projectDescription');
            $customerIDCol = $dsSearchResults->columnExists('customerID');
            $statusCol = $dsSearchResults->columnExists('status');
            $reasonCol = $dsSearchResults->columnExists('reason');
            $fixCol = $dsSearchResults->columnExists('fix');
            $dateCol = $dsSearchResults->columnExists('date');
            $startCol = $dsSearchResults->columnExists('startTime');
            $endCol = $dsSearchResults->columnExists('endTime');
            $contractDescriptionCol = $dsSearchResults->columnExists('contractDescription');
            $problemIDCol = $dsSearchResults->columnExists('problemID');

            /*
        if we are displaying checked T&M activities then show Generate Sales Order and Skip Sales Order buttons
        */
            if ($dsSearchForm->getValue('status') == 'CHECKED_T_AND_M') {
                $bulkActionButtons =
                    '<input name="Search" type="submit" value="Generate Sales Orders" />
          <input name="Search" type="submit" value="Skip Sales Orders" />';
                $checkAllBox =
                    '<input type="checkbox" name="checkAllBox" id="checkAllBox" value="0" onClick="checkAll();"/>';

            } elseif ($dsSearchForm->getValue('status') == 'CHECKED_NON_T_AND_M') {
                $bulkActionButtons =
                    '<input name="Search" type="submit" value="Complete SRs" />';
                $checkAllBox =
                    '<input type="checkbox" name="checkAllBox" id="checkAllBox" value="0" onClick="checkAll();"/>';
            } else {
                $bulkActionButtons = '';
                $checkAllBox = '';
            }

            $requestUri = $this->removeQuerystringVar(
                $_SERVER['REQUEST_URI'],
                'sortColumn'
            );

            $weirdColumns = '<td class="listHeadText">Start</td>
            <td class="listHeadText">End</td>
            <td class="listHeadTextRight"><a href="' . $requestUri . '&sortColumn=slaResponseHours">SLA</a></td>
            <td class="listHeadTextRight"><a href="' . $requestUri . '&sortColumn=respondedHours">Resp</a></td>';

            $headerColSpan = 13;

            if ($dsSearchForm->getValue('status') == 'CHECKED_T_AND_M' ||
                $dsSearchForm->getValue('status') == 'CHECKED_NON_T_AND_M') {
                $weirdColumns = '<td class="listHeadText"><div style="width: 100px">SO</div></td>';
                $headerColSpan = 10;
            }

            $this->template->set_var(
                array(
                    'bulkActionButtons' => $bulkActionButtons,
                    'checkAllBox'       => $checkAllBox,
                    'requestUri'        => $requestUri,
                    'weirdColumns'      => $weirdColumns,
                    'headerColSpan'     => $headerColSpan
                )
            );

            while ($dsSearchResults->fetchNext()) {
                $callActivityID = $dsSearchResults->getValue($callActivityIDCol);
                $problemID = $dsSearchResults->getValue($problemIDCol);

                /*
          if we are displaying checked T&M activities then show Generate Sales Order checkbox
          */


                $displayActivityURL =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'         => CTACTIVITY_ACT_DISPLAY_ACTIVITY,
                            'callActivityID' => $dsSearchResults->getValue($callActivityIDCol)
                        )
                    );

                $weirdFields = '<td align="top" class="listItemText">' . $dsSearchResults->getValue($startCol) . '</td>
            <td align="top" class="listItemText">' . $dsSearchResults->getValue($endCol) . '</td>
            <td align="right" nowrap>' . $dsSearchResults->getValue('slaResponseHours') . '</td>
            <td align="right" nowrap>' . $dsSearchResults->getValue('respondedHours') . '</td>';

                $contractField = $dsSearchResults->getValue($contractDescriptionCol);

                if (
                    $dsSearchForm->getValue('status') == 'CHECKED_T_AND_M' ||
                    $dsSearchForm->getValue('status') == 'CHECKED_NON_T_AND_M'
                ) {
                    $checkBox =
                        '<input type="checkbox" id="callActivityID" name="callActivityID[' . $callActivityID . ']" value="' . $callActivityID . '" />';

                    $dbeProblem = new DBEProblem($this);
                    $dbeProblem->getRow($problemID);


                    $salesOrderID = $dbeProblem->getValue(DBEProblem::linkedSalesOrderID);
                    $salesOrderLink = "";
                    if ($salesOrderID) {
                        $salesOrderURL =
                            Controller::buildLink(
                                'SalesOrder.php',
                                array(
                                    'action'    => 'displaySalesOrder',
                                    'ordheadID' => $salesOrderID
                                )
                            );

                        $salesOrderLink = '<a href="' . $salesOrderURL . '" target="_blank">' . $salesOrderID . '</a>';
                    }

                    $weirdFields = '<td class="listItemText">' . $salesOrderLink . '</td>';


                    $contracts = $this->getContractsForCustomer($dbeProblem->getValue(DBEProblem::customerID));

                    $contractCustomerItemID = $dbeProblem->getValue(DBEProblem::contractCustomerItemID);

                    $contractField = "<select name='problem[" . $problemID . "][contract]' onchange='tickBox()'>";


                    $contractField .= "<option value " . ($contractCustomerItemID ? '' : 'selected') . ">T&M</option>";

                    foreach ($contracts as $contractType => $contractItems) {

                        $contractField .= "<optgroup label='" . $contractType . "'>";

                        foreach ($contractItems as $contractItem) {
                            $selected = $contractCustomerItemID == $contractItem['id'];
                            $contractField .= "<option value='" . $contractItem['id'] . "'" . ($selected ? 'selected' : '') . ">" . $contractItem['description'] . " </option>";
                        }
                        $contractField .= "</optgroup>";
                    }

                    $contractField .= "</select>";


                } else {
                    $checkBox = '';
                }


                // Reason
                $reason = $dsSearchResults->getValue($reasonCol);

                $this->template->set_var(
                    array(
                        'listCustomerName'          => $dsSearchResults->getValue($customerNameCol),
                        'listContractDescription'   => $contractField,
                        //                        'listProjectDescription' => $dsSearchResults->getValue($projectDescriptionCol),
                        'listCallURL'               => $displayActivityURL,
                        'listCallActivityID'        => $dsSearchResults->getValue($callActivityIDCol),
                        'listProblemID'             => $problemID,
                        'listStatus'                => $dsSearchResults->getValue($statusCol),
                        'listDate'                  => Controller::dateYMDtoDMY($dsSearchResults->getValue($dateCol)),
                        //                        'listStart'                 => $dsSearchResults->getValue($startCol),
                        //                        'listEnd'                   => $dsSearchResults->getValue($endCol),
                        'listPriority'              => $dsSearchResults->getValue('priority'),
                        //                        'listSlaResponseHours'      => $dsSearchResults->getValue('slaResponseHours'),
                        //                        'listRespondedHours'        => $dsSearchResults->getValue('respondedHours'),
                        'listWorkingHours'          => $dsSearchResults->getValue('workingHours'),
                        'listActivityDurationHours' => $dsSearchResults->getValue('activityDurationHours'),
                        'listRootCause'             => $dsSearchResults->getValue('rootCause'),
                        'listFixEngineer'           => $dsSearchResults->getValue('fixEngineer'),
                        'listActivityCount'         => $dsSearchResults->getValue('activityCount'),
                        'reason'                    => substr(
                            common_stripEverything($reason),
                            0,
                            50
                        ),
                        'checkBox'                  => $checkBox,
                        'weirdFields'               => $weirdFields
                    )
                );
                $this->template->parse(
                    'activities',
                    'activityBlock',
                    true
                );
            }
        }
        $this->template->parse(
            'CONTENTS',
            'ActivitySearch',
            true
        );
        $this->parsePage();
    }

    function rootCauseDropdown($selectedID,
                               $template = 'ActivityCreate6',
                               $block = 'rootCauseBlock'
    )
    {
        $this->template->set_block(
            $template,
            $block,
            'rootCauses'
        );

        $buRootCause = new BURootCause($this);
        $buRootCause->getAll($dsRootCause);

        while ($dsRootCause->fetchNext()) {

            $rootCauseSelected = ($selectedID == $dsRootCause->getValue("rootCauseID")) ? CT_SELECTED : '';

            $this->template->set_var(

                array(
                    'rootCauseSelected'    => $rootCauseSelected,
                    'rootCauseID'          => $dsRootCause->getValue("rootCauseID"),
                    'rootCauseDescription' => $dsRootCause->getValue("description") . " (" . $dsRootCause->getValue(
                            "longDescription"
                        ) . ")"
                )

            );

            $this->template->parse(
                'rootCauses',
                $block,
                true
            );

        }
    }

    function priorityDropdown(
        $selectedID,
        $template = 'ActivityCreate6',
        $block = 'priorityBlock'
    )
    {
        $this->template->set_block(
            $template,
            $block,
            'priorities'
        );

        foreach ($this->buActivity->priorityArray as $key => $value) {

            $prioritySelected = ($selectedID == $key) ? CT_SELECTED : '';

            $this->template->set_var(
                array(
                    'prioritySelected' => $prioritySelected,
                    'priority'         => $key,
                    'priorityDesc'     => $value
                )
            );
            $this->template->parse(
                'priorities',
                $block,
                true
            );
        }
    }

    function breachedSlaDropdown(
        $selectedID,
        $template = 'ActivitySearch',
        $block = 'breachedSlaOptionBlock'
    )
    {
        $this->template->set_block(
            $template,
            $block,
            'breaches'
        );

        foreach ($this->buActivity->breachedSlaOptionArray as $key => $value) {

            $breachedSlaOptionSelected = ($selectedID == $key) ? CT_SELECTED : '';

            $this->template->set_var(
                array(
                    'breachedSlaOptionSelected' => $breachedSlaOptionSelected,
                    'breachedSlaOption'         => $key,
                    'breachedSlaOptionDesc'     => $value
                )
            );
            $this->template->parse(
                'breaches',
                $block,
                true
            );
        }
    }


    function contractDropdown(
        $customerID,
        $contractCustomerItemID,
        $templateName = 'ActivityCreate6',
        $blockName = 'contractBlock'
    )
    {

        $includeExpired = false;

        $buCustomerItem = new BUCustomerItem($this);
        $buCustomerItem->getContractsByCustomerID(
            $customerID,
            $dsContract
        );

        if ($contractCustomerItemID == '99') {
            $this->template->set_var(
                array(
                    'noContractSelected' => CT_SELECTED
                )
            );
        }
        if (!$contractCustomerItemID) {
            $this->template->set_var(
                array(
                    'tandMSelected' => CT_SELECTED
                )
            );
        }

        $this->template->set_block(
            $templateName,
            $blockName,
            'contracts'
        );

        $lastRenewalType = '';
        while ($dsContract->fetchNext()) {
            /*
        Option group renewal Type
        */
            if ($dsContract->getValue('renewalType') != $lastRenewalType) {
                if ($lastRenewalType != '') {
                    $optGroupClose = '</optgroup>';
                } else {
                    $optGroupClose = '';

                }

                $optGroupOpen = '<optgroup label="' . $dsContract->getValue('renewalType') . '">';
            } else {
                $optGroupOpen = '';
                $optGroupClose = '';
            }
            $lastRenewalType = $dsContract->getValue('renewalType');

            $contractSelected = ($contractCustomerItemID == $dsContract->getValue("customerItemID")) ? CT_SELECTED : '';

            $description = $dsContract->getValue("itemDescription") . ' ' . $dsContract->getValue(
                    'adslPhone'
                ) . ' ' . $dsContract->getValue('notes') . ' ' . $dsContract->getValue('postcode');

            $this->template->set_var(
                array(
                    'contractSelected'       => $contractSelected,
                    'contractCustomerItemID' => $dsContract->getValue("customerItemID"),
                    'contractDescription'    => $description,
                    'optGroupOpen'           => $optGroupOpen,
                    'optGroupClose'          => $optGroupClose
                )
            );
            $this->template->parse(
                'contracts',
                $blockName,
                true
            );
        }

    } //end userDropdown

    function userDropdown($userID,
                          $templateName,
                          $activeUsersOnly = true
    )
    {
        // user selection
        $dbeUser = new DBEUser($this);
        $dbeUser->getRows();

        $this->template->set_block(
            $templateName,
            'userBlock',
            'users'
        );

        while ($dbeUser->fetchNext()) {

            if ($userID == $dbeUser->getValue("userID")) {
                $userSelected = CT_SELECTED;
            } else {
                $userSelected = '';

                if ($activeUsersOnly && $dbeUser->getValue('activeFlag') == 'N') {
                    continue;
                }

            }

            $userSelected = ($userID == $dbeUser->getValue("userID")) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    'userSelected' => $userSelected,
                    'userID'       => $dbeUser->getValue("userID"),
                    'userName'     => $dbeUser->getValue("name")
                )
            );
            $this->template->parse(
                'users',
                'userBlock',
                true
            );
        }
    }

    function removeQuerystringVar($url,
                                  $key
    )
    {
        $url = preg_replace(
            '/(.*)(?|&)' . $key . '=[^&]+?(&)(.*)/i',
            '$1$2$4',
            $url . '&'
        );
        $url = substr(
            $url,
            0,
            -1
        );
        return $url;
    } // end activityCreate1

    function parsePage()
    {
        $urlLogo = '';
        $this->template->set_var(
            array(
                'urlLogo' => $urlLogo,
                'txtHome' => 'Home'
            )
        );
        parent::parsePage();
    }

    function countParamsSet($array)
    {
        $count = 0;
        $elements = $array[1];
        foreach ($elements as $key => $element) {
            if (!empty($element)) {
                $count++;
            }
        }
        return $count;
    }// end displayContracts

    function generateCSV()
    {
        $fileName = 'ACTIVITY.CSV';
        Header('Content-type: text/plain');
        Header('Content-Disposition: attachment; filename=' . $fileName);

        echo "customerName,postcode,contactName,serviceRequestID,priority,callActivityID,date,startTime,endTime,duration,value,activityType,userName,projectDescription,contractDescription,reason,internalNotes,managementReviewReason,rootCause\n";

        while ($this->dsSearchResults->fetchNext()) {
            echo $this->dsSearchResults->getExcelValue('customerName') . "," .
                $this->dsSearchResults->getExcelValue('postcode') . "," .
                $this->dsSearchResults->getExcelValue('contactName') . "," .
                $this->dsSearchResults->getExcelValue('problemID') . "," .
                $this->dsSearchResults->getExcelValue('priority') . "," .
                $this->dsSearchResults->getExcelValue('callActivityID') . "," .
                $this->dsSearchResults->getExcelValue('date') . "," .
                $this->dsSearchResults->getExcelValue('startTime') . "," .
                $this->dsSearchResults->getExcelValue('endTime') . "," .
                number_format(
                    $this->dsSearchResults->getExcelValue('duration') / 60 / 60,
                    2
                ) . "," .
                number_format(
                    $this->dsSearchResults->getExcelValue('duration') / 60 / 60,
                    2
                ) * $this->dsSearchResults->getValue('salePrice') . "," .
                $this->dsSearchResults->getExcelValue('activityType') . "," .
                $this->dsSearchResults->getExcelValue('userName') . "," .
                $this->dsSearchResults->getExcelValue('projectDescription') . "," .
                $this->dsSearchResults->getExcelValue('contractDescription') . "," .
                str_replace(
                    ',',
                    '\'',
                    addslashes(common_stripEverything($this->dsSearchResults->getValue('reason')))
                ) . "," .
                str_replace(
                    ',',
                    '\'',
                    addslashes(common_stripEverything($this->dsSearchResults->getValue('internalNotes')))
                ) . "," .
                $this->dsSearchResults->getExcelValue('managementReviewReason') . "," .
                $this->dsSearchResults->getExcelValue('rootCause') .
                "\n";
        }
        $this->pageClose();
        exit;
    } // end contractDropdown

    /**
     * Edit/Add Activity
     * @access private
     */
    function displayActivity()
    {
        $this->setMethodName('displayActivity');
        $this->setPageTitle('Activity');

        $this->buActivity->getActivityByID(
            $_REQUEST['callActivityID'],
            $dsCallActivity
        );
        $callActivityID = $dsCallActivity->getValue('callActivityID');

        $problemID = $dsCallActivity->getValue('problemID');
        $dbeJProblem = new DBEJProblem($this);
        $dbeJProblem->getRow($problemID);

        $this->setTemplateFiles(
            array(
                'ActivityDisplay'        => 'ActivityDisplay.inc',
                'ActivityDisplayOnSite'  => 'ActivityDisplayOnSite.inc',
                'ActivityDisplayContext' => 'ActivityDisplayContext.inc',
                'ActivityWizardHeader'   => 'ActivityWizardHeader.inc'
            )
        );

        // only allow include travel
        if (
            $dsCallActivity->getValue('travelFlag') == 'Y' &&
            strstr(
                $_SERVER['HTTP_REFERER'],
                'search'
            )
        ) {

            $_SESSION['includeTravel'] = 1;

        } else {

            if ($_REQUEST['toggleIncludeTravel']) {

                if ($_SESSION['includeTravel']) {

                    $_SESSION['includeTravel'] = 0;

                } else {

                    $_SESSION['includeTravel'] = 1;

                }

            }

        }

        if (
            $dsCallActivity->getValue('callActTypeID') == CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID
        ) {

            $_SESSION['includeOperationalTasks'] = 1;

        } else {

            if ($_REQUEST['toggleIncludeOperationalTasks']) {

                if ($_SESSION['includeOperationalTasks']) {

                    $_SESSION['includeOperationalTasks'] = 0;

                } else {

                    $_SESSION['includeOperationalTasks'] = 1;

                }

            }
        }


        if (
            $dsCallActivity->getValue('callActTypeID') == CONFIG_SERVER_GUARD_UPDATE_ACTIVITY_TYPE_ID &&
            strstr(
                $_SERVER['HTTP_REFERER'],
                'search'
            )
        ) {

            $_SESSION['includeServerGuardUpdates'] = 1;

        } else {

            if ($_REQUEST['toggleIncludeServerGuardUpdates']) {

                if ($_SESSION['includeServerGuardUpdates']) {

                    $_SESSION['includeServerGuardUpdates'] = 0;

                } else {

                    $_SESSION['includeServerGuardUpdates'] = 1;
                }
            }
        }

        if ($_REQUEST['toggleContext']) {

            if ($_SESSION['context'] == 'project') {

                $_SESSION['context'] = 'Problem';

            } else {
                $_SESSION['context'] = 'project';
            }
        }

        if (!$dsCallActivity->getValue('projectID')) {

            $_SESSION['context'] = 'Problem';

        } else {
            $this->template->parse(
                'activityDisplayContext',
                'ActivityDisplayContext',
                true
            );
        }

        // get list of other activities in this problem or project
        $linksArray =
            $this->buActivity->getNavigateLinks(
                $callActivityID,
                $dbeCallActivity,
                $_SESSION['includeTravel'],
                $_SESSION['includeOperationalTasks'],
                $_SESSION['includeServerGuardUpdates']
            );

        /*
      Now decide what we should do about travel
      */
        if (!$_SESSION['includeTravel'] && $dsCallActivity->getValue('travelFlag') == 'Y') {

            if ($dbeCallActivity->rowCount() > 0) {

                $dbeCallActivity->fetchNext();

                $callActivityID = $dbeCallActivity->getValue('callActivityID');

                $this->buActivity->getActivityByID(
                    $callActivityID,
                    $dsCallActivity
                );
            }

        }

        $this->setPageTitle(CONFIG_SERVICE_REQUEST_DESC . ' ' . $dsCallActivity->getValue('problemID'));

        $buCustomer = new BUCustomer($this);

        $buCustomer->getCustomerByID(
            $dsCallActivity->getValue('customerID'),
            $dsCustomer
        );

        $buCustomer->getSiteByCustomerIDSiteNo(
            $dsCallActivity->getValue('customerID'),
            $dsCallActivity->getValue('siteNo'),
            $dsSite
        );

        $buCustomer->getContactByID(
            $dsCallActivity->getValue('contactID'),
            $dsContact
        );


        $customerDetails =
            $dsCustomer->getValue(DBECustomer::name) .
            ', ' . $dsSite->getValue(DBESite::add1) .
            ', ' . $dsSite->getValue(DBESite::add2) .
            ', ' . $dsSite->getValue(DBESite::add3) .
            ', ' . $dsSite->getValue(DBESite::town) .
            ', ' . $dsSite->getValue(DBESite::postcode);

        if ($dsContact) {
            $customerDetails .=
                ', ' . $dsContact->getValue('firstName') . ' ' . $dsContact->getValue('lastName');


            if ($dsContact->getValue('email') != '') {
                $customerDetails .=
                    '<A HREF="mailto:' . $dsContact->getValue(
                        'email'
                    ) . '?subject=Service Request ' . $dsCallActivity->getValue('problemID') . '"' .
                    ' title="Send email to contact"><img src="images/email.gif" border="0"></A>';
            }
        }
        $customerName = $customerDetails;
        /*
      allow delete if open (no end time) OR (if user is member of Supervisor group then
      not authorised)
      */
        if (
            $dsCallActivity->getValue('endTime') == '' ||
            (
                $dsCallActivity->getValue('status') != 'A' AND
                $this->hasPermissions(PHPLIB_PERM_MAINTENANCE)
            )
        ) {
            $urlDeleteActivity =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'         => CTACTIVITY_ACT_DELETE_ACTIVITY,
                        'callActivityID' => $callActivityID
                    )
                );
            $txtDeleteActivity = 'Delete';

            if ($this->buActivity->countActivitiesInProblem($problemID) == 1) {

                $deleteLink = '<A href="' . $urlDeleteActivity . '"  title="Delete Request" onClick="if(!confirm(\'Deleting this activity will remove all traces of this Service Request from the system. Are you sure?\')) return(false)">Delete Request</A>';

            } else {

                $deleteLink = '<A href="' . $urlDeleteActivity . '"  title="Delete Activity" onClick="if(!confirm(\'Delete this activity?\')) return(false)">Delete Activity</A>';

            }
        } else {

            $deleteLink = '';

        }
//        /*
//      Allow certain named users to add managers comments
//      */
//        if (in_array($this->userID, $GLOBALS['can_add_manager_comment'])) {
//
//            $urlManagerComment =
//                Controller::buildLink(
//                    'Activity.php',
//                    array(
//                        'action' => 'managerCommentPopup',
//                        'problemID' => $problemID,
//                        'htmlFmt' => CT_HTML_FMT_POPUP
//                    )
//                );
//
//            if (
//            $this->buActivity->getManagerComment($problemID)
//            ) {
//
//                $txtManagerComment = '<div class="navigateLinkCustomerNoteExists">Comment</div>';
//            } else {
//                $txtManagerComment = 'Comment';
//
//            }
//        } else {
//            $urlManagerComment = '';
//            $txtManagerComment = '';
//        }
//
//        $this->template->set_var(
//            array(
//                'urlManagerComment' => $urlManagerComment,
//                'txtManagerComment' => $txtManagerComment
//            )
//        );
//
//        $this->template->set_var(
//            array(
//                'urlManagerComment' => $urlManagerComment,
//                'txtManagerComment' => $txtManagerComment
//            )
//        );
        /*
      allow move of activity/Problem to another Problem
      */
        if ($this->hasPermissions(PHPLIB_PERM_SUPERVISOR)) {

            $urlLinkToProblem =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'             => 'linkProblems',
                        'fromCallActivityID' => $callActivityID
                    )
                );
            $txtLinkToProblem = 'Move';

            $this->template->set_var(
                array(
                    'urlLinkToProblem'     => $urlLinkToProblem,
                    'toCustomerActivityID' => $toCustomerActivityID
                )
            );
            $this->template->parse(
                'activityDisplayLinkToProblem',
                'ActivityDisplayLinkToProblem',
                true
            );
        }

        /*
      Expenses edit
      */
        if ($dsCallActivity->getValue('allowExpensesFlag') == 'Y') {
            $urlViewExpenses =
                Controller::buildLink(
                    'Expense.php',
                    array(
                        'action'         => CTCNC_ACT_VIEW,
                        'callActivityID' => $callActivityID
                    )
                );
            $txtViewExpenses = 'Expenses';
        } else {
            $urlViewExpenses = '';
            $txtViewExpenses = '';
        }
        /*
      Show check link if this activity is closed
      */
        $buUser = new BUUser($this);

        if ($buUser->isSdManager($this->userID) &&
            $dsCallActivity->getValue(DBEJCallActivity::problemHideFromCustomerFlag) == 'Y') {
            $urlUnhideSR =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'         => 'unhideSR',
                        'callActivityID' => $callActivityID
                    )
                );
            $txtUnhideSR = 'Unhide SR';
        } else {
            $urlUnhideSR = '';
            $txtUnhideSR = '';
        }
        /*
      Show SCR report and visit confirmation links if this activity type allows
      */
        $urlAddToCalendar =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => CTACTIVITY_ACT_ADD_TO_CALENDAR,
                    'callActivityID' => $callActivityID
                )
            );
        $txtAddToCalendar = 'Calendar';
        if ($dsCallActivity->getValue('allowSCRFlag') == 'Y') {

            $urlSendVistEmail =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'         => CTACTIVITY_ACT_SEND_VISIT_EMAIL,
                        'callActivityID' => $callActivityID
                    )
                );
            $txtSendVisitEmail = 'Confirm Email';

            if (            // old call ref SCR
                $dsCallActivity->getValue('projectID') != 0 AND
                $dsCallActivity->getValue('projectID') > 5 AND
                $dsCallActivity->getValue('projectID') < 21249
            ) {
                $projectSCRText = 'Old Call SCR';
                $activitySCRText = '';
            } else {
                $projectSCRText = '';
                $activitySCRText = 'Activity SCR';
            }


        } else {
            $urlSendVisitEmail = '';
            $txtSendVisitEmail = '';
        }


        if ($dbeJProblem->getValue('status') == 'P') {

            $urlChangeRequest =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'             => 'createFollowOnActivity',
                        'callActivityID'     => $callActivityID,
                        'callActivityTypeID' => CONFIG_CHANGE_REQUEST_ACTIVITY_TYPE_ID
                    )
                );
            $txtChangeRequest = 'Change Request';
        } else {
            $urlChangeRequest = '';
            $txtChangeRequest = '';
        }


        if (
            $dbeJProblem->getValue('status') == 'P' &&
            $dbeJProblem->getValue('rootCauseID') != false
        ) {

            $urlSetProblemFixed =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'         => 'setProblemFixed',
                        'problemID'      => $dsCallActivity->getValue('problemID'),
                        'callActivityID' => $dsCallActivity->getValue('callActivityID')
                    )
                );
            $txtSetProblemFixed = 'Fixed';
        } else {
            $urlSetProblemFixed = '';
            $txtSetProblemFixed = '';
        }

        $this->template->set_block(
            'ActivityDisplay',
            'jumpBlock',
            'jumpActivities'
        );
        $dbeCallActivity->initialise();

        while ($dbeCallActivity->fetchNext()) {

            $urlJumpToActivity =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'         => CTACTIVITY_ACT_DISPLAY_ACTIVITY,
                        'callActivityID' => $dbeCallActivity->getValue('callActivityID')
                    )
                );


            $this->template->set_var(
                array(
                    'callActivityID'    => $dbeCallActivity->getValue('callActivityID'),
                    'dateEngineer'      => $dbeCallActivity->getValue('dateEngineer'),
                    'contactName'       => $dbeCallActivity->getValue('contactName'),
                    'activityType'      => $dbeCallActivity->getValue('activityType'),
                    'urlJumpToActivity' => $urlJumpToActivity,
                    'selected'          => $dbeCallActivity->getValue(
                        'callActivityID'
                    ) == $callActivityID ? 'SELECTED' : ''
                )
            );
            $this->template->parse(
                'jumpActivities',
                'jumpBlock',
                true
            );
        }


        $activityChainCount = $dbeCallActivity->rowCount();
        // next/previous activity links

        if ($linksArray['previous']) {

            $urlPreviousActivity =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'         => CTACTIVITY_ACT_DISPLAY_ACTIVITY,
                        'callActivityID' => $linksArray['previous']
                    )
                );

            $linkPreviousActivity =
                '<button type="button" onclick="location.href=\'' . $urlPreviousActivity . '\';"><i class="fa fa-backward" aria-hidden="true"></i> Previous</button>';

        }

        if ($linksArray['first']) {

            $urlFirstActivity =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'         => CTACTIVITY_ACT_DISPLAY_ACTIVITY,
                        'callActivityID' => $linksArray['first']
                    )
                );

            $linkFirstActivity =
                '<button type="button" onclick="location.href=\'' . $urlFirstActivity . '\';"><i class="fa fa-step-backward"></i> First</button>';
        }

        if ($linksArray['next']) {

            $urlNextActivity =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'         => CTACTIVITY_ACT_DISPLAY_ACTIVITY,
                        'callActivityID' => $linksArray['next']
                    )
                );

            $linkNextActivity =
                '<button type="button" style="white-space: nowrap" onclick="location.href=\'' . $urlNextActivity . '\';">Next <i class="fa fa-forward" aria-hidden="true"></i></button>';
        }

        if ($linksArray['last']) {

            $urlLastActivity =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'         => CTACTIVITY_ACT_DISPLAY_ACTIVITY,
                        'callActivityID' => $linksArray['last']
                    )
                );

            $linkLastActivity =
                '<button type="button" style="white-space: nowrap" onclick="location.href=\'' . $urlLastActivity . '\';">Last <i class="fa fa-fast-forward" aria-hidden="true"></i></button>';
        }

        $urlToggleCriticalFlag =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => 'toggleCriticalFlag',
                    'callActivityID' => $callActivityID
                )
            );

        $urlToggleMonitoringFlag =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => 'toggleMonitoringFlag',
                    'callActivityID' => $callActivityID
                )
            );

        $urlToggleIncludeTravel =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'              => CTACTIVITY_ACT_DISPLAY_ACTIVITY,
                    'callActivityID'      => $callActivityID,
                    'toggleIncludeTravel' => '1'
                )
            );

        $urlToggleIncludeOperationalTasks =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'                        => CTACTIVITY_ACT_DISPLAY_ACTIVITY,
                    'callActivityID'                => $callActivityID,
                    'toggleIncludeOperationalTasks' => '1'
                )
            );

        $urlToggleIncludeServerGuardUpdates =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'                          => CTACTIVITY_ACT_DISPLAY_ACTIVITY,
                    'callActivityID'                  => $callActivityID,
                    'toggleIncludeServerGuardUpdates' => '1'
                )
            );

        $urlToggleContext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => CTACTIVITY_ACT_DISPLAY_ACTIVITY,
                    'callActivityID' => $callActivityID,
                    'toggleContext'  => '1'
                )
            );
        $buCustomerItem = new BUCustomerItem($this);
        $minResponseTime = $buCustomerItem->getMinResponseTime($dsCallActivity->getValue('customerID'));

        $problemStatus = $this->buActivity->problemStatusArray[$dbeJProblem->getValue('status')];

        $dbeLastActivity = $this->buActivity->getLastActivityInProblem($dsCallActivity->getValue('problemID'));


        // If In Progress, find out whether this problem is waiting on CNC or on Customer
        if ($dbeJProblem->getValue('status') == 'P') {

            if ($dbeJProblem->getValue('awaitingCustomerResponseFlag') == 'Y') {

                $problemStatus .= ' - Awaiting Customer';

            } else {

                $problemStatus .= ' - Awaiting CNC';

            }

        }

        /*
      Display user name with green background if this activity is being edited now

      Also, disable the edit link
      */
        $urlEditActivity = '';
        $txtEditActivity = '';

        if (
            $dbeLastActivity->getValue('callActTypeID') == 0 &&
            $dbeLastActivity->getValue('userID') != $GLOBALS['auth']->is_authenticated()
        ) {
            $currentUserBgColor = self::GREEN;
            $currentUser = $dbeLastActivity->getValue('userName') . ' Is Adding New Activity To This Request Now';
        } else {
            $currentUserBgColor = self::CONTENT;
            $currentUser = '';;

            if ($this->buActivity->canEdit($dsCallActivity)) {

                $urlEditActivity =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'         => CTACTIVITY_ACT_EDIT_ACTIVITY,
                            'callActivityID' => $callActivityID
                        )
                    );
                $txtEditActivity = 'Edit';
            }

            if (
                $dsCustomer->getValue(DBECustomer::referredFlag) != 'Y' &&   // customer not referred
                $dbeJProblem->getValue('status') != 'C'           // not completed
            ) {
                $urlDuplicate =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'         => 'createFollowOnActivity',
                            'callActivityID' => $callActivityID
                        )
                    );
                $urlAddTravel =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'             => 'createFollowOnActivity',
                            'callActivityID'     => $callActivityID,
                            'callActivityTypeID' => CONFIG_ENGINEER_TRAVEL_ACTIVITY_TYPE_ID
                        )
                    );

                /*
            if the problem status is Initial, not ServerGuard and Email Flag is set to email customer then warn user about commencing work email to client
            */
                if (
                    $dbeCallActivity->getValue('problemStatus') == 'I' &&
                    $dbeCallActivity->getValue('serverGuard') == 'N' &&
                    $dbeJProblem->getValue('hideFromCustomerFlag') == 'N'
                ) {
                    $followLink = '<A href="' . $urlDuplicate . '"  title="Create Follow On" onClick="if(!confirm(\'You are about to commence work and an email will be sent to the customer?\')) return(false)"> Follow-on</A>';
                } else {
                    $followLink = '<A href="' . $urlDuplicate . '"  title="Create Follow On"> Follow-on</A>';

                }
                $travelLink = '<A href="' . $urlAddTravel . '"  title="Add travel">Add Travel</A>';
            }

        }

        $urlMessageToSales =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => 'messageToSales',
                    'callActivityID' => $callActivityID,
                )
            );
        $urlSalesRequest =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => 'sendSalesRequest',
                    'problemID' => $problemID,
                )
            );

        if ($dsCallActivity->getValue('contractCustomerItemID')) {
            $dbeContract = new DBEJContract($this);
            $dbeContract->getRowByContractID($dsCallActivity->getValue('contractCustomerItemID'));
            $contractDescription = Controller::htmlDisplayText(
                $description = $dbeContract->getValue("itemDescription") . ' ' . $dbeContract->getValue(
                        'adslPhone'
                    ) . ' ' . $dbeContract->getValue('notes') . ' ' . $dbeContract->getValue('postcode')
            );
        } else {
            $contractDescription = 'T & M';
        }

        $urlLinkedSalesOrder =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'         => 'editLinkedSalesOrder',
                    'htmlFmt'        => CT_HTML_FMT_POPUP,
                    'callActivityID' => $callActivityID
                )
            );

        if ($this->hasPermissions(PHPLIB_PERM_SUPERVISOR)) {
            $disabled = ''; // not
        } else {
            $disabled = CTCNC_HTML_DISABLED;
        }

        if (
            $dsCallActivity->getValue('hideFromCustomerFlag') == 'Y' ||
            $dsCallActivity->getValue('problemHideFromCustomerFlag') == 'Y'
        ) {
            $hiddenText = 'Hidden From Customer';
        } else {
            $hiddenText = '';
        }

        if ((int)$dbeJProblem->getValue(DBEProblem::authorisedBy)) {
            $dbeContact = new DBEContact($this);

            $dbeContact->getRow($dbeJProblem->getValue(DBEProblem::authorisedBy));

            $authorisedByName = $dbeContact->getValue(DBEContact::firstName) . " " . $dbeContact->getValue(
                    DBEContact::lastName
                );
        }


        $this->template->set_var(
            array(
                'hiddenText'               => $hiddenText,
                'currentUserBgColor'       => $currentUserBgColor,
                'currentUser'              => $currentUser,
                'problemPriority'          => $this->buActivity->priorityArray[$dbeJProblem->getValue('priority')],
                'problemStatus'            => $problemStatus,
                'renewalsLink'             => $this->getRenewalsLink($dsCallActivity->getValue('customerID')),
                'callActivityID'           => $callActivityID,
                'problemID'                => $dsCallActivity->getValue('problemID'),
                'customerID'               => $dsCallActivity->getValue('customerID'),
                'underContractFlag'        => $dsCallActivity->getValue('underContractFlag'),
                'contactName'              => Controller::htmlDisplayText($dsCallActivity->getValue('contactName')),
                'engineerName'             => Controller::htmlDisplayText($dsCallActivity->getValue('userName')),
                'priority'                 => Controller::htmlDisplayText(
                    $this->buActivity->priorityArray[$dsCallActivity->getValue('priority')]
                ),
                'customerDetails'          => $customerDetails,
                'customerName'             => $customerDetails,
                'customerNameDisplayClass' => $this->getCustomerNameDisplayClass($dsCustomer),
                'urlCustomer'              => $this->getCustomerUrl($dsCallActivity->getValue('customerID')),
                'urlMessageToSales'        => $urlMessageToSales,
                'callDate'                 => Controller::dateYMDtoDMY($dsCallActivity->getValue('date')),
                'customerItemID'           => $dsCallActivity->getValue('customerItemID'),
                'contractDescription'      => $contractDescription,
                'projectDescription'       => Controller::htmlDisplayText(
                    $dsCallActivity->getValue('projectDescription')
                ),
                'date'                     => Controller::dateYMDtoDMY($dsCallActivity->getValue('date')),
                'completeDate'             => Controller::dateYMDtoDMY($dsCallActivity->getValue('completeDate')),
                'curValue'                 => Controller::htmlDisplayText($dsCallActivity->getValue('curValue')),
                'startTime'                => Controller::htmlDisplayText($dsCallActivity->getValue('startTime')),
                'endTime'                  => Controller::htmlDisplayText($dsCallActivity->getValue('endTime')),
                'reason'                   => $dsCallActivity->getValue('reason'),
                'internalNotes'            => $dsCallActivity->getValue('internalNotes'),
                'siteDesc'                 => Controller::htmlInputText($dsCallActivity->getValue('siteDesc')),
                'status'                   => $dsCallActivity->getValue('status'),
                'rootCauseDescription'     => Controller::htmlInputText(
                    $dsCallActivity->getValue('rootCauseDescription')
                ),
                'urlEditActivity'          => $urlEditActivity,
                'txtEditActivity'          => $txtEditActivity,
                'urlSetActivityComplete'   => $urlSetActivityComplete,
                'txtSetActivityComplete'   => $txtSetActivityComplete,
                'urlSetProblemFixed'       => $urlSetProblemFixed,
                'txtSetProblemFixed'       => $txtSetProblemFixed,
                'urlViewExpenses'          => $urlViewExpenses,
                'txtViewExpenses'          => $txtViewExpenses,
                'deleteLink'               => $deleteLink,

                'urlToggleIncludeTravel' => $urlToggleIncludeTravel,

                'urlToggleOperationalTasks' => $urlToggleOperationalTasks,

                'urlToggleCriticalFlag'          => $urlToggleCriticalFlag,
                'criticalFlagChecked'            => $dsCallActivity->getValue('criticalFlag') == 'Y' ? 'CHECKED' : '',
                'urlToggleMonitoringFlag'        => $urlToggleMonitoringFlag,
                'monitoringFlagChecked'          => $this->checkMonitoring(
                    $dsCallActivity->getValue('problemID')
                ) ? 'CHECKED' : '',
                'includeOperationalTasksChecked' => $_SESSION['includeOperationalTasks'] ? 'CHECKED' : '',

                'urlToggleIncludeServerGuardUpdates' => $urlToggleIncludeServerGuardUpdates,
                'urlToggleIncludeOperationalTasks'   => $urlToggleIncludeOperationalTasks,
                'urlToggleContext'                   => $urlToggleContext,
                'followLink'                         => $followLink,
                'travelLink'                         => $travelLink,
                'urlUnhideSR'                        => $urlUnhideSR,
                'txtUnhideSR'                        => $txtUnhideSR,
                'activityType'                       => Controller::htmlDisplayText(
                    $dsCallActivity->getValue('activityType')
                ),
                'serverGuard'                        => Controller::htmlDisplayText(
                    $this->serverGuardArray[$dsCallActivity->getValue('serverGuard')]
                ),
                'urlAddToCalendar'                   => $urlAddToCalendar,
                'txtAddToCalendar'                   => $txtAddToCalendar,
                'urlChangeRequest'                   => $urlChangeRequest,
                'txtChangeRequest'                   => $txtChangeRequest,
                'urlSendVistEmail'                   => $urlSendVistEmail,
                'txtSendVisitEmail'                  => $txtSendVisitEmail,
                'linkNextActivity'                   => $linkNextActivity,
                'linkLastActivity'                   => $linkLastActivity,
                'linkPreviousActivity'               => $linkPreviousActivity,
                'linkFirstActivity'                  => $linkFirstActivity,
                'projectID'                          => $dsCallActivity->getValue('projectID'),
                'projectSCRText'                     => $projectSCRText,
                'activitySCRText'                    => $activitySCRText,
                'activityChainCount'                 => $activityChainCount,
                'thisRowNumber'                      => $linksArray['thisRowNumber'],
                'includeTravelChecked'               => $_SESSION['includeTravel'] ? 'CHECKED' : '',
                'includeServerGuardUpdatesChecked'   => $_SESSION['includeServerGuardUpdates'] ? 'CHECKED' : '',
                'projectChecked'                     => $_SESSION['context'] == 'project' ? 'CHECKED' : '',
                'minResponseTime'                    => $minResponseTime,
                'totalActivityDurationHours'         => $dbeJProblem->getValue('totalActivityDurationHours'),
                'chargeableActivityDurationHours'    => $dbeJProblem->getValue('chargeableActivityDurationHours'),
                'currentDocumentsLink'               => $currentDocumentsLink,
                'problemHistoryLink'                 => $this->getProblemHistoryLink(
                    $dsCallActivity->getValue('problemID')
                ),
                'projectLink'                        => BUProject::getCurrentProjectLink(
                    $dsCallActivity->getValue('customerID')
                ),
                'passwordLink'                       => $this->getPasswordLink($dsCallActivity->getValue('customerID')),
                'generatePasswordLink'               => $this->getGeneratePasswordLink(),
                'thirdPartyContactLink'              => $this->getThirdPartyContactLink(
                    $dsCallActivity->getValue('customerID')
                ),
                'contractListPopupLink'              => $this->getContractListPopupLink(
                    $dsCallActivity->getValue('customerID')
                ),
                'contactHistoryLink'                 => $this->getServiceRequestForContactLink(
                    $dsCallActivity->getValue(DBECallActivity::contactID)
                ),
                'salesOrderLink'                     => $this->getSalesOrderLink(
                    $dsCallActivity->getValue('linkedSalesOrderID')
                ),
                'contactNotes'                       => $dsCallActivity->getValue('contactNotes'),
                'techNotes'                          => $dsCallActivity->getValue('techNotes'),
                'urlLinkedSalesOrder'                => $urlLinkedSalesOrder,
                'urlSalesRequest'                    => $urlSalesRequest,
                'disabled'                           => $disabled,
                'contactPhone'                       => $buCustomer->getContactPhoneForHtml(
                    $dsCallActivity->getValue('contactID')
                ),
                'authorisedByHide'                   => $authorisedByName ? '' : "hidden",
                'authorisedByName'                   => $authorisedByName

            )
        );

        $problemID = $dsCallActivity->getValue('problemID');
        $this->documents(
            $callActivityID,
            $problemID,
            'ActivityDisplay'
        );

        /*
      On Site Activities within 5 days

      show a list of any on-site activity within 5 days either side
      */
        $db =
            $this->buActivity->getOnSiteActivitiesWithinFiveDaysOfActivity(
                $callActivityID,
                $problemID,
                $dsCallActivity->getValue('date')
            );

        $db->next_record();

        if ($db->Record) {

            $haveOnSiteActivitiesWithinFiveDays = true;

            $this->template->set_block(
                'ActivityDisplayOnSite',
                'onSiteActivityBlock',
                'activities'
            );

            do {

                $urlOnSiteActivity =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'         => CTACTIVITY_ACT_DISPLAY_ACTIVITY,
                            'callActivityID' => $db->Record['caa_callactivityno']
                        )
                    );


                $this->template->set_var(
                    array(
                        'urlOnSiteActivity'  => $urlOnSiteActivity,
                        'onSiteProblemID'    => $db->Record['caa_problemno'],
                        'onSiteEngineerName' => $db->Record['cns_name'],
                        'onSiteActivityDate' => $db->Record['formattedDate']
                    )
                );

                $this->template->parse(
                    'activities',
                    'onSiteActivityBlock',
                    true
                );

            } while ($db->next_record());

        }// end if
        else {

            $haveOnSiteActivitiesWithinFiveDays = false;

        }

        /*
      Expenses section
      */
        if ($dsCallActivity->getValue('allowExpensesFlag') == 'Y') {

            $buExpense = new BUExpense($this);

            $this->template->set_var(
                'totalValue',
                Controller::formatNumber($totalValue)
            );

            $buExpense->getExpensesByCallActivityID(
                $callActivityID,
                $dsExpense
            );

            if ($dsExpense->rowCount() > 0) {
                $totalValue = 0;
                $this->template->set_block(
                    'ActivityDisplay',
                    'expenseBlock',
                    'expenses'
                );
                while ($dsExpense->fetchNext()) {

                    $expenseID = $dsExpense->getValue('expenseID');

                    $this->template->set_var(
                        array(
                            'expenseID'   => $expenseID,
                            'expenseType' => Controller::htmlDisplayText($dsExpense->getValue('expenseType')),
                            'mileage'     => Controller::htmlDisplayText($dsExpense->getValue('mileage')),
                            'value'       => Controller::formatNumber($dsExpense->getValue('value')),
                            'vatFlag'     => Controller::htmlDisplayText($dsExpense->getValue('vatFlag'))
                        )
                    );

                    $totalValue += $dsExpense->getValue('value');

                    $this->template->parse(
                        'expenses',
                        'expenseBlock',
                        true
                    );

                }//while $dsExpense->fetchNext()

                $this->template->set_var(
                    'totalValue',
                    Controller::formatNumber($totalValue)
                );

            }
        }
        /*
      End of expenses section
      */

        if ($haveOnSiteActivitiesWithinFiveDays) {
            $this->template->parse(
                'onSiteActivities',
                'ActivityDisplayOnSite',
                true
            );
        }

        $this->template->parse(
            'activityWizardHeader',
            'ActivityWizardHeader',
            true
        );
        $this->template->parse(
            'CONTENTS',
            'ActivityDisplay',
            true
        );
        $this->parsePage();
    } // end siteDropdown

    function getRenewalsLink($customerID)
    {
        $renewalsLinkURL =
            Controller::buildLink(
                'RenewalReport.php',
                array(
                    'action'     => 'produceReport',
                    'customerID' => $customerID
                )
            );


        $renewalsLink = '<a href="' . $renewalsLinkURL . '" target="_blank" title="Renewals">Renewal Information</a>';

        return $renewalsLink;
    }// end displayProjects

    function getCustomerNameDisplayClass($dsCustomer)
    {
        if (
            $dsCustomer->getValue(DBECustomer::specialAttentionFlag) == 'Y' &&
            $dsCustomer->getValue(DBECustomer::specialAttentionEndDate) >= date('Y-m-d')
        ) {
            $ret = 'specialAttentionCustomer';
        } else {
            $ret = '';
        }
        return $ret;
    }// end create5

    function getCustomerUrl($customerID)
    {
        return Controller::buildLink(
            'SalesOrder.php',
            array(
                'action'     => 'search',
                'customerID' => $customerID
            )
        );

    }// end displayOpenSrs


//----------------

    function getProblemHistoryLink($problemID)
    {
        if ($problemID) {
            $url = Controller::buildLink(
                'Activity.php',
                array(
                    'action'    => 'problemHistoryPopup',
                    'problemID' => $problemID,
                    'htmlFmt'   => CT_HTML_FMT_POPUP
                )
            );

            $link = '| <A HREF="' . $url . ' " target="_blank" >History</A>';
        } else {
            $link = '';
        }

        return $link;

    }// end create6

    function getPasswordLink($customerID)
    {
        $passwordLinkURL =
            Controller::buildLink(
                'Password.php',
                array(
                    'action'     => 'list',
                    'customerID' => $customerID
                )
            );


        $passwordLink = '| <a href="' . $passwordLinkURL . '" target="_blank" title="Passwords">Passwords</a>';

        return $passwordLink;
    }

    function getThirdPartyContactLink($customerID)
    {
        $thirdPartyContactLinkURL =
            Controller::buildLink(
                'ThirdPartyContact.php',
                array(
                    'action'     => 'list',
                    'customerID' => $customerID
                )
            );


        $thirdPartyContactLink = '| <a href="' . $thirdPartyContactLinkURL . '" target="_blank" title="ThirdPartyContacts">Third Party Contacts</a>';

        return $thirdPartyContactLink;
    }

    function getGeneratePasswordLink()
    {
        $generatePasswordLinkURL =
            Controller::buildLink(
                'Password.php',
                array(
                    'action'  => 'generate',
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );


        $passwordLink = '| <a href="#" title="Generate new password"
        onClick = "window.open(
          \'' . $generatePasswordLinkURL . '\',
          \'reason\',
          \'scrollbars=yes,resizable=yes,height=50,width=80,copyhistory=no, menubar=0\')" >Generate Password</a> ';

        return $passwordLink;
    }

    function getContractListPopupLink($customerID)
    {
        $contractListPopupLinkURL =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'     => 'contractListPopup',
                    'customerID' => $customerID,
                    'htmlFmt'    => CT_HTML_FMT_POPUP
                )
            );


        $contractListPopupLink = '| <a href="' . $contractListPopupLinkURL . '" target="_blank" title="Contracts">Contracts</a>';

        return $contractListPopupLink;
    }

    function getSalesOrderLink($linkedOrdheadID)
    {
        if ($linkedOrdheadID) {
            $linkURL =
                Controller::buildLink(
                    'SalesOrder.php',
                    array(
                        'action'    => 'displaySalesOrder',
                        'ordheadID' => $linkedOrdheadID
                    )
                );
            $linkMarkup = '| <a href="' . $linkURL . '" target="_blank" title="Sales Order">Sales Order</a>';
        } else {
            $linkMarkup = '| <a href="#" onclick="linkedSalesOrderPopup()">Sales Order</a>';
        }

        return $linkMarkup;
    }

    /**
     * Documents display and upload
     *
     */
    function documents($callActivityID,
                       $problemID,
                       $templateName
    )
    {
        $this->template->set_block(
            $templateName,
            'documentBlock',
            'documents'
        );

        $urlUploadFile =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => CTACTIVITY_ACT_UPLOAD_FILE,
                    'problemID'      => $problemID,
                    'callActivityID' => $callActivityID
                )
            );
        $txtUploadFile = '[upload]';

        $this->template->set_var(
            array(
                'uploadDescription' => $_REQUEST['uploadDescription'],
                'userfile'          => $_FILES['userfile']['name'],
                'txtUploadFile'     => $txtUploadFile,
                'urlUploadFile'     => $urlUploadFile
            )
        );

        $dbeJCallDocument = new DBEJCallDocument($this);
        $dbeJCallDocument->setValue(
            'problemID',
            $problemID
        );
        $dbeJCallDocument->getRowsByColumn('problemID');

        while ($dbeJCallDocument->fetchNext()) {

            $urlViewFile =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'         => CTACTIVITY_ACT_VIEW_FILE,
                        'callDocumentID' => $dbeJCallDocument->getValue('callDocumentID')
                    )
                );

            $urlDeleteFile =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'         => CTACTIVITY_ACT_DELETE_FILE,
                        'callActivityID' => $callActivityID,
                        'callDocumentID' => $dbeJCallDocument->getValue('callDocumentID')
                    )
                );

            $this->template->set_var(
                array(
                    'description'    => $dbeJCallDocument->getValue("description"),
                    'filename'       => $dbeJCallDocument->getValue("filename"),
                    'createUserName' => $dbeJCallDocument->getValue("createUserName"),
                    'createDate'     => $dbeJCallDocument->getValue("createDate"),
                    'urlViewFile'    => $urlViewFile,
                    'urlDeleteFile'  => $urlDeleteFile,
                    'txtDeleteFile'  => '[delete]'
                )
            );
            $this->template->parse(
                'documents',
                'documentBlock',
                true
            );
        }


    }// end function editActivity()

    function displayFirstActivity()
    {
        $dbeCallActivity = $this->buActivity->getFirstActivityInProblem($_REQUEST['problemID']);

        $this->redirectToDisplay($dbeCallActivity->getValue('callActivityID'));

    }// end function editLinkedSalesOrder()

    /**
     * Redirect to call page
     * @access private
     */
    function redirectToDisplay($callActivityID)
    {
        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'callActivityID' => $callActivityID,
                    'action'         => CTACTIVITY_ACT_DISPLAY_ACTIVITY
                )
            );
        header('Location: ' . $urlNext);
        exit;
    }

    function redirectToFixed($callActivityID)
    {
        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'callActivityID' => $callActivityID,
                    'action'         => 'gatherFixedInformation'
                )
            );
        header('Location: ' . $urlNext);
        exit;
    }

    function displayLastActivity()
    {
        $dbeCallActivity = $this->buActivity->getLastActivityInProblem($_REQUEST['problemID']);

        $this->redirectToDisplay($dbeCallActivity->getValue('callActivityID'));

    }

    /**
     * Create wizard step 1: Customer, site and contact selection
     * @access private
     */
    function activityCreate1($referred = false,
                             $reason = false
    )
    {
        $this->setMethodName('activityCreate1');

        if ($_REQUEST['reason']) {
            $reason = $_REQUEST['reason'];
        }
        // Parameters

        /* do the search if POST request or we already have a customer.
      i.e. called from createRequestFromCustomerRequest()
      */
        if (
            $_SERVER['REQUEST_METHOD'] == 'POST' OR
            $_REQUEST['customerID']
        ) {

            if (!$db = mysqli_connect(
                DB_HOST,
                DB_USER,
                DB_PASSWORD
            )) {
                echo 'Could not connect to mysql host ' . DB_HOST;
                exit;
            }

            if (
                !$_REQUEST['customerID'] &&
                !$_REQUEST['contactFirstName'] &&
                !$_REQUEST['contactLastName'] &&
                !$_REQUEST['customerString']
            ) {

                $error = 'Please enter either a customer or contact name';

            }

            if (!$error) {

                mysqli_select_db(
                    $db,
                    DB_NAME
                );

                // mailflag8 is the support contact flag

                $query =
                    "SELECT 
            cus_custno,
            cus_name,
            con_contno,
            add_town AS site_name,
            contact.con_first_name,
            contact.con_last_name,
            contact.con_phone,
            contact.con_notes,
            address.add_phone,
            supportLevel,
            con_position,
            specialAttentionContactFlag,
            (
              SELECT
                COUNT(*)
              FROM
                problem
              WHERE
                pro_custno = cus_custno
                AND pro_status IN( 'I', 'P')
            ) AS openSrCount,
       (SELECT cui_itemno IS NOT NULL FROM custitem WHERE custitem.`cui_itemno` = 4111 AND custitem.`declinedFlag` <> 'Y' AND custitem.`cui_custno` = customer.`cus_custno` limit 1) AS hasPrepay,
       (SELECT item.`itm_desc` FROM custitem LEFT JOIN item ON cui_itemno = item.`itm_itemno` WHERE itm_desc LIKE '%servicedesk%' AND custitem.`declinedFlag` <> 'Y' AND custitem.`cui_custno` = customer.`cus_custno` limit 1 ) AS hasServiceDesk
                
          FROM customer
          
            JOIN contact ON con_custno = cus_custno
            JOIN address ON add_custno = cus_custno AND add_siteno = con_siteno
          WHERE supportLevel is not null and supportLevel <> '' ";

                if ($_REQUEST['customerString']) {
                    $query .= " AND ( cus_name LIKE '%" . $_REQUEST['customerString'] . "%' OR customer.cus_custno = '" . $_REQUEST['customerString'] . "')";
                }
                if ($_REQUEST['contactFirstName']) {
                    $query .= "
            AND con_first_name LIKE '%" . $_REQUEST['contactFirstName'] . "%'";
                }
                if ($_REQUEST['contactLastName']) {
                    $query .= "
            AND con_last_name LIKE '%" . $_REQUEST['contactLastName'] . "%'";
                }
                if ($_REQUEST['contactLastName']) {
                    $query .= "
            AND con_last_name LIKE '%" . $_REQUEST['contactLastName'] . "%'";
                }

                if ($_REQUEST['customerID']) {
                    $query .= " AND customer.cus_custno = " . $_REQUEST['customerID'];
                }

                if ($_REQUEST['contactID']) {
                    $query .= " AND con_contno = " . $_REQUEST['contactID'];
                }

                $query .= " ORDER BY cus_name, con_last_name, con_first_name";
                $result = mysqli_query(
                    $db,
                    $query
                );
            }
        }// end IF POST

        $this->setTemplateFiles(
            'ActivityCreate1',
            'ActivityCreate1.inc'
        );
        // Parameters
        $this->setPageTitle("Log " . CONFIG_SERVICE_REQUEST_DESC);


        $submitURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array('action' => 'activityCreate1')
            );

        if ($reason) {
            $reasonMarkup = '<div style="width: 500px; border: dotted; padding: 5px; ">' . $reason . '</div>';
        } else {
            $reasonMarkup = '';
        }


        $this->template->set_var(
            array(
                'contactFirstName' => $_REQUEST['contactFirstName'],
                'contactLastName'  => $_REQUEST['contactLastName'],
                'customerString'   => $_REQUEST['customerString'],
                'error'            => $error,
                'referred'         => $referred,
                'submitURL'        => $submitURL,
                'reasonMarkup'     => $reasonMarkup,
                'reason'           => htmlentities($reason)
            )
        );

        if ($result) {                    // got some results

            $this->template->set_block(
                'ActivityCreate1',
                'resultsBlock',
                'results'
            );


            while ($row = mysqli_fetch_assoc($result)) {
                $supportClass = "";
                // main suport contact?
                if ($row['supportLevel'] == 'main') {
                    $supportClass = "mainSupportContact";
                }
                $action = 'displayOpenSrs';
                if ($row['openSrCount'] == 0) {
                    $action = 'editServiceRequestHeader';
                }
                $cus_name = $row['cus_name'];
                $contact_name = $row['con_first_name'] . ' ' . $row['con_last_name'];
                $site_name = $row['site_name'];
                $contact_phone = $row['con_phone'];
                $contact_position = $row['con_position'];
                $site_phone = $row['add_phone'];

                if ($row['specialAttentionContactFlag'] == 'Y') {
                    $rowColor = "style='background-color:#ffe6e6 '";
                }

                $this->template->set_var(
                    array(
                        'cus_name'             => $cus_name,
                        'contact_name'         => $contact_name,
                        'contact_position'     => $contact_position,
                        'con_phone'            => $contact_phone,
                        'add_phone'            => $site_phone,
                        'site_name'            => $site_name,
                        'supportClass'         => $supportClass,
                        'formAction'           => $action,
                        'customerID'           => $row['cus_custno'],
                        'contactID'            => $row['con_contno'],
                        'contact_notes'        => $row['con_notes'],
                        'contact_supportLevel' => $row['supportLevel'],
                        'contract'             => $row['hasPrepay'] ? 'PrePay' : ($row['hasServiceDesk'] ? $row['hasServiceDesk'] : 'T&M Authorisation Required')
                    )
                );
                $this->template->parse(
                    'results',
                    'resultsBlock',
                    true
                );
            }
        }
        $this->template->parse(
            'CONTENTS',
            'ActivityCreate1',
            true
        );
        $this->parsePage();

    } // end cancelEdit

    /**
     * edit a value only SR
     * @access private
     */
    function editValueOnlyServiceRequest()
    {
        $this->setMethodName('editValueOnlyServiceRequest');

        $this->validateSession();

        $this->setTemplateFiles(
            'ServiceRequestValueEdit',
            'ServiceRequestValueEdit.inc'
        );
        // Parameters
        $this->setPageTitle("Create Activity: Value");

        /* validate if this is a POST request */
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if ($_REQUEST['curValue'] && !is_numeric($_REQUEST['curValue'])) {
                $error['curValue'] = 'Enter a currency value';
            }

            if (!$_REQUEST['contractCustomerItemID']) {
                $error['contractCustomerItemID'] = 'Required';
            } else {
                $_SESSION[$this->sessionKey]['contractCustomerItemID'] = $_REQUEST['contractCustomerItemID'];

            }


            if (count($error) == 0) {
                $_SESSION[$this->sessionKey]['callActivityID'] = 0;
                $_SESSION[$this->sessionKey]['date'] = date('d/m/Y');
                $_SESSION[$this->sessionKey]['curValue'] = $_REQUEST['curValue'];
                $_SESSION[$this->sessionKey]['startTime'] = date('H:i');
                $_SESSION[$this->sessionKey]['status'] = 'C';
                $_SESSION[$this->sessionKey]['contractCustomerItemID'] =
                    $_REQUEST['contractCustomerItemID'];
                $_SESSION[$this->sessionKey]['userID'] = $GLOBALS['auth']->is_authenticated();

                $dsCallActivity = $this->buActivity->createActivityFromSession($this->sessionKey);
                $callActivityID = $dsCallActivity->getValue('callActivityID');

                unset ($_SESSION[$this->sessionKey]); // clear the session variable

                $nextURL =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'         => 'displayActivity',
                            'callActivityID' => $callActivityID
                        )
                    );
                header('Location: ' . $nextURL);

            }
        }// end IF POST

        $this->setTemplateFiles(
            'ServiceRequestValueEdit',
            'ServiceRequestValueEdit.inc'
        );

        $submitURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array('action' => 'editValueOnlyServiceRequest')
            );

        $backURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array('action' => 'activityCreate1')
            );


        $this->template->set_var(
            array(
                'customerName'           => $_SESSION[$this->sessionKey]['customerName'],
                'currentDocumentsLink'   => $this->getCurrentDocumentsLink(
                    $_SESSION[$this->sessionKey]['customerID'],
                    $buCustomer
                ),
                'renewalsLink'           => $this->getRenewalsLink($_SESSION[$this->sessionKey]['customerID']),
                'curValue'               => $_SESSION[$this->sessionKey]['curValue'],
                'curValueMessage'        => $error['curValue'],
                'contractCustomerItemID' => $_SESSION[$this->sessionKey]['contractCustomerItemID'],
                'contractCustomerItemIDMessage'
                                         => $error['contractCustomerItemID'],
                'submitURL'              => $submitURL,
                'backURL'                => $backURL
            )
        );

        $this->contractDropdown(
            $_SESSION[$this->sessionKey]['customerID'],
            $_SESSION[$this->sessionKey]['contractCustomerItemID'],
            'ServiceRequestValueEdit',
            'contractBlock'
        );

        $this->template->parse(
            'activityWizardHeader',
            'ActivityWizardHeader',
            true
        );

        $this->template->parse(
            'CONTENTS',
            'ServiceRequestValueEdit',
            true
        );

        $this->parsePage();

    }

    function validateSession()
    {

        if (!isset($_SESSION[$this->sessionKey])) {

            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=activityCreate1');
            exit;

        }

    }// end function displayActivity()

    function getCurrentDocumentsLink($customerID,
                                     &$buCustomer
    )
    {

        if (!$buCustomer) {
            $buCustomer = new BUCustomer($this);
        }

//        if ($buCustomer->customerFolderExists($customerID)) {
//
//            $currentDocumentsPath = $buCustomer->checkCurrentDocumentsFolderExists($customerID);
//
//            $currentDocumentsLink = '<a href="file:' . $currentDocumentsPath . '" target="_blank" title="Current Documentation Folder">Current Documentation Folder</a>';
//        } else {
//            $currentDocumentsLink = '';
//        }

        return null;
//        return $currentDocumentsLink;

    }

    function displayOpenSrs()
    {
        $this->setMethodName('displayOpenSrs');

        $buCustomer = new BUCustomer($this);
        $buCustomer->getCustomerByID(
            $_REQUEST['customerID'],
            $dsCustomer
        );

        $this->setPageTitle("Existing Service Requests for " . $dsCustomer->getValue(DBECustomer::name));

        $_SESSION[$this->sessionKey]['reason'] = $_REQUEST['reason'];
        $_SESSION[$this->sessionKey]['customerName'] = $dsCustomer->getValue(DBECustomer::name);

        $_SESSION[$this->sessionKey]['hideFromCustomerFlag'] = $_REQUEST['hideFromCustomerFlag'];

        $_SESSION[$this->sessionKey]['internalNotes'] = $_REQUEST['internalNotes'];


        $dsContactSrs = $this->buActivity->getProblemsByContact($_REQUEST['contactID']);

        $dsActiveSrs = $this->buActivity->getActiveProblemsByCustomer($_REQUEST['customerID']);

        $this->setTemplateFiles(
            'ActivityExistingRequests',
            'ActivityExistingRequests.inc'
        );

        $dbeContact = new DBEContact($this);
        $dbeContact->getRow($_REQUEST['contactID']);


        $this->template->set_var(
            [
                'techNotes'   => $dsCustomer->getValue(DBECustomer::techNotes),
                'contactName' => $dbeContact->getValue(DBEContact::firstName) . " " . $dbeContact->getValue(
                        DBEContact::lastName
                    )
            ]
        );

        $this->template->set_block(
            'ActivityExistingRequests',
            'contactProblemBlock',
            'contactProblems'
        );

        while ($dsContactSrs->fetchNext()) {

            $urlCreateFollowOn =
                Controller::buildLink(
                    'Activity.php',
                    array(
                        'action'         => 'createFollowOnActivity',
                        'callActivityID' => $dsContactSrs->getValue('lastCallActivityID'),
                        'reason'         => $_REQUEST['reason']
                    )
                );

            $urlProblemHistoryPopup =
                Controller::buildLink(
                    'Activity.php',
                    array(
                        'action'    => 'problemHistoryPopup',
                        'problemID' => $dsContactSrs->getValue('problemID'),
                        'htmlFmt'   => CT_HTML_FMT_POPUP
                    )
                );

            $this->template->set_var(
                array(
                    'contactProblemID'              => $dsContactSrs->getValue("problemID"),
                    'contactDateRaised'             => Controller::dateYMDtoDMY($dsContactSrs->getValue('dateRaised')),
                    'contactReason'                 => self::truncate(
                        $dsContactSrs->getValue("reason"),
                        100
                    ),
                    'contactLastReason'             => self::truncate(
                        $dsContactSrs->getValue("lastReason"),
                        100
                    ),
                    'contactEngineerName'           => $dsContactSrs->getValue("engineerName"),
                    'createFollowOnLink'            => $dsContactSrs->getValue(
                        DBEJProblem::status
                    ) == 'C' ? '' : "<a href=" . $urlCreateFollowOn . ">Log activity</a>",
                    'contactUrlProblemHistoryPopup' => $urlProblemHistoryPopup,
                    'contactPriority'               => $dsContactSrs->getValue(DBEJProblem::priority),
                    'contactPriorityClass'          => $dsContactSrs->getValue(
                        DBEJProblem::priority
                    ) == 1 ? 'class="redRow"' : null
                )
            );

            $this->template->parse(
                'contactProblems',
                'contactProblemBlock',
                true
            );

        }

        $this->template->set_block(
            'ActivityExistingRequests',
            'problemBlock',
            'problems'
        );

        while ($dsActiveSrs->fetchNext()) {

            $urlCreateFollowOn =
                Controller::buildLink(
                    'Activity.php',
                    array(
                        'action'         => 'createFollowOnActivity',
                        'callActivityID' => $dsActiveSrs->getValue('lastCallActivityID'),
                        'reason'         => $_REQUEST['reason']
                    )
                );

            $urlProblemHistoryPopup =
                Controller::buildLink(
                    'Activity.php',
                    array(
                        'action'    => 'problemHistoryPopup',
                        'problemID' => $dsActiveSrs->getValue('problemID'),
                        'htmlFmt'   => CT_HTML_FMT_POPUP
                    )
                );

            $this->template->set_var(
                array(
                    'problemID'              => $dsActiveSrs->getValue("problemID"),
                    'dateRaised'             => Controller::dateYMDtoDMY($dsActiveSrs->getValue('dateRaised')),
                    'reason'                 => self::truncate(
                        $dsActiveSrs->getValue("reason"),
                        100
                    ),
                    'lastReason'             => self::truncate(
                        $dsActiveSrs->getValue("lastReason"),
                        100
                    ),
                    'engineerName'           => $dsActiveSrs->getValue("engineerName"),
                    'urlCreateFollowOn'      => $urlCreateFollowOn,
                    'urlProblemHistoryPopup' => $urlProblemHistoryPopup,
                    'priority'               => $dsActiveSrs->getValue(DBEJProblem::priority),
                    'priorityClass'          => $dsActiveSrs->getValue(
                        DBEJProblem::priority
                    ) == 1 ? 'class="redRow"' : null
                )
            );

            $this->template->parse(
                'problems',
                'problemBlock',
                true
            );

        }
        $urlCreateNewSr =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => 'editServiceRequestHeader',
                    'customerID' => $_REQUEST['customerID'],
                    'contactID'  => $_REQUEST['contactID'],
                    'reason'     => $_REQUEST['reason']
                )
            );

        $this->template->set_var(array('urlCreateNewSr' => $urlCreateNewSr));

        $this->template->parse(
            'CONTENTS',
            'ActivityExistingRequests',
            true
        );

        $this->parsePage();

    }

    /**
     * Create Service Request
     * @access private
     */
    function editServiceRequestHeader()
    {
        $this->setMethodName('editServiceRequestHeader');

        if ($_REQUEST['reason']) {
            $_SESSION[$this->sessionKey]['reason'] = $_REQUEST['reason'];
        }


        /* validate if this is a POST request */
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $_SESSION[$this->sessionKey]['reason'] = $_REQUEST['reason'];
            $_SESSION[$this->sessionKey]['authorisedBy'] = $_REQUEST['authorisedBy'];

            if ($_REQUEST['hideFromCustomerFlag']) {
                $_SESSION[$this->sessionKey]['hideFromCustomerFlag'] = $_REQUEST['hideFromCustomerFlag'];
            } else {
                $_SESSION[$this->sessionKey]['hideFromCustomerFlag'] = 'N';

            }

            $_SESSION[$this->sessionKey]['internalNotes'] = $_REQUEST['internalNotes'];

            $_SESSION[$this->sessionKey]['callActTypeID'] = CONFIG_INITIAL_ACTIVITY_TYPE_ID;

            $_SESSION[$this->sessionKey]['customerID'] = $_REQUEST['customerID'];

            /*
        Check nothing in fields that don't allow content
        */
            if (trim($_REQUEST['reason']) == '') {
                $error['reason'] = 'Please enter the datails';

            }

            if ($_REQUEST['siteNo'] == 99) {
                $error['siteNo'] = 'Required';
            } else {
                $_SESSION[$this->sessionKey]['siteNo'] = $_REQUEST['siteNo'];
            }

            $_SESSION[$this->sessionKey]['completeDate'] = '';

            $isAddToQueue = false;
            if (isset($_REQUEST["hdQ"])) {
                $_SESSION[$this->sessionKey]['queueNo'] = 1;
                $isAddToQueue = true;
            }
            if (isset($_REQUEST["escQ"])) {
                $_SESSION[$this->sessionKey]['queueNo'] = 2;
                $isAddToQueue = true;
            }
            if (isset($_REQUEST["imtQ"])) {
                $_SESSION[$this->sessionKey]['queueNo'] = 3;
                $isAddToQueue = true;
            }

            if (isset($_REQUEST['salesQ'])) {
                $_SESSION[$this->sessionKey]['queueNo'] = 4;
                $isAddToQueue = true;
            }

            $_SESSION[$this->sessionKey]['date'] = date(CONFIG_MYSQL_DATE);
            $_SESSION[$this->sessionKey]['startTime'] = date('H:i');

            $_SESSION[$this->sessionKey]['dateRaised'] = date(CONFIG_MYSQL_DATE);
            $_SESSION[$this->sessionKey]['timeRaised'] = date('H:i');

            if (!$_SESSION[$this->sessionKey]['priority'] = $_REQUEST['priority']) {

                $error['priority'] = 'Required';
            }
            if ($_FILES['userfile']['name'] != '' & !$_REQUEST['uploadDescription']) {
                $error['file'] = 'Description Required';
            }


            if (count($error) == 0) {
                /* Create initial activity */
                $dsCallActivity = $this->buActivity->createActivityFromSession($this->sessionKey);

                /*
          Upload file
          */
                if (isset($_FILES['userfile']) && $_FILES['userfile']['name'] != '') {
                    $this->buActivity->uploadDocumentFile(
                        $dsCallActivity->getValue('problemID'),
                        $_REQUEST['uploadDescription'],
                        $_FILES['userfile']
                    );
                }

                /*
          Add to queue so return to dashboard
          */
                if ($isAddToQueue) {
                    $nextURL =
                        Controller::buildLink(
                            'CurrentActivityReport.php',
                            array()
                        );

                    header('Location: ' . $nextURL);
                    exit;
                }
                /*
          Start work create follow-on
          */
                if (isset($_REQUEST['StartWork'])) {

                    $nextURL =
                        Controller::buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'           => 'createFollowOnActivity',
                                'callActivityID'   => $dsCallActivity->getValue('callActivityID'),
                                'moveToUsersQueue' => 1
                            )
                        );

                    header('Location: ' . $nextURL);
                    exit;
                }
                exit;
            }

        }// end IF POST
        $this->validateSession();

        if ($_REQUEST['customerID']) {
            $_SESSION[$this->sessionKey]['customerID'] = $_REQUEST['customerID'];
        }

        if (!isset($_SESSION[$this->sessionKey]['siteNo'])) {
            $_SESSION[$this->sessionKey]['siteNo'] = 99; // default none selected
        }

        $this->setTemplateFiles(
            array(
                'ActivityCreate6'      => 'ActivityCreate6.inc',
                'ActivityWizardHeader' => 'ActivityWizardHeader.inc'
            )
        );

// Parameters
        $this->setPageTitle("Record " . CONFIG_SERVICE_REQUEST_DESC . " Details");

        $_SESSION[$this->sessionKey]['callActTypeID'] = CONFIG_INITIAL_ACTIVITY_TYPE_ID;

        /* initialise */
        if ($_SESSION[$this->sessionKey]['dateRaised'] == '') {
            $_SESSION[$this->sessionKey]['dateRaised'] = date('d/m/Y');
        }
        if ($_SESSION[$this->sessionKey]['timeRaised'] == '') {
            $_SESSION[$this->sessionKey]['timeRaised'] = date('H:i');
        }

        if ($_SESSION[$this->sessionKey]['hideFromCustomerFlag'] == '') {
            $_SESSION[$this->sessionKey]['hideFromCustomerFlag'] = 'N';
        }

        $this->priorityDropdown($_SESSION[$this->sessionKey]['priority']);

        $this->siteDropdown(
            $_SESSION[$this->sessionKey]['customerID'],
            $_SESSION[$this->sessionKey]['siteNo'],
            'ActivityCreate6',
            'siteBlock'
        );

        $this->onlyMainAndSupervisorsDropdown(
            'ActivityCreate6',
            $_SESSION[$this->sessionKey]['customerID'],
            $_SESSION[$this->sessionKey]['contactID']
        );

        $this->contactDropdown(
            'ActivityCreate6',
            $_SESSION[$this->sessionKey]['customerID'],
            $_SESSION[$this->sessionKey]['contactID']
        );


        $submitURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'editServiceRequestHeader'
                )
            );

        if ($this->hasPermissions(PHPLIB_PERM_SUPERVISOR)) {
            $disabled = ''; // not
        } else {
            $disabled = CTCNC_HTML_DISABLED;
            $calendarLinkDate = '';
        }

        $backURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array('action' => 'activityCreate1')
            );

        $this->template->set_var(
            array(
                'callActivityID'              => $_SESSION[$this->sessionKey]['callActivityID'],
                'customerID'                  => $_SESSION[$this->sessionKey]['customerID'],
                'siteNoMessage'               => $error['siteNo'],
                'reason'                      => $_SESSION[$this->sessionKey]['reason'],
                'reasonMessage'               => $error['reason'],
                'internalNotes'               => $_SESSION[$this->sessionKey]['internalNotes'],
                'customerName'                => $_SESSION[$this->sessionKey]['customerName'],
                'customerNameDisplayClass'
                                              => $_SESSION[$this->sessionKey]['customerNameDisplayClass'],
                'currentDocumentsLink'        => $this->getCurrentDocumentsLink(
                    $_SESSION[$this->sessionKey]['customerID'],
                    $buCustomer
                ),
                'renewalsLink'                => $this->getRenewalsLink($_SESSION[$this->sessionKey]['customerID']),
                'projectLink'                 => BUProject::getCurrentProjectLink(
                    $_SESSION[$this->sessionKey]['customerID']
                ),
                'contractListPopupLink'       => $this->getContractListPopupLink(
                    $_SESSION[$this->sessionKey]['customerID']
                ),
                'dateRaised'                  => Controller::dateYMDtoDMY($_SESSION[$this->sessionKey]['dateRaised']),
                'timeRaised'                  => $_SESSION[$this->sessionKey]['timeRaised'],
                'dateMessage'                 => $error['date'],
                'startTimeMessage'            => $error['startTime'],
                'priorityMessage'             => $error['priority'],
                'fileMessage'                 => $error['file'],
                'contactNotes'                => $_SESSION[$this->sessionKey]['contactNotes'],
                'techNotes'                   => $_SESSION[$this->sessionKey]['techNotes'],
                'urlCustomer'                 => $this->getCustomerUrl($_SESSION[$this->sessionKey]['customerID']),
                'calendarLinkDate'            => $calendarLinkDate,
                'hideFromCustomerFlagChecked' => Controller::htmlChecked(
                    $_SESSION[$this->sessionKey]['hideFromCustomerFlag']
                ),
                'passwordLink'                => $this->getPasswordLink($_SESSION[$this->sessionKey]['customerID']),
                'thirdPartyContactLink'       => $this->getThirdPartyContactLink(
                    $_SESSION[$this->sessionKey]['customerID']
                ),
                'generatePasswordLink'        => $this->getGeneratePasswordLink(),
                'DISABLED'                    => $disabled,
                'submitURL'                   => $submitURL,
                'backURL'                     => $backURL
            )
        );

        $this->template->parse(
            'activityWizardHeader',
            'ActivityWizardHeader',
            true
        );

        $this->template->parse(
            'CONTENTS',
            'ActivityCreate6',
            true
        );

        $this->parsePage();

    }

    function siteDropdown(
        $customerID,
        $siteNo,
        $templateName = 'ActivityCreate6',
        $blockName = 'siteBlock'
    )
    {
        // Site selection
        $dbeSite = new DBESite($this);
        $dbeSite->setValue(
            DBESite::customerID,
            $customerID
        );
        $dbeSite->getRowsByCustomerID();

        $siteCount = 0;
        while ($dbeSite->fetchNext()) {
            $siteCount++;
        }

        $dbeSite->setValue(
            DBESite::customerID,
            $customerID
        );
        $dbeSite->getRowsByCustomerID();

        $this->template->set_block(
            $templateName,
            $blockName,
            'sites'
        );

        while ($dbeSite->fetchNext()) {

            if ($siteCount == 1) {
                $siteSelected = CT_SELECTED;
            } else {
                $siteSelected = ($siteNo == $dbeSite->getValue(DBESite::siteNo)) ? CT_SELECTED : '';
            }

            $siteDesc = $dbeSite->getValue(DBESite::add1) . ' ' . $dbeSite->getValue(
                    DBESite::town
                ) . ' ' . $dbeSite->getValue(DBESite::postcode);

            $this->template->set_var(
                array(
                    'siteSelected' => $siteSelected,
                    'siteNo'       => $dbeSite->getValue(DBESite::siteNo),
                    'siteDesc'     => $siteDesc
                )
            );
            $this->template->parse(
                'sites',
                $blockName,
                true
            );
        }

    }

    function contactDropdown($templateName = 'ActivityEdit',
                             $customerID,
                             $contactID
    )
    {
        $dbeContact = new DBEContact($this);
        $dbeSite = new DBESite($this);

        $dbeContact->getRowsByCustomerID(
            $customerID,
            false,
            true
        );

        $this->template->set_block(
            $templateName,
            'contactBlock',
            'contacts'
        );

        $lastSiteNo = '';

        while ($dbeContact->fetchNext()) {

            $dataDelegate = "";
            $contactSelected = ($contactID == $dbeContact->getValue("contactID")) ? CT_SELECTED : '';

            if ($dbeContact->getValue(DBEContact::supportLevel) == DBEContact::supportLevelMain) {
                $startMainContactStyle = '*';
                $endMainContactStyle = '*';
            } elseif ($dbeContact->getValue(DBEContact::supportLevel) == DBEContact::supportLevelDelegate) {
                $startMainContactStyle = '- Delegate';
                $endMainContactStyle = '- Delegate';
                $dataDelegate = "data-delegate='true'";
            } elseif ($dbeContact->getValue(DBEContact::supportLevel) == DBEContact::supportLevelSupervisor) {
                $startMainContactStyle = '- Supervisor';
                $endMainContactStyle = '- Supervisor';
            } else {
                $startMainContactStyle = '';
                $endMainContactStyle = '';
            }

            $dbeSite->setValue(
                DBESite::customerID,
                $dbeContact->getValue("customerID")
            );
            $dbeSite->setValue(
                DBESite::siteNo,
                $dbeContact->getValue("siteNo")
            );
            $dbeSite->getRow();

            $name = $dbeContact->getValue("firstName") . ' ' . $dbeContact->getValue("lastName");

            if ($dbeContact->getValue("position")) {
                $name .= ' (' . $dbeContact->getValue("position") . ')';
            }

            /*
        Option group site
        */

            if ($dbeContact->getValue('siteNo') != $lastSiteNo) {

                if ($dbeContact->getValue('siteNo') != '') {

                    if ($lastSiteNo !== '') {
                        $optGroupClose = '</optgroup>';
                    } else {
                        $optGroupClose = '';

                    }
                }

                $optGroupOpen = '<optgroup label="' . $dbeSite->getValue(DBESite::add1) . ' ' . $dbeSite->getValue(
                        DBESite::town
                    ) . ' ' . $dbeSite->getValue(DBESite::postcode) . '">';
                $optGroupClose = '';
            } else {
                $optGroupOpen = '';
                $optGroupClose = '';
            }

            $lastSiteNo = $dbeContact->getValue('siteNo');

            $this->template->set_var(
                array(
                    'contactSelected'       => $contactSelected,
                    'contactID'             => $dbeContact->getValue("contactID"),
                    'contactName'           => $name,
                    'startMainContactStyle' => $startMainContactStyle,
                    'endMainContactStyle'   => $endMainContactStyle,
                    'optGroupOpen'          => $optGroupOpen,
                    'optGroupClose'         => $optGroupClose,
                    'dataDelegate'          => $dataDelegate
                )
            );
            $this->template->parse(
                'contacts',
                'contactBlock',
                true
            );
        }

    }  // end finaliseProblem

    function createTravel()
    {
        $this->buActivity->getActivityByID(
            $_REQUEST['callActivityID'],
            $dsActivity
        );

        $this->buActivity->createTravelActivity($_REQUEST['callActivityID']);

        if ($_REQUEST['nextStatus'] == 'Fixed') {

            /* Gather fixed info */
            $urlNext =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'         => 'gatherFixedInformation',
                        'callActivityID' => $_REQUEST['callActivityID']
                    )
                );

            header('Location: ' . $urlNext);
            exit;

        }

        if ($_REQUEST['nextStatus'] == 'Escalate') {

            $this->buActivity->escalateProblemByCallActivityID($_REQUEST['callActivityID']);
        }


        $this->redirectToDisplay($_REQUEST['callActivityID']);
        exit;
    }

    function editLinkedSalesOrder()
    {
        $this->setMethodName('editLinkedSalesOrder');

        $this->setPageTitle('Linked Sales Order');

        $errorMessage = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if ($_POST['linkedOrderID']) {

                $linkedOrderID = $_POST['linkedOrderID'];
                $callActivityID = $_POST['callActivityID'];

                $dbeSalesOrder = new DBEOrdhead($this);
                if ($dbeSalesOrder->getRow($linkedOrderID)) {

                    $this->buActivity->getActivityByID(
                        $callActivityID,
                        $dsActivity
                    );

                    if ($dsActivity->getValue('customerID') != $dbeSalesOrder->getValue('customerID')) {
                        $errorMessage = "Sales Order Not For This Customer";
                    } else {
                        $this->buActivity->updateLinkedSalesOrder(
                            $callActivityID,
                            $linkedOrderID
                        );
                        echo '<script type="text/javascript"> window.opener.location.reload(false); window.close(); </script>';
                    }
                } else {
                    $errorMessage = "Sales Order Does Not Exist";
                }

            } else {
                $errorMessage = "Sales Order ID Required";
            }

        } else {
            $callActivityID = $_REQUEST['callActivityID'];
            $linkedOrderID = '';
        }

        $this->setTemplateFiles(
            array(
                'ActivityEditLinkedSalesOrder' => 'ActivityEditLinkedSalesOrder.inc'
            )
        );

        $this->setHTMLFmt(CT_HTML_FMT_POPUP);

        $this->template->set_var(
            array(
                'callActivityID' => $callActivityID,
                'errorMessage'   => $errorMessage,
                'urlCancelEdit'  => $urlCancelEdit,
                'linkedOrderID'  => $linkedOrderID
            )
        );


        $this->template->parse(
            'CONTENTS',
            'ActivityEditLinkedSalesOrder',
            true
        );
        $this->parsePage();
    }

    function problemHistoryPopup()
    {
        $this->setTemplateFiles(
            'ActivityReasonPopup',
            'ActivityReasonPopup.inc'
        );

        $dsResults = $this->buActivity->getActivitiesByProblemID($_REQUEST['problemID']);

        $dbeProblem = new DBEJProblem($this);
        $dbeProblem->getRow($_REQUEST['problemID']);

        $dbeJContract = new DBEJContract($this);

        $this->setPageTitle($_REQUEST['problemID'] . ' - ' . $dbeProblem->getValue('customerName'));

        $this->template->set_block(
            'ActivityReasonPopup',
            'activityBlock',
            'rows'
        );

        while ($dsResults->fetchNext()) {

            if ($dsResults->getValue('hideFromCustomerFlag') == 'Y') {
                $activityHiddenText = 'Hidden From Customer';
            } else {
                $activityHiddenText = '';
            }
            $this->template->set_var(
                array(
                    'reason'             => $dsResults->getValue('reason'),
                    'date'               => Controller::dateYMDtoDMY($dsResults->getValue('date')),
                    'startTime'          => $dsResults->getValue('startTime'),
                    'endTime'            => $dsResults->getValue('endTime'),
                    'activityType'       => $dsResults->getValue('activityType'),
                    'contactName'        => $dsResults->getValue('contactName'),
                    'duration'           => number_format(
                        $dsResults->getValue('durationMinutes') / 60,
                        2
                    ),
                    'userName'           => $dsResults->getValue('userName'),
                    'activityHiddenText' => $activityHiddenText
                )
            );

            $this->template->parse(
                'rows',
                'activityBlock',
                true
            );

        }

        if ($dsResults->getValue('contractCustomerItemID')) {
            $dbeJContract->getRowByContractID($dsResults->getValue('contractCustomerItemID'));

            $contractDescription =
                $dbeJContract->getValue('itemDescription') . ' ' . $dbeJContract->getValue(
                    'adslPhone'
                ) . $dbeJContract->getValue('postcode');
        } else {
            $contractDescription = 'No contract selected';
        }


        if ($dbeProblem->getValue('hideFromCustomerFlag') == 'Y') {
            $problemHiddenText = 'Entire SR Hidden From Customer';
        } else {
            $problemHiddenText = '';
        }

        $this->template->set_var(
            array(
                'internalNotes'       => $dbeProblem->getValue('internalNotes'),
                'contractDescription' => $contractDescription,
                'problemHiddenText'   => $problemHiddenText
            )
        );

        $this->template->parse(
            'CONTENTS',
            'ActivityReasonPopup',
            true
        );

        $this->parsePage();

        exit;
    }    // end allocateAdditionalTime

    function serviceRequestsForContactPopup()
    {
        $this->setTemplateFiles(
            'ServiceRequestsForContactPopup',
            'ServiceRequestsForContactPopup'
        );
        $dsContactSrs = $this->buActivity->getProblemsByContact($_REQUEST['contactID']);


        $dbeContact = new DBEContact($this);
        $dbeContact->getRow($_REQUEST['contactID']);
        $this->setPageTitle(
            'Service Requests For ' . $dbeContact->getValue(DBEContact::firstName) . " " . $dbeContact->getValue(
                DBEContact::lastName
            )
        );

        $this->template->set_block(
            'ServiceRequestsForContactPopup',
            'contactProblemBlock',
            'contactProblems'
        );

        while ($dsContactSrs->fetchNext()) {

            $urlCreateFollowOn =
                Controller::buildLink(
                    'Activity.php',
                    array(
                        'action'         => 'createFollowOnActivity',
                        'callActivityID' => $dsContactSrs->getValue('lastCallActivityID'),
                        'reason'         => $_REQUEST['reason']
                    )
                );

            $urlProblemHistoryPopup =
                Controller::buildLink(
                    'Activity.php',
                    array(
                        'action'    => 'problemHistoryPopup',
                        'problemID' => $dsContactSrs->getValue('problemID'),
                        'htmlFmt'   => CT_HTML_FMT_POPUP
                    )
                );

            $this->template->set_var(
                array(
                    'contactProblemID'              => $dsContactSrs->getValue("problemID"),
                    'contactDateRaised'             => Controller::dateYMDtoDMY($dsContactSrs->getValue('dateRaised')),
                    'contactReason'                 => self::truncate(
                        $dsContactSrs->getValue("reason"),
                        100
                    ),
                    'contactLastReason'             => self::truncate(
                        $dsContactSrs->getValue("lastReason"),
                        100
                    ),
                    'contactEngineerName'           => $dsContactSrs->getValue("engineerName"),
                    'createFollowOnLink'            => $dsContactSrs->getValue(
                        DBEJProblem::status
                    ) == 'C' ? '' : "<a href=" . $urlCreateFollowOn . ">Log activity</a>",
                    'contactUrlProblemHistoryPopup' => $urlProblemHistoryPopup,
                    'contactPriority'               => $dsContactSrs->getValue(DBEJProblem::priority),
                    'contactPriorityClass'          => $dsContactSrs->getValue(
                        DBEJProblem::priority
                    ) == 1 ? 'class="redRow"' : null
                )
            );

            $this->template->parse(
                'contactProblems',
                'contactProblemBlock',
                true
            );

        }
        $this->template->parse(
            'CONTENTS',
            'ServiceRequestsForContactPopup',
            true
        );
        $this->parsePage();

        exit;
    }    // end allocateAddition

    function customerProblemPopup()
    {
        $this->setTemplateFiles(
            'ActivityCustomerProblemPopup',
            'ActivityCustomerProblemPopup.inc'
        );

        $record = $this->buActivity->getCustomerRaisedRequest($_REQUEST['customerProblemID']);

        $this->setPageTitle('To Be Logged');

        $this->template->set_var(
            array(
                'details' => str_replace(
                    "\n",
                    "<br/>",
                    $record['cpr_reason']
                )
            )
        );

        $this->template->parse(
            'CONTENTS',
            'ActivityCustomerProblemPopup',
            true
        );

        $this->parsePage();

        exit;
    }

    /**
     * Edit/Add Activity
     * @access private
     */
    function editActivity()
    {
        $this->setMethodName('editActivity');

        $dsCallActivity = &$this->dsCallActivity; // ref to class var

        if ($_REQUEST['action'] == CTACTIVITY_ACT_CREATE_ACTIVITY || $_REQUEST['action'] == CTACTIVITY_ACT_CREATE_RESOLVED_ACTIVITY) {

            if (!$this->getFormError()) {

                if ($_REQUEST['action'] == CTACTIVITY_ACT_CREATE_ACTIVITY) {

                    $this->buActivity->initialiseCallActivity(
                        $_REQUEST['customerID'],
                        $this->userID,
                        $dsCallActivity
                    );
                }
            }
            $callActivityID = 0;

            $urlUpdateActivity =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTACTIVITY_ACT_INSERT_ACTIVITY
                    )
                );
        } else { // editing
            if (!$this->getFormError()) {

                $this->buActivity->getActivityByID(
                    $_REQUEST['callActivityID'],
                    $dsCallActivity
                );
            }

            $urlUpdateActivity =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTACTIVITY_ACT_UPDATE_ACTIVITY
                    )
                );

            $callActivityID = $dsCallActivity->getValue('callActivityID');
        }

        if (!$this->buActivity->canEdit($dsCallActivity)) {
            $this->raiseError('No permissions to edit this activity');
        }
        if ($this->hasPermissions(PHPLIB_PERM_SUPERVISOR)) {

            $disabled = ''; // not

            if ($dsCallActivity->getValue('callActTypeID') != CONFIG_INITIAL_ACTIVITY_TYPE_ID) {
                $setTimeNowLink = '<a href="javascript:;"  onclick="setServerTime(endTime);"><img src="images/clock.gif" alt="Clock" width="24" height="22" hspace="0" vspace="0" border="0" align="absmiddle" title="Set end time now" /></a>';
            }
        } else {

            $disabled = CTCNC_HTML_DISABLED;

            $calendarLinkDate = '';
        }
        /*
      Only enable the complete date and autocomplete checkbox if Fixed
      */
        if ($dsCallActivity->getValue('problemStatus') == 'F') {
            $complete_disabled = '';
        } else {
            $complete_disabled = CTCNC_HTML_DISABLED;
        }

        if ($this->canChangeSrPriority()) {
            $priority_disabled = '';
        } else {
            $priority_disabled = CTCNC_HTML_DISABLED;
        }
        /*
      Contract can only be changed by member of Accounts group
      */
        if ($this->dbeUser->getValue(DBEUser::changeSRContractsFlag) == 'Y') {
            $contract_disabled = '';
        } else {
            $contract_disabled = CTCNC_HTML_DISABLED;
        }

        /*
      Only enable the date and time if not initial activity type
      */
        $initial_disabled = '';
        $canChangeInitialDateAndTime = true;
        if (
        in_array(
            $dsCallActivity->getValue('callActTypeID'),
            array(
                CONFIG_INITIAL_ACTIVITY_TYPE_ID,
                CONFIG_CHANGE_REQUEST_ACTIVITY_TYPE_ID
            )
        )
        ) {
            if (!$this->hasPermissions(PHPLIB_PERM_MAINTENANCE)) {
                $initial_disabled = CTCNC_HTML_DISABLED;
            }


            if ($this->dbeUser->getValue(DBEUser::changeInitialDateAndTimeFlag) == 'Y') {
                $canChangeInitialDateAndTime = true;
            }

        }

        $this->setPageTitle(CONFIG_SERVICE_REQUEST_DESC . ' ' . $dsCallActivity->getValue('problemID'));

        $this->setTemplateFiles(
            array(
                'ActivityEdit'              => 'ActivityEdit.inc',
                'ActivityWizardHeader'      => 'ActivityWizardHeader.inc',
                'ActivityEditInternalNotes' => 'ActivityEditInternalNotes.inc'
            )
        );

        $urlDisplayActivity =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => CTACTIVITY_ACT_DISPLAY_ACTIVITY,
                    'callActivityID' => $callActivityID
                )
            );

        $urlCancelEdit =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => CTACTIVITY_ACT_CANCEL_EDIT,
                    'callActivityID' => $callActivityID
                )
            );

        $buCustomer = new BUCustomer($this);
        $buCustomer->getCustomerByID(
            $dsCallActivity->getValue('customerID'),
            $dsCustomer
        );
        $dsCustomer->fetchNext();

        if ($dsCallActivity->getValue('contactID') != 0 & $dsCallActivity->getValue('siteNo') != '') {


            $buCustomer->getSiteByCustomerIDSiteNo(
                $dsCallActivity->getValue('customerID'),
                $dsCallActivity->getValue('siteNo'),
                $dsSite
            );

            $buCustomer->getContactByID(
                $dsCallActivity->getValue('contactID'),
                $dsContact
            );

            $customerDetails =
                $dsCustomer->getValue(DBECustomer::name) .
                ', ' . $dsSite->getValue(DBESite::add1) .
                ', ' . $dsSite->getValue(DBESite::add2) .
                ', ' . $dsSite->getValue(DBESite::add3) .
                ', ' . $dsSite->getValue(DBESite::town) .
                ', ' . $dsSite->getValue(DBESite::postcode) .
                ', ' . $dsContact->getValue('firstName') . ' ' . $dsContact->getValue(
                    'lastName'
                ) . ', <span class="contactPhone">' . $buCustomer->getContactPhoneForHtml(
                    $dsCallActivity->getValue('contactID')
                ) . '</span>';

            if ($dsContact->getValue('email') != '') {
                $customerDetails .=
                    ' <A HREF="mailto:' . $dsContact->getValue(
                        'email'
                    ) . '?subject=Service Request ' . $dsCallActivity->getValue('problemID') . '"' .
                    ' title="Send email to contact"><img src="images/email.gif" border="0"></A>';
            }
            if ($dsContact->getValue('notes') != '') {
                $dsCallActivity->setValue(
                    'contactNotes',
                    $dsContact->getValue('notes')
                );
            }
            if ($dsCustomer->getValue(DBECustomer::techNotes) != '') {
                $dsCallActivity->setValue(
                    'techNotes',
                    $dsCustomer->getValue(DBECustomer::techNotes)
                );
            }
        }

        $buCustomerItem = new BUCustomerItem($this);

        $currentDocumentsLink = $this->getCurrentDocumentsLink(
            $dsCallActivity->getValue('customerID'),
            $buCustomer
        );
        $renewalsLink = $this->getRenewalsLink($dsCallActivity->getValue('customerID'));


        $dbeProblem = new DBEProblem($this);

        $dbeProblem->getRow($dsCallActivity->getValue(DBECallActivity::problemID));

        if ($dbeProblem->getValue(DBEProblem::hideFromCustomerFlag) == 'Y' || $dsCallActivity->getValue(
                'problemHideFromCustomerFlag'
            ) == 'Y') {
            $hideFromCustomerFlag = 'Y';
            $hideFromCustomerDisabled = CTCNC_HTML_DISABLED;
        } else {
            $hideFromCustomerFlag = $dsCallActivity->getValue('hideFromCustomerFlag');
            $hideFromCustomerDisabled = '';
        }

        $userID = $dsCallActivity->getValue('allocatedUserID');

        $level = $this->buActivity->getLevelByUserID($userID);


        if ($dsCallActivity->getValue('onSiteFlag') == 'Y') {
            $onSiteFlag = 'Y';

        } else {
            $onSiteFlag = 'N';

        }

        if (isset($_FILES['userfile']) && $_FILES['userfile']['name'] != '') {
            $this->buActivity->uploadDocumentFile(
                $dsCallActivity->getValue('problemID'),
                $_REQUEST['uploadDescription'],
                $_FILES['userfile']
            );
        }

        $currentLoggedInUserID = ( string )$GLOBALS['auth']->is_authenticated();

        $problemID = $dsCallActivity->getValue('problemID');
        $hdUsedMinutes = $this->buActivity->getHDTeamUsedTime($problemID);
        $esUsedMinutes = $this->buActivity->getESTeamUsedTime($problemID);
        $imUsedMinutes = $this->buActivity->getIMTeamUsedTime($problemID);
        $hdUsedMinutesNotInclusive = $this->buActivity->getHDTeamUsedTime(
            $problemID,
            $callActivityID
        );
        $esUsedMinutesNotInclusive = $this->buActivity->getESTeamUsedTime(
            $problemID,
            $callActivityID
        );
        $imUsedMinutesNotInclusive = $this->buActivity->getIMTeamUsedTime(
            $problemID,
            $callActivityID
        );

        $dbeProblem = new DBEProblem($this);
        $dbeProblem->setValue(
            DBEProblem::problemID,
            $problemID
        );
        $dbeProblem->getRow();


        $hdAssignedMinutes = $dbeProblem->getValue(DBEProblem::hdLimitMinutes);
        $esAssignedMinutes = $dbeProblem->getValue(DBEProblem::esLimitMinutes);
        $imAssignedMinutes = $dbeProblem->getValue(DBEProblem::imLimitMinutes);


        $urlLinkedSalesOrder =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'         => 'editLinkedSalesOrder',
                    'htmlFmt'        => CT_HTML_FMT_POPUP,
                    'callActivityID' => $callActivityID
                )
            );

        if ((int)$dbeProblem->getValue(DBEProblem::authorisedBy)) {
            $dbeContact = new DBEContact($this);

            $dbeContact->getRow($dbeProblem->getValue(DBEProblem::authorisedBy));

            $authorisedByName = $dbeContact->getValue(DBEContact::firstName) . " " . $dbeContact->getValue(
                    DBEContact::lastName
                );
        }


        $this->template->set_var(
            array(
                'level'                        => $level,
                'onSiteFlag'                   => $onSiteFlag,
                'allocatedUserID'              => $dsCallActivity->getValue('allocatedUserID'),
                'reason'                       => $dsCallActivity->getValue('reason'),
                'reasonMessage'                => $dsCallActivity->getMessage('reason'),
                'internalNotes'                => $dsCallActivity->getValue('internalNotes'),
                'callActivityID'               => $callActivityID,
                'problemStatus'                => $dsCallActivity->getValue('problemStatus'),
                'problemStatusMessage'         => $dsCallActivity->getMessage('problemStatus'),
                'problemID'                    => $dsCallActivity->getValue('problemID'),
                'customerID'                   => $dsCallActivity->getValue('customerID'),
                'hiddenCallActTypeID'          => $dsCallActivity->getValue('callActTypeID'),
                'hiddenPriority'               => $dsCallActivity->getValue('priority'),
                'hiddenContractCustomerItemID' => $dsCallActivity->getValue('contractCustomerItemID'),
                'customerDetails'              => $customerDetails,
                'contactPhone'                 => $buCustomer->getContactPhoneForHtml(
                    $dsCallActivity->getValue('contactID')
                ),
                'expenseExportFlag'            => $dsCallActivity->getValue('expenseExportFlag'),
                'customerName'                 => Controller::htmlDisplayText(
                    $dsCallActivity->getValue('customerName')
                ),
                'customerNameDisplayClass'     => $this->getCustomerNameDisplayClass($dsCustomer),
                'urlCustomer'                  => $this->getCustomerUrl($dsCallActivity->getValue('customerID')),
                'date'                         => Controller::dateYMDtoDMY($dsCallActivity->getValue('date')),
                'dateMessage'                  => $dsCallActivity->getMessage('date'),
                'curValue'                     => $dsCallActivity->getValue('curValue'),
                'startTime'                    => $dsCallActivity->getValue('startTime'),
                'startTimeMessage'             => $dsCallActivity->getMessage('startTime'),
                'endTime'                      => $dsCallActivity->getValue('endTime'),
                'endTimeMessage'               => $dsCallActivity->getMessage('endTime'),
                'internalNotesMessage'         => $dsCallActivity->getMessage('internalNotes'),
                'siteDesc'                     => Controller::htmlInputText($dsCallActivity->getValue('siteDesc')),
                'siteNoMessage'                => Controller::htmlDisplayText($dsCallActivity->getMessage('siteNo')),
                'status'                       => $dsCallActivity->getValue('status'),
                'contactNotes'                 => $dsCallActivity->getValue('contactNotes'),
                'techNotes'                    => $dsCallActivity->getValue('techNotes'),
                'userIDMessage'                => Controller::htmlDisplayText($dsCallActivity->getMessage('userID')),
                'callActTypeIDMessage'         => Controller::htmlDisplayText(
                    $dsCallActivity->getMessage("callActTypeID")
                ),
                'urlDisplayActivity'           => $urlDisplayActivity,
                'urlCancelEdit'                => $urlCancelEdit,
                'urlUpdateActivity'            => $urlUpdateActivity,
                'currentDocumentsLink'         => $currentDocumentsLink,
                'renewalsLink'                 => $renewalsLink,
                'passwordLink'                 => $this->getPasswordLink($dsCallActivity->getValue('customerID')),
                'thirdPartyContactLink'        => $this->getThirdPartyContactLink(
                    $dsCallActivity->getValue('customerID')
                ),
                'contactHistoryLink'           => $this->getServiceRequestForContactLink(
                    $dsCallActivity->getValue(DBECallActivity::contactID)
                ),
                'generatePasswordLink'         => $this->getGeneratePasswordLink(),
                'salesOrderLink'               => $this->getSalesOrderLink(
                    $dsCallActivity->getValue('linkedSalesOrderID')
                ),
                'urlLinkedSalesOrder'          => $urlLinkedSalesOrder,
                'problemHistoryLink'           => $this->getProblemHistoryLink(
                    $dsCallActivity->getValue('problemID')
                ),
                'projectLink'                  => BUProject::getCurrentProjectLink(
                    $dsCallActivity->getValue('customerID')
                ),
                'contractListPopupLink'        => $this->getContractListPopupLink(
                    $dsCallActivity->getValue('customerID')
                ),
                'javaScript'                   => $javaScript,
                'bodyTagExtras'                => $bodyTagExtras,
                'DISABLED'                     => $disabled,
                'COMPLETE_DISABLED'            => $complete_disabled,
                'INITIAL_DISABLED'             => $initial_disabled,
                'INITIAL_DATE_DISABLED'        => $canChangeInitialDateAndTime ? '' : "disabled",
                'PRIORITY_DISABLED'            => $priority_disabled,
                'CONTRACT_DISABLED'            => $contract_disabled,
                'setTimeNowLink'               => $setTimeNowLink,
                'calendarLinkDate'             => $calendarLinkDate,
                'completeDate'                 => Controller::dateYMDtoDMY($dsCallActivity->getValue('completeDate')),
                'contactIDMessage'             => Controller::htmlDisplayText($dsCallActivity->getMessage('contactID')),
                'alarmDate'                    => Controller::dateYMDtoDMY($dsCallActivity->getValue('alarmDate')),
                'alarmTime'                    => $dsCallActivity->getValue(
                    'alarmTime'
                ) != '00:00:00' ? $dsCallActivity->getValue('alarmTime') : '',
                'alarmDateMessage'             => Controller::htmlDisplayText($dsCallActivity->getMessage('alarmDate')),
                'alarmTimeMessage'             => Controller::htmlDisplayText($dsCallActivity->getMessage('alarmTime')),

                'hideFromCustomerFlagChecked' => Controller::htmlChecked($hideFromCustomerFlag),

                'hideFromCustomerDisabled' => $hideFromCustomerDisabled,

                'hdRemainMinutes'           => $hdAssignedMinutes - $hdUsedMinutes,
                'esRemainMinutes'           => $esAssignedMinutes - $esUsedMinutes,
                'imRemainMinutes'           => $imAssignedMinutes - $imUsedMinutes,
                'hdUsedMinutesNotInclusive' => $hdUsedMinutesNotInclusive,
                'esUsedMinutesNotInclusive' => $esUsedMinutesNotInclusive,
                'imUsedMinutesNotInclusive' => $imUsedMinutesNotInclusive,
                'hdAssignedMinutes'         => $hdAssignedMinutes,
                'hdUsedMinutes'             => $hdUsedMinutes,
                'esAssignedMinutes'         => $esAssignedMinutes,
                'esUsedMinutes'             => $esUsedMinutes,
                'imAssignedMinutes'         => $imAssignedMinutes,
                'imUsedMinutes'             => $imUsedMinutes,
                'userWarned'                => $this->userWarned,
                'authoriseHide'             => $authorisedByName ? '' : 'hidden',
                'authorisedByName'          => $authorisedByName,
                'salesRequestStatus'        => $dsCallActivity->getValue(DBECallActivity::salesRequestStatus)
            )
        );

        $this->documents(
            $callActivityID,
            $dsCallActivity->getValue('problemID'),
            'ActivityEdit'
        );

        $this->rootCauseDropdown(
            $dsCallActivity->getValue('rootCauseID'),
            'ActivityEdit',
            'rootCauseBlock'
        );

        $this->activityTypeDropdown($dsCallActivity->getValue('callActTypeID'));

        $this->priorityDropdown(
            $dsCallActivity->getValue("priority"),
            'ActivityEdit'
        );

        if ($dsCallActivity->getValue("siteNo") != '') {
            $this->contactDropdown(
                'ActivityEdit',
                $dsCallActivity->getValue('customerID'),
                $dsCallActivity->getValue("contactID")
            );
        }
        // user selection
        $dbeUser = new DBEUser($this);
        $dbeUser->getRows(false);        // get ALL users including inactive

        $this->template->set_block(
            'ActivityEdit',
            'userBlock',
            'users'
        );

        while ($dbeUser->fetchNext()) {

            if ($dsCallActivity->getValue("userID") == $dbeUser->getValue("userID")) {
                $userSelected = CT_SELECTED;
            } else {
                $userSelected = '';

                if ($dbeUser->getValue('activeFlag') == 'N') {
                    continue;
                }

            }

            $this->template->set_var(
                array(
                    'userSelected' => $userSelected,
                    'userID'       => $dbeUser->getValue("userID"),
                    'userName'     => $dbeUser->getValue("name")
                )
            );
            $this->template->parse(
                'users',
                'userBlock',
                true
            );
        }

        $this->siteDropdown(
            $dsCallActivity->getValue('customerID'),
            $dsCallActivity->getValue("siteNo"),
            'ActivityEdit',
            'siteBlock'
        );

        //Contract selection
        $this->contractDropdown(
            $dsCallActivity->getValue('customerID'),
            $dsCallActivity->getValue("contractCustomerItemID"),
            'ActivityEdit',
            'contractBlock'
        );
        /*
      Use initial activity date to determine list of projects
      */
        $dbeInitialActivity = $this->buActivity->getFirstActivityInProblem(
            $dsCallActivity->
            getValue('problemID')
        );

        $this->projectDropdown(
            $dsCallActivity->getValue('projectID'),
            $dsCallActivity->getValue('customerID'),
            'ActivityEdit',
            'projectBlock',
            $dbeInitialActivity->getValue('date')
        );

        $this->template->parse(
            'activityWizardHeader',
            'ActivityWizardHeader',
            true
        );

        $this->template->parse(
            'htmlInternalNotes',
            'ActivityEditInternalNotes',
            true
        );


        $this->template->parse(
            'CONTENTS',
            'ActivityEdit',
            true
        );
        $this->parsePage();
    }// end changeRequestApproval

    private
    function activityTypeDropdown($callActTypeID
    )
    {
        $dbeJCallActType = new DBECallActType($this);
        $buUser = new BUUser($this);
        $dbeJCallActType->getActiveAndVisibleRows(!$buUser->isSdManager($this->userID));
        $current = new DBECallActType($this);
        $current->getRow($callActTypeID);

        $this->template->set_block(
            'ActivityEdit',
            'activityTypeBlock',
            'activities'
        );

        $foundCurrent = false;
        while ($dbeJCallActType->fetchNext()) {

            $activityTypeSelected = ($callActTypeID == $dbeJCallActType->getValue('callActTypeID')
            ) ? CT_SELECTED : '';

            if ($activityTypeSelected == CT_SELECTED) {
                $foundCurrent = true;
            }

            $this->template->set_var(
                array(
                    'activityTypeSelected' => $activityTypeSelected,
                    'callActTypeID'        => $dbeJCallActType->getValue("callActTypeID"),
                    'activityTypeDesc'     => $dbeJCallActType->getValue("description")
                )
            );

            $this->template->parse(
                'activities',
                'activityTypeBlock',
                true
            );
        }

        if (!$foundCurrent && $callActTypeID) {
            $this->template->set_var(
                array(
                    'activityTypeSelected' => 'selected',
                    'callActTypeID'        => $current->getValue("callActTypeID"),
                    'activityTypeDesc'     => $current->getValue("description")
                )
            );

            $this->template->parse(
                'activities',
                'activityTypeBlock',
                true
            );

            $this->template->set_var(
                "typeDisabled",
                'disabled'
            );
        }
    }

    function projectDropdown(
        $projectID,
        $customerID,
        $templateName,
        $blockName = 'projectBlock',
        $activityDate
    )
    {
        // Display list of projects that are current at given activity date
        $buProject = new BUProject($this);

        $buProject->getProjectsByCustomerID(
            $customerID,
            $dsProject,
            $activityDate
        );
        $this->template->set_block(
            $templateName,
            $blockName,
            'projects'
        );

        while ($dsProject->fetchNext()) {


            $projectSelected = ($dsProject->getValue("projectID") == $projectID) ? CT_SELECTED : '';

            $this->template->set_var(
                array(
                    'projectID'          => $dsProject->getValue("projectID"),
                    'projectDescription' => $dsProject->getValue("description"),
                    'projectSelected'    => $projectSelected,
                )
            );

            $this->template->parse(
                'projects',
                $blockName,
                true
            );


        }

        $projectPleaseSelect = ($projectID == 99) ? CT_SELECTED : '';

        $noProject = ($projectID == 0) ? CT_SELECTED : '';

        $this->template->set_var(
            array(
                'noProject'           => $noProject,
                'projectPleaseSelect' => $projectPleaseSelect
            )
        );

    }

    /**
     * Delete Activity
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function deleteCallActivity()
    {
        $this->setMethodName('deleteCallActivity');
        $this->buActivity->getActivityByID(
            $_REQUEST['callActivityID'],
            $dsCallActivity
        );

        $callActivityID = $dsCallActivity->getValue('callActivityID');

        $problemID = $this->buActivity->deleteCallActivity($_REQUEST['callActivityID']);

        /*
      if the whole service requested has been removed then redirect to search page
      otherwise display problem
      */
        if (!$problemID) {
            $urlNext =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTCNC_ACT_DISPLAY_SEARCH_FORM
                    )
                );
        } else {
            $urlNext =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => 'displayLastActivity',
                        'problemID' => $problemID
                    )
                );

        }
        header('Location: ' . $urlNext);
    }  // end finaliseProblem

    /**
     * Cancel Edit/Add Activity
     *
     * Where there have been no details added, also delete the activity record and
     * return user to parent activity view (if a parent activity exists) otherwise
     * man activities page
     *
     * @access private
     */
    function cancelEdit()
    {
        $this->setMethodName('cancelEdit');

        if (!$this->buActivity->getActivityByID(
            $_REQUEST['callActivityID'],
            $dsCallActivity
        )) {
            $this->raiseError('Activity ID ' . $_REQUEST['callActivityID'] . ' not found');
            exit;
        }

        if ($dsCallActivity->getValue('callActTypeID') == 0) {                    //delete this activity

            $this->buActivity->deleteCallActivity($_REQUEST['callActivityID']);

            if ($dsCallActivity->getValue('problemID') != 0) {

                $dbeCallActivity = $this->buActivity->getLastActivityInProblem($dsCallActivity->getValue('problemID'));

                $this->redirectToDisplay($dbeCallActivity->getValue('callActivityID'));

            } else {

                $urlNext =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTCNC_ACT_DISPLAY_SEARCH_FORM
                        )
                    );
                header('Location: ' . $urlNext);

            }

        } else {
            $this->redirectToDisplay($_REQUEST['callActivityID']);

        }

    }

    /**
     * Update call activity details
     * @access private
     */
    function updateCallActivity()
    {
        $this->setMethodName('updateCallActivity');
        $dsCallActivity = &$this->dsCallActivity;
        $this->formError = (!$this->dsCallActivity->populateFromArray($_REQUEST['callActivity']));

        $callActivityID = $dsCallActivity->getValue('callActivityID');

        // these names must not be part of an html array as the fckeditor does not work
        $dsCallActivity->setUpdateModeUpdate();
        $dsCallActivity->setValue(
            'reason',
            $_POST['reason']
        );
        $dsCallActivity->setValue(
            'internalNotes',
            $_POST['internalNotes']
        );
        $dsCallActivity->post();

        if ($dsCallActivity->getValue('callActTypeID') == 0 || $dsCallActivity->getValue('callActTypeID') == '') {
            $this->formError = true;
            $this->dsCallActivity->setMessage(
                'callActTypeID',
                'Required'
            );
        } else {

            $dbeCallActType = new DBEJCallActType($this);
            $dbeCallActType->getRow($dsCallActivity->getValue('callActTypeID'));
            if ($dsCallActivity->getValue('siteNo') == '') {
                $this->formError = true;
                $this->dsCallActivity->setMessage(
                    'siteNo',
                    'Required'
                );
            }

            if ($dsCallActivity->getValue('contactID') == '' OR $dsCallActivity->getValue('contactID') == 0) {
                $this->formError = true;
                $this->dsCallActivity->setMessage(
                    'contactID',
                    'Required'
                );
            } else {
                if ($this->buActivity->needsTravelHoursAdding(
                    $dsCallActivity->getValue('callActTypeID'),
                    $dsCallActivity->getValue('customerID'),
                    $dsCallActivity->getValue('siteNo')
                )) {
                    $this->formError = true;
                    $this->dsCallActivity->setMessage(
                        'callActTypeID',
                        'Travel hours need entering for this site'
                    );
                }

                // is the selected contact a nominated support contact?
                $buCustomer = new BUCustomer($this);
                if (!$buCustomer->isASupportContact($dsCallActivity->getValue('contactID'))) {
                    $this->formError = true;
                    $this->dsCallActivity->setMessage(
                        'contactID',
                        'Not a nominated support contact'
                    );
                }
            }

            if ($dbeCallActType->getValue('curValueFlag') == 'Y' && $this->dsCallActivity->getValue('curValue') == '') {
                $this->formError = true;
                $this->dsCallActivity->setMessage(
                    'curValue',
                    'Required'
                );
            } else {  // if no end time set then set to time now
                if (
                    !isset($_REQUEST['Update']) &&
                    $dbeCallActType->getValue('requireCheckFlag') == 'N' &&
                    $dbeCallActType->getValue('onSiteFlag') == 'N' &&
                    $this->dsCallActivity->getValue('endTime') == ''
                ) {
                    $this->dsCallActivity->setValue(
                        'endTime',
                        date('H:i')
                    );
                }
                // required fields
                if ($dbeCallActType->getValue('reqReasonFlag') == 'Y' && trim(
                        $this->dsCallActivity->getValue('reason')
                    ) == '') {
                    $this->formError = true;
                    $this->dsCallActivity->setMessage(
                        'reason',
                        'Required'
                    );
                }

                if ($dbeCallActType->getValue('reqReasonFlag') == 'Y' && trim(
                        $this->dsCallActivity->getValue('reason')
                    ) == '') {
                    $this->formError = true;
                    $this->dsCallActivity->setMessage(
                        'reason',
                        'Required'
                    );
                }


                if (
                    $dsCallActivity->getValue('contractCustomerItemID') &&
                    $dsCallActivity->getValue('projectID')
                ) {
                    $this->dsCallActivity->setMessage(
                        'projectID',
                        'Project work must be logged under T&M'
                    );
                    $this->formError = true;
                }
                /*
          Date/time must be after Initial activity
          */
                if ($dsCallActivity->getValue('callActTypeID') != CONFIG_INITIAL_ACTIVITY_TYPE_ID) {

                    $dbeFirstActivity = $this->buActivity->getFirstActivityInProblem(
                        $this->dsCallActivity->getValue('problemID')
                    );

                    if (
                        $this->dsCallActivity->getValue('date') . $this->dsCallActivity->getValue('startTime') <
                        $dbeFirstActivity->getValue('date') . $dbeFirstActivity->getValue('startTime')
                    ) {
                        $this->formError = true;
                        $this->dsCallActivity->setMessage(
                            'date',
                            'Date/time must be after Initial activity'
                        );
                    }
                }


                // Only require these if the activity is ended
                if ($this->dsCallActivity->getValue('endTime') != '') {

                    if ($this->dsCallActivity->getValue('endTime') < $this->dsCallActivity->getValue('startTime')) {
                        $this->formError = true;
                        $this->dsCallActivity->setMessage(
                            'endTime',
                            'End time must be after start time!'
                        );
                    }

                    $durationHours = common_convertHHMMToDecimal(
                            $dsCallActivity->getValue('endTime')
                        ) - common_convertHHMMToDecimal($dsCallActivity->getValue('startTime'));

                    $durationMinutes = convertHHMMToMinutes(
                            $dsCallActivity->getValue('endTime')
                        ) - convertHHMMToMinutes($dsCallActivity->getValue('startTime'));


                    $activityType = $dsCallActivity->getValue('callActTypeID');

                    if (in_array(
                        $activityType,
                        [4, 8, 11, 18]
                    )) {
                        $problemID = $dsCallActivity->getValue('problemID');

                        $userID = $dsCallActivity->getValue(DBEJCallActivity::userID);
                        $dbeUser = new DBEUser($this);
                        $dbeUser->setValue(
                            'userID',
                            $userID
                        );
                        $dbeUser->getRow();

                        $dbeProblem = new DBEProblem($this);
                        $dbeProblem->setValue(
                            DBEProblem::problemID,
                            $problemID
                        );
                        $dbeProblem->getRow();

                        $teamID = $dbeUser->getValue('teamID');

                        if ($teamID <= 4) {
                            $usedTime = 0;
                            $allocatedTime = 0;

                            if ($teamID == 1) {
                                $usedTime = $this->buActivity->getHDTeamUsedTime(
                                    $problemID,
                                    $callActivityID
                                );
                                $allocatedTime = $dbeProblem->getValue(DBEProblem::hdLimitMinutes);
                            }

                            if ($teamID == 2) {
                                $usedTime = $this->buActivity->getESTeamUsedTime(
                                    $problemID,
                                    $callActivityID
                                );
                                $allocatedTime = $dbeProblem->getValue(DBEProblem::esLimitMinutes);
                            }

                            if ($teamID == 4) {
                                $usedTime = $this->buActivity->getIMTeamUsedTime(
                                    $problemID,
                                    $callActivityID
                                );
                                $allocatedTime = $dbeProblem->getValue(DBEProblem::imLimitMinutes);
                            }

                            if ($usedTime + $durationMinutes > $allocatedTime) {
                                $this->formError = true;
                                $this->dsCallActivity->setMessage(
                                    'endTime',
                                    'You cannot assign more time than left over'
                                );
                            }
                        }

                        if (!$_REQUEST['userWarned']) {

                            $buHeader = new BUHeader($this);
                            $buHeader->getHeader($dsHeader);


                            if (
                                $dsCallActivity->getValue(
                                    'callActTypeID'
                                ) == CONFIG_CUSTOMER_CONTACT_ACTIVITY_TYPE_ID &&
                                $durationHours > $dsHeader->getValue('customerContactWarnHours')
                            ) {
                                $this->formError = true;
                                $this->userWarned = true;
                                $this->dsCallActivity->setMessage(
                                    'endTime',
                                    'Warning: Duration exceeds ' . $dsHeader->getValue(
                                        'customerContactWarnHours'
                                    ) . ' hours'
                                );

                            }

                            if ($dsCallActivity->getValue(
                                    'callActTypeID'
                                ) == CONFIG_REMOTE_TELEPHONE_ACTIVITY_TYPE_ID) {
                                if ($durationHours > $dsHeader->getValue('remoteSupportWarnHours')) {
                                    $this->formError = true;
                                    $this->userWarned = true;
                                    $this->dsCallActivity->setMessage(
                                        'endTime',
                                        'Warning: Activity duration exceeds ' . $dsHeader->getValue(
                                            'remoteSupportWarnHours'
                                        ) . ' hours'
                                    );
                                }

                                $minHours = $dsHeader->getValue(DBEHeader::RemoteSupportMinWarnHours);

                                if ($durationHours < $minHours) {
                                    $this->formError = true;
                                    $this->userWarned = true;
                                    $this->dsCallActivity->setMessage(
                                        'endTime',
                                        'Remote support under ' . (floor(
                                            $minHours * 60
                                        )) . ' minutes, should this be Customer Contact instead?”.'
                                    );
                                }

                            }
                        }
                    }

                }
            }

            $problemID = $dsCallActivity->getValue('problemID');
            if (isset($_REQUEST['problem']) && isset($_REQUEST['problem'][$problemID]) && isset($_REQUEST['problem'][$problemID]['authorisedBy'])) {
                $dbeProblem = new DBEProblem($this);
                $dbeProblem->setValue(
                    DBEProblem::problemID,
                    $problemID
                );
                $dbeProblem->getRow();

                $dbeProblem->setValue(
                    DBEProblem::authorisedBy,
                    $_REQUEST['problem'][$problemID]['authorisedBy']
                );
                $dbeProblem->updateRow();
            }


            if (isset($_REQUEST['Fixed'])) {

                //try to close all the activities
                $this->buActivity->closeActivitiesWithEndTime($dsCallActivity->getValue('problemID'));
                if ($this->buActivity->countOpenActivitiesInRequest(
                        $dsCallActivity->getValue('problemID'),
                        $dsCallActivity->getValue('callActivityID')
                    ) > 0) {
                    $this->dsCallActivity->setMessage(
                        'problemStatus',
                        'Can not fix, there are open activities on this request'
                    );
                    $this->formError = true;
                }
            }
        }
        if ($this->formError) {
            if ($_REQUEST['action'] == CTACTIVITY_ACT_INSERT_ACTIVITY) {
                $_REQUEST['callActivityID'] = $callActivityID;
                $_REQUEST['action'] = CTACTIVITY_ACT_CREATE_ACTIVITY;
            } else {
                $_REQUEST['action'] = CTACTIVITY_ACT_EDIT_ACTIVITY;
            }
            $this->editActivity();
            exit;
        }

        /*
      Record action button selected
      */
        if (isset($_REQUEST['Fixed'])) {
            $nextStatus = 'Fixed';
        } elseif (isset($_REQUEST['CustomerAction'])) {
            $dsCallActivity->setUpdateModeUpdate();
            $dsCallActivity->setValue(
                'awaitingCustomerResponseFlag',
                'Y'
            );
            $dsCallActivity->post();
            $nextStatus = 'CustomerAction';
        } elseif (isset($_REQUEST['CncAction'])) {
            $dsCallActivity->setUpdateModeUpdate();
            $dsCallActivity->setValue(
                'awaitingCustomerResponseFlag',
                'N'
            );
            $dsCallActivity->post();
            $nextStatus = 'CncAction';
        } elseif (isset($_REQUEST['Escalate'])) {
            $nextStatus = 'Escalate';
            $this->buActivity->escalateProblemByCallActivityID($callActivityID);
        } else {
            $nextStatus = false;
        }

        if ($nextStatus == 'Fixed') {
            $isFixed = true;
        } else {
            $isFixed = false;
        }

        $enteredEndTime = $this->buActivity->updateCallActivity(
            $this->dsCallActivity,
            $isFixed
        );

        /*
      If an end time was entered and this is a chargeable on site activity then see whether to
      create a travel activity automatically OR if one exists for today prompt whether another should be
      added.


      @todo: What should happen now that we have buttons for next action to take? e.g. Fixed/customer action etc
      */
        if (
            $enteredEndTime &&
            $dbeCallActType->getValue('onSiteFlag') == 'Y' &&
            $dbeCallActType->getValue('itemSalePrice') > 0
        ) {
            $dbeSite = new DBESite($this);
            $dbeSite->setValue(
                DBESite::customerID,
                $this->dsCallActivity->getValue('customerID')
            );
            $dbeSite->setValue(
                DBESite::siteNo,
                $this->dsCallActivity->getValue('siteNo')
            );
            $dbeSite->getRowByCustomerIDSiteNo();
            if (
                $this->buActivity->travelActivityForCustomerEngineerTodayExists(
                    $this->dsCallActivity->getValue('customerID'),
                    $this->dsCallActivity->getValue('siteNo'),
                    $this->dsCallActivity->getValue('userID'),
                    $this->dsCallActivity->getValue('date')
                )
                && $dbeSite->getValue(DBESite::maxTravelHours) > 0    // the site has travel hours

            ) {
                $urlNext =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'callActivityID' => $callActivityID,
                            'action'         => 'promptCreateTravel',
                            'nextStatus'     => $nextStatus
                        )
                    );
                header('Location: ' . $urlNext);
                exit;
            } else {
                $this->buActivity->createTravelActivity($callActivityID);
            }

        }

        if ($nextStatus == 'Fixed') {

            //try to close all the activities
            $this->buActivity->closeActivitiesWithEndTime($dsCallActivity->getValue('problemID'));
            if ($this->buActivity->countOpenActivitiesInRequest($dsCallActivity->getValue('problemID')) > 0) {
                $this->dsCallActivity->setMessage(
                    'problemStatus',
                    'Can not fix, there are open activities on this request'
                );
                $_REQUEST['callActivityID'] = $callActivityID;
                $_REQUEST['action'] = CTACTIVITY_ACT_EDIT_ACTIVITY;
                $this->editActivity();
                exit;
            } else {
                $urlNext =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'callActivityID' => $callActivityID,
                            'action'         => 'gatherFixedInformation'
                        )
                    );

                header('Location: ' . $urlNext);
                exit;
            }
        }

        $this->redirectToDisplay($callActivityID);
        exit;
    }

    /**
     * Check Activity ready for export
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function checkActivity()
    {
        $this->setMethodName('checkActivity');
        $this->buActivity->getActivityByID(
            $_REQUEST['callActivityID'],
            $dsCallActivity
        );
        $this->buActivity->setActivityStatusChecked($_REQUEST['callActivityID']);

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => CTACTIVITY_ACT_DISPLAY_ACTIVITY,
                    'callActivityID' => $dsCallActivity->getValue('callActivityID')
                )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Create copy of this activity but with:
     *        start time now and end time not set
     *    User = current user
     *        date = today
     *        Status = not completed
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function createFollowOnActivity()
    {

        $this->setMethodName('createFollowOnActivity');

        $newActivityID = $this->buActivity->createFollowOnActivity(
            $_REQUEST['callActivityID'],
            $_REQUEST['callActivityTypeID'],
            false,
            $_REQUEST['reason'],
            true,
            false,
            $GLOBALS['auth']->is_authenticated(),
            $_REQUEST['moveToUsersQueue']
        );

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => CTACTIVITY_ACT_EDIT_ACTIVITY,
                    'callActivityID' => $newActivityID
                )
            );

        header('Location: ' . $urlNext);
    }

    function createRequestFromCustomerRequest()
    {
        $customerproblemno = $_REQUEST['cpr_customerproblemno'];

        $customerproblem = $this->buActivity->getCustomerRaisedRequest($customerproblemno);

        if ($customerproblem['con_custno']) {
            $_REQUEST['customerID'] = $customerproblem['con_custno'];
            $_REQUEST['contactID'] = $customerproblem['cpr_contno'];

        }

        $_REQUEST['reason'] = '<p>' . str_replace(
                "\n",
                '<BR/>',
                $customerproblem['cpr_reason']
            ) . '</p>';

        $this->activityCreate1(false);

        $this->buActivity->deleteCustomerRaisedRequest($customerproblemno);

    }

    function updateRequestFromCustomerRequest()
    {
        $customerproblemno = $_REQUEST['cpr_customerproblemno'];

        $customerproblem = $this->buActivity->getCustomerRaisedRequest($customerproblemno);

        $dbeCallActivity = $this->buActivity->getLastActivityInProblem($customerproblem['cpr_problemno']);

        $callActivityID =
            $this->buActivity->createFollowOnActivity(
                $dbeCallActivity->getValue('callActivityID'),
                CONFIG_CUSTOMER_CONTACT_ACTIVITY_TYPE_ID,
                $customerproblem['cpr_contno'],
                $customerproblem['cpr_reason'],

                false,
                true,
                USER_SYSTEM
            );

        $this->buActivity->deleteCustomerRaisedRequest($customerproblemno);

        $this->redirectToDisplay($callActivityID);

    }

    function displayServiceRequest()
    {
        $dbeCallActivity = $this->buActivity->getLastActivityInProblem($_REQUEST['problemID']);
        $this->redirectToDisplay($dbeCallActivity->getValue('callActivityID'));

    }

    function setProblemFixed()
    {

        if (!isset($_REQUEST['problemID'])) {
            $this->raiseError('problemID not passed');
            exit;
        }
        if (!isset($_REQUEST['callActivityID'])) {
            $this->raiseError('callActivityID not passed');
            exit;
        }

        $this->buActivity->setProblemToFixed($_REQUEST['problemID']);

        $this->redirectToDisplay($_REQUEST['callActivityID']);

        exit;
    }

    /**
     * Customer has confirmed this Service Request is completed
     *
     */
    function setProblemComplete()
    {

        if (!isset($_REQUEST['problemID'])) {
            $this->raiseError('problemID not passed');
            exit;
        }

        $newActivityID = $this->buActivity->setProblemToCompleted($_REQUEST['problemID']);

        // Allow details to be entered
        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => CTACTIVITY_ACT_EDIT_ACTIVITY,
                    'callActivityID' => $newActivityID
                )
            );

        header('Location: ' . $urlNext);
    }

    function linkProblems()
    {
        if (!isset($_REQUEST['toCallActivityID'])) {
            $this->raiseError('No toCallActivityID Passed');
            exit;
        }
        if (!isset($_REQUEST['fromCallActivityID'])) {
            $this->raiseError('No fromCallActivityID Passed');
            exit;
        }

        if (isset($_REQUEST['linkWholeProblem'])) {
            $wholeProblem = true;
        } else {
            $wholeProblem = false;
        }

        if (
        $this->buActivity->linkActivities(
            $_REQUEST['fromCallActivityID'],
            $_REQUEST['toCallActivityID'],
            $wholeProblem
        )
        ) {

            // redirect
            $this->redirectToDisplay($_REQUEST['fromCallActivityID']);

        } else {
            $this->redirectToDisplay($_REQUEST['fromCallActivityID']);

        }

    }

    /**
     * Export General Support activities that have not previously been exported to a CSV file
     * @access private
     */
    function gscExportForm($dsResults = false)
    {
        $this->setMethodName('gscExportForm');
        $urlPreview = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTACTIVITY_ACT_EXPORT_GENERATE,
                'update' => 0
            )
        );
        $urlExport = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTACTIVITY_ACT_EXPORT_GENERATE,
                'update' => 1
            )
        );
        $this->setPageTitle('Export General Support Contract Activities');
        $this->setTemplateFiles(
            'GSCExport',
            'GSCExport.inc'
        );
        /*
      if (!$this->getFormError()){
      $this->buActivity->initialiseExportDataset($this->dsGSCExport);
      }
      */
        if (!is_object($this->dsGSCExport)) {
            $this->buActivity->initialiseExportDataset($this->dsGSCExport);
        }
        $this->template->set_var(
            array(
                'endDate'        => Controller::dateYMDtoDMY($this->dsGSCExport->getValue('endDate')),
                'endDateMessage' => Controller::dateYMDtoDMY($this->dsGSCExport->getMessage('endDate')),
                'urlPreview'     => $urlPreview,
                'urlExport'      => $urlExport
            )
        );

        if ($dsResults) {
            $dsResults->initialise();
            $this->template->set_block(
                'GSCExport',
                'resultBlock',
                'results'
            ); // ss avoids naming confict!
            while ($dsResults->fetchNext()) {
                $urlStatement =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'         => CTACTIVITY_ACT_EDIT_CALL,
                            'callActivityID' => $_REQUEST['callActivityID']
                        )
                    );

                $this->template->setVar(
                    array(
                        'customerName'    => $dsResults->getValue('customerName'),
                        'previousBalance' => $dsResults->getValue('previousBalance'),
                        'currentBalance'  => $dsResults->getValue('currentBalance'),
                        'topUp'           => $dsResults->getValue('topUp'),
                        'expiryDate'      => $dsResults->getValue('expiryDate'),
                        'contacts'        => $dsResults->getValue('contacts'),
                        'contractType'    => $dsResults->getValue('contractType'),
                        'webFileLink'     => $dsResults->getValue('webFileLink')
                    )
                );
                $this->template->parse(
                    'results',
                    'resultBlock',
                    true
                );
            }
        }

        $this->template->parse(
            'CONTENTS',
            'GSCExport',
            true
        );
        $this->parsePage();
    }

    function gscExportGenerate()
    {
        $this->setMethodName('gscExportGenerate');
        $this->buActivity->initialiseExportDataset($this->dsGSCExport);
        if (!$this->dsGSCExport->populateFromArray($_REQUEST['gscExport'])) {
            $this->setFormErrorOn();
        } else {
            $dsResults =
                $this->buActivity->exportPrePayActivities(
                    $this->dsGSCExport,
                    $_REQUEST['update']
                );

            if ($_REQUEST['update']) {
                if ($dsResults) {
                    $this->setFormErrorMessage('Export files created');
                } else {
                    $this->setFormErrorMessage('No data to export for this date');
                }
            }
        }
        $this->gscExportForm($dsResults);
    }

    /**
     * Add Activity to calendar
     * @access private
     */
    function addToCalendar()
    {
        $this->setMethodName('addToCalendar');
        $this->template->set_file(
            'page',
            'AddToCalendar.inc.ics'
        );

        $this->buActivity->getActivityByID(
            $_REQUEST['callActivityID'],
            $dsCallActivity
        );

        $buSite = new BUSite($this);
        $buSite->getSiteByID(
            $dsCallActivity->getValue('customerID'),
            $dsCallActivity->getValue('siteNo'),
            $dsSite
        );
        $buCustomer = new BUCustomer($this);

        $callRef = $dsCallActivity->getValue('callActivityID');
        if ($dsCallActivity->getValue('endTime') == '') {
            $endTime = $dsCallActivity->getValue('startTime');
        } else {
            $endTime = $dsCallActivity->getValue('endTime');
        }

        $urlActivity =
            'http://' . $_SERVER['HTTP_HOST'] .
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'callActivityID' => $dsCallActivity->getValue('callActivityID'),
                    'action'         => CTACTIVITY_ACT_DISPLAY_ACTIVITY
                )
            );

        $notes = 'Details:\n\n' . CTActivity::prepareForICS($dsCallActivity->getValue('reason'));
        if ($dsCallActivity->getValue('internalNotes')) {
            $notes .= '\n\nInternal Notes:\n\n' . CTActivity::prepareForICS($dsCallActivity->getValue('internalNotes'));
        }

        $this->template->set_var(
            array(
                'scrRef'       => $callRef,
                'userName'     => $dsCallActivity->getValue('userName'),
                'contactName'  => $dsCallActivity->getValue('contactName'),
                'contactPhone' => $buCustomer->getContactPhone($dsCallActivity->getValue('contactID')),
                'dateYYYYMMDD' => str_replace(
                    '-',
                    '',
                    $dsCallActivity->getValue('date')
                ),
                'nowYYYYMMDD'  => date('Ymd'),
                'nowHHMMSS'    => date('His'),
                'startHHMM'    => str_replace(
                    ':',
                    '',
                    $dsCallActivity->getValue('startTime')
                ),
                'endHHMM'      => str_replace(
                    ':',
                    '',
                    $endTime
                ),
                'customerName' => $dsCallActivity->getValue('customerName'),
                'notes'        => $notes,
                'add1'         => $dsSite->getValue(DBESite::add1),
                'add2'         => $dsSite->getValue(DBESite::add2),
                'add3'         => $dsSite->getValue(DBESite::add3),
                'town'         => $dsSite->getValue(DBESite::town),
                'county'       => $dsSite->getValue(DBESite::county),
                'postcode'     => $dsSite->getValue(DBESite::postcode),
                'urlActivity'  => $urlActivity
            )
        );
        $this->template->parse(
            'output',
            'page',
            true
        );
        $output = $this->template->get_var('output');
        header("Content-Type: text/calendar");
        header("Content-Disposition: inline; filename=Appointment.ics");
        print $output;
        exit;
    }

    /**
     * prepareForICS
     *
     * @param mixed $description
     * @return mixed
     */
    function prepareForICS($description)
    {
        $description = str_replace(
            "\r\n",
            '',
            trim($description)
        );
        $description = str_replace(
            "\n",
            '',
            $description
        );
        $description = str_replace(
            '<br />',
            '\n',
            $description
        );
        $description = str_replace(
            '<br/>',
            '\n',
            $description
        );
        $description = str_replace(
            '<BR/>',
            '\n',
            $description
        );
        $description = str_replace(
            '<BR>',
            '\n',
            $description
        );
        $description = str_replace(
            '</p>',
            '\n',
            $description
        );
        $description = html_entity_decode(
            $description,
            ENT_QUOTES
        );
        return strip_tags($description);

    }

    /**
     * Sends a site visit confirmation email to the activity contact
     * @access private
     */
    private
    function sendVisitEmail()
    {
        $this->setMethodName('sendVisitEmail');
        $this->buActivity->sendSiteVisitEmail($_REQUEST['callActivityID']);
        $this->redirectToDisplay($_REQUEST['callActivityID']);
    }

    /**
     * Upload new document from local disk
     * @access private
     */
    function uploadFile()
    {
        // validate
        if ($_REQUEST['problemID'] == '') {
            $this->setFormErrorMessage('problemID not passed');
        }
        if ($_REQUEST['uploadDescription'] == '') {
            $this->setFormErrorMessage('Please enter a description');
        }
        if ($_FILES['userfile']['name'] == '') {
            $this->setFormErrorMessage('Please enter a file path');
        }
        if (!is_uploaded_file($_FILES['userfile']['tmp_name'])) {
            $this->setFormErrorMessage('Document not loaded - is it bigger than 6 MBytes?');
        }
        if ($this->formError) {
            if ($_POST['gatherFixed']) {

                $this->redirectToFixed($_REQUEST['callActivityID']);
            }

            if ($_POST['edit']) {
                $this->editActivity();
                exit;
            }

            $this->displayActivity();
            exit;
        }
        $this->buActivity->uploadDocumentFile(
            $_REQUEST['problemID'],
            $_REQUEST['uploadDescription'],
            $_FILES['userfile']
        );

        if ($_POST['gatherFixed']) {
            $this->redirectToFixed($_REQUEST['callActivityID']);
        }

        if ($_POST['edit']) {
            $this->redirectToEdit($_REQUEST['callActivityID']);
            exit;
        }

        $this->redirectToDisplay($_REQUEST['callActivityID']);
    }

    /**
     * Generate page required to embed file
     * this is done because simply calling documentView() with PDF files causes
     * IE to call documentView a second time! this is a known problem. The workaround
     * is to produce a page with and EMBED tag that makes a call back to the server.
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function viewFile()
    {
        // Validation and setting of variables
        $this->setMethodName('viewFile');
        $dbeCallDocument = new DBECallDocument($this);
        if (!$dbeCallDocument->getRow($_REQUEST['callDocumentID'])) {
            $this->displayFatalError('Activity file not found.');
        }
        $this->getFile();
        exit;
    }

    /**
     * echo given document to client
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function getFile()
    {
        // Validation and setting of variables
        $this->setMethodName('getFile');

        if (!$db = mysqli_connect(
            DB_HOST,
            DB_USER,
            DB_PASSWORD
        )) {
            echo 'Could not connect to mysql host ' . DB_HOST;
            exit;
        }
        mysqli_select_db(
            $db,
            DB_NAME
        );

        $query =
            "SELECT * FROM calldocument
      WHERE callDocumentID = " . $_REQUEST['callDocumentID'];

        $result = mysqli_query(
            $db,
            $query
        );

        $row = mysqli_fetch_assoc($result);

        header('Content-type: ' . $row['fileMIMEType']);
//      header('Content-Length: ' . $row['fileLength']);
        header('Content-Disposition: attachment; filename="' . $row['filename'] . '"');

        print $row['file'];

        exit;
    }

    /**
     * Generate page required to embed file
     * this is done because simply calling documentView() with PDF files causes
     * IE to call documentView a second time! this is a known problem. The workaround
     * is to produce a page with and EMBED tag that makes a call back to the server.
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function deleteFile()
    {
        // Validation and setting of variables
        $this->setMethodName('deleteFile');
        $dbeCallDocument = new DBECallDocument($this);
        if (!$dbeCallDocument->getRow($_REQUEST['callDocumentID'])) {
            $this->displayFatalError('Document not found.');
        }
        $callActivityID = $_REQUEST['callActivityID'];
        $dbeCallDocument->deleteRow();
        if ($_GET['isEdit']) {
            return $this->redirectToEdit($callActivityID);
        }

        if ($_GET['isGather']) {
            return $this->redirectToGather($callActivityID);
        }

        return $this->redirectToDisplay($callActivityID);
    }

    /**
     * Function to automatically update the text fields of an activity when the user is in the activity
     * still
     *
     * CONFIG_ACTIVITY_AUTO_UPDATE_INTERVAL frequency
     */
    function autoUpdate()
    {
        if (!$_REQUEST['callActivityID']) {

            echo 'callActivityID not passed';

        }

        $this->buActivity->updateTextFields(
            $_REQUEST['callActivityID'],
            $_REQUEST['reason'],
            $_REQUEST['internalNotes']
        );
    }

    function gatherFixedInformation()
    {
        $this->setMethodName('gatherFixedInformation');

        $this->setTemplateFiles(
            array(
                'ServiceRequestFixedEdit'                 => 'ServiceRequestFixedEdit.inc',
                'ServiceRequestFixedEditContractDropdown' => 'ServiceRequestFixedEditContractDropdown.inc'
            )
        );

        $this->setPageTitle("Service Request Fix Summary");

        $this->buActivity->getActivityByID(
            $_REQUEST['callActivityID'],
            $dsCallActivity
        );

        /* validate if this is a POST request */
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (trim($_REQUEST['resolutionSummary']) == '') {
                $error['resolutionSummary'] = 'Please enter summary of resolution';

            }

            if ($_REQUEST['contractCustomerItemID'] == 99) {
                $error['contractCustomerItemID'] = 'Required';
            }

            if ($_REQUEST['rootCauseID'] == 0) {
                $error['rootCauseID'] = 'Required';
            }

            if (count($error) == 0) {

                $this->buActivity->setProblemToFixed(
                    $dsCallActivity->getValue('problemID'),
                    false,
                    $_REQUEST['contractCustomerItemID'],
                    $_REQUEST['rootCauseID'],
                    $_REQUEST['resolutionSummary']
                );

                if ($_REQUEST['managementReviewFlag'] == 'Y') {

                    $nextURL =
                        Controller::buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'problemID' => $dsCallActivity->getValue('problemID'),
                                'action'    => 'gatherManagementReviewDetails'
                            )
                        );

                } else {
                    $nextURL =
                        Controller::buildLink(
                            'CurrentActivityReport.php',
                            array()
                        );

                }
                header('Location: ' . $nextURL);
                exit;

            }

        }// end IF POST
        else {
            $_REQUEST['contractCustomerItemID'] = 99; // prompts for Please select
        }

        if ($_FILES['userfile']['name'] != '' & !$_REQUEST['uploadDescription']) {
            $errorFile = 'Description Required';
        }

        if (!$errorFile && isset($_FILES['userfile']) && $_FILES['userfile']['name'] != '') {
            $this->buActivity->uploadDocumentFile(
                $dsCallActivity->getValue('problemID'),
                $_REQUEST['uploadDescription'],
                $_FILES['userfile']
            );
        }

        $submitURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'gatherFixedInformation'
                )
            );

        $this->documents(
            $_REQUEST['callActivityID'],
            $dsCallActivity->getValue('problemID'),
            'ServiceRequestFixedEdit'
        );

        if (!isset($_REQUEST['resolutionSummary'])) {
            $dbeJCallActivity = $this->buActivity->getActivitiesByProblemID($dsCallActivity->getValue('problemID'));
            while ($dbeJCallActivity->fetchNext()) {
                if ($dbeJCallActivity->getValue(DBEJCallActivity::callActTypeID) == 57) {
                    $_REQUEST['resolutionSummary'] = $dbeJCallActivity->getValue(DBEJCallActivity::reason);
                }
            }
        }

        $uploadURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => 'gatherFixedInformation',
                    'problemID'      => $dsCallActivity->getValue('problemID'),
                    'callActivityID' => $_REQUEST['callActivityID']
                )
            );

        $urlMessageToSales =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => 'messageToSales',
                    'callActivityID' => $_REQUEST['callActivityID'],
                )
            );

        $urlSalesRequest =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => 'sendSalesRequest',
                    'problemID' => $dsCallActivity->getValue('problemID'),
                )
            );

        $this->template->set_var(
            array(
                'callActivityID'                => $_REQUEST['callActivityID'],
                'customerID'                    => $dsCallActivity->getValue('customerID'),
                'customerName'                  => $dsCallActivity->getValue('customerName'),
                'resolutionSummary'             => $_REQUEST['resolutionSummary'],
                'resolutionSummaryMessage'      => $error['resolutionSummary'],
                'rootCauseIDMessage'            => $error['rootCauseID'],
                'contractCustomerItemIDMessage' => $error['contractCustomerItemID'],
                'submitURL'                     => $submitURL,
                'historyLink'                   => $this->getProblemHistoryLink($dsCallActivity->getValue('problemID')),
                'uploadErrors'                  => $errorFile,
                'uploadURL'                     => $uploadURL,
                'urlMessageToSales'             => $urlMessageToSales,
                'urlSalesRequest'               => $urlSalesRequest
            )
        );

        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);

        $this->rootCauseDropdown(
            $_REQUEST['rootCauseID'],
            'ServiceRequestFixedEdit',
            'rootCauseBlock'
        );
        /*
      Whether to allow selection of a contract (otherwise set to T&M)
      */
        if (
            $dsCallActivity->getValue('priority') < 4 OR
            /*
        User in Accounts and priority > 3
        */
            ($this->hasPermissions(PHPLIB_PERM_ACCOUNTS) && $dsCallActivity->getValue('priority') > 3) OR

            /*
        priority > 3 and activity hours greater than system theshold
        */
            ($dsCallActivity->getValue('priority') > 3 AND $dsCallActivity->getValue(
                    'totalActivityDurationHours'
                ) < $dsHeader->getValue('srPromptContractThresholdHours'))

        ) {

            $this->contractDropdown(
                $dsCallActivity->getValue('customerID'),
                $_REQUEST['contractCustomerItemID'],
                'ServiceRequestFixedEditContractDropdown'
            );

            $this->template->parse(
                'contractDropdown',
                'ServiceRequestFixedEditContractDropdown',
                true
            );
        }

        $this->template->parse(
            'CONTENTS',
            'ServiceRequestFixedEdit',
            true
        );

        $this->parsePage();

    }

    function gatherManagementReviewDetails()
    {
        $this->setMethodName('gatherManagementReviewDetails');

        $this->setTemplateFiles(
            array(
                'ServiceRequestManagementReview' => 'ServiceRequestManagementReview.inc'
            )
        );

        $this->setPageTitle("Management Review Reason");

        /* validate if this is a POST request */
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (trim($_REQUEST['managementReviewDetails']) == '') {
                $error['managementReviewDetails'] = 'Please enter reason for management review';

            }

            if (count($error) == 0) {

                $this->buActivity->updateManagementReviewReason(
                    $_REQUEST['problemID'],
                    $_REQUEST['managementReviewDetails']
                );

                $nextURL =
                    Controller::buildLink(
                        'CurrentActivityReport.php',
                        array()
                    );

                header('Location: ' . $nextURL);
                exit;

            }

        }// end IF POST


        $submitURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'gatherManagementReviewDetails'
                )
            );

        $this->template->set_var(
            array(
                'problemID'                      => $_REQUEST['problemID'],
                'managementReviewDetails'        => $_REQUEST['managementReviewDetails'],
                'managementReviewDetailsMessage' => $error['managementReviewDetails'],
                'submitURL'                      => $submitURL,
                'historyLink'                    => $this->getProblemHistoryLink($_REQUEST['problemID'])
            )
        );


        $this->template->parse(
            'CONTENTS',
            'ServiceRequestManagementReview',
            true
        );

        $this->parsePage();

    }

    function allocateAdditionalTime()
    {
        $this->setMethodName('allocateAdditionalTime');

        $dbeFirstActivity = $this->buActivity->getFirstActivityInProblem($_REQUEST['problemID']);

        $this->setTemplateFiles(
            array(
                'ServiceRequestAllocateAdditionalTime' => 'ServiceRequestAllocateAdditionalTime.inc'
            )
        );

        $this->setPageTitle("Allocate Additional Time To Service Request");


        /* validate if this is a POST request */
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $teamLevel = $_REQUEST['teamLevel'];

            $minutes = 0;

            switch ($_REQUEST['allocatedTimeAmount']) {
                case 'minutes':
                    $minutes = $_REQUEST['allocatedTimeValue'];
                    break;
                case 'hours':
                    $minutes = $_REQUEST['allocatedTimeValue'] * 60;
                    break;
                case 'days':
                    $buHeader = new BUHeader($this);
                    /** @var $dsHeader DataSet */
                    $buHeader->getHeader($dsHeader);
                    $minutesInADay = $dsHeader->getValue(DBEHeader::ImplementationTeamMinutesInADay);

                    $minutes = $minutesInADay * $_REQUEST['allocatedTimeValue'];
            }

            $this->buActivity->allocateAdditionalTime(
                $_REQUEST['problemID'],
                $_REQUEST['teamLevel'],
                $minutes,
                $_REQUEST['comments']
            );

            $this->buActivity->logOperationalActivity(
                $_REQUEST['problemID'],
                '<p>Additional time allocated: ' . $minutes . ' minutes</p><p>' . $_REQUEST['comments'] . '</p>'
            );

            $nextURL =
                Controller::buildLink(
                    'CurrentActivityReport.php',
                    array()
                );

            header('Location: ' . $nextURL);
            exit;
        }// end IF POST
        else {
            if ($dbeFirstActivity->getValue('queueNo') == 1) {
                $teamLevel = 1;
            } elseif ($dbeFirstActivity->getValue('queueNo') == 2) {
                $teamLevel = 2;
            } else {
                $teamLevel = 3;           // implementations

            }
        }

        if ($teamLevel == 1) {
            $teamLevel1Selected = CT_SELECTED;
        } elseif ($teamLevel == 2) {
            $teamLevel2Selected = CT_SELECTED;
        } else {
            $teamLevel3Selected = CT_SELECTED;
        }

        $urlProblemHistoryPopup =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'    => 'problemHistoryPopup',
                    'problemID' => $_REQUEST['problemID'],
                    'htmlFmt'   => CT_HTML_FMT_POPUP
                )
            );

        $submitURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'allocateAdditionalTime'
                )
            );

        $this->template->set_var(
            array(
                'teamLevel1Selected'     => $teamLevel1Selected,
                'teamLevel2Selected'     => $teamLevel2Selected,
                'teamLevel3Selected'     => $teamLevel3Selected,
                'problemID'              => $_REQUEST['problemID'],
                'customerID'             => $dbeFirstActivity->getValue('customerID'),
                'customerName'           => $dbeFirstActivity->getValue('customerName'),
                'submitURL'              => $submitURL,
                'urlProblemHistoryPopup' => $urlProblemHistoryPopup
            )
        );

        $this->allocatedMinutesDropdown(
            $_REQUEST['allocatedMinutes'],
            'ServiceRequestAllocateAdditionalTime',
            'allocatedMinutesBlock'
        );

        $this->template->parse(
            'CONTENTS',
            'ServiceRequestAllocateAdditionalTime',
            true
        );

        $this->parsePage();

    }

    function allocatedMinutesDropdown(
        $selectedID,
        $template,
        $block
    )
    {
        $this->template->set_block(
            $template,
            $block,
            'allocatedMinutes'
        );

        foreach ($this->buActivity->allocatedMinutesArray as $key => $value) {

            $selected = ($selectedID == $key) ? CT_SELECTED : '';

            $this->template->set_var(
                array(
                    'selected'           => $selected,
                    'minutes'            => $key,
                    'minutesDescription' => $value
                )
            );
            $this->template->parse(
                'allocatedMinutes',
                $block,
                true
            );
        }
    }

    /**
     * Request more time for SR
     * Called via AJAX request from ActivityEdit
     */
    function requestAdditionalTime()
    {
        if (!$_REQUEST['problemID']) {

            echo 'problemID not passed';
        }

        $this->buActivity->requestAdditionalTime(
            $_REQUEST['problemID'],
            $_REQUEST['reason'],
            $_REQUEST['callActivityID']
        );
    }

    function salesRequestReview()
    {
        $this->setMethodName('salesRequestReview');

        $callActivityID = $_REQUEST['callActivityID'];

        $this->buActivity->getActivityByID(
            $callActivityID,
            $dsCallActivity
        );

        if ($dsCallActivity->getValue(DBECallActivity::salesRequestStatus) !== 'O') {

            $this->template->setVar(
                'CONTENTS',
                'This Sales Request has already been processed',
                true
            );
            $this->parsePage();
            exit;
        }

        $problemID = $dsCallActivity->getValue('problemID');

        $dbeFirstActivity = $this->buActivity->getFirstActivityInProblem($problemID);

        $this->setTemplateFiles(
            array(
                'ServiceSalesRequestReview' => 'ServiceSalesRequestReview.inc'
            )
        );

        $this->setPageTitle("Review Sales Request");

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            switch ($_REQUEST['Submit']) {

                case 'Approve':
                    $option = 'A';

                    break;

                case 'Deny':
                    $option = 'D';
                    break;
            }

            $this->buActivity->salesRequestProcess(
                $callActivityID,
                $this->userID,
                $option,
                $_REQUEST['comments']
            );

            $nextURL =
                Controller::buildLink(
                    'SalesRequestDashboard.php',
                    array()
                );
            header('Location: ' . $nextURL);
            exit;
        }

        $urlProblemHistoryPopup =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'    => 'problemHistoryPopup',
                    'problemID' => $problemID,
                    'htmlFmt'   => CT_HTML_FMT_POPUP
                )
            );


        $submitURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTACTIVITY_ACT_CHANGE_REQUEST_REVIEW,
                )
            );

        $this->template->set_var(
            array(
                'callActivityID' => $callActivityID,

                'problemID' => $problemID,

                'customerID' => $dbeFirstActivity->getValue('customerID'),

                'customerName'           => $dbeFirstActivity->getValue('customerName'),
                'requestDetails'         => $dsCallActivity->getValue('reason'),
                'userName'               => $dsCallActivity->getValue('userName'),
                'submitUrl'              => $submitURL,
                'urlProblemHistoryPopup' => $urlProblemHistoryPopup
            )
        );

        $this->template->parse(
            'CONTENTS',
            'ServiceSalesRequestReview',
            true
        );

        $this->parsePage();
    }

    function changeRequestReview()
    {

        $this->setMethodName('changeRequestReview');

        $callActivityID = $_REQUEST['callActivityID'];

        $this->buActivity->getActivityByID(
            $callActivityID,
            $dsCallActivity
        );

        $problemID = $dsCallActivity->getValue('problemID');

        $dbeFirstActivity = $this->buActivity->getFirstActivityInProblem($problemID);

        $this->setTemplateFiles(
            array(
                'ServiceChangeRequestReview' => 'ServiceChangeRequestReview.inc'
            )
        );

        $this->setPageTitle("Review Change Request");

        if (isset($_REQUEST['fromEmail'])) {
            $url =
                Controller::buildLink(
                    'Activity.php',
                    [
                        "action"         => CTACTIVITY_ACT_CHANGE_REQUEST_REVIEW,
                        "callActivityID" => $_REQUEST['callActivityID']
                    ]
                );
            header('Location: ' . $url);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_REQUEST['fromEmail'])) {

            switch ($_REQUEST['Submit']) {

                case 'Approve':
                    $option = 'A';
                    break;

                case 'Deny':
                    $option = 'D';
                    break;

                case 'Further Details Required':
                default:
                    $option = 'I';
                    break;
            }

            $this->buActivity->changeRequestProcess(
                $callActivityID,
                $this->userID,
                $option,
                $_REQUEST['comments']
            );

            $nextURL =
                Controller::buildLink(
                    'CurrentActivityReport.php',
                    array()
                );

            header('Location: ' . $nextURL);
            exit;
        }

        $urlProblemHistoryPopup =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'    => 'problemHistoryPopup',
                    'problemID' => $problemID,
                    'htmlFmt'   => CT_HTML_FMT_POPUP
                )
            );


        $submitURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTACTIVITY_ACT_CHANGE_REQUEST_REVIEW,
                )
            );

        $this->template->set_var(
            array(
                'callActivityID' => $callActivityID,

                'problemID' => $problemID,

                'customerID' => $dbeFirstActivity->getValue('customerID'),

                'customerName'           => $dbeFirstActivity->getValue('customerName'),
                'requestDetails'         => $dsCallActivity->getValue('reason'),
                'userName'               => $dsCallActivity->getValue('userName'),
                'submitUrl'              => $submitURL,
                'urlProblemHistoryPopup' => $urlProblemHistoryPopup
            )
        );

        $this->template->parse(
            'CONTENTS',
            'ServiceChangeRequestReview',
            true
        );

        $this->parsePage();

    }

    function timeRequestReview()
    {

        $this->setMethodName('timeRequestReview');

        $callActivityID = $_REQUEST['callActivityID'];

        $this->buActivity->getActivityByID(
            $callActivityID,
            $dsCallActivity
        );

        $problemID = $dsCallActivity->getValue('problemID');

        $dbeFirstActivity = $this->buActivity->getFirstActivityInProblem($problemID);
        $dbeProblem = new DBEProblem($this);
        $dbeProblem->getRow($problemID);
        $this->setTemplateFiles(
            array(
                'ServiceTimeRequestReview' => 'ServiceTimeRequestReview.inc'
            )
        );

        $this->setPageTitle("Time Request");
        $requestorID = $dsCallActivity->getValue(DBECallActivity::userID);
        $this->dbeUser->getRow($requestorID);
        $teamID = $this->dbeUser->getValue(DBEUser::teamID);
        $teamName = '';
        $usedMinutes = 0;
        $assignedMinutes = 0;
        switch ($teamID) {
            case 1:
                $usedMinutes = $this->buActivity->getHDTeamUsedTime($problemID);
                $assignedMinutes = $dbeProblem->getValue(DBEProblem::hdLimitMinutes);
                $teamName = 'Help Desk';
                break;
            case 2:
                $usedMinutes = $this->buActivity->getESTeamUsedTime($problemID);
                $assignedMinutes = $dbeProblem->getValue(DBEProblem::esLimitMinutes);
                $teamName = 'Escalation';
                break;
            case 4:
                $usedMinutes = $this->buActivity->getIMTeamUsedTime($problemID);
                $assignedMinutes = $dbeProblem->getValue(DBEProblem::imLimitMinutes);
                $teamName = 'Implementation';
        }

        $leftOnBudget = $assignedMinutes - $usedMinutes;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            switch ($_REQUEST['Submit']) {

                case 'Approve':
                    $option = 'A';
                    break;

                case 'Deny':
                    $option = 'D';
                    break;

                case 'Delete':
                default:
                    $option = 'DEL';
                    break;
            }

            $minutes = 0;

            switch ($_REQUEST['allocatedTimeAmount']) {
                case 'minutes':
                    $minutes = $_REQUEST['allocatedTimeValue'];
                    break;
                case 'hours':
                    $minutes = $_REQUEST['allocatedTimeValue'] * 60;
                    break;
                case 'days':
                    $buHeader = new BUHeader($this);
                    /** @var $dsHeader DataSet */
                    $buHeader->getHeader($dsHeader);
                    $minutesInADay = $dsHeader->getValue(DBEHeader::ImplementationTeamMinutesInADay);

                    $minutes = $minutesInADay * $_REQUEST['allocatedTimeValue'];
            }

            $this->buActivity->timeRequestProcess(
                $callActivityID,
                $this->userID,
                $option,
                $_REQUEST['comments'],
                $minutes
            );

            $nextURL =
                Controller::buildLink(
                    'TimeRequestDashboard.php',
                    array()
                );

            header('Location: ' . $nextURL);
            exit;
        }

        $urlProblemHistoryPopup =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'    => 'problemHistoryPopup',
                    'problemID' => $problemID,
                    'htmlFmt'   => CT_HTML_FMT_POPUP
                )
            );


        $submitURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTACTIVITY_ACT_CHANGE_REQUEST_REVIEW,
                )
            );

        $this->template->set_var(
            array(
                'callActivityID' => $callActivityID,

                'problemID' => $problemID,

                'customerID' => $dbeFirstActivity->getValue('customerID'),

                'customerName'           => $dbeFirstActivity->getValue('customerName'),
                'requestDetails'         => $dsCallActivity->getValue('reason'),
                'userName'               => $dsCallActivity->getValue('userName'),
                'submitUrl'              => $submitURL,
                'urlProblemHistoryPopup' => $urlProblemHistoryPopup,
                'requesterTeamName'      => $teamName,
                'notes'                  => $dsCallActivity->getValue(DBEJCallActivity::reason),
                'requestedDateTime'      => $dsCallActivity->getValue(
                        DBEJCallActivity::date
                    ) . ' ' . $dsCallActivity->getValue(DBEJCallActivity::startTime),
                'chargeableHours'        => $dbeProblem->getValue(DBEJProblem::chargeableActivityDurationHours),
                'timeSpentSoFar'         => $usedMinutes,
                'timeLeftOnBudget'       => $leftOnBudget,
                'requesterTeam'          => $teamName
            )
        );

        $this->template->parse(
            'CONTENTS',
            'ServiceTimeRequestReview',
            true
        );

        $this->parsePage();

    }

    function contractListPopup()
    {
        $this->setTemplateFiles(
            array(
                'ContractListPopup' => 'ContractListPopup.inc'
            )
        );

        $this->displayContracts(
            $_REQUEST['customerID'],
            'ContractListPopup'
        );

        $this->template->parse(
            'CONTENTS',
            'ContractListPopup',
            true
        );
        $this->parsePage();
    }

    function displayContracts(
        $customerID,
        $templateName,
        $blockName = 'contractBlock'
    )
    {
        // Display list of contracts that have linked customer items
        $includeExpired = false;
        $onlyWithLinkedItems = false;

        $buCustomerItem = new BUCustomerItem($this);

        $buCustomerItem->getContractsByCustomerID(
            $customerID,
            $dsContract,
            $includeExpired,
            $onlyWithLinkedItems
        );

        $itemTypes = [];

        $items = [];

        while ($dsContract->fetchNext()) {
            $itemTypeID = $dsContract->getValue(DBEJContract::itemTypeID);

            if (!$itemTypes[$itemTypeID]) {
                $dbeItemType = new DBEItemType($this);

                $dbeItemType->getRow($itemTypeID);
                $itemTypes[$itemTypeID] = [
                    DBEItemType::description => $dbeItemType->getValue(DBEItemType::description)
                ];
            }

            $items[] = [
                "itemTypeDescription"         => $itemTypes[$itemTypeID][DBEItemType::description],
                DBEJContract::customerItemID  => $dsContract->getValue(DBEJContract::customerItemID),
                DBEJContract::itemDescription => $dsContract->getValue(DBEJContract::itemDescription),
                DBEJContract::adslPhone       => $dsContract->getValue(DBEJContract::adslPhone),
                DBEJContract::notes           => $dsContract->getValue(DBEJContract::notes),
                DBEJContract::postcode        => $dsContract->getValue(DBEJContract::postcode),
                DBEJContract::serialNo        => $dsContract->getValue(DBEJContract::serialNo)
            ];
        }

        usort(
            $items,
            function ($a,
                      $b
            ) {
                return $a['itemTypeDescription'] <=> $b['itemTypeDescription'];
            }
        );

        $lastItemTypeDescription = false;

        $this->template->set_block(
            $templateName,
            $blockName,
            'contracts'
        );
        foreach ($items as $item) {


            if ($item['itemTypeDescription'] != $lastItemTypeDescription) {
                $itemTypeHeader = '<tr><td colspan="2"><h3>' . $item['itemTypeDescription'] . '</h3></td></tr>';
            } else {
                $itemTypeHeader = '';
            }
            $lastItemTypeDescription = $item['itemTypeDescription'];
            $this->template->set_var(
                array(
                    'itemTypeHeader' => $itemTypeHeader
                )
            );

            $this->template->set_var(
                array(
                    'contractTitle' => 'Contracts'
                )
            );

            $urlRenewalContract =
                Controller::buildLink(
                    'CustomerItem.php',
                    array(
                        'action'         => 'displayRenewalContract',
                        'customerItemID' => $item[DBEJContract::customerItemID]
                    )
                );

            $description = $item[DBEJContract::itemDescription] . ' ' . $item[DBEJContract::adslPhone] . ' ' .
                $item[DBEJContract::notes] . ' ' . $item[DBEJContract::postcode];

            $this->template->set_var(
                array(
                    'contractCustomerItemID'  => $item[DBEJContract::customerItemID],
                    'contractItemDescription' => $description,
                    'serialNo'                => $item[DBEJContract::serialNo],
                    'urlRenewalContract'      => $urlRenewalContract
                )
            );
            $this->template->parse(
                'contracts',
                $blockName,
                true
            );


        }

    }

    /**
     * Form to create a new manager comment
     *
     */
    function managerCommentPopup()
    {
        $this->setTemplateFiles(
            'ManagerCommentPopup',
            'ManagerCommentPopup.inc'
        );

        $this->pageTitle = 'Manager Comment';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (!$_REQUEST['problemID']) {
                $this->raiseError('No problemID Passed');
            }

            $record =
                $this->buActivity->updateManagerComment(
                    $_REQUEST['problemID'],
                    $_REQUEST['details']
                );

            echo '<script language="javascript">window.close()</script>;';

        } else {
            if ($_REQUEST ['problemID']) {
                $_REQUEST['details'] = $this->buActivity->getManagerComment($_REQUEST ['problemID']);

            }
        }


        $urlSubmit =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'managerCommentPopup'
                )
            );

        $this->template->set_var(
            array(
                'problemID' => $_REQUEST['problemID'],
                'details'   => $_REQUEST['details'],
                'urlSubmit' => $urlSubmit
            )
        );
        $this->template->parse(
            'CONTENTS',
            'ManagerCommentPopup',
            true
        );
        $this->parsePage();

    }

    function messageToSales()
    {

        $this->setMethodName('messageToSales');

        $message = $_REQUEST['message'];
        $callActivityID = $_REQUEST['callActivityID'];

        $this->buActivity->sendEmailToSales(
            $callActivityID,
            $message
        );
        return ["status" => "ok"];
    }

    function sendSalesRequest()
    {
        $this->setMethodName('sendSalesRequest');

        $message = $_REQUEST['message'];
        $problemID = $_REQUEST['problemID'];
        $type = $_REQUEST['type'];

        try {

            $this->buActivity->sendSalesRequest(
                $problemID,
                $message,
                $type
            );
        } catch (\Exception $exception) {
            return ["status" => "error", "message" => $exception->getMessage()];
        }
        return ["status" => "ok"];
    }

    function toggleDoNextFlag()
    {
        if (!$_REQUEST['problemID']) {

            echo 'problemID not passed';

        }

        $this->buActivity->toggleDoNextFlag($_REQUEST['problemID']);

    }

    function toggleCriticalFlag()
    {
        if (!$_REQUEST['callActivityID']) {

            echo 'callActivityID not passed';

        }
        $this->buActivity->getActivityByID(
            $_REQUEST['callActivityID'],
            $dsActivity
        );

        $this->buActivity->toggleCriticalFlag($dsActivity->getValue('problemID'));

        $this->redirectToDisplay($_REQUEST['callActivityID']);
    }

    /**
     * called by Ajax on ActivityEdit.inc.html to get server time
     *
     */
    function getServerTime()
    {
        echo date('H') . ':' . date('i');
    }

    function updateHistoricUserTimeLogs(DateTime $startDate = null)
    {
        $this->buActivity->updateAllHistoricUserLoggedHours($startDate);
        echo "Done";
    }

    function secsToText($time)
    {
        return str_pad(
                (int)floor($time / 3600),
                2,
                0,
                STR_PAD_LEFT
            ) . ':' . str_pad(
                (int)floor($time / 60) % 60,
                2,
                0,
                STR_PAD_LEFT
            );
    }

    function parseWarrantySelector($warrantyID)
    {
        // Manufacturer selector
        $dbeWarranty = new DBEWarranty($this);
        $dbeWarranty->getRows();
        $this->template->set_block(
            'CallDisplay',
            'warrantyBlock',
            'warranties'
        );
        while ($dbeWarranty->fetchNext()) {
            $this->template->set_var(
                array(
                    'warrantyDescription' => $dbeWarranty->getValue('description'),
                    'warrantyID'          => $dbeWarranty->getValue('warrantyID'),
                    'warrantySelected'    => ($warrantyID == $dbeWarranty->getValue('warrantyID')) ? CT_SELECTED : ''
                )
            );
            $this->template->parse(
                'warranties',
                'warrantyBlock',
                true
            );
        } // while ($dbeWarranty->fetchNext()
    }

    function awaitingCustomerDropdown(
        $awaitingCustomerResponseFlag,
        $template = 'ActivityCreate6',
        $block = 'awaitingCustomerBlock'
    )
    {
        $this->template->set_block(
            $template,
            $block,
            'awaitingCustomer'
        );

        foreach ($this->buActivity->awaitingCustomerArray as $key => $value) {

            $awaitingCustomerResponseFlagSelected = ($awaitingCustomerResponseFlag == $key) ? CT_SELECTED : '';

            $this->template->set_var(
                array(
                    'awaitingCustomerResponseFlagSelected' => $awaitingCustomerResponseFlagSelected,
                    'awaitingCustomerResponseFlag'         => $key,
                    'awaitingCustomerDesc'                 => $value
                )
            );
            $this->template->parse(
                'awaitingCustomer',
                $block,
                true
            );
        }
    }

    function serverGuardDropdown(
        $selectedID,
        $template = 'ActivityCreate9',
        $block = 'serverGuardBlock'
    )
    {

        $this->template->set_block(
            $template,
            $block,
            'serverGuards'
        );

        foreach ($this->serverGuardArray as $key => $value) {
            $serverGuardSelected = ($selectedID == $key) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    'serverGuardSelected'    => $serverGuardSelected,
                    'serverGuardValue'       => $key,
                    'serverGuardDescription' => $value
                )
            );
            $this->template->parse(
                'serverGuards',
                $block,
                true
            );
        }

    }

    function promptCreateTravel()
    {

        if (!$_REQUEST['callActivityID']) {
            $this->raiseError('callActivityID not passed');
        }

        $urlCreateTravel =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'callActivityID' => $_REQUEST['callActivityID'],
                    'action'         => 'createTravel',
                    'nextStatus'     => $_REQUEST['nextStatus']
                )
            );

        if ($_REQUEST['nextStatus'] == 'Fixed') {

            $urlSkipTravel =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'callActivityID' => $_REQUEST['callActivityID'],
                        'action'         => 'gatherFixedInformation'
                    )
                );
        } else {
            $urlSkipTravel =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'callActivityID' => $_REQUEST['callActivityID'],
                        'action'         => 'displayActivity'
                    )
                );
        }

        $this->template->set_var(
            array(
                'urlCreateTravel' => $urlCreateTravel,
                'urlSkipTravel'   => $urlSkipTravel
            )
        );

        $this->setTemplateFiles(
            array('ActivityCreateTravel' => 'ActivityCreateTravel.inc')
        );

        $this->template->parse(
            'CONTENTS',
            'ActivityCreateTravel',
            true
        );
        $this->parsePage();

    }// end contactDropdown

    function createHTMLOptions($dsSet)
    {
        $string = '';

        while ($dsSet->fetchNext()) {


            $string .= '<option>' . $dsSet->getValue('description') . '</option>';
        }
        return $string;
    }

    function finaliseProblem($callActivityID = false)
    {

        if ($callActivityID) {
            $_REQUEST['callActivityID'] = $callActivityID;
        }
        if (!$_REQUEST['callActivityID']) {
            $this->raiseError('callActivityID not passed');
        }

        $this->buActivity->finaliseProblem($_REQUEST['callActivityID']);

        $urlNext =
            Controller::buildLink(
                'CurrentActivityReport.php',
                array()
            );

        header('Location: ' . $urlNext);
        exit;

    }

    function redirectToEdit($callActivityID)
    {
        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'callActivityID' => $callActivityID,
                    'action'         => CTACTIVITY_ACT_EDIT_ACTIVITY
                )
            );
        header('Location: ' . $urlNext);
        exit;
    }

    private
    function redirectToGather($callActivityID
    )
    {
        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'callActivityID' => $callActivityID,
                    'action'         => 'gatherFixedInformation'
                )
            );
        header('Location: ' . $urlNext);
        exit;
    }

    private
    function toggleMonitoringFlag()
    {
        if (!$_REQUEST['callActivityID']) {

            echo 'callActivityID not passed';

        }
        $this->buActivity->getActivityByID(
            $_REQUEST['callActivityID'],
            $dsActivity
        );

        $this->buActivity->toggleMonitoringFlag($dsActivity->getValue('problemID'));

        $this->redirectToDisplay($_REQUEST['callActivityID']);

    }

    private
    function checkMonitoring($problemID
    )
    {
        return $this->buActivity->checkMonitoringFlag($problemID);
    }

    private
    function unhideSR()
    {

        if (!$_REQUEST['callActivityID']) {

            echo 'callActivityID not passed';

        }
        $dsActivity = new DataSet($this);
        $this->buActivity->getActivityByID(
            $_REQUEST['callActivityID'],
            $dsActivity
        );

        $firstName = $this->dbeUser->getValue(DBEUser::firstName);
        $lastName = $this->dbeUser->getValue(DBEUser::lastName);

        $this->buActivity->unhideSR($dsActivity->getValue(DBEJCallActivity::problemID));

        $this->buActivity->logOperationalActivity(
            $dsActivity->getValue('problemID'),
            $firstName . ' ' . $lastName . " converted this from a hidden SR to a visible SR."
        );

        $this->redirectToDisplay($_REQUEST['callActivityID']);
    }

    private function onlyMainAndSupervisorsDropdown($templateName,
                                                    $customerID,
                                                    $contactID
    )
    {
        $dbeContact = new DBEContact($this);
        $dbeSite = new DBESite($this);

        $dbeContact->getRowsByCustomerID(
            $customerID,
            false,
            true
        );

        $this->template->set_block(
            $templateName,
            'contactOnlyMainAndSupervisorsBlock',
            'contactsOnlyMainAndSupervisor'
        );

        $lastSiteNo = '';
        while ($dbeContact->fetchNext()) {
            $contactSelected = ($contactID == $dbeContact->getValue("contactID")) ? CT_SELECTED : '';

            if ($dbeContact->getValue(DBEContact::supportLevel) == DBEContact::supportLevelMain) {
                $startMainContactStyle = '*';
                $endMainContactStyle = '*';
            } elseif ($dbeContact->getValue(DBEContact::supportLevel) == DBEContact::supportLevelSupervisor) {
                $startMainContactStyle = '- Supervisor';
                $endMainContactStyle = '- Supervisor';
            } else {
                continue;
            }

            $dbeSite->setValue(
                DBESite::customerID,
                $dbeContact->getValue("customerID")
            );
            $dbeSite->setValue(
                DBESite::siteNo,
                $dbeContact->getValue("siteNo")
            );
            $dbeSite->getRow();

            $name = $dbeContact->getValue("firstName") . ' ' . $dbeContact->getValue("lastName");

            if ($dbeContact->getValue("position")) {
                $name .= ' (' . $dbeContact->getValue("position") . ')';
            }

            /*
        Option group site
        */

            if ($dbeContact->getValue('siteNo') != $lastSiteNo) {

                if ($dbeContact->getValue('siteNo') != '') {

                    if ($lastSiteNo !== '') {
                        $optGroupClose = '</optgroup>';
                    } else {
                        $optGroupClose = '';

                    }
                }

                $optGroupOpen = '<optgroup label="' . $dbeSite->getValue(DBESite::add1) . ' ' . $dbeSite->getValue(
                        DBESite::town
                    ) . ' ' . $dbeSite->getValue(DBESite::postcode) . '">';
                $optGroupClose = '';
            } else {
                $optGroupOpen = '';
                $optGroupClose = '';
            }

            $lastSiteNo = $dbeContact->getValue('siteNo');

            $this->template->set_var(
                array(
                    'contactSelected'       => $contactSelected,
                    'contactID'             => $dbeContact->getValue("contactID"),
                    'contactName'           => $name,
                    'startMainContactStyle' => $startMainContactStyle,
                    'endMainContactStyle'   => $endMainContactStyle,
                    'optGroupOpen'          => $optGroupOpen,
                    'optGroupClose'         => $optGroupClose
                )
            );
            $this->template->parse(
                'contactsOnlyMainAndSupervisor',
                'contactOnlyMainAndSupervisorsBlock',
                true
            );
        }
    }

    private function getContactNotes()
    {
        $contactId = @$_REQUEST['contactID'];

        if (!$contactId) {
            throw new Exception('Contact ID is missing');
        }

        $dbeContact = new DBEContact($this);
        $dbeContact->getRow($contactId);

        return $dbeContact->getValue(DBEContact::notes);
    }

    private function getServiceRequestForContactLink($contactID)
    {
        $contactHistory =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'    => 'displayServiceRequestForContactPopup',
                    'contactID' => $contactID,
                    'htmlFmt'   => CT_HTML_FMT_POPUP
                )
            );
        return '| <a href="#" title="Contact SR History" onclick="window.open(\'' . $contactHistory . '\', \'reason\', \'scrollbars=yes,resizable=yes,height=400,width=1225,copyhistory=no, menubar=0\')">Contact SR History</a>';
    }

    private function getAuthorisingContacts()
    {

        $customerID = @$_REQUEST['customerID'];

        if (!$customerID) {
            throw new Exception('Customer ID is missing');
        }

        $buContact = new BUContact($this);
        $dsResults = new DataSet($this);
        $buContact->getAuthorisingContacts(
            $dsResults,
            $customerID
        );


        $contacts = [];

        while ($dsResults->fetchNext()) {
            $contacts[] = [
                'id'           => $dsResults->getValue(DBEContact::contactID),
                'supportLevel' => $dsResults->getValue(DBEContact::supportLevel),
                'firstName'    => $dsResults->getValue(DBEContact::firstName),
                'lastName'     => $dsResults->getValue(DBEContact::lastName)
            ];
        }
        return $contacts;
    }

    private function getContractsForCustomer($customerID)
    {
        $buCustomerItem = new BUCustomerItem($this);
        $dsContract = new DataSet($this);
        $buCustomerItem->getContractsByCustomerID(
            $customerID,
            $dsContract
        );

        $data = [];

        while ($dsContract->fetchNext()) {

            if (!isset($data[$dsContract->getValue('renewalType')])) {
                $data[$dsContract->getValue('renewalType')] = [];
            }
            $data[$dsContract->getValue('renewalType')][] = [
                "description" => $dsContract->getValue("itemDescription") . ' ' . $dsContract->getValue(
                        'adslPhone'
                    ) . ' ' . $dsContract->getValue('notes') . ' ' . $dsContract->getValue('postcode'),
                "id"          => $dsContract->getValue(DBEJContract::customerItemID)

            ];

        }
        return $data;
    }
}

?>
