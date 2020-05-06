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
require_once($cfg ['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');
require_once($cfg ["path_bu"] . "/BUMail.inc.php");

class CTServiceDeskReportCustomer extends CTCNC
{
    const searchFormCustomerID = "customerID";
    const searchFormFromDate = "fromDate";
    const searchFormToDate = "toDate";

    public $dsPrintRange;
    public $dsSearchForm;
    public $buServiceDeskReport;

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
        $this->buServiceDeskReport = new BUServiceDeskReport($this);
        $this->dsSearchForm = new DSForm ($this);
        $this->dsSearchForm->addColumn(self::searchFormCustomerID, DA_STRING, DA_ALLOW_NULL);
        $this->dsSearchForm->addColumn(self::searchFormFromDate, DA_DATE, DA_ALLOW_NULL);
        $this->dsSearchForm->addColumn(self::searchFormToDate, DA_DATE, DA_ALLOW_NULL);
        $this->dsSearchForm->setValue(self::searchFormCustomerID, '');
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
        $report = null;
        $this->setMethodName('search');
        if (isset ($_REQUEST ['searchForm']) == 'POST') {
            if (!$this->dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                $this->buServiceDeskReport->startDate = $this->dsSearchForm->getValue(self::searchFormFromDate);
                $this->buServiceDeskReport->endDate = $this->dsSearchForm->getValue(self::searchFormToDate);
                $this->buServiceDeskReport->customerID = $this->dsSearchForm->getValue(self::searchFormCustomerID);

                $report = $this->buServiceDeskReport->getCustomerReport();

                $buMail = new BUMail($this);

                $senderEmail = CONFIG_SUPPORT_EMAIL;

                $dbeUser = new DBEUser($this);
                $loggedInUserID = $GLOBALS ['auth']->is_authenticated();
                $dbeUser->getRow($loggedInUserID);
                $toEmail = $dbeUser->getValue(DBEUser::username) . '@' . CONFIG_PUBLIC_DOMAIN;

                $hdrs = array(
                    'From'         => $senderEmail,
                    'To'           => $toEmail,
                    'Subject'      => 'Service Desk Report ' . $this->buServiceDeskReport->getPeriod(
                        ) . ' - ' . $this->buServiceDeskReport->getCustomerName(),
                    'Date'         => date("r"),
                    'Content-Type' => 'text/html; charset=UTF-8'
                );

                $buMail->mime->setHTMLBody($report);

                $mime_params = array(
                    'text_encoding' => '7bit',
                    'text_charset'  => 'UTF-8',
                    'html_charset'  => 'UTF-8',
                    'head_charset'  => 'UTF-8'
                );
                $body = $buMail->mime->get($mime_params);

                $hdrs = $buMail->mime->headers($hdrs);

                $buMail->putInQueue(
                    $senderEmail,
                    $toEmail,
                    $hdrs,
                    $body
                );


            }

        }

        if ($this->dsSearchForm->getValue(self::searchFormFromDate) == '') {
            $this->dsSearchForm->setUpdateModeUpdate();
            $this->dsSearchForm->setValue(self::searchFormFromDate, date('Y-m-d', strtotime("-1 month")));
            $this->dsSearchForm->post();
        }
        if (!$this->dsSearchForm->getValue(self::searchFormToDate)) {
            $this->dsSearchForm->setUpdateModeUpdate();
            $this->dsSearchForm->setValue(self::searchFormToDate, date('Y-m-d'));
            $this->dsSearchForm->post();
        }


        $this->setMethodName('displaySearchForm');

        $this->setTemplateFiles(
            array(
                'ServiceDeskReportCustomerPage' => 'ServiceDeskReportCustomerPage.inc'
            )
        );

        $urlSubmit = Controller::buildLink($_SERVER ['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));

        $this->setPageTitle('Customer Service Desk Report');
        $customerString = null;
        if ($this->dsSearchForm->getValue(self::searchFormCustomerID) != 0) {
            $dsCustomer = new DataSet($this);
            $buCustomer = new BUCustomer ($this);
            $buCustomer->getCustomerByID($this->dsSearchForm->getValue(self::searchFormCustomerID), $dsCustomer);
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }
        $urlCustomerPopup = Controller::buildLink(
            CTCNC_PAGE_CUSTOMER,
            array('action' => CTCNC_ACT_DISP_CUST_POPUP, 'htmlFmt' => CT_HTML_FMT_POPUP)
        );

        $this->template->set_var(
            array(
                'formError'        => $this->formError,
                'customerID'       => $this->dsSearchForm->getValue(self::searchFormCustomerID),
                'customerString'   => $customerString,
                'fromDate'         => $this->dsSearchForm->getValue(self::searchFormFromDate),
                'fromDateMessage'  => $this->dsSearchForm->getMessage(self::searchFormFromDate),
                'toDate'           => $this->dsSearchForm->getValue(self::searchFormToDate),
                'toDateMessage'    => $this->dsSearchForm->getMessage(self::searchFormToDate),
                'urlCustomerPopup' => $urlCustomerPopup,
                'urlSubmit'        => $urlSubmit,
                'report'           => $report
            )
        );

        $this->template->parse('CONTENTS', 'ServiceDeskReportCustomerPage', true);

        $this->parsePage();
    }
}
