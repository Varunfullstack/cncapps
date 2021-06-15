<?php
/**
 * Prepay Adjustment Controller Class
 * CNC Ltd
 *
 * @access public
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Business\BUActivity;

global $cfg;
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');

class CTPrepayAdjustment extends CTCNC
{
    /** @var BUActivity */
    private $buActivity;
    /**
     * @var DSForm
     */
    private $dsCallActivity;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = ACCOUNTS_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(707);
        $this->buActivity     = new BUActivity($this);
        $this->dsCallActivity = new DSForm($this);
        $this->dsCallActivity->copyColumnsFrom($this->buActivity->dbeJCallActivity);
        $this->dsCallActivity->setNull(DBEJCallActivity::customerID, DA_NOT_NULL);
        $this->dsCallActivity->setNull(DBEJCallActivity::curValue, DA_NOT_NULL);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->edit();
    }

    /**
     * Display search form
     * @access private
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');
        $this->setTemplateFiles('PrepayAdjustment', 'PrepayAdjustment.inc');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $formError = (!$this->dsCallActivity->populateFromArray($this->getParam('callActivity')));
            if (!$this->dsCallActivity->getValue(DBEJCallActivity::customerID)) {
                $this->dsCallActivity->setMessage(DBEJCallActivity::customerID, 'Please select a customer');
                $formError = true;
            } else {
                $dbeCustomerItem = new DBECustomerItem($this);
                if (!$dbeCustomerItem->getGSCRow($this->dsCallActivity->getValue(DBEJCallActivity::customerID))) {
                    $this->dsCallActivity->setMessage(DBEJCallActivity::customerID, 'Not a Prepay Customer');
                    $formError = true;
                }
            }
            if (!$formError) {
                $this->buActivity->createPrepayAdjustment(
                    $this->dsCallActivity->getValue(DBEJCallActivity::customerID),
                    $this->dsCallActivity->getValue(DBEJCallActivity::curValue),
                    $this->dsCallActivity->getValue(DBEJCallActivity::date)
                );
                $urlNext = Controller::buildLink(
                    '/',
                    array()
                );
                header('Location: ' . $urlNext);
                exit;
            }
        }
        $urlSubmit = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            []
        );
        $urlCustomerPopup = Controller::buildLink(
            CTCNC_PAGE_CUSTOMER,
            array(
                'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $this->setPageTitle('Create Prepay Adjustment');
        $this->template->set_var(
            array(
                'formError'         => $this->formError,
                'customerID'        => $this->dsCallActivity->getValue(DBEJCallActivity::customerID),
                'customerIDMessage' => $this->dsCallActivity->getMessage(DBEJCallActivity::customerID),
                'customerName'      => $this->getParam('customerName'),
                'urlCustomerPopup'  => $urlCustomerPopup,
                'callActivityID'    => $this->dsCallActivity->getValue(DBEJCallActivity::callActivityID),
                'date'              => $this->dsCallActivity->getValue(DBEJCallActivity::date),
                'dateMessage'       => $this->dsCallActivity->getMessage(DBEJCallActivity::date),
                'curValue'          => $this->dsCallActivity->getValue(DBEJCallActivity::curValue),
                'curValueMessage'   => $this->dsCallActivity->getMessage(DBEJCallActivity::curValue),
                'urlSubmit'         => $urlSubmit
            )
        );
        $this->template->parse('CONTENTS', 'PrepayAdjustment', true);
        $this->parsePage();
    }
}
