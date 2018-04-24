<?php
/**
 * Customer Activity Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUServiceDeskReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerNew.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTActivityProfitReport extends CTCNC
{
    var $dsPrintRange = '';
    var $dsSearchForm = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buServiceDeskReport = new BUServiceDeskReport($this);

        $this->dsSearchForm = new DSForm ($this);
        $this->dsSearchForm->addColumn('customerID', DA_STRING, DA_ALLOW_NULL);
        $this->dsSearchForm->addColumn('fromDate', DA_DATE, DA_ALLOW_NULL);
        $this->dsSearchForm->addColumn('toDate', DA_DATE, DA_ALLOW_NULL);
        $this->dsSearchForm->setValue('customerID', '');
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->search();
    }

    function search()
    {

        $this->setMethodName('search');

        if (isset ($_REQUEST ['searchForm']) == 'POST') {

            if (!$this->dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {

                $this->setFormErrorOn();

            } else {
                $this->buServiceDeskReport->fromDate = $this->dsSearchForm->getValue('fromDate');
                $this->buServiceDeskReport->toDate = $this->dsSearchForm->getValue('toDate');
                $this->buServiceDeskReport->customerID = $this->dsSearchForm->getValue('customerID');

                $report = $this->buServiceDeskReport->getCustomerReport();

            }

        }

        if ($this->dsSearchForm->getValue('fromDate') == '') {
            $this->dsSearchForm->setUpdateModeUpdate();
            $this->dsSearchForm->setValue('fromDate', date('Y-m-d', strtotime("-1 month")));
            $this->dsSearchForm->post();
        }
        if (!$this->dsSearchForm->getValue('toDate')) {
            $this->dsSearchForm->setUpdateModeUpdate();
            $this->dsSearchForm->setValue('toDate', date('Y-m-d'));
            $this->dsSearchForm->post();
        }


        $this->setMethodName('displaySearchForm');

        $this->setTemplateFiles(
            array(
                'ServiceDeskReportCustomerPage' => 'ServiceDeskReportCustomerPage.inc'
            )
        );

        $urlSubmit = $this->buildLink($_SERVER ['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));

        $this->setPageTitle('Customer Service Desk Report');

        if ($this->dsSearchForm->getValue('customerID') != 0) {
            $buCustomer = new BUCustomer ($this);
            $buCustomer->getCustomerByID($this->dsSearchForm->getValue('customerID'), $dsCustomer);
            $customerString = $dsCustomer->getValue('name');
        }

        $this->template->set_var(
            array(
                'formError' => $this->formError,
                'customerID' => $this->dsSearchForm->getValue('customerID'),
                'customerString' => $customerString,
                'fromDate' => Controller::dateYMDtoDMY($this->dsSearchForm->getValue('fromDate')),
                'fromDateMessage' => $this->dsSearchForm->getMessage('fromDate'),
                'toDate' => Controller::dateYMDtoDMY($this->dsSearchForm->getValue('toDate')),
                'toDateMessage' => $this->dsSearchForm->getMessage('toDate'),
                'urlCustomerPopup' => $urlCustomerPopup,
                'urlSubmit' => $urlSubmit,
                'report' => $report
            )
        );

        $this->template->parse('CONTENTS', 'CustomerServiceDeskReportPage', true);

        $this->parsePage();

    } // end function displaySearchForm

} // end of class
?>