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
require_once($cfg ['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTCustomerSrAnalysisReport extends CTCNC
{
    var $dsPrintRange = '';
    var $dsSearchForm = '';
    var $dsResults = '';

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
        if (!$this->isUserSDManager()) {
            $roles = [
                "reports",
            ];
            if (!self::hasPermissions($roles)) {
                Header("Location: /NotAllowed.php");
                exit;
            }
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
                    $dsSearchForm->setValue(
                        'fromDate',
                        date(
                            'Y-m-d',
                            strtotime("-1 year")
                        )
                    );
                    $dsSearchForm->post();
                }

                if (!$dsSearchForm->getValue('toDate')) {
                    $dsSearchForm->setUpdateModeUpdate();
                    $dsSearchForm->setValue(
                        'toDate',
                        date('Y-m-d')
                    );
                    $dsSearchForm->post();
                }

                if ($results = $this->buCustomerSrAnalysisReport->search($dsSearchForm)) {


                    Header('Content-type: text/plain');
                    Header('Content-Disposition: attachment; filename=SRAnalysis.csv');

                    echo
                        'Period,' .
                        'No. Of Priority 1 SRs,' .
                        'Average Response for 1 SRs,' .
                        'Average Fix for 1 SRs,' .
                        'No. Of Priority 2 SRs,' .
                        'Average Response for 2 SRs,' .
                        'Average Fix for 2 SRs,' .
                        'No. Of Priority 3 SRs,' .
                        'Average Response for 3 SRs,' .
                        'Average Fix for 3 SRs,' .
                        'No. Of Priority 4 SRs,' .
                        'Average Response for 4 SRs,' .
                        'Average Fix for 4 SRs,' .
                        'Contract' . "\n";


                    foreach ($results as $row) {
                        foreach ($row['types'] as $type => $value) {
                            echo $row['period'] . ',';
                            foreach ($value as $priority => $data) {

                                echo $data['count'] . ',' .
                                    number_format(
                                        $data['hoursResponded'],
                                        1
                                    ) . ',' .
                                    number_format(
                                        $data['hoursFix'],
                                        1
                                    ) . ',';
                            }
                            echo $type . "\n";
                        }
                    }
                }
            }
            exit;
        }


        $this->setPageTitle('Customer SR Analysis Report');

        $this->setTemplateFiles(
            'ServiceRequestReport',
            'ServiceRequestReport.inc'
        );
        $urlCustomerPopup = $this->buildLink(
            CTCNC_PAGE_CUSTOMER,
            array(
                'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );


        $this->template->set_var(
            array(
                'formError'        => $this->formError,
                'customerID'       => $dsSearchForm->getValue('customerID'),
                'customerString'   => $customerString,
                'fromDate'         => Controller::dateYMDtoDMY($dsSearchForm->getValue('fromDate')),
                'fromDateMessage'  => $dsSearchForm->getMessage('fromDate'),
                'toDate'           => Controller::dateYMDtoDMY($dsSearchForm->getValue('toDate')),
                'toDateMessage'    => $dsSearchForm->getMessage('toDate'),
                'urlCustomerPopup' => $urlCustomerPopup
            )
        );

        $this->template->parse(
            'CONTENTS',
            'ServiceRequestReport',
            true
        );
        $this->parsePage();

    } // end function displaySearchForm

} // end of class
?>