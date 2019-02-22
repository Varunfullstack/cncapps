<?php
/**
 * Daily Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUMonthlyReport.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');

// Actions

class CTMonthlyReport extends CTCNC
{
    var $buMonthlyReport = '';
    var $dsServiceDeskReport = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "maintenance",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->dsServiceDeskReport = new DSForm($this);
        $dbeServiceDeskReport = new dbeServiceDeskReport($this);
        $this->dsServiceDeskReport->copyColumnsFrom($dbeServiceDeskReport);
        $this->buMonthlyReport = new BUMonthlyReport($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
        /*
        In addition, only RH and GL are allowed to run this
        */
        if (
            $this->userID != USER_GL AND
            $this->userID != USER_GJ AND
            $this->userID != USER_RH AND
            $this->userID != 1              // Karim
        ) {
            $this->displayFatalError('You do not have the permissions required for the requested operation');

        }

        switch ($_REQUEST['action']) {
            case 'create':
            case 'edit':
                $this->edit();
                break;
            case 'update':
                $this->update();
                break;
            case 'displayList':
            default:
                $this->displayList();
                break;
        }
    }

    /**
     * Display list of MonthlyReports
     * @access private
     */
    function displayList()
    {
        $this->setMethodName('displayList');

        $this->setPageTitle('MonthlyReports');

        $this->setTemplateFiles(
            array('MonthlyReportList' => 'MonthlyReportList.inc')
        );

        $results = $this->buMonthlyReport->getAll();

        $this->template->set_block('MonthlyReportList', 'ReportBlock', 'reports');

        /*
        * First link is to create last month if not already created
        */
        $lastYearMonth = date('Ym', strtotime('-1 month'));

        if (!$this->buMonthlyReport->reportExistsByPeriod($lastYearMonth)) {

            $urlEdit =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'edit',
                        'yearMonth' => $lastYearMonth,
                        'serviceDeskReportID' => 0
                    )
                );

            $this->template->set_var(
                array(
                    'period' => Controller::htmlDisplayText($lastYearMonth),
                    'urlEdit' => $urlEdit
                )
            );


            $this->template->parse('reports', 'ReportBlock', true);

        }

        while ($row = $results->fetch_object()) {

            $urlEdit =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'edit',
                        'serviceDeskReportID' => $row->sdr_servicedeskreportno
                    )
                );
            $txtEdit = '[edit]';

            $this->template->set_var(
                array(
                    'period' => Controller::htmlDisplayText($row->sdr_year_month),
                    'urlEdit' => $urlEdit
                )
            );

            $this->template->parse('reports', 'ReportBlock', true);

        }

        $this->template->parse('CONTENTS', 'MonthlyReportList', true);
        $this->parsePage();
    }

    /**
     * Edit/Add Further Action
     * @access private
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsServiceDeskReport = &$this->dsServiceDeskReport; // ref to class var

        $baseDate = strtotime('-1 month');

        if ($_REQUEST['serviceDeskReportID']) { // view exitings

            $this->buMonthlyReport->getMonthlyReportByID(
                $_REQUEST['serviceDeskReportID'],
                $dsServiceDeskReport
            );

            $readonly = 'READONLY';
            $serviceDeskReportID = $_REQUEST['serviceDeskReportID'];
        } else {                                                                    // creating new
            $readonly = '';
            $dsServiceDeskReport->initialise();
            $dsServiceDeskReport->setValue('serviceDeskReportID', '0');
            $serviceDeskReportID = '0';
            $dsServiceDeskReport->setValue('yearMonth', date('Ym', $baseDate));
        }

        $yearMonth = $dsServiceDeskReport->getValue('yearMonth');

        $urlUpdate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'update'
                )
            );

        $this->setPageTitle('Monthly Report for ' . $yearMonth);
        $this->setTemplateFiles(
            array('MonthlyReportEdit' => 'MonthlyReportEdit.inc')
        );

        $monthTotal = $this->buMonthlyReport->getIncidentCount('M', $yearMonth);

        $monthSev1Total = $this->buMonthlyReport->getIncidentCount('M', $yearMonth, 1);
        $monthSev1Resp = $this->buMonthlyReport->getIncidentCount('M', $yearMonth, 1, 'R');
        $monthSev1RespPerc = $monthSev1Resp / $monthSev1Total * 100;
        $monthSev1Fix = $this->buMonthlyReport->getIncidentCount('M', $yearMonth, 1, 'F');
        $monthSev1FixPerc = $monthSev1Fix / $monthSev1Total * 100;

        $monthSev2Total = $this->buMonthlyReport->getIncidentCount('M', $yearMonth, 2);
        $monthSev2Resp = $this->buMonthlyReport->getIncidentCount('M', $yearMonth, 2, 'R');
        $monthSev2RespPerc = $monthSev2Resp / $monthSev2Total * 100;
        $monthSev2Fix = $this->buMonthlyReport->getIncidentCount('M', $yearMonth, 2, 'F');
        $monthSev2FixPerc = $monthSev2Fix / $monthSev2Total * 100;

        $monthSev3Total = $this->buMonthlyReport->getIncidentCount('M', $yearMonth, 3);
        $monthSev3Resp = $this->buMonthlyReport->getIncidentCount('M', $yearMonth, 3, 'R');
        $monthSev3RespPerc = $monthSev3Resp / $monthSev3Total * 100;
        $monthSev3Fix = $this->buMonthlyReport->getIncidentCount('M', $yearMonth, 3, 'F');
        $monthSev3FixPerc = $monthSev3Fix / $monthSev3Total * 100;

        $monthSev4Total = $this->buMonthlyReport->getIncidentCount('M', $yearMonth, 4);
        /**
         * if this period is same as report period then display quarterly figures
         *
         */
        $period = $this->getQuarter($baseDate);

        $lastQuarterTotal = $this->buMonthlyReport->getIncidentCount('Q', $period);

        $lastQuarterSev1Total = $this->buMonthlyReport->getIncidentCount('Q', $period, 1);
        $lastQuarterSev1Resp = $this->buMonthlyReport->getIncidentCount('Q', $period, 1, 'R');
        $lastQuarterSev1RespPerc = $lastQuarterSev1Resp / $lastQuarterSev1Total * 100;
        $lastQuarterSev1Fix = $this->buMonthlyReport->getIncidentCount('Q', $period, 1, 'F');
        $lastQuarterSev1FixPerc = $lastQuarterSev1Fix / $lastQuarterSev1Total * 100;

        $lastQuarterSev2Total = $this->buMonthlyReport->getIncidentCount('Q', $period, 2);
        $lastQuarterSev2Resp = $this->buMonthlyReport->getIncidentCount('Q', $period, 2, 'R');
        $lastQuarterSev2RespPerc = $lastQuarterSev2Resp / $lastQuarterSev2Total * 100;
        $lastQuarterSev2Fix = $this->buMonthlyReport->getIncidentCount('Q', $period, 2, 'F');
        $lastQuarterSev2FixPerc = $lastQuarterSev2Fix / $lastQuarterSev2Total * 100;

        $lastQuarterSev3Total = $this->buMonthlyReport->getIncidentCount('Q', $period, 3);
        $lastQuarterSev3Resp = $this->buMonthlyReport->getIncidentCount('Q', $period, 3, 'R');
        $lastQuarterSev3RespPerc = $lastQuarterSev3Resp / $lastQuarterSev3Total * 100;
        $lastQuarterSev3Fix = $this->buMonthlyReport->getIncidentCount('Q', $period, 3, 'F');
        $lastQuarterSev3FixPerc = $lastQuarterSev3Fix / $lastQuarterSev3Total * 100;

        $lastQuarterSev4Total = $this->buMonthlyReport->getIncidentCount('Q', $period, 4);

        $this->template->set_var(
            array(
                'readonly' => $readonly,
                'quarterStartDate' => Controller::dateYMDtoDMY($period['start']),
                'quarterEndDate' => Controller::dateYMDtoDMY($period['end']),
                'serviceDeskReportID' => $serviceDeskReportID,
                'yearMonth' => Controller::htmlDisplayText($dsServiceDeskReport->getValue('yearMonth')),
                'callsReceived' => Controller::htmlInputText($dsServiceDeskReport->getValue('callsReceived')),
                'callsReceivedMessage' => Controller::htmlDisplayText($dsServiceDeskReport->getMessage('callsReceived')),
                'callsOverflowed' => Controller::htmlInputText($dsServiceDeskReport->getValue('callsOverflowed')),
                'callsOverflowedMessage' => Controller::htmlDisplayText($dsServiceDeskReport->getMessage('callsOverflowed')),
                'callsHelpdesk' => Controller::htmlInputText($dsServiceDeskReport->getValue('callsHelpdesk')),
                'callsHelpdeskMessage' => Controller::htmlDisplayText($dsServiceDeskReport->getMessage('callsHelpdesk')),
                'callsAnswerSeconds' => Controller::htmlInputText($dsServiceDeskReport->getValue('callsAnswerSeconds')),
                'callsAnswerSecondsMessage' => Controller::htmlDisplayText($dsServiceDeskReport->getMessage('callsAnswerSeconds')),
                'callsAbandoned' => Controller::htmlInputText($dsServiceDeskReport->getValue('callsAbandoned')),
                'callsAbandonedMessage' => Controller::htmlDisplayText($dsServiceDeskReport->getMessage('callsAbandoned')),
                'meetingResults' => Controller::htmlTextArea($dsServiceDeskReport->getValue('meetingResults')),
                'staffIssues' => Controller::htmlTextArea($dsServiceDeskReport->getValue('staffIssues')),
                'staffHolidayDays' => Controller::htmlInputText($dsServiceDeskReport->getValue('staffHolidayDays')),
                'staffHolidayDaysMessage' => Controller::htmlDisplayText($dsServiceDeskReport->getMessage('staffHolidayDays')),
                'staffSickDays' => Controller::htmlInputText($dsServiceDeskReport->getValue('staffSickDays')),
                'staffSickDaysMessage' => Controller::htmlDisplayText($dsServiceDeskReport->getMessage('staffSickDays')),
                'training' => Controller::htmlTextArea($dsServiceDeskReport->getValue('training')),
                'anyOtherBusiness' => Controller::htmlTextArea($dsServiceDeskReport->getValue('anyOtherBusiness')),

                'monthSev1Total' => number_format($monthSev1Total, 0),
                'monthSev1Resp' => number_format($monthSev1Resp, 0),
                'monthSev1RespPerc' => number_format($monthSev1RespPerc, 0),
                'monthSev1Fix' => number_format($monthSev1Fix, 0),
                'monthSev1FixPerc' => number_format($monthSev1FixPerc, 0),

                'monthSev2Total' => number_format($monthSev2Total, 0),
                'monthSev2Resp' => number_format($monthSev2Resp, 0),
                'monthSev2RespPerc' => number_format($monthSev2RespPerc, 0),
                'monthSev2Fix' => number_format($monthSev2Fix, 0),
                'monthSev2FixPerc' => number_format($monthSev2FixPerc, 0),

                'monthSev3Total' => number_format($monthSev3Total, 0),
                'monthSev3Resp' => number_format($monthSev3Resp, 0),
                'monthSev3RespPerc' => number_format($monthSev3RespPerc, 0),
                'monthSev3Fix' => number_format($monthSev3Fix, 0),
                'monthSev3FixPerc' => number_format($monthSev3FixPerc, 0),

                'monthSev4Total' => number_format($monthSev4Total, 0),

                'lastQuarterSev1Total' => number_format($lastQuarterSev1Total, 0),
                'lastQuarterSev1Resp' => number_format($lastQuarterSev1Resp, 0),
                'lastQuarterSev1RespPerc' => number_format($lastQuarterSev1RespPerc, 0),
                'lastQuarterSev1Fix' => number_format($lastQuarterSev1Fix, 0),
                'lastQuarterSev1FixPerc' => number_format($lastQuarterSev1FixPerc, 0),

                'lastQuarterSev2Total' => number_format($lastQuarterSev2Total, 0),
                'lastQuarterSev2Resp' => number_format($lastQuarterSev2Resp, 0),
                'lastQuarterSev2RespPerc' => number_format($lastQuarterSev2RespPerc, 0),
                'lastQuarterSev2Fix' => number_format($lastQuarterSev2Fix, 0),
                'lastQuarterSev2FixPerc' => number_format($lastQuarterSev2FixPerc, 0),

                'lastQuarterSev3Total' => number_format($lastQuarterSev3Total, 0),
                'lastQuarterSev3Resp' => number_format($lastQuarterSev3Resp, 0),
                'lastQuarterSev3RespPerc' => number_format($lastQuarterSev3RespPerc, 0),
                'lastQuarterSev3Fix' => number_format($lastQuarterSev3Fix, 0),
                'lastQuarterSev3FixPerc' => number_format($lastQuarterSev3FixPerc, 0),

                'lastQuarterSev4Total' => number_format($lastQuarterSev4Total, 0),

                'urlUpdate' => $urlUpdate,
                'urlDisplayList' => $urlDisplayList
            )
        );

        /* breach exception */
        $results = $this->buMonthlyReport->getRequestsOutsideOla($yearMonth);

        $this->template->set_block('MonthlyReportEdit', 'breachBlock', 'breaches');

        while ($row = $results->fetch_object()) {

            $this->template->setVar(
                array(
                    'breachProblemID' => $row->pro_problemno,
                    'breachCustomer' => $row->cus_name,
                    'breachComment' => $row->pro_breach_comment
                )
            );

            $this->template->parse('breaches', 'breachBlock', true);

        }
        /* Manager comments */
        $results = $this->buMonthlyReport->getManagerComments($yearMonth);

        $this->template->set_block('MonthlyReportEdit', 'managerCommentBlock', 'managerComments');

        while ($row = $results->fetch_object()) {

            $this->template->setVar(
                array(
                    'commentProblemID' => $row->pro_problemno,
                    'commentCustomer' => $row->cus_name,
                    'commentComment' => $row->pro_manager_comment
                )
            );

            $this->template->parse('managerComments', 'managerCommentBlock', true);

        }
        /* root causes */
        $results = $this->buMonthlyReport->getRootCauses($yearMonth);

        $this->template->set_block('MonthlyReportEdit', 'rootCauseBlock', 'rootCauses');

        while ($row = $results->fetch_object()) {

            $this->template->setVar(
                array(
                    'rootCauseDescription' => $row->rtc_desc,
                    'rootCauseCount' => $row->count
                )
            );

            $this->template->parse('rootCauses', 'rootCauseBlock', true);

        }

        $this->template->parse('CONTENTS', 'MonthlyReportEdit', true);

        $this->parsePage();
    }// end function editFurther Action()

    /**
     * Returns an array with a start and end date for the current quarter from given date
     * eg. If given is 23 Feb 2009, returns $x['start'] = 1 Jan 2009, $x[end] = 31 mar 2009
     *
     * $date defaults to today
     *
     * Based upon code from PHP manual mkdate() contribution from p2409@hotmail.com 02-Aug-2009 03:49
     */
    function getQuarter($date = false)
    {

        if ($date) {
            $year = date('Y', $date);
            $month = date('m', $date);
        } else {
            $year = date("Y", mktime());
            $month = date("m", mktime());
        }

        // Formula to get a quarter in the year from a month
//    $startmth = $month - 3 - ( ( $month - 1) % 3 );
        $startmth = $month - (($month - 1) % 3);

        // Fix up Jan - Feb to get LAST year's quarter dates (Oct - Dec)
        if ($startmth == -2) {
            $startmth += 12;
            $year -= 1;
        }

        $endmth = $startmth + 2;
        $last_quarter['start'] = date('Y-m-d', mktime(0, 0, 0, $startmth, 1, $year));
        $last_quarter['end'] = date('Y-m-d',
                                    mktime(0, 0, 0, $endmth, date("t", mktime(0, 0, 0, $endmth, 1, $year)), $year));

        return $last_quarter;
    }

    /**
     * Update call Further Action details
     * @access private
     */

    function update()
    {
        $this->setMethodName('update');
        $dsServiceDeskReport = &$this->dsServiceDeskReport;
        $this->dsServiceDeskReport->populateFromArray($_REQUEST['serviceDeskReport']);

        $buActivity = new BUActivity($this);
        /* get breach comments */
        foreach ($_REQUEST['breach'] as $key => $value) {
            $this->buMonthlyReport->updateBreachComment($key, $value['comment']);
        }

        if ($this->formError) {
            if ($this->dsServiceDeskReport->getValue('serviceDeskReportID') == '') {                    // attempt to insert
                $_REQUEST['action'] = 'edit';
            } else {
                $_REQUEST['action'] = 'create';
            }
            $this->edit();
            exit;
        }

        $serviceDeskReportID = $this->buMonthlyReport->updateMonthlyReport($this->dsServiceDeskReport);

        $urlEdit =
            Controller::buildLink(
                'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
                array(
                    'action' => 'edit',
                    'serviceDeskReportID' => $serviceDeskReportID
                )
            );

        $this->buMonthlyReport->emailLink($urlEdit);

        $urlNext =
            Controller::buildLink($_SERVER['PHP_SELF'],
                             array(
                                 'serviceDeskReportID' => $this->dsServiceDeskReport->getValue('serviceDeskReportID'),
                                 'action' => 'view'
                             )
            );
        header('Location: ' . $urlNext);
    }
}// end of class
?>
