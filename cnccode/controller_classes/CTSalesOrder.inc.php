<?php
/**
 * Sales Order controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUProject.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerNote.inc.php');
require_once($cfg['path_bu'] . '/BUSalesOrder.inc.php');
require_once($cfg['path_bu'] . '/BUSalesOrderDocument.inc.php');
require_once($cfg['path_bu'] . '/BUDespatch.inc.php');
require_once($cfg['path_bu'] . '/BUInvoice.inc.php');
require_once($cfg['path_bu'] . '/BUItem.inc.php');
require_once($cfg['path_bu'] . '/BURenewal.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_ct'] . '/CTDeliveryNotes.inc.php');
require_once($cfg["path_bu"] . "/BUPDFSalesQuote.inc.php");
require_once($cfg["path_bu"] . "/BUPDF.inc.php");
require_once($cfg["path_dbe"] . "/DBEPaymentTerms.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomerItem.inc.php");
require_once($cfg["path_dbe"] . "/DBEStandardText.inc.php");
require_once($cfg["path_dbe"] . "/DBEQuotationTemplate.inc.php");
require_once($cfg["path_dbe"] . "/DBESignableEnvelope.inc.php");
require_once($cfg["path_func"] . "/Common.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");
require_once($cfg['path_dbe'] . '/DBESupplier.inc.php');
// Parameters
define(
    'CTSALESORDER_VAL_NONE_SELECTED',
    -1
);
// Actions
define(
    'CTSALESORDER_ACT_DISP_SEARCH',
    'dispSearch'
);
define(
    'CTSALESORDER_ACT_SEARCH',
    'search'
);
define(
    'CTSALESORDER_ACT_DELETE_ORDER',
    'deleteOrder'
);
define(
    'CTSALESORDER_ACT_ORDER_LINES_SUBMIT',
    'orderLinesSubmit'
);
define(
    'CTSALESORDER_ACT_UPLOAD_QUOTE_DOC',
    'uploadQuoteDoc'
);
define(
    'CTSALESORDER_ACT_SEND_QUOTE_DOC',
    'sendQuoteDoc'
);
define(
    'CTSALESORDER_ACT_DELETE_QUOTE_DOC',
    'deleteQuoteDoc'
);
define(
    'CTSALESORDER_ACT_DISPLAY_QUOTE_DOC',
    'displayQuoteDoc'
);
define(
    'CTSALESORDER_ACT_CREATE_QUOTE',
    'createQuote'
);
define(
    'CTSALESORDER_ACT_CREATE_TEMPLATED_QUOTE',
    'createTemplatedQuote'
);
define(
    'CTSALESORDER_ACT_CREATE_ORDER',
    'createOrder'
);
define(
    'CTSALESORDER_ACT_COPY_TO_ORDER',
    'copyToOrder'
);        // bulk copy to initial order using selector
define(
    'CTSALESORDER_ACT_CONVERT_TO_ORDER',
    'convertToOrder'
);  // bulk convert to initial order using selector
define(
    'CTSALESORDER_ACT_DELETE_LINES',
    'deleteLines'
);         // bulk delete of lines using selector
define(
    'CTSALESORDER_ACT_CREATE_SR_FROM_LINES',
    'createSrFromLines'
);
define(
    'CTSALESORDER_ACT_CREATE_MANUAL_ORDER_FORM',
    'genManualOrderForm'
);
define(
    'CTSALESORDER_ACT_CHANGE_SUPPLIER',
    'changeSupplier'
);
define(
    'CTSALESORDER_ACT_UPDATE_LINES',
    'updateLines'
);                    // bulk update of lines
define(
    'CTSALESORDER_ACT_INSERT_FROM_ORDER',
    'insertFromOrder'
);// insert lines from another order
define(
    'CTSALESORDER_ACT_ADD_ORDLINE',
    'addOrdline'
);
define(
    'CTSALESORDER_ACT_EDIT_ORDLINE',
    'editOrdline'
);
define(
    'CTSALESORDER_ACT_UPDATE_ORDLINE',
    'updtOrdline'
);
define(
    'CTSALESORDER_ACT_INSERT_ORDLINE',
    'insrtOrdline'
);
define(
    'CTSALESORDER_ACT_MOVE_ORDLINE_UP',
    'moveUpOrdline'
);
define(
    'CTSALESORDER_ACT_MOVE_ORDLINE_DOWN',
    'moveDownOrdline'
);
define(
    'CTSALESORDER_ACT_DELETE_ORDLINE',
    'delOrdline'
);
define(
    'CTSALESORDER_ACT_UPDATE_DEL_ADDRESS',
    'updDelAdd'
);
define(
    'CTSALESORDER_ACT_UPDATE_INV_ADDRESS',
    'updInvAdd'
);
define(
    'CTSALESORDER_ACT_UPDATE_DEL_CONTACT',
    'updDelCon'
);
define(
    'CTSALESORDER_ACT_UPDATE_INV_CONTACT',
    'updInvCon'
);
define(
    'CTSALESORDER_ACT_UPDATE_HEADER',
    'updateHead'
);
define(
    'CTSALESORDER_ACT_SEND_CONFIRMATION',
    'sendOrderConfirmation'
);
// Messages
define(
    'CTSALESORDER_MSG_CUSTTRING_REQ',
    'Please enter customer to search for'
);
define(
    'CTSALESORDER_MSG_NONE_FND',
    'No customers found'
);
define(
    'CTSALESORDER_MSG_CUSTOMERID_NOT_PASSED',
    'customerID not passed'
);
define(
    'CTSALESORDER_MSG_CUS_NOT_FND',
    'Customer not found'
);
define(
    'CTSALESORDER_MSG_SELECT_USER',
    'User?'
);
define(
    'CTSALESORDER_MSG_SELECT_SALUTATION',
    'Salutation?'
);
define(
    'CTSALESORDER_MSG_SELECT_INTRODUCTION',
    'Introduction?'
);
define(
    'CTSALESORDER_MSG_NO_LINES',
    'Select lines to include'
);
define(
    'CTSALESORDER_MSG_USER_NOT_FND',
    'User not found'
);
define(
    'CTSALESORDER_MSG_PROBLEM_SENDING_QUOTE',
    'Quote could not be sent'
);
define(
    'CTSALESORDER_MSG_QUOTEID_NOT_PASSED',
    'quotationID not passed'
);
define(
    'CTSALESORDER_MSG_CONTACTID_NOT_PASSED',
    'contactID not passed'
);
define(
    'CTSALESORDER_MSG_QUOTE_NOT_FOUND',
    'Quote not found'
);
define(
    'CTSALESORDER_MSG_MUST_BE_QUOTE_OR_INITIAL',
    'Not Quote or Initial Order'
);
define(
    'CTSALESORDER_MSG_ORDHEADID_NOT_PASSED',
    'ordheadID not passed'
);
define(
    'CTSALESORDER_MSG_ORDER_NOT_FND',
    'Order not found'
);
define(
    'CTSALESORDER_MSG_ORDLINE_NOT_FND',
    'Order Line not found'
);
define(
    'CTSALESORDER_MSG_SEQNO_NOT_PASSED',
    'sequenceNo not passed'
);
define(
    'CTSALESORDER_NOT_NUMERIC',
    'Must be a number'
);
define(
    'CTSALESORDER_TO_DATE_SMALLER',
    'End date must be greater than start date'
);
define(
    'CTSALESORDER_CLS_FORM_ERROR',
    'formError'
);
define(
    'CTSALESORDER_CLS_ORDER_LINE_ITEM',
    'orderLineItem'
);
//define('CTSALESORDER_CLS_ORDER_LINE_COMMENT', 'orderLineComment');
define(
    'CTSALESORDER_CLS_ORDER_LINE_LOSS',
    'orderLineLoss'
);
define(
    'CTSALESORDER_CLS_ORDER_TOTAL_ITEM',
    'orderTotalItem'
);
define(
    'CTSALESORDER_CLS_ORDER_TOTAL_LOSS',
    'orderTotalLoss'
);
define(
    'CTSALESORDER_NOTEPAD_ITEM',
    'IT'
);
define(
    'CTSALESORDER_TXT_INTRODUCTION',
    'With reference to your recent enquiry, I have great pleasure in providing you with the following prices:'
);
//define('CTSALESORDER_TXT_EMAIL_SUBJECT_START', 'Your Quotation Ref:');
define(
    'CTSALESORDER_TXT_SEND',
    'Send'
);
define(
    'CTSALESORDER_TXT_DELETE',
    'Delete'
);


class CTSalesOrder extends CTCNC
{
    const etaDate = 'etaDate';
    const curUnitSaleMessage = 'curUnitSaleMessage';
    const curUnitCostMessage = 'curUnitCostMessage';
    const qtyOrderedMessage = 'qtyOrderedMessage';
    const supplierNameMessage = 'supplierNameMessage';
    const descriptionMessage = 'descriptionMessage';
    const supplierName = 'supplierName';
    const MOVE_LINE_TO_POSITION = "MOVE_LINE_TO_POSITION";
    const UPDATE_LINE_VALUES = "UPDATE_LINE_VALUES";
    const UPDATE_ITEM_PRICE = "UPDATE_ITEM_PRICE";
    const CREATE_SIGNABLE_QUOTE = "CREATE_SIGNABLE_QUOTE";
    /** @var */
    public $customerID;
    /** @var */
    public $customerString;
    /** @var */
    public $buCustomer;
    /** @var */
    public $dsQuotation;
    /** @var */
    public $buItem;
    /** @var BUSalesOrder */
    public $buSalesOrder;
    /** @var */
    public $customerStringMessage;
    /** @var */
    public $quoteFileMessage;
    /** @var */
    public $userMessage;
    /** @var */
    public $updateSupplierID;
    /** @var */
    public $fromOrdheadID;
    /** @var */
    public $linesMessage;
    /** @var */
    public $uploadUserMessage;
    /** @var */
    public $ordheadIDMessage;
    /** @var */
    public $fromDateMessage;
    /** @var */
    public $toDateMessage;
    /** @var */
    public $serviceRequestCustomerItemID;
    /** @var */
    public $serviceRequestText;
    /** @var DBEOrdline */
    public $dsOrdline;
    /** @var */
    public $dsCustomer;
    /** @var */
    public $dsContact;
    /** @var */
    public $dsSite;
    /** @var */
    public $siteNo;
    /** @var */
    public $sequenceNo;
    /** @var */
    public $dsOrdhead;
    /** @var */
    public $ordheadID;
    /** @var */
    public $quotationID;
    /** @var */
    public $emailSubject;
    /** @var */
    public $orderType;
    /** @var */
    public $custPORef;
    /** @var */
    public $lineText;
    /** @var */
    public $fromDate;
    /** @var */
    public $toDate;
    /** @var */
    public $salutation;
    /**
     * @var DBEUser
     */
    public $dsUser;
    /** @var */
    public $introduction;
    /** @var */
    public $dsSelectedOrderLine;
    /** @var */
    public $contactID;
    /** @var */
    public $quotationUserID;
    /** @var */
    public $urlCallback;
    public $orderTypeArray = array(
        "I" => "Initial",
        "Q" => "Quotation",
        "P" => "Part Despatched",
        "C" => "Completed",
        "B" => "Both Initial & Part Despatched"
    );
    public $lineValidationError = null;

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
        $this->setMenuId(301);
        $this->buCustomer = new BUCustomer($this);
        $this->buSalesOrder = new BUSalesOrder($this);
        $this->buItem = new BUItem($this);
        $this->dsOrdhead = new Dataset($this);
        $this->dsSelectedOrderLine = new Dataset($this);
        $this->dsSelectedOrderLine->addColumn(
            DBEOrdline::id,
            DA_ID,
            DA_NOT_NULL
        );
        $this->dsSelectedOrderLine->addColumn(
            DBEOrdline::sequenceNo,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $this->dsSelectedOrderLine->addColumn(
            DBEOrdline::qtyOrdered,
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->dsSelectedOrderLine->addColumn(
            DBEOrdline::curUnitCost,
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->dsSelectedOrderLine->addColumn(
            DBEOrdline::curUnitSale,
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->dsQuotation = new DataSet($this);
        $this->dsQuotation->copyColumnsFrom($this->buSalesOrder->dbeQuotation);
    }

    function initialProcesses()
    {
        $this->retrieveHTMLVars();
        parent::initialProcesses();
    }

    // these dummies are needed because every HTML variable passed must have a handler
    function setUrlCallback($dummy)
    {
    }

    function setCNCCode($dummy)
    {
    }

    function setOrdhead($dummy)
    {
    }

    function getServiceRequestCustomerItemID()
    {
        return $this->serviceRequestCustomerItemID;
    }

    function setServiceRequestCustomerItemID($value)
    {
        $this->serviceRequestCustomerItemID = $value;
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        parent::defaultAction();
        switch ($this->getAction()) {
            case CTSALESORDER_ACT_SEARCH:
                $this->search();
                break;
            case CTCNC_ACT_DISP_SALESORDER:
            case CTSALESORDER_ACT_CREATE_QUOTE:
            case CTSALESORDER_ACT_CREATE_ORDER:
                $this->displayOrder();
                break;
            case CTSALESORDER_ACT_SEND_QUOTE_DOC:
                $this->checkPermissions(SALES_PERMISSION);
                $this->sendQuoteDoc();
                break;
            case CTSALESORDER_ACT_DELETE_QUOTE_DOC:
                $this->checkPermissions(SALES_PERMISSION);
                $this->deleteQuoteDoc();
                break;
            case self::UPDATE_LINE_VALUES:
                $data = json_decode(file_get_contents('php://input'), true);

                if (!array_key_exists('lineId', $data)) {
                    throw new \CNCLTD\Exceptions\JsonHttpException(400, "Line id is required");
                }

                if (!array_key_exists('unitSale', $data)) {
                    throw new \CNCLTD\Exceptions\JsonHttpException(400, 'unitSale is required');
                }
                if (!array_key_exists('quantity', $data)) {
                    throw new \CNCLTD\Exceptions\JsonHttpException(400, 'quantity is required');
                }
                if (!array_key_exists('unitCost', $data)) {
                    throw new \CNCLTD\Exceptions\JsonHttpException(400, 'unitCost is required');
                }
                $dbeOrdline = new DBEOrdline($this);
                $dbeOrdline->getRow($data['lineId']);
                $dbeOrdline->setValue(DBEOrdline::curUnitCost, $data['unitCost']);
                $dbeOrdline->setValue(DBEOrdline::curTotalCost, $data['quantity'] * $data['unitCost']);
                $dbeOrdline->setValue(DBEOrdline::curUnitSale, $data['unitSale']);
                $dbeOrdline->setValue(DBEOrdline::curTotalSale, $data['quantity'] * $data['unitSale']);
                $dbeOrdline->setValue(DBEOrdline::qtyOrdered, $data['quantity']);
                $dbeOrdline->updateRow();
                echo json_encode(["status" => "ok"]);
                break;
            case CTSALESORDER_ACT_UPDATE_LINES:
                $this->checkPermissions(SALES_PERMISSION);
                $this->updateLines();
                break;
            case CTSALESORDER_ACT_COPY_TO_ORDER:
            case CTSALESORDER_ACT_CONVERT_TO_ORDER:
                $this->checkPermissions(SALES_PERMISSION);
                $this->convertToOrder();
                break;
            case CTSALESORDER_ACT_INSERT_FROM_ORDER:
                $this->checkPermissions(SALES_PERMISSION);
                $this->insertFromOrder();
                break;
            case CTSALESORDER_ACT_DELETE_LINES:
                $this->checkPermissions(SALES_PERMISSION);
                $this->deleteLines();                        // bulk delete of selected lines
                break;
            case CTSALESORDER_ACT_CHANGE_SUPPLIER:
                $this->checkPermissions(SALES_PERMISSION);
                $this->changeSupplier();
                break;
            case CTSALESORDER_ACT_CREATE_MANUAL_ORDER_FORM:
                $this->checkPermissions(SALES_PERMISSION);
                $this->generateOrderForm();
                break;
            case self::CREATE_SIGNABLE_QUOTE:
                $this->generateQuoteDoc();
                break;
            case CTSALESORDER_ACT_UPLOAD_QUOTE_DOC:
                $this->checkPermissions(SALES_PERMISSION);
                $this->uploadQuoteDoc();
                break;
            case CTSALESORDER_ACT_DISPLAY_QUOTE_DOC:
                $this->checkPermissions(SALES_PERMISSION);
                $this->displayQuoteDoc();
                break;
            case CTSALESORDER_ACT_DELETE_ORDER:
                $this->checkPermissions(SALES_PERMISSION);
                $this->deleteOrder();
                break;
            case CTSALESORDER_ACT_DOWNLOAD_CSV:
                $this->checkPermissions(SALES_PERMISSION);
                $this->downloadCSV();
                break;
            case CTSALESORDER_ACT_UPDATE_DEL_ADDRESS:
            case CTSALESORDER_ACT_UPDATE_INV_ADDRESS:
                $this->checkPermissions(SALES_PERMISSION);
                $this->updateAddress();
                break;
            case CTSALESORDER_ACT_UPDATE_INV_CONTACT:
            case CTSALESORDER_ACT_UPDATE_DEL_CONTACT:
                $this->checkPermissions(SALES_PERMISSION);
                $this->updateContact();
                break;
            case CTSALESORDER_ACT_ADD_ORDLINE:
            case CTSALESORDER_ACT_EDIT_ORDLINE:
                $this->checkPermissions(SALES_PERMISSION);
                $this->editOrderLine();
                break;
            case CTSALESORDER_ACT_UPDATE_ORDLINE:
            case CTSALESORDER_ACT_INSERT_ORDLINE:
                $this->checkPermissions(SALES_PERMISSION);
                $this->updateOrderLine();
                break;
            case CTSALESORDER_ACT_MOVE_ORDLINE_UP:
                $this->checkPermissions(SALES_PERMISSION);
                $this->moveOrderLineUp();
                break;
            case CTSALESORDER_ACT_MOVE_ORDLINE_DOWN:
                $this->checkPermissions(SALES_PERMISSION);
                $this->moveOrderLineDown();
                break;
            case CTSALESORDER_ACT_DELETE_ORDLINE:
                $this->checkPermissions(SALES_PERMISSION);
                $this->deleteOrderLine();
                break;
            case CTSALESORDER_ACT_UPDATE_HEADER:
                $this->checkPermissions(SALES_PERMISSION);
                $this->updateHeader();
                break;
            case CTSALESORDER_ACT_SEND_CONFIRMATION:
                $this->checkPermissions(SALES_PERMISSION);
                $this->sendOrderConfirmation();
                break;
            case 'toggleSOMonitor':
                $salesOrderId = @$_REQUEST['salesOrderId'];
                if (!$salesOrderId) {
                    echo json_encode(["status" => "error", "message" => "Sales Order ID not given"]);
                    http_response_code(400);
                    exit;
                }
                global $db;
                $params = [
                    ["type" => "i", "value" => $salesOrderId],
                    ["type" => "i", "value" => $this->userID],
                ];
                $query = 'delete from salesOrderMonitor where  salesOrderId = ? and userId = ?';
                if (!$this->checkMonitoringSalesOrder($salesOrderId)) {
                    $query = 'insert into salesOrderMonitor values (?,?)';
                }
                $db->preparedQuery(
                    $query,
                    $params
                );
                echo json_encode(["status" => "ok"]);
                break;
            case self::UPDATE_ITEM_PRICE:
                if (!$this->hasPermissions(SALES_PERMISSION)) {
                    throw new \CNCLTD\Exceptions\JsonHttpException(
                        403,
                        'You do not sufficient permissions to perform this task'
                    );
                }

                $this->updateItemPrice();
                break;
            case 'serviceRequest':
                $this->checkPermissions(SALES_PERMISSION);
                $this->serviceRequest();
                break;
            case CTSALESORDER_ACT_CREATE_SR_FROM_LINES:
                $this->checkPermissions(SALES_PERMISSION);
                $this->serviceRequestFromLines();
                break;
            case 'sendReminder':
                $this->sendReminderQuote();
                break;
            case CTSALESORDER_ACT_CREATE_TEMPLATED_QUOTE:
                $this->createTemplatedQuote();
                break;
            case self::MOVE_LINE_TO_POSITION:
                $data = json_decode(file_get_contents('php://input'), true);
                if (!array_key_exists('lineId', $data)) {
                    throw new \CNCLTD\Exceptions\JsonHttpException(400, 'Line id is required');
                }
                if (!array_key_exists('replacedLineId', $data)) {
                    throw new \CNCLTD\Exceptions\JsonHttpException(400, 'Replaced Line Id is required');
                }
                $dbeOrdline = new DBEOrdline($this);
                $dbeOrdline->getRow($data['lineId']);
                $replacedLine = new DBEOrdline($this);
                $replacedLine->getRow($data['replacedLineId']);
                $dbeOrdline->swapPlaces(
                    $dbeOrdline->getValue(DBEOrdline::sequenceNo),
                    $replacedLine->getValue(DBEOrdline::sequenceNo)
                );
                echo json_encode(["status" => 'ok']);
                break;
            default:
                $this->displaySearchForm();
                break;
        }
    }

    /**
     * Search for customers using customerString
     * @access private
     * @throws Exception
     */
    function search()
    {
        $this->setMethodName('search');
        $this->setCustomerID(
            $this->getParam('customerID')
        ); // Have to do this because I couldn't use Javascript to set form[customerID]
        if (
            (!is_numeric($this->getOrdheadID())) &
            ($this->getOrdheadID())
        ) {
            $this->setOrdheadIDMessage(CTSALESORDER_NOT_NUMERIC);
        }
        if (($this->getFromDate()) && (!$this->isValidDate($this->getFromDate()))) {
            $this->setFromDateMessage(CTCNC_MSG_INVALID_DATE);
        }
        if (($this->getToDate()) && (!$this->isValidDate($this->getToDate()))) {
            $this->setToDateMessage(CTCNC_MSG_INVALID_DATE);
        }
        if ($this->getFormError()) {
            $this->displaySearchForm();
            return;
        }

        if ($this->getToDate() && $this->getFromDate() && $this->getToDate() < $this->getFromDate()) {
            $this->setToDateMessage(CTSALESORDER_TO_DATE_SMALLER);
        }

        if ($this->getFormError()) {
            $this->displaySearchForm();
            return;
        }
        $this->setMethodName('search');
        $this->buSalesOrder->search(
            $this->getCustomerID(),
            $this->getOrdheadID(),
            $this->getOrderType(),
            $this->getCustPORef(),
            $this->getLineText(),
            $this->getFromDate(),
            $this->getToDate(),
            $this->getQuotationUserID(),
            $this->dsOrdhead
        );
        $this->setAction(CTSALESORDER_ACT_DISP_SEARCH);
        $this->defaultAction();
    }

    function getOrdheadID()
    {
        return $this->ordheadID;
    }

    function setOrdheadID($ordheadID)
    {
        $this->ordheadID = trim($ordheadID);
    }

    function getFromDate()
    {
        return $this->fromDate;
    }

    function setFromDate($date)
    {
        $this->fromDate = $date;
    }

    function getToDate()
    {
        return $this->toDate;
    }

    function setToDate($date)
    {
        $this->toDate = $date;
    }

    /**
     * Display the initial form that prompts for search params
     * @access private
     * @throws Exception
     */
    function displaySearchForm()
    {
        $this->setMethodName('displaySearchForm');
        $this->setTemplateFiles(
            array(
                'SalesOrderSearch'  => 'SalesOrderSearch.inc',
                'OrderTypeSelector' => 'OrderTypeSelector.inc',
                'UserSelector'      => 'UserSelector.inc'
            )
        );
// Parameters
        $this->setPageTitle("Sales Orders");
        $submitURL = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array('action' => CTSALESORDER_ACT_SEARCH)
        );
        $clearURL = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array()
        );
        $customerPopupURL =
            Controller::buildLink(
                CTCNC_PAGE_CUSTOMER,
                array(
                    'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $createQuoteURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTSALESORDER_ACT_CREATE_QUOTE
                )
            );

        $createTemplatedQuoteURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTSALESORDER_ACT_CREATE_TEMPLATED_QUOTE
                )
            );

        $createOrderURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTSALESORDER_ACT_CREATE_ORDER
                )
            );
        $this->dsOrdhead->initialise();
        $this->template->set_block(
            'OrderTypeSelector',
            'orderTypeBlock',
            'orderTypes'
        );
        $this->parseOrderTypeSelector($this->getOrderType());
        $this->template->parse(
            'orderTypeSelector',
            'OrderTypeSelector',
            true
        );

        $this->template->set_block(
            'UserSelector',
            'userBlock',
            'users'
        );
        $this->parseUserSelector($this->getQuotationUserID());
        $this->template->parse(
            'userSelector',
            'UserSelector',
            true
        );

        $this->setSessionParam(
            'urlReferer',
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'          => CTSALESORDER_ACT_SEARCH,
                    'customerID'      => $this->getCustomerID(),
                    'quotationUserID' => $this->getQuotationUserID(),
                    'orderType'       => $this->getOrderType(),
                    'ordheadID'       => $this->getOrdheadID(),
                    'fromDate'        => $this->getFromDate(),
                    'toDate'          => $this->getToDate(),
                    'lineText'        => $this->getLineText()
                )
            )
        );

        if ($this->dsOrdhead->rowCount() > 0) {

            $this->template->set_block(
                'SalesOrderSearch',
                'orderBlock',
                'orders'
            );
            $customerNameCol = $this->dsOrdhead->columnExists(DBEJOrdhead::customerName);
            $ordheadIDCol = $this->dsOrdhead->columnExists(DBEOrdhead::ordheadID);
            $customerIDCol = $this->dsOrdhead->columnExists(DBEOrdhead::customerID);
            $typeCol = $this->dsOrdhead->columnExists(DBEOrdhead::type);
            $dateCol = $this->dsOrdhead->columnExists(DBEOrdhead::date);
            $custPORefCol = $this->dsOrdhead->columnExists(DBEOrdhead::custPORef);
            $rowNum = 1;
            while ($this->dsOrdhead->fetchNext()) {
                if ($this->hasPermissions(SALES_PERMISSION)) {
                    $customerURL =
                        Controller::buildLink(
                            CTCNC_PAGE_CUSTOMER,
                            array(
                                'action'     => CTCNC_ACT_DISP_EDIT,
                                'customerID' => $this->dsOrdhead->getValue($customerIDCol)
                            )
                        );
                    $customerLink = '<a href="' . $customerURL . '">' . $this->dsOrdhead->getValue(
                            $customerNameCol
                        ) . '</A>';
                } else {
                    $customerLink = $this->dsOrdhead->getValue($customerNameCol);
                }

                $this->setOrdheadID($this->dsOrdhead->getValue($ordheadIDCol));
                $orderURL = $this->getDisplayOrderURL();
                $this->setOrdheadID(null);

                $this->template->set_var(
                    array(
                        'listCustomerLink' => $customerLink,
                        'listOrderURL'     => $orderURL,
                        'listOrdheadID'    => $this->dsOrdhead->getValue($ordheadIDCol),
                        'listOrderType'    => $this->getTypeDescription($this->dsOrdhead->getValue($typeCol)),
                        'listOrderDate'    => strftime(
                            "%d/%m/%Y",
                            strtotime($this->dsOrdhead->getValue($dateCol))
                        ),
                        'listCustPORef'    => $this->dsOrdhead->getValue($custPORefCol),
                        'rowNum'           => $rowNum
                    )
                );
                $this->template->parse(
                    'orders',
                    'orderBlock',
                    true
                );
                $rowNum++;
            }
        }
        $this->template->set_var(
            'rowNum',
            1
        ); // just so that javascript does not error!
        if ($this->getCustomerID()) {
            $dsCustomer = new DataSet($this);
            $this->buCustomer->getCustomerByID(
                $this->getCustomerID(),
                $dsCustomer
            );
            $this->setCustomerString($dsCustomer->getValue(DBECustomer::name));
        }
        $this->template->set_var(
            array(
                'customerString'          => $this->getCustomerString(),
                'customerStringMessage'   => $this->getCustomerStringMessage(),
                'toDateMessage'           => $this->getToDateMessage(),
                'fromDateMessage'         => $this->getFromDateMessage(),
                'customerID'              => $this->getCustomerID(),
                'ordheadID'               => $this->getOrdheadID(),
                'fromDate'                => $this->getFromDate(),
                'toDate'                  => $this->getToDate(),
                'ordheadIDMessage'        => $this->getOrdheadIDMessage(),
                'custPORef'               => $this->getCustPORef(),
                'lineText'                => Controller::htmlDisplayText($this->getLineText()),
                'submitURL'               => $submitURL,
                'clearURL'                => $clearURL,
                'createQuoteURL'          => $createQuoteURL,
                'createOrderURL'          => $createOrderURL,
                'createTemplatedQuoteURL' => $createTemplatedQuoteURL,
                'customerPopupURL'        => $customerPopupURL,
            )
        );
        $this->template->parse(
            'CONTENTS',
            'SalesOrderSearch',
            true
        );
        $this->parsePage();
    }

    /**
     * Get and parse order type drop-down selector
     * @access private
     * @param $orderType
     */
    function parseOrderTypeSelector($orderType)
    {
        foreach ($this->orderTypeArray as $key => $value) {
            $orderTypeSelected = ($orderType == $key) ? CT_SELECTED : null;
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
    }

    function getOrderType()
    {
        return $this->orderType;
    }

    function setOrderType($orderType)
    {
        $this->orderType = $orderType;
    }

    function parseUserSelector($userID)
    {
        $dsUser = new DataSet($this);
        $this->buSalesOrder->getSalesUsers($dsUser);

        while ($dsUser->fetchNext()) {
            $userSelected = ($userID == $dsUser->getValue(DBEUser::userID)) ? CT_SELECTED : null;
            $this->template->set_var(
                array(
                    'userSelected' => $userSelected,
                    'userID'       => $dsUser->getValue(DBEUser::userID),
                    'userName'     => $dsUser->getValue(DBEUser::name)
                )
            );
            $this->template->parse(
                'users',
                'userBlock',
                true
            );
        }
    }

    function getQuotationUserID()
    {
        return $this->quotationUserID;
    }

    function setQuotationUserID($userID)
    {
        $this->setNumericVar(
            'quotationUserID',
            $userID
        );
    }

    function getCustomerID()
    {
        return $this->customerID;
    }

    function setCustomerID($customerID)
    {
        $this->setNumericVar(
            'customerID',
            $customerID
        );
    }

    function getLineText()
    {
        return $this->lineText;
    }

    function setLineText($text)
    {
        $this->lineText = $text;
    }

    /**
     * @access private
     * @throws Exception
     */
    function getDisplayOrderURL()
    {
        return Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'    => CTCNC_ACT_DISP_SALESORDER,
                'ordheadID' => $this->getOrdheadID()
            )
        );

    }

    function getTypeDescription($type)
    {
        return $this->orderTypeArray[$type];
    }

    function getCustomerString()
    {
        return $this->customerString;
    }

    function setCustomerString($customerString)
    {
        $this->customerString = $customerString;
    }

    function getCustomerStringMessage()
    {
        return $this->customerStringMessage;
    }

    function setCustomerStringMessage($message)
    {
        if (func_get_arg(0)) $this->setFormErrorOn();
        $this->customerStringMessage = $message;
    }

    function getToDateMessage()
    {
        return $this->toDateMessage;
    }

    function setToDateMessage($message)
    {
        if (func_get_arg(0)) $this->setFormErrorOn();
        $this->toDateMessage = $message;
    }

    function getFromDateMessage()
    {
        return $this->fromDateMessage;
    }

    function setFromDateMessage($message)
    {
        if (func_get_arg(0)) $this->setFormErrorOn();
        $this->fromDateMessage = $message;
    }

    function getOrdheadIDMessage()
    {
        return $this->ordheadIDMessage;
    }

    function setOrdheadIDMessage($message)
    {
        if (func_get_arg(0)) $this->setFormErrorOn();
        $this->ordheadIDMessage = $message;
    }

    function getCustPORef()
    {
        return $this->custPORef;
    }

    /**
     * Display one order
     * @access private
     * @throws Exception
     */
    function displayOrder()
    {
        $dsOrdhead = new DataSet($this);
        $dsOrdline = new DataSet($this);
        $dsDeliveryContact = new DataSet($this);
        $this->setMethodName('displayOrder');

        $markupOriginalQuote = null;
        $urlUpdateDelAddress = null;
        $urlUpdateInvAddress = null;
        $urlUpdateDelContact = null;
        $urlUpdateInvContact = null;
        $urlUpdateHeader = null;
        $urlDeleteOrder = null;
        $txtDeleteOrder = null;
        $urlSitePopup = null;
        $urlSiteEdit = null;
        $urlCustomerDisplay = null;
        $urlContactPopup = null;
        $urlContactEdit = null;
        $urlRenewalReport = null;
        $txtRenewalReport = null;
        $txtCustomerNote = null;
        $urlCustomerNote = null;


        if ($this->getAction() != CTSALESORDER_ACT_CREATE_QUOTE and $this->getAction(
            ) != CTSALESORDER_ACT_CREATE_ORDER) {

            if (!$this->getOrdheadID()) {
                $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
                return;
            }

            if (!$this->buSalesOrder->getOrderWithCustomerName(
                $this->getOrdheadID(),
                $dsOrdhead,
                $dsOrdline,
                $dsDeliveryContact
            )) {
                $this->displayFatalError(CTSALESORDER_MSG_ORDER_NOT_FND);
                return;
            }
            if ($this->lineValidationError) {
                $dsOrdline = &$this->dsOrdline;                    // this is the dataset with validation problems
                $dsOrdline->initialise();
            }
        } else {
            if (!$this->getCustomerID()) {
                $this->displayFatalError(CTSALESORDER_MSG_CUSTOMERID_NOT_PASSED);
                return;
            }
            if (!$this->buCustomer->getCustomerByID(
                $this->getCustomerID(),
                $dsCustomer
            )) {
                $this->displayFatalError(CTSALESORDER_MSG_CUS_NOT_FND);
                return;
            }

            if ($this->getAction() == CTSALESORDER_ACT_CREATE_ORDER) {
                $this->buSalesOrder->initialiseOrder(
                    $dsOrdhead,
                    $dsOrdline,
                    $dsCustomer
                );
            } else {
                $this->buSalesOrder->InitialiseQuote(
                    $dsOrdhead,
                    $dsOrdline,
                    $dsCustomer
                );
            }
            $this->setOrdheadID($dsOrdhead->getValue(DBEOrdhead::ordheadID));
        }
        $orderType = $dsOrdhead->getValue(DBEOrdhead::type);
        $projectLink = null;
        if ($dsOrdhead->getValue(DBEOrdhead::customerID)) {
            $projectLink = BUProject::getCurrentProjectLink($dsOrdhead->getValue(DBEOrdhead::customerID));
        }

        $this->setTemplateFiles(
            array(
                'SalesOrderDisplay'                 => 'SalesOrderDisplay.inc',
                'SalesOrderDisplayConvertToOrder'   => 'SalesOrderDisplayConvertToOrder.inc',
                'SalesOrderDisplayCreatePO'         => 'SalesOrderDisplayCreatePO.inc',
                'SalesOrderLineEditJS'              => 'SalesOrderLineEditJS.inc',
                'SalesOrderDisplayNewLine'          => 'SalesOrderDisplayNewLine.inc',
                'SalesOrderHeadDisplay'             => 'SalesOrderHeadDisplay.inc',
                'SalesOrderHeadAmend'               => 'SalesOrderHeadAmend.inc',
                'SalesOrderDisplayDocuments'        => 'SalesOrderDisplayDocuments.inc',
                'SalesOrderDisplayQuotes'           => 'SalesOrderDisplayQuotes.inc',
                'SalesOrderGenerateQuotes'          => 'SalesOrderGenerateQuotes.inc',
                'SalesOrderItemLine'                => 'SalesOrderItemLine.inc',
                'SalesOrderCommentLine'             => 'SalesOrderCommentLine.inc',
                'SalesOrderLineIcons'               => 'SalesOrderLineIcons.inc',
                'SalesOrderLineUpdateItemPriceIcon' => 'SalesOrderLineUpdateItemPriceIcon.inc',
                'SalesOrderSiteEditJS'              => 'SalesOrderSiteEditJS.inc',
                'DespatchDisplayNotes'              => 'DespatchDisplayNotes.inc'
            )
        );

        $purchaseOrderCount = $this->buSalesOrder->countPurchaseOrders($dsOrdhead->getValue(DBEOrdhead::ordheadID));

        // Initialise an array of actions that may be performed upon this order: they are displayed in a drop-down
        // below the lines section and will be applied to the selected (checked) lines
        $actions = array();
        $quickQuoteDisabled = false;
        if ($dsOrdline->rowCount()) {                        // There are lines
            if ($orderType == 'Q') {
                $actions[CTSALESORDER_ACT_COPY_TO_ORDER] = 'copy to order';
                $actions[CTSALESORDER_ACT_CONVERT_TO_ORDER] = 'convert to order';
            }
            if ($orderType == 'Q' || $orderType == 'I') {
                $actions[CTSALESORDER_ACT_DELETE_LINES] = 'delete lines';
            }

            if ($orderType !== 'C') {
                $quickQuoteDisabled = !$this->dbeUser->getValue(DBEUser::signatureFilename);
                $actions[self::CREATE_SIGNABLE_QUOTE] = 'create Signable quote';
            }

            $actions[CTSALESORDER_ACT_CREATE_MANUAL_ORDER_FORM] = 'create manual order form';
            $actions[CTSALESORDER_ACT_CHANGE_SUPPLIER] = 'change supplier';
            $actions[CTSALESORDER_ACT_CREATE_SR_FROM_LINES] = 'create new SR';
        }
        $order = [
            self::CREATE_SIGNABLE_QUOTE,
            CTSALESORDER_ACT_COPY_TO_ORDER,
            CTSALESORDER_ACT_CONVERT_TO_ORDER,
            CTSALESORDER_ACT_DELETE_LINES,
            CTSALESORDER_ACT_UPDATE_LINES,
            CTSALESORDER_ACT_SEND_CONFIRMATION,
            CTSALESORDER_ACT_CREATE_MANUAL_ORDER_FORM,
            CTSALESORDER_ACT_CHANGE_SUPPLIER,
            CTSALESORDER_ACT_CREATE_SR_FROM_LINES,
        ];

        uksort(
            $actions,
            function ($a, $b) use ($order) {
                return array_search($a, $order) - array_search($b, $order);
            }
        );

        if (!empty($actions)) {
            $this->template->set_block(
                'SalesOrderDisplay',
                'actionBlock',
                'actions'
            );
            foreach ($actions as $action => $actionDescription) {
                $this->template->set_var(
                    array(
                        'SELECTED'          => ($this->getAction() == $action) ? CT_SELECTED : null,
                        'action'            => $action,
                        'actionDescription' => $actionDescription,
                        'disabled'          => $quickQuoteDisabled && $action == self::CREATE_SIGNABLE_QUOTE ? 'disabled' : null
                    )
                );
                $this->template->parse(
                    'actions',
                    'actionBlock',
                    true
                );
            }
        }

        /*
    Any one outside the sales group will not be able to do anything or see anything other
    than non-value fields.
    */
        if (!$this->hasPermissions(SALES_PERMISSION)) {
            $restrictedView = CTCNC_HTML_DISABLED;
            $readOnly = CTCNC_HTML_DISABLED;
            $valuesDisabled = CTCNC_HTML_DISABLED;
        } else {

            $restrictedView = null;
            /*
      Inside sales group, decide which items are readonly
      */
            if ($orderType == 'Q' || $orderType == 'I') {
                /*
        Quotes or initial orders allow all
        */
                $readOnly = null;
                $valuesDisabled = null;

            } else {
                $readOnly = CTCNC_HTML_DISABLED;
                $valuesDisabled = CTCNC_HTML_DISABLED;
            }

            if ($orderType == 'C' && !$this->hasPermissions(ACCOUNTS_PERMISSION)) {
                $valuesDisabled = null;
            }

        }
        $urlSubmitOrderLines = null;

        // Build the various URL links required on the page
        if (!$restrictedView) {

            $urlDeleteOrder = null;
            $txtDeleteOrder = null;
            // Allow delete if quote or initial order and no purchase orders exist yet
            if (
                $orderType == 'Q' || ($orderType == 'I' && !$purchaseOrderCount)
            ) {
                $urlCallback =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTSALESORDER_ACT_DISP_SEARCH
                        )
                    );
                $urlDeleteOrder =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'      => CTSALESORDER_ACT_DELETE_ORDER,
                            'ordheadID'   => $this->getOrdheadID(),
                            'urlCallback' => $urlCallback
                        )
                    );
                $txtDeleteOrder = CTSALESORDER_TXT_DELETE;
            }


            $urlSubmitOrderLines =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array()
                );
            /*
      Display link to original quote if exists and is not same as this
      */
            $markupOriginalQuote = null;
            if ($dsOrdhead->getValue(DBEOrdhead::quotationOrdheadID) && $dsOrdhead->getValue(
                    DBEOrdhead::quotationOrdheadID
                ) != $this->getOrdheadID()) {
                $urlOriginalQuote =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'    => $this->getAction(),
                            'ordheadID' => $dsOrdhead->getValue(DBEOrdhead::quotationOrdheadID)
                        )
                    );
                $markupOriginalQuote = '<a href="' . $urlOriginalQuote . '" target="_blank">Original quote ' . $dsOrdhead->getValue(
                        DBEOrdhead::quotationOrdheadID
                    ) . '</a>';
            }

            $urlUpdateInvAddress =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSALESORDER_ACT_UPDATE_INV_ADDRESS
                    )
                );
            $urlUpdateDelAddress =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSALESORDER_ACT_UPDATE_DEL_ADDRESS
                    )
                );
            $urlUpdateInvContact =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSALESORDER_ACT_UPDATE_INV_CONTACT
                    )
                );
            $urlUpdateDelContact =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSALESORDER_ACT_UPDATE_DEL_CONTACT
                    )
                );
            $urlSiteEdit =
                Controller::buildLink(
                    CTCNC_PAGE_SITE,
                    array(
                        'action'  => CTCNC_ACT_SITE_EDIT,
                        'htmlFmt' => CT_HTML_FMT_POPUP
                    )
                );
            $urlUpdateHeader =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSALESORDER_ACT_UPDATE_HEADER
                    )
                );
            $urlSitePopup =
                Controller::buildLink(
                    CTCNC_PAGE_SITE,
                    array(
                        'action'  => CTCNC_ACT_SITE_POPUP,
                        'htmlFmt' => CT_HTML_FMT_POPUP
                    )
                );

            $urlCustomerDisplay =
                Controller::buildLink(
                    'Customer.php',
                    array(
                        'action'     => 'dispEdit',
                        'customerID' => $dsOrdhead->getValue(DBEOrdhead::customerID)
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
            $urlContactPopup =
                Controller::buildLink(
                    CTCNC_PAGE_CONTACT,
                    array(
                        'action'     => CTCNC_ACT_CONTACT_POPUP,
                        'customerID' => $dsOrdhead->getValue(DBEOrdhead::customerID),
                        'htmlFmt'    => CT_HTML_FMT_POPUP
                    )
                );
            if ($orderType != 'Q') {
                // Show navigate Purchase Orders if they exist
                if ($purchaseOrderCount > 0) {
                    $urlPurchaseOrders =
                        Controller::buildLink(
                            CTCNC_PAGE_PURCHASEORDER,
                            array(
                                'action'    => CTCNC_ACT_SEARCH,
                                'ordheadID' => $dsOrdhead->getValue(DBEOrdhead::ordheadID)
                            )
                        );

                    $this->template->set_var(
                        array(
                            'urlPurchaseOrders' => $urlPurchaseOrders,
                            'txtPurchaseOrders' => 'Purchase Orders'
                        )
                    );
                }

                $linkedServiceRequestCount = $this->buSalesOrder->countLinkedServiceRequests(
                    $dsOrdhead->getValue(DBEOrdhead::ordheadID)
                );

                if ($linkedServiceRequestCount == 0) {
                    /* create new */
                    $urlServiceRequest =
                        Controller::buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'    => 'serviceRequest',
                                'ordheadID' => $dsOrdhead->getValue(DBEOrdhead::ordheadID)
                            )
                        );

//          $linkServiceRequest = '<a href="#" onclick="serviceRequestPopup()">Service Request</a>';
                    $linkServiceRequest = '<a href="' . $urlServiceRequest . '" >Create SR</a>';

                } elseif ($linkedServiceRequestCount == 1) {

                    $problemID = $this->buSalesOrder->getLinkedServiceRequestID(
                        $dsOrdhead->getValue(DBEOrdhead::ordheadID)
                    );

                    $urlServiceRequest =
                        Controller::buildLink(
                            'Activity.php',
                            array(
                                'action'    => 'displayFirstActivity',
                                'problemID' => $problemID
                            )
                        );

                    $linkServiceRequest = '<a href="' . $urlServiceRequest . '" target="_blank"><div class="navigateLinkCustomerNoteExists">View SR</div></a>';

                } else {     // many SRs so display search page
                    $urlServiceRequest =
                        Controller::buildLink(
                            'Activity.php',
                            array(
                                'action'             => 'search',
                                'linkedSalesOrderID' => $dsOrdhead->getValue(DBEOrdhead::ordheadID)
                            )
                        );

                    $linkServiceRequest = '<a href="' . $urlServiceRequest . '" target="_blank"><div class="navigateLinkCustomerNoteExists">View SRs</div></a>';

                }

                $this->template->set_var(
                    array(
                        'urlServiceRequest'  => $urlServiceRequest,
                        'linkServiceRequest' => $linkServiceRequest
                    )
                );

            }

            $urlRenewalReport =
                Controller::buildLink(
                    'RenewalReport.php',
                    array(
                        'action'     => 'produceReport',
                        'customerID' => $dsOrdhead->getValue(DBEOrdhead::customerID)
                    )
                );


            $txtRenewalReport = 'Renewal Report';

            $urlCustomerNote =
                Controller::buildLink(
                    'CustomerNote.php',
                    array(
                        'action'     => 'customerNotePopup',
                        'customerID' => $dsOrdhead->getValue(DBEOrdhead::customerID),
                        'ordheadID'  => $dsOrdhead->getValue(DBEOrdhead::ordheadID),
                        'htmlFmt'    => CT_HTML_FMT_POPUP
                    )
                );

            $buCustomerNote = new BUCustomerNote($this);

            if (
            $buCustomerNote->getNote(
                false,
                false,
                'salesOrder',
                false,
                $dsOrdhead->getValue(DBEOrdhead::ordheadID)
            )
            ) {

                $txtCustomerNote = '<div class="navigateLinkCustomerNoteExists">Customer Note</div>';

            } else {
                $txtCustomerNote = 'Customer Note';

            }

            // Show navigate link to invoices if order is part or completed and they exist
            if (($orderType == 'P') or ($orderType == 'C')) {
                $buInvoice = new BUInvoice($this);
                $invoiceCount = $buInvoice->countInvoicesByOrdheadID($dsOrdhead->getValue(DBEOrdhead::ordheadID));
                if ($invoiceCount > 0) {
                    $urlInvoices =
                        Controller::buildLink(
                            CTCNC_PAGE_INVOICE,
                            array(
                                'action'    => CTCNC_ACT_SEARCH,
                                'ordheadID' => $dsOrdhead->getValue(DBEOrdhead::ordheadID)
                            )
                        );
                    $this->template->set_var(
                        array(
                            'urlInvoices' => $urlInvoices,
                            'txtInvoices' => 'Invoices'
                        )
                    );
                }
                unset($buInvoice);
            }
            // Show despatch link if order type is part-despatched or initial and there are lines
            if (
                (($orderType == 'P') || ($orderType == 'I')) && ($dsOrdline->rowCount(
                )) || (!common_isAnInternalStockLocation($dsOrdhead->getValue(DBEOrdhead::customerID)))
            ) {
                $urlDespatch =
                    Controller::buildLink(
                        CTCNC_PAGE_DESPATCH,
                        array(
                            'action'    => CTCNC_ACT_DISPLAY_DESPATCH,
                            'ordheadID' => $dsOrdhead->getValue(DBEOrdhead::ordheadID)
                        )
                    );
                $this->template->set_var(
                    array(
                        'urlDespatch' => $urlDespatch,
                        'txtDespatch' => 'Despatch'
                    )
                );
            }
        } // end !$restrictedView


        $this->displaySalesOrderHeader($dsOrdhead);

        if ($this->getAction() != CTSALESORDER_ACT_INSERT_FROM_ORDER) {
            $this->template->set_var(
                array(
                    'fromOrdheadIDStyle' => 'style="display: none"'
                )
            );
        }

        if ($this->getAction() != CTSALESORDER_ACT_CHANGE_SUPPLIER) {
            $this->template->set_var(
                array(
                    'updateSupplierNameStyle' => 'style="display: none"'
                )
            );
        }
        $this->template->set_var(
            array(
                'customerID'                   => $dsOrdhead->getValue(DBEOrdhead::customerID),
                'invContact'                   => $dsOrdhead->getValue(
                        DBEOrdhead::invContactSalutation
                    ) . ' ' . $dsOrdhead->getValue(
                        DBEOrdhead::invContactName
                    ),
                'invContactID'                 => $dsOrdhead->getValue(DBEOrdhead::invContactID),
                'delContactID'                 => $dsOrdhead->getValue(DBEOrdhead::delContactID),
                'invContactPhone'              => $dsOrdhead->getValue(DBEOrdhead::invContactPhone),
                'invSitePhone'                 => $dsOrdhead->getValue(DBEOrdhead::invSitePhone),
                'invContactFax'                => $dsOrdhead->getValue(DBEOrdhead::invContactFax),
                'invContactEmail'              => $dsOrdhead->getValue(DBEOrdhead::invContactEmail),
                'invSiteNo'                    => $dsOrdhead->getValue(DBEOrdhead::invSiteNo),
                'invAdd1'                      => $dsOrdhead->getValue(DBEOrdhead::invAdd1),
                'invAdd2'                      => $dsOrdhead->getValue(DBEOrdhead::invAdd2),
                'invAdd3'                      => $dsOrdhead->getValue(DBEOrdhead::invAdd3),
                'invTown'                      => $dsOrdhead->getValue(DBEOrdhead::invTown),
                'invCounty'                    => $dsOrdhead->getValue(DBEOrdhead::invCounty),
                'invPostcode'                  => $dsOrdhead->getValue(DBEOrdhead::invPostcode),
                'delContact'                   => $dsOrdhead->getValue(
                        DBEOrdhead::delContactSalutation
                    ) . ' ' . $dsOrdhead->getValue(
                        DBEOrdhead::delContactName
                    ),
                'delContactPhone'              => $dsOrdhead->getValue(DBEOrdhead::delContactPhone),
                'delSitePhone'                 => $dsOrdhead->getValue(DBEOrdhead::delSitePhone),
                'delContactFax'                => $dsOrdhead->getValue(DBEOrdhead::delContactFax),
                'delContactEmail'              => $dsOrdhead->getValue(DBEOrdhead::delContactEmail),
                'delSiteNo'                    => $dsOrdhead->getValue(DBEOrdhead::delSiteNo),
                'delAdd1'                      => $dsOrdhead->getValue(DBEOrdhead::delAdd1),
                'delAdd2'                      => $dsOrdhead->getValue(DBEOrdhead::delAdd2),
                'delAdd3'                      => $dsOrdhead->getValue(DBEOrdhead::delAdd3),
                'delTown'                      => $dsOrdhead->getValue(DBEOrdhead::delTown),
                'delCounty'                    => $dsOrdhead->getValue(DBEOrdhead::delCounty),
                'delPostcode'                  => $dsOrdhead->getValue(DBEOrdhead::delPostcode),
                'ordheadID'                    => $dsOrdhead->getValue(DBEOrdhead::ordheadID),
                'serviceRequestCustomerItemID' => $dsOrdhead->getValue(DBEOrdhead::serviceRequestCustomerItemID),
                'serviceRequestText'           => $dsOrdhead->getValue(DBEOrdhead::serviceRequestText),
                'markupOriginalQuote'          => $markupOriginalQuote,
                'urlUpdateDelAddress'          => $urlUpdateDelAddress,
                'urlUpdateInvAddress'          => $urlUpdateInvAddress,
                'urlUpdateDelContact'          => $urlUpdateDelContact,
                'urlUpdateInvContact'          => $urlUpdateInvContact,
                'urlUpdateHeader'              => $urlUpdateHeader,
                'urlDeleteOrder'               => $urlDeleteOrder,
                'txtDeleteOrder'               => $txtDeleteOrder,
                'urlSitePopup'                 => $urlSitePopup,
                'urlSiteEdit'                  => $urlSiteEdit,
                'urlCustomerDisplay'           => $urlCustomerDisplay,
                'urlContactPopup'              => $urlContactPopup,
                'urlContactEdit'               => $urlContactEdit,
                'urlRenewalReport'             => $urlRenewalReport,
                'txtRenewalReport'             => $txtRenewalReport,
                'txtCustomerNote'              => $txtCustomerNote,
                'urlCustomerNote'              => $urlCustomerNote,
                'linesMessage'                 => $this->getLinesMessage(),
                'lineValidationError'          => $this->lineValidationError,
                'restrictedView'               => $restrictedView,
                'readOnly'                     => $readOnly,
                'valuesDisabled'               => $valuesDisabled,
                'updatedTime'                  => $dsOrdhead->getValue(DBEOrdhead::updatedTime),
                'currentDocumentsLink'         => $this->getCurrentDocumentsLink(
                    $dsOrdhead->getValue(DBEOrdhead::customerID),
                    $this->buCustomer
                ),
                'projectLink'                  => $projectLink
            )
        );
        $buRenewal = null;
        // Order lines section
        $curSaleGrandTotal = 0;
        $curProfitGrandTotal = 0;
        $curCostGrandTotal = 0;
        $percProfitGrandTotal = 0;

        $recurringCurSaleGrandTotal = 0;
        $recurringCurCostGrandTotal = 0;
        $recurringCurProfitGrandTotal = 0;
        $recurringPercProfitGrandTotal = 0;

        $hasGreenArrow = false;
        if ($dsOrdline->fetchNext()) {

            $this->template->set_block(
                'SalesOrderDisplay',
                'orderLineBlock',
                'orderLines'
            );

            $this->template->set_block(
                'SalesOrderDisplay',
                'recurringOrderLineBlock',
                'recurringOrderLines'
            );


            do {
                $this->renderLine(
                    $dsOrdline,
                    $curSaleGrandTotal,
                    $curProfitGrandTotal,
                    $curCostGrandTotal,
                    $percProfitGrandTotal,
                    $recurringCurSaleGrandTotal,
                    $recurringCurCostGrandTotal,
                    $recurringCurProfitGrandTotal,
                    $recurringPercProfitGrandTotal,
                    $buRenewal,
                    $readOnly,
                    $restrictedView,
                    $dsOrdhead
                );
            } while ($dsOrdline->fetchNext());
        }
        // END OF ORDER LINES SECTION

        // Order totals
        if (!$dsOrdline->getValue(DBEOrdline::isRecurring)) {
            if ($curCostGrandTotal) {
                $percProfitGrandTotal = $curProfitGrandTotal * (100 / $curCostGrandTotal);
            }
        } else {
            if ($recurringCurCostGrandTotal) {
                $recurringPercProfitGrandTotal = $recurringCurProfitGrandTotal * (100 / $recurringCurCostGrandTotal);
            }
        }

        $this->template->set_var(
            array(
                'curSaleGrandTotal'              => Controller::formatNumber($curSaleGrandTotal),
                'curCostGrandTotal'              => Controller::formatNumber($curCostGrandTotal),
                'curProfitGrandTotal'            => Controller::formatNumber($curProfitGrandTotal),
                'percProfitGrandTotal'           => Controller::formatNumber($percProfitGrandTotal, 1),
                'orderTotalProfitClass'          => ($curProfitGrandTotal < 0) ? CTSALESORDER_CLS_ORDER_TOTAL_LOSS : CTSALESORDER_CLS_ORDER_TOTAL_ITEM,
                'orderTotalSaleClass'            => ($curSaleGrandTotal < 0) ? CTSALESORDER_CLS_ORDER_TOTAL_LOSS : CTSALESORDER_CLS_ORDER_TOTAL_ITEM,
                'orderTotalCostClass'            => ($curCostGrandTotal < 0) ? CTSALESORDER_CLS_ORDER_TOTAL_LOSS : CTSALESORDER_CLS_ORDER_TOTAL_ITEM,
                'recurringCurCostGrandTotal'     => Controller::formatNumber($recurringCurSaleGrandTotal),
                'recurringCurSaleGrandTotal'     => Controller::formatNumber($recurringCurCostGrandTotal),
                'recurringCurProfitGrandTotal'   => Controller::formatNumber($recurringCurProfitGrandTotal),
                'recurringPercProfitGrandTotal'  => Controller::formatNumber($recurringPercProfitGrandTotal, 1),
                'recurringOrderTotalCostClass'   => ($recurringCurProfitGrandTotal < 0) ? CTSALESORDER_CLS_ORDER_TOTAL_LOSS : CTSALESORDER_CLS_ORDER_TOTAL_ITEM,
                'recurringOrderTotalSaleClass'   => ($recurringCurSaleGrandTotal < 0) ? CTSALESORDER_CLS_ORDER_TOTAL_LOSS : CTSALESORDER_CLS_ORDER_TOTAL_ITEM,
                'recurringOrderTotalProfitClass' => ($recurringCurCostGrandTotal < 0) ? CTSALESORDER_CLS_ORDER_TOTAL_LOSS : CTSALESORDER_CLS_ORDER_TOTAL_ITEM,
            )
        );
        // End of order totals

        $uploadQuoteDocURL = null;

        if (!$restrictedView) {

            $uploadQuoteDocURL =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSALESORDER_ACT_UPLOAD_QUOTE_DOC
                    )
                );
            $this->template->set_var(
                array(
                    'urlSubmitOrderLines' => $urlSubmitOrderLines
                )
            );
            /*
        Quote documents
      */
            $thereAreQuoteDocuments = $this->buSalesOrder->getQuotationsByOrdheadID(
                $this->getOrdheadID(),
                $this->dsQuotation
            );
            // if this is a quote with item lines then display upload and "generate quick-quote" forms
            if (
                ($orderType == 'Q' || $orderType == 'I') &&
                ($dsOrdline->rowCount() > 0)
            ) {
                if ((!$this->getSalutation()) && (!$this->getFormError())) {
                    $this->setSalutation('Dear ' . $dsDeliveryContact->getValue(DBEContact::firstName));
                }
                if ((!$this->getIntroduction()) && (!$this->getFormError())) {
                    if ($dsOrdhead->getValue(DBEOrdhead::quotationIntroduction)) {
                        $this->setIntroduction($dsOrdhead->getValue(DBEOrdhead::quotationIntroduction));
                    } else {
                        $this->setIntroduction(CTSALESORDER_TXT_INTRODUCTION);
                    }
                }
                if ((!$this->getEmailSubject()) && (!$this->getFormError())) {
                    if ($dsOrdhead->getValue(DBEOrdhead::quotationSubject)) {
                        $this->setEmailSubject($dsOrdhead->getValue(DBEOrdhead::quotationSubject));
                    }
                }

                $this->template->setBlock('SalesOrderGenerateQuotes', 'quickQuoteTextBlock', 'quickQuoteText');


                $dbeStandardText = new DBEStandardText($this);
                $dbeStandardText->getRowsByTypeID(8, DBEStandardText::stt_standardtextno);
                $selected = false;
                while ($dbeStandardText->fetchNext()) {
                    $selectedText = null;
                    if (!$selected) {
                        $selectedText = CT_SELECTED;
                        $selected = true;
                    }
                    $this->template->set_var(
                        [
                            "quickQuoteTextSelected"    => $selectedText,
                            "quickQuoteTextValue"       => base64_encode(
                                $dbeStandardText->getValue(DBEStandardText::stt_text)
                            ),
                            "quickQuoteTextDescription" => $dbeStandardText->getValue(DBEStandardText::stt_desc)
                        ]
                    );
                    $this->template->parse('quickQuoteText', 'quickQuoteTextBlock', true);
                }

                $this->template->set_var(
                    array(
                        'salutation'        => $this->getSalutation(),
                        'userMessage'       => $this->getUserMessage(),
                        'uploadUserMessage' => $this->getUploadUserMessage(),
                        'quoteFileMessage'  => $this->getQuoteFileMessage(),
                        'introduction'      => $this->getIntroduction(),
                        'emailSubject'      => $this->getEmailSubject(),
                        'uploadQuoteDocURL' => $uploadQuoteDocURL
                    )
                );
                $this->template->parse(
                    'salesOrderGenerateQuotes',
                    'SalesOrderGenerateQuotes',
                    true
                );
            }

            $this->template->setVar(
                'monitorChecked',
                $this->checkMonitoringSalesOrder(
                    $dsOrdhead->getValue(DBEOrdhead::ordheadID)
                ) ? 'checked' : ''
            );
            $quoteSentDateTime = null;

            if ($thereAreQuoteDocuments) {
                $this->dsQuotation->initialise();
                $this->template->set_block(
                    'SalesOrderDisplayQuotes',
                    'quotationBlock',
                    'quotations'
                );
                while ($this->dsQuotation->fetchNext()) {
                    $displayQuoteDocURL =
                        Controller::buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'      => CTSALESORDER_ACT_DISPLAY_QUOTE_DOC,
                                'quotationID' => $this->dsQuotation->getValue(DBEQuotation::quotationID)
                            )
                        );
                    $quoteSent = !!$this->dsQuotation->getValue(DBEQuotation::sentDateTime);
                    $fileExists = $this->checkQuoteDocFile($this->dsQuotation);

                    $sendQuoteDocURLQuoteDocURL = null;
                    $deleteQuoteDocURL = null;
                    $txtDelete = null;
                    $txtSendQuote = null;
                    $txtReminder = null;
                    $sendQuoteDocURL = null;
                    $flagAsSentQuoteDocURL = null;
                    $txtFlagAsSent = null;
                    if (!$fileExists) {
                        $deleteQuoteDocURL =
                            Controller::buildLink(
                                $_SERVER['PHP_SELF'],
                                array(
                                    'action'      => CTSALESORDER_ACT_DELETE_QUOTE_DOC,
                                    'quotationID' => $this->dsQuotation->getValue(DBEQuotation::quotationID)
                                )
                            );
                        $txtDelete = 'File Not Found: ' . CTSALESORDER_TXT_DELETE;
                    } else {
                        if (!$quoteSent) {
                            $sendQuoteDocURL =
                                Controller::buildLink(
                                    $_SERVER['PHP_SELF'],
                                    array(
                                        'action'      => CTSALESORDER_ACT_SEND_QUOTE_DOC,
                                        'quotationID' => $this->dsQuotation->getValue(DBEQuotation::quotationID)
                                    )
                                );
                            $flagAsSentQuoteDocURL =
                                Controller::buildLink(
                                    $_SERVER['PHP_SELF'],
                                    array(
                                        'action'      => CTSALESORDER_ACT_SEND_QUOTE_DOC,
                                        'quotationID' => $this->dsQuotation->getValue(DBEQuotation::quotationID),
                                        'flagAsSent'  => 1
                                    )
                                );
                            $deleteQuoteDocURL =
                                Controller::buildLink(
                                    $_SERVER['PHP_SELF'],
                                    array(
                                        'action'      => CTSALESORDER_ACT_DELETE_QUOTE_DOC,
                                        'quotationID' => $this->dsQuotation->getValue(DBEQuotation::quotationID)
                                    )
                                );
                            $txtDelete = CTSALESORDER_TXT_DELETE;
                            $txtSendQuote = CTSALESORDER_TXT_SEND;
                            $txtFlagAsSent = "Flag as sent";
                            if ($this->dsQuotation->getValue(DBEQuotation::documentType) == 'manualUpload') {
                                $txtSendQuote = null;
                            } elseif ($this->dsQuotation->getValue(DBEQuotation::documentType) != 'quotation') {
                                $txtFlagAsSent = null;
                            }


                            $quoteSentDateTime = 'Not sent';
                        } else {
                            if ($this->dsQuotation->getValue(
                                    DBEQuotation::fileExtension
                                ) == 'pdf' && $this->dsQuotation->getValue(
                                    DBEQuotation::documentType
                                ) == 'quotation' && $hasGreenArrow) {
                                $txtReminder = "Send Reminder";
                            }

                            if ($sentDateTime = strtotime($this->dsQuotation->getValue(DBEQuotation::sentDateTime))) {
                                $quoteSentDateTime = date(
                                    "d/m/Y H:i:s",
                                    $sentDateTime
                                );
                            }

                        }
                    }
                    $documentType = $this->dsQuotation->getValue(DBEQuotation::documentType);
                    $documentType = $documentType == 'manualUpload' ? 'Manual Upload' : $documentType;
                    $signableStatus = null;
                    if ($this->dsQuotation->getValue(DBEQuotation::signableEnvelopeID)) {
                        $dbeEnvelop = new DBESignableEnvelope($this);
                        $dbeEnvelop->getRow($this->dsQuotation->getValue(DBEQuotation::signableEnvelopeID));
                        $signableStatus = $dbeEnvelop->getValue(DBESignableEnvelope::status);

                        if ($signableStatus == "signed-envelope" || $signableStatus == "signed-envelope-complete") {
                            $txtReminder = null;
                        }

                        if ($dbeEnvelop->getValue(DBESignableEnvelope::downloadLink)) {
                            $signableStatus = "<a href='" . $dbeEnvelop->getValue(
                                    DBESignableEnvelope::downloadLink
                                ) . "'>$signableStatus</a>";
                        }

                    }

                    $separator = $txtFlagAsSent && $txtSendQuote ? ' - ' : null;

                    $this->template->set_var(
                        array(
                            'displayQuoteDocURL'    => $displayQuoteDocURL,
                            'separator'             => $separator,
                            'sendQuoteDocURL'       => $sendQuoteDocURL,
                            'flagAsSentQuoteDocURL' => $flagAsSentQuoteDocURL,
                            'txtFlagAsSent'         => $txtFlagAsSent,
                            'deleteQuoteDocURL'     => $deleteQuoteDocURL,
                            'txtSendQuote'          => $txtSendQuote,
                            'txtDelete'             => $txtDelete,
                            'quoteVersionNo'        => $this->dsQuotation->getValue(DBEQuotation::versionNo),
                            'quoteSentDateTime'     => $quoteSentDateTime,
                            'quoteUserName'         => $this->dsQuotation->getValue(DBEJQuotation::userName),
                            'documentType'          => $documentType,
                            "signableStatus"        => $signableStatus,
                            "txtReminder"           => $txtReminder,
                            'quotationID'           => $this->dsQuotation->getValue(DBEQuotation::quotationID)
                        )
                    );
                    $this->template->parse(
                        'quotations',
                        'quotationBlock',
                        true
                    );
                } // while ($this->dsQuotation->fetchNext());
                $this->template->parse(
                    'salesOrderDisplayQuotes',
                    'SalesOrderDisplayQuotes',
                    true
                );
            } // if ($thereAreQuoteDocuments)

            // convert to order button for quotes
            if ($orderType == 'Q') {
                $this->template->parse(
                    'salesOrderDisplayConvertToOrder',
                    'SalesOrderDisplayConvertToOrder',
                    true
                );
            }

            // if initial order and no purchase orders exist then show generate POs button
            if (
                ($orderType == 'I') &
                ($purchaseOrderCount == 0)
            ) {
                $urlCreatePO =
                    Controller::buildLink(
                        CTCNC_PAGE_PURCHASEORDER,
                        array(
                            'action'    => CTCNC_ACT_GENERATE_POS_FROM_SO,
                            'ordheadID' => $this->getOrdheadID()
                        )
                    );

                $project = new DBEProject($this);

                $project->setValue(
                    DBEProject::ordHeadID,
                    $dsOrdhead->getValue(DBEOrdhead::ordheadID)
                );
                $project->getRowsByColumn(DBEProject::ordHeadID);
                $requiredByDateValue = null;
                if ($project->fetchNext() && $project->getValue(DBEProject::commenceDate)) {
                    $requiredByDateValue = $project->getValue(DBEProject::commenceDate);
                }

                $this->template->set_var(
                    [
                        'urlCreatePO'         => $urlCreatePO,
                        "requiredByDateValue" => $requiredByDateValue
                    ]
                );
                $this->template->parse(
                    'salesOrderDisplayCreatePO',
                    'SalesOrderDisplayCreatePO',
                    true
                );
            }


            $this->buSalesOrder->initialiseNewOrdline(
                $this->getOrdheadID(),
                $this->dsOrdline
            );
            $_SESSION['urlReferer'] =                    // so called functions know where to come back to
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => $this->getAction(),
                        'ordheadID' => $this->getOrdheadID()
                    )
                );
            // So that focus is set on item field when page loads:
            $bodyTagExtras =
                'onLoad="window.moveTo(0,0);
				self.resizeTo(screen.availWidth, screen.availHeight);
				self.focus();
				document.forms[0].elements[6].focus();"';
            $this->template->set_var(
                'bodyTagExtras',
                $bodyTagExtras
            );
            $this->orderLineForm(
                $dsOrdhead,
                'SalesOrderDisplayNewLine'
            );
            if ($dsOrdhead->getValue(DBEOrdhead::type) !== 'C') {
                $this->template->parse(
                    'salesOrderDisplayNewLine',
                    'SalesOrderDisplayNewLine',
                    true
                );
            }
            $this->template->parse(
                'salesOrderLineEditJS',
                'SalesOrderLineEditJS',
                true
            );
            $this->template->parse(
                'salesOrderSiteEditJS',
                'SalesOrderSiteEditJS',
                true
            );
            // End Add new line section

        } // end if ( !$readOnly && !$restrictedView )

        if ($this->getOrdheadID()) {
            $this->documents(
                $this->getOrdheadID(),
                'SalesOrderDisplayDocuments'
            );
        }


        // if part despatched or complete show any delivery notes
        if (($orderType == 'P') or ($orderType == 'C')) {
            $buDespatch = new BUDespatch($this);
            $ctDeliveryNotes = new CTDeliveryNotes(
                $this,
                $this->getOrdheadID(),
                $buDespatch
            );
            $ctDeliveryNotes->execute();
        }
        // Show header details that dont apply to quotes - may be amended if initial
        if (
            ($orderType != 'Q') &
            (!common_isAnInternalStockLocation($dsOrdhead->getValue(DBEOrdhead::customerID)))
        ) {
            $this->template->parse(
                'salesOrderHeadAmend',
                'SalesOrderHeadAmend',
                true
            );
        }
        $this->template->parse(
            'salesOrderHeadDisplay',
            'SalesOrderHeadDisplay',
            true
        );
        $this->template->parse(
            'CONTENTS',
            'SalesOrderDisplay',
            true
        );
        $this->parsePage();
    }

    /**
     * Display header info
     * @access private
     * @param $dsOrdhead DBEOrdhead|DataSet
     * @throws Exception
     */
    function displaySalesOrderHeader(&$dsOrdhead)
    {
        $title = $this->getTypeDescription($dsOrdhead->getValue(DBEOrdhead::type));
        if ($dsOrdhead->getValue(DBEOrdhead::type) != 'Q') {
            $title .= ' Sales Order';
        }
        $this->setPageTitle($title);
        $originalQuoteURL =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => CTCNC_ACT_DISP_SALESORDER,
                    'ordheadID' => $dsOrdhead->getValue(DBEOrdhead::quotationOrdheadID)
                )
            );

        $customerDisplayURL =
            Controller::buildLink(
                'Customer.php',
                array(
                    'action'     => 'dispEdit',
                    'customerID' => $dsOrdhead->getValue(DBEOrdhead::customerID)
                )
            );
        $this->template->set_var(
            array(
                'customerDisplayURL' => $customerDisplayURL,
                'ordheadID'          => $dsOrdhead->getValue(DBEOrdhead::ordheadID),
                'fromOrdheadID'      => $this->getFromOrdheadID(),
                'customerID'         => $dsOrdhead->getValue(DBEOrdhead::customerID),
                'customerName'       => $dsOrdhead->getValue(DBEJOrdhead::customerName),
                'date'               => strftime(
                    "%d/%m/%Y",
                    strtotime($dsOrdhead->getValue(DBEOrdhead::date))
                ),
                'requestedDate'      => ($dsOrdhead->getValue(DBEOrdhead::requestedDate) ? $dsOrdhead->getValue(
                    DBEOrdhead::requestedDate
                ) : 'N/A'),
                'promisedDate'       => ($dsOrdhead->getValue(DBEOrdhead::promisedDate) ? $dsOrdhead->getValue(
                    DBEOrdhead::promisedDate
                ) : 'N/A'),
                'expectedDate'       => ($dsOrdhead->getValue(DBEOrdhead::expectedDate) ? $dsOrdhead->getValue(
                    DBEOrdhead::expectedDate
                ) : 'N/A'),
                'quotationOrdheadID' => substr(
                    $dsOrdhead->getValue(DBEOrdhead::quotationOrdheadID),
                    0,
                    30
                ),
                'originalQuoteURL'   => $originalQuoteURL,
                'custPORef'          => $dsOrdhead->getValue(DBEOrdhead::custPORef),
                'partInvoiceChecked' => $this->getChecked($dsOrdhead->getValue(DBEOrdhead::partInvoice)),
                'addItemChecked'     => $this->getChecked($dsOrdhead->getValue(DBEOrdhead::addItem)),
                'addCustomerItem'    => ($dsOrdhead->getValue(DBEOrdhead::addItem) == 'Y') ? 'Yes' : 'No',
                'vat'                => $dsOrdhead->getValue(DBEOrdhead::vatCode) . ' ' . $dsOrdhead->getValue(
                        DBEOrdhead::vatRate
                    ),
            )
        );
        // do payment method
        $dbePaymentTerms = new DBEPaymentTerms($this);
        $dbePaymentTerms->getRows();
        $this->template->set_block(
            'SalesOrderHeadAmend',
            'payMethodBlock',
            'payMethods'
        );
        while ($dbePaymentTerms->fetchNext()) {
            $payMethodSelected = ($dsOrdhead->getValue(DBEPaymentTerms::paymentTermsID) == $dbePaymentTerms->getValue(
                DBEPaymentTerms::paymentTermsID
            ) ? CT_SELECTED : null);
            $this->template->set_var(
                array(
                    'payMethodSelected' => $payMethodSelected,
                    'paymentTermsID'    => $dbePaymentTerms->getValue(DBEPaymentTerms::paymentTermsID),
                    'payMethodDesc'     => $dbePaymentTerms->getValue(DBEPaymentTerms::description)
                )
            );
            $this->template->parse(
                'payMethods',
                'payMethodBlock',
                true
            );
        }// foreach
    }

    function getFromOrdheadID()
    {
        return $this->fromOrdheadID;
    }

    function setFromOrdheadID($ID)
    {
        $this->setNumericVar(
            'fromOrdheadID',
            $ID
        );
    }

    function getLinesMessage()
    {
        return $this->linesMessage;
    }

    function setLinesMessage($message)
    {
        if (func_get_arg(0)) $this->setFormErrorOn();
        $this->linesMessage = $message;
    }

    function getCurrentDocumentsLink($customerID,
                                     &$buCustomer
    )
    {

        if (!$buCustomer) {
            $buCustomer = new BUCustomer($this);
        }
        $currentDocumentsLink = null;
        if ($buCustomer->customerFolderExists($customerID)) {
            $currentDocumentsPath = $buCustomer->checkCurrentDocumentsFolderExists($customerID);
            $currentDocumentsLink = '<a href="file:' . $currentDocumentsPath . '" target="_blank" title="Documentation">Documentation</a>';
        }
        return $currentDocumentsLink;
    }

    function renderLine($dsOrdline,
                        &$curSaleGrandTotal,
                        &$curProfitGrandTotal,
                        &$curCostGrandTotal,
                        &$percProfitGrandTotal,
                        &$recurringCurSaleGrandTotal,
                        &$recurringCurCostGrandTotal,
                        &$recurringCurProfitGrandTotal,
                        &$recurringPercProfitGrandTotal,
                        $buRenewal,
                        $readOnly,
                        $restrictedView,
                        $dsOrdhead
    )
    {
        $urlEditLine = null;
        $urlDeleteLine = null;
        $urlAddLine = null;
        $handle = 'orderLineBlock';
        $name = "orderLines";
        if ($dsOrdline->getValue(DBEOrdline::isRecurring)) {
            $handle = "recurringOrderLineBlock";
            $name = "recurringOrderLines";
        }
        $renewalIcon = null;
        if ($dsOrdline->getValue(DBEJOrdline::renewalTypeID)) {
            if (!$buRenewal) {
                $buRenewal = new BURenewal($this);
            }
            $buRenewal->getRenewalBusinessObject(
                $dsOrdline->getValue(DBEJOrdline::renewalTypeID),
                $page
            );

            $urlEditRenewal =
                Controller::buildLink(
                    $page,
                    array(
                        'action' => 'editFromSalesOrder',
                        'lineId' => $dsOrdline->getValue(DBEOrdline::id),
                    )
                );

            $createItem = true;
            $iconColor = 'red';
            if ($dsOrdline->getValue(DBEOrdline::renewalCustomerItemID)) {
                $iconColor = 'green';
                $createItem = false;
                $hasGreenArrow = true;
            }


            $renewalIcon =
                '<A HREF="' . $urlEditRenewal . '" target="_BLANK" onclick="checkCreation()" ' . ($createItem ? ' class="createItem" ' : null) . '>' .
                '<i class="fal fa-2x fa-step-forward" style="color: ' . $iconColor . '"></i>
                         </A>';
        }

        // if form error and there is a set of lines to redisplay then set the values accordingly
        if (!$readOnly && !$restrictedView) {

            $urlEditLine =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'      => CTSALESORDER_ACT_EDIT_ORDLINE,
                        'lineId'      => $dsOrdline->getValue(DBEOrdline::id),
                        'updatedTime' => $dsOrdhead->getValue(DBEOrdhead::updatedTime),
                    )
                );
            // common to comment and item lines
            $urlAddLine =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'      => CTSALESORDER_ACT_ADD_ORDLINE,
                        'lineId'      => $dsOrdline->getValue(DBEOrdline::id),
                        'updatedTime' => $dsOrdhead->getValue(DBEOrdhead::updatedTime),
                    )
                );


            $urlDeleteLine =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'      => CTSALESORDER_ACT_DELETE_ORDLINE,
                        'lineId'      => $dsOrdline->getValue(DBEOrdline::id),
                        'updatedTime' => $dsOrdhead->getValue(DBEOrdhead::updatedTime)
                    )
                );
            $salesOrderLineDesc =
                '<A href="' . $urlEditLine . '">' . Controller::htmlDisplayText(
                    $dsOrdline->getValue(DBEOrdline::description)
                ) . '</A>';
        } else {
            $salesOrderLineDesc = Controller::htmlDisplayText($dsOrdline->getValue(DBEOrdline::description));
        }

        // for javascript message remove all " and ' chars
        $removeDescription = str_replace(
            '"',
            '',
            $dsOrdline->getValue(DBEOrdline::description)
        );
        $removeDescription = str_replace(
            '\'',
            '',
            $removeDescription
        );

        $ordline = new DBEOrdline($this);

        $ordline->getRow($dsOrdline->getValue(DBEOrdline::id));

        $this->template->set_var(
            array(
                'salesOrderLineDesc' => $salesOrderLineDesc,
                'description'        => $dsOrdline->getValue(DBEOrdline::description),
                'qtyOrdered'         => number_format($dsOrdline->getValue(DBEOrdline::qtyOrdered), 2, '.', ''),
                'lineType'           => $dsOrdline->getValue(DBEOrdline::lineType),
                'partNo'             => Controller::htmlDisplayText($dsOrdline->getValue(DBEJOrdline::partNo)),
                'sequenceNo'         => $dsOrdline->getValue(DBEOrdline::sequenceNo),
                'lineId'             => $dsOrdline->getValue(DBEOrdline::id),
                'orderLineChecked'   => ($this->dsSelectedOrderLine->search(
                    'id',
                    $dsOrdline->getValue(DBEOrdline::id)
                )) ? CT_CHECKED : null,
                //                'urlMoveLineUp'      => $urlMoveLineUp,
                //                'urlMoveLineDown'    => $urlMoveLineDown,
                //                'moveUpHidden'       => $ordline->isFirst() ? 'hidden' : null,
                //                'moveDownHidden'     => $ordline->isLast() ? 'hidden' : null,
                'removeDescription'  => $removeDescription,
                'urlEditLine'        => $urlEditLine,
                'urlDeleteLine'      => $urlDeleteLine,
                'urlAddLine'         => $urlAddLine,
                'isRecurring'        => $dsOrdline->getValue(DBEOrdline::isRecurring) ? 'true' : 'false'
            )
        );
        if ($dsOrdline->getValue(
                DBEOrdline::lineType
            ) == "I") {                    // Item line needs all these fields

            $curSaleTotal = $dsOrdline->getValue(DBEOrdline::curUnitSale) * $dsOrdline->getValue(
                    DBEOrdline::qtyOrdered
                );
            $curCostTotal = $dsOrdline->getValue(DBEOrdline::curUnitCost) * $dsOrdline->getValue(
                    DBEOrdline::qtyOrdered
                );
            $curProfit = $curSaleTotal - $curCostTotal;
            if ($curCostTotal != 0) {
                $percProfit = $curProfit * (100 / $curCostTotal);
            } else {
                $percProfit = 100;
            }
            if ($dsOrdline->getValue(DBEJOrdline::webSiteURL)) {
                $supplierName = '<A HREF="' . $dsOrdline->getValue(
                        DBEJOrdline::webSiteURL
                    ) . '" target="_blank">' .
                    Controller::htmlDisplayText($dsOrdline->getValue(DBEJOrdline::supplierName)) . '</A>';
            } else {
                $supplierName = Controller::htmlDisplayText($dsOrdline->getValue(DBEJOrdline::supplierName));
            }

            if (!$restrictedView) {

                $this->template->set_var(
                    array(
                        'stockcat'                => $dsOrdline->getValue(DBEOrdline::stockcat),
                        'renewalIcon'             => $renewalIcon,
                        'lineSupplierName'        => $supplierName,
                        'curUnitCost'             => number_format(
                            $dsOrdline->getValue(DBEOrdline::curUnitCost),
                            2
                            ,
                            '.',
                            ''
                        ),
                        'curCostTotal'            => number_format($curCostTotal, 2),
                        'curUnitSale'             => number_format(
                            $dsOrdline->getValue(DBEOrdline::curUnitSale),
                            2,
                            '.',
                            ''
                        ),
                        'curSaleTotal'            => number_format($curSaleTotal, 2),
                        'curProfit'               => number_format($curProfit, 2),
                        'percProfit'              => number_format($percProfit, 2),
                        'orderLineProfitClass'    => ($curProfit < 0) ? CTSALESORDER_CLS_ORDER_LINE_LOSS : CTSALESORDER_CLS_ORDER_LINE_ITEM,
                        'orderLineSaleTotalClass' => ($curSaleTotal < 0) ? CTSALESORDER_CLS_ORDER_LINE_LOSS : CTSALESORDER_CLS_ORDER_LINE_ITEM,
                        'orderLineCostTotalClass' => ($curCostTotal < 0) ? CTSALESORDER_CLS_ORDER_LINE_LOSS : CTSALESORDER_CLS_ORDER_LINE_ITEM
                    )
                );

                if (!$dsOrdline->getValue(DBEOrdline::isRecurring)) {
                    $curSaleGrandTotal += $curSaleTotal;
                    $curProfitGrandTotal += $curProfit;
                    $curCostGrandTotal += $curCostTotal;
                } else {
                    $recurringCurSaleGrandTotal += $curSaleTotal;
                    $recurringCurCostGrandTotal += $curProfit;
                    $recurringCurProfitGrandTotal += $curCostTotal;

                }

                if (!$readOnly) {

                    $urlUpdateItemPrice =
                        Controller::buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'      => 'updateItemPrice',
                                'ordheadID'   => $this->getOrdheadID(),
                                'itemID'      => $dsOrdline->getValue(DBEOrdline::itemID),
                                'curUnitCost' => $dsOrdline->getValue(DBEOrdline::curUnitCost),
                                'curUnitSale' => $dsOrdline->getValue(DBEOrdline::curUnitSale)
                            )
                        );
                    $this->template->set_var(
                        'urlUpdateItemPrice',
                        $urlUpdateItemPrice
                    );
                    $this->template->parse(
                        'salesOrderLineUpdateItemPriceIcon',
                        'SalesOrderLineUpdateItemPriceIcon',
                        true
                    );
                    $this->template->parse(
                        'salesOrderLineIcons',
                        'SalesOrderLineIcons',
                        true
                    );

                }
            }
            $this->template->parse(
                'salesOrderLine',
                'SalesOrderItemLine'
            );
        } else {
            if (!$readOnly) {
                $this->template->parse(
                    'salesOrderLineIcons',
                    'SalesOrderLineIcons',
                    true
                );
            }
            $this->template->parse(
                'salesOrderLine',
                'SalesOrderCommentLine'
            );
        }
        $this->template->parse(
            $name,
            $handle,
            true
        );
        $this->template->set_var(
            'salesOrderAddIcon',
            null
        ); // clears for next time
        $this->template->set_var(
            'salesOrderLineIcons',
            null
        ); // clears for next time
        $this->template->set_var(
            'salesOrderLineUpdateItemPriceIcon',
            null
        ); // clears for next time
    }

    function getSalutation()
    {
        return $this->salutation;
    }

    function setSalutation($salutation)
    {
        $this->salutation = $salutation;
    }

    function getIntroduction()
    {
        return $this->introduction;
    }

    function setIntroduction($text)
    {
        $this->introduction = $text;
    }

    function getEmailSubject()
    {
        return $this->emailSubject;
    }

    function setEmailSubject($message)
    {
        $this->emailSubject = $message;
    }

    function getUserMessage()
    {
        return $this->userMessage;
    }

    function setUserMessage($message)
    {
        if (func_get_arg(0)) $this->setFormErrorOn();
        $this->userMessage = $message;
    }

    function getUploadUserMessage()
    {
        return $this->uploadUserMessage;
    }

    function setUploadUserMessage($message)
    {
        if (func_get_arg(0)) $this->setFormErrorOn();
        $this->uploadUserMessage = $message;
    }

    function getQuoteFileMessage()
    {
        return $this->quoteFileMessage;
    }

    function setQuoteFileMessage($message)
    {
        if (func_get_arg(0)) $this->setFormErrorOn();
        $this->quoteFileMessage = $message;
    }

    /**
     * @param $salesOrderId
     * @return bool
     * @throws Exception
     */
    function checkMonitoringSalesOrder($salesOrderId): bool
    {
        global $db;
        $result = $db->preparedQuery(
            'select 1 from salesOrderMonitor where userId = ? and salesOrderId = ? ',
            [
                ["type" => "i", "value" => $this->userID],
                ["type" => "i", "value" => $salesOrderId]
            ]
        );
        return (bool)$result->num_rows;
    }

    /**
     * @param $dsQuotation DataSet
     * @return bool
     */
    private function checkQuoteDocFile($dsQuotation)
    {
        $quoteFile = $dsQuotation->getValue(DBEQuotation::ordheadID) . '_' . $dsQuotation->getValue(
                DBEQuotation::versionNo
            );
        if (!$dsQuotation->getValue(DBEQuotation::fileExtension)) {
            $quoteFile .= '.pdf';
        } else {
            $quoteFile .= '.' . $this->dsQuotation->getValue(
                    DBEQuotation::fileExtension
                );        // if no extension in DB then assume PDF
        }
        return file_exists('quotes/' . $quoteFile);
    }

    /**
     * @param $dsOrdhead DBEOrdhead|DataSet
     * @param string $parentPage
     * @throws Exception
     */
    function orderLineForm(&$dsOrdhead,
                           $parentPage = 'SalesOrderLineEdit'
    )
    {
        // Lines
        if ($this->dsOrdline->getValue(DBEJOrdline::lineType) != "I") {                    // Comment line
            $this->template->set_var(
                array(
                    'stockcat'     => null,
                    'itemID'       => null,
                    'description'  => htmlspecialchars($this->dsOrdline->getValue(DBEJOrdline::description)),
                    'supplierName' => null,
                    'supplierID'   => null,
                    'qtyOrdered'   => null,
                    'curUnitCost'  => null,
                    'curUnitSale'  => null
                )
            );
        } else {                                                                                                // Item line
            $this->template->set_var(
                array(
                    'stockcat'              => $this->dsOrdline->getValue(DBEJOrdline::stockcat),
                    'itemID'                => $this->dsOrdline->getValue(DBEJOrdline::itemID),
                    'description'           => htmlspecialchars($this->dsOrdline->getValue(DBEJOrdline::description)),
                    'supplierName'          => htmlspecialchars($this->dsOrdline->getValue(DBEJOrdline::supplierName)),
                    'supplierID'            => $this->dsOrdline->getValue(DBEJOrdline::supplierID),
                    'qtyOrdered'            => $this->dsOrdline->getValue(DBEJOrdline::qtyOrdered),
                    'curUnitCost'           => $this->dsOrdline->getValue(DBEJOrdline::curUnitCost),
                    'curUnitSale'           => $this->dsOrdline->getValue(DBEJOrdline::curUnitSale),
                    'renewalCustomerItemID' => $this->dsOrdline->getValue(DBEJOrdline::renewalCustomerItemID)
                )
            );
        }
        if (($this->formError) && ($parentPage == 'SalesOrderLineEdit')) {
            $this->template->set_var(
                array(
                    'descriptionMessage'  => $this->dsOrdline->getValue(self::descriptionMessage),
                    'supplierNameMessage' => $this->dsOrdline->getValue(self::supplierNameMessage),
                    'qtyOrderedMessage'   => $this->dsOrdline->getValue(self::qtyOrderedMessage),
                    'curUnitCostMessage'  => $this->dsOrdline->getValue(self::curUnitCostMessage),
                    'curUnitSaleMessage'  => $this->dsOrdline->getValue(self::curUnitSaleMessage)
                )
            );
        }
        if ($this->getAction() == CTSALESORDER_ACT_EDIT_ORDLINE) {
            $urlSubmit =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSALESORDER_ACT_UPDATE_ORDLINE
                    )
                );
        } else {
            $urlSubmit =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSALESORDER_ACT_INSERT_ORDLINE
                    )
                );
        }
        $urlCancel = $this->getDisplayOrderURL();
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

        $this->template->set_var(
            array(
                'sequenceNo'       => $this->dsOrdline->getValue(DBEOrdline::sequenceNo),
                'ordheadID'        => $this->dsOrdline->getValue(DBEOrdline::ordheadID),
                'lineId'           => $this->dsOrdline->getValue(DBEOrdline::id),
                'urlSubmit'        => $urlSubmit,
                'urlItemPopup'     => $urlItemPopup,
                'urlItemEdit'      => $urlItemEdit,
                'urlSupplierPopup' => $urlSupplierPopup,
                'urlSupplierEdit'  => $urlSupplierEdit,
                'urlCancel'        => $urlCancel,
                'updatedTime'      => $dsOrdhead->getValue(DBEOrdhead::updatedTime),
                'isRecurring'      => $this->dsOrdline->getValue(DBEOrdline::isRecurring) ? '1' : '',
            )
        );
        if ($parentPage == 'SalesOrderLineEdit') {
            $lineTypeArray =
                array(
                    "I" => "Item",
                    "C" => "Comment"
                );
        } else {                                    // Allow Sales Order Only When Using main page to add items
            $lineTypeArray =
                array(
                    "I" => "Item",
                    "C" => "Comment",
                    "T" => "Template",
                    "O" => "Insert From Order",
                );
        }

        $this->template->set_block(
            $parentPage,
            'lineTypeBlock',
            'lineTypes'
        );
        foreach ($lineTypeArray as $key => $value) {
            $lineTypeSelected = ($this->dsOrdline->getValue(DBEOrdline::lineType) == $key) ? CT_SELECTED : null;
            $this->template->set_var(
                array(
                    'lineTypeSelected' => $lineTypeSelected,
                    'lineType'         => $key,
                    'lineTypeDesc'     => $value
                )
            );
            $this->template->parse(
                'lineTypes',
                'lineTypeBlock',
                true
            );
        }
    }

    /**
     * @param $ordheadID
     * @param $templateName
     * @throws Exception
     */
    function documents($ordheadID,
                       $templateName
    )
    {
        $this->template->set_block(
            $templateName,
            'salesOrderDocumentBlock',
            'salesOrderDocuments'
        );

        $buSalesOrderDocument = new BUSalesOrderDocument($this);
        $dsSalesOrderDocument = new DataSet($this);
        $buSalesOrderDocument->getDocumentsByOrdheadID(
            $ordheadID,
            $dsSalesOrderDocument
        );

        $urlAddDocument =
            Controller::buildLink(
                'SalesOrderDocument.php',
                array(
                    'action'    => 'add',
                    'ordheadID' => $ordheadID
                )
            );


        $this->template->set_var(
            array(
                'txtAddDocument' => 'Add document',
                'urlAddDocument' => $urlAddDocument
            )
        );

        while ($dsSalesOrderDocument->fetchNext()) {

            $urlEditDocument =
                Controller::buildLink(
                    'SalesOrderDocument.php',
                    array(
                        'action'               => 'edit',
                        'salesOrderDocumentID' => $dsSalesOrderDocument->getValue(
                            DBESalesOrderDocument::salesOrderDocumentID
                        )
                    )
                );

            $urlViewFile =
                Controller::buildLink(
                    'SalesOrderDocument.php',
                    array(
                        'action'               => 'viewFile',
                        'salesOrderDocumentID' => $dsSalesOrderDocument->getValue(
                            DBESalesOrderDocument::salesOrderDocumentID
                        )
                    )
                );

            $urlDeleteDocument =
                Controller::buildLink(
                    'SalesOrderDocument.php',
                    array(
                        'action'               => 'delete',
                        'salesOrderDocumentID' => $dsSalesOrderDocument->getValue(
                            DBESalesOrderDocument::salesOrderDocumentID
                        )
                    )
                );

            $createdDate = null;
            if ($dsSalesOrderDocument->getValue(DBESalesOrderDocument::createdDate)) {
                $createdDate = date_format(
                    date_create($dsSalesOrderDocument->getValue(DBESalesOrderDocument::createdDate)),
                    'd/m/Y H:i:s'
                );
            }

            $this->template->set_var(
                array(
                    'description'       => $dsSalesOrderDocument->getValue(DBESalesOrderDocument::description),
                    'filename'          => $dsSalesOrderDocument->getValue(DBESalesOrderDocument::filename),
                    'createdDate'       => $createdDate,
                    'urlViewFile'       => $urlViewFile,
                    'urlEditDocument'   => $urlEditDocument,
                    'urlDeleteDocument' => $urlDeleteDocument,
                )
            );
            $this->template->parse(
                'salesOrderDocuments',
                'salesOrderDocumentBlock',
                true
            );

        } // end while

        $this->template->parse(
            'salesOrderDisplayDocuments',
            'SalesOrderDisplayDocuments',
            true
        );
    }

    /**
     * send quote.
     * @access private
     * @throws Exception
     */
    function sendQuoteDoc()
    {
        $this->setMethodName('sendQuoteDoc');
        if (!$this->getQuotationID()) {
            $this->displayFatalError(CTSALESORDER_MSG_QUOTEID_NOT_PASSED);
            return;
        }
        if (!$this->buSalesOrder->getQuoteByID(
            $this->getQuotationID(),
            $this->dsQuotation
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_QUOTE_NOT_FOUND);
            return;
        }
        $flagAsSent = $this->getParam('flagAsSent');
        $this->dsQuotation->fetchNext();
        $updateDB = TRUE;
        // if this is a PDF file then send an email to the customer else simply st the sent date.
        if ($this->dsQuotation->getValue(DBEQuotation::documentType) == 'quotation' && !$flagAsSent) {
            $buPDFSalesQuote = new BUPDFSalesQuote($this);
            $updateDB = $buPDFSalesQuote->sendPDFEmailQuote($this->getQuotationID());
        }
        if ($updateDB) {
            $this->dsQuotation->setUpdateModeUpdate();
            $this->dsQuotation->setValue(
                DBEQuotation::sentDateTime,
                date('Y-m-d H:i:s')
            );
            $this->dsQuotation->post();
            $this->buSalesOrder->insertQuotation($this->dsQuotation);
        }
        $this->setOrdheadID($this->dsQuotation->getValue(DBEQuotation::ordheadID));
        header('Location: ' . $this->getDisplayOrderURL());
        exit;
    }

    function getQuotationID()
    {
        return $this->quotationID;
    }

    function setQuotationID($quotationID)
    {
        $this->setNumericVar(
            'quotationID',
            $quotationID
        );
    } // End function Display Sales Order Header

    /**
     * delete a quote document
     * @access private
     * @throws Exception
     */
    function deleteQuoteDoc()
    {
        $this->setMethodName('deleteQuoteDoc');
        if (!$this->getQuotationID()) {
            $this->displayFatalError(CTSALESORDER_MSG_QUOTEID_NOT_PASSED);
            return;
        }
        if (!$this->buSalesOrder->getQuoteByID(
            $this->getQuotationID(),
            $this->dsQuotation
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_QUOTE_NOT_FOUND);
            return;
        }
        $this->setOrdheadID($this->dsQuotation->getValue(DBEQuotation::ordheadID));
        $quoteFile =
            'quotes/' .
            $this->dsQuotation->getValue(DBEQuotation::ordheadID) . '_' . $this->dsQuotation->getValue(
                DBEQuotation::versionNo
            ) . '.' .
            $this->dsQuotation->getValue(DBEQuotation::fileExtension);
        $this->buSalesOrder->deleteQuotationDoc($this->getQuotationID());
        unlink($quoteFile);
        header('Location: ' . $this->getDisplayOrderURL());
        exit;
    }

    /**
     * Update order lines details
     * This is when the Update button has been clicked from the SalesOrderDisplay screen
     * and processes an array of order line information:
     * Qty, Unit Cost and Unit Sale.
     * @access private
     * @throws Exception
     */
    function updateLines()
    {
        $this->setMethodName('updateLines');
        if (!$this->getParam('ordheadID')) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return;
        } else {
            $this->setOrdheadID($this->getParam('ordheadID'));
        }
        $dsOrdhead = new DataSet($this);
        if (!$this->buSalesOrder->getOrdheadByID(
            $this->getOrdheadID(),
            $dsOrdhead
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDER_NOT_FND);
            return;
        }
        $this->checkUpdatedByAnotherUser(
            $this->getParam('updatedTime'),
            $dsOrdhead->getValue(DBEOrdhead::updatedTime)
        );
        if (($dsOrdhead->getValue(DBEOrdhead::type) != 'Q') && ($dsOrdhead->getValue(DBEOrdhead::type) != 'I')) {
            $this->displayFatalError(CTSALESORDER_MSG_MUST_BE_QUOTE_OR_INITIAL);
            return;
        }
        $dbeJOrdline = new DBEJOrdline($this);
        $this->dsOrdline = new DataSet($this);
        $this->dsOrdline->copyColumnsFrom($dbeJOrdline);
        var_dump($this->getParam('ordline'));
        exit;
        if (!$this->dsOrdline->populateFromArray($this->getParam('ordline'))) {
            $this->lineValidationError = 'One or more order line values are invalid';
            $this->displayOrder();
        } else {
            // update the order
            $this->buSalesOrder->updateOrderLineValues(
                $this->getOrdheadID(),
                $this->dsOrdline
            );
            header('Location: ' . $this->getDisplayOrderURL());
        }
    }

    /**
     * This function deals with the case where another user has updated the order we are attempting
     * to update.
     * $pageDate has been POSTED from the page and indicates the version of the record we intend to change
     * if, however, the orderDate is different then another user has already updated the record and so
     * we must not. Instead, we display a message and load the latest version of the record.
     * @access private
     * @param $pageDate
     * @param $orderDate
     * @throws Exception
     */
    function checkUpdatedByAnotherUser($pageDate,
                                       $orderDate
    )
    {
        $this->setMethodName('checkUpdatedByAnotherUser');
        if ($pageDate != $orderDate) {
            $this->setFormErrorMessage(
                '** ANOTHER USER HAS UPDATED THE ORDER **. Your action was abandoned and the latest version is now shown on this page'
            );
            $this->displayOrder();
            exit;
        }
    }

    /**
     * Convert quote to order
     *
     * 1. If all lines have been selected using checkboxes and Convert To Order
     * button has been pressed then the status of the quote is set to Initial Order.
     *
     * 2. If Copy To Order button has been pressed then a new Initial Order is created with the
     * lines selected. The quote is left intact.
     *
     * 3. If some lines have been selected using checkboxes and Convert To Order
     * button has been pressed then do 2.
     *
     * @access private
     * @throws Exception
     */
    function convertToOrder()
    {
        if (!$this->getOrdheadID()) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return false;
        }
        if (count($this->postVars['selectedOrderLine']) == 0) {
            $this->setLinesMessage(CTSALESORDER_MSG_NO_LINES);
            $this->displayOrder();
            return FALSE;
        } else {
            $this->setSelectedOrderLines($this->postVars['selectedOrderLine']);
        }
        $convertToOrder = ($this->getAction() == CTSALESORDER_ACT_CONVERT_TO_ORDER); // determine action to take below
        $this->setOrdheadID(
            $this->buSalesOrder->convertQuoteToOrder(
                $this->getOrdheadID(),
                $convertToOrder,
                $this->dsSelectedOrderLine
            )
        );
        header('Location: ' . $this->getDisplayOrderURL());
        exit;
    }// end function orderLineForm()

    function setSelectedOrderLines($array)
    {
        if (!is_array($array)) {
            return FALSE;
        }
        foreach ($array as $value) {
            $this->dsSelectedOrderLine->setUpdateModeInsert();
            $this->dsSelectedOrderLine->setValue(DBEOrdline::id, $value);
            $this->dsSelectedOrderLine->post();
        }
        return TRUE;
    }

    /**
     * @return bool
     * @throws Exception
     */
    function insertFromOrder()
    {
        if (!$this->getOrdheadID()) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return false;
        }
        if (!$this->getFromOrdheadID()) {
            $this->setLinesMessage('No From Sales Order entered');
            $this->displayOrder();
            return false;
        }

        if (!$this->buSalesOrder->getOrdheadByID(
            $this->getFromOrdheadID(),
            $dsOrdhead
        )) {
            $this->setLinesMessage('From Sales Order not found');
            $this->displayOrder();
            return false;
        }
        $sequenceNo = null;
        if (isset($this->postVars['selectedOrderLine']) && count($this->postVars['selectedOrderLine'])) {
            $lastLineId = $this->postVars['selectedOrderLine'][count($this->postVars['selectedOrderLine']) - 1];
            $dbeOrdline = new DBEOrdline($this);
            $dbeOrdline->getRow($lastLineId);
            $sequenceNo = $dbeOrdline->getValue(DBEOrdline::sequenceNo);
        }

        $this->buSalesOrder->pasteLinesFromOrder(
            $this->getFromOrdheadID(),
            $this->getOrdheadID(),
            $sequenceNo
        );
        header('Location: ' . $this->getDisplayOrderURL());
        exit;
    }

    /**
     * Delete order lines
     *    Deletes selected lines from order
     * @access private
     * @throws Exception
     */
    function deleteLines()
    {
        if (!$this->getOrdheadID()) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return false;
        }
        if (!$this->buSalesOrder->getOrderWithCustomerName(
            $this->getOrdheadID(),
            $dsOrdhead,
            $dsOrdline,
            $dsDeliveryContact
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDER_NOT_FND);
            return false;
        }
        if (count($this->postVars['selectedOrderLine']) == 0) {
            $this->setLinesMessage(CTSALESORDER_MSG_NO_LINES);
            $this->displayOrder();
            return FALSE;
        } else {
            $this->setSelectedOrderLines($this->postVars['selectedOrderLine']);
        }
        $this->buSalesOrder->deleteLines(
            $this->getOrdheadID(),
            $this->dsSelectedOrderLine
        );
        header('Location: ' . $this->getDisplayOrderURL());
        exit;
    }

    /**
     * @return bool|void
     * @throws Exception
     */
    function changeSupplier()
    {
        if (!$this->getOrdheadID()) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return false;
        }
        if (!$this->getUpdateSupplierID()) {
            $this->setLinesMessage('Supplier not set');
            $this->displayOrder();
            return FALSE;
        }
        if (!$this->buSalesOrder->getOrderWithCustomerName(
            $this->getOrdheadID(),
            $dsOrdhead,
            $dsOrdline,
            $dsDeliveryContact
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDER_NOT_FND);
            return;
        }
        if (count($this->postVars['selectedOrderLine']) == 0) {
            $this->setLinesMessage(CTSALESORDER_MSG_NO_LINES);
            $this->displayOrder();
            return FALSE;
        } else {
            $this->setSelectedOrderLines($this->postVars['selectedOrderLine']);
        }
        $this->buSalesOrder->changeSupplier(
            $this->getOrdheadID(),
            $this->getUpdateSupplierID(),
            $this->dsSelectedOrderLine
        );
        header('Location: ' . $this->getDisplayOrderURL());
    }

    function getUpdateSupplierID()
    {
        return $this->updateSupplierID;
    }

    function setUpdateSupplierID($updateSupplierID)
    {
        $this->setNumericVar(
            'updateSupplierID',
            $updateSupplierID
        );
    }

    /**
     * generate a PDF order form.
     * @access private
     * @param bool $isESigned
     * @return bool
     * @throws Exception
     */
    function generateOrderForm()
    {
        $this->setMethodName('generateOrderForm');
        if (count($this->postVars['selectedOrderLine']) == 0) {
            $this->setLinesMessage(CTSALESORDER_MSG_NO_LINES);
            $this->displayOrder();
            return FALSE;
        } else {
            $this->setSelectedOrderLines($this->postVars['selectedOrderLine']);
        }
        if (!$this->getOrdheadID()) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return FALSE;
        }
        $this->buildOrderForm();
        header('Location: ' . $this->getDisplayOrderURL());
        exit;
    }

    /**
     *
     */
    function buildOrderForm()
    {
        $dsOrdhead = new DataSet($this);
        $dsOrdline = new DataSet($this);
        $dsDeliveryContact = new DataSet($this);
        if (!$this->buSalesOrder->getOrderWithCustomerName(
            $this->getOrdheadID(),
            $dsOrdhead,
            $dsOrdline,
            $dsDeliveryContact
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDER_NOT_FND);
        }
        $versionNo = $this->buSalesOrder->getNextQuoteVersion($this->getOrdheadID());
        $orderFile = 'quotes/' . $this->getOrdheadID() . '_' . $versionNo . '.pdf';
        $this->buSalesOrder->getUserByID(
            $this->userID,
            $this->dsUser
        );

        $buPDF = new BUPDF(
            $this,
            $orderFile,
            $this->dsUser->getValue(DBEUser::name),
            $this->getOrdheadID() . '/' . $versionNo,
            'CNC Ltd',
            'Customer Order Form'
        );

        $buPDF->startPage();

        define(
            'QTY_LEFT',
            11
        );
        define(
            'QTY_WIDTH',
            28
        );
        define(
            'DETAILS_WIDTH',
            90
        );
        define(
            'UNIT_WIDTH',
            28
        );
        define(
            'TOTAL_WIDTH',
            28
        );

        define(
            'DETAILS_LEFT',
            QTY_LEFT +
            QTY_WIDTH
        );
        define(
            'UNIT_LEFT',
            QTY_LEFT +
            QTY_WIDTH +
            DETAILS_WIDTH
        );
        define(
            'TOTAL_LEFT',
            QTY_LEFT +
            QTY_WIDTH +
            DETAILS_WIDTH +
            UNIT_WIDTH
        );
        define(
            'ALL_WIDTH',
            QTY_WIDTH +
            DETAILS_WIDTH +
            UNIT_WIDTH +
            TOTAL_WIDTH
        );

        $buPDF->setBoldOff();
        $buPDF->setFontSize(10);
        $buPDF->setFontFamily(BUPDF_FONT_ARIAL);
        $buPDF->setFont();
        $buPDF->printStringAt(
            110,
            'From:'
        );
        $firstName = $dsDeliveryContact->getValue(DBEContact::firstName);
        $buPDF->printStringAt(
            130,
            $dsDeliveryContact->getValue(DBEContact::title) . ' ' . $firstName{0} . ' ' . $dsDeliveryContact->getValue(
                DBEContact::lastName
            )
        );
        $buPDF->CR();
        $buPDF->printStringAt(
            130,
            $dsOrdhead->getValue(DBEJOrdhead::customerName)
        );
        $buPDF->CR();
        $buPDF->printStringAt(
            130,
            $dsOrdhead->getValue(DBEOrdhead::delAdd1)
        );
        if ($dsOrdhead->getValue(DBEOrdhead::delAdd2)) {
            $buPDF->CR();
            $buPDF->printStringAt(
                130,
                $dsOrdhead->getValue(DBEOrdhead::delAdd2)
            );
        }
        if ($dsOrdhead->getValue(DBEOrdhead::delAdd3)) {
            $buPDF->CR();
            $buPDF->printStringAt(
                130,
                $dsOrdhead->getValue(DBEOrdhead::delAdd3)
            );
        }
        $buPDF->CR();
        $buPDF->printStringAt(
            130,
            $dsOrdhead->getValue(DBEOrdhead::delTown)
        );
        if ($dsOrdhead->getValue(DBEOrdhead::delCounty)) {
            $buPDF->CR();
            $buPDF->printStringAt(
                130,
                $dsOrdhead->getValue(DBEOrdhead::delCounty)
            );
        }
        $buPDF->CR();
        $buPDF->printStringAt(
            130,
            $dsOrdhead->getValue(DBEOrdhead::delPostcode)
        );
        $buPDF->CR();
        $buPDF->printString(
            $this->dsUser->getValue(DBEUser::firstName) . ' ' . $this->dsUser->getValue(DBEUser::lastName)
        );
        $buPDF->CR();
        $buHeader = new BUHeader($this);
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);
        $buPDF->printString($dsHeader->getValue(DBEHeader::name));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue(DBEHeader::add1));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue(DBEHeader::add2));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue(DBEHeader::add3));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue(DBEHeader::town));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue(DBEHeader::county));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue(DBEHeader::postcode));
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString(date('l, jS F Y'));
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('Dear ' . $this->dsUser->getValue(DBEUser::firstName) . ',');
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString(
            'Please accept this as official confirmation that we wish to proceed with the supply and installation of the following equipment and services as per your reference ' . $this->getOrdheadID(
            ) . '/' . $versionNo
        );
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->setBoldOn();
        $buPDF->setFont();
        $boxTop = $buPDF->getYPos();
        $buPDF->printStringRJAt(
            28,
            'Qty'
        );
        $buPDF->box(
            QTY_LEFT,
            $boxTop,
            ALL_WIDTH,
            $buPDF->getFontSize() / 2
        );
        $buPDF->printStringAt(
            40,
            'Details'
        );
        $buPDF->printStringRJAt(
            150,
            'Unit'
        );
        $buPDF->printStringRJAt(
            173,
            'Total'
        );
        $buPDF->setBoldOff();
        $buPDF->setFont();
        $buPDF->CR();
        $dsOrdline->initialise();

        $grand_total = 0;

        while ($dsOrdline->fetchNext()) {
            if ($this->dsSelectedOrderLine->search(
                DBEOrdline::id,
                $dsOrdline->getValue(DBEOrdline::id)
            )) {
                if ($dsOrdline->getValue(DBEOrdline::lineType) == "I") {
                    $buPDF->printStringRJAt(
                        28,
                        $dsOrdline->getValue(DBEOrdline::qtyOrdered)
                    );
                    if ($dsOrdline->getValue(DBEOrdline::description)) {
                        $buPDF->printStringAt(
                            40,
                            $dsOrdline->getValue(DBEOrdline::description)
                        );
                    } else {
                        $buPDF->printStringAt(
                            40,
                            $dsOrdline->getValue(DBEJOrdline::itemDescription)
                        );
                    }
                    $buPDF->printStringRJAt(
                        150,
                        Controller::formatNumberCur($dsOrdline->getValue(DBEOrdline::curUnitSale))
                    );
                    $total = ($dsOrdline->getValue(DBEOrdline::curUnitSale) * $dsOrdline->getValue(
                            DBEOrdline::qtyOrdered
                        ));
                    $grand_total += $total;
                    $buPDF->printStringRJAt(
                        173,
                        Controller::formatNumberCur($total)
                    );
                    if ($dsOrdline->getValue(DBEOrdline::itemID)) {
                        // some item lines in old system did not have a related item record
                        $this->buItem->getItemByID(
                            $dsOrdline->getValue(DBEOrdline::itemID),
                            $dsItem
                        );
                    }
                } else {
                    $buPDF->printStringAt(
                        40,
                        $dsOrdline->getValue(DBEOrdline::description)
                    ); // comment line
                }
                $buPDF->box(
                    QTY_LEFT,
                    $buPDF->getYPos(),
                    ALL_WIDTH,
                    $buPDF->getFontSize() / 2
                );
                $buPDF->CR();
            }
        }
        //$buPDF->box(QTY_LEFT, $boxTop, QTY_WIDTH, $buPDF->getYPos() - $boxTop);
        //$buPDF->box(UNIT_LEFT, $boxTop, UNIT_WIDTH, $buPDF->getYPos() - $boxTop);
        //$buPDF->box(TOTAL_LEFT, $boxTop, TOTAL_WIDTH, $buPDF->getYPos() - $boxTop);

        $buPDF->setBoldOn();
        $buPDF->setFont();
        $buPDF->printStringAt(
            UNIT_LEFT,
            'Grand total'
        ); // comment line
        //$buPDF->box(TOTAL_LEFT, $buPDF->getYPos(), TOTAL_WIDTH, $buPDF->getFontSize()/2);
        $buPDF->printStringRJAt(
            173,
            Controller::formatNumberCur($grand_total)
        );

        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printStringRJAt(
            UNIT_LEFT - 2,
            'Our official order no:'
        );
        //$buPDF->box(UNIT_LEFT, $buPDF->getYPos(), UNIT_WIDTH * 2, $buPDF->getFontSize()/2);
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printStringRJAt(
            UNIT_LEFT - 2,
            'Name:'
        );
        //$buPDF->box(UNIT_LEFT, $buPDF->getYPos(), UNIT_WIDTH * 2, $buPDF->getFontSize()/2);
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printStringRJAt(
            UNIT_LEFT - 2,
            'Signed:'
        );
        //$buPDF->box(UNIT_LEFT, $buPDF->getYPos(), UNIT_WIDTH * 2, $buPDF->getFontSize()/2);
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printStringRJAt(
            UNIT_LEFT - 2,
            'Date:'
        );
        //$buPDF->box(UNIT_LEFT, $buPDF->getYPos(), UNIT_WIDTH * 2, $buPDF->getFontSize()/2);
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printStringRJAt(
            UNIT_LEFT - 2,
            'Position:'
        );
        //$buPDF->box(UNIT_LEFT, $buPDF->getYPos(), UNIT_WIDTH * 2, $buPDF->getFontSize()/2);
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->setBoldOn();
        $buPDF->printString('All prices are subject to VAT at the standard rate.');
        $buPDF->setBoldOff();
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->CR();
        $pkValue = null;
        $buPDF->printString(
            'Please return a signed copy to sales@cnc-ltd.co.uk'
        );
        $buPDF->endPage();
        // End of second page
        $buPDF->close();

        // Insert into database
        $this->dsQuotation = new DataSet($this);
        $this->dsQuotation->copyColumnsFrom($this->buSalesOrder->dbeQuotation);
        $this->dsQuotation->setUpdateModeInsert();

        $this->dsQuotation->setValue(
            DBEQuotation::versionNo,
            $versionNo
        );
        $this->dsQuotation->setValue(
            DBEQuotation::ordheadID,
            $dsOrdhead->getValue(DBEOrdhead::ordheadID)
        );
        $this->dsQuotation->setValue(
            DBEQuotation::userID,
            $this->userID
        );
        $this->dsQuotation->setValue(
            DBEQuotation::sentDateTime,
            null
        );
        $this->dsQuotation->setValue(
            DBEQuotation::salutation,
            $this->getSalutation()
        );
        $this->dsQuotation->setValue(
            DBEQuotation::emailSubject,
            $this->getEmailSubject()
        );
        $this->dsQuotation->setValue(
            DBEQuotation::documentType,
            'order form'
        );
        $this->dsQuotation->setValue(
            DBEQuotation::fileExtension,
            'pdf'
        );
        $this->dsQuotation->post();
        $this->buSalesOrder->insertQuotation($this->dsQuotation);


    }

    /**
     * generate a PDF quote.
     * @access private
     * @throws Exception
     */
    function generateQuoteDoc()
    {

        if (!$this->hasPermissions(SALES_PERMISSION)) {
            throw new \CNCLTD\Exceptions\JsonHttpException(403, 'You do not have enough permissions to proceed');
        }

        $data = $this->getJSONData();


        $this->setMethodName('generateQuoteDoc');
        if (!array_key_exists('selectedLines', $data) || empty($data['selectedLines'])) {
            throw  new \CNCLTD\Exceptions\JsonHttpException(
                400,
                'You have to select at least one line to create the quote'
            );
        }

        $this->setSelectedOrderLines($data['selectedLines']);

        if (!array_key_exists('salutation', $data) || empty($data['salutation'])) {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, 'You have to provide a salutation message');
        }

        if (!array_key_exists('introduction', $data) || empty($data['introduction'])) {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, 'You have to provide a introduction message');
        }
        if (!array_key_exists('orderHeadId', $data) || empty($data['orderHeadId'])) {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, 'Order Head Id required');
        }
        if (!array_key_exists('emailSubject', $data) || empty($data['emailSubject'])) {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, 'Email Subject required');
        }
        if (!$this->dbeUser->getValue(DBEUser::signatureFilename)) {
            throw new \CNCLTD\Exceptions\JsonHttpException(
                400,
                'User does not have signature file assigned, please assign one to continue'
            );
        }

        $buPDFSalesQuote = new BUPDFSalesQuote($this);
        try {
            $buPDFSalesQuote->generate(
                $data['orderHeadId'],
                $data['salutation'],
                $data['introduction'],
                $data['emailSubject'],
                $this->dsSelectedOrderLine
            );
            echo json_encode(["status" => "ok"]);
        } catch (Exception $exception) {
            throw new \CNCLTD\Exceptions\JsonHttpException('500', $exception->getMessage());
        }
    }

    /**
     * upload a quote document from local client
     * @access private
     * @throws Exception
     */
    function uploadQuoteDoc()
    {
        $this->setMethodName('uploadQuoteDoc');
        if (!$this->getOrdheadID()) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return FALSE;
        }
        if (!$_FILES['quoteFile']['name']) {
            $this->setQuoteFileMessage('You must specify a document to load');
        }
        if ($_FILES['quoteFile']['name']) {                // User has sent a file
            if (!is_uploaded_file($_FILES['quoteFile']['tmp_name'])) {                    // Possible hack?
                $this->setQuoteFileMessage(CTPROJECT_MSG_DOCUMENT_NOT_LOADED);
            }
            if ($_FILES['quoteFile']['size'] == 0) {
                $this->setQuoteFileMessage(CTPROJECT_MSG_DOCUMENT_NOT_LOADED);
            }
        }

        if (isset($_FILES['userFile']) && $_FILES['userfile']['size'] > CTPROJECT_MAX_DOCUMENT_FILE_SIZE) {
            $this->setQuoteFileMessage(CTPROJECT_MSG_DOCUMENT_TOO_BIG);
        }
        if ($this->formError) {
            $this->displayOrder();                // redisplay with error message(s)
            exit;
        }
        // Insert into database
        $dsOrdhead = new DataSet($this);
        if (!$this->buSalesOrder->getOrderWithCustomerName(
            $this->getOrdheadID(),
            $dsOrdhead,
            $dsOrdline,
            $dsDeliveryContact
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDER_NOT_FND);
        }
        $versionNo = $this->buSalesOrder->getNextQuoteVersion($this->getOrdheadID());
        $quoteFile = $GLOBALS['cfg']['quote_path'] . '/' . $this->getOrdheadID() . '_' . $versionNo;//.'.pdf';
        $extension = substr(
            $_FILES['quoteFile']['name'],
            strpos(
                $_FILES['quoteFile']['name'],
                '.'
            ) + 1,
            3
        );
        move_uploaded_file(
            $_FILES['quoteFile']['tmp_name'],
            $quoteFile . '.' . $extension
        ); // use original extension
        $this->dsQuotation->setUpdateModeInsert();
        $this->dsQuotation->setValue(
            DBEQuotation::versionNo,
            $versionNo
        );
        $this->dsQuotation->setValue(
            DBEQuotation::ordheadID,
            $dsOrdhead->getValue(DBEOrdhead::ordheadID)
        );
        $this->dsQuotation->setValue(
            DBEQuotation::userID,
            $this->userID
        );
        $this->dsQuotation->setValue(
            DBEQuotation::sentDateTime,
            null
        );
        $this->dsQuotation->setValue(
            DBEQuotation::salutation,
            $this->getSalutation()
        );
        $this->dsQuotation->setValue(
            DBEQuotation::fileExtension,
            $extension
        );
        $this->dsQuotation->setValue(
            DBEQuotation::documentType,
            'manualUpload'
        );
        $this->dsQuotation->post();
        $this->buSalesOrder->insertQuotation($this->dsQuotation);
        header('Location: ' . $this->getDisplayOrderURL());
        exit;
    }

    /**
     * display PDF quote
     * @access private
     */
    function displayQuoteDoc()
    {
        $this->setMethodName('displayQuoteDoc');
        if (!$this->getQuotationID()) {
            $this->displayFatalError(CTSALESORDER_MSG_QUOTEID_NOT_PASSED);
            return;
        }
        if (!$this->buSalesOrder->getQuoteByID(
            $this->getQuotationID(),
            $this->dsQuotation
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_QUOTE_NOT_FOUND);
            return;
        }
        $quoteFile = $this->dsQuotation->getValue(DBEQuotation::ordheadID) . '_' . $this->dsQuotation->getValue(
                DBEQuotation::versionNo
            );
        if (!$this->dsQuotation->getValue(DBEQuotation::fileExtension)) {
            $quoteFile .= '.pdf';
        } else {
            $quoteFile .= '.' . $this->dsQuotation->getValue(
                    DBEQuotation::fileExtension
                );        // if no extension in DB then assume PDF
        }
        switch ($this->dsQuotation->getValue(DBEQuotation::fileExtension)) {
            case "pdf":
                $ctype = "application/pdf";
                break;
            case "exe":
                $ctype = "application/octet-stream";
                break;
            case "zip":
                $ctype = "application/zip";
                break;
            case "doc":
                $ctype = "application/msword";
                break;
            case "xls":
                $ctype = "application/vnd.ms-excel";
                break;
            case "ppt":
                $ctype = "application/vnd.ms-powerpoint";
                break;
            case "gif":
                $ctype = "image/gif";
                break;
            case "png":
                $ctype = "image/png";
                break;
            case "jpg":
                $ctype = "image/jpg";
                break;
            default:
                $ctype = "application/force-download";
        }
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: $ctype");
        header("Content-Disposition: attachment; filename=" . $quoteFile . ";");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize('quotes/' . $quoteFile));
        readfile('quotes/' . $quoteFile);

        exit();
    }

    /**
     * delete a quote/order
     * @access private
     * @throws Exception
     */
    function deleteOrder()
    {
        $this->setMethodName('deleteOrder');
        $this->buSalesOrder->deleteOrder($this->getOrdheadID());
        if ($this->getParam('urlCallback')) {
            $url = $this->getParam('urlCallback');
        } else {
            if ($this->getSessionParam('urlReferer')) {
                $url = $this->getSessionParam('urlReferer');
                $this->setSessionParam('urlReferer', null);
            } else {
                $url =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTSALESORDER_ACT_DISP_SEARCH
                        )
                    );
            }
        }
        header('Location: ' . $url);
        exit;
    }

    /**
     * generate a CSV of the selected lines.
     * @access private
     * @throws Exception
     */
    function downloadCSV()
    {
        $this->setMethodName('downloadCSV');
        if (count($this->postVars['selectedOrderLine']) == 0) {
            $this->setLinesMessage(CTSALESORDER_MSG_NO_LINES);
            $this->displayOrder();
            return FALSE;
        } else {
            $this->setSelectedOrderLines($this->postVars['selectedOrderLine']);
        }
        if (!$this->getOrdheadID()) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return FALSE;
        }
        $dsOrdline = new DataSet($this);
        if (!$this->buSalesOrder->getOrderWithCustomerName(
            $this->getOrdheadID(),
            $dsOrdhead,
            $dsOrdline,
            $dsDeliveryContact
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDER_NOT_FND);
        }

        $fileName = 'order.csv';
        Header('Content-type: text/plain');
        Header('Content-Disposition: attachment; filename=' . $fileName);

        print    "quantity, description , unitSale\n";

        while ($dsOrdline->fetchNext()) {

            if ($this->dsSelectedOrderLine->search(
                DBEOrdline::id,
                $dsOrdline->getValue(DBEOrdline::id)
            )) {

                if ($dsOrdline->getValue(DBEJOrdline::itemDescription)) {
                    $description = $dsOrdline->getValue(DBEJOrdline::itemDescription);                // from item table
                } else {
                    $description = $dsOrdline->getValue(DBEOrdline::description);
                }
                $qtyOrdered = null;
                $unitSale = null;

                if ($dsOrdline->getValue(DBEOrdline::lineType) == "I") { // item line

                    $qtyOrdered = number_format(
                        $dsOrdline->getValue(DBEOrdline::qtyOrdered),
                        2
                    );
                    $unitSale = number_format(
                        $dsOrdline->getValue(DBEOrdline::curUnitSale),
                        2
                    );
                    /*
					if we have item notes then add them to the description
					*/
                    if ($dsOrdline->getValue(DBEOrdline::itemID)) {
                        // some item lines do not have a related item record
                        $dsItem = new DataSet($this);
                        $this->buItem->getItemByID(
                            $dsOrdline->getValue(DBEOrdline::itemID),
                            $dsItem
                        );

                        if ($dsItem->getValue(DBEItem::notes)) {
                            $description .= "\n" . str_replace(
                                    chr(13),
                                    '',
                                    $dsItem->getValue(DBEItem::notes)
                                );
                        }
                    }

                }

                print    '"' . $qtyOrdered . '","' . $description . '","' . $unitSale . "\"\n";


            }
        }

        $this->pageClose();
        exit;
    }

    /**
     * Update order address
     * @access private
     * @throws Exception
     */
    function updateAddress()
    {
        $this->setMethodName('updateAddress');
        if (!$this->getOrdheadID()) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return;
        }
        $dsOrdhead = new DataSet($this);
        if (!$this->buSalesOrder->getOrderWithCustomerName(
            $this->getOrdheadID(),
            $dsOrdhead,
            $dsOrdline,
            $dsDeliveryContact
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDER_NOT_FND);
            return;
        }
        if (($dsOrdhead->getValue(DBEOrdhead::type) != 'Q') & ($dsOrdhead->getValue(DBEOrdhead::type) != 'I')) {
            $this->displayFatalError(CTSALESORDER_MSG_MUST_BE_QUOTE_OR_INITIAL);
            return;
        }
        $this->checkUpdatedByAnotherUser(
            $this->getParam('updatedTime'),
            $dsOrdhead->getValue(DBEOrdhead::updatedTime)
        );
        if ($this->getAction() == CTSALESORDER_ACT_UPDATE_INV_ADDRESS) {
            $this->buSalesOrder->updateInvoiceAddress(
                $this->getOrdheadID(),
                $this->getSiteNo()
            );
        } else {
            $this->buSalesOrder->updateDeliveryAddress(
                $this->getOrdheadID(),
                $this->getSiteNo()
            );
        }
        header('Location: ' . $this->getDisplayOrderURL());
        exit;
    }

    function getSiteNo()
    {
        return $this->siteNo;
    }

    function setSiteNo($siteNo)
    {
        $this->siteNo = $siteNo;
    }

    /**
     * Update order contact
     * @access private
     * @throws Exception
     */
    function updateContact()
    {
        $this->setMethodName('updateContact');
        if (!$this->getOrdheadID()) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return;
        }
        if (!$this->getContactID()) {
            $this->displayFatalError(CTSALESORDER_MSG_CONTACTID_NOT_PASSED);
            return;
        }
        $dsOrdhead = new DataSet($this);
        if (!$this->buSalesOrder->getOrderWithCustomerName(
            $this->getOrdheadID(),
            $dsOrdhead,
            $dsOrdline,
            $dsDeliveryContact
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDER_NOT_FND);
            return;
        }
        $this->checkUpdatedByAnotherUser(
            $this->getParam('updatedTime'),
            $dsOrdhead->getValue(DBEOrdhead::updatedTime)
        );
        if (($dsOrdhead->getValue(DBEOrdhead::type) != 'Q') & ($dsOrdhead->getValue(DBEOrdhead::type) != 'I')) {
            $this->displayFatalError(CTSALESORDER_MSG_MUST_BE_QUOTE_OR_INITIAL);
            return;
        }
        if ($this->getAction() == CTSALESORDER_ACT_UPDATE_INV_CONTACT) {
            $this->buSalesOrder->updateInvoiceContact(
                $this->getOrdheadID(),
                $this->getContactID()
            );
        } else {
            $this->buSalesOrder->updateDeliveryContact(
                $this->getOrdheadID(),
                $this->getContactID()
            );
        }
        header('Location: ' . $this->getDisplayOrderURL());
        exit;
    }

    function getContactID()
    {
        return $this->contactID;
    }

    function setContactID($id)
    {
        $this->contactID = $id;
    }

    /**
     * Edit/Add Order Line
     * @access private
     * @throws Exception
     */
    function editOrderLine()
    {
        if (!$this->getParam('lineId')) {
            $this->displayFatalError('Line ID not provided');
            return;
        }
        $this->dsOrdline = new DBEJOrdline($this);
        if (!$this->dsOrdline->getRow($this->getParam('lineId'))) {
            $this->displayFatalError('Line not found');
            return;
        }

        $this->setOrdheadID($this->dsOrdline->getValue(DBEOrdline::ordheadID));

        $this->setMethodName('editOrderLine');
        if (!$this->getOrdheadID()) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return;
        }
        $dsOrdhead = new DataSet($this);
        if (!$this->buSalesOrder->getOrdheadByID(
            $this->getOrdheadID(),
            $dsOrdhead
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDER_NOT_FND);
            return;
        }
        $this->checkUpdatedByAnotherUser(
            $this->getParam('updatedTime'),
            $dsOrdhead->getValue(DBEOrdhead::updatedTime)
        );
        if (($dsOrdhead->getValue(DBEOrdhead::type) != 'Q') & ($dsOrdhead->getValue(DBEOrdhead::type) != 'I')) {
            $this->displayFatalError(CTSALESORDER_MSG_MUST_BE_QUOTE_OR_INITIAL);
            return;
        }

        if (!$this->formError && $this->getAction() != CTSALESORDER_ACT_EDIT_ORDLINE) {
            $this->buSalesOrder->initialiseNewOrdline(
                $this->dsOrdline->getValue(DBEOrdline::ordheadID),
                $this->dsOrdline
            );
            if ($this->getParam('lineId')) {
                $lineBefore = new DBEOrdline($this);
                $lineBefore->getRow($this->getParam('lineId'));
                $this->dsOrdline->setValue(DBEOrdline::sequenceNo, $lineBefore->getValue(DBEOrdline::sequenceNo));
            }

        }
        $this->setTemplateFiles(
            array(
                'SalesOrderHeadDisplay' => 'SalesOrderHeadDisplay.inc',
                'SalesOrderLineEdit'    => 'SalesOrderLineEdit.inc',
                'SalesOrderLineEditJS'  => 'SalesOrderLineEditJS.inc' // javascript
            )
        );

        $this->template->setVar(
            [
                'javaScript' => '<script src="js/react.development.js" crossorigin></script>
                    <script src="js/react-dom.development.js" crossorigin></script>
                    <script type="module" src=\'components/ChildAndParentItems/ChildAndParentItems.js\'></script>'
            ]
        );

        $this->displaySalesOrderHeader($dsOrdhead);
        $this->orderLineForm($dsOrdhead);
        $this->template->parse(
            'salesOrderLineEditJS',
            'SalesOrderLineEditJS',
            true
        );
        $this->template->parse(
            'salesOrderHeadDisplay',
            'SalesOrderHeadDisplay',
            true
        );
        $this->template->parse(
            'CONTENTS',
            'SalesOrderLineEdit',
            true
        );
        $this->parsePage();
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
        // pasting lines from another Sales Order
        if ($this->getParam('ordline')[1]['lineType'] == 'T') {
            $this->pasteLinesFromQuotationTemplate();
            header('Location: ' . $this->getDisplayOrderURL());
            exit;
        }

        if ($this->getParam('ordline')[1]['lineType'] == 'O') {
            $this->pasteLinesFromSO();
            header('Location: ' . $this->getDisplayOrderURL());
            exit;
        }

        $this->dsOrdline = new DataSet($this);
        $dbeOrdline = new DBEOrdline($this);
        $this->dsOrdline->copyColumnsFrom($dbeOrdline);
        $this->dsOrdline->addColumn(
            self::descriptionMessage,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsOrdline->addColumn(
            self::supplierName,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsOrdline->addColumn(
            self::supplierNameMessage,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsOrdline->addColumn(
            self::qtyOrderedMessage,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->dsOrdline->addColumn(
            self::curUnitCostMessage,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsOrdline->addColumn(
            self::curUnitSaleMessage,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsOrdline->setNull(DBEOrdline::sequenceNo, DA_ALLOW_NULL);

        if ($this->getParam('ordline')[1]['lineType'] == "I") {                    // Item line
            $this->dsOrdline->setNull(
                DBEOrdline::itemID,
                DA_NOT_NULL
            );
            $this->dsOrdline->setNull(
                DBEOrdline::supplierID,
                DA_NOT_NULL
            );
            $this->dsOrdline->setNull(
                DBEOrdline::qtyOrdered,
                DA_NOT_NULL
            );
            $this->dsOrdline->setNull(
                DBEOrdline::curUnitCost,
                DA_NOT_NULL
            );
            $this->dsOrdline->setNull(
                DBEOrdline::curUnitSale,
                DA_NOT_NULL
            );
            $this->dsOrdline->setNull(
                DBEJOrdline::supplierName,
                DA_NOT_NULL
            );
            $this->dsOrdline->setNull(
                DBEOrdline::description,
                DA_NOT_NULL
            );
        } else {                                                                                                        // Comment line
            $this->dsOrdline->setNull(
                DBEOrdline::itemID,
                DA_ALLOW_NULL
            );
            $this->dsOrdline->setNull(
                DBEOrdline::qtyOrdered,
                DA_ALLOW_NULL
            );
            $this->dsOrdline->setNull(
                DBEOrdline::curUnitCost,
                DA_ALLOW_NULL
            );
            $this->dsOrdline->setNull(
                DBEOrdline::curUnitSale,
                DA_ALLOW_NULL
            );
            $this->dsOrdline->setNull(
                DBEJOrdline::supplierName,
                DA_ALLOW_NULL
            );
            $this->dsOrdline->setNull(
                DBEOrdline::supplierID,
                DA_ALLOW_NULL
            );
            $this->dsOrdline->setNull(
                DBEOrdline::description,
                DA_NOT_NULL
            );
        }
        $this->formError = !$this->dsOrdline->populateFromArray($this->getParam('ordline'));
        $this->setOrdheadID($this->dsOrdline->getValue(DBEOrdhead::ordheadID));
        $dsOrdhead = new DataSet($this);
        if (!$this->buSalesOrder->getOrdheadByID(
            $this->getOrdheadID(),
            $dsOrdhead
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDER_NOT_FND);
            return;
        }
        $this->checkUpdatedByAnotherUser(
            $this->getParam('updatedTime'),
            $dsOrdhead->getValue(DBEOrdhead::updatedTime)
        );
        // Validate Item line
        if ($this->formError) {                    // Form error so redisplay edit form
            if ($this->getAction() == CTSALESORDER_ACT_INSERT_ORDLINE) {
                $this->setAction(CTSALESORDER_ACT_ADD_ORDLINE);
            } else {
                $this->setAction(CTSALESORDER_ACT_UPDATE_ORDLINE);
            }
            $this->editOrderLine();
            exit;
        }

        if ($this->getAction() == CTSALESORDER_ACT_INSERT_ORDLINE) {
            $this->buSalesOrder->insertNewOrderLine($this->dsOrdline);
            if ($this->dsOrdline->getValue(DBEOrdline::lineType) == 'I') {
                $dbeItem = new DBEItem($this);
                $itemID = $this->dsOrdline->getValue(DBEOrdline::itemID);
                $dbeItem->getChildItems($itemID);

                $rowCount = 1;
                $dbeSupplier = new DBESupplier($this);
                $dbeSupplier->getRow(53);
                while ($dbeItem->fetchNext()) {
                    $toInsertChildDsOrdline = new DataSet($this);
                    $toInsertChildDsOrdline->copyColumnsFrom($dbeOrdline);
                    $toInsertChildDsOrdline->setValue(
                        DBEOrdline::ordheadID,
                        $this->dsOrdline->getValue(DBEOrdline::ordheadID)
                    );
                    $toInsertChildDsOrdline->setValue(DBEOrdline::itemID, $dbeItem->getValue(DBEItem::itemID));
                    $toInsertChildDsOrdline->setValue(DBEOrdline::lineType, 'I');
                    $toInsertChildDsOrdline->setValue(
                        DBEOrdline::supplierID,
                        $dbeSupplier->getValue(DBESupplier::supplierID)
                    );
                    $toInsertChildDsOrdline->setValue(
                        DBEOrdline::qtyOrdered,
                        $this->dsOrdline->getValue(DBEOrdline::qtyOrdered)
                    );
                    $toInsertChildDsOrdline->setValue(
                        DBEOrdline::curUnitCost,
                        $dbeItem->getValue(DBEItem::curUnitCost)
                    );
                    $toInsertChildDsOrdline->setValue(
                        DBEOrdline::curUnitSale,
                        $dbeItem->getValue(DBEItem::curUnitSale)
                    );
                    $toInsertChildDsOrdline->setValue(
                        DBEOrdline::description,
                        $dbeItem->getValue(DBEItem::description)
                    );
                    $sequenceNo = $this->dsOrdline->getValue(DBEOrdline::sequenceNo);
                    if ($sequenceNo) {
                        $toInsertChildDsOrdline->setValue(DBEOrdline::sequenceNo, $sequenceNo + $rowCount);
                    }
                    $rowCount++;
                    $this->buSalesOrder->insertNewOrderLine($toInsertChildDsOrdline);
                }
            }
        } else {
            $this->buSalesOrder->updateOrderLine($this->dsOrdline);
        }
        header('Location: ' . $this->getDisplayOrderURL());
    }

    /**
     * Paste lines from another Sales Order onto the end of this one
     * $this->getParam('ordline')[1]['description'] holds ordheadID of order to paste from
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function pasteLinesFromQuotationTemplate()
    {
        $this->setOrdheadID($this->getParam('ordline')[1]['ordheadID']);
        if (!is_numeric($this->getParam('ordline')[1]['itemID'])) {
            $this->setFormErrorMessage('Sales order number must be numeric');
            $this->displayOrder();
            return;
        }
        if (!$this->buSalesOrder->getOrdheadByID(
            $this->getParam('ordline')[1]['itemID'],
            $dsOrdhead
        )) {
            $this->setFormErrorMessage('The sales order you are trying to paste from does not exist');
            $this->displayOrder();
            return;
        }
        $this->buSalesOrder->pasteLinesFromOrder(
            $this->getParam('ordline')[1]['itemID'],
            $this->getOrdheadID()
        );
        $this->displayOrder();
    }

    /**
     * Paste lines from another Sales Order onto the end of this one
     * $this->getParam('ordline')[1]['description'] holds ordheadID of order to paste from
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function pasteLinesFromSO()
    {
        $this->setMethodName('pasteLinesFromSO');
        $this->setOrdheadID($this->getParam('ordline')[1]['ordheadID']);
        if (!is_numeric($this->getParam('ordline')[1]['description'])) {
            $this->setFormErrorMessage('Sales order number must be numeric');
            $this->displayOrder();
            return;
        }
        if (!$this->buSalesOrder->getOrdheadByID(
            $this->getParam('ordline')[1]['description'],
            $dsOrdhead
        )) {
            $this->setFormErrorMessage('The sales order you are trying to paste from does not exist');
            $this->displayOrder();
            return;
        }
        $this->buSalesOrder->pasteLinesFromOrder(
            $this->getParam('ordline')[1]['description'],
            $this->getOrdheadID()
        );
        $this->displayOrder();
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
        $this->moveOrderLineValidation($this->getParam('lineId'));
        $this->buSalesOrder->moveOrderLineUp($this->getParam('lineId'));
        header('Location: ' . $this->getDisplayOrderURL());
    }

    /**
     * @param $lineId
     * @throws Exception
     */
    function moveOrderLineValidation($lineId)
    {
        $dbeOrderLine = new DBEOrdline($this);
        if (!$dbeOrderLine->getRow($lineId)) {
            $this->displayFatalError('Line not found');
        }

        $this->setOrdheadID($dbeOrderLine->getValue(DBEOrdline::ordheadID));
        if (!$this->getOrdheadID()) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
        }
        $dsOrdhead = new DataSet($this);
        if (!$this->buSalesOrder->getOrdheadByID(
            $this->getOrdheadID(),
            $dsOrdhead
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDER_NOT_FND);
        }
        $this->checkUpdatedByAnotherUser(
            $this->getParam('updatedTime'),
            $dsOrdhead->getValue(DBEOrdhead::updatedTime)
        );
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
        $this->moveOrderLineValidation($this->getParam('lineId'));
        $this->buSalesOrder->moveOrderLineDown($this->getParam('lineId'));
        header('Location: ' . $this->getDisplayOrderURL());
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
        $this->setMethodName('deleteOrderLine');

        $this->moveOrderLineValidation($this->getParam('lineId'));
        $this->buSalesOrder->deleteOrderLine($this->getParam('lineId'));
        header('Location: ' . $this->getDisplayOrderURL());
    }

    /**
     * Update order header details
     * @access private
     * @throws Exception
     */
    function updateHeader()
    {
        $this->setMethodName('updateHeader');
        if (!$this->getOrdheadID()) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return;
        }
        $dsOrdhead = new DataSet($this);
        if (!$this->buSalesOrder->getOrdheadByID(
            $this->getOrdheadID(),
            $dsOrdhead
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDER_NOT_FND);
            return;
        }
        $this->checkUpdatedByAnotherUser(
            $this->getParam('updatedTime'),
            $dsOrdhead->getValue(DBEOrdhead::updatedTime)
        );

        if (($dsOrdhead->getValue(DBEOrdhead::type) != 'Q') & ($dsOrdhead->getValue(DBEOrdhead::type) != 'I')) {
            $this->displayFatalError(CTSALESORDER_MSG_MUST_BE_QUOTE_OR_INITIAL);
            return;
        }


        $this->buSalesOrder->updateHeader(
            $this->getOrdheadID(),
            $this->getParam('form')['custPORef'],
            $this->getParam('form')['paymentTermsID'],
            isset($this->getParam('form')['partInvoice']) ? 'Y' : 'N',
            isset($this->getParam('form')['addItem']) == 'Y' ? 'Y' : 'N'
        );
        header('Location: ' . $this->getDisplayOrderURL());
        exit;
    }

    /**
     * send an emailed PDF order confirmation to delivery contact.
     * @access private
     * @throws Exception
     */
    function sendOrderConfirmation()
    {
        $this->setMethodName('sendOrderConfirmation');
        $dsOrdhead = new DataSet($this);
        $dsDeliveryContact = new DataSet($this);
        $dsOrdline = new DataSet($this);
        if (!$this->buSalesOrder->getOrderWithCustomerName(
            $this->getOrdheadID(),
            $dsOrdhead,
            $dsOrdline,
            $dsDeliveryContact
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDER_NOT_FND);
        }
        $this->buSalesOrder->getUserByID(
            $this->userID,
            $this->dsUser
        );
        $this->setSalutation('Dear ' . $dsDeliveryContact->getValue(DBEContact::firstName));
        $tempFile = tempnam(
            '/tmp',
            'CNF'
        );
        $versionNo = null;
        $buPDF = new BUPDF(
            $this,
            $tempFile,
            $this->dsUser->getValue(DBEUser::name),
            $this->getOrdheadID() . '/' . $versionNo,
            'CNC Ltd',
            'Order Confirmation'
        );
        $buPDF->startPage();
        $buPDF->placeImageAt(
            $GLOBALS['cfg']['cnclogo_path'],
            'PNG',
            142,
            38
        );
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->setFontFamily(BUPDF_FONT_ARIAL);
        $buPDF->setBoldItalicOn();
        $buPDF->setFontSize(8);
        $buPDF->setFont();
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->setBoldOn();
        $buPDF->setFontSize(14);
        $buPDF->setFont();
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('Order Confirmation: ' . $this->getOrdheadID());
        $buPDF->setFontSize(10);
        $buPDF->setBoldOff();
        $buPDF->setFont();
        $buPDF->CR();
        $buPDF->CR();
        $firstName = $dsDeliveryContact->getValue(DBEContact::firstName);
        $buPDF->printString(
            $dsDeliveryContact->getValue(DBEContact::title) . ' ' . $firstName{0} . ' ' . $dsDeliveryContact->getValue(
                DBEContact::lastName
            )
        );
        $buPDF->CR();
        $buPDF->printString($dsOrdhead->getValue(DBEJOrdhead::customerName));
        $buPDF->CR();
        $buPDF->printString($dsOrdhead->getValue(DBEOrdhead::delAdd1));
        if ($dsOrdhead->getValue(DBEOrdhead::delAdd2)) {
            $buPDF->CR();
            $buPDF->printString($dsOrdhead->getValue(DBEOrdhead::delAdd2));
        }
        if ($dsOrdhead->getValue(DBEOrdhead::delAdd3)) {
            $buPDF->CR();
            $buPDF->printString($dsOrdhead->getValue(DBEOrdhead::delAdd3));
        }
        $buPDF->CR();
        $buPDF->printString($dsOrdhead->getValue(DBEOrdhead::delTown));
        if ($dsOrdhead->getValue(DBEOrdhead::delCounty)) {
            $buPDF->CR();
            $buPDF->printString($dsOrdhead->getValue(DBEOrdhead::delCounty));
        }
        $buPDF->CR();
        $buPDF->printString($dsOrdhead->getValue(DBEOrdhead::delPostcode));
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString(date('l, jS F Y'));
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString($this->getSalutation() . ',');
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('Following receipt of your official order,');
        if ($dsOrdhead->getValue(DBEOrdhead::custPORef)) {
            $buPDF->printString(' (Ref: ' . $dsOrdhead->getValue(DBEOrdhead::custPORef) . '),');
        }
        $buPDF->printString(' please find confirmation of the items to be supplied detailed below.');
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->setBoldOn();
        $buPDF->setFont();
        $buPDF->printStringRJAt(
            30,
            'Qty'
        );
        $buPDF->printStringAt(
            40,
            'Details'
        );
        $buPDF->printStringRJAt(
            150,
            'Unit'
        );
        $buPDF->printStringRJAt(
            170,
            'Total'
        );
        $buPDF->setBoldOff();
        $buPDF->setFont();
        $buPDF->CR();
        $grandTotal = 0;
        while ($dsOrdline->fetchNext()) {
            if ($dsOrdline->getValue(DBEJOrdline::lineType) == "I") {
                if ($dsOrdline->getValue(DBEJOrdline::description)) {
                    $buPDF->printStringAt(
                        40,
                        $dsOrdline->getValue(DBEJOrdline::description)
                    );
                } else {
                    $buPDF->printStringAt(
                        40,
                        $dsOrdline->getValue(DBEJOrdline::itemDescription)
                    );
                }
                $buPDF->printStringRJAt(
                    30,
                    Controller::formatNumber(
                        $dsOrdline->getValue(DBEJOrdline::qtyOrdered),
                        2
                    )
                );
                /*
Do not print zero sale values
*/
                if ($dsOrdline->getValue(DBEJOrdline::curUnitSale) != 0) {
                    $buPDF->printStringRJAt(
                        150,
                        Controller::formatNumberCur($dsOrdline->getValue(DBEJOrdline::curUnitSale))
                    );
                    $total = ($dsOrdline->getValue(DBEJOrdline::curUnitSale) * $dsOrdline->getValue(
                            DBEJOrdline::qtyOrdered
                        ));
                    $buPDF->printStringRJAt(
                        170,
                        Controller::formatNumberCur($total)
                    );
                    $grandTotal += $total;
                }
                if ($dsOrdline->getValue(DBEJOrdline::itemID)) {
                    // some item lines in old system did not have a related item record
                    $dsItem = new DataSet($this);
                    $this->buItem->getItemByID(
                        $dsOrdline->getValue(DBEJOrdline::itemID),
                        $dsItem
                    );
                    /*
now that the notes are in a text field we need to split the lines up for the PDF printing
*/
                    if ($dsItem->getValue(DBEItem::notes)) {
                        $buPDF->setFontSize(8);
                        $buPDF->setFont();
                        $notesArray = explode(
                            chr(13) . chr(10),
                            $dsItem->getValue(DBEItem::notes)
                        );
                        foreach ($notesArray as $noteLine) {
                            if (trim($noteLine)) {                    // ignore blank lines
                                $buPDF->CR();
                                $buPDF->printStringAt(
                                    40,
                                    $noteLine
                                );
                            }
                        }
                        $buPDF->setFontSize(10);
                        $buPDF->setFont();
                    }
                }
            } else {
                $buPDF->printStringAt(
                    40,
                    $dsOrdline->getValue(DBEOrdline::description)
                ); // comment line
            }
            $buPDF->CR();
        }
        $buPDF->setBoldOn();
        $buPDF->setFont();
        $buPDF->printStringRJAt(
            150,
            'Grand Total'
        );
        $buPDF->printStringRJAt(
            170,
            Controller::formatNumberCur($grandTotal)
        );
        $buPDF->setBoldOff();
        $buPDF->setFont();
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString(
            'These goods or services will be supplied to you ASAP and if installation services are required, our technical department will contact you to arrange a suitable appointment.'
        );
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('If I can be of any further assistance please do not hesitate to contact me.');
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('Yours sincerely,');
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('For and on behalf of');
        $buPDF->CR();
        $buPDF->printString('COMPUTER & NETWORK CONSULTANTS LTD');
        $buPDF->CR();
        $buPDF->CR();
        if ($this->dsUser->getValue(DBEUser::signatureFilename)) {
            $buPDF->placeImageAt(
                IMAGES_DIR . '/' . $this->dsUser->getValue(DBEUser::signatureFilename),
                'PNG',
                10,
                35
            );
        }
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString(
            $this->dsUser->getValue(DBEUser::firstName) . ' ' . $this->dsUser->getValue(DBEUser::lastName)
        );
        $buPDF->CR();
        $buPDF->setBoldOn();
        $buPDF->setFont();
        $buPDF->printString($this->dsUser->getValue(DBEUser::jobTitle));
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('E. & O. E.');
        $buPDF->endPage();
        $buPDF->close();
        $senderEmail = $this->dsUser->getValue(DBEUser::username) . '@cnc-ltd.co.uk';
        $senderName = $this->dsUser->getValue(DBEUser::firstName) . ' ' . $this->dsUser->getValue(DBEUser::lastName);
        // Send email with attachment
        $message = '<p class=MsoNormal><span style=\'font-size:10.0pt;color:black\'>';
        $message .= $this->getSalutation();
        $message .= '</span></p>';
        $message .= '<p class=MsoNormal><span style=\'font-size:10.0pt;color:black\'>';
        $message .= 'Please find attached confirmation of your recent order.';
        $message .= '</span></p>';
        $subject = 'Your confirmation ' . $dsOrdhead->getValue(DBEOrdhead::ordheadID);
        $filename = $dsOrdhead->getValue(DBEOrdhead::ordheadID) . '.pdf';
        $mime_boundary = "----=_NextPart_" . md5(time());
        $headers = "From: " . $senderName . " <" . $senderEmail . ">\r\n";
        $headers .= "Return-Receipt-To: " . $senderName . " <" . $senderEmail . ">\r\n";
        $headers .= "Disposition-Notification-To: " . $senderName . " <" . $senderEmail . ">\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed;\r\n";
        $headers .= " boundary=\"$mime_boundary\"\r\n";
        $mime_message = "\r\nThis is a multi-part message in MIME format.\r\n";
        $mime_message .= "\r\n--$mime_boundary\r\n";
        $mime_message .= "Content-Type: text/html;";
        $mime_message .= " charset=utf-8\n";
        $mime_message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $mime_message .= "$message\r\n"; // text content of email
        $mime_message .= "\r\n--$mime_boundary\r\n";
        $mime_message .= "Content-Type: application/pdf; name=\"$filename\"\r\n";
        $mime_message .= "Content-Transfer-Encoding: base64\r\n";
        $mime_message .= "Content-Disposition: attachment; filename=\"$filename\"\r\n\r\n";
        $mime_message .= chunk_split(
                base64_encode(file_get_contents($tempFile)),
                60
            ) . "\r\n"; // split attachment to email
        $mime_message .= "\r\n--$mime_boundary--";

        ini_set(
            "sendmail_from",
            $senderEmail
        );        // the envelope from address

        mail(
            $dsOrdhead->getValue(DBEOrdhead::delContactEmail),
            $subject,
            $mime_message,
            $headers
        );
        unlink($tempFile);
        header('Location: ' . $this->getDisplayOrderURL());
    }

    /**
     * @throws Exception
     */
    function updateItemPrice()
    {
        $this->setMethodName('updateItemPrice');
        $data = $this->getJSONData();

        if (!array_key_exists('lineId', $data)) {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, "Line Id is required");
        }
        if (!array_key_exists('unitSale', $data)) {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, "Unit Sale is required");
        }
        if (!array_key_exists('unitCost', $data)) {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, "Unit Cost is required");
        }
        $dbOrderLine = new DBEOrdline($this);
        $dbOrderLine->getRow($data['lineId']);
        $itemId = $dbOrderLine->getValue(DBEOrdline::itemID);

        if (!$itemId) {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, "The line selected does not have connected item");
        }

        $dbeItem = new DBEItem($this);
        $dbeItem->getRow($itemId);
        $dbeItem->setValue(
            DBEItem::curUnitSale,
            $data['unitSale']
        );
        $dbeItem->setValue(
            DBEItem::curUnitCost,
            $data['unitCost']
        );
        $dbeItem->updateRow();

        echo json_encode(["status" => "ok"]);

    }

    /**
     * @throws Exception
     */
    function serviceRequest()
    {
        $this->setMethodName('serviceRequest');

        $buActivity = new BUActivity($this);
        $dsOrdline = new DataSet($this);
        $dsOrdhead = new DataSet($this);
        if ($this->getOrdheadID()) {
            $this->buSalesOrder->getOrderByOrdheadID(
                $this->getOrdheadID(),
                $dsOrdhead,
                $dsOrdline
            );
        } else {
            $this->raiseError('ordheadID not passed');
            exit;
        }

        $dsInput = new DSForm($this);
        $dsInput->addColumn(
            self::etaDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsInput->addColumn(
            DBEOrdhead::serviceRequestCustomerItemID,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsInput->addColumn(
            DBEOrdhead::serviceRequestPriority,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsInput->addColumn(
            DBEOrdhead::serviceRequestText,
            DA_STRING,
            DA_ALLOW_NULL
        );
        /*
    get existing values
    */
        if (!$dsOrdhead->getValue(DBEOrdhead::serviceRequestText)) {
            $dsInput->setValue(
                DBEOrdhead::serviceRequestText,
                $dsOrdhead->getValue(DBEOrdhead::serviceRequestText)
            );
            $dsInput->setValue(
                DBEOrdhead::serviceRequestCustomerItemID,
                $dsOrdhead->getValue(DBEOrdhead::serviceRequestCustomerItemID)
            );
            $dsInput->setValue(
                DBEOrdhead::serviceRequestPriority,
                $dsOrdhead->getValue(DBEOrdhead::serviceRequestPriority)
            );
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $formError = !$dsInput->populateFromArray($this->getParam('inputForm'));

            if ($dsInput->getValue(DBEOrdhead::serviceRequestCustomerItemID) == 99) {
                $formError = true;
            }

            if ($dsInput->getValue(DBEOrdhead::serviceRequestPriority) == 0) {
                $formError = true;
            }

            if (!$formError) {

                $queue = $_REQUEST['queue'] == "Create For Small Projects" ? 3 : 5;

                if ($dsInput->getValue(self::etaDate)) {
                    $buActivity->createSalesServiceRequest(
                        $this->getOrdheadID(),
                        $dsInput,
                        @$_SESSION['selectedOrderLine'],
                        $queue
                    );
                    unset($_SESSION['selectedOrderLine']);
                } else {
                    $this->buSalesOrder->updateServiceRequestDetails(
                        $this->getOrdheadID(),
                        $dsInput->getValue(DBEOrdhead::serviceRequestCustomerItemID),
                        $dsInput->getValue(DBEOrdhead::serviceRequestPriority),
                        $dsInput->getValue(DBEOrdhead::serviceRequestText)
                    );
                }
                /*
        redirect to order
        */
                header('Location: ' . $this->getDisplayOrderURL());

                exit;

            }
        }

        $this->setPageTitle("Service Request");

        $this->setTemplateFiles(
            array(
                'SalesOrderServiceRequest' => 'SalesOrderServiceRequest.inc'
            )
        );

        $urlSubmit =

            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => 'serviceRequest',
                    'ordheadID' => $this->getOrdheadID()
                )
            );

        $this->template->set_var(
            array(
                'etaDate'        => $dsInput->getValue(self::etaDate),
                'etaDateMessage' => $dsInput->getMessage(self::etaDate),

                'serviceRequestText'                  => $dsInput->getValue(DBEOrdhead::serviceRequestText),
                'serviceRequestPriorityMessage'       => $dsInput->getMessage(DBEOrdhead::serviceRequestPriority),
                'serviceRequestCustomerItemIDMessage' => $dsInput->getMessage(DBEOrdhead::serviceRequestCustomerItemID),

                'urlSubmit' => $urlSubmit
            )
        );

        $this->contractDropdown(
            $dsOrdhead->getValue(DBEOrdhead::customerID),
            $dsInput->getValue(DBEOrdhead::serviceRequestCustomerItemID)
        );

        $this->priorityDropdown(
            $dsInput->getValue(DBEOrdhead::serviceRequestPriority),
            $buActivity
        );

        $this->standardTextList(
            'SalesOrderServiceRequest',
            'standardTextBlock'
        );

        $this->template->parse(
            'CONTENTS',
            'SalesOrderServiceRequest',
            true
        );
        //$this->setHTMLFmt( CT_HTML_FMT_POPUP );

        $this->parsePage();

    }

    function contractDropdown(
        $customerID,
        $serviceRequestCustomerItemID,
        $templateName = 'SalesOrderServiceRequest',
        $blockName = 'contractBlock'
    )
    {
        $buCustomerItem = new BUCustomerItem($this);
        $dsContract = new DataSet($this);
        $buCustomerItem->getContractsByCustomerID(
            $customerID,
            $dsContract,
            null
        );

        if ($serviceRequestCustomerItemID == '99') {
            $this->template->set_var(
                array(
                    'noContractSelected' => CT_SELECTED
                )
            );
        }
        if (!$serviceRequestCustomerItemID) {
            $this->template->set_var(
                array(
                    'tandMSelected' => CT_SELECTED
                )
            );
        }

        $this->template->set_block(
            $templateName,
            $blockName,
            'contracts'
        );
        while ($dsContract->fetchNext()) {

            $contractSelected = ($serviceRequestCustomerItemID == $dsContract->getValue(
                    DBEJContract::customerItemID
                )) ? CT_SELECTED : null;

            $this->template->set_var(
                array(
                    'contractSelected'             => $contractSelected,
                    'serviceRequestCustomerItemID' => $dsContract->getValue(DBEJContract::customerItemID),
                    'contractDescription'          => $dsContract->getValue(DBEJContract::itemDescription)
                )
            );
            $this->template->parse(
                'contracts',
                $blockName,
                true
            );
        }

    }

    function priorityDropdown(
        $serviceRequestPriority,
        $buActivity,
        $templateName = 'SalesOrderServiceRequest',
        $blockName = 'priorityBlock'
    )
    {

        $this->template->set_block(
            $templateName,
            $blockName,
            'priorities'
        );

        foreach ($buActivity->priorityArray as $priority => $priorityDescription) {

            $prioritySelected = ($serviceRequestPriority == $priority) ? CT_SELECTED : null;

            $this->template->set_var(
                array(
                    'prioritySelected'    => $prioritySelected,
                    'priority'            => $priority,
                    'priorityDescription' => $priorityDescription
                )
            );
            $this->template->parse(
                'priorities',
                $blockName,
                true
            );
        }

    }

    function standardTextList(
        $template,
        $block
    )
    {

        $dbeStandardText = new DBEStandardText($this);

        $dbeStandardText->getRowsByTypeID(3);

        $this->template->set_block(
            $template,
            $block,
            'rows'
        );

        while ($dbeStandardText->fetchNext()) {

            $this->template->set_var(
                array(
                    'standardTextContent'     => htmlentities($dbeStandardText->getValue(DBEStandardText::stt_text)),
                    'standardTextDescription' => $dbeStandardText->getValue(DBEStandardText::stt_desc)
                )
            );
            $this->template->parse(
                'rows',
                $block,
                true
            );
        }

    }

    /**
     * Store selected SO lines in session var then redirect to serviceRequest()
     * @return bool
     * @throws Exception
     */
    function serviceRequestFromLines()
    {
        if (count($this->postVars['selectedOrderLine']) == 0) {
            $this->setLinesMessage(CTSALESORDER_MSG_NO_LINES);
            $this->displayOrder();
            return FALSE;
        } else {
            $this->setSessionParam('selectedOrderLine', $this->postVars['selectedOrderLine']);
            $redirectUrl =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => 'serviceRequest',
                        'ordheadID' => $this->getOrdheadID()
                    )
                );
            header('Location: ' . $redirectUrl);
        }
        return true;
    }

    /**
     * @throws Exception
     */
    function sendReminderQuote()
    {
        $this->setMethodName('sendReminderQuote');
        if (!$this->getQuotationID()) {
            $this->displayFatalError(CTSALESORDER_MSG_QUOTEID_NOT_PASSED);
            return;
        }
        if (!$this->buSalesOrder->getQuoteByID(
            $this->getQuotationID(),
            $this->dsQuotation
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_QUOTE_NOT_FOUND);
            return;
        }
        $this->dsQuotation->fetchNext();
        if (!$this->emailSubject) {
            $this->displayFatalError('Email Subject Missing');
            return;
        }

        if ($this->dsQuotation->getValue(DBEJQuotation::fileExtension) == 'pdf') {
            $buPDFSalesQuote = new BUPDFSalesQuote($this);
            $buPDFSalesQuote->sendReminderPDFEmailQuote(
                $this->getQuotationID(),
                $this->emailSubject
            );
            $ordHeadID = $this->dsQuotation->getValue(DBEQuotation::ordheadID);
            $dbeQuotation = new DBEQuotation($this);
            $dbeQuotation->setValue(
                DBEQuotation::ordheadID,
                $ordHeadID
            );

            $versionNo = $this->buSalesOrder->getNextQuoteVersion($ordHeadID);
            $previousVersion = $this->dsQuotation->getValue(DBEQuotation::versionNo);
            $previousFile = 'quotes/' . $ordHeadID . '_' . $previousVersion . '.pdf';
            $newFile = 'quotes/' . $ordHeadID . '_' . $versionNo . '.pdf';

            copy(
                $previousFile,
                $newFile
            );

            $dbeQuotation->setValue(
                DBEQuotation::versionNo,
                $versionNo
            );
            $dbeQuotation->setValue(
                DBEQuotation::salutation,
                $this->dsQuotation->getValue(DBEQuotation::salutation)
            );
            $dbeQuotation->setValue(
                DBEQuotation::emailSubject,
                $this->dsQuotation->getValue(DBEQuotation::emailSubject)
            );
            $dbeQuotation->setValue(
                DBEQuotation::sentDateTime,
                date('Y-m-d H:i:s')
            );
            $dbeQuotation->setValue(
                DBEQuotation::userID,
                $this->dsQuotation->getValue(DBEQuotation::userID)
            );
            $dbeQuotation->setValue(
                DBEQuotation::fileExtension,
                $this->dsQuotation->getValue(DBEQuotation::fileExtension)
            );
            $dbeQuotation->setValue(
                DBEQuotation::documentType,
                'reminder'
            );
            $dbeQuotation->insertRow();

        }

        $this->setOrdheadID($this->dsQuotation->getValue(DBEJQuotation::ordheadID));
        header('Location: ' . $this->getDisplayOrderURL());
        exit;
    } // end contractDropdown

    /**
     * @throws Exception
     */
    function createTemplatedQuote()
    {

        $dsInput = new DSForm($this);
        $dsInput->addColumn(
            "customerID",
            DA_ID,
            DA_NOT_NULL
        );
        $dsInput->addColumn(
            "customerString",
            DA_STRING,
            DA_NOT_NULL
        );
        $dsInput->addColumn(
            "templates",
            DA_ARRAY,
            DA_ALLOW_NULL
        );
        $dsInput->addColumn(
            "existingQuotationID",
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $openTabURL = null;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($dsInput->populateFromArray(
                [$this->getParam('form')]
            )) {
                try {
                    $destinationQuoteID = $this->createTemplatedQuoteDocument(
                        $dsInput->getValue('customerID'),
                        $dsInput->getValue('templates'),
                        $dsInput->getValue('existingQuotationID')
                    );
                    $openTabURL = "/SalesOrder.php?action=displaySalesOrder&ordheadID=$destinationQuoteID";
                } catch (Exception $exception) {
                    $this->formErrorMessage = $exception->getMessage();
                    $this->formError = true;
                }
            };
        } else {
            if ($this->getParam('customerID')) {
                $dbeCustomer = new DBECustomer($this);
                $dbeCustomer->getRow($this->getParam('customerID'));
                if (!$dbeCustomer->rowCount()) {
                    $this->raiseError('The customer provided does not exist');
                    exit;
                }
                $dsInput->setValue('customerID', $this->getParam('customerID'));
                $dsInput->setValue('customerString', $dbeCustomer->getValue(DBECustomer::name));
            }
        }

        $this->setTemplateFiles('TemplatedQuote', 'TemplatedQuote');
        $this->setPageTitle('Create Templated Sales Quotation');

        $this->template->setVar(
            [
                "customerID"          => $dsInput->getValue('customerID'),
                "customerString"      => $dsInput->getValue('customerString'),
                'existingQuotationID' => $dsInput->getValue('existingQuotationID'),
                'openTabURL'          => $openTabURL
            ]
        );

        $this->template->setBlock('TemplatedQuote', 'templatesBlock', 'templates');
        $this->quoteTemplatesSelector($dsInput->getValue('templates'), 'templates', 'templatesBlock');

        $this->template->parse('CONTENTS', 'TemplatedQuote');
        $this->parsePage();
    } // end contractDropdown

    /**
     * @param $customerID
     * @param $templateIDs
     * @param $existingQuotationID
     * @return bool
     * @throws Exception
     */
    private function createTemplatedQuoteDocument($customerID, array $templateIDs, $existingQuotationID)
    {
        // check that the customer exits
        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRow($customerID);
        if (!$dbeCustomer->rowCount()) {
            throw new Exception('Customer does not exist');
        }
        $destinationQuotation = null;
        //check the existingQuotationID refers to an existing quotation, which actually belongs to the customer given
        $dbeSalesOrderHeader = new DBEOrdhead($this);
        if ($existingQuotationID) {

            $dbeSalesOrderHeader->getRow($existingQuotationID);
            if (!$dbeSalesOrderHeader->rowCount()) {
                throw new Exception('The given existing quotation does not exist');
            }
            if ($dbeSalesOrderHeader->getValue(DBEOrdhead::type) != 'Q') {
                throw new Exception('The given existing quotation is not of Quotation type');
            }
            if ($dbeSalesOrderHeader->getValue(DBEOrdhead::customerID) != $customerID) {
                throw new Exception('The given existing quotation does not belong to the given customer');
            }
            $destinationQuotation = $existingQuotationID;
        } else {
            // we have to create the quotation from scratch
            $dsOrdline = new DataSet($this);
            $dsOrdhead = new DataSet($this);
            $this->buSalesOrder->initialiseQuote($dsOrdhead, $dsOrdline, $dbeCustomer);
            $destinationQuotation = $dsOrdhead->getValue(DBEOrdhead::ordheadID);
        }

        $dbeTemplateQuotation = new DBEQuotationTemplate($this);
        foreach ($templateIDs as $templateID) {
            $dbeTemplateQuotation->getRow($templateID);
            $salesOrderID = $dbeTemplateQuotation->getValue(DBEQuotationTemplate::linkedSalesOrderId);
            $this->buSalesOrder->pasteLinesFromOrder($salesOrderID, $destinationQuotation);
        }
        return $destinationQuotation;
    }

    /**
     * Get and parse user drop-down selector
     * @access private
     * @param $selectedTemplateIds
     * @param $blockVar
     * @param $block
     */
    function quoteTemplatesSelector($selectedTemplateIds,
                                    $blockVar,
                                    $block
    )
    {
        $dbeQuoteTemplates = new DBEQuotationTemplate($this);
        $dbeQuoteTemplates->getRows();
        while ($dbeQuoteTemplates->fetchNext()) {
            $templateID = $dbeQuoteTemplates->getValue(DBEQuotationTemplate::id);
            $selected = in_array(
                $templateID,
                $selectedTemplateIds
            ) ? CT_SELECTED : null;
            $this->template->set_var(
                [
                    "templateID"  => $templateID,
                    "description" => $dbeQuoteTemplates->getValue(DBEQuotationTemplate::description),
                    "selected"    => $selected
                ]
            );
            $this->template->parse(
                $blockVar,
                $block,
                true
            );
        }
    }

    /**
     * Get and parse user drop-down selector
     * @access private
     * @param $siteNo
     * @param $dsSite DataSet|DBESite
     * @param $blockVar
     * @param $block
     */
    function parseSiteSelector($siteNo,
                               &$dsSite,
                               $blockVar,
                               $block
    )
    {
        while ($dsSite->fetchNext()) {
            $siteSelected = ($dsSite->getValue(DBESite::siteNo) == $siteNo) ? CT_SELECTED : null;
            $this->template->set_var(
                array(
                    $block . 'Selected' => $siteSelected,
                    $block . 'SiteNo'   => $dsSite->getValue(DBESite::siteNo),
                    $block . 'Add1'     => $dsSite->getValue(DBESite::add1),
                    $block . 'Add2'     => $dsSite->getValue(DBESite::add2),
                    $block . 'Add3'     => $dsSite->getValue(DBESite::add3),
                    $block . 'Town'     => $dsSite->getValue(DBESite::town),
                    $block . 'County'   => $dsSite->getValue(DBESite::county),
                    $block . 'Postcode' => $dsSite->getValue(DBESite::postcode)
                )
            );
            $this->template->parse(
                $blockVar,
                $block,
                true
            );
        }
    } // end function documents

    /**
     * Get and parse contact drop-down selector
     * @access private
     * @param $contactID
     * @param $dsContact DBEContact|DataSet
     * @param $blockVar
     * @param $block
     */
    function parseContactSelector($contactID,
                                  &$dsContact,
                                  $blockVar,
                                  $block
    )
    {
        while ($dsContact->fetchNext()) {
            $contactSelected = ($dsContact->getValue(DBEContact::contactID) == $contactID) ? CT_SELECTED : null;
            $this->template->set_var(
                array(
                    $block . 'Selected'  => $contactSelected,
                    $block . 'ContactID' => $dsContact->getValue(DBEContact::contactID),
                    $block . 'FirstName' => $dsContact->getValue(DBEContact::firstName),
                    $block . 'LastName'  => $dsContact->getValue(DBEContact::lastName)
                )
            );
            $this->template->parse(
                $blockVar,
                $block,
                true
            );
        }
    }
}
