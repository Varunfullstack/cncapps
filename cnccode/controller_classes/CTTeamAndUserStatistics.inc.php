<?php
/**
 * Customer Activity Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Exceptions\JsonHttpException;

global $cfg;
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUEscalationReport.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTTeamAndUserStatistics extends CTCNC
{
    const searchFormFromDate             = 'fromDate';
    const searchFormToDate               = 'toDate';
    const GET_FIXED_SERVICE_REQUEST_DATA = "GET_FIXED_SERVICE_REQUEST_DATA";
    const GET_TEAM_PERFORMANCE_DATA      = "GET_TEAM_PERFORMANCE_DATA";

    private $dsSearchForm = '';
    private $buEscalationReport;

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
        if (!$this->isUserSDManager()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(207);
        $this->buEscalationReport = new BUEscalationReport($this);
        $this->dsSearchForm = new DSForm ($this);
        $this->dsSearchForm->addColumn(
            self::searchFormFromDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->dsSearchForm->addColumn(
            self::searchFormToDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case self::GET_TEAM_PERFORMANCE_DATA:
                $data = $this->getJSONData();
                if (empty($data['startDate'])) {
                    throw new JsonHttpException(
                        400, 'Please provide a start date in YYYY-MM-DD format'
                    );
                }
                $startDate = $data['startDate'];
                if (empty($data['endDate'])) {
                    throw new JsonHttpException(
                        400, 'Please provide a end date in YYYY-MM-DD format'
                    );
                }
                $endDate             = $data['endDate'];
                $startYearMonthArray = explode('-', $startDate);
                $startYearMonth      = "{$startYearMonthArray[0]}-{$startYearMonthArray[1]}";
                $endYearMonthArray   = explode('-', $endDate);
                $endYearMonth        = "{$endYearMonthArray[0]}-{$endYearMonthArray[1]}";
                global $db;
                $query                 = "
                SELECT
  AVG(hdTeamActualSlaPercentage) AS hdTeamAvgSLAPercentage,
  AVG(hdTeamActualFixHours) AS hdTeamAvgFixHours,
  AVG(`esTeamActualSlaPercentage`) AS esTeamAvgSLAPercentage,
  AVG(`esTeamActualFixHours`) AS esTeamAvgFixHours,
  AVG(`imTeamActualSlaPercentage`) AS spTeamAvgSLAPercentage,
  AVG(`imTeamActualFixHours`) AS spTeamAvgFixHours,
  AVG(`projectTeamActualSlaPercentage`) AS pTeamAvgSLAPercentage,
  AVG(`projectTeamActualFixHours`) AS pTeamAvgFixHours
FROM
  team_performance
WHERE CONCAT(
    team_performance.`year`,
    '-',
    LPAD(team_performance.`month`, 2, 0)
  ) >= ?
  AND CONCAT(
    team_performance.`year`,
    '-',
    LPAD(team_performance.`month`, 2, 0)
  ) <= ?
                ";
                $teamPerformanceResult = $db->preparedQuery($query, [
                                                                      [
                                                                          "type"  => "s",
                                                                          "value" => $startYearMonth
                                                                      ],
                                                                      [
                                                                          "type"  => "s",
                                                                          "value" => $endYearMonth
                                                                      ],
                                                                  ]);
                $teamPerformanceData   = $teamPerformanceResult->fetch_array(MYSQLI_ASSOC);
                echo json_encode([
                                     "status" => "ok",
                                     "data"   => $teamPerformanceData
                                 ]);
                break;
            case self::GET_FIXED_SERVICE_REQUEST_DATA:
                $data = $this->getJSONData();
                if (empty($data['startDate'])) {
                    throw new JsonHttpException(
                        400, 'Please provide a start date in YYYY-MM-DD format'
                    );
                }
                $startDate = $data['startDate'];
                if (empty($data['endDate'])) {
                    throw new JsonHttpException(
                        400, 'Please provide a end date in YYYY-MM-DD format'
                    );
                }
                $endDate = $data['endDate'];
                global $db;
                $query               = "
                       SELECT
  consultant.`cns_name` AS userName,
  consultant.`cns_consno` AS userId,
  consultant.`teamID` as teamId,
  SUM(isFixed) AS `fixed`,
  SUM(isInitial) AS raised,
  SUM(isTimeRequest) AS timeRequests,
  SUM(isTechnicalChangeRequest) AS changeRequests,
  SUM(isOperationalTask) AS operationalTasks
FROM
  (SELECT
    callactivity.`caa_consno` AS userId,
    callactivity.`caa_problemno` AS `srId`,
    SUM(
      callactivity.`caa_callacttypeno` = 57
    ) AS isFixed,
    SUM(
      callactivity.`caa_callacttypeno` = 51
    ) AS isInitial,
    SUM(
      callactivity.`caa_callacttypeno` = 61
    ) AS isTimeRequest,
    SUM(
      callactivity.`caa_callacttypeno` = 59
    ) AS isTechnicalChangeRequest,
    SUM(
      callactivity.`caa_callacttypeno` = 60
    ) AS isOperationalTask
  FROM
    callactivity
  WHERE callactivity.`caa_date` >= ? and callactivity.`caa_date` <= ? 
    AND callactivity.`caa_consno` <> 67
  GROUP BY callactivity.`caa_problemno`,
    callactivity.`caa_consno`) a
  LEFT JOIN consultant
    ON consultant.`cns_consno` = a.`userId`
WHERE consultant.`teamID` <= 5
GROUP BY a.userId
ORDER BY teamId,
  userName             
                ";
                $result              = $db->preparedQuery($query, [
                                                                    [
                                                                        "type"  => "s",
                                                                        "value" => $startDate
                                                                    ],
                                                                    [
                                                                        "type"  => "s",
                                                                        "value" => $endDate
                                                                    ],
                                                                ]);
                $teamPerformanceData = $result->fetch_all(MYSQLI_ASSOC);
                echo json_encode([
                                     "status" => "ok",
                                     "data"   => $teamPerformanceData
                                 ]);
                break;
            default:
                $this->search();
        }
    }

    /**
     * @throws Exception
     */
    function search()
    {

        $this->setMethodName('search');
        $teamReport       = null;
        $technicianReport = null;
        if (isset ($_REQUEST ['searchForm']) == 'POST') {
            if (!$this->dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                $teamReport       = $this->buEscalationReport->getTeamReport($this->dsSearchForm);
                $technicianReport = $this->buEscalationReport->getTechnicianReport($this->dsSearchForm);
            }
        }
        if ($this->dsSearchForm->getValue(self::searchFormFromDate) == '') {
            $this->dsSearchForm->setUpdateModeUpdate();
            $this->dsSearchForm->setValue(
                self::searchFormFromDate,
                date(
                    'Y-m-d',
                    strtotime("-1 month")
                )
            );
            $this->dsSearchForm->post();
        }
        if (!$this->dsSearchForm->getValue(self::searchFormToDate)) {
            $this->dsSearchForm->setUpdateModeUpdate();
            $this->dsSearchForm->setValue(
                self::searchFormToDate,
                date('Y-m-d')
            );
            $this->dsSearchForm->post();
        }
        $this->setMethodName('displaySearchForm');
        $this->setTemplateFiles(array(
                                    'EscalationReport' => 'EscalationReport.inc'
                                ));
        $this->loadReactCSS('FixedServiceRequestCountComponent.css');
        $this->loadReactScript('FixedServiceRequestCountComponent.js');
        $urlSubmit = Controller::buildLink($_SERVER ['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));
        $this->setPageTitle('Team & User Statistics', 'Engineer Service Request Statistics');
        $this->template->set_var(array(
                                     'formError'        => $this->formError,
                                     'fromDate'         => $this->dsSearchForm->getValue(self::searchFormFromDate),
                                     'fromDateMessage'  => $this->dsSearchForm->getMessage(self::searchFormFromDate),
                                     'toDate'           => $this->dsSearchForm->getValue(self::searchFormToDate),
                                     'toDateMessage'    => $this->dsSearchForm->getMessage(self::searchFormToDate),
                                     'urlSubmit'        => $urlSubmit,
                                     'teamReport'       => $teamReport,
                                     'technicianReport' => $technicianReport
                                 ));
        $this->template->parse(
            'CONTENTS',
            'EscalationReport',
            true
        );
        $this->parsePage();

    } // end function displaySearchForm

}
