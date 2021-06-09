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
            case CTPAYMENTTERMS_ACT_DISPLAY_LIST:
            default:
                $this->displayForm();
                break;
        }
    }
      /**
     * Display list of types
     * @access private
     * @throws Exception
     */
    function displayForm()
    {   
        $this->setPageTitle('Payment Terms');
        $this->setTemplateFiles(
            array('form' => 'PaymentTermsList.inc')
        );
        $this->loadReactScript('PaymentTermsComponent.js');
        $this->loadReactCSS('PaymentTermsComponent.css');
        $this->template->parse(
            'CONTENTS',
            'form',
            true
        );
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
