<?php
/**
 * Daily Helpdesk Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUServiceDeskReport.inc.php');
require_once($cfg['path_func'] . '/Common.inc.php');
require_once("Mail.php");
require_once("Mail/mime.php");

class CTMonthlyServiceDeskReport extends CTCNC
{
    var $dsActivtyEngineer = '';
    var $page = '';
    public $buServiceDeskReport;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $this->buServiceDeskReport = new buServiceDeskReport($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {

            default:
                $this->page = $this->allInOne();
                break;
        }
    }

    /**
     * @access private
     */

    function allInOne()
    {
        $this->setMethodName('allInOne');

        //$this->setHTMLFmt( CT_HTML_FMT_PRINTER );

        $this->setTemplateFiles(
            array(
                'HelpdeskReport' => 'HelpdeskReport.inc',
                'HelpdeskReportStatus' => 'HelpdeskReportStatus.inc',
                'HelpdeskReportTopTenCustomers' => 'HelpdeskReportTopTenCustomers.inc',
                'HelpdeskReportTopTenProblems' => 'HelpdeskReportTopTenProblems.inc'
            )
        );

        $this->setPageTitle("Help Desk Report");

        $counts = $this->buDailyServiceDeskReport->getOutstandingActivityCounts();

        $this->template->set_var(

            array(
                'totalActivityCount' => Controller::htmlDisplayText($this->buDailyServiceDeskReport->getTotalActivityCount()),
                'serverGuardActivityCount' => Controller::htmlDisplayText($this->buDailyServiceDeskReport->getServerguardActivityCount()),
                'helpDeskOSServiceDeskCount' => Controller::htmlDisplayText($counts->helpDeskOSServiceDeskCount),
                'helpDeskOSServerCareCount' => Controller::htmlDisplayText($counts->helpDeskOSServerCareCount),
                'helpDeskOSPrePayCount' => Controller::htmlDisplayText($counts->helpDeskOSPrePayCount),
                'helpDeskOSEscalationCount' => Controller::htmlDisplayText($counts->helpDeskOSEscalationCount),
                'helpDeskOSCustResponseCount' => Controller::htmlDisplayText($counts->helpDeskOSCustResponseCount),
                'helpDeskProblems' => Controller::formatForHTML($this->buDailyServiceDeskReport->getHelpDeskProblems())

            )
        );

        if ($_REQUEST['today'] == 1) {

            if ($result = $this->buDailyServiceDeskReport->getStaffAvailability()) {
                $this->template->set_block('HelpdeskReportStatus', 'availablityBlock', 'staffAvailables');

                while ($available = $result->fetch_object()) {

                    $this->template->set_var(
                        array(
                            'engineer' => Controller::htmlDisplayText($available->engineer),
                            'amChecked' => $available->am == 0.5 ? CT_CHECKED : '',
                            'pmChecked' => $available->pm == 0.5 ? CT_CHECKED : ''
                        )
                    );

                    $this->template->parse('staffAvailables', 'availablityBlock', true);

                }
            }

            if ($result = $this->buDailyServiceDeskReport->getVisits()) {

                $this->template->set_block('HelpdeskReportStatus', 'visitBlock', 'visits');

                while ($visit = $result->fetch_object()) {

                    $this->template->set_var(
                        array(
                            'visitEngineer' => Controller::htmlDisplayText($visit->engineer),
                            'visitCustomer' => Controller::htmlDisplayText($visit->customer),
                            'visitDate' => Controller::htmlDisplayText($visit->date),
                            'visitTimeOfDay' => Controller::htmlDisplayText($visit->timeOfDay)
                        )
                    );

                    $this->template->parse('visits', 'visitBlock', true);

                }
            }

        } // end if $_REQUEST['today'] == 1

        $customers = array();
        $hours = array();
        $activities = array();

        if ($result = $this->buDailyServiceDeskReport->getTopTenCustomers($_REQUEST['today'])) {

            $this->template->set_block('HelpdeskReportTopTenCustomers', 'customerBlock', 'customers');

            $minHours = 99999;
            $minActivities = 99999;
            $maxHours = 0;
            $maxActivities = 0;

            while ($customer = $result->fetch_object()) {


                $customers[] = $customer->customer;
                $hours[] = $customer->hours;
                $activities[] = $customer->activities;

                if ($customer->hours > $maxHours) {

                    $maxHours = $customer->hours;

                }

                if ($customer->activities > $maxActivities) {

                    $maxActivities = $customer->activities;

                }

                if ($customer->hours < $minHours) {

                    $minHours = $customer->hours;

                }

                if ($customer->activities < $minActivities) {

                    $minActivities = $customer->activities;

                }

                $this->template->set_var(
                    array(
                        'periodDescription' => $this->buDailyServiceDeskReport->getPeriodDescription($_REQUEST['today']),
                        'customer' => Controller::htmlDisplayText($customer->customer),
                        'activities' => Controller::htmlDisplayText($customer->activities),
                        'hours' => common_numberFormat($customer->hours)
                    )
                );

                $this->template->parse('customers', 'customerBlock', true);

            }

        }

        if ($result = $this->buDailyServiceDeskReport->getTopTenProblems($_REQUEST['today'])) {

            $this->template->set_block('HelpdeskReportTopTenProblems', 'problemBlock', 'problems');

            $minValue = 0;
            $maxValue = 0;
            $data = false;
            $ylabel = false;

            while ($problem = $result->fetch_object()) {

                $data[] = $problem->hours;
                $ylabel[] = $problem->category;


                if ($problem->hours > $maxValue) {

                    $maxValue = $problem->hours;

                }

                $this->template->set_var(
                    array(
                        'periodDescription' => $this->buDailyServiceDeskReport->getPeriodDescription($_REQUEST['today']),
                        'category' => Controller::htmlDisplayText($problem->category),
                        'hours' => common_numberFormat($problem->hours)
                    )
                );

                $this->template->parse('problems', 'problemBlock', true);

            }

        }

        if ($_REQUEST['today'] == 1) {
            $this->template->parse('helpdeskReportStatus', 'HelpdeskReportStatus', true);
        }
        $this->template->parse('helpdeskReportTopTenCustomers', 'HelpdeskReportTopTenCustomers', true);
        $this->template->parse('helpdeskReportTopTenProblems', 'HelpdeskReportTopTenProblems', true);

        $this->template->parse("CONTENTS", "HelpdeskReport", true);

//		$this->template->parse("CONTENTS", "page");

//		return $this->template->finish($this->template->get_var('CONTENTS'));
        $this->parsePage();


    }

    function produceReport($sendReport = true)
    {

        global $cfg;

        $buMail = new BUMail($this);

        $senderEmail = CONFIG_SUPPORT_EMAIL;
        $senderName = 'CNC Support Department';

        $toEmail = 'HelpdeskUpdate@' . CONFIG_PUBLIC_DOMAIN;

        $template = new Template ($cfg["path_templates"], "remove");
        $template->set_file('page', 'DailyServiceDeskReport.inc.html');

        $template->set_var(
            array(
                'slaLoggedToday' => $this->slaLoggedToday,
                'nonSlaLoggedToday' => $this->nonSlaLoggedToday,
                'fixedToday' => $this->fixedToday,
                'inProgress' => $this->inProgress,
                'percentWithinSLA' => common_numberFormat($this->getPercentWithinSLA()),
                'percentOverSLA' => common_numberFormat($this->getPercentOverSLA()),
                'averageResponseHours' => common_numberFormat($this->getAverageReponseHours()),
                'averageFixHours' => common_numberFormat($this->getAverageFixHours()),
                'notes' => str_replace("\n", "<BR/>", $this->problemNotes),
                'reportDate' => date('d/m/Y')
            )
        );

        $template->set_block('page', 'visitBlock', 'visits');

        $visits = $this->getVisitsTomorrow();

        while ($visit = $visits->fetch_object()) {

            $urlRequest = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayServiceRequest&problemID=' . $visit->problemID;

            $template->set_var(
                array(
                    'userName' => $visit->userName,
                    'customerName' => $visit->customerName,
                    'date' => $visit->date,
                    'problemID' => $visit->problemID,
                    'urlRequest' => $urlRequest,
                    'amPm' => $visit->amPm
                )
            );

            $template->parse('visits', 'visitBlock', true);

        }

        $template->set_block('page', 'priorityOneBlock', 'priorityOnes');

        $results = $this->getProblemsRaisedToday();

        while ($result = $results->fetch_object()) {

            if ($result->hoursSpent >= 2 OR $result->priority == 1) {

                if ($result->hoursSpent >= 2) {
                    $reasonForListing = round($result->hoursSpent, 0) . ' hours spent';
                } else {
                    $reasonForListing = 'URGENT!';
                }

                $urlRequest = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayServiceRequest&problemID=' . $result->problemID;

                $template->set_var(
                    array(
                        'customerName' => $result->customerName,
                        'problemID' => $result->problemID,
                        'reasonForListing' => $reasonForListing,
                        'urlRequest' => $urlRequest,
                        'details' => substr(strip_tags($this->getDetailsByProblemID($result->problemID)), 0, 250)
                    )
                );
                $template->parse('priorityOnes', 'priorityOneBlock', true);
            }

        }

        $template->parse('output', 'page', true);

        $body = $template->get_var('output');

        $hdrs = array(
            'From' => $senderEmail,
            'To' => $toEmail,
            'Subject' => 'Daily Service Desk Report',
            'Date' => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        echo $body;

        if ($sendReport) {
            $buMail->mime->setHTMLBody($body);

            $mime_params = array(
                'text_encoding' => '7bit',
                'text_charset' => 'UTF-8',
                'html_charset' => 'UTF-8',
                'head_charset' => 'UTF-8'
            );
            $body = $buMail->mime->get($mime_params);

            $hdrs = $buMail->mime->headers($hdrs);

            $buMail->send(
                $senderEmail,
                $toEmail,
                $hdrs,
                $body
            );
        }
    }

}// end of class
?>