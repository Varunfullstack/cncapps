<?php
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg["path_dbe"] . "/DBConnect.php");
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg ["path_dbe"] . "/DBEJCallActivity.php");
require_once($cfg['path_bu'] . '/BUHeader.inc.php');

class CTRequestDashboard extends CTCNC
{
    const GET_TIME_REQUEST = "getTimeRequest";
    const GET_CHANGE_REQUEST="getChangeRequest";
    const GET_SALES_REQUEST="getSalesRequest";
    const SET_TIME_REQUEST="setTimeRequest";
    const SET_CHANGE_REQUEST="processChangeRequest";
    const SET_ALLOCATE_USER="setAllocateUser";
    const SET_SALES_REQUEST="processSalesRequest";
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
            false
        );
        if (!self::isSdManager()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(225);
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
        $dbejCallActivity = new DBEJCallActivity($this);
        $showHelpDesk      = isset($_REQUEST['HD']);
        $showEscalation    = isset($_REQUEST['ES']);
        $showSmallProjects = isset($_REQUEST['SP']);
        $showProjects      = isset($_REQUEST['P']);                 
        $isP5=isset($_REQUEST['P5']);       
        $limit=$this->getParam("limit");       
        $dbejCallActivity->getPendingTimeRequestRows($showHelpDesk, $showEscalation , $showSmallProjects, $showProjects ,  $isP5,$limit);
        $buActivity = new BUActivity($this);

        $buHeader = new BUHeader($this);
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);
        $isAdditionalTimeApprover = $this->dbeUser->getValue(DBEUser::additionalTimeLevelApprover);
        $result=array();
        while ($dbejCallActivity->fetchNext()) {
            $problemID = $dbejCallActivity->getValue(DBEJCallActivity::problemID);
            $lastActivity = $buActivity->getLastActivityInProblem($problemID);
            // $srLink = Controller::buildLink(
            //     'SRActivity.php',
            //     [
            //         "callActivityID" => $lastActivity->getValue(DBEJCallActivity::callActivityID),
            //         "action"         => "displayActivity"
            //     ]
            // );

            // $srLink = "<a href='$srLink' target='_blank'>" . $problemID . "</a>";

            $processCRLink = Controller::buildLink(
                'Activity.php',
                [
                    "callActivityID" => $dbejCallActivity->getValue(DBEJCallActivity::callActivityID),
                    "action"         => "timeRequestReview"
                ]
            );


            $requestingUserID = $dbejCallActivity->getValue(DBEJCallActivity::userID);
            $requestingUser = new DBEUser($this);
            $requestingUser->getRow($requestingUserID);

            $teamID = $requestingUser->getValue(DBEUser::teamID);

            $leftOnBudget = null;
            $usedMinutes = 0;
            $assignedMinutes = 0;

            $dbeProblem = new DBEJProblem($this);
            $dbeProblem->getRow($problemID);
            $teamName = '';
            //$processCRLink = "<a href='$processCRLink'>Process Time Request</a>";
            $isOverLimit = false;
            switch ($teamID) {
                case 1:
                    $usedMinutes = $buActivity->getHDTeamUsedTime($problemID);
                    $assignedMinutes = $dbeProblem->getValue(DBEProblem::hdLimitMinutes);
                    $isOverLimit = $assignedMinutes >= $dsHeader->getValue(
                            DBEHeader::hdTeamManagementTimeApprovalMinutes
                        );
                    $teamName = 'Helpdesk';
                    break;
                case 2:
                    $usedMinutes = $buActivity->getESTeamUsedTime($problemID);
                    $assignedMinutes = $dbeProblem->getValue(DBEProblem::esLimitMinutes);
                    $isOverLimit = $assignedMinutes >= $dsHeader->getValue(
                            DBEHeader::esTeamManagementTimeApprovalMinutes
                        );
                    $teamName = 'Escalation';
                    break;
                case 4:
                    $usedMinutes = $buActivity->getSPTeamUsedTime($problemID);
                    $assignedMinutes = $dbeProblem->getValue(DBEProblem::smallProjectsTeamLimitMinutes);
                    $isOverLimit = $assignedMinutes >= $dsHeader->getValue(
                            DBEHeader::smallProjectsTeamManagementTimeApprovalMinutes
                        );
                    $teamName = 'Small Projects';
                    break;
                case 5:
                    $usedMinutes = $buActivity->getUsedTimeForProblemAndTeam($problemID, 5);
                    $assignedMinutes = $dbeProblem->getValue(DBEProblem::projectTeamLimitMinutes);
                    $teamName = 'Projects';
            }
            if ($isOverLimit && !$isAdditionalTimeApprover) {
                $processCRLink = '';
            }
            $leftOnBudget = $assignedMinutes - $usedMinutes;
            $requestedDateTimeString = $dbejCallActivity->getValue(
                    DBEJCallActivity::date
                ) . ' ' . $dbejCallActivity->getValue(DBEJCallActivity::startTime) . ":00";
            $requestedDateTime = DateTime::createFromFormat(DATE_MYSQL_DATETIME, $requestedDateTimeString);
            $alertTime = (new DateTime(''))->sub(
                new DateInterval('PT' . $dsHeader->getValue(DBEHeader::pendingTimeLimitActionThresholdMinutes) . "M")
            );
            array_push($result,
                [
                    'customerName'      => $dbejCallActivity->getValue(DBEJCallActivity::customerName),
                    //'srLink'            => $srLink,
                    'notes'             => $dbejCallActivity->getValue(DBEJCallActivity::reason),
                    'requestedBy'       => $dbejCallActivity->getValue(DBEJCallActivity::userName),
                    'requestedDateTime' => $requestedDateTimeString,
                    'processCRLink'     => $processCRLink,
                    'chargeableHours'   => $dbeProblem->getValue(DBEJProblem::chargeableActivityDurationHours),
                    'timeSpentSoFar'    => round($usedMinutes),
                    'timeLeftOnBudget'  => $leftOnBudget,
                    'requesterTeam'     => $teamName,
                    'alertRow'          => $requestedDateTime < $alertTime ? 'warning' : null,
                    'approvalLevel'     => $isOverLimit ? 'Mgmt' : 'Team Lead',
                    "callActivityID" => $lastActivity->getValue(DBEJCallActivity::callActivityID),
                    'problemID' => $dbejCallActivity->getValue(DBEJCallActivity::problemID),
                ]
            );

             
        }
        return $result;
    }
    function setTimeRequest()
    {
        $body = json_decode(file_get_contents('php://input'));
        $buActivity=new BUActivity($this);
        $this->setMethodName('setTimeRequest');
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);
        $callActivityID = $body->callActivityID;
        $dsCallActivity = new DataSet($this);
        $buActivity->getActivityByID(
            $callActivityID,
            $dsCallActivity
        );
        $problemID        = $dsCallActivity->getValue(DBEJCallActivity::problemID); 
        $dbeProblem       = new DBEProblem($this);
        $dbeProblem->getRow($problemID);         
        $requestorID = $dsCallActivity->getValue(DBECallActivity::userID);
        $dbeUser     = new DBEUser($this);
        $dbeUser->getRow($requestorID);
        $teamID             = $dbeUser->getValue(DBEUser::teamID);        
        $assignedMinutes    = 0;

        $isOverLimit        = false;
        switch ($teamID) {
            case 1:                
                $assignedMinutes    = $dbeProblem->getValue(DBEProblem::hdLimitMinutes);                
                $isOverLimit        = $assignedMinutes >= $dsHeader->getValue(
                        DBEHeader::hdTeamManagementTimeApprovalMinutes
                    );
                break;
            case 2:                
                $assignedMinutes    = $dbeProblem->getValue(DBEProblem::esLimitMinutes);                
                $isOverLimit        = $assignedMinutes >= $dsHeader->getValue(
                        DBEHeader::esTeamManagementTimeApprovalMinutes
                    );
                break;
            case 4:
                $assignedMinutes    = $dbeProblem->getValue(DBEProblem::smallProjectsTeamLimitMinutes);              
                $isOverLimit        = $assignedMinutes >= $dsHeader->getValue(
                        DBEHeader::smallProjectsTeamManagementTimeApprovalMinutes
                    );
                break;
            case 5:
                $assignedMinutes    = $dbeProblem->getValue(DBEProblem::projectTeamLimitMinutes);
               
        }
         
        {

            switch ($body->status ) {

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
            switch ($body->allocatedTimeAmount ) {
                case 'minutes':
                    $minutes = $body->allocatedTimeValue ;
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
        }      
        return ["status"=>true];

    }
    //--------------change request
    function getChangeRequestData()
    {
        $showHelpDesk      = isset($_REQUEST['HD']);
        $showEscalation    = isset($_REQUEST['ES']);
        $showSmallProjects = isset($_REQUEST['SP']);
        $showProjects      = isset($_REQUEST['P']);
        $isP5      = isset($_REQUEST['P5']);
        $dbejCallActivity  = new DBEJCallActivity($this);
        $dbejCallActivity->getPendingChangeRequestRows(
            $showHelpDesk,
            $showEscalation,
            $showSmallProjects,
            $showProjects,
            $isP5
        );
        $result = [];
        while ($dbejCallActivity->fetchNext()) {
            $result[] = [
                'customerName'     => $dbejCallActivity->getValue(DBEJCallActivity::customerName),
                'problemID' => $dbejCallActivity->getValue(DBEJCallActivity::problemID),
                'requestBody'      => $dbejCallActivity->getValue(DBEJCallActivity::reason),
                'requestedBy'    => $dbejCallActivity->getValue(DBEJCallActivity::userAccount),
                'requestedDateTime'      => $dbejCallActivity->getValue(
                        DBEJCallActivity::date
                    ) . ' ' . $dbejCallActivity->getValue(DBEJCallActivity::startTime) . ':00',
                'callActivityID'       => $dbejCallActivity->getValue(DBEJCallActivity::callActivityID)
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
        $body = json_decode(file_get_contents('php://input'));
        $callActivityID = $body->callActivityID;        
        switch ($body->status) {
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
        $buActivity=new BUActivity($this);
        $buActivity->changeRequestProcess(
            $callActivityID,
            $this->userID,
            $option,
            $body->comments
        );
        return ["status"=>true];
    }
    //-----------------sales request
    function getSalesRequestData()
    {
        $showHelpDesk      = isset($_REQUEST['HD']);
        $showEscalation    = isset($_REQUEST['ES']);
        $showSmallProjects = isset($_REQUEST['SP']);
        $showProjects      = isset($_REQUEST['P']);
        $isP5      = isset($_REQUEST['P5']);
        $dbejCallActivity  = new DBEJCallActivity($this);
        $result=$dbejCallActivity->getPendingSalesRequestRows(
            $showHelpDesk,
            $showEscalation,
            $showSmallProjects,
            $showProjects,
            $isP5
        );
        $result = array_map(
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
                        "filename" => $dbeJCallDocument->getValue(DBECallDocumentWithoutFile::filename)
                    ];
                }
                return $request;
            },
            $result
        );
        //$result = [];
        // while ($dbejCallActivity->fetchNext()) {
        //     $result[] = [
        //         'customerName'     => $dbejCallActivity->getValue(DBEJCallActivity::customerName),
        //         'problemID' => $dbejCallActivity->getValue(DBEJCallActivity::problemID),
        //         'requestBody'      => $dbejCallActivity->getValue(DBEJCallActivity::reason),
        //         'requestedBy'    => $dbejCallActivity->getValue(DBEJCallActivity::userAccount),
        //         'requestedDateTime'      => $dbejCallActivity->getValue(
        //                 DBEJCallActivity::date
        //             ) . ' ' . $dbejCallActivity->getValue(DBEJCallActivity::startTime) . ':00',
        //         'callActivityID'       => $dbejCallActivity->getValue(DBEJCallActivity::callActivityID),
        //         'type'    => $dbejCallActivity->getValue("type"),
        //         'salesRequestAssignedUserId'=>$dbejCallActivity->getValue('salesRequestAssignedUserId'),

        //     ];
        // }
        return $result;
/*
        global $db;
                $query = "
                        SELECT
                        callactivity.`caa_callactivityno` AS activityId,
                        callactivity.`caa_problemno` AS serviceRequestId,
                        standardtext.`stt_desc` AS `type`,
                        customer.cus_name AS customerName,
                        callactivity.`reason` AS requestBody,
                        CONCAT(callactivity.`caa_date`,' ',callactivity.`caa_starttime`,':00') AS requestedAt,
                        consultant.cns_name AS requesterName,
                        problem.`salesRequestAssignedUserId`
                        FROM
                        callactivity 
                        LEFT JOIN standardtext ON callactivity.`requestType` = standardtext.`stt_standardtextno`
                        LEFT JOIN problem ON callactivity.`caa_problemno` = problem.`pro_problemno`
                        LEFT JOIN customer ON problem.`pro_custno` = customer.`cus_custno`
                        LEFT JOIN consultant ON callactivity.`caa_consno` = consultant.cns_consno
                        WHERE callactivity.salesRequestStatus = 'O'
                        AND caa_callacttypeno = 43";
                $statement = $db->preparedQuery($query, []);
                $requests = $statement->fetch_all(MYSQLI_ASSOC);
                $requests = array_map(
                    function ($request) {
                        $dbeJCallDocument = new DBECallDocumentWithoutFile($this);
                        $dbeJCallDocument->setValue(
                            DBECallDocumentWithoutFile::callActivityID,
                            $request['activityId']
                        );
                        $dbeJCallDocument->getRowsByColumn(DBECallDocumentWithoutFile::callActivityID);
                        $request['attachments'] = [];
                        while ($dbeJCallDocument->fetchNext()) {
                            $request['attachments'][] = [
                                "documentId" => $dbeJCallDocument->getValue(DBECallDocumentWithoutFile::callDocumentID)
                            ];
                        }
                        return $request;
                    },
                    $requests
                );
                echo json_encode(["status" => "ok", "data" => $requests]);
                exit;
                */
    }
    function setAllocateUser(){
        $userID=$_REQUEST["userID"];
        $problemID=$_REQUEST["problemID"];        
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
        return ["status"=>true];
    }
    function processSalesRequest()
    {
        $this->setMethodName('processSalesRequest');
        $body = json_decode(file_get_contents('php://input'));
        $callActivityID = $body->callActivityID;
        $dsCallActivity = new DataSet($this);
        $buActivity=new BUActivity($this);
        $buActivity->getActivityByID(
            $callActivityID,
            $dsCallActivity
        );
        if ($dsCallActivity->getValue(DBECallActivity::salesRequestStatus) !== 'O') {
            return ["status"=>false,"error"=>"This Sales Request has already been processed"];            
        }
        {
            $notify = true;
            switch ($body->status) {

                case 'Approve Without Notifying Sales':
                    $notify = false;
                case 'Approve':
                    $option = 'A';
                    break;
                case 'Deny':
                    $option = 'D';
                    break;
                default:
                    throw new Exception('Action not valid');
            }
            $buActivity->salesRequestProcess(
                $callActivityID,
                $this->userID,
                $option,
                $body->comments,
                $notify
            );            
        }
        return ["status"=>true];            
    }
}
