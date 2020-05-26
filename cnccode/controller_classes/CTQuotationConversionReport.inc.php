<?php
/**
 * Customer Activity Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUQuotationConversionReport.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');

require_once("Mail.php");
require_once("Mail/mime.php");

// Actions
class CTQuotationConversionReport extends CTCNC
{
    /** @var DSForm */
    public $dsSearchForm;
    /** @var BUQuotationConversionReport */
    public $buQuotationConversionReport;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = REPORTS_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(508);
        $this->buQuotationConversionReport = new BUQuotationConversionReport($this);
        $this->dsSearchForm = new DSForm($this);
        $this->buQuotationConversionReport->initialiseSearchForm($this->dsSearchForm);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            default:
                $this->displaySearchForm();
                break;
        }
    }

    /**
     * @throws Exception
     */
    function displaySearchForm()
    {
        $dsSearchForm = &$this->dsSearchForm; // ref to global

        $this->setMethodName('displaySearchForm');

        $quotationConversionData = array();

        if ($_POST) {

            if (!$this->dsSearchForm->populateFromArray($this->getParam('search'))) {
                $this->setFormErrorOn();
            } else {

                $quotationConversionData =
                    $this->buQuotationConversionReport->getConversionData(
                        $this->dsSearchForm->getValue(BUQuotationConversionReport::searchFormFromDate),
                        $this->dsSearchForm->getValue(BUQuotationConversionReport::searchFormToDate),
                        $this->dsSearchForm->getValue(BUQuotationConversionReport::searchFormCustomerID)
                    );
            }

        }//end if ( $_POST )

        $this->setTemplateFiles(
            array(
                'QuotationConversionReport' => 'QuotationConversionReport.inc'
            )
        );


        $urlCustomerPopup = Controller::buildLink(
            CTCNC_PAGE_CUSTOMER,
            array(
                'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );

        $urlSubmit = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTCNC_ACT_SEARCH
            )
        );

        $this->setPageTitle('Quotation Conversion Report');

        $dsSearchForm->initialise();
        $customerString = null;
        if ($dsSearchForm->getValue(BUQuotationConversionReport::searchFormCustomerID) != 0) {
            $buCustomer = new BUCustomer($this);
            $dsCustomer = new DataSet($this);
            $buCustomer->getCustomerByID(
                $dsSearchForm->getValue(BUQuotationConversionReport::searchFormCustomerID),
                $dsCustomer
            );
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }

        $this->template->set_var(
            array(
                'formError'         => $this->formError,
                'customerID'        => $dsSearchForm->getValue(BUQuotationConversionReport::searchFormCustomerID),
                'customerIDMessage' => $dsSearchForm->getMessage(BUQuotationConversionReport::searchFormCustomerID),
                'customerString'    => $customerString,
                'fromDate'          => $dsSearchForm->getValue(BUQuotationConversionReport::searchFormFromDate),
                'fromDateMessage'   => $dsSearchForm->getMessage(BUQuotationConversionReport::searchFormFromDate),
                'toDate'            => $dsSearchForm->getValue(BUQuotationConversionReport::searchFormToDate),
                'toDateMessage'     => $dsSearchForm->getMessage(BUQuotationConversionReport::searchFormToDate),
                'urlCustomerPopup'  => $urlCustomerPopup,
                'urlSubmit'         => $urlSubmit
            )
        );

        if (count($quotationConversionData)) {
            $this->template->set_block('QuotationConversionReport', 'rowBlock', 'rows');

            foreach ($quotationConversionData as $row) {

                if ($row['quoteCount'] != 0) {
                    $percentage = $row['conversionCount'] / $row['quoteCount'] * 100;
                } else {
                    $percentage = 0;
                }

                $this->template->set_var(
                    array(
                        'month'                => $row['month'],
                        'year'                 => $row['year'],
                        'quoteCount'           => $row['quoteCount'],
                        'conversionCount'      => $row['conversionCount'],
                        'conversionPercentage' => number_format($percentage, 2)
                    )

                );

                $this->template->parse('rows', 'rowBlock', true);
            }
        }
        $this->template->parse('CONTENTS', 'QuotationConversionReport', true);
        $this->parsePage();
    }
}
