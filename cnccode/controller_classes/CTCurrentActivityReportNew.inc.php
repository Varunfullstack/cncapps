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
class CTCurrentActivityReportNew extends CTCNC
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
    ) {
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
             break;
             case "getEscalationsInbox":
                echo json_encode($this->renderQueue(2));
                exit;                
             break;
             case "getSalesInbox":
                echo json_encode($this->renderQueue(4));
                exit;                
             break;
             case "getSmallProjectsInbox":
                echo json_encode($this->renderQueue(3));
                exit;                
             break;
             case "getProjectsInbox":
                echo json_encode($this->renderQueue(5));
                exit;                
             break;
             case "getFixedInbox":
                echo json_encode($this->renderQueue(6));
                exit;                
             break;
             case "getFutureInbox":
                echo json_encode($this->renderQueue(7));
                exit;                
             break;
             case 'changeQueue':
                echo $this->changeQueue();
                exit;  
                break;
            case 'allocatedUsers':
                echo $this->getAllocatedUsers();
                exit;  
                break;
            case 'allocateUser':
               echo $this->allocateUser();
                exit; 
                break;
            default:
                $this->setTemplate();
                break;
        }
    }

    function setTemplate()
    {
        $this->setMethodName('setTemplate');
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
    //$this->renderQueue(1);  // Helpdesk
    //$this->renderQueue(2);  // Escalations
    //$this->renderQueue(3);  // Sales
    //$this->renderQueue(4);  // Small Projects
    //$this->renderQueue(5);  // Projects
    //$this->renderQueue(6);  //Fixed
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

         } 
        //elseif ($queueNo == 7) {
        //     /* future dated */
        //     $this->buActivity->getFutureProblems($serviceRequests);

         else {
            $this->buActivity->getProblemsByQueueNoWithFuture(
                $queueNo,
                $serviceRequests
            );
        }

        $queueOptions = [
            '<option>-</option>',
        ];
/*
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
       */
        // $this->template->set_block(
        //     'CurrentActivityReport',
        //     $blockName,
        //     'requests' . $queueNo
        // );
        $result=array();
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
            /*
            if (!in_array($serviceRequests->getValue(DBEJProblem::priority), $this->getSessionParam('priorityFilter'))
            ) {
                continue;
            }
*/
/*
Filter by selected customer
            if ($this->getSessionParam('selectedCustomerID') && $this->getSessionParam(
                    'selectedCustomerID'
                ) != $serviceRequests->getValue(
                    DBEJProblem::customerID
                )) {
                continue;
            }
*/

/*
fileter by user
            if ($this->getSessionParam('selectedUserID') &&
                $serviceRequests->getValue(DBEJProblem::userID) &&
                $this->getSessionParam(
                    'selectedUserID'
                ) != $serviceRequests->getValue(
                    DBEJProblem::userID
                )) {
                continue;
            }
*/
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
                $alarmDateTimeDisplay =  
                        $serviceRequests->getValue(DBEProblem::alarmDate)
                     . ' ' . $serviceRequests->getValue(DBEProblem::alarmTime);

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
            //we don't need this part
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

             array_push($result,

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
                    'updated'=>   $serviceRequests->getValue(DBEJProblem::lastDate)." ".$serviceRequests->getValue(DBEJProblem::lastStartTime),
                    'problemID'                  => $serviceRequests->getValue(DBEJProblem::problemID),
                    'problemStatus'              => $serviceRequests->getValue(DBEJProblem::status),
                    'reason'                     => CTCurrentActivityReportNew::truncate(
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
                    'engineerId'               => $serviceRequests->getValue(DBEJProblem::userID),                    
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
                    "callActivityID"             =>$serviceRequests->getValue(DBEJProblem::callActivityID),
                    "lastCallActTypeID"         =>$serviceRequests->getValue(DBEJProblem::lastCallActTypeID),
                    'customerID'               => $serviceRequests->getValue(DBEJProblem::customerID),
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
    function getCustomerNameDisplayClass(
        $specialAttentionFlag,
        $specialAttentionEndDate,
        $specialAttentionContactFlag
    ) {
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
        return json_encode(["status"=>true]);
    }
    function getAllocatedUsers()
    {
        $dbeUser = new DBEUser($this);

        $dbeUser->getRows('firstName');
        $allocatedUser=array();
        while ($dbeUser->fetchNext()) {

            $userRow =
                array(
                    'userID'   => $dbeUser->getValue(DBEUser::userID),
                    'userName' => $dbeUser->getValue(DBEUser::name),
                    'fullName' => $dbeUser->getValue(DBEUser::firstName) . ' ' . $dbeUser->getValue(
                            DBEUser::lastName
                    ),
                    'appearInQueueFlag'=>$dbeUser->getValue(DBEUser::appearInQueueFlag)
                );
                array_push($allocatedUser,$userRow);
            // if ($dbeUser->getValue(DBEUser::appearInQueueFlag) == 'Y') {

            //     $this->filterUser[$dbeUser->getValue(DBEUser::userID)] = $userRow;
            // }
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
        return json_encode(["status"=>true]);
    }

}
