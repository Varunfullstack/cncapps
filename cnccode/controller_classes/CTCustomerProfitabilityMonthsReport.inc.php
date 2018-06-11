<?php
/**
 * Customer Review Meeting By Months Controller Class
 * CNC Ltd
 *
 * Prompts for year
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerProfitabilityMonthsReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerNew.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTCustomerProfitabilityMonthsReport extends CTCNC
{

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "accounts",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buCustomerProfitabilityMonthsReport = new BUCustomerProfitabilityMonthsReport ($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {

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

        $this->buCustomerProfitabilityMonthsReport->initialiseSearchForm($dsSearchForm);

        $this->setTemplateFiles(array('CustomerProfitabilityMonthsReport' => 'CustomerProfitabilityMonthsReport.inc'));

        if (isset($_REQUEST ['searchForm'])) {

            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                $periodRegx = '/^(0[1-9]{1}|1[0-2]{1})\/\d{4}$/';

                if (!preg_match($periodRegx, $dsSearchForm->getValue('startYearMonth'), $matches)) {
                    $dsSearchForm->setMessage('startYearMonth', 'Use format MM/YYYY');
                    $this->setFormErrorOn();
                } elseif (!preg_match($periodRegx, $dsSearchForm->getValue('endYearMonth'), $matches)) {
                    $dsSearchForm->setMessage('endYearMonth', 'Use format MM/YYYY');
                    $this->setFormErrorOn();
                } else {



                    $reportData =
                        $this->buCustomerProfitabilityMonthsReport->getReportData(
                            $dsSearchForm->getValue('customerID'),
                            $dsSearchForm->getValue('startYearMonth'),
                            $dsSearchForm->getValue('endYearMonth')
                        );
                    if ($_REQUEST['Search'] == 'CSV') {
                        $this->generateCSV($reportData);
                    }
                    $this->template->set_block('CustomerProfitabilityMonthsReport', 'periodBlock', 'periods');

                    foreach ($reportData as $period => $profit) {

                        $this->template->set_var(
                            array(
                                'period' => $period,
                                'profit' => $profit
                            )
                        );

                        $this->template->parse('periods', 'periodBlock', true);

                    }
                }
            }
        }

        $urlCustomerPopup = $this->buildLink(CTCNC_PAGE_CUSTOMER,
                                             array('action' => CTCNC_ACT_DISP_CUST_POPUP, 'htmlFmt' => CT_HTML_FMT_POPUP));

        $urlSubmit = $this->buildLink($_SERVER ['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));

        $this->setPageTitle('Customer Profitability Export');

        if ($dsSearchForm->getValue('customerID') != 0) {
            $buCustomer = new BUCustomer ($this);
            $buCustomer->getCustomerByID($dsSearchForm->getValue('customerID'), $dsCustomer);
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }

        $this->template->set_var(
            array(
                'customerID' => $dsSearchForm->getValue('customerID'),
                'customerIDMessage' => $dsSearchForm->getMessage('customerID'),
                'customerString' => $customerString,
                'startYearMonth' => $dsSearchForm->getValue('startYearMonth'),
                'startYearMonthMessage' => $dsSearchForm->getMessage('startYearMonth'),
                'endYearMonth' => $dsSearchForm->getValue('endYearMonth'),
                'endYearMonthMessage' => $dsSearchForm->getMessage('endYearMonth'),
                'urlCustomerPopup' => $urlCustomerPopup,
                'urlSubmit' => $urlSubmit
            )
        );

        $this->template->parse('CONTENTS', 'CustomerProfitabilityMonthsReport', true);
        $this->parsePage();
    }

    function generateCSV($reportData)
    {
        $fileName = 'profit.csv';
        Header('Content-type: text/plain');
        Header('Content-Disposition: attachment; filename=' . $fileName);

        echo "period,profit\n";

        foreach ($reportData as $period => $profit) {
            echo $period . ',' . $profit . "\n";
        }
        $this->pageClose();
        exit;
    }
} // end of class
?>