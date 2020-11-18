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
class CTCurrentActivityReport extends CTCNC
{

    const AMBER = '#FFF5B3';
    const RED = '#F8A5B6';
    const GREEN = '#BDF8BA';
    const
        /** @noinspection SpellCheckingInspection */
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

    function __construct(
        $requestMethod,
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
        $this->buActivity = new BUActivity($this);
        $this->buCustomerItem = new BUCustomerItem($this);

        if ($checkPermissions) {

            $roles = [
                "technical",
            ];
            if (!self::hasPermissions($roles)) {
                Header("Location: /NotAllowed.php");
                exit;
            }
        }
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        //$this->renderQueue(1);  // Helpdesk
        //$this->renderQueue(2);  // Escalations
        //$this->renderQueue(4);  // Sales wrong
        //$this->renderQueue(3);  // Small Projects
        //$this->renderQueue(5);  // Projects
        //$this->renderQueue(6);  //Fixed
        switch ($this->getAction()) {
            case "getHelpDeskInbox":
                echo json_encode($this->renderQueue(1));
                exit;
            case "getEscalationsInbox":
                echo json_encode($this->renderQueue(2));
                exit;
            case "getSalesInbox":
                echo json_encode($this->renderQueue(4));
                exit;
            case "getSmallProjectsInbox":
                echo json_encode($this->renderQueue(3));
                exit;
            case "getProjectsInbox":
                echo json_encode($this->renderQueue(5));
                exit;
            case "getFixedInbox":
                echo json_encode($this->renderQueue(6));
                exit;
            case "getFutureInbox":
                echo json_encode($this->renderQueue(7));
                exit;
            case 'changeQueue':
                echo $this->changeQueue();
                exit;
            case 'allocatedUsers':
                echo $this->getAllocatedUsers();
                exit;
            case 'allocateUser':
                echo $this->allocateUser();
                exit;
            case "getToBeLoggedInbox":
                echo $this->getToBeLogged();
                exit;
            case "getPendingReopenedInbox":
                echo $this->getPendingReopenedRequests();
                exit;
            case 'deleteCustomerRequest':
                $this->checkPermissions(TECHNICAL_PERMISSION);
                echo $this->deleteCustomerRequest();
                exit;
            case 'processPendingReopened':
                echo $this->processPendingReopened();
                exit;
            case 'pendingReopenedPopup':
                $this->pendingReopenedDescriptionPopUp();
                break;
            case 'getCustomerOpenSR':
                echo json_encode($this->renderQueue(13));
                exit;
            default:
                $this->setTemplate();
                break;
        }
    }

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

        } else if ($queueNo == 13) {
            /* fixed awaiting closure */
            $this->buActivity->getCustomerOpenSR(
                $_REQUEST["customerID"],
                $serviceRequests
            );

        } else {
            $this->buActivity->getProblemsByQueueNoWithFuture(
                $queueNo,
                $serviceRequests
            );
        }

        $queueOptions = [
            '<option>-</option>',
        ];

        $result = array();
        $rowCount = 0;
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);
        while ($serviceRequests->fetchNext()) {
            $rowCount++;
            $bgColour = $this->getResponseColour(
                $serviceRequests->getValue(DBEJProblem::status),
                $serviceRequests->getValue(DBEJProblem::priority),
                $serviceRequests->getValue(DBEJProblem::slaResponseHours),
                $serviceRequests->getValue(DBEJProblem::workingHours),
                $serviceRequests->getValue(DBEJProblem::respondedHours)
            );

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


            if ($serviceRequests->getValue(DBEJProblem::lastEndTime) == null) {
                $workBgColor = self::GREEN; // green = in progress
            } else {
                $workBgColor = self::CONTENT;
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

            array_push(
                $result,

                array(
                    //'queueOptions'               => implode($queueOptions),
                    //'workOnClick'                => $workOnClick,
                    'hoursRemaining'             => $hoursRemaining,
                    //'updatedBgColor'             => $updatedBgColor,
                    'priorityBgColor'            => $priorityBgColor,
                    'hoursRemainingBgColor'      => $hoursRemainingBgColor,
                    //'totalActivityDurationHours' => $totalActivityDurationHours,
                    //'timeSpentColorClass'        => $timeSpentColorClass,
                    'hdRemaining'                => $hdRemaining,
                    'esRemaining'                => $esRemaining,
                    'smallProjectsTeamRemaining' => $smallProjectsTeamRemaining,
                    "projectTeamRemaining"       => $projectTeamRemaining,
                    'hdColor'                    => $this->pickColor($hdRemaining),
                    'esColor'                    => $this->pickColor($esRemaining),
                    'smallProjectsTeamColor'     => $this->pickColor($smallProjectsTeamRemaining),
                    'projectTeamColor'           => $this->pickColor($projectTeamRemaining),
                    //'urlCustomer'                => $urlCustomer,
                    'time'                       => $serviceRequests->getValue(DBEJProblem::lastStartTime),
                    'date'                       => Controller::dateYMDtoDMY(
                        $serviceRequests->getValue(DBEJProblem::lastDate)
                    ),
                    'updated'                    => $serviceRequests->getValue(
                            DBEJProblem::lastDate
                        ) . " " . $serviceRequests->getValue(DBEJProblem::lastStartTime),
                    'problemID'                  => $serviceRequests->getValue(DBEJProblem::problemID),
                    'problemStatus'              => $serviceRequests->getValue(DBEJProblem::status),
                    'reason'                     => CTCurrentActivityReport::truncate(
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
                    'engineerId'                 => $serviceRequests->getValue(DBEJProblem::userID),
                    'customerName'               => $serviceRequests->getValue(DBEJProblem::customerName),
                    'customerNameDisplayClass'   => $this->getCustomerNameDisplayClass(
                        $serviceRequests->getValue(DBEJProblem::specialAttentionFlag),
                        $serviceRequests->getValue(DBEJProblem::specialAttentionEndDate),
                        $serviceRequests->getValue(DBEJProblem::specialAttentionContactFlag)
                    ),
                    //'urlViewActivity'            => $urlViewActivity,
                    //'linkAllocateAdditionalTime' => $linkAllocateAdditionalTime,
                    'slaResponseHours'           => number_format(
                        $serviceRequests->getValue(DBEJProblem::slaResponseHours),
                        1
                    ),
                    'priority'                   => Controller::htmlDisplayText(
                        $serviceRequests->getValue(DBEJProblem::priority)
                    ),
                    'alarmDateTime'              => $serviceRequests->getValue(
                            DBEJProblem::alarmDate
                        ) . ' ' . $serviceRequests->getValue(DBEJProblem::alarmTime),
                    'bgColour'                   => $bgColour,
                    'workBgColor'                => $workBgColor,
                    'workHidden'                 => $hideWork ? 'hidden' : null,
                    "callActivityID"             => $serviceRequests->getValue(DBEJProblem::callActivityID),
                    "lastCallActTypeID"          => $serviceRequests->getValue(DBEJProblem::lastCallActTypeID),
                    'customerID'                 => $serviceRequests->getValue(DBEJProblem::customerID),
                    'queueNo'                    => $serviceRequests->getValue(DBEJProblem::queueNo),

                )
            );
        } // end while
        return $result;
        $this->template->set_var(
            array(
                'queue' . $queueNo . 'Count' => $rowCount,
                'queue' . $queueNo . 'Name'  => $this->buActivity->workQueueDescriptionArray[$queueNo],

            )
        );
    }

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
    }
    //$this->renderQueue(1);  // Helpdesk
    //$this->renderQueue(2);  // Escalations
    //$this->renderQueue(3);  // Sales
    //$this->renderQueue(4);  // Small Projects
    //$this->renderQueue(5);  // Projects
    //$this->renderQueue(6);  //Fixed

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

    function getCustomerNameDisplayClass(
        $specialAttentionFlag,
        $specialAttentionEndDate,
        $specialAttentionContactFlag
    )
    {
        if (
            $specialAttentionFlag == 'Y' &&
            $specialAttentionEndDate >= date('Y-m-d')
        ) {
            return 'specialAttentionCustomer';
        }

        if ($specialAttentionContactFlag == 'Y') {
            return 'specialAttentionContact';
        }

        return null;
    } // end function displayReport

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
        return json_encode(["status" => true]);
    }

    function getAllocatedUsers()
    {
        $dbeUser = new DBEUser($this);

        $dbeUser->getRows('firstName');
        $allocatedUser = array();
        while ($dbeUser->fetchNext()) {

            $userRow =
                array(
                    'userID'            => $dbeUser->getValue(DBEUser::userID),
                    'userName'          => $dbeUser->getValue(DBEUser::name),
                    'fullName'          => $dbeUser->getValue(DBEUser::firstName) . ' ' . $dbeUser->getValue(
                            DBEUser::lastName
                        ),
                    'appearInQueueFlag' => $dbeUser->getValue(DBEUser::appearInQueueFlag),
                    "teamID"            => $dbeUser->getValue(DBEUser::teamID),
                );
            array_push($allocatedUser, $userRow);
        }
        return json_encode($allocatedUser);
    }

    /**
     * @param array $options
     * @throws Exception
     */
    function allocateUser()
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
        return json_encode(["status" => true]);
    }

    function getToBeLogged()
    {
        $this->setMethodName('getToBeLogged');
        $customerRaisedRequests = $this->buActivity->getCustomerRaisedRequests();
        /*
        Requests raised via the customer portal
        */
        $customerRaisedRequests->next_record();
        $count = 0;
        $result = array();
        if ($customerRaisedRequests->Record) {
            do {


                $urlCustomer =
                    Controller::buildLink(
                        'SalesOrder.php',
                        array(
                            'action'     => 'search',
                            'customerID' => $customerRaisedRequests->Record['con_custno']
                        )
                    );
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
                array_push(
                    $result,
                    array(
                        'cpCustomerProblemID' => $customerRaisedRequests->Record['cpr_customerproblemno'],
                        'cpCustomerName'      => $customerRaisedRequests->Record['cus_name'],
                        'cpContactName'       => $customerRaisedRequests->Record['con_first_name'] . ' ' . $customerRaisedRequests->Record['con_last_name'],
                        'cpDate'              => $customerRaisedRequests->Record['cpr_date'],
                        'cpUrlServiceRequest' => $urlServiceRequest,
                        'cpServiceRequestID'  => $customerRaisedRequests->Record['cpr_problemno'],
                        'cpPriority'          => $customerRaisedRequests->Record['cpr_priority'],
                        'cpTruncatedReason'   => $truncatedReason,
                        'cpFullReason'        => $customerRaisedRequests->Record['cpr_reason'],
                        'cpUrlCustomer'       => $urlCustomer,
                        'cpBgColor'           => $bgColour,
                        'cpCount'             => $count,
                    )
                );
            } while ($customerRaisedRequests->next_record());
        }
        return json_encode($result);

    }

    function getPendingReopenedRequests()
    {
        $pendingReopenedRequests = $this->buActivity->getPendingReopenedRequests();
        $result = array();
        if ($pendingReopenedRequests && count($pendingReopenedRequests)) {

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
                $truncatedReason = CTCurrentActivityReport::truncate(
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
                array_push(
                    $result,
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
            }
        }
        return json_encode($result);
    }

    /**
     * @throws Exception
     */
    function deleteCustomerRequest()
    {
        $customerproblemno = $this->getParam('cpr_customerproblemno');
        $this->buActivity->deleteCustomerRaisedRequest($customerproblemno);
        return 1;
    }

    /**
     * @return int
     * @throws Exception
     */
    private function processPendingReopened()
    {
        $body = json_decode(file_get_contents('php://input'));
        $pendingReopenedID = $body->pendingReopenedID;
        $result = $body->result;
        if (isset($pendingReopenedID) && isset($result)) {
            $dbePendingReopened = new DBEPendingReopened($this);
            if ($result == 'R') {
                $dbePendingReopened->getRow($pendingReopenedID);
                $this->buActivity->approvePendingReopened($dbePendingReopened);
            } elseif ($result == 'D') {
                $dbePendingReopened->deleteRow($pendingReopenedID);
            }
            return 1;
        }
        return 0;
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
    }

    function setTemplate()
    {
        $this->setMethodName('setTemplate');
        $this->template->setVar("menuId", 103);
        $action = $this->getAction();
        $this->setPageTitle('Service Requests');
        switch ($action) {
            case 'inbox':
                $this->setPageTitle('Service Requests');
                break;
        }

        $this->setTemplateFiles(
            'CurrentActivityReportNew',
            'CurrentActivityReportNew.inc'
        );

        $this->loadReactScript('CurrentActivityReportComponent.js');
        $this->loadReactCSS('CurrentActivityReportComponent.css');

        $this->template->parse(
            'CONTENTS',
            'CurrentActivityReportNew',
            true
        );
        $this->parsePage();
    }

    function getHelpDeskInbox()
    {
        $this->setMethodName('getHelpDeskInbox');

        return json_encode($this->renderQueue(1));
    }

    /**
     * @param DBEProblem|DataSet $dbeProblem
     * @return bool
     */
    protected function getIsSLABreached($dbeProblem)
    {
        $status = $dbeProblem->getValue(DBEJProblem::status);
        $priority = $dbeProblem->getValue(DBEJProblem::priority);
        $slaResponseHours = $dbeProblem->getValue(DBEJProblem::slaResponseHours);
        $workingHours = $dbeProblem->getValue(DBEJProblem::workingHours);
        $respondedHours = $dbeProblem->getValue(DBEJProblem::respondedHours);
        if ($slaResponseHours == 0) {
            $slaResponseHours = 1;
        }

        if ($priority == 5) {
            return false;
        }
        if ($status != 'I' && $respondedHours <= $slaResponseHours) {
            return false;
        }

        $percentageSLA = ($workingHours / $slaResponseHours);
        if ($status == 'I' && $percentageSLA < 1) {
            return false;
        }

        return true;
    }

    /**
     * @param DBEJProblem|DataSet $dbeProblem
     * @return bool
     */
    protected function isRequestBeingWorkedOn($dbeProblem)
    {
        $dateString = $dbeProblem->getValue(DBEJProblem::lastDate);
        $timeString = $dbeProblem->getValue(DBEJProblem::lastStartTime);
        $activityDateTime = DateTime::createFromFormat('Y-m-d H:i', "$dateString $timeString");

        if ($activityDateTime > (new DateTime())) {
            return false;
        }

        return !$dbeProblem->getValue(DBEJProblem::lastEndTime);
    }

}
