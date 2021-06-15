<?php
/**
 * Home controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;

use CNCLTD\Data\DBConnect;
use CNCLTD\Exceptions\JsonHttpException;
use CNCLTD\Utils;

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBESalesOrderTotals.inc.php');
require_once($cfg['path_dbe'] . '/DBEInvoiceTotals.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUProject.inc.php');
require_once($cfg['path_bu'] . '/BUTeamPerformance.inc.php');
require_once($cfg['path_ct'] . '/CTProject.inc.php');


class CTHome extends CTCNC
{
    const DetailedChartsAction               = 'detailedCharts';
    const GetDetailedChartsDataAction        = "getDetailedChartsData";
    const getFirstTimeFixData                = "getFirstTimeFixData";
    const getFixedAndReopenData              = "getFixedAndReopenData";
    const getUpcomingVisitsData              = "getUpcomingVisitsData";
    const GET_USER_PERFORMANCE_BETWEEN_DATES = 'getUserPerformanceBetweenDates';
    const GET_SALES_FIGURES                  = 'salesFigures';
    const GET_TEAM_PERFORMANCE               = 'teamPerformance';
    const GET_ALL_USER_PERFORMANCE           = 'allUserPerformance';
    const GET_USER_PERFORMANCE               = 'userPerformance';
    const DEFAULT_LAYOUT                     = 'defaultLayout';
    const GET_LOGGED_ACTIVITY_TIMES          = 'getLoggedActivityTimes';
    const GET_FEEDBACK_TEAMS                 = 'teamsFeedback';
    /** @var DataSet|DBEHeader */
    private $dsHeader;
    /** @var BUUser */
    private $buUser;
    /**
     * @var BUCustomer
     */
    private $buCustomer;

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
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($this->dsHeader);
        $this->buUser = new BUUser($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($this->getAction()) {
            case 'lastWeekHelpDesk':
                $team = 1;
                if ($this->getParam('team')) {
                    $team = $this->getParam('team');
                }
                echo json_encode(
                    $this->getLastWeekPerformanceDataForTeam($team),
                    JSON_NUMERIC_CHECK
                );
                break;
            case self::GetDetailedChartsDataAction:
                echo json_encode(
                    $this->getDetailedChartsData(
                        $this->getParam('engineerID'),
                        $this->getParam('startDate'),
                        $this->getParam('endDate')
                    ),
                    JSON_NUMERIC_CHECK
                );
                break;
            case self::DetailedChartsAction:
                $this->showDetailCharts(
                    $this->getParam('engineerID'),
                    $this->getParam('startDate'),
                    $this->getParam('endDate')
                );
                break;
            case self::getFirstTimeFixData:
                echo html_entity_decode($this->getFirstTimeFixData());
                break;
            case self::getFixedAndReopenData:
                echo html_entity_decode($this->getFixedAndReopenData());
                break;
            case self::GET_LOGGED_ACTIVITY_TIMES:
                $team = 1;
                if ($this->getParam('team')) {
                    $team = $this->getParam('team');
                }
                $dateTime = new DateTime();
                if ($this->getParam('date')) {
                    $dateTime = DateTime::createFromFormat(DATE_MYSQL_DATE, $this->getParam('date'));
                    if (!$dateTime) {
                        throw new JsonHttpException(
                            400, "Please provide date a valid date in YYYY-MM-DD format"
                        );
                    }
                }
                $data = array_values($this->getLoggedActivityByTimeBracket($team, $dateTime));
                echo json_encode(["status" => "ok", "data" => $data], JSON_NUMERIC_CHECK);
                break;
            case self::getUpcomingVisitsData:
                echo $this->getUpcomingVisitsData();
                break;
            case self::GET_USER_PERFORMANCE_BETWEEN_DATES:
                try {
                    echo json_encode(
                        ["status" => "ok", "data" => $this->getUserPerformanceBetweenDatesController()]
                    );
                } catch (Exception $exception) {
                    throw new JsonHttpException(400, $exception->getMessage());
                }
                break;
            case self::GET_SALES_FIGURES:
                echo html_entity_decode(json_encode($this->getSalesFigures()));
                break;
            case self::GET_TEAM_PERFORMANCE:
                echo json_encode($this->getTeamPerformance());
                break;
            case self::GET_ALL_USER_PERFORMANCE:
                echo json_encode($this->getAllUsersPerformance());
                break;
            case 'charts' :
                $this->displayChartsWithoutMenu();
                break;
            case self::GET_USER_PERFORMANCE:
                echo json_encode($this->getUserPerformance());
                break;
            case self::DEFAULT_LAYOUT:
                if ($method == 'GET') echo json_encode($this->getDefaultLayout());
                if ($method == 'POST') echo json_encode($this->setDefaultLayout());
                break;
            case self::GET_FEEDBACK_TEAMS:
                echo json_encode($this->getFeedbackTeams(), JSON_NUMERIC_CHECK);
                break;
            default:
                $this->displayReact();
                break;
        }
    }

    /**
     * @param $team
     * @return array
     * @throws Exception
     */
    private function getLastWeekPerformanceDataForTeam($team)
    {
        $isStandardUser = false;
        if (!$this->buUser->isSdManager($this->userID)) {
            if ($this->buUser->getLevelByUserID($this->userID) <= 5) {
                $team           = $this->buUser->getLevelByUserID($this->userID);
                $isStandardUser = true;
            } else {
                return [];
            }
        }
        $dbeUser = $this->getDbeUser();
        $dbeUser->setValue(
            DBEUser::userID,
            $this->userID
        );
        $dbeUser->getRow();
        $usersData = [];
        $results   = $this->buUser->teamMembersPerformanceData(
            $team,
            $this->buUser->isSdManager($this->userID)
        );
        foreach ($results as $result) {
            if ($isStandardUser && $result['userID'] != $this->dbeUser->getValue(DBEUser::userID)) {
                continue;
            }
            // if the user doesn't have a graph yet create it
            if (!isset($usersData[$result['userID']])) {
                $usersData[$result['userID']]['userID']     = $result['userID'];
                $usersData[$result['userID']]['userName']   = $result['userLabel'];
                $usersData[$result['userID']]['dataPoints'] = [];
            }
            $usersData[$result['userID']]['dataPoints'][] = [
                'date'           => (new DateTime($result['loggedDate']))->format(DATE_ISO8601),
                'loggedHours'    => $result['loggedHours'],
                'cncLoggedHours' => $result['cncLoggedHours'],
                'isHolidays'     => $result['holiday'],
                'holidayHours'   => $result['holidayHours']
            ];
        }
        usort(
            $usersData,
            function ($a,
                      $b
            ) {
                return strcmp(
                    $a['userName'],
                    $b['userName']
                );
            }
        );
        return $usersData;
    }

    /**
     * @param $engineerID
     * @param $startDate
     * @param $endDate
     * @return array
     * @throws Exception
     */
    private function getDetailedChartsData($engineerID,
                                           $startDate,
                                           $endDate
    )
    {
        if (!$this->buUser->isSdManager($this->userID)) {
            return $this->buUser->getEngineerDetailedData(
                $this->userID,
                (new DateTime($startDate)),
                (new DateTime($endDate))
            );
        }
        // we need to pull data
        $data = $this->buUser->getEngineerDetailedData(
            $engineerID,
            (new DateTime($startDate)),
            (new DateTime($endDate))
        );
        return $data;

    }

    /**
     * @param $engineerID
     * @param $startDate
     * @param $endDate
     * @throws Exception
     */
    private function showDetailCharts($engineerID,
                                      $startDate,
                                      $endDate
    )
    {
        $this->setTemplateFiles(
            'detailedCharts',
            'HomeDetailCharts.inc'
        );
        if (!$engineerID) {
            $this->formError        = "Engineer ID not given";
            $this->formErrorMessage = "Engineer ID not given";
            return;
        }
        $dbeUser = new DBEUser($this);
        $dbeUser->setValue(
            DBEJUser::userID,
            $engineerID
        );
        $dbeUser->getRow();
        $this->template->set_var(
            [
                "dataFetchUrl" => Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => self::GetDetailedChartsDataAction
                    )
                ),
                "engineerID"   => $engineerID,
                "engineerName" => $dbeUser->getValue(DBEJUser::firstName) . ' ' . $dbeUser->getValue(
                        DBEJUser::lastName
                    ),
                "startDate"    => $startDate,
                "endDate"      => $endDate
            ]
        );
        $this->template->parse(
            'CONTENTS',
            'detailedCharts',
            true
        );
        $this->parsePage();
    }

    private function getFirstTimeFixData()
    {
        global $db;
        $db->query("select firstTimeFix from homeData limit 1");
        $db->next_record(MYSQLI_ASSOC);
        return $db->Record['firstTimeFix'];
    }

    private function getFixedAndReopenData()
    {
        global $db;
        $db->query("select fixedAndReopenData from homeData limit 1");
        $db->next_record(MYSQLI_ASSOC);
        return $db->Record['fixedAndReopenData'];
    }

    private function getUpcomingVisitsData()
    {
        global $db;
        $result = $db->query(
            "SELECT 
  caa_problemno AS serviceRequestID,
  caa_callactivityno AS callActivityID,
  caa_date AS date,
  caa_starttime AS time,
  cus_name AS customerName,
  CONCAT(
    consultant.firstName,
    ' ',
    consultant.lastName
  ) AS engineerName,
  emailSubjectSummary 
FROM
  callactivity 
  LEFT JOIN problem 
    ON problem.`pro_problemno` = caa_problemno 
  LEFT JOIN customer 
    ON customer.`cus_custno` = problem.pro_custno 
  LEFT JOIN consultant 
    ON consultant.`cns_consno` = callactivity.`caa_consno` 
WHERE callactivity.`caa_callacttypeno` IN (4, 7) 
  AND caa_date >= date(NOW()) 
  AND caa_date <= date((NOW() + INTERVAL 1 WEEK)) 
  AND (
    caa_endtime IS NULL 
    OR caa_endtime = \"\"
  ) 
ORDER BY caa_date ASC,
  caa_starttime ASC "
        );
        $data   = [];
        while ($row = $result->fetch_assoc()) {
            $row['emailSubjectSummary'] = substr(
                Utils::stripEverything($row['emailSubjectSummary']),
                0
            );
            $data[]                     = $row;
        }
        return html_entity_decode(json_encode($data));
    }

    /**
     * @throws Exception
     */
    function displayReact()
    {
        /**
         * if user is only in the technical group then display the current activity dash-board
         */
        if ($this->hasPermissions(TECHNICAL_PERMISSION) && !$this->hasPermissions(
                SUPERVISOR_PERMISSION
            ) && !$this->hasPermissions(MAINTENANCE_PERMISSION) && !$this->hasPermissions(ACCOUNTS_PERMISSION)) {

            $urlNext = Controller::buildLink(
                'CurrentActivityReport.php',
                array()
            );
            header('Location: ' . $urlNext);
            exit;

        }
        /*
        Otherwise display other sections based upon group membership
        */
        $this->setTemplateFiles(
            'HOME',
            'Home.rct'
        );
        $this->template->parse(
            'CONTENTS',
            'HOME',
            true
        );
        $this->loadReactScript('HomeComponent.js');
        $this->loadReactCSS('HomeComponent.css');
        $this->parsePage();
    } // end display projects

    /**
     * @throws Exception
     */
    private function displayUpcomingVisits()
    {
        $this->setTemplateFiles(
            'upcomingVisits',
            'upcomingVisits'
        );
        $this->template->set_var(
            [
                "upcomingVisitsFetchDataURL" => Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    [
                        'action' => self::getUpcomingVisitsData
                    ]
                )
            ]
        );
        $this->template->parse(
            'CONTENTS',
            'upcomingVisits',
            true
        );
    } // end displayTeamPerformanceReport

    function displaySalesFigures()
    {
        $this->setTemplateFiles(
            'SalesFigures',
            'SalesFigures.inc'
        );
        $dbeSalesOrderTotals = new DBESalesOrderTotals($this);
        $dbeSalesOrderTotals->getRow();
        $profit = $dbeSalesOrderTotals->getValue(DBESalesOrderTotals::saleValue) - $dbeSalesOrderTotals->getValue(
                DBESalesOrderTotals::costValue
            );
        $this->template->set_var(
            array(
                'soSale'   => Controller::formatNumber($dbeSalesOrderTotals->getValue(DBESalesOrderTotals::saleValue)),
                'soCost'   => Controller::formatNumber($dbeSalesOrderTotals->getValue(DBESalesOrderTotals::costValue)),
                'soProfit' => Controller::formatNumber($profit)
            )
        );
        $profitTotal      = $profit;
        $saleTotal        = $dbeSalesOrderTotals->getValue(DBESalesOrderTotals::saleValue);
        $costTotal        = $dbeSalesOrderTotals->getValue(DBESalesOrderTotals::costValue);
        $dbeInvoiceTotals = new DBEInvoiceTotals($this);
        $dbeInvoiceTotals->getCurrentMonthTotals();
        $profit = $dbeInvoiceTotals->getValue(DBEInvoiceTotals::saleValue) - $dbeInvoiceTotals->getValue(
                DBEInvoiceTotals::costValue
            );
        $this->template->set_var(
            array(
                'invPrintedSale'   => Controller::formatNumber(
                    $dbeInvoiceTotals->getValue(DBEInvoiceTotals::saleValue)
                ),
                'invPrintedCost'   => Controller::formatNumber(
                    $dbeInvoiceTotals->getValue(DBEInvoiceTotals::costValue)
                ),
                'invPrintedProfit' => Controller::formatNumber($profit)
            )
        );
        $profitTotal += $profit;
        $saleTotal   += $dbeInvoiceTotals->getValue(DBEInvoiceTotals::saleValue);
        $costTotal   += $dbeInvoiceTotals->getValue(DBEInvoiceTotals::costValue);
        $dbeInvoiceTotals->getUnprintedTotals();
        $profit = $dbeInvoiceTotals->getValue(DBEInvoiceTotals::saleValue) - $dbeInvoiceTotals->getValue(
                DBEInvoiceTotals::costValue
            );
        $this->template->set_var(
            array(
                'invUnprintedSale'   => Controller::formatNumber(
                    $dbeInvoiceTotals->getValue(DBEInvoiceTotals::saleValue)
                ),
                'invUnprintedCost'   => Controller::formatNumber(
                    $dbeInvoiceTotals->getValue(DBEInvoiceTotals::costValue)
                ),
                'invUnprintedProfit' => Controller::formatNumber($profit)
            )
        );
        $profitTotal += $profit;
        $saleTotal   += $dbeInvoiceTotals->getValue(DBEInvoiceTotals::saleValue);
        $costTotal   += $dbeInvoiceTotals->getValue(DBEInvoiceTotals::costValue);
        $this->template->set_var(
            array(
                'saleTotal'   => Controller::formatNumber($saleTotal),
                'costTotal'   => Controller::formatNumber($costTotal),
                'profitTotal' => Controller::formatNumber($profitTotal)
            )
        );
        $this->template->parse(
            'CONTENTS',
            'SalesFigures',
            true
        );


    } // end displayUserLoggingPerformanceReport

    /**
     * @return mixed
     * @throws Exception
     */
    private function displayFirstTimeFixFigures()
    {
        $this->setTemplateFiles(
            'firstTimeFigures',
            'FirstTimeFigures'
        );
        $this->template->set_var(
            [
                "fetchDataURL" => Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    [
                        'action' => self::getFirstTimeFixData
                    ]
                )
            ]
        );
        $this->template->parse(
            'OUTPUT',
            'firstTimeFigures',
            true
        );
        return $this->template->getVar('OUTPUT');

    }

    /**
     * @return mixed
     * @throws Exception
     */
    function displayFixedAndReopen()
    {

        $template = new Template (
            $GLOBALS ["cfg"] ["path_templates"], "remove"
        );
        $template->setFile(
            'FixedAndReopened',
            'HomeFixedAndReopened.inc.html'
        );
        $template->set_var(
            [
                "fetchDataURL" => Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    [
                        'action' => self::getFixedAndReopenData
                    ]
                )
            ]
        );
        $template->parse(
            'OUTPUT',
            'FixedAndReopened',
            true
        );
        return $template->getVar('OUTPUT');
    }

    function displayTeamPerformanceReport()
    {
        $this->setTemplateFiles(
            'DashboardTeamPerformanceReport',
            'DashboardTeamPerformanceReport.inc'
        );
        $buTeamPerformance = new BUTeamPerformance($this);
        $this->template->set_var(
            array(
                'esTeamTargetSlaPercentage'            => $this->dsHeader->getValue(
                    DBEHeader::esTeamTargetSlaPercentage
                ),
                'esTeamTargetFixHours'                 => $this->dsHeader->getValue(DBEHeader::esTeamTargetFixHours),
                'smallProjectsTeamTargetSlaPercentage' => $this->dsHeader->getValue(
                    DBEHeader::smallProjectsTeamTargetSlaPercentage
                ),
                'smallProjectsTeamTargetFixHours'      => $this->dsHeader->getValue(
                    DBEHeader::smallProjectsTeamTargetFixHours
                ),
                'hdTeamTargetSlaPercentage'            => $this->dsHeader->getValue(
                    DBEHeader::hdTeamTargetSlaPercentage
                ),
                'hdTeamTargetFixHours'                 => $this->dsHeader->getValue(DBEHeader::hdTeamTargetFixHours),
                'projectTeamTargetSlaPercentage'       => $this->dsHeader->getValue(
                    DBEHeader::projectTeamTargetSlaPercentage
                ),
                'projectTeamTargetFixHours'            => $this->dsHeader->getValue(
                    DBEHeader::projectTeamTargetFixHours
                )
            )
        );
        /* Extract data and build report */
        $results = $buTeamPerformance->getQuarterlyRecordsByYear(date('Y'));
        foreach ($results as $result) {
            $esSLAPerformanceClass                = 'performance-warn';
            $esFixHoursClass                      = 'performance-warn';
            $hdSLAPerformanceClass                = 'performance-warn';
            $hdFixHoursClass                      = 'performance-warn';
            $smallProjectsTeamSLAPerformanceClass = 'performance-warn';
            $smallProjectsTeamFixHoursClass       = 'performance-warn';
            $projectTeamSLAPerformanceClass       = 'performance-warn';
            $projectTeamFixHoursClass             = 'performance-warn';
            if (round($result['esTeamActualSlaPercentage'], 1) >= round($result['esTeamTargetSlaPercentage'], 1)) {
                $esSLAPerformanceClass = 'performance-green';
            }
            if (round($result['hdTeamActualSlaPercentage'], 1) >= round($result['hdTeamTargetSlaPercentage'], 1)) {
                $hdSLAPerformanceClass = 'performance-green';
            }
            if (round(
                    $result['smallProjectsTeamActualSlaPercentage']
                ) >= round($result['smallProjectsTeamTargetSlaPercentage'])) {
                $smallProjectsTeamSLAPerformanceClass = 'performance-green';
            }
            if (round(
                    $result['projectTeamActualSlaPercentage']
                ) >= $result['projectTeamTargetSlaPercentage']) {
                $projectTeamSLAPerformanceClass = 'performance-green';
            }
            if ($result['esTeamActualFixHours'] <= $result['esTeamTargetFixHours']) {
                $esFixHoursClass = 'performance-green';
            }
            if ($result['hdTeamActualFixHours'] <= $result['hdTeamTargetFixHours']) {
                $hdFixHoursClass = 'performance-green';
            }
            if ($result['smallProjectsTeamActualFixHours'] <= $result['smallProjectsTeamTargetFixHours']) {
                $smallProjectsTeamFixHoursClass = 'performance-green';
            }
            if ($result['projectTeamActualFixHours'] <= $result['projectTeamTargetFixHours']) {
                $projectTeamFixHoursClass = 'performance-green';
            }
            $this->template->set_var(
                array(
                    'esTeamActualSlaPercentage' . $result['quarter']                      => number_format(
                        $result['esTeamActualSlaPercentage'],
                        1
                    ),
                    'esTeamActualFixHours' . $result['quarter']                           => number_format(
                        $result['esTeamActualFixHours'],
                        2
                    ),
                    'esTeamActualFixQty' . $result['quarter']                             => $result['esTeamActualFixQty'],
                    'smallProjectsTeamActualSlaPercentage' . $result['quarter']           => number_format(
                        $result['smallProjectsTeamActualSlaPercentage'],
                        0
                    ),
                    'smallProjectsTeamActualFixHours' . $result['quarter']                => number_format(
                        $result['smallProjectsTeamActualFixHours'],
                        2
                    ),
                    'smallProjectsTeamActualFixQty' . $result['quarter']                  => $result['smallProjectsTeamActualFixQty'],
                    'projectTeamActualSlaPercentage' . $result['quarter']                 => number_format(
                        $result['projectTeamActualSlaPercentage'],
                        1
                    ),
                    'projectTeamActualFixHours' . $result['quarter']                      => number_format(
                        $result['projectTeamActualFixHours'],
                        2
                    ),
                    'projectTeamActualFixQty' . $result['quarter']                        => $result['projectTeamActualFixQty'],
                    'hdTeamActualSlaPercentage' . $result['quarter']                      => number_format(
                        $result['hdTeamActualSlaPercentage'],
                        1
                    ),
                    'hdTeamActualFixHours' . $result['quarter']                           => number_format(
                        $result['hdTeamActualFixHours'],
                        2
                    ),
                    'hdTeamActualFixQty' . $result['quarter']                             => $result['hdTeamActualFixQty'],
                    'hdTeamActualSlaPercentage' . $result['quarter'] . 'Class'            => $hdSLAPerformanceClass,
                    'hdTeamActualFixHours' . $result['quarter'] . 'Class'                 => $hdFixHoursClass,
                    'esTeamActualSlaPercentage' . $result['quarter'] . 'Class'            => $esSLAPerformanceClass,
                    'esTeamActualFixHours' . $result['quarter'] . 'Class'                 => $esFixHoursClass,
                    'smallProjectsTeamActualSlaPercentage' . $result['quarter'] . 'Class' => $smallProjectsTeamSLAPerformanceClass,
                    'smallProjectsTeamActualFixHours' . $result['quarter'] . 'Class'      => $smallProjectsTeamFixHoursClass,
                    'projectTeamActualSlaPercentage' . $result['quarter'] . 'Class'       => $projectTeamSLAPerformanceClass,
                    'projectTeamActualFixHours' . $result['quarter'] . 'Class'            => $projectTeamFixHoursClass,
                )
            );

        }
        $this->template->parse(
            'CONTENTS',
            'DashboardTeamPerformanceReport',
            true
        );

    }

    function displayAllUsersPerformanceReport()
    {
        $this->setTemplateFiles(
            'DashboardAllUsersPerformanceReport',
            'DashboardAllUsersPerformanceReport.inc'
        );
        $hdTeamTargetLogPercentage            = $this->dsHeader->getValue(DBEHeader::hdTeamTargetLogPercentage);
        $esTeamTargetLogPercentage            = $this->dsHeader->getValue(DBEHeader::esTeamTargetLogPercentage);
        $smallProjectsTeamTargetLogPercentage = $this->dsHeader->getValue(
            DBEHeader::smallProjectsTeamTargetLogPercentage
        );
        $projectTeamTargetLogPercentage       = $this->dsHeader->getValue(DBEHeader::projectTeamTargetLogPercentage);
        $hdUsers                              = $this->buUser->getUsersByTeamLevel(1);
        $esUsers                              = $this->buUser->getUsersByTeamLevel(2);
        $imUsers                              = $this->buUser->getUsersByTeamLevel(3);
        $projectUsers                         = $this->buUser->getUsersByTeamLevel(5);
        /*
        Extract data and build report
        2 sections: HD users and ES users
        */
        $this->template->set_block(
            'DashboardAllUsersPerformanceReport',
            'hdUserBlock',
            'hdUsers'
        );
        foreach ($hdUsers as $user) {

            $weekly                = $this->buUser->getUserPerformanceByUser(
                $user['cns_consno'],
                7
            );
            $monthly               = $this->buUser->getUserPerformanceByUser(
                $user['cns_consno'],
                30
            );
            $weeklyPercentageClass = null;
            if ($weekly['performancePercentage'] < $hdTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-warn';
            }
            if ($weekly['performancePercentage'] >= $hdTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-green';
            }
            $monthlyPercentageClass = null;
            if ($monthly['performancePercentage'] < $hdTeamTargetLogPercentage) {
                $monthlyPercentageClass = 'performance-warn';
            }
            if ($monthly['performancePercentage'] >= $hdTeamTargetLogPercentage) {
                $monthlyPercentageClass = 'performance-green';
            }
            $this->template->set_var(
                array(
                    'initials'               => $user['initials'],
                    'targetPercentage'       => $hdTeamTargetLogPercentage,
                    'weeklyPercentage'       => number_format(
                        $weekly['performancePercentage'],
                        2
                    ),
                    'weeklyHours'            => number_format(
                        $weekly['loggedHours'],
                        2
                    ),
                    'monthlyPercentage'      => number_format(
                        $monthly['performancePercentage'],
                        2
                    ),
                    'monthlyHours'           => number_format(
                        $monthly['loggedHours'],
                        2
                    ),
                    'weeklyPercentageClass'  => $weeklyPercentageClass,
                    'monthlyPercentageClass' => $monthlyPercentageClass
                )
            );
            $this->template->parse(
                'hdUsers',
                'hdUserBlock',
                true
            );
        }
        $this->template->set_block(
            'DashboardAllUsersPerformanceReport',
            'esUserBlock',
            'esUsers'
        );
        foreach ($esUsers as $user) {

            $weekly                = $this->buUser->getUserPerformanceByUser(
                $user['cns_consno'],
                7
            );
            $monthly               = $this->buUser->getUserPerformanceByUser(
                $user['cns_consno'],
                30
            );
            $weeklyPercentageClass = null;
            if ($weekly['performancePercentage'] < $esTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-warn';
            }
            if ($weekly['performancePercentage'] >= $esTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-green';
            }
            $monthlyPercentageClass = null;
            if ($monthly['performancePercentage'] < $esTeamTargetLogPercentage) {
                $monthlyPercentageClass = 'performance-warn';
            }
            if ($monthly['performancePercentage'] >= $esTeamTargetLogPercentage) {
                $monthlyPercentageClass = 'performance-green';
            }
            $this->template->set_var(
                array(
                    'initials'               => $user['initials'],
                    'targetPercentage'       => $esTeamTargetLogPercentage,
                    'weeklyPercentage'       => number_format(
                        $weekly['performancePercentage'],
                        2
                    ),
                    'weeklyHours'            => number_format(
                        $weekly['loggedHours'],
                        2
                    ),
                    'monthlyPercentage'      => number_format(
                        $monthly['performancePercentage'],
                        2
                    ),
                    'monthlyHours'           => number_format(
                        $monthly['loggedHours'],
                        2
                    ),
                    'weeklyPercentageClass'  => $weeklyPercentageClass,
                    'monthlyPercentageClass' => $monthlyPercentageClass
                )
            );
            $this->template->parse(
                'esUsers',
                'esUserBlock',
                true
            );
        }
        /*
        Small Projects team users
        */
        $this->template->set_block(
            'DashboardAllUsersPerformanceReport',
            'imUserBlock',
            'imUsers'
        );
        foreach ($imUsers as $user) {

            $weekly                = $this->buUser->getUserPerformanceByUser(
                $user['cns_consno'],
                7
            );
            $monthly               = $this->buUser->getUserPerformanceByUser(
                $user['cns_consno'],
                30
            );
            $weeklyPercentageClass = null;
            if ($weekly['performancePercentage'] < $smallProjectsTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-warn';
            }
            if ($weekly['performancePercentage'] >= $smallProjectsTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-green';
            }
            $monthlyPercentageClass = null;
            if ($monthly['performancePercentage'] < $smallProjectsTeamTargetLogPercentage) {
                $monthlyPercentageClass = 'performance-warn';
            }
            if ($monthly['performancePercentage'] >= $smallProjectsTeamTargetLogPercentage) {
                $monthlyPercentageClass = 'performance-green';
            }
            $this->template->set_var(
                array(
                    'initials'               => $user['initials'],
                    'targetPercentage'       => $smallProjectsTeamTargetLogPercentage,
                    'weeklyPercentage'       => number_format(
                        $weekly['performancePercentage'],
                        2
                    ),
                    'weeklyHours'            => number_format(
                        $weekly['loggedHours'],
                        2
                    ),
                    'monthlyPercentage'      => number_format(
                        $monthly['performancePercentage'],
                        2
                    ),
                    'monthlyHours'           => number_format(
                        $monthly['loggedHours'],
                        2
                    ),
                    'weeklyPercentageClass'  => $weeklyPercentageClass,
                    'monthlyPercentageClass' => $monthlyPercentageClass
                )
            );
            $this->template->parse(
                'imUsers',
                'imUserBlock',
                true
            );
        }
        /*
        Projects team users
        */
        $this->template->set_block(
            'DashboardAllUsersPerformanceReport',
            'projectUserBlock',
            'projectUsers'
        );
        foreach ($projectUsers as $user) {

            $weekly                = $this->buUser->getUserPerformanceByUser(
                $user['cns_consno'],
                7
            );
            $monthly               = $this->buUser->getUserPerformanceByUser(
                $user['cns_consno'],
                30
            );
            $weeklyPercentageClass = null;
            if ($weekly['performancePercentage'] < $projectTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-warn';
            }
            if ($weekly['performancePercentage'] >= $projectTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-green';
            }
            $monthlyPercentageClass = null;
            if ($monthly['performancePercentage'] < $projectTeamTargetLogPercentage) {
                $monthlyPercentageClass = 'performance-warn';
            }
            if ($monthly['performancePercentage'] >= $projectTeamTargetLogPercentage) {
                $monthlyPercentageClass = 'performance-green';
            }
            $this->template->set_var(
                array(
                    'initials'               => $user['initials'],
                    'targetPercentage'       => $projectTeamTargetLogPercentage,
                    'weeklyPercentage'       => number_format(
                        $weekly['performancePercentage'],
                        2
                    ),
                    'weeklyHours'            => number_format(
                        $weekly['loggedHours'],
                        2
                    ),
                    'monthlyPercentage'      => number_format(
                        $monthly['performancePercentage'],
                        2
                    ),
                    'monthlyHours'           => number_format(
                        $monthly['loggedHours'],
                        2
                    ),
                    'weeklyPercentageClass'  => $weeklyPercentageClass,
                    'monthlyPercentageClass' => $monthlyPercentageClass
                )
            );
            $this->template->parse(
                'projectUsers',
                'projectUserBlock',
                true
            );
        }
        $this->template->parse(
            'CONTENTS',
            'DashboardAllUsersPerformanceReport',
            true
        );

    }

    function displayUserPerformanceReport()
    {
        $this->setTemplateFiles(
            'DashboardUserPerformanceReport',
            'DashboardUserPerformanceReport.inc'
        );
        $teamLevel           = $this->buUser->getLevelByUserID($this->userID);
        $targetLogPercentage = 0;
        switch ($teamLevel) {
            case 1:
                $targetLogPercentage = $this->dsHeader->getValue(DBEHeader::hdTeamTargetLogPercentage);
                break;
            case 2:
                $targetLogPercentage = $this->dsHeader->getValue(DBEHeader::esTeamTargetLogPercentage);
                break;
            case 3:
                $targetLogPercentage = $this->dsHeader->getValue(DBEHeader::smallProjectsTeamTargetLogPercentage);
                break;
            case 5:
                $targetLogPercentage = $this->dsHeader->getValue(DBEHeader::projectTeamTargetLogPercentage);
        }
        /* Extract data and build report */
        $weekly  = $this->buUser->getUserPerformanceByUser(
            $this->userID,
            7
        );
        $monthly = $this->buUser->getUserPerformanceByUser(
            $this->userID,
            31
        );
        if ($weekly['performancePercentage'] < $targetLogPercentage) {

            $this->template->set_var(
                'weeklyPercentageClass',
                'performance-warn'
            );
        }
        if ($monthly['performancePercentage'] < $targetLogPercentage) {

            $this->template->set_var(
                'monthlyPercentageClass',
                'performance-warn'
            );
        }
        $this->template->set_var(
            array(
                'targetPercentage'  => $targetLogPercentage,
                'weeklyPercentage'  => number_format(
                    $weekly['performancePercentage'],
                    2
                ),
                'weeklyHours'       => number_format(
                    $weekly['loggedHours'],
                    2
                ),
                'monthlyPercentage' => number_format(
                    $monthly['performancePercentage'],
                    2
                ),
                'monthlyHours'      => number_format(
                    $monthly['loggedHours'],
                    2
                ),
            )
        );
        $this->template->parse(
            'CONTENTS',
            'DashboardUserPerformanceReport',
            true
        );

    }

    private function displayCharts()
    {
        $this->setTemplateFiles(
            'HomeCharts',
            'HomeCharts'
        );
        $this->template->set_var(
            [
                "userLevel" => $teamLevel = $this->buUser->getLevelByUserID($this->userID),
                "userID"    => $this->buUser->dbeUser->getValue(DBEUser::userID),
                "isManager" => $this->buUser->isSdManager($this->userID) ? 'true' : 'false',
            ]
        );
        $this->template->parse(
            'CONTENTS',
            'HomeCharts',
            true
        );
    }

    private function displayChartsWithoutMenu()
    {
        $this->setHTMLFmt(CT_HTML_FMT_POPUP);
        $this->setTemplateFiles(
            'HomeCharts',
            'HomeCharts'
        );
        $this->template->set_var(
            [
                "userLevel" => $teamLevel = $this->buUser->getLevelByUserID($this->userID),
                "userID"    => $this->buUser->dbeUser->getValue(DBEUser::userID),
                "isManager" => $this->buUser->isSdManager($this->userID) ? 'true' : 'false',
            ]
        );
        $this->template->parse(
            'CONTENTS',
            'HomeCharts',
            true
        );
        $this->parsePage();
    }

    /**
     * Displays list of customers to review
     *
     * @throws Exception
     */
    function displayReviewList()
    {

        $this->setMethodName('displayReviewList');
        $this->setTemplateFiles(
            'CustomerReviewList',
            'CustomerReviewList.inc'
        );
        $this->template->set_block(
            'CustomerReviewList',
            'reviewBlock',
            'reviews'
        );
        $this->buCustomer = new BUCustomer($this);
        $dsCustomer       = new DataSet($this);
        if ($this->buCustomer->getDailyCallList($this, $dsCustomer)) {


            while ($dsCustomer->fetchNext()) {

                $linkURL = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => 'displayEditForm',
                        'customerID' => $dsCustomer->getValue(DBECustomer::customerID)
                    )
                );
                if ($dsCustomer->getValue(DBECustomer::reviewUserID)) {
                    $dsUser = new DataSet($this);
                    $this->buUser->getUserByID(
                        $dsCustomer->getValue(DBECustomer::reviewUserID),
                        $dsUser
                    );
                    $user = $dsUser->getValue(DBEUser::name);
                } else {
                    $user = false;
                }
                $this->template->set_var(
                    array(
                        'customerName' => $dsCustomer->getValue(DBECustomer::name),
                        'reviewDate'   => $dsCustomer->getValue(DBECustomer::reviewDate),
                        'reviewTime'   => $dsCustomer->getValue(DBECustomer::reviewTime),
                        'reviewAction' => $dsCustomer->getValue(DBECustomer::reviewAction),
                        'reviewUser'   => $user,
                        'linkURL'      => $linkURL
                    )
                );
                $this->template->parse(
                    'reviews',
                    'reviewBlock',
                    true
                );

            }
            $this->template->parse(
                'CONTENTS',
                'CustomerReviewList',
                true
            );

        }
    }

    /**
     * @throws Exception
     */
    function displayProjects()
    {
        $this->setTemplateFiles(
            'DashboardProjectList',
            'DashboardProjectList.inc'
        );
        $this->template->set_block(
            'DashboardProjectList',
            'projectBlock',
            'projects'
        );
        $buProject = new BUProject($this);
        $projects  = $buProject->getCurrentProjects();
        foreach ($projects as $project) {

            $hasProjectPlan           = !!$project['planFileName'];
            $projectPlanDownloadURL   = Controller::buildLink(
                '/Project.php',
                [
                    'action'    => CTProject::DOWNLOAD_PROJECT_PLAN,
                    'projectID' => $project['projectID']
                ]
            );
            $downloadProjectPlanClass = $hasProjectPlan ? null : 'class="redText"';
            $downloadProjectPlanURL   = $hasProjectPlan ? "href='$projectPlanDownloadURL' target='_blank' " : 'href="#"';
            $projectPlanLink          = "<a id='projectPlanLink' $downloadProjectPlanClass $downloadProjectPlanURL>Project Plan</a>";
            $editProjectLink          = Controller::buildLink(
                'Project.php',
                array(
                    'action'     => 'edit',
                    'projectID'  => $project['projectID'],
                    'backToHome' => true
                )
            );
            $lastUpdated              = 'No updates';
            $lastUpdatedURL           = Controller::buildLink(
                'Project.php',
                [
                    'action'  => 'lastUpdate',
                    'htmlFmt' => 'popup'
                ]
            );
            if ($project['createdBy']) {
                $editProjectLink = Controller::buildLink(
                    'Project.php',
                    array(
                        'action'     => 'edit',
                        'projectID'  => $project['projectID'],
                        'backToHome' => true
                    )
                );
                /** @noinspection JSUnresolvedFunction */
                /** @noinspection BadExpressionStatementJS */
                $lastUpdated = '<a href="#" onclick="showLastUpdatedPopup(' . $project['projectID'] . ')" >Status</a>';
            }
            $historyPopupURL = Controller::buildLink(
                'Project.php',
                array(
                    'action'  => 'historyPopup',
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
            $this->template->set_var(
                array(
                    'projectID'       => $project['projectID'],
                    'customerName'    => $project['customerName'],
                    'description'     => $project['description'],
                    'notes'           => $project['notes'],
                    'projectPlanLink' => $projectPlanLink,
                    'commenceDate'    => $project['commenceDate'] ? strftime(
                        "%d/%m/%Y",
                        strtotime($project['commenceDate'])
                    ) : null,
                    'urlEdit'         => $editProjectLink,
                    'engineerName'    => $project['engineerName'],
                    'lastUpdatePopup' => $lastUpdated,
                    'lastUpdateURL'   => $lastUpdatedURL,
                    'historyPopupURL' => $historyPopupURL
                )
            );
            $this->template->parse(
                'projects',
                'projectBlock',
                true
            );

        }
        $this->template->parse(
            'CONTENTS',
            'DashboardProjectList',
            true
        );

    }

    private function getUserPerformanceBetweenDatesController()
    {
        $data = $this->getJSONData();
        if (!@$data['startDate'] || !@$data['endDate'] || !@$data['userId']) {
            throw new JsonHttpException(400, "Start date, end date and user Id are required");
        }
        $startDateString = $data['startDate'];
        $startDate       = DateTime::createFromFormat(DATE_MYSQL_DATE, $startDateString);
        if (!$startDate) {
            throw new JsonHttpException(400, "Start date must have YYYY-MM-DD format");
        }
        $endDateString = $data['endDate'];
        $endDate       = DateTime::createFromFormat(DATE_MYSQL_DATE, $endDateString);
        if (!$endDate) {
            throw new JsonHttpException(400, "End date must have YYYY-MM-DD format");
        }
        return $this->buUser->getUserPerformanceByUserBetweenDates($data['userId'], $startDate, $endDate);
    }

    //Json data
    function getSalesFigures()
    {
        if (!$this->hasPermissions(ACCOUNTS_PERMISSION)) {
            http_response_code(403);
            return ["status" => 'error', "message" => "You are not allowed to access this resource"];
        }
        $result              = [];
        $dbeSalesOrderTotals = new DBESalesOrderTotals($this);
        $dbeSalesOrderTotals->getRow();
        $profit             = $dbeSalesOrderTotals->getValue(
                DBESalesOrderTotals::saleValue
            ) - $dbeSalesOrderTotals->getValue(
                DBESalesOrderTotals::costValue
            );
        $result['soSale']   = $dbeSalesOrderTotals->getValue(DBESalesOrderTotals::saleValue);
        $result['soCost']   = $dbeSalesOrderTotals->getValue(DBESalesOrderTotals::costValue);
        $result['soProfit'] = $profit;
        $profitTotal        = $profit;
        $saleTotal          = $dbeSalesOrderTotals->getValue(DBESalesOrderTotals::saleValue);
        $costTotal          = $dbeSalesOrderTotals->getValue(DBESalesOrderTotals::costValue);
        $dbeInvoiceTotals   = new DBEInvoiceTotals($this);
        $dbeInvoiceTotals->getCurrentMonthTotals();
        $profit                     = $dbeInvoiceTotals->getValue(
                DBEInvoiceTotals::saleValue
            ) - $dbeInvoiceTotals->getValue(
                DBEInvoiceTotals::costValue
            );
        $result['invPrintedSale']   = $dbeInvoiceTotals->getValue(DBEInvoiceTotals::saleValue);
        $result['invPrintedCost']   = $dbeInvoiceTotals->getValue(DBEInvoiceTotals::costValue);
        $result['invPrintedProfit'] = $profit;
        $profitTotal                += $profit;
        $saleTotal                  += $dbeInvoiceTotals->getValue(DBEInvoiceTotals::saleValue);
        $costTotal                  += $dbeInvoiceTotals->getValue(DBEInvoiceTotals::costValue);
        $dbeInvoiceTotals->getUnprintedTotals();
        $profit                       = $dbeInvoiceTotals->getValue(
                DBEInvoiceTotals::saleValue
            ) - $dbeInvoiceTotals->getValue(
                DBEInvoiceTotals::costValue
            );
        $result['invUnprintedSale']   = $dbeInvoiceTotals->getValue(DBEInvoiceTotals::saleValue);
        $result['invUnprintedCost']   = $dbeInvoiceTotals->getValue(DBEInvoiceTotals::costValue);
        $result['invUnprintedProfit'] = $profit;
        $profitTotal                  += $profit;
        $saleTotal                    += $dbeInvoiceTotals->getValue(DBEInvoiceTotals::saleValue);
        $costTotal                    += $dbeInvoiceTotals->getValue(DBEInvoiceTotals::costValue);
        $result['saleTotal']          = $saleTotal;
        $result['costTotal']          = $costTotal;
        $result['profitTotal']        = $profitTotal;
        return ["status" => 'ok', 'data' => $result];
    }

    function getTeamPerformance()
    {
        $data                                         = [];
        $buTeamPerformance                            = new BUTeamPerformance($this);
        $data['esTeamTargetSlaPercentage']            = $this->dsHeader->getValue(
            DBEHeader::esTeamTargetSlaPercentage
        );
        $data['esTeamTargetFixHours']                 = $this->dsHeader->getValue(DBEHeader::esTeamTargetFixHours);
        $data['smallProjectsTeamTargetSlaPercentage'] = $this->dsHeader->getValue(
            DBEHeader::smallProjectsTeamTargetSlaPercentage
        );
        $data['smallProjectsTeamTargetFixHours']      = $this->dsHeader->getValue(
            DBEHeader::smallProjectsTeamTargetFixHours
        );
        $data['hdTeamTargetSlaPercentage']            = $this->dsHeader->getValue(
            DBEHeader::hdTeamTargetSlaPercentage
        );
        $data['hdTeamTargetFixHours']                 = $this->dsHeader->getValue(DBEHeader::hdTeamTargetFixHours);
        $data['projectTeamTargetSlaPercentage']       = $this->dsHeader->getValue(
            DBEHeader::projectTeamTargetSlaPercentage
        );
        $data['projectTeamTargetFixHours']            = $this->dsHeader->getValue(
            DBEHeader::projectTeamTargetFixHours
        );
        /* Extract data and build report */
        $results = $buTeamPerformance->getQuarterlyRecordsByYear(date('Y'));
        foreach ($results as $result) {
            $esSLAPerformanceClass                = 'performance-warn';
            $esFixHoursClass                      = 'performance-warn';
            $hdSLAPerformanceClass                = 'performance-warn';
            $hdFixHoursClass                      = 'performance-warn';
            $smallProjectsTeamSLAPerformanceClass = 'performance-warn';
            $smallProjectsTeamFixHoursClass       = 'performance-warn';
            $projectTeamSLAPerformanceClass       = 'performance-warn';
            $projectTeamFixHoursClass             = 'performance-warn';
            if (round($result['esTeamActualSlaPercentage'], 1) >= round($result['esTeamTargetSlaPercentage'], 1)) {
                $esSLAPerformanceClass = 'performance-green';
            }
            if (round($result['hdTeamActualSlaPercentage'], 1) >= round($result['hdTeamTargetSlaPercentage'], 1)) {
                $hdSLAPerformanceClass = 'performance-green';
            }
            if (round(
                    $result['smallProjectsTeamActualSlaPercentage']
                ) >= round($result['smallProjectsTeamTargetSlaPercentage'])) {
                $smallProjectsTeamSLAPerformanceClass = 'performance-green';
            }
            if (round(
                    $result['projectTeamActualSlaPercentage']
                ) >= $result['projectTeamTargetSlaPercentage']) {
                $projectTeamSLAPerformanceClass = 'performance-green';
            }
            if ($result['esTeamActualFixHours'] <= $result['esTeamTargetFixHours']) {
                $esFixHoursClass = 'performance-green';
            }
            if ($result['hdTeamActualFixHours'] <= $result['hdTeamTargetFixHours']) {
                $hdFixHoursClass = 'performance-green';
            }
            if ($result['smallProjectsTeamActualFixHours'] <= $result['smallProjectsTeamTargetFixHours']) {
                $smallProjectsTeamFixHoursClass = 'performance-green';
            }
            if ($result['projectTeamActualFixHours'] <= $result['projectTeamTargetFixHours']) {
                $projectTeamFixHoursClass = 'performance-green';
            }
            $data = array_merge(
                $data,
                array(
                    'esTeamActualSlaPercentage' . $result['quarter']                      => number_format(
                        $result['esTeamActualSlaPercentage'],
                        1
                    ),
                    'esTeamActualFixHours' . $result['quarter']                           => number_format(
                        $result['esTeamActualFixHours'],
                        2
                    ),
                    'esTeamActualFixQty' . $result['quarter']                             => $result['esTeamActualFixQty'],
                    'smallProjectsTeamActualSlaPercentage' . $result['quarter']           => number_format(
                        $result['smallProjectsTeamActualSlaPercentage'],
                        0
                    ),
                    'smallProjectsTeamActualFixHours' . $result['quarter']                => number_format(
                        $result['smallProjectsTeamActualFixHours'],
                        2
                    ),
                    'smallProjectsTeamActualFixQty' . $result['quarter']                  => $result['smallProjectsTeamActualFixQty'],
                    'projectTeamActualSlaPercentage' . $result['quarter']                 => number_format(
                        $result['projectTeamActualSlaPercentage'],
                        1
                    ),
                    'projectTeamActualFixHours' . $result['quarter']                      => number_format(
                        $result['projectTeamActualFixHours'],
                        2
                    ),
                    'projectTeamActualFixQty' . $result['quarter']                        => $result['projectTeamActualFixQty'],
                    'hdTeamActualSlaPercentage' . $result['quarter']                      => number_format(
                        $result['hdTeamActualSlaPercentage'],
                        1
                    ),
                    'hdTeamActualFixHours' . $result['quarter']                           => number_format(
                        $result['hdTeamActualFixHours'],
                        2
                    ),
                    'hdTeamActualFixQty' . $result['quarter']                             => $result['hdTeamActualFixQty'],
                    'hdTeamActualSlaPercentage' . $result['quarter'] . 'Class'            => $hdSLAPerformanceClass,
                    'hdTeamActualFixHours' . $result['quarter'] . 'Class'                 => $hdFixHoursClass,
                    'esTeamActualSlaPercentage' . $result['quarter'] . 'Class'            => $esSLAPerformanceClass,
                    'esTeamActualFixHours' . $result['quarter'] . 'Class'                 => $esFixHoursClass,
                    'smallProjectsTeamActualSlaPercentage' . $result['quarter'] . 'Class' => $smallProjectsTeamSLAPerformanceClass,
                    'smallProjectsTeamActualFixHours' . $result['quarter'] . 'Class'      => $smallProjectsTeamFixHoursClass,
                    'projectTeamActualSlaPercentage' . $result['quarter'] . 'Class'       => $projectTeamSLAPerformanceClass,
                    'projectTeamActualFixHours' . $result['quarter'] . 'Class'            => $projectTeamFixHoursClass,
                )
            );
        }
        return $data;
    }

    function getAllUsersPerformance()
    {
        $data                                 = [];
        $hdTeamTargetLogPercentage            = $this->dsHeader->getValue(DBEHeader::hdTeamTargetLogPercentage);
        $esTeamTargetLogPercentage            = $this->dsHeader->getValue(DBEHeader::esTeamTargetLogPercentage);
        $smallProjectsTeamTargetLogPercentage = $this->dsHeader->getValue(
            DBEHeader::smallProjectsTeamTargetLogPercentage
        );
        $projectTeamTargetLogPercentage       = $this->dsHeader->getValue(DBEHeader::projectTeamTargetLogPercentage);
        $hdUsers                              = $this->buUser->getUsersByTeamLevel(1);
        $esUsers                              = $this->buUser->getUsersByTeamLevel(2);
        $imUsers                              = $this->buUser->getUsersByTeamLevel(3);
        $projectUsers                         = $this->buUser->getUsersByTeamLevel(5);
        /*
        Extract data and build report
        2 sections: HD users and ES users
        */
        $this->template->set_block(
            'DashboardAllUsersPerformanceReport',
            'hdUserBlock',
            'hdUsers'
        );
        foreach ($hdUsers as $user) {

            $weekly                = $this->buUser->getUserPerformanceByUser(
                $user['cns_consno'],
                7
            );
            $monthly               = $this->buUser->getUserPerformanceByUser(
                $user['cns_consno'],
                30
            );
            $weeklyPercentageClass = null;
            if ($weekly['performancePercentage'] < $hdTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-warn';
            }
            if ($weekly['performancePercentage'] >= $hdTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-green';
            }
            $monthlyPercentageClass = null;
            if ($monthly['performancePercentage'] < $hdTeamTargetLogPercentage) {
                $monthlyPercentageClass = 'performance-warn';
            }
            if ($monthly['performancePercentage'] >= $hdTeamTargetLogPercentage) {
                $monthlyPercentageClass = 'performance-green';
            }
            $data [] = array(
                'team'                   => "hd",
                'initials'               => $user['initials'],
                'targetPercentage'       => $hdTeamTargetLogPercentage,
                'weeklyPercentage'       => number_format(
                    $weekly['performancePercentage'],
                    2
                ),
                'weeklyHours'            => number_format(
                    $weekly['loggedHours'],
                    2
                ),
                'monthlyPercentage'      => number_format(
                    $monthly['performancePercentage'],
                    2
                ),
                'monthlyHours'           => number_format(
                    $monthly['loggedHours'],
                    2
                ),
                'weeklyPercentageClass'  => $weeklyPercentageClass,
                'monthlyPercentageClass' => $monthlyPercentageClass
            );

        }
        foreach ($esUsers as $user) {

            $weekly                = $this->buUser->getUserPerformanceByUser(
                $user['cns_consno'],
                7
            );
            $monthly               = $this->buUser->getUserPerformanceByUser(
                $user['cns_consno'],
                30
            );
            $weeklyPercentageClass = null;
            if ($weekly['performancePercentage'] < $esTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-warn';
            }
            if ($weekly['performancePercentage'] >= $esTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-green';
            }
            $monthlyPercentageClass = null;
            if ($monthly['performancePercentage'] < $esTeamTargetLogPercentage) {
                $monthlyPercentageClass = 'performance-warn';
            }
            if ($monthly['performancePercentage'] >= $esTeamTargetLogPercentage) {
                $monthlyPercentageClass = 'performance-green';
            }
            $data [] = array(
                'team'                   => 'es',
                'initials'               => $user['initials'],
                'targetPercentage'       => $esTeamTargetLogPercentage,
                'weeklyPercentage'       => number_format(
                    $weekly['performancePercentage'],
                    2
                ),
                'weeklyHours'            => number_format(
                    $weekly['loggedHours'],
                    2
                ),
                'monthlyPercentage'      => number_format(
                    $monthly['performancePercentage'],
                    2
                ),
                'monthlyHours'           => number_format(
                    $monthly['loggedHours'],
                    2
                ),
                'weeklyPercentageClass'  => $weeklyPercentageClass,
                'monthlyPercentageClass' => $monthlyPercentageClass
            );
        }
        foreach ($imUsers as $user) {

            $weekly                = $this->buUser->getUserPerformanceByUser(
                $user['cns_consno'],
                7
            );
            $monthly               = $this->buUser->getUserPerformanceByUser(
                $user['cns_consno'],
                30
            );
            $weeklyPercentageClass = null;
            if ($weekly['performancePercentage'] < $smallProjectsTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-warn';
            }
            if ($weekly['performancePercentage'] >= $smallProjectsTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-green';
            }
            $monthlyPercentageClass = null;
            if ($monthly['performancePercentage'] < $smallProjectsTeamTargetLogPercentage) {
                $monthlyPercentageClass = 'performance-warn';
            }
            if ($monthly['performancePercentage'] >= $smallProjectsTeamTargetLogPercentage) {
                $monthlyPercentageClass = 'performance-green';
            }
            $data [] = array(
                'team'                   => 'sp',
                'initials'               => $user['initials'],
                'targetPercentage'       => $smallProjectsTeamTargetLogPercentage,
                'weeklyPercentage'       => number_format(
                    $weekly['performancePercentage'],
                    2
                ),
                'weeklyHours'            => number_format(
                    $weekly['loggedHours'],
                    2
                ),
                'monthlyPercentage'      => number_format(
                    $monthly['performancePercentage'],
                    2
                ),
                'monthlyHours'           => number_format(
                    $monthly['loggedHours'],
                    2
                ),
                'weeklyPercentageClass'  => $weeklyPercentageClass,
                'monthlyPercentageClass' => $monthlyPercentageClass
            );
        }
        /*
        Projects team users
        */
        foreach ($projectUsers as $user) {

            $weekly                = $this->buUser->getUserPerformanceByUser(
                $user['cns_consno'],
                7
            );
            $monthly               = $this->buUser->getUserPerformanceByUser(
                $user['cns_consno'],
                30
            );
            $weeklyPercentageClass = null;
            if ($weekly['performancePercentage'] < $projectTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-warn';
            }
            if ($weekly['performancePercentage'] >= $projectTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-green';
            }
            $monthlyPercentageClass = null;
            if ($monthly['performancePercentage'] < $projectTeamTargetLogPercentage) {
                $monthlyPercentageClass = 'performance-warn';
            }
            if ($monthly['performancePercentage'] >= $projectTeamTargetLogPercentage) {
                $monthlyPercentageClass = 'performance-green';
            }
            $data [] = array(
                'team'                   => 'p',
                'initials'               => $user['initials'],
                'targetPercentage'       => $projectTeamTargetLogPercentage,
                'weeklyPercentage'       => number_format(
                    $weekly['performancePercentage'],
                    2
                ),
                'weeklyHours'            => number_format(
                    $weekly['loggedHours'],
                    2
                ),
                'monthlyPercentage'      => number_format(
                    $monthly['performancePercentage'],
                    2
                ),
                'monthlyHours'           => number_format(
                    $monthly['loggedHours'],
                    2
                ),
                'weeklyPercentageClass'  => $weeklyPercentageClass,
                'monthlyPercentageClass' => $monthlyPercentageClass
            );

        }
        return $data;
    }

    function getUserPerformance()
    {
        $data                = [];
        $teamLevel           = $this->buUser->getLevelByUserID($this->userID);
        $targetLogPercentage = 0;
        switch ($teamLevel) {
            case 1:
                $targetLogPercentage = $this->dsHeader->getValue(DBEHeader::hdTeamTargetLogPercentage);
                break;
            case 2:
                $targetLogPercentage = $this->dsHeader->getValue(DBEHeader::esTeamTargetLogPercentage);
                break;
            case 3:
                $targetLogPercentage = $this->dsHeader->getValue(DBEHeader::smallProjectsTeamTargetLogPercentage);
                break;
            case 5:
                $targetLogPercentage = $this->dsHeader->getValue(DBEHeader::projectTeamTargetLogPercentage);
        }
        /* Extract data and build report */
        $weekly  = $this->buUser->getUserPerformanceByUser(
            $this->userID,
            7
        );
        $monthly = $this->buUser->getUserPerformanceByUser(
            $this->userID,
            31
        );
        if ($weekly['performancePercentage'] < $targetLogPercentage) {
            $data['weeklyPercentageClass'] = 'performance-warn';
        }
        if ($monthly['performancePercentage'] < $targetLogPercentage) {
            $data['monthlyPercentageClass'] = 'performance-warn';
        }
        $data['targetPercentage']  = $targetLogPercentage;
        $data['weeklyPercentage']  = number_format($weekly['performancePercentage'], 2);
        $data['weeklyHours']       = number_format($weekly['loggedHours'], 2);
        $data['monthlyPercentage'] = number_format($monthly['performancePercentage'], 2);
        $data['monthlyHours']      = number_format($monthly['loggedHours'], 2);
        return $data;
    }

    function getDefaultLayout()
    {
        $result = DBConnect::fetchOne("select settings from cons_settings where consno=67 and type='home'");
        return ['status' => true, 'data' => json_decode($result['settings'])];
    }

    function setDefaultLayout()
    {
        $body    = json_decode(file_get_contents('php://input'));
        $default = DBConnect::fetchOne("select * from cons_settings where consno=67 and type='home'");
        if (isset($default['settings'])) //update row
        {
            return DBConnect::execute(
                "update cons_settings set settings=:settings where consno=67 and type='home'",
                ["settings" => $body->settings]
            );
        } else //insert new row
        {
            return DBConnect::execute(
                "insert into cons_settings(consno,type,settings) values(67,'home',:settings)",
                ['settings' => $body->settings]
            );
        }
    }

    function getFeedbackTeams()
    {
        $query = "SELECT       
                    COUNT(IF(f.value=1, 1, NULL)) happy,
                    COUNT(IF(f.value=2, 1, NULL)) average,
                    COUNT(IF(f.value=3, 1, NULL)) unhappy,
                    cons.teamID,
                    'Q1' quarter
                FROM `customerfeedback` f 
                    JOIN problem ON problem.`pro_problemno`=f.serviceRequestId
                    JOIN callactivity cal ON cal.caa_problemno=f.serviceRequestId
                    JOIN `consultant`  cons ON cons.`cns_consno`=cal.`caa_consno`
                WHERE cal.caa_callacttypeno=57
                    AND f.`createdAt` >= DATE_FORMAT(NOW(), '%Y-01-01') 
                    AND f.`createdAt` < DATE_FORMAT(NOW(), '%Y-04-01')
                    AND  cons.teamID<=5
                    AND problem.pro_custno <> 282
                GROUP BY   cons.teamID   
            UNION         
                SELECT       
                    COUNT(IF(f.value=1, 1, NULL)) happy,
                    COUNT(IF(f.value=2, 1, NULL)) average,
                    COUNT(IF(f.value=3, 1, NULL)) unhappy,
                    cons.teamID,
                    'Q2' quarter
                FROM `customerfeedback` f 
                    JOIN problem ON problem.`pro_problemno`=f.serviceRequestId
                    JOIN callactivity cal ON cal.caa_problemno=f.serviceRequestId
                    JOIN `consultant`  cons ON cons.`cns_consno`=cal.`caa_consno`
                WHERE cal.caa_callacttypeno=57
                    AND f.`createdAt` >= DATE_FORMAT(NOW(), '%Y-04-01') 
                    AND f.`createdAt` < DATE_FORMAT(NOW(), '%Y-07-01')
                    AND  cons.teamID<=5
                    AND problem.pro_custno <> 282
                GROUP BY   cons.teamID      
            UNION         
                SELECT       
                    COUNT(IF(f.value=1, 1, NULL)) happy,
                    COUNT(IF(f.value=2, 1, NULL)) average,
                    COUNT(IF(f.value=3, 1, NULL)) unhappy,
                    cons.teamID,
                    'Q3' quarter
                FROM `customerfeedback` f 
                    JOIN problem ON problem.`pro_problemno`=f.serviceRequestId
                    JOIN callactivity cal ON cal.caa_problemno=f.serviceRequestId
                    JOIN `consultant`  cons ON cons.`cns_consno`=cal.`caa_consno`
                WHERE cal.caa_callacttypeno=57
                    AND f.`createdAt` >= DATE_FORMAT(NOW(), '%Y-07-01') 
                    AND f.`createdAt` < DATE_FORMAT(NOW(), '%Y-10-01')
                    AND  cons.teamID<=5
                    AND problem.pro_custno <> 282
                GROUP BY   cons.teamID   
            UNION         
                SELECT       
                    COUNT(IF(f.value=1, 1, NULL)) happy,
                    COUNT(IF(f.value=2, 1, NULL)) average,
                    COUNT(IF(f.value=3, 1, NULL)) unhappy,
                    cons.teamID,
                    'Q4' quarter
                FROM `customerfeedback` f 
                    JOIN problem ON problem.`pro_problemno`=f.serviceRequestId
                    JOIN callactivity cal ON cal.caa_problemno=f.serviceRequestId
                    JOIN `consultant`  cons ON cons.`cns_consno`=cal.`caa_consno`
                WHERE cal.caa_callacttypeno=57
                    AND f.`createdAt` >= DATE_FORMAT(NOW(), '%Y-10-01') 
                    AND f.`createdAt` <= DATE_FORMAT(NOW(), '%Y-12-31')
                    AND  cons.teamID<=5
                    AND problem.pro_custno <> 282
                GROUP BY   cons.teamID 
          ";
        return DBConnect::fetchAll($query, []);
    }

    private function getLoggedActivityByTimeBracket(int $team, ?DateTime $dateTime = null)
    {
        if (!$dateTime) {
            $dateTime = new DateTime();
        }
        $isStandardUser = false;
        if (!$this->buUser->isSdManager($this->userID)) {
            if ($this->buUser->getLevelByUserID($this->userID) <= 5) {
                $team           = $this->buUser->getLevelByUserID($this->userID);
                $isStandardUser = true;
            } else {
                return [];
            }
        }
        $dbeUser = $this->getDbeUser();
        $dbeUser->setValue(
            DBEUser::userID,
            $this->userID
        );
        global $db;
        $queryString = "SELECT
  caa_starttime AS startTime,
  caa_endtime AS endTime,
  `caa_consno` AS engineerId,
  consultant.cns_name AS engineerName
FROM
  callactivity
  LEFT JOIN consultant
    ON consultant.cns_consno = callactivity.caa_consno
  LEFT JOIN team
    ON consultant.`teamID` = team.`teamID`
WHERE callactivity.`caa_date` = ?
  AND callactivity.`caa_endtime`
  AND callactivity.`caa_consno` <> 67
  AND team.`level` = ?
  and callactivity.caa_callacttypeno not in (6,22)
  and consultant.excludeFromStatsFlag <> 'Y'
ORDER BY engineerName,
  startTime";
        $statement   = $db->preparedQuery(
            $queryString,
            [
                [
                    "type"  => "s",
                    "value" => $dateTime->format(DATE_MYSQL_DATE)
                ],
                [
                    "type"  => "i",
                    "value" => $team
                ],
            ]
        );
        $activities  = $statement->fetch_all(MYSQLI_ASSOC);
        $data        = [];
        foreach ($activities as $activity) {
            $engineerName = $activity['engineerName'];
            if ($isStandardUser && $activity['engineerId'] !== $dbeUser->getValue(DBEUser::userID)) {
                continue;
            }
            if (!key_exists($engineerName, $data)) {
                $data[$engineerName] = [
                    "engineerId"   => $activity['engineerId'],
                    "engineerName" => $activity["engineerName"],
                    "dataPoints"   => array_fill(0, 24, 0)
                ];
            }
            foreach ($data[$engineerName]["dataPoints"] as $hour => $amount) {
                $thisHour  = DateTime::createFromFormat('H', $hour);
                $startTime = DateTime::createFromFormat('H:i', $activity["startTime"]);
                $endTime   = DateTime::createFromFormat('H:i', $activity['endTime']);
                $nextHour  = (clone($thisHour))->add(new DateInterval('PT1H'));
                if ($startTime > $nextHour || $endTime < $thisHour) {
                    continue;
                }
                if ($startTime < $thisHour) {
                    $startTime = $thisHour;
                }
                if ($endTime > $nextHour) {
                    $endTime = $nextHour;
                }
                $diff                                     = $startTime->diff($endTime);
                $differenceInMinutes                      = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
                $data[$engineerName]["dataPoints"][$hour] += $differenceInMinutes;
                if ($data[$engineerName]["dataPoints"][$hour] > 60) {
                    $data[$engineerName]["dataPoints"][$hour] = 60;
                }
            }
        }
        return $data;
    }
}
