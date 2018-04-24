<?php
/**
 * Customer Activity Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerSrAnalysisReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerNew.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTCustomerSrAnalysisReport extends CTCNC
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
        $this->buCustomerSrAnalysisReport = new BUCustomerSrAnalysisReport ($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST ['action']) {

            default :
                $this->search();
                break;
        }
    }

    function search()
    {

        $this->setMethodName('search');

        $this->buCustomerSrAnalysisReport->initialiseSearchForm($dsSearchForm);

        if (isset ($_REQUEST ['searchForm']) == 'POST') {
            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();

            } else {

                if ($dsSearchForm->getValue('fromDate') == '') {

                    $dsSearchForm->setUpdateModeUpdate();
                    $dsSearchForm->setValue('fromDate', date('Y-m-d', strtotime("-1 year")));
                    $dsSearchForm->post();
                }

                if (!$dsSearchForm->getValue('toDate')) {
                    $dsSearchForm->setUpdateModeUpdate();
                    $dsSearchForm->setValue('toDate', date('Y-m-d'));
                    $dsSearchForm->post();
                }

                if ($results = $this->buCustomerSrAnalysisReport->search($dsSearchForm)) {

                    $firstRow = true;

                    Header('Content-type: text/plain');
                    Header('Content-Disposition: attachment; filename=SRAnalysis.csv');

                    echo
                        'Period,' .
                        'No. Of Priority 1-3 SRs,' .
                        'Average Response for 1-3 SRs,' .
                        'Average Fix for 1-3 SRs,' .
                        'No. Of Priority 4 SRs,' .
                        'Contract' . "\n";
                    foreach ($results as $key => $row) {
                        echo
                            $row['period'] . ',' .
                            $row['serverCareCount1And3'] . ',' .
                            number_format($row['serverCareHoursResponded'], 1) . ',' .
                            number_format($row['serverCareHoursFix'], 1) . ',' .
                            number_format($row['serverCareCount4'], 1) . ',' .
                            'ServerCare' . "\n";

                        echo
                            $row['period'] . ',' .
                            number_format($row['serviceDeskCount1And3'], 1) . ',' .
                            number_format($row['serviceDeskHoursResponded'], 1) . ',' .
                            number_format($row['serviceDeskHoursFix'], 1) . ',' .
                            number_format($row['serviceDeskCount4'], 1) . ',' .
                            'ServiceDesk' . "\n";

                        echo
                            $row['period'] . ',' .
                            number_format($row['prepayCount1And3'], 1) . ',' .
                            number_format($row['prepayHoursResponded'], 1) . ',' .
                            number_format($row['prepayHoursFix'], 1) . ',' .
                            number_format($row['prepayCount4'], 1) . ',' .
                            'Prepay' . "\n";

                        echo
                            $row['period'] . ',' .
                            number_format($row['otherCount1And3'], 1) . ',' .
                            number_format($row['otherHoursResponded'], 1) . ',' .
                            number_format($row['otherHoursFix'], 1) . ',' .
                            number_format($row['otherCount4'], 1) . ',' .
                            'Other' . "\n";
                    }
                }
                exit;
            }

        }


        $this->setPageTitle('Customer SR Analysis Report');

        $this->setTemplateFiles('ServiceRequestReport', 'ServiceRequestReport.inc');
        $urlCustomerPopup = $this->buildLink(
            CTCNC_PAGE_CUSTOMER,
            array(
                'action' => CTCNC_ACT_DISP_CUST_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );


        $this->template->set_var(
            array(
                'formError' => $this->formError,
                'customerID' => $dsSearchForm->getValue('customerID'),
                'customerString' => $customerString,
                'fromDate' => Controller::dateYMDtoDMY($dsSearchForm->getValue('fromDate')),
                'fromDateMessage' => $dsSearchForm->getMessage('fromDate'),
                'toDate' => Controller::dateYMDtoDMY($dsSearchForm->getValue('toDate')),
                'toDateMessage' => $dsSearchForm->getMessage('toDate'),
                'urlCustomerPopup' => $urlCustomerPopup)
        );

        $this->template->parse('CONTENTS', 'ServiceRequestReport', true);
        $this->parsePage();

    } // end function displaySearchForm

} // end of class
?>