<?php
global $cfg;

use CNCLTD\Business\BUActivity;
use CNCLTD\Data\DBEJProblem;

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg["path_dbe"] . "/DBConnect.php");
require_once($cfg ["path_dbe"] . "/DBEJCallActivity.php");
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_dbe'] . '/DBECallDocumentWithoutFile.php');

class CTRequestDashboard extends CTCNC
{
    const GET_TIME_REQUEST                = "getTimeRequest";
    const GET_CHANGE_REQUEST              = "getChangeRequest";
    const GET_SALES_REQUEST               = "getSalesRequest";
    const SET_TIME_REQUEST                = "setTimeRequest";
    const SET_CHANGE_REQUEST              = "processChangeRequest";
    const SET_ALLOCATE_USER               = "setAllocateUser";
    const SET_SALES_REQUEST               = "processSalesRequest";
    const APPROVE_WITHOUT_NOTIFYING_SALES = 'Approve Without Notifying Sales';
    const APPROVE                         = 'Approve';
    const DENY                            = 'Deny';
    const FURTHER_DETAILS_REQUIRED        = 'Further Details Required';
    const DELETE                          = 'Delete';

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
            $cfg,
        );
        if (!self::isSdManager()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(202);
    }


    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case self::GET_TIME_REQUEST:
                echo json_encode($this->getTimeRequestData());
                exit;
            case self::GET_CHANGE_REQUEST:
                echo json_encode($this->getChangeRequestData());
                exit;
            case self::GET_SALES_REQUEST:
                echo json_encode($this->getSalesRequestData());
                exit;
            case self::SET_TIME_REQUEST:
                echo json_encode($this->setTimeRequest());
                exit;
            case self::SET_CHANGE_REQUEST:
                echo json_encode($this->processChangeRequest());
                exit;
            case self::SET_ALLOCATE_USER:
                echo json_encode($this->setAllocateUser());
                exit;
            case self::SET_SALES_REQUEST:
                echo json_encode($this->processSalesRequest());
                exit;
            default:
                $this->setTemplate();
                break;
        }
    }


    function setTemplate()
    {

        $this->setPageTitle('Request Dashboard');
        $this->setTemplateFiles(
            array('RequestDashboard' => 'RequestDashboard.rct')
        );
        $isAdditionalTimeApprover = $this->dbeUser->getValue(DBEUser::additionalTimeLevelApprover);
        $this->template->setVar(
            'additionalTimeLimitApprover',
            $isAdditionalTimeApprover ? 'true' : 'false',
        );
        $this->loadReactScript('RequestDashboardComponent.js');
        $this->loadReactCSS('RequestDashboardComponent.css');
        $this->template->parse(
            'CONTENTS',
            'RequestDashboard',
            true
        );
        $this->parsePage();
    }

    function getTimeRequestData()
    {
        $dbejCallActivity  = new DBEJCallActivity($this);
        $showHelpDesk      = isset($_REQUEST['HD']);
        $showEscalation    = isset($_REQUEST['ES']);
        $showSmallProjects = isset($_REQUEST['SP']);
        $showProjects      = isset($_REQUEST['P']);
        $limit             = $this->getParam("limit");
        $dbejCallActivity->getPendingTimeRequestRows(
            $showHelpDesk,
            $showEscalation,
            $showSmallProjects,
            $showProjects,
            $limit
        );
        $buActivity = new BUActivity($this);
        $buHeader   = new BUHeader($this);
        $dsHeader   = new DataSet($this);
        $buHeader->getHeader($dsHeader);
        $result = array();
        while ($dbejCallActivity->fetchNext()) {
            $problemID        = $dbejCallActivity->getValue(DBEJCallActivity::problemID);
            $requestingUserID = $dbejCallActivity->getValue(DBEJCallActivity::userID);
            $requestingUser   = new DBEUser($this);
            $requestingUser->getRow($requestingUserID);
            $teamID          = $requestingUser->getValue(DBEUser::teamID);
            $leftOnBudget    = null;
            $usedMinutes     = 0;
            $assignedMinutes = 0;
            $dbeProblem      = new DBEJProblem($this);
            $dbeProblem->getRow($problemID);
            $teamName                          = '';
            $teamManagementTimeApprovalMinutes = null;
            switch ($teamID) {
                case 1:
                    $usedMinutes                       = $buActivity->getHDTeamUsedTime($problemID);
                    $assignedMinutes                   = $dbeProblem->getValue(DBEProblem::hdLimitMinutes);
                    $teamManagementTimeApprovalMinutes = $dsHeader->getValue(
                        DBEHeader::hdTeamManagementTimeApprovalMinutes
                    );
                    $teamName                          = 'Helpdesk';
                    break;
                case 2:
                    $usedMinutes                       = $buActivity->getESTeamUsedTime($problemID);
                    $assignedMinutes                   = $dbeProblem->getValue(DBEProblem::esLimitMinutes);
                    $teamManagementTimeApprovalMinutes = $dsHeader->getValue(
                        DBEHeader::esTeamManagementTimeApprovalMinutes
                    );
                    $teamName                          = 'Escalation';
                    break;
                case 4:
                    $usedMinutes                       = $buActivity->getSPTeamUsedTime($problemID);
                    $assignedMinutes                   = $dbeProblem->getValue(
                        DBEProblem::smallProjectsTeamLimitMinutes
                    );
                    $teamManagementTimeApprovalMinutes = $dsHeader->getValue(
                        DBEHeader::smallProjectsTeamManagementTimeApprovalMinutes
                    );
                    $teamName                          = 'Small Projects';
                    break;
                case 5:
                    $usedMinutes     = $buActivity->getUsedTimeForProblemAndTeam($problemID, 5);
                    $assignedMinutes = $dbeProblem->getValue(DBEProblem::projectTeamLimitMinutes);
                    $teamName        = 'Projects';
            }
            $leftOnBudget            = $assignedMinutes - $usedMinutes;
            $requestedDateTimeString = $dbejCallActivity->getValue(
                    DBEJCallActivity::date
                ) . ' ' . $dbejCallActivity->getValue(DBEJCallActivity::startTime) . ":00";
            $requestedDateTime       = DateTime::createFromFormat(DATE_MYSQL_DATETIME, $requestedDateTimeString);
            $alertTime               = (new DateTime(''))->sub(
                new DateInterval('PT' . $dsHeader->getValue(DBEHeader::pendingTimeLimitActionThresholdMinutes) . "M")
            );
            array_push(
                $result,
                [
                    'customerName'                  => $dbejCallActivity->getValue(DBEJCallActivity::customerName),
                    'notes'                         => $dbejCallActivity->getValue(DBEJCallActivity::reason),
                    'requestedBy'                   => $dbejCallActivity->getValue(DBEJCallActivity::userName),
                    'requestedDateTime'             => $requestedDateTimeString,
                    'chargeableHours'               => $dbeProblem->getValue(
                        DBEJProblem::chargeableActivityDurationHours
                    ),
                    'timeSpentSoFar'                => round($usedMinutes),
                    'timeLeftOnBudget'              => $leftOnBudget,
                    'requesterTeam'                 => $teamName,
                    'alertRow'                      => $requestedDateTime < $alertTime ? 'warning' : null,
                    'teamManagementApprovalMinutes' => $teamManagementTimeApprovalMinutes,
                    "callActivityID"                => $dbejCallActivity->getValue(DBEJCallActivity::callActivityID),
                    'problemID'                     => $dbejCallActivity->getValue(DBEJCallActivity::problemID),
                    "linkedSalesOrderID"            => $dbejCallActivity->getValue(
                        DBEJCallActivity::linkedSalesOrderID
                    ),
                ]
            );


        }
        return $result;
    }

    function setTimeRequest()
    {
        $body       = json_decode(file_get_contents('php://input'));
        $buActivity = new BUActivity($this);
        $this->setMethodName('setTimeRequest');
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);
        $callActivityID = $body->callActivityID;
        $dsCallActivity = new DataSet($this);
        $buActivity->getActivityByID(
            $callActivityID,
            $dsCallActivity
        );
        $problemID  = $dsCallActivity->getValue(DBEJCallActivity::problemID);
        $dbeProblem = new DBEProblem($this);
        $dbeProblem->getRow($problemID);
        $requestorID = $dsCallActivity->getValue(DBECallActivity::userID);
        $dbeUser     = new DBEUser($this);
        $dbeUser->getRow($requestorID);
        switch ($body->status) {
            case self::APPROVE:
                $option = 'A';
                break;
            case self::DENY:
                $option = 'D';
                break;
            case self::DELETE:
            default:
                $option = 'DEL';
                break;
        }
        $minutes = 0;
        switch ($body->allocatedTimeAmount) {
            case 'minutes':
                $minutes = $body->allocatedTimeValue;
                break;
            case 'hours':
                $minutes = $body->allocatedTimeValue * 60;
                break;
            case 'days':
                $buHeader = new BUHeader($this);
                /** @var $dsHeader DataSet */
                $buHeader->getHeader($dsHeader);
                $minutesInADay = $dsHeader->getValue(DBEHeader::smallProjectsTeamMinutesInADay);
                $minutes       = $minutesInADay * $body->allocatedTimeValue;
        }
        $buActivity->timeRequestProcess(
            $callActivityID,
            $this->userID,
            $option,
            $body->comments,
            $minutes
        );
        return ["status" => true];

    }

    function getChangeRequestData()
    {
        $showHelpDesk      = isset($_REQUEST['HD']);
        $showEscalation    = isset($_REQUEST['ES']);
        $showSmallProjects = isset($_REQUEST['SP']);
        $showProjects      = isset($_REQUEST['P']);
        $dbejCallActivity  = new DBEJCallActivity($this);
        $dbejCallActivity->getPendingChangeRequestRows(
            $showHelpDesk,
            $showEscalation,
            $showSmallProjects,
            $showProjects
        );
        $result = [];
        while ($dbejCallActivity->fetchNext()) {
            $result[] = [
                'customerName'       => $dbejCallActivity->getValue(DBEJCallActivity::customerName),
                'problemID'          => $dbejCallActivity->getValue(DBEJCallActivity::problemID),
                'requestBody'        => $dbejCallActivity->getValue(DBEJCallActivity::reason),
                'requestedBy'        => $dbejCallActivity->getValue(DBEJCallActivity::userAccount),
                'requestedDateTime'  => $dbejCallActivity->getValue(
                        DBEJCallActivity::date
                    ) . ' ' . $dbejCallActivity->getValue(DBEJCallActivity::startTime) . ':00',
                'callActivityID'     => $dbejCallActivity->getValue(DBEJCallActivity::callActivityID),
                "linkedSalesOrderID" => $dbejCallActivity->getValue(DBEJCallActivity::linkedSalesOrderID),
            ];
        }
        return $result;
    }

    /**
     * @throws Exception
     */
    function processChangeRequest()
    {
        $this->setMethodName('processChangeRequest');
        $body           = $this->getJSONData();
        $callActivityID = $body["callActivityID"];
        switch ($body["status"]) {
            case self::APPROVE:
                $option = 'A';
                break;
            case self::DENY:
                $option = 'D';
                break;
        }
        $buActivity = new BUActivity($this);
        $buActivity->changeRequestProcess(
            $callActivityID,
            $this->userID,
            $option,
            $body["comments"]
        );
        return ["status" => true];
    }

    function getSalesRequestData()
    {
        $showHelpDesk      = isset($_REQUEST['HD']);
        $showEscalation    = isset($_REQUEST['ES']);
        $showSmallProjects = isset($_REQUEST['SP']);
        $showProjects      = isset($_REQUEST['P']);
        $dbejCallActivity  = new DBEJCallActivity($this);
        $result            = $dbejCallActivity->getPendingSalesRequestRows(
            $showHelpDesk,
            $showEscalation,
            $showSmallProjects,
            $showProjects
        );
        $result            = array_map(
            function ($request) {
                $dbeJCallDocument = new DBECallDocumentWithoutFile($this);
                $dbeJCallDocument->setValue(
                    DBECallDocumentWithoutFile::problemID,
                    $request['problemID']
                );
                $dbeJCallDocument->getRowsByColumn(DBECallDocumentWithoutFile::problemID);
                $request['attachments'] = [];
                while ($dbeJCallDocument->fetchNext()) {
                    $request['attachments'][] = [
                        "documentId" => $dbeJCallDocument->getValue(DBECallDocumentWithoutFile::callDocumentID),
                        "filename"   => $dbeJCallDocument->getValue(DBECallDocumentWithoutFile::filename)
                    ];
                }
                return $request;
            },
            $result
        );
        return $result;

    }

    function setAllocateUser()
    {
        $userID    = $_REQUEST["userID"];
        $problemID = $_REQUEST["problemID"];
        if (!isset($userID)) {
            throw new Exception('user ID Field required');
        }
        if (!isset($problemID)) {
            throw new Exception('Problem ID required');
        }
        $dbeProblem = new DBEProblem($this);
        $dbeProblem->getRow($problemID);
        $dbeProblem->setValue(DBEProblem::salesRequestAssignedUserId, $userID);
        $dbeProblem->updateRow();
        return ["status" => true];
    }

    function processSalesRequest()
    {
        $this->setMethodName('processSalesRequest');
        $body           = $this->getJSONData();
        $callActivityID = $body['callActivityID'];
        $dsCallActivity = new DataSet($this);
        $buActivity     = new BUActivity($this);
        $buActivity->getActivityByID(
            $callActivityID,
            $dsCallActivity
        );
        if ($dsCallActivity->getValue(DBECallActivity::salesRequestStatus) !== 'O') {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, "This sales request has already been processed");
        }
        {
            $notify = true;
            switch ($body['status']) {
                case self::APPROVE_WITHOUT_NOTIFYING_SALES:
                    $notify = false;
                case self::APPROVE:
                    $option = 'A';
                    break;
                case self::DENY:
                    $option = 'D';
                    break;
                default:
                    throw new \CNCLTD\Exceptions\JsonHttpException(400, 'Action not valid');
            }
            try {
                $buActivity->salesRequestProcess(
                    $callActivityID,
                    $this->userID,
                    $option,
                    $body['comments'],
                    $notify
                );
            } catch (\Exception $exception) {
                throw new \CNCLTD\Exceptions\JsonHttpException(400, $exception->getMessage());
            }
        }
        return ["status" => true];
    }
}
