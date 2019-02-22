<?php
/**
 * Payment Terms controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUPaymentTerms.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define('CTPAYMENTTERMS_ACT_DISPLAY_LIST', 'paymentTermsList');
define('CTPAYMENTTERMS_ACT_CREATE', 'createPaymentTerms');
define('CTPAYMENTTERMS_ACT_EDIT', 'editPaymentTerms');
define('CTPAYMENTTERMS_ACT_DELETE', 'deletePaymentTerms');
define('CTPAYMENTTERMS_ACT_UPDATE', 'updatePaymentTerms');

class CTPaymentTerms extends CTCNC
{
    var $dsPaymentTerms = '';
    var $buPaymentTerms = '';

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
        $this->buPaymentTerms = new BUPaymentTerms($this);
        $this->dsPaymentTerms = new DSForm($this);
        $this->dsPaymentTerms->copyColumnsFrom($this->buPaymentTerms->dbePaymentTerms);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
        switch ($_REQUEST['action']) {
            case CTPAYMENTTERMS_ACT_EDIT:
            case CTPAYMENTTERMS_ACT_CREATE:
                $this->edit();
                break;
            case CTPAYMENTTERMS_ACT_DELETE:
                $this->delete();
                break;
            case CTPAYMENTTERMS_ACT_UPDATE:
                $this->update();
                break;
            case CTPAYMENTTERMS_ACT_DISPLAY_LIST:
            default:
                $this->displayList();
                break;
        }
    }

    /**
     * Display list of terms
     * @access private
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Payment Terms');
        $this->setTemplateFiles(
            array('PaymentTermsList' => 'PaymentTermsList.inc')
        );

        $this->buPaymentTerms->getAllTerms($dsPaymentTerms);

        $urlCreate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTPAYMENTTERMS_ACT_CREATE
                )
            );

        $this->template->set_var(
            array('urlCreate' => $urlCreate)
        );

        if ($dsPaymentTerms->rowCount() > 0) {
            $this->template->set_block('PaymentTermsList', 'termsBlock', 'terms');
            while ($dsPaymentTerms->fetchNext()) {
                $paymentTermsID = $dsPaymentTerms->getValue('paymentTermsID');
                $urlEdit =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTPAYMENTTERMS_ACT_EDIT,
                            'paymentTermsID' => $paymentTermsID
                        )
                    );
                $txtEdit = '[edit]';
                $this->template->set_var(
                    array(
                        'paymentTermsID' => $paymentTermsID,
                        'description' => Controller::htmlDisplayText($dsPaymentTerms->getValue('description')),
                        'urlEdit' => $urlEdit,
                        'txtEdit' => $txtEdit
                    )
                );
                $this->template->parse('terms', 'termsBlock', true);
            }//while $dsPaymentTerms->fetchNext()
        }
        $this->template->parse('CONTENTS', 'PaymentTermsList', true);
        $this->parsePage();
    }

    /**
     * Edit/Add Payment Terms
     * @access private
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsPaymentTerms = &$this->dsPaymentTerms; // ref to class var

        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == CTPAYMENTTERMS_ACT_EDIT) {
                $this->buPaymentTerms->getPaymentTermsByID($_REQUEST['paymentTermsID'], $dsPaymentTerms);
                $paymentTermsID = $_REQUEST['paymentTermsID'];
            } else {                                                                    // creating new
                $dsPaymentTerms->initialise();
                $dsPaymentTerms->setValue('paymentTermsID', '0');
                $paymentTermsID = '0';
            }
        } else {                                                                        // form validation error
            $dsPaymentTerms->initialise();
            $dsPaymentTerms->fetchNext();
            $paymentTermsID = $dsPaymentTerms->getValue('paymentTermsID');
        }
        if ($_REQUEST['action'] == CTPAYMENTTERMS_ACT_EDIT && $this->buPaymentTerms->canDeletePaymentTerms($_REQUEST['paymentTermsID'])) {
            $urlDelete =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTPAYMENTTERMS_ACT_DELETE,
                        'paymentTermsID' => $paymentTermsID
                    )
                );
            $txtDelete = 'Delete';
        } else {
            $urlDelete = '';
            $txtDelete = '';
        }
        $urlUpdate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTPAYMENTTERMS_ACT_UPDATE,
                    'paymentTermsID' => $paymentTermsID
                )
            );
        $urlDisplayList =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTPAYMENTTERMS_ACT_DISPLAY_LIST
                )
            );
        $this->setPageTitle('Edit Payment Terms');
        $this->setTemplateFiles(
            array('PaymentTermsEdit' => 'PaymentTermsEdit.inc')
        );
        $this->template->set_var(
            array(
                'paymentTermsID' => $dsPaymentTerms->getValue('paymentTermsID'),
                'description' => Controller::htmlInputText($dsPaymentTerms->getValue('description')),
                'descriptionMessage' => Controller::htmlDisplayText($dsPaymentTerms->getMessage('description')),
                'days' => Controller::htmlInputText($dsPaymentTerms->getValue('days')),
                'daysMessage' => Controller::htmlDisplayText($dsPaymentTerms->getMessage('days')),
                'generateInvoiceFlagChecked' => Controller::htmlChecked($dsPaymentTerms->getValue('generateInvoiceFlag')),
                'automaticInvoiceFlagChecked' => Controller::htmlChecked($dsPaymentTerms->getValue('automaticInvoiceFlag')),
                'urlUpdate' => $urlUpdate,
                'urlDelete' => $urlDelete,
                'txtDelete' => $txtDelete,
                'urlDisplayList' => $urlDisplayList
            )
        );
        $this->template->parse('CONTENTS', 'PaymentTermsEdit', true);
        $this->parsePage();
    }// end function editPayment Terms()

    /**
     * Update call payment terms details
     * @access private
     */
    function update()
    {
        $this->setMethodName('update');
        $dsPaymentTerms = &$this->dsPaymentTerms;
        $this->formError = (!$this->dsPaymentTerms->populateFromArray($_REQUEST['paymentTerms']));
        if ($this->formError) {
            if ($this->dsPaymentTerms->getValue('paymentTermsID') == '0') {                    // attempt to insert
                $_REQUEST['action'] = CTPAYMENTTERMS_ACT_EDIT;
            } else {
                $_REQUEST['action'] = CTPAYMENTTERMS_ACT_CREATE;
            }
            $this->edit();
            exit;
        }

        $this->buPaymentTerms->updatePaymentTerms($this->dsPaymentTerms);

        $urlNext =
            Controller::buildLink($_SERVER['PHP_SELF'],
                             array(
                                 'paymentTermsID' => $this->dsPaymentTerms->getValue('paymentTermsID'),
                                 'action' => CTCNC_ACT_VIEW
                             )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Delete Payment Terms
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function delete()
    {
        $this->setMethodName('delete');
        if (!$this->buPaymentTerms->deletePaymentTerms($_REQUEST['paymentTermsID'])) {
            $this->displayFatalError('Cannot delete this payment term');
            exit;
        } else {
            $urlNext =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTPAYMENTTERMS_ACT_DISPLAY_LIST
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }
}// end of class
?>