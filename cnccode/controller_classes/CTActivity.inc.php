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

use CNCLTD\ServiceRequestInternalNote\infra\ServiceRequestInternalNotePDORepository;
use CNCLTD\Utils;

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

    const GREEN                                        = '#BDF8BA';
    const CONTENT                                      = '#F4f4f2';
    const ASSIGN_TO_BE_LOGGED_TO_SERVICE_REQUEST       = 'assignToBeLoggedToServiceRequest';
    const CHECK_PREPAY                                 = 'checkPrepay';
    const AUTHORISING_CONTACTS                         = 'authorisingContacts';
    const CONTACT_NOTES                                = 'contactNotes';
    const SEND_CHANGE_REQUEST                          = 'sendChangeRequest';
    const SEND_SALES_REQUEST                           = 'sendSalesRequest';
    const UNHIDE_SR                                    = 'unhideSR';
    const TOGGLE_MONITORING_FLAG                       = 'toggleMonitoringFlag';
    const UPDATE_HISTORIC_USER_TIME_LOGS               = 'updateHistoricUserTimeLogs';
    const GET_SERVER_TIME                              = 'getServerTime';
    const TOGGLE_CRITICAL_FLAG                         = 'toggleCriticalFlag';
    const TOGGLE_DO_NEXT_FLAG                          = 'toggleDoNextFlag';
    const MESSAGE_TO_SALES                             = 'messageToSales';
    const MANAGER_COMMENT_POPUP                        = 'managerCommentPopup';
    const CONTRACT_LIST_POPUP                          = 'contractListPopup';
    const REQUEST_ADDITIONAL_TIME                      = 'requestAdditionalTime';
    const ALLOCATE_ADDITIONAL_TIME                     = 'allocateAdditionalTime';
    const GATHER_MANAGEMENT_REVIEW_DETAILS             = 'gatherManagementReviewDetails';
    const GATHER_FIXED_INFORMATION                     = 'gatherFixedInformation';
    const AUTO_UPDATE                                  = 'autoUpdate';
    const LINK_PROBLEMS                                = 'linkProblems';
    const SET_PROBLEM_COMPLETE                         = 'setProblemComplete';
    const SET_PROBLEM_FIXED                            = 'setProblemFixed';
    const DISPLAY_SERVICE_REQUEST                      = 'displayServiceRequest';
    const UPDATE_REQUEST_FROM_CUSTOMER_REQUEST         = 'updateRequestFromCustomerRequest';
    const CREATE_REQUEST_FROM_CUSTOMER_REQUEST         = 'createRequestFromCustomerRequest';
    const CREATE_FOLLOW_ON_ACTIVITY                    = 'createFollowOnActivity';
    const CUSTOMER_PROBLEM_POPUP                       = 'customerProblemPopup';
    const PROBLEM_HISTORY_POPUP                        = 'problemHistoryPopup';
    const EDIT_LINKED_SALES_ORDER                      = 'editLinkedSalesOrder';
    const EDIT_SERVICE_REQUEST_HEADER                  = 'editServiceRequestHeader';
    const DISPLAY_OPEN_SRS                             = 'displayOpenSrs';
    const EDIT_VALUE_ONLY_SERVICE_REQUEST              = 'editValueOnlyServiceRequest';
    const DISPLAY_LAST_ACTIVITY                        = 'displayLastActivity';
    const DISPLAY_FIRST_ACTIVITY                       = 'displayFirstActivity';
    const DISPLAY_SERVICE_REQUEST_FOR_CONTACT_POPUP    = 'displayServiceRequestForContactPopup';
    const UNLINK_SALES_ORDER                           = 'unlinkSalesOrder';
    const ASSIGN_LINKED_SALES_ORDER_TO_SERVICE_REQUEST = 'assignLinkedSalesOrderToServiceRequest';
    public $statusArrayCustomer = array(
        "A" => "Active",
        "E" => "Ended",
        ""  => "All"
    );
    public $serverGuardArray    = array(
        ""  => "Please select",
        "Y" => "ServerGuard Related",
        "N" => "Not ServerGuard Related"
    );
    public $arrContractType     = array(
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
    private $statusArray = array(
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
        "FIXED_OR_COMPLETED"  => "Fixed Or Completed",
        "HOLD_FOR_QA"         => "Hold for QA"
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
        $this->buActivity      = new BUActivity($this);
        $this->dsSearchForm    = new DSForm($this);
        $this->dsSearchResults = new DataSet($this);
        $this->dsCallActivity  = new DSForm($this);
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
            $this->contactID  = $this->getParam('contactID');
            $this->sessionKey = 'activity_create' . $this->contactID;
            $this->updateSession('contactID', $this->contactID);
            $sess[$this->sessionKey]['contactID'] = $this->contactID;
        }
        switch ($this->getAction()) {

            case CTCNC_ACT_SEARCH:
                /* if user has clicked Generate Sales Orders or Skip Sales Orders */ if ($this->getParam(
                    'Search'
                ) == 'Generate Sales Orders') {
                $this->createSalesOrder();
                return;

            }
                if ($this->getParam('Search') == 'Complete SRs') {
                    $this->completeSRs();
                    return;
                }
                if ($this->getParam('Search') == 'Skip Sales Orders') {
                    $this->assignContracts();
                    $this->skipSalesOrder();
                    return;
                }
                $this->search();
                return;
            case self::UNLINK_SALES_ORDER:
                $this->unlinkSalesOrder();
                echo json_encode(["status" => "ok"]);
                exit;
            case self::DISPLAY_SERVICE_REQUEST_FOR_CONTACT_POPUP:
                $this->serviceRequestsForContactPopup();
                break;
            case self::DISPLAY_FIRST_ACTIVITY:
                $this->displayFirstActivity();
                break;
            case self::DISPLAY_LAST_ACTIVITY:
                $this->displayLastActivity();
                break;
            case self::EDIT_VALUE_ONLY_SERVICE_REQUEST:
                $this->editValueOnlyServiceRequest();
                break;
            case self::DISPLAY_OPEN_SRS:
                $this->displayOpenSrs();
                break;
            case self::EDIT_SERVICE_REQUEST_HEADER:
                $this->editServiceRequestHeader();
                break;
            case self::EDIT_LINKED_SALES_ORDER:
                $this->editLinkedSalesOrder();
                break;
            case self::ASSIGN_LINKED_SALES_ORDER_TO_SERVICE_REQUEST:
                $this->assignLinkedSalesOrderToServiceRequestController();
                exit;
            case self::PROBLEM_HISTORY_POPUP:
                $this->problemHistoryPopup();
                break;
            case self::CUSTOMER_PROBLEM_POPUP:
                $this->customerProblemPopup();
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
            case self::CREATE_FOLLOW_ON_ACTIVITY:
                $this->createFollowOnActivity();
                break;
            case self::CREATE_REQUEST_FROM_CUSTOMER_REQUEST:
                $this->checkPermissions(TECHNICAL_PERMISSION);
                $this->createRequestFromCustomerRequest();
                break;
            case self::UPDATE_REQUEST_FROM_CUSTOMER_REQUEST:
                $this->checkPermissions(TECHNICAL_PERMISSION);
                $this->updateRequestFromCustomerRequest();
                break;
            case self::DISPLAY_SERVICE_REQUEST:
                $this->displayServiceRequest();
                break;
            case self::SET_PROBLEM_FIXED:
                $this->setProblemFixed();
                break;
            case self::SET_PROBLEM_COMPLETE:
                $this->checkPermissions(SUPERVISOR_PERMISSION);
                $this->setProblemComplete();
                break;
            case self::LINK_PROBLEMS:
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
            case self::AUTO_UPDATE:
                $this->autoUpdate();
                break;
            case self::GATHER_FIXED_INFORMATION:
                $this->gatherFixedInformation();
                break;
            case self::GATHER_MANAGEMENT_REVIEW_DETAILS:
                $this->gatherManagementReviewDetails();
                break;
            case self::ALLOCATE_ADDITIONAL_TIME:
                $this->allocateAdditionalTime();
                break;
            case self::REQUEST_ADDITIONAL_TIME:
                $this->requestAdditionalTime();
                echo json_encode(["status" => "ok"]);
                break;
            case self::CONTRACT_LIST_POPUP:
                $this->contractListPopup();
                break;
            case self::MANAGER_COMMENT_POPUP:
                $this->managerCommentPopup();
                break;
            case self::MESSAGE_TO_SALES:
                echo json_encode($this->messageToSales());
                break;
            case self::TOGGLE_DO_NEXT_FLAG:
                $this->checkPermissions(SUPERVISOR_PERMISSION);
                $this->toggleDoNextFlag();
                break;
            case self::TOGGLE_CRITICAL_FLAG:
                $this->checkPermissions(SUPERVISOR_PERMISSION);
                $this->toggleCriticalFlag();
                break;
            case self::GET_SERVER_TIME:
                $this->getServerTime();
                break;
            case self::UPDATE_HISTORIC_USER_TIME_LOGS:
                $startDateData = @$this->getParam('startDate');
                $startDate     = new DateTime($startDateData);
                $this->updateHistoricUserTimeLogs($startDate);
                break;
            case self::TOGGLE_MONITORING_FLAG:
                $this->toggleMonitoringFlag();
                break;
            case self::UNHIDE_SR:
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
            case self::SEND_SALES_REQUEST:
                echo json_encode($this->sendSalesRequest());
                break;
            case self::SEND_CHANGE_REQUEST:
                echo json_encode($this->sendChangeRequest());
                break;
            case self::CONTACT_NOTES:
                $buCustomer = new BUCustomer($this);
                $subject    = null;
                if ($this->getParam("problemID")) {
                    $subject = "Service Request {$this->getParam("problemID")}";
                }
                $phoneHtml = $buCustomer->getContactPhoneForHtml(@$this->getParam('contactID'), $subject);
                echo json_encode(['data' => $this->getContactNotes(), 'phone' => $phoneHtml]);
                break;
            case self::AUTHORISING_CONTACTS:
                echo json_encode(['data' => $this->getAuthorisingContacts()]);
                break;
            case self::CHECK_PREPAY:
                echo json_encode(["hiddenCharges" => $this->hasHiddenCharges($this->getParam('problemID'))]);
                break;
            case self::ASSIGN_TO_BE_LOGGED_TO_SERVICE_REQUEST:
                $data = $this->getJSONData();
                if (!isset($data['toBeLogged'])) {
                    throw new \CNCLTD\Exceptions\JsonHttpException(51, 'To Be logged id is required');
                }
                if (!isset($data['serviceRequestId'])) {
                    throw new \CNCLTD\Exceptions\JsonHttpException(51, 'Service request id is required');
                }
                try {
                    $this->assignToBeLoggedToServiceRequest($data['toBeLogged'], $data['serviceRequestId']);
                } catch (Exception $exception) {
                    throw new \CNCLTD\Exceptions\JsonHttpException(51, $exception->getMessage());
                }
                echo json_encode(["status" => "ok"]);
                exit;
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
            $urlNext = Controller::buildLink(
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
            $urlNext = Controller::buildLink(
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
        $activities      = $this->getParam('callActivityID');
        $problems        = $this->getParam('problem');
        $dbeCallActivity = new DBECallActivity($this);
        $dbeProblem      = new DBEProblem($this);
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
            if ($this->countParamsSet($this->getParam('activity')) < 2 and empty(
                $this->dsSearchForm->getValue(
                    BUActivity::searchFormCustomerID
                )
                ) and $this->dsSearchForm->getValue(
                    BUActivity::searchFormContractCustomerItemID
                ) == '99' and $this->dsSearchForm->getValue(BUActivity::searchFormStatus) !== 'CHECKED_NON_T_AND_M') {
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
        $dsSearchForm    = &$this->dsSearchForm; // ref to global
        $dsSearchResults = &$this->dsSearchResults; // ref to global
        $this->setMenuId(102);
        $this->setMethodName('displaySearchForm');
        $urlCreateActivity = null;
        $urlCustomerPopup  = null;
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
        $this->template->setVar('javaScript', "<link rel='stylesheet' href='components/shared/ToolTip.css'>");
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
            $customerNameCol        = $dsSearchResults->columnExists(DBEJCallActivity::customerName);
            $callActivityIDCol      = $dsSearchResults->columnExists(DBEJCallActivity::callActivityID);
            $statusCol              = $dsSearchResults->columnExists(DBEJCallActivity::status);
            $reasonCol              = $dsSearchResults->columnExists(DBEJCallActivity::reason);
            $dateCol                = $dsSearchResults->columnExists(DBEJCallActivity::date);
            $startCol               = $dsSearchResults->columnExists(DBEJCallActivity::startTime);
            $endCol                 = $dsSearchResults->columnExists(DBEJCallActivity::endTime);
            $contractDescriptionCol = $dsSearchResults->columnExists(DBEJCallActivity::contractDescription);
            $problemIDCol           = $dsSearchResults->columnExists(DBEJCallActivity::problemID);
            /*
        if we are displaying checked T&M activities then show Generate Sales Order and Skip Sales Order buttons
        */
            $bulkActionButtons = null;
            $checkAllBox       = null;
            if ($dsSearchForm->getValue(BUActivity::searchFormStatus) == 'CHECKED_T_AND_M') {
                $bulkActionButtons = '<input name="Search" type="submit" value="Generate Sales Orders" onclick="postToBlank()" />
          <input name="Search" type="submit" value="Skip Sales Orders" />';
                $checkAllBox       = '<input type="checkbox" name="checkAllBox" id="checkAllBox" value="0" onClick="checkAll();"/>';

            } elseif ($dsSearchForm->getValue(BUActivity::searchFormStatus) == 'CHECKED_NON_T_AND_M') {
                $bulkActionButtons = '<input name="Search" type="submit" value="Complete SRs" />';
                $checkAllBox       = '<input type="checkbox" name="checkAllBox" id="checkAllBox" value="0" onClick="checkAll();"/>';
            }
            $requestUri    = $this->removeQuerystringVar(
                $_SERVER['REQUEST_URI'],
                'sortColumn'
            );
            $weirdColumns  = '<td class="listHeadText">Start</td>
            <td class="listHeadText">End</td>
            <td class="listHeadTextRight"><a href="' . $requestUri . '&sortColumn=slaResponseHours">SLA</a></td>
            <td class="listHeadTextRight"><a href="' . $requestUri . '&sortColumn=respondedHours">Resp</a></td>';
            $headerColSpan = 13;
            if ($dsSearchForm->getValue(BUActivity::searchFormStatus) == 'CHECKED_T_AND_M' || $dsSearchForm->getValue(
                    BUActivity::searchFormStatus
                ) == 'CHECKED_NON_T_AND_M') {
                $weirdColumns  = '';
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
                $problemID      = $dsSearchResults->getValue($problemIDCol);
                /*
          if we are displaying checked T&M activities then show Generate Sales Order checkbox
          */
                $displayActivityURL = Controller::buildLink(
                    'SRActivity.php',
                    array(
                        'action'         => CTACTIVITY_ACT_DISPLAY_ACTIVITY,
                        'callActivityID' => $dsSearchResults->getValue($callActivityIDCol)
                    )
                );
                $weirdFields        = '<td align="top" class="listItemText">' . $dsSearchResults->getValue($startCol) . '</td>
            <td align="top" class="listItemText">' . $dsSearchResults->getValue($endCol) . '</td>
            <td align="right" nowrap>' . $dsSearchResults->getValue(DBECallActivitySearch::slaResponseHours) . '</td>
            <td align="right" nowrap>' . $dsSearchResults->getValue(DBECallActivitySearch::respondedHours) . '</td>';
                $contractField      = $dsSearchResults->getValue($contractDescriptionCol);
                $checkBox           = null;
                $dbeProblem         = new DBEProblem($this);
                $dbeProblem->getRow($problemID);
                $salesOrderID   = $dbeProblem->getValue(DBEProblem::linkedSalesOrderID);
                $salesOrderLink = "";
                if ($salesOrderID) {
                    $salesOrderURL  = Controller::buildLink(
                        'SalesOrder.php',
                        array(
                            'action'    => 'displaySalesOrder',
                            'ordheadID' => $salesOrderID
                        )
                    );
                    $salesOrderLink = '<a href="' . $salesOrderURL . '" target="_blank">' . $salesOrderID . '</a>';
                }
                if ($dsSearchForm->getValue(
                        BUActivity::searchFormStatus
                    ) == 'CHECKED_T_AND_M' || $dsSearchForm->getValue(
                        BUActivity::searchFormStatus
                    ) == 'CHECKED_NON_T_AND_M') {
                    $weirdFields            = null;
                    $checkBox               = '<input type="checkbox" id="callActivityID" name="callActivityID[' . $callActivityID . ']" value="' . $callActivityID . '" />';
                    $contracts              = $this->getContractsForCustomer(
                        $dbeProblem->getValue(DBEProblem::customerID)
                    );
                    $contractCustomerItemID = $dbeProblem->getValue(DBEProblem::contractCustomerItemID);
                    $contractField          = "<select name='problem[" . $problemID . "][contract]' onchange='tickBox()'>";
                    $contractField          .= "<option value " . ($contractCustomerItemID ? null : 'selected') . ">T&M</option>";
                    foreach ($contracts as $contractType => $contractItems) {

                        $contractField .= "<optgroup label='" . $contractType . "'>";
                        foreach ($contractItems as $contractItem) {
                            $selected      = $contractCustomerItemID == $contractItem['id'];
                            $disabled      = $contractItem['disabled'] ? 'disabled' : null;
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
                            Utils::stripEverything($reason),
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

    function priorityDropdown($selectedID,
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

    function breachedSlaDropdown($selectedID,
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

    function fixSLADropdown($selectedID,
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

    function contractDropdown($customerID,
                              $contractCustomerItemID,
                              $templateName = 'ActivityCreate6',
                              $blockName = 'contractBlock',
                              bool $linkedToSalesOrder = false
    )
    {
        $buCustomerItem = new BUCustomerItem($this);
        $dsContract     = new DataSet($this);
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
        $currentRow      = 0;
        while ($dsContract->fetchNext()) {
            $optGroupOpen  = null;
            $optGroupClose = null;
            if ($dsContract->getValue(DBEJContract::renewalType) != $lastRenewalType) {
                if ($lastRenewalType) {
                    $optGroupClose = '</optgroup>';
                }
                $optGroupOpen = '<optgroup label="' . $dsContract->getValue(DBEJContract::renewalType) . '">';
            }
            $lastRenewalType  = $dsContract->getValue(DBEJContract::renewalType);
            $contractSelected = ($contractCustomerItemID == $dsContract->getValue(
                    DBEJContract::customerItemID
                )) ? CT_SELECTED : null;
            $description      = $dsContract->getValue(DBEJContract::itemDescription) . ' ' . $dsContract->getValue(
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
    }

    private function getContractsForCustomer($customerID)
    {
        $buCustomerItem = new BUCustomerItem($this);
        $dsContract     = new DataSet($this);
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
        $count    = 0;
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
            echo $this->dsSearchResults->getExcelValue(
                    DBECallActivitySearch::customerName
                ) . "," . $this->dsSearchResults->getExcelValue(
                    DBECallActivitySearch::postcode
                ) . "," . $this->dsSearchResults->getExcelValue(
                    DBECallActivitySearch::contactName
                ) . "," . $this->dsSearchResults->getExcelValue(
                    DBECallActivitySearch::problemID
                ) . "," . $this->dsSearchResults->getExcelValue(
                    DBECallActivitySearch::priority
                ) . "," . $this->dsSearchResults->getExcelValue(
                    DBECallActivitySearch::callActivityID
                ) . "," . $this->dsSearchResults->getExcelValue(
                    DBECallActivitySearch::date
                ) . "," . $this->dsSearchResults->getExcelValue(
                    DBECallActivitySearch::startTime
                ) . "," . $this->dsSearchResults->getExcelValue(DBECallActivitySearch::endTime) . "," . number_format(
                    $this->dsSearchResults->getExcelValue(DBECallActivitySearch::duration) / 60 / 60,
                    2
                ) . "," . number_format(
                    $this->dsSearchResults->getExcelValue(DBECallActivitySearch::duration) / 60 / 60,
                    2
                ) * $this->dsSearchResults->getValue(
                    DBECallActivitySearch::salePrice
                ) . "," . $this->dsSearchResults->getExcelValue(
                    DBECallActivitySearch::activityType
                ) . "," . $this->dsSearchResults->getExcelValue(
                    DBECallActivitySearch::userName
                ) . "," . $this->dsSearchResults->getExcelValue(
                    DBECallActivitySearch::projectDescription
                ) . "," . $this->dsSearchResults->getExcelValue(
                    DBECallActivitySearch::contractDescription
                ) . "," . str_replace(
                    ',',
                    '\'',
                    addslashes(
                        Utils::stripEverything($this->dsSearchResults->getValue(DBECallActivitySearch::reason))
                    )
                ) . "," . $this->dsSearchResults->getExcelValue(
                    DBECallActivitySearch::managementReviewReason
                ) . "," . $this->dsSearchResults->getExcelValue(DBECallActivitySearch::rootCause) . "\n";
        }
        $this->pageClose();
        exit;
    } // end siteDropdown

    private function unlinkSalesOrder()
    {
        $serviceRequestId = @$_REQUEST['serviceRequestId'];
        if (!$serviceRequestId) {
            throw new Exception('Service Request Id is missing');
        }
        $dbeProblem = new DBEProblem($this);
        $dbeProblem->getRow($serviceRequestId);
        $dbeProblem->setValue(DBEProblem::linkedSalesOrderID, null);
        $dbeProblem->updateRow();
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
        $dbeContact   = new DBEContact($this);
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

            $urlCreateFollowOn      = Controller::buildLink(
                'Activity.php',
                array(
                    'action'         => 'createFollowOnActivity',
                    'callActivityID' => $dsContactSrs->getValue(DBEJProblem::lastCallActivityID),
                    'reason'         => $this->getParam('reason')
                )
            );
            $urlProblemHistoryPopup = Controller::buildLink(
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
                    'contactReason'                 => Utils::truncate(
                        $dsContactSrs->getValue(DBEJProblem::reason),
                        100
                    ),
                    'contactLastReason'             => Utils::truncate(
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
     * @param $customerID
     * @return string
     * @throws Exception
     */
    function getRenewalsLink($customerID)
    {
        $renewalsLinkURL = Controller::buildLink(
            'RenewalReport.php',
            array(
                'action'     => 'produceReport',
                'customerID' => $customerID
            )
        );
        $renewalsLink    = '<a href="' . $renewalsLinkURL . '" target="_blank" title="Renewals">Renewal Information</a>';
        return $renewalsLink;
    }

    /**
     * @param DataSet|DBECustomer $dsCustomer
     * @return string
     */
    function getCustomerNameDisplayClass($dsCustomer)
    {
        if ($dsCustomer->getValue(DBECustomer::specialAttentionFlag) == 'Y' && $dsCustomer->getValue(
                DBECustomer::specialAttentionEndDate
            ) >= date('Y-m-d')) {
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

    private function checkMonitoring($problemID)
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
        $passwordLinkURL = Controller::buildLink(
            'Password.php',
            array(
                'action'     => 'list',
                'customerID' => $customerID
            )
        );
        return '| <a href="' . $passwordLinkURL . '" target="_blank" title="Passwords">Passwords</a>';
    }

    /**
     * @return string
     * @throws Exception
     */
    function getGeneratePasswordLink()
    {
        $generatePasswordLinkURL = Controller::buildLink(
            'Password.php',
            array(
                'action'  => 'generate',
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        return '| <a href="#" title="Generate new password"
        onClick = "window.open(
          \'' . $generatePasswordLinkURL . '\',
          \'reason\',
          \'scrollbars=yes,resizable=yes,height=524,width=855,copyhistory=no, menubar=0\')" >Generate Password</a> ';
    }

    /**
     * @param $customerID
     * @return string
     * @throws Exception
     */
    function getThirdPartyContactLink($customerID)
    {
        $thirdPartyContactLinkURL = Controller::buildLink(
            'ThirdPartyContact.php',
            array(
                'action'     => 'list',
                'customerID' => $customerID
            )
        );
        $thirdPartyContactLink    = '| <a href="' . $thirdPartyContactLinkURL . '" target="_blank" title="ThirdPartyContacts">Third Party Contacts</a>';
        return $thirdPartyContactLink;
    }// end function editLinkedSalesOrder()

    /**
     * @param $customerID
     * @return string
     * @throws Exception
     */
    function getContractListPopupLink($customerID)
    {
        $contractListPopupLinkURL = Controller::buildLink(
            'Activity.php',
            array(
                'action'     => 'contractListPopup',
                'customerID' => $customerID,
            )
        );
        $contractListPopupLink    = '| <a href="' . $contractListPopupLinkURL . '" target="_blank" title="Contracts">Contracts</a>';
        return $contractListPopupLink;
    }

    /**
     * @param $contactID
     * @return string
     * @throws Exception
     */
    private function getServiceRequestForContactLink($contactID)
    {
        $contactHistory = Controller::buildLink(
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
            $linkURL    = Controller::buildLink(
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
                $return = "<div style='font-size: 14px;font-weight: 100; display:inline-block'>
                  <div class='tooltip' > ";
                $title  = "";
                switch ($dbeProblemRaiseType->getValue(DBEProblemRaiseType::description)) {
                    case 'Email':
                        $return .= "<i class='fal fa-envelope ml-5 pointer' style='font-size: 18px;' ></i>";
                        $title  = "This Service Request was raised by email";
                        break;
                    case 'Portal':
                        $return .= "<i class='icon-chrome_icon' style='font-size: 18px; margin:5px; color:#000080 ' ></i>";
                        $title  = "This Service Request was raised by the portal";
                        break;
                    case 'Phone':
                        $return .= "<i class='fal fa-phone ml-5 pointer' style='font-size: 18px;' ></i>";
                        $title  = "This Service Request was raised by phone";
                        break;
                    case 'On site':
                        $return .= "<i class='fal fa-building ml-5 pointer' style='font-size: 18px;' ></i>";
                        $title  = "This Service Request was raised by an on site engineer";
                        break;
                    case 'Alert':
                        $return .= "<i class='fal fa-bell ml-5 pointer' style='font-size: 18px;' ></i>";
                        $title  = "This Service Request was raised by an alert";
                        break;
                    case 'Sales':
                        $return .= "<i class='fal fa-shopping-cart ml-5 pointer' style='font-size: 18px;' ></i>";
                        $title  = "This Service Request was raised via Sales";
                        break;
                    case 'Manual':
                        $return .= "<i class='fal fa-user-edit ml-5 pointer' style='font-size: 18px;' ></i>";
                        $title  = "This Service Request was raised manually";
                        break;
                }
                $return .= "<div class='tooltiptext tooltip-bottom' style='width:300px' >$title</div> </div> ";
                return $return;
            }
        }
        return null;
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
        $urlUploadFile = Controller::buildLink(
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

            $urlViewFile   = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => CTACTIVITY_ACT_VIEW_FILE,
                    'callDocumentID' => $dbeJCallDocument->getValue(DBEJCallDocument::callDocumentID)
                )
            );
            $urlDeleteFile = Controller::buildLink(
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
        $dbeCallActivity = $this->buActivity->getFirstActivityInServiceRequest($this->getParam('problemID'));
        $this->redirectToDisplay($dbeCallActivity->getValue(DBEJCallActivity::callActivityID));

    }

    /**
     * Redirect to call page
     * @access private
     * @param $callActivityID
     * @throws Exception
     */
    function redirectToDisplay($callActivityID)
    {
        $urlNext = Controller::buildLink(
            "SRActivity.php",
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
                $sessionValue['callActivityID']         = 0;
                $sessionValue['date']                   = date('d/m/Y');
                $sessionValue['curValue']               = $this->getParam('curValue');
                $sessionValue['startTime']              = date('H:i');
                $sessionValue['status']                 = 'C';
                $sessionValue['contractCustomerItemID'] = $this->getParam('contractCustomerItemID');
                $sessionValue['userID']                 = $GLOBALS['auth']->is_authenticated();
                $this->setSessionParam($this->sessionKey, $sessionValue);
                $dsCallActivity = $this->buActivity->createActivityFromSession($this->sessionKey);
                $callActivityID = $dsCallActivity->getValue(DBEJCallActivity::callActivityID);
                unset ($_SESSION[$this->sessionKey]); // clear the session variable
                $nextURL = Controller::buildLink(
                    'SRActivity.php',
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
        $submitURL = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array('action' => 'editValueOnlyServiceRequest')
        );
        $backURL   = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array('action' => 'activityCreate1')
        );
        $this->template->set_var(
            array(
                'customerName'                  => $_SESSION[$this->sessionKey]['customerName'],
                'renewalsLink'                  => $this->getRenewalsLink($_SESSION[$this->sessionKey]['customerID']),
                'curValue'                      => $_SESSION[$this->sessionKey]['curValue'],
                'curValueMessage'               => $error['curValue'],
                'contractCustomerItemID'        => $_SESSION[$this->sessionKey]['contractCustomerItemID'],
                'contractCustomerItemIDMessage' => $error['contractCustomerItemID'],
                'submitURL'                     => $submitURL,
                'backURL'                       => $backURL
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
        $sessionValue['reason']               = $this->getParam('reason');
        $sessionValue['customerName']         = $dsCustomer->getValue(DBECustomer::name);
        $sessionValue['hideFromCustomerFlag'] = $this->getParam('hideFromCustomerFlag');
        $sessionValue['internalNotes']        = $this->getParam('internalNotes');
        $this->setSessionParam($this->sessionKey, $sessionValue);
        $dsContactSrs = $this->buActivity->getProblemsByContact($this->getParam('contactID'));
        $dsActiveSrs  = $this->buActivity->getActiveProblemsByCustomer($this->getParam('customerID'));
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
            $urlProblemHistoryPopup = Controller::buildLink(
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
                    'contactReason'                 => Utils::truncate(
                        $dsContactSrs->getValue(DBEJProblem::reason),
                        100
                    ),
                    'contactLastReason'             => Utils::truncate(
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

            $urlCreateFollowOn      = Controller::buildLink(
                'Activity.php',
                array(
                    'action'         => 'createFollowOnActivity',
                    'callActivityID' => $dsActiveSrs->getValue(DBEJProblem::lastCallActivityID),
                    'reason'         => $this->getParam('reason')
                )
            );
            $urlProblemHistoryPopup = Controller::buildLink(
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
                    'reason'                 => Utils::truncate(
                        $dsActiveSrs->getValue(DBEJProblem::reason),
                        100
                    ),
                    'lastReason'             => Utils::truncate(
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
        $urlCreateNewSr = Controller::buildLink(
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
            $this->updateSession('raiseTypeId', $this->getParam('raiseTypeId'));
            if ($this->getParam('pendingReopenedID')) $this->updateSession(
                'pendingReopenedID',
                $this->getParam('pendingReopenedID')
            );
            if ($this->getParam('deletePending')) $this->updateSession(
                'deletePending',
                $this->getParam('deletePending')
            );
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
                $deletePending     = $_SESSION[$this->sessionKey]['deletePending'];
                //$this->console_log($pendingReopenedID);
                /* Create initial activity */
                $dsCallActivity = $this->buActivity->createActivityFromSession($this->sessionKey);
                if (isset($dsCallActivity) && isset($pendingReopenedID) && isset($deletePending) && $deletePending == 'true') {
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

                    $nextURL = Controller::buildLink(
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
                    $nextURL = Controller::buildLink(
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
        $submitURL = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => 'editServiceRequestHeader'
            )
        );
        $disabled  = null;
        if (!$this->hasPermissions(SUPERVISOR_PERMISSION)) {
            $disabled = CTCNC_HTML_DISABLED;
        }
        if (!isset($backURL)) {
            $backURL = null;
        }
        $this->template->set_var(
            array(
                'callActivityID'              => @$_SESSION[$this->sessionKey]['callActivityID'],
                'customerID'                  => @$_SESSION[$this->sessionKey]['customerID'],
                'siteNoMessage'               => @$error['siteNo'],
                'reason'                      => @$_SESSION[$this->sessionKey]['reason'],
                'reasonMessage'               => @$error['reason'],
                'internalNotes'               => @$_SESSION[$this->sessionKey]['internalNotes'],
                'customerName'                => @$_SESSION[$this->sessionKey]['customerName'],
                'customerNameDisplayClass'    => @$_SESSION[$this->sessionKey]['customerNameDisplayClass'],
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
                'raiseTypeId'                 => @$_SESSION[$this->sessionKey]['raiseTypeId'],
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
        $hasError  = false;
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

    function siteDropdown($customerID,
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
        $dbeSite    = new DBESite($this);
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
                $endMainContactStyle   = '*';
            } elseif ($dbeContact->getValue(DBEContact::supportLevel) == DBEContact::supportLevelSupervisor) {
                $startMainContactStyle = '- Supervisor';
                $endMainContactStyle   = '- Supervisor';
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
        $dbeSite    = new DBESite($this);
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
            $dataDelegate          = "";
            $contactSelected       = ($contactID == $dbeContact->getValue(DBEContact::contactID)) ? CT_SELECTED : null;
            $startMainContactStyle = null;
            $endMainContactStyle   = null;
            if ($dbeContact->getValue(DBEContact::supportLevel) == DBEContact::supportLevelMain) {
                $startMainContactStyle = '*';
                $endMainContactStyle   = '*';
            } elseif ($dbeContact->getValue(DBEContact::supportLevel) == DBEContact::supportLevelDelegate) {
                $startMainContactStyle = '- Delegate';
                $endMainContactStyle   = '- Delegate';
                $dataDelegate          = "data-delegate='true'";
            } elseif ($dbeContact->getValue(DBEContact::supportLevel) == DBEContact::supportLevelSupervisor) {
                $startMainContactStyle = '- Supervisor';
                $endMainContactStyle   = '- Supervisor';
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
        $errorMessage   = null;
        $callActivityID = $this->getParam('callActivityID');
        $linkedOrderID  = null;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($_POST['linkedOrderID']) {

                $linkedOrderID  = $_POST['linkedOrderID'];
                $callActivityID = $_POST['callActivityID'];
                $dbeSalesOrder  = new DBEOrdhead($this);
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
                        echo '<script type="text/javascript"> window.close(); </script>';
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
        $this->template->setVar('javaScript', "<link rel='stylesheet' href='components/shared/ToolTip.css'>");        

        $problemId             = $this->getParam('problemID');
        $activitiesByProblemID = $this->buActivity->getActivitiesByProblemID($problemId);
        $dbeProblem            = new DBEJProblem($this);
        $dbeProblem->getRow($problemId);
        $dbeJContract = new DBEJContract($this);
        $title        = $problemId . ' - ' . $dbeProblem->getValue(DBEJProblem::customerName)
                        .$this->getProblemRaiseIcon($dbeProblem);
        $this->template->set_block(
            'ActivityReasonPopup',
            'activityBlock',
            'rows'
        );
        $foundFirst     = false;
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
            $date      = null;
            if ($dateValue) {
                $dateTime = DateTime::createFromFormat(DATE_MYSQL_DATE, $dateValue);
                if ($dateTime) {
                    $date = $dateTime->format('d-m-Y');
                }
            }
            $startTime             = $activitiesByProblemID->getValue(DBEJCallActivity::startTime);
            $endTime               = $activitiesByProblemID->getValue(DBEJCallActivity::endTime);
            $duration              = number_format(
                $activitiesByProblemID->getValue(DBEJCallActivity::durationMinutes) / 60,
                2
            );
            $activityType          = $activitiesByProblemID->getValue(DBEJCallActivity::activityType);
            $contactName           = $activitiesByProblemID->getValue(DBEJCallActivity::contactName);
            $siteAddress           = $dsSite->getValue(DBESite::add1);
            $userName              = $activitiesByProblemID->getValue(DBEJCallActivity::userName);
            $reason                = $activitiesByProblemID->getValue(DBEJCallActivity::reason);
            $originalRequestHeader = null;
            $colorClass            = "boring-gray";
            if (!$foundFirst) {
                $originalRequestHeader = ' <tr>        <td class="redText">Original Request</td>    </tr>';
                $colorClass            = "performance-green";
                $foundFirst            = true;
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
                    'colorClass'            => $colorClass,
                )
            );
            if (!in_array($activitiesByProblemID->getValue(DBECallActivity::callActTypeID), [60, 61])) {
                $lastActivityID     = $activitiesByProblemID->getValue(DBECallActivity::callActivityID);
                $lastActivityText   = "$date $startTime - $endTime ($duration) $activityType - $contactName - $siteAddress - $userName";
                $lastActivityReason = $reason;
                $lastCncNextAction  = $activitiesByProblemID->getValue(DBEJCallActivity::cncNextAction);
                $lastCustomerSummary  = $activitiesByProblemID->getValue(DBEJCallActivity::customerSummary);

            }
            $this->template->parse(
                'rows',
                'activityBlock',
                true
            );
        }
        $this->template->set_block(
            'ActivityReasonPopup',
            'internalNotesBlock',
            'internalNotes'
        );
        $repo                         = new ServiceRequestInternalNotePDORepository();
        $internalNotes                = $repo->getServiceRequestInternalNotesForSR($problemId);
        $internalNotesConsultantNames = [];
        foreach ($internalNotes as $internalNote) {
            $updatedByUserId = $internalNote->getUpdatedBy();
            if (!key_exists($updatedByUserId, $internalNotesConsultantNames)) {
                $dbeUser = new DBEUser($this);
                $dbeUser->getRow($updatedByUserId);
                $internalNotesConsultantNames[$updatedByUserId] = "{$dbeUser->getValue(DBEUser::firstName)} {$dbeUser->getValue(DBEUser::lastName)}";
            }
            $this->template->set_var(
                array(
                    'internalNoteDate'          => $internalNote->getUpdatedAt()->format(DATE_MYSQL_DATETIME),
                    'internalNoteUpdatedByName' => $internalNotesConsultantNames[$updatedByUserId],
                    'internalNoteContent'       => $internalNote->getContent(),
                )
            );
            $this->template->parse(
                'internalNotes',
                'internalNotesBlock',
                true
            );
        }
        $url  = Controller::buildLink(
            'SRActivity.php',
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
            $contractDescription = $dbeJContract->getValue(
                    DBEJContract::itemDescription
                ) . ' ' . $dbeJContract->getValue(
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
                'contractDescription' => $contractDescription,
                'problemHiddenText'   => $problemHiddenText,
                'lastActivityText'    => $lastActivityText,
                'lastActivityReason'  => $lastActivityReason,
                'lastCncNextAction'   => $lastCncNextAction,
                'lastCustomerSummary' => $lastCustomerSummary
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

    function projectDropdown($projectID,
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
        $noProject           = ($projectID == 0) ? CT_SELECTED : null;
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
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'serviceRequestId' => $problemID
            )
        );
        if (!$problemID) {
            $urlNext = Controller::buildLink(
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
                $urlNext = Controller::buildLink(
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
        $previousEndTime   = $this->dsCallActivity->getValue(DBECallActivity::endTime);
        $this->formError   = (!$this->dsCallActivity->populateFromArray($this->getParam('callActivity')));
        if (!isset($this->getParam('callActivity')[1][DBECallActivity::hideFromCustomerFlag])) {
            $this->dsCallActivity->setValue(DBECallActivity::hideFromCustomerFlag, 'N');
        }
        if (!isset($this->getParam('callActivity')[1][DBECallActivity::submitAsOvertime])) {
            $this->dsCallActivity->setValue(DBECallActivity::submitAsOvertime, false);
        }
        $this->dsCallActivity->setUpdateModeUpdate();
        // these names must not be part of an html array as the fckeditor does not work
        $this->dsCallActivity->setValue(
            DBEJCallActivity::reason,
            $_POST['reason']
        );
        $this->dsCallActivity->post();
        if (($previousStartTime != $this->dsCallActivity->getValue(
                    DBECallActivity::startTime
                ) || $previousEndTime != $this->dsCallActivity->getValue(
                    DBECallActivity::endTime
                )) && $this->dsCallActivity->getValue(DBECallActivity::overtimeExportedFlag) == 'N') {
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
                if (!$this->getParam('Update') && $dbeCallActType->getValue(
                        DBECallActType::requireCheckFlag
                    ) == 'N' && $dbeCallActType->getValue(
                        DBECallActType::onSiteFlag
                    ) == 'N' && !$this->dsCallActivity->getValue(DBEJCallActivity::endTime)) {
                    $this->dsCallActivity->setValue(
                        DBEJCallActivity::endTime,
                        date('H:i')
                    );
                }
                if (!trim($this->dsCallActivity->getValue(DBEJCallActivity::reason))) {
                    $this->formError = true;
                    $this->dsCallActivity->setMessage(
                        DBEJCallActivity::reason,
                        'Required'
                    );
                }
                if ($this->dsCallActivity->getValue(
                        DBEJCallActivity::contractCustomerItemID
                    ) && $this->dsCallActivity->getValue(DBEJCallActivity::projectID)) {
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

                    $dbeFirstActivity = $this->buActivity->getFirstActivityInServiceRequest(
                        $this->dsCallActivity->getValue(DBEJCallActivity::problemID)
                    );
                    if ($this->dsCallActivity->getValue(DBEJCallActivity::date) . $this->dsCallActivity->getValue(
                            DBEJCallActivity::startTime
                        ) < $dbeFirstActivity->getValue(DBEJCallActivity::date) . $dbeFirstActivity->getValue(
                            DBEJCallActivity::startTime
                        )) {
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
                    $durationHours   = common_convertHHMMToDecimal(
                            $this->dsCallActivity->getValue(DBEJCallActivity::endTime)
                        ) - common_convertHHMMToDecimal($this->dsCallActivity->getValue(DBEJCallActivity::startTime));
                    $durationMinutes = convertHHMMToMinutes(
                            $this->dsCallActivity->getValue(DBEJCallActivity::endTime)
                        ) - convertHHMMToMinutes($this->dsCallActivity->getValue(DBEJCallActivity::startTime));
                    $activityType    = $this->dsCallActivity->getValue(DBEJCallActivity::callActTypeID);
                    if (in_array(
                        $activityType,
                        [4, 8, 11, 18]
                    )) {
                        $problemID = $this->dsCallActivity->getValue(DBEJCallActivity::problemID);
                        $userID    = $this->dsCallActivity->getValue(DBEJCallActivity::userID);
                        $dbeUser   = new DBEUser($this);
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
                            $usedTime      = 0;
                            $allocatedTime = 0;
                            if ($teamID == 1) {
                                $usedTime      = $this->buActivity->getHDTeamUsedTime(
                                    $problemID,
                                    $callActivityID
                                );
                                $allocatedTime = $dbeProblem->getValue(DBEProblem::hdLimitMinutes);
                            }
                            if ($teamID == 2) {
                                $usedTime      = $this->buActivity->getESTeamUsedTime(
                                    $problemID,
                                    $callActivityID
                                );
                                $allocatedTime = $dbeProblem->getValue(DBEProblem::esLimitMinutes);
                            }
                            if ($teamID == 4) {
                                $usedTime      = $this->buActivity->getSPTeamUsedTime(
                                    $problemID,
                                    $callActivityID
                                );
                                $allocatedTime = $dbeProblem->getValue(DBEProblem::smallProjectsTeamLimitMinutes);
                            }
                            if ($teamID == 5) {
                                $usedTime      = $this->buActivity->getUsedTimeForProblemAndTeam(
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
                            if ($this->dsCallActivity->getValue(
                                    DBEJCallActivity::callActTypeID
                                ) == CONFIG_CUSTOMER_CONTACT_ACTIVITY_TYPE_ID && $durationHours > $dsHeader->getValue(
                                    DBEHeader::customerContactWarnHours
                                )) {
                                $this->formError  = true;
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
                                    $this->formError  = true;
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
                                    $this->formError  = true;
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
                $this->formError        = true;
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
        if ($enteredEndTime && $dbeCallActType->getValue(
                DBECallActType::onSiteFlag
            ) == 'Y' && $dbeCallActType->getValue(DBEJCallActType::itemSalePrice) > 0) {
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
            if ($this->buActivity->travelActivityForCustomerEngineerTodayExists(
                    $this->dsCallActivity->getValue(DBEJCallActivity::customerID),
                    $this->dsCallActivity->getValue(DBEJCallActivity::siteNo),
                    $this->dsCallActivity->getValue(DBEJCallActivity::userID),
                    $this->dsCallActivity->getValue(DBEJCallActivity::date)
                ) && $dbeSite->getValue(DBESite::maxTravelHours) > 0    // the site has travel hours
            ) {
                $urlNext = Controller::buildLink(
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
            $urlNext = Controller::buildLink(
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
        $urlNext = Controller::buildLink(
            'SRActivity.php',
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
        $urlNext       = Controller::buildLink(
        //$_SERVER['PHP_SELF'],
            "SRActivity.php",
            array(
                'action'         => CTACTIVITY_ACT_EDIT_ACTIVITY,
                'callActivityID' => $newActivityID,
                'isFollow'       => true
            )
        );
        header('Location: ' . $urlNext);
    }

    /**
     * @throws Exception
     */
    function updateRequestFromCustomerRequest()
    {
        $customerProblemNo = $this->getParam('cpr_customerproblemno');
        $customerProblem   = $this->buActivity->getCustomerRaisedRequest($customerProblemNo);
        $dbeCallActivity   = $this->buActivity->getLastActivityInProblem($customerProblem['cpr_problemno']);
        $callActivityID    = $this->buActivity->createFollowOnActivity(
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
        $urlNext = Controller::buildLink(
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
        if ($this->buActivity->linkActivities(
            $this->getParam('fromCallActivityID'),
            $this->getParam('toCallActivityID'),
            $wholeProblem
        )) {

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
        $callRef    = $dsCallActivity->getValue(DBEJCallActivity::callActivityID);
        if (!$dsCallActivity->getValue(DBEJCallActivity::endTime)) {
            $endTime = $dsCallActivity->getValue(DBEJCallActivity::startTime);
        } else {
            $endTime = $dsCallActivity->getValue(DBEJCallActivity::endTime);
        }
        $urlActivity    = SITE_URL . Controller::buildLink(
                'SRActivity.php',
                array(
                    'callActivityID' => $dsCallActivity->getValue(DBEJCallActivity::callActivityID),
                    'action'         => CTACTIVITY_ACT_DISPLAY_ACTIVITY
                )
            );
        $what3WordsLink = "";
        if ($dsSite->getValue(DBESite::what3Words)) {
            $what3WordsLink = "https://what3words.com/{$dsSite->getValue(DBESite::what3Words)}\n\n";
        }
        $notes             = 'Details:\n\n' . CTActivity::prepareForICS(
                $dsCallActivity->getValue(DBEJCallActivity::reason)
            );
        $internalNotesRepo = new ServiceRequestInternalNotePDORepository();
        $internalNotes     = $internalNotesRepo->getServiceRequestInternalNotesForSR(
            $dsCallActivity->getValue(DBEJCallActivity::problemID)
        );
        if (count($internalNotes)) {
            $notes                    .= '\n\nInternal Notes:\n\n';
            $internalNotesConsultants = [];
            foreach ($internalNotes as $internalNote) {
                $updatedByConsultantId = $internalNote->getUpdatedBy();
                if (!key_exists($updatedByConsultantId, $internalNotesConsultants)) {
                    $updatedByConsultant = new DBEUser($this);
                    $updatedByConsultant->getRow($updatedByConsultantId);
                    $internalNotesConsultants[$updatedByConsultantId] = "{$updatedByConsultant->getValue(DBEUser::firstName)} {$updatedByConsultant->getValue(DBEUser::lastName)}";
                }
                $notes             .= "\n\n{$internalNote->getUpdatedAt()->format(DATE_CNC_DATE_TIME_FORMAT)} by {$internalNotesConsultants[$updatedByConsultantId]}";
                $processedContents = CTActivity::prepareForICS($internalNote->getContent());
                $notes             .= "\n\n{$processedContents}";
            }
            $notes .= "\n\n";
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
        $urlNext = Controller::buildLink(
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
        $urlNext = Controller::buildLink(
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
        $query  = "SELECT * FROM calldocument
      WHERE callDocumentID = " . $this->getParam('callDocumentID');
        $result = mysqli_query(
            $db,
            $query
        );
        $row    = mysqli_fetch_assoc($result);
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
    private function redirectToGather($callActivityID)
    {
        $urlNext = Controller::buildLink(
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
        // go to new react page
        $newUrl = str_replace("Activity.php", "SRActivity.php", $this->getFullPath());
        header('Location: ' . $newUrl);
        return;
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

                    $nextURL = Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'problemID' => $dsCallActivity->getValue(DBEJCallActivity::problemID),
                            'action'    => 'gatherManagementReviewDetails'
                        )
                    );

                } else {
                    $nextURL = Controller::buildLink(
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
        $submitURL = Controller::buildLink(
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
        $uploadURL         = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'         => 'gatherFixedInformation',
                'problemID'      => $dsCallActivity->getValue(DBEJCallActivity::problemID),
                'callActivityID' => $this->getParam('callActivityID')
            )
        );
        $urlMessageToSales = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'         => 'messageToSales',
                'callActivityID' => $this->getParam('callActivityID'),
            )
        );
        $urlSalesRequest   = Controller::buildLink(
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
                'SRLink'                        => "<a href='SRActivity.php?serviceRequestId=" . $dsCallActivity->getValue(
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
        if ($dsCallActivity->getValue(DBEJCallActivity::priority) < 4 or /*
        User in Accounts and priority > 3
        */ ($this->hasPermissions(ACCOUNTS_PERMISSION) && $dsCallActivity->getValue(
                    DBEJCallActivity::priority
                ) > 3) or /*
        priority > 3 and activity hours greater than system threshold
        */ ($dsCallActivity->getValue(DBEJCallActivity::priority) > 3 and $dsCallActivity->getValue(
                    DBEJCallActivity::totalActivityDurationHours
                ) < $dsHeader->getValue(DBEHeader::srPromptContractThresholdHours))) {
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
                $nextURL = Controller::buildLink(
                    'CurrentActivityReport.php',
                    array()
                );
                header('Location: ' . $nextURL);
                exit;

            }

        }// end IF POST
        $submitURL = Controller::buildLink(
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
        $dbeFirstActivity = $this->buActivity->getFirstActivityInServiceRequest($this->getParam('problemID'));
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
            $nextURL = Controller::buildLink(
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
        $urlProblemHistoryPopup = Controller::buildLink(
            'Activity.php',
            array(
                'action'    => 'problemHistoryPopup',
                'problemID' => $this->getParam('problemID'),
                'htmlFmt'   => CT_HTML_FMT_POPUP
            )
        );
        $submitURL              = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => 'allocateAdditionalTime'
            )
        );
        $dbeProblem             = new DBEProblem($this);
        $dbeProblem->getRow($this->getParam('problemID'));
        $helpdeskHardLimitRemainingMinutes      = $dsHeader->getValue(
                DBEHeader::hdTeamManagementTimeApprovalMinutes
            ) - $dbeProblem->getValue(DBEProblem::hdLimitMinutes);
        $escalationsHardLimitRemainingMinutes   = $dsHeader->getValue(
                DBEHeader::esTeamManagementTimeApprovalMinutes
            ) - $dbeProblem->getValue(DBEProblem::esLimitMinutes);
        $smallProjectsHardLimitRemainingMinutes = $dsHeader->getValue(
                DBEHeader::smallProjectsTeamManagementTimeApprovalMinutes
            ) - $dbeProblem->getValue(DBEProblem::smallProjectsTeamLimitMinutes);
        $isAdditionalTimeApprover               = $this->dbeUser->getValue(DBEUser::additionalTimeLevelApprover);
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

    function allocatedMinutesDropdown($selectedID,
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
    function displayContracts($customerID,
                              $templateName,
                              $blockName = 'contractBlock'
    )
    {
        $buCustomerItem = new BUCustomerItem($this);
        $dsContract     = new DataSet($this);
        $buCustomerItem->getContractsByCustomerID(
            $customerID,
            $dsContract,
            null
        );
        $itemTypes = [];
        $items     = [];
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
            $urlRenewalContract = Controller::buildLink(
                'CustomerItem.php',
                array(
                    'action'         => 'displayRenewalContract',
                    'customerItemID' => $item[DBEJContract::customerItemID]
                )
            );
            $description        = $item[DBEJContract::itemDescription] . ' ' . $item[DBEJContract::adslPhone] . ' ' . $item[DBEJContract::notes] . ' ' . $item[DBEJContract::postcode];
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
        $urlSubmit = Controller::buildLink(
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
        $message        = $this->getParam('message');
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
    private function unhideSR()
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
        $lastName  = $this->dbeUser->getValue(DBEUser::lastName);
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
        $message   = $this->getParam('message');
        $problemID = $this->getParam('problemID');
        $type      = $this->getParam('type');
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
        $message   = $this->getParam('message');
        $problemID = $this->getParam('problemID');
        $type      = $this->getParam('type');
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
        /** @var dbSweetcode $db */ global $db;
        $result = $db->preparedQuery($query, [["type" => "i", "value" => $problemID]]);
        $test   = $result->fetch_assoc();
        return !!$test['hiddenChargeableActivities'];
    }

    private function assignToBeLoggedToServiceRequest($toBeLogged, $serviceRequestId)
    {
        $usecase = new \CNCLTD\core\domain\usecases\AssignToBeLoggedToServiceRequest();
        $usecase->__invoke($toBeLogged, $serviceRequestId, $this->dbeUser);
    }

    private function assignLinkedSalesOrderToServiceRequestController()
    {
        $jsonData         = $this->getJSONData();
        $serviceRequestId = $jsonData['serviceRequestId'];
        $salesOrderId     = $jsonData['salesOrderId'];
        $dbeSalesOrder    = new DBEOrdhead($this);
        try {

            if (!$dbeSalesOrder->getRow($salesOrderId)) {
                error_log('sales order does not exist ..return json error');
                throw new \CNCLTD\Exceptions\JsonHttpException(123, "Sales Order Does Not Exist");
            }
            $dbeProblem = new DBEProblem($this);
            if (!$dbeProblem->getRow($serviceRequestId)) {
                throw new \CNCLTD\Exceptions\JsonHttpException(123, "Service Request Does Not Exist");
            }
            if ($dbeProblem->getValue(DBEProblem::linkedSalesOrderID)) {
                throw new \CNCLTD\Exceptions\JsonHttpException(123, "Service Request already has a linked sales order");
            }
            if ($dbeSalesOrder->getValue(DBEOrdhead::customerID) !== $dbeProblem->getValue(DBEProblem::customerID)) {
                throw new \CNCLTD\Exceptions\JsonHttpException(
                    123, "The given sales order does not belong to the customer of the Service Request"
                );
            }
            $dbeProblem->setValue(
                DBEJProblem::linkedSalesOrderID,
                $salesOrderId
            );
            $dbeProblem->updateRow();
            echo json_encode(["status" => "ok"]);
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }
}