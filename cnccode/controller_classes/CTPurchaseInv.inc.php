<?php
/**
 * Purchase Invoice controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUPurchaseInv.inc.php');
require_once($cfg['path_bu'] . '/BUPurchaseOrder.inc.php');
require_once($cfg['path_bu'] . '/BUGoodsIn.inc.php');
require_once($cfg['path_bu'] . '/BUSupplier.inc.php');
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
    var $buPurchaseInv = '';
    var $dsPurchaseInv = '';
    var $buPurchaseOrder = '';
    var $dsPorhead = '';
    var $dsPorline = '';
    var $orderTypeArray = array(
        "I" => "Initial",
        "P" => "Part Received",
        "B" => "Both Initial & Part Received",
        "C" => "Completed",
        "A" => "Authorised"
    );

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
        $this->buPurchaseInv = new BUPurchaseInv($this);
        $this->buPurchaseOrder = new BUPurchaseOrder($this);
        $this->dsPorhead = new DSForm($this);
        $this->dsPorline = new DSForm($this);
        $this->dsPorline->copyColumnsFrom($this->buPurchaseOrder->dbeJPorline);
        $this->dsPorhead->copyColumnsFrom($this->buPurchaseOrder->dbeJPorhead);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_ACCOUNTS);
        switch ($_REQUEST['action']) {
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
     */
    function search()
    {
        $this->setMethodName('search');
        if (($_REQUEST['porheadID'] != '') AND (!is_numeric($_REQUEST['porheadID']))) {
            $this->setFormErrorMessage('Order no must be numeric');;
        }
        if ($this->getFormError() == 0) {

            $found = $this->buPurchaseInv->search(
                $_REQUEST['supplierID'],
                $_REQUEST['porheadID'],
                $_REQUEST['supplierRef'],
                $_REQUEST['lineText'],
                $this->dsPorhead
            );
        }
        // one row and not already authorised
        if ($found & $this->dsPorhead->rowCount() == 1) {
            $this->dsPorhead->fetchNext();
            $urlNext =
                $this->buildLink($_SERVER['PHP_SELF'],
                                 array(
                                     'action' => CTPURCHASEINV_ACT_DISPLAY,
                                     'porheadID' => $this->dsPorhead->getValue('porheadID')
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
     */
    function displaySearchForm()
    {
        $this->setMethodName('displaySearchForm');
        $this->setTemplateFiles('PurchaseInvSearch', 'PurchaseInvSearch.inc');
// Parameters
        $this->setPageTitle("Purchase Invoice Authorisation");
        $submitURL = $this->buildLink($_SERVER['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));
        $urlSupplierPopup =
            $this->buildLink(
                CTCNC_PAGE_SUPPLIER,
                array(
                    'action' => CTCNC_ACT_DISP_SUPPLIER_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $this->dsPorhead->initialise();
        if ($this->dsPorhead->rowCount() > 0) {
            $this->template->set_block('PurchaseInvSearch', 'orderBlock', 'orders');
            $supplierNameCol = $this->dsPorhead->columnExists('supplierName');
            $typeCol = $this->dsPorhead->columnExists('type');
            $customerNameCol = $this->dsPorhead->columnExists('customerName');
            $porheadIDCol = $this->dsPorhead->columnExists('porheadID');
            $porheadDateCol = $this->dsPorhead->columnExists('date');
            $supplierRefCol = $this->dsPorhead->columnExists('supplierRef');
            $printedCol = $this->dsPorhead->columnExists('printed');
            $ordheadIDCol = $this->dsPorhead->columnExists('ordheadID');
            while ($this->dsPorhead->fetchNext()) {
                $purchaseInvURL =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTPURCHASEINV_ACT_DISPLAY,
                            'porheadID' => $this->dsPorhead->getValue($porheadIDCol)
                        )
                    );
                $customerName = $this->dsPorhead->getValue($customerNameCol);
                $supplierName = $this->dsPorhead->getValue($supplierNameCol);
                $this->template->set_var(
                    array(
                        'listCustomerName' => $customerName,
                        'listSupplierName' => $supplierName,
                        'listPurchaseInvURL' => $purchaseInvURL,
                        'listPorheadID' => $this->dsPorhead->getValue($porheadIDCol),
                        'listDate' => Controller::dateYMDtoDMY($this->dsPorhead->getValue($porheadDateCol)),
                        'listOrderType' => $this->orderTypeArray[$this->dsPorhead->getValue($typeCol)],
                        'listSupplierRef' => $this->dsPorhead->getValue($supplierRefCol)//,
                    )
                );
                $this->template->parse('orders', 'orderBlock', true);
            }
        }
// search parameter section
        if ($_REQUEST['supplierID'] != '') {
            $buSupplier = new BUSupplier($this);
            $buSupplier->getSupplierByID($_REQUEST['supplierID'], $dsSupplier);
            $supplierName = $dsSupplier->getValue('name');
        } else {
            $supplierName = '';
        }
        $this->template->set_var(
            array(
                'supplierName' => $supplierName,
                'porheadID' => $_REQUEST['porheadID'],
                'supplierID' => $_REQUEST['supplierID'],
                'lineText' => $_REQUEST['lineText'],
                'submitURL' => $submitURL,
                'urlSupplierPopup' => $urlSupplierPopup
            )
        );
        $this->template->parse('CONTENTS', 'PurchaseInvSearch', true);
        $this->parsePage();
    }

    /**
     * Display the results of order search
     * @access private
     */
    function display()
    {
        $this->setMethodName('display');
        $dsPorhead = &$this->dsPorhead;
        $dsPorline = &$this->dsPorline;
        if ($_REQUEST['porheadID'] == '') {
            $this->displayFatalError(CTPURCHASEINV_MSG_PORHEADID_NOT_PASSED);
            return;
        }
        $this->buPurchaseOrder->getHeaderByID($_REQUEST['porheadID'], $dsPorhead);
        $dsPorhead->fetchNext();
        $this->buPurchaseOrder->getLinesByID($dsPorhead->getValue('porheadID'), $dsPorline);

        // Do we require customer items to be created?
        $buSalesOrder = new BUSalesOrder($this);
        $buSalesOrder->getOrderByOrdheadID($dsPorhead->getValue('ordheadID'), $dsOrdhead, $dsOrdline);
        $addCustomerItems = ($dsOrdhead->getValue('addItem') == 'Y');

        if ($this->dsPorhead->getValue('directDeliveryFlag') == 'Y') {

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
        $_REQUEST['purchaseInvoiceDate'] = date('Y-m-d');

        $porheadID = $dsPorhead->getValue('porheadID');
        $orderType = $dsPorhead->getValue('type');
        $this->setPageTitle("Purchase Invoice Authorisation");
        $this->setTemplateFiles(array('PurchaseInvDisplay' => 'PurchaseInvDisplay.inc'));

        $urlUpdate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTPURCHASEINV_ACT_UPDATE,
                    'porheadID' => $porheadID
                )
            );

        $urlPurchaseOrder =
            $this->buildLink(
                CTCNC_PAGE_PURCHASEORDER,
                array(
                    'action' => CTCNC_ACT_DISPLAY_PO,
                    'porheadID' => $porheadID
                )
            );

        $urlSalesOrder =
            $this->buildLink(
                CTCNC_PAGE_SALESORDER,
                array(
                    'action' => CTCNC_ACT_DISP_SALESORDER,
                    'ordheadID' => $dsPorhead->getValue('ordheadID'),
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

        $this->template->set_var(
            array(
                'porheadID' => $porheadID,
                'supplierName' => Controller::htmlDisplayText($dsPorhead->getValue('supplierName')),
                'vatRate' => $dsPorhead->getValue('vatRate'),
                'purchaseInvoiceDate' => Controller::dateYMDtoDMY(($_REQUEST['purchaseInvoiceDate'])),
                'purchaseInvoiceNo' => Controller::htmlDisplayText($_REQUEST['purchaseInvoiceNo']),
                'urlUpdate' => $urlUpdate,
                'urlPurchaseOrder' => $urlPurchaseOrder,
                'urlSalesOrder' => $urlSalesOrder
            )
        );

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
                        'description' => Controller::htmlDisplayText($this->dsPurchaseInv->getValue("description")),
                        'sequenceNo' => $this->dsPurchaseInv->getValue('sequenceNo'),
                        'orderSequenceNo' => $this->dsPurchaseInv->getValue('orderSequenceNo')
                    )
                );
                $bodyTagExtras = 'onLoad="calculateTotals(); document.forms[0].elements[1].focus();"';
                $this->template->set_var('bodyTagExtras', $bodyTagExtras);
                $this->template->set_var(
                    array(
                        'qtyOrdered' => number_format($this->dsPurchaseInv->getValue("qtyOrdered"), 1, '.', ''),
                        'qtyOS' => number_format($this->dsPurchaseInv->getValue("qtyOS"), 1, '.', ''),
                        'curPOUnitCost' => number_format($this->dsPurchaseInv->getValue("curPOUnitCost"), 2, '.', ''),
                        'curInvUnitCost' => number_format($this->dsPurchaseInv->getValue("curInvUnitCost"), 2, '.', ''),
                        'curInvTotalCost' => number_format($this->dsPurchaseInv->getValue("curInvTotalCost"),
                                                           2,
                                                           '.',
                                                           ''),
                        'itemID' => $this->dsPurchaseInv->getValue("itemID"),
                        'qtyToInvoice' => $this->dsPurchaseInv->getValue("qtyToInvoice"),
                        'partNo' => Controller::htmlDisplayText($this->dsPurchaseInv->getValue("partNo")),
                        'requireSerialNo' => $this->dsPurchaseInv->getValue("requireSerialNo"),
                        'serialNo' => $this->dsPurchaseInv->getValue("serialNo"),
                        'renew' => $this->dsPurchaseInv->getValue("renew"),
                        'warrantyID' => $this->dsPurchaseInv->getValue("warrantyID")
                    )
                );

                /*
                Prevent submit if renewals are not completed
                */
                if ($this->getFormError()) {
                    $this->template->set_var('SUBMIT_DISABLED', 'DISABLED');
                } else {
                    $this->template->set_var('SUBMIT_DISABLED', '');
                }

                if ($this->dsPurchaseInv->getValue('requireSerialNo')) {

                    $this->template->set_var('DISABLED', '');

                    // There is a warranty drop-down for each line
                    $dsWarranty->initialise();
                    $thisWarrantyID = $this->dsPurchaseInv->getValue('warrantyID');
                    while ($dsWarranty->fetchNext()) {
                        $this->template->set_var(
                            array(
                                'warrantyDescription' => $dsWarranty->getValue('description'),
                                'warrantyID' => $dsWarranty->getValue('warrantyID'),
                                'warrantySelected' => ($thisWarrantyID == $dsWarranty->getValue('warrantyID')) ? CT_SELECTED : ''
                            )
                        );
                        $this->template->parse('warranties', 'warrantyBlock', true);
                    } // while ($dsWarranty->fetchNext()
                } else {
                    $this->template->set_var('DISABLED', 'disabled'); // no serial no or warranty
                }

                $this->template->parse('orderLines', 'orderLineBlock', true);
                $this->template->set_var('warranties', ''); // clear for next line
            } // while ($dsPorline->fetchNext())
        }// if ($dsPorline->rowCount() > 0)
        $this->template->parse('CONTENTS', 'PurchaseInvDisplay', true);
        $this->parsePage();
    }

    /**
     * perform updates
     */
    function doUpdate()
    {
        $this->setMethodName('doUpdate');
        $dsPurchaseInv = &$this->dsPurchaseInv;
        $this->buPurchaseInv->initialiseDataset($dsPurchaseInv);
        if (!isset($_REQUEST['porheadID'])) {
            $this->displayFatalError(CTGOODSIN_MSG_PORHEADID_NOT_PASSED);
        }
        if (!$dsPurchaseInv->populateFromArray($_REQUEST['purchaseInv'])) {
            $this->setFormErrorMessage('Values entered must be numeric');
            $this->display();
            exit;
        }

        if (!$this->buPurchaseInv->validateQtys($dsPurchaseInv)) {
            $this->setFormErrorMessage('Quantitites to invoice must not exceed outstanding quantities');
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

        if ($_REQUEST['purchaseInvoiceNo'] == '') {
            $this->setFormErrorMessage('Please enter a purchase invoice number');
            $this->display();
            exit;
        }
        if (!$this->buPurchaseInv->invoiceNoIsUnique($_REQUEST['purchaseInvoiceNo'], $_REQUEST['porheadID'])) {
            $this->setFormErrorMessage('This purchase invoice no has already been used');
            $this->display();
            exit;
        }
        if ($_REQUEST['purchaseInvoiceDate'] == '') {
            $this->setFormErrorMessage('Please enter a purchase invoice date');
            $this->display();
            exit;
        }
        $dateArray = explode('/', $_REQUEST['purchaseInvoiceDate']);
        if (!checkdate($dateArray[1], $dateArray[0], $dateArray[2])) {
            $this->setFormErrorMessage('Please enter a valid purchase invoice date');
            $this->display();
            exit;
        } else {
            $invoiceDateYMD = $dateArray[2] . '-' . $dateArray[1] . '-' . $dateArray[0];
        }
        $this->buPurchaseInv->update(
            $_REQUEST['porheadID'],
            $_REQUEST['purchaseInvoiceNo'],
            $invoiceDateYMD,
            $dsPurchaseInv,
            $this->userID
        );
        $this->buPurchaseOrder->getHeaderByID($_REQUEST['porheadID'], $dsPorhead);
        if ($dsPorhead->getValue('type') == 'A') {
            $urlNext =
                $this->buildLink($_SERVER['PHP_SELF'],
                                 array(
                                     'action' => CTCNC_ACT_DISPLAY_SEARCH_FORM
                                 )
                );
        } else {
            $urlNext =
                $this->buildLink($_SERVER['PHP_SELF'],
                                 array(
                                     'action' => CTCNC_ACT_DISPLAY_GOODS_IN,
                                     'porheadID' => $_REQUEST['porheadID']
                                 )
                );
        }
        header('Location: ' . $urlNext);
        exit;
    }

}// end of class
?>