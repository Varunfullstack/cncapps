<?php
/**
 * Prepay Adjustment Controller Class
 * CNC Ltd
 *
 * @access public
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');

class CTPrepayAdjustment extends CTCNC
{
    private $buActivity = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg, "", "", "", "");
        $roles = [
            "sales",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buActivity = new BUActivity($this);
        $this->dsCallActivity = new DSForm($this);
        $this->dsCallActivity->copyColumnsFrom($this->buActivity->dbeJCallActivity);
        $this->dsCallActivity->setNull('customerID', DA_NOT_NULL);
        $this->dsCallActivity->setNull('curValue', DA_NOT_NULL);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->edit();           // this is all we can do
    }

    /**
     * Display search form
     * @access private
     */
    function edit()
    {
        $this->setMethodName('edit');

        $this->setTemplateFiles('PrepayAdjustment', 'PrepayAdjustment.inc');
        /*
        submited so validate/update
        */
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $formError = (!$this->dsCallActivity->populateFromArray($_REQUEST['callActivity']));

            if ($this->dsCallActivity->getValue('customerID') == 0) {
                $this->dsCallActivity->setMessage('customerID', 'Please select a customer');
                $formError = true;
            } else {
                $dbeCustomerItem = new DBECustomerItem($this);

                if (!$dbeCustomerItem->getGSCRow($this->dsCallActivity->getValue('customerID'))) {

                    $this->dsCallActivity->setMessage('customerID', 'Not a Prepay Customer');
                    $formError = true;

                }

            }

            if (!$formError) {

                $this->buActivity->createPrepayAdjustment(
                    $this->dsCallActivity->getValue('customerID'),
                    $this->dsCallActivity->getValue('curValue'),
                    $this->dsCallActivity->getValue('date')
                );

                $urlNext =
                    $this->buildLink(
                        '/',
                        array()
                    );
                header('Location: ' . $urlNext);
                exit;

            }

        }

        $urlSubmit = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => 'update'
            )
        );

        $urlCustomerPopup = $this->buildLink(
            CTCNC_PAGE_CUSTOMER,
            array(
                'action' => CTCNC_ACT_DISP_CUST_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );

        $this->setPageTitle('Create Prepay Adjustment');

        $this->template->set_var(
            array(
                'formError' => $this->formError,
                'customerID' => $this->dsCallActivity->getValue('customerID'),
                'customerIDMessage' => $this->dsCallActivity->getMessage('customerID'),
                'customerName' => $_POST['customerName'],
                'urlCustomerPopup' => $urlCustomerPopup,
                'callActivityID' => $this->dsCallActivity->getValue('callActivityID'),
                'date' => Controller::dateYMDtoDMY($this->dsCallActivity->getValue('date')),
                'dateMessage' => $this->dsCallActivity->getMessage('date'),
                'curValue' => $this->dsCallActivity->getValue('curValue'),
                'curValueMessage' => $this->dsCallActivity->getMessage('curValue'),
                'urlSubmit' => $urlSubmit
            )
        );

        $this->template->parse('CONTENTS', 'PrepayAdjustment', true);

        $this->parsePage();
    } // end function

}// end of class
?>
