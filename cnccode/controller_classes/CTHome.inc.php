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

class CTHome extends CTCNC
{
    const DetailedChartsAction = 'detailedCharts';
    const GetDetailedChartsDataAction = "getDetailedChartsData";

    private $dsHeader = '';
    private $buUser;

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
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {
            case 'updateServiceDetails':
                $this->updateServiceDetails();
                break;
            case 'lastWeekHelpDesk':

                $days = $_REQUEST['days'];
                if (!$days) {
                    $days = 30;
                }
                $team = 1;

                if (isset($_REQUEST['team'])) {
                    $team = $_REQUEST['team'];
                }


                echo json_encode(
                    $this->showLastWeekHelpDeskData(
                        $team,
                        $days
                    ),
                    JSON_NUMERIC_CHECK
                );
                break;

            case self::GetDetailedChartsDataAction:


                echo json_encode(
                    $this->getDetailedChartsData(
                        $_REQUEST['engineerID'],
                        $_REQUEST['startDate'],
                        $_REQUEST['endDate']
                    ),
                    JSON_NUMERIC_CHECK
                );
                break;
            case self::DetailedChartsAction:
                $this->showDetailCharts(
                    $_REQUEST['engineerID'],
                    $_REQUEST['startDate'],
                    $_REQUEST['endDate']
                );
                break;
            default:
                $this->display();
                break;
        }
    }

    function display()
    {
        /**
         * if user is only in the technical group then display the curent activity dash-board
         */
        if (
            $this->hasPermissions(PHPLIB_PERM_TECHNICAL) &&
            !$this->hasPermissions(PHPLIB_PERM_SUPERVISOR) &&
            !$this->hasPermissions(PHPLIB_PERM_MAINTENANCE) &&
            !$this->hasPermissions(PHPLIB_PERM_ACCOUNTS)
        ) {

            $urlNext =
                $this->buildLink(
                    'CurrentActivityReport.php',
                    array()
                );
            header('Location: ' . $urlNext);
            exit;

        }

        /*
        Otherwise display other sections based upon group membership
        */

        if ($this->hasPermissions(PHPLIB_PERM_ACCOUNTS)) {
            $this->displaySalesFigures();
        }

        $this->displayProjects();

        $this->displayFixedAndReopen();
        $this->displayFirstTimeFixFigures();
        $this->displayTeamPerformanceReport();

        if ($this->buUser->isSdManager($this->userID)) {
            $this->displayAllUsersPerformanceReport();
        } else {
            $this->displayUserPerformanceReport();
        }

        $this->displayCharts();


        $this->parsePage();
    }

    function displayFixedAndReopen()
    {
        $this->setTemplateFiles(
            'FixedAndReopened',
            'HomeFixedAndReopened.inc'
        );
        global $db;
        /** @var mysqli_result $query */
        $query = $db->query(
            "SELECT 
              SUM(fixer.`teamID` = 1) AS hdFixed,
              SUM(fixer.teamID	= 2) AS escFixed,
              SUM(fixer.teamID = 4) AS imtFixed ,
              SUM(fixer.`teamID` IN (1,2,4)) AS totalFixed
            FROM
              problem 
              LEFT JOIN consultant fixer 
                ON problem.`pro_fixed_consno` = fixer.`cns_consno` 
            WHERE DATE(problem.`pro_fixed_date`) = CURRENT_DATE 
              AND pro_status = 'F' 
              AND problem.`pro_custno` <> 282
              AND fixer.`cns_consno` <> 67
              GROUP BY DATE(problem.pro_fixed_date)"
        );

        $result = $query->fetch_assoc();
        $this->template->set_var(
            array(
                "hdFixed"    => Controller::formatNumber(
                    $result['hdFixed'],
                    0
                ),
                "escFixed"   => Controller::formatNumber(
                    $result['escFixed'],
                    0
                ),
                "imtFixed"   => Controller::formatNumber(
                    $result['imtFixed'],
                    0
                ),
                "totalFixed" => Controller::formatNumber(
                    $result['totalFixed'],
                    0
                ),
            )
        );

        $query = $db->query(
            "SELECT 
              SUM(teamID = 1) AS hdReopened,
              SUM(teamID = 2) AS escReopened,
              SUM(teamID = 4) AS imtReopened,
              SUM(teamID IN (1, 2, 4)) AS totalReopened
            FROM
              (SELECT 
                pro_problemno,
                reopener.teamID,
                MAX(fixedActivity.created) 
              FROM
                problem 
                JOIN callactivity fixedActivity 
                  ON fixedActivity.caa_problemno = problem.pro_problemno 
                  AND fixedActivity.caa_callacttypeno = 57 
                JOIN consultant reopener 
                  ON fixedActivity.`caa_consno` = reopener.`cns_consno` 
              WHERE problem.`pro_custno` <> 282 
                AND problem.`pro_reopened_flag` = 'Y' 
                AND reopener.`cns_consno` <> 67 
                AND problem.pro_reopened_date = CURRENT_DATE 
              GROUP BY pro_problemno) test "
        );

        $result = $query->fetch_assoc();
        $this->template->set_var(
            array(
                "hdReopened"    => Controller::formatNumber(
                    $result['hdReopened'],
                    0
                ),
                "escReopened"   => Controller::formatNumber(
                    $result['escReopened'],
                    0
                ),
                "imtReopened"   => Controller::formatNumber(
                    $result['imtReopened'],
                    0
                ),
                "totalReopened" => Controller::formatNumber(
                    $result['totalReopened'],
                    0
                ),
            )
        );

        $this->template->parse(
            'CONTENTS',
            'FixedAndReopened',
            true
        );
    }

    function displaySalesFigures()
    {
        $this->setTemplateFiles(
            'SalesFigures',
            'SalesFigures.inc'
        );

        $dbeSalesOrderTotals = new DBESalesOrderTotals($this);

        $dbeSalesOrderTotals->getRow();
        $profit = $dbeSalesOrderTotals->getValue('saleValue') - $dbeSalesOrderTotals->getValue('costValue');
        $this->template->set_var(
            array(
                'soSale'   => Controller::formatNumber($dbeSalesOrderTotals->getValue('saleValue')),
                'soCost'   => Controller::formatNumber($dbeSalesOrderTotals->getValue('costValue')),
                'soProfit' => Controller::formatNumber($profit)
            )
        );
        $profitTotal = $profit;
        $saleTotal = $dbeSalesOrderTotals->getValue('saleValue');
        $costTotal = $dbeSalesOrderTotals->getValue('costValue');

        $dbeInvoiceTotals = new DBEInvoiceTotals($this);
        $dbeInvoiceTotals->getCurrentMonthTotals();
        $profit = $dbeInvoiceTotals->getValue('saleValue') - $dbeInvoiceTotals->getValue('costValue');
        $this->template->set_var(
            array(
                'invPrintedSale'   => Controller::formatNumber($dbeInvoiceTotals->getValue('saleValue')),
                'invPrintedCost'   => Controller::formatNumber($dbeInvoiceTotals->getValue('costValue')),
                'invPrintedProfit' => Controller::formatNumber($profit)
            )
        );
        $profitTotal += $profit;
        $saleTotal += $dbeInvoiceTotals->getValue('saleValue');
        $costTotal += $dbeInvoiceTotals->getValue('costValue');

        $dbeInvoiceTotals->getUnprintedTotals();
        $profit = $dbeInvoiceTotals->getValue('saleValue') - $dbeInvoiceTotals->getValue('costValue');
        $this->template->set_var(
            array(
                'invUnprintedSale'   => Controller::formatNumber($dbeInvoiceTotals->getValue('saleValue')),
                'invUnprintedCost'   => Controller::formatNumber($dbeInvoiceTotals->getValue('costValue')),
                'invUnprintedProfit' => Controller::formatNumber($profit)
            )
        );
        $profitTotal += $profit;
        $saleTotal += $dbeInvoiceTotals->getValue('saleValue');
        $costTotal += $dbeInvoiceTotals->getValue('costValue');

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
     * Displays list of customers to review
     *
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

        if ($this->buCustomer->getDailyCallList($dsCustomer)) {


            while ($dsCustomer->fetchNext()) {

                $linkURL =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => 'displayEditForm',
                            'customerID' => $dsCustomer->getValue(DBECustomer::customerID)
                        )
                    );

                if ($dsCustomer->getValue(DBECustomer::reviewUserID)) {
                    $this->buUser->getUserByID(
                        $dsCustomer->getValue(DBECustomer::reviewUserID),
                        $dsUser
                    );
                    $user = $dsUser->getValue('name');
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

            $editProjectLink =
                $this->buildLink(
                    'Project.php',
                    array(
                        'action'     => 'edit',
                        'projectID'  => $project['projectID'],
                        'backToHome' => true
                    )
                );


            $this->template->set_var(
                array(
                    'projectID'    => $project['projectID'],
                    'customerName' => $project['customerName'],
                    'description'  => $project['description'],
                    'notes'        => $project['notes'],
                    'startDate'    => strftime(
                        "%d/%m/%Y",
                        strtotime($project['startDate'])
                    ),
                    'expiryDate'   => strftime(
                        "%d/%m/%Y",
                        strtotime($project['expiryDate'])
                    ),
                    'urlEdit'      => $editProjectLink
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

    } // end display projects

    function displayTeamPerformanceReport()
    {
        $this->setTemplateFiles(
            'DashboardTeamPerformanceReport',
            'DashboardTeamPerformanceReport.inc'
        );

        $buTeamPerformance = new BUTeamPerformance($this);

        $this->template->set_var(
            array(
                'esTeamTargetSlaPercentage' => $this->dsHeader->getValue('esTeamTargetSlaPercentage'),
                'esTeamTargetFixHours'      => $this->dsHeader->getValue('esTeamTargetFixHours'),
                'esTeamTargetFixQty'        => $this->dsHeader->getValue('esTeamTargetFixQtyPerMonth') * 3,

                'imTeamTargetSlaPercentage' => $this->dsHeader->getValue('imTeamTargetSlaPercentage'),
                'imTeamTargetFixHours'      => $this->dsHeader->getValue('imTeamTargetFixHours'),
                'imTeamTargetFixQty'        => $this->dsHeader->getValue('imTeamTargetFixQtyPerMonth') * 3,

                'hdTeamTargetSlaPercentage' => $this->dsHeader->getValue('hdTeamTargetSlaPercentage'),
                'hdTeamTargetFixHours'      => $this->dsHeader->getValue('hdTeamTargetFixHours'),
                'hdTeamTargetFixQty'        => $this->dsHeader->getValue('hdTeamTargetFixQtyPerMonth') * 3
            )
        );

        /* Extract data and build report */
        $results = $buTeamPerformance->getQuarterlyRecordsByYear(date('Y'));

        foreach ($results as $result) {

            if (round($result['esTeamActualSlaPercentage']) < $result['esTeamTargetSlaPercentage']) {

                $this->template->set_var(
                    'esTeamActualSlaPercentage' . $result['quarter'] . 'Class',
                    'performance-warn'
                );
            }

            if (round($result['hdTeamActualSlaPercentage']) < $result['hdTeamTargetSlaPercentage']) {

                $this->template->set_var(
                    'hdTeamActualSlaPercentage' . $result['quarter'] . 'Class',
                    'performance-warn'
                );
            }

            if (round($result['imTeamActualSlaPercentage']) < $result['imTeamTargetSlaPercentage']) {

                $this->template->set_var(
                    'imTeamActualSlaPercentage' . $result['quarter'] . 'Class',
                    'performance-warn'
                );
            }

            if ($result['esTeamActualFixQty'] < $result['esTeamTargetFixQty']) {

                $this->template->set_var(
                    'esTeamActualFixQty' . $result['quarter'] . 'Class',
                    'performance-warn'
                );
            }

            if ($result['hdTeamActualFixQty'] < $result['hdTeamTargetFixQty']) {

                $this->template->set_var(
                    'hdTeamActualFixQty' . $result['quarter'] . 'Class',
                    'performance-warn'
                );
            }

            if ($result['imTeamActualFixQty'] < $result['imTeamTargetFixQty']) {

                $this->template->set_var(
                    'imTeamActualFixQty' . $result['quarter'] . 'Class',
                    'performance-warn'
                );
            }

            if ($result['esTeamActualFixHours'] > $result['esTeamTargetFixHours']) {

                $this->template->set_var(
                    'esTeamActualFixHours' . $result['quarter'] . 'Class',
                    'performance-warn'
                );
            }

            if ($result['hdTeamActualFixHours'] > round(
                    $result['hdTeamTargetFixHours'],
                    0
                )) {

                $this->template->set_var(
                    'hdTeamActualFixHours' . $result['quarter'] . 'Class',
                    'performance-warn'
                );
            }

            if ($result['imTeamActualFixHours'] > $result['imTeamTargetFixHours']) {

                $this->template->set_var(
                    'imTeamActualFixHours' . $result['quarter'] . 'Class',
                    'performance-warn'
                );
            }

            $this->template->set_var(
                array(
                    'esTeamActualSlaPercentage' . $result['quarter'] => number_format(
                        $result['esTeamActualSlaPercentage'],
                        0
                    ),

                    'esTeamActualFixHours' . $result['quarter'] => number_format(
                        $result['esTeamActualFixHours'],
                        2
                    ),

                    'esTeamActualFixQty' . $result['quarter'] => $result['esTeamActualFixQty'],

                    'imTeamActualSlaPercentage' . $result['quarter'] => number_format(
                        $result['imTeamActualSlaPercentage'],
                        0
                    ),

                    'imTeamActualFixHours' . $result['quarter'] => number_format(
                        $result['imTeamActualFixHours'],
                        2
                    ),

                    'imTeamActualFixQty' . $result['quarter'] => $result['imTeamActualFixQty'],

                    'hdTeamActualSlaPercentage' . $result['quarter'] => number_format(
                        $result['hdTeamActualSlaPercentage'],
                        0
                    ),

                    'hdTeamActualFixHours' . $result['quarter'] => number_format(
                        $result['hdTeamActualFixHours'],
                        2
                    ),
                    'hdTeamActualFixQty' . $result['quarter']   => $result['hdTeamActualFixQty']
                )
            );

        }

        $this->template->parse(
            'CONTENTS',
            'DashboardTeamPerformanceReport',
            true
        );

    } // end displayTeamPerformanceReport

    function displayUserPerformanceReport()
    {
        $this->setTemplateFiles(
            'DashboardUserPerformanceReport',
            'DashboardUserPerformanceReport.inc'
        );

        $teamLevel = $this->buUser->getLevelByUserID($this->userID);

        if ($teamLevel == 1) {
            $targetLogPercentage = $this->dsHeader->getValue('hdTeamTargetLogPercentage');
        } else {
            $targetLogPercentage = $this->dsHeader->getValue('esTeamTargetLogPercentage');

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

    } // end displayUserLoggingPerformanceReport

    function displayAllUsersPerformanceReport()
    {
        $this->setTemplateFiles(
            'DashboardAllUsersPerformanceReport',
            'DashboardAllUsersPerformanceReport.inc'
        );

        $hdTeamTargetLogPercentage = $this->dsHeader->getValue('hdTeamTargetLogPercentage');

        $esTeamTargetLogPercentage = $this->dsHeader->getValue('esTeamTargetLogPercentage');

        $imTeamTargetLogPercentage = $this->dsHeader->getValue('imTeamTargetLogPercentage');

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

            if ($weekly['performancePercentage'] < $hdTeamTargetLogPercentage) {

                $weeklyPercentageClass = 'performance-warn';
            } else {
                $weeklyPercentageClass = '';
            }

            if ($monthly['performancePercentage'] < $hdTeamTargetLogPercentage) {

                $monthlyPercentageClass = 'performance-warn';
            } else {
                $monthlyPercentageClass = '';
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

            if ($weekly['performancePercentage'] < $esTeamTargetLogPercentage) {

                $weeklyPercentageClass = 'performance-warn';
            } else {
                $weeklyPercentageClass = '';
            }

            if ($monthly['performancePercentage'] < $esTeamTargetLogPercentage) {

                $monthlyPercentageClass = 'performance-warn';
            } else {
                $monthlyPercentageClass = '';
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

            if ($weekly['performancePercentage'] < $imTeamTargetLogPercentage) {

                $weeklyPercentageClass = 'performance-warn';
            } else {
                $weeklyPercentageClass = '';
            }

            if ($monthly['performancePercentage'] < $imTeamTargetLogPercentage) {

                $monthlyPercentageClass = 'performance-warn';
            } else {
                $monthlyPercentageClass = '';
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

    private function showLastWeekHelpDeskData($team,
                                              $days
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
            'userID',
            $this->userID
        );
        $dbeUser->getRow();

        $target = null;
        switch ($team) {
            case 1:
                $target = $this->dsHeader->getValue(DBEHeader::hdTeamTargetLogPercentage);
                break;
            case 2:
                $target = $this->dsHeader->getValue(DBEHeader::esTeamTargetLogPercentage);
                break;
            case 3:
                $target = $this->dsHeader->getValue(DBEHeader::imTeamTargetLogPercentage);
                break;
            default:
                throw new Exception('Team not valid');
        }


        $graphs = [];

        $dataStructure = [
            "cols"       => [
                ["id" => "dates", "label" => "Dates", "type" => 'date'],
            ],
            "rows"       => [

            ],
            "dataPoints" => [

            ],
            "userName"   => null
        ];


        $results = $this->buUser->teamMembersPerformanceData(
            $team,
            $days,
            $this->buUser->isSdManager($this->userID)
        );

        foreach ($results as $result) {
            if ($isStandardUser && $result['userID'] !== $this->dbeUser->getValue(DBEUser::userID)) {
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
                        ["v" => (new \DateTime($result['loggedDate']))->format(DATE_ISO8601)],
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
                "dataFetchUrl" => $this->buildLink(
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

    private function displayFirstTimeFixFigures()
    {
        $this->setTemplateFiles(
            'firstTimeFigures',
            'FirstTimeFigures'
        );

        $this->template->set_block(
            'firstTimeFigures',
            'firstTimeFixBlock',
            'figures'
        );

        global $db;

        $result = $db->query(
            "SELECT 
  CONCAT(
    engineer.`firstName`,
    ' ',
    engineer.`lastName`
  ) AS name,
  SUM(
    COALESCE(
      (SELECT 
        1 
      FROM
        callactivity 
      WHERE callactivity.caa_problemno = problem.pro_problemno 
        AND callactivity.caa_callacttypeno = 8 
        AND TIME_TO_SEC(
          TIMEDIFF(
            callactivity.caa_starttime,
            initial.caa_endtime
          )
        ) <= (5 * 60) 
        AND callactivity.`caa_consno` = engineer.`cns_consno`),
      0
    )
  ) AS attemptedFirstTimeFix,
  SUM(
    COALESCE(
      (SELECT 
        1 
      FROM
        problem test 
        JOIN callactivity initial 
          ON initial.caa_problemno = test.pro_problemno 
          AND initial.caa_callacttypeno = 51 
        JOIN callactivity remoteSupport 
          ON remoteSupport.caa_problemno = test.pro_problemno 
          AND remoteSupport.caa_callacttypeno = 8 
        JOIN callactivity fixedActivity 
          ON fixedActivity.caa_problemno = test.pro_problemno 
          AND fixedActivity.caa_callacttypeno = 57 
      WHERE test.pro_problemno = problem.`pro_problemno` 
        AND remoteSupport.caa_consno = engineer.`cns_consno` 
        AND fixedActivity.caa_consno = engineer.`cns_consno` 
        AND TIME_TO_SEC(
          TIMEDIFF(
            remoteSupport.caa_starttime,
            initial.caa_endtime
          )
        ) <= (5 * 60) 
        AND TIME_TO_SEC(
          TIMEDIFF(
            fixedActivity.caa_starttime,
            remoteSupport.caa_endtime
          )
        ) <= (5 * 60)),
      0
    )
  ) AS firstTimeFix,
  SUM(1) AS totalRaised  
FROM
  problem 
  JOIN callactivity initial 
    ON initial.caa_problemno = problem.pro_problemno 
    AND initial.caa_callacttypeno = 51 
  JOIN consultant engineer 
    ON initial.`caa_consno` = engineer.`cns_consno` 
WHERE problem.`pro_custno` <> 282 
  AND initial.caa_date = CURRENT_DATE 
  AND engineer.`teamID` = 1 
GROUP BY engineer.`cns_consno`  order by engineer.firstName"
        );

        $totalRaised = 0;
        $totalAttempted = 0;
        $totalAchieved = 0;
        while ($row = $result->fetch_assoc()) {
            $this->template->set_var(
                [
                    'name'                  => $row['name'],
                    'firstTimeFix'          => $row['firstTimeFix'],
                    'attemptedFirstTimeFix' => $row['attemptedFirstTimeFix'],
                    'totalRaised'           => $row['totalRaised']
                ]
            );

            $totalRaised += $row['totalRaised'];
            $totalAttempted += $row['attemptedFirstTimeFix'];
            $totalAchieved += $row['firstTimeFix'];
            $this->template->parse(
                'figures',
                'firstTimeFixBlock',
                true
            );
        }

        $this->template->set_var(
            [
                'firstTimeFixAttemptedPct' => $totalRaised > 0 ? round(
                    ($totalAttempted / $totalRaised) * 100
                ) : 'N/A',
                'firstTimeFixAchievedPct'  => $totalRaised > 0 ? round(
                    ($totalAchieved / $totalRaised) * 100
                ) : 'N/A',
                'phonedThroughRequests'    => $totalRaised
            ]
        );

        $this->template->parse(
            'CONTENTS',
            'firstTimeFigures',
            true
        );


    }
}// end of class
?>