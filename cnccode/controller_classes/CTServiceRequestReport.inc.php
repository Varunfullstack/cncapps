<?php
/**
 * Customer Activity Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUServiceRequestReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTServiceRequestReport extends CTCNC
{
    public $dsPrintRange;
    public $dsSearchForm;
    public $dsResults;
    public $buServiceRequestReport;

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
        $this->buServiceRequestReport = new BUServiceRequestReport ($this);
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
        $this->buServiceRequestReport->initialiseSearchForm($dsSearchForm);

        if (isset ($_REQUEST ['searchForm']) == 'POST') {
            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();

            } else {

                if (!$dsSearchForm->getValue(BUServiceRequestReport::searchFormFromDate)) {

                    $dsSearchForm->setUpdateModeUpdate();
                    $dsSearchForm->setValue(
                        BUServiceRequestReport::searchFormFromDate,
                        date(
                            'Y-m-d',
                            strtotime("-1 year")
                        )
                    );
                    $dsSearchForm->post();
                }

                if (!$dsSearchForm->getValue(BUServiceRequestReport::searchFormToDate)) {
                    $dsSearchForm->setUpdateModeUpdate();
                    $dsSearchForm->setValue(
                        BUServiceRequestReport::searchFormToDate,
                        date('Y-m-d')
                    );
                    $dsSearchForm->post();
                }

                $results = $this->buServiceRequestReport->search($dsSearchForm);

                $firstRow = true;

                Header('Content-type: text/plain');
                Header('Content-Disposition: attachment; filename=ServiceRequests.csv');

                while ($row = $results->fetch_array(MYSQLI_NUM)) {

                    if ($firstRow) {
                        echo "RaisedDate,RaisedTime,FixDate,FixTime,LastUpdated,Created,Customer,Contact,Priority,RootCause,CallReference, TotalHours,ResponseHours,MinContractResponseHours,DiffResponseContract, FixHours,FixEngineer,Contract\n";
                        $firstRow = false;
                    }
                    $row[17] = str_replace(
                        "\n",
                        "",
                        $row[17]
                    );
                    $row[17] = str_replace(
                        "\r",
                        "",
                        $row[17]
                    );
                    echo implode(
                            ',',
                            $row
                        ) . "\n";

                }

                exit;
            }

        }


        $this->setPageTitle('Service Request Report');

        $this->setTemplateFiles(
            'ServiceRequestReport',
            'ServiceRequestReport.inc'
        );
        $urlCustomerPopup = Controller::buildLink(
            CTCNC_PAGE_CUSTOMER,
            array(
                'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );


        $this->template->set_var(
            array(
                'formError'        => $this->formError,
                'customerID'       => $dsSearchForm->getValue(BUServiceRequestReport::searchFormCustomerID),
                'fromDate'         => Controller::dateYMDtoDMY(
                    $dsSearchForm->getValue(BUServiceRequestReport::searchFormFromDate)
                ),
                'fromDateMessage'  => $dsSearchForm->getMessage(BUServiceRequestReport::searchFormFromDate),
                'toDate'           => Controller::dateYMDtoDMY(
                    $dsSearchForm->getValue(BUServiceRequestReport::searchFormToDate)
                ),
                'toDateMessage'    => $dsSearchForm->getMessage(BUServiceRequestReport::searchFormToDate),
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

}
