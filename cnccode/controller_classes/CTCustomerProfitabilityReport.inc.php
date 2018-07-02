<?php
/**
 * Customer Activity Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerProfitabilityReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTCustomerProfitabilityReport extends CTCNC
{
    var $dsPrintRange = '';
    var $dsSearchForm = '';
    var $dsResults = '';

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
        $this->buActivityProfitabilityReport = new BUCustomerProfitabilityReport ($this);
        $this->dsSearchForm = new DSForm ($this);
        $this->dsResults = new DataSet ($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST ['action']) {

            case CTCNC_ACT_SEARCH :
                $this->search();
                break;
            default :
                $this->displaySearchForm();
                break;
        }
    }

    function search()
    {

        $this->setMethodName('search');

        $this->buActivityProfitabilityReport->initialiseSearchForm($this->dsSearchForm);
        if (isset ($_REQUEST ['searchForm']) == 'POST') {

            if (!$this->dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
                $this->displaySearchForm(); //redisplay with errors
                exit ();
            }

        }

        if ($this->dsSearchForm->getValue('fromDate') == '') {
            $this->dsSearchForm->setUpdateModeUpdate();
            $this->dsSearchForm->setValue('fromDate', date('Y-m-d', strtotime("-1 year")));
            $this->dsSearchForm->post();
        }
        if (!$this->dsSearchForm->getValue('toDate')) {
            $this->dsSearchForm->setUpdateModeUpdate();
            $this->dsSearchForm->setValue('toDate', date('Y-m-d'));
            $this->dsSearchForm->post();
        }

        $this->results =

            $this->buActivityProfitabilityReport->search($this->dsSearchForm);

        $this->displaySearchForm();
        exit ();
    }

    /**
     * Display search form
     * @access private
     */
    function displaySearchForm()
    {
        $dsSearchForm = &$this->dsSearchForm; // ref to global


        $this->setMethodName('displaySearchForm');

        $this->setTemplateFiles(array('CustomerProfitabilityReport' => 'CustomerProfitabilityReport.inc'));

        $urlCustomerPopup = $this->buildLink(CTCNC_PAGE_CUSTOMER,
                                             array('action' => CTCNC_ACT_DISP_CUST_POPUP, 'htmlFmt' => CT_HTML_FMT_POPUP));

        $urlSubmit = $this->buildLink($_SERVER ['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));

        $this->setPageTitle('Customer Profitability Report');

        if ($dsSearchForm->rowCount() == 0) {
            $this->buActivityProfitabilityReport->initialiseSearchForm($dsSearchForm);
        }

        if ($dsSearchForm->getValue('customerID') != 0) {
            $buCustomer = new BUCustomer ($this);
            $buCustomer->getCustomerByID($dsSearchForm->getValue('customerID'), $dsCustomer);
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }

        $totalCost = 0;
        $totalProfit = 0;
        $totalHours = 0;
        $totalCNCCost = 0;
        $totalBottomLineProfit = 0;
        $totalOtherTurnover = 0;
        $totalMaintenanceTurnover = 0;
        $totalPrePayTurnover = 0;
        $totalInternetTurnover = 0;
        $totalTAndMTurnover = 0;
        $totalServiceDeskTurnover = 0;
        $totalServerCareTurnover = 0;
        $totalManagedTurnover = 0;

        if ($this->results) {

            $this->template->set_block('CustomerProfitabilityReport', 'resultBlock', 'results');

            while ($row = $this->results->fetch_object()) {

                $this->template->set_var(
                    array(
                        'customerName' => $row->customerName,
                        'sale' => number_format($row->sale, 2),
                        'profit' => number_format($row->profit, 2),
                        'hours' => number_format($row->hours, 2),
                        'cncCost' => number_format($row->cncCost, 2),
                        'bottomLineProfit' => number_format($row->bottomLineProfit, 2),
                        'otherTurnover' => number_format($row->otherTurnover, 2),
                        'maintenanceTurnover' => number_format($row->maintenanceTurnover, 2),
                        'prePayTurnover' => number_format($row->prePayTurnover, 2),
                        'internetTurnover' => number_format($row->internetTurnover, 2),
                        'tAndMTurnover' => number_format($row->tAndMTurnover, 2),
                        'serviceDeskTurnover' => number_format($row->serviceDeskTurnover, 2),
                        'serverCareTurnover' => number_format($row->serverCareTurnover, 2),
                        'managedTurnover' => number_format($row->managedTurnover, 2)
                    )
                );

                $totalSale += $row->sale;
                $totalProfit += $profit;
                $totalHours += $row->hours;
                $totalCNCCost += $row->cncCost;
                $totalBottomLineProfit += $row->bottomLineProfit;
                $totalOtherTurnover += $row->otherTurnover;
                $totalMaintenanceTurnover += $row->maintenanceTurnover;
                $totalPrePayTurnover += $row->prePayTurnover;
                $totalInternetTurnover += $row->internetTurnover;
                $totalTAndMTurnover += $row->tAndMTurnover;
                $totalServiceDeskTurnover += $row->serviceDeskTurnover;
                $totalServerCareTurnover += $row->serverCareTurnover;
                $totalManagedTurnover += $row->managedTurnover;

                $this->template->parse('results', 'resultBlock', true);
            }

        }

        $this->template->set_var(
            array(
                'formError' => $this->formError,
                'customerID' => $dsSearchForm->getValue('customerID'),
                'customerString' => $customerString,
                'fromDate' => Controller::dateYMDtoDMY($dsSearchForm->getValue('fromDate')),
                'fromDateMessage' => $dsSearchForm->getMessage('fromDate'),
                'toDate' => Controller::dateYMDtoDMY($dsSearchForm->getValue('toDate')),
                'toDateMessage' => $dsSearchForm->getMessage('toDate'),
                'urlCustomerPopup' => $urlCustomerPopup,
                'urlSubmit' => $urlSubmit,
                'totalSale' => number_format($totalSale, 2),
                'totalProfit' => number_format($totalProfit, 2),
                'totalHours' => number_format($totalHours, 2),
                'totalCNCCost' => number_format($totalCNCCost, 2),
                'totalBottomLineProfit' => number_format($totalBottomLineProfit, 2),
                'totalOtherTurnover' => number_format($totalOtherTurnover, 2),
                'totalMaintenanceTurnover' => number_format($totalMaintenanceTurnover, 2),
                'totalPrePayTurnover' => number_format($totalPrePayTurnover, 2),
                'totalInternetTurnover' => number_format($totalInternetTurnover, 2),
                'totalTAndMTurnover' => number_format($totalTAndMTurnover, 2),
                'totalServiceDeskTurnover' => number_format($totalServiceDeskTurnover, 2),
                'totalServerCareTurnover' => number_format($totalServerCareTurnover, 2),
                'totalManagedTurnover' => number_format($totalManagedTurnover, 2)
            )
        );

        $this->template->parse('CONTENTS', 'CustomerProfitabilityReport', true);
        $this->parsePage();
    } // end function displaySearchForm


} // end of class
?>