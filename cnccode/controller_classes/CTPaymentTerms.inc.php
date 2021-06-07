<?php
/**
 * Payment Terms controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
use CNCLTD\Exceptions\APIException;
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
    /** @var DSForm */
    public $dsPaymentTerms;
    /** @var BUPaymentTerms */
    public $buPaymentTerms;
    const CONST_PAYMENT_TERMS = "paymentTerms";

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = ACCOUNTS_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(710);
        $this->buPaymentTerms = new BUPaymentTerms($this);
        $this->dsPaymentTerms = new DSForm($this);
        $this->dsPaymentTerms->copyColumnsFrom($this->buPaymentTerms->dbePaymentTerms);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->checkPermissions(MAINTENANCE_PERMISSION);
        switch ($this->getAction()) {
            case 'json':
            switch ($this->requestMethod) {
                case 'GET':
                    echo  json_encode($this->getPaymentTerms(),JSON_NUMERIC_CHECK);
                    break;
                case 'POST':
                    echo  json_encode($this->addPaymentTerms(),JSON_NUMERIC_CHECK);
                    break;
                case 'PUT':
                    echo  json_encode($this->updatePaymentTerms(),JSON_NUMERIC_CHECK);
                    break;
                case 'DELETE':
                    echo  json_encode($this->deletePaymentTerms(),JSON_NUMERIC_CHECK);
                    break;
                default:
                    # code...
                    break;
            }
            exit;   
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
     * Edit/Add Payment Terms
     * @access private
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsPaymentTerms = &$this->dsPaymentTerms; // ref to class var

        if (!$this->getFormError()) {
            if ($this->getAction() == CTPAYMENTTERMS_ACT_EDIT) {
                $this->buPaymentTerms->getPaymentTermsByID($this->getParam('paymentTermsID'), $dsPaymentTerms);
                $paymentTermsID = $this->getParam('paymentTermsID');
            } else {                                                                    // creating new
                $dsPaymentTerms->initialise();
                $dsPaymentTerms->setValue(DBEPaymentTerms::paymentTermsID, null);
                $paymentTermsID = null;
            }
        } else {                                                                        // form validation error
            $dsPaymentTerms->initialise();
            $dsPaymentTerms->fetchNext();
            $paymentTermsID = $dsPaymentTerms->getValue(DBEPaymentTerms::paymentTermsID);
        }
        $urlDelete = null;
        $txtDelete = null;
        if ($this->getAction() == CTPAYMENTTERMS_ACT_EDIT && $this->buPaymentTerms->canDeletePaymentTerms(
                $this->getParam('paymentTermsID')
            )) {
            $urlDelete = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => CTPAYMENTTERMS_ACT_DELETE,
                    'paymentTermsID' => $paymentTermsID
                )
            );
            $txtDelete = 'Delete';
        }
        $urlUpdate = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'         => CTPAYMENTTERMS_ACT_UPDATE,
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
                'paymentTermsID'              => $dsPaymentTerms->getValue(DBEPaymentTerms::paymentTermsID),
                'description'                 => Controller::htmlInputText(
                    $dsPaymentTerms->getValue(DBEPaymentTerms::description)
                ),
                'descriptionMessage'          => Controller::htmlDisplayText(
                    $dsPaymentTerms->getMessage(DBEPaymentTerms::description)
                ),
                'days'                        => Controller::htmlInputText(
                    $dsPaymentTerms->getValue(DBEPaymentTerms::days)
                ),
                'daysMessage'                 => Controller::htmlDisplayText(
                    $dsPaymentTerms->getMessage(DBEPaymentTerms::days)
                ),
                'generateInvoiceFlagChecked'  => Controller::htmlChecked(
                    $dsPaymentTerms->getValue(DBEPaymentTerms::generateInvoiceFlag)
                ),
                'automaticInvoiceFlagChecked' => Controller::htmlChecked(
                    $dsPaymentTerms->getValue(DBEPaymentTerms::automaticInvoiceFlag)
                ),
                'urlUpdate'                   => $urlUpdate,
                'urlDelete'                   => $urlDelete,
                'txtDelete'                   => $txtDelete,
                'urlDisplayList'              => $urlDisplayList
            )
        );
        $this->template->parse('CONTENTS', 'PaymentTermsEdit', true);
        $this->parsePage();
    }

        /**
     * Delete Payment Terms
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function delete()
    {
        $this->setMethodName('delete');
        if (!$this->buPaymentTerms->deletePaymentTerms($this->getParam('paymentTermsID'))) {
            $this->displayFatalError('Cannot delete this payment term');
            exit;
        } else {
            $urlNext = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTPAYMENTTERMS_ACT_DISPLAY_LIST
                )
            );
            header('Location: ' . $urlNext);
            exit;
        }
    }// end function editPayment Terms()

    /**
     * Update call payment terms details
     * @access private
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $this->formError = (!$this->dsPaymentTerms->populateFromArray($this->getParam('paymentTerms')));
        if ($this->formError) {
            if ($this->dsPaymentTerms->getValue(
                    DBEPaymentTerms::paymentTermsID
                ) == null) {                    // attempt to insert
                $this->setAction(CTPAYMENTTERMS_ACT_EDIT);
            } else {
                $this->setAction(CTPAYMENTTERMS_ACT_CREATE);
            }
            $this->edit();
            exit;
        }

        $this->buPaymentTerms->updatePaymentTerms($this->dsPaymentTerms);

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'paymentTermsID' => $this->dsPaymentTerms->getValue(DBEPaymentTerms::paymentTermsID),
                    'action'         => CTCNC_ACT_VIEW
                )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Display list of terms
     * @access private
     * @throws Exception
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Payment Terms');
        $this->setTemplateFiles(
            array('PaymentTermsList' => 'PaymentTermsList.inc')
        );
        $dsPaymentTerms = new DataSet($this);
        $this->buPaymentTerms->getAllTerms($dsPaymentTerms);

        $urlCreate = Controller::buildLink(
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
                $paymentTermsID = $dsPaymentTerms->getValue(DBEPaymentTerms::paymentTermsID);
                $urlEdit =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'         => CTPAYMENTTERMS_ACT_EDIT,
                            'paymentTermsID' => $paymentTermsID
                        )
                    );
                $txtEdit = '[edit]';
                $this->template->set_var(
                    array(
                        'paymentTermsID' => $paymentTermsID,
                        'description'    => Controller::htmlDisplayText(
                            $dsPaymentTerms->getValue(DBEPaymentTerms::description)
                        ),
                        'urlEdit'        => $urlEdit,
                        'txtEdit'        => $txtEdit
                    )
                );
                $this->template->parse('terms', 'termsBlock', true);
            }//while $dsPaymentTerms->fetchNext()
        }
        $this->template->parse('CONTENTS', 'PaymentTermsList', true);
        $this->parsePage();
    }

        //--------------------new 
        function getPaymentTerms()
        {
            $DBEPaymentTerms = new DBEPaymentTerms($this);
            $DBEPaymentTerms->getRows(); // DBEPaymentTerms::sortOrder
            $data = [];
            while ($DBEPaymentTerms->fetchNext()) {
                $data[] = [
                    "id"                   => $DBEPaymentTerms->getValue(DBEPaymentTerms::paymentTermsID),
                    "description"          => $DBEPaymentTerms->getValue(DBEPaymentTerms::description),
                    "days"                 => $DBEPaymentTerms->getValue(DBEPaymentTerms::days),
                    "generateInvoiceFlag"  => $DBEPaymentTerms->getValue(DBEPaymentTerms::generateInvoiceFlag),
                    "automaticInvoiceFlag" => $DBEPaymentTerms->getValue(DBEPaymentTerms::automaticInvoiceFlag),
                ];
            }
           return $this->success($data);
        }

        function addPaymentTerms()
        {
            $body=$this->getBody();
            $DBEPaymentTerms = new DBEPaymentTerms($this);
            $DBEPaymentTerms->setValue(DBEPaymentTerms::description,$body->description);
            $DBEPaymentTerms->setValue(DBEPaymentTerms::days, $body->days);
            $DBEPaymentTerms->setValue(DBEPaymentTerms::generateInvoiceFlag, $body->generateInvoiceFlag);
            $DBEPaymentTerms->setValue(DBEPaymentTerms::automaticInvoiceFlag, $body->automaticInvoiceFlag);
            //$DBEPaymentTerms->setValue($DBEPaymentTerms::sortOrder, $DBEPaymentTerms->getNextSortOrder());
            $DBEPaymentTerms->insertRow();
            return $this->success();
        }

        function updatePaymentTerms()
        {
            $body =$this->getBody();
            if(!isset($body->id))
                return $this->fail(APIException::badRequest,"Bad Request");
    
            $DBEPaymentTerms = new DBEPaymentTerms($this);
            $DBEPaymentTerms->getRow($body->id);
    
            if (!$DBEPaymentTerms->rowCount)             
                return $this->fail(APIException::notFound,"Not Found");
    
            $DBEPaymentTerms->setValue(DBEPaymentTerms::description,$body->description);
            $DBEPaymentTerms->setValue(DBEPaymentTerms::days, $body->days);
            $DBEPaymentTerms->setValue(DBEPaymentTerms::generateInvoiceFlag, $body->generateInvoiceFlag);
            $DBEPaymentTerms->setValue(DBEPaymentTerms::automaticInvoiceFlag, $body->automaticInvoiceFlag);
            $DBEPaymentTerms->updateRow();
            return $this->success();        
        }

        function deletePaymentTerms()
        {
            $id=@$_REQUEST['id'];
        
            if (!$id) 
                return $this->fail(APIException::notFound, "Id is Missing");
    
            $DBEPaymentTerms = new DBEPaymentTerms($this);
            $DBEPaymentTerms->getRow($id);
            if (!$DBEPaymentTerms->rowCount) {
                return $this->fail(APIException::notFound, "Not Found");
            }
            $DBEPaymentTerms->deleteRow();
            return $this->success();
        }
}
?>
