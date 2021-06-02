<?php
/**
 * Further Action controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Data\DBConnect;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUProject.inc.php');
require_once($cfg['path_bu'] . '/BUSalesOrder.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEOrdhead.inc.php');
require_once($cfg['path_dbe'] . '/DBEProject.inc.php');
require_once($cfg['path_dbe'] . '/DBEProjectIssues.inc.php');
require_once($cfg['path_bu'] . '/BUExpense.inc.php');

// Actions
abstract class OrderItems
{
    const DAILY_LABOUR_CHARGE      = 1502;
    const HOURLY_LABOUR_CHARGE     = 2237;
    const DAILY_OOH_LABOUR_CHARGE  = 1503;
    const HOURLY_OOH_LABOUR_CHARGE = 16865;
}

;

class CTProjects extends CTCNC
{
    const DOWNLOAD_PROJECT_PLAN                        = "downloadProjectPlan";
    const CONST_PROJECTS                               = 'projects';
    const CONST_PROJECT_STAGE                          = 'projectStage';
    const CONST_HISTORY                                = 'history';
    const CONST_PROJECT                                = 'project';
    const CONST_PROJECT_SUMMARY                        = 'projectSummary';
    const CONST_BUDGET_DATA                            = 'budgetData';
    const CONST_PROJECT_FILES                          = 'projectFiles';
    const CONST_UNLINK_SALES_ORDER                     = 'unlinkSalesOrder';
    const CONST_LINK_SALES_ORDER                       = 'linkSalesOrder';
    const CONST_CALCULATE_BUDGET                       = 'calculateBudget';
    const CONST_PROJECT_ISSUES                         = 'projectIssues';
    const CONST_PROJECT_STAGES                         = 'projectStagesHistory';
    const CONST_PROJECT_ORIGINAL_QUOTOE_DOC            = 'projectOriginalQuotoeDoc';
    const CONST_PROJECTS_SUMMARY                       = 'projectsSummary';
    const CONST_PROJECTS_SEARCH                        = 'projectsSearch';
    const CONST_PROJECTS_CONSULTANT_IN_PROGRESS        = 'projectsByConsultantInProgress';
    const CONST_PROJECTS_CUSTOMER_STAGE_FALLS_STARTEND = 'projectsByCustomerStageFallsStartEnd';
    const CONST_PROJECTS_WITHOUT_CLOUSURE_MEETING      = 'projectsWithoutClousureMeeting';

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
        $roles = [
            "technical"
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(107);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($this->getAction()) {
            case self::CONST_PROJECTS:
                echo json_encode($this->getProjects());
                break;
            case self::CONST_HISTORY:
                echo json_encode($this->getProjectHistory());
                break;
            case self::CONST_PROJECT:
                if ($method == 'GET') echo json_encode(
                    $this->getProject()
                ); else if ($method == 'PUT') echo json_encode(
                    $this->updateProject()
                ); else if ($method == 'POST') echo json_encode($this->updateProject(true));
                break;
            case self::CONST_PROJECT_STAGE:
                echo json_encode($this->updateProjectStage());
                break;
            case self::CONST_PROJECT_SUMMARY:
                if ($method == 'GET') echo json_encode(
                    $this->getProjectSummary()
                ); else if ($method == 'PUT') echo json_encode($this->updateProjectSummary());
                break;
            case self::CONST_BUDGET_DATA:
                echo json_encode($this->getBudgetData());
                break;
            case self::CONST_PROJECT_FILES:
                if ($method == 'POST') echo json_encode(
                    $this->uploadProjectFiles()
                ); else if ($method == 'GET') $this->downloadProjectFiles();
                break;
            case self::CONST_UNLINK_SALES_ORDER:
                echo json_encode($this->unlinkSalesOrder());
                break;
            case self::CONST_LINK_SALES_ORDER:
                echo json_encode($this->linkSalesOrder());
                break;
            case self::CONST_CALCULATE_BUDGET:
                echo json_encode($this->calculateBudget());
                break;
            case self::CONST_PROJECT_ISSUES:
                switch ($method) {
                    case 'GET':
                        echo json_encode($this->getProjectIssues(), JSON_NUMERIC_CHECK);
                        break;
                    case 'POST':
                        echo json_encode($this->postProjectIssue());
                        break;
                    case 'PUT':
                        echo json_encode($this->updateProjectIssue());
                        break;
                    case 'DELETE':
                        echo json_encode($this->deleteProjectIssue());
                        break;
                }
                break;
            case self::CONST_PROJECT_STAGES:
                echo json_encode($this->getProjectStagesHistory());
                break;
            case self::CONST_PROJECT_ORIGINAL_QUOTOE_DOC:
                $this->getProjectOriginalQuotoeDoc();
                break;
            case self::CONST_PROJECTS_SUMMARY:
                echo json_encode($this->getProjectsSummary(), JSON_NUMERIC_CHECK);
                break;
            case self::CONST_PROJECTS_SEARCH:
                echo json_encode($this->getProjectsSearch(), JSON_NUMERIC_CHECK);
                break;
            case self::CONST_PROJECTS_CONSULTANT_IN_PROGRESS:
                echo json_encode($this->getProjectsByConsultantInProgress(), JSON_NUMERIC_CHECK);
                break;
            case self::CONST_PROJECTS_CUSTOMER_STAGE_FALLS_STARTEND:
                echo json_encode($this->getProjectsByCustomerStageFallsStartEnd(), JSON_NUMERIC_CHECK);
                break;
            case self::CONST_PROJECTS_WITHOUT_CLOUSURE_MEETING:
                echo json_encode($this->getProjectsWithoutClousureMeeting(), JSON_NUMERIC_CHECK);
                break;
            default:
                $this->setTemplate();
        }
    }

    function setTemplate()
    {
        $this->setPageTitle('Projects', '<a href="/Projects.php" style="color:#000080">Projects</a>');
        $this->setTemplateFiles(
            array('Projects' => 'Projects.rct')
        );
        $this->loadReactScript('ProjectsComponent.js');
        $this->loadReactCSS('ProjectsComponent.css');
        $this->template->parse(
            'CONTENTS',
            'Projects',
            true
        );
        $this->parsePage();
    }

    /**
     * @throws Exception
     */
    private function getProjects()
    {
        $this->setMethodName("getProjects");
        $dbeProject      = new DBEProject($this);
        $currentProjects = $dbeProject->getCurrentProjects();
        $data            = [];
        foreach ($currentProjects as $project) {
            $hasProjectPlan           = !!$project['planFileName'];
            $projectPlanDownloadURL   = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                [
                    'action'    => self::DOWNLOAD_PROJECT_PLAN,
                    'projectID' => $project['projectID']
                ]
            );
            $downloadProjectPlanClass = $hasProjectPlan ? '' : 'class="redText"';
            $downloadProjectPlanURL   = $hasProjectPlan ? "href='$projectPlanDownloadURL' target='_blank' " : 'href="#"';
            $projectPlanLink          = "<a id='projectPlanLink' $downloadProjectPlanClass $downloadProjectPlanURL>Project Plan</a>";
            $historyPopupURL          = Controller::buildLink(
                'Project.php',
                array(
                    'action'    => 'historyPopup',
                    'htmlFmt'   => CT_HTML_FMT_POPUP,
                    'projectID' => $project['projectID']
                )
            );
            $inHoursBudget            = "??";
            $inHoursUsed              = "??";
            $outHoursBudget           = "??";
            $outHoursUsed             = "??";
            if ($project['calculatedBudget'] == 'Y') {
                $hoursUsed      = $this->calculateInHoursOutHoursUsed($project['projectID']);
                $inHoursBudget  = $project['inHoursBudgetDays'];
                $inHoursUsed    = $hoursUsed['inHoursUsed'];
                $outHoursBudget = $project['outOfHoursBudgetDays'];
                $outHoursUsed   = $hoursUsed['outHoursUsed'];
            }
            $data[] = [
                "projectID"              => $project['projectID'],
                "description"            => $project['description'],
                "commenceDate"           => $project['commenceDate'],
                'customerName'           => $project['customerName'],
                "projectPlanLink"        => $projectPlanLink,
                "historyPopupURL"        => $historyPopupURL,
                "inHoursBudget"          => $inHoursBudget,
                "inHoursUsed"            => $inHoursUsed,
                "outHoursBudget"         => $outHoursBudget,
                "outHoursUsed"           => $outHoursUsed,
                'assignedEngineer'       => $project['engineerName'],
                'hasProjectPlan'         => !!$project['planFileName'],
                'createdAt'              => $project['createdAt'],
                'createdBy'              => $project['createdBy'],
                'comment'                => $project['comment'],
                'projectStageName'       => $project['projectStageName'],
                'projectTypeName'        => $project['projectTypeName'],
                'expectedHandoverQADate' => $project['expectedHandoverQADate'],
            ];
        }
        return $data;
    }

    private function calculateInHoursOutHoursUsed($projectID)
    {
        $dbeProject = new DBEProject($this);
        $dbeProject->getRow($projectID);
        $buHeader  = new BUHeader($this);
        $dbeHeader = new DataSet($this);
        $buHeader->getHeader($dbeHeader);
        $data = [
            "inHoursUsed"   => 0,
            "outHoursUsed"  => 0,
            "minutesPerDay" => $dbeHeader->getValue(DBEHeader::smallProjectsTeamMinutesInADay)
        ];
        if (!$dbeProject->getValue(DBEProject::ordHeadID)) {
            return $data;
        }
        $salesOrderID         = $dbeProject->getValue(DBEProject::ordHeadID);
        $activities           = $this->usedBudgetData($salesOrderID);
        $chargeableActivities = [4, 8];
        foreach ($activities as $activity) {
            if (!in_array(
                $activity['caa_callacttypeno'],
                $chargeableActivities
            )) {
                continue;
            }
            $data['inHoursUsed']  += $activity['inHours'];
            $data['outHoursUsed'] += $activity['outHours'];
        }
        $data['inHoursUsed']  = round(
            ($data['inHoursUsed'] * 60) / $data['minutesPerDay'],
            2
        );
        $data['outHoursUsed'] = round(
            ($data['outHoursUsed'] * 60) / $data['minutesPerDay'],
            2
        );
        return $data;
    }

    private function usedBudgetData($salesOrderID)
    {
        $startTime = '08:00';
        $endTime   = '18:00';
        // here we get the information about the inHours and outOfHours time used
        $query = "SELECT 
                ROUND(
                    COALESCE(
                        SUM(
                        IF(
                        isBankHoliday (`caa_date`),
                        0,
                        TIME_TO_SEC(
                            IF(
                                caa_endtime < '$startTime',
                            '$startTime',
                            IF(
                                caa_endtime > '$endTime',
                                '$endTime',
                                caa_endtime
                            )
                            )
                        ) - TIME_TO_SEC(
                            IF(
                                caa_starttime >= '$startTime',
                            IF(
                                caa_starttime > '$endTime',
                                '$endTime',
                                caa_starttime
                            ),
                            '$startTime'
                            )
                        )
                        )
                    ) / 3600,
                    0
                    ),
                    2
                ) AS inHours,
                ROUND(
                    COALESCE(
                        SUM(
                        IF(
                        isBankHoliday (`caa_date`),
                        COALESCE(
                            TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime),
                            0
                        ),
                        IF(
                            caa_starttime < '$startTime',
                            COALESCE(
                                TIME_TO_SEC(IF(caa_endtime >  '$startTime',  '$startTime', caa_endtime)) - TIME_TO_SEC(caa_starttime),
                                0
                            ),
                            0
                        ) + IF(
                        caa_endtime > '$endTime',
                            COALESCE(
                                TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(IF(caa_starttime < '$endTime', '$endTime', caa_starttime)),
                                0
                            ),
                            0
                        )
                        )
                    ) / 3600,
                    0
                    ),
                    2
                ) AS outHours,
                callactivity.`caa_callacttypeno`,
                callacttype.`cat_desc`,
                callactivity.`caa_consno`,
                consultant.`firstName`,
                consultant.`lastName` 
                FROM
                callactivity 
                LEFT JOIN problem 
                    ON callactivity.`caa_problemno` = problem.`pro_problemno` 
                LEFT JOIN callacttype 
                    ON callactivity.`caa_callacttypeno` = callacttype.`cat_callacttypeno` 
                LEFT JOIN consultant 
                    ON `callactivity`.`caa_consno` = consultant.`cns_consno` 
                WHERE pro_linked_ordno = $salesOrderID and caa_starttime <> '' and caa_starttime is not null and caa_endtime <> '' and caa_endtime is not null 
                and callactivity.`caa_callacttypeno` <> 51 and callactivity.`caa_callacttypeno` <> 60 and callactivity.`caa_callacttypeno` <> 35 and caa_consno <> 67
                GROUP BY caa_callacttypeno,
                caa_consno";
        global $db;
        $db->query($query);
        $data = [];
        while ($db->next_record(MYSQLI_ASSOC)) {
            $data[] = $db->Record;
        }
        return $data;
    }

    function getProjectHistory($projectID = null, $lastUpdateOnly = false)
    {
        if ($projectID == null) $projectID = $_REQUEST['projectID'];
        if (!isset($projectID)) throw new Exception('Project Id required', 400);
        $query = "select * from projectUpdates where projectID = :projectID order by createdAt desc";
        if (!$lastUpdateOnly) $lastUpdateOnly = $_REQUEST['lastUpdateOnly'] ?? false;
        if ($lastUpdateOnly) $query .= " limit 1";
        $history = DBConnect::fetchAll($query, ['projectID' => $projectID]);
        return $history;
    }

    function getProject()
    {
        $this->setMethodName('getProject');
        if (!isset($_REQUEST['projectID'])) return null;
        $data       = [];
        $projectID  = $_REQUEST['projectID'];
        $lastUpdate = $this->getProjectHistory($projectID, true);
        $buProject  = new BUProject($this);
        $buProject->getProjectByID(
            $projectID,
            $dsProject
        );
        $dbeCustomer = new DBECustomer($this);
        //$dbeCustomer->setPK($dsProject->getValue(DBEProject::customerID));
        $dbeCustomer->getRow($dsProject->getValue(DBEProject::customerID));
        $customerName       = $dbeCustomer->getValue(DBECustomer::name);
        $linkServiceRequest = "";
        if ($dsProject->getValue(DBEProject::ordHeadID)) {
            $buSalesOrder              = new BUSalesOrder($this);
            $linkedServiceRequestCount = $buSalesOrder->countLinkedServiceRequests(
                $dsProject->getValue(DBEProject::ordHeadID)
            );
            if ($linkedServiceRequestCount == 1) {
                $problemID          = $buSalesOrder->getLinkedServiceRequestID(
                    $dsProject->getValue(DBEProject::ordHeadID)
                );
                $urlServiceRequest  = Controller::buildLink(
                    'Activity.php',
                    array(
                        'action'    => 'displayFirstActivity',
                        'problemID' => $problemID
                    )
                );
                $linkServiceRequest = '<a href="' . $urlServiceRequest . '" target="_blank"><div>View SR</div></a>';
            } else {     // many SRs so display search page
                $urlServiceRequest  = Controller::buildLink(
                    'Activity.php',
                    array(
                        'action'             => 'search',
                        'linkedSalesOrderID' => $dsProject->getValue(DBEProject::ordHeadID)
                    )
                );
                $linkServiceRequest = '<a href="' . $urlServiceRequest . '" target="_blank"><div>View SRs</div></a>';

            }
        }
        $hasProjectPlan                  = !!$dsProject->getValue(DBEProject::planFileName);
        $projectCalculateBudgetURL       = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action'    => 'calculateBudget',
                'projectID' => $projectID
            ]
        );
        $projectCalculateBudgetClass     = null;
        $projectCalculateBudgetURL       = "href='$projectCalculateBudgetURL'";
        $projectCalculateBudgetLinkClick = "onclick='return confirm(\"Are you sure? You can only do this once.\")'";
        $isProjectManager                = $this->dbeUser->getValue(DBEUser::projectManagementFlag) === 'Y';
        if ($dsProject->getValue(DBEProject::calculatedBudget) == 'Y' || !$isProjectManager) {
            $projectCalculateBudgetURL       = "href='#'";
            $projectCalculateBudgetClass     = "class='grayedOut'";
            $projectCalculateBudgetLinkClick = null;
        }
        $projectCalculateBudgetLink = null;
        if ($dsProject->getValue(DBEProject::ordHeadID)) {
            $projectCalculateBudgetLink = "<a  $projectCalculateBudgetURL  $projectCalculateBudgetClass $projectCalculateBudgetLinkClick>Calculate Budget</a>";
        }
        $inHoursBudget  = "N/A";
        $inHoursUsed    = "0.00";
        $outHoursBudget = "N/A";
        $outHoursUsed   = "0.00";
        if ($dsProject->getValue(DBEProject::calculatedBudget) == 'Y') {
            $hoursUsed      = $this->calculateInHoursOutHoursUsed($projectID);
            $inHoursBudget  = $dsProject->getValue(DBEProject::inHoursBudgetDays);
            $inHoursUsed    = $hoursUsed['inHoursUsed'];
            $outHoursBudget = $dsProject->getValue(DBEProject::outOfHoursBudgetDays);
            $outHoursUsed   = $hoursUsed['outHoursUsed'];
        }
        $data = [
            'customerID'                       => $dsProject->getValue(DBEProject::customerID),
            'projectID'                        => $projectID,
            'description'                      => $dsProject->getValue(DBEProject::description),
            'notes'                            => $dsProject->getValue(DBEProject::notes),
            'openedDate'                       => $dsProject->getValue(DBEProject::openedDate) ?? '',
            'completedDate'                    => $dsProject->getValue(DBEProject::completedDate) ?? '',
            'commenceDate'                     => $dsProject->getValue(DBEProject::commenceDate) ?? '',
            'ordHeadID'                        => $dsProject->getValue(DBEProject::ordHeadID),
            'hasProjectPlan'                   => $hasProjectPlan,
            'calculateBudgetLink'              => $projectCalculateBudgetLink,
            'projectManagementCheck'           => $isProjectManager ? '' : 'readonly disabled',
            'viewSRLink'                       => $linkServiceRequest,
            'lastUpdate'                       => count($lastUpdate) > 0 ? $lastUpdate[0] : [],
            'projectEngineer'                  => $dsProject->getValue(DBEProject::projectEngineer),
            "inHoursBudget"                    => $inHoursBudget,
            "inHoursUsed"                      => $inHoursUsed,
            "outHoursBudget"                   => $outHoursBudget,
            "outHoursUsed"                     => $outHoursUsed,
            'calculatedBudget'                 => $dsProject->getValue(DBEProject::calculatedBudget) == 'Y',
            'customerName'                     => $customerName,
            'projectManager'                   => $dsProject->getValue(DBEProject::projectManager),
            'projectPlanningDate'              => $dsProject->getValue(DBEProject::projectPlanningDate),
            'expectedHandoverQADate'           => $dsProject->getValue(DBEProject::expectedHandoverQADate),
            'projectTypeID'                    => $dsProject->getValue(DBEProject::projectTypeID),
            'projectStageID'                   => $dsProject->getValue(DBEProject::projectStageID),
            'ordOriginalHeadID'                => $dsProject->getValue(DBEProject::ordOriginalHeadID),
            'originalQuoteDocumentFinalAgreed' => $dsProject->getValue(DBEProject::originalQuoteDocumentFinalAgreed),
        ];
        return $data;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getBudgetData()
    {
        if (!$this->getParam('projectID')) {
            throw new Exception('Project ID is missing');
        }
        $dbeProject = new DBEProject($this);
        $dbeProject->getRow($this->getParam('projectID'));
        $buHeader  = new BUHeader($this);
        $dbeHeader = new DataSet($this);
        $buHeader->getHeader($dbeHeader);
        $data = [
            "salesOrderID"     => (int)$dbeProject->getValue(DBEProject::ordHeadID),
            "calculatedBudget" => $dbeProject->getValue(DBEProject::calculatedBudget) == 'Y',
            "stats"            => [
                "inHoursAllocated" => 'N/A',
                "inHoursUsed"      => 'N/A',
                "ooHoursAllocated" => 'N/A',
                "ooHoursUsed"      => 'N/A',
            ],
            "minutesPerDay"    => $dbeHeader->getValue(DBEHeader::smallProjectsTeamMinutesInADay),
            "data"             => []
        ];
        if (!$dbeProject->getValue(DBEProject::ordHeadID)) {
            return $data;
        }
        $salesOrderID              = $dbeProject->getValue(DBEProject::ordHeadID);
        $data['data']              = $this->usedBudgetData($salesOrderID);
        $buExpense                 = new BUExpense($this);
        $data['stats']['expenses'] = $buExpense->getTotalExpensesForSalesOrder($salesOrderID);
        if ($dbeProject->getValue(DBEProject::calculatedBudget) != 'Y') {
            return $data;
        }
        $data['stats']['inHoursAllocated'] = $dbeProject->getValue(DBEProject::inHoursBudgetDays);
        $data['stats']['ooHoursAllocated'] = $dbeProject->getValue(DBEProject::outOfHoursBudgetDays);
        return $data;
    }

    function updateProject($newProject = false)
    {
        //return ['status'=>true];
        $data        = $this->getBody();
        $projectID   = $data->projectID ?? null;
        $isProManger = $this->dbeUser->getValue(DBEUser::projectManagementFlag) == 'Y';
        if ($isProManger) // update project
        {
            //return ['test'=>true];
            $buHeader  = new BUHeader($this);
            $dbeHeader = new DataSet($this);
            $buHeader->getHeader($dbeHeader);
            $dbeProject = new DBEProject($this);
            if (!$newProject) $dbeProject->getRow($projectID);
            if (!empty($data->inHoursQuantity)) {
                $toAddDays = null;
                // we need to add the amount of hours or days to the in hours budget
                $currentDays = (float)$dbeProject->getValue(DBEProject::inHoursBudgetDays);
                switch ($data->inHoursMeasure) {
                    case 'h':
                        $toAddMinutes = (int)$data->inHoursQuantity * 60;
                        $toAddDays    = $toAddMinutes / $dbeHeader->getValue(DBEHeader::smallProjectsTeamMinutesInADay);
                        break;
                    case 'd':
                        $toAddDays = (float)$data->inHoursQuantity;
                }
                $dbeProject->setValue(
                    DBEProject::inHoursBudgetDays,
                    $currentDays + $toAddDays
                );
            }
            if (!empty($data->outOfHoursQuantity)) {
                $toAddDays = null;
                // we need to add the amount of hours or days to the in hours budget
                $currentDays = (float)$dbeProject->getValue(DBEProject::outOfHoursBudgetDays);
                switch ($data->outOfHoursMeasure) {
                    case'h':
                        $toAddMinutes = (int)$data->outOfHoursQuantity * 60;
                        $toAddDays    = $toAddMinutes / $dbeHeader->getValue(DBEHeader::smallProjectsTeamMinutesInADay);
                        break;
                    case 'd':
                        $toAddDays = (float)$data->outOfHoursQuantity;
                }
                $dbeProject->setValue(
                    DBEProject::outOfHoursBudgetDays,
                    $currentDays + $toAddDays
                );
            }
            $dbeProject->setValue(DBEProject::customerID, $data->customerID);
            $dbeProject->setValue(DBEProject::description, $data->description);
            $dbeProject->setValue(DBEProject::notes, $data->notes);
            $dbeProject->setValue(DBEProject::openedDate, $data->openedDate);
            $dbeProject->setValue(DBEProject::commenceDate, $data->commenceDate);
            $dbeProject->setValue(DBEProject::completedDate, $data->completedDate);
            $dbeProject->setValue(DBEProject::projectEngineer, $data->projectEngineer);
            $dbeProject->setValue(DBEProject::projectManager, $data->projectManager);
            $dbeProject->setValue(DBEProject::projectPlanningDate, $data->projectPlanningDate);
            $dbeProject->setValue(DBEProject::expectedHandoverQADate, $data->expectedHandoverQADate);
            $dbeProject->setValue(DBEProject::projectTypeID, $data->projectTypeID);
            //$dbeProject->setValue(DBEProject::projectStageID,$data->projectStageID);
            $dbeProject->setValue(
                DBEProject::originalQuoteDocumentFinalAgreed,
                $data->originalQuoteDocumentFinalAgreed
            );
            if (!$newProject) $dbeProject->updateRow(); else {
                $dbeProject->insertRow();
                $projectID = $dbeProject->getValue(DBEProject::projectID);
            }
        }
        // check to add new Update
        if (!empty($data->newUpdate)) {
            DBConnect::execute(
                "insert into projectUpdates(createdBy,projectID,comment) 
            values (:createdBy, :projectID, :comment)",
                [
                    "createdBy" => $this->dbeUser->getValue(DBEUser::firstName) . " " . $this->dbeUser->getValue(
                            DBEUser::lastName
                        ),
                    "projectID" => $projectID,
                    "comment"   => $data->newUpdate,
                ]
            );
        }
        return ['status' => true, 'projectID' => $projectID];
    }

    function updateProjectStage()
    {
        $projectID  = @$_REQUEST["projectID"];
        $newStageID = @$_REQUEST["newStageID"];
        $oldStageID = @$_REQUEST["oldStageID"];
        if (empty($projectID) || empty($newStageID) || empty($oldStageID)) throw new Exception('Missing data');
        $dbeProject = new DBEProject($this);
        $dbeProject->getRow($projectID);
        $dbeProject->setValue(DBEProject::projectStageID, $newStageID);
        $dbeProject->updateRow();
        // check if project stage changed
        if ($newStageID != $oldStageID) {
            $todayTime = strtotime(date("Y-m-d H:i:s"));
            // clac origin time diff
            $openDateStr    = $dbeProject->getValue(DBEProject::openedDate);
            $openDateTime   = strtotime($openDateStr);
            $timeDiffOrigin = $todayTime - $openDateTime; // time will be in seconds
            $timeDiffOrigin = $timeDiffOrigin / (60 * 60); //in hours
            if ($oldStageID != 'null') {
                // get the old stage recored and update it's time
                $oldStage = DBConnect::fetchOne(
                    "select id,createAt,stageTimeHours from ProjectStagesHistory where projectID=:projectID and stageID =:stageID",
                    ["projectID" => $projectID, "stageID" => $oldStageID]
                );
                if (!empty($oldStage["id"])) {
                    //update stageTimeHours
                    $createDateTime = strtotime($oldStage["createAt"]);
                    $timeDiff       = ($todayTime - $createDateTime) / (60 * 60); // in hours;
                    $stageTimeHours = floatval($oldStage["stageTimeHours"] ?? 0) + $timeDiff;
                    DBConnect::execute(
                        "update ProjectStagesHistory set stageTimeHours=:stageTimeHours where id=:id",
                        ["id" => $oldStage["id"], "stageTimeHours" => $stageTimeHours]
                    );
                } else { // insert new recored subtract from project open date
                    // insert old recored;
                    DBConnect::execute(
                        "insert into ProjectStagesHistory(projectID,stageID,consID,stageTimeHours)
                                        values(:projectID,:stageID,:consID,:stageTimeHours)",
                        [
                            "projectID"      => $projectID,
                            "stageID"        => $oldStageID,
                            "consID"         => $this->dbeUser->getPKValue(),
                            "stageTimeHours" => $timeDiffOrigin
                        ]
                    );
                }
            }
            $stageTimeHours = null;
            if ($oldStageID == 'null') {
                $stageTimeHours = $timeDiffOrigin;
            }
            // insert the new stage recored         
            DBConnect::execute(
                "insert into ProjectStagesHistory(projectID,stageID,consID,stageTimeHours)
                                values(:projectID,:stageID,:consID,:stageTimeHours)",
                [
                    "projectID"      => $projectID,
                    "stageID"        => $newStageID,
                    "consID"         => $this->dbeUser->getPKValue(),
                    "stageTimeHours" => $stageTimeHours
                ]
            );
            return ["status" => true];
        }
        return ["status" => false];
    }

    function uploadProjectFiles()
    {

        if (!isset($_FILES['files']) || !count($_FILES['files']['name'])) {
            throw new Exception('At least one file must be provided');
        }
        if (!$this->getParam('projectID')) {
            throw new Exception('Project ID is missing');
        }
        //return ["status"=>count($_FILES['files'])];
        $dbeProject = new DBEProject($this);
        $dbeProject->getRow($this->getParam('projectID'));
        $file = $_FILES['files'];
        $dbeProject->setUpdateModeUpdate();
        $dbeProject->setValue(
            DBEProject::planFile,
            file_get_contents($file['tmp_name'])
        );
        $dbeProject->setValue(
            DBEProject::planFileName,
            $file['name']
        );
        $dbeProject->setValue(
            DBEProject::planMIMEType,
            $file['type']
        );
        $dbeProject->updateRow();
        return ["status" => true];
    }

    /**
     * @throws Exception
     */
    private function unlinkSalesOrder()
    {
        $projectID = @$_REQUEST['projectID'];
        if (!$projectID) {
            throw new Exception('Project ID is missing');
        }
        $orignalOrder = @$_REQUEST['orignalOrder'] == 'true' ? true : false;
        $project      = new DBEProject($this);
        $project->getRow($projectID);
        if (!$orignalOrder) $project->setValue(DBEProject::ordHeadID, null); else
            $project->setValue(DBEProject::ordOriginalHeadID, null);
        $project->updateRow();
        return ['status' => true];
    }

    private function linkSalesOrder()
    {
        $projectID     = @$_REQUEST['projectID'];
        $linkedOrderID = @$_REQUEST['ordHeadID'];
        $orignalOrder  = @$_REQUEST['orignalOrder'] == 'true' ? true : false;
        if (!$projectID || !$linkedOrderID) {
            throw new Exception('Project ID is missing');
        }
        $buProject = new BUProject($this);
        try {

            $buProject->updateLinkedSalesOrder(
                $projectID,
                $linkedOrderID,
                $orignalOrder
            );
            return ["status" => true];
        } catch (Exception $exception) {
            return ["status" => false, 'error' => $exception->getMessage()];
        }
    }

    /**
     * @throws Exception
     */
    private function calculateBudget()
    {
        $projectID = @$this->getParam('projectID');
        if (!$projectID) throw new Exception('Project ID is missing');
        $dbeProject = new DBEProject($this);
        $dbeProject->getRow($projectID);
        if (!$dbeProject->getValue(DBEProject::ordHeadID)) throw new Exception(
            'The project does not have a linked Sales Order'
        );
        if ($dbeProject->getValue(DBEProject::calculatedBudget) == 'Y') throw new Exception(
            'The project budget has already been calculated'
        );
        $buSalesOrder = new BUSalesOrder($this);
        $dsOrdHead    = new DataSet($this);
        $dsOrdLine    = new DataSet($this);
        $buSalesOrder->getOrderByOrdheadID(
            $dbeProject->getValue(DBEProject::ordHeadID),
            $dsOrdHead,
            $dsOrdLine
        );
        $BUHeader  = new BUHeader($this);
        $dbeHeader = new DataSet($this);
        $BUHeader->getHeader($dbeHeader);
        $minutesInADay         = $dbeHeader->getValue(DBEHeader::smallProjectsTeamMinutesInADay);
        $normalMinutes         = 0;
        $oohMinutes            = 0;
        $data                  = [];
        $data['minutesInADay'] = $minutesInADay;
        while ($dsOrdLine->fetchNext()) {
            if ($dsOrdLine->getValue(DBEOrdline::lineType) == 'I') {
                $data['sequence'] = $dsOrdLine->getValue(DBEOrdline::sequenceNo);
                $data['itemID']   = $dsOrdLine->getValue(DBEOrdline::itemID);
                switch ($dsOrdLine->getValue(DBEOrdline::itemID)) {
                    case OrderItems::DAILY_LABOUR_CHARGE:
                        $normalMinutes += ((float)$dsOrdLine->getValue(DBEOrdline::qtyOrdered)) * $minutesInADay;
                        break;
                    case OrderItems::HOURLY_LABOUR_CHARGE:
                        $normalMinutes += ((float)$dsOrdLine->getValue(DBEOrdline::qtyOrdered)) * 60;
                        break;
                    case OrderItems::DAILY_OOH_LABOUR_CHARGE:
                        $oohMinutes += ((float)$dsOrdLine->getValue(DBEOrdline::qtyOrdered)) * $minutesInADay;
                        break;
                    case OrderItems::HOURLY_OOH_LABOUR_CHARGE:
                        $oohMinutes += ((float)$dsOrdLine->getValue(DBEOrdline::qtyOrdered)) * 60;
                        break;
                }
                $data['normalMinutes'] = $normalMinutes;
                $data["oohMinutes"]    = $oohMinutes;
                //echo "<div>Normal Minutes: $normalMinutes</div><div>Out Of Hours Minutes: $oohMinutes</div>";
            }

        }
        $dbeProject->setValue(
            DBEProject::inHoursBudgetDays,
            $normalMinutes / $minutesInADay
        );
        $dbeProject->setValue(
            DBEProject::outOfHoursBudgetDays,
            $oohMinutes / $minutesInADay
        );
        $dbeProject->setValue(
            DBEProject::calculatedBudget,
            'Y'
        );
        $dbeProject->updateRow();
        return ["status" => true, "data" => $data];
    }

    function downloadProjectFiles()
    {
        if (!$this->getParam('projectID')) {
            echo 'Project ID missing';
            http_response_code(400);
            exit;
        }
        $dbeDocuments = new DBEProject($this);
        $dbeDocuments->getRow($this->getParam('projectID'));
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $dbeDocuments->getValue(DBEProject::planMIMEType));
        header(
            'Content-Disposition: attachment; filename="' . $dbeDocuments->getValue(
                DBEProject::planFileName
            ) . '"'
        );
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($dbeDocuments->getValue(DBEProject::planFile)));
        echo $dbeDocuments->getValue(DBEProject::planFile);
    }

    function getProjectIssues()
    {
        $projectID = @$_REQUEST["projectID"];
        if (!$projectID) throw new Exception("project id is missing");
        $query  = "SELECT id, `consID`,`projectID`,`issuesRaised`,`cns_name` as engineerName,createAt
                FROM projectIssues p JOIN `consultant` c ON p.consID=c.`cns_consno` 
                where 
                projectID=:projectID
                ";
        $issues = DBConnect::fetchAll($query, ["projectID" => $projectID]);
        return $issues;
    }

    function postProjectIssue()
    {
        $body      = $this->getBody();
        $projectID = @$_REQUEST["projectID"];
        if (!$projectID) throw new Exception("project id is missing");
        $consID           = $this->dbeUser->getPKValue();
        $dbeProjectIssues = new DBEProjectIssues($this);
        $dbeProjectIssues->setValue(DBEProjectIssues::consID, $consID);
        $dbeProjectIssues->setValue(DBEProjectIssues::projectID, $projectID);
        $dbeProjectIssues->setValue(DBEProjectIssues::issuesRaised, $body->issuesRaised);
        $dbeProjectIssues->setValue(DBEProjectIssues::createAt, date("Y-m-d H:i:s"));
        $dbeProjectIssues->insertRow();
        return ["status" => true, 'id' => $dbeProjectIssues->getPKValue()];
    }

    function updateProjectIssue()
    {
        $body = $this->getBody();
        if (!$body->id) throw new Exception("id is missing");
        $dbeProjectIssues = new DBEProjectIssues($this);
        $dbeProjectIssues->getRow($body->id);
        $dbeProjectIssues->setValue(DBEProjectIssues::issuesRaised, $body->issuesRaised);
        $dbeProjectIssues->updateRow();
        return ["status" => true, 'id' => $dbeProjectIssues->getPKValue()];
    }

    function deleteProjectIssue()
    {
        $id = @$_REQUEST["id"];
        if (!$id) throw new Exception("id is missing");
        $dbeProjectIssues = new DBEProjectIssues($this);
        $dbeProjectIssues->getRow($id);
        if ($dbeProjectIssues->getValue(DBEProjectIssues::consID) == $this->dbeUser->getPKValue()) {
            $dbeProjectIssues->deleteRow();
            return ["status" => true];
        } else
            return ["status" => false];
    }

    function getProjectSummary()
    {
        $proejctID = @$_REQUEST["projectID"];
        if ($proejctID) {
            $dbeProject = new DBEProject($this);
            $dbeProject->getRow($proejctID);
            return [
                'engineersSummary'       => $dbeProject->getValue(DBEProject::engineersSummary),
                'projectManagersSummary' => $dbeProject->getValue(DBEProject::projectManagersSummary),
                'projectClosureNotes'    => $dbeProject->getValue(DBEProject::projectClosureNotes),
                'projectClosureDate'     => $dbeProject->getValue(DBEProject::projectClosureDate),
            ];
        }
        return ['status' => false];
    }

    function updateProjectSummary()
    {
        $proejctID = @$_REQUEST["projectID"];
        $body      = $this->getBody();
        if ($proejctID) {
            $dbeProject = new DBEProject($this);
            $dbeProject->getRow($proejctID);
            $dbeProject->setValue(DBEProject::engineersSummary, $body->engineersSummary);
            $dbeProject->setValue(DBEProject::projectManagersSummary, $body->projectManagersSummary);
            $dbeProject->setValue(DBEProject::projectClosureNotes, $body->projectClosureNotes);
            $dbeProject->setValue(DBEProject::projectClosureDate, $body->projectClosureDate);
            $dbeProject->updateRow();
            return ["status" => true];
        }
        return ["status" => false];
    }

    function getProjectStagesHistory()
    {
        $projectID = @$_REQUEST["projectID"];
        if (!$projectID) throw new Exception("Project Id is missing");
        return DBConnect::fetchAll(
            "select p.id, p.projectID,p.stageID,p.consID,p.stageTimeHours,p.createAt ,        
            cons.cns_name engineerName,
            ps.name stageName
            from ProjectStagesHistory p
            join consultant cons on cons.cns_consno=p.consID
            join projectstages ps on ps.id=p.stageID
            where projectID=:projectID",
            ["projectID" => $projectID]
        );
    }

    function getProjectOriginalQuotoeDoc()
    {
        $projectID = @$_REQUEST["projectID"];
        if (!$projectID) throw new Exception("Project Id is missing");
        $dbeProject = new DBEProject($this);
        $dbeProject->getRow($projectID);
        $file = $dbeProject->getValue(DBEProject::originalQuoteDocumentFinalAgreed);
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        }
    }

    function getProjectsSummary()
    {
        $query = "SELECT COUNT(*) total,stage.name
        FROM   `project` 
          LEFT JOIN projectstages stage ON stage.id = project.projectStageID  
        WHERE expiryDate >= NOW() OR expiryDate IS NULL
        GROUP BY projectStageID
        ORDER BY stage.`stageOrder`";
        return DBConnect::fetchAll($query);
    }

    //-------------------------------------------Report API
    function getProjectsSearch()
    {
        $consID         = @$_REQUEST["consID"];
        $dateFrom       = @$_REQUEST["dateFrom"];
        $dateTo         = @$_REQUEST["dateTo"];
        $projectTypeID  = @$_REQUEST["stageID"];
        $projectStageID = @$_REQUEST["typeID"];
        // if(!$consID&&!$dateFrom&&!$dateTo)
        //     throw new Exception("Paramter missing",404);
        $query  = "
        select  
        p.projectID,
        p.customerID,
        p.description,
        p.startDate,
        p.expiryDate,
        p.notes,
        c.cus_name as customerName,
        ps.name as projectStageName,
        pt.name as projectTypeName,
        inHoursBudgetDays,       
        outOfHoursBudgetDays,       
        calculatedBudget,
        concat(engineer.firstName, ' ', engineer.lastName) as engineerName
        from project p
        join customer c on c.cus_custno=p.customerID
        join consultant engineer on p.consultantID = engineer.cns_consno    
        left join projectstages ps on ps.id = p.projectStageID
        left join projecttypes pt on  pt.id = p.projectTypeID
        where 1=1
        
        ";
        $params = [];
        if (!empty($consID)) {
            $query            .= " and consultantID=:consID";
            $params["consID"] = $consID;
        }
        if (!empty($dateFrom)) {
            $query              .= " and p.startDate >=:dateFrom";
            $params["dateFrom"] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $query            .= " and p.startDate <=:dateTo";
            $params["dateTo"] = $dateTo;
        }
        if (!empty($projectStageID)) {
            $query                    .= " and projectStageID =:projectStageID";
            $params["projectStageID"] = $projectStageID;
        }
        if (!empty($projectTypeID)) {
            $query                   .= " and projectTypeID =:projectTypeID";
            $params["projectTypeID"] = $projectTypeID;
        }
        //return    $params;
        $projects = DBConnect::fetchAll($query, $params);
        for ($i = 0; $i < count($projects); $i++) {
            $inHoursBudget  = "??";
            $inHoursUsed    = "??";
            $outHoursBudget = "??";
            $outHoursUsed   = "??";
            if ($projects[$i]['calculatedBudget'] == 'Y') {
                $hoursUsed                      = $this->calculateInHoursOutHoursUsed($projects[$i]['projectID']);
                $inHoursBudget                  = $projects[$i]['inHoursBudgetDays'];
                $inHoursUsed                    = $hoursUsed['inHoursUsed'];
                $outHoursBudget                 = $projects[$i]['outOfHoursBudgetDays'];
                $outHoursUsed                   = $hoursUsed['outHoursUsed'];
                $projects[$i]["inHoursBudget"]  = $inHoursBudget;
                $projects[$i]["inHoursUsed"]    = $inHoursUsed;
                $projects[$i]["outHoursBudget"] = $outHoursBudget;
                $projects[$i]["outHoursUsed"]   = $outHoursUsed;
            }
        }
        return $projects;
    }

    function getProjectsByConsultantInProgress()
    {
        $consID   = @$_REQUEST["consID"];
        $dateFrom = @$_REQUEST["dateFrom"];
        $dateTo   = @$_REQUEST["dateTo"];
        if (!$consID) throw new Exception("Paramter missing", 404);
        $query  = "
        SELECT  
        p.projectID,
        p.customerID,
        p.description,
        p.startDate,
        p.expiryDate,
        p.notes,
        c.cus_name AS customerName,
        ps.name AS projectStageName,
        pt.name AS projectTypeName
        FROM project p
        JOIN customer c ON c.cus_custno=p.customerID
        LEFT JOIN projectstages ps ON ps.id = p.projectStageID
        LEFT JOIN projecttypes pt ON  pt.id = p.projectTypeID
        JOIN `projectstageshistory` psh ON psh.`projectID` = p.projectID
        WHERE 
        `stageID`=3 -- Project in progress
          AND psh.`createAt` BETWEEN  p.`startDate` AND p.`expiryDate`
        ";
        $params = [];
        if (!empty($consID)) {
            $query            .= " and consultantID=:consID";
            $params["consID"] = $consID;
        }
        if (!empty($dateFrom)) {
            $query              .= " and startDate >=:dateFrom";
            $params["dateFrom"] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $query            .= " and startDate <=:dateTo";
            $params["dateTo"] = $dateTo;
        }
        $projects = DBConnect::fetchAll($query, $params);
        return $projects;
    }

    function getProjectsByCustomerStageFallsStartEnd()
    {
        $customerID = @$_REQUEST["customerID"];
        $dateFrom   = @$_REQUEST["dateFrom"];
        $dateTo     = @$_REQUEST["dateTo"];
        if (!$customerID) throw new Exception("Paramter missing", 404);
        $query  = "
        SELECT  
        p.projectID,
        p.customerID,
        p.description,
        p.startDate,
        p.expiryDate,
        p.notes,
        c.cus_name AS customerName,
        ps.name AS projectStageName,
        pt.name AS projectTypeName
        FROM project p
        JOIN customer c ON c.cus_custno=p.customerID
        LEFT JOIN projectstages ps ON ps.id = p.projectStageID
        LEFT JOIN projecttypes pt ON  pt.id = p.projectTypeID        
        WHERE 
        1=1
          
        ";
        $params = [];
        if (!empty($customerID)) {
            $query                .= " and customerID=:customerID";
            $params["customerID"] = $customerID;
        }
        $query .= " and EXISTS(select * from projectstageshistory psh where psh.`projectID` = p.projectID and psh.`createAt` BETWEEN  p.`startDate` AND p.`expiryDate`)";
        if (!empty($dateFrom)) {
            $query              .= " and startDate >=:dateFrom";
            $params["dateFrom"] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $query            .= " and startDate <=:dateTo";
            $params["dateTo"] = $dateTo;
        }
        $projects = DBConnect::fetchAll($query, $params);
        return $projects;
    }

    function getProjectsWithoutClousureMeeting()
    {
        $consID   = @$_REQUEST["consID"];
        $dateFrom = @$_REQUEST["dateFrom"];
        $dateTo   = @$_REQUEST["dateTo"];
        $query    = "
        SELECT  
        p.projectID,
        p.customerID,
        p.description,
        p.commenceDate,
        p.expiryDate,
        p.notes,
        c.cus_name AS customerName,
        ps.name AS projectStageName,
        pt.name AS projectTypeName
        FROM project p
        JOIN customer c ON c.cus_custno=p.customerID
        LEFT JOIN projectstages ps ON ps.id = p.projectStageID
        LEFT JOIN projecttypes pt ON  pt.id = p.projectTypeID        
        WHERE 
        projectClosureDate is null
          
        ";
        $params   = [];
        if (!empty($consID)) {
            $query            .= " and consultantID=:consID";
            $params["consID"] = $consID;
        }
        if (!empty($dateFrom)) {
            $query              .= " and startDate >=:dateFrom";
            $params["dateFrom"] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $query            .= " and startDate <=:dateTo";
            $params["dateTo"] = $dateTo;
        }
        $query    .= " order by customerName";
        $projects = DBConnect::fetchAll($query, $params);
        return $projects;
    }
}
