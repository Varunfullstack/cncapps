<?php
/**
 * Service request by customer report
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUActivity.inc.php');
require_once($cfg ['path_bu'] . '/BUMail.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTServiceRequestsByCustomerReport extends CTCNC
{

    private $buActivity;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $this->buActivity = new BUActivity($this);
    }

    function defaultAction()
    {

        switch ($_REQUEST['action']) {

            case 'email':
                $this->email();
                break;

            case 'search':
            default:
                $this->search();
                break;

        }
    }

    function search()
    {

        global $cfg;

        $this->setMethodName('search');

        $dsResults = new DataSet ($this);

        $dsSearchForm = $this->initialiseSearchForm();

        $this->setTemplateFiles(array('ServiceRequestsByCustomerReport' => 'ServiceRequestsByCustomerReport.inc'));

        if (isset($_REQUEST ['searchForm'])) {

            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                $results = $this->buActivity->getSrPercentages(
                    $dsSearchForm->getValue('days'),
                    $dsSearchForm->getValue('fromDate'),
                    $dsSearchForm->getValue('toDate')
                );

                if ($results) {

                    $this->renderReport('ServiceRequestsByCustomerReport', $results, $dsSearchForm);

                }//end if $results

            }
        }

        $urlSubmit = $this->buildLink(
            $_SERVER ['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH)
        );

        $this->setPageTitle('Service Requests By Customer Report');

        $this->template->set_var(
            array(
                'formError' => $this->formError,
                'days' => $dsSearchForm->getValue('days'),
                'daysMessage' => $dsSearchForm->getMessage('days'),
                'fromDate' => Controller::dateYMDtoDMY($dsSearchForm->getValue('fromDate')),
                'fromDateMessage' => $dsSearchForm->getMessage('fromDate'),
                'toDate' => Controller::dateYMDtoDMY($dsSearchForm->getValue('toDate')),
                'toDateMessage' => $dsSearchForm->getMessage('toDate'),
                'urlSubmit' => $urlSubmit
            )
        );

        $this->template->parse('CONTENTS', 'ServiceRequestsByCustomerReport', true);
        $this->parsePage();
    }

    function initialiseSearchForm()
    {
        $dsSearchForm = new DSForm ($this);
        $dsSearchForm->addColumn('fromDate', DA_DATE, DA_ALLOW_NULL);
        $dsSearchForm->addColumn('toDate', DA_DATE, DA_ALLOW_NULL);
        $dsSearchForm->addColumn('days', DA_INTEGER, DA_ALLOW_NULL);
        $dsSearchForm->setValue('days', 7);

        return $dsSearchForm;

    }

    /*
    Render results section
    */
    public function renderReport($templateName, $results, $dsSearchForm)
    {
        $totalPercentage = 0;
        $totalHours = 0;
        $totalSrCount = 0;

        $this->template->set_block($templateName, 'customersBlock', 'customers');

        foreach ($results as $row) {

            $this->template->set_var(
                array(
                    'customerName' => $row['cus_name'],
                    'hours' => number_format($row['hours'], 1),
                    'srCount' => $row['srCount'],
                    'percentage' => number_format($row['percentage'], 1)
                )
            );
            $this->template->parse('customers', 'customersBlock', true);

            $totalHours += $row['hours'];
            $totalPercentage += $row['percentage'];
            $totalSrCount += $row['srCount'];
        }

        $this->template->set_var(
            array(
                'days' => $dsSearchForm->getValue('days'),
                'totalHours' => number_format($totalHours, 1),
                'totalSrCount' => $totalSrCount,
                'totalPercentage' => number_format($totalPercentage, 1)
            )
        );


    } // end renderReport()

    /*
    Send email report for past $days
    */
    function email()
    {

        global $cfg;

        $this->setMethodName('email');

        $dsResults = new DataSet ($this);

        $dsSearchForm = $this->initialiseSearchForm();

        $days = $_REQUEST['days'];

        $dsSearchForm->setValue('days', $days);

        $results = $this->buActivity->getSrPercentages($days);

        if ($results) {

            $buMail = new BUMail($this);

            $senderEmail = CONFIG_SUPPORT_EMAIL;

            $senderName = 'CNC Support Department';

            $toEmail = 'monthlysdreport@cnc-ltd.co.uk';

            $this->template = new Template(EMAIL_TEMPLATE_DIR, "remove");
            $this->template->set_file('page', 'ServiceRequestsByCustomerReportEmail.inc.html');

            $this->renderReport('page', $results, $dsSearchForm);

            $this->template->parse('output', 'page', true);

            $body = $this->template->get_var('output');

            $subject = 'Service Requests By Customer - Days: ' . $days;

            $hdrs = array(
                'From' => $senderEmail,
                'Subject' => $subject,
                'Date' => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );

            $buMail->mime->setHTMLBody($body);

            $body = $buMail->mime->get();

            $hdrs = $buMail->mime->headers($hdrs);

            $buMail->putInQueue(
                $senderEmail,
                $toEmail,
                $hdrs,
                $body,
                true
            );

            echo 'email queued to be sent';
        }

    } // end email

} // end of class
?>