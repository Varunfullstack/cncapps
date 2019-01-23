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
    }

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
                "fetchDataURL" => $this->buildLink(
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

    private function getFixedAndReopenData()
    {
        global $db;
        $db->query("select fixedAndReopenData from homeData limit 1");

        $db->next_record(MYSQLI_ASSOC);

        return $db->Record['fixedAndReopenData'];
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

            $hasProjectPlan = !!$project['planFileName'];

            $projectPlanDownloadURL =
                $this->buildLink(
                    '/Project.php',
                    [
                        'action'    => CTProject::DOWNLOAD_PROJECT_PLAN,
                        'projectID' => $project['projectID']
                    ]
                );

            $downloadProjectPlanClass = $hasProjectPlan ? '' : 'class="redText"';
            $downloadProjectPlanURL = $hasProjectPlan ? "href='$projectPlanDownloadURL' target='_blank' " : 'href="#"';
            $projectPlanLink = "<a id='projectPlanLink' $downloadProjectPlanClass $downloadProjectPlanURL>Project Plan</a>";

            $editProjectLink =
                $this->buildLink(
                    'Project.php',
                    array(
                        'action'     => 'edit',
                        'projectID'  => $project['projectID'],
                        'backToHome' => true
                    )
                );

            $lastUpdated = 'No updates';

            $lastUpdatedURL =
                $this->buildLink(
                    'Project.php',
                    [
                        'action'  => 'lastUpdate',
                        'htmlFmt' => 'popup'
                    ]
                );

            if ($project['createdBy']) {
                $editProjectLink =
                    $this->buildLink(
                        'Project.php',
                        array(
                            'action'     => 'edit',
                            'projectID'  => $project['projectID'],
                            'backToHome' => true
                        )
                    );
                $lastUpdated = '<a href="#" onclick="showLastUpdatedPopup(' . $project['projectID'] . ')" >Status</a>';
            }

            $historyPopupURL = $this->buildLink(
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
                    ) : '',
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

            if (round($result['esTeamActualSlaPercentage']) == 100) {
                $this->template->setVar(
                    'esTeamActualSlaPercentage' . $result['quarter'] . 'Class',
                    'performance-green'
                );
            }

            if (round($result['hdTeamActualSlaPercentage']) < $result['hdTeamTargetSlaPercentage']) {

                $this->template->set_var(
                    'hdTeamActualSlaPercentage' . $result['quarter'] . 'Class',
                    'performance-warn'
                );
            }

            if (round($result['hdTeamActualSlaPercentage']) == 100) {
                $this->template->setVar(
                    'hdTeamActualSlaPercentage' . $result['quarter'] . 'Class',
                    'performance-green'
                );
            }

            if (round($result['imTeamActualSlaPercentage']) < $result['imTeamTargetSlaPercentage']) {

                $this->template->set_var(
                    'imTeamActualSlaPercentage' . $result['quarter'] . 'Class',
                    'performance-warn'
                );
            }


            if (round($result['imTeamActualSlaPercentage']) == 100) {
                $this->template->setVar(
                    'imTeamActualSlaPercentage' . $result['quarter'] . 'Class',
                    'performance-green'
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

            if ($result['esTeamTargetFixHours'] - $result['esTeamActualFixHours'] > 1) {

                $this->template->set_var(
                    'esTeamActualFixHours' . $result['quarter'] . 'Class',
                    'performance-green'
                );
            }

            if ($result['hdTeamActualFixHours'] > $result['hdTeamTargetFixHours']) {

                $this->template->set_var(
                    'hdTeamActualFixHours' . $result['quarter'] . 'Class',
                    'performance-warn'
                );
            }

            if ($result['hdTeamTargetFixHours'] - $result['hdTeamActualFixHours'] > 1) {

                $this->template->set_var(
                    'hdTeamActualFixHours' . $result['quarter'] . 'Class',
                    'performance-green'
                );
            }

            if ($result['imTeamActualFixHours'] > $result['imTeamTargetFixHours']) {

                $this->template->set_var(
                    'imTeamActualFixHours' . $result['quarter'] . 'Class',
                    'performance-warn'
                );
            }

            if ($result['imTeamTargetFixHours'] - $result['imTeamActualFixHours'] > 1) {

                $this->template->set_var(
                    'imTeamActualFixHours' . $result['quarter'] . 'Class',
                    'performance-green'
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

            $weeklyPercentageClass = '';

            if ($weekly['performancePercentage'] < $hdTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-warn';
            }

            if ($weekly['performancePercentage'] >= $hdTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-green';
            }

            $monthlyPercentageClass = '';

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

            $weeklyPercentageClass = '';
            if ($weekly['performancePercentage'] < $esTeamTargetLogPercentage) {

                $weeklyPercentageClass = 'performance-warn';
            }

            if ($weekly['performancePercentage'] >= $esTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-green';
            }

            $monthlyPercentageClass = '';

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


            $weeklyPercentageClass = '';

            if ($weekly['performancePercentage'] < $imTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-warn';
            }

            if ($weekly['performancePercentage'] >= $imTeamTargetLogPercentage) {
                $weeklyPercentageClass = 'performance-green';
            }

            $monthlyPercentageClass = '';
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

        $this->template->set_var(
            [
                "fetchDataURL" => $this->buildLink(
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

    private function getFirstTimeFixData()
    {
        global $db;
        $db->query("select firstTimeFix from homeData limit 1");

        $db->next_record(MYSQLI_ASSOC);

        return $db->Record['firstTimeFix'];
    }

    private function getUpcomingVisitsData()
    {
        global $db;
        $db->query("select upcomingVisitsData from homeData limit 1");

        $db->next_record(MYSQLI_ASSOC);

        return $db->Record['upcomingVisitsData'];
    }

    private function displayUpcomingVisits()
    {
        $this->setTemplateFiles(
            'upcomingVisits',
            'upcomingVisits'
        );

        $this->template->set_var(
            [
                "upcomingVisitsFetchDataURL" => $this->buildLink(
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
    }

}// end of class
?>