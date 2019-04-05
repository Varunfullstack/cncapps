<?php
/**
 * Purchase Order controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUPurchaseOrder.inc.php');
require_once($cfg['path_bu'] . '/BUPDFPurchaseOrder.inc.php');
require_once($cfg['path_bu'] . '/BUSupplier.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_gc'] . '/DataSet.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
//require_once($cfg['path_dbe'].'/DBELocation.inc.php');
// Messages
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
    'Must be an initial order'
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
    'delete'
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
    var $dsDateRange = '';
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
        $this->checkPermissions(PHPLIB_PERM_SALES);
        switch ($_REQUEST['action']) {
            case CTCNC_ACT_GENERATE_POS_FROM_SO:
                $this->generateFromSO();
                break;
            case CTCNC_ACT_SEARCH:
                $this->search();
                break;
            case CTPURCHASEORDER_ACT_DISP_SEARCH:
                $this->displaySearchForm();
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
            case CTPURCHASEORDER_ACT_DELETE:
                $this->deleteOrder();
                break;
            case CTPURCHASEORDER_ACT_GENERATE_PDF:
                $this->generatePDF();
                break;
            default:
                $this->displaySearchForm();
                break;
        }
    }

    /**
     * Display form to allow selection of date range for which to produce invoices
     * @access private
     */
    function generateFromSO()
    {
        $this->setMethodName('generateFromSO');
        $requiredByDate = null;
        if (isset($_REQUEST['requiredByDate'])) {
            $requiredByDate = DateTime::createFromFormat(
                'd/m/Y',
                $_REQUEST['requiredByDate']
            );
        }

        $this->buPurchaseOrder->createPOsFromSO(
            $_REQUEST['ordheadID'],
            $this->userID,
            $requiredByDate
        );
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'    => CTCNC_ACT_SEARCH,
                'ordheadID' => $_REQUEST['ordheadID']
            )
        );
        header("Location: " . $urlNext);
        exit;
    }

    /**
     * Creates new PO from passed sales order no and supplierID
     * @access private
     */
    function createPO()
    {
        $this->setMethodName('createPO');
        if (!is_numeric($_REQUEST['supplierID'])) {
            $this->setFormErrorMessage('Supplier No must be numeric');;
        }
        if (!is_numeric($_REQUEST['ordheadID'])) {
            $this->setFormErrorMessage('Sales order no must be numeric');;
        } else {
            $buSalesOrder = new BUSalesOrder($this);
            if (!$buSalesOrder->getOrdheadByID(
                $_REQUEST['ordheadID'],
                $dsOrdhead
            )) {
                $this->setFormErrorMessage('sales order not found');
            } else {
                if ($dsOrdhead->getValue('type') != 'I' & $dsOrdhead->getValue('type') != 'P') {
                    $this->setFormErrorMessage('sales order is not initial or part-despatched status');
                }
            }
        }
        if ($this->getFormError()) {
            $this->displaySearchForm();
            exit;
        }

        $porheadID = $this->buPurchaseOrder->createNewPO(
            $_REQUEST['ordheadID'],
            $_REQUEST['supplierID'],
            $this->userID
        );
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'    => CTCNC_ACT_DISPLAY_PO,
                'porheadID' => $porheadID
            )
        );
        header("Location: " . $urlNext);
        exit;
    }

    /**
     * Run search based upon passed parameters
     * Display search form with results
     * @access private
     */
    function search()
    {
        $this->setMethodName('search');
        // remove trailing spaces from params passed
        foreach ($_REQUEST as $key => $value) {
            $_REQUEST[$key] = trim($value);
        }
        if (
            $_REQUEST['supplierID'] .
            $_REQUEST['porheadID'] .
            $_REQUEST['ordheadID'] .
            $_REQUEST['supplierRef'] .
            $_REQUEST['orderType'] .
            $_REQUEST['lineText'] .
            $_REQUEST['fromDate'] .
            $_REQUEST['toDate'] .
            $_REQUEST['partNo']
            == '') {
            $this->setFormErrorMessage('Please specify at least one parameter');
        }
        if (($_REQUEST['porheadID'] != '') AND (!is_numeric($_REQUEST['porheadID']))) {
            $this->setFormErrorMessage('Order no must be numeric');;
        }
        if (($_REQUEST['ordheadID'] != '') AND (!is_numeric($_REQUEST['ordheadID']))) {
            $this->setFormErrorMessage('Sales order no must be numeric');;
        }
        if ($this->getFormError() == 0) {
            $this->buPurchaseOrder->search(
                $_REQUEST['supplierID'],
                $_REQUEST['porheadID'],
                $_REQUEST['ordheadID'],
                $_REQUEST['supplierRef'],
                $_REQUEST['orderType'],
                $_REQUEST['lineText'],
                $_REQUEST['partNo'],
                common_convertDateDMYToYMD($_REQUEST['fromDate']),
                common_convertDateDMYToYMD($_REQUEST['toDate']),
                $this->dsPorhead
            );
        }
        if ($this->dsPorhead->rowCount() == 1) {
            $this->dsPorhead->fetchNext();
            $urlNext =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => CTCNC_ACT_DISPLAY_PO,
                        'porheadID' => $this->dsPorhead->getValue('porheadID') // if this is set then will show
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
     */
    function displaySearchForm()
    {
        $this->setMethodName('displaySearchForm');
        $this->setTemplateFiles(
            'PurchaseOrderSearch',
            'PurchaseOrderSearch.inc'
        );
// Parameters
        $this->setPageTitle("Purchase Orders");
        $submitURL = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array('action' => CTCNC_ACT_SEARCH)
        );
        $clearURL = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array()
        );
        $urlSupplierPopup =
            Controller::buildLink(
                CTCNC_PAGE_SUPPLIER,
                array(
                    'action'  => CTCNC_ACT_DISP_SUPPLIER_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

        $urlSupplierEdit =
            Controller::buildLink(
                CTCNC_PAGE_SUPPLIER,
                array(
                    'action'  => CTCNC_ACT_SUPPLIER_EDIT,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

        $urlCreateOrder =
            Controller::buildLink(
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
            $orderTypeSelected = ($_REQUEST['orderType'] == $key) ? CT_SELECTED : '';
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
                    'supplierID'  => $_REQUEST['supplierID'],
                    'porheadID'   => $_REQUEST['porheadID'],
                    'ordheadID'   => $_REQUEST['ordheadID'],
                    'supplierRef' => $_REQUEST['supplierRef'],
                    'type'        => $_REQUEST['orderType'],
                    'lineText'    => $_REQUEST['lineText'],
                    'partNo'      => $_REQUEST['partNo']
                )
            );
        if ($this->dsPorhead->rowCount() > 0) {
            $this->template->set_block(
                'PurchaseOrderSearch',
                'orderBlock',
                'orders'
            );
            $supplierNameCol = $this->dsPorhead->columnExists('supplierName');
            $typeCol = $this->dsPorhead->columnExists('type');
            $customerNameCol = $this->dsPorhead->columnExists('customerName');
            $porheadIDCol = $this->dsPorhead->columnExists('porheadID');
            $supplierRefCol = $this->dsPorhead->columnExists('supplierRef');
            $printedCol = $this->dsPorhead->columnExists('printed');
            $ordheadIDCol = $this->dsPorhead->columnExists('ordheadID');
            while ($this->dsPorhead->fetchNext()) {
                $orderURL =
                    Controller::buildLink(
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
        if ($_REQUEST['supplierID'] != '') {
            $buSupplier = new BUSupplier($this);
            $buSupplier->getSupplierByID(
                $_REQUEST['supplierID'],
                $dsSupplier
            );
            $supplierName = $dsSupplier->getValue('name');
        } else {
            $supplierName = '';
        }

        $this->template->set_var(
            array(
                'supplierName'     => $supplierName,
                'porheadID'        => $_REQUEST['porheadID'],
                'ordheadID'        => $_REQUEST['ordheadID'],
                'supplierID'       => $_REQUEST['supplierID'],
                'orderType'        => $_REQUEST['orderType'],
                'lineText'         => Controller::htmlDisplayText($_REQUEST['lineText']),
                'partNo'           => $_REQUEST['partNo'],
                'fromDate'         => $_REQUEST['fromDate'],
                'toDate'           => $_REQUEST['toDate'],
                'supplierRef'      => $_REQUEST['supplierRef'],
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

    /**
     * Display the results of order search
     * @access private
     */
    function displayOrder()
    {
        $this->setMethodName('displayOrder');
        $dsPorhead = &$this->dsPorhead;
        $dsPorline = &$this->dsPorline;
        if (!$this->formError) {
            if ($_REQUEST['porheadID'] == '') {
                $this->displayFatalError(CTPURCHASEORDER_MSG_PURCHASEORDERID_NOT_PASSED);
                return;
            }
            $this->buPurchaseOrder->getOrderByID(
                $_REQUEST['porheadID'],
                $dsPorhead,
                $dsPorline
            );
            $dsPorhead->fetchNext();
        } else {    // if we are redisplaying header then only need lines
            $dsPorhead->initialise();
            $dsPorhead->fetchNext();
            $this->buPurchaseOrder->getLinesByID(
                $dsPorhead->getValue('porheadID'),
                $dsPorline
            );
        }
        $porheadID = $dsPorhead->getValue('porheadID');
        $orderType = $dsPorhead->getValue('type');
        $disabled = CTCNC_HTML_DISABLED;                            // default - no editing
        switch ($orderType) {
            case 'I':
                $title = 'Purchase Order - Initial';
                $disabled = ''; // only initial orders may be edited
                $urlUpdateHeader =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'    => CTPURCHASEORDER_ACT_UPDATE_ORDHEAD,
                            'porheadID' => $porheadID
                        )
                    );
                $urlContactPopup =
                    Controller::buildLink(
                        CTCNC_PAGE_CONTACT,
                        array(
                            'action'     => CTCNC_ACT_CONTACT_POPUP,
                            'supplierID' => $dsPorhead->getValue('supplierID'),
                            'htmlFmt'    => CT_HTML_FMT_POPUP
                        )
                    );
                $urlContactEdit =
                    Controller::buildLink(
                        CTCNC_PAGE_CONTACT,
                        array(
                            'action'  => CTCNC_ACT_CONTACT_EDIT,
                            'htmlFmt' => CT_HTML_FMT_POPUP
                        )
                    );
                $urlSupplierPopup =
                    Controller::buildLink(
                        CTCNC_PAGE_SUPPLIER,
                        array(
                            'action'  => CTCNC_ACT_DISP_SUPPLIER_POPUP,
                            'htmlFmt' => CT_HTML_FMT_POPUP
                        )
                    );
                $urlSupplierEdit =
                    Controller::buildLink(
                        CTCNC_PAGE_SUPPLIER,
                        array(
                            'action'  => CTCNC_ACT_SUPPLIER_EDIT,
                            'htmlFmt' => CT_HTML_FMT_POPUP
                        )
                    );
                $urlDeleteOrder =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'    => CTPURCHASEORDER_ACT_DELETE,
                            'porheadID' => $porheadID
                        )
                    );
                $txtDeleteOrder = 'Delete';
                break;
            case 'P':
                $title = 'Purchase Order - Part Receieved';
                $disabled = ''; // only initial orders may be edited
                $urlUpdateHeader =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'    => CTPURCHASEORDER_ACT_UPDATE_ORDHEAD,
                            'porheadID' => $porheadID
                        )
                    );
                $urlContactPopup =
                    Controller::buildLink(
                        CTCNC_PAGE_CONTACT,
                        array(
                            'action'     => CTCNC_ACT_CONTACT_POPUP,
                            'supplierID' => $dsPorhead->getValue('supplierID'),
                            'htmlFmt'    => CT_HTML_FMT_POPUP
                        )
                    );
                $urlContactEdit =
                    Controller::buildLink(
                        CTCNC_PAGE_CONTACT,
                        array(
                            'action'  => CTCNC_ACT_CONTACT_EDIT,
                            'htmlFmt' => CT_HTML_FMT_POPUP
                        )
                    );
                $urlSupplierPopup =
                    Controller::buildLink(
                        CTCNC_PAGE_SUPPLIER,
                        array(
                            'action'  => CTCNC_ACT_DISP_SUPPLIER_POPUP,
                            'htmlFmt' => CT_HTML_FMT_POPUP
                        )
                    );
                $urlSupplierEdit =
                    Controller::buildLink(
                        CTCNC_PAGE_SUPPLIER,
                        array(
                            'action'  => CTCNC_ACT_SUPPLIER_EDIT,
                            'htmlFmt' => CT_HTML_FMT_POPUP
                        )
                    );
                $urlDeleteOrder =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'    => CTPURCHASEORDER_ACT_DELETE,
                            'porheadID' => $porheadID
                        )
                    );
                $txtDeleteOrder = 'Delete';
                break;
            case 'A':
                $title = 'Purchase Order - Authorised';
                break;
            case 'C':
                $title = 'Purchase Order - Completed (not authorised)';
                break;
        }// end switch

        if (
            (($orderType == 'I') or ($orderType == 'P')) &
            ($dsPorhead->getValue('directDeliveryFlag') == 'N')
        ) {
            $urlGoodsIn =
                Controller::buildLink(
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
                'AddFirstLineIcon'         => 'AddFirstLineIcon.inc'
            )
        );
        // if there is a sales order then display the delivery details etc
        $this->template->set_var(
            'delAdd1',
            ''
        ); // default
        $dbeOrdhead = new DBEJOrdhead($this);
        $dbeOrdhead->setValue(
            'ordheadID',
            $dsPorhead->getValue('ordheadID')
        );
        if ($dbeOrdhead->getRow()) {
            $urlSalesOrder =
                Controller::buildLink(
                    CTCNC_PAGE_SALESORDER,
                    array(
                        'action'    => CTCNC_ACT_DISP_SALESORDER,
                        'ordheadID' => $dsPorhead->getValue('ordheadID')
                    )
                );
            $txtSalesOrder = 'Sales Order';
            if ($dsPorhead->getValue('directDeliveryFlag') == 'Y') {
                $this->template->set_var(
                    array(
                        'customerName' => Controller::htmlDisplayText($dbeOrdhead->getValue('customerName')),
                        'delAdd1'      => Controller::htmlDisplayText($dbeOrdhead->getValue('delAdd1')),
                        'delAdd2'      => Controller::htmlDisplayText($dbeOrdhead->getValue('delAdd2')),
                        'delAdd3'      => Controller::htmlDisplayText($dbeOrdhead->getValue('delAdd3')),
                        'delTown'      => Controller::htmlDisplayText($dbeOrdhead->getValue('delTown')),
                        'delCounty'    => Controller::htmlDisplayText($dbeOrdhead->getValue('delCounty')),
                        'delPostcode'  => Controller::htmlDisplayText($dbeOrdhead->getValue('delPostcode'))
                    )
                );
            }
        }
        // get sales order delivery contact
        $dbeContact = new DBEContact($this);
        $dbeContact->getRow($dbeOrdhead->getValue('delContactID'));

        // if there are lines then allow print of purchase order
        if ($dsPorline->rowCount() > 0) {
            $urlGeneratePDF =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => CTPURCHASEORDER_ACT_GENERATE_PDF,
                        'porheadID' => $porheadID
                    )
                );
            $txtGeneratePDF = 'Print';
        }
        $ordheadID = ($dsPorhead->getValue('ordheadID') != 0 ? $dsPorhead->getValue('ordheadID') : '');

        // If supplier has a web site then display link
        if ($dsPorhead->getValue("webSiteURL") != '') {
            $supplierLink = '<A HREF="' . $dsPorhead->getValue('webSiteURL') . '" target="_blank">Web Site</A>';
        } else {
            $supplierLink = '';
        }
        // If contact has an email address then display email link
        if ($dsPorhead->getValue("contactEmail") != '') {
            $emailLink =
                '<A HREF="mailto:' . $dsPorhead->getValue('contactEmail') . '"' .
                ' title="Send email to contact"><img src="images/email.gif" border="0"></A>';
        } else {
            $emailLink = '';
        }

        $this->template->set_var(
            array(
                'supplierID'                   => $dsPorhead->getValue('supplierID'),
                'type'                         => $dsPorhead->getValue('type'),
                'porheadID'                    => $porheadID,
                'userID'                       => $dsPorhead->getValue('userID'),
                'orderUserID'                  => $dsPorhead->getValue('orderUserID'),
                'contactID'                    => $dsPorhead->getValue('contactID'),
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
                'orderRequiredBy'              => Controller::dateYMDtoDMY(
                    $dsPorhead->getValue(DBEPorhead::requiredBy)
                ),
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
        $buSupplier = new BUSupplier($this);
        $buSupplier->getAllPayMethods($dsPayMethod);
        $this->template->set_block(
            'PurchaseOrderHeadDisplay',
            'payMethodBlock',
            'payMethods'
        );
        while ($dsPayMethod->fetchNext()) {
            $this->template->set_var(
                array(
                    'payMethodDescription' => $dsPayMethod->getValue(DBEPayMethod::description),
                    'payMethodID'          => $dsPayMethod->getValue(DBEPayMethod::payMethodID),
                    'payMethodSelected'    => ($dsPorhead->getValue(DBEJPorhead::payMethodID) == $dsPayMethod->getValue(
                            DBEPayMethod::payMethodID
                        )) ? CT_SELECTED : ''
                )
            );
            $this->template->parse(
                'payMethods',
                'payMethodBlock',
                true
            );
        }
        if ($dsPorline->rowCount() == 0) {                // no lines yet so need way of adding first
            $urlAddLine =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => CTPURCHASEORDER_ACT_ADD_ORDLINE,
                        'porheadID'  => $porheadID,
                        'sequenceNo' => (1)
                    )
                );
            $this->template->set_var(
                'urlAddLine',
                $urlAddLine
            );
            $this->template->parse(
                'salesOrderLineIcons',
                'AddFirstLineIcon',
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
                $sequenceNo = $dsPorline->getValue(DBEJPorline::sequenceNo);
                $itemDescription = $dsPorline->getValue(DBEJPorline::itemDescription);
                if ($dsPorline->getValue(DBEPorline::expectedDate)) {
                    $expectedDate = Controller::dateYMDtoDMY($dsPorline->getValue(DBEJPorline::expectedDate));
                } else {
                    $expectedDate = null;
                }
                $curTotalCost = $dsPorline->getValue(DBEJPorline::curUnitCost) * $dsPorline->getValue(
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
                        'expectedDate' => $expectedDate
                    )
                );
                if ($disabled != CTCNC_HTML_DISABLED) {        // enabled so allow/show editing options
                    $urlEditLine =
                        Controller::buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'     => CTPURCHASEORDER_ACT_EDIT_ORDLINE,
                                'porheadID'  => $porheadID,
                                'sequenceNo' => $sequenceNo
                            )
                        );
                    // common to comment and item lines
                    $urlAddLine =
                        Controller::buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'     => CTPURCHASEORDER_ACT_ADD_ORDLINE,
                                'porheadID'  => $porheadID,
                                'sequenceNo' => ($sequenceNo + 1)    // new line below current
                            )
                        );
                    $urlMoveLineUp =
                        Controller::buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'     => CTPURCHASEORDER_ACT_MOVE_ORDLINE_UP,
                                'porheadID'  => $porheadID,
                                'sequenceNo' => $sequenceNo
                            )
                        );
                    $urlMoveLineDown =
                        Controller::buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'     => CTPURCHASEORDER_ACT_MOVE_ORDLINE_DOWN,
                                'porheadID'  => $porheadID,
                                'sequenceNo' => $sequenceNo
                            )
                        );
                    $urlDeleteLine =
                        Controller::buildLink(
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
                    $lineDescription =
                        '<A href="' . $urlEditLine . '">' . Controller::htmlDisplayText($itemDescription) . '</A>';
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
                    ''
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
     * Edit/Add Order Line
     * @access private
     */
    function editOrderLine()
    {
        $this->setMethodName('editOrderLine');
        $this->setPageTitle('Purchase Order - Edit Line');
        if ($_REQUEST['porheadID'] == '') {
            $this->displayFatalError(CTPURCHASEORDER_MSG_PORHEADID_NOT_PASSED);
            return;
        }
        if (!$this->buPurchaseOrder->getOrderHeaderByID(
            $_REQUEST['porheadID'],
            $this->dsPorhead
        )) {
            $this->displayFatalError(CTPURCHASEORDER_MSG_PURCHASEORDER_NOT_FND);
            return;
        }
        if ($this->dsPorhead->getValue('type') != 'I') {
            $this->displayFatalError(CTPURCHASEORDER_MSG_MUST_BE_INITIAL);
            return;
        }
        if ($_REQUEST['sequenceNo'] == '') {
            $this->displayFatalError(CTPURCHASEORDER_MSG_SEQNO_NOT_PASSED);
            return;
        }
        if (!$this->formError) {
            if ($_REQUEST['action'] == CTPURCHASEORDER_ACT_EDIT_ORDLINE) {
                if (!$this->buPurchaseOrder->getOrdlineByIDSeqNo(
                    $_REQUEST['porheadID'],
                    $_REQUEST['sequenceNo'],
                    $this->dsPorline
                )) {
                    $this->displayFatalError(CTPURCHASEORDER_MSG_ORDLINE_NOT_FND);
                    return;
                }
            } else {
                $this->buPurchaseOrder->initialiseNewOrdline(
                    $_REQUEST['porheadID'],
                    $_REQUEST['sequenceNo'],
                    $this->dsPorline
                );
            }
        }
        $this->setTemplateFiles(
            array(
//				'PurchaseOrderHeadDisplay' =>  'PurchaseOrderHeadDisplay.inc',
'PurchaseOrderLineEdit'   => 'PurchaseOrderLineEdit.inc',
'PurchaseOrderLineEditJS' => 'PurchaseOrderLineEditJS.inc' // javascript
            )
        );
//		$this->displayPurchaseOrderHeader($dsPorhead);
        $this->orderLineForm();
        $this->template->parse(
            'purchaseOrderLineEditJS',
            'PurchaseOrderLineEditJS',
            true
        );
//		$this->template->parse('purchaseOrderHeadDisplay', 	'purchaseOrderHeadDisplay', true);
        $this->template->parse(
            'CONTENTS',
            'PurchaseOrderLineEdit',
            true
        );
        $this->parsePage();
    }

    function orderLineForm($parentPage = 'PurchaseOrderLineEdit')
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
                'expectedDate'           => Controller::dateYMDtoDMY(
                    $this->dsPorline->getValue(DBEJPorline::expectedDate)
                ),
                'expectedDateMessage'    => $this->dsPorline->getMessage(DBEJPorline::expectedDate)
            )
        );
        if ($_REQUEST['action'] == CTPURCHASEORDER_ACT_EDIT_ORDLINE) {
            $urlSubmit =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTPURCHASEORDER_ACT_UPDATE_ORDLINE
                    )
                );
        } else {
            $urlSubmit =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTPURCHASEORDER_ACT_INSERT_ORDLINE
                    )
                );
        }
        $urlCancel =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'porheadID' => $this->dsPorhead->getValue(DBEJPorhead::porheadID),
                    'action'    => CTCNC_ACT_DISPLAY_PO
                )
            );
        $urlItemPopup =
            Controller::buildLink(
                CTCNC_PAGE_ITEM,
                array(
                    'action'  => CTCNC_ACT_DISP_ITEM_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $urlItemEdit =
            Controller::buildLink(
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
    }// end function orderLineForm()

    /**
     * Update/Insert order line
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function updateOrderLine()
    {
        $this->setMethodName('updateOrderLine');
        $this->formError = !$this->dsPorline->populateFromArray($_REQUEST['porline']);
        if ($this->formError) {                    // Form error so redisplay edit form
            if ($_REQUEST['action'] == CTPURCHASEORDER_ACT_INSERT_ORDLINE) {
                $_REQUEST['action'] = CTPURCHASEORDER_ACT_ADD_ORDLINE;
            } else {
                $_REQUEST['action'] = CTPURCHASEORDER_ACT_EDIT_ORDLINE;
            }
            $_REQUEST['porheadID'] = $this->dsPorline->getValue('porheadID');
            $_REQUEST['sequenceNo'] = $this->dsPorline->getValue('sequenceNo');
            $this->editOrderLine();
            exit;
        }
        if ($_REQUEST['action'] == CTPURCHASEORDER_ACT_INSERT_ORDLINE) {
            $this->buPurchaseOrder->insertNewOrderLine($this->dsPorline);
        } else {
            $this->buPurchaseOrder->updateOrderLine(
                $this->dsPorline,
                'U'
            );
        }
        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'porheadID' => $this->dsPorline->getValue('porheadID'),
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
     */
    function moveOrderLineUp()
    {
        $this->setMethodName('moveOrderLineUp');
        $this->buPurchaseOrder->moveOrderLineUp(
            $_REQUEST['porheadID'],
            $_REQUEST['sequenceNo']
        );
        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'porheadID' => $_REQUEST['porheadID'],
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
     */
    function moveOrderLineDown()
    {
        $this->setMethodName('moveOrderLineDown');
        $this->buPurchaseOrder->moveOrderLineDown(
            $_REQUEST['porheadID'],
            $_REQUEST['sequenceNo']
        );
        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'porheadID' => $_REQUEST['porheadID'],
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
     */
    function deleteOrderLine()
    {
        $this->setMethodName('deleteOrderLine');
        $this->buPurchaseOrder->deleteOrderLine(
            $_REQUEST['porheadID'],
            $_REQUEST['sequenceNo']
        );
        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'porheadID' => $_REQUEST['porheadID'],
                    'action'    => CTCNC_ACT_DISPLAY_PO
                )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Delete order
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function deleteOrder()
    {
        $this->setMethodName('deleteOrder');
        if ($_REQUEST['porheadID'] == '') {
            $this->displayFatalError(CTPURCHASEORDER_MSG_PORHEADID_NOT_PASSED);
            return;
        }
        if (!$this->buPurchaseOrder->getOrderHeaderByID(
            $_REQUEST['porheadID'],
            $this->dsPorhead
        )) {
            $this->displayFatalError(CTPURCHASEORDER_MSG_PURCHASEORDER_NOT_FND);
            return;
        }
        $this->buPurchaseOrder->deleteOrder($_REQUEST['porheadID']);

        $urlNext =                        // default action
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => CTCNC_ACT_SEARCH,
                    'ordheadID' => $this->dsPorhead->getValue('ordheadID') // if this is set then will show
                )                                                                                                                    // remaining POs for SO
            );
        if ($this->dsPorhead->getValue('ordheadID') <> '') {
            $buSalesOrder = new BUSalesOrder($this);
            $purchaseOrderCount = $buSalesOrder->countPurchaseOrders($this->dsPorhead->getValue('ordheadID'));
            if ($purchaseOrderCount == 0) {
                $urlNext =
                    Controller::buildLink(
                        CTCNC_PAGE_SALESORDER,
                        array(
                            'action'    => CTCNC_ACT_DISP_SALESORDER,
                            'ordheadID' => $this->dsPorhead->getValue('ordheadID') // if this is set then will show
                        )                                                                                                                    // remaining POs for SO
                    );
            }
        }
        header('Location: ' . $urlNext);
    }

    /**
     * Update order header details
     * @access private
     */
    function updateHeader()
    {
        $this->setMethodName('updateHeader');

        $dsPorhead = &$this->dsPorhead;
        $this->formError = (!$dsPorhead->populateFromArray($_REQUEST['porhead']));
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

            if ($dbePurchaseOrder->getValue(DBEPorhead::deliveryConfirmedFlag) == 'N' && $dsPorhead->getValue(
                    DBEJPorhead::deliveryConfirmedFlag
                ) == 'Y') {
                $buSalesOrder = new BUSalesOrder($this);

                $buSalesOrder->notifyPurchaseOrderCompletion($dbePurchaseOrder);

                $dsPorhead->setValue(
                    DBEJPorhead::completionNotifiedFlag,
                    $dbePurchaseOrder->getValue(DBEPorhead::deliveryConfirmedFlag)
                );

            }
            $this->buPurchaseOrder->updateHeader($dsPorhead);

            $urlNext =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'porheadID' => $_REQUEST['porheadID'],
                        'action'    => CTCNC_ACT_DISPLAY_PO
                    )
                );
            header('Location: ' . $urlNext);
        }
    }

    function generatePDF()
    {
        // generate PDF purchase order:
        $buPDFPurchaseOrder = new BUPDFPurchaseOrder(
            $this,
            $this->buPurchaseOrder,
            $_REQUEST['porheadID']
        );
        $fileName = 'P0' . $_REQUEST['porheadID'];
        if ($pdfFile = $buPDFPurchaseOrder->generateFile()) {
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename=' . $fileName . '.pdf;');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($pdfFile));
            readfile($pdfFile);
            unlink($pdfFile);
            $this->buPurchaseOrder->setOrderedFields(
                $_REQUEST['porheadID'],
                $this->userID
            );
            exit();
        }
    }

    function parsePage()
    {
        if ($_REQUEST['action'] == CTPURCHASEORDER_ACT_DISP_SEARCH) {
            $urlLogo = '';
        } else {
            $urlLogo =
                Controller::buildLink(
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
}// end of class
?>