<?php
/**
 * MIS Report controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUMISReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTMISReport extends CTCNC
{
    public $dsPrintRange;
    /** @var DSForm */
    public $dsSearchForm;
    /** @var DataSet */
    public $dsResults;
    /**
     * @var string
     */
    public $results;
    /**
     * @var BUMISReport
     */
    public $buMISReport;

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
        $this->buMISReport = new BUMISReport ($this);
        $this->dsSearchForm = new DSForm ($this);
        $this->dsResults = new DataSet ($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {

            case CTCNC_ACT_SEARCH :
                $this->search();
                break;
            default :
                $this->displaySearchForm();
                break;
        }
    }

    /**
     * @throws Exception
     */
    function search()
    {

        $this->setMethodName('search');

        $this->buMISReport->initialiseSearchForm($this->dsSearchForm);
        if (isset ($_REQUEST ['searchForm']) == 'POST') {

            if (!$this->dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
                $this->displaySearchForm(); //redisplay with errors
                exit ();
            }

        }
        set_time_limit(240);
        $this->results = $this->buMISReport->getContractCsv($this->dsSearchForm);

        $this->displaySearchForm();
        exit ();
    }

    /**
     * Display search form
     * @access private
     * @throws Exception
     */
    function displaySearchForm()
    {
        $dsSearchForm = &$this->dsSearchForm; // ref to global


        $this->setMethodName('displaySearchForm');

        $this->setTemplateFiles(array('MISReport' => 'MISReport.inc'));

        $urlCustomerPopup = Controller::buildLink(
            CTCNC_PAGE_CUSTOMER,
            array(
                'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );

        $urlSubmit = Controller::buildLink($_SERVER ['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));

        $this->setPageTitle('MIS Report');

        if ($dsSearchForm->rowCount() == 0) {
            $this->buMISReport->initialiseSearchForm($dsSearchForm);
        }
        $customerString = null;
        if ($dsSearchForm->getValue(BUMISReport::searchFormCustomerID)) {
            $buCustomer = new BUCustomer ($this);
            $dsCustomer = new DataSet($this);
            $buCustomer->getCustomerByID($dsSearchForm->getValue(BUMISReport::searchFormCustomerID), $dsCustomer);
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }

        if ($this->results) {

            Header('Content-type: text/plain');
            Header('Content-Disposition: attachment; filename=MISReport.csv');
            echo $this->results;
            exit;
        }

        $this->template->set_var(
            array(
                'formError'        => $this->formError,
                'customerID'       => $dsSearchForm->getValue(BUMISReport::searchFormCustomerID),
                'customerString'   => $customerString,
                'months'           => $dsSearchForm->getValue(BUMISReport::searchFormMonths),
                'monthsMessage'    => $dsSearchForm->getMessage(BUMISReport::searchFormMonths),
                'urlCustomerPopup' => $urlCustomerPopup,
                'urlSubmit'        => $urlSubmit,
            )
        );

        $this->template->parse('CONTENTS', 'MISReport', true);
        $this->parsePage();
    }
}