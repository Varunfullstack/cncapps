<?php
/**
 * CustomerAnalysis Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUStartersAndLeaversReport.php');
require_once($cfg ['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTStartersAndLeaversReport extends CTCNC
{
    /**
     * @var BUStartersAndLeaversReport
     */
    public $buStartersAndLeaversReport;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "reports",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }

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
        $buStartersAndLeaversReport = new BUStartersAndLeaversReport($this);
        $buStartersAndLeaversReport->initialiseSearchForm($dsSearchForm);

        $this->setTemplateFiles(array('StartersAndLeaversReport' => 'StartersAndLeaversReport'));

        if (isset($_REQUEST ['searchForm'])) {

            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                set_time_limit(240);
                $results = $buStartersAndLeaversReport->getReportData($dsSearchForm);

                if ($this->getParam('Search') == 'Generate CSV') {

                    $template = new Template ($cfg["path_templates"], "remove");

                    $template->set_file('page', 'StartersAndLeaversReport.inc.csv');

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
                    Header('Content-Disposition: attachment; filename=StartersAndLeaversReport.csv');
                    echo $output;
                    exit;
                } else { // Screen Report
                    $this->template->setVar(
                        [
                            "startersQuantity"           => $results[0]['quantity'],
                            "startersMaxDuration"        => number_format($results[0]['maxDuration'], 2),
                            "startersAvgDuration"        => number_format($results[0]['avgDuration'], 2),
                            "startersMinDuration"        => number_format($results[0]['minDuration'], 2),
                            "startersMaxOpenHours"       => number_format($results[0]['maxOpenHours'], 2),
                            "startersAvgOpenHours"       => number_format($results[0]['avgOpenHours'], 2),
                            "startersMinOpenHours"       => number_format($results[0]['minOpenHours'], 2),
                            "startersAvgCost"            => number_format($results[0]['avgCost'], 2),
                            "startersTotalCost"          => number_format($results[0]['totalCost'], 2),
                            "startersAvgCustomerContact" => number_format($results[0]['avgCustomerContact'], 2),
                            "startersAvgRemoteSupport"   => number_format($results[0]['avgRemoteSupport'], 2),
                            "startersAvgActivities"      => number_format($results[0]['avgActivities'], 2),
                            "leaversQuantity"            => number_format($results[1]['quantity'], 2),
                            "leaversMaxDuration"         => number_format($results[1]['maxDuration'], 2),
                            "leaversAvgDuration"         => number_format($results[1]['avgDuration'], 2),
                            "leaversMinDuration"         => number_format($results[1]['minDuration'], 2),
                            "leaversMaxOpenHours"        => number_format($results[1]['maxOpenHours'], 2),
                            "leaversAvgOpenHours"        => number_format($results[1]['avgOpenHours'], 2),
                            "leaversMinOpenHours"        => number_format($results[1]['minOpenHours'], 2),
                            "leaversAvgCost"             => number_format($results[1]['avgCost'], 2),
                            "leaversTotalCost"           => number_format($results[1]['totalCost'], 2),
                            "leaversAvgCustomerContact"  => number_format($results[1]['avgCustomerContact'], 2),
                            "leaversAvgRemoteSupport"    => number_format($results[1]['avgRemoteSupport'], 2),
                            "leaversAvgActivities"       => number_format($results[1]['avgActivities'], 2),
                            "totalQuantity"              => number_format(
                                $results[0]['quantity'] + $results[1]['quantity'],
                                2
                            ),
                            "totalMaxDuration"           => number_format(
                                $results[0]['maxDuration'] > $results[1]['maxDuration'] ? $results[0]['maxDuration'] : $results[1]['maxDuration'],
                                2
                            ),
                            "totalAvgDuration"           => number_format(
                                ($results[0]['avgDuration'] + $results[1]['avgDuration']) / 2,
                                2
                            ),
                            "totalMinDuration"           => number_format(
                                $results[0]['minDuration'] < $results[1]['minDuration'] ? $results[0]['minDuration'] : $results[1]['minDuration'],
                                2
                            ),
                            "totalMaxOpenHours"          => number_format(
                                $results[0]['maxOpenHours'] > $results[1]['maxOpenHours'] ? $results[0]['maxOpenHours'] : $results[1]['maxOpenHours'],
                                2
                            ),
                            "totalAvgOpenHours"          => number_format(
                                ($results[0]['avgOpenHours'] + $results[1]['avgOpenHours']) / 2,
                                2
                            ),
                            "totalMinOpenHours"          => number_format(
                                $results[0]['minOpenHours'] < $results[1]['minOpenHours'] ? $results[0]['minOpenHours'] : $results[1]['minOpenHours'],
                                2
                            ),
                            "totalAvgCost"               => number_format(
                                ($results[0]['avgCost'] + $results[1]['avgCost']) / 2,
                                2
                            ),
                            "totalTotalCost"             => number_format(
                                $results[0]['totalCost'] + $results[1]['totalCost'],
                                2
                            ),
                            "totalAvgCustomerContact"    => number_format(
                                ($results[0]['avgCustomerContact'] + $results[1]['avgCustomerContact']) / 2,
                                2
                            ),
                            "totalAvgRemoteSupport"      => number_format(
                                ($results[0]['avgRemoteSupport'] + $results[1]['avgRemoteSupport']) / 2,
                                2
                            ),
                            "totalAvgActivities"         => number_format(
                                ($results[0]['avgActivities'] + $results[1]['avgActivities']) / 2,
                                2
                            ),
                        ]
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
        if ($dsSearchForm->getValue(BUStartersAndLeaversReport::searchFormCustomerID)) {
            $buCustomer = new BUCustomer ($this);
            $dsCustomer = new DataSet($this);
            $buCustomer->getCustomerByID(
                $dsSearchForm->getValue(BUStartersAndLeaversReport::searchFormCustomerID),
                $dsCustomer
            );
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }

        $this->template->set_var(
            array(
                'formError'        => $this->formError,
                'customerID'       => $dsSearchForm->getValue(BUStartersAndLeaversReport::searchFormCustomerID),
                'customerString'   => $customerString,
                'startDate'        => self::dateYMDtoDMY($dsSearchForm->getValue(BUStartersAndLeaversReport::searchFormStartDate)),
                'endDate'          => self::dateYMDtoDMY($dsSearchForm->getValue(BUStartersAndLeaversReport::searchFormEndDate)),
                'urlCustomerPopup' => $urlCustomerPopup,
                'urlSubmit'        => $urlSubmit,
            )
        );

        $this->template->parse('CONTENTS', 'StartersAndLeaversReport', true);
        $this->parsePage();
    }
}
