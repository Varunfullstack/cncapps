<?php
/**
 * Purchase Invoice controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Supplier\infra\MySQLSupplierRepository;
use CNCLTD\Supplier\SupplierId;

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
define('CTPURCHASEINV_ACT_DISP_SEARCH', 'dispSearch');
define('CTPURCHASEINV_ACT_UPDATE', 'doUpdate');
define('CTPURCHASEINV_ACT_DISPLAY', 'display');

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
    public $orderTypeArray = array(
        "I" => "Initial",
        "P" => "Part Received",
        "B" => "Both Initial & Part Received",
        "C" => "Completed",
        "A" => "Authorised"
    );

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
            case CTCNC_ACT_SEARCH:
                $this->search();
                break;
            case CTPURCHASEINV_ACT_DISP_SEARCH:
                $this->displaySearchForm();
                break;
            case CTPURCHASEINV_ACT_DISPLAY:
                $this->display();
                break;
            case CTPURCHASEINV_ACT_UPDATE:
                $this->doUpdate();
                break;
            default:
                $this->displaySearchForm();
                break;
        }
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
        if (($this->getParam('porheadID')) && (!is_numeric($this->getParam('porheadID')))) {
            $this->setFormErrorMessage('Order no must be numeric');
        }
        $found = false;
        if ($this->getFormError() == 0) {
           
            $found = $this->buPurchaseInv->search(
                $this->getParam('supplierID'),
                $this->getParam('porheadID'),
                $this->getParam('supplierRef'),
                $this->getParam('lineText'),
                $this->dsPorhead
            );
        }
        // one row and not already authorised
        if ($found & $this->dsPorhead->rowCount() == 1) {
            $this->dsPorhead->fetchNext();
            $urlNext = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => CTPURCHASEINV_ACT_DISPLAY,
                    'porheadID' => $this->dsPorhead->getValue(DBEJPorhead::porheadID)
                )
            );
            header('Location: ' . $urlNext);
            exit;
        } else {
            $this->setAction(CTPURCHASEINV_ACT_DISP_SEARCH);
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
        $this->setTemplateFiles('PurchaseInvSearch', 'PurchaseInvSearch.inc');
// Parameters
        $this->setPageTitle("Purchase Invoice Authorisation");
        $submitURL        = Controller::buildLink($_SERVER['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));
        $urlSupplierPopup = Controller::buildLink(
            CTCNC_PAGE_SUPPLIER,
            array(
                'action'  => CTCNC_ACT_DISP_SUPPLIER_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $this->dsPorhead->initialise();
        if ($this->dsPorhead->rowCount() > 0) {
            $this->template->set_block('PurchaseInvSearch', 'orderBlock', 'orders');
            $supplierNameCol = $this->dsPorhead->columnExists(DBEJPorhead::supplierName);
            $typeCol         = $this->dsPorhead->columnExists(DBEJPorhead::type);
            $customerNameCol = $this->dsPorhead->columnExists(DBEJPorhead::customerName);
            $porheadIDCol    = $this->dsPorhead->columnExists(DBEJPorhead::porheadID);
            $porheadDateCol  = $this->dsPorhead->columnExists(DBEJPorhead::date);
            $supplierRefCol  = $this->dsPorhead->columnExists(DBEJPorhead::supplierRef);
            while ($this->dsPorhead->fetchNext()) {
                $purchaseInvURL = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => CTPURCHASEINV_ACT_DISPLAY,
                        'porheadID' => $this->dsPorhead->getValue($porheadIDCol)
                    )
                );
                $customerName   = $this->dsPorhead->getValue($customerNameCol);
                $supplierName   = $this->dsPorhead->getValue($supplierNameCol);
                $this->template->set_var(
                    array(
                        'listCustomerName'   => $customerName,
                        'listSupplierName'   => $supplierName,
                        'listPurchaseInvURL' => $purchaseInvURL,
                        'listPorheadID'      => $this->dsPorhead->getValue($porheadIDCol),
                        'listDate'           => Controller::dateYMDtoDMY($this->dsPorhead->getValue($porheadDateCol)),
                        'listOrderType'      => $this->orderTypeArray[$this->dsPorhead->getValue($typeCol)],
                        'listSupplierRef'    => $this->dsPorhead->getValue($supplierRefCol)//,
                    )
                );
                $this->template->parse('orders', 'orderBlock', true);
            }
        }
// search parameter section
        $supplierName = null;
        if (($this->getParam('supplierID'))) {
            $supplierRepo = new MySQLSupplierRepository();
            $supplier     = $supplierRepo->getById(new SupplierId((int)$this->getParam('supplierID')));
            $supplierName = $supplier->name()->value();
        }
        $this->template->set_var(
            array(
                'supplierName'     => $supplierName,
                'porheadID'        => $this->getParam('porheadID'),
                'supplierID'       => $this->getParam('supplierID'),
                'lineText'         => $this->getParam('lineText'),
                'submitURL'        => $submitURL,
                'urlSupplierPopup' => $urlSupplierPopup
            )
        );
        $this->loadReactCSS('SupplierSearchComponent.css');
        $this->loadReactScript('SupplierSearchComponent.js');
        $this->template->parse('CONTENTS', 'PurchaseInvSearch', true);
        $this->parsePage();
    }

    /**
     * Display the results of order search
     * @access private
     * @throws Exception
     */
    function display()
    {
        $this->setMethodName('display');
        $dsPorhead = &$this->dsPorhead;
        $dsPorline = &$this->dsPorline;
        if (!$this->getParam('porheadID')) {
            $this->displayFatalError(CTPURCHASEINV_MSG_PORHEADID_NOT_PASSED);
            return;
        }
        $this->buPurchaseOrder->getHeaderByID($this->getParam('porheadID'), $dsPorhead);
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
        $this->setPageTitle("Purchase Invoice Authorisation");
        $this->setTemplateFiles(array('PurchaseInvDisplay' => 'PurchaseInvDisplay.inc'));
        $urlUpdate        = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'    => CTPURCHASEINV_ACT_UPDATE,
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
        $urlSalesOrder    = Controller::buildLink(
            CTCNC_PAGE_SALESORDER,
            array(
                'action'    => CTCNC_ACT_DISP_SALESORDER,
                'ordheadID' => $dsPorhead->getValue(DBEJPorhead::ordheadID),
                'htmlFmt'   => CT_HTML_FMT_POPUP
            )
        );
        $this->template->set_var(
            array(
                'porheadID'           => $porheadID,
                'supplierName'        => Controller::htmlDisplayText($dsPorhead->getValue(DBEJPorhead::supplierName)),
                'vatRate'             => $dsPorhead->getValue(DBEJPorhead::vatRate),
                'purchaseInvoiceDate' => $this->getParam('purchaseInvoiceDate'),
                'purchaseInvoiceNo'   => Controller::htmlDisplayText($this->getParam('purchaseInvoiceNo')),
                'urlUpdate'           => $urlUpdate,
                'urlPurchaseOrder'    => $urlPurchaseOrder,
                'urlSalesOrder'       => $urlSalesOrder
            )
        );
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
                $this->template->set_var(
                    array(
                        'description'     => Controller::htmlDisplayText(
                            $this->dsPurchaseInv->getValue(BUPurchaseInv::purchaseInvoiceDescription)
                        ),
                        'sequenceNo'      => $this->dsPurchaseInv->getValue(BUPurchaseInv::purchaseInvoiceSequenceNo),
                        'orderSequenceNo' => $this->dsPurchaseInv->getValue(
                            BUPurchaseInv::purchaseInvoiceOrderSequenceNo
                        )
                    )
                );
                $bodyTagExtras = 'onLoad="calculateTotals(); document.forms[0].elements[1].focus();"';
                $this->template->set_var('bodyTagExtras', $bodyTagExtras);
                $this->template->set_var(
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
                        'warrantyID'      => $this->dsPurchaseInv->getValue(BUPurchaseInv::purchaseInvoiceWarrantyID)
                    )
                );
                /*
                Prevent submit if renewals are not completed
                */
                if ($this->getFormError()) {
                    $this->template->set_var('SUBMIT_DISABLED', 'DISABLED');
                } else {
                    $this->template->set_var('SUBMIT_DISABLED', null);
                }
                if ($this->dsPurchaseInv->getValue(BUPurchaseInv::purchaseInvoiceRequireSerialNo)) {

                    $this->template->set_var('DISABLED', null);
                    // There is a warranty drop-down for each line
                    $dsWarranty->initialise();
                    $thisWarrantyID = $this->dsPurchaseInv->getValue(BUPurchaseInv::purchaseInvoiceWarrantyID);
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
                        $this->template->parse('warranties', 'warrantyBlock', true);
                    } // while ($dsWarranty->fetchNext()
                } else {
                    $this->template->set_var('DISABLED', 'disabled'); // no serial no or warranty
                }
                $this->template->parse('orderLines', 'orderLineBlock', true);
                $this->template->set_var('warranties', null); // clear for next line
            } // while ($dsPorline->fetchNext())
        }// if ($dsPorline->rowCount() > 0)
        $this->template->parse('CONTENTS', 'PurchaseInvDisplay', true);
        $this->parsePage();
    }

    /**
     * perform updates
     * @throws Exception
     */
    function doUpdate()
    {
        $this->setMethodName('doUpdate');
        $dsPurchaseInv = &$this->dsPurchaseInv;
        $this->buPurchaseInv->initialiseDataset($dsPurchaseInv);
        if (!$this->getParam('porheadID')) {
            $this->displayFatalError(CTGOODSIN_MSG_PORHEADID_NOT_PASSED);
        }
        if (!$dsPurchaseInv->populateFromArray($this->getParam('purchaseInv'))) {
            $this->setFormErrorMessage('Values entered must be numeric');
            $this->display();
            exit;
        }
        if (!$this->buPurchaseInv->validateQtys($dsPurchaseInv)) {
            $this->setFormErrorMessage('Quantities to invoice must not exceed outstanding quantities');
            $this->display();
            exit;
        }
        if (!$this->buPurchaseInv->validatePrices($dsPurchaseInv)) {
            $this->setFormErrorMessage('Invoice Prices and VAT amounts must be in range 0 to 99999');
            $this->display();
            exit;
        }
        if (!$this->buPurchaseInv->validateSerialNos($dsPurchaseInv)) {
            $this->setFormErrorMessage('Please complete the serial numbers');
            $this->display();
            exit;
        }
        if (!$this->buPurchaseInv->validateWarranties($dsPurchaseInv)) {
            $this->setFormErrorMessage('Please select warranties for all items');
            $this->display();
            exit;
        }
        if (!$this->getParam('purchaseInvoiceNo')) {
            $this->setFormErrorMessage('Please enter a purchase invoice number');
            $this->display();
            exit;
        }
        if (!$this->buPurchaseInv->invoiceNoIsUnique(
            $this->getParam('purchaseInvoiceNo'),
            $this->getParam('porheadID')
        )) {
            $this->setFormErrorMessage('This purchase invoice no has already been used');
            $this->display();
            exit;
        }
        if (!$this->getParam('purchaseInvoiceDate')) {
            $this->setFormErrorMessage('Please enter a purchase invoice date');
            $this->display();
            exit;
        }
        $dateString = $this->getParam('purchaseInvoiceDate');
        $date       = DateTime::createFromFormat(DATE_MYSQL_DATE, $dateString);
        if (!$date) {
            $this->setFormErrorMessage('Please enter a valid purchase invoice date');
            $this->display();
            exit;
        }
        $this->buPurchaseInv->update(
            $this->getParam('porheadID'),
            $this->getParam('purchaseInvoiceNo'),
            $date->format(DATE_MYSQL_DATE),
            $dsPurchaseInv,
            $this->userID
        );
        $dsPorhead = new DataSet($this);
        $this->buPurchaseOrder->getHeaderByID($this->getParam('porheadID'), $dsPorhead);
        if ($dsPorhead->getValue(DBEJPorhead::type) == 'A') {
            $urlNext = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTCNC_ACT_DISPLAY_SEARCH_FORM
                )
            );
        } else {
            $urlNext = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => CTCNC_ACT_DISPLAY_GOODS_IN,
                    'porheadID' => $this->getParam('porheadID')
                )
            );
        }
        header('Location: ' . $urlNext);
        exit;
    }

}
