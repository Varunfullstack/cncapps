<?php /** @noinspection HtmlDeprecatedAttribute */
/**
 * Activity controller class
 * CNC Ltd
 *
 * a change
 * @access public
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

global $cfg;
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
require_once($cfg['path_dbe'] . '/DBEProblemRaiseType.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUExpenseType.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerItem.inc.php');
require_once($cfg['path_bu'] . '/BUSite.inc.php');
require_once($cfg['path_bu'] . '/BUContact.inc.php');
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
    public $statusArrayCustomer =
        array(
            "A" => "Active",
            "E" => "Ended",
            ""  => "All"
        );
    public $serverGuardArray =
        array(
            ""  => "Please select",
            "Y" => "ServerGuard Related",
            "N" => "Not ServerGuard Related"
        );
    public $arrContractType =
        array(
            "TM"  => "T & M",
            "GSC" => "Pre-pay",
            "O"   => "Other Contract",
            ""    => "All"
        );
    /**
     * @var DSForm
     */
    public $dsCallActivity;
    /**
     *
     * @var DSForm
     */
    private $dsSearchForm;
    /** @var DataSet|DBEJCallActivity */
    private $dsSearchResults;
    /** @var DataSet */
    private $sessionKey;
    private $contactID;
    private $userWarned = false;
    /** @var BUActivity */
    private $buActivity;
    private $statusArray =
        array(
            ""                    => "All",
            "INITIAL"             => "Awaiting Initial Response",
            "CUSTOMER"            => "Awaiting Customer",
            "CNC"                 => "Awaiting CNC",
            "FIXED"               => "Fixed",
            "COMPLETED"           => "Completed",
            "NOT_FIXED"           => "Not Fixed",
            "CHECKED_T_AND_M"     => "Checked T&M Due Completion",
            "CHECKED_NON_T_AND_M" => "Checked Non-T&M Due Completion",
            "UNCHECKED"           => "Unchecked",
            "FIXED_OR_COMPLETED"  => "Fixed Or Completed"
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
            DBECallActivity::siteNo,
            DA_ALLOW_NULL
        );
        $this->dsCallActivity->setNull(
            DBECallActivity::contactID,
            DA_ALLOW_NULL
        );
        $this->dsCallActivity->setNull(
            DBECallActivity::callActTypeID,
            DA_ALLOW_NULL
        );

        $roles = [
            SALES_PERMISSION,
            ACCOUNTS_PERMISSION,
            TECHNICAL_PERMISSION,
            SUPERVISOR_PERMISSION,
            REPORTS_PERMISSION,
            MAINTENANCE_PERMISSION,
            RENEWALS_PERMISSION,
        ];

        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        if ($this->getParam('contactID')) {
            $this->contactID = $this->getParam('contactID');
            $this->sessionKey = 'activity_create' . $this->contactID;
            $this->updateSession('contactID', $this->contactID);
            $sess[$this->sessionKey]['contactID'] = $this->contactID;
        }
        switch ($this->getAction()) {

            case CTCNC_ACT_SEARCH:
                /* if user has clicked Generate Sales Orders or Skip Sales Orders */
                if ($this->getParam('Search') == 'Generate Sales Orders') {
                    return $this->createSalesOrder();

                }
                if ($this->getParam('Search') == 'Complete SRs') {
                    return $this->completeSRs();
                }
                if ($this->getParam('Search') == 'Skip Sales Orders') {
                    $this->assignContracts();
                    return $this->skipSalesOrder();
                }
                return $this->search();

                break;
            case 'unlinkSalesOrder':
                $this->unlinkSalesOrder();
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
                $roles = [
                    "technical",
                ];

                if (!self::hasPermissions($roles)) {
                    Header("Location: /NotAllowed.php");
                    exit;
                }
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
                $this->checkPermissions(SUPERVISOR_PERMISSION);
                $this->checkActivity();
                break;
            case 'createFollowOnActivity':
                $this->createFollowOnActivity();
                break;
            case 'createRequestFromCustomerRequest':
                $this->checkPermissions(TECHNICAL_PERMISSION);
                $this->createRequestFromCustomerRequest();
                break;
            case 'updateRequestFromCustomerRequest':
                $this->checkPermissions(TECHNICAL_PERMISSION);
                $this->updateRequestFromCustomerRequest();
                break;
            case 'displayServiceRequest':
                $this->displayServiceRequest();
                break;
            case 'setProblemFixed':
                $this->setProblemFixed();
                break;
            case 'setProblemComplete':
                $this->checkPermissions(SUPERVISOR_PERMISSION);
                $this->setProblemComplete();
                break;
            case 'linkProblems':
                $this->linkProblems();
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
                $this->checkPermissions(SUPERVISOR_PERMISSION);
                $this->toggleDoNextFlag();
                break;
            case 'toggleCriticalFlag':
                $this->checkPermissions(SUPERVISOR_PERMISSION);
                $this->toggleCriticalFlag();
                break;
            case 'getServerTime':
                $this->getServerTime();
                break;

            case 'updateHistoricUserTimeLogs':
                $startDateData = @$this->getParam('startDate');

                $startDate = new DateTime($startDateData);

                $this->updateHistoricUserTimeLogs($startDate);
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
                echo json_encode($this->getContractsForCustomer($this->getParam('customerID')));
                break;
            case 'sendSalesRequest':
                echo json_encode($this->sendSalesRequest());
                break;
            case 'sendChangeRequest':
                echo json_encode($this->sendChangeRequest());
                break;
            case 'salesRequestReview':
                $this->salesRequestReview();
                break;
            case 'contactNotes':
                $buCustomer = new BUCustomer($this);
                $subject = null;
                if ($this->getParam("problemID")) {
                    $subject = "Service Request {$this->getParam("problemID")}";
                }
                $phoneHtml = $buCustomer->getContactPhoneForHtml(@$this->getParam('contactID'), $subject);
                echo json_encode(['data' => $this->getContactNotes(), 'phone' => $phoneHtml]);
                break;
            case 'authorisingContacts':
                echo json_encode(['data' => $this->getAuthorisingContacts()]);
                break;
            case 'checkPrepay':
                echo json_encode(["hiddenCharges" => $this->hasHiddenCharges($this->getParam('problemID'))]);
                break;
            case CTCNC_ACT_DISPLAY_SEARCH_FORM:
            default:
                $roles = [
                    TECHNICAL_PERMISSION,
                ];

                if (!self::hasPermissions($roles)) {
                    Header("Location: /NotAllowed.php");
                    exit;
                }
                $this->displaySearchForm();
                break;

        }
    }

    private function updateSession(string $string, $value)
    {
        $sessionValue = $this->getSessionParam($this->sessionKey);
        if (!$sessionValue) {
            $sessionValue = [];
        }

        $sessionValue[$string] = $value;
        $this->setSessionParam($this->sessionKey, $sessionValue);
    }

    /**
     * Create sales orders from checked activities
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function createSalesOrder()
    {
        $this->setMethodName('createSalesOrder');
        if ($this->getParam('callActivityID')) {
            $this->buActivity->createSalesOrdersFromActivities($this->getParam('callActivityID'));
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

    /**
     * @throws Exception
     */
    function completeSRs()
    {
        $this->setMethodName('completeSRs');
        if ($this->getParam('callActivityID')) {
            $this->buActivity->completeSRs($this->getParam('callActivityID'));

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
    }

    private function assignContracts()
    {
        $activities = $this->getParam('callActivityID');
        $problems = $this->getParam('problem');

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
    } // end function salesRequest

    /**
     * Skip creation of sales order but authorise checked activities of given call
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function skipSalesOrder()
    {
        $this->setMethodName('skipSalesOrder');
        if ($this->getParam('callActivityID')) {
            $this->buActivity->skipSalesOrdersForActivities($this->getParam('callActivityID'));
        }
        $this->search();
    } // end function displaySearchForm

    /**
     * @throws Exception
     */
    function search()
    {
        $this->setMethodName('search');
        $this->buActivity->initialiseSearchForm($this->dsSearchForm);
        /* Special Case */
        if ($this->getParam('linkedSalesOrderID')) {
            $this->dsSearchForm->setUpdateModeUpdate();
            $this->dsSearchForm->setValue(
                BUActivity::searchFormLinkedSalesOrderID,
                $this->getParam('linkedSalesOrderID')
            );
            $this->dsSearchForm->setValue(
                BUActivity::searchFormCallActTypeID,
                CONFIG_INITIAL_ACTIVITY_TYPE_ID
            );
            $this->dsSearchForm->post();
        } elseif ($this->getParam('activity')) {
            if (!$this->dsSearchForm->populateFromArray($this->getParam('activity'))) {
                $this->setFormErrorOn();
                $this->displaySearchForm(); //redisplay with errors
                exit;
            }
            if ($this->countParamsSet($this->getParam('activity')) < 2 and
                empty($this->dsSearchForm->getValue(BUActivity::searchFormCustomerID)) and
                $this->dsSearchForm->getValue(BUActivity::searchFormContractCustomerItemID) == '99' and
                $this->dsSearchForm->getValue(BUActivity::searchFormStatus) !== 'CHECKED_NON_T_AND_M'
            ) {
                $this->formErrorMessage = 'you have not selected any filtering criteria for your search, this is not allowed';
                $this->setFormErrorOn();
                $this->displaySearchForm();
                exit;
            }
        } else {

            // default (i.e. called from menu link) is last 7 days
            $this->dsSearchForm->setUpdateModeUpdate();
            $this->dsSearchForm->setValue(
                BUActivity::searchFormFromDate,
                date(
                    'Y-m-d',
                    strtotime("-1 week")
                )
            );
            $this->dsSearchForm->post();
        }

        if ($this->getParam('CSV')) {
            $limit = false;                        // no row count limit
        } else {
            $limit = true;
        }

        if ($this->getParam('sortColumn')) {
            if ($this->getSessionParam('sortColumn') == $this->getParam('sortColumn')) {
                if ($this->getSessionParam('sortDirection') == 'ASC') {
                    $this->setSessionParam('sortDirection', 'DESC');
                } else {
                    $this->setSessionParam('sortDirection', 'ASC');
                }
            } else {
                $this->setSessionParam('sortColumn', $this->getParam('sortColumn'));
                $this->setSessionParam('sortDirection', 'ASC');
            }
        }
        $this->buActivity->search(
            $this->dsSearchForm,
            $this->dsSearchResults,
            $this->getSessionParam('sortColumn') ? $_SESSION['sortColumn'] : null,
            $this->getSessionParam('sortDirection') ? $_SESSION['sortDirection'] : null,
            $limit
        );

        if ($this->getParam('CSV')) {
            $this->generateCSV();
        } else {
            $this->displaySearchForm(); // show results
        }
        exit;
    }

    /**
     * Display search form
     * @access private
     * @throws Exception
     */
    function displaySearchForm()
    {
        if (!self::hasPermissions(TECHNICAL_PERMISSION)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $dsSearchForm = &$this->dsSearchForm; // ref to global
        $dsSearchResults = &$this->dsSearchResults; // ref to global
        $this->setMenuId(102);
        $this->setMethodName('displaySearchForm');
        $urlCreateActivity = null;
        $urlCustomerPopup = null;
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
        $customerString = null;

        if ($dsSearchForm->getValue(BUActivity::searchFormCustomerID)) {
            $buCustomer = new BUCustomer($this);
            $dsCustomer = new DataSet($this);
            $buCustomer->getCustomerByID(
                $dsSearchForm->getValue(BUActivity::searchFormCustomerID),
                $dsCustomer
            );
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }
        $this->template->set_var(
            array(
                'formError'                   => $this->formError,
                'customerID'                  => $dsSearchForm->getValue(BUActivity::searchFormCustomerID),
                'customerString'              => $customerString,
                'problemID'                   => Controller::htmlDisplayText(
                    $dsSearchForm->getValue(BUActivity::searchFormProblemID)
                ),
                'problemIDMessage'            => Controller::htmlDisplayText(
                    $dsSearchForm->getMessage(BUActivity::searchFormProblemID)
                ),
                'callActivityID'              => Controller::htmlDisplayText(
                    $dsSearchForm->getValue(BUActivity::searchFormCallActivityID)
                ),
                'callActivityIDMessage'       => Controller::htmlDisplayText(
                    $dsSearchForm->getMessage(BUActivity::searchFormCallActivityID)
                ),
                'serviceRequestSpentTime'     => Controller::htmlDisplayText(
                    $dsSearchForm->getValue(BUActivity::searchFormServiceRequestSpentTime)
                ),
                'individualActivitySpentTime' => Controller::htmlDisplayText(
                    $dsSearchForm->getValue(BUActivity::searchFormIndividualActivitySpentTime)
                ),
                'activityText'                => Controller::htmlDisplayText(
                    $dsSearchForm->getValue(BUActivity::searchFormActivityText)
                ),
                'fromDate'                    => $dsSearchForm->getValue(BUActivity::searchFormFromDate),
                'fromDateMessage'             => $dsSearchForm->getMessage(BUActivity::searchFormFromDate),
                'toDate'                      => $dsSearchForm->getValue(BUActivity::searchFormToDate),
                'toDateMessage'               => $dsSearchForm->getMessage(BUActivity::searchFormToDate),
                'rowsFound'                   => $dsSearchResults->rowCount(),
                'urlCreateActivity'           => $urlCreateActivity,
                'urlCustomerPopup'            => $urlCustomerPopup,
                'fetchContractsURL'           => $fetchContractsURL,
                'managementReviewOnlyChecked' => Controller::htmlChecked(
                    $dsSearchForm->getValue(BUActivity::searchFormManagementReviewOnly)
                ),
                'urlSubmit'                   => $urlSubmit
            )
        );

        // activity status selector
        $this->template->set_block(
            'ActivitySearch',
            'statusBlock',
            'status'
        ); // ss avoids naming conflict!

        $statusArray = &$this->statusArray;
        foreach ($statusArray as $key => $value) {
            $statusSelected = ($dsSearchForm->getValue(BUActivity::searchFormStatus) == $key) ? CT_SELECTED : null;
            $this->template->set_var(
                array(
                    'statusSelected'    => $statusSelected,
                    'statusValue'       => $key,
                    'statusDescription' => $value
                )
            );
            $this->template->parse(
                'status',
                'statusBlock',
                true
            );
        }

        $this->rootCauseDropdown(
            $dsSearchForm->getValue(BUActivity::searchFormRootCauseID),
            'ActivitySearch',
            'rootCauseBlock'
        );

        $this->priorityDropdown(
            $dsSearchForm->getValue(BUActivity::searchFormPriority),
            'ActivitySearch',
            'priorityBlock'
        );

        $this->breachedSlaDropdown($dsSearchForm->getValue(BUActivity::searchFormBreachedSlaOption));
        $this->fixSLADropdown($dsSearchForm->getValue(BUActivity::searchFormFixSLAOption));

        //Contract selection

        $this->contractDropdown(
            $dsSearchForm->getValue(BUActivity::searchFormCustomerID),
            $dsSearchForm->getValue(BUActivity::searchFormContractCustomerItemID),
            'ActivitySearch',
            'contractBlock'
        );


        $this->userDropdown(
            $dsSearchForm->getValue(BUActivity::searchFormUserID),
            'ActivitySearch'
        );

        $dbeCallActType = new DBECallActType($this);
        $dbeCallActType->setValue(
            DBECallActType::activeFlag,
            'Y'
        );
        $dbeCallActType->getRowsByColumn(
            DBECallActType::activeFlag,
            'description'
        );

        // activity type selector
        $this->template->set_block(
            'ActivitySearch',
            'activityTypeBlock',
            'activityTypes'
        );
        while ($dbeCallActType->fetchNext()) {
            $activityTypeSelected = ($dsSearchForm->getValue(
                    BUActivity::searchFormCallActTypeID
                ) == $dbeCallActType->getValue(
                    DBECallActType::callActTypeID
                )) ? CT_SELECTED : null;
            $this->template->set_var(
                array(
                    'activityTypeSelected'    => $activityTypeSelected,
                    'callActTypeID'           => $dbeCallActType->getValue(DBECallActType::callActTypeID),
                    'activityTypeDescription' => $dbeCallActType->getValue(DBECallActType::description),
                    'allowOvertime'           => $dbeCallActType->getValue(
                        DBECallActType::engineerOvertimeFlag
                    ) == 'Y' ? 1 : 0
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
                'show/hide latest activity'
            );
            $customerNameCol = $dsSearchResults->columnExists(DBEJCallActivity::customerName);
            $callActivityIDCol = $dsSearchResults->columnExists(DBEJCallActivity::callActivityID);
            $statusCol = $dsSearchResults->columnExists(DBEJCallActivity::status);
            $reasonCol = $dsSearchResults->columnExists(DBEJCallActivity::reason);
            $dateCol = $dsSearchResults->columnExists(DBEJCallActivity::date);
            $startCol = $dsSearchResults->columnExists(DBEJCallActivity::startTime);
            $endCol = $dsSearchResults->columnExists(DBEJCallActivity::endTime);
            $contractDescriptionCol = $dsSearchResults->columnExists(DBEJCallActivity::contractDescription);
            $problemIDCol = $dsSearchResults->columnExists(DBEJCallActivity::problemID);

            /*
        if we are displaying checked T&M activities then show Generate Sales Order and Skip Sales Order buttons
        */
            $bulkActionButtons = null;
            $checkAllBox = null;

            if ($dsSearchForm->getValue(BUActivity::searchFormStatus) == 'CHECKED_T_AND_M') {
                $bulkActionButtons =
                    '<input name="Search" type="submit" value="Generate Sales Orders" onclick="postToBlank()" />
          <input name="Search" type="submit" value="Skip Sales Orders" />';
                $checkAllBox =
                    '<input type="checkbox" name="checkAllBox" id="checkAllBox" value="0" onClick="checkAll();"/>';

            } elseif ($dsSearchForm->getValue(BUActivity::searchFormStatus) == 'CHECKED_NON_T_AND_M') {
                $bulkActionButtons =
                    '<input name="Search" type="submit" value="Complete SRs" />';
                $checkAllBox =
                    '<input type="checkbox" name="checkAllBox" id="checkAllBox" value="0" onClick="checkAll();"/>';
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

            if ($dsSearchForm->getValue(BUActivity::searchFormStatus) == 'CHECKED_T_AND_M' ||
                $dsSearchForm->getValue(BUActivity::searchFormStatus) == 'CHECKED_NON_T_AND_M') {
                $weirdColumns = '';
                $headerColSpan = 12;
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
            <td align="right" nowrap>' . $dsSearchResults->getValue(DBECallActivitySearch::slaResponseHours) . '</td>
            <td align="right" nowrap>' . $dsSearchResults->getValue(DBECallActivitySearch::respondedHours) . '</td>';

                $contractField = $dsSearchResults->getValue($contractDescriptionCol);

                $checkBox = null;
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


                if (
                    $dsSearchForm->getValue(BUActivity::searchFormStatus) == 'CHECKED_T_AND_M' ||
                    $dsSearchForm->getValue(BUActivity::searchFormStatus) == 'CHECKED_NON_T_AND_M'
                ) {
                    $weirdFields = null;
                    $checkBox =
                        '<input type="checkbox" id="callActivityID" name="callActivityID[' . $callActivityID . ']" value="' . $callActivityID . '" />';

                    $contracts = $this->getContractsForCustomer($dbeProblem->getValue(DBEProblem::customerID));

                    $contractCustomerItemID = $dbeProblem->getValue(DBEProblem::contractCustomerItemID);

                    $contractField = "<select name='problem[" . $problemID . "][contract]' onchange='tickBox()'>";


                    $contractField .= "<option value " . ($contractCustomerItemID ? null : 'selected') . ">T&M</option>";

                    foreach ($contracts as $contractType => $contractItems) {

                        $contractField .= "<optgroup label='" . $contractType . "'>";

                        foreach ($contractItems as $contractItem) {
                            $selected = $contractCustomerItemID == $contractItem['id'];
                            $disabled = $contractItem['disabled'] ? 'disabled' : null;
                            $contractField .= "<option {$disabled} value='" . $contractItem['id'] . "' " . ($selected ? 'selected' : null) . ">" . $contractItem['description'] . " </option>";
                        }
                        $contractField .= "</optgroup>";
                    }

                    $contractField .= "</select>";
                }


                // Reason
                $reason = $dsSearchResults->getValue($reasonCol);

                $this->template->set_var(
                    array(
                        'listCustomerName'          => $dsSearchResults->getValue($customerNameCol),
                        'listContractDescription'   => $contractField,
                        'listCallURL'               => $displayActivityURL,
                        'listCallActivityID'        => $dsSearchResults->getValue($callActivityIDCol),
                        'listProblemID'             => $problemID,
                        'listStatus'                => $dsSearchResults->getValue($statusCol),
                        'listDate'                  => Controller::dateYMDtoDMY($dsSearchResults->getValue($dateCol)),
                        'listPriority'              => $dsSearchResults->getValue(DBECallActivitySearch::priority),
                        'listWorkingHours'          => number_format(
                            $dsSearchResults->getValue(DBECallActivitySearch::workingHours),
                            2
                        ),
                        'listActivityDurationHours' => number_format(
                            $dsSearchResults->getValue(DBECallActivitySearch::activityDurationHours),
                            2
                        ),
                        'listRootCause'             => $dsSearchResults->getValue(DBECallActivitySearch::rootCause),
                        'listFixEngineer'           => $dsSearchResults->getValue(DBECallActivitySearch::fixEngineer),
                        'listActivityCount'         => $dsSearchResults->getValue(DBECallActivitySearch::activityCount),
                        'listOrderLink'             => $salesOrderLink,
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
        $dsRootCause = new DataSet($this);
        $buRootCause->getAll($dsRootCause);

        while ($dsRootCause->fetchNext()) {

            $rootCauseSelected = ($selectedID == $dsRootCause->getValue(
                    DBERootCause::rootCauseID
                )) ? CT_SELECTED : null;

            $this->template->set_var(

                array(
                    'rootCauseSelected'    => $rootCauseSelected,
                    'itemRootCauseID'      => $dsRootCause->getValue(DBERootCause::rootCauseID),
                    'rootCauseDescription' => $dsRootCause->getValue(
                            DBERootCause::description
                        ) . " (" . $dsRootCause->getValue(
                            DBERootCause::longDescription
                        ) . ")",
                    'rootCauseFixedText'   => base64_encode(
                        $dsRootCause->getValue(
                            DBERootCause::fixedExplanation
                        )
                    )
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

            $prioritySelected = ($selectedID == $key) ? CT_SELECTED : null;

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

            $breachedSlaOptionSelected = ($selectedID == $key) ? CT_SELECTED : null;

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

    function fixSLADropdown(
        $selectedID,
        $template = 'ActivitySearch',
        $block = 'searchFormFixSLAOptionBlock'
    )
    {
        $this->template->set_block(
            $template,
            $block,
            'fixSLAOptions'
        );

        foreach ($this->buActivity->breachedSlaOptionArray as $key => $value) {

            $breachedSlaOptionSelected = ($selectedID == $key) ? CT_SELECTED : null;

            $this->template->set_var(
                array(
                    'searchFormFixSLAOptionSelected'    => $breachedSlaOptionSelected,
                    'searchFormFixSLAOptionValue'       => $key,
                    'searchFormFixSLAOptionDescription' => $value
                )
            );
            $this->template->parse(
                'fixSLAOptions',
                $block,
                true
            );
        }
    }

    function contractDropdown(
        $customerID,
        $contractCustomerItemID,
        $templateName = 'ActivityCreate6',
        $blockName = 'contractBlock',
        bool $linkedToSalesOrder = false
    )
    {
        $buCustomerItem = new BUCustomerItem($this);
        $dsContract = new DataSet($this);
        if ($customerID) {
            $buCustomerItem->getContractsByCustomerID(
                $customerID,
                $dsContract,
                null
            );
        }

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
                    'tandMSelected' => CT_SELECTED,
                )
            );
        }

        if ($linkedToSalesOrder) {
            $this->template->set_var(
                [

                    'salesOrderReason' => "- Must be selected because this is linked to a Sales Order"
                ]

            );
        }

        $this->template->set_block(
            $templateName,
            $blockName,
            'contracts'
        );

        $lastRenewalType = null;
        $currentRow = 0;
        while ($dsContract->fetchNext()) {
            $optGroupOpen = null;
            $optGroupClose = null;
            if ($dsContract->getValue(DBEJContract::renewalType) != $lastRenewalType) {
                if ($lastRenewalType) {
                    $optGroupClose = '</optgroup>';
                }
                $optGroupOpen = '<optgroup label="' . $dsContract->getValue(DBEJContract::renewalType) . '">';
            }
            $lastRenewalType = $dsContract->getValue(DBEJContract::renewalType);

            $contractSelected = ($contractCustomerItemID == $dsContract->getValue(
                    DBEJContract::customerItemID
                )) ? CT_SELECTED : null;

            $description = $dsContract->getValue(DBEJContract::itemDescription) . ' ' . $dsContract->getValue(
                    DBEJContract::adslPhone
                ) . ' ' . $dsContract->getValue(DBEJContract::notes) . ' ' . $dsContract->getValue(
                    DBEJContract::postcode
                );
            $this->template->set_var(
                array(
                    'contractSelected'       => $contractSelected,
                    'contractCustomerItemID' => $dsContract->getValue(DBEJContract::customerItemID),
                    'contractDescription'    => $description,
                    'optGroupOpen'           => $optGroupOpen,
                    'optGroupClose'          => $optGroupClose,
                    'optGroupCloseLast'      => $dsContract->rowCount() == $currentRow ? '</optgroup>' : 'null',
                    'prepayContract'         => $dsContract->getValue(DBEJContract::itemTypeID) == 57,
                    'isDisabled'             => !$dsContract->getValue(
                        DBEJContract::allowSRLog
                    ) || $linkedToSalesOrder ? 'disabled' : null,
                )
            );
            $this->template->parse(
                'contracts',
                $blockName,
                true
            );
            $currentRow++;
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
            if ($userID != $dbeUser->getValue(DBEUser::userID) && $activeUsersOnly && $dbeUser->getValue(
                    DBEUser::activeFlag
                ) == 'N') {
                continue;
            }

            $userSelected = ($userID == $dbeUser->getValue(DBEUser::userID)) ? CT_SELECTED : null;
            $this->template->set_var(
                array(
                    'userSelected' => $userSelected,
                    'userID'       => $dbeUser->getValue(DBEUser::userID),
                    'userName'     => $dbeUser->getValue(DBEUser::name)
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

    private function getContractsForCustomer($customerID)
    {
        $buCustomerItem = new BUCustomerItem($this);
        $dsContract = new DataSet($this);
        $buCustomerItem->getContractsByCustomerID(
            $customerID,
            $dsContract,
            null
        );

        $data = [];

        while ($dsContract->fetchNext()) {

            if (!isset($data[$dsContract->getValue(DBEJContract::renewalType)])) {
                $data[$dsContract->getValue(DBEJContract::renewalType)] = [];
            }
            $data[$dsContract->getValue(DBEJContract::renewalType)][] = [
                "description" => $dsContract->getValue(DBEJContract::itemDescription) . ' ' . $dsContract->getValue(
                        DBEJContract::adslPhone
                    ) . ' ' . $dsContract->getValue(DBEJContract::notes) . ' ' . $dsContract->getValue(
                        DBEJContract::postcode
                    ),
                "id"          => $dsContract->getValue(DBEJContract::customerItemID),
                "disabled"    => !$dsContract->getValue(DBEJContract::allowSRLog)
            ];

        }
        return $data;
    }

    function parsePage()
    {
        parent::parsePage();
        $urlLogo = null;
        $this->template->set_var(
            array(
                'urlLogo' => $urlLogo,
                'txtHome' => 'Home'
            )
        );
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
    } // end contractDropdown

    function generateCSV()
    {
        $fileName = 'ACTIVITY.CSV';
        Header('Content-type: text/plain');
        Header('Content-Disposition: attachment; filename=' . $fileName);

        echo "customerName,postcode,contactName,serviceRequestID,priority,callActivityID,date,startTime,endTime,duration,value,activityType,userName,projectDescription,contractDescription,reason,internalNotes,managementReviewReason,rootCause\n";

        while ($this->dsSearchResults->fetchNext()) {
            echo $this->dsSearchResults->getExcelValue(DBECallActivitySearch::customerName) . "," .
                $this->dsSearchResults->getExcelValue(DBECallActivitySearch::postcode) . "," .
                $this->dsSearchResults->getExcelValue(DBECallActivitySearch::contactName) . "," .
                $this->dsSearchResults->getExcelValue(DBECallActivitySearch::problemID) . "," .
                $this->dsSearchResults->getExcelValue(DBECallActivitySearch::priority) . "," .
                $this->dsSearchResults->getExcelValue(DBECallActivitySearch::callActivityID) . "," .
                $this->dsSearchResults->getExcelValue(DBECallActivitySearch::date) . "," .
                $this->dsSearchResults->getExcelValue(DBECallActivitySearch::startTime) . "," .
                $this->dsSearchResults->getExcelValue(DBECallActivitySearch::endTime) . "," .
                number_format(
                    $this->dsSearchResults->getExcelValue(DBECallActivitySearch::duration) / 60 / 60,
                    2
                ) . "," .
                number_format(
                    $this->dsSearchResults->getExcelValue(DBECallActivitySearch::duration) / 60 / 60,
                    2
                ) * $this->dsSearchResults->getValue(DBECallActivitySearch::salePrice) . "," .
                $this->dsSearchResults->getExcelValue(DBECallActivitySearch::activityType) . "," .
                $this->dsSearchResults->getExcelValue(DBECallActivitySearch::userName) . "," .
                $this->dsSearchResults->getExcelValue(DBECallActivitySearch::projectDescription) . "," .
                $this->dsSearchResults->getExcelValue(DBECallActivitySearch::contractDescription) . "," .
                str_replace(
                    ',',
                    '\'',
                    addslashes(common_stripEverything($this->dsSearchResults->getValue(DBECallActivitySearch::reason)))
                ) . "," .
                str_replace(
                    ',',
                    '\'',
                    addslashes(
                        common_stripEverything($this->dsSearchResults->getValue(DBECallActivitySearch::internalNotes))
                    )
                ) . "," .
                $this->dsSearchResults->getExcelValue(DBECallActivitySearch::managementReviewReason) . "," .
                $this->dsSearchResults->getExcelValue(DBECallActivitySearch::rootCause) .
                "\n";
        }
        $this->pageClose();
        exit;
    } // end siteDropdown

    private function unlinkSalesOrder()
    {
        $activityId = @$_REQUEST['activityId'];

        if (!$activityId) {
            throw new Exception('Activity ID is missing');
        }

        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($activityId);
        $problemId = $dbeCallActivity->getValue(DBECallActivity::problemID);
        $dbeProblem = new DBEProblem($this);
        $dbeProblem->getRow($problemId);
        $dbeProblem->setValue(DBEProblem::linkedSalesOrderID, null);
        $dbeProblem->updateRow();
        $urlNext =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'         => 'displayActivity',
                    'callActivityID' => $activityId
                )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * @throws Exception
     */
    function serviceRequestsForContactPopup()
    {
        $this->setTemplateFiles(
            'ServiceRequestsForContactPopup',
            'ServiceRequestsForContactPopup'
        );
        $dsContactSrs = $this->buActivity->getProblemsByContact($this->getParam('contactID'));


        $dbeContact = new DBEContact($this);
        $dbeContact->getRow($this->getParam('contactID'));
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
                        'callActivityID' => $dsContactSrs->getValue(DBEJProblem::lastCallActivityID),
                        'reason'         => $this->getParam('reason')
                    )
                );

            $urlProblemHistoryPopup =
                Controller::buildLink(
                    'Activity.php',
                    array(
                        'action'    => 'problemHistoryPopup',
                        'problemID' => $dsContactSrs->getValue(DBEJProblem::problemID),
                        'htmlFmt'   => CT_HTML_FMT_POPUP
                    )
                );

            $this->template->set_var(
                array(
                    'contactProblemID'              => $dsContactSrs->getValue(DBEJProblem::problemID),
                    'contactDateRaised'             => Controller::dateYMDtoDMY(
                        $dsContactSrs->getValue(DBEJProblem::dateRaised)
                    ),
                    'contactReason'                 => self::truncate(
                        $dsContactSrs->getValue(DBEJProblem::reason),
                        100
                    ),
                    'contactLastReason'             => self::truncate(
                        $dsContactSrs->getValue(DBEJProblem::lastReason),
                        100
                    ),
                    'contactEngineerName'           => $dsContactSrs->getValue(DBEJProblem::engineerName),
                    'createFollowOnLink'            => $dsContactSrs->getValue(
                        DBEJProblem::status
                    ) == 'C' ? null : "<a href=" . $urlCreateFollowOn . ">Log activity</a>",
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
    }// end create5

    /**
     * Edit/Add Activity
     * @access private
     * @throws Exception
     */
    function displayActivity()
    {
        $this->setMethodName('displayActivity');
        $this->setPageTitle('Activity');
        $dsCallActivity = new DataSet($this);
        $this->buActivity->getActivityByID(
            $this->getParam('callActivityID'),
            $dsCallActivity
        );
        $callActivityID = $dsCallActivity->getValue(DBEJCallActivity::callActivityID);

        $problemID = $dsCallActivity->getValue(DBEJCallActivity::problemID);
        $dbeJProblem = new DBEJProblem($this);
        $dbeJProblem->getRow($problemID);

        $followLink = null;
        $travelLink = null;
        $urlSendVisitEmail = null;
        $linkNextActivity = null;
        $linkLastActivity = null;
        $linkPreviousActivity = null;
        $linkFirstActivity = null;
        $projectSCRText = null;
        $activitySCRText = null;

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
            $dsCallActivity->getValue(DBEJCallActivity::travelFlag) == 'Y' &&
            strstr(
                @$_SERVER['HTTP_REFERER'],
                'search'
            )
        ) {
            $_SESSION['includeTravel'] = 1;
        } else {
            if (isset($_REQUEST['toggleIncludeTravel'])) {
                $_SESSION['includeTravel'] = !@$_SESSION['includeTravel'];
            }
        }

        if ($dsCallActivity->getValue(DBEJCallActivity::callActTypeID) == CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID) {
            $_SESSION['includeOperationalTasks'] = 1;
        } else {
            if (isset($_REQUEST['toggleIncludeOperationalTasks'])) {
                $_SESSION['includeOperationalTasks'] = !@$_SESSION['includeOperationalTasks'];
            }
        }


        if (
            $dsCallActivity->getValue(DBEJCallActivity::callActTypeID) == CONFIG_SERVER_GUARD_UPDATE_ACTIVITY_TYPE_ID &&
            strstr(
                @$_SERVER['HTTP_REFERER'],
                'search'
            )
        ) {
            @$_SESSION['includeServerGuardUpdates'] = 1;
        } else {
            if (isset($_REQUEST['toggleIncludeServerGuardUpdates'])) {
                $_SESSION['includeServerGuardUpdates'] = !@$_SESSION['includeServerGuardUpdates'];
            }
        }

        if (isset($_REQUEST['toggleContext'])) {
            if (@$_SESSION['context'] == 'project') {
                $_SESSION['context'] = 'Problem';
            } else {
                $_SESSION['context'] = 'project';
            }
        }

        if (!$dsCallActivity->getValue(DBEJCallActivity::projectID)) {
            $this->setSessionParam('context', 'Problem');
        } else {
            $this->template->parse(
                'activityDisplayContext',
                'ActivityDisplayContext',
                true
            );
        }
        $dbeCallActivity = new DataSet($this);
        // get list of other activities in this problem or project
        $linksArray =
            $this->buActivity->getNavigateLinks(
                $callActivityID,
                $dbeCallActivity,
                @$_SESSION['includeTravel'],
                @$_SESSION['includeOperationalTasks'],
                @$_SESSION['includeServerGuardUpdates']
            );

        /*
      Now decide what we should do about travel
      */
        if (!@$_SESSION['includeTravel'] && $dsCallActivity->getValue(
                DBEJCallActivity::travelFlag
            ) == 'Y' && $dbeCallActivity->rowCount() > 0) {

            $dbeCallActivity->fetchNext();

            $callActivityID = $dbeCallActivity->getValue(DBEJCallActivity::callActivityID);

            $this->buActivity->getActivityByID(
                $callActivityID,
                $dsCallActivity
            );
        }

        $this->setPageTitle(CONFIG_SERVICE_REQUEST_DESC . ' ' . $dsCallActivity->getValue(DBEJCallActivity::problemID));

        $buCustomer = new BUCustomer($this);
        $dsCustomer = new DataSet($this);
        $buCustomer->getCustomerByID(
            $dsCallActivity->getValue(DBEJCallActivity::customerID),
            $dsCustomer
        );
        $dsSite = new DataSet($this);
        $buCustomer->getSiteByCustomerIDSiteNo(
            $dsCallActivity->getValue(DBEJCallActivity::customerID),
            $dsCallActivity->getValue(DBEJCallActivity::siteNo),
            $dsSite
        );
        $dsContact = new DataSet($this);
        $buCustomer->getContactByID(
            $dsCallActivity->getValue(DBEJCallActivity::contactID),
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
            $customerDetails .= ', ' . $dsContact->getValue(DBEContact::firstName) . ' ' . $dsContact->getValue(
                    DBEContact::lastName
                );
        }
        /*
      allow delete if open (no end time) OR (if user is member of Supervisor group then
      not authorised)
      */
        $deleteLink = null;
        if (
            !$dsCallActivity->getValue(DBEJCallActivity::endTime) || ($dsCallActivity->getValue(
                    DBEJCallActivity::status
                ) != 'A' && $this->hasPermissions(MAINTENANCE_PERMISSION))
        ) {
            $urlDeleteActivity =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'         => CTACTIVITY_ACT_DELETE_ACTIVITY,
                        'callActivityID' => $callActivityID
                    )
                );
            $deleteLink = '<A href="' . $urlDeleteActivity . '"  title="Delete Activity" onClick="if(!confirm(\'Delete this activity?\')) return(false)">Delete Activity</A>';
            if ($this->buActivity->countActivitiesInProblem($problemID) == 1) {
                $deleteLink = '<A href="' . $urlDeleteActivity . '"  title="Delete Request" onClick="if(!confirm(\'Deleting this activity will remove all traces of this Service Request from the system. Are you sure?\')) return(false)">Delete Request</A>';
            }
        }

        /*
      allow move of activity/Problem to another Problem
      */
        if ($this->hasPermissions(SUPERVISOR_PERMISSION)) {

            $urlLinkToProblem =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'             => 'linkProblems',
                        'fromCallActivityID' => $callActivityID
                    )
                );

            $this->template->set_var(
                array(
                    'urlLinkToProblem' => $urlLinkToProblem,
                )
            );
            $this->template->parse(
                'activityDisplayLinkToProblem',
                'ActivityDisplayLinkToProblem',
                true
            );
        }

        $urlViewExpenses = null;
        $txtViewExpenses = null;
        $buExpenseType = new BUExpenseType($this);
        $expenseTypes = $buExpenseType->getExpenseTypesAllowedForActivityTypeID(
            $dsCallActivity->getValue(DBEJCallActivity::callActTypeID)
        );
        if (count($expenseTypes)) {
            $urlViewExpenses =
                Controller::buildLink(
                    'Expense.php',
                    array(
                        'action'         => CTCNC_ACT_VIEW,
                        'callActivityID' => $callActivityID
                    )
                );
            $txtViewExpenses = 'Expenses';
        }
        /*
      Show check link if this activity is closed
      */
        $buUser = new BUUser($this);
        $urlUnhideSR = null;
        $txtUnhideSR = null;
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
        $txtSendVisitEmail = null;
        if ($dsCallActivity->getValue(DBEJCallActivity::allowSCRFlag) == 'Y') {

            $urlSendVisitEmail =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'         => CTACTIVITY_ACT_SEND_VISIT_EMAIL,
                        'callActivityID' => $callActivityID
                    )
                );
            $txtSendVisitEmail = 'Confirm Email';
            $projectSCRText = null;
            $activitySCRText = 'Activity SCR';
            if (            // old call ref SCR
                $dsCallActivity->getValue(DBEJCallActivity::projectID) != 0 and
                $dsCallActivity->getValue(DBEJCallActivity::projectID) > 5 and
                $dsCallActivity->getValue(DBEJCallActivity::projectID) < 21249
            ) {
                $projectSCRText = 'Old Call SCR';
                $activitySCRText = null;
            }
        }


        $urlSetProblemFixed = null;
        $txtSetProblemFixed = null;
        if (
            $dbeJProblem->getValue(DBEJProblem::status) == 'P' &&
            !$dbeJProblem->getValue(DBEJProblem::rootCauseID)
        ) {

            $urlSetProblemFixed =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'         => 'setProblemFixed',
                        'problemID'      => $dsCallActivity->getValue(DBEJCallActivity::problemID),
                        'callActivityID' => $dsCallActivity->getValue(DBEJCallActivity::callActivityID)
                    )
                );
            $txtSetProblemFixed = 'Fixed';
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
                        'callActivityID' => $dbeCallActivity->getValue(DBEJCallActivity::callActivityID)
                    )
                );


            $this->template->set_var(
                array(
                    'callActivityID'    => $dbeCallActivity->getValue(DBEJCallActivity::callActivityID),
                    'dateEngineer'      => $dbeCallActivity->getValue(DBEJCallActivity::dateEngineer),
                    'contactName'       => $dbeCallActivity->getValue(DBEJCallActivity::contactName),
                    'activityType'      => $dbeCallActivity->getValue(DBEJCallActivity::activityType),
                    'urlJumpToActivity' => $urlJumpToActivity,
                    'selected'          => $dbeCallActivity->getValue(
                        DBEJCallActivity::callActivityID
                    ) == $callActivityID ? 'SELECTED' : null
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
        $minResponseTime = $buCustomerItem->getMinResponseTime($dsCallActivity->getValue(DBEJCallActivity::customerID));

        $problemStatus = @$this->buActivity->problemStatusArray[$dbeJProblem->getValue(DBEJProblem::status)];

        $dbeLastActivity = $this->buActivity->getLastActivityInProblem(
            $dsCallActivity->getValue(DBEJCallActivity::problemID)
        );


        // If In Progress, find out whether this problem is waiting on CNC or on Customer
        if ($dbeJProblem->getValue(DBEJProblem::status) == 'P') {

            if ($dbeJProblem->getValue(DBEJProblem::awaitingCustomerResponseFlag) == 'Y') {

                $problemStatus .= ' - Awaiting Customer';

            } else {

                $problemStatus .= ' - Awaiting CNC';

            }

        }

        /*
      Display user name with green background if this activity is being edited now

      Also, disable the edit link
      */
        $urlEditActivity = null;
        $editTooltip = null;
        $editClass = null;

        if (
            $dbeLastActivity->getValue(DBEJCallActivity::callActTypeID) == 0 &&
            $dbeLastActivity->getValue(DBEJCallActivity::userID) != $GLOBALS['auth']->is_authenticated()
        ) {
            $currentUserBgColor = self::GREEN;
            $currentUser = $dbeLastActivity->getValue(
                    DBEJCallActivity::userName
                ) . ' Is Adding New Activity To This Request Now';
        } else {
            $currentUserBgColor = self::CONTENT;
            $currentUser = null;

            if (($editionCheck = $this->buActivity->checkActivityEdition(
                    $dsCallActivity,
                    $this
                )) == 'ALL_GOOD') {

                $urlEditActivity =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'         => CTACTIVITY_ACT_EDIT_ACTIVITY,
                            'callActivityID' => $callActivityID
                        )
                    );
                $editTooltip = "Edit activity details";
            } else {
                $urlEditActivity = '#';
                $editTooltip = $editionCheck;
                $editClass = "disabled";
            }

            if (
                $dsCustomer->getValue(DBECustomer::referredFlag) != 'Y' &&   // customer not referred
                $dbeJProblem->getValue(DBEJProblem::status) != 'C'           // not completed
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
                    $dbeCallActivity->getValue(DBEJCallActivity::problemStatus) == 'I' &&
                    $dbeCallActivity->getValue(DBEJCallActivity::serverGuard) == 'N' &&
                    $dbeJProblem->getValue(DBEJProblem::hideFromCustomerFlag) == 'N'
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

        if ($dsCallActivity->getValue(DBEJCallActivity::contractCustomerItemID)) {
            $dbeContract = new DBEJContract($this);
            $dbeContract->getRowByContractID($dsCallActivity->getValue(DBEJCallActivity::contractCustomerItemID));
            $contractDescription = Controller::htmlDisplayText(
                $description = $dbeContract->getValue(DBEJContract::itemDescription) . ' ' . $dbeContract->getValue(
                        DBEJContract::adslPhone
                    ) . ' ' . $dbeContract->getValue(DBEJContract::notes) . ' ' . $dbeContract->getValue(
                        DBEJContract::postcode
                    )
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

        $disabled = null;
        if (!$this->hasPermissions(SUPERVISOR_PERMISSION)) {
            $disabled = CTCNC_HTML_DISABLED;
        }
        $hiddenText = null;

        if (
            $dsCallActivity->getValue(DBEJCallActivity::hideFromCustomerFlag) == 'Y' ||
            $dsCallActivity->getValue(DBEJCallActivity::problemHideFromCustomerFlag) == 'Y'
        ) {
            $hiddenText = 'Hidden From Customer';
        }
        $authorisedByName = null;
        if ((int)$dbeJProblem->getValue(DBEProblem::authorisedBy)) {
            $dbeContact = new DBEContact($this);

            $dbeContact->getRow($dbeJProblem->getValue(DBEProblem::authorisedBy));

            $authorisedByName = $dbeContact->getValue(DBEContact::firstName) . " " . $dbeContact->getValue(
                    DBEContact::lastName
                );
        }


        $this->template->set_var(
            array(
                'hiddenText'                         => $hiddenText,
                'currentUserBgColor'                 => $currentUserBgColor,
                'currentUser'                        => $currentUser,
                'problemPriority'                    => @$this->buActivity->priorityArray[$dbeJProblem->getValue(
                    DBEJProblem::priority
                )],
                'problemStatus'                      => $problemStatus,
                'renewalsLink'                       => $this->getRenewalsLink(
                    $dsCallActivity->getValue(DBEJCallActivity::customerID)
                ),
                'callActivityID'                     => $callActivityID,
                'problemID'                          => $dsCallActivity->getValue(DBEJCallActivity::problemID),
                'customerID'                         => $dsCallActivity->getValue(DBEJCallActivity::customerID),
                'underContractFlag'                  => $dsCallActivity->getValue(DBEJCallActivity::underContractFlag),
                'contactName'                        => Controller::htmlDisplayText(
                    $dsCallActivity->getValue(DBEJCallActivity::contactName)
                ),
                'engineerName'                       => Controller::htmlDisplayText(
                    $dsCallActivity->getValue(DBEJCallActivity::userName)
                ),
                'priority'                           => Controller::htmlDisplayText(
                    $this->buActivity->priorityArray[$dsCallActivity->getValue(DBEJCallActivity::priority)]
                ),
                'customerDetails'                    => $customerDetails,
                'customerName'                       => $customerDetails,
                'customerNameDisplayClass'           => $this->getCustomerNameDisplayClass($dsCustomer),
                'urlCustomer'                        => $this->getCustomerUrl(
                    $dsCallActivity->getValue(DBEJCallActivity::customerID)
                ),
                'urlMessageToSales'                  => $urlMessageToSales,
                'callDate'                           => $dsCallActivity->getValue(DBEJCallActivity::date),
                'customerItemID'                     => $dsCallActivity->getValue(DBEJCallActivity::customerItemID),
                'contractDescription'                => $contractDescription,
                'projectDescription'                 => Controller::htmlDisplayText(
                    $dsCallActivity->getValue(DBEJCallActivity::projectDescription)
                ),
                'date'                               => Controller::dateYMDtoDMY(
                    $dsCallActivity->getValue(DBEJCallActivity::date)
                ),
                'completeDate'                       => Controller::dateYMDtoDMY(
                    $dsCallActivity->getValue(DBEJCallActivity::completeDate)
                ),
                'curValue'                           => Controller::htmlDisplayText(
                    $dsCallActivity->getValue(DBEJCallActivity::curValue)
                ),
                'startTime'                          => Controller::htmlDisplayText(
                    $dsCallActivity->getValue(DBEJCallActivity::startTime)
                ),
                'endTime'                            => Controller::htmlDisplayText(
                    $dsCallActivity->getValue(DBEJCallActivity::endTime)
                ),
                'reason'                             => $dsCallActivity->getValue(DBEJCallActivity::reason),
                'internalNotes'                      => $dsCallActivity->getValue(DBEJCallActivity::internalNotes),
                'siteDesc'                           => Controller::htmlInputText(
                    $dsCallActivity->getValue(DBEJCallActivity::siteDesc)
                ),
                'status'                             => $dsCallActivity->getValue(DBEJCallActivity::status),
                'rootCauseDescription'               => Controller::htmlInputText(
                    $dsCallActivity->getValue(DBEJCallActivity::rootCauseDescription)
                ),
                'urlEditActivity'                    => $urlEditActivity,
                'editTooltip'                        => $editTooltip,
                'editClass'                          => $editClass,
                'urlSetProblemFixed'                 => $urlSetProblemFixed,
                'txtSetProblemFixed'                 => $txtSetProblemFixed,
                'urlViewExpenses'                    => $urlViewExpenses,
                'txtViewExpenses'                    => $txtViewExpenses,
                'deleteLink'                         => $deleteLink,
                'urlToggleIncludeTravel'             => $urlToggleIncludeTravel,
                'urlToggleCriticalFlag'              => $urlToggleCriticalFlag,
                'criticalFlagChecked'                => $dsCallActivity->getValue(
                    DBEJCallActivity::criticalFlag
                ) == 'Y' ? 'CHECKED' : null,
                'urlToggleMonitoringFlag'            => $urlToggleMonitoringFlag,
                'monitoringFlagChecked'              => $this->checkMonitoring(
                    $dsCallActivity->getValue(DBEJCallActivity::problemID)
                ) ? 'CHECKED' : null,
                'includeOperationalTasksChecked'     => $this->getSessionParam(
                    'includeOperationalTasks'
                ) ? 'CHECKED' : null,
                'urlToggleIncludeServerGuardUpdates' => $urlToggleIncludeServerGuardUpdates,
                'urlToggleIncludeOperationalTasks'   => $urlToggleIncludeOperationalTasks,
                'urlToggleContext'                   => $urlToggleContext,
                'followLink'                         => $followLink,
                'travelLink'                         => $travelLink,
                'urlUnhideSR'                        => $urlUnhideSR,
                'txtUnhideSR'                        => $txtUnhideSR,
                'activityType'                       => Controller::htmlDisplayText(
                    $dsCallActivity->getValue(DBEJCallActivity::activityType)
                ),
                'serverGuard'                        => Controller::htmlDisplayText(
                    $this->serverGuardArray[$dsCallActivity->getValue(DBEJCallActivity::serverGuard)]
                ),
                'urlAddToCalendar'                   => $urlAddToCalendar,
                'txtAddToCalendar'                   => $txtAddToCalendar,
                'disabledChangeRequest'              => $dbeJProblem->getValue(
                    DBEJProblem::status
                ) == 'P' ? '' : 'disabled',
                'urlSendVisitEmail'                  => $urlSendVisitEmail,
                'txtSendVisitEmail'                  => $txtSendVisitEmail,
                'linkNextActivity'                   => $linkNextActivity,
                'linkLastActivity'                   => $linkLastActivity,
                'linkPreviousActivity'               => $linkPreviousActivity,
                'linkFirstActivity'                  => $linkFirstActivity,
                'projectID'                          => $dsCallActivity->getValue(DBEJCallActivity::projectID),
                'projectSCRText'                     => $projectSCRText,
                'activitySCRText'                    => $activitySCRText,
                'activityChainCount'                 => $activityChainCount,
                'thisRowNumber'                      => $linksArray['thisRowNumber'],
                'includeTravelChecked'               => $this->getSessionParam('includeTravel') ? 'CHECKED' : null,
                'includeServerGuardUpdatesChecked'   => $this->getSessionParam(
                    'includeServerGuardUpdates'
                ) ? 'CHECKED' : null,
                'projectChecked'                     => $_SESSION['context'] == 'project' ? 'CHECKED' : null,
                'minResponseTime'                    => $minResponseTime,
                'totalActivityDurationHours'         => $dbeJProblem->getValue(DBEJProblem::totalActivityDurationHours),
                'chargeableActivityDurationHours'    => $dbeJProblem->getValue(
                    DBEJProblem::chargeableActivityDurationHours
                ),
                'problemHistoryLink'                 => $this->getProblemHistoryLink(
                    $dsCallActivity->getValue(DBEJCallActivity::problemID)
                ),
                'projectLink'                        => BUProject::getCurrentProjectLink(
                    $dsCallActivity->getValue(DBEJCallActivity::customerID)
                ),
                'passwordLink'                       => $this->getPasswordLink(
                    $dsCallActivity->getValue(DBEJCallActivity::customerID)
                ),
                'generatePasswordLink'               => $this->getGeneratePasswordLink(),
                'thirdPartyContactLink'              => $this->getThirdPartyContactLink(
                    $dsCallActivity->getValue(DBEJCallActivity::customerID)
                ),
                'contractListPopupLink'              => $this->getContractListPopupLink(
                    $dsCallActivity->getValue(DBEJCallActivity::customerID)
                ),
                'contactHistoryLink'                 => $this->getServiceRequestForContactLink(
                    $dsCallActivity->getValue(DBECallActivity::contactID)
                ),
                'salesOrderLink'                     => $this->getSalesOrderLink(
                    $dsCallActivity->getValue(DBEJCallActivity::linkedSalesOrderID),
                    $dsCallActivity->getValue(DBEJCallActivity::callActivityID)
                ),
                'contactNotes'                       => $dsCallActivity->getValue(DBEJCallActivity::contactNotes),
                'techNotes'                          => $dsCallActivity->getValue(DBEJCallActivity::techNotes),
                'urlLinkedSalesOrder'                => $urlLinkedSalesOrder,
                'urlSalesRequest'                    => $urlSalesRequest,
                'disabled'                           => $disabled,
                'contactPhone'                       => $buCustomer->getContactPhoneForHtml(
                    $dsCallActivity->getValue(DBEJCallActivity::contactID),
                    "Service Request {$problemID}"
                ),
                'authorisedByHide'                   => $authorisedByName ? null : "hidden",
                'authorisedByName'                   => $authorisedByName,
                'raiseIcon'                          => $this->getProblemRaiseIcon($dbeJProblem)
            )
        );

        $problemID = $dsCallActivity->getValue(DBEJCallActivity::problemID);
        $this->documents(
            $callActivityID,
            $problemID,
            'ActivityDisplay'
        );

        /*
      On Site Activities within 5 days

      show a list of any on-site activity within 5 days either side
      */
        $db = $this->buActivity->getOnSiteActivitiesWithinFiveDaysOfActivity($callActivityID);

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
        $totalValue = 0;
        if (count($expenseTypes)) {
            $buExpense = new BUExpense($this);

            $this->template->set_var(
                'totalValue',
                Controller::formatNumber($totalValue)
            );
            $dsExpense = new DataSet($this);
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

                    $expenseID = $dsExpense->getValue(DBEJExpense::expenseID);

                    $this->template->set_var(
                        array(
                            'expenseID'   => $expenseID,
                            'expenseType' => Controller::htmlDisplayText(
                                $dsExpense->getValue(DBEJExpense::expenseType)
                            ),
                            'mileage'     => Controller::htmlDisplayText($dsExpense->getValue(DBEJExpense::mileage)),
                            'value'       => Controller::formatNumber($dsExpense->getValue(DBEJExpense::value)),
                            'vatFlag'     => Controller::htmlDisplayText($dsExpense->getValue(DBEJExpense::vatFlag))
                        )
                    );

                    $totalValue += $dsExpense->getValue(DBEJExpense::value);

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
    }// end displayOpenSrs


//----------------

    /**
     * @param $customerID
     * @return string
     * @throws Exception
     */
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
    }

    /**
     * @param DataSet|DBECustomer $dsCustomer
     * @return string
     */
    function getCustomerNameDisplayClass($dsCustomer)
    {
        if (
            $dsCustomer->getValue(DBECustomer::specialAttentionFlag) == 'Y' &&
            $dsCustomer->getValue(DBECustomer::specialAttentionEndDate) >= date('Y-m-d')
        ) {
            return 'specialAttentionCustomer';
        }

        return null;
    }

    /**
     * @param $customerID
     * @return mixed|string
     * @throws Exception
     */
    function getCustomerUrl($customerID)
    {
        return Controller::buildLink(
            'Customer.php',
            array(
                'action'     => 'dispEdit',
                'customerID' => $customerID
            )
        );

    }

    private
    function checkMonitoring($problemID
    )
    {
        return $this->buActivity->checkMonitoringFlag($problemID);
    }

    /**
     * @param $problemID
     * @return string
     * @throws Exception
     */
    function getProblemHistoryLink($problemID)
    {
        if (!$problemID) {
            return null;
        }
        $url = Controller::buildLink(
            'Activity.php',
            array(
                'action'    => 'problemHistoryPopup',
                'problemID' => $problemID,
                'htmlFmt'   => CT_HTML_FMT_POPUP
            )
        );

        return '| <A HREF="' . $url . ' " target="_blank" >History</A>';
    }

    /**
     * @param $customerID
     * @return string
     * @throws Exception
     */
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

    /**
     * @return string
     * @throws Exception
     */
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
          \'scrollbars=yes,resizable=yes,height=524,width=855,copyhistory=no, menubar=0\')" >Generate Password</a> ';

        return $passwordLink;
    }// end function editActivity()

    /**
     * @param $customerID
     * @return string
     * @throws Exception
     */
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
    }// end function editLinkedSalesOrder()

    /**
     * @param $customerID
     * @return string
     * @throws Exception
     */
    function getContractListPopupLink($customerID)
    {
        $contractListPopupLinkURL =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'     => 'contractListPopup',
                    'customerID' => $customerID,
                )
            );


        $contractListPopupLink = '| <a href="' . $contractListPopupLinkURL . '" target="_blank" title="Contracts">Contracts</a>';

        return $contractListPopupLink;
    }

    /**
     * @param $contactID
     * @return string
     * @throws Exception
     */
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

    /**
     * @param $linkedOrdheadID
     * @param $activityId
     * @return string
     * @throws Exception
     */
    function getSalesOrderLink($linkedOrdheadID, $activityId)
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
            $linkMarkup = '| <a href="?action=unlinkSalesOrder&activityId=' . $activityId . '" onclick="return confirm(\'Are you sure you want to unlink this request to Sales Order ' . $linkedOrdheadID . '?\');">Unlink</a>  <a href="' . $linkURL . '" target="_blank" title="Sales Order">Sales Order</a>';
        } else {
            $linkMarkup = '| <a href="#" onclick="linkedSalesOrderPopup();">Sales Order</a>';
        }

        return $linkMarkup;
    }

    private function getProblemRaiseIcon($dbeJProblem)
    {
        if (isset($dbeJProblem)) {
            $raiseTypeId = $dbeJProblem->getValue(DBEProblem::raiseTypeId);
            if (isset($raiseTypeId) && $raiseTypeId != null) {
                $dbeProblemRaiseType = new  DBEProblemRaiseType($this);
                $dbeProblemRaiseType->setPKValue($raiseTypeId);
                $dbeProblemRaiseType->getRow();
                switch ($dbeProblemRaiseType->getValue(DBEProblemRaiseType::description)) {
                    case 'Email':
                        return "<i class='fa fa-envelope' title='This Service Request was raised by email'></i>";
                        break;
                    case 'Portal':
                        return "<i class='fa fa-edge' title='This Service Request was raised by the portal'></i>";
                        break;
                    case 'Phone':
                        return "<i class='fa fa-phone' title='This Service Request was raised by phone'></i>";
                        break;
                    case 'On site':
                        return "<i class='fas fa-building' title='This Service Request was raised by an on site engineer'></i>";
                        break;
                    case 'Alert':
                        return "<i class='fas fa-bell' title='This Service Request was raised by an alert'></i>";
                        break;
                    case 'Sales':
                        return "<i class='fas fa-shopping-cart' title='This Service Request was raised via Sales'></i>";
                        break;
                    case 'Manual':
                        return "<i class='fas fa-user-edit' title='This Service Request was raised manually'></i>";
                        break;
                }
            }
        } else return null;
    } // end cancelEdit

    /**
     * Documents display and upload
     *
     * @param $callActivityID
     * @param $problemID
     * @param $templateName
     * @throws Exception
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
                'uploadDescription' => $this->getParam('uploadDescription'),
                'userfile'          => isset($_FILES['userfile']) ? $_FILES['userfile']['name'] : null,
                'txtUploadFile'     => $txtUploadFile,
                'urlUploadFile'     => $urlUploadFile
            )
        );

        $dbeJCallDocument = new DBEJCallDocument($this);
        $dbeJCallDocument->setValue(
            DBEJCallDocument::problemID,
            $problemID
        );
        $dbeJCallDocument->getRowsByColumn(DBEJCallDocument::problemID);

        while ($dbeJCallDocument->fetchNext()) {

            $urlViewFile =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'         => CTACTIVITY_ACT_VIEW_FILE,
                        'callDocumentID' => $dbeJCallDocument->getValue(DBEJCallDocument::callDocumentID)
                    )
                );

            $urlDeleteFile =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'         => CTACTIVITY_ACT_DELETE_FILE,
                        'callActivityID' => $callActivityID,
                        'callDocumentID' => $dbeJCallDocument->getValue(DBEJCallDocument::callDocumentID)
                    )
                );

            $this->template->set_var(
                array(
                    'description'    => $dbeJCallDocument->getValue(DBEJCallDocument::description),
                    'filename'       => $dbeJCallDocument->getValue(DBEJCallDocument::filename),
                    'createUserName' => $dbeJCallDocument->getValue(DBEJCallDocument::createUserName),
                    'createDate'     => $dbeJCallDocument->getValue(DBEJCallDocument::createDate),
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


    }

    /**
     * @throws Exception
     */
    function displayFirstActivity()
    {
        $dbeCallActivity = $this->buActivity->getFirstActivityInProblem($this->getParam('problemID'));

        $this->redirectToDisplay($dbeCallActivity->getValue(DBEJCallActivity::callActivityID));

    }// end function displayActivity()

    /**
     * Redirect to call page
     * @access private
     * @param $callActivityID
     * @throws Exception
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

    /**
     * @throws Exception
     */
    function displayLastActivity()
    {
        $dbeCallActivity = $this->buActivity->getLastActivityInProblem($this->getParam('problemID'));

        $this->redirectToDisplay($dbeCallActivity->getValue(DBEJCallActivity::callActivityID));

    }

    /**
     * Create wizard step 1: Customer, site and contact selection
     * @access private
     * @param bool $referred
     * @param bool $reason
     * @throws Exception
     */
    function activityCreate1($referred = false,
                             $reason = false
    )
    {
        $this->setMenuId(101);
        $this->setMethodName('activityCreate1');

        if (!self::hasPermissions(TECHNICAL_PERMISSION)) {
            Header("Location: /NotAllowed.php");
            exit;
        }

        if ($this->getParam('reason')) {
            $reason = $this->getParam('reason');
        }
        // Parameters
        $error = null;
        $result = null;
        /* do the search if POST request or we already have a customer.
      i.e. called from createRequestFromCustomerRequest()
      */
        if (
            $_SERVER['REQUEST_METHOD'] == 'POST' or
            $this->getParam('customerID')
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
                !$this->getParam('customerID') &&
                !$this->getParam('contactFirstName') &&
                !$this->getParam('contactLastName') &&
                !$this->getParam('customerString')
            ) {
                $error = 'Please enter either a customer or contact name';
            }

            if (!$error) {

                mysqli_select_db(
                    $db,
                    DB_NAME
                );
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
            cus_referred,
            specialAttentionContactFlag = 'Y' as specialAttentionContact,
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
          WHERE supportLevel is not null";

                if ($this->getParam('customerString')) {
                    $query .= " AND ( cus_name LIKE '%" . mysqli_real_escape_string(
                            $db,
                            $this->getParam(
                                'customerString'
                            )
                        ) . "%' OR customer.cus_custno = '" . mysqli_real_escape_string(
                            $db,
                            $this->getParam(
                                'customerString'
                            )
                        ) . "')";
                }
                if ($this->getParam('contactFirstName')) {
                    $query .= "
            AND con_first_name LIKE '%" . mysqli_real_escape_string($db, $this->getParam('contactFirstName')) . "%'";
                }
                if ($this->getParam('contactLastName')) {
                    $query .= "
            AND con_last_name LIKE '%" . mysqli_real_escape_string($db, $this->getParam('contactLastName')) . "%'";
                }
                if ($this->getParam('contactLastName')) {
                    $query .= "
            AND con_last_name LIKE '%" . mysqli_real_escape_string($db, $this->getParam('contactLastName')) . "%'";
                }

                if ($this->getParam('customerID')) {
                    $query .= " AND customer.cus_custno = " . $this->getParam('customerID');
                }

                if ($this->getParam('contactID')) {
                    $query .= " AND con_contno = " . $this->getParam('contactID');
                }

                $query .= " and active ORDER BY cus_name, con_last_name, con_first_name";
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

        $reasonMarkup = null;
        if ($reason) {
            $reasonMarkup = '<div style="width: 500px; border: dotted; padding: 5px; ">' . $reason . '</div>';
        }


        $this->template->set_var(
            array(
                'contactFirstName' => $this->getParam('contactFirstName'),
                'contactLastName'  => $this->getParam('contactLastName'),
                'customerString'   => $this->getParam('customerString'),
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
                // main support contact?
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

                $this->template->set_var(
                    array(
                        'cus_name'                => $cus_name,
                        'contact_name'            => $contact_name,
                        'contact_position'        => $contact_position,
                        'con_phone'               => $contact_phone,
                        'add_phone'               => $site_phone,
                        'site_name'               => $site_name,
                        'supportClass'            => $supportClass,
                        'formAction'              => $action,
                        'customerID'              => $row['cus_custno'],
                        'contactID'               => $row['con_contno'],
                        'contact_notes'           => $row['con_notes'],
                        'contact_supportLevel'    => $row['supportLevel'],
                        'contract'                => $row['hasPrepay'] ? 'PrePay' : ($row['hasServiceDesk'] ? $row['hasServiceDesk'] : 'T&M Authorisation Required'),
                        'referredDisabled'        => $row['cus_referred'] == 'Y' ? "disabled" : null,
                        'furloughDisabled'        => $row['supportLevel'] === DBEContact::supportLevelFurlough ? 'true' : 'false',
                        'specialAttentionContact' => $row['specialAttentionContact'] ? 'specialAttentionContact' : null
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

    }

    /**
     * edit a value only SR
     * @access private
     * @throws Exception
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
        $error = [];
        /* validate if this is a POST request */
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $sessionValue = $this->getSessionParam($this->sessionKey);

            if (!$sessionValue) {
                $sessionValue = [];
            }

            if ($this->getParam('curValue') && !is_numeric($this->getParam('curValue'))) {
                $error['curValue'] = 'Enter a currency value';
            }

            if (!$this->getParam('contractCustomerItemID')) {
                $error['contractCustomerItemID'] = 'Required';
            } else {
                $sessionValue['contractCustomerItemID'] = $this->getParam('contractCustomerItemID');

            }
            if (count($error) == 0) {
                $sessionValue['callActivityID'] = 0;
                $sessionValue['date'] = date('d/m/Y');
                $sessionValue['curValue'] = $this->getParam('curValue');
                $sessionValue['startTime'] = date('H:i');
                $sessionValue['status'] = 'C';
                $sessionValue['contractCustomerItemID'] = $this->getParam('contractCustomerItemID');
                $sessionValue['userID'] = $GLOBALS['auth']->is_authenticated();
                $this->setSessionParam($this->sessionKey, $sessionValue);
                $dsCallActivity = $this->buActivity->createActivityFromSession($this->sessionKey);
                $callActivityID = $dsCallActivity->getValue(DBEJCallActivity::callActivityID);
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

    }  // end finaliseProblem

    function validateSession()
    {

        if (!isset($_SESSION[$this->sessionKey])) {

            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=activityCreate1');
            exit;

        }

    }

    /**
     * @throws Exception
     */
    function displayOpenSrs()
    {
        $this->setMethodName('displayOpenSrs');

        $buCustomer = new BUCustomer($this);
        $dsCustomer = new DataSet($this);
        $buCustomer->getCustomerByID(
            $this->getParam('customerID'),
            $dsCustomer
        );
        $title = "Existing Service Requests for " . $dsCustomer->getValue(DBECustomer::name);
        if ($dsCustomer->getValue(DBECustomer::specialAttentionFlag) == 'Y') {
            $title .= "<span style='color: red'> On Special Attention</span>";
        }
        $this->setPageTitle($title);

        $sessionValue = $this->getSessionParam($this->sessionKey);
        if (!$sessionValue) {
            $sessionValue = [];
        }

        $sessionValue['reason'] = $this->getParam('reason');
        $sessionValue['customerName'] = $dsCustomer->getValue(DBECustomer::name);
        $sessionValue['hideFromCustomerFlag'] = $this->getParam('hideFromCustomerFlag');
        $sessionValue['internalNotes'] = $this->getParam('internalNotes');

        $this->setSessionParam($this->sessionKey, $sessionValue);

        $dsContactSrs = $this->buActivity->getProblemsByContact($this->getParam('contactID'));

        $dsActiveSrs = $this->buActivity->getActiveProblemsByCustomer($this->getParam('customerID'));

        $this->setTemplateFiles(
            'ActivityExistingRequests',
            'ActivityExistingRequests.inc'
        );

        $dbeContact = new DBEContact($this);
        $dbeContact->getRow($this->getParam('contactID'));


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
            $urlProblemHistoryPopup =
                Controller::buildLink(
                    'Activity.php',
                    array(
                        'action'    => 'problemHistoryPopup',
                        'problemID' => $dsContactSrs->getValue(DBEJProblem::problemID),
                        'htmlFmt'   => CT_HTML_FMT_POPUP
                    )
                );

            $this->template->set_var(
                array(
                    'contactProblemID'              => $dsContactSrs->getValue(DBEJProblem::problemID),
                    'contactDateRaised'             => Controller::dateYMDtoDMY(
                        $dsContactSrs->getValue(DBEJProblem::dateRaised)
                    ),
                    'contactReason'                 => self::truncate(
                        $dsContactSrs->getValue(DBEJProblem::reason),
                        100
                    ),
                    'contactLastReason'             => self::truncate(
                        $dsContactSrs->getValue(DBEJProblem::lastReason),
                        100
                    ),
                    'contactEngineerName'           => $dsContactSrs->getValue(DBEJProblem::engineerName),
                    'shouldBeHidden'                => $dsContactSrs->getValue(
                        DBEJProblem::status
                    ) == 'C' ? 'hidden' : null,
                    'contactActivityID'             => $dsContactSrs->getValue(DBEJProblem::lastCallActivityID),
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
                        'callActivityID' => $dsActiveSrs->getValue(DBEJProblem::lastCallActivityID),
                        'reason'         => $this->getParam('reason')
                    )
                );

            $urlProblemHistoryPopup =
                Controller::buildLink(
                    'Activity.php',
                    array(
                        'action'    => 'problemHistoryPopup',
                        'problemID' => $dsActiveSrs->getValue(DBEJProblem::problemID),
                        'htmlFmt'   => CT_HTML_FMT_POPUP
                    )
                );

            $this->template->set_var(
                array(
                    'problemID'              => $dsActiveSrs->getValue(DBEJProblem::problemID),
                    'dateRaised'             => Controller::dateYMDtoDMY(
                        $dsActiveSrs->getValue(DBEJProblem::dateRaised)
                    ),
                    'reason'                 => self::truncate(
                        $dsActiveSrs->getValue(DBEJProblem::reason),
                        100
                    ),
                    'lastReason'             => self::truncate(
                        $dsActiveSrs->getValue(DBEJProblem::lastReason),
                        100
                    ),
                    'engineerName'           => $dsActiveSrs->getValue(DBEJProblem::engineerName),
                    'activityID'             => $dsActiveSrs->getValue(DBEJProblem::lastCallActivityID),
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
                    'customerID' => $this->getParam('customerID'),
                    'contactID'  => $this->getParam('contactID'),
                    'reason'     => $this->getParam('reason')
                )
            );

        $this->template->set_var(
            [
                'reason'     => base64_encode($this->getParam('reason')),
                'customerID' => $this->getParam('customerID'),
                'contactID'  => $this->getParam('contactID'),
            ]
        );

        $this->template->parse(
            'CONTENTS',
            'ActivityExistingRequests',
            true
        );

        $this->parsePage();

    }    // end allocateAdditionalTime

    /**
     * Create Service Request
     * @access private
     * @throws Exception
     */
    function editServiceRequestHeader()
    {
        $this->setMethodName('editServiceRequestHeader');

        if ($this->getParam('reason')) {
            $this->updateSession('reason', $this->getParam('reason'));
        }


        $error = [];
        /* validate if this is a POST request */
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $this->updateSession('reason', $this->getParam('reason'));
            $this->updateSession('authorisedBy', $this->getParam('authorisedBy'));

            if ($this->getParam('hideFromCustomerFlag')) {
                $this->updateSession(
                    'hideFromCustomerFlag',
                    $this->getParam('hideFromCustomerFlag')
                );
            } else {
                $this->updateSession('hideFromCustomerFlag', 'N');
            }

            $this->updateSession('criticalSRFlag', $this->getParam('criticalSRFlag') ? 'Y' : 'N');
            $this->updateSession('monitorSRFlag', $this->getParam('monitorSRFlag') ? 'Y' : 'N');

            $this->updateSession('internalNotes', $this->getParam('internalNotes'));

            $this->updateSession('callActTypeID', CONFIG_INITIAL_ACTIVITY_TYPE_ID);

            $this->updateSession('customerID', $this->getParam('customerID'));
            if ($this->getParam('pendingReopenedID'))
                $this->updateSession('pendingReopenedID', $this->getParam('pendingReopenedID'));
            if ($this->getParam('deletePending'))
                $this->updateSession('deletePending', $this->getParam('deletePending'));


            /*
        Check nothing in fields that don't allow content
        */
            if (!trim($this->getParam('reason'))) {
                $error['reason'] = 'Please enter the details';
            }

            if ($this->getParam('siteNo') == 99) {
                $error['siteNo'] = 'Required';
            } else {
                $this->updateSession('siteNo', $this->getParam('siteNo'));
            }

            $this->updateSession('completeDate', null);

            $isAddToQueue = false;
            if ($this->getParam("hdQ")) {
                $this->updateSession('queueNo', 1);
                $isAddToQueue = true;
            }
            if ($this->getParam("escQ")) {
                $this->updateSession('queueNo', 2);
                $isAddToQueue = true;
            }
            if ($this->getParam("smallProjectsQueue")) {
                $this->updateSession('queueNo', 3);
                $isAddToQueue = true;
            }
            if ($this->getParam('salesQ')) {
                $this->updateSession('queueNo', 4);
                $isAddToQueue = true;
            }
            if ($this->getParam("projectsQueue")) {
                $this->updateSession('queueNo', 5);
                $isAddToQueue = true;
            }

            $this->updateSession('date', date(DATE_MYSQL_DATE));
            $this->updateSession('startTime', date('H:i'));

            $this->updateSession('dateRaised', date(DATE_MYSQL_DATE));
            $this->updateSession('timeRaised', date('H:i'));

            if (!$_SESSION[$this->sessionKey]['priority'] = $this->getParam('priority')) {
                $error['priority'] = 'Required';
            }

            if (count($error) == 0) {
                $pendingReopenedID = $_SESSION[$this->sessionKey]['pendingReopenedID'];
                $deletePending = $_SESSION[$this->sessionKey]['deletePending'];
                //$this->console_log($pendingReopenedID);
                /* Create initial activity */
                $dsCallActivity = $this->buActivity->createActivityFromSession($this->sessionKey);
                if (isset($dsCallActivity) && isset($pendingReopenedID)
                    && isset($deletePending) && $deletePending == 'true') {
                    //delete pending
                    $dbePendingReopened = new DBEPendingReopened($this);
                    $dbePendingReopened->deleteRow($pendingReopenedID);
                }
                /*
          Upload file
          */

                $this->handleUploads($dsCallActivity->getValue(DBEJCallActivity::problemID));

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
                if ($this->getParam('StartWork')) {
                    $nextURL =
                        Controller::buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'           => 'createFollowOnActivity',
                                'callActivityID'   => $dsCallActivity->getValue(DBEJCallActivity::callActivityID),
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

        if ($this->getParam('customerID')) {
            $this->updateSession('customerID', $this->getParam('customerID'));
        }

        if (!isset($_SESSION[$this->sessionKey]['siteNo'])) {
            $this->updateSession('siteNo', 99);
        }

        $this->setTemplateFiles(
            array(
                'ActivityCreate6'      => 'ActivityCreate6.inc',
                'ActivityWizardHeader' => 'ActivityWizardHeader.inc'
            )
        );

// Parameters
        $this->setPageTitle("Record " . CONFIG_SERVICE_REQUEST_DESC . " Details");

        $this->updateSession('callActTypeID', CONFIG_INITIAL_ACTIVITY_TYPE_ID);

        $session = $this->getSessionParam($this->sessionKey);

        /* initialise */
        if (!isset($session['dateRaised'])) {
            $this->updateSession('dateRaised', date('d/m/Y'));
        }
        if (!isset($session['timeRaised'])) {
            $this->updateSession('timeRaised', date('H:i'));
        }

        if (!isset($session['hideFromCustomerFlag'])) {
            $this->updateSession('hideFromCustomerFlag', 'N');
        }


        $this->priorityDropdown(@$session['priority']);

        $this->siteDropdown(
            @$_SESSION[$this->sessionKey]['customerID'],
            @$_SESSION[$this->sessionKey]['siteNo'],
            'ActivityCreate6',
            'siteBlock'
        );

        $this->onlyMainAndSupervisorsDropdown(
            'ActivityCreate6',
            @$_SESSION[$this->sessionKey]['customerID'],
            @$_SESSION[$this->sessionKey]['contactID']
        );

        $this->contactDropdown(
            @$_SESSION[$this->sessionKey]['customerID'],
            @$_SESSION[$this->sessionKey]['contactID'],
            'ActivityCreate6'
        );


        $submitURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'editServiceRequestHeader'
                )
            );
        $disabled = null;

        if (!$this->hasPermissions(SUPERVISOR_PERMISSION)) {
            $disabled = CTCNC_HTML_DISABLED;
        }

        $backURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array('action' => 'activityCreate1')
            );

        $this->template->set_var(
            array(
                'callActivityID'              => @$_SESSION[$this->sessionKey]['callActivityID'],
                'customerID'                  => @$_SESSION[$this->sessionKey]['customerID'],
                'siteNoMessage'               => @$error['siteNo'],
                'reason'                      => @$_SESSION[$this->sessionKey]['reason'],
                'reasonMessage'               => @$error['reason'],
                'internalNotes'               => @$_SESSION[$this->sessionKey]['internalNotes'],
                'customerName'                => @$_SESSION[$this->sessionKey]['customerName'],
                'customerNameDisplayClass'
                                              => @$_SESSION[$this->sessionKey]['customerNameDisplayClass'],
                'renewalsLink'                => $this->getRenewalsLink(@$_SESSION[$this->sessionKey]['customerID']),
                'projectLink'                 => BUProject::getCurrentProjectLink(
                    @$_SESSION[$this->sessionKey]['customerID']
                ),
                'contractListPopupLink'       => $this->getContractListPopupLink(
                    @$_SESSION[$this->sessionKey]['customerID']
                ),
                'dateRaised'                  => Controller::dateYMDtoDMY(@$_SESSION[$this->sessionKey]['dateRaised']),
                'timeRaised'                  => @$_SESSION[$this->sessionKey]['timeRaised'],
                'dateMessage'                 => @$error['date'],
                'startTimeMessage'            => @$error['startTime'],
                'priorityMessage'             => @$error['priority'],
                'fileMessage'                 => @$error['file'],
                'contactNotes'                => @$_SESSION[$this->sessionKey]['contactNotes'],
                'techNotes'                   => @$_SESSION[$this->sessionKey]['techNotes'],
                'urlCustomer'                 => $this->getCustomerUrl(@$_SESSION[$this->sessionKey]['customerID']),
                'hideFromCustomerFlagChecked' => Controller::htmlChecked(
                    @$_SESSION[$this->sessionKey]['hideFromCustomerFlag']
                ),
                'passwordLink'                => $this->getPasswordLink(@$_SESSION[$this->sessionKey]['customerID']),
                'thirdPartyContactLink'       => $this->getThirdPartyContactLink(
                    @ $_SESSION[$this->sessionKey]['customerID']
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

    private function handleUploads($problemID)
    {
        $fileCount = count($_FILES['userfile']['name']);
        $hasError = false;
        for ($i = 0; $i < $fileCount; $i++) {
            if (!is_uploaded_file($_FILES['userfile']['tmp_name'][$i])) {
                $hasError = true;
                continue;
            }

            $file = [
                'tmp_name' => $_FILES['userfile']['tmp_name'][$i],
                'size'     => $_FILES['userfile']['size'][$i],
                'name'     => $_FILES['userfile']['name'][$i],
                'type'     => $_FILES['userfile']['type'][$i]
            ];
            $this->buActivity->uploadDocumentFile(
                $problemID,
                $file['name'],
                $file
            );
        }

        return !$hasError;
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
                $siteSelected = ($siteNo == $dbeSite->getValue(DBESite::siteNo)) ? CT_SELECTED : null;
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

        $lastSiteNo = null;
        while ($dbeContact->fetchNext()) {
            $contactSelected = ($contactID == $dbeContact->getValue(DBEContact::contactID)) ? CT_SELECTED : null;

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
                $dbeContact->getValue(DBEContact::customerID)
            );
            $dbeSite->setValue(
                DBESite::siteNo,
                $dbeContact->getValue(DBEContact::siteNo)
            );
            $dbeSite->getRow();

            $name = $dbeContact->getValue(DBEContact::firstName) . ' ' . $dbeContact->getValue(DBEContact::lastName);

            if ($dbeContact->getValue(DBEContact::position)) {
                $name .= ' (' . $dbeContact->getValue(DBEContact::position) . ')';
            }

            $optGroupOpen = null;
            if ($dbeContact->getValue(DBEContact::siteNo) != $lastSiteNo) {
                $optGroupOpen = '<optgroup label="' . $dbeSite->getValue(DBESite::add1) . ' ' . $dbeSite->getValue(
                        DBESite::town
                    ) . ' ' . $dbeSite->getValue(DBESite::postcode) . '">';
            }
            $lastSiteNo = $dbeContact->getValue(DBEContact::siteNo);

            $this->template->set_var(
                array(
                    'contactSelected'       => $contactSelected,
                    'contactID'             => $dbeContact->getValue(DBEContact::contactID),
                    'contactName'           => $name,
                    'startMainContactStyle' => $startMainContactStyle,
                    'endMainContactStyle'   => $endMainContactStyle,
                    'optGroupOpen'          => $optGroupOpen
                )
            );
            $this->template->parse(
                'contactsOnlyMainAndSupervisor',
                'contactOnlyMainAndSupervisorsBlock',
                true
            );
        }
    }


    /**
     * @param $customerID
     * @param $contactID
     * @param string $templateName
     */
    function contactDropdown($customerID,
                             $contactID,
                             $templateName = 'ActivityEdit'
    )
    {
        $dbeContact = new DBEContact($this);
        $dbeSite = new DBESite($this);
        $dbeContact->getRowsByCustomerID(
            $customerID,
            false,
            true,
            true
        );

        $this->template->set_block(
            $templateName,
            'contactBlock',
            'contacts'
        );

        $lastSiteNo = null;

        while ($dbeContact->fetchNext()) {
            $dataDelegate = "";
            $contactSelected = ($contactID == $dbeContact->getValue(DBEContact::contactID)) ? CT_SELECTED : null;
            $startMainContactStyle = null;
            $endMainContactStyle = null;

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
            }

            $dbeSite->setValue(
                DBESite::customerID,
                $dbeContact->getValue(DBEContact::customerID)
            );
            $dbeSite->setValue(
                DBESite::siteNo,
                $dbeContact->getValue(DBEContact::siteNo)
            );
            $dbeSite->getRow();

            $name = $dbeContact->getValue(DBEContact::firstName) . ' ' . $dbeContact->getValue(DBEContact::lastName);

            if ($dbeContact->getValue(DBEContact::position)) {
                $name .= ' (' . $dbeContact->getValue(DBEContact::position) . ')';
            }
            $optGroupOpen = null;

            if ($dbeContact->getValue(DBEContact::siteNo) != $lastSiteNo) {
                $optGroupOpen = '<optgroup label="' . $dbeSite->getValue(DBESite::add1) . ' ' . $dbeSite->getValue(
                        DBESite::town
                    ) . ' ' . $dbeSite->getValue(DBESite::postcode) . '">';
            }
            $lastSiteNo = $dbeContact->getValue(DBEContact::siteNo);

            $this->template->set_var(
                array(
                    'contactSelected'       => $contactSelected,
                    'contactID'             => $dbeContact->getValue(DBEContact::contactID),
                    'contactName'           => $name,
                    'startMainContactStyle' => $startMainContactStyle,
                    'endMainContactStyle'   => $endMainContactStyle,
                    'optGroupOpen'          => $optGroupOpen,
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

    /**
     * @throws Exception
     */
    function editLinkedSalesOrder()
    {
        $this->setMethodName('editLinkedSalesOrder');

        $this->setPageTitle('Linked Sales Order');
        $errorMessage = null;
        $callActivityID = $this->getParam('callActivityID');
        $linkedOrderID = null;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($_POST['linkedOrderID']) {

                $linkedOrderID = $_POST['linkedOrderID'];
                $callActivityID = $_POST['callActivityID'];

                $dbeSalesOrder = new DBEOrdhead($this);
                if ($dbeSalesOrder->getRow($linkedOrderID)) {
                    $dsActivity = new DataSet($this);
                    $this->buActivity->getActivityByID(
                        $callActivityID,
                        $dsActivity
                    );

                    if ($dsActivity->getValue(DBEJCallActivity::customerID) != $dbeSalesOrder->getValue(
                            DBEJOrdhead::customerID
                        )) {
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

    /**
     * @throws Exception
     */
    function problemHistoryPopup()
    {
        $this->setTemplateFiles(
            'ActivityReasonPopup',
            'ActivityReasonPopup.inc'
        );
        $problemId = $this->getParam('problemID');
        $activitiesByProblemID = $this->buActivity->getActivitiesByProblemID($problemId);

        $dbeProblem = new DBEJProblem($this);
        $dbeProblem->getRow($problemId);

        $dbeJContract = new DBEJContract($this);
        $title = $problemId . ' - ' . $dbeProblem->getValue(DBEJProblem::customerName);


        $this->template->set_block(
            'ActivityReasonPopup',
            'activityBlock',
            'rows'
        );
        $foundFirst = false;
        $lastActivityID = null;
        while ($activitiesByProblemID->fetchNext()) {


            $activityHiddenText = null;
            if ($activitiesByProblemID->getValue(DBEJCallActivity::hideFromCustomerFlag) == 'Y') {
                $activityHiddenText = 'Hidden From Customer';
            }

            $dbeContact = new DBEContact($this);
            $dbeContact->getRow($activitiesByProblemID->getValue(DBEJCallActivity::contactID));
            $siteNo = $dbeContact->getValue(DBEContact::siteNo);

            $buSite = new BUSite($this);
            $dsSite = new DataSet($this);
            $buSite->getSiteByID($dbeProblem->getValue(DBEProblem::customerID), $siteNo, $dsSite);

            $dateValue = $activitiesByProblemID->getValue(DBEJCallActivity::date);
            $date = null;
            if ($dateValue) {
                $dateTime = DateTime::createFromFormat(DATE_MYSQL_DATE, $dateValue);
                if ($dateTime) {
                    $date = $dateTime->format('d-m-Y');
                }
            }
            $startTime = $activitiesByProblemID->getValue(DBEJCallActivity::startTime);
            $endTime = $activitiesByProblemID->getValue(DBEJCallActivity::endTime);
            $duration = number_format($activitiesByProblemID->getValue(DBEJCallActivity::durationMinutes) / 60, 2);
            $activityType = $activitiesByProblemID->getValue(DBEJCallActivity::activityType);
            $contactName = $activitiesByProblemID->getValue(DBEJCallActivity::contactName);
            $siteAddress = $dsSite->getValue(DBESite::add1);
            $userName = $activitiesByProblemID->getValue(DBEJCallActivity::userName);

            $reason = $activitiesByProblemID->getValue(DBEJCallActivity::reason);
            $originalRequestHeader = null;
            $colorClass = "boring-gray";
            if (!$foundFirst) {
                $originalRequestHeader = ' <tr>        <td class="redText">Original Request</td>    </tr>';
                $colorClass = "performance-green";
                $foundFirst = true;
            }
            $this->template->set_var(
                array(
                    'reason'                => $reason,
                    'date'                  => $date,
                    'startTime'             => $startTime,
                    'endTime'               => $endTime,
                    'activityType'          => $activityType,
                    'contactName'           => $contactName,
                    'duration'              => $duration,
                    'userName'              => $userName,
                    'activityHiddenText'    => $activityHiddenText,
                    'siteAddress'           => $siteAddress,
                    'originalRequestHeader' => $originalRequestHeader,
                    'colorClass'            => $colorClass
                )
            );
            if (!in_array($activitiesByProblemID->getValue(DBECallActivity::callActTypeID), [60, 61])) {
                $lastActivityID = $activitiesByProblemID->getValue(DBECallActivity::callActivityID);
                $lastActivityText = "$date $startTime - $endTime ($duration) $activityType - $contactName - $siteAddress - $userName";

                $lastActivityReason = $reason;
            }
            $this->template->parse(
                'rows',
                'activityBlock',
                true
            );
        }
        $url = Controller::buildLink(
            'Activity.php',
            array(
                'action'         => 'displayActivity',
                'callActivityID' => $lastActivityID,
            )
        );
        $link = "<a href='" . $url . "' target='_blank'>$title</a>";
        $this->setPageTitle($title, $link);
        if ($activitiesByProblemID->getValue(DBEJCallActivity::contractCustomerItemID)) {
            $dbeJContract->getRowByContractID(
                $activitiesByProblemID->getValue(DBEJCallActivity::contractCustomerItemID)
            );

            $contractDescription =
                $dbeJContract->getValue(DBEJContract::itemDescription) . ' ' . $dbeJContract->getValue(
                    DBEJContract::adslPhone
                ) . $dbeJContract->getValue(DBEJContract::postcode);
        } else {
            $contractDescription = 'No contract selected';
        }


        $problemHiddenText = null;
        if ($dbeProblem->getValue(DBEJProblem::hideFromCustomerFlag) == 'Y') {
            $problemHiddenText = 'Entire SR Hidden From Customer';
        }

        $this->template->set_var(
            array(
                'internalNotes'       => $dbeProblem->getValue(DBEJProblem::internalNotes),
                'contractDescription' => $contractDescription,
                'problemHiddenText'   => $problemHiddenText,
                'lastActivityText'    => $lastActivityText,
                'lastActivityReason'  => $lastActivityReason,
            )
        );

        $this->template->parse(
            'CONTENTS',
            'ActivityReasonPopup',
            true
        );

        $this->parsePage();

        exit;
    }  // end finaliseProblem

    /**
     * @throws Exception
     */
    function customerProblemPopup()
    {
        $this->setTemplateFiles(
            'ActivityCustomerProblemPopup',
            'ActivityCustomerProblemPopup.inc'
        );

        $record = $this->buActivity->getCustomerRaisedRequest($this->getParam('customerProblemID'));

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
     * @throws Exception
     */
    function editActivity()
    {
        $this->setMethodName('editActivity');
        $setTimeNowLink = null;
        $calendarLinkDate = null;
        $dsCallActivity = &$this->dsCallActivity; // ref to class var

        if ($this->getAction() == CTACTIVITY_ACT_CREATE_ACTIVITY || $this->getAction(
            ) == CTACTIVITY_ACT_CREATE_RESOLVED_ACTIVITY) {

            if (!$this->getFormError()) {

                if ($this->getAction() == CTACTIVITY_ACT_CREATE_ACTIVITY) {

                    $this->buActivity->initialiseCallActivity(
                        $this->getParam('customerID'),
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
                    $this->getParam('callActivityID'),
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

            $callActivityID = $dsCallActivity->getValue(DBEJCallActivity::callActivityID);
        }

        if ($this->buActivity->checkActivityEdition(
                $dsCallActivity,
                $this
            ) !== 'ALL_GOOD') {
            $this->raiseError('No permissions to edit this activity');
        }
        $disabled = CTCNC_HTML_DISABLED;
        $calendarLinkDate = null;
        if ($this->hasPermissions(SUPERVISOR_PERMISSION)) {
            $disabled = null;
            if ($dsCallActivity->getValue(DBEJCallActivity::callActTypeID) != CONFIG_INITIAL_ACTIVITY_TYPE_ID) {
                /** @noinspection CheckImageSize */
                /** @noinspection HtmlDeprecatedAttribute */
                $setTimeNowLink = '<a href="javascript:;"  onclick="setServerTime(endTime);"><img src="images/clock.gif" alt="Clock" width="24" height="22" hspace="0" vspace="0" border="0" style="margin: auto" title="Set end time now" /></a>';
            }
        }
        /*
      Only enable the complete date and autocomplete checkbox if Fixed
      */
        $complete_disabled = null;
        if ($dsCallActivity->getValue(DBEJCallActivity::problemStatus) != 'F') {
            $complete_disabled = CTCNC_HTML_DISABLED;
        }

        $priority_disabled = null;
        if (!$this->canChangeSrPriority()) {
            $priority_disabled = CTCNC_HTML_DISABLED;
        }
        /*
      Contract can only be changed by member of Accounts group
      */
        $contract_disabled = null;
        if ($this->dbeUser->getValue(DBEUser::changeSRContractsFlag) != 'Y') {
            $contract_disabled = CTCNC_HTML_DISABLED;
        }

        /*
      Only enable the date and time if not initial activity type
      */
        $initial_disabled = null;
        $canChangeInitialDateAndTime = true;
        $hiddenActivityType = null;
        if (
        in_array(
            $dsCallActivity->getValue(DBEJCallActivity::callActTypeID),
            array(
                CONFIG_INITIAL_ACTIVITY_TYPE_ID,
                CONFIG_CHANGE_REQUEST_ACTIVITY_TYPE_ID
            )
        )
        ) {
            if (!$this->hasPermissions(MAINTENANCE_PERMISSION)) {
                $initial_disabled = CTCNC_HTML_DISABLED;
                $hiddenActivityType = " <input type=\"hidden\" name=\"callActivity[1][callActTypeID]\" value=\"" . $dsCallActivity->getValue(
                        DBEJCallActivity::callActTypeID
                    ) . "\">";
            }


            if ($this->dbeUser->getValue(DBEUser::changeInitialDateAndTimeFlag) == 'Y') {
                $canChangeInitialDateAndTime = true;
            }

        }

        $this->setPageTitle(CONFIG_SERVICE_REQUEST_DESC . ' ' . $dsCallActivity->getValue(DBEJCallActivity::problemID));

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
        $dsCustomer = new DataSet($this);
        $buCustomer->getCustomerByID(
            $dsCallActivity->getValue(DBEJCallActivity::customerID),
            $dsCustomer
        );
        $dsCustomer->fetchNext();
        $customerDetails = null;
        if ($dsCallActivity->getValue(DBEJCallActivity::contactID) && $dsCallActivity->getValue(
                DBEJCallActivity::siteNo
            ) !== null) {

            $dsSite = new DataSet($this);
            $buCustomer->getSiteByCustomerIDSiteNo(
                $dsCallActivity->getValue(DBEJCallActivity::customerID),
                $dsCallActivity->getValue(DBEJCallActivity::siteNo),
                $dsSite
            );
            $dsContact = new DataSet($this);
            $buCustomer->getContactByID(
                $dsCallActivity->getValue(DBEJCallActivity::contactID),
                $dsContact
            );

            $customerDetails =
                $dsCustomer->getValue(DBECustomer::name) .
                ', ' . $dsSite->getValue(DBESite::add1) .
                ', ' . $dsSite->getValue(DBESite::add2) .
                ', ' . $dsSite->getValue(DBESite::add3) .
                ', ' . $dsSite->getValue(DBESite::town) .
                ', ' . $dsSite->getValue(DBESite::postcode) .
                ', ' . $dsContact->getValue(DBEContact::firstName) . ' ' . $dsContact->getValue(
                    DBEContact::lastName
                ) . ', <span class="contactPhone">' . $buCustomer->getContactPhoneForHtml(
                    $dsCallActivity->getValue(DBEJCallActivity::contactID),
                    "Service Request {$dsCallActivity->getValue(DBEJCallActivity::problemID)}"
                ) . '</span>';

            if ($dsContact->getValue(DBEContact::notes)) {
                $dsCallActivity->setValue(
                    DBEJCallActivity::contactNotes,
                    $dsContact->getValue(DBEContact::notes)
                );
            }
            if ($dsCustomer->getValue(DBECustomer::techNotes)) {
                $dsCallActivity->setValue(
                    DBEJCallActivity::techNotes,
                    $dsCustomer->getValue(DBECustomer::techNotes)
                );
            }
        }

        $renewalsLink = $this->getRenewalsLink($dsCallActivity->getValue(DBEJCallActivity::customerID));


        $dbeProblem = new DBEProblem($this);

        $dbeProblem->getRow($dsCallActivity->getValue(DBECallActivity::problemID));

        $hideFromCustomerFlag = $dsCallActivity->getValue(DBEJCallActivity::hideFromCustomerFlag);
        $hideFromCustomerDisabled = null;
        if ($dbeProblem->getValue(DBEProblem::hideFromCustomerFlag) == 'Y' || $dsCallActivity->getValue(
                DBEJCallActivity::problemHideFromCustomerFlag
            ) == 'Y') {
            $hideFromCustomerFlag = 'Y';
            $hideFromCustomerDisabled = CTCNC_HTML_DISABLED;
        }

        $userID = $dsCallActivity->getValue(DBEJCallActivity::allocatedUserID);

        $level = $this->buActivity->getLevelByUserID($userID);


        if ($dsCallActivity->getValue(DBEJCallActivity::onSiteFlag) == 'Y') {
            $onSiteFlag = 'Y';
        } else {
            $onSiteFlag = 'N';
        }

        if (isset($_FILES['userfile']) && $_FILES['userfile']['name']) {
            $this->handleUploads($dsCallActivity->getValue(DBEJCallActivity::problemID));
        }

        $problemID = $dsCallActivity->getValue(DBEJCallActivity::problemID);
        $hdUsedMinutes = $this->buActivity->getHDTeamUsedTime($problemID);
        $esUsedMinutes = $this->buActivity->getESTeamUsedTime($problemID);
        $imUsedMinutes = $this->buActivity->getSPTeamUsedTime($problemID);
        $projectUsedMinutes = $this->buActivity->getUsedTimeForProblemAndTeam(
            $problemID,
            5
        );
        $hdUsedMinutesNotInclusive = $this->buActivity->getHDTeamUsedTime(
            $problemID,
            $callActivityID
        );
        $esUsedMinutesNotInclusive = $this->buActivity->getESTeamUsedTime(
            $problemID,
            $callActivityID
        );
        $imUsedMinutesNotInclusive = $this->buActivity->getSPTeamUsedTime(
            $problemID,
            $callActivityID
        );
        $projectUsedMinutesNotInclusive = $this->buActivity->getUsedTimeForProblemAndTeam(
            $problemID,
            5,
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
        $imAssignedMinutes = $dbeProblem->getValue(DBEProblem::smallProjectsTeamLimitMinutes);
        $projectTeamAssignedMinutes = $dbeProblem->getValue(DBEProblem::projectTeamLimitMinutes);


        $urlLinkedSalesOrder =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'         => 'editLinkedSalesOrder',
                    'htmlFmt'        => CT_HTML_FMT_POPUP,
                    'callActivityID' => $callActivityID
                )
            );
        $authorisedByName = null;
        if ((int)$dbeProblem->getValue(DBEProblem::authorisedBy)) {
            $dbeContact = new DBEContact($this);

            $dbeContact->getRow($dbeProblem->getValue(DBEProblem::authorisedBy));

            $authorisedByName = $dbeContact->getValue(DBEContact::firstName) . " " . $dbeContact->getValue(
                    DBEContact::lastName
                );
        }

        $sdManagerDisabled = CTCNC_HTML_DISABLED;
        if ($this->isSdManager() || $this->dsCallActivity->getValue(
                DBECallActivity::callActTypeID
            ) == 51) {
            $sdManagerDisabled = "";
        }


        $this->template->set_var(
            array(
                'level'                          => $level,
                'onSiteFlag'                     => $onSiteFlag,
                'allocatedUserID'                => $dsCallActivity->getValue(DBEJCallActivity::allocatedUserID),
                'reason'                         => $dsCallActivity->getValue(DBEJCallActivity::reason),
                'reasonMessage'                  => $dsCallActivity->getMessage(DBEJCallActivity::reason),
                'internalNotes'                  => $dsCallActivity->getValue(DBEJCallActivity::internalNotes),
                'rootCauseID'                    => $dsCallActivity->getValue(DBEJCallActivity::rootCauseID),
                'awaitingCustomerResponseFlag'   => $dsCallActivity->getValue(
                    DBECallActivity::awaitingCustomerResponseFlag
                ),
                'callActivityID'                 => $callActivityID,
                'problemStatus'                  => $dsCallActivity->getValue(DBEJCallActivity::problemStatus),
                'problemStatusMessage'           => $dsCallActivity->getMessage(DBEJCallActivity::problemStatus),
                'problemID'                      => $dsCallActivity->getValue(DBEJCallActivity::problemID),
                'customerID'                     => $dsCallActivity->getValue(DBEJCallActivity::customerID),
                'hiddenCallActTypeID'            => $dsCallActivity->getValue(DBEJCallActivity::callActTypeID),
                'hiddenPriority'                 => $dsCallActivity->getValue(DBEJCallActivity::priority),
                'hiddenContractCustomerItemID'   => $dsCallActivity->getValue(DBEJCallActivity::contractCustomerItemID),
                'hiddenActivityType'             => $hiddenActivityType,
                'customerDetails'                => $customerDetails,
                'SDManagerDisabled'              => $sdManagerDisabled,
                'contactPhone'                   => $buCustomer->getContactPhoneForHtml(
                    $dsCallActivity->getValue(DBEJCallActivity::contactID),
                    "Service Request {$problemID}"
                ),
                'expenseExportFlag'              => $dsCallActivity->getValue(DBEJCallActivity::expenseExportFlag),
                'customerName'                   => Controller::htmlDisplayText(
                    $dsCallActivity->getValue(DBEJCallActivity::customerName)
                ),
                'customerNameDisplayClass'       => $this->getCustomerNameDisplayClass($dsCustomer),
                'urlCustomer'                    => $this->getCustomerUrl(
                    $dsCallActivity->getValue(DBEJCallActivity::customerID)
                ),
                'date'                           => $dsCallActivity->getValue(DBEJCallActivity::date),
                'dateMessage'                    => $dsCallActivity->getMessage(DBEJCallActivity::date),
                'curValue'                       => $dsCallActivity->getValue(DBEJCallActivity::curValue),
                'startTime'                      => $dsCallActivity->getValue(DBEJCallActivity::startTime),
                'startTimeMessage'               => $dsCallActivity->getMessage(DBEJCallActivity::startTime),
                'endTime'                        => $dsCallActivity->getValue(DBEJCallActivity::endTime),
                'endTimeMessage'                 => $dsCallActivity->getMessage(DBEJCallActivity::endTime),
                'internalNotesMessage'           => $dsCallActivity->getMessage(DBEJCallActivity::internalNotes),
                'siteDesc'                       => Controller::htmlInputText(
                    $dsCallActivity->getValue(DBEJCallActivity::siteDesc)
                ),
                'siteNoMessage'                  => Controller::htmlDisplayText(
                    $dsCallActivity->getMessage(DBEJCallActivity::siteNo)
                ),
                'status'                         => $dsCallActivity->getValue(DBEJCallActivity::status),
                'contactNotes'                   => $dsCallActivity->getValue(DBEJCallActivity::contactNotes),
                'techNotes'                      => $dsCallActivity->getValue(DBEJCallActivity::techNotes),
                'userIDMessage'                  => Controller::htmlDisplayText(
                    $dsCallActivity->getMessage(DBEJCallActivity::userID)
                ),
                'callActTypeIDMessage'           => Controller::htmlDisplayText(
                    $dsCallActivity->getMessage(DBEJCallActivity::callActTypeID)
                ),
                'urlDisplayActivity'             => $urlDisplayActivity,
                'urlCancelEdit'                  => $urlCancelEdit,
                'urlUpdateActivity'              => $urlUpdateActivity,
                'renewalsLink'                   => $renewalsLink,
                'passwordLink'                   => $this->getPasswordLink(
                    $dsCallActivity->getValue(DBEJCallActivity::customerID)
                ),
                'thirdPartyContactLink'          => $this->getThirdPartyContactLink(
                    $dsCallActivity->getValue(DBEJCallActivity::customerID)
                ),
                'contactHistoryLink'             => $this->getServiceRequestForContactLink(
                    $dsCallActivity->getValue(DBECallActivity::contactID)
                ),
                'generatePasswordLink'           => $this->getGeneratePasswordLink(),
                'salesOrderLink'                 => $this->getSalesOrderLink(
                    $dsCallActivity->getValue(DBEJCallActivity::linkedSalesOrderID),
                    $dsCallActivity->getValue(DBEJCallActivity::callActivityID)
                ),
                'urlLinkedSalesOrder'            => $urlLinkedSalesOrder,
                'problemHistoryLink'             => $this->getProblemHistoryLink(
                    $dsCallActivity->getValue(DBEJCallActivity::problemID)
                ),
                'projectLink'                    => BUProject::getCurrentProjectLink(
                    $dsCallActivity->getValue(DBEJCallActivity::customerID)
                ),
                'contractListPopupLink'          => $this->getContractListPopupLink(
                    $dsCallActivity->getValue(DBEJCallActivity::customerID)
                ),
                'DISABLED'                       => $disabled,
                'COMPLETE_DISABLED'              => $complete_disabled,
                'INITIAL_DISABLED'               => $initial_disabled,
                'INITIAL_DATE_DISABLED'          => $canChangeInitialDateAndTime ? null : "disabled",
                'PRIORITY_DISABLED'              => $priority_disabled,
                'CONTRACT_DISABLED'              => $contract_disabled,
                'setTimeNowLink'                 => $setTimeNowLink,
                'calendarLinkDate'               => $calendarLinkDate,
                'completeDate'                   => $dsCallActivity->getValue(DBEJCallActivity::completeDate),
                'contactIDMessage'               => Controller::htmlDisplayText(
                    $dsCallActivity->getMessage(DBEJCallActivity::contactID)
                ),
                'alarmDate'                      => $dsCallActivity->getValue(DBEJCallActivity::alarmDate),
                'alarmTime'                      => $dsCallActivity->getValue(
                    DBEJCallActivity::alarmTime
                ) != '00:00:00' ? $dsCallActivity->getValue(DBEJCallActivity::alarmTime) : null,
                'alarmDateMessage'               => Controller::htmlDisplayText(
                    $dsCallActivity->getMessage(DBEJCallActivity::alarmDate)
                ),
                'alarmTimeMessage'               => Controller::htmlDisplayText(
                    $dsCallActivity->getMessage(DBEJCallActivity::alarmTime)
                ),
                'hideFromCustomerFlagChecked'    => Controller::htmlChecked($hideFromCustomerFlag),
                'hideFromCustomerDisabled'       => $hideFromCustomerDisabled,
                'submitAsOvertimeChecked'        => $dsCallActivity->getValue(
                    DBECallActivity::submitAsOvertime
                ) ? 'checked' : null,
                'overtimeExportedFlag'           => $dsCallActivity->getValue(DBECallActivity::overtimeExportedFlag),
                'hdRemainMinutes'                => $hdAssignedMinutes - $hdUsedMinutes,
                'esRemainMinutes'                => $esAssignedMinutes - $esUsedMinutes,
                'imRemainMinutes'                => $imAssignedMinutes - $imUsedMinutes,
                'projectRemainMinutes'           => $projectTeamAssignedMinutes - $projectUsedMinutes,
                'hdUsedMinutesNotInclusive'      => $hdUsedMinutesNotInclusive,
                'esUsedMinutesNotInclusive'      => $esUsedMinutesNotInclusive,
                'imUsedMinutesNotInclusive'      => $imUsedMinutesNotInclusive,
                'projectUsedMinutesNotInclusive' => $projectUsedMinutesNotInclusive,
                'hdAssignedMinutes'              => $hdAssignedMinutes,
                'hdUsedMinutes'                  => $hdUsedMinutes,
                'esAssignedMinutes'              => $esAssignedMinutes,
                'esUsedMinutes'                  => $esUsedMinutes,
                'imAssignedMinutes'              => $imAssignedMinutes,
                'imUsedMinutes'                  => $imUsedMinutes,
                'userWarned'                     => $this->userWarned,
                'authoriseHide'                  => $authorisedByName ? null : 'hidden',
                'authorisedByName'               => $authorisedByName,
                'salesRequestStatus'             => $dsCallActivity->getValue(DBECallActivity::salesRequestStatus)
            )
        );

        $this->documents(
            $callActivityID,
            $dsCallActivity->getValue(DBEJCallActivity::problemID),
            'ActivityEdit'
        );

        $this->rootCauseDropdown(
            $dsCallActivity->getValue(DBEJCallActivity::rootCauseID),
            'ActivityEdit',
            'rootCauseBlock'
        );

        $this->activityTypeDropdown($dsCallActivity->getValue(DBEJCallActivity::callActTypeID));

        $this->priorityDropdown(
            $dsCallActivity->getValue(DBEJCallActivity::priority),
            'ActivityEdit'
        );


        if ($dsCallActivity->getValue(DBEJCallActivity::siteNo) !== null) {
            $this->contactDropdown(
                $dsCallActivity->getValue(DBEJCallActivity::customerID),
                $dsCallActivity->getValue(DBEJCallActivity::contactID),
                'ActivityEdit'
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
            $userSelected = CT_SELECTED;
            if ($dsCallActivity->getValue(DBEJCallActivity::userID) != $dbeUser->getValue(DBEUser::userID)) {
                $userSelected = null;
                if ($dbeUser->getValue(DBEUser::activeFlag) == 'N') {
                    continue;
                }
            }

            $this->template->set_var(
                array(
                    'userSelected' => $userSelected,
                    'userID'       => $dbeUser->getValue(DBEUser::userID),
                    'userName'     => $dbeUser->getValue(DBEUser::name)
                )
            );
            $this->template->parse(
                'users',
                'userBlock',
                true
            );
        }

        $this->siteDropdown(
            $dsCallActivity->getValue(DBEJCallActivity::customerID),
            $dsCallActivity->getValue(DBEJCallActivity::siteNo),
            'ActivityEdit',
            'siteBlock'
        );
        //Contract selection
        $this->contractDropdown(
            $dsCallActivity->getValue(DBEJCallActivity::customerID),
            $dsCallActivity->getValue(DBEJCallActivity::contractCustomerItemID),
            'ActivityEdit',
            'contractBlock',
            !!$dsCallActivity->getValue(DBEJCallActivity::linkedSalesOrderID)
        );
        /*
      Use initial activity date to determine list of projects
      */
        $dbeInitialActivity = $this->buActivity->getFirstActivityInProblem(
            $dsCallActivity->
            getValue('problemID')
        );

        $this->projectDropdown(
            $dsCallActivity->getValue(DBEJCallActivity::projectID),
            $dsCallActivity->getValue(DBEJCallActivity::customerID),
            'ActivityEdit',
            $dbeInitialActivity->getValue(DBEJCallActivity::date),
            'projectBlock'
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
    }

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

            $activityTypeSelected = ($callActTypeID == $dbeJCallActType->getValue(DBEJCallActType::callActTypeID)
            ) ? CT_SELECTED : null;

            if ($activityTypeSelected == CT_SELECTED) {
                $foundCurrent = true;
            }

            $this->template->set_var(
                array(
                    'activityTypeSelected' => $activityTypeSelected,
                    'callActTypeID'        => $dbeJCallActType->getValue(DBEJCallActType::callActTypeID),
                    'activityTypeDesc'     => $dbeJCallActType->getValue(DBEJCallActType::description),
                    'allowOvertime'        => $dbeJCallActType->getValue(
                        DBEJCallActType::engineerOvertimeFlag
                    ) == 'Y' ? 1 : 0,
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
                    'callActTypeID'        => $current->getValue(DBECallActType::callActTypeID),
                    'activityTypeDesc'     => $current->getValue(DBECallActType::description)
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
        $activityDate,
        $blockName = 'projectBlock'
    )
    {
        // Display list of projects that are current at given activity date
        $buProject = new BUProject($this);
        $dsProject = new DataSet($this);
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
            $projectSelected = ($dsProject->getValue(DBEProject::projectID) == $projectID) ? CT_SELECTED : null;

            $this->template->set_var(
                array(
                    'projectID'          => $dsProject->getValue(DBEProject::projectID),
                    'projectDescription' => $dsProject->getValue(DBEProject::description),
                    'projectSelected'    => $projectSelected,
                )
            );

            $this->template->parse(
                'projects',
                $blockName,
                true
            );


        }

        $projectPleaseSelect = ($projectID == 99) ? CT_SELECTED : null;

        $noProject = ($projectID == 0) ? CT_SELECTED : null;

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
     * @throws Exception
     */
    function deleteCallActivity()
    {
        $this->setMethodName('deleteCallActivity');
        $this->buActivity->getActivityByID(
            $this->getParam('callActivityID'),
            $dsCallActivity
        );
        $problemID = $this->buActivity->deleteCallActivity($this->getParam('callActivityID'));

        /*
      if the whole service requested has been removed then redirect to search page
      otherwise display problem
      */
        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => 'displayLastActivity',
                    'problemID' => $problemID
                )
            );
        if (!$problemID) {
            $urlNext =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTCNC_ACT_DISPLAY_SEARCH_FORM
                    )
                );
        }
        header('Location: ' . $urlNext);
    }

    /**
     * Cancel Edit/Add Activity
     *
     * Where there have been no details added, also delete the activity record and
     * return user to parent activity view (if a parent activity exists) otherwise
     * man activities page
     *
     * @access private
     * @throws Exception
     */
    function cancelEdit()
    {
        $this->setMethodName('cancelEdit');
        $dsCallActivity = new DataSet($this);
        if (!$this->buActivity->getActivityByID(
            $this->getParam('callActivityID'),
            $dsCallActivity
        )) {
            $this->raiseError('Activity ID ' . $this->getParam('callActivityID') . ' not found');
            exit;
        }

        if (in_array(
            $dsCallActivity->getValue(DBECallActivity::callActTypeID),
            [0, 59]
        )) {                    //delete this activity

            $this->buActivity->deleteCallActivity($this->getParam('callActivityID'));

            if ($dsCallActivity->getValue(DBEJCallActivity::problemID) != 0) {

                $dbeCallActivity = $this->buActivity->getLastActivityInProblem(
                    $dsCallActivity->getValue(DBEJCallActivity::problemID)
                );

                $this->redirectToDisplay($dbeCallActivity->getValue(DBEJCallActivity::callActivityID));

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
            $this->redirectToDisplay($this->getParam('callActivityID'));
        }
    }

    /**
     * Update call activity details
     * @access private
     * @throws Exception
     */
    function updateCallActivity()
    {
        $this->setMethodName('updateCallActivity');
        $callActivityID = $this->getParam('callActivity')[1]['callActivityID'];
        $this->buActivity->getActivityByID(
            $callActivityID,
            $this->dsCallActivity
        );
        $previousStartTime = $this->dsCallActivity->getValue(DBECallActivity::startTime);
        $previousEndTime = $this->dsCallActivity->getValue(DBECallActivity::endTime);
        $this->formError = (!$this->dsCallActivity->populateFromArray($this->getParam('callActivity')));

        $this->dsCallActivity->setUpdateModeUpdate();
        // these names must not be part of an html array as the fckeditor does not work
        $this->dsCallActivity->setValue(
            DBEJCallActivity::reason,
            $_POST['reason']
        );
        $this->dsCallActivity->setValue(
            DBEJCallActivity::internalNotes,
            $_POST['internalNotes']
        );
        $this->dsCallActivity->post();

        if (($previousStartTime != $this->dsCallActivity->getValue(DBECallActivity::startTime)
                || $previousEndTime != $this->dsCallActivity->getValue(DBECallActivity::endTime)
            ) && $this->dsCallActivity->getValue(DBECallActivity::overtimeExportedFlag) == 'N'
        ) {
            $this->dsCallActivity->setValue(DBECallActivity::overtimeDurationApproved, null);
            $this->dsCallActivity->setValue(DBECallActivity::overtimeApprovedDate, null);
            $this->dsCallActivity->setValue(DBECallActivity::overtimeApprovedBy, null);
        }

        $dbeCallActType = new DBEJCallActType($this);


        if (!$this->dsCallActivity->getValue(
            DBEJCallActivity::callActTypeID
        )) {

            $this->formError = true;
            $this->dsCallActivity->setMessage(
                DBEJCallActivity::callActTypeID,
                'Required'
            );
        } else {
            $dbeCallActType->getRow($this->dsCallActivity->getValue(DBEJCallActivity::callActTypeID));
            if ($this->dsCallActivity->getValue(DBEJCallActivity::siteNo) === null) {
                $this->formError = true;
                $this->dsCallActivity->setMessage(
                    DBEJCallActivity::siteNo,
                    'Required'
                );
            }

            if (!$this->dsCallActivity->getValue(DBEJCallActivity::contactID) || $this->dsCallActivity->getValue(
                    DBEJCallActivity::contactID
                ) == 0) {
                $this->formError = true;
                $this->dsCallActivity->setMessage(
                    DBEJCallActivity::contactID,
                    'Required'
                );
            } else {
                if ($this->buActivity->needsTravelHoursAdding(
                    $this->dsCallActivity->getValue(DBEJCallActivity::callActTypeID),
                    $this->dsCallActivity->getValue(DBEJCallActivity::customerID),
                    $this->dsCallActivity->getValue(DBEJCallActivity::siteNo)
                )) {
                    $this->formError = true;
                    $this->dsCallActivity->setMessage(
                        DBEJCallActivity::callActTypeID,
                        'Travel hours need entering for this site'
                    );
                }

                // is the selected contact a nominated support contact?
                $buCustomer = new BUCustomer($this);
                if (!$buCustomer->isASupportContact($this->dsCallActivity->getValue(DBEJCallActivity::contactID))) {
                    $this->formError = true;
                    $this->dsCallActivity->setMessage(
                        DBEJCallActivity::contactID,
                        'Not a nominated support contact'
                    );
                }
            }

            if ($dbeCallActType->getValue(DBECallActType::curValueFlag) == 'Y' && !$this->dsCallActivity->getValue(
                    DBEJCallActivity::curValue
                )) {
                $this->formError = true;
                $this->dsCallActivity->setMessage(
                    DBEJCallActivity::curValue,
                    'Required'
                );
            } else {  // if no end time set then set to time now
                if (
                    !$this->getParam('Update') &&
                    $dbeCallActType->getValue(DBECallActType::requireCheckFlag) == 'N' &&
                    $dbeCallActType->getValue(DBECallActType::onSiteFlag) == 'N' &&
                    !$this->dsCallActivity->getValue(DBEJCallActivity::endTime)
                ) {
                    $this->dsCallActivity->setValue(
                        DBEJCallActivity::endTime,
                        date('H:i')
                    );
                }
                // required fields
                if ($dbeCallActType->getValue(DBECallActType::reqReasonFlag) == 'Y' && !trim(
                        $this->dsCallActivity->getValue(DBEJCallActivity::reason)
                    )) {
                    $this->formError = true;
                    $this->dsCallActivity->setMessage(
                        DBEJCallActivity::reason,
                        'Required'
                    );
                }

                if ($dbeCallActType->getValue(DBECallActType::reqReasonFlag) == 'Y' && !trim(
                        $this->dsCallActivity->getValue(DBEJCallActivity::reason)
                    )) {
                    $this->formError = true;
                    $this->dsCallActivity->setMessage(
                        DBEJCallActivity::reason,
                        'Required'
                    );
                }


                if (
                    $this->dsCallActivity->getValue(DBEJCallActivity::contractCustomerItemID) &&
                    $this->dsCallActivity->getValue(DBEJCallActivity::projectID)
                ) {
                    $this->dsCallActivity->setMessage(
                        DBEJCallActivity::projectID,
                        'Project work must be logged under T&M'
                    );
                    $this->formError = true;
                }
                /*
          Date/time must be after Initial activity
          */
                if ($this->dsCallActivity->getValue(
                        DBEJCallActivity::callActTypeID
                    ) != CONFIG_INITIAL_ACTIVITY_TYPE_ID) {

                    $dbeFirstActivity = $this->buActivity->getFirstActivityInProblem(
                        $this->dsCallActivity->getValue(DBEJCallActivity::problemID)
                    );

                    if (
                        $this->dsCallActivity->getValue(DBEJCallActivity::date) . $this->dsCallActivity->getValue(
                            DBEJCallActivity::startTime
                        ) <
                        $dbeFirstActivity->getValue(DBEJCallActivity::date) . $dbeFirstActivity->getValue(
                            DBEJCallActivity::startTime
                        )
                    ) {
                        $this->formError = true;
                        $this->dsCallActivity->setMessage(
                            DBEJCallActivity::date,
                            'Date/time must be after Initial activity'
                        );
                    }
                }


                // Only require these if the activity is ended
                if ($this->dsCallActivity->getValue(DBEJCallActivity::endTime)) {

                    if ($this->dsCallActivity->getValue(DBEJCallActivity::endTime) < $this->dsCallActivity->getValue(
                            DBEJCallActivity::startTime
                        )) {
                        $this->formError = true;
                        $this->dsCallActivity->setMessage(
                            DBEJCallActivity::endTime,
                            'End time must be after start time!'
                        );
                    }

                    $durationHours = common_convertHHMMToDecimal(
                            $this->dsCallActivity->getValue(DBEJCallActivity::endTime)
                        ) - common_convertHHMMToDecimal($this->dsCallActivity->getValue(DBEJCallActivity::startTime));

                    $durationMinutes = convertHHMMToMinutes(
                            $this->dsCallActivity->getValue(DBEJCallActivity::endTime)
                        ) - convertHHMMToMinutes($this->dsCallActivity->getValue(DBEJCallActivity::startTime));


                    $activityType = $this->dsCallActivity->getValue(DBEJCallActivity::callActTypeID);

                    if (in_array(
                        $activityType,
                        [4, 8, 11, 18]
                    )) {
                        $problemID = $this->dsCallActivity->getValue(DBEJCallActivity::problemID);

                        $userID = $this->dsCallActivity->getValue(DBEJCallActivity::userID);
                        $dbeUser = new DBEUser($this);
                        $dbeUser->setValue(
                            DBEUser::userID,
                            $userID
                        );
                        $dbeUser->getRow();

                        $dbeProblem = new DBEProblem($this);
                        $dbeProblem->setValue(
                            DBEProblem::problemID,
                            $problemID
                        );
                        $dbeProblem->getRow();

                        $teamID = $dbeUser->getValue(DBEUser::teamID);

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
                                $usedTime = $this->buActivity->getSPTeamUsedTime(
                                    $problemID,
                                    $callActivityID
                                );
                                $allocatedTime = $dbeProblem->getValue(DBEProblem::smallProjectsTeamLimitMinutes);
                            }

                            if ($teamID == 5) {
                                $usedTime = $this->buActivity->getUsedTimeForProblemAndTeam(
                                    $problemID,
                                    5,
                                    $callActivityID
                                );
                                $allocatedTime = $dbeProblem->getValue(DBEProblem::projectTeamLimitMinutes);
                            }

                            if ($usedTime + $durationMinutes > $allocatedTime) {
                                $this->formError = true;
                                $this->dsCallActivity->setMessage(
                                    DBEJCallActivity::endTime,
                                    'You cannot assign more time than left over'
                                );
                            }
                        }

                        if (!$this->getParam('userWarned')) {

                            $buHeader = new BUHeader($this);
                            $dsHeader = new DataSet($this);
                            $buHeader->getHeader($dsHeader);


                            if (
                                $this->dsCallActivity->getValue(
                                    DBEJCallActivity::callActTypeID
                                ) == CONFIG_CUSTOMER_CONTACT_ACTIVITY_TYPE_ID &&
                                $durationHours > $dsHeader->getValue(DBEHeader::customerContactWarnHours)
                            ) {
                                $this->formError = true;
                                $this->userWarned = true;
                                $this->dsCallActivity->setMessage(
                                    DBEJCallActivity::endTime,
                                    'Warning: Duration exceeds ' . $dsHeader->getValue(
                                        DBEHeader::customerContactWarnHours
                                    ) . ' hours'
                                );

                            }

                            if ($this->dsCallActivity->getValue(
                                    DBEJCallActivity::callActTypeID
                                ) == CONFIG_REMOTE_TELEPHONE_ACTIVITY_TYPE_ID) {
                                if ($durationHours > $dsHeader->getValue(DBEHeader::remoteSupportWarnHours)) {
                                    $this->formError = true;
                                    $this->userWarned = true;
                                    $this->dsCallActivity->setMessage(
                                        DBEJCallActivity::endTime,
                                        'Warning: Activity duration exceeds ' . $dsHeader->getValue(
                                            DBEHeader::remoteSupportWarnHours
                                        ) . ' hours'
                                    );
                                }

                                $minHours = $dsHeader->getValue(DBEHeader::RemoteSupportMinWarnHours);

                                if ($durationHours < $minHours) {
                                    $this->formError = true;
                                    $this->userWarned = true;
                                    $this->dsCallActivity->setMessage(
                                        DBEJCallActivity::endTime,
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

            $problemID = $this->dsCallActivity->getValue(DBEJCallActivity::problemID);
            if ($this->getParam('problem') && isset(
                    $this->getParam(
                        'problem'
                    )[$problemID]
                ) && $this->getParam('problem')[$problemID]['authorisedBy']) {
                $dbeProblem = new DBEProblem($this);
                $dbeProblem->setValue(
                    DBEProblem::problemID,
                    $problemID
                );
                $dbeProblem->getRow();

                $dbeProblem->setValue(
                    DBEProblem::authorisedBy,
                    $this->getParam('problem')[$problemID]['authorisedBy']
                );
                $dbeProblem->updateRow();
            }


            if ($this->getParam('Fixed')) {
                //try to close all the activities

                $this->buActivity->closeActivitiesWithEndTime(
                    $this->dsCallActivity->getValue(DBEJCallActivity::problemID)
                );

                if ($this->buActivity->countOpenActivitiesInRequest(
                        $this->dsCallActivity->getValue(DBEJCallActivity::problemID),
                        $this->dsCallActivity->getValue(DBEJCallActivity::callActivityID)
                    ) > 0) {
                    $this->dsCallActivity->setMessage(
                        DBEJCallActivity::problemStatus,
                        'Can not fix, there are open activities on this request'
                    );
                    $this->formError = true;
                }
            }
        }

        if ($this->formError) {
            if ($this->getAction() == CTACTIVITY_ACT_INSERT_ACTIVITY) {
                $this->setParam('callActivityID', $callActivityID);
                $this->setAction(CTACTIVITY_ACT_CREATE_ACTIVITY);
            } else {
                $this->setAction(CTACTIVITY_ACT_EDIT_ACTIVITY);
            }
            $this->editActivity();
            exit;
        }

        /*
      Record action button selected
      */
        $this->dsCallActivity->setUpdateModeUpdate();
        $this->dsCallActivity->setValue(
            DBECallActivity::submitAsOvertime,
            isset($this->getParam('callActivity')[1]['submitAsOvertime'])
        );
        $this->dsCallActivity->post();
        $updateAwaitingCustomer = false;
        if ($this->getParam('Fixed')) {
            $nextStatus = 'Fixed';
        } elseif ($this->getParam('CustomerAction')) {
            $this->dsCallActivity->setUpdateModeUpdate();
            $this->dsCallActivity->setValue(
                DBEJCallActivity::awaitingCustomerResponseFlag,
                'Y'
            );
            $updateAwaitingCustomer = true;
            $this->dsCallActivity->post();
            $nextStatus = 'CustomerAction';
        } elseif ($this->getParam('CncAction')) {
            $this->dsCallActivity->setUpdateModeUpdate();
            $this->dsCallActivity->setValue(
                DBEJCallActivity::awaitingCustomerResponseFlag,
                'N'
            );
            $updateAwaitingCustomer = true;

            $this->dsCallActivity->post();
            $nextStatus = 'CncAction';
        } elseif ($this->getParam('Escalate')) {
            $dbeProblem = new DBEProblem($this);
            $dbeProblem->setValue(
                DBEProblem::problemID,
                $this->dsCallActivity->getValue(DBECallActivity::problemID)
            );
            $dbeProblem->getRow();
            if (!in_array($dbeProblem->getValue(DBEProblem::status), ["I", "F", "C"]) && !$this->getParam(
                    'escalationReason'
                )) {
                $this->formError = true;
                $this->formErrorMessage = 'Please provide an escalate reason';
                if ($this->getAction() == CTACTIVITY_ACT_INSERT_ACTIVITY) {
                    $this->setParam('callActivityID', $callActivityID);
                    $this->setAction(CTACTIVITY_ACT_CREATE_ACTIVITY);
                } else {
                    $this->setAction(CTACTIVITY_ACT_EDIT_ACTIVITY);
                }
                $this->editActivity();
                exit;
            }
            $nextStatus = 'Escalate';
            $this->buActivity->escalateProblemByCallActivityID($callActivityID, $this->getParam('escalationReason'));
        } else {
            $nextStatus = false;
        }


        if ($updateAwaitingCustomer) {
            $toUpdateProblem = new DBEProblem($this);
            $toUpdateProblem->getRow($this->dsCallActivity->getValue(DBECallActivity::problemID));
            $toUpdateProblem->setValue(
                DBEProblem::awaitingCustomerResponseFlag,
                $this->dsCallActivity->getValue(DBECallActivity::awaitingCustomerResponseFlag)
            );
            $toUpdateProblem->updateRow();
        }
        $enteredEndTime = $this->buActivity->updateCallActivity(
            $this->dsCallActivity
        );
        /*
      If an end time was entered and this is a chargeable on site activity then see whether to
      create a travel activity automatically OR if one exists for today prompt whether another should be
      added.
      */
        if (
            $enteredEndTime &&
            $dbeCallActType->getValue(DBECallActType::onSiteFlag) == 'Y' &&
            $dbeCallActType->getValue(DBEJCallActType::itemSalePrice) > 0
        ) {
            $dbeSite = new DBESite($this);
            $dbeSite->setValue(
                DBESite::customerID,
                $this->dsCallActivity->getValue(DBEJCallActivity::customerID)
            );
            $dbeSite->setValue(
                DBESite::siteNo,
                $this->dsCallActivity->getValue(DBEJCallActivity::siteNo)
            );
            $dbeSite->getRowByCustomerIDSiteNo();
            if (
                $this->buActivity->travelActivityForCustomerEngineerTodayExists(
                    $this->dsCallActivity->getValue(DBEJCallActivity::customerID),
                    $this->dsCallActivity->getValue(DBEJCallActivity::siteNo),
                    $this->dsCallActivity->getValue(DBEJCallActivity::userID),
                    $this->dsCallActivity->getValue(DBEJCallActivity::date)
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

        $this->redirectToDisplay($callActivityID);
        exit;
    }

    /**
     * Check Activity ready for export
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function checkActivity()
    {
        $this->setMethodName('checkActivity');
        $dsCallActivity = new DataSet($this);
        $this->buActivity->getActivityByID(
            $this->getParam('callActivityID'),
            $dsCallActivity
        );
        $this->buActivity->setActivityStatusChecked($this->getParam('callActivityID'));

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => CTACTIVITY_ACT_DISPLAY_ACTIVITY,
                    'callActivityID' => $dsCallActivity->getValue(DBEJCallActivity::callActivityID)
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
     * @throws Exception
     */
    function createFollowOnActivity()
    {

        $this->setMethodName('createFollowOnActivity');
        $newActivityID = $this->buActivity->createFollowOnActivity(
            $this->getParam('callActivityID'),
            $this->getParam('callActivityTypeID'),
            false,
            $this->getParam('reason'),
            true,
            false,
            $GLOBALS['auth']->is_authenticated(),
            $this->getParam('moveToUsersQueue')
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

    /**
     * @throws Exception
     */
    function createRequestFromCustomerRequest()
    {
        $customerProblemNo = $this->getParam('cpr_customerproblemno');

        $customerProblem = $this->buActivity->getCustomerRaisedRequest($customerProblemNo);

        if ($customerProblem['con_custno']) {
            $this->setParam('customerID', $customerProblem['con_custno']);
            $this->setParam('contactID', $customerProblem['cpr_contno']);

        }

        $this->setParam(
            'reason',
            '<p>' . str_replace(
                "\n",
                '<BR/>',
                $customerProblem['cpr_reason']
            ) . '</p>'
        );

        $this->activityCreate1(false);

        $this->buActivity->deleteCustomerRaisedRequest($customerProblemNo);

    }

    /**
     * @throws Exception
     */
    function updateRequestFromCustomerRequest()
    {
        $customerProblemNo = $this->getParam('cpr_customerproblemno');

        $customerProblem = $this->buActivity->getCustomerRaisedRequest($customerProblemNo);

        $dbeCallActivity = $this->buActivity->getLastActivityInProblem($customerProblem['cpr_problemno']);

        $callActivityID =
            $this->buActivity->createFollowOnActivity(
                $dbeCallActivity->getValue(DBEJCallActivity::callActivityID),
                CONFIG_CUSTOMER_CONTACT_ACTIVITY_TYPE_ID,
                $customerProblem['cpr_contno'],
                $customerProblem['cpr_reason'],
                false,
                true
            );

        $this->buActivity->deleteCustomerRaisedRequest($customerProblemNo);

        $this->redirectToDisplay($callActivityID);

    }

    /**
     * @throws Exception
     */
    function displayServiceRequest()
    {
        $dbeCallActivity = $this->buActivity->getLastActivityInProblem($this->getParam('problemID'));
        $this->redirectToDisplay($dbeCallActivity->getValue(DBEJCallActivity::callActivityID));

    }

    /**
     * @throws Exception
     */
    function setProblemFixed()
    {

        if (!$this->getParam('problemID')) {
            $this->raiseError('problemID not passed');
            exit;
        }
        $problemID = $this->getParam('problemID');
        if (!$this->getParam('callActivityID')) {
            $this->raiseError('callActivityID not passed');
            exit;
        }

        $dbeProblem = new DBEProblem($this);
        $dbeProblem->getRow($problemID);
        $this->buActivity->setProblemToFixed(
            $problemID,
            USER_SYSTEM,
            $dbeProblem->getValue(DBEProblem::contractCustomerItemID),
            $dbeProblem->getValue(DBEProblem::rootCauseID),
            ""
        );

        $this->redirectToDisplay($this->getParam('callActivityID'));

        exit;
    }

    /**
     * Customer has confirmed this Service Request is completed
     *
     * @throws Exception
     */
    function setProblemComplete()
    {

        if (!$this->getParam('problemID')) {
            $this->raiseError('problemID not passed');
            exit;
        }

        $newActivityID = $this->buActivity->setProblemToCompleted($this->getParam('problemID'));

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

    /**
     * @throws Exception
     */
    function linkProblems()
    {
        if (!$this->getParam('toCallActivityID')) {
            $this->raiseError('No toCallActivityID Passed');
            exit;
        }
        if (!$this->getParam('fromCallActivityID')) {
            $this->raiseError('No fromCallActivityID Passed');
            exit;
        }

        if ($this->getParam('linkWholeProblem')) {
            $wholeProblem = true;
        } else {
            $wholeProblem = false;
        }

        if (
        $this->buActivity->linkActivities(
            $this->getParam('fromCallActivityID'),
            $this->getParam('toCallActivityID'),
            $wholeProblem
        )
        ) {

            // redirect
            $this->redirectToDisplay($this->getParam('fromCallActivityID'));

        } else {
            $this->redirectToDisplay($this->getParam('fromCallActivityID'));
        }
    }

    /**
     * Add Activity to calendar
     * @access private
     * @throws Exception
     */
    function addToCalendar()
    {
        $this->setMethodName('addToCalendar');
        $this->template->set_file(
            'page',
            'AddToCalendar.inc.ics'
        );
        $dsCallActivity = new DataSet($this);
        $this->buActivity->getActivityByID(
            $this->getParam('callActivityID'),
            $dsCallActivity
        );

        $buSite = new BUSite($this);
        $dsSite = new DataSet($this);
        $buSite->getSiteByID(
            $dsCallActivity->getValue(DBEJCallActivity::customerID),
            $dsCallActivity->getValue(DBEJCallActivity::siteNo),
            $dsSite
        );
        $buCustomer = new BUCustomer($this);

        $callRef = $dsCallActivity->getValue(DBEJCallActivity::callActivityID);
        if (!$dsCallActivity->getValue(DBEJCallActivity::endTime)) {
            $endTime = $dsCallActivity->getValue(DBEJCallActivity::startTime);
        } else {
            $endTime = $dsCallActivity->getValue(DBEJCallActivity::endTime);
        }

        $urlActivity =
            SITE_URL .
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'callActivityID' => $dsCallActivity->getValue(DBEJCallActivity::callActivityID),
                    'action'         => CTACTIVITY_ACT_DISPLAY_ACTIVITY
                )
            );

        $what3WordsLink = "";
        if ($dsSite->getValue(DBESite::what3Words)) {
            $what3WordsLink = "https://what3words.com/{$dsSite->getValue(DBESite::what3Words)}\n\n";
        }

        $notes = 'Details:\n\n' . CTActivity::prepareForICS($dsCallActivity->getValue(DBEJCallActivity::reason));
        if ($dsCallActivity->getValue(DBEJCallActivity::internalNotes)) {
            $notes .= '\n\nInternal Notes:\n\n' . CTActivity::prepareForICS(
                    $dsCallActivity->getValue(DBEJCallActivity::internalNotes)
                );
        }

        $this->template->set_var(
            array(
                'scrRef'         => $callRef,
                'userName'       => $dsCallActivity->getValue(DBEJCallActivity::userName),
                'contactName'    => $dsCallActivity->getValue(DBEJCallActivity::contactName),
                'contactPhone'   => $buCustomer->getContactPhone(
                    $dsCallActivity->getValue(DBEJCallActivity::contactID)
                ),
                'dateYYYYMMDD'   => str_replace(
                    '-',
                    '',
                    $dsCallActivity->getValue(DBEJCallActivity::date)
                ),
                'nowYYYYMMDD'    => date('Ymd'),
                'nowHHMMSS'      => date('His'),
                'startHHMM'      => str_replace(
                    ':',
                    '',
                    $dsCallActivity->getValue(DBEJCallActivity::startTime)
                ),
                'endHHMM'        => str_replace(
                    ':',
                    '',
                    $endTime
                ),
                'customerName'   => $dsCallActivity->getValue(DBEJCallActivity::customerName),
                'notes'          => $notes,
                'what3WordsLink' => $what3WordsLink,
                'add1'           => $dsSite->getValue(DBESite::add1),
                'add2'           => $dsSite->getValue(DBESite::add2),
                'add3'           => $dsSite->getValue(DBESite::add3),
                'town'           => $dsSite->getValue(DBESite::town),
                'county'         => $dsSite->getValue(DBESite::county),
                'postcode'       => $dsSite->getValue(DBESite::postcode),
                'urlActivity'    => $urlActivity
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
     * @throws Exception
     */
    private function sendVisitEmail()
    {
        $this->setMethodName('sendVisitEmail');
        $this->buActivity->sendSiteVisitEmail($this->getParam('callActivityID'));
        $this->redirectToDisplay($this->getParam('callActivityID'));
    }

    /**
     * Upload new document from local disk
     * @access private
     * @throws Exception
     */
    function uploadFile()
    {
        // validate
        if (!$this->getParam('problemID')) {
            $this->setFormErrorMessage('problemID not passed');
        }
        if (!@$_FILES['userfile']['name']) {
            $this->setFormErrorMessage('Please enter a file path');
        }

        if (!$this->handleUploads($this->getParam('problemID'))) {
            $this->setFormErrorMessage('Failed Uploading file: File larger than 6mb?');
        }

        if ($this->formError) {
            if (@$_POST['gatherFixed']) {
                $this->redirectToFixed($this->getParam('callActivityID'));
                exit;
            }

            if (@$_POST['edit']) {
                $this->editActivity();
                exit;
            }

            $this->displayActivity();
            exit;
        }

        if (@$_POST['gatherFixed']) {
            $this->redirectToFixed($this->getParam('callActivityID'));
            exit;
        }

        if (@$_POST['edit']) {
            $this->redirectToEdit($this->getParam('callActivityID'));
            exit;
        }

        $this->redirectToDisplay($this->getParam('callActivityID'));
    }

    /**
     * @param $callActivityID
     * @throws Exception
     */
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

    /**
     * @param $callActivityID
     * @throws Exception
     */
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
        if (!$dbeCallDocument->getRow($this->getParam('callDocumentID'))) {
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
      WHERE callDocumentID = " . $this->getParam('callDocumentID');

        $result = mysqli_query(
            $db,
            $query
        );

        $row = mysqli_fetch_assoc($result);

        header('Content-type: ' . $row['fileMIMEType']);
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
     * @throws Exception
     */
    function deleteFile()
    {
        // Validation and setting of variables
        $this->setMethodName('deleteFile');
        $dbeCallDocument = new DBECallDocument($this);
        if (!$dbeCallDocument->getRow($this->getParam('callDocumentID'))) {
            $this->displayFatalError('Document not found.');
        }
        $callActivityID = $this->getParam('callActivityID');
        $dbeCallDocument->deleteRow();
        if (isset($_GET['isEdit'])) {
            return $this->redirectToEdit($callActivityID);
        }

        if (isset($_GET['isGather'])) {
            return $this->redirectToGather($callActivityID);
        }

        return $this->redirectToDisplay($callActivityID);
    }

    /**g
     * @param $callActivityID
     * @throws Exception
     */
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

    /**
     * Function to automatically update the text fields of an activity when the user is in the activity
     * still
     *
     * CONFIG_ACTIVITY_AUTO_UPDATE_INTERVAL frequency
     */
    function autoUpdate()
    {
        if (!$this->getParam('callActivityID')) {

            echo 'callActivityID not passed';

        }

        $this->buActivity->updateTextFields(
            $this->getParam('callActivityID'),
            $this->getParam('reason'),
            $this->getParam('internalNotes')
        );
    }

    /**
     * @throws Exception
     */
    function gatherFixedInformation()
    {
        $this->setMethodName('gatherFixedInformation');

        $this->setTemplateFiles(
            array(
                'ServiceRequestFixedEdit'                 => 'ServiceRequestFixedEdit.inc',
                'ServiceRequestFixedEditContractDropdown' => 'ServiceRequestFixedEditContractDropdown.inc'
            )
        );

        $this->setPageTitle("Service Request Fix Summary ");
        $dsCallActivity = new DataSet($this);
        $this->buActivity->getActivityByID(
            $this->getParam('callActivityID'),
            $dsCallActivity
        );
        $error = [];

        /* validate if this is a POST request */
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (!trim($this->getParam('resolutionSummary'))) {
                $error['resolutionSummary'] = 'Please enter summary of resolution';
            }

            if ($this->getParam('contractCustomerItemID') == 99) {
                $error['contractCustomerItemID'] = 'Required';
            }

            if ($this->getParam('rootCauseID') == 0) {
                $error['rootCauseID'] = 'Required';
            }

            if ($dsCallActivity->getValue(DBEJCallActivity::problemID) == 0) {
                $error['problemID'] = 'Problem ID is not set!';
            }

            if (!count($error)) {

                $this->buActivity->setProblemToFixed(
                    $dsCallActivity->getValue(DBEJCallActivity::problemID),
                    false,
                    $this->getParam('contractCustomerItemID'),
                    $this->getParam('rootCauseID'),
                    $this->getParam('resolutionSummary')
                );

                if ($this->getParam('managementReviewFlag') == 'Y') {

                    $nextURL =
                        Controller::buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'problemID' => $dsCallActivity->getValue(DBEJCallActivity::problemID),
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
            $this->setParam('contractCustomerItemID', 99); // prompts for Please select
        }

        $this->setPageTitle("Service Request Fix Summary " . $dsCallActivity->getValue(DBEJCallActivity::problemID));
        $errorFile = null;
        if (@$_FILES['userfile']['name'] && !$this->getParam('uploadDescription')) {
            $errorFile = 'Description Required';
        }

        if (!$errorFile && isset($_FILES['userfile']) && $_FILES['userfile']['name']) {
            $this->handleUploads($dsCallActivity->getValue(DBEJCallActivity::problemID));
        }

        $submitURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'gatherFixedInformation'
                )
            );

        $this->documents(
            $this->getParam('callActivityID'),
            $dsCallActivity->getValue(DBEJCallActivity::problemID),
            'ServiceRequestFixedEdit'
        );

        if (!$this->getParam('resolutionSummary')) {
            $dbeJCallActivity = $this->buActivity->getActivitiesByProblemID(
                $dsCallActivity->getValue(DBEJCallActivity::problemID)
            );
            while ($dbeJCallActivity->fetchNext()) {
                if ($dbeJCallActivity->getValue(DBEJCallActivity::callActTypeID) == 57) {
                    $this->setParam('resolutionSummary', $dbeJCallActivity->getValue(DBEJCallActivity::reason));
                }
            }
        }

        $uploadURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => 'gatherFixedInformation',
                    'problemID'      => $dsCallActivity->getValue(DBEJCallActivity::problemID),
                    'callActivityID' => $this->getParam('callActivityID')
                )
            );

        $urlMessageToSales =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => 'messageToSales',
                    'callActivityID' => $this->getParam('callActivityID'),
                )
            );

        $urlSalesRequest =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => 'sendSalesRequest',
                    'problemID' => $dsCallActivity->getValue(DBEJCallActivity::problemID),
                )
            );

        $this->template->set_var(
            array(
                'callActivityID'                => $this->getParam('callActivityID'),
                'customerID'                    => $dsCallActivity->getValue(DBEJCallActivity::customerID),
                'customerName'                  => $dsCallActivity->getValue(DBEJCallActivity::customerName),
                'hiddenSR'                      => $dsCallActivity->getValue(
                    DBEJCallActivity::problemHideFromCustomerFlag
                ) == 'Y' ? 'true' : 'false',
                'resolutionSummary'             => $this->getParam('resolutionSummary'),
                'resolutionSummaryMessage'      => @$error['resolutionSummary'],
                'rootCauseID'                   => $dsCallActivity->getValue(DBEJCallActivity::rootCauseID),
                'rootCauseIDMessage'            => @$error['rootCauseID'],
                'contractCustomerItemIDMessage' => @$error['contractCustomerItemID'],
                'submitURL'                     => $submitURL,
                'historyLink'                   => $this->getProblemHistoryLink(
                    $dsCallActivity->getValue(DBEJCallActivity::problemID)
                ),
                'SRLink'                        => "<a href='Activity.php?action=displayLastActivity&problemID=" . $dsCallActivity->getValue(
                        DBEJCallActivity::problemID
                    ) . "' target='_blank'>SR</a>",
                'uploadErrors'                  => $errorFile,
                'uploadURL'                     => $uploadURL,
                'urlMessageToSales'             => $urlMessageToSales,
                'urlSalesRequest'               => $urlSalesRequest,
                'problemID'                     => $dsCallActivity->getValue(DBEJCallActivity::problemID)
            )
        );

        $buHeader = new BUHeader($this);
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);

        $this->rootCauseDropdown(
            $dsCallActivity->getValue(DBEJCallActivity::rootCauseID),
            'ServiceRequestFixedEdit',
            'rootCauseBlock'
        );
        /*
      Whether to allow selection of a contract (otherwise set to T&M)
      */
        if (
            $dsCallActivity->getValue(DBEJCallActivity::priority) < 4 or
            /*
        User in Accounts and priority > 3
        */
            ($this->hasPermissions(ACCOUNTS_PERMISSION) && $dsCallActivity->getValue(
                    DBEJCallActivity::priority
                ) > 3) or

            /*
        priority > 3 and activity hours greater than system threshold
        */
            ($dsCallActivity->getValue(DBEJCallActivity::priority) > 3 and $dsCallActivity->getValue(
                    DBEJCallActivity::totalActivityDurationHours
                ) < $dsHeader->getValue(DBEHeader::srPromptContractThresholdHours))

        ) {
            $this->contractDropdown(
                $dsCallActivity->getValue(DBEJCallActivity::customerID),
                $this->getParam('contractCustomerItemID'),
                'ServiceRequestFixedEditContractDropdown',
                'contractBlock',
                !!$dsCallActivity->getValue(DBEJCallActivity::linkedSalesOrderID)
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

    /**
     * @throws Exception
     */
    function gatherManagementReviewDetails()
    {
        $error = null;
        $this->setMethodName('gatherManagementReviewDetails');

        $this->setTemplateFiles(
            array(
                'ServiceRequestManagementReview' => 'ServiceRequestManagementReview.inc'
            )
        );

        $this->setPageTitle("Management Review Reason");

        /* validate if this is a POST request */
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (!trim($this->getParam('managementReviewDetails'))) {
                $error['managementReviewDetails'] = 'Please enter reason for management review';
            }

            if (count($error) == 0) {

                $this->buActivity->updateManagementReviewReason(
                    $this->getParam('problemID'),
                    $this->getParam('managementReviewDetails')
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
                'problemID'                      => $this->getParam('problemID'),
                'managementReviewDetails'        => $this->getParam('managementReviewDetails'),
                'managementReviewDetailsMessage' => $error['managementReviewDetails'],
                'submitURL'                      => $submitURL,
                'historyLink'                    => $this->getProblemHistoryLink($this->getParam('problemID'))
            )
        );


        $this->template->parse(
            'CONTENTS',
            'ServiceRequestManagementReview',
            true
        );

        $this->parsePage();

    }

    /**
     * @throws Exception
     */
    function allocateAdditionalTime()
    {
        $this->setMethodName('allocateAdditionalTime');
        $dbeFirstActivity = $this->buActivity->getFirstActivityInProblem($this->getParam('problemID'));
        $this->setTemplateFiles(
            array(
                'ServiceRequestAllocateAdditionalTime' => 'ServiceRequestAllocateAdditionalTime.inc'
            )
        );
        $buHeader = new BUHeader($this);
        /** @var $dsHeader DataSet */
        $buHeader->getHeader($dsHeader);

        $this->setPageTitle("Allocate Additional Time To Service Request");


        $minutesInADay = $dsHeader->getValue(DBEHeader::smallProjectsTeamMinutesInADay);
        /* validate if this is a POST request */
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $minutes = 0;
            switch ($this->getParam('allocatedTimeAmount')) {
                case 'minutes':
                    $minutes = $this->getParam('allocatedTimeValue');
                    break;
                case 'hours':
                    $minutes = $this->getParam('allocatedTimeValue') * 60;
                    break;
                case 'days':
                    $minutes = $minutesInADay * $this->getParam('allocatedTimeValue');
            }

            $this->buActivity->allocateAdditionalTime(
                $this->getParam('problemID'),
                $this->getParam('teamLevel'),
                $minutes,
                $this->getParam('comments'),
                $this->dbeUser
            );

            $this->buActivity->logOperationalActivity(
                $this->getParam('problemID'),
                "<p>Additional time allocated to {$this->buActivity->getTeamName($this->getParam('teamLevel'))} Team: {$minutes} minutes</p><p>{$this->getParam('comments')}</p>"
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
            if ($dbeFirstActivity->getValue(DBEJCallActivity::queueNo) == 1) {
                $teamLevel = 1;
            } elseif ($dbeFirstActivity->getValue(DBEJCallActivity::queueNo) == 2) {
                $teamLevel = 2;
            } elseif ($dbeFirstActivity->getValue(DBEJCallActivity::queueNo) == 3) {
                $teamLevel = 3;           // Small Projects
            } else {
                $teamLevel = 5;           // Projects
            }
        }

        $teamLevel1Selected = null;
        $teamLevel2Selected = null;
        $teamLevel3Selected = null;
        $teamLevel5Selected = null;

        if ($teamLevel == 1) {
            $teamLevel1Selected = CT_SELECTED;
        } elseif ($teamLevel == 2) {
            $teamLevel2Selected = CT_SELECTED;
        } elseif ($teamLevel == 3) {
            $teamLevel3Selected = CT_SELECTED;
        } else {
            $teamLevel5Selected = CT_SELECTED;
        }

        $urlProblemHistoryPopup =
            Controller::buildLink(
                'Activity.php',
                array(
                    'action'    => 'problemHistoryPopup',
                    'problemID' => $this->getParam('problemID'),
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

        $dbeProblem = new DBEProblem($this);
        $dbeProblem->getRow($this->getParam('problemID'));
        $helpdeskHardLimitRemainingMinutes = $dsHeader->getValue(
                DBEHeader::hdTeamManagementTimeApprovalMinutes
            ) - $dbeProblem->getValue(DBEProblem::hdLimitMinutes);
        $escalationsHardLimitRemainingMinutes = $dsHeader->getValue(
                DBEHeader::esTeamManagementTimeApprovalMinutes
            ) - $dbeProblem->getValue(DBEProblem::esLimitMinutes);
        $smallProjectsHardLimitRemainingMinutes = $dsHeader->getValue(
                DBEHeader::smallProjectsTeamManagementTimeApprovalMinutes
            ) - $dbeProblem->getValue(DBEProblem::smallProjectsTeamLimitMinutes);

        $isAdditionalTimeApprover = $this->dbeUser->getValue(DBEUser::additionalTimeLevelApprover);

        $this->template->set_var(
            [
                'teamLevel1Selected'                     => $teamLevel1Selected,
                'teamLevel2Selected'                     => $teamLevel2Selected,
                'teamLevel3Selected'                     => $teamLevel3Selected,
                'teamLevel5Selected'                     => $teamLevel5Selected,
                'helpdeskHardLimitRemainingMinutes'      => $helpdeskHardLimitRemainingMinutes,
                'escalationsHardLimitRemainingMinutes'   => $escalationsHardLimitRemainingMinutes,
                'smallProjectsHardLimitRemainingMinutes' => $smallProjectsHardLimitRemainingMinutes,
                'problemID'                              => $this->getParam('problemID'),
                'customerID'                             => $dbeFirstActivity->getValue(DBEJCallActivity::customerID),
                'customerName'                           => $dbeFirstActivity->getValue(DBEJCallActivity::customerName),
                'submitURL'                              => $submitURL,
                'urlProblemHistoryPopup'                 => $urlProblemHistoryPopup,
                'additionalTimeLimitApprover'            => $isAdditionalTimeApprover ? 'true' : 'false',
                'minutesInADay'                          => $minutesInADay
            ]
        );

        $this->allocatedMinutesDropdown(
            $this->getParam('allocatedMinutes'),
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

            $selected = ($selectedID == $key) ? CT_SELECTED : null;

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
        if (!$this->getParam('problemID') && !$this->getParam('callActivityID')) {
            http_response_code(400);
            echo json_encode(["status" => 'error', "message" => "ProblemID or CallactivityID required"]);
            exit;
        }

        $this->buActivity->requestAdditionalTime(
            $this->getParam('problemID'),
            $this->getParam('reason'),
            $this->getParam('callActivityID')
        );
    }

    /**
     * @throws Exception
     */
    function changeRequestReview()
    {

        $this->setMethodName('changeRequestReview');

        $callActivityID = $this->getParam('callActivityID');
        $dsCallActivity = new DataSet($this);
        $this->buActivity->getActivityByID(
            $callActivityID,
            $dsCallActivity
        );

        $problemID = $dsCallActivity->getValue(DBEJCallActivity::problemID);

        $dbeFirstActivity = $this->buActivity->getFirstActivityInProblem($problemID);

        $this->setTemplateFiles(
            array(
                'ServiceChangeRequestReview' => 'ServiceChangeRequestReview.inc'
            )
        );

        $this->setPageTitle("Review Change Request");

        if ($this->getParam('fromEmail')) {
            $url =
                Controller::buildLink(
                    'Activity.php',
                    [
                        "action"         => CTACTIVITY_ACT_CHANGE_REQUEST_REVIEW,
                        "callActivityID" => $this->getParam('callActivityID')
                    ]
                );
            header('Location: ' . $url);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$this->getParam('fromEmail')) {

            switch ($this->getParam('Submit')) {

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
                $this->getParam('comments')
            );

            $nextURL =
                Controller::buildLink(
                    'ChangeRequestDashboard.php?HD&ES&SP&P',
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

                'customerID' => $dbeFirstActivity->getValue(DBEJCallActivity::customerID),

                'customerName'           => $dbeFirstActivity->getValue(DBEJCallActivity::customerName),
                'requestDetails'         => $dsCallActivity->getValue(DBEJCallActivity::reason),
                'userName'               => $dsCallActivity->getValue(DBEJCallActivity::userName),
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

    /**
     * @throws Exception
     */
    function timeRequestReview()
    {

        $this->setMethodName('timeRequestReview');
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);
        $callActivityID = $this->getParam('callActivityID');
        $dsCallActivity = new DataSet($this);
        $this->buActivity->getActivityByID(
            $callActivityID,
            $dsCallActivity
        );

        $problemID = $dsCallActivity->getValue(DBEJCallActivity::problemID);

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
        $dbeUser = new DBEUser($this);
        $dbeUser->getRow($requestorID);
        $teamID = $dbeUser->getValue(DBEUser::teamID);
        $teamName = null;
        $usedMinutes = 0;
        $assignedMinutes = 0;
        $remainingTimeLimit = null;
        $isOverLimit = false;
        switch ($teamID) {
            case 1:
                $usedMinutes = $this->buActivity->getHDTeamUsedTime($problemID);
                $assignedMinutes = $dbeProblem->getValue(DBEProblem::hdLimitMinutes);
                $teamName = 'Help Desk';
                $remainingTimeLimit = $dsHeader->getValue(
                        DBEHeader::hdTeamManagementTimeApprovalMinutes
                    ) - $assignedMinutes;
                $isOverLimit = $assignedMinutes >= $dsHeader->getValue(
                        DBEHeader::hdTeamManagementTimeApprovalMinutes
                    );
                break;
            case 2:
                $usedMinutes = $this->buActivity->getESTeamUsedTime($problemID);
                $assignedMinutes = $dbeProblem->getValue(DBEProblem::esLimitMinutes);
                $teamName = 'Escalation';
                $remainingTimeLimit = $dsHeader->getValue(
                        DBEHeader::esTeamManagementTimeApprovalMinutes
                    ) - $assignedMinutes;
                $isOverLimit = $assignedMinutes >= $dsHeader->getValue(
                        DBEHeader::esTeamManagementTimeApprovalMinutes
                    );
                break;
            case 4:
                $usedMinutes = $this->buActivity->getSPTeamUsedTime($problemID);
                $assignedMinutes = $dbeProblem->getValue(DBEProblem::smallProjectsTeamLimitMinutes);
                $teamName = 'Small Projects';
                $remainingTimeLimit = $dsHeader->getValue(
                        DBEHeader::smallProjectsTeamManagementTimeApprovalMinutes
                    ) - $assignedMinutes;
                $isOverLimit = $assignedMinutes >= $dsHeader->getValue(
                        DBEHeader::smallProjectsTeamManagementTimeApprovalMinutes
                    );
                break;
            case 5:
                $usedMinutes = $this->buActivity->getUsedTimeForProblemAndTeam($problemID, 5);
                $assignedMinutes = $dbeProblem->getValue(DBEProblem::projectTeamLimitMinutes);
                $teamName = 'Projects';
                $remainingTimeLimit = 'Infinity';
        }

        $leftOnBudget = $assignedMinutes - $usedMinutes;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            switch ($this->getParam('Submit')) {

                case 'Approve':

                    if ($isOverLimit && !$this->dbeUser->getValue(DBEUser::additionalTimeLevelApprover)) {
                        throw new Exception('You do not have enough permissions to proceed');
                    }
                    $option = 'A';
                    break;

                case 'Deny':
                    $option = 'D';
                    break;

                case 'Delete':
                default:
                    if ($isOverLimit && !$this->dbeUser->getValue(DBEUser::additionalTimeLevelApprover)) {
                        throw new Exception('You do not have enough permissions to proceed');
                    }
                    $option = 'DEL';
                    break;
            }

            $minutes = 0;

            switch ($this->getParam('allocatedTimeAmount')) {
                case 'minutes':
                    $minutes = $this->getParam('allocatedTimeValue');
                    break;
                case 'hours':
                    $minutes = $this->getParam('allocatedTimeValue') * 60;
                    break;
                case 'days':
                    $buHeader = new BUHeader($this);
                    /** @var $dsHeader DataSet */
                    $buHeader->getHeader($dsHeader);
                    $minutesInADay = $dsHeader->getValue(DBEHeader::smallProjectsTeamMinutesInADay);

                    $minutes = $minutesInADay * $this->getParam('allocatedTimeValue');
            }

            $this->buActivity->timeRequestProcess(
                $callActivityID,
                $this->userID,
                $option,
                $this->getParam('comments'),
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

                'customerID' => $dbeFirstActivity->getValue(DBEJCallActivity::customerID),

                'customerName'                => $dbeFirstActivity->getValue(DBEJCallActivity::customerName),
                'requestDetails'              => $dsCallActivity->getValue(DBEJCallActivity::reason),
                'userName'                    => $dsCallActivity->getValue(DBEJCallActivity::userName),
                'submitUrl'                   => $submitURL,
                'urlProblemHistoryPopup'      => $urlProblemHistoryPopup,
                'requesterTeamName'           => $teamName,
                'notes'                       => $dsCallActivity->getValue(DBEJCallActivity::reason),
                'requestedDateTime'           => $dsCallActivity->getValue(
                        DBEJCallActivity::date
                    ) . ' ' . $dsCallActivity->getValue(DBEJCallActivity::startTime),
                'remainingLimitTime'          => $remainingTimeLimit,
                'chargeableHours'             => $dbeProblem->getValue(DBEJProblem::chargeableActivityDurationHours),
                'timeSpentSoFar'              => $usedMinutes,
                'timeLeftOnBudget'            => $leftOnBudget,
                'requesterTeam'               => $teamName,
                'additionalTimeLimitApprover' => $this->dbeUser->getValue(
                    DBEUser::additionalTimeLevelApprover
                ) ? 'true' : 'false',
                'minutesInADay'               => $dsHeader->getValue(DBEHeader::smallProjectsTeamMinutesInADay)
            )
        );

        $this->template->parse(
            'CONTENTS',
            'ServiceTimeRequestReview',
            true
        );

        $this->parsePage();

    }

    /**
     * @throws Exception
     */
    function contractListPopup()
    {
        $this->setTemplateFiles(
            array(
                'ContractListPopup' => 'ContractListPopup.inc'
            )
        );

        $this->displayContracts(
            $this->getParam('customerID'),
            'ContractListPopup'
        );

        $this->template->parse(
            'CONTENTS',
            'ContractListPopup',
            true
        );
        $this->parsePage();
    }

    /**
     * @param $customerID
     * @param $templateName
     * @param string $blockName
     * @throws Exception
     */
    function displayContracts(
        $customerID,
        $templateName,
        $blockName = 'contractBlock'
    )
    {
        $buCustomerItem = new BUCustomerItem($this);
        $dsContract = new DataSet($this);
        $buCustomerItem->getContractsByCustomerID(
            $customerID,
            $dsContract,
            null
        );

        $itemTypes = [];

        $items = [];

        while ($dsContract->fetchNext()) {
            $itemTypeID = $dsContract->getValue(DBEJContract::itemTypeID);

            if (!isset($itemTypes[$itemTypeID])) {
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
            $itemTypeHeader = null;
            if ($item['itemTypeDescription'] != $lastItemTypeDescription) {
                $itemTypeHeader = '<tr><td colspan="2"><h3>' . $item['itemTypeDescription'] . '</h3></td></tr>';
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
     * @throws Exception
     */
    function managerCommentPopup()
    {
        $this->setTemplateFiles(
            'ManagerCommentPopup',
            'ManagerCommentPopup.inc'
        );

        $this->setPageTitle('Manager Comment');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (!$this->getParam('problemID')) {
                $this->raiseError('No problemID Passed');
            }


            $this->buActivity->updateManagerComment(
                $this->getParam('problemID'),
                $this->getParam('details')
            );

            echo '<script lang="js">window.close()</script>;';

        } else {
            if ($_REQUEST ['problemID']) {
                $this->setParam('details', $this->buActivity->getManagerComment($_REQUEST ['problemID']));

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
                'problemID' => $this->getParam('problemID'),
                'details'   => $this->getParam('details'),
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

        $message = $this->getParam('message');
        $callActivityID = $this->getParam('callActivityID');

        if (!$callActivityID) {
            http_response_code(400);
            return ['error' => true, 'errorDescription' => "callActivityID is missing"];
        }

        $this->buActivity->sendEmailToSales(
            $callActivityID,
            $message
        );
        return ["status" => "ok"];
    }

    function toggleDoNextFlag()
    {
        if (!$this->getParam('problemID')) {

            echo 'problemID not passed';

        }

        $this->buActivity->toggleDoNextFlag($this->getParam('problemID'));

    }

    /**
     * @throws Exception
     */
    function toggleCriticalFlag()
    {
        if (!$this->getParam('callActivityID')) {
            echo 'callActivityID not passed';
        }
        $dsActivity = new DataSet($this);

        $this->buActivity->getActivityByID(
            $this->getParam('callActivityID'),
            $dsActivity
        );

        $this->buActivity->toggleCriticalFlag($dsActivity->getValue(DBEJCallActivity::problemID));

        $this->redirectToDisplay($this->getParam('callActivityID'));
    }

    /**
     * called by Ajax on ActivityEdit.inc.html to get server time
     *
     */
    function getServerTime()
    {
        echo date('H') . ':' . date('i');
    }// end contactDropdown

    function updateHistoricUserTimeLogs(DateTime $startDate = null)
    {
        $this->buActivity->updateAllHistoricUserLoggedHours($startDate);
        echo "Done";
    }

    /**
     * @throws Exception
     */
    private function toggleMonitoringFlag()
    {
        if (!$this->getParam('callActivityID')) {
            echo 'callActivityID not passed';
        }
        $dsActivity = new DataSet($this);
        $this->buActivity->getActivityByID(
            $this->getParam('callActivityID'),
            $dsActivity
        );

        $this->buActivity->toggleMonitoringFlag($dsActivity->getValue(DBEJCallActivity::problemID));

        $this->redirectToDisplay($this->getParam('callActivityID'));
    }

    /**
     * @throws Exception
     */
    private
    function unhideSR()
    {

        if (!$this->getParam('callActivityID')) {

            echo 'callActivityID not passed';

        }
        $dsActivity = new DataSet($this);
        $this->buActivity->getActivityByID(
            $this->getParam('callActivityID'),
            $dsActivity
        );

        $firstName = $this->dbeUser->getValue(DBEUser::firstName);
        $lastName = $this->dbeUser->getValue(DBEUser::lastName);

        $this->buActivity->unhideSR($dsActivity->getValue(DBEJCallActivity::problemID));

        $this->buActivity->logOperationalActivity(
            $dsActivity->getValue(DBEJCallActivity::problemID),
            $firstName . ' ' . $lastName . " converted this from a hidden SR to a visible SR."
        );

        $this->redirectToDisplay($this->getParam('callActivityID'));
    }

    function sendSalesRequest()
    {
        $this->setMethodName('sendSalesRequest');

        $message = $this->getParam('message');
        $problemID = $this->getParam('problemID');
        $type = $this->getParam('type');

        try {

            $this->buActivity->sendSalesRequest(
                $problemID,
                $message,
                $type
            );
        } catch (Exception $exception) {
            return ["status" => "error", "message" => $exception->getMessage()];
        }
        return ["status" => "ok"];
    }

    function sendChangeRequest()
    {
        $this->setMethodName('sendChangeRequest');

        $message = $this->getParam('message');
        $problemID = $this->getParam('problemID');
        $type = $this->getParam('type');

        try {
            $this->buActivity->sendChangeRequest(
                $problemID,
                $message,
                $type
            );
        } catch (Exception $exception) {
            return ["status" => "error", "message" => $exception->getMessage()];
        }
        return ["status" => "ok"];
    }

    /**
     * @throws Exception
     */
    function salesRequestReview()
    {
        $this->setMethodName('salesRequestReview');

        $callActivityID = $this->getParam('callActivityID');
        $dsCallActivity = new DataSet($this);
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

        $problemID = $dsCallActivity->getValue(DBEJCallActivity::problemID);

        $dbeFirstActivity = $this->buActivity->getFirstActivityInProblem($problemID);

        $this->setTemplateFiles(
            array(
                'ServiceSalesRequestReview' => 'ServiceSalesRequestReview.inc'
            )
        );

        $this->setPageTitle("Review Sales Request");

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            switch ($this->getParam('Submit')) {

                case 'Approve':
                    $option = 'A';

                    break;

                case 'Deny':
                    $option = 'D';
                    break;
                default:
                    throw new Exception('Action not valid');
            }

            $this->buActivity->salesRequestProcess(
                $callActivityID,
                $this->userID,
                $option,
                $this->getParam('comments')
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

                'customerID' => $dbeFirstActivity->getValue(DBEJCallActivity::customerID),

                'customerName'           => $dbeFirstActivity->getValue(DBEJCallActivity::customerName),
                'requestDetails'         => $dsCallActivity->getValue(DBEJCallActivity::reason),
                'userName'               => $dsCallActivity->getValue(DBEJCallActivity::userName),
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

    /**
     * @return bool|float|int|string
     * @throws Exception
     */
    private function getContactNotes()
    {
        $contactId = @$this->getParam('contactID');

        if (!$contactId) {
            throw new Exception('Contact ID is missing');
        }

        $dbeContact = new DBEContact($this);
        $dbeContact->getRow($contactId);

        return $dbeContact->getValue(DBEContact::notes);
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getAuthorisingContacts()
    {

        $customerID = @$this->getParam('customerID');

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

    private function hasHiddenCharges($problemID)
    {
        $dbeProblem = new DBEProblem($this);
        $dbeProblem->getRow($problemID);
        if ($dbeProblem->getValue(DBEProblem::hideFromCustomerFlag) == 'Y' && $dbeProblem->getValue(
                DBEProblem::chargeableActivityDurationHours
            ) > 0) {
            return true;
        }

        $query = "SELECT
  COUNT(*) > 0 AS hiddenChargeableActivities
FROM
  callactivity
  JOIN callacttype
    ON cat_callacttypeno = caa_callacttypeno
  JOIN item AS at_item
    ON cat_itemno = at_item.itm_itemno
WHERE caa_problemno = ?
  AND callactivity.`caa_hide_from_customer_flag` = 'Y'
  AND at_item.itm_sstk_price > 0";
        /** @var dbSweetcode $db */
        global $db;
        $result = $db->preparedQuery($query, [["type" => "i", "value" => $problemID]]);
        $test = $result->fetch_assoc();

        return !!$test['hiddenChargeableActivities'];
    }
}