<?php
/**
 * Customer Activity Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEPendingReopened.php');

// Actions
class CTCurrentActivityReportOld extends CTCNC
{

    const AMBER = '#FFF5B3';
    const RED = '#F8A5B6';
    const GREEN = '#BDF8BA';
    const /** @noinspection SpellCheckingInspection */
        BLUE = '#b2daff';
    const CONTENT = null;
    const PURPLE = '#dcbdff';
    const ORANGE = '#FFE6AB';
    var $filterUser = array();
    var $allocatedUser = array();
    var $priority = array();
    var $loggedInUserIsSdManager;
    var $customerFilterList;
    /**
     * @var BUCustomerItem
     */
    public $buCustomerItem;
    /**
     * @var BUActivity
     */
    public $buActivity;

    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
                         $cfg,
                         $checkPermissions = true
    )
    {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );

        if ($checkPermissions) {

            $roles = [
                "technical",
            ];
            if (!self::hasPermissions($roles)) {
                Header("Location: /NotAllowed.php");
                exit;
            }
        }


        $this->buActivity = new BUActivity($this);

        $this->buCustomerItem = new BUCustomerItem($this);

        $dbeUser = new DBEUser($this);

        $dbeUser->getRows('firstName');

        while ($dbeUser->fetchNext()) {

            $userRow =
                array(
                    'userID'   => $dbeUser->getValue(DBEUser::userID),
                    'userName' => $dbeUser->getValue(DBEUser::name),
                    'fullName' => $dbeUser->getValue(DBEUser::firstName) . ' ' . $dbeUser->getValue(
                            DBEUser::lastName
                        )
                );

            $this->allocatedUser[$dbeUser->getValue(DBEUser::userID)] = $userRow;

            if ($dbeUser->getValue(DBEUser::appearInQueueFlag) == 'Y') {

                $this->filterUser[$dbeUser->getValue(DBEUser::userID)] = $userRow;
            }
        }

        if (!$this->getSessionParam('priorityFilter')) {
            $priorityFilter = $this->getSessionParam('priorityFilter');

            if (!$priorityFilter) {
                $priorityFilter = [];
            }
            foreach ($this->buActivity->priorityArray as $key => $value) {
                $priorityFilter[] = $key;
            }
            $this->setSessionParam('priorityFilter', $priorityFilter);
        }

        $buUser = new BUUser($this);
        $this->loggedInUserIsSdManager = $buUser->isSdManager($this->userID);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {


        if (!$this->getSessionParam('displayToBeLoggedSectionFlag')) {
            $this->setSessionParam('displayToBeLoggedSectionFlag', 1);
        }
        if (!isset($_SESSION['displayQueue1Flag'])) {
            $this->setSessionParam('displayQueue1Flag', 1);
        }
        if (!isset($_SESSION['displayQueue2Flag'])) {
            $this->setSessionParam('displayQueue2Flag', 1);
        }
        if (!isset($_SESSION['displayQueue3Flag'])) {
            $this->setSessionParam('displayQueue3Flag', 1);
        }
        if (!isset($_SESSION['displayQueue4Flag'])) {
            $this->setSessionParam('displayQueue4Flag', 1);
        }
        if (!isset($_SESSION['displayQueue5Flag'])) {
            $this->setSessionParam('displayQueue5Flag', 1);
        }
        if (!isset($_SESSION['displayQueue7Flag'])) {
            $this->setSessionParam('displayQueue7Flag', 1);
        }

        switch ($this->getAction()) {

            case 'allocateUser':
                $this->allocateUser();
                break;

            case 'showMineOnly':
                $this->showMineOnly();
                break;

            case 'setFilter':
                $this->setFilter();
                break;

            case 'resetFilter':
                $this->resetFilter();
                break;

            case 'toggleDisplayToBeLoggedFlag':
                $this->toggleDisplayFlag('displayToBeLoggedFlag');
                break;
            case 'toggleDisplayPendingReopenedFlag':
                $this->toggleDisplayFlag('pendingReopened');
                break;

            case 'toggleDisplayQueue1Flag':
                $this->toggleDisplayFlag('displayQueue1Flag');
                break;

            case 'toggleDisplayQueue2Flag':
                $this->toggleDisplayFlag('displayQueue2Flag');
                break;

            case 'toggleDisplayQueue3Flag':
                $this->toggleDisplayFlag('displayQueue3Flag');
                break;

            case 'toggleDisplayQueue4Flag':
                $this->toggleDisplayFlag('displayQueue4Flag');
                break;

            case 'toggleDisplayQueue5Flag':
                $this->toggleDisplayFlag('displayQueue5Flag');
                break;

            case 'toggleDisplayQueue6Flag':
                $this->toggleDisplayFlag('displayQueue6Flag');
                break;

            case 'toggleDisplayQueue7Flag':
                $this->toggleDisplayFlag('displayQueue7Flag');
                break;
            case 'changeQueue':
                $this->changeQueue();
                break;
            case 'deleteCustomerRequest':
                $this->checkPermissions(TECHNICAL_PERMISSION);
                $this->deleteCustomerRequest();
                break;
            case 'pendingReopenedPopup':
                $this->pendingReopenedDescriptionPopUp();
                break;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'processPendingReopened':
                $this->processPendingReopened($_REQUEST['pendingReopenedID'], $_REQUEST['result']);
            default:
                $this->displayReport();
                break;
        }
    }

    /**
     * @param array $options
     * @throws Exception
     */
    function allocateUser($options = [])
    {
        $dbeUser = new DBEUser ($this);
        $dbeUser->setValue(
            DBEUser::userID,
            $this->userID
        );
        $dbeUser->getRow();

        $this->buActivity->allocateUserToRequest(
            $this->getParam('problemID'),
            $this->getParam('userID') == 0 ? null : $this->getParam('userID'),
            $dbeUser
        );

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                $options ? $options : []
            );

        header('Location: ' . $urlNext);
        exit;
    }

    /**
     * @throws Exception
     */
    function showMineOnly()
    {
        $this->unsetSessionParam('selectedUserID');

        $this->setSessionParam('selectedUserID', $GLOBALS['auth']->is_authenticated());

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array()
            );
        header('Location: ' . $urlNext);
        exit;

    }

    /**
     * Set filtering
     * @throws Exception
     */
    function setFilter()
    {
        $this->setSessionParam('selectedUserID', $this->getParam('selectedUserID'));
        $this->setSessionParam('priorityFilter', $this->getParam('priorityFilter'));
        $this->setSessionParam('selectedCustomerID', $this->getParam('selectedCustomerID'));
        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array()
            );

        header('Location: ' . $urlNext);
        exit;
    }

    /**
     * Remove all filters
     * @throws Exception
     */
    function resetFilter()
    {

        $this->unsetSessionParam('selectedUserID');
        $this->unsetSessionParam('selectedCustomerID');
        $this->unsetSessionParam('priorityFilter');

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array()
            );

        header('Location: ' . $urlNext);
        exit;
    }

    function toggleDisplayFlag($flag)
    {
        if ($this->getSessionParam($flag)) {
            $this->setSessionParam($flag, false);
        } else {
            $this->setSessionParam($flag, true);
        }
    }

    /**
     * @throws Exception
     */
    function changeQueue()
    {
        $problemID = $this->getParam('problemID');
        $newQueue = $this->getParam('queue');
        $reason = $this->getParam('reason');

        $this->buActivity->escalateProblemByProblemID(
            $problemID,
            $reason,
            $newQueue
        );

        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array()
        );
        header('Location: ' . $urlNext);
        exit;

    }

    /**
     * @throws Exception
     */
    function deleteCustomerRequest()
    {
        $customerproblemno = $this->getParam('cpr_customerproblemno');

        $this->buActivity->deleteCustomerRaisedRequest($customerproblemno);
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array()
        );
        header('Location: ' . $urlNext);
        exit;

    }

    /**
     * @throws Exception
     */
    function pendingReopenedDescriptionPopUp()
    {
        $this->setTemplateFiles(
            'ActivityCustomerProblemPopup',
            'ActivityCustomerProblemPopup.inc'
        );

        $this->setPageTitle('Pending Reopened Description');
        $pendingReopenedID = $this->getParam('pendingReopenedID');
        if (!$pendingReopenedID) {
            throw new Exception('Pending reopened ID is missing');
        }

        $dbePendingReopened = new DBEPendingReopened($this);
        $dbePendingReopened->getRow($pendingReopenedID);

        $this->template->set_var(
            array(
                'details' => str_replace(
                    "\n",
                    "<br/>",
                    $dbePendingReopened->getValue(DBEPendingReopened::reason)
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
    }  // end finaliseProblem

    /**
     * @param $pendingReopenedID
     * @param $result
     * @throws Exception
     */
    private function processPendingReopened($pendingReopenedID, $result)
    {
        $dbePendingReopened = new DBEPendingReopened($this);
        switch ($result) {
            case 'R':
            {
                $dbePendingReopened->getRow($pendingReopenedID);
                $this->buActivity->approvePendingReopened($dbePendingReopened);
                break;
            }
            case 'D':
            {
                $dbePendingReopened->deleteRow($pendingReopenedID);
                break;
            }
        }
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            []
        );
        header('Location: ' . $urlNext);
    }

    /**
     * Display search form
     * @access private
     * @throws Exception
     */
    function displayReport()
    {

        $this->setMethodName('displayReport');
        $this->template->setVar("menuId", 103);
        unset($this->customerFilterList);   // for the customer filter drop-down (limited to customers with SRs)

        $this->setTemplateFiles(
            array(
                'CurrentActivityReport' => 'CurrentActivityReportEngineer.inc',
            )
        );


        $this->setTemplateFiles(
            'CurrentActivityReport',
            'CurrentActivityReport.inc'
        );

        $openSrsByUser = $this->buActivity->getOpenSrsByUser();

        $this->template->set_block(
            'CurrentActivityReport',
            'userSrCountBlock',
            'userSrCount'
        );

        foreach ($openSrsByUser as $row) {

            $this->template->set_var(

                array(
                    'openSrInitials' => $row['initials'],
                    'openSrCount'    => $row['count']
                )
            );

            $this->template->parse(
                'userSrCount',
                'userSrCountBlock',
                true
            );

        }

        $pendingReopenedRequests = $this->buActivity->getPendingReopenedRequests();
        if ($pendingReopenedRequests && count($pendingReopenedRequests)) {
            $this->template->set_block(
                'CurrentActivityReport',
                'pendingReopenedBlock',
                'pendingReopenedRequests'
            );

            foreach ($pendingReopenedRequests as $pendingReopenedRequest) {
                $pendingReopenSRURL = Controller::buildLink(
                    'Activity.php',
                    array(
                        'action'    => 'displayLastActivity',
                        'problemID' => $pendingReopenedRequest['problemID']
                    )
                );
                $pendingReopenSR = $pendingReopenedRequest['problemID'];
                $pendingReopenCustomerName = $pendingReopenedRequest['customerName'];
                $pendingReopenedPriority = $pendingReopenedRequest['priority'];
                $truncatedReason = CTCurrentActivityReportOld::truncate(
                    $pendingReopenedRequest['reason'],
                    150
                );
                $pendingReopenDescriptionSummary = $truncatedReason;
                $pendingReopenDescriptionURL =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'            => 'pendingReopenedPopup',
                            'pendingReopenedID' => $pendingReopenedRequest['id'],
                            'htmlFmt'           => CT_HTML_FMT_POPUP
                        )
                    );
                $this->template->set_var(
                    [
                        "pendingReopenSRURL"              => $pendingReopenSRURL,
                        "pendingReopenSR"                 => $pendingReopenSR,
                        "pendingReopenCustomerName"       => $pendingReopenCustomerName,
                        "pendingReopenPriority"           => $pendingReopenedPriority,
                        "pendingReopenDescriptionURL"     => $pendingReopenDescriptionURL,
                        "pendingReopenDescriptionSummary" => $pendingReopenDescriptionSummary,
                        "pendingReopenedID"               => $pendingReopenedRequest['id'],
                        "receivedDate"                    => $pendingReopenedRequest['createdAt'],
                        "pendingReopenedCustomerID"       => $pendingReopenedRequest['customerID'],
                        "pendingReopenedContactID"        => $pendingReopenedRequest['contactID'],
                        "base64Reason"                    => base64_encode($pendingReopenedRequest['reason']),

                    ]
                );

                $this->template->parse(
                    'pendingReopenedRequests',
                    'pendingReopenedBlock',
                    true
                );
                $this->template->setVar('pendingReopenedCount', count($pendingReopenedRequests));
            }
        }


        $customerRaisedRequests = $this->buActivity->getCustomerRaisedRequests();
        /*
        Requests raised via the customer portal
        */
        $customerRaisedRequests->next_record();

        $count = 0;

        if ($customerRaisedRequests->Record) {

            $this->template->set_block(
                'CurrentActivityReport',
                'customerRequestsBlock',
                'customerRequests'
            );

            do {

                $urlCreateRequestFromCustomerRequest =
                    Controller::buildLink(
                        'Activity.php',
                        array(
                            'action'                => 'createRequestFromCustomerRequest',
                            'cpr_customerproblemno' => $customerRaisedRequests->Record['cpr_customerproblemno']
                        )
                    );

                $createRequestOnClick = "document.location='" . $urlCreateRequestFromCustomerRequest . "'";

                $newButton = '
          <INPUT
          type="button"
          value="N"
          title="Create New"
          onClick="' . $createRequestOnClick . '">';


                $updateButton = null;
                if ($customerRaisedRequests->Record['cpr_update_existing_request'] == 1) {
                    $urlUpdateCustomerRequest =
                        Controller::buildLink(
                            'Activity.php',
                            array(
                                'action'                => 'updateRequestFromCustomerRequest',
                                'cpr_customerproblemno' => $customerRaisedRequests->Record['cpr_customerproblemno']
                            )
                        );

                    $updateRequestOnClick = "document.location='" . $urlUpdateCustomerRequest . "'";

                    $updateButton = '<input type="button" value="U"      title="Update Existing" onClick="' . $updateRequestOnClick . '">';
                }

                $urlDeleteCustomerRequest =
                    Controller::buildLink(
                        'CurrentActivityReport.php',
                        array(
                            'action'                => 'deleteCustomerRequest',
                            'cpr_customerproblemno' => $customerRaisedRequests->Record['cpr_customerproblemno']
                        )
                    );

                $urlCustomer =
                    Controller::buildLink(
                        'SalesOrder.php',
                        array(
                            'action'     => 'search',
                            'customerID' => $customerRaisedRequests->Record['con_custno']
                        )
                    );
                $txtServiceRequestID = null;
                $urlServiceRequest = null;
                if ($customerRaisedRequests->Record['cpr_problemno'] > 0) {
                    $urlServiceRequest =
                        Controller::buildLink(
                            'Activity.php',
                            array(
                                'action'    => 'displayServiceRequest',
                                'problemID' => $customerRaisedRequests->Record['cpr_problemno']
                            )
                        );
                    $txtServiceRequestID = $customerRaisedRequests->Record['cpr_problemno'];
                }

                $truncatedReason = CTCurrentActivityReportOld::truncate(
                    $customerRaisedRequests->Record['cpr_reason'],
                    150
                );

                $bgColour = self::RED;    // customer raised
                if ($customerRaisedRequests->Record['cpr_source'] == 'S') {
                    $bgColour = self::CONTENT;
                }
                $count++;

                $urlDetailsPopup =
                    Controller::buildLink(
                        'Activity.php',
                        array(
                            'action'            => 'customerProblemPopup',
                            'customerProblemID' => $customerRaisedRequests->Record['cpr_customerproblemno'],
                            'htmlFmt'           => CT_HTML_FMT_POPUP
                        )
                    );


                $this->template->set_var(

                    array(
                        'urlDetailsPopup'          => $urlDetailsPopup,
                        'cpCustomerProblemID'      => $customerRaisedRequests->Record['cpr_customerproblemno'],
                        'cpNewButton'              => $newButton,
                        'cpUpdateButton'           => $updateButton,
                        'cpCustomerName'           => $customerRaisedRequests->Record['cus_name'],
                        'cpContactName'            => $customerRaisedRequests->Record['con_first_name'] . ' ' . $customerRaisedRequests->Record['con_last_name'],
                        'cpDate'                   => $customerRaisedRequests->Record['cpr_date'],
                        'cpServiceRequestID'       => $txtServiceRequestID,
                        'cpUrlServiceRequest'      => $urlServiceRequest,
                        'cpPriority'               => $customerRaisedRequests->Record['cpr_priority'],
                        'cpTruncatedReason'        => $truncatedReason,
                        'cpFullReason'             => $customerRaisedRequests->Record['cpr_reason'],
                        'cpUrlCustomer'            => $urlCustomer,
                        'urlDeleteCustomerRequest' => $urlDeleteCustomerRequest,
                        'cpBgColor'                => $bgColour,
                        'cpCount'                  => $count
                    )

                );

                $this->template->parse(
                    'customerRequests',
                    'customerRequestsBlock',
                    true
                );

            } while ($customerRaisedRequests->next_record());

        }

        $this->template->set_var(
            'customerPortalCount',
            $count
        );

        $this->setTemplateFlags($this->template);
        $this->setPageTitle(CONFIG_SERVICE_REQUEST_DESC . 's');

        $this->renderQueue(1);  // Helpdesk
        $this->renderQueue(2);  // Escalations
        $this->renderQueue(3);  // Sales
        $this->renderQueue(4);  // Small Projects
        $this->renderQueue(5);  // Projects

        if ($this->getSessionParam('selectedCustomerID')) {
            $this->renderQueue(6);  //Fixed
        } else {
            $this->template->set_block(
                'CurrentActivityReport',
                'queue6Block',
                'requests6'
            );
            $this->template->set_var(

                array(

                    'workOnClick'                => null,
                    'hoursRemaining'             => null,
                    'updatedBgColor'             => null,
                    'priorityBgColor'            => null,
                    'hoursRemainingBgColor'      => null,
                    'totalActivityDurationHours' => null,
                    'hdRemaining'                => null,
                    'esRemaining'                => null,
                    'spRemaining'                => null,
                    'hdColor'                    => null,
                    'esColor'                    => null,
                    'spColor'                    => null,
                    'urlCustomer'                => null,
                    'time'                       => null,
                    'date'                       => null,
                    'problemID'                  => null,
                    'reason'                     => null,
                    'urlProblemHistoryPopup'     => null,
                    'engineerDropDown'           => null,
                    'engineerName'               => null,
                    'customerName'               => null,
                    'customerNameDisplayClass'   => null,
                    'urlViewActivity'            => null,
                    'linkAllocateAdditionalTime' => null,
                    'slaResponseHours'           => null,
                    'priority'                   => null,
                    'alarmDateTime'              => null,
                    'bgColour'                   => null,
                    'workBgColor'                => null,

                )

            );
            $this->template->parse(
                'requests6',
                'queue6Block',
                true
            );

        }

        $this->renderQueue(7); // Future

        $this->template->set_block(
            'CurrentActivityReport',
            'userFilterBlock',
            'users'
        );

        $loggedInUserID = $this->userID;

        usort(
            $this->filterUser,
            function ($a,
                      $b
            ) use (
                $loggedInUserID
            ) {

                if ($a['userID'] == $loggedInUserID) {
                    return -1;
                }

                if ($b['userID'] == $loggedInUserID) {
                    return 1;
                }
                return strcasecmp(
                    $a['fullName'],
                    $b['fullName']
                );
            }
        );

        foreach ($this->filterUser as $value) {

            $userSelected = null;
            if ($value['userID'] == $this->getSessionParam('selectedUserID')) {
                $userSelected = 'SELECTED';
            }

            $this->template->set_var(
                array(
                    'filterUserName'     => $value['fullName'],
                    'filterUserID'       => $value['userID'],
                    'filterUserSelected' => $userSelected
                )
            );

            $this->template->parse(
                'users',
                'userFilterBlock',
                true
            );
        }
        /*
        Priority Filter
        */
        $this->template->set_block(
            'CurrentActivityReport',
            'priorityFilterBlock',
            'priorityFilters'
        );

        foreach ($this->buActivity->priorityArray as $key => $value) {

            $checked = null;
            if (in_array($key, $this->getSessionParam('priorityFilter'))
            ) {
                $checked = 'checked';
            }

            $this->template->set_var(
                ['priority' => $key, 'priorityChecked' => $checked]
            );

            $this->template->parse(
                'priorityFilters',
                'priorityFilterBlock',
                true
            );

        }
        // end priority filter

        /*
        customer filter
        */
        $this->template->set_block(
            'CurrentActivityReport',
            'customerFilterBlock',
            'customers'
        );

        if ($this->customerFilterList) {
            asort($this->customerFilterList);
            foreach ($this->customerFilterList as $customerID => $customerName) {
                $customerIDSelected = null;
                if ($this->getSessionParam('selectedCustomerID') == $customerID) {
                    $customerIDSelected = 'SELECTED';
                }
                $this->template->set_var(
                    array(
                        'filterCustomerIDSelected' => $customerIDSelected,
                        'filterCustomerID'         => $customerID,
                        'filterCustomerName'       => $customerName
                    )
                );

                $this->template->parse(
                    'customers',
                    'customerFilterBlock',
                    true
                );
            }
        }
        /*
        end customer filter
        */
        $urlSetFilter =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'setFilter'
                )
            );

        $urlResetFilter =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array('action' => 'resetFilter')
            );

        $urlShowMineOnly =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array('action' => 'showMineOnly')
            );
        $this->template->set_var(
            array(
                'urlResetFilter'  => $urlResetFilter,
                'urlShowMineOnly' => $urlShowMineOnly,
                'urlSetFilter'    => $urlSetFilter,
                'isSDManager'     => $this->loggedInUserIsSdManager ? 'true' : 'false'
            )

        );


        $this->template->parse(
            'CONTENTS',
            'CurrentActivityReport',
            true
        );


        $this->parsePage();

    }

    private function setTemplateFlags(Template $template)
    {
        $flags = [
            'displayToBeLoggedFlag',
            'displayPendingReopenedFlag',
            'displayQueue1Flag',
            'displayQueue2Flag',
            'displayQueue3Flag',
            'displayQueue4Flag',
            'displayQueue5Flag',
            'displayQueue6Flag',
            'displayQueue7Flag',
        ];
        foreach ($flags as $flag) {
            $template->set_var(
                $flag,
                ($this->getSessionParam($flag) == 0) ? '0' : '1'
            );
        }
    }

    /**
     * @param $queueNo
     * @throws Exception
     */
    private function renderQueue($queueNo)
    {
        /** @var DBEProblem|DataSet $serviceRequests */
        $serviceRequests = new DataSet($this);
        if ($queueNo == 6) {
            /* fixed awaiting closure */
            $this->buActivity->getProblemsByStatus(
                'F',
                $serviceRequests,
                false
            );

        } elseif ($queueNo == 7) {
            /* future dated */
            $this->buActivity->getFutureProblems($serviceRequests);

        } else {
            $this->buActivity->getProblemsByQueueNo(
                $queueNo,
                $serviceRequests
            );
        }

        $queueOptions = [
            '<option>-</option>',
        ];

        if ($queueNo != 1) {
            $queueOptions[] = '<option value="1">H</option>';
        }

        if ($queueNo != 2) {
            $queueOptions[] = '<option value="2">E</option>';
        }

        if ($queueNo != 3) {
            $queueOptions[] = '<option value="3">SP</option>';
        }

        if ($queueNo != 5) {
            $queueOptions[] = '<option value="5">P</option>';
        }

        if ($queueNo != 4) {
            $queueOptions[] = '<option value="4">S</option>';
        }

        $blockName = 'queue' . $queueNo . 'Block';

        $this->template->set_block(
            'CurrentActivityReport',
            $blockName,
            'requests' . $queueNo
        );

        $rowCount = 0;
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);
        while ($serviceRequests->fetchNext()) {
            $totalActivityDurationHours = $serviceRequests->getValue(DBEJProblem::totalActivityDurationHours);
            $totalActivityDurationMinutes = $totalActivityDurationHours * 60;
            $timeSpentColorClass = null;
            $compareMinutes = null;

            if (in_array($queueNo, [1, 2, 3, 7])) {

                switch ($serviceRequests->getValue(DBEJCallActivity::queueNo)) {
                    case 1:
                        $compareMinutes = $dsHeader->getValue(DBEHeader::hdTeamManagementTimeApprovalMinutes);
                        break;
                    case 2:
                        $compareMinutes = $dsHeader->getValue(DBEHeader::esTeamManagementTimeApprovalMinutes);
                        break;
                    case 3:
                        $compareMinutes = $dsHeader->getValue(
                            DBEHeader::smallProjectsTeamManagementTimeApprovalMinutes
                        );
                }
                $timeSpentColorClass = $totalActivityDurationMinutes >= $compareMinutes ? 'alert-field' : null;
            }


            $linkAllocateAdditionalTime = null;
            $this->customerFilterList[$serviceRequests->getValue(DBEJProblem::customerID)] = $serviceRequests->getValue(
                DBEJProblem::customerName
            );

            if (!in_array($serviceRequests->getValue(DBEJProblem::priority), $this->getSessionParam('priorityFilter'))
            ) {
                continue;
            }


            if ($this->getSessionParam('selectedCustomerID') && $this->getSessionParam(
                    'selectedCustomerID'
                ) != $serviceRequests->getValue(
                    DBEJProblem::customerID
                )) {
                continue;
            }

            if ($this->getSessionParam('selectedUserID') &&
                $serviceRequests->getValue(DBEJProblem::userID) &&
                $this->getSessionParam(
                    'selectedUserID'
                ) != $serviceRequests->getValue(
                    DBEJProblem::userID
                )) {
                continue;
            }

            $rowCount++;

            $urlViewActivity =
                Controller::buildLink(
                    'Activity.php',
                    array(
                        'action'    => 'displayLastActivity',
                        'problemID' => $serviceRequests->getValue(DBEJProblem::problemID)
                    )
                );

            if ($this->loggedInUserIsSdManager) {

                $urlAllocateAdditionalTime =
                    Controller::buildLink(
                        'Activity.php',
                        array(
                            'action'    => 'allocateAdditionalTime',
                            'problemID' => $serviceRequests->getValue(DBEJProblem::problemID)
                        )
                    );

                $linkAllocateAdditionalTime = '<a href="' . $urlAllocateAdditionalTime . '" title="Allocate additional time"><img src="/images/clock.png" width="20px" alt="time">';
            }

            $bgColour = $this->getResponseColour(
                $serviceRequests->getValue(DBEJProblem::status),
                $serviceRequests->getValue(DBEJProblem::priority),
                $serviceRequests->getValue(DBEJProblem::slaResponseHours),
                $serviceRequests->getValue(DBEJProblem::workingHours),
                $serviceRequests->getValue(DBEJProblem::respondedHours)
            );
            /*
            Updated by another user?
            */
            if (
                $serviceRequests->getValue(DBEJProblem::userID) &&
                $serviceRequests->getValue(DBEJProblem::userID) != $serviceRequests->getValue(DBEJProblem::lastUserID)
            ) {
                $updatedBgColor = self::PURPLE;
            } else {
                $updatedBgColor = self::CONTENT;
            }

            if ($serviceRequests->getValue(DBEJProblem::respondedHours) == 0 && $serviceRequests->getValue(
                    DBEJProblem::status
                ) == 'I') {
                /*
                Initial SRs that have not yet been responded to
                */
                $hoursRemainingBgColor = self::AMBER;
            } elseif ($serviceRequests->getValue(DBEJProblem::awaitingCustomerResponseFlag) == 'Y') {
                $hoursRemainingBgColor = self::GREEN;
            } else {
                $hoursRemainingBgColor = self::BLUE;
            }
            /* ------------------------------ */

            $urlCustomer = Controller::buildLink(
                'Customer.php',
                array(
                    'action'     => 'dispEdit',
                    'customerID' => $serviceRequests->getValue(DBEJProblem::customerID)
                )
            );
            $alarmDateTimeDisplay = null;
            if ($serviceRequests->getValue(DBEProblem::alarmDate)) {
                $alarmDateTimeDisplay = Controller::dateYMDtoDMY(
                        $serviceRequests->getValue(DBEProblem::alarmDate)
                    ) . ' ' . $serviceRequests->getValue(DBEProblem::alarmTime);

                /*
                Has an alarm date that is in the past, set updated BG Colour (indicates moved back into work queue from future queue)
                */
                if ($serviceRequests->getValue(DBEJProblem::alarmDate) <= date(DATE_MYSQL_DATE)) {
                    $updatedBgColor = self::PURPLE;
                }

            }
            /*
            If the dashboard is filtered by customer then the Work button opens
            Activity edit
            */
            if ($serviceRequests->getValue(DBEJProblem::lastCallActTypeID) == null) {
                $workBgColor = self::GREEN; // green = in progress
                $workOnClick = "alert( 'Another user is currently working on this SR' ); return false";
            } else {
                $workBgColor = self::CONTENT;
                if ($this->getSessionParam('selectedCustomerID')) {
                    $urlWork =
                        Controller::buildLink(
                            'Activity.php',
                            array(
                                'action'         => 'createFollowOnActivity',
                                'callActivityID' => $serviceRequests->getValue(DBEJProblem::callActivityID)
                            )

                        );

                    $workOnClick = "if(confirm('Are you sure you want to start work on this SR? It will be automatically allocated to you UNLESS it is already allocated')) document.location='" . $urlWork . "'";
                } else {

                    /*
                    If the dashboard is not filtered by customer then the Work button filters by
                    this customer
                    */
                    $urlWork =
                        Controller::buildLink(
                            'CurrentActivityReport.php',
                            array(
                                'action'             => 'setFilter',
                                'selectedCustomerID' => $serviceRequests->getValue(DBEJProblem::customerID),
                                'selectedUserID'     => null
                            )
                        );

                    $workOnClick = "if(confirm('Filter all SRs by this customer in preparation to start work')) document.location='" . $urlWork . "'";
                }
            }

            if ($serviceRequests->getValue(DBEJProblem::priority) == 1) {
                $priorityBgColor = self::ORANGE;
            } else {
                $priorityBgColor = self::CONTENT;
            }


            $problemID = $serviceRequests->getValue(DBEJProblem::problemID);
            $buActivity = new BUActivity($this);

            $hdUsedMinutes = $buActivity->getHDTeamUsedTime($problemID);
            $esUsedMinutes = $buActivity->getESTeamUsedTime($problemID);
            $spUsedMinutes = $buActivity->getSPTeamUsedTime($problemID);
            $projectUsedMinutes = $buActivity->getUsedTimeForProblemAndTeam($problemID, 5);

            $dbeProblem = new DBEProblem($this);
            $dbeProblem->setValue(
                DBEProblem::problemID,
                $problemID
            );
            $dbeProblem->getRow();

            $hdAssignedMinutes = $dbeProblem->getValue(DBEProblem::hdLimitMinutes);
            $esAssignedMinutes = $dbeProblem->getValue(DBEProblem::esLimitMinutes);
            $smallProjectsTeamAssignedMinutes = $dbeProblem->getValue(DBEProblem::smallProjectsTeamLimitMinutes);
            $projectTeamAssignedMinutes = $dbeProblem->getValue(DBEProblem::projectTeamLimitMinutes);

            $hdRemaining = $hdAssignedMinutes - $hdUsedMinutes;
            $esRemaining = $esAssignedMinutes - $esUsedMinutes;
            $smallProjectsTeamRemaining = $smallProjectsTeamAssignedMinutes - $spUsedMinutes;
            $projectTeamRemaining = $projectTeamAssignedMinutes - $projectUsedMinutes;


            $hoursRemaining = number_format(
                $serviceRequests->getValue(DBEJProblem::workingHours) - $serviceRequests->getValue(
                    DBEJProblem::slaResponseHours
                ),
                1
            );


            $dbeCustomer = new DBECustomer($this);
            $dbeCustomer->getRow($serviceRequests->getValue(DBEJProblem::customerID));
            $hideWork = $dbeCustomer->getValue(DBECustomer::referredFlag) == 'Y';

            $this->template->set_var(

                array(
                    'queueOptions'               => implode($queueOptions),
                    'workOnClick'                => $workOnClick,
                    'hoursRemaining'             => $hoursRemaining,
                    'updatedBgColor'             => $updatedBgColor,
                    'priorityBgColor'            => $priorityBgColor,
                    'hoursRemainingBgColor'      => $hoursRemainingBgColor,
                    'totalActivityDurationHours' => $totalActivityDurationHours,
                    'timeSpentColorClass'        => $timeSpentColorClass,
                    'hdRemaining'                => $hdRemaining,
                    'esRemaining'                => $esRemaining,
                    'smallProjectsTeamRemaining' => $smallProjectsTeamRemaining,
                    "projectTeamRemaining"       => $projectTeamRemaining,
                    'hdColor'                    => $this->pickColor($hdRemaining),
                    'esColor'                    => $this->pickColor($esRemaining),
                    'smallProjectsTeamColor'     => $this->pickColor($smallProjectsTeamRemaining),
                    'projectTeamColor'           => $this->pickColor($projectTeamRemaining),
                    'urlCustomer'                => $urlCustomer,
                    'time'                       => $serviceRequests->getValue(DBEJProblem::lastStartTime),
                    'date'                       => Controller::dateYMDtoDMY(
                        $serviceRequests->getValue(DBEJProblem::lastDate)
                    ),
                    'problemID'                  => $serviceRequests->getValue(DBEJProblem::problemID),
                    'problemStatus'              => $serviceRequests->getValue(DBEJProblem::status),
                    'reason'                     => CTCurrentActivityReportOld::truncate(
                        $serviceRequests->getValue(DBEJProblem::reason),
                        150
                    ),
                    'urlProblemHistoryPopup'     => $this->getProblemHistoryLink(
                        $serviceRequests->getValue(DBEJProblem::problemID)
                    ),
                    'engineerDropDown'           => $this->getAllocatedUserDropdown(
                        $serviceRequests->getValue(DBEJProblem::problemID),
                        $serviceRequests->getValue(DBEJProblem::userID)
                    ),
                    'engineerName'               => $serviceRequests->getValue(DBEJProblem::engineerName),
                    'customerName'               => $serviceRequests->getValue(DBEJProblem::customerName),
                    'customerNameDisplayClass'   => $this->getCustomerNameDisplayClass(
                        $serviceRequests->getValue(DBEJProblem::specialAttentionFlag),
                        $serviceRequests->getValue(DBEJProblem::specialAttentionEndDate),
                        $serviceRequests->getValue(DBEJProblem::specialAttentionContactFlag)
                    ),
                    'urlViewActivity'            => $urlViewActivity,
                    'linkAllocateAdditionalTime' => $linkAllocateAdditionalTime,
                    'slaResponseHours'           => number_format(
                        $serviceRequests->getValue(DBEJProblem::slaResponseHours),
                        1
                    ),
                    'priority'                   => Controller::htmlDisplayText(
                        $serviceRequests->getValue(DBEJProblem::priority)
                    ),
                    'alarmDateTime'              => $alarmDateTimeDisplay,
                    'bgColour'                   => $bgColour,
                    'workBgColor'                => $workBgColor,
                    'workHidden'                 => $hideWork ? 'hidden' : null,
                )

            );

            $this->template->parse(
                'requests' . $queueNo,
                $blockName,
                true
            );


        } // end while

        $this->template->set_var(
            array(
                'queue' . $queueNo . 'Count' => $rowCount,
                'queue' . $queueNo . 'Name'  => $this->buActivity->workQueueDescriptionArray[$queueNo],

            )
        );
    }

    /**
     * Return the appropriate background colour for this problem
     *
     *
     * @param $status
     * @param $priority
     * @param $slaResponseHours
     * @param $workingHours
     * @param $respondedHours
     * @return string
     */
    function getResponseColour(
        $status,
        $priority,
        $slaResponseHours,
        $workingHours,
        $respondedHours
    )
    {
        /*
        Prevent divide by zero error
        */
        if ($slaResponseHours == 0) {
            $slaResponseHours = 1;
        }
        /*
        priority 5 always green
        */
        if ($priority == 5) {
            $bgColour = self::GREEN; /// green
        } else {
            if ($status == 'I') {
                /* initial status so calculate */

                $percentageSLA = ($workingHours / $slaResponseHours);

                if ($percentageSLA <= 0.75) {

                    $bgColour = self::GREEN; /// green

                } elseif ($percentageSLA > 0.75 and $percentageSLA < 1) {

                    $bgColour = self::AMBER; // amber

                } else {

                    $bgColour = self::RED; // red

                }

            } else {
                // Status is beyond initial so use recorded
                if ($respondedHours <= $slaResponseHours) {
                    $bgColour = self::GREEN;  // within SLA
                } else {
                    $bgColour = self::RED;    // Missed SLA
                }
            }
        }

        return $bgColour;
    }

    protected function pickColor($value)
    {
        if ($value <= 5) {
            return 'red';
        } else if ($value >= 6 && $value <= 20) {
            return '#FFBF00';
        } else {
            return 'green';
        }
    } // end function displayReport

    /**
     * @param $problemID
     * @return mixed|string
     * @throws Exception
     */
    function getProblemHistoryLink($problemID)
    {
        return Controller::buildLink(
            'Activity.php',
            array(
                'action'    => 'problemHistoryPopup',
                'problemID' => $problemID,
                'htmlFmt'   => CT_HTML_FMT_POPUP
            )
        );

    }

    /**
     * return list of user options for dropdown
     *
     * @param $problemID
     * @param mixed $selectedID
     * @return string
     * @throws Exception
     */
    function getAllocatedUserDropdown($problemID,
                                      $selectedID
    )
    {

        // user selection
        $userSelected = ($selectedID == 0) ? CT_SELECTED : null;

        $urlAllocateUser =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => 'allocateUser',
                    'userID'    => '0',
                    'problemID' => $problemID
                )
            );

        $string = '<option ' . $userSelected . ' value="' . $urlAllocateUser . '"></option>';

        foreach ($this->allocatedUser as $value) {

            $userSelected = ($selectedID == $value['userID']) ? CT_SELECTED : null;
            $urlAllocateUser =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => 'allocateUser',
                        'userID'    => $value['userID'],
                        'problemID' => $problemID
                    )
                );

            $string .= '<option ' . $userSelected . ' value="' . $urlAllocateUser . '">' . $value['userName'] . '</option>';

        }

        return $string;

    }

    function getCustomerNameDisplayClass($specialAttentionFlag,
                                         $specialAttentionEndDate,
                                         $specialAttentionContactFlag
    )
    {
        if (
            $specialAttentionFlag == 'Y' &&
            $specialAttentionEndDate >= date('Y-m-d')
        ) {
            return 'class="specialAttentionCustomer"';
        }

        if ($specialAttentionContactFlag == 'Y') {
            return 'class="specialAttentionContact"';
        }

        return null;
    }
}