<?php
/**
 * Customer Activity Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\SDManagerDashboard\ServiceRequestSummaryDTO;
use CNCLTD\Utils;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
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
    const CONST_CALLBACK='callback';
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
        $checkPermissions=!in_array($this->getAction(),[self::CONST_CALLBACK]);
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
        $method=$this->getRequestMethodeName();
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
            case self::CONST_CALLBACK:
                switch ($method) {
                    case 'POST':
                        echo json_encode($this->addCallBack());
                        break;   
                    case 'GET':
                        echo json_encode($this->getMyCallback());
                        break; 
                }
                exit;
            default:
                $this->setTemplate();
                break;
        }
    }

    private function renderQueue($queueNo)
    {
        if ($queueNo == self::FIXED_AWAITING_CLOSURE) {
            $serviceRequests = $this->buActivity->getProblemsByStatus(
                'F',
                false
            );
        } else if ($queueNo == self::CUSTOMER_OPEN_SR) {
            $serviceRequests = $this->buActivity->getCustomerOpenSR($_REQUEST["customerID"]);
        } else {
            $serviceRequests = $this->buActivity->getProblemsByQueue($queueNo);
        }
        $result = [];
        while ($serviceRequests->fetchNext()) {
            $result[] = ServiceRequestSummaryDTO::fromDBEJProblem($serviceRequests);
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
        $dbeUser->getRows('firstName');
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
        $userSelected = ($selectedID == 0) ? CT_SELECTED : null;
        $urlAllocateUser = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'    => 'allocateUser',
                'userID'    => '0',
                'problemID' => $problemID
            )
        );
        $string = '<option ' . $userSelected . ' value="' . $urlAllocateUser . '"></option>';
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
            $string .= '<option ' . $userSelected . ' value="' . $urlAllocateUser . '">' . $value['userName'] . '</option>';

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
    public function addCallBack(){         
        $body=$this->getBody();
        $problemID=$body->problemID;
        $customerID=$body->customerID;
        $contactID=$body->contactID;
        $callActivityID=$body->callActivityID;
        $description=$body->description;
        $callback_datetime=$body->date.' '.$body->time.':00';        
        // echo  $callback_datetime;
        // exit;
        if(empty($problemID)||empty($customerID))
            return $this->getResponseError(400,"Missing data");
        $dbeCallback=new DBECallback($this);
        $dbeCallback->setValue(DBECallback::problemID,$problemID);
        $dbeCallback->setValue(DBECallback::callActivityID,$callActivityID);
        $dbeCallback->setValue(DBECallback::contactID,$contactID);
        $dbeCallback->setValue(DBECallback::description,$description);
        $dbeCallback->setValue(DBECallback::callback_datetime,$callback_datetime);
        $dbeCallback->setValue(DBECallback::consID,$this->dbeUser->getPKValue());
        $dbeCallback->setValue(DBECallback::createAt,date('Y-m-d H:i:s'));
        $dbeCallback->setValue(DBECallback::is_callback,0);
        $dbeCallback->insertRow();
        return ["status"=>true];              
    }
    public function getMyCallback(){
        $query="SELECT cb.id, cb.consID,cb.problemID,cb.callActivityID,cb.contactID,cb.DESCRIPTION,cb.callback_datetime,cb.is_callback,cb.createAt,
                    concat(c.con_first_name,' ',c.con_last_name) contactName,
                    cus_name customerName,
                    TIMESTAMPDIFF(MINUTE,NOW(),cb.callback_datetime) timeRemain
                FROM contact_callback cb
                JOIN  `problem` p ON cb.problemID=p.`pro_problemno`
                JOIN contact c on c.con_contno =cb.contactID
                JOIN customer cu on cu.cus_custno = p.pro_custno
                WHERE p.`pro_consno`=:consID
                order by timeRemain asc
                ";
        return DBConnect::fetchAll($query,["consID"=>$this->dbeUser->getPKValue()]);
    }
}
