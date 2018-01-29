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
require_once($cfg ["path_bu"] . "/BUMail.inc.php");

class CTServiceDeskReportCustomer extends CTCNC
{
    public $dsPrintRange;
    public $dsSearchForm;
    public $buServiceDeskReport;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
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
                $this->buServiceDeskReport->startDate = $this->dsSearchForm->getValue('fromDate');
                $this->buServiceDeskReport->endDate = $this->dsSearchForm->getValue('toDate');
                $this->buServiceDeskReport->customerID = $this->dsSearchForm->getValue('customerID');

                $report = $this->buServiceDeskReport->getCustomerReport();

                $buMail = new BUMail($this);

                $senderEmail = CONFIG_SUPPORT_EMAIL;
                $senderName = 'CNC Support Department';

                $dbeUser = new DBEUser($this);
                $loggedInUserID = $GLOBALS ['auth']->is_authenticated();
                $dbeUser->getRow($loggedInUserID);
                $toEmail = $dbeUser->getValue('username') . '@' . CONFIG_PUBLIC_DOMAIN;

                $hdrs = array(
                    'From' => $senderEmail,
                    'To' => $toEmail,
                    'Subject' => 'Service Desk Report ' . $this->buServiceDeskReport->getPeriod() . ' - ' . $this->buServiceDeskReport->getCustomerName(),
                    'Date' => date("r"),
                    'Content-Type' => 'text/html; charset=UTF-8'
                );

                $buMail->mime->setHTMLBody($report);

                $body = $buMail->mime->get();

                $hdrs = $buMail->mime->headers($hdrs);

                $buMail->putInQueue(
                    $senderEmail,
                    $toEmail,
                    $hdrs,
                    $body
                );


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
        $urlCustomerPopup = $this->buildLink(CTCNC_PAGE_CUSTOMER, array('action' => CTCNC_ACT_DISP_CUST_POPUP, 'htmlFmt' => CT_HTML_FMT_POPUP));

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

        $this->template->parse('CONTENTS', 'ServiceDeskReportCustomerPage', true);

        $this->parsePage();

    } // end function displaySearchForm

} // end of class
?>