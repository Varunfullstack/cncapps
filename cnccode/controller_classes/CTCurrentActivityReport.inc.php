<?php
/**
 * Customer Activity Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Business\BUActivity;
use CNCLTD\Data\CallBackStatus;
use CNCLTD\Data\DBConnect;
use CNCLTD\Exceptions\ColumnOutOfRangeException;
use CNCLTD\SDManagerDashboard\ServiceRequestSummaryDTO;
use CNCLTD\Utils;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEPendingReopened.php');
require_once($cfg['path_dbe'] . '/DBECallback.inc.php');


// Actions
class CTCurrentActivityReport extends CTCNC
{

    const AMBER                  = '#FFF5B3';
    const RED                    = '#F8A5B6';
    const GREEN                  = '#BDF8BA';
    const
        /** @noinspection SpellCheckingInspection */
          BLUE                   = '#b2daff';
    const CONTENT                = null;
    const PURPLE                 = '#dcbdff';
    const ORANGE                 = '#FFE6AB';
    const FIXED_AWAITING_CLOSURE = 6;
    const CUSTOMER_OPEN_SR       = 13;
    var $filterUser    = array();
    var $allocatedUser = array();
    var $priority      = array();
    var $loggedInUserIsSdManager;
    var $customerFilterList;
    const CONST_CALLBACK                 = 'callback';
    const CONST_CALLBACK_SEARCH          = 'callbackSearch';
    const CONST_ALLOCATE_ADDITIONAL_TIME = 'allocateAdditionalTime';
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
        $this->buActivity     = new BUActivity($this);
        $this->buCustomerItem = new BUCustomerItem($this);
        $checkPermissions     = !in_array($this->getAction(), [self::CONST_CALLBACK]);

        if ($checkPermissions) {

            $roles = [
                "technical"
            ];
            
        }
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $method = $this->getRequestMethodeName();
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
                echo json_encode($this->renderQueue(self::CUSTOMER_OPEN_SR));
                exit;
            case self::CONST_CALLBACK:
                switch ($method) {
                    case 'POST':
                        echo json_encode($this->addCallBack());
                        break;
                    case 'GET':
                        echo json_encode($this->getMyCallback());
                        break;
                    case 'DELETE':
                        echo json_encode($this->cancelCallBack());
                        break;
                    case 'PUT':
                        echo json_encode($this->updateCallbackStatus());
                        break;
                }
                exit;
            case self::CONST_CALLBACK_SEARCH:
                echo json_encode($this->callBackSearch());
                break;
            case self::CONST_ALLOCATE_ADDITIONAL_TIME:
                echo json_encode($this->allocateAdditionalTime());
                break;
            default:
                $this->setTemplate();
                break;
        }
    }

    /**
     * @throws ColumnOutOfRangeException
     */
    private function renderQueue($queueNo)
    {
        if ($queueNo == self::FIXED_AWAITING_CLOSURE) {
            $serviceRequests = $this->buActivity->getProblemsByStatus(
                'F',
                false
            );
        } else if ($queueNo == self::CUSTOMER_OPEN_SR) {
            $serviceRequests = $this->buActivity->getCustomerOpenSR($_REQUEST["customerID"], $_REQUEST["srNumber"]);
        } else {
            $serviceRequests = $this->buActivity->getProblemsByQueue($queueNo);
        }
        $result = [];
        while ($serviceRequests->fetchNext()) {
            $result[] = ServiceRequestSummaryDTO::fromDBEJProblem($serviceRequests, $this->getDbeUser());
        }
        return $result;
    }

    /**
     * @throws Exception
     */
    function changeQueue()
    {
        $problemID = $this->getParam('problemID');
        $newQueue  = $this->getParam('queue');
        $reason    = $this->getParam('reason');
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
        $dbeUser->getActiveUsers();
        $allocatedUser = array();
        while ($dbeUser->fetchNext()) {

            $userRow = array(
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
        $count  = 0;
        $result = array();
        if ($customerRaisedRequests->Record) {
            do {


                $urlCustomer       = Controller::buildLink(
                    'SalesOrder.php',
                    array(
                        'action'     => 'search',
                        'customerID' => $customerRaisedRequests->Record['con_custno']
                    )
                );
                $urlServiceRequest = null;
                if ($customerRaisedRequests->Record['cpr_problemno'] > 0) {
                    $urlServiceRequest = Controller::buildLink(
                        'Activity.php',
                        array(
                            'action'    => 'displayServiceRequest',
                            'problemID' => $customerRaisedRequests->Record['cpr_problemno']
                        )
                    );
                }
                $truncatedReason = Utils::truncate(
                    $customerRaisedRequests->Record['cpr_reason'],
                    150
                );
                $bgColour        = self::RED;    // customer raised
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
                        'emailSubject'        => $customerRaisedRequests->Record['emailSubject']
                    )
                );
            } while ($customerRaisedRequests->next_record());
        }
        return json_encode($result);

    }

    function getPendingReopenedRequests()
    {
        $pendingReopenedRequests = $this->buActivity->getPendingReopenedRequests();
        $result                  = array();
        if ($pendingReopenedRequests && count($pendingReopenedRequests)) {

            foreach ($pendingReopenedRequests as $pendingReopenedRequest) {
                $pendingReopenSRURL              = Controller::buildLink(
                    'SRActivity.php',
                    array(
                        'action'           => "displayActivity",
                        'serviceRequestId' => $pendingReopenedRequest['problemID']
                    )
                );
                $pendingReopenSR                 = $pendingReopenedRequest['problemID'];
                $pendingReopenCustomerName       = $pendingReopenedRequest['customerName'];
                $pendingReopenedPriority         = $pendingReopenedRequest['priority'];
                $truncatedReason                 = Utils::truncate(
                    $pendingReopenedRequest['reason'],
                    150
                );
                $pendingReopenDescriptionSummary = $truncatedReason;
                $pendingReopenDescriptionURL     = Controller::buildLink(
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
    } // end function displayReport

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
        $body              = json_decode(file_get_contents('php://input'));
        $pendingReopenedID = $body->pendingReopenedID;
        $result            = $body->result;
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
        $this->setPageTitle('Current Service Requests');
        switch ($action) {
            case 'inbox':
                $this->setPageTitle('Current Service Requests');
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

    function getResponseColour($status,
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
        $userSelected    = ($selectedID == 0) ? CT_SELECTED : null;
        $urlAllocateUser = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'    => 'allocateUser',
                'userID'    => '0',
                'problemID' => $problemID
            )
        );
        $string          = '<option ' . $userSelected . ' value="' . $urlAllocateUser . '"></option>';
        foreach ($this->allocatedUser as $value) {

            $userSelected    = ($selectedID == $value['userID']) ? CT_SELECTED : null;
            $urlAllocateUser = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => 'allocateUser',
                    'userID'    => $value['userID'],
                    'problemID' => $problemID
                )
            );
            $string          .= '<option ' . $userSelected . ' value="' . $urlAllocateUser . '">' . $value['userName'] . '</option>';

        }
        return $string;

    }

    function getCustomerNameDisplayClass($specialAttentionFlag,
                                         $specialAttentionEndDate,
                                         $specialAttentionContactFlag
    )
    {
        if ($specialAttentionFlag == 'Y' && $specialAttentionEndDate >= date('Y-m-d')) {
            return 'specialAttentionCustomer';
        }
        if ($specialAttentionContactFlag == 'Y') {
            return 'specialAttentionContact';
        }
        return null;
    }

    function getHelpDeskInbox()
    {
        $this->setMethodName('getHelpDeskInbox');
        return json_encode($this->renderQueue(1));
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

    public function addCallBack()
    {
        $body              = $this->getBody();
        $problemID         = $body->problemID;
        $customerID        = $body->customerID;
        $contactID         = $body->contactID;
        $description       = $body->description;
        $callback_datetime = $body->date . ' ' . $body->time . ':00';
        $notifyTeamLead    = $body->notifyTeamLead ? 1 : 0;
        if (empty($problemID) || empty($customerID)) return $this->getResponseError(400, "Missing data");
        $problem = new DBEProblem($this);
        $problem->getRow($problemID);
        $dbeContact = new DBEContact($this);
        $dbeContact->getRow($contactID);
        $contactName  = $dbeContact->getValue(DBEContact::firstName) . " " . $dbeContact->getValue(
                DBEContact::lastName
            );
        $callDateTime = new DateTime($callback_datetime);
        $query        = "INSERT INTO `contact_callback` (
            
            `consID`,
            `problemID`,
            `contactID`,
            `description`,
            `callback_datetime`,
            `createAt`,
            `status`,
            `notifyTeamLead`
          )
          VALUES
            (              
              :consID,
              :problemID,
              :contactID,
              :description,
              :callback_datetime,
              :createAt,
              :status,
              :notifyTeamLead
            );
          ";
        $createAt     = date('Y-m-d H:i:s');
        DBConnect::execute(
            $query,
            [
                "consID"            => $this->dbeUser->getPKValue(),
                "problemID"         => $problemID,
                "contactID"         => $contactID,
                "description"       => $description,
                "callback_datetime" => $callback_datetime,
                "createAt"          => $createAt,
                "status"            => CallBackStatus::AWAITING,
                "notifyTeamLead"    => $notifyTeamLead,
            ]
        );
        // add activity
        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->setValue(DBECallActivity::callActTypeID, 11);
        $dbeCallActivity->setValue(DBECallActivity::contactID, $contactID);
        $dbeCallActivity->setValue(DBECallActivity::siteNo, $dbeContact->getValue(DBEContact::siteNo));
        $dbeCallActivity->setValue(DBECallActivity::userID, $this->dbeUser->getPKValue());
        $dbeCallActivity->setValue(DBECallActivity::date, date('Y-m-d'));
        $endTime   = new DateTime();
        $startTime = (clone $endTime)->sub(new DateInterval('PT3M'));
        $dbeCallActivity->setValue(DBECallActivity::startTime, $startTime->format('H:i'));
        $dbeCallActivity->setValue(DBECallActivity::endTime, $endTime->format('H:i'));
        $dbeCallActivity->setValue(DBECallActivity::status, 'C');
        $additionalInfo = !empty($description) ? "<p> Additional information: " . $description . '</p>' : '';
        $dbeCallActivity->setValue(
            DBECallActivity::reason,
            $contactName . ' called in regarding this update ' . $additionalInfo
        );
        $dbeCallActivity->setValue(
            DBECallActivity::cncNextAction,
            "Please call $contactName at " . $callDateTime->format(
                'Y-m-d'
            ) . " at " . $callDateTime->format('H:i')
        );
        $dbeCallActivity->setValue(DBECallActivity::awaitingCustomerResponseFlag, "N");
        $dbeCallActivity->setValue(DBECallActivity::problemID, $problemID);
        $dbeCallActivity->insertRow();
        $problem->setValue(DBEProblem::awaitingCustomerResponseFlag, 'N');
        $problem->setValue(DBEProblem::alarmDate, null);
        $problem->setValue(DBEProblem::alarmTime, null);
        $problem->updateRow();
        // Send email to engineer and team leader
        if ($problem->getValue(DBEProblem::userID) != null) {
            //get problem consultant
            $engineer = new DBEUser($this);
            $engineer->getRow($problem->getValue(DBEProblem::userID));
            //get consultant team
            $team = new DBETeam($this);
            $team->getRow($engineer->getValue(DBEUser::teamID));
            //get team leader
            $teamLeader = new DBEUser($this);
            $teamLeader->getRow($team->getValue(DBETeam::leaderId));
            $engineerEmail   = $engineer->getValue(DBEUser::username) . "@cnc-ltd.co.uk";
            $teamLeaderEmail = $teamLeader->getValue(DBEUser::username) . "@cnc-ltd.co.uk";
            $customer        = new DBECustomer($this);
            $customer->getRow($customerID);
            $to      = "";
            $cc      = [];
            $subject = "You have a call back request for $contactName from " . $customer->getValue(DBECustomer::name);
            if ($notifyTeamLead) {
                // send email to both
                $to    = $engineerEmail;
                $cc [] = $teamLeaderEmail;
            } else if ($engineer->getValue(DBEUser::callBackEmail)) { // check if the engineer has callback email check
                // send email to engineer only
                $to = $engineerEmail;
            }
            if ($to != "") {
                $dateTimeFormat = 'd/m/Y H:i';
                $buMail         = new BUMail($this);
                global $twig;
                $urlService = SITE_URL . '/SRActivity.php?action=displayActivity&serviceRequestId=' . $problemID;
                $body       = $twig->render(
                    '@internal/callBackEmail.html.twig',
                    [
                        'createAt'           => date($dateTimeFormat, strtotime($createAt)),
                        'urlService'         => $urlService,
                        'contactName'        => $contactName,
                        'customerName'       => $customer->getValue(DBECustomer::name),
                        'serviceRequestId'   => $problemID,
                        'callback_datetime'  => $callDateTime->format($dateTimeFormat),
                        'reason'             => $description != "" ? "Additional Information: " . $description : "",
                        'consultantFullName' => "{$this->dbeUser->getValue(DBEUser::firstName)} {$this->dbeUser->getValue(DBEUser::lastName)}"
                    ]
                );
                $buMail->sendSimpleEmail($body, $subject, $to, CONFIG_SUPPORT_EMAIL, $cc);
            }
        }
        $buActivity = new BUActivity($this);
        $buActivity->updateInbound($dbeCallActivity->getPKValue(), true);
        return ["status" => true, "callActivityID" => $dbeCallActivity->getPKValue()];
    }

    public function getMyCallback()
    {
        $unAssigned        = "";
        $team              = $_REQUEST["team"] ?? '';
        $customerID        = $_REQUEST["customerID"] ?? '';
        $customerCondition = "";
        if ($this->isSdManager() || $this->isSRQueueManager()) {

            $teamCondition = "";
            if ($team == 'H') $teamCondition .= " and pro_queue_no = 1";
            if ($team == 'E') $teamCondition .= " and pro_queue_no = 2";
            if ($team == 'SP') $teamCondition .= " and pro_queue_no = 3";
            if ($team == 'P') $teamCondition .= " and pro_queue_no = 5";
            if ($team == 'S') $teamCondition .= " and pro_queue_no = 4";
            if ($customerID != '') $customerCondition = " and  p.pro_custno = $customerID";
            $unAssigned = "or (p.`pro_consno`is null $teamCondition )";
        }
        $query      = "SELECT cb.id, cb.consID,cb.problemID,cb.contactID,cb.DESCRIPTION,cb.callback_datetime,cb.createAt,
                    concat(c.con_first_name,' ',c.con_last_name) contactName,
                    cus_name customerName,
                    TIMESTAMPDIFF(MINUTE,NOW(),cb.callback_datetime) timeRemain,
                    cb.status,
                    p.pro_consno useID
                FROM contact_callback cb
                JOIN  `problem` p ON cb.problemID=p.`pro_problemno`
                JOIN contact c on c.con_contno =cb.contactID
                JOIN customer cu on cu.cus_custno = p.pro_custno
                WHERE (p.`pro_consno`=:consID $unAssigned)
                and cb.status='awaiting'
                $customerCondition
                order by timeRemain asc
                ";
        $myCallBack = DBConnect::fetchAll($query, ["consID" => $this->dbeUser->getPKValue()]);
        return $myCallBack;
    }

    public function updateCallbackStatus()
    {
        try {
            $id     = @$_REQUEST['id'];
            $status = @$_REQUEST['status'];
            if (!isset($id) || !isset($status)) return ['status' => false, 'error' => "Missing data"];
            $dbeCallBack = new DBECallback($this);
            $dbeCallBack->getRow($id);
            $dbeCallBack->setValue(DBECallback::status, $status);
            $dbeCallBack->updateRow();
            $contactID  = $dbeCallBack->getValue(DBECallback::contactID);
            $problemID  = $dbeCallBack->getValue(DBECallback::problemID);
            $dbeContact = new DBEContact($this);
            $dbeContact->getRow($contactID);
            $contactName     = $dbeContact->getValue(DBEContact::firstName) . " " . $dbeContact->getValue(
                    DBEContact::lastName
                );
            $dbeCallActivity = new DBECallActivity($this);
            $dbeCallActivity->setValue(DBECallActivity::callActTypeID, 11);
            $dbeCallActivity->setValue(DBECallActivity::contactID, $contactID);
            $dbeCallActivity->setValue(DBECallActivity::siteNo, $dbeContact->getValue(DBEContact::siteNo));
            $dbeCallActivity->setValue(DBECallActivity::userID, $this->dbeUser->getPKValue());
            $dbeCallActivity->setValue(DBECallActivity::date, date('Y-m-d'));
            $dbeCallActivity->setValue(DBECallActivity::startTime, date('H:i'));
            $dbeCallActivity->setValue(DBECallActivity::reason, 'I have returned the call for ' . $contactName);
            $dbeCallActivity->setValue(DBECallActivity::awaitingCustomerResponseFlag, "N");
            $dbeCallActivity->setValue(DBECallActivity::problemID, $problemID);
            $dbeCallActivity->insertRow();
            $dbeProblem = new DBEProblem($this);
            $dbeProblem->getRow($problemID);
            $dbeProblem->setValue(DBEProblem::status, 'P');
            $dbeProblem->updateRow();
            return ['status' => true, "callActivityID" => $dbeCallActivity->getPKValue()];
        } catch (Exception $ex) {
            return ['status' => false, 'error' => $ex->getMessage()];
        }

    }

    public function cancelCallBack()
    {
        try {

            $id     = $_REQUEST['id'];
            $reason = $_REQUEST['reason'];
            if (!isset($id) || !isset($reason)) return ['status' => false, 'error' => "Missing data"];
            $dbeCallBack = new DBECallback($this);
            $dbeCallBack->getRow($id);
            $dbeContact = new DBEContact($this);
            $dbeContact->getRow($dbeCallBack->getValue(DBECallback::contactID));
            $staffName       = $this->dbeUser->getValue(DBEUser::firstName) . ' ' . $this->dbeUser->getValue(
                    DBEUser::lastName
                );
            $dbeCallActivity = new DBECallActivity($this);
            $dbeCallActivity->setValue(DBECallActivity::callActTypeID, 11);
            $dbeCallActivity->setValue(DBECallActivity::contactID, $dbeCallBack->getValue(DBECallback::contactID));
            $dbeCallActivity->setValue(DBECallActivity::siteNo, $dbeContact->getValue(DBEContact::siteNo));
            $dbeCallActivity->setValue(DBECallActivity::userID, $this->dbeUser->getPKValue());
            $dbeCallActivity->setValue(DBECallActivity::date, date('Y-m-d'));
            $dbeCallActivity->setValue(DBECallActivity::startTime, date('H:i'));
            $endTime = new DateTime();
            $dbeCallActivity->setValue(DBECallActivity::endTime, $endTime->format('H:i'));
            $dbeCallActivity->setValue(DBECallActivity::status, 'C');
            $dbeCallActivity->setValue(
                DBECallActivity::reason,
                $staffName . ' cancelled this call back for the following reason: ' . $reason
            );
            $dbeCallActivity->setValue(DBECallActivity::awaitingCustomerResponseFlag, "N");
            $dbeCallActivity->setValue(DBECallActivity::hideFromCustomerFlag, "Y");
            $dbeCallActivity->setValue(DBECallActivity::problemID, $dbeCallBack->getValue(DBECallback::problemID));
            $dbeCallActivity->insertRow();
            // update call back to be cancelled
            $dbeCallBack->setValue(DBECallback::status, CallBackStatus::CANCELED);
            $dbeCallBack->updateRow();
            return ['status' => true];
        } catch (Exception $ex) {
            return ['status' => false, 'error' => $ex->getMessage()];
        }
    }

    public function callBackSearch()
    {

        $consID     = $this->getParamOrNull('consID');
        $customerID = $this->getParamOrNull('customerID');
        $from       = $this->getParamOrNull('from');
        $to         = $this->getParamOrNull('to');
        $status     = $this->getParamOrNull('status');
        $query      = "SELECT cb.id, cb.consID,cb.problemID,cb.contactID,cb.DESCRIPTION,cb.callback_datetime,cb.createAt,
        concat(c.con_first_name,' ',c.con_last_name) contactName,
        cus_name customerName,
        TIMESTAMPDIFF(MINUTE,NOW(),cb.callback_datetime) timeRemain,
        cb.status,
        concat(cons.firstName,' ',cons.lastName) consName
    FROM contact_callback cb
        JOIN  `problem` p ON cb.problemID=p.`pro_problemno`
        JOIN contact c on c.con_contno =cb.contactID
        JOIN customer cu on cu.cus_custno = p.pro_custno
        JOIN consultant cons on cons.cns_consno=p.`pro_consno`
    WHERE (:consID is null or p.`pro_consno`=:consID)
        and (:customerID is null or p.`pro_custno`=:customerID)
        and (:from is null or cb.createAt >=:from)
        and (:to is null or cb.createAt <=:to)
        and (:status is null or cb.status =:status)
        order by timeRemain asc
        ";
        return DBConnect::fetchAll(
            $query,
            [
                "consID"     => $consID,
                "customerID" => $customerID,
                "from"       => $from,
                "to"         => $to,
                "status"     => $status
            ]
        );
    }

    /**
     * @throws Exception
     */
    function allocateAdditionalTime()
    {
        $this->setMethodName('allocateAdditionalTime');
        $body     = $this->getBody();
        $buHeader = new BUHeader($this);
        /** @var $dsHeader DataSet */
        $buHeader->getHeader($dsHeader);
        $minutesInADay = $dsHeader->getValue(DBEHeader::smallProjectsTeamMinutesInADay);
        /* validate if this is a POST request */
        $minutes   = 0;
        $teamLevel = $this->buActivity->getQueueTeamLevel($body->queueID);
        switch ($body->allocatedTimeAmount) {
            case 'minutes':
                $minutes = $body->allocatedTimeValue;
                break;
            case 'hours':
                $minutes = $body->allocatedTimeValue * 60;
                break;
            case 'days':
                $minutes = $minutesInADay * $body->allocatedTimeValue;
        }
        $this->buActivity->allocateAdditionalTime(
            $body->problemID,
            $teamLevel,
            $minutes,
            $body->comments,
            $this->dbeUser
        );
        $dbeTeam = new DBETeam($this);
        $dbeTeam->getRow($body->teamID);
        $this->buActivity->logOperationalActivity(
            $body->problemID,
            "<p>Additional time allocated to {$this->buActivity->getTeamName($teamLevel)} Team: {$minutes} minutes</p><p>{$body->comments}</p>"
        );
        return ["status" => true];
    }
}
