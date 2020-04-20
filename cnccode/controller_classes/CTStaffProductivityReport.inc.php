<?php
/**
 * Staff Productivity Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUStaffProductivityReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg ['path_bu'] . '/BUExpense.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTStaffProductivityReport extends CTCNC
{
    public $dsPrintRange;
    public $dsSearchForm;
    public $dsResults;
    public $BUStaffProductivityReport;

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
        $roles = [
            "accounts",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(905);
        $this->BUStaffProductivityReport = new BUStaffProductivityReport ($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {

            default :
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
        $dsSearchForm = new DSForm($this);
        $this->BUStaffProductivityReport->initialiseSearchForm($dsSearchForm);

        if ($_SERVER ['REQUEST_METHOD'] == 'POST') {
            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                if (!$dsSearchForm->getValue(BUStaffProductivityReport::searchFormStartDate)) {

                    $dsSearchForm->setUpdateModeUpdate();
                    $dsSearchForm->setValue(
                        BUStaffProductivityReport::searchFormStartDate,
                        date(
                            'Y-m-d',
                            strtotime("-1 year")
                        )
                    );
                    $dsSearchForm->post();
                }

                if (!$dsSearchForm->getValue(BUStaffProductivityReport::searchFormEndDate)) {
                    $dsSearchForm->setUpdateModeUpdate();
                    $dsSearchForm->setValue(
                        BUStaffProductivityReport::searchFormEndDate,
                        date('Y-m-d')
                    );
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
                    echo implode(
                            ',',
                            $row
                        ) . "\n";

                }

                exit;
            }

        }


        $this->setPageTitle('Staff Productivity Report');

        $this->setTemplateFiles(
            'StaffProductivityReport',
            'StaffProductivityReport.inc'
        );

        $this->template->set_var(
            array(
                'formError'        => $this->formError,
                'startDate'        => Controller::dateYMDtoDMY(
                    $dsSearchForm->getValue(BUStaffProductivityReport::searchFormStartDate)
                ),
                'startDateMessage' => $dsSearchForm->getMessage(BUStaffProductivityReport::searchFormStartDate),
                'endDate'          => Controller::dateYMDtoDMY(
                    $dsSearchForm->getValue(BUStaffProductivityReport::searchFormEndDate)
                ),
                'endDateMessage'   => $dsSearchForm->getMessage(BUStaffProductivityReport::searchFormEndDate)
            )
        );

        $this->template->parse(
            'CONTENTS',
            'StaffProductivityReport',
            true
        );
        $this->parsePage();

    } // end function displaySearchForm

}
