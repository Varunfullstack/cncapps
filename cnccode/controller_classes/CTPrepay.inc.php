<?php
/**
 * Prepay Export controller class
 * CNC Ltd
 *
 * @access public
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUPrepay.inc.php');
require_once($cfg['path_bu'] . '/BUSite.inc.php');
require_once($cfg['path_bu'] . '/BUContact.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');

class CTPrepay extends CTCNC
{

    private $buPrepay = '';
    private $dsPrepayExport;

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
        $this->buPrepay = new BUPrepay ($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {

            case 'exportGenerate':
                $this->checkPermissions(PHPLIB_PERM_ACCOUNTS);
                $this->exportGenerate();
                break;

            case 'exportForm':
            default:
                $this->checkPermissions(PHPLIB_PERM_ACCOUNTS);
                $this->exportForm();
                break;
        }

    }

    /**
     * Export Prepay requests that have not previously been exported
     * @access private
     */
    function exportForm($dsResults = false)
    {
        $this->setMethodName('exportForm');
        $urlPreview = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => 'exportGenerate',
                'update' => 0
            )
        );
        $urlExport = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => 'exportGenerate',
                'update' => 1
            )
        );
        $this->setPageTitle('Export Prepay Service Requests');
        $this->setTemplateFiles('PrepayExport', 'PrepayExport.inc');

        if (!is_object($this->dsPrepayExport)) {
            $this->buPrepay->initialiseExportDataset($this->dsPrepayExport);
        }
        $this->template->set_var(
            array(
                'endDate' => Controller::dateYMDtoDMY($this->dsPrepayExport->getValue('endDate')),
                'endDateMessage' => Controller::dateYMDtoDMY($this->dsPrepayExport->getMessage('endDate')),
                'urlPreview' => $urlPreview,
                'urlExport' => $urlExport
            )
        );

        if ($dsResults) {
            $dsResults->initialise();
            $this->template->set_block('PrepayExport', 'resultBlock', 'results');
            while ($dsResults->fetchNext()) {
                $urlStatement =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTACTIVITY_ACT_EDIT_CALL,
                            'callActivityID' => $_REQUEST['callActivityID']
                        )
                    );

                $this->template->setVar(
                    array(
                        'customerName' => $dsResults->getValue('customerName'),
                        'previousBalance' => $dsResults->getValue('previousBalance'),
                        'currentBalance' => $dsResults->getValue('currentBalance'),
                        'topUp' => $dsResults->getValue('topUp'),
                        'expiryDate' => $dsResults->getValue('expiryDate'),
                        'contacts' => $dsResults->getValue('contacts'),
                        'contractType' => $dsResults->getValue('contractType'),
                        'webFileLink' => $dsResults->getValue('webFileLink')
                    )
                );
                $this->template->parse('results', 'resultBlock', true);
            }
        }

        $this->template->parse('CONTENTS', 'PrepayExport', true);
        $this->parsePage();
    }

    function exportGenerate()
    {
        $this->setMethodName('exportGenerate');
        $this->buPrepay->initialiseExportDataset($this->dsPrepayExport);

        if (!$this->dsPrepayExport->populateFromArray($_REQUEST['export'])) {
            $this->setFormErrorOn();
        } else {
            // do export
            $dsResults =
                $this->buPrepay->exportPrepayActivities(
                    $this->dsPrepayExport,
                    $_REQUEST['update']
                );
//
//            if ($_REQUEST['update']) {
//                if ($dsResults) {
//                    $this->setFormErrorMessage('Export files created');
//                } else {
//                    $this->setFormErrorMessage('No data to export for this date');
//                }
//            }
//
        }
        $this->exportForm($dsResults);
    }

}// end of class
?>
