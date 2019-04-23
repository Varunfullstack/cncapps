<?php
/**
 * Customer Activity Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');

// Actions
class CTCurrentActivityReport extends CTCNC
{

    var $filterUser = array();
    var $allocatedUser = array();
    var $priority = array();
    var $prioritySelectArray = array();
    var $loggedInUserIsSdManager;

    var $customerFilterList;

    const AMBER = '#FFF5B3';
    const RED = '#F8A5B6';
    const GREEN = '#BDF8BA';
    const /** @noinspection SpellCheckingInspection */
        BLUE = '#b2daff';
    const CONTENT = null;
    const PURPLE = '#dcbdff';
    const ORANGE = '#FFE6AB';
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

        if (!isset($_SESSION['priorityFilter'])) {

            foreach ($this->buActivity->priorityArray as $key => $value) {

                $_SESSION['priorityFilter'][] = $key;

            }

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


        if (!isset($_SESSION['displayToBeLoggedSectionFlag'])) {
            $_SESSION['displayToBeLoggedSectionFlag'] = 1;
        }
        if (!isset($_SESSION['displayQueue1Flag'])) {
            $_SESSION['displayQueue1Flag'] = 1;
        }
        if (!isset($_SESSION['displayQueue2Flag'])) {
            $_SESSION['displayQueue2Flag'] = 1;
        }
        if (!isset($_SESSION['displayQueue3Flag'])) {
            $_SESSION['displayQueue3Flag'] = 1;
        }
        if (!isset($_SESSION['displayQueue4Flag'])) {
            $_SESSION['displayQueue4Flag'] = 1;
        }
        if (!isset($_SESSION['displayQueue5Flag'])) {
            $_SESSION['displayQueue5Flag'] = 1;
        }
        if (!isset($_SESSION['displayQueue7Flag'])) {
            $_SESSION['displayQueue7Flag'] = 1;
        }

        switch ($_REQUEST['action']) {

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
                $this->toggleDisplayToBeLoggedFlag();
                break;

            case 'toggleDisplayQueue1Flag':
                $this->toggleDisplayQueue1Flag();
                break;

            case 'toggleDisplayQueue2Flag':
                $this->toggleDisplayQueue2Flag();
                break;

            case 'toggleDisplayQueue3Flag':
                $this->toggleDisplayQueue3Flag();
                break;

            case 'toggleDisplayQueue4Flag':
                $this->toggleDisplayQueue4Flag();
                break;

            case 'toggleDisplayQueue5Flag':
                $this->toggleDisplayQueue5Flag();
                break;

            case 'toggleDisplayQueue6Flag':
                $this->toggleDisplayQueue6Flag();
                break;

            case 'toggleDisplayQueue7Flag':
                $this->toggleDisplayQueue7Flag();
                break;

            case 'escalate':
                $this->escalate();
                break;

            case 'deescalate':
                $this->deescalate();
                break;
            case 'changeQueue':
                $this->changeQueue();
                break;
            case 'deleteCustomerRequest':
                $this->checkPermissions(PHPLIB_PERM_TECHNICAL);
                $this->deleteCustomerRequest();
                break;
            default:
                $this->displayReport();
                break;
        }
    }

    function toggleDisplayToBeLoggedFlag()
    {
        if ($_SESSION['displayToBeLoggedFlag']) {
            $_SESSION['displayToBeLoggedFlag'] = false;
        } else {
            $_SESSION['displayToBeLoggedFlag'] = true;
        }
    }

    function toggleDisplayQueue1Flag()
    {
        if ($_SESSION['displayQueue1Flag']) {
            $_SESSION['displayQueue1Flag'] = false;
        } else {
            $_SESSION['displayQueue1Flag'] = true;
        }
    }

    function toggleDisplayQueue2Flag()
    {
        if ($_SESSION['displayQueue2Flag']) {
            $_SESSION['displayQueue2Flag'] = false;
        } else {
            $_SESSION['displayQueue2Flag'] = true;
        }
    }

    function toggleDisplayQueue3Flag()
    {
        if ($_SESSION['displayQueue3Flag']) {
            $_SESSION['displayQueue3Flag'] = false;
        } else {
            $_SESSION['displayQueue3Flag'] = true;
        }
    }

    function toggleDisplayQueue4Flag()
    {
        if ($_SESSION['displayQueue4Flag']) {
            $_SESSION['displayQueue4Flag'] = false;
        } else {
            $_SESSION['displayQueue4Flag'] = true;
        }
    }

    function toggleDisplayQueue5Flag()
    {
        if ($_SESSION['displayQueue5Flag']) {
            $_SESSION['displayQueue5Flag'] = false;
        } else {
            $_SESSION['displayQueue5Flag'] = true;
        }
    }

    function toggleDisplayQueue6Flag()
    {
        if ($_SESSION['displayQueue6Flag']) {
            $_SESSION['displayQueue6Flag'] = false;
        } else {
            $_SESSION['displayQueue6Flag'] = true;
        }
    }

    function toggleDisplayQueue7Flag()
    {
        if ($_SESSION['displayQueue7Flag']) {
            $_SESSION['displayQueue7Flag'] = false;
        } else {
            $_SESSION['displayQueue7Flag'] = true;
        }
    }

    /**
     * @throws Exception
     */
    function showMineOnly()
    {
        unset($_SESSION['selectedUserID']);

        $_SESSION['selectedUserID'] = $GLOBALS['auth']->is_authenticated();

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array()
            );
        header('Location: ' . $urlNext);
        exit;

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
            $_REQUEST['problemID'],
            $_REQUEST['userID'],
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
     * Remove all filters
     * @throws Exception
     */
    function resetFilter()
    {

        unset($_SESSION['selectedUserID']);
        unset($_SESSION['selectedCustomerID']);
        unset($_SESSION['priorityFilter']);

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

        if (isset($_REQUEST['selectedUserID'])) {
            $_SESSION['selectedUserID'] = $_REQUEST['selectedUserID'];
        }

        if (isset($_REQUEST['priorityFilter'])) {
            $_SESSION['priorityFilter'] = $_REQUEST['priorityFilter'];
        }

        if (isset($_REQUEST['selectedCustomerID'])) {
            $_SESSION['selectedCustomerID'] = $_REQUEST['selectedCustomerID'];
        }

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array()
            );

        header('Location: ' . $urlNext);
        exit;
    }

    /**
     * Display search form
     * @access private
     * @throws Exception
     */
    function displayReport()
    {

        $this->setMethodName('displayReport');

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

                $truncatedReason = CTCurrentActivityReport::truncate(
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

        $this->template->set_var(
            'displayToBeLoggedFlag',
            ($_SESSION['displayToBeLoggedFlag'] == 0) ? '0' : '1'
        );
        $this->template->set_var(
            'displayQueue1Flag',
            ($_SESSION['displayQueue1Flag'] == 0) ? '0' : '1'
        );
        $this->template->set_var(
            'displayQueue2Flag',
            ($_SESSION['displayQueue2Flag'] == 0) ? '0' : '1'
        );
        $this->template->set_var(
            'displayQueue3Flag',
            ($_SESSION['displayQueue3Flag'] == 0) ? '0' : '1'
        );
        $this->template->set_var(
            'displayQueue4Flag',
            ($_SESSION['displayQueue4Flag'] == 0) ? '0' : '1'
        );
        $this->template->set_var(
            'displayQueue5Flag',
            ($_SESSION['displayQueue5Flag'] == 0) ? '0' : '1'
        );
        $this->template->set_var(
            'displayQueue6Flag',
            ($_SESSION['displayQueue6Flag'] == 0) ? '0' : '1'
        );
        $this->template->set_var(
            'displayQueue7Flag',
            ($_SESSION['displayQueue7Flag'] == 0) ? '0' : '1'
        );


        $this->setPageTitle(CONFIG_SERVICE_REQUEST_DESC . 's');


        $this->renderQueue(1);  // Helpdesk
        $this->renderQueue(2);  //Escalations
        $this->renderQueue(3);  // Sales
        $this->renderQueue(4);  //Implementations
        $this->renderQueue(5);  // Managers

        if ($_SESSION['selectedCustomerID']) {
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
                    'imRemaining'                => null,
                    'hdColor'                    => null,
                    'esColor'                    => null,
                    'imColor'                    => null,
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
            if ($value['userID'] == $_SESSION['selectedUserID']) {
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
            if (in_array($key, $_SESSION['priorityFilter'])
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
                if ($_SESSION['selectedCustomerID'] == $customerID) {
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

    } // end function displayReport

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
            '<option value="1">H</option>',
            '<option value="2">E</option>',
            '<option value="3">I</option>',
            '<option value="4">S</option>',
            '<option value="5">M</option>'
        ];

        unset($queueOptions[$queueNo]);

        $blockName = 'queue' . $queueNo . 'Block';

        $this->template->set_block(
            'CurrentActivityReport',
            $blockName,
            'requests' . $queueNo
        );

        $rowCount = 0;

        while ($serviceRequests->fetchNext()) {
            $linkAllocateAdditionalTime = null;
            $this->customerFilterList[$serviceRequests->getValue(DBEJProblem::customerID)] = $serviceRequests->getValue(DBEJProblem::customerName);

            if (
            !in_array(
                $serviceRequests->getValue(DBEJProblem::priority),
                $_SESSION['priorityFilter']
            )
            ) {
                continue;
            }


            if ($_SESSION['selectedCustomerID'] && $_SESSION['selectedCustomerID'] != $serviceRequests->getValue(
                    DBEJProblem::customerID
                )) {
                continue;
            }

            if (
                ($_SESSION['selectedUserID'] && $_SESSION['selectedUserID'] != $serviceRequests->getValue(DBEJProblem::userID)) AND

                $serviceRequests->getValue(DBEJProblem::userID) != '0'        // always show Unallocated
            ) {
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

            if ($serviceRequests->getValue(DBEJProblem::respondedHours) == 0 && $serviceRequests->getValue(DBEJProblem::status) == 'I') {
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
                'SalesOrder.php',
                array(
                    'action'     => 'search',
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
                if ($serviceRequests->getValue(DBEJProblem::alarmDate) <= date(CONFIG_MYSQL_DATE)) {
                    $updatedBgColor = self::PURPLE;
                }

            }
            /*
            If the dashboard is filtered by customer then the Work button opens
            Activity edit
            */
            if (
                $serviceRequests->getValue(DBEJProblem::lastCallActTypeID) == 0
            ) {
                $workBgColor = self::GREEN; // green = in progress
                $workOnClick = "alert( 'Another user is currently working on this SR' ); return false";
            } else {

                $workBgColor = self::CONTENT;

                if ($_SESSION['selectedCustomerID']) {
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
            $imUsedMinutes = $buActivity->getIMTeamUsedTime($problemID);

            $dbeProblem = new DBEProblem($this);
            $dbeProblem->setValue(
                DBEProblem::problemID,
                $problemID
            );
            $dbeProblem->getRow();

            $hdAssignedMinutes = $dbeProblem->getValue(DBEProblem::hdLimitMinutes);
            $esAssignedMinutes = $dbeProblem->getValue(DBEProblem::esLimitMinutes);
            $imAssignedMinutes = $dbeProblem->getValue(DBEProblem::imLimitMinutes);

            $hdRemaining = $hdAssignedMinutes - $hdUsedMinutes;
            $esRemaining = $esAssignedMinutes - $esUsedMinutes;
            $imRemaining = $imAssignedMinutes - $imUsedMinutes;


            $hoursRemaining = number_format(
                $serviceRequests->getValue(DBEJProblem::workingHours) - $serviceRequests->getValue(DBEJProblem::slaResponseHours),
                1
            );
            $totalActivityDurationHours = $serviceRequests->getValue(DBEJProblem::totalActivityDurationHours);

            $this->template->set_var(

                array(
                    'queueOptions'               => implode($queueOptions),
                    'workOnClick'                => $workOnClick,
                    'hoursRemaining'             => $hoursRemaining,
                    'updatedBgColor'             => $updatedBgColor,
                    'priorityBgColor'            => $priorityBgColor,
                    'hoursRemainingBgColor'      => $hoursRemainingBgColor,
                    'totalActivityDurationHours' => $totalActivityDurationHours,
                    'hdRemaining'                => $hdRemaining,
                    'esRemaining'                => $esRemaining,
                    'imRemaining'                => $imRemaining,
                    'hdColor'                    => $this->pickColor($hdRemaining),
                    'esColor'                    => $this->pickColor($esRemaining),
                    'imColor'                    => $this->pickColor($imRemaining),
                    'urlCustomer'                => $urlCustomer,
                    'time'                       => $serviceRequests->getValue(DBEJProblem::lastStartTime),
                    'date'                       => Controller::dateYMDtoDMY($serviceRequests->getValue(DBEJProblem::lastDate)),
                    'problemID'                  => $serviceRequests->getValue(DBEJProblem::problemID),
                    'reason'                     => CTCurrentActivityReport::truncate(
                        $serviceRequests->getValue(DBEJProblem::reason),
                        150
                    ),
                    'urlProblemHistoryPopup'     => $this->getProblemHistoryLink($serviceRequests->getValue(DBEJProblem::problemID)),
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
                    'priority'                   => Controller::htmlDisplayText($serviceRequests->getValue(DBEJProblem::priority)),
                    'alarmDateTime'              => $alarmDateTimeDisplay,
                    'bgColour'                   => $bgColour,
                    'workBgColor'                => $workBgColor

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
    } // end render queue


    protected function pickColor($value)
    {
        if ($value <= 5) {
            return 'red';
        } else if ($value >= 6 && $value <= 20) {
            return '#FFBF00';
        } else {
            return 'green';
        }
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

                } elseif ($percentageSLA > 0.75 AND $percentageSLA < 1) {

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

    function getAlarmColour($alarmDate,
                            $alarmTime
    )
    {

        if ($alarmDate) {
            $dateNow = strtotime(date('Y-m-d H:i'));
            $dateAlarm = strtotime($alarmDate . ' ' . $alarmTime);

            if ($dateNow >= $dateAlarm) {

                $bgColour = self::RED; // red = ready to start

            } else {

                $bgColour = self::AMBER; // amber = on hold

            }
        } else {
            $bgColour = self::GREEN; /// green = active

        }
        return $bgColour;
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

    /**
     * @param $problemID
     * @return mixed|string
     * @throws Exception
     */
    function getProblemHistoryLink($problemID)
    {
        $url = Controller::buildLink(
            'Activity.php',
            array(
                'action'    => 'problemHistoryPopup',
                'problemID' => $problemID,
                'htmlFmt'   => CT_HTML_FMT_POPUP
            )
        );

        return $url;

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

    /**
     * @throws Exception
     */
    function deleteCustomerRequest()
    {
        $customerproblemno = $_REQUEST['cpr_customerproblemno'];

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
    function changeQueue()
    {
        $problemID = $_REQUEST['problemID'];
        $newQueue = $_REQUEST['queue'];

        $this->buActivity->escalateProblemByProblemID(
            $problemID,
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
    function escalate()
    {

        $problemID = $_REQUEST['problemID'];

        $this->buActivity->escalateProblemByProblemID($problemID);

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
    function deescalate()
    {
        $problemID = $_REQUEST['problemID'];
        $this->buActivity->deEscalateProblemByProblemID($problemID);

        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array()
        );
        header('Location: ' . $urlNext);
        exit;
    }
}
