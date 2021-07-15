<?php
/**
 * Purchase Invoice controller class
 * CNC Ltd
 *
 */

use CNCLTD\Exceptions\APIException;

global $cfg;
require_once($cfg['path_bu'] . '/BUPurchaseInv.inc.php');
require_once($cfg['path_bu'] . '/BUPurchaseOrder.inc.php');
require_once($cfg['path_bu'] . '/BUGoodsIn.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_gc'] . '/DataSet.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Messages
define('CTPURCHASEINV_MSG_PURCHASEORDER_NOT_FND', 'Purchase Order not found');
define('CTPURCHASEINV_MSG_PORHEADID_NOT_PASSED', 'porheadID not passed');
define('CTPURCHASEINV_MSG_SEQNO_NOT_PASSED', 'sequence no not passed');
define('CTPURCHASEINV_MSG_ORDLINE_NOT_FND', 'order line not found');
// Actions

class CTPurchaseInv extends CTCNC
{
    /** @var BUPurchaseInv */
    public $buPurchaseInv;
    /** @var DSForm */
    public $dsPurchaseInv;
    /** @var BUPurchaseOrder */
    public $buPurchaseOrder;
    /** @var DSForm */
    public $dsPorhead;
    /** @var DSForm */
    public $dsPorline;
    /** @var array */
  
    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = ACCOUNTS_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(702);
        $this->buPurchaseInv   = new BUPurchaseInv($this);
        $this->buPurchaseOrder = new BUPurchaseOrder($this);
        $this->dsPorhead       = new DSForm($this);
        $this->dsPorline       = new DSForm($this);
        $this->dsPorline->copyColumnsFrom($this->buPurchaseOrder->dbeJPorline);
        $this->dsPorhead->copyColumnsFrom($this->buPurchaseOrder->dbeJPorhead);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->checkPermissions(ACCOUNTS_PERMISSION);
        switch ($this->getAction()) {
            case "orders":
                switch ($this->requestMethod) {
                    case 'GET':
                        echo json_encode($this->getOrders());
                        break;
                    case 'PUT':
                        echo json_encode($this->updateInvoice());
                        break;
                    default:
                        # code...
                        break;
                }
                break;
            case "lines":
                echo json_encode($this->getOrdersLines()); 
                break;        
            default:
                $this->displaySearch();
                break;
        }
    }
 
    /**
     * Display list of types
     * @access private
     * @throws Exception
     */
    function displaySearch()
    {   
        $this->setPageTitle('Purchase Invoice Authorisation');
        $this->setTemplateFiles(
            array('PurchaseInvComponent' => 'PurchaseInvSearch.inc')
        );
        $this->loadReactScript('PurchaseInvComponent.js');
        $this->loadReactCSS('PurchaseInvComponent.css');
        $this->template->parse(
            'CONTENTS',
            'PurchaseInvComponent',
            true
        );
        $this->parsePage();
    }

    function getOrders(){         
         $this->buPurchaseInv->search(
            null,
            null,
            null,
            null,
            $this->dsPorhead
        );
        $orders=[];
        while( $this->dsPorhead->fetchNext()){
            $orders []=[
                "customerID"=>$this->dsPorhead->getValue(DBEJPorhead::customerID),
                'porheadID'           => $this->dsPorhead->getValue(DBEJPorhead::porheadID),
                'supplierName'        => $this->dsPorhead->getValue(DBEJPorhead::supplierName),
                'vatRate'             => $this->dsPorhead->getValue(DBEJPorhead::vatRate),
                'purchaseInvoiceDate' => $this->dsPorhead->getValue(DBEJPorhead::date),
                'type'                => $this->dsPorhead->getValue(DBEJPorhead::type),
                "supplierRef"         =>$this->dsPorhead->getValue(DBEJPorhead::supplierRef),
                //'purchaseInvoiceNo'   => Controller::htmlDisplayText($this->getParam('purchaseInvoiceNo')),
                'customerName'        => $this->dsPorhead->getValue(DBEJPorhead::customerName),
                "ordheadID"           => $this->dsPorhead->getValue(DBEJPorhead::ordheadID),
            ];
        }
        return $this->success($orders);
    }

    function getOrdersLines(){
        $this->setMethodName('getOrdersLines');
        $dsPorhead = &$this->dsPorhead;
        $dsPorline = &$this->dsPorline;
        $porheadID=$_REQUEST["porheadID"];
        if (! $porheadID) {
            return $this->fail(APIException::badRequest,CTPURCHASEINV_MSG_PORHEADID_NOT_PASSED);            
        }
        $lines=[];
        $this->buPurchaseOrder->getHeaderByID($porheadID, $dsPorhead);
        $dsPorhead->fetchNext();
        $this->buPurchaseOrder->getLinesByID($dsPorhead->getValue(DBEJPorhead::porheadID), $dsPorline);
        // Do we require customer items to be created?
        $buSalesOrder = new BUSalesOrder($this);
        $dsOrdhead    = new DataSet($this);
        $buSalesOrder->getOrderByOrdheadID($dsPorhead->getValue(DBEJPorhead::ordheadID), $dsOrdhead, $dsOrdline);
        $addCustomerItems = ($dsOrdhead->getValue(DBEJOrdhead::addItem) == 'Y');
        if ($this->dsPorhead->getValue(DBEJPorhead::directDeliveryFlag) == 'Y') {
            if ($errorMessage = $this->buPurchaseInv->renewalsNotCompleted($dsOrdline)) {
                $this->setFormErrorMessage($errorMessage);
            }
        }
        $this->buPurchaseInv->getInitialValues(
            $dsPorhead,
            $dsPorline,
            $this->dsPurchaseInv,
            $addCustomerItems
        );
        $this->setParam('purchaseInvoiceDate', date('Y-m-d'));
        $porheadID = $dsPorhead->getValue(DBEJPorhead::porheadID);
       
       
        $dsWarranty = new DataSet($this);
        if ($addCustomerItems) {
            $buGoodsIn = new BUGoodsIn($this);
            $buGoodsIn->getAllWarranties($dsWarranty);
        }
        $this->dsPurchaseInv->initialise();
        if ($this->dsPurchaseInv->rowCount() > 0) {
            $this->template->set_block('PurchaseInvDisplay', 'warrantyBlock', 'warranties'); // innermost first
            $this->template->set_block('PurchaseInvDisplay', 'orderLineBlock', 'orderLines');
            while ($this->dsPurchaseInv->fetchNext()) {
                $disabled = false;                         
                   $warranties=[];
                if ($this->dsPurchaseInv->getValue(BUPurchaseInv::purchaseInvoiceRequireSerialNo)) {
                    // There is a warranty drop-down for each line
                    $dsWarranty->initialise();
                    $thisWarrantyID = $this->dsPurchaseInv->getValue(BUPurchaseInv::purchaseInvoiceWarrantyID);
                    while ($dsWarranty->fetchNext()) {
                        $warranties []=
                            array(
                                'warrantyDescription' => $dsWarranty->getValue(DBEWarranty::description),
                                'warrantyID'          => $dsWarranty->getValue(DBEWarranty::warrantyID),
                                'warrantySelected'    => ($thisWarrantyID == $dsWarranty->getValue(
                                        DBEWarranty::warrantyID
                                    )) ? CT_SELECTED : null
                                );
                        
                        
                    } 
                } else {
                    $disabled=true;
                }
             
                $lines []=
                    array(
                        'qtyOrdered'      => number_format(
                            $this->dsPurchaseInv->getValue(BUPurchaseInv::purchaseInvoiceQtyOrdered),
                            1,
                            '.',
                            ''
                        ),
                        'qtyOS'           => number_format(
                            $this->dsPurchaseInv->getValue(BUPurchaseInv::purchaseInvoiceQtyOS),
                            1,
                            '.',
                            ''
                        ),
                        'curPOUnitCost'   => number_format(
                            $this->dsPurchaseInv->getValue(BUPurchaseInv::purchaseInvoiceCurPOUnitCost),
                            2,
                            '.',
                            ''
                        ),
                        'curInvUnitCost'  => number_format(
                            $this->dsPurchaseInv->getValue(BUPurchaseInv::purchaseInvoiceCurInvUnitCost),
                            2,
                            '.',
                            ''
                        ),
                        'curInvTotalCost' => number_format(
                            $this->dsPurchaseInv->getValue(BUPurchaseInv::purchaseInvoiceCurInvTotalCost),
                            2,
                            '.',
                            ''
                        ),
                        'itemID'          => $this->dsPurchaseInv->getValue(BUPurchaseInv::purchaseInvoiceItemID),
                        'qtyToInvoice'    => $this->dsPurchaseInv->getValue(BUPurchaseInv::purchaseInvoiceQtyToInvoice),
                        'partNo'          => Controller::htmlDisplayText(
                            $this->dsPurchaseInv->getValue(BUPurchaseInv::purchaseInvoicePartNo)
                        ),
                        'requireSerialNo' => $this->dsPurchaseInv->getValue(
                            BUPurchaseInv::purchaseInvoiceRequireSerialNo
                        ),
                        'serialNo'        => $this->dsPurchaseInv->getValue(BUPurchaseInv::purchaseInvoiceSerialNo),
                        'renew'           => $this->dsPurchaseInv->getValue(BUPurchaseInv::purchaseInvoiceRenew),
                        'warrantyID'      => $this->dsPurchaseInv->getValue(BUPurchaseInv::purchaseInvoiceWarrantyID),
                        "warranties"      => $warranties,
                        "lineDisabled"        => $disabled,   
                        'description'     => Controller::htmlDisplayText(
                            $this->dsPurchaseInv->getValue(BUPurchaseInv::purchaseInvoiceDescription)
                        ),
                        'sequenceNo'      => $this->dsPurchaseInv->getValue(BUPurchaseInv::purchaseInvoiceSequenceNo),
                        'orderSequenceNo' => $this->dsPurchaseInv->getValue(
                            BUPurchaseInv::purchaseInvoiceOrderSequenceNo
                        )
                    );
            } 
        }
        return $this->success($lines);
    }
    
    function updateInvoice(){
        $body=$this->getBody(true);
        $porheadID=$body["porheadID"];
        $items=$body["items"];
        $invoiceNo=$body["invoiceNo"];
        $invoiceDate=$body["invoiceDate"];

        $this->setMethodName('updateInvoice');
        $dsPurchaseInv = &$this->dsPurchaseInv;
        $this->buPurchaseInv->initialiseDataset($dsPurchaseInv);
        //$dsPurchaseInv->debug=true;
        if (!$porheadID) {
            return $this->fail(APIException::badRequest,"porheadID is missing");
        }
        if (!$dsPurchaseInv->populateFromArray($items)) {              
            return $this->fail(APIException::badRequest,'Values entered must be numeric');
        }
        if (!$this->buPurchaseInv->validateQtys($dsPurchaseInv)) {
             return $this->fail(APIException::badRequest,'Quantities to invoice must not exceed outstanding quantities');
        }
        if (!$this->buPurchaseInv->validatePrices($dsPurchaseInv)) {            
            return $this->fail(APIException::badRequest,'Invoice Prices and VAT amounts must be in range 0 to 99999');
        }
        if (!$this->buPurchaseInv->validateSerialNos($dsPurchaseInv)) {            
            return $this->fail(APIException::badRequest,'Please complete the serial numbers');
        }
        if (!$this->buPurchaseInv->validateWarranties($dsPurchaseInv)) {
            return $this->fail(APIException::badRequest,'Please select warranties for all items');
        }
        if (!$invoiceNo) {            
            return $this->fail(APIException::badRequest,'Please enter a purchase invoice number');
        }
        if (!$this->buPurchaseInv->invoiceNoIsUnique(
            $invoiceNo,
            $porheadID
        )) {            
            return $this->fail(APIException::badRequest,'This purchase invoice no has already been used');
        }
        if (!$invoiceDate) {            
            return $this->fail(APIException::badRequest,'Please enter a purchase invoice date');
        }
        $date       = DateTime::createFromFormat(DATE_MYSQL_DATE, $invoiceDate);
        if (!$date) {            
            return $this->fail(APIException::badRequest,'Please enter a valid purchase invoice date');
        }
        $this->buPurchaseInv->update(
            $porheadID,
            $invoiceNo,
            $date->format(DATE_MYSQL_DATE),
            $dsPurchaseInv,
            $this->userID
        );
        $dsPorhead = new DataSet($this);
        $this->buPurchaseOrder->getHeaderByID($porheadID, $dsPorhead);
        return $this->success(["type"=>$dsPorhead->getValue(DBEJPorhead::type)]);       
    }
}
