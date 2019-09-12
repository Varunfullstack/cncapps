<?php
/**
 * Home controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBESalesOrderTotals.inc.php');
require_once($cfg['path_dbe'] . '/DBEInvoiceTotals.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUProject.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUTeamPerformance.inc.php');
require_once($cfg['path_ct'] . '/CTProject.inc.php');


class CTHome extends CTCNC
{
    const DetailedChartsAction = 'detailedCharts';
    const GetDetailedChartsDataAction = "getDetailedChartsData";
    const getFirstTimeFixData = "getFirstTimeFixData";
    const getFixedAndReopenData = "getFixedAndReopenData";
    const getUpcomingVisitsData = "getUpcomingVisitsData";
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
        switch ($this->getAction()) {
            case 'lastWeekHelpDesk':
                $team = 1;
                if ($this->getParam('team')) {
                    $team = $this->getParam('team');
                }
                echo json_encode(
                    $this->showLastWeekHelpDeskData(
                        $team
                    ),
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
                echo $this->getFirstTimeFixData();
                break;

            case self::getFixedAndReopenData:
                echo $this->getFixedAndReopenData();
                break;

            case self::getUpcomingVisitsData:
                echo $this->getUpcomingVisitsData();
                break;
            default:
                $this->display();
                break;
        }
    }

    /**
     * @param $team
     * @return array
     * @throws Exception
     */
    private function showLastWeekHelpDeskData($team
    )
    {
        $isStandardUser = false;
        if (!$this->buUser->isSdManager($this->userID)) {
            if ($this->buUser->getLevelByUserID($this->userID) <= 3) {
                $team = $this->buUser->getLevelByUserID($this->userID);
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

        $graphs = [];

        $dataStructure = [
            "cols"       => [
                ["id" => "dates", "label" => "Dates", "type" => 'date',""],
            ],
            "rows"       => [

            ],
            "dataPoints" => [

            ],
            "userName"   => null
        ];


        $results = $this->buUser->teamMembersPerformanceData(
            $team,
            $this->buUser->isSdManager($this->userID)
        );

        foreach ($results as $result) {
            if ($isStandardUser && $result['userID'] != $this->dbeUser->getValue(DBEUser::userID)) {
                continue;
            }

            // if the user doesn't have a graph yet create it
            if (!isset($graphs[$result['userID']])) {
                $graphs[$result['userID']] = $dataStructure;
                $graphs[$result['userID']]['cols'][] = [
                    "id"    => $result['userID'],
                    "label" => $result['userLabel'],
                    'type'  => 'number'
                ];
                $graphs[$result['userID']]['userName'] = $result['userLabel'];
            }

            $cell = [
                "c" =>
                    [
                        ["v" => (new DateTime($result['loggedDate']))->format(DATE_ISO8601), "p" => ["style" => 'border: 1px solid green;']],
                        ["v" => $result['loggedHours']]
                    ]
            ];

            $graphs[$result['userID']]['rows'][] = $cell;
        }

        $toReturn = [];

        foreach ($graphs as $userID => $graph) {
            $toReturn[] = array_merge(
                ["userID" => $userID],
                $graph
            );
        }

        usort(
            $toReturn,
            function ($a,
                      $b
            ) {
                return strcmp(
                    $a['userName'],
                    $b['userName']
                );
            }
        );

        return $toReturn;
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
            $this->formError = "Engineer ID not given";
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
        $db->query("select upcomingVisitsData from homeData limit 1");

        $db->next_record(MYSQLI_ASSOC);

        return $db->Record['upcomingVisitsData'];
    } // end display projects

    /**
     * @throws Exception
     */
    function display()
    {
        /**
         * if user is only in the technical group then display the current activity dash-board
         */
        if (
            $this->hasPermissions(PHPLIB_PERM_TECHNICAL) &&
            !$this->hasPermissions(PHPLIB_PERM_SUPERVISOR) &&
            !$this->hasPermissions(PHPLIB_PERM_MAINTENANCE) &&
            !$this->hasPermissions(PHPLIB_PERM_ACCOUNTS)
        ) {

            $urlNext =
                Controller::buildLink(
                    'CurrentActivityReport.php',
                    array()
                );
            header('Location: ' . $urlNext);
            exit;

        }

        /*
        Otherwise display other sections based upon group membership
        */
        $this->displayUpcomingVisits();


        if ($this->hasPermissions(PHPLIB_PERM_ACCOUNTS)) {
            $this->displaySalesFigures();
        }

        $this->setTemplateFiles(
            'dashboardTest',
            'DashboardStats'
        );


        $firstTimeFixFigures = $this->displayFirstTimeFixFigures();
        $fixedReopen = $this->displayFixedAndReopen();
        $this->template->set_var(
            [
                "thing1" => $fixedReopen,
                "thing2" => $firstTimeFixFigures
            ]
        );
        $this->template->parse(
            'CONTENTS',
            'dashboardTest',
            true
        );


        $this->displayTeamPerformanceReport();

        if ($this->buUser->isSdManager($this->userID)) {
            $this->displayAllUsersPerformanceReport();
        } else {
            $this->displayUserPerformanceReport();
        }

        $this->displayCharts();


        $this->parsePage();
    } // end displayTeamPerformanceReport

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
    } // end displayUserLoggingPerformanceReport

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
        $profitTotal = $profit;
        $saleTotal = $dbeSalesOrderTotals->getValue(DBESalesOrderTotals::saleValue);
        $costTotal = $dbeSalesOrderTotals->getValue(DBESalesOrderTotals::costValue);

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
        $saleTotal += $dbeInvoiceTotals->getValue(DBEInvoiceTotals::saleValue);
        $costTotal += $dbeInvoiceTotals->getValue(DBEInvoiceTotals::costValue);

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
        $saleTotal += $dbeInvoiceTotals->getValue(DBEInvoiceTotals::saleValue);
        $costTotal += $dbeInvoiceTotals->getValue(DBEInvoiceTotals::costValue);

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


    }

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
            $GLOBALS ["cfg"] ["path_templates"],
            "remove"
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
                'esTeamTargetSlaPercentage' => $this->dsHeader->getValue(DBEHeader::esTeamTargetSlaPercentage),
                'esTeamTargetFixHours'      => $this->dsHeader->getValue(DBEHeader::esTeamTargetFixHours),

                'imTeamTargetSlaPercentage' => $this->dsHeader->getValue(DBEHeader::imTeamTargetSlaPercentage),
                'imTeamTargetFixHours'      => $this->dsHeader->getValue(DBEHeader::imTeamTargetFixHours),

                'hdTeamTargetSlaPercentage' => $this->dsHeader->getValue(DBEHeader::hdTeamTargetSlaPercentage),
                'hdTeamTargetFixHours'      => $this->dsHeader->getValue(DBEHeader::hdTeamTargetFixHours),

            )
        );

        /* Extract data and build report */
        $results = $buTeamPerformance->getQuarterlyRecordsByYear(date('Y'));
        foreach ($results as $result) {
            $esSLAPerformanceClass = 'performance-warn';
            $esFixHoursClass = 'performance-warn';


            $hdSLAPerformanceClass = 'performance-warn';
            $hdFixHoursClass = 'performance-warn';


            $imSLAPerformanceClass = 'performance-warn';
            $imFixHoursClass = 'performance-warn';

            if (round($result['esTeamActualSlaPercentage']) >= $result['esTeamTargetSlaPercentage']) {
                $esSLAPerformanceClass = 'performance-green';
            }

            if (round($result['hdTeamActualSlaPercentage']) >= $result['hdTeamTargetSlaPercentage']) {
                $hdSLAPerformanceClass = 'performance-green';
            }

            if (round($result['imTeamActualSlaPercentage']) >= $result['imTeamTargetSlaPercentage']) {
                $imSLAPerformanceClass = 'performance-green';
            }
            if ($result['esTeamActualFixHours'] <= $result['esTeamTargetFixHours']) {
                $esFixHoursClass = 'performance-green';
            }

            if ($result['hdTeamActualFixHours'] <= $result['hdTeamTargetFixHours']) {
                $hdFixHoursClass = 'performance-green';
            }

            if ($result['imTeamActualFixHours'] <= $result['imTeamTargetFixHours']) {
                $imFixHoursClass = 'performance-green';
            }

            $this->template->set_var(
                array(
                    'esTeamActualSlaPercentage' . $result['quarter']           => number_format(
                        $result['esTeamActualSlaPercentage'],
                        0
                    ),
                    'esTeamActualFixHours' . $result['quarter']                => number_format(
                        $result['esTeamActualFixHours'],
                        2
                    ),
                    'esTeamActualFixQty' . $result['quarter']                  => $result['esTeamActualFixQty'],
                    'imTeamActualSlaPercentage' . $result['quarter']           => number_format(
                        $result['imTeamActualSlaPercentage'],
                        0
                    ),
                    'imTeamActualFixHours' . $result['quarter']                => number_format(
                        $result['imTeamActualFixHours'],
                        2
                    ),
                    'imTeamActualFixQty' . $result['quarter']                  => $result['imTeamActualFixQty'],
                    'hdTeamActualSlaPercentage' . $result['quarter']           => number_format(
                        $result['hdTeamActualSlaPercentage'],
                        0
                    ),
                    'hdTeamActualFixHours' . $result['quarter']                => number_format(
                        $result['hdTeamActualFixHours'],
                        2
                    ),
                    'hdTeamActualFixQty' . $result['quarter']                  => $result['hdTeamActualFixQty'],
                    'hdTeamActualSlaPercentage' . $result['quarter'] . 'Class' => $hdSLAPerformanceClass,
                    'hdTeamActualFixHours' . $result['quarter'] . 'Class'      => $hdFixHoursClass,
                    'esTeamActualSlaPercentage' . $result['quarter'] . 'Class' => $esSLAPerformanceClass,
                    'esTeamActualFixHours' . $result['quarter'] . 'Class'      => $esFixHoursClass,
                    'imTeamActualSlaPercentage' . $result['quarter'] . 'Class' => $imSLAPerformanceClass,
                    'imTeamActualFixHours' . $result['quarter'] . 'Class'      => $imFixHoursClass,
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

        $hdTeamTargetLogPercentage = $this->dsHeader->getValue(DBEHeader::hdTeamTargetLogPercentage);

        $esTeamTargetLogPercentage = $this->dsHeader->getValue(DBEHeader::esTeamTargetLogPercentage);

        $imTeamTargetLogPercentage = $this->dsHeader->getValue(DBEHeader::imTeamTargetLogPercentage);

        $hdUsers = $this->buUser->getUsersByTeamLevel(1);

        $esUsers = $this->buUser->getUsersByTeamLevel(2);

        $imUsers = $this->buUser->getUsersByTeamLevel(3);

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

            $weekly = $this->buUser->getUserPerformanceByUser(
                $user['cns_consno'],
                7
            );

            $monthly = $this->buUser->getUserPerformanceByUser(
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
                    'initials' => $user['initials'],

                    'targetPercentage' => $hdTeamTargetLogPercentage,

                    'weeklyPercentage' => number_format(
                        $weekly['performancePercentage'],
                        2
                    ),

                    'weeklyHours' => number_format(
                        $weekly['loggedHours'],
                        2
                    ),

                    'monthlyPercentage' => number_format(
                        $monthly['performancePercentage'],
                        2
                    ),

                    'monthlyHours' => number_format(
                        $monthly['loggedHours'],
                        2
                    ),

                    'weeklyPercentageClass' => $weeklyPercentageClass,

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

            $weekly = $this->buUser->getUserPerformanceByUser(
                $user['cns_consno'],
                7
            );

            $monthly = $this->buUser->getUserPerformanceByUser(
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
                    'initials' => $user['initials'],

                    'targetPercentage' => $esTeamTargetLogPercentage,

                    'weeklyPercentage' => number_format(
                        $weekly['performancePercentage'],
                        2
                    ),

                    'weeklyHours' => number_format(
                        $weekly['loggedHours'],
                        2
                    ),

                    'monthlyPercentage' => number_format(
                        $monthly['performancePercentage'],
                        2
                    ),

                    'monthlyHours' => number_format(
                        $monthly['loggedHours'],
                        2
                    ),

                    'weeklyPercentageClass' => $weeklyPercentageClass,

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
        Implementation team users
        */
        $this->template->set_block(
            'DashboardAllUsersPerformanceReport',
            'imUserBlock',
            'imUsers'
        );

        foreach ($imUsers as $user) {

            $weekly = $this->buUser->getUserPerformanceByUser(
                $user['cns_consno'],
                7
            );

            $monthly = $this->buUser->getUserPerformanceByUser(
                $user['cns_consno'],
                30
            );


            $weeklyPercentageClass = null;

            if ($weekly['performancePercentage'] < $imTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-warn';
            }

            if ($weekly['performancePercentage'] >= $imTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-green';
            }

            $monthlyPercentageClass = null;
            if ($monthly['performancePercentage'] < $imTeamTargetLogPercentage) {
                $monthlyPercentageClass = 'performance-warn';
            }
            if ($monthly['performancePercentage'] >= $imTeamTargetLogPercentage) {
                $monthlyPercentageClass = 'performance-green';
            }

            $this->template->set_var(
                array(
                    'initials' => $user['initials'],

                    'targetPercentage' => $imTeamTargetLogPercentage,

                    'weeklyPercentage' => number_format(
                        $weekly['performancePercentage'],
                        2
                    ),

                    'weeklyHours' => number_format(
                        $weekly['loggedHours'],
                        2
                    ),

                    'monthlyPercentage' => number_format(
                        $monthly['performancePercentage'],
                        2
                    ),

                    'monthlyHours' => number_format(
                        $monthly['loggedHours'],
                        2
                    ),

                    'weeklyPercentageClass' => $weeklyPercentageClass,

                    'monthlyPercentageClass' => $monthlyPercentageClass
                )
            );

            $this->template->parse(
                'imUsers',
                'imUserBlock',
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

        $teamLevel = $this->buUser->getLevelByUserID($this->userID);
        $targetLogPercentage = 0;
        switch ($teamLevel) {
            case 1:
                $targetLogPercentage = $this->dsHeader->getValue(DBEHeader::hdTeamTargetLogPercentage);
                break;
            case 2:
                $targetLogPercentage = $this->dsHeader->getValue(DBEHeader::esTeamTargetLogPercentage);
                break;
            case 3:
                $targetLogPercentage = $this->dsHeader->getValue(DBEHeader::imTeamTargetLogPercentage);
                break;
        }

        /* Extract data and build report */
        $weekly = $this->buUser->getUserPerformanceByUser(
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
                'targetPercentage' => $targetLogPercentage,

                'weeklyPercentage' => number_format(
                    $weekly['performancePercentage'],
                    2
                ),

                'weeklyHours' => number_format(
                    $weekly['loggedHours'],
                    2
                ),

                'monthlyPercentage' => number_format(
                    $monthly['performancePercentage'],
                    2
                ),

                'monthlyHours' => number_format(
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
        $dsCustomer = new DataSet($this);
        if ($this->buCustomer->getDailyCallList($this, $dsCustomer)) {


            while ($dsCustomer->fetchNext()) {

                $linkURL =
                    Controller::buildLink(
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

        $projects = $buProject->getCurrentProjects();


        foreach ($projects as $project) {

            $hasProjectPlan = !!$project['planFileName'];

            $projectPlanDownloadURL =
                Controller::buildLink(
                    '/Project.php',
                    [
                        'action'    => CTProject::DOWNLOAD_PROJECT_PLAN,
                        'projectID' => $project['projectID']
                    ]
                );

            $downloadProjectPlanClass = $hasProjectPlan ? null : 'class="redText"';
            $downloadProjectPlanURL = $hasProjectPlan ? "href='$projectPlanDownloadURL' target='_blank' " : 'href="#"';
            $projectPlanLink = "<a id='projectPlanLink' $downloadProjectPlanClass $downloadProjectPlanURL>Project Plan</a>";

            $editProjectLink =
                Controller::buildLink(
                    'Project.php',
                    array(
                        'action'     => 'edit',
                        'projectID'  => $project['projectID'],
                        'backToHome' => true
                    )
                );

            $lastUpdated = 'No updates';

            $lastUpdatedURL =
                Controller::buildLink(
                    'Project.php',
                    [
                        'action'  => 'lastUpdate',
                        'htmlFmt' => 'popup'
                    ]
                );

            if ($project['createdBy']) {
                $editProjectLink =
                    Controller::buildLink(
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

}
