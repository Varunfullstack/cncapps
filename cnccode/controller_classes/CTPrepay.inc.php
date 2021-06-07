<?php
/**
 * Prepay Export controller class
 * CNC Ltd
 *
 * @access public
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg['path_bu'] . '/BUPrepay.inc.php');
require_once($cfg['path_bu'] . '/BUSite.inc.php');
require_once($cfg['path_bu'] . '/BUContact.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');

class CTPrepay extends CTCNC
{
    /** @var BUPrepay */
    private $buPrepay;
    /** @var DataSet */
    private $dsPrepayExport;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = ACCOUNTS_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(706);
        $this->buPrepay = new BUPrepay ($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {

            case 'exportGenerate':
                $this->checkPermissions(ACCOUNTS_PERMISSION);
                $this->exportGenerate();
                break;

            case 'exportForm':
            default:
                $this->checkPermissions(ACCOUNTS_PERMISSION);
                $this->exportForm();
                break;
        }

    }

    /**
     * @throws Exception
     */
    function exportGenerate()
    {
        $this->setMethodName('exportGenerate');
        $this->buPrepay->initialiseExportDataset($this->dsPrepayExport);
        $dsResults = null;
        if (!$this->dsPrepayExport->populateFromArray($this->getParam('export'))) {
            $this->setFormErrorOn();
        } else {
            // do export
            $dsResults = $this->buPrepay->exportPrepayActivities(
                $this->dsPrepayExport,
                $this->getParam('update')
            );
        }
        $this->exportForm($dsResults);
    }

    /**
     * Export Prepay requests that have not previously been exported
     * @access private
     * @param null|DataSet $dsResults
     * @throws Exception
     */
    function exportForm($dsResults = null)
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
            $this->dsPrepayExport = new DSForm($this);
            $this->buPrepay->initialiseExportDataset($this->dsPrepayExport);
        }
        $this->template->set_var(
            array(
                'endDate'        => $this->dsPrepayExport->getValue(BUPrepay::exportDataSetEndDate),
                'endDateMessage' => $this->dsPrepayExport->getMessage(BUPrepay::exportDataSetEndDate),
                'urlPreview'     => $urlPreview,
                'urlExport'      => $urlExport
            )
        );

        if ($dsResults) {
            $dsResults->initialise();
            $this->template->set_block('PrepayExport', 'resultBlock', 'results');
            while ($dsResults->fetchNext()) {
                $this->template->setVar(
                    array(
                        'customerName'    => $dsResults->getValue(BUPrepay::exportPrePayCustomerName),
                        'previousBalance' => $dsResults->getValue(BUPrepay::exportPrePayPreviousBalance),
                        'currentBalance'  => $dsResults->getValue(BUPrepay::exportPrePayCurrentBalance),
                        'topUp'           => $dsResults->getValue(BUPrepay::exportPrePayTopUp),
                        'expiryDate'      => $dsResults->getValue(BUPrepay::exportPrePayExpiryDate),
                        'contacts'        => $dsResults->getValue(BUPrepay::exportPrePayContacts),
                        'contractType'    => $dsResults->getValue(BUPrepay::exportPrePayContractType),
                        'webFileLink'     => $dsResults->getValue(BUPrepay::exportPrePayWebFileLink),
                        'redClass'        => $dsResults->getValue(
                            BUPrepay::exportPrePayPreviousBalance
                        ) < 100 ? 'redRow' : null
                    )
                );
                $this->template->parse('results', 'resultBlock', true);
            }
        }

        $this->template->parse('CONTENTS', 'PrepayExport', true);
        $this->parsePage();
    }
}
