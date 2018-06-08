<?php
/**
 * Customer Activity Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUActivityProfitReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerNew.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTActivityProfitReport extends CTCNC
{
    var $dsPrintRange = '';
    var $dsSearchForm = '';
    var $dsResults = '';

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
        $this->buActivityProfitReport = new BUActivityProfitReport ($this);
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
            case 'drillDown' :
                $this->drillDown();
                break;
            case 'drillDownInvoices' :
                $this->drillDownInvoices();
                break;
            default :
                $this->displaySearchForm();
                break;
        }
    }

    function search()
    {

        $this->setMethodName('search');

        $this->buActivityProfitReport->initialiseSearchForm($this->dsSearchForm);
        if (isset ($_REQUEST ['searchForm']) == 'POST') {

            if (!$this->dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
                $this->displaySearchForm(); //redisplay with errors
                exit ();
            }

        }

        if ($_REQUEST ['CSV']) {
            $limit = false; // no row count limit
        } else {
            $limit = true;
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

        $this->dsResults =

            $this->buActivityProfitReport->search($this->dsSearchForm);

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

        $this->setTemplateFiles(array('ActivityProfitReport' => 'ActivityProfitReport.inc'));

        $urlCustomerPopup = $this->buildLink(CTCNC_PAGE_CUSTOMER,
                                             array('action' => CTCNC_ACT_DISP_CUST_POPUP, 'htmlFmt' => CT_HTML_FMT_POPUP));

        $urlSubmit = $this->buildLink($_SERVER ['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));

        $this->setPageTitle('Contract Profit Report');

        if ($dsSearchForm->rowCount() == 0) {
            $this->buActivityProfitReport->initialiseSearchForm($dsSearchForm);
        }

        if ($dsSearchForm->getValue('customerID') != 0) {
            $buCustomer = new BUCustomer ($this);
            $buCustomer->getCustomerByID($dsSearchForm->getValue('customerID'), $dsCustomer);
            $customerString = $dsCustomer->getValue(DBECustomer::Name);
        }

        $this->template->set_block('ActivityProfitReport', 'resultBlock', 'results');

        while ($this->dsResults->fetchNext()) {

            $urlDrill =
                $this->buildLink(
                    $_SERVER ['PHP_SELF'],
                    array(
                        'action' => 'drillDown',
                        'customerID' => $this->dsResults->getValue('CustomerID'),
                        'fromDate' => $dsSearchForm->getValue('fromDate'),
                        'toDate' => $dsSearchForm->getValue('toDate')
                    )
                );

            $urlDrillInvoices =
                $this->buildLink(
                    $_SERVER ['PHP_SELF'],
                    array(
                        'action' => 'drillDownInvoices',
                        'customerID' => $this->dsResults->getValue('CustomerID'),
                        'fromDate' => $dsSearchForm->getValue('fromDate'),
                        'toDate' => $dsSearchForm->getValue('toDate')
                    )
                );

            $this->template->set_var(
                array(
                    'CustomerName' => $this->dsResults->getValue('CustomerName'),
                    'SDHours' => number_format($this->dsResults->getValue('SDHours'), 2),
                    'SDCharge' => number_format($this->dsResults->getValue('SDCharge'), 2),
                    'SDProRata' => number_format($this->dsResults->getValue('SDProRata'), 2),
                    'PPHours' => number_format($this->dsResults->getValue('PPHours'), 2),
                    'PPCharge' => number_format($this->dsResults->getValue('PPCharge'), 2),
                    'SCHours' => number_format($this->dsResults->getValue('SCHours'), 2),
                    'SCCharge' => number_format($this->dsResults->getValue('SCCharge'), 2),
                    'SCProRata' => number_format($this->dsResults->getValue('SCProRata'), 2),
                    'TMHours' => number_format($this->dsResults->getValue('TMHours'), 2),
                    'TMCharge' => number_format($this->dsResults->getValue('TMCharge'), 2),
                    'TotalHours' => number_format($this->dsResults->getValue('TotalHours'), 0),
                    'CncCost' => number_format($this->dsResults->getValue('CncCost'), 2),
                    'SalesProfit' => number_format($this->dsResults->getValue('SalesProfit'), 2),
                    'ServiceProfit' => number_format($this->dsResults->getValue('ServiceProfit'), 2),
                    'urlDrill' => $urlDrill, 'urlDrillInvoices' => $urlDrillInvoices
                )
            );

            $SDHoursTotal += $this->dsResults->getValue('SDHours');
            $SDChargeTotal += $this->dsResults->getValue('SDCharge');
            $SDProRataTotal += $this->dsResults->getValue('SDProRata');
            $PPHoursTotal += $this->dsResults->getValue('PPHours');
            $PPChargeTotal += $this->dsResults->getValue('PPCharge');
            $SCHoursTotal += $this->dsResults->getValue('SCHours');
            $SCChargeTotal += $this->dsResults->getValue('SCCharge');
            $SCProRataTotal += $this->dsResults->getValue('SCProRata');
            $TMHoursTotal += $this->dsResults->getValue('TMHours');
            $TMChargeTotal += $this->dsResults->getValue('TMCharge');
            $TotalHoursTotal += $this->dsResults->getValue('TotalHours');
            $CncCostTotal += $this->dsResults->getValue('CncCost');
            $SalesProfitTotal += $this->dsResults->getValue('SalesProfit');
            $ServiceProfitTotal += $this->dsResults->getValue('ServiceProfit');

            $this->template->parse('results', 'resultBlock', true);
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
                'SDHoursTotal' => number_format($SDHoursTotal, 2),
                'SDChargeTotal' => number_format($SDChargeTotal, 2),
                'SDProRataTotal' => number_format($SDProRataTotal, 2),
                'PPHoursTotal' => number_format($PPHoursTotal, 2),
                'PPChargeTotal' => number_format($PPChargeTotal, 2),
                'SCHoursTotal' => number_format($SCHoursTotal, 2),
                'SCChargeTotal' => number_format($SCChargeTotal, 2),
                'SCProRataTotal' => number_format($SCProRataTotal, 2),
                'TMHoursTotal' => number_format($TMHoursTotal, 2),
                'TMChargeTotal' => number_format($TMChargeTotal, 2),
                'TotalHoursTotal' => number_format($TotalHoursTotal, 2),
                'CncCostTotal' => number_format($CncCostTotal, 2),
                'SalesProfitTotal' => number_format($SalesProfitTotal, 2),
                'ServiceProfitTotal' => number_format($ServiceProfitTotal, 2)
            )
        );

        $this->template->parse('CONTENTS', 'ActivityProfitReport', true);
        $this->parsePage();
    } // end function displaySearchForm


    /**
     * Display drill down
     * @access private
     */
    function drillDown()
    {
        $dsSearchForm = &$this->dsSearchForm; // ref to global


        $this->setMethodName('drillDown');

        $dsResults = $this->buActivityProfitReport->searchDrill($_REQUEST ['customerID'],
                                                                $_REQUEST ['fromDate'],
                                                                $_REQUEST ['toDate']);

        $this->setTemplateFiles(array('ActivityProfitReportDrill' => 'ActivityProfitReportDrill.inc'));

        $this->setPageTitle('Contract Profit Report Drill Down');

        if ($dsSearchForm->rowCount() == 0) {
            $this->buActivityProfitReport->initialiseSearchForm($dsSearchForm);
        }

        $this->template->set_block('ActivityProfitReportDrill', 'resultBlock', 'results');

        while ($dsResults->fetchNext()) {

            $this->template->set_var(array('ActivityID' => $dsResults->getValue('ActivityID'), 'Date' => $dsResults->getValue('Date'), 'SDHours' => number_format($dsResults->getValue('SDHours'),
                                                                                                                                                                  2), 'SDCharge' => number_format($dsResults->getValue('SDCharge'),
                                                                                                                                                                                                  2), 'PPHours' => number_format($dsResults->getValue('PPHours'),
                                                                                                                                                                                                                                 2), 'PPCharge' => number_format($dsResults->getValue('PPCharge'),
                                                                                                                                                                                                                                                                 2), 'SCHours' => number_format($dsResults->getValue('SCHours'),
                                                                                                                                                                                                                                                                                                2), 'SCCharge' => number_format($dsResults->getValue('SCCharge'),
                                                                                                                                                                                                                                                                                                                                2), 'TMHours' => number_format($dsResults->getValue('TMHours'),
                                                                                                                                                                                                                                                                                                                                                               2), 'TMCharge' => number_format($dsResults->getValue('TMCharge'),
                                                                                                                                                                                                                                                                                                                                                                                               2)));

            $SDHoursTotal += $dsResults->getValue('SDHours');
            $SDChargeTotal += $dsResults->getValue('SDCharge');
            $SDProRataTotal += $dsResults->getValue('SDProRata');
            $PPHoursTotal += $dsResults->getValue('PPHours');
            $PPChargeTotal += $dsResults->getValue('PPCharge');
            $SCHoursTotal += $dsResults->getValue('SCHours');
            $SCChargeTotal += $dsResults->getValue('SCCharge');
            $SCProRataTotal += $dsResults->getValue('SCProRata');
            $TMHoursTotal += $dsResults->getValue('TMHours');
            $TMChargeTotal += $dsResults->getValue('TMCharge');

            $this->template->parse('results', 'resultBlock', true);
        }
        $buCustomer = new BUCustomer ($this);

        $buCustomer->getCustomerByID($_REQUEST ['customerID'], $dsCustomer);

        $this->template->set_var(
            array(
                'formError' => $this->formError,
                'customerName' => $dsCustomer->getValue(DBECustomer::Name),
                'fromDate' => Controller::dateYMDtoDMY($_REQUEST ['fromDate']),
                'toDate' => Controller::dateYMDtoDMY($_REQUEST ['toDate']),
                'SDHoursTotal' => number_format($SDHoursTotal, 2),
                'SDChargeTotal' => number_format($SDChargeTotal, 2),
                'SDProRataTotal' => number_format($SDProRataTotal, 2),
                'PPHoursTotal' => number_format($PPHoursTotal, 2),
                'PPChargeTotal' => number_format($PPChargeTotal, 2),
                'SCHoursTotal' => number_format($SCHoursTotal, 2),
                'SCChargeTotal' => number_format($SCChargeTotal, 2),
                'SCProRataTotal' => number_format($SCProRataTotal, 2),
                'TMHoursTotal' => number_format($TMHoursTotal, 2),
                'TMChargeTotal' => number_format($TMChargeTotal, 2))
        );

        $this->template->parse('CONTENTS', 'ActivityProfitReportDrill', true);
        $this->parsePage();
    } // end function displaySearchForm

    /**
     * Display drill down
     * @access private
     */
    function drillDownInvoices()
    {
        $dsSearchForm = &$this->dsSearchForm; // ref to global


        $this->setMethodName('drillDownInvoices');

        $results = $this->buActivityProfitReport->searchDrillInvoices(
            $_REQUEST ['customerID'],
            $_REQUEST ['fromDate'],
            $_REQUEST ['toDate']
        );

        $this->setTemplateFiles(array('ActivityProfitReportInvoiceDrill' => 'ActivityProfitReportInvoiceDrill.inc'));

        $this->setPageTitle('Contract Profit Report Drill Down');

        if ($dsSearchForm->rowCount() == 0) {
            $this->buActivityProfitReport->initialiseSearchForm($dsSearchForm);
        }

        $this->template->set_var(
            array(
                'ProductSalesTurnover' => number_format($results ['ProductSalesTurnover'], 2),
                'ProductSalesProfit' => number_format($results ['ProductSalesProfit'], 2),
                'InternetRevenueTurnover' => number_format($results ['InternetRevenueTurnover'], 2),
                'InternetRevenueProfit' => number_format($results ['InternetRevenueProfit'], 2),
                'ManagedServiceRevenueTurnover' => number_format($results ['ManagedServiceRevenueTurnover'], 2),
                'ManagedServiceRevenueProfit' => number_format($results ['ManagedServiceRevenueProfit'], 2),
                'MaintAndGenSupportTurnover' => number_format($results ['MaintAndGenSupportTurnover'], 2),
                'MaintAndGenSupportProfit' => number_format($results ['MaintAndGenSupportProfit'], 2)
            )
        );

        $buCustomer = new BUCustomer ($this);

        $buCustomer->getCustomerByID($_REQUEST ['customerID'], $dsCustomer);

        $this->template->set_var(
            array(
                'formError' => $this->formError,
                'customerName' => $dsCustomer->getValue(DBECustomer::Name),
                'fromDate' => Controller::dateYMDtoDMY($_REQUEST ['fromDate']),
                'toDate' => Controller::dateYMDtoDMY($_REQUEST ['toDate']
                )
            )
        );

        $this->template->parse('CONTENTS', 'ActivityProfitReportInvoiceDrill', true);
        $this->parsePage();
    } // end function salesDrillDown


} // end of class
?>