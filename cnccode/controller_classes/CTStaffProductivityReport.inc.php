<?php
/**
 * Staff Productivity Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUStaffProductivityReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomerNew.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTStaffProductivityReport extends CTCNC
{
    public $dsPrintRange = '';
    public $dsSearchForm = '';
    public $dsResults = '';
    public $BUStaffProductivityReport;

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
        $this->BUStaffProductivityReport = new BUStaffProductivityReport ($this);
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

        $this->BUStaffProductivityReport->initialiseSearchForm($dsSearchForm);

        if ($_SERVER [REQUEST_METHOD] == 'POST') {
            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();

            } else {

                if ($dsSearchForm->getValue('startDate') == '') {

                    $dsSearchForm->setUpdateModeUpdate();
                    $dsSearchForm->setValue('startDate', date('Y-m-d', strtotime("-1 year")));
                    $dsSearchForm->post();
                }

                if (!$dsSearchForm->getValue('endDate')) {
                    $dsSearchForm->setUpdateModeUpdate();
                    $dsSearchForm->setValue('endDate', date('Y-m-d'));
                    $dsSearchForm->post();
                }

                $results = $this->BUStaffProductivityReport->search($dsSearchForm);

                $firstRow = true;

                Header('Content-type: text/plain');
                Header('Content-Disposition: attachment; filename=StaffProductivity.csv');

                foreach ($results as $key => $row) {

                    if ($firstRow) {
                        echo "Name,T&M Hours,T&M Costs,T&M Billed,Pre-pay Hours,Pre-pay Cost, Pre-pay Billed,Service Desk Hours,Service Desk Cost,Server Care Hours,Server Care Cost,In-house Hours,In-house Cost,Total Hours,Total Cost,Total Billed\n";
                        $firstRow = false;
                    }
                    echo implode(',', $row) . "\n";

                }

                exit;
            }

        }


        $this->setPageTitle('Staff Productivity Report');

        $this->setTemplateFiles('StaffProductivityReport', 'StaffProductivityReport.inc');
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
                'startDate' => Controller::dateYMDtoDMY($dsSearchForm->getValue('startDate')),
                'startDateMessage' => $dsSearchForm->getMessage('startDate'),
                'endDate' => Controller::dateYMDtoDMY($dsSearchForm->getValue('endDate')),
                'endDateMessage' => $dsSearchForm->getMessage('endDate')
            )
        );

        $this->template->parse('CONTENTS', 'StaffProductivityReport', true);
        $this->parsePage();

    } // end function displaySearchForm

} // end of class
?>