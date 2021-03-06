<?php
/**
 * CustomerAnalysis Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
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
        if (!self::isSdManager()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(214);
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

        setlocale(LC_MONETARY, "en_GB");

        if (isset($_REQUEST ['searchForm'])) {

            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                set_time_limit(240);
                $results = $buStartersAndLeaversReport->getReportData($dsSearchForm);
                $this->template->set_block(
                    "StartersAndLeaversReport",
                    "rowBlock",
                    "rows"
                );
                foreach ($results as $row) {
                    $quantityText = number_format($row['quantity']);
                    if (in_array($row['type'], ['starters', 'leavers'])) {

                        $url = Controller::buildLink(
                            'activity.php',
                            [
                                "action"                              => 'search',
                                "activity[1][customerID]"             => $_REQUEST['searchForm'][1]['customerID'],
                                "customerString"                      => $this->getParam('customerString'),
                                "activity[1][contractCustomerItemID]" => 99,
                                "activity[1][rootCauseID]"            => $row['type'] == 'starters' ? 58 : 62,
                                "activity[1][fromDate]"               => $_REQUEST['searchForm'][1]['startDate'],
                                "activity[1][toDate]"                 => $_REQUEST['searchForm'][1]['endDate'],
                                "Search"                              => "Search",
                                "activity[1][callActTypeID]"          => 51
                            ]
                        );

                        $quantityText = "<a href='$url' target='_blank'>$quantityText</a>";
                    }

                    $this->template->set_var(
                        [
                            "customerName"       => $row['customerName'],
                            "type"               => $row['type'],
                            "quantity"           => $quantityText,
                            "maxDuration"        => number_format($row['maxDuration'], 2),
                            "avgDuration"        => number_format($row['avgDuration'], 2),
                            "minDuration"        => number_format($row['minDuration'], 2),
                            "totalDuration"      => number_format($row['totalDuration'], 2),
                            "maxOpenHours"       => number_format($row['maxOpenHours'], 2),
                            "avgOpenHours"       => number_format($row['avgOpenHours'], 2),
                            "minOpenHours"       => number_format($row['minOpenHours'], 2),
                            "avgCost"            => utf8MoneyFormat(UK_MONEY_FORMAT, $row['avgCost']),
                            "totalCost"          => utf8MoneyFormat(UK_MONEY_FORMAT, $row['totalCost']),
                            "avgCustomerContact" => number_format($row['avgCustomerContact'], 2),
                            "avgRemoteSupport"   => number_format($row['avgRemoteSupport'], 2),
                            "avgActivities"      => number_format($row['avgActivities'], 2),
                        ]
                    );
                    $this->template->parse('rows', "rowBlock", true);
                }
            }

        }

        $urlCustomerPopup = Controller::buildLink(
            CTCNC_PAGE_CUSTOMER,
            array('action' => CTCNC_ACT_DISP_CUST_POPUP, 'htmlFmt' => CT_HTML_FMT_POPUP)
        );

        $urlSubmit = Controller::buildLink($_SERVER ['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));

        $this->setPageTitle('Starters And Leavers Report');
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
                'startDate'        => $dsSearchForm->getValue(BUStartersAndLeaversReport::searchFormStartDate),
                'endDate'          => $dsSearchForm->getValue(BUStartersAndLeaversReport::searchFormEndDate),
                'urlCustomerPopup' => $urlCustomerPopup,
                'urlSubmit'        => $urlSubmit,
            )
        );

        $this->template->parse('CONTENTS', 'StartersAndLeaversReport', true);
        $this->parsePage();
    }
}
