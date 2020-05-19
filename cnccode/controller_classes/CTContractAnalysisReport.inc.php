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

    const searchFormEndYearMonth = 'endYearMonth';
    const searchFormStartYearMonth = 'startYearMonth';
    const searchFormContracts = 'contracts';

    /**
     * @var BUContractAnalysisReport
     */
    private $buContractAnalysisReport;

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
        $roles = REPORTS_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(511);
        $this->buContractAnalysisReport = new BUContractAnalysisReport ($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {

        switch ($this->getAction()) {
            case 'email':
                $this->email();
                break;

            case 'search':
            default:
                $this->search();
                break;

        }
    }

/**
     * @throws Exception
     */
    function email()
    {
        $this->setMethodName('email');
        $dsSearchForm = new DSForm ($this);
        $this->buContractAnalysisReport->initialiseSearchForm($dsSearchForm);
        set_time_limit(240);

        /* One year excluding current month */
        $d = new DateTime('now');

        $d->modify('first day of previous month');

        $dsSearchForm->setValue(
            self::searchFormEndYearMonth,
            $d->format('m') . '/' . $d->format('Y')
        );

        $d->modify('11 months ago');

        $dsSearchForm->setValue(
            self::searchFormStartYearMonth,
            $d->format('m') . '/' . $d->format('Y')
        );

        $dsSearchForm->setValue(
            self::searchFormContracts,
            null
        );      // All

        $results = $this->buContractAnalysisReport->getResults($dsSearchForm);

        if ($results) {

            $buMail = new BUMail($this);

            $senderEmail = CONFIG_SUPPORT_EMAIL;

            $this->template = new Template(
                EMAIL_TEMPLATE_DIR,
                "remove"
            );
            $this->template->set_file(
                'page',
                'ContractAnalysisReportEmail.inc.html'
            );

            $this->renderReport(
                'page',
                $results,
                $dsSearchForm
            );

            $this->template->parse(
                'output',
                'page',
                true
            );

            $body = $this->template->get_var('output');

            $subject = 'Monthly Customer Profitability Report';

            /* Sent to the directors only */
            $toEmail = "MonthlyCustomerProfitabilityReport@cnc-ltd.co.uk";

            $hdrs = array(
                'From'         => $senderEmail,
                'To'           => $toEmail,
                'Subject'      => $subject,
                'Date'         => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );

            $buMail->mime->setHTMLBody($body);

            $mime_params = array(
                'text_encoding' => '7bit',
                'text_charset'  => 'UTF-8',
                'html_charset'  => 'UTF-8',
                'head_charset'  => 'UTF-8'
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

    }

    /*
    Render results section
    */

/**
     * @param $templateName
     * @param $results
     * @param DSForm $dsSearchForm
     * @throws Exception
     */
    public function renderReport($templateName,
                                 $results,
                                 $dsSearchForm
    )
    {
        $totalSales = 0;
        $totalCost = 0;
        $totalLabour = 0;
        $totalLabourHours = 0;

        $this->template->set_block(
            $templateName,
            'customersBlock',
            'customers'
        );

        $orderBy = $this->getParam('orderBy');

        $profit = array_column($results, 'profit');
        $sales = array_column($results, 'sales');
        $cost = array_column($results, 'cost');
        $labourCost = array_column($results, 'labourCost');
        $labourHours = array_column($results, 'labourHours');
        $profitPercent = array_column($results, 'profitPercent');

        if ($orderBy) {
            array_multisort(
                $$orderBy,
                SORT_ASC,
                $results
            );
        } else {
            array_multisort(
                $profit,
                SORT_ASC,
                $results
            );
        }

        foreach ($results as $customerName => $row) {

            $profitAlertClass = null;
            if ($row['profit'] <= 0) {
                $profitAlertClass = 'profitAlert';
            }

            $customerAnalysisUrl =
                Controller::buildLink(
                    'CustomerAnalysisReport.php',
                    array(
                        'searchForm[1][customerID]'     => $row['customerID'],
                        'searchForm[1][startYearMonth]' => $dsSearchForm->getValue(self::searchFormStartYearMonth),
                        'searchForm[1][endYearMonth]'   => $dsSearchForm->getValue(self::searchFormEndYearMonth),
                    )
                );

            $reportUrl =
                Controller::buildLink(
                    'ContractAnalysisReport.php',
                    array(
                        'searchForm[1][contracts]'      => @$_REQUEST ['searchForm'][1]['contracts'],
                        'searchForm[1][startYearMonth]' => $dsSearchForm->getValue(self::searchFormStartYearMonth),
                        'searchForm[1][endYearMonth]'   => $dsSearchForm->getValue(self::searchFormEndYearMonth),
                    )
                );
            $this->template->set_var(
                array(
                    'customerName'        => $customerName,
                    'customerAnalysisUrl' => SITE_URL . "/" . $customerAnalysisUrl,
                    'reportUrl'           => $reportUrl,
                    'sales'               => number_format(
                        $row['sales'],
                        2
                    ),
                    'cost'                => number_format(
                        $row['cost'],
                        2
                    ),
                    'labourCost'          => number_format(
                        $row['labourCost'],
                        2
                    ),
                    'profit'              => number_format(
                        $row['profit'],
                        2
                    ),
                    'profitPercent'       => $row['profitPercent'],
                    'labourHours'         => $row['labourHours'],
                    'profitAlertClass'    => $profitAlertClass,
                    'serverHost'          => $_SERVER['SERVER_NAME']
                )
            );
            $this->template->parse(
                'customers',
                'customersBlock',
                true
            );

            $totalSales += $row['sales'];
            $totalCost += $row['cost'];
            $totalLabour += $row['labourCost'];
            $totalLabourHours += $row['labourHours'];
        }
        $this->template->set_var(
            array(
                'totalSales'         => number_format(
                    $totalSales,
                    2
                ),
                'totalCost'          => number_format(
                    $totalCost,
                    2
                ),
                'totalLabour'        => number_format(
                    $totalLabour,
                    2
                ),
                'totalProfit'        => number_format(
                    $totalSales - $totalCost - $totalLabour,
                    2
                ),
                'totalProfitPercent' => number_format(
                    100 - (($totalCost + $totalLabour) / $totalSales) * 100,
                    2
                ),
                'totalLabourHours'   => number_format(
                    $totalLabourHours,
                    2
                ),
            )
        );


    } // end renderReport()

    /*
    Send report as an email of all customers for past 12 months
    */

    /**
     * @throws Exception
     */
    function search()
    {
        global $cfg;

        $this->setMethodName('search');

        $dsSearchForm = new DSForm ($this);

        $this->buContractAnalysisReport->initialiseSearchForm($dsSearchForm);

        $this->setTemplateFiles(array('ContractAnalysisReport' => 'ContractAnalysisReport.inc'));

        if (isset($_REQUEST ['searchForm'])) {

            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                set_time_limit(240);

                $results = $this->buContractAnalysisReport->getResults($dsSearchForm);

                if ($results && $this->getParam('Search') == 'Generate CSV') {

                    $template = new Template (
                        $cfg["path_templates"],
                        "remove"
                    );

                    $template->set_file(
                        'page',
                        'ContractAnalysisReport.inc.csv'
                    );

                    $template->set_block(
                        'page',
                        'customersBlock',
                        'customers'
                    );

                    foreach ($results as $customerName => $row) {
                        $template->set_var(
                            array(
                                'customerName'  => $customerName,
                                'sales'         => $row['sales'],
                                'cost'          => $row['cost'],
                                'labourCost'    => $row['labourCost'],
                                'profit'        => $row['profit'],
                                'profitPercent' => $row['profitPercent'],
                                'labourHours'   => $row['labourHours']
                            )
                        );
                        $template->parse(
                            'customers',
                            'customersBlock',
                            true
                        );
                    }
                    $template->parse(
                        'output',
                        'page',
                        true
                    );

                    $output = $template->get_var('output');

                    Header('Content-type: text/plain');
                    Header('Content-Disposition: attachment; filename=ContractAnalysisReport.csv');
                    echo $output;
                    exit;
                } else { // Screen Report

                    if ($results) {

                        $this->renderReport(
                            'ContractAnalysisReport',
                            $results,
                            $dsSearchForm
                        );


                    }//end if $results

                }

            }
        }

        $urlSubmit = Controller::buildLink(
            $_SERVER ['PHP_SELF'],
            array('action' => CTCNC_ACT_SEARCH)
        );

        $this->setPageTitle('Contract Analysis Report');

        $this->template->set_var(
            array(
                'formError'      => $this->formError,
                'contracts'      => $dsSearchForm->getValue(self::searchFormContracts),
                'startYearMonth' => $dsSearchForm->getValue(self::searchFormStartYearMonth),
                'endYearMonth'   => $dsSearchForm->getValue(self::searchFormEndYearMonth),
                'urlSubmit'      => $urlSubmit
            )
        );

        $this->template->parse(
            'CONTENTS',
            'ContractAnalysisReport',
            true
        );
        $this->parsePage();
    } // end email

}