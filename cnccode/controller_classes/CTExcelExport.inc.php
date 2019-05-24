<?php
/**
 * Excel Export controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUExcelExport.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_func'] . '/Common.inc.php');
// Messages
define('CTEXCEL_EXPORT_MSG_INV_NOT_FND', 'No Invoices Found');
define('CTEXCEL_EXPORT_MSG_PUR_NOT_FND', 'No Purchases Found');
// Actions
define('CTEXCEL_EXPORT_ACT_SELECT', 'select');
define('CTEXCEL_EXPORT_ACT_GENERATE', 'generate');

class CTExcelExport extends CTCNC
{
    /**
     * @var BUExcelExport
     */
    public $buExcelExport;

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
        $this->buExcelExport = new BUExcelExport($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case CTEXCEL_EXPORT_ACT_GENERATE:
                $this->generate();
                break;
            case CTEXCEL_EXPORT_ACT_SELECT:
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
                'action' => CTEXCEL_EXPORT_ACT_GENERATE
            )
        );
        $this->setPageTitle('Excel Sales Export');
        $this->setTemplateFiles('ExcelExport', 'ExcelExport.inc');
        $this->template->set_var(
            array(
                'month'     => Controller::htmlInputText($this->getParam('month')),
                'year'      => Controller::htmlInputText($this->getParam('year')),
                'urlSubmit' => $urlSubmit
            )
        );
        // display results
        $this->template->parse('CONTENTS', 'ExcelExport', true);
        $this->parsePage();
    }

    /**
     * @throws Exception
     */
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
        $this->buExcelExport->generateFile($this->getParam('year'), $this->getParam('month'));

        $fileName = $this->buExcelExport->generateFile($this->getParam('year'), $this->getParam('month'));
        if ($fileName == FALSE) {
            $this->setFormErrorMessage('No transactions found for given period');
            $this->select();
        } else {
            $this->setFormErrorMessage('Created export file ' . $fileName);
            $this->select();
        }
        exit();
    }
}// end of class
