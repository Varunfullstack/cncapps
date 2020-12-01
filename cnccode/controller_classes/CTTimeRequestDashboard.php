<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 22/08/2018
 * Time: 10:39
 */

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg ["path_dbe"] . "/DBEJCallActivity.php");

class CTTimeRequestDashboard extends CTCNC
{
    const GET_DATA = 'getData';
    const GET_DATATABLES_DATA = 'getDatatablesData';

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
        if (!self::isSdManager()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(202);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {

        switch ($this->getAction()) {

            case self::GET_DATA:
            {
                $result = $this->getPendingTimeRequests();
                echo json_encode(
                    $result,
                    JSON_NUMERIC_CHECK
                );
                exit;
            }
            default:
                $this->displayReport();
                break;
        }
    }

    private function getPendingTimeRequests()
    {

        $queryString = "SELECT
  customer.`cus_name` AS customerName,
  problem.`pro_problemno` AS serviceRequestId,
  callactivity.`caa_callactivityno` AS activityId,
  requester.cns_consno as requesterId,
  requester.teamID as requesterTeamId,
  CASE
    requester.teamID
    WHEN 1
    THEN 'Helpdesk'
    WHEN 2
    THEN 'Escalation'
    WHEN 4
    THEN 'Small Projects'
    WHEN 5
    THEN 'Projects'
  END AS requesterTeam,
  requester.cns_name AS requesterName,
  CONCAT(
    callactivity.`caa_date`,
    ' ',
    callactivity.`caa_starttime`,
    ':00'
  ) AS requestedAt,
  callactivity.`reason` AS notes,
  problem.pro_chargeable_activity_duration_hours AS chargeableHours,
  CASE
    requester.teamID
    WHEN 1
    THEN problem.pro_hd_limit_minutes
    WHEN 2
    THEN problem.pro_es_limit_minutes
    WHEN 4
    THEN problem.pro_im_limit_minutes
    WHEN 5
    THEN problem.`projectTeamLimitMinutes`
  END as teamLimitMinutes,
   CASE
    requester.teamID
    WHEN 1
    THEN headert.`hdTeamManagementTimeApprovalMinutes`
    WHEN 2
    THEN headert.`esTeamManagementTimeApprovalMinutes`
    WHEN 4
    THEN headert.`smallProjectsTeamManagementTimeApprovalMinutes`
  END AS teamManagementApprovalMinutes
FROM
  callactivity
  JOIN problem
    ON problem.`pro_problemno` = callactivity.`caa_problemno`
  JOIN customer
    ON problem.`pro_custno` = customer.`cus_custno`
  JOIN consultant requester
    ON requester.`cns_consno` = callactivity.`caa_consno`
  LEFT JOIN headert ON 1
WHERE callactivity.caa_status = 'O'
  AND callactivity.caa_callacttypeno = 61";

        /** @var dbSweetcode $db */
        global $db;

        $queryString .= " order by requestedAt asc";
        $result = $db->preparedQuery(
            $queryString,
            []
        );
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $buActivity = new BUActivity($this);

        $data = array_map(
            function ($datum) use ($buActivity) {
                $problemID = $datum['serviceRequestId'];
                $teamID = $datum['requesterTeamId'];

                $leftOnBudget = null;
                $usedMinutes = 0;
                $dbeProblem = new DBEJProblem($this);
                $dbeProblem->getRow($problemID);
                $assignedMinutes = $datum['teamLimitMinutes'];
                $isOverLimit = $assignedMinutes >= $datum['teamManagementApprovalMinutes'];

                switch ($teamID) {
                    case 1:
                        $usedMinutes = $buActivity->getHDTeamUsedTime($problemID);
                        break;
                    case 2:
                        $usedMinutes = $buActivity->getESTeamUsedTime($problemID);
                        break;
                    case 4:
                        $usedMinutes = $buActivity->getSPTeamUsedTime($problemID);
                        break;
                    case 5:
                        $usedMinutes = $buActivity->getUsedTimeForProblemAndTeam($problemID, 5);
                        $isOverLimit = false;
                }

                $leftOnBudget = $assignedMinutes - $usedMinutes;

                return array_merge(
                    $datum,
                    [
                        "timeSpentSoFar"   => round($usedMinutes),
                        "timeLeftOnBudget" => round($leftOnBudget),
                        "approvalLevel"    => $isOverLimit ? 'Mgmt' : 'Team Lead',
                        "isOverLimit"      => $isOverLimit
                    ]
                );
            },
            $data
        );

        return [
            "data" => $data,
            "meta" => [
                "total"    => $result->num_rows,
                "filtered" => $result->num_rows,
            ]

        ];
    }

    function displayReport()
    {

        $this->setMethodName('displayReport');

        $this->setTemplateFiles(
            'TimeRequestDashboard',
            'TimeRequestDashboard'
        );

        $this->setPageTitle('Time Request Dashboard');
        $buHeader = new BUHeader($this);
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);
        $isAdditionalTimeApprover = $this->dbeUser->getValue(DBEUser::additionalTimeLevelApprover);
        $this->template->setVar('additionalTimeApprover', $isAdditionalTimeApprover ? 'true' : 'false');
        $this->template->setVar(
            'pendingTimeLimitActionThresholdMinutes',
            $dsHeader->getValue(DBEHeader::pendingTimeLimitActionThresholdMinutes)
        );

        $this->template->parse(
            'CONTENTS',
            'TimeRequestDashboard',
            true
        );
        $this->parsePage();


    }
}