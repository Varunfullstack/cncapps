<?php
global $cfg;
require_once($cfg['path_ct'] . '/CTCurrentActivityReport.inc.php');
require_once($cfg['path_bu'] . '/BUSecondSite.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg["path_dbe"] . "/DBConnect.php");

class CTSDManagerDashboard extends CTCurrentActivityReport
{
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

        $this->setMenuId(201);

    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case 'allocateUser':
                $options = [];

                if ($this->getSessionParam('HD')) {
                    $options['HD'] = true;
                }
                if ($this->getSessionParam('ES')) {
                    $options['ES'] = true;
                }
                if ($this->getSessionParam('SP')) {
                    $options['SP'] = true;
                }
                if ($this->getSessionParam('P')) {
                    $options['P'] = true;
                }
                if ($this->getSessionParam('showP5')) {
                    $options['showP5'] = true;
                }

                $this->allocateUser($options);
                break;

            case "getQueue":
                echo json_encode($this->getQueue());
                exit;
            case "dailyStatsSummary":
                echo json_encode($this->getDailyStatsSummary());
                exit;
            case "react":
            default:
                $this->setTemplate();
                break;
        }
    }

    function getQueue()
    {
        $queue = $_REQUEST["queue"];
        if (!isset($queue))
            return [];
        $buProblem = new BUActivity($this);
        $problems = new DataSet($this);
        $isP5 = $_REQUEST["p5"] == "true";
        $showHelpDesk = $_REQUEST["hd"] == "true";
        $showEscalation = $_REQUEST["es"] == "true";
        $showSmallProjects = $_REQUEST["sp"] == "true";
        $showProjects = $_REQUEST["p"] == "true";
        $limit = $_REQUEST["limit"] ?? 10;
        $code = 'shortestSLARemaining';
        if ($queue == 9) {
            return $this->renderOpenSRByCustomerJson(
                $showHelpDesk,
                $showEscalation,
                $showSmallProjects,
                $showProjects,
                $limit
            );
        }
        switch ($queue) {
            case 1: //Shortest SLA Remaining
                $code = 'shortestSLARemaining';
                break;
            case 2: //Current Open P1 Requests
                $code = 'currentOpenP1Requests';
                break;
            case 3: //Current Open P1 Requests
                $code = 'shortestSLAFixRemaining';
                break;
            case 4: //Critical Service Requests
                $code = 'critical';
                break;
            case 5: //Current Open SRs
                $code = 'currentOpenSRs';
                break;
            case 6: //Oldest Updated SRs
                $code = 'oldestUpdatedSR';
                break;
            case 7: //Longest Open SR
                $code = 'longestOpenSR';
                break;
            case 8: //Most Hours Logged
                $code = 'mostHoursLogged';
                break;
        }
        $buProblem->getSDDashBoardData(
            $problems,
            $limit,
            $code,
            $isP5,
            $showHelpDesk,
            $showEscalation,
            $showSmallProjects,
            $showProjects
        );
        return $this->renderQueueJson($problems);
    }

    /**
     * @param bool $showHelpDesk
     * @param bool $showEscalation
     * @param bool $showSmallProjects
     * @param bool $showProjects
     * @param int $limit
     * @return array
     */
    private function renderOpenSRByCustomerJson($showHelpDesk = true,
                                                $showEscalation = true,
                                                $showSmallProjects = true,
                                                $showProjects = true,
                                                $limit = 10
    )
    {
        global $db;
        $query = 'SELECT 
              cus_custno,
              cus_name,
              (SELECT 
                COUNT(pro_problemno) 
              FROM
                problem 
              WHERE problem.`pro_custno` = customer.`cus_custno` 
                AND problem.`pro_status` IN ("I", "P") ';
        if (!$showHelpDesk) {
            $query .= ' and pro_queue_no <> 1 ';
        }

        if (!$showEscalation) {
            $query .= ' and pro_queue_no <> 2 ';
        }

        if (!$showSmallProjects) {
            $query .= ' and pro_queue_no <> 3 ';
        }

        if (!$showProjects) {
            $query .= ' and pro_queue_no <> 5 ';
        }

        $query .= " ) openSRCount 
            FROM
              customer WHERE cus_custno <> 282 ORDER BY openSRCount DESC LIMIT $limit";

        /** @var mysqli_result $result */
        $result = $db->query($query);
        $problems = [];
        while ($row = $result->fetch_assoc()) {
            array_push(
                $problems,
                array(
                    'customerName' => $row['cus_name'],
                    //'srCount'      => "<A href='CurrentActivityReport.php?action=setFilter&selectedCustomerID=" . $row['cus_custno'] . "'>" . $row["openSRCount"] . "</A>"
                    'srCount'      => $row["openSRCount"],
                    "customerID"   => $row["cus_custno"],
                )
            );
        }
        return $problems;
    }

    /**
     * @param DataSet $problems
     * @return mixed|void|null
     * @throws Exception
     */
    private function renderQueueJson(DataSet $problems)
    {
        $rowCount = 0;
        $result = [];
        if (!$problems->rowCount()) {
            return [];
        }

        while ($problems->fetchNext()) {
            $rowCount++;
            $buActivity = new BUActivity($this);
            $activityCount = $buActivity->getActivityCount($problems->getValue(DBEJProblem::problemID));

            $alarmDateTimeDisplay = null;
            if ($problems->getValue(DBEProblem::alarmDate)) {
                $alarmDateTimeDisplay = Controller::dateYMDtoDMY(
                        $problems->getValue(DBEJProblem::alarmDate)
                    ) . ' ' . $problems->getValue(DBEJProblem::alarmTime);
            }

            $totalActivityDurationHours = $problems->getValue(DBEJProblem::totalActivityDurationHours);
            array_push(
                $result,
                array(
                    'hoursRemaining'             => $problems->getValue(DBEJProblem::hoursRemaining),
                    'isBeingWorkedOn'            => $this->isRequestBeingWorkedOn($problems),
                    'status'                     => $problems->getValue(DBEProblem::status),
                    'isSLABreached'              => $this->getIsSLABreached($problems),
                    'totalActivityDurationHours' => $totalActivityDurationHours,
                    'time'                       => $problems->getValue(DBEJProblem::lastStartTime),
                    'date'                       => Controller::dateYMDtoDMY(
                        $problems->getValue(DBEJProblem::lastDate)
                    ),
                    'dateTime'                   => Controller::dateYMDtoDMY(
                            $problems->getValue(DBEJProblem::lastDate)
                        ) . ' ' . $problems->getValue(DBEJProblem::lastStartTime),

                    'problemID'                => $problems->getValue(DBEJProblem::problemID),
                    'reason'                   => self::truncate($problems->getValue(DBEJProblem::reason), 150),
                    'urlProblemHistoryPopup'   => $this->getProblemHistoryLink(
                        $problems->getValue(DBEJProblem::problemID)
                    ),
                    'engineerDropDown'         => $this->getAllocatedUserDropdown(
                        $problems->getValue(DBEJProblem::problemID),
                        $problems->getValue(DBEJProblem::userID)
                    ),
                    'engineerName'             => $problems->getValue(DBEJProblem::engineerName),
                    'customerID'               => $problems->getValue(DBEJProblem::customerID),
                    'customerName'             => $problems->getValue(DBEJProblem::customerName),
                    'specialAttentionCustomer' =>
                        (bool)$this->getCustomerNameDisplayClass(
                            $problems->getValue(DBEJProblem::specialAttentionFlag),
                            $problems->getValue(DBEJProblem::specialAttentionEndDate),
                            $problems->getValue(DBEJProblem::specialAttentionContactFlag)
                        ),
                    'slaResponseHours'         => number_format(
                        $problems->getValue(DBEJProblem::slaResponseHours),
                        1
                    ),
                    'priority'                 => $problems->getValue(DBEJProblem::priority),
                    'alarmDateTime'            => $alarmDateTimeDisplay,
                    'activityCount'            => $activityCount,
                    'teamID'                   => $problems->getValue(DBEJProblem::teamID),
                    "engineerId"               => $problems->getValue(DBEJProblem::userID),
                    "workHidden"               => $problems->getValue(DBECustomer::referredFlag) == 'Y',
                    "lastCallActTypeID"        => $problems->getValue(DBEJProblem::lastCallActTypeID),
                    "callActivityID"           => $problems->getValue(DBEJProblem::callActivityID)
                )
            );


        }
        return $result;
    }


    function getDailyStatsSummary()
    {
        $openSrTeamSummary = $this->getNumberOfOpenServiceRequestPerTeamExcludingSales();
        $dailySourceSummary = $this->getDailySource();
        $prioritySummary = $this->getPrioritySummary();
        $raisedTodaySummary = $this->getRaisedToday();
        $fixedTodaySummary = $this->getFixedToday();
        $nearSLASummary = $this->getNearSLA();
        $reopenTodaySummary = $this->getReopenToday();
        $raisedStartTodaySummary = $this->getRaisedAndStartedToday();
        $uniqueCustomerTodaySummary = $this->getUniqueCustomer();
        $breachedSLATodaySummary = $this->getBreachedSLA();

        return [
            "prioritySummary"            => $prioritySummary,
            "openSrTeamSummary"          => $openSrTeamSummary,
            "dailySourceSummary"         => $dailySourceSummary,
            "raisedTodaySummary"         => $raisedTodaySummary,
            "fixedTodaySummary"          => $fixedTodaySummary,
            "nearSLASummary"             => $nearSLASummary,
            "reopenTodaySummary"         => $reopenTodaySummary,
            "raisedStartTodaySummary"    => $raisedStartTodaySummary,
            'breachedSLATodaySummary'    => $breachedSLATodaySummary,
            'uniqueCustomerTodaySummary' => $uniqueCustomerTodaySummary
        ];
    }

    /**
     * @return array
     */
    private function getNumberOfOpenServiceRequestPerTeamExcludingSales(): array
    {
        $query = "SELECT c.`teamID`, COUNT(*) total
                FROM problem p JOIN consultant c ON p.`pro_consno`=c.`cns_consno` 
                WHERE 
                pro_status<>'C' 
                AND pro_status<>'F'
                AND c.teamID<>7 
                AND pro_custno <> 282
                GROUP BY c.`teamID`";
        return DBConnect::fetchAll($query, []);
    }

    /**
     * @param bool|string $today
     * @return array
     */
    private function getDailySource()
    {
        $query = "SELECT r.`description`,COUNT(*)  total
                FROM problem p LEFT JOIN `problemraisetype` r ON p.`raiseTypeId`=r.`id`
                WHERE    
                pro_custno <> 282
                AND pro_date_raised >=  CURDATE() AND pro_date_raised < CURDATE() + INTERVAL 1 DAY  
                GROUP BY raiseTypeId";
        return DBConnect::fetchAll($query, []);
    }

    /**
     * @return array
     */
    private function getPrioritySummary(): array
    {
        $query = "SELECT pro_priority priority, COUNT(pro_priority) total FROM problem 
                WHERE 
                `pro_status`<>'C'  
                AND `pro_status`<>'F'
                AND pro_priority <> 5
                AND pro_custno <> 282
                GROUP BY pro_priority";
        return DBConnect::fetchAll($query, []);
    }

    /**
     * @return array
     */
    private function getRaisedToday(): array
    {
        $query = "SELECT COUNT(DISTINCT  p.pro_problemno) total FROM `callactivity` c JOIN   problem p ON c.`caa_problemno`=p.`pro_problemno`
                WHERE 
                pro_custno <> 282   
                AND  caa_consno <> 67
                AND caa_callacttypeno NOT IN (60, 35)
                AND pro_status IN ('F')
                AND pro_date_raised >=  CURDATE() AND pro_date_raised < CURDATE() + INTERVAL 1 DAY";
        $raisedTodaySummary = DBConnect::fetchOne($query, []);
        return array($query, $raisedTodaySummary);
    }

    /**
     * @return array
     */
    private function getFixedToday(): array
    {
        $query = "SELECT COUNT(DISTINCT  p.pro_problemno)  total
                FROM `callactivity` c JOIN   problem p ON c.`caa_problemno`=p.`pro_problemno`
                WHERE    
                pro_custno <> 282
                AND pro_consno <> 67
                AND pro_status <> 'I'
                AND c.`caa_callacttypeno`=57
                AND pro_date_raised >=  CURDATE() AND pro_date_raised < CURDATE() + INTERVAL 1 DAY";
        return DBConnect::fetchOne($query, []);

    }

    /**
     * @return array
     */
    private function getNearSLA(): array
    {
        $query = "SELECT COUNT(*) total FROM problem 
                WHERE    
                pro_custno <> 282
                AND  pro_status IN ( 'I', 'P' )
                AND  pro_alarm_date <= curdate() and (pro_alarm_time is null or pro_alarm_time  < time(NOW()))";
        $nearSLASummary = DBConnect::fetchOne($query, []);
        return array($query, $nearSLASummary);
    }

    /**
     * @return array
     */
    private function getReopenToday(): array
    {
        $query = "SELECT COUNT(*) total FROM problem 
                WHERE 
                pro_custno <> 282   
                AND `pro_reopened_date` = curdate()";
        return DBConnect::fetchOne($query, []);
    }

    /**
     * @return array
     */
    private function getRaisedAndStartedToday(): array
    {
        $query = "SELECT COUNT(DISTINCT  p.pro_problemno) total FROM `callactivity` c JOIN   problem p ON c.`caa_problemno`=p.`pro_problemno`
         WHERE 
         pro_custno <> 282   
         AND  caa_consno <> 67
         AND caa_callacttypeno NOT IN (60, 35)
         AND pro_status IN ('P')
         AND pro_date_raised >=  CURDATE() AND pro_date_raised < CURDATE() + INTERVAL 1 DAY";
        return DBConnect::fetchOne($query, []);
    }

    /**
     * @return array
     */
    private function getUniqueCustomer(): array
    {
        $query = "SELECT COUNT(DISTINCT  p.pro_custno) total FROM `callactivity` c JOIN   problem p ON c.`caa_problemno`=p.`pro_problemno`
        WHERE 
        pro_custno <> 282   
        AND  caa_consno <> 67
        AND caa_callacttypeno NOT IN (60, 35)
        AND pro_status IN ('F')
        AND pro_date_raised >=  CURDATE() AND pro_date_raised < CURDATE() + INTERVAL 1 DAY";
        return DBConnect::fetchOne($query, []);
    }

    /**
     * @return mixed
     */
    private function getBreachedSLA(): array
    {
//9- Breached SLA
        $query = "SELECT COUNT(  DISTINCT  p.pro_problemno) total FROM `callactivity` c JOIN   problem p ON c.`caa_problemno`=p.`pro_problemno` 
         JOIN customer cu ON p.`pro_custno`=cu.`cus_custno`
         WHERE
          pro_custno <> 282   
            AND pro_priority <> 5
            AND pro_working_hours > CASE pro_priority
                                   WHEN 1 THEN slaFixHoursP1
                                   WHEN 2 THEN slaFixHoursP2
                                   WHEN 3 THEN slaFixHoursP3
                                   WHEN 4 THEN slaFixHoursP4
                                   ELSE 0 END
         AND pro_date_raised >=  CURDATE() AND pro_date_raised < CURDATE() + INTERVAL 1 DAY";
        return DBConnect::fetchOne($query, []);
    }

    function setTemplate()
    {
        $isP5 = isset($_REQUEST['showP5']);
        $this->setPageTitle('SD Manager Dashboard');
        $this->setTemplateFiles(
            array('SDManagerDashboard' => 'SDManagerDashboard.rct')
        );
        $this->loadReactScript('SDManagerDashboardComponent.js');
        $this->loadReactCSS('SDManagerDashboardComponent.css');
        $this->template->parse(
            'CONTENTS',
            'SDManagerDashboard',
            true
        );
        $this->parsePage();
    }

    function humanize($string)
    {
        return str_replace(
            '_',
            ' ',
            $string
        );
    }
}
