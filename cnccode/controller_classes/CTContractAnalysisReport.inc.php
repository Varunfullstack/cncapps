<?php
/**
 * Contract Analysis Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUContractAnalysisReport.inc.php');
require_once($cfg ['path_bu'] . '/BUMail.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTContractAnalysisReport extends CTCNC
{

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $this->buContractAnalysisReport = new BUContractAnalysisReport ($this);
    }

    /**
     * Route to function based upon action passed
     */
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

        $dsSearchForm = new DSForm ($this);
        $dsResults = new DataSet ($this);

        $this->buContractAnalysisReport->initialiseSearchForm($dsSearchForm);

        $this->setTemplateFiles(array('ContractAnalysisReport' => 'ContractAnalysisReport.inc'));

        if (isset($_REQUEST ['searchForm'])) {

            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                set_time_limit(240);

                $results = $this->buContractAnalysisReport->getResults($dsSearchForm);

                if ($results && $_REQUEST['Search'] == 'Generate CSV') {

                    $template = new Template ($cfg["path_templates"], "remove");

                    $template->set_file('page', 'ContractAnalysisReport.inc.csv');

                    $template->set_block('page', 'customersBlock', 'customers');

                    foreach ($results as $customerName => $row) {
                        $template->set_var(
                            array(
                                'customerName' => $customerName,
                                'sales' => $row['sales'],
                                'cost' => $row['cost'],
                                'labourCost' => $row['labourCost'],
                                'profit' => $row['profit'],
                                'profitPercent' => $row['profitPercent'],
                                'labourHours' => $row['labourHours']
                            )
                        );
                        $template->parse('customers', 'customersBlock', true);
                    }
                    $template->parse('output', 'page', true);

                    $output = $template->get_var('output');

                    Header('Content-type: text/plain');
                    Header('Content-Disposition: attachment; filename=ContractAnalysisReport.csv');
                    echo $output;
                    exit;
                } else { // Screen Report

                    if ($results) {

                        $this->renderReport('ContractAnalysisReport', $results, $dsSearchForm);


                    }//end if $results

                }

            }
        }

        $urlCustomerPopup = $this->buildLink(CTCNC_PAGE_CUSTOMER, array('action' => CTCNC_ACT_DISP_CUST_POPUP, 'htmlFmt' => CT_HTML_FMT_POPUP));

        $urlSubmit = $this->buildLink($_SERVER ['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));

        $this->setPageTitle('Contract Analysis Report');

        $this->template->set_var(
            array(
                'formError' => $this->formError,
                'contracts' => $dsSearchForm->getValue('contracts'),
                'startYearMonth' => $dsSearchForm->getValue('startYearMonth'),
                'endYearMonth' => $dsSearchForm->getValue('endYearMonth'),
                'urlSubmit' => $urlSubmit
            )
        );

        $this->template->parse('CONTENTS', 'ContractAnalysisReport', true);
        $this->parsePage();
    }

    /*
    Render results section
    */
    public function renderReport($templateName, $results, $dsSearchForm)
    {
        $totalSales = 0;
        $totalCost = 0;
        $totalLabour = 0;
        $totalLabourHours = 0;

        $this->template->set_block($templateName, 'customersBlock', 'customers');


        foreach ($results as $key => $row) {
            $sales[$key] = $row['sales'];
            $cost[$key] = $row['cost'];
            $labourCost[$key] = $row['labourCost'];
            $labourHours[$key] = $row['labourHours'];
            $profitPercent[$key] = $row['profitPercent'];
            $profit[$key] = $row['profit'];
        }

        if (isset($_REQUEST['orderBy'])) {
            array_multisort($$_REQUEST['orderBy'], SORT_ASC, $results);
        } else {
            array_multisort($profit, SORT_ASC, $results);
        }

        foreach ($results as $customerName => $row) {

            if ($row['profit'] <= 0) {
                $profitAlertClass = 'profitAlert';
            } else {
                $profitAlertClass = '';
            }

            $customerAnalysisUrl =
                $this->buildLink(
                    'CustomerAnalysisReport.php',
                    array(
                        'searchForm[1][customerID]' => $row['customerID'],
                        'searchForm[1][startYearMonth]' => $dsSearchForm->getValue('startYearMonth'),
                        'searchForm[1][endYearMonth]' => $dsSearchForm->getValue('endYearMonth'),
                    )
                );

            $reportUrl =
                $this->buildLink(
                    'ContractAnalysisReport.php',
                    array(
                        'searchForm[1][contracts]' => $_REQUEST ['searchForm'][1]['contracts'],
                        'searchForm[1][startYearMonth]' => $dsSearchForm->getValue('startYearMonth'),
                        'searchForm[1][endYearMonth]' => $dsSearchForm->getValue('endYearMonth'),
                    )
                );
            $this->template->set_var(
                array(
                    'customerName' => $customerName,
                    'customerAnalysisUrl' => $customerAnalysisUrl,
                    'reportUrl' => $reportUrl,
                    'sales' => number_format($row['sales'], 2),
                    'cost' => number_format($row['cost'], 2),
                    'labourCost' => number_format($row['labourCost'], 2),
                    'profit' => number_format($row['profit'], 2),
                    'profitPercent' => $row['profitPercent'],
                    'labourHours' => $row['labourHours'],
                    'profitAlertClass' => $profitAlertClass,
                    'serverHost' => $_SERVER['SERVER_NAME']
                )
            );
            $this->template->parse('customers', 'customersBlock', true);

            $totalSales += $row['sales'];
            $totalCost += $row['cost'];
            $totalLabour += $row['labourCost'];
            $totalLabourHours += $row['labourHours'];
        }
        $this->template->set_var(
            array(
                'totalSales' => number_format($totalSales, 2),
                'totalCost' => number_format($totalCost, 2),
                'totalLabour' => number_format($totalLabour, 2),
                'totalProfit' => number_format($totalSales - $totalCost - $totalLabour, 2),
                'totalProfitPercent' => number_format(100 - (($totalCost + $totalLabour) / $totalSales) * 100, 2),
                'totalLabourHours' => number_format($totalLabourHours, 2),
            )
        );


    } // end renderReport()

    /*
    Send report as an email of all customers for past 12 months
    */
    function email()
    {

        global $cfg;

        $this->setMethodName('email');

        $dsSearchForm = new DSForm ($this);
        $dsResults = new DataSet ($this);

        $this->buContractAnalysisReport->initialiseSearchForm($dsSearchForm);

        set_time_limit(240);

        /* One year excluding current month */
        $d = new \DateTime('now');

        $d->modify('first day of previous month');

        $dsSearchForm->setValue('endYearMonth', $d->format('Y') . '-' . $d->format('m'));

        $d->modify('11 months ago');

        $dsSearchForm->setValue('startYearMonth', $d->format('Y') . '-' . $d->format('m'));

        $dsSearchForm->setValue('contracts', '');      // All

        $results = $this->buContractAnalysisReport->getResults($dsSearchForm);

        if ($results) {

            $buMail = new BUMail($this);

            $senderEmail = CONFIG_SUPPORT_EMAIL;

            $senderName = 'CNC Support Department';

            $this->template = new Template(EMAIL_TEMPLATE_DIR, "remove");
            $this->template->set_file('page', 'ContractAnalysisReportEmail.inc.html');

            $this->renderReport('page', $results, $dsSearchForm);

            $this->template->parse('output', 'page', true);

            $body = $this->template->get_var('output');

            $subject = 'Monthly Customer Profitability Report';

            /* Sent to the directors only */
            $toEmail = 'grahaml@' . CONFIG_PUBLIC_DOMAIN . ', garyj@' . CONFIG_PUBLIC_DOMAIN;

            $hdrs = array(
                'From' => $senderEmail,
                'Subject' => $subject,
                'Date' => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );

            $buMail->mime->setHTMLBody($body);

            $mime_params = array(
                'text_encoding' => '7bit',
                'text_charset' => 'UTF-8',
                'html_charset' => 'UTF-8',
                'head_charset' => 'UTF-8'
            );
            $body = $buMail->mime->get($mime_params);

            $hdrs = $buMail->mime->headers($hdrs);

            $buMail->putInQueue(
                $senderEmail,
                $toEmail,
                $hdrs,
                $body
            );
        }

    } // end email

} // end of class
?>