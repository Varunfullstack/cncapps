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
        $this->buSageExport = new BUSageExport($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_ACCOUNTS);
        switch ($_REQUEST['action']) {
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
     */
    function select()
    {
        $this->setMethodName('select');
        $urlSubmit = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTSAGE_EXPORT_ACT_GENERATE
            )
        );
        $this->setPageTitle('Sage Exports');
        $this->setTemplateFiles('SageExport', 'SageExport.inc');
        $this->template->set_var(
            array(
                'month' => Controller::htmlInputText($_REQUEST['month']),
                'year' => Controller::htmlInputText($_REQUEST['year']),
                'includeSalesChecked' => Controller::htmlChecked($_REQUEST['includeSales']),
                'includePurchasesChecked' => Controller::htmlChecked($_REQUEST['includePurchases']),
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
        if ($_REQUEST['month'] == '') {
            $this->setFormErrorMessage('Month required');
            $this->select();
            exit();
        }
        if ($_REQUEST['year'] == '') {
            $this->setFormErrorMessage('Year required');
            $this->select();
            exit();
        }
        if (!is_numeric($_REQUEST['year'])) {
            $this->setFormErrorMessage('Year must be numeric');
            $this->select();
            exit();
        }
        if (!common_inRange($_REQUEST['year'], date('Y') - 1, date('Y'))) {
            $this->setFormErrorMessage('Year out of range');
            $this->select();
            exit();
        }
        if (!common_inRange($_REQUEST['month'], 1, 12)) {
            $this->setFormErrorMessage('Month out of range');
            $this->select();
            exit();
        }
        if (!isset($_REQUEST['includeSales']) AND !isset($_REQUEST['includePurchases'])) {
            $this->setFormErrorMessage('Choose at least one report to produce');
            $this->select();
            exit();
        }
        $this->buSageExport->generateSageData(
            $_REQUEST['year'],
            $_REQUEST['month'],
            isset($_REQUEST['includeSales']),
            isset($_REQUEST['includePurchases'])
        );
        $this->setFormErrorMessage('The transaction files have been created in the export directory ready for import to Sage');
        $this->select();
        exit();
    }
}// end of class
?>