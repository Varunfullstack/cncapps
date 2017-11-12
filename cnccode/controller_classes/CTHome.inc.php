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
require_once($cfg['path_bu'] . '/BUStaffAvailable.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUProject.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUTeamPerformance.inc.php');

class CTHome extends CTCNC
{
    private $dsHeader = '';
    private $buUser;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($this->dsHeader);
        $this->buUser = new BUUser($this);

        var_dump($this->buUser);
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
        /*
                if ( $this->hasPermissions(PHPLIB_PERM_MAINTENANCE) ||  $this->hasPermissions(PHPLIB_PERM_SUPERVISOR )){

                    $this->displayStaffAvailability();
                }
        */

        $this->displayTeamPerformanceReport();

        if ($this->buUser->isSdManager($this->userID)) {
            $this->displayAllUsersPerformanceReport();

        } else {
            $this->displayUserPerformanceReport();
        }


        $this->parsePage();
    }

    function displaySalesFigures()
    {
        $this->setTemplateFiles('SalesFigures', 'SalesFigures.inc');

        $dbeSalesOrderTotals = new DBESalesOrderTotals($this);

        $dbeSalesOrderTotals->getRow();
        $profit = $dbeSalesOrderTotals->getValue('saleValue') - $dbeSalesOrderTotals->getValue('costValue');
        $this->template->set_var(
            array(
                'soSale' => Controller::formatNumber($dbeSalesOrderTotals->getValue('saleValue')),
                'soCost' => Controller::formatNumber($dbeSalesOrderTotals->getValue('costValue')),
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
                'invPrintedSale' => Controller::formatNumber($dbeInvoiceTotals->getValue('saleValue')),
                'invPrintedCost' => Controller::formatNumber($dbeInvoiceTotals->getValue('costValue')),
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
                'invUnprintedSale' => Controller::formatNumber($dbeInvoiceTotals->getValue('saleValue')),
                'invUnprintedCost' => Controller::formatNumber($dbeInvoiceTotals->getValue('costValue')),
                'invUnprintedProfit' => Controller::formatNumber($profit)
            )
        );
        $profitTotal += $profit;
        $saleTotal += $dbeInvoiceTotals->getValue('saleValue');
        $costTotal += $dbeInvoiceTotals->getValue('costValue');

        $this->template->set_var(
            array(
                'saleTotal' => Controller::formatNumber($saleTotal),
                'costTotal' => Controller::formatNumber($costTotal),
                'profitTotal' => Controller::formatNumber($profitTotal)
            )
        );

        $this->template->parse('CONTENTS', 'SalesFigures', true);


    }

    /**
     * Not called but left here for possible future use as per Adrian Cragg
     *
     */
    function displayStaffAvailability()
    {
        $this->setTemplateFiles(
            array('StaffAvailableList' => 'StaffAvailableList.inc')
        );

        $buStaffAvailable = new BUStaffAvailable($this);
        $buStaffAvailable->createRecordsForToday($dsStaffAvailable);

        $buStaffAvailable->getAllStaffAvailable($dsStaffAvailable);


        $urlUpdate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'updateServiceDetails'
                )
            );

        $this->template->set_var(
            array('urlCreate' => $urlCreate)
        );

        if ($dsStaffAvailable->rowCount() > 0) {

            $this->template->set_block('StaffAvailableList', 'staffAvailableBlock', 'staffAvailables');

            while ($dsStaffAvailable->fetchNext()) {

                $this->template->set_var(
                    array(
                        'staffAvailableID' => $dsStaffAvailable->getValue('staffAvailableID'),
                        'firstName' => Controller::htmlDisplayText($dsStaffAvailable->getValue('firstName')),
                        'lastName' => Controller::htmlDisplayText($dsStaffAvailable->getValue('lastName')),
                        'amChecked' => $dsStaffAvailable->getValue('am') > 0 ? CT_CHECKED : '',
                        'pmChecked' => $dsStaffAvailable->getValue('pm') > 0 ? CT_CHECKED : '',
                        'urlUpdate' => $urlUpdate
                    )
                );

                $this->template->parse('staffAvailables', 'staffAvailableBlock', true);

            }//while $dsStaffAvailable->fetchNext()

            $this->template->set_var(
                array(
                    'helpDeskProblems' => Controller::htmlInputText($this->dsHeader->getValue('helpDeskProblems'))
                )
            );
        }

        $this->template->parse('CONTENTS', 'StaffAvailableList', true);

    }

    /**
     * Update details
     *
     * The data comes from the form in an array
     *
     * array(
     *    staffavailableID => value
     *    am => value,
     *    pm =>value
     * )
     *
     * @access private
     */
    function updateServiceDetails()
    {
        $this->setMethodName('updateServiceDetails');

        $buStaffAvailable = new BUStaffAvailable($this);
        $buStaffAvailable->updateStaffAvailable($_REQUEST['staffAvailable']);

        $buHeader = new BUHeader($this);

        $buHeader->updateHelpDesk($_REQUEST['header']);

        $urlNext =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array()
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Displays list of customers to review
     *
     */
    function displayReviewList()
    {

        $this->setMethodName('displayReviewList');

        $this->setTemplateFiles('CustomerReviewList', 'CustomerReviewList.inc');

        $this->template->set_block('CustomerReviewList', 'reviewBlock', 'reviews');

        $this->buCustomer = new BUCustomer($this);

        if ($this->buCustomer->getDailyCallList($dsCustomer)) {


            while ($dsCustomer->fetchNext()) {

                $linkURL =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'displayEditForm',
                            'customerID' => $dsCustomer->getValue('customerID')
                        )
                    );

                if ($dsCustomer->getValue('reviewUserID')) {
                    $this->buUser->getUserByID($dsCustomer->getValue('reviewUserID'), $dsUser);
                    $user = $dsUser->getValue('name');
                } else {
                    $user = false;
                }

                $this->template->set_var(
                    array(
                        'customerName' => $dsCustomer->getValue('name'),
                        'reviewDate' => $dsCustomer->getValue('reviewDate'),
                        'reviewTime' => $dsCustomer->getValue('reviewTime'),
                        'reviewAction' => $dsCustomer->getValue('reviewAction'),
                        'reviewUser' => $user,
                        'linkURL' => $linkURL
                    )
                );

                $this->template->parse('reviews', 'reviewBlock', true);

            }

            $this->template->parse('CONTENTS', 'CustomerReviewList', true);

        }
    }

    function displayProjects()
    {
        $this->setTemplateFiles('DashboardProjectList', 'DashboardProjectList.inc');

        $this->template->set_block('DashboardProjectList', 'projectBlock', 'projects');

        $buProject = new BUProject($this);

        $projects = $buProject->getCurrentProjects();

        foreach ($projects as $project) {

            $editProjectLink =
                $this->buildLink('Project.php',
                    array(
                        'action' => 'edit',
                        'projectID' => $project['projectID'],
                        'backToHome' => true
                    )
                );


            $this->template->set_var(
                array(
                    'projectID' => $project['projectID'],
                    'customerName' => $project['customerName'],
                    'description' => $project['description'],
                    'notes' => $project['notes'],
                    'startDate' => strftime("%d/%m/%Y", strtotime($project['startDate'])),
                    'expiryDate' => strftime("%d/%m/%Y", strtotime($project['expiryDate'])),
                    'urlEdit' => $editProjectLink
                )
            );

            $this->template->parse('projects', 'projectBlock', true);

        }

        $this->template->parse('CONTENTS', 'DashboardProjectList', true);

    } // end display projects

    function displayTeamPerformanceReport()
    {
        $this->setTemplateFiles('DashboardTeamPerformanceReport', 'DashboardTeamPerformanceReport.inc');

        $buTeamPerformance = new BUTeamPerformance($this);

        $this->template->set_var(
            array(
                'esTeamTargetSlaPercentage' => $this->dsHeader->getValue('esTeamTargetSlaPercentage'),
                'esTeamTargetFixHours' => $this->dsHeader->getValue('esTeamTargetFixHours'),
                'esTeamTargetFixQty' => $this->dsHeader->getValue('esTeamTargetFixQtyPerMonth') * 3,

                'imTeamTargetSlaPercentage' => $this->dsHeader->getValue('imTeamTargetSlaPercentage'),
                'imTeamTargetFixHours' => $this->dsHeader->getValue('imTeamTargetFixHours'),
                'imTeamTargetFixQty' => $this->dsHeader->getValue('imTeamTargetFixQtyPerMonth') * 3,

                'hdTeamTargetSlaPercentage' => $this->dsHeader->getValue('hdTeamTargetSlaPercentage'),
                'hdTeamTargetFixHours' => $this->dsHeader->getValue('hdTeamTargetFixHours'),
                'hdTeamTargetFixQty' => $this->dsHeader->getValue('hdTeamTargetFixQtyPerMonth') * 3
            )
        );

        /* Extract data and build report */
        $results = $buTeamPerformance->getQuarterlyRecordsByYear(date('Y'));

        foreach ($results as $result) {

            if (round($result['esTeamActualSlaPercentage']) < $result['esTeamTargetSlaPercentage']) {

                $this->template->set_var('esTeamActualSlaPercentage' . $result['quarter'] . 'Class', 'performance-warn');
            }

            if (round($result['hdTeamActualSlaPercentage']) < $result['hdTeamTargetSlaPercentage']) {

                $this->template->set_var('hdTeamActualSlaPercentage' . $result['quarter'] . 'Class', 'performance-warn');
            }

            if (round($result['imTeamActualSlaPercentage']) < $result['imTeamTargetSlaPercentage']) {

                $this->template->set_var('imTeamActualSlaPercentage' . $result['quarter'] . 'Class', 'performance-warn');
            }

            if ($result['esTeamActualFixQty'] < $result['esTeamTargetFixQty']) {

                $this->template->set_var('esTeamActualFixQty' . $result['quarter'] . 'Class', 'performance-warn');
            }

            if ($result['hdTeamActualFixQty'] < $result['hdTeamTargetFixQty']) {

                $this->template->set_var('hdTeamActualFixQty' . $result['quarter'] . 'Class', 'performance-warn');
            }

            if ($result['imTeamActualFixQty'] < $result['imTeamTargetFixQty']) {

                $this->template->set_var('imTeamActualFixQty' . $result['quarter'] . 'Class', 'performance-warn');
            }

            if ($result['esTeamActualFixHours'] > $result['esTeamTargetFixHours']) {

                $this->template->set_var('esTeamActualFixHours' . $result['quarter'] . 'Class', 'performance-warn');
            }

            if ($result['hdTeamActualFixHours'] > round($result['hdTeamTargetFixHours'], 0)) {

                $this->template->set_var('hdTeamActualFixHours' . $result['quarter'] . 'Class', 'performance-warn');
            }

            if ($result['imTeamActualFixHours'] > $result['imTeamTargetFixHours']) {

                $this->template->set_var('imTeamActualFixHours' . $result['quarter'] . 'Class', 'performance-warn');
            }

            $this->template->set_var(
                array(
                    'esTeamActualSlaPercentage' . $result['quarter'] => number_format($result['esTeamActualSlaPercentage'], 0),

                    'esTeamActualFixHours' . $result['quarter'] => number_format($result['esTeamActualFixHours'], 2),

                    'esTeamActualFixQty' . $result['quarter'] => $result['esTeamActualFixQty'],

                    'imTeamActualSlaPercentage' . $result['quarter'] => number_format($result['imTeamActualSlaPercentage'], 0),

                    'imTeamActualFixHours' . $result['quarter'] => number_format($result['imTeamActualFixHours'], 2),

                    'imTeamActualFixQty' . $result['quarter'] => $result['imTeamActualFixQty'],

                    'hdTeamActualSlaPercentage' . $result['quarter'] => number_format($result['hdTeamActualSlaPercentage'], 0),

                    'hdTeamActualFixHours' . $result['quarter'] => number_format($result['hdTeamActualFixHours'], 2),
                    'hdTeamActualFixQty' . $result['quarter'] => $result['hdTeamActualFixQty']
                )
            );

        }

        $this->template->parse('CONTENTS', 'DashboardTeamPerformanceReport', true);

    } // end displayTeamPerformanceReport

    function displayUserPerformanceReport()
    {
        $this->setTemplateFiles('DashboardUserPerformanceReport', 'DashboardUserPerformanceReport.inc');

        $teamLevel = $this->buUser->getLevelByUserID($this->userID);

        if ($teamLevel == 1) {
            $targetLogPercentage = $this->dsHeader->getValue('hdTeamTargetLogPercentage');
        } else {
            $targetLogPercentage = $this->dsHeader->getValue('esTeamTargetLogPercentage');

        }

        /* Extract data and build report */
        $weekly = $this->buUser->getUserPerformanceByUser($this->userID, 7);

        $monthly = $this->buUser->getUserPerformanceByUser($this->userID, 31);

        if ($weekly['performancePercentage'] < $targetLogPercentage) {

            $this->template->set_var('weeklyPercentageClass', 'performance-warn');
        }

        if ($monthly['performancePercentage'] < $targetLogPercentage) {

            $this->template->set_var('monthlyPercentageClass', 'performance-warn');
        }

        $this->template->set_var(
            array(
                'targetPercentage' => $targetLogPercentage,

                'weeklyPercentage' => number_format($weekly['performancePercentage'], 2),

                'weeklyHours' => number_format($weekly['loggedHours'], 2),

                'monthlyPercentage' => number_format($monthly['performancePercentage'], 2),

                'monthlyHours' => number_format($monthly['loggedHours'], 2),
            )
        );

        $this->template->parse('CONTENTS', 'DashboardUserPerformanceReport', true);

    } // end displayUserLoggingPerformanceReport

    function displayAllUsersPerformanceReport()
    {
        $this->setTemplateFiles('DashboardAllUsersPerformanceReport', 'DashboardAllUsersPerformanceReport.inc');

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


        $this->template->set_block('DashboardAllUsersPerformanceReport', 'hdUserBlock', 'hdUsers');

        foreach ($hdUsers as $user) {

            $weekly = $this->buUser->getUserPerformanceByUser($user['cns_consno'], 7);

            $monthly = $this->buUser->getUserPerformanceByUser($user['cns_consno'], 30);

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

                    'weeklyPercentage' => number_format($weekly['performancePercentage'], 2),

                    'weeklyHours' => number_format($weekly['loggedHours'], 2),

                    'monthlyPercentage' => number_format($monthly['performancePercentage'], 2),

                    'monthlyHours' => number_format($monthly['loggedHours'], 2),

                    'weeklyPercentageClass' => $weeklyPercentageClass,

                    'monthlyPercentageClass' => $monthlyPercentageClass
                )
            );

            $this->template->parse('hdUsers', 'hdUserBlock', true);
        }

        $this->template->set_block('DashboardAllUsersPerformanceReport', 'esUserBlock', 'esUsers');

        foreach ($esUsers as $user) {

            $weekly = $this->buUser->getUserPerformanceByUser($user['cns_consno'], 7);

            $monthly = $this->buUser->getUserPerformanceByUser($user['cns_consno'], 30);

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

                    'weeklyPercentage' => number_format($weekly['performancePercentage'], 2),

                    'weeklyHours' => number_format($weekly['loggedHours'], 2),

                    'monthlyPercentage' => number_format($monthly['performancePercentage'], 2),

                    'monthlyHours' => number_format($monthly['loggedHours'], 2),

                    'weeklyPercentageClass' => $weeklyPercentageClass,

                    'monthlyPercentageClass' => $monthlyPercentageClass
                )
            );

            $this->template->parse('esUsers', 'esUserBlock', true);
        }
        /*
        Implementation team users
        */
        $this->template->set_block('DashboardAllUsersPerformanceReport', 'imUserBlock', 'imUsers');

        foreach ($imUsers as $user) {

            $weekly = $this->buUser->getUserPerformanceByUser($user['cns_consno'], 7);

            $monthly = $this->buUser->getUserPerformanceByUser($user['cns_consno'], 30);

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

                    'weeklyPercentage' => number_format($weekly['performancePercentage'], 2),

                    'weeklyHours' => number_format($weekly['loggedHours'], 2),

                    'monthlyPercentage' => number_format($monthly['performancePercentage'], 2),

                    'monthlyHours' => number_format($monthly['loggedHours'], 2),

                    'weeklyPercentageClass' => $weeklyPercentageClass,

                    'monthlyPercentageClass' => $monthlyPercentageClass
                )
            );

            $this->template->parse('imUsers', 'imUserBlock', true);
        }


        $this->template->parse('CONTENTS', 'DashboardAllUsersPerformanceReport', true);

    } // end displayUserLoggingPerformanceReport
}// end of class
?>