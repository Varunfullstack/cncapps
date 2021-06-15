<?php
/**
 * Purchase Order controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Exceptions\JsonHttpException;
use CNCLTD\Supplier\infra\MySQLSupplierRepository;
use CNCLTD\Supplier\SupplierId;

global $cfg;
require_once($cfg['path_bu'] . '/BUPurchaseOrder.inc.php');
require_once($cfg['path_bu'] . '/BUPDFPurchaseOrder.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_gc'] . '/DataSet.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
define(
    'CTPURCHASEORDER_MSG_PURCHASEORDER_NOT_FND',
    'Purchase Order not found'
);
define(
    'CTPURCHASEORDER_MSG_PURCHASEORDERID_NOT_PASSED',
    'PurchaseOrderID not passed'
);
define(
    'CTPURCHASEORDER_MSG_SUPPLIERID_NOT_PASSED',
    'supplierID not passed'
);
define(
    'CTPURCHASEORDER_MSG_MUST_BE_INITIAL',
    'Must be an initial order or part received'
);
define(
    'CTPURCHASEORDER_MSG_SEQNO_NOT_PASSED',
    'sequence no not passed'
);
define(
    'CTPURCHASEORDER_MSG_ORDLINE_NOT_FND',
    'order line not found'
);
// Actions
define(
    'CTPURCHASEORDER_ACT_DISP_SEARCH',
    'dispSearch'
);
define(
    'CTPURCHASEORDER_ACT_CREATE',
    'create'
);
define(
    'CTPURCHASEORDER_ACT_DELETE',
    'deleteOrder'
);
define(
    'CTPURCHASEORDER_ACT_ADD_ORDLINE',
    'addOrdline'
);
define(
    'CTPURCHASEORDER_ACT_EDIT_ORDLINE',
    'editOrdline'
);
define(
    'CTPURCHASEORDER_ACT_UPDATE_ORDLINE',
    'updtOrdline'
);
define(
    'CTPURCHASEORDER_ACT_INSERT_ORDLINE',
    'insrtOrdline'
);
define(
    'CTPURCHASEORDER_ACT_MOVE_ORDLINE_UP',
    'moveUpOrdline'
);
define(
    'CTPURCHASEORDER_ACT_MOVE_ORDLINE_DOWN',
    'moveDownOrdline'
);
define(
    'CTPURCHASEORDER_ACT_DELETE_ORDLINE',
    'delOrdline'
);
define(
    'CTPURCHASEORDER_ACT_UPDATE_ORDHEAD',
    'updateOrdhead'
);
define(
    'CTPURCHASEORDER_ACT_GENERATE_PDF',
    'generatePDF'
);
// Page text
define(
    'CTPURCHASEORDER_TXT_DELETE',
    'delete'
);
define(
    'CTPURCHASEORDER_TXT_NEW_PURCHASEORDER',
    'Create Purchase Order'
);
define(
    'CTPURCHASEORDER_TXT_UPDATE_PURCHASEORDER',
    'Update Purchase Order'
);
define(
    'CTPURCHASEORDER_TXT_PRINT_PURCHASEORDERS',
    'Print Purchase Orders'
);

class CTPurchaseOrder extends CTCNC
{
    const UPDATE_STATUS = "UPDATE_STATUS";
    public $dsDateRange;
    public $buPurchaseOrder;
    public $dsPorhead;
    public $dsPorline;
    public $orderTypeArray = array(
        "I" => "Initial",
        "P" => "Part Received",
        "B" => "Both Initial & Part Received",
        "C" => "Completed",
        "A" => "Authorised"
    );

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
            "sales",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(302);
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
        $this->checkPermissions(SALES_PERMISSION);
        switch ($this->getAction()) {
            case 'saveLine':
                $this->saveLine();
                echo json_encode(["status" => "ok"]);
                break;
            case CTCNC_ACT_GENERATE_POS_FROM_SO:
                $this->generateFromSO();
                break;
            case CTCNC_ACT_SEARCH:
                $this->search();
                break;
            case CTCNC_ACT_DISPLAY_PO:
                $this->displayOrder();
                break;
            case CTPURCHASEORDER_ACT_CREATE:
                $this->createPO();
                break;
            case CTPURCHASEORDER_ACT_ADD_ORDLINE:
            case CTPURCHASEORDER_ACT_EDIT_ORDLINE:
                $this->editOrderLine();
                break;
            case CTPURCHASEORDER_ACT_UPDATE_ORDLINE:
            case CTPURCHASEORDER_ACT_INSERT_ORDLINE:
                $this->updateOrderLine();
                break;
            case CTPURCHASEORDER_ACT_MOVE_ORDLINE_UP:
                $this->moveOrderLineUp();
                break;
            case CTPURCHASEORDER_ACT_MOVE_ORDLINE_DOWN:
                $this->moveOrderLineDown();
                break;
            case CTPURCHASEORDER_ACT_DELETE_ORDLINE:
                $this->deleteOrderLine();
                break;
            case CTPURCHASEORDER_ACT_UPDATE_ORDHEAD:
                $this->updateHeader();
                break;
            case self::UPDATE_STATUS:
                echo json_encode($this->updateStatusController());
                break;
            case CTPURCHASEORDER_ACT_DELETE:
                $this->deleteOrder();
                break;
            case CTPURCHASEORDER_ACT_GENERATE_PDF:
                $this->generatePDF();
                break;
            case CTPURCHASEORDER_ACT_DISP_SEARCH:
            default:
                $this->displaySearchForm();
                break;
        }
    }

    private function saveLine()
    {
        $contents = json_decode(file_get_contents('php://input'), true);
        if (!isset($contents['purchaseOrderId'])) {
            throw new Exception('Purchase Order Id is required');
        }
        if (!isset($contents['line'])) {
            throw new Exception('Line is required');
        }
        $purchaseOrderLine = new DBEPorline($this);
        $purchaseOrderLine->setValue(
            DBEPorline::porheadID,
            $contents['purchaseOrderId']
        );
        $purchaseOrderLine->setValue(
            DBEPorline::sequenceNo,
            $contents['line']['seqNo']
        );
        $purchaseOrderLine->getRow();
        $purchaseOrderLine->setValue(DBEPorline::expectedDate, $contents['line']['expectedDate']);
        $purchaseOrderLine->setValue(DBEPorline::expectedTBC, $contents['line']['TBC']);
        $purchaseOrderLine->updateRow();
    }

    /**
     * Display form to allow selection of date range for which to produce invoices
     * @access private
     * @throws Exception
     */
    function generateFromSO()
    {
        $this->setMethodName('generateFromSO');
        $requiredByDate = null;
        if ($this->getParam('requiredByDate')) {
            $requiredByDate = DateTime::createFromFormat(
                'Y-m-d',
                $this->getParam('requiredByDate')
            );
        }
        $this->buPurchaseOrder->createPOsFromSO(
            $this->getParam('ordheadID'),
            $this->userID,
            $requiredByDate
        );
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'    => CTCNC_ACT_SEARCH,
                'ordheadID' => $this->getParam('ordheadID')
            )
        );
        header("Location: " . $urlNext);
        exit;
    }

    /**
     * Run search based upon passed parameters
     * Display search form with results
     * @access private
     * @throws Exception
     */
    function search()
    {
        $this->setMethodName('search');
        // remove trailing spaces from params passed
        foreach ($_REQUEST as $key => $value) {
            $_REQUEST[$key] = trim($value);
        }
        if (!($this->getParam('supplierID') . $this->getParam('porheadID') . $this->getParam(
                'ordheadID'
            ) . $this->getParam('supplierRef') . $this->getParam('orderType') . $this->getParam(
                'lineText'
            ) . $this->getParam('fromDate') . $this->getParam('toDate') . $this->getParam('partNo'))) {
            $this->setFormErrorMessage('Please specify at least one parameter');
        }
        if ($this->getParam('porheadID') && !is_numeric($this->getParam('porheadID'))) {
            $this->setFormErrorMessage('Order no must be numeric');
        }
        if ($this->getParam('ordheadID') && !is_numeric($this->getParam('ordheadID'))) {
            $this->setFormErrorMessage('Sales order no must be numeric');
        }
        if ($this->getFormError() == 0) {
            $this->buPurchaseOrder->search(
                $this->getParam('supplierID'),
                $this->getParam('porheadID'),
                $this->getParam('ordheadID'),
                $this->getParam('supplierRef'),
                $this->getParam('orderType'),
                $this->getParam('lineText'),
                $this->getParam('partNo'),
                $this->getParam('fromDate'),
                $this->getParam('toDate'),
                $this->dsPorhead
            );
        }
        if ($this->dsPorhead->rowCount() == 1) {
            $this->dsPorhead->fetchNext();
            $urlNext = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => CTCNC_ACT_DISPLAY_PO,
                    'porheadID' => $this->dsPorhead->getValue(DBEJPorhead::porheadID)
                    // if this is set then will show
                )                                                                                                                    // remaining POs for SO
            );
            header('Location: ' . $urlNext);
            exit;
        } else {
            $this->setAction(CTPURCHASEORDER_ACT_DISP_SEARCH);
            $this->displaySearchForm();
        }
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
            'PurchaseOrderSearch',
            'PurchaseOrderSearch.inc'
        );
        $this->loadReactCSS('SupplierSearchComponent.css');
        $this->loadReactScript('SupplierSearchComponent.js');
// Parameters
        $this->setPageTitle("Purchase Orders");
        $submitURL        = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array('action' => CTCNC_ACT_SEARCH)
        );
        $clearURL         = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array()
        );
        $urlSupplierPopup = Controller::buildLink(
            CTCNC_PAGE_SUPPLIER,
            array(
                'action'  => CTCNC_ACT_DISP_SUPPLIER_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $urlSupplierEdit  = Controller::buildLink(
            CTCNC_PAGE_SUPPLIER,
            array(
                'action'  => CTCNC_ACT_SUPPLIER_EDIT,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $urlCreateOrder   = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTPURCHASEORDER_ACT_CREATE
            )
        );
        $this->dsPorhead->initialise();
        $this->template->set_block(
            'PurchaseOrderSearch',
            'orderTypeBlock',
            'orderTypes'
        );
        foreach ($this->orderTypeArray as $key => $value) {
            $orderTypeSelected = ($this->getParam('orderType') == $key) ? CT_SELECTED : null;
            $this->template->set_var(
                array(
                    'orderTypeSelected'    => $orderTypeSelected,
                    'orderType'            => $key,
                    'orderTypeDescription' => $value
                )
            );
            $this->template->parse(
                'orderTypes',
                'orderTypeBlock',
                true
            );
        }
        $_SESSION['urlReferer'] =                    // so called functions know where to come back to
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'      => CTCNC_ACT_SEARCH,
                    'supplierID'  => $this->getParam('supplierID'),
                    'porheadID'   => $this->getParam('porheadID'),
                    'ordheadID'   => $this->getParam('ordheadID'),
                    'supplierRef' => $this->getParam('supplierRef'),
                    'type'        => $this->getParam('orderType'),
                    'lineText'    => $this->getParam('lineText'),
                    'partNo'      => $this->getParam('partNo')
                )
            );
        if ($this->dsPorhead->rowCount() > 0) {
            $this->template->set_block(
                'PurchaseOrderSearch',
                'orderBlock',
                'orders'
            );
            $supplierNameCol = $this->dsPorhead->columnExists(DBEJPorhead::supplierName);
            $typeCol         = $this->dsPorhead->columnExists(DBEJPorhead::type);
            $customerNameCol = $this->dsPorhead->columnExists(DBEJPorhead::customerName);
            $porheadIDCol    = $this->dsPorhead->columnExists(DBEJPorhead::porheadID);
            $supplierRefCol  = $this->dsPorhead->columnExists(DBEJPorhead::supplierRef);
            while ($this->dsPorhead->fetchNext()) {
                $orderURL     = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => CTCNC_ACT_DISPLAY_PO,
                        'porheadID' => $this->dsPorhead->getValue($porheadIDCol)
                    )
                );
                $customerName = $this->dsPorhead->getValue($customerNameCol);
                $supplierName = $this->dsPorhead->getValue($supplierNameCol);
                $this->template->set_var(
                    array(
                        'listCustomerName' => $customerName,
                        'listSupplierName' => $supplierName,
                        'listOrderURL'     => $orderURL,
                        'listPorheadID'    => $this->dsPorhead->getValue($porheadIDCol),
                        'listOrderType'    => $this->orderTypeArray[$this->dsPorhead->getValue($typeCol)],
                        'listSupplierRef'  => $this->dsPorhead->getValue($supplierRefCol)
                    )
                );
                $this->template->parse(
                    'orders',
                    'orderBlock',
                    true
                );
            }
        }
// search parameter section
        $supplierName = null;
        if ($this->getParam('supplierID')) {
            $supplierRepo = new MySQLSupplierRepository();
            $supplier     = $supplierRepo->getById(new SupplierId((int)$this->getParam('supplierID')));
            $supplierName = $supplier->name()->value();
        }
        $this->template->set_var(
            array(
                'supplierName'     => $supplierName,
                'porheadID'        => $this->getParam('porheadID'),
                'ordheadID'        => $this->getParam('ordheadID'),
                'supplierID'       => $this->getParam('supplierID'),
                'orderType'        => $this->getParam('orderType'),
                'lineText'         => Controller::htmlDisplayText($this->getParam('lineText')),
                'partNo'           => $this->getParam('partNo'),
                'fromDate'         => $this->getParam('fromDate'),
                'toDate'           => $this->getParam('toDate'),
                'supplierRef'      => $this->getParam('supplierRef'),
                'submitURL'        => $submitURL,
                'clearURL'         => $clearURL,
                'urlCreateOrder'   => $urlCreateOrder,
                'urlSupplierPopup' => $urlSupplierPopup,
                'urlSupplierEdit'  => $urlSupplierEdit
            )
        );
        $this->template->parse(
            'CONTENTS',
            'PurchaseOrderSearch',
            true
        );
        $this->parsePage();
    }

    function parsePage()
    {
        $urlLogo = null;
        if ($this->getAction() != CTPURCHASEORDER_ACT_DISP_SEARCH) {
            $urlLogo = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTPURCHASEORDER_ACT_DISP_SEARCH
                )
            );
        }
        $this->template->set_var(
            array(
                'urlLogo' => $urlLogo,
                'txtHome' => 'Home'
            )
        );
        parent::parsePage();
    }

    /**
     * Display the results of order search
     * @access private
     * @throws Exception
     */
    function displayOrder()
    {
        $this->setMethodName('displayOrder');
        $dsPorhead        = &$this->dsPorhead;
        $dsPorline        = &$this->dsPorline;
        $urlContactPopup  = null;
        $urlContactEdit   = null;
        $urlSupplierPopup = null;
        $urlSupplierEdit  = null;
        $urlUpdateHeader  = null;
        $urlDeleteOrder   = null;
        $txtDeleteOrder   = null;
        $urlSalesOrder    = null;
        $txtSalesOrder    = null;
        $urlGoodsIn       = null;
        $txtGoodsIn       = null;
        $urlGeneratePDF   = null;
        $txtGeneratePDF   = null;
        if (!$this->formError) {
            if (!$this->getParam('porheadID')) {
                $this->displayFatalError(CTPURCHASEORDER_MSG_PURCHASEORDERID_NOT_PASSED);
                return;
            }
            $this->buPurchaseOrder->getOrderByID(
                $this->getParam('porheadID'),
                $dsPorhead,
                $dsPorline
            );
            $dsPorhead->fetchNext();
        } else {    // if we are re-displaying header then only need lines
            $dsPorhead->initialise();
            $dsPorhead->fetchNext();
            $this->buPurchaseOrder->getLinesByID(
                $dsPorhead->getValue(DBEJPorhead::porheadID),
                $dsPorline
            );
        }
        $porheadID       = $dsPorhead->getValue(DBEJPorhead::porheadID);
        $orderType       = $dsPorhead->getValue(DBEJPorhead::type);
        $disabled        = CTCNC_HTML_DISABLED;                            // default - no editing
        $title           = null;
        $isPartReceived  = false;
        $canChangeStatus = $this->getDbeUser()->canChangeSalesOrdersAndPurchaseOrdersStatus();
        switch ($orderType) {
            case 'I':
                $title            = 'Purchase Order - Initial';
                $disabled         = null; // only initial orders may be edited
                $urlUpdateHeader  = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => CTPURCHASEORDER_ACT_UPDATE_ORDHEAD,
                        'porheadID' => $porheadID
                    )
                );
                $urlContactPopup  = Controller::buildLink(
                    CTCNC_PAGE_CONTACT,
                    array(
                        'action'     => CTCNC_ACT_CONTACT_POPUP,
                        'supplierID' => $dsPorhead->getValue(DBEJPorhead::supplierID),
                        'htmlFmt'    => CT_HTML_FMT_POPUP
                    )
                );
                $urlContactEdit   = Controller::buildLink(
                    CTCNC_PAGE_CONTACT,
                    array(
                        'action'  => CTCNC_ACT_CONTACT_EDIT,
                        'htmlFmt' => CT_HTML_FMT_POPUP
                    )
                );
                $urlSupplierPopup = Controller::buildLink(
                    CTCNC_PAGE_SUPPLIER,
                    array(
                        'action'  => CTCNC_ACT_DISP_SUPPLIER_POPUP,
                        'htmlFmt' => CT_HTML_FMT_POPUP
                    )
                );
                $urlSupplierEdit  = Controller::buildLink(
                    CTCNC_PAGE_SUPPLIER,
                    array(
                        'action'  => CTCNC_ACT_SUPPLIER_EDIT,
                        'htmlFmt' => CT_HTML_FMT_POPUP
                    )
                );
                $urlDeleteOrder   = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => CTPURCHASEORDER_ACT_DELETE,
                        'porheadID' => $porheadID
                    )
                );
                $txtDeleteOrder   = 'Delete';
                break;
            case 'P':
                $isPartReceived   = true;
                $title            = 'Purchase Order - Part Received';
                $disabled         = null; // only initial orders may be edited
                $urlUpdateHeader  = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => CTPURCHASEORDER_ACT_UPDATE_ORDHEAD,
                        'porheadID' => $porheadID
                    )
                );
                $urlContactPopup  = Controller::buildLink(
                    CTCNC_PAGE_CONTACT,
                    array(
                        'action'     => CTCNC_ACT_CONTACT_POPUP,
                        'supplierID' => $dsPorhead->getValue(DBEJPorhead::supplierID),
                        'htmlFmt'    => CT_HTML_FMT_POPUP
                    )
                );
                $urlContactEdit   = Controller::buildLink(
                    CTCNC_PAGE_CONTACT,
                    array(
                        'action'  => CTCNC_ACT_CONTACT_EDIT,
                        'htmlFmt' => CT_HTML_FMT_POPUP
                    )
                );
                $urlSupplierPopup = Controller::buildLink(
                    CTCNC_PAGE_SUPPLIER,
                    array(
                        'action'  => CTCNC_ACT_DISP_SUPPLIER_POPUP,
                        'htmlFmt' => CT_HTML_FMT_POPUP
                    )
                );
                $urlSupplierEdit  = Controller::buildLink(
                    CTCNC_PAGE_SUPPLIER,
                    array(
                        'action'  => CTCNC_ACT_SUPPLIER_EDIT,
                        'htmlFmt' => CT_HTML_FMT_POPUP
                    )
                );
                $urlDeleteOrder   = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => CTPURCHASEORDER_ACT_DELETE,
                        'porheadID' => $porheadID
                    )
                );
                $txtDeleteOrder   = 'Delete';
                break;
            case 'A':
                $title = 'Purchase Order - Authorised';
                break;
            case 'C':
                $title = 'Purchase Order - Completed (not authorised)';
                break;
        }// end switch
        if ((($orderType == 'I') || ($orderType == 'P')) && ($dsPorhead->getValue(
                    DBEJPorhead::directDeliveryFlag
                ) == 'N')) {
            $urlGoodsIn = Controller::buildLink(
                CTCNC_PAGE_GOODSIN,
                array(
                    'action'    => CTCNC_ACT_DISPLAY_GOODS_IN,
                    'porheadID' => $porheadID
                )
            );
            $txtGoodsIn = 'Goods In';
        }
        $this->setPageTitle($title);
        $this->setTemplateFiles(
            array(
                'PurchaseOrderDisplay'     => 'PurchaseOrderDisplay.inc',
                'PurchaseOrderLineEditJS'  => 'PurchaseOrderLineEditJS.inc',
                'PurchaseOrderHeadDisplay' => 'PurchaseOrderHeadDisplay.inc',
                'SalesOrderLineIcons'      => 'SalesOrderLineIcons.inc',
            )
        );
        $this->loadReactScript('PurchaseOrderSupplierAndContactInputsComponent.js');
        $this->loadReactCSS('PurchaseOrderSupplierAndContactInputsComponent.css');
        // if there is a sales order then display the delivery details etc
        $this->template->set_var(
            [
                'delAdd1'         => null,
                'canChangeStatus' => $canChangeStatus ? 'true' : 'false',
                'type'            => $dsPorhead->getValue(DBEPorhead::type)
            ]
        ); // default
        $dbeOrdhead = new DBEJOrdhead($this);
        if ($dbeOrdhead->getRow($dsPorhead->getValue(DBEJPorhead::ordheadID))) {
            $urlSalesOrder = Controller::buildLink(
                CTCNC_PAGE_SALESORDER,
                array(
                    'action'    => CTCNC_ACT_DISP_SALESORDER,
                    'ordheadID' => $dsPorhead->getValue(DBEJPorhead::ordheadID)
                )
            );
            $txtSalesOrder = 'Sales Order';
            if ($dsPorhead->getValue(DBEJPorhead::directDeliveryFlag) == 'Y') {
                $this->template->set_var(
                    array(
                        'customerName' => Controller::htmlDisplayText($dbeOrdhead->getValue(DBEJOrdhead::customerName)),
                        'delAdd1'      => Controller::htmlDisplayText($dbeOrdhead->getValue(DBEJOrdhead::delAdd1)),
                        'delAdd2'      => Controller::htmlDisplayText($dbeOrdhead->getValue(DBEJOrdhead::delAdd2)),
                        'delAdd3'      => Controller::htmlDisplayText($dbeOrdhead->getValue(DBEJOrdhead::delAdd3)),
                        'delTown'      => Controller::htmlDisplayText($dbeOrdhead->getValue(DBEJOrdhead::delTown)),
                        'delCounty'    => Controller::htmlDisplayText($dbeOrdhead->getValue(DBEJOrdhead::delCounty)),
                        'delPostcode'  => Controller::htmlDisplayText($dbeOrdhead->getValue(DBEJOrdhead::delPostcode))
                    )
                );
            }
        }
        // get sales order delivery contact
        $dbeContact = new DBEContact($this);
        $dbeContact->getRow($dbeOrdhead->getValue(DBEJOrdhead::delContactID));
        // if there are lines then allow print of purchase order
        if ($dsPorline->rowCount() > 0) {
            $urlGeneratePDF = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => CTPURCHASEORDER_ACT_GENERATE_PDF,
                    'porheadID' => $porheadID
                )
            );
            $txtGeneratePDF = 'Print';
        }
        $ordheadID    = ($dsPorhead->getValue(DBEJPorhead::ordheadID) ? $dsPorhead->getValue(
            DBEJPorhead::ordheadID
        ) : null);
        $supplierLink = null;
        // If supplier has a web site then display link
        if ($dsPorhead->getValue(DBEJPorhead::webSiteURL)) {
            $supplierLink = '<A HREF="' . $dsPorhead->getValue(
                    DBEJPorhead::webSiteURL
                ) . '" target="_blank">Web Site</A>';
        }
        // If contact has an email address then display email link
        $emailLink = null;
        if ($dsPorhead->getValue(DBEJPorhead::contactEmail)) {
            /** @noinspection HtmlDeprecatedAttribute */
            $emailLink = '<A HREF="mailto:' . $dsPorhead->getValue(
                    DBEJPorhead::contactEmail
                ) . '"' . ' title="Send email to contact"><img src="images/email.gif" border="0" alt="email"></A>';
        }
        $this->template->set_var(
            array(
                'isPartReceived'               => $isPartReceived ? 'true' : 'false',
                'supplierID'                   => $dsPorhead->getValue(DBEJPorhead::supplierID),
                'type'                         => $dsPorhead->getValue(DBEJPorhead::type),
                'porheadID'                    => $porheadID,
                'userID'                       => $dsPorhead->getValue(DBEJPorhead::userID),
                'orderUserID'                  => $dsPorhead->getValue(DBEJPorhead::orderUserID),
                'contactID'                    => $dsPorhead->getValue(DBEJPorhead::supplierContactId),
                'contactName'                  => Controller::htmlInputText(
                    $dsPorhead->getValue(DBEJPorhead::contactName)
                ),
                'contactPhone'                 => Controller::htmlDisplayText(
                    $dsPorhead->getValue(DBEJPorhead::contactPhone)
                ),
                'emailLink'                    => $emailLink,
                'raisedByName'                 => Controller::htmlDisplayText(
                    $dsPorhead->getValue(DBEJPorhead::raisedByName)
                ),
                'orderedByName'                => Controller::htmlDisplayText(
                    $dsPorhead->getValue(DBEJPorhead::orderedByName)
                ),
                'orderRequiredBy'              => $dsPorhead->getValue(DBEPorhead::requiredBy),
                'orderDate'                    => Controller::dateYMDtoDMY(
                    $dsPorhead->getValue(DBEJPorhead::orderDate)
                ),
                'supplierName'                 => Controller::htmlInputText(
                    $dsPorhead->getValue(DBEJPorhead::supplierName)
                ),
                'supplierLink'                 => $supplierLink,
                'date'                         => Controller::dateYMDtoDMY($dsPorhead->getValue(DBEJPorhead::date)),
                'vatCode'                      => Controller::htmlDisplayText(
                    $dsPorhead->getValue(DBEJPorhead::vatCode)
                ),
                'vatRate'                      => Controller::htmlDisplayText(
                    $dsPorhead->getValue(DBEJPorhead::vatRate)
                ),
                'directDeliveryFlagChecked'    => $this->getChecked(
                    $dsPorhead->getValue(DBEJPorhead::directDeliveryFlag)
                ),
                'deliveryConfirmedFlagChecked' => $this->getChecked(
                    $dsPorhead->getValue(DBEPorhead::deliveryConfirmedFlag)
                ),
                'completionNotifiedFlag'       => $dsPorhead->getValue(DBEPorhead::completionNotifiedFlag),
                'supplierRef'                  => Controller::htmlInputText(
                    $dsPorhead->getValue(DBEJPorhead::supplierRef)
                ),
                'ordheadID'                    => $ordheadID,
                'salesOrderContact'            => Controller::htmlDisplayText(
                    $dbeContact->getValue(DBEContact::firstName) . ' ' . $dbeContact->getValue(DBEContact::lastName)
                ),
                'DISABLED'                     => $disabled,
                'urlContactPopup'              => $urlContactPopup,
                'urlContactEdit'               => $urlContactEdit,
                'urlSupplierPopup'             => $urlSupplierPopup,
                'urlSupplierEdit'              => $urlSupplierEdit,
                'urlUpdateHeader'              => $urlUpdateHeader,
                'urlDeleteOrder'               => $urlDeleteOrder,
                'txtDeleteOrder'               => $txtDeleteOrder,
                'urlSalesOrder'                => $urlSalesOrder,
                'txtSalesOrder'                => $txtSalesOrder,
                'urlGoodsIn'                   => $urlGoodsIn,
                'txtGoodsIn'                   => $txtGoodsIn,
                'urlGeneratePDF'               => $urlGeneratePDF,
                'txtGeneratePDF'               => $txtGeneratePDF
            )
        );
        // payment method
        $dbePayMethod = new DBEPayMethod($this);
        $dbePayMethod->getRows('description');    // description is the sort order
        $this->template->set_block(
            'PurchaseOrderHeadDisplay',
            'payMethodBlock',
            'payMethods'
        );
        while ($dbePayMethod->fetchNext()) {
            $this->template->set_var(
                array(
                    'payMethodDescription' => $dbePayMethod->getValue(DBEPayMethod::description),
                    'payMethodID'          => $dbePayMethod->getValue(DBEPayMethod::payMethodID),
                    'payMethodSelected'    => ($dsPorhead->getValue(
                            DBEJPorhead::payMethodID
                        ) == $dbePayMethod->getValue(
                            DBEPayMethod::payMethodID
                        )) ? CT_SELECTED : null
                )
            );
            $this->template->parse(
                'payMethods',
                'payMethodBlock',
                true
            );
        }
        $curGrandTotalCost = 0;
        if ($dsPorline->rowCount() > 0) {
            $this->template->set_block(
                'PurchaseOrderDisplay',
                'orderLineBlock',
                'orderLines'
            );
            while ($dsPorline->fetchNext()) {
                $sequenceNo        = $dsPorline->getValue(DBEJPorline::sequenceNo);
                $itemDescription   = $dsPorline->getValue(DBEJPorline::itemDescription);
                $expectedDateInput = $TBCInput = null;
                if ((float)$dsPorline->getValue(
                        DBEPorline::curUnitCost
                    ) && $dsPorline->getValue(DBEPorline::itemID) != 1491) {
                    $checkedAttribute  = $dsPorline->getValue(DBEPorline::expectedTBC) ? 'checked' : null;
                    $expectedDateInput = "<input type='date'  onchange='expectedChanged()' value='{$dsPorline->getValue(DBEPorline::expectedDate)}'>";
                    $TBCInput          = "<input type='checkbox' onchange='tbcChanged()' {$checkedAttribute}>";
                }
                $curTotalCost      = $dsPorline->getValue(DBEJPorline::curUnitCost) * $dsPorline->getValue(
                        DBEJPorline::qtyOrdered
                    );
                $curGrandTotalCost += $curTotalCost;
                $this->template->set_var(
                    array(
                        'itemID'       => $dsPorline->getValue(DBEJPorline::itemID),
                        'partNo'       => Controller::htmlDisplayText($dsPorline->getValue(DBEJPorline::partNo)),
                        'qtyOrdered'   => number_format(
                            $dsPorline->getValue(DBEJPorline::qtyOrdered),
                            2,
                            '.',
                            ''
                        ),
                        'qtyReceived'  => number_format(
                            $dsPorline->getValue(DBEJPorline::qtyReceived),
                            2,
                            '.',
                            ''
                        ),
                        'curUnitCost'  => number_format(
                            $dsPorline->getValue(DBEJPorline::curUnitCost),
                            2,
                            '.',
                            ''
                        ),
                        'curTotalCost' => number_format(
                            $curTotalCost,
                            2,
                            '.',
                            ''
                        ),
                        'expectedDate' => $expectedDateInput,
                        'TBCInput'     => $TBCInput,
                        'seqNo'        => $dsPorline->getValue(DBEJPorline::sequenceNo)
                    )
                );
                if ($disabled != CTCNC_HTML_DISABLED) {        // enabled so allow/show editing options
                    $urlEditLine = Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => CTPURCHASEORDER_ACT_EDIT_ORDLINE,
                            'porheadID'  => $porheadID,
                            'sequenceNo' => $sequenceNo
                        )
                    );
                    // common to comment and item lines
                    $urlAddLine      = Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => CTPURCHASEORDER_ACT_ADD_ORDLINE,
                            'porheadID'  => $porheadID,
                            'sequenceNo' => ($sequenceNo + 1)    // new line below current
                        )
                    );
                    $urlMoveLineUp   = Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => CTPURCHASEORDER_ACT_MOVE_ORDLINE_UP,
                            'porheadID'  => $porheadID,
                            'sequenceNo' => $sequenceNo
                        )
                    );
                    $urlMoveLineDown = Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => CTPURCHASEORDER_ACT_MOVE_ORDLINE_DOWN,
                            'porheadID'  => $porheadID,
                            'sequenceNo' => $sequenceNo
                        )
                    );
                    $urlDeleteLine   = Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => CTPURCHASEORDER_ACT_DELETE_ORDLINE,
                            'porheadID'  => $porheadID,
                            'sequenceNo' => $sequenceNo
                        )
                    );
                    // for javascript message remove all " and ' chars
                    $removeDescription = str_replace(
                        '"',
                        '',
                        $itemDescription
                    );
                    $removeDescription = str_replace(
                        '\'',
                        '',
                        $removeDescription
                    );
                    $this->template->set_var(
                        array(
                            'urlMoveLineUp'     => $urlMoveLineUp,
                            'urlMoveLineDown'   => $urlMoveLineDown,
                            'removeDescription' => $removeDescription,
                            'urlEditLine'       => $urlEditLine,
                            'urlDeleteLine'     => $urlDeleteLine,
                            'urlAddLine'        => $urlAddLine
                        )
                    );
                    $this->template->parse(
                        'salesOrderLineIcons',
                        'SalesOrderLineIcons',
                        true
                    );
                    if ($isPartReceived) {
                        $lineDescription = Controller::htmlDisplayText($itemDescription);
                    } else {
                        $lineDescription = '<A href="' . $urlEditLine . '">' . Controller::htmlDisplayText(
                                $itemDescription
                            ) . '</A>';
                    }

                }// not disabled
                else { // disabled
                    $lineDescription = Controller::htmlDisplayText($itemDescription);
                }
                $this->template->set_var(
                    'lineDescription',
                    $lineDescription
                );
                $this->template->parse(
                    'orderLines',
                    'orderLineBlock',
                    true
                );
                $this->template->set_var(
                    'salesOrderLineIcons',
                    null
                ); // clear for next line
            }
            $this->template->set_var(
                'curGrandTotalCost',
                number_format(
                    $curGrandTotalCost,
                    2,
                    '.',
                    ''
                )
            );
        }//if ($dsPorline->rowCount() > 0)
        $this->template->parse(
            'purchaseOrderHeadDisplay',
            'PurchaseOrderHeadDisplay',
            true
        );
        $this->template->parse(
            'CONTENTS',
            'PurchaseOrderDisplay',
            true
        );
        $this->parsePage();
    }

    /**
     * Creates new PO from passed sales order no and supplierID
     * @access private
     * @throws Exception
     */
    function createPO()
    {
        $this->setMethodName('createPO');
        if (!is_numeric($this->getParam('supplierID'))) {
            $this->setFormErrorMessage('Supplier No must be numeric');
        }
        if (!is_numeric($this->getParam('ordheadID'))) {
            $this->setFormErrorMessage('Sales order no must be numeric');
        } else {
            $buSalesOrder = new BUSalesOrder($this);
            $dsOrdhead    = new DataSet($this);
            if (!$buSalesOrder->getOrdheadByID(
                $this->getParam('ordheadID'),
                $dsOrdhead
            )) {
                $this->setFormErrorMessage('sales order not found');
            } else {
                if ($dsOrdhead->getValue(DBEJOrdhead::type) != 'I' & $dsOrdhead->getValue(DBEJOrdhead::type) != 'P') {
                    $this->setFormErrorMessage('sales order is not initial or part-despatched status');
                }
            }
        }
        if ($this->getFormError()) {
            $this->displaySearchForm();
            exit;
        }
        $porheadID = $this->buPurchaseOrder->createNewPO(
            $this->getParam('ordheadID'),
            $this->getParam('supplierID'),
            $this->userID
        );
        $urlNext   = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'    => CTCNC_ACT_DISPLAY_PO,
                'porheadID' => $porheadID
            )
        );
        header("Location: " . $urlNext);
        exit;
    }// end function orderLineForm()

    /**
     * Edit/Add Order Line
     * @access private
     * @throws Exception
     */
    function editOrderLine()
    {
        $this->setMethodName('editOrderLine');
        $this->setPageTitle('Purchase Order - Edit Line');
        if ($this->getParam('porheadID') === null) {
            $this->displayFatalError('Purchase order ID not provided');
            return;
        }
        if (!$this->buPurchaseOrder->getOrderHeaderByID(
            $this->getParam('porheadID'),
            $this->dsPorhead
        )) {
            $this->displayFatalError(CTPURCHASEORDER_MSG_PURCHASEORDER_NOT_FND);
            return;
        }
        if (!in_array($this->dsPorhead->getValue(DBEJPorhead::type), ['I', 'P'])) {
            $this->displayFatalError(CTPURCHASEORDER_MSG_MUST_BE_INITIAL);
            return;
        }
        if (!$this->formError) {
            if ($this->getAction() == CTPURCHASEORDER_ACT_EDIT_ORDLINE) {
                if (!$this->buPurchaseOrder->getOrdlineByIDSeqNo(
                    $this->getParam('porheadID'),
                    $this->getParam('sequenceNo'),
                    $this->dsPorline
                )) {
                    $this->displayFatalError(CTPURCHASEORDER_MSG_ORDLINE_NOT_FND);
                    return;
                }
            } else {
                $this->buPurchaseOrder->initialiseNewOrdline(
                    $this->getParam('porheadID'),
                    $this->dsPorline
                );
            }
        }
        $this->setTemplateFiles(
            array(
                'PurchaseOrderLineEdit'   => 'PurchaseOrderLineEdit.inc',
                'PurchaseOrderLineEditJS' => 'PurchaseOrderLineEditJS.inc' // javascript
            )
        );
        $this->loadReactScript('ItemListTypeAheadRenderer.js');
        $this->orderLineForm();
        $this->template->setVar(
            'disableOnPartReceive',
            $this->dsPorhead->getValue(DBEPorhead::type) == 'P' ? 'readonly' : null
        );
        $this->template->parse(
            'purchaseOrderLineEditJS',
            'PurchaseOrderLineEditJS',
            true
        );
        $this->template->parse(
            'CONTENTS',
            'PurchaseOrderLineEdit',
            true
        );
        $this->parsePage();
    }

    /**
     * @throws Exception
     */
    function orderLineForm()
    {
        // Lines
        $this->template->set_var(
            array(
                'stockcat'               => $this->dsPorline->getValue(DBEJPorline::stockcat),
                'supplierName'           => $this->dsPorhead->getValue(DBEJPorhead::supplierName),
                'itemID'                 => $this->dsPorline->getValue(DBEJPorline::itemID),
                'itemDescription'        => htmlspecialchars($this->dsPorline->getValue(DBEJPorline::itemDescription)),
                'itemDescriptionMessage' => $this->dsPorline->getMessage(DBEJPorline::itemDescription),
                'qtyOrdered'             => $this->dsPorline->getValue(DBEJPorline::qtyOrdered),
                'qtyOrderedMessage'      => $this->dsPorline->getMessage(DBEJPorline::qtyOrdered),
                'qtyReceived'            => $this->dsPorline->getValue(DBEJPorline::qtyReceived),
                'qtyInvoiced'            => $this->dsPorline->getValue(DBEJPorline::qtyInvoiced),
                'curUnitCost'            => $this->dsPorline->getValue(DBEJPorline::curUnitCost),
                'curUnitCostMessage'     => $this->dsPorline->getMessage(DBEJPorline::curUnitCost),
                'expectedDate'           => $this->dsPorline->getValue(DBEJPorline::expectedDate),
                'expectedDateMessage'    => $this->dsPorline->getMessage(DBEJPorline::expectedDate),
                'expectedTBCChecked'     => $this->dsPorline->getValue(DBEPorline::expectedTBC) ? "checked" : null
            )
        );
        if ($this->getAction() == CTPURCHASEORDER_ACT_EDIT_ORDLINE) {
            $urlSubmit = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTPURCHASEORDER_ACT_UPDATE_ORDLINE
                )
            );
        } else {
            $urlSubmit = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTPURCHASEORDER_ACT_INSERT_ORDLINE
                )
            );
        }
        $urlCancel    = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'porheadID' => $this->dsPorhead->getValue(DBEJPorhead::porheadID),
                'action'    => CTCNC_ACT_DISPLAY_PO
            )
        );
        $urlItemPopup = Controller::buildLink(
            CTCNC_PAGE_ITEM,
            array(
                'action'  => CTCNC_ACT_DISP_ITEM_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $urlItemEdit  = Controller::buildLink(
            CTCNC_PAGE_ITEM,
            array(
                'action'  => CTCNC_ACT_ITEM_EDIT,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $this->template->set_var(
            array(
                'sequenceNo'   => $this->dsPorline->getValue(DBEJPorline::sequenceNo),
                'porheadID'    => $this->dsPorline->getValue(DBEJPorline::porheadID),
                'urlSubmit'    => $urlSubmit,
                'urlItemPopup' => $urlItemPopup,
                'urlItemEdit'  => $urlItemEdit,
                'urlCancel'    => $urlCancel
            )
        );
    }

    /**
     * Update/Insert order line
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function updateOrderLine()
    {
        $this->setMethodName('updateOrderLine');
        $this->formError = !$this->dsPorline->populateFromArray($this->getParam('porline'));
        if ($this->formError) {                    // Form error so redisplay edit form
            if ($this->getAction() == CTPURCHASEORDER_ACT_INSERT_ORDLINE) {
                $this->setAction(CTPURCHASEORDER_ACT_ADD_ORDLINE);
            } else {
                $this->setAction(CTPURCHASEORDER_ACT_EDIT_ORDLINE);
            }
            $this->setParam('porheadID', $this->dsPorline->getValue(DBEJPorline::porheadID));
            $this->setParam('sequenceNo', $this->dsPorline->getValue(DBEJPorline::sequenceNo));
            $this->editOrderLine();
            exit;
        }
        if ($this->getAction() == CTPURCHASEORDER_ACT_INSERT_ORDLINE) {
            $this->buPurchaseOrder->insertNewOrderLine($this->dsPorline);
        } else {
            $this->buPurchaseOrder->updateOrderLine(
                $this->dsPorline,
                'U'
            );
        }
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'porheadID' => $this->dsPorline->getValue(DBEJPorline::porheadID),
                'action'    => CTCNC_ACT_DISPLAY_PO
            )
        );
        header('Location: ' . $urlNext);
    }

    /**
     * Move order line up
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function moveOrderLineUp()
    {
        $this->setMethodName('moveOrderLineUp');
        $this->buPurchaseOrder->moveOrderLineUp(
            $this->getParam('porheadID'),
            $this->getParam('sequenceNo')
        );
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'porheadID' => $this->getParam('porheadID'),
                'action'    => CTCNC_ACT_DISPLAY_PO
            )
        );
        header('Location: ' . $urlNext);
    }

    /**
     * Move order line down
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function moveOrderLineDown()
    {
        $this->setMethodName('moveOrderLineDown');
        $this->buPurchaseOrder->moveOrderLineDown(
            $this->getParam('porheadID'),
            $this->getParam('sequenceNo')
        );
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'porheadID' => $this->getParam('porheadID'),
                'action'    => CTCNC_ACT_DISPLAY_PO
            )
        );
        header('Location: ' . $urlNext);
    }

    /**
     * Delete order line
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function deleteOrderLine()
    {

        $data = $this->getJSONData();
        if (empty($data['purchaseOrderHeadId'])) {
            throw new JsonHttpException(400, 'Purchase Order Id required');
        }
        if (empty($data['sequenceNumber'])) {
            throw new JsonHttpException(400, 'Sequence Number required');
        }
        $purchaseOrderHeadId = $data['purchaseOrderHeadId'];
        $sequenceNumber      = $data['sequenceNumber'];
        $this->buPurchaseOrder->deleteOrderLine(
            $purchaseOrderHeadId,
            $sequenceNumber
        );
        echo json_encode(["status" => "ok"]);
    }

    /**
     * Update order header details
     * @access private
     * @throws Exception
     */
    function updateHeader()
    {
        $this->setMethodName('updateHeader');
        $dsPorhead       = &$this->dsPorhead;
        $this->formError = (!$dsPorhead->populateFromArray($this->getParam('porhead')));
        if ($dsPorhead->getValue(DBEJPorhead::ordheadID) != 0) {
            $buSalesOrder = new BUSalesOrder($this);
            if (!$buSalesOrder->getOrdheadByID(
                $dsPorhead->getValue(DBEJPorhead::ordheadID),
                $dsOrdhead
            )) {
                $this->setFormErrorMessage('Not a valid Sales Order Number');
                $this->formError = TRUE;
            }
        }
        if ($this->formError) {
            $this->displayOrder();
            exit;
        } else {
            $dbePurchaseOrder = new DBEPorhead($this);
            $dbePurchaseOrder->getRow($dsPorhead->getValue(DBEJPorhead::porheadID));
            $buSalesOrder = new BUSalesOrder($this);
            if ($dbePurchaseOrder->getValue(DBEPorhead::deliveryConfirmedFlag) == 'N' && $dsPorhead->getValue(
                    DBEJPorhead::deliveryConfirmedFlag
                ) == 'Y' && $dbePurchaseOrder->getValue(DBEJPorhead::completionNotifiedFlag) == 'N') {

                $buSalesOrder->notifyPurchaseOrderCompletion($dbePurchaseOrder);
                $dsPorhead->setValue(
                    DBEJPorhead::completionNotifiedFlag,
                    $dbePurchaseOrder->getValue(DBEPorhead::completionNotifiedFlag)
                );

            }
            $this->buPurchaseOrder->updateHeader($dsPorhead,);
            if ($this->getParam('applyToAll')) {
                $buSalesOrder->updatePurchaseOrdersRequiredByDate($this->buPurchaseOrder->dbePorhead);
            }
            $urlNext = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'porheadID' => $this->getParam('porheadID'),
                    'action'    => CTCNC_ACT_DISPLAY_PO
                )
            );
            header('Location: ' . $urlNext);
        }
    }

    /**
     * Delete order
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function deleteOrder()
    {
        $this->setMethodName('deleteOrder');
        if (!$this->getParam('porheadID')) {
            $this->displayFatalError('Purchase order ID not provided');
            return;
        }
        if (!$this->buPurchaseOrder->getOrderHeaderByID(
            $this->getParam('porheadID'),
            $this->dsPorhead
        )) {
            $this->displayFatalError(CTPURCHASEORDER_MSG_PURCHASEORDER_NOT_FND);
            return;
        }
        $this->buPurchaseOrder->deleteOrder($this->getParam('porheadID'));
        $urlNext =                        // default action
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => CTCNC_ACT_SEARCH,
                    'ordheadID' => $this->dsPorhead->getValue(DBEJPorhead::ordheadID) // if this is set then will show
                )                                                                                                                    // remaining POs for SO
            );
        if ($this->dsPorhead->getValue(DBEJPorhead::ordheadID)) {
            $buSalesOrder       = new BUSalesOrder($this);
            $purchaseOrderCount = $buSalesOrder->countPurchaseOrders(
                $this->dsPorhead->getValue(DBEJPorhead::ordheadID)
            );
            if ($purchaseOrderCount == 0) {
                $urlNext = Controller::buildLink(
                    CTCNC_PAGE_SALESORDER,
                    array(
                        'action'    => CTCNC_ACT_DISP_SALESORDER,
                        'ordheadID' => $this->dsPorhead->getValue(DBEJPorhead::ordheadID)
                        // if this is set then will show
                    )                                                                                                                    // remaining POs for SO
                );
            }
        }
        header('Location: ' . $urlNext);
    }

    function generatePDF()
    {
        // generate PDF purchase order:
        $buPDFPurchaseOrder = new BUPDFPurchaseOrder(
            $this, $this->buPurchaseOrder, $this->getParam('porheadID')
        );
        $fileName           = 'P0' . $this->getParam('porheadID');
        if ($pdfFile = $buPDFPurchaseOrder->generateFile()) {
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename=' . $fileName . '.pdf;');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($pdfFile));
            echo stream_get_contents($pdfFile);
            $this->buPurchaseOrder->setOrderedFields(
                $this->getParam('porheadID'),
                $this->userID
            );
            exit();
        }
    }

    private function updateStatusController(): array
    {
        if (!$this->getDbeUser()->canChangeSalesOrdersAndPurchaseOrdersStatus()) {
            throw new JsonHttpException(403, "You are not authorized to perform this action!");
        }
        $purchaseOrderId = @$_GET['purchaseOrderId'];
        if (!$purchaseOrderId) {
            throw new JsonHttpException(400, "Purchase order Id is required!");
        }
        $purchaseOrdearHead = new DBEPorhead($this);
        if (!$purchaseOrdearHead->getRow($purchaseOrderId)) {
            throw new JsonHttpException(404, "Could not found Purchase order with Id: {$purchaseOrderId}!");
        }
        $newStatus = @$_GET['newStatus'];
        if (!in_array($newStatus, array_keys($this->orderTypeArray))) {
            $statuses = implode(',', array_keys($this->orderTypeArray));
            throw new JsonHttpException(400, "New status is not valid please provide on of {$statuses}");
        }
        $purchaseOrdearHead->setValue(DBEPorhead::type, $newStatus);
        $purchaseOrdearHead->updateRow();
        return ["status" => "ok"];
    }
}
