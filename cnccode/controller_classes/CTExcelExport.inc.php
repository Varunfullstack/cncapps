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
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {
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
                'month' => Controller::htmlInputText($_REQUEST['month']),
                'year' => Controller::htmlInputText($_REQUEST['year']),
                'urlSubmit' => $urlSubmit
            )
        );
        // display results
        $this->template->parse('CONTENTS', 'ExcelExport', true);
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
        $fileURL = $this->buExcelExport->generateFile($_REQUEST['year'], $_REQUEST['month']);
        //header('Location:'.$fileURL);
        //exit;
        //echo $fileURL;
        /*
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Content-Type: application/vnd.ms-excel");
                header("Content-Disposition: attachment; filename=".basename($fileURL).";" );
                header("Content-Transfer-Encoding: binary");
                header("Content-Length: ".filesize($fileURL));
                readfile($fileURL);
                exit();
        */
        $fileName = $this->buExcelExport->generateFile($_REQUEST['year'], $_REQUEST['month']);
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
?>