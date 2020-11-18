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

            $bgColour = $this->getResponseColour(
                $problems->getValue(DBEJProblem::status),
                $problems->getValue(DBEJProblem::priority),
                $problems->getValue(DBEJProblem::slaResponseHours),
                $problems->getValue(DBEJProblem::workingHours),
                $problems->getValue(DBEJProblem::respondedHours)
            );
            /*
            Updated by another user?
            */
            if (
                $problems->getValue(DBEJProblem::userID) &&
                $problems->getValue(DBEJProblem::userID) != $problems->getValue(DBEJProblem::lastUserID)
            ) {
                $updatedBgColor = self::PURPLE;
            } else {
                $updatedBgColor = self::CONTENT;
            }

            if ($problems->getValue(DBEJProblem::respondedHours) == 0 && $problems->getValue(
                    DBEJProblem::status
                ) == 'I') {
                /*
                Initial SRs that have not yet been responded to
                */
                $hoursRemainingBgColor = self::AMBER;
            } elseif ($problems->getValue(DBEJProblem::awaitingCustomerResponseFlag) == 'Y') {
                $hoursRemainingBgColor = self::GREEN;
            } else {
                $hoursRemainingBgColor = self::BLUE;
            }

            $alarmDateTimeDisplay = null;
            if ($problems->getValue(DBEProblem::alarmDate)) {

                $alarmDateTimeDisplay = Controller::dateYMDtoDMY(
                        $problems->getValue(DBEJProblem::alarmDate)
                    ) . ' ' . $problems->getValue(DBEJProblem::alarmTime);

                /*
                Has an alarm date that is in the past, set updated BG Colour (indicates moved back into work queue from future queue)
                */
                if ($problems->getValue(DBEJProblem::alarmDate) <= date(DATE_MYSQL_DATE)) {
                    $updatedBgColor = self::PURPLE;
                }

            }
            /*
            If the dashboard is filtered by customer then the Work button opens
            Activity edit
            */
            if (
                $problems->getValue(DBEJProblem::lastCallActTypeID) == 0
            ) {
                $workBgColor = self::GREEN; // green = in progress
            } else {
                $workBgColor = self::CONTENT;
            }

            if ($problems->getValue(DBEJProblem::priority) == 1) {
                $priorityBgColor = self::ORANGE;
            } else {
                $priorityBgColor = self::CONTENT;
            }


            $problemID = $problems->getValue(DBEJProblem::problemID);
            $dbeProblem = new DBEProblem($this);
            $dbeProblem->setValue(
                DBEProblem::problemID,
                $problemID
            );
            $dbeProblem->getRow();
            $hoursRemainingBgColor="";
            if ($problems->getValue(DBEJProblem::respondedHours) == 0 && $problems->getValue(
                DBEJProblem::status
            ) == 'I') {
            /*
            Initial SRs that have not yet been responded to
            */
            $hoursRemainingBgColor = self::AMBER;
            } elseif ($problems->getValue(DBEJProblem::awaitingCustomerResponseFlag) == 'Y') {
                $hoursRemainingBgColor = self::GREEN;
            } else {
                $hoursRemainingBgColor = self::BLUE;
            }

            $totalActivityDurationHours = $problems->getValue(DBEJProblem::totalActivityDurationHours);
            array_push(
                $result,
                array(
                    'hoursRemaining'             => number_format(
                        $problems->getValue(DBEJProblem::hoursRemaining),
                        1
                    ),
                    'updatedBgColor'             => $updatedBgColor,
                    'priorityBgColor'            => $priorityBgColor,
                    'hoursRemainingBgColor'      => $hoursRemainingBgColor,
                    'totalActivityDurationHours' => $totalActivityDurationHours,
                    'time'                       => $problems->getValue(DBEJProblem::lastStartTime),
                    'date'                       => Controller::dateYMDtoDMY(
                        $problems->getValue(DBEJProblem::lastDate)
                    ),
                    'dateTime'                   => Controller::dateYMDtoDMY(
                            $problems->getValue(DBEJProblem::lastDate)
                        ) . ' ' . $problems->getValue(DBEJProblem::lastStartTime),

                    'problemID'              => $problems->getValue(DBEJProblem::problemID),
                    'reason'                 => self::truncate(
                        $problems->getValue(DBEJProblem::reason),
                        150
                    ),
                    'urlProblemHistoryPopup' => $this->getProblemHistoryLink(
                        $problems->getValue(DBEJProblem::problemID)
                    ),
                    'engineerDropDown'       => $this->getAllocatedUserDropdown(
                        $problems->getValue(DBEJProblem::problemID),
                        $problems->getValue(DBEJProblem::userID)
                    ),
                    'engineerName'           => $problems->getValue(DBEJProblem::engineerName),
                    'customerID'             => $problems->getValue(DBEJProblem::customerID),
                    'customerName'           => $problems->getValue(DBEJProblem::customerName),
                    'customerNameDisplayClass'
                                             => $this->getCustomerNameDisplayClass(
                        $problems->getValue(DBEJProblem::specialAttentionFlag),
                        $problems->getValue(DBEJProblem::specialAttentionEndDate),
                        $problems->getValue(DBEJProblem::specialAttentionContactFlag)
                    ),
                    'slaResponseHours'       => number_format(
                        $problems->getValue(DBEJProblem::slaResponseHours),
                        1
                    ),
                    'priority'               => Controller::htmlDisplayText(
                        $problems->getValue(DBEJProblem::priority)
                    ),
                    'alarmDateTime'          => $alarmDateTimeDisplay,
                    'bgColour'               => $bgColour,
                    'workBgColor'            => $workBgColor,
                    'activityCount'          => $activityCount,
                    'teamID'                 => $problems->getValue(DBEJProblem::teamID),
                    "engineerId"             => $problems->getValue(DBEJProblem::userID),
                    "hoursRemainingBgColor"  =>$hoursRemainingBgColor,
                    "workHidden"             => $problems->getValue(DBECustomer::referredFlag) == 'Y',
                    'customerNameDisplayClass'   => $this->getCustomerNameDisplayClass(
                        $problems->getValue(DBEJProblem::specialAttentionFlag),
                        $problems->getValue(DBEJProblem::specialAttentionEndDate),
                        $problems->getValue(DBEJProblem::specialAttentionContactFlag)
                    ),
                    "lastCallActTypeID"      =>$problems->getValue(DBEJProblem::lastCallActTypeID),
                    "callActivityID"         =>$problems->getValue(DBEJProblem::callActivityID)
                )
            );


        } // end while
        return $result;
    } // end render queue

    function getDailyStatsSummary()
    {
        $today = date("Y-m-d");

        //1-  -- get priority summary
        $query = "SELECT pro_priority priority, COUNT(pro_priority) total FROM problem 
                WHERE 
                `pro_status`<>'C'  
                AND `pro_status`<>'F'
                AND pro_priority <> 5
                AND pro_custno <> 282
                GROUP BY pro_priority";
        $prioritySummary = DBConnect::fetchAll($query, []);

        //2-  -- number of open sr foreach team exclude sales
        $query = "SELECT c.`teamID`, COUNT(*) total
                FROM problem p JOIN consultant c ON p.`pro_consno`=c.`cns_consno` 
                WHERE 
                pro_status<>'C' 
                AND pro_status<>'F'
                AND c.teamID<>7 
                AND pro_custno <> 282
                GROUP BY c.`teamID`";
        $openSrTeamSummary = DBConnect::fetchAll($query, []);

        //3-  -- daily source
        $query = "SELECT r.`description`,COUNT(*)  total
                FROM problem p LEFT JOIN `problemraisetype` r ON p.`raiseTypeId`=r.`id`
                WHERE    
                pro_custno <> 282
                AND DATE_FORMAT(`pro_date_raised`,'%Y-%m-%d') = '$today'  
                GROUP BY raiseTypeId";
        $dailySourceSummary = DBConnect::fetchAll($query, []);

        //4- raised today
        $query = "SELECT COUNT(DISTINCT  p.pro_problemno) total FROM `callactivity` c JOIN   problem p ON c.`caa_problemno`=p.`pro_problemno`
                WHERE 
                pro_custno <> 282   
                AND  caa_consno <> 67
                AND caa_callacttypeno NOT IN (60, 35)
                AND pro_status IN ('F')
                AND DATE_FORMAT(`pro_date_raised`,'%Y-%m-%d') = '$today'";
        $raisedTodaySummary = DBConnect::fetchOne($query, []);

        //5 Fixed Today
        $query = "SELECT COUNT(DISTINCT  p.pro_problemno)  total
                FROM `callactivity` c JOIN   problem p ON c.`caa_problemno`=p.`pro_problemno`
                WHERE    
                pro_custno <> 282
                AND pro_consno <> 67
                AND pro_status <> 'I'
                AND c.`caa_callacttypeno`=57
                AND DATE_FORMAT(`pro_date_raised`,'%Y-%m-%d') = '$today'";
        $fixedTodaySummary = DBConnect::fetchOne($query, []);

        //6 Near SLA
        $query = "SELECT COUNT(*) total FROM problem 
                WHERE    
                pro_custno <> 282
                AND  pro_status IN ( 'I', 'P' )
                AND CONCAT( pro_alarm_date, ' ', COALESCE(pro_alarm_time, '00:00:00') )  < NOW()";
        $nearSLASummary = DBConnect::fetchOne($query, []);

        //7  reopen today
        $query = "SELECT COUNT(*) total FROM problem 
                WHERE 
                pro_custno <> 282   
                AND DATE_FORMAT(`pro_reopened_date`,'%Y-%m-%d') = '$today'";
        $reopenTodaySummary = DBConnect::fetchOne($query, []);

        //8- Raised & started Today
        $query = "SELECT COUNT(DISTINCT  p.pro_problemno) total FROM `callactivity` c JOIN   problem p ON c.`caa_problemno`=p.`pro_problemno`
         WHERE 
         pro_custno <> 282   
         AND  caa_consno <> 67
         AND caa_callacttypeno NOT IN (60, 35)
         AND pro_status IN ('P')
         AND DATE_FORMAT(`pro_date_raised`,'%Y-%m-%d') = '$today'";
        $raisedStartTodaySummary = DBConnect::fetchOne($query, []);

        //9- unique Customer
        $query = "SELECT COUNT(DISTINCT  p.pro_custno) total FROM `callactivity` c JOIN   problem p ON c.`caa_problemno`=p.`pro_problemno`
        WHERE 
        pro_custno <> 282   
        AND  caa_consno <> 67
        AND caa_callacttypeno NOT IN (60, 35)
        AND pro_status IN ('F')
        AND DATE_FORMAT(`pro_date_raised`,'%Y-%m-%d') = '$today'";
        $uniqueCustomerTodaySummary = DBConnect::fetchOne($query, []);

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
         AND DATE_FORMAT(`pro_date_raised`,'%Y-%m-%d') = '$today'";
        $breachedSLATodaySummary = DBConnect::fetchOne($query, []);

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
