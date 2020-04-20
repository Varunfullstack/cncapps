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
require_once($cfg ['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTCustomerProfitabilityMonthsReport extends CTCNC
{

    /**
     * @var BUCustomerProfitabilityMonthsReport
     */
    public $buCustomerProfitabilityMonthsReport;

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
        $this->setMenuId(512);
        $this->buCustomerProfitabilityMonthsReport = new BUCustomerProfitabilityMonthsReport ($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {

            default:
                $this->search();
                break;
        }
    }

    /**
     * @throws Exception
     */
    function search()
    {
        $this->setMethodName('search');
        $dsSearchForm = new DSForm ($this);
        $this->buCustomerProfitabilityMonthsReport->initialiseSearchForm($dsSearchForm);

        $this->setTemplateFiles(array('CustomerProfitabilityMonthsReport' => 'CustomerProfitabilityMonthsReport.inc'));

        if (isset($_REQUEST ['searchForm'])) {

            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                $periodRegx = '/^(0[1-9]{1}|1[0-2]{1})\/\d{4}$/';

                if (!preg_match(
                    $periodRegx,
                    $dsSearchForm->getValue(BUCustomerProfitabilityMonthsReport::searchFormStartYearMonth),
                    $matches
                )) {
                    $dsSearchForm->setMessage(
                        BUCustomerProfitabilityMonthsReport::searchFormStartYearMonth,
                        'Use format MM/YYYY'
                    );
                    $this->setFormErrorOn();
                } elseif (!preg_match(
                    $periodRegx,
                    $dsSearchForm->getValue(
                        BUCustomerProfitabilityMonthsReport::searchFormEndYearMonth
                    ),
                    $matches
                )) {
                    $dsSearchForm->setMessage(
                        BUCustomerProfitabilityMonthsReport::searchFormEndYearMonth,
                        'Use format MM/YYYY'
                    );
                    $this->setFormErrorOn();
                } else {


                    $reportData =
                        $this->buCustomerProfitabilityMonthsReport->getReportData(
                            $dsSearchForm->getValue(BUCustomerProfitabilityMonthsReport::searchFormCustomerID),
                            $dsSearchForm->getValue(BUCustomerProfitabilityMonthsReport::searchFormStartYearMonth),
                            $dsSearchForm->getValue(BUCustomerProfitabilityMonthsReport::searchFormEndYearMonth)
                        );
                    if ($this->getParam('Search') == 'CSV') {
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

        $urlCustomerPopup = Controller::buildLink(
            CTCNC_PAGE_CUSTOMER,
            array('action' => CTCNC_ACT_DISP_CUST_POPUP, 'htmlFmt' => CT_HTML_FMT_POPUP)
        );

        $urlSubmit = Controller::buildLink($_SERVER ['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));

        $this->setPageTitle('Customer Profitability Export');
        $customerString = null;
        if ($dsSearchForm->getValue(BUCustomerProfitabilityMonthsReport::searchFormCustomerID) != 0) {
            $buCustomer = new BUCustomer ($this);
            $dsCustomer = new DataSet($this);
            $buCustomer->getCustomerByID(
                $dsSearchForm->getValue(BUCustomerProfitabilityMonthsReport::searchFormCustomerID),
                $dsCustomer
            );
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }

        $this->template->set_var(
            array(
                'customerID'            => $dsSearchForm->getValue(
                    BUCustomerProfitabilityMonthsReport::searchFormCustomerID
                ),
                'customerIDMessage'     => $dsSearchForm->getMessage(
                    BUCustomerProfitabilityMonthsReport::searchFormCustomerID
                ),
                'customerString'        => $customerString,
                'startYearMonth'        => $dsSearchForm->getValue(
                    BUCustomerProfitabilityMonthsReport::searchFormStartYearMonth
                ),
                'startYearMonthMessage' => $dsSearchForm->getMessage(
                    BUCustomerProfitabilityMonthsReport::searchFormStartYearMonth
                ),
                'endYearMonth'          => $dsSearchForm->getValue(
                    BUCustomerProfitabilityMonthsReport::searchFormEndYearMonth
                ),
                'endYearMonthMessage'   => $dsSearchForm->getMessage(
                    BUCustomerProfitabilityMonthsReport::searchFormEndYearMonth
                ),
                'urlCustomerPopup'      => $urlCustomerPopup,
                'urlSubmit'             => $urlSubmit
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
}
