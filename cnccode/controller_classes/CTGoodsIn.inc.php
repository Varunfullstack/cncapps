<?php
/**
 * Goods Inwards controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;

use CNCLTD\Exceptions\APIException;
use CNCLTD\Supplier\infra\MySQLSupplierRepository;
use CNCLTD\Supplier\SupplierId;

require_once($cfg['path_bu'] . '/BUPurchaseOrder.inc.php');
require_once($cfg['path_bu'] . '/BUGoodsIn.inc.php');
require_once($cfg['path_bu'] . '/BUPDFPurchaseOrder.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_gc'] . '/DataSet.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Messages
define(
    'CTGOODSIN_MSG_PURCHASEORDER_NOT_FND',
    'Purchase Order not found'
);
define(
    'CTGOODSIN_MSG_PORHEADID_NOT_PASSED',
    'porheadID not passed'
);
define(
    'CTGOODSIN_MSG_SEQNO_NOT_PASSED',
    'sequence no not passed'
);
define(
    'CTGOODSIN_MSG_ORDLINE_NOT_FND',
    'order line not found'
);
// Actions
define(
    'CTGOODSIN_ACT_DISP_SEARCH',
    'dispSearch'
);
define(
    'CTGOODSIN_ACT_RECEIVE',
    'receive'
);

// Page text
class CTGoodsIn extends CTCNC
{

    public $dsDateRange;
    /** @var BUPurchaseOrder */
    public $buPurchaseOrder;
    /** @var BUGoodsIn */
    public $buGoodsIn;
    /** @var DSForm */
    public $dsPorhead;
    const LINES='lines';
    public $orderTypeArray = array(
        "I" => "Initial",
        "P" => "Part Received",
        "B" => "Both Initial & Part Received",
        "C" => "Completed",
        "A" => "Authorised"
    );
    /**
     * @var DSForm
     */
    public $dsPorline;
    /** @var DataSet $dsGoodsIn ReceiveDataSet */
    private $dsGoodsIn;

    /**
     * Dataset for Purchase Order record storage.
     *
     * @access  private
     * @param $requestMethod
     * @param $postVars
     * @param $getVars
     * @param $cookieVars
     * @param $cfg
     */
    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
                         $cfg
    )
    {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
        $roles = [
            SALES_PERMISSION
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(308);
        $this->buPurchaseOrder = new BUPurchaseOrder($this);
        $this->buGoodsIn       = new BUGoodsIn($this);
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
        switch ($this->getAction()) {
            case CTCNC_ACT_SEARCH:
                echo json_encode($this->search(),JSON_NUMERIC_CHECK);
                break;
            case self::LINES:
                switch ($this->requestMethod) {
                    case 'GET':
                        echo json_encode($this->getOrderLines(),JSON_NUMERIC_CHECK);
                        break;
                    case 'POST':
                        echo json_encode($this->handleReceive(),JSON_NUMERIC_CHECK);
                        break;
                    default:
                        # code...
                        break;
                }
                break;
            case CTGOODSIN_ACT_DISP_SEARCH:
                $this->displaySearchForm();
                break;
            case CTCNC_ACT_DISPLAY_GOODS_IN:
                // $this->displayGoodsIn();
                // break;
            case CTGOODSIN_ACT_RECEIVE:
                
            default:
                $this->displaySearch();
                break;
        }
        exit;
    }

   /**
     * Display list of types
     * @access private
     * @throws Exception
     */
    function displaySearch()
    {   
        $this->setPageTitle('Goods In');
        $this->setTemplateFiles(
            array('GoodsInComponent' => 'GoodsInSearch.inc')
        );
        $this->loadReactScript('GoodsInComponent.js');
        $this->loadReactCSS('GoodsInComponent.css');
        $this->template->parse(
            'CONTENTS',
            'GoodsInComponent',
            true
        );
        $this->parsePage();
    }

    /**
     * Display the results of order search
     * @access private
     * @throws Exception
     */
    function displaySearchForm()
    {
        $this->setMethodName('displaySearchForm');
        $this->setTemplateFiles(
            'GoodsInSearch',
            'GoodsInSearch.inc'
        );
// Parameters
        $this->setPageTitle("Goods In");
        $submitURL        = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array('action' => CTCNC_ACT_SEARCH)
        );
        $urlSupplierPopup = Controller::buildLink(
            CTCNC_PAGE_SUPPLIER,
            array(
                'action'  => CTCNC_ACT_DISP_SUPPLIER_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $this->dsPorhead->initialise();
        if ($this->dsPorhead->rowCount() > 0) {
            $this->template->set_block(
                'GoodsInSearch',
                'orderBlock',
                'orders'
            );
            $supplierNameCol = $this->dsPorhead->columnExists(DBEJPorhead::supplierName);
            $typeCol         = $this->dsPorhead->columnExists(DBEJPorhead::type);
            $customerNameCol = $this->dsPorhead->columnExists(DBEJPorhead::customerName);
            $porheadIDCol    = $this->dsPorhead->columnExists(DBEJPorhead::porheadID);
            $supplierRefCol  = $this->dsPorhead->columnExists(DBEJPorhead::supplierRef);
            while ($this->dsPorhead->fetchNext()) {
                $goodsInURL   = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => CTCNC_ACT_DISPLAY_GOODS_IN,
                        'porheadID' => $this->dsPorhead->getValue($porheadIDCol)
                    )
                );
                $customerName = $this->dsPorhead->getValue($customerNameCol);
                $supplierName = $this->dsPorhead->getValue($supplierNameCol);
                $this->template->set_var(
                    array(
                        'listCustomerName' => $customerName,
                        'listSupplierName' => $supplierName,
                        'listGoodsInURL'   => $goodsInURL,
                        'listPorheadID'    => $this->dsPorhead->getValue($porheadIDCol),
                        'listOrderType'    => $this->orderTypeArray[$this->dsPorhead->getValue($typeCol)],
                        'listSupplierRef'  => $this->dsPorhead->getValue($supplierRefCol)//,
                    )
                );
                $this->template->parse(
                    'orders',
                    'orderBlock',
                    true
                );
            }
        }
        $supplierName = null;
// search parameter section
        if ($this->getParam('supplierID')) {
            $supplierRepo = new MySQLSupplierRepository();
            $supplier     = $supplierRepo->getById(new SupplierId((int)$this->getParam('supplierID')));
            $supplierName = $supplier->name()->value();
        }
        $this->template->set_var(
            array(
                'supplierName'     => $supplierName,
                'porheadID'        => $this->getParam('porheadID'),
                'supplierID'       => $this->getParam('supplierID'),
                'submitURL'        => $submitURL,
                'urlSupplierPopup' => $urlSupplierPopup
            )
        );
        $this->loadReactCSS('SupplierSearchComponent.css');
        $this->loadReactScript('SupplierSearchComponent.js');
        $this->template->parse(
            'CONTENTS',
            'GoodsInSearch',
            true
        );
        $this->parsePage();
    }

    /**
     * Display the results of order search
     * @access private
     * @throws Exception
     */
    function displayGoodsIn()
    {
        $this->setMethodName('displayGoodsIn');
        $dsPorhead = &$this->dsPorhead;
        $dsPorline = &$this->dsPorline;
        if (!$this->getParam('porheadID')) {
            $this->displayFatalError(CTGOODSIN_MSG_PORHEADID_NOT_PASSED);
            return;
        }
        $this->buPurchaseOrder->getHeaderByID(
            $this->getParam('porheadID'),
            $dsPorhead
        );
        $dsPorhead->fetchNext();
        $this->buPurchaseOrder->getLinesByID(
            $dsPorhead->getValue(DBEPorhead::porheadID),
            $dsPorline
        );
        $dsOrdhead = new DataSet($this);
        // determine whether we should be asking for serial no and warranty for any items on this
        // order. e.g. There is a sales order and addItem flag is set.
        if ($dsPorhead->getValue(DBEPorhead::ordheadID) != 0) {
            $buSalesOrder = new BUSalesOrder($this);
            $buSalesOrder->getOrdheadByID(
                $dsPorhead->getValue(DBEPorhead::ordheadID),
                $dsOrdhead
            );
            $addCustomerItems = ($dsOrdhead->getValue(DBEOrdhead::addItem) == 'Y');
        } else {
            $addCustomerItems = FALSE;
        }
        if (!$this->getFormError()) {

            /*
            If the customer is an internal stock location then update the appropriate stock level
            */
            if ($dsPorhead->getValue(DBEPorhead::supplierID) == CONFIG_SALES_STOCK_SUPPLIERID) {
                $this->buGoodsIn->getInitialStockReceiveQtys(
                    CONFIG_SALES_STOCK_CUSTOMERID,
                    $dsPorline,
                    $this->dsGoodsIn
                );
            } else if ($dsPorhead->getValue(DBEPorhead::supplierID) == CONFIG_MAINT_STOCK_SUPPLIERID) {
                $this->buGoodsIn->getInitialStockReceiveQtys(
                    CONFIG_MAINT_STOCK_CUSTOMERID,
                    $dsPorline,
                    $this->dsGoodsIn
                );
            } else {
                $this->buGoodsIn->getInitialReceiveQtys(
                    $dsPorline,
                    $this->dsGoodsIn,
                    $addCustomerItems
                );
            }
        }
        $porheadID = $dsPorhead->getValue(DBEPorhead::porheadID);
        $this->setPageTitle('Goods In');
        $this->setTemplateFiles(array('GoodsInDisplay' => 'GoodsInDisplay.inc'));
        $urlReceive       = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'    => CTGOODSIN_ACT_RECEIVE,
                'porheadID' => $porheadID
            )
        );
        $urlPurchaseOrder = Controller::buildLink(
            CTCNC_PAGE_PURCHASEORDER,
            array(
                'action'    => CTCNC_ACT_DISPLAY_PO,
                'porheadID' => $porheadID
            )
        );
        $this->template->set_var(
            array(
                'porheadID'        => $porheadID,
                'supplierName'     => $dsPorhead->getValue(DBEJPorhead::supplierName),
                'customerName'     => $dsOrdhead->getValue(DBEJOrdhead::customerName),
                'ordheadID'        => $dsPorhead->getValue(DBEPorhead::ordheadID),
                'customerID'       => $dsOrdhead->getValue(DBEOrdhead::customerID),
                'urlReceive'       => $urlReceive,
                'urlPurchaseOrder' => $urlPurchaseOrder
            )
        );
        $dsWarranty = new DataSet($this);
        if ($addCustomerItems) {
            $this->buGoodsIn->getAllWarranties($dsWarranty);
        }
        $dsPorline->initialise();
        $this->dsGoodsIn->initialise();
        if ($this->dsGoodsIn->rowCount() > 0) {
            $this->template->set_block(
                'GoodsInDisplay',
                'warrantyBlock',
                'warranties'
            ); // innermost first
            $this->template->set_block(
                'GoodsInDisplay',
                'orderLineBlock',
                'orderLines'
            );
            while ($this->dsGoodsIn->fetchNext()) {
                $this->template->set_var(
                    array(
                        'description'     => Controller::htmlDisplayText(
                            $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetDescription)
                        ),
                        'sequenceNo'      => $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetSequenceNo),
                        'orderSequenceNo' => $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetOrderSequenceNo)
                    )
                );
                $this->template->set_var(
                    array(
                        'qtyOrdered'      => number_format(
                            $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetQtyOrdered),
                            1,
                            '.',
                            ''
                        ),
                        'itemID'          => $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetItemID),
                        'partNo'          => Controller::htmlDisplayText(
                            $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetPartNo)
                        ),
                        'qtyOS'           => number_format(
                            $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetQtyOS),
                            1,
                            '.',
                            ''
                        ),
                        'qtyToReceive'    => $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetQtyToReceive),
                        'serialNo'        => $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetSerialNo),
                        'requireSerialNo' => $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetRequireSerialNo),
                        'allowReceive'    => $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetAllowReceive),
                        'renew'           => $this->dsGoodsIn->getValue(
                            BUGoodsIn::receiveDataSetRenew
                        ) ? CT_CHECKED : null,
                        'customerItemID'  => $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetCustomerItemID),
                    )
                );
                if ($this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetRequireSerialNo)) {
                    $this->template->set_var(
                        'DISABLED',
                        null
                    );
                    // There is a warranty drop-down for each line
                    $dsWarranty->initialise();
                    $thisWarrantyID = $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetWarrantyID);
                    while ($dsWarranty->fetchNext()) {
                        $this->template->set_var(
                            array(
                                'warrantyDescription' => $dsWarranty->getValue(DBEWarranty::description),
                                'warrantyID'          => $dsWarranty->getValue(DBEWarranty::warrantyID),
                                'warrantySelected'    => ($thisWarrantyID == $dsWarranty->getValue(
                                        DBEWarranty::warrantyID
                                    )) ? CT_SELECTED : null
                            )
                        );
                        $this->template->parse(
                            'warranties',
                            'warrantyBlock',
                            true
                        );
                    } // while ($dsWarranty->fetchNext()
                } else {
                    $this->template->set_var(
                        'DISABLED',
                        'disabled'
                    ); // no serial no or warranty
                }
                if ($this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetAllowReceive) == FALSE) {
                    $this->template->set_var(
                        'lineDisabled',
                        'disabled'
                    ); // entry of anything!
                } else {
                    $this->template->set_var(
                        'lineDisabled',
                        null
                    );
                }
                $this->template->parse(
                    'orderLines',
                    'orderLineBlock',
                    true
                );
                $this->template->set_var(
                    'warranties',
                    null
                );
            }
        }
        $this->template->parse(
            'CONTENTS',
            'GoodsInDisplay',
            true
        );
        $this->parsePage();
    }

    /**
     * Perform receive
     * @access private
     * @throws Exception
     */
    function receive()
    {

        if ($_SERVER ['REQUEST_METHOD'] == 'POST') {
            $dsGoodsIn = &$this->dsGoodsIn;
            $this->buGoodsIn->initialiseReceiveDataset($dsGoodsIn);
            if (!$this->getParam('porheadID')) {
                $this->displayFatalError(CTGOODSIN_MSG_PORHEADID_NOT_PASSED);
            }
            if (!$dsGoodsIn->populateFromArray($this->getParam('receive'))) {
                $this->setFormErrorMessage('Quantities entered must be numeric');
                $this->displayGoodsIn();
                exit;
            }
            if (!$this->buGoodsIn->validateQtys($dsGoodsIn)) {
                $this->setFormErrorMessage('Quantities to receive must not exceed outstanding quantities');
                $this->displayGoodsIn();
                exit;
            }
            if (!$this->buGoodsIn->validateSerialNos($dsGoodsIn)) {
                $this->setFormErrorMessage('Please complete the serial numbers');
                $this->displayGoodsIn();
                exit;
            }
            if (!$this->buGoodsIn->validateWarranties($dsGoodsIn)) {
                $this->setFormErrorMessage('Please select warranties for all items');
                $this->displayGoodsIn();
                exit;
            }
            $this->buGoodsIn->receive(
                $this->getParam('porheadID'),
                $dsGoodsIn
            );
            $this->buPurchaseOrder->getHeaderByID(
                $this->getParam('porheadID'),
                $dsPorhead
            );
            $urlNext = Controller::buildLink(
                CTCNC_PAGE_PURCHASEORDER,
                array(
                    'action'    => CTCNC_ACT_DISPLAY_PO,
                    'porheadID' => $this->getParam('porheadID')
                )
            );
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $urlNext);
            exit;
        }
    }
    //-------------------new 
      /**
     * Run search based upon passed parameters
     * Display search form with results
     * @access private
     * @throws Exception
     */
    function search()
    {
        foreach ($_REQUEST as $key => $value) {
            $_REQUEST[$key] = trim($value);
        }
        $supplierID=@$_REQUEST["supplierID"];
        $porheadID=@$_REQUEST["porheadID"];      
        if ($porheadID && (!is_numeric($porheadID))) {            
            return $this->fail(APIException::badRequest,'Order no must be numeric');
        }
        $data =[];
        $this->dsPorhead->initialise();
        $this->buGoodsIn->search(
            $this->dsPorhead,
            $supplierID,
            $porheadID,
            null,
            null,
            'B'
        );
        if ($this->dsPorhead->rowCount() > 0) {         
            $supplierNameCol = $this->dsPorhead->columnExists(DBEJPorhead::supplierName);
            $typeCol         = $this->dsPorhead->columnExists(DBEJPorhead::type);
            $customerNameCol = $this->dsPorhead->columnExists(DBEJPorhead::customerName);
            $porheadIDCol    = $this->dsPorhead->columnExists(DBEJPorhead::porheadID);
            $supplierRefCol  = $this->dsPorhead->columnExists(DBEJPorhead::supplierRef);
            while ($this->dsPorhead->fetchNext()) {            
                $customerName = $this->dsPorhead->getValue($customerNameCol);
                $supplierName = $this->dsPorhead->getValue($supplierNameCol);
                $data []=
                    array(
                        'customerName' => $customerName,
                        'supplierName' => $supplierName,                        
                        'porheadID'    => $this->dsPorhead->getValue($porheadIDCol),
                        'orderType'    => $this->orderTypeArray[$this->dsPorhead->getValue($typeCol)],
                        'supplierRef'  => $this->dsPorhead->getValue($supplierRefCol),
                        'ordheadID'        => $this->dsPorhead->getValue(DBEPorhead::ordheadID),
                        'customerID'       => $this->dsPorhead->getValue(DBEJPorhead::customerID),
                    
                );
               
            }
        }
        return  $this->success( $data ) ;
    }
     
     /**
     * Display the results of order search
     * @access private
     * @throws Exception
     */
    function getOrderLines()
    {
        $porheadID =@$_REQUEST["porheadID"];
        $dsPorhead = &$this->dsPorhead;
        $dsPorline = &$this->dsPorline;
        if (!$porheadID) {
            return $this->fail(APIException::badRequest,CTGOODSIN_MSG_PORHEADID_NOT_PASSED);            
        }
        $this->buPurchaseOrder->getHeaderByID(
            $porheadID,
            $dsPorhead
        );
      
        $dsPorhead->fetchNext();
        //return $this->success( $dsPorhead->getValue(DBEPorhead::porheadID));        
        $this->buPurchaseOrder->getLinesByID(
            $dsPorhead->getValue(DBEPorhead::porheadID),
            $dsPorline
        );
        $dsOrdhead = new DataSet($this);
        // determine whether we should be asking for serial no and warranty for any items on this
        // order. e.g. There is a sales order and addItem flag is set.
        if ($dsPorhead->getValue(DBEPorhead::ordheadID) != 0) {
            $buSalesOrder = new BUSalesOrder($this);
            $buSalesOrder->getOrdheadByID(
                $dsPorhead->getValue(DBEPorhead::ordheadID),
                $dsOrdhead
            );
            $addCustomerItems = ($dsOrdhead->getValue(DBEOrdhead::addItem) == 'Y');
        } else {
            $addCustomerItems = FALSE;
        }
        if (!$this->getFormError()) {

            /*
            If the customer is an internal stock location then update the appropriate stock level
            */
            if ($dsPorhead->getValue(DBEPorhead::supplierID) == CONFIG_SALES_STOCK_SUPPLIERID) {
                $this->buGoodsIn->getInitialStockReceiveQtys(
                    CONFIG_SALES_STOCK_CUSTOMERID,
                    $dsPorline,
                    $this->dsGoodsIn
                );
            } else if ($dsPorhead->getValue(DBEPorhead::supplierID) == CONFIG_MAINT_STOCK_SUPPLIERID) {
                $this->buGoodsIn->getInitialStockReceiveQtys(
                    CONFIG_MAINT_STOCK_CUSTOMERID,
                    $dsPorline,
                    $this->dsGoodsIn
                );
            } else {
                $this->buGoodsIn->getInitialReceiveQtys(
                    $dsPorline,
                    $this->dsGoodsIn,
                    $addCustomerItems
                );
            }
        }
        $porheadID = $dsPorhead->getValue(DBEPorhead::porheadID);        
        $dsWarranty = new DataSet($this);
        $warranties =[];
        $lines=[];
        if ($addCustomerItems) {
            $this->buGoodsIn->getAllWarranties($dsWarranty);
        }
        $dsPorline->initialise();
        $this->dsGoodsIn->initialise();
        if ($this->dsGoodsIn->rowCount() > 0) {           
            while ($this->dsGoodsIn->fetchNext()) {
                $lineDisabled=false;
                if ($this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetAllowReceive) == FALSE) {
                    $lineDisabled =true;
                }  
                $disabled=false;
                $warrantyID="";
                if ($this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetRequireSerialNo)) {                    
                    // There is a warranty drop-down for each line
                    $dsWarranty->initialise();
                    $thisWarrantyID = $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetWarrantyID);
                    while ($dsWarranty->fetchNext()) {
                        if($thisWarrantyID == $dsWarranty->getValue(
                            DBEWarranty::warrantyID
                        ))
                        $warrantyID=$thisWarrantyID;
                        $warranties []=
                            array(
                                'warrantyDescription' => $dsWarranty->getValue(DBEWarranty::description),
                                'warrantyID'          => $dsWarranty->getValue(DBEWarranty::warrantyID),
                                
                                );
                                
                    } // while ($dsWarranty->fetchNext()
                } 
                else
                $disabled=true;
                $lines []=
                    [
                        'lineDisabled'    => $lineDisabled,
                        'description'     => Controller::htmlDisplayText(
                            $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetDescription)
                        ),
                        'sequenceNo'      => $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetSequenceNo),
                        'orderSequenceNo' => $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetOrderSequenceNo),                
                        'qtyOrdered'      => number_format(
                            $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetQtyOrdered),
                            1,
                            '.',
                            ''
                        ),
                        'itemID'          => $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetItemID),
                        'partNo'          => Controller::htmlDisplayText(
                            $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetPartNo)
                        ),
                        'qtyOS'           => number_format(
                            $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetQtyOS),
                            1,
                            '.',
                            ''
                        ),
                        'qtyToReceive'    => $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetQtyToReceive),
                        'serialNo'        => $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetSerialNo)??"",
                        'requireSerialNo' => $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetRequireSerialNo),
                        'allowReceive'    => $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetAllowReceive),
                        'renew'           => $this->dsGoodsIn->getValue(
                            BUGoodsIn::receiveDataSetRenew
                        ) ? CT_CHECKED : null,
                        'customerItemID'  => $this->dsGoodsIn->getValue(BUGoodsIn::receiveDataSetCustomerItemID),
                        "warranties" => $warranties ,
                        "disabled"=>$disabled,
                        "warrantyID"=>$warrantyID
                        ];                
            }
        }      
        return ["lines"=>$lines];
    }
  /**
     * Perform receive
     * @access private
     * @throws Exception
     */
    function handleReceive()
    {
        $porheadID = @$_REQUEST["porheadID"];
        $body = $this->getBody(true);
        $dsGoodsIn = &$this->dsGoodsIn;
        $this->buGoodsIn->initialiseReceiveDataset($dsGoodsIn);
        if (!$porheadID) {
            return $this->fail(APIException::notFound,"Missing parameters");
        }
        if (!$dsGoodsIn->populateFromArray($body)) {
            return $this->fail(APIException::conflict, 'Quantities entered must be numeric');
        }
        if (!$this->buGoodsIn->validateQtys($dsGoodsIn)) {
            return $this->fail(APIException::badRequest, 'Quantities to receive must not exceed outstanding quantities');
        }
        if (!$this->buGoodsIn->validateSerialNos($dsGoodsIn)) {
            return $this->fail(APIException::badRequest, 'Please complete the serial numbers');
        }
        if (!$this->buGoodsIn->validateWarranties($dsGoodsIn)) {
            return $this->fail(APIException::badRequest, 'Please select warranties for all items');
        }
        $this->buGoodsIn->receive(
            $porheadID,
            $dsGoodsIn
        );
        $this->buPurchaseOrder->getHeaderByID(
            $porheadID,
            $dsPorhead
        );
        return $this->success();
    }
}
