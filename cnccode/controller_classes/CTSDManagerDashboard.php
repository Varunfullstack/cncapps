<?php
global $cfg;

use CNCLTD\SDManagerDashboard\ServiceRequestSummaryDTO;

require_once($cfg['path_ct'] . '/CTCurrentActivityReport.inc.php');
require_once($cfg['path_bu'] . '/BUSecondSite.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg["path_dbe"] . "/DBConnect.php");

class CTSDManagerDashboard extends CTCurrentActivityReport
{
    const DAILY_STATS_SUMMARY           = "dailyStatsSummary";
    const CONST_MISSED_CALL_BACK        = "missedCallBack";
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
        $action = @$_REQUEST['action'];
        if ($action != self::DAILY_STATS_SUMMARY && !self::isSdManager() && !self::isSRQueueManager()) {
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
            case self::DAILY_STATS_SUMMARY:
                echo json_encode($this->getDailyStatsSummary(), JSON_NUMERIC_CHECK);
                exit;
            case self::CONST_MISSED_CALL_BACK:
                echo json_encode($this->missedCallBack(), JSON_NUMERIC_CHECK);
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
        if (!isset($queue)) return [];
        $buProblem         = new BUActivity($this);
        $isP5              = $_REQUEST["p5"] == "true";
        $showHelpDesk      = $_REQUEST["hd"] == "true";
        $showEscalation    = $_REQUEST["es"] == "true";
        $showSmallProjects = $_REQUEST["sp"] == "true";
        $showProjects      = $_REQUEST["p"] == "true";
        $limit             = $_REQUEST["limit"] ?? 10;
        $code              = 'shortestSLARemaining';
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
            case 11: //Held for QA
                $code = 'holdForQA';
                break;
        }
        return $this->renderQueueJson(
            $buProblem->getSDDashBoardData(
                $limit,
                $code,
                $isP5,
                $showHelpDesk,
                $showEscalation,
                $showSmallProjects,
                $showProjects
            )
        );
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
        $result   = $db->query($query);
        $problems = [];
        while ($row = $result->fetch_assoc()) {
            array_push(
                $problems,
                array(
                    'customerName' => $row['cus_name'],
                    'srCount'      => $row["openSRCount"],
                    "customerID"   => $row["cus_custno"],
                )
            );
        }
        return $problems;
    }

    /**
     * @param DBEJProblem $problems
     * @return mixed|void|null
     * @throws Exception
     */
    private function renderQueueJson(DBEJProblem $problems)
    {
        $result = [];
        if (!$problems->rowCount()) {
            return $result;
        }
        while ($problems->fetchNext()) {
            $result[] = ServiceRequestSummaryDTO::fromDBEJProblem($problems, true);
        }
        return $result;
    }

    function getDailyStatsSummary()
    {
        $openSrTeamSummary          = $this->getNumberOfOpenServiceRequestPerTeamExcludingSales();
        $dailySourceSummary         = $this->getDailySource();
        $prioritySummary            = $this->getPrioritySummary();
        $raisedTodaySummary         = $this->getRaisedToday();
        $fixedTodaySummary          = $this->getFixedToday();
        $nearSLASummary             = $this->getNearSLA();
        $reopenTodaySummary         = $this->getReopenToday();
        $raisedStartTodaySummary    = $this->getRaisedAndStartedToday();
        $uniqueCustomerTodaySummary = $this->getUniqueCustomer();
        $breachedSLATodaySummary    = $this->getBreachedSLA();
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
        $query = "SELECT
  c.`teamID`,
  COUNT(*) total
FROM
  problem p
  JOIN team c
    ON c.level = p.pro_queue_no
WHERE pro_status NOT IN ('C', 'F')
  AND c.teamID < 6
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
        $query = "SELECT
  COUNT(distinct pro_problemno) as total
FROM
  `callactivity` c
  JOIN problem p
    ON c.`caa_problemno` = p.`pro_problemno`
WHERE pro_custno <> 282
    AND caa_callacttypeno = 51
  AND pro_date_raised >= CURDATE()
  AND pro_date_raised < CURDATE() + INTERVAL 1 DAY";
        return DBConnect::fetchOne($query, []);
    }

    /**
     * @return array
     */
    private function getFixedToday(): array
    {
        $query  = "SELECT
  COUNT(DISTINCT p.`pro_problemno`) AS total
FROM
  callactivity c
  JOIN problem p
    ON c.caa_problemno = p.pro_problemno
WHERE pro_custno <> 282
  AND c.`caa_consno` <> 67
  AND c.caa_callacttypeno = 57
  AND c.caa_date = CURDATE()
  AND pro_status = 'F'";
        $result = DBConnect::fetchOne($query, []);
        if (!$result) {
            return ["total" => 0];
        }
        return $result;
    }

    /**
     * @return array
     */
    private function getNearSLA(): array
    {
        $query = "SELECT
  count(*) as total
FROM
  problem
WHERE pro_custno <> 282
  AND pro_status = 'I'
  AND pro_priority <> 5
  AND pro_sla_response_hours - pro_working_hours <= 0.3 AND pro_sla_response_hours - pro_working_hours >= 0";
        return DBConnect::fetchOne($query, []);
    }

    /**
     * @return array
     */
    private function getReopenToday(): array
    {
        $query = "SELECT
COUNT(*)  AS total
  FROM
  problem
  LEFT JOIN callactivity AS FIXED ON problem.`pro_problemno` = fixed.`caa_problemno` AND fixed.`caa_date` = CURDATE() AND fixed.`caa_callacttypeno` = 57
WHERE pro_custno <> 282
  AND `pro_reopened_date` = CURDATE()
  AND (fixed.`caa_callactivityno` IS NULL OR fixed.`caa_consno` <> 67)";
        return DBConnect::fetchOne($query, []);
    }

    /**
     * @return array
     */
    private function getRaisedAndStartedToday(): array
    {
        $query = "SELECT
  COUNT(pro_problemno) as total
FROM
  `callactivity` c
  JOIN problem p
    ON c.`caa_problemno` = p.`pro_problemno`
WHERE pro_custno <> 282
    AND caa_callacttypeno = 51
  and pro_status <> 'I'
  AND pro_date_raised >= CURDATE()
  AND pro_date_raised < CURDATE() + INTERVAL 1 DAY";
        return DBConnect::fetchOne($query, []);
    }

    /**
     * @return array
     */
    private function getUniqueCustomer(): array
    {
        $query = "SELECT
  COUNT(DISTINCT pro_custno) total
FROM
  `callactivity` c
  JOIN problem
    ON problem.`pro_problemno` = c.`caa_problemno`
WHERE pro_custno <> 282
  AND caa_callacttypeno = 51
  AND `caa_date` >= CURDATE()
  AND caa_date < CURDATE() + INTERVAL 1 DAY";
        return DBConnect::fetchOne($query, []);
    }

    /**
     * @return mixed
     */
    private function getBreachedSLA(): array
    {
        //9- Breached SLA
        $query = "SELECT
  COUNT(pro_problemno) AS total
FROM
  problem p
  JOIN customer cu
    ON p.`pro_custno` = cu.`cus_custno`
WHERE pro_custno <> 282
  AND pro_priority <> 5
  AND (
    (
      pro_status = 'I'
      AND pro_sla_response_hours - pro_working_hours <= 0
    )
    OR (
      `pro_responded_hours` > pro_sla_response_hours
    )
  )
  AND pro_date_raised >= CURDATE()
  AND pro_date_raised < (CURDATE() + INTERVAL 1 DAY);  ";
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
    function missedCallBack(){
        $query="SELECT cb.id, cb.consID,cb.problemID,cb.callActivityID,cb.contactID,cb.DESCRIPTION,cb.callback_datetime,cb.createAt,
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
    WHERE cb.status=:status 
     AND TIMESTAMPDIFF(HOUR,cb.callback_datetime,NOW()) >=72  ";      
    
        if ($_REQUEST['hd']=='false') {
            $query .= ' and pro_queue_no <> 1 ';
        }
        if ($_REQUEST['es']=='false') {
            $query .= ' and pro_queue_no <> 2 ';
        }
        if ($_REQUEST['sp']=='false') {
            $query .= ' and pro_queue_no <> 3 ';
        }
        if ($_REQUEST['p']=='false') {
            $query .= ' and pro_queue_no <> 5 ';
        }
        $limit = $_REQUEST['limit'];
        $query .=" order by timeRemain asc limit ".$limit;

        return DBConnect::fetchAll( $query,["status"=>CallBackStatus::AWAITING]);
    }
}
