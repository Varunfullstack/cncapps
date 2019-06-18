<?php
/**
 * CustomerAnalysis Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerAnalysisReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTStarterAndLeaverReport extends CTCNC
{
    /**
     * @var BUCustomerAnalysisReport
     */
    public $buCustomerAnalysisReport;

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
        $this->buCustomerAnalysisReport = new BUCustomerAnalysisReport ($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->search();
    }

    /**
     * @throws Exception
     */
    function search()
    {
        global $cfg;
        $this->setMethodName('search');
        $dsSearchForm = new DSForm ($this);
        $this->buCustomerAnalysisReport->initialiseSearchForm($dsSearchForm);

        $this->setTemplateFiles(array('CustomerAnalysisReport' => 'CustomerAnalysisReport.inc'));

        if (isset($_REQUEST ['searchForm'])) {

            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                set_time_limit(240);

                $results = $this->getData($dsSearchForm);

                if ($this->getParam('Search') == 'Generate CSV') {

                    $template = new Template ($cfg["path_templates"], "remove");

                    $template->set_file('page', 'CustomerAnalysisReport.inc.csv');

                    $template->set_block('page', 'contractsBlock', 'contracts');
                    foreach ($results as $contractName => $row) {
                        $template->set_var(
                            array(
                                'contract'    => $contractName,
                                'sales'       => $row['sales'],
                                'cost'        => $row['cost'],
                                'labour'      => $row['labourCost'],
                                'labourHours' => $row['labourHours'],
                            )
                        );
                        $template->parse('contracts', 'contractsBlock', true);
                    }
                    $template->parse('output', 'page', true);

                    $output = $template->get_var('output');

                    Header('Content-type: text/plain');
                    Header('Content-Disposition: attachment; filename=CustomerAnalysisReport.csv');
                    echo $output;
                    exit;
                } else { // Screen Report

                    $totalSales = 0;
                    $totalCost = 0;
                    $totalLabour = 0;
                    $totalLabourHours = 0;
                    $grandTotalSales = 0;
                    $grandTotalCost = 0;
                    $grandTotalLabour = 0;
                    $grandTotalLabourHours = 0;

                    if ($this->getParam('orderBy')) {
                        $columns = [
                            "sales"         => [],
                            "cost"          => [],
                            "labourCost"    => [],
                            "labourHours"   => [],
                            "profitPercent" => [],
                            "profit"        => [],
                        ];
                        foreach ($results as $key => $row) {
                            $columns['sales'][$key] = $row['sales'];
                            $columns['cost'][$key] = $row['cost'];
                            $columns['labourCost'][$key] = $row['labourCost'];
                            $columns['labourHours'][$key] = $row['labourHours'];
                            $columns['profitPercent'][$key] = $row['profitPercent'];
                            $columns['profit'][$key] = $row['profit'];
                        }
                        array_multisort($columns[$this->getParam('orderBy')], SORT_ASC, $results);
                    }

                    $this->template->set_block('CustomerAnalysisReport', 'contractsBlock', 'contracts');

                    $reportUrl =
                        Controller::buildLink(
                            'CustomerAnalysisReport.php',
                            array(
                                'searchForm[1][customerID]'     => $_REQUEST ['searchForm'][1]['customerID'],
                                'searchForm[1][startYearMonth]' => $_REQUEST ['searchForm'][1]['startYearMonth'],
                                'searchForm[1][endYearMonth]'   => $_REQUEST ['searchForm'][1]['endYearMonth'],
                            )
                        );
                    foreach ($results as $contractName => $row) {

                        $profitAlertClass = null;
                        if ($row['profit'] <= 0) {
                            $profitAlertClass = 'profitAlert';
                        }

                        $target = 'contracts';
                        $handle = 'contractsBlock';

                        if ($contractName == 'Other Sales') {
                            $this->template->setBlock('CustomerAnalysisReport', 'otherSalesBlock', 'otherSales');
                            $target = 'otherSales';
                            $handle = 'otherSalesBlock';

                        } else {
                            $totalSales += $row['sales'];
                            $totalCost += $row['cost'];
                            $totalLabour += $row['labourCost'];
                            $totalLabourHours += $row['labourHours'];
                        }

                        $grandTotalSales += $row['sales'];
                        $grandTotalCost += $row['cost'];
                        $grandTotalLabour += $row['labourCost'];
                        $grandTotalLabourHours += $row['labourHours'];

                        $this->template->set_var(
                            array(
                                'contract'         => $contractName,
                                'sales'            => number_format($row['sales'], 2),
                                'cost'             => number_format($row['cost'], 2),
                                'labour'           => number_format($row['labourCost'], 2),
                                'profit'           => number_format($row['profit'], 2),
                                'profitPercent'    => $row['profitPercent'],
                                'labourHours'      => number_format($row['labourHours'], 0),
                                'profitAlertClass' => $profitAlertClass,
                                'reportUrl'        => $reportUrl
                            )
                        );
                        $this->template->parse($target, $handle, true);


                    }
                    $this->template->set_var(
                        array(
                            'totalSales'         => number_format($totalSales, 2),
                            'totalCost'          => number_format($totalCost, 2),
                            'totalLabour'        => number_format($totalLabour, 2),
                            'totalProfit'        => number_format($totalSales - $totalCost - $totalLabour, 2),
                            'totalProfitPercent' => number_format(
                                $totalSales > 0 ? 100 - (($totalCost + $totalLabour) / $totalSales) * 100 : 0,
                                2
                            ),
                            'totalLabourHours'   => number_format($totalLabourHours, 2),
                        )
                    );
                    $this->template->set_var(
                        array(
                            'grandTotalSales'         => number_format($grandTotalSales, 2),
                            'grandTotalCost'          => number_format($grandTotalCost, 2),
                            'grandTotalLabour'        => number_format($grandTotalLabour, 2),
                            'grandTotalProfit'        => number_format(
                                $grandTotalSales - $grandTotalCost - $grandTotalLabour,
                                2
                            ),
                            'grandTotalProfitPercent' => number_format(
                                $grandTotalSales > 0 ? 100 - (($grandTotalCost + $grandTotalLabour) / $grandTotalSales) * 100 : 0,
                                2
                            ),
                            'grandTotalLabourHours'   => number_format($grandTotalLabourHours, 2),
                        )
                    );

                }

            }

        }

        $urlCustomerPopup = Controller::buildLink(
            CTCNC_PAGE_CUSTOMER,
            array('action' => CTCNC_ACT_DISP_CUST_POPUP, 'htmlFmt' => CT_HTML_FMT_POPUP)
        );

        $urlSubmit = Controller::buildLink($_SERVER ['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));

        $this->setPageTitle('CustomerAnalysis Report');
        $customerString = null;
        if ($dsSearchForm->getValue(BUCustomerAnalysisReport::searchFormCustomerID) != 0) {
            $buCustomer = new BUCustomer ($this);
            $dsCustomer = new DataSet($this);
            $buCustomer->getCustomerByID(
                $dsSearchForm->getValue(BUCustomerAnalysisReport::searchFormCustomerID),
                $dsCustomer
            );
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }

        $this->template->set_var(
            array(
                'formError'        => $this->formError,
                'customerID'       => $dsSearchForm->getValue(BUCustomerAnalysisReport::searchFormCustomerID),
                'customerString'   => $customerString,
                'startYearMonth'   => $dsSearchForm->getValue(BUCustomerAnalysisReport::searchFormStartYearMonth),
                'endYearMonth'     => $dsSearchForm->getValue(BUCustomerAnalysisReport::searchFormEndYearMonth),
                'urlCustomerPopup' => $urlCustomerPopup,
                'urlSubmit'        => $urlSubmit,
            )
        );

        $this->template->parse('CONTENTS', 'CustomerAnalysisReport', true);
        $this->parsePage();
    }

    private function getData(DSForm $dsSearchForm)
    {
        $sql = "SELECT
'starters',
  SUM(`pro_rootcauseno` = 58) AS starters,
  AVG(pro_total_activity_duration_hours) AS averageDuration,
  AVG(getOpenHours(pro_problemno)) AS averageOpenHours,
  MAX(pro_total_activity_duration_hours) AS maxDuration,
  MAX(getOpenHours(pro_problemno)) AS maxOpenHours,
  MIN(pro_total_activity_duration_hours) AS minDuration,
  MIN(getOpenHours(pro_problemno)) AS minOpenHours,
  AVG(pro_total_activity_duration_hours * (SELECT hed_hourly_labour_cost FROM headert LIMIT 1)) AS averageCost,
  SUM(pro_total_activity_duration_hours * (SELECT hed_hourly_labour_cost FROM headert LIMIT 1)) AS totalCost,
  AVG((SELECT COUNT(*) FROM callactivity WHERE callactivity.`caa_problemno` = problem.`pro_problemno` AND callactivity.`caa_callacttypeno`= 11)) AS averageCustomerContact,
  AVG((SELECT COUNT(*) FROM callactivity WHERE callactivity.`caa_problemno` = problem.`pro_problemno` AND callactivity.`caa_callacttypeno` = 8)) AS averageRemoteSupport,
  AVG((SELECT COUNT(*) FROM callactivity WHERE callactivity.`caa_problemno` = problem.`pro_problemno` AND callactivity.`caa_callacttypeno` IN (8,11,18))) AS averageActivities
FROM
  problem
WHERE `pro_custno` = 520
  AND CAST(
    problem.`pro_date_raised` AS DATE
  ) BETWEEN '2018-01-01'
  AND '2019-01-01' AND pro_rootcauseno  = 58 AND `pro_status` IN ('F', 'C') UNION 
  SELECT
  'leavers',
  SUM(`pro_rootcauseno` = 62) AS leavers,
  AVG(pro_total_activity_duration_hours) AS averageDuration,
  AVG(getOpenHours(pro_problemno)) AS averageOpenHours,
  MAX(pro_total_activity_duration_hours) AS maxDuration,
  MAX(getOpenHours(pro_problemno)) AS maxOpenHours,
  MIN(pro_total_activity_duration_hours) AS minDuration,
  MIN(getOpenHours(pro_problemno)) AS minOpenHours,
  AVG(pro_total_activity_duration_hours * (SELECT hed_hourly_labour_cost FROM headert LIMIT 1)) AS averageCost,
  SUM(pro_total_activity_duration_hours * (SELECT hed_hourly_labour_cost FROM headert LIMIT 1)) AS totalCost,
  AVG((SELECT COUNT(*) FROM callactivity WHERE callactivity.`caa_problemno` = problem.`pro_problemno` AND callactivity.`caa_callacttypeno` = 11)) AS averageCustomerContact,
  AVG((SELECT COUNT(*) FROM callactivity WHERE callactivity.`caa_problemno` = problem.`pro_problemno` AND callactivity.`caa_callacttypeno` = 8)) AS averageRemoteSupport,
  AVG((SELECT COUNT(*) FROM callactivity WHERE callactivity.`caa_problemno` = problem.`pro_problemno` AND callactivity.`caa_callacttypeno` IN (8,11,18))) AS averageActivities
FROM
  problem
WHERE `pro_custno` = 520
  AND CAST(
    problem.`pro_date_raised` AS DATE
  ) BETWEEN '2018-01-01'
  AND '2019-01-01' AND pro_rootcauseno  =  62 AND `pro_status` IN ('F', 'C')
 ";
    }
}
