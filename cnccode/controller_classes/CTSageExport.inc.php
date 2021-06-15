<?php
/**
 * Sage Export controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUSageExport.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_func'] . '/Common.inc.php');
// Messages
define('CTSAGE_EXPORT_MSG_INV_NOT_FND', 'No Invoices Found');
define('CTSAGE_EXPORT_MSG_PUR_NOT_FND', 'No Purchases Found');
// Actions
define('CTSAGE_EXPORT_ACT_SELECT', 'select');
define('CTSAGE_EXPORT_ACT_GENERATE', 'generate');

class CTSageExport extends CTCNC
{
    var $fileNames = '';        // array of file names created
    public $buSageExport;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = ACCOUNTS_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(705);
        $this->buSageExport = new BUSageExport($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(ACCOUNTS_PERMISSION);
        switch ($this->getAction()) {
            case CTSAGE_EXPORT_ACT_GENERATE:
                $this->generate();
                break;
            case CTSAGE_EXPORT_ACT_SELECT:
                $this->select();
                break;
            default:
                $this->select();
                break;
        }
    }

    /**
     * Display search form
     * @access private
     * @throws Exception
     * @throws Exception
     */
    function select()
    {
        $this->setMethodName('select');
        $urlSubmit = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTSAGE_EXPORT_ACT_GENERATE
            )
        );
        $this->setPageTitle('Sage Exports');
        $this->setTemplateFiles('SageExport', 'SageExport.inc');
        $this->template->set_var(
            array(
                'month' => Controller::htmlInputText($this->getParam('month')),
                'year' => Controller::htmlInputText($this->getParam('year')),
                'includeSalesChecked' => Controller::htmlChecked($this->getParam('includeSales')),
                'includePurchasesChecked' => Controller::htmlChecked($this->getParam('includePurchases')),
                'urlSubmit' => $urlSubmit
            )
        );
        // display results
        $this->template->parse('CONTENTS', 'SageExport', true);
        $this->parsePage();
    }

    function generate()
    {
        $this->setMethodName('generate');
        if ($this->getParam('month') == '') {
            $this->setFormErrorMessage('Month required');
            $this->select();
            exit();
        }
        if ($this->getParam('year') == '') {
            $this->setFormErrorMessage('Year required');
            $this->select();
            exit();
        }
        if (!is_numeric($this->getParam('year'))) {
            $this->setFormErrorMessage('Year must be numeric');
            $this->select();
            exit();
        }
        if (!common_inRange($this->getParam('year'), date('Y') - 1, date('Y'))) {
            $this->setFormErrorMessage('Year out of range');
            $this->select();
            exit();
        }
        if (!common_inRange($this->getParam('month'), 1, 12)) {
            $this->setFormErrorMessage('Month out of range');
            $this->select();
            exit();
        }
        if (!$this->getParam('includeSales') && !$this->getParam('includePurchases')) {
            $this->setFormErrorMessage('Choose at least one report to produce');
            $this->select();
            exit();
        }
        $this->buSageExport->generateSageData(
            $this->getParam('year'),
            $this->getParam('month'),
            $this->getParam('includeSales'),
            $this->getParam('includePurchases')
        );
        $this->setFormErrorMessage('The transaction files have been created in the export directory ready for import to Sage');
        $this->select();
        exit();
    }
}// end of class
