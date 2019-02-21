<?php
/**
 * Sales Order controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
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
require_once($cfg["path_func"] . "/Common.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");
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
    'CTSALESORDER_ACT_CREATE_QUICK_QUOTE',
    'genQuickQuote'
); // generate quick quote using selector
define(
    'CTSALESORDER_ACT_CREATE_ORDER_FORM',
    'genOrderForm'
);
define(
    'CTSALESORDER_ACT_CHANGE_SUPPLIER',
    'changeSupplier'
);
define(
    'CTSALESORDER_ACT_DOWNLOAD_CSV',
    'downloadCSV'
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
    var $customerID = '';
    var $customerString = '';                                            // Used when searching for an order by string
    var $buCustomer = '';
    var $dsQuotation = '';
//	var $buNotepad='';
    var $buItem = '';
    var $buSalesOrder = '';
    var $customerStringMessage = '';
    var $quoteFileMessage = '';
    var $userMessage = '';
    var $updateSupplierID;
    var $fromOrdheadID;
    var $linesMessage = '';
    var $uploadUserMessage = '';
    var $ordheadIDMessage = '';
    var $fromDateMessage = '';
    var $toDateMessage = '';
    var $serviceRequestCustomerItemID = '';
    var $serviceRequestText = '';
    var $dsOrdline = '';
    var $dsCustomer = '';
    var $dsContact = '';
    var $dsSite = '';
    var $siteNo = '';
    var $seqenceNo = '';
    var $dsOrdhead = '';
    var $ordheadID = '';
    var $quotationID = '';
    var $emailSubject = '';
    var $orderType = '';
    var $custPORef = '';
    var $lineText = '';
    var $fromDate = '';
    var $toDate = '';
    var $salutation = '';
    var $dsUser = '';
    var $introduction = '';
    var $dsSelectedOrderLine = '';
    var $contactID = '';
    var $quotationUserID = '';
    var $urlCallback = '';
    var $orderTypeArray = array(
        "I" => "Initial",
        "Q" => "Quotation",
        "P" => "Part Despatched",
        "C" => "Completed",
        "B" => "Both Initial & Part Despatched"
    );
    var $lineValidationError = '';

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
            "technical",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buCustomer = new BUCustomer($this);
        $this->buSalesOrder = new BUSalesOrder($this);
        $this->buItem = new BUItem($this);
        $this->dsOrdhead = new Dataset($this);
        $this->dsSelectedOrderLine = new Dataset($this);
        $this->dsSelectedOrderLine->addColumn(
            'sequenceNo',
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $this->dsSelectedOrderLine->addColumn(
            'qtyOrdered',
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->dsSelectedOrderLine->addColumn(
            'curUnitCost',
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->dsSelectedOrderLine->addColumn(
            'curUnitSale',
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

    // these dumies are needed because every HTML variable passed must have a handler
    function setUrlCallback($dummy)
    {
    }

    function setSeSweetcode($dummy)
    {
    }

    function setOrdhead($dummy)
    {
    }

    function setSiteNo($siteNo)
    {
        $this->siteNo = $siteNo;
    }

    function getSiteNo()
    {
        return $this->siteNo;
    }

    function setSequenceNo($no)
    {
        $this->sequenceNo = $no;
    }

    function getSequenceNo()
    {
        return $this->sequenceNo;
    }

    function setContactID($id)
    {
        $this->contactID = $id;
    }

    function getContactID()
    {
        return $this->contactID;
    }

    function setCustomerID($customerID)
    {
        $this->setNumericVar(
            'customerID',
            $customerID
        );
    }

    function getCustomerID()
    {
        return $this->customerID;
    }

    function setUpdateSupplierID($updateSupplierID)
    {
        $this->setNumericVar(
            'updateSupplierID',
            $updateSupplierID
        );
    }

    function getUpdateSupplierID()
    {
        return $this->updateSupplierID;
    }

    function setFromOrdheadID($ID)
    {
        $this->setNumericVar(
            'fromOrdheadID',
            $ID
        );
    }

    function getFromOrdheadID()
    {
        return $this->fromOrdheadID;
    }

    function setQuotationID($quotationID)
    {
        $this->setNumericVar(
            'quotationID',
            $quotationID
        );
    }

    function getQuotationID()
    {
        return $this->quotationID;
    }

    function setOrdheadID($ordheadID)
    {
        $this->ordheadID = trim($ordheadID);
    }

    function getOrdheadID()
    {
        return $this->ordheadID;
    }

    function setQuotationUserID($userID)
    {
        $this->setNumericVar(
            'quotationUserID',
            $userID
        );
    }

    function getQuotationUserID()
    {
        return $this->quotationUserID;
    }

    function getServiceRequestCustomerItemID()
    {
        return $this->serviceRequestCustomerItemID;
    }

    function setServiceRequestCustomerItemID($value)
    {
        $this->serviceRequestCustomerItemID = $value;
    }

    function setOrderType($orderType)
    {
        $this->orderType = $orderType;
    }

    function getOrderType()
    {
        return $this->orderType;
    }

    function setSalutation($salutation)
    {
        $this->salutation = $salutation;
    }

    function getSalutation()
    {
        return $this->salutation;
    }

    function setEmailSubject($message)
    {
        $this->emailSubject = $message;
    }

    function getEmailSubject()
    {
        return $this->emailSubject;
    }

    function setIntroduction($text)
    {
        $this->introduction = $text;
    }

    function getIntroduction()
    {
        return $this->introduction;
    }

    function setCustPORef($ref)
    {
        $this->custPORef = $ref;
    }

    function getCustPORef()
    {
        return $this->custPORef;
    }

    function setLineText($text)
    {
        $this->lineText = $text;
    }

    function getLineText()
    {
        return $this->lineText;
    }

    function getTypeDescription($type)
    {
        return $this->orderTypeArray[$type];
    }

    function getToDate()
    {
        return $this->toDate;
    }

    function getToDateYMD()
    {
        return $this->convertDateYMD($this->getToDate());
    }

    function setToDate($date)
    {
        $this->toDate = $date;
    }

    function getFromDate()
    {
        return $this->fromDate;
    }

    function getFromDateYMD()
    {
        return $this->convertDateYMD($this->getFromDate());
    }

    function setFromDate($date)
    {
        $this->fromDate = $date;
    }

    function convertDateYMD($dateDMY)
    {
        if ($dateDMY != '') {
            $dateArray = explode(
                '/',
                $dateDMY
            );
            return ($dateArray[2] . '-' . str_pad(
                    $dateArray[1],
                    2,
                    '0',
                    STR_PAD_LEFT
                ) . '-' . str_pad(
                    $dateArray[0],
                    2,
                    '0',
                    STR_PAD_LEFT
                ));
        } else {
            return '';
        }
    }

    function setCustomerString($customerString)
    {
        $this->customerString = $customerString;
    }

    function getCustomerString()
    {
        return $this->customerString;
    }

    function setCustomerStringMessage($message)
    {
        if (func_get_arg(0) != "") $this->setFormErrorOn();
        $this->customerStringMessage = $message;
    }

    function getCustomerStringMessage()
    {
        return $this->customerStringMessage;
    }

    function setFromDateMessage($message)
    {
        if (func_get_arg(0) != "") $this->setFormErrorOn();
        $this->fromDateMessage = $message;
    }

    function getFromDateMessage()
    {
        return $this->fromDateMessage;
    }

    function setToDateMessage($message)
    {
        if (func_get_arg(0) != "") $this->setFormErrorOn();
        $this->toDateMessage = $message;
    }

    function getToDateMessage()
    {
        return $this->toDateMessage;
    }

    function setUserMessage($message)
    {
        if (func_get_arg(0) != "") $this->setFormErrorOn();
        $this->userMessage = $message;
    }

    function getUserMessage()
    {
        return $this->userMessage;
    }

    function setLinesMessage($message)
    {
        if (func_get_arg(0) != "") $this->setFormErrorOn();
        $this->linesMessage = $message;
    }

    function getLinesMessage()
    {
        return $this->linesMessage;
    }

    function setUploadUserMessage($message)
    {
        if (func_get_arg(0) != "") $this->setFormErrorOn();
        $this->uploadUserMessage = $message;
    }

    function getUploadUserMessage()
    {
        return $this->uploadUserMessage;
    }

    function setOrdheadIDMessage($message)
    {
        if (func_get_arg(0) != "") $this->setFormErrorOn();
        $this->ordheadIDMessage = $message;
    }

    function getOrdheadIDMessage()
    {
        return $this->ordheadIDMessage;
    }

    /*
	function getYN($flag){
		return ($flag=='Y' ? $flag : 'N');
	}
*/
    function setQuoteFileMessage($message)
    {
        if (func_get_arg(0) != "") $this->setFormErrorOn();
        $this->quoteFileMessage = $message;
    }

    function getQuoteFileMessage()
    {
        return $this->quoteFileMessage;
    }

    function setSelectedOrderLines($array)
    {
        if (!is_array($array)) {
            return FALSE;
        }
        foreach ($array as $value) {
            $this->dsSelectedOrderLine->setUpdateModeInsert();
            $this->dsSelectedOrderLine->setValue(
                'sequenceNo',
                $value
            );
            $this->dsSelectedOrderLine->post();
        }
        return TRUE;
    }

    /**
     * Route to function based upon action passed
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
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->sendQuoteDoc();
                break;
            case CTSALESORDER_ACT_DELETE_QUOTE_DOC:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->deleteQuoteDoc();
                break;
            case CTSALESORDER_ACT_UPDATE_LINES:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->updateLines();
                break;
            case CTSALESORDER_ACT_COPY_TO_ORDER:
            case CTSALESORDER_ACT_CONVERT_TO_ORDER:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->convertToOrder();
                break;
            case CTSALESORDER_ACT_INSERT_FROM_ORDER:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->insertFromOrder();
                break;
            case CTSALESORDER_ACT_DELETE_LINES:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->deleteLines();                        // bulk delete of selected lines
                break;
            case CTSALESORDER_ACT_CHANGE_SUPPLIER:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->changeSupplier();
                break;
            case CTSALESORDER_ACT_CREATE_ORDER_FORM:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->generateOrderForm();
                break;
            case CTSALESORDER_ACT_CREATE_QUICK_QUOTE:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->generateQuoteDoc();
                break;
            case CTSALESORDER_ACT_UPLOAD_QUOTE_DOC:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->uploadQuoteDoc();
                break;
            case CTSALESORDER_ACT_DISPLAY_QUOTE_DOC:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->displayQuoteDoc();
                break;
            case CTSALESORDER_ACT_DELETE_ORDER:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->deleteOrder();
                break;
            case CTSALESORDER_ACT_DOWNLOAD_CSV:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->downloadCSV();
                break;
            case CTSALESORDER_ACT_UPDATE_DEL_ADDRESS:
            case CTSALESORDER_ACT_UPDATE_INV_ADDRESS:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->updateAddress();
                break;
            case CTSALESORDER_ACT_UPDATE_INV_CONTACT:
            case CTSALESORDER_ACT_UPDATE_DEL_CONTACT:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->updateContact();
                break;
            case CTSALESORDER_ACT_ADD_ORDLINE:
            case CTSALESORDER_ACT_EDIT_ORDLINE:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->editOrderLine();
                break;
            case CTSALESORDER_ACT_UPDATE_ORDLINE:
            case CTSALESORDER_ACT_INSERT_ORDLINE:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->updateOrderLine();
                break;
            case CTSALESORDER_ACT_MOVE_ORDLINE_UP:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->moveOrderLineUp();
                break;
            case CTSALESORDER_ACT_MOVE_ORDLINE_DOWN:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->moveOrderLineDown();
                break;
            case CTSALESORDER_ACT_DELETE_ORDLINE:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->deleteOrderLine();
                break;
            case CTSALESORDER_ACT_UPDATE_HEADER:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->updateHeader();
                break;
            case CTSALESORDER_ACT_SEND_CONFIRMATION:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->sendOrderConfirmation();
                break;
            case 'updateItemPrice':
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->updateItemPrice();
                break;
            case 'serviceRequest':
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->serviceRequest();
                break;
            case CTSALESORDER_ACT_CREATE_SR_FROM_LINES:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->serviceRequestFromLines();
                break;
            default:
                $this->displaySearchForm();
                break;
        }
    }

    /**
     * Display the initial form that prompts for search params
     * @access private
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
        $submitURL = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array('action' => CTSALESORDER_ACT_SEARCH)
        );
        $clearURL = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array()
        );
        $customerPopupURL =
            $this->buildLink(
                CTCNC_PAGE_CUSTOMER,
                array(
                    'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $createQuoteURL =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTSALESORDER_ACT_CREATE_QUOTE
                )
            );
        $createOrderURL =
            $this->buildLink(
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

        $_SESSION['urlReferer'] =                    // so called functions know where to come back to
            $this->buildLink(
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
            );

        if ($this->dsOrdhead->rowCount() > 0) {

            $this->template->set_block(
                'SalesOrderSearch',
                'orderBlock',
                'orders'
            );
            $customerNameCol = $this->dsOrdhead->columnExists('customerName');
            $ordheadIDCol = $this->dsOrdhead->columnExists('ordheadID');
            $customerIDCol = $this->dsOrdhead->columnExists('customerID');
            $typeCol = $this->dsOrdhead->columnExists('type');
            $dateCol = $this->dsOrdhead->columnExists('date');
            $custPORefCol = $this->dsOrdhead->columnExists('custPORef');
            $rowNum = 1;
            while ($this->dsOrdhead->fetchNext()) {
                if ($this->hasPermissions(PHPLIB_PERM_SALES)) {
                    $customerURL =
                        $this->buildLink(
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
                $this->setOrdheadID('');
                $customerName = $this->dsOrdhead->getValue($customerNameCol);

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
        if ($this->getCustomerID() != '') {
            $this->buCustomer->getCustomerByID(
                $this->getCustomerID(),
                $dsCustomer
            );
            $this->setCustomerString($dsCustomer->getValue(DBECustomer::name));
        }
        $this->template->set_var(
            array(
                'customerString'        => $this->getCustomerString(),
                'customerStringMessage' => $this->getCustomerStringMessage(),
                'toDateMessage'         => $this->getToDateMessage(),
                'fromDateMessage'       => $this->getFromDateMessage(),
                'customerID'            => $this->getCustomerID(),
                'ordheadID'             => $this->getOrdheadID(),
                'fromDate'              => $this->getFromDate(),
                'toDate'                => $this->getToDate(),
                'ordheadIDMessage'      => $this->getOrdheadIDMessage(),
                'custPORef'             => $this->getCustPORef(),
                'lineText'              => Controller::htmlDisplayText($this->getLineText()),
                'submitURL'             => $submitURL,
                'clearURL'              => $clearURL,
                'createQuoteURL'        => $createQuoteURL,
                'createOrderURL'        => $createOrderURL,
                'customerPopupURL'      => $customerPopupURL,
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
     * Search for customers usng customerString
     * @access private
     */
    function search()
    {
        $this->setMethodName('search');
        $this->setCustomerID(
            $_REQUEST['customerID']
        ); // Have to do this because I couldn't use Javascript to set form[customerID]
        if (
            (!is_numeric($this->getOrdheadID())) &
            ($this->getOrdheadID() != '')
        ) {
            $this->setOrdheadIDMessage(CTSALESORDER_NOT_NUMERIC);
        }
        if (($this->getFromDate() != '') && (!$this->isValidDate($this->getFromDate()))) {
            $this->setFromDateMessage(CTCNC_MSG_INVALID_DATE);
        }
        if (($this->getToDate() != '') && (!$this->isValidDate($this->getToDate()))) {
            $this->setToDateMessage(CTCNC_MSG_INVALID_DATE);
        }
        if ($this->getFormError()) {
            $this->displaySearchForm();
            return;
        }
        if (($this->getToDateYMD() != '') & ($this->getFromDateYMD() != '')) {
            if ($this->getToDateYMD() < $this->getFromDateYMD()) {
                $this->setToDateMessage(CTSALESORDER_TO_DATE_SMALLER);
            }
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
            $this->getFromDateYMD(),
            $this->getToDateYMD(),
            $this->getQuotationUserID(),
            $this->dsOrdhead
        );
        $this->setAction(CTSALESORDER_ACT_DISP_SEARCH);
        $this->defaultAction();
    }

    /**
     * Display one order
     * @access private
     */
    function displayOrder()
    {

        $this->setMethodName('displayOrder');
        if ($this->getAction() != CTSALESORDER_ACT_CREATE_QUOTE AND $this->getAction(
            ) != CTSALESORDER_ACT_CREATE_ORDER) {

            if ($this->getOrdheadID() == '') {
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
            if ($this->lineValidationError != '') {
                $dsOrdline = &$this->dsOrdline;                    // this is the dataset with validation problems
                $dsOrdline->initialise();
            }
        } else {
            if ($this->getCustomerID() == '') {
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
            $this->setOrdheadID($dsOrdhead->getValue('ordheadID'));
        }
        $orderType = $dsOrdhead->getValue('type');
        $projectLink = '';
        if ($dsOrdhead->getValue(DBEOrdhead::customerID)) {
            $projectLink = BUProject::getCurrentProjectLink($dsOrdhead->getValue(DBEOrdhead::customerID));
        }

        $this->setTemplateFiles(
            array(
                'SalesOrderDisplay'                 => 'SalesOrderDisplay.inc',
                'SalesOrderDisplayConvertToOrder'   => 'SalesOrderDisplayConvertToOrder.inc',
                'SalesOrderDisplayCreatePO'         => 'SalesOrderDisplayCreatePO.inc',
                'SalesOrderDisplayUpdateLines'      => 'SalesOrderDisplayUpdateLines.inc',
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

        $purchaseOrderCount = $this->buSalesOrder->countPurchaseOrders($dsOrdhead->getValue('ordheadID'));

        // Initialise an array of actions that may be performed upon this order: they are displayed in a drop-down
        // below the lines section and will be applied to the selected (checked) lines
        $actions = array();
        if ($dsOrdline->rowCount() > 0) {                        // There are lines
            if ($orderType == 'Q') {
                $actions[CTSALESORDER_ACT_CREATE_QUICK_QUOTE] = 'create quick quote';
                $actions[CTSALESORDER_ACT_COPY_TO_ORDER] = 'copy to order';
                $actions[CTSALESORDER_ACT_CONVERT_TO_ORDER] = 'convert to order';
            }
            if ($orderType == 'Q' OR $orderType == 'I') {
                $actions[CTSALESORDER_ACT_DELETE_LINES] = 'delete lines';
                $actions[CTSALESORDER_ACT_UPDATE_LINES] = 'update values';
                $actions[CTSALESORDER_ACT_INSERT_FROM_ORDER] = 'insert lines from order';
            }
            if ($orderType == 'I') {
                $actions[CTSALESORDER_ACT_SEND_CONFIRMATION] = 'send confirmation email';
            }
            $actions[CTSALESORDER_ACT_CREATE_ORDER_FORM] = 'create order form';
            $actions[CTSALESORDER_ACT_CHANGE_SUPPLIER] = 'change supplier';
            $actions[CTSALESORDER_ACT_DOWNLOAD_CSV] = 'download CSV';
            $actions[CTSALESORDER_ACT_CREATE_SR_FROM_LINES] = 'create new SR';

        }
        if (count($actions) > 0) {
            $this->template->set_block(
                'SalesOrderDisplay',
                'actionBlock',
                'actions'
            );
            foreach ($actions as $action => $actionDescription) {
                $this->template->set_var(
                    array(
                        'SELECTED'          => ($this->getAction() == $action) ? CT_SELECTED : '',
                        'action'            => $action,
                        'actionDescription' => $actionDescription
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
        if (!$this->hasPermissions(PHPLIB_PERM_SALES)) {

            $restrictedView = CTCNC_HTML_DISABLED;
            $readOnly = CTCNC_HTML_DISABLED;
            $valuesDisabled = CTCNC_HTML_DISABLED;

        } else {

            $restrictedView = '';
            /*
      Inside sales group, decide which items are readonly
      */
            if ($orderType == 'Q' OR $orderType == 'I') {
                /*
        Quotes or initial orders allow all
        */
                $readOnly = '';
                $valuesDisabled = '';

            } else {
                $readOnly = CTCNC_HTML_DISABLED;
                $valuesDisabled = CTCNC_HTML_DISABLED;
            }

            if ($orderType == 'C' AND !$this->hasPermissions(PHPLIB_PERM_ACCOUNTS)) {
                $valuesDisabled = '';
                $disabled = CTCNC_HTML_DISABLED;
            }

        }

        // Build the various URL links required on the page
        if (!$restrictedView) {

            // Allow delete if quote or initial order and no purchase orders exist yet
            if (
                ($orderType == 'Q')
                OR
                ($orderType == 'I' && $purchaseOrderCount == 0)
            ) {
                $urlCallback =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTSALESORDER_ACT_DISP_SEARCH
                        )
                    );
                $urlDeleteOrder =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'      => CTSALESORDER_ACT_DELETE_ORDER,
                            'ordheadID'   => $this->getOrdheadID(),
                            'urlCallback' => $urlCallback
                        )
                    );
                $txtDeleteOrder = CTSALESORDER_TXT_DELETE;
            } else {
                $urlDeleteOrder = '';
                $txtDeleteOrder = '';
            }


            $urlSubmitOrderLines =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array()
                );
            $uploadQuoteDocURL =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSALESORDER_ACT_UPLOAD_QUOTE_DOC
                    )
                );
            /*
      Display link to original quote if exists and is not same as this
      */
            if ($dsOrdhead->getValue("quotationOrdheadID") && $dsOrdhead->getValue(
                    "quotationOrdheadID"
                ) != $this->getOrdheadID()) {
                $urlOriginalQuote =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'    => $this->getAction(),
                            'ordheadID' => $dsOrdhead->getValue("quotationOrdheadID")
                        )
                    );
                $markupOriginalQuote = '<a href="' . $urlOriginalQuote . '" target="_blank">Original quote ' . $dsOrdhead->getValue(
                        "quotationOrdheadID"
                    ) . '</a>';
            } else {
                $markupOriginalQuote = '';
            }

            $urlUpdateInvAddress =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSALESORDER_ACT_UPDATE_INV_ADDRESS
                    )
                );
            $urlUpdateDelAddress =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSALESORDER_ACT_UPDATE_DEL_ADDRESS
                    )
                );
            $urlUpdateInvContact =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSALESORDER_ACT_UPDATE_INV_CONTACT
                    )
                );
            $urlUpdateDelContact =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSALESORDER_ACT_UPDATE_DEL_CONTACT
                    )
                );
            $urlSiteEdit =
                $this->buildLink(
                    CTCNC_PAGE_SITE,
                    array(
                        'action'  => CTCNC_ACT_SITE_EDIT,
                        'htmlFmt' => CT_HTML_FMT_POPUP
                    )
                );
            $urlUpdateHeader =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSALESORDER_ACT_UPDATE_HEADER
                    )
                );
            $urlSitePopup =
                $this->buildLink(
                    CTCNC_PAGE_SITE,
                    array(
                        'action'  => CTCNC_ACT_SITE_POPUP,
                        'htmlFmt' => CT_HTML_FMT_POPUP
                    )
                );

            $urlCustomerDisplay =
                $this->buildLink(
                    'Customer.php',
                    array(
                        'action'     => 'dispEdit',
                        'customerID' => $dsOrdhead->getValue('customerID')
                    )
                );

            $urlContactEdit =
                $this->buildLink(
                    CTCNC_PAGE_CONTACT,
                    array(
                        'action'  => CTCNC_ACT_CONTACT_EDIT,
                        'htmlFmt' => CT_HTML_FMT_POPUP
                    )
                );
            $urlContactPopup =
                $this->buildLink(
                    CTCNC_PAGE_CONTACT,
                    array(
                        'action'     => CTCNC_ACT_CONTACT_POPUP,
                        'customerID' => $dsOrdhead->getValue('customerID'),
                        'htmlFmt'    => CT_HTML_FMT_POPUP
                    )
                );
            if ($orderType != 'Q') {
                // Display link to sales order confirmation document
                $uncSalesOrderConf =
                    '<A HREF=
				  "file:' . COMPANY_DIR_FROM_BROWSER . '/sales/sales orders/' . $dsOrdhead->getValue(
                        'customerID'
                    ) . '_' .
                    $dsOrdhead->getValue('ordheadID') . '.pdf" target="_blank" title="Customer Confirmation Document (opens in new window)">
				  <IMG src="images/pdf_icon.gif" height="15" border="0"></A>';
                $this->template->set_var(
                    array(
                        'uncSalesOrderConf' => $uncSalesOrderConf
                    )
                );
                // Show navigate Purchase Orders if they exist
                if ($purchaseOrderCount > 0) {
                    $urlPurchaseOrders =
                        $this->buildLink(
                            CTCNC_PAGE_PURCHASEORDER,
                            array(
                                'action'    => CTCNC_ACT_SEARCH,
                                'ordheadID' => $dsOrdhead->getValue('ordheadID')
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
                    $dsOrdhead->getValue('ordheadID')
                );

                if ($linkedServiceRequestCount == 0) {
                    /* create new */
                    $urlServiceRequest =
                        $this->buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'    => 'serviceRequest',
                                'ordheadID' => $dsOrdhead->getValue('ordheadID')
                            )
                        );

//          $linkServiceRequest = '<a href="#" onclick="serviceRequestPopup()">Service Request</a>';
                    $linkServiceRequest = '<a href="' . $urlServiceRequest . '" >Create SR</a>';

                } elseif ($linkedServiceRequestCount == 1) {

                    $problemID = $this->buSalesOrder->getLinkedServiceRequestID($dsOrdhead->getValue('ordheadID'));

                    $urlServiceRequest =
                        $this->buildLink(
                            'Activity.php',
                            array(
                                'action'    => 'displayFirstActivity',
                                'problemID' => $problemID
                            )
                        );

                    $linkServiceRequest = '<a href="' . $urlServiceRequest . '" target="_blank"><div class="navigateLinkCustomerNoteExists">View SR</div></a>';

                } else {     // many SRs so display search page
                    $urlServiceRequest =
                        $this->buildLink(
                            'Activity.php',
                            array(
                                'action'             => 'search',
                                'linkedSalesOrderID' => $dsOrdhead->getValue('ordheadID')
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
                $this->buildLink(
                    'RenewalReport.php',
                    array(
                        'action'     => 'produceReport',
                        'customerID' => $dsOrdhead->getValue('customerID')
                    )
                );


            $txtRenewalReport = 'Renewal Report';

            $urlCustomerNote =
                $this->buildLink(
                    'CustomerNote.php',
                    array(
                        'action'     => 'customerNotePopup',
                        'customerID' => $dsOrdhead->getValue('customerID'),
                        'ordheadID'  => $dsOrdhead->getValue('ordheadID'),
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
                $dsOrdhead->getValue('ordheadID')
            )
            ) {

                $txtCustomerNote = '<div class="navigateLinkCustomerNoteExists">Customer Note</div>';

            } else {
                $txtCustomerNote = 'Customer Note';

            }

            // Show navigate link to invoices if order is part or completed and they exist
            if (($orderType == 'P') OR ($orderType == 'C')) {
                $buInvoice = new BUInvoice($this);
                $invoiceCount = $buInvoice->countInvoicesByOrdheadID($dsOrdhead->getValue('ordheadID'));
                if ($invoiceCount > 0) {
                    $urlInvoices =
                        $this->buildLink(
                            CTCNC_PAGE_INVOICE,
                            array(
                                'action'    => CTCNC_ACT_SEARCH,
                                'ordheadID' => $dsOrdhead->getValue('ordheadID')
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
                (($orderType == 'P') OR ($orderType == 'I')) and
                ($dsOrdline->rowCount() > 0) and
                (!common_isAnInternalStockLocation($dsOrdhead->getValue('customerID')))
            ) {
                $urlDespatch =
                    $this->buildLink(
                        CTCNC_PAGE_DESPATCH,
                        array(
                            'action'    => CTCNC_ACT_DISPLAY_DESPATCH,
                            'ordheadID' => $dsOrdhead->getValue('ordheadID')
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
                'customerID'                   => $dsOrdhead->getValue('customerID'),
                'invContact'                   => $dsOrdhead->getValue(
                        'invContactSalutation'
                    ) . ' ' . $dsOrdhead->getValue(
                        'invContactName'
                    ),
                'invContactID'                 => $dsOrdhead->getValue('invContactID'),
                'delContactID'                 => $dsOrdhead->getValue('delContactID'),
                'invContactPhone'              => $dsOrdhead->getValue('invContactPhone'),
                'invSitePhone'                 => $dsOrdhead->getValue('invSitePhone'),
                'invContactFax'                => $dsOrdhead->getValue('invContactFax'),
                'invContactEmail'              => $dsOrdhead->getValue('invContactEmail'),
                'invSiteNo'                    => $dsOrdhead->getValue('invSiteNo'),
                'invAdd1'                      => $dsOrdhead->getValue('invAdd1'),
                'invAdd2'                      => $dsOrdhead->getValue('invAdd2'),
                'invAdd3'                      => $dsOrdhead->getValue('invAdd3'),
                'invTown'                      => $dsOrdhead->getValue('invTown'),
                'invCounty'                    => $dsOrdhead->getValue('invCounty'),
                'invPostcode'                  => $dsOrdhead->getValue('invPostcode'),
                'delContact'                   => $dsOrdhead->getValue(
                        'delContactSalutation'
                    ) . ' ' . $dsOrdhead->getValue(
                        'delContactName'
                    ),
                'delContactPhone'              => $dsOrdhead->getValue('delContactPhone'),
                'delSitePhone'                 => $dsOrdhead->getValue('delSitePhone'),
                'delContactFax'                => $dsOrdhead->getValue('delContactFax'),
                'delContactEmail'              => $dsOrdhead->getValue('delContactEmail'),
                'delSiteNo'                    => $dsOrdhead->getValue('delSiteNo'),
                'delAdd1'                      => $dsOrdhead->getValue('delAdd1'),
                'delAdd2'                      => $dsOrdhead->getValue('delAdd2'),
                'delAdd3'                      => $dsOrdhead->getValue('delAdd3'),
                'delTown'                      => $dsOrdhead->getValue('delTown'),
                'delCounty'                    => $dsOrdhead->getValue('delCounty'),
                'delPostcode'                  => $dsOrdhead->getValue('delPostcode'),
                'ordheadID'                    => $dsOrdhead->getValue('ordheadID'),
                'serviceRequestCustomerItemID' => $dsOrdhead->getValue('serviceRequestCustomerItemID'),
                'serviceRequestText'           => $dsOrdhead->getValue('serviceRequestText'),
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
                'updatedTime'                  => $dsOrdhead->getValue('updatedTime'),
                'currentDocumentsLink'         => $this->getCurrentDocumentsLink(
                    $dsOrdhead->getValue('customerID'),
                    $this->buCustomer
                ),
                'projectLink'                  => $projectLink
            )
        );

        // Order lines section
        if ($dsOrdline->fetchNext()) {
            $this->template->set_block(
                'SalesOrderDisplay',
                'orderLineBlock',
                'orderLines'
            );
            $curSaleGrandTotal = 0;
            $curProfitGrandTotal = 0;
            $percProfitGrandTotal = 0;
            $curCostGrandTotal = 0;
            do {

                if ($dsOrdline->getValue('renewalTypeID')) {

                    if (!$buRenewal) {

                        $buRenewal = new BURenewal($this);

                    }

                    $buRenewalObject =
                        $buRenewal->getRenewalBusinessObject(
                            $dsOrdline->getValue('renewalTypeID'),
                            $page
                        );

                    $urlEditRenewal =
                        $this->buildLink(
                            $page,
                            array(
                                'action'     => 'editFromSalesOrder',
                                'ordheadID'  => $dsOrdhead->getValue('ordheadID'),
                                'sequenceNo' => $dsOrdline->getValue("sequenceNo")
                            )
                        );

                    $createItem = true;
                    $iconColor = 'red';
                    if ($dsOrdline->getValue('renewalCustomerItemID')) {
                        $iconColor = 'green';
                        $createItem = false;
                    }


                    $renewalIcon =
                        '<A HREF="' . $urlEditRenewal . '" target="_BLANK" onclick="checkCreation()"' . ($createItem ? ' class="createItem" ' : null) . '>' .
                        '<i class="fa fa-2x fa-step-forward" style="color: ' . $iconColor . '"></i>
                         </A>';
                } else {
                    $renewalIcon = '';
                }

                // if form error and there is a set of lines to redisplay then set the values accordingly
                if (!$readOnly && !$restrictedView) {

                    $urlEditLine =
                        $this->buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'      => CTSALESORDER_ACT_EDIT_ORDLINE,
                                'ordheadID'   => $this->getOrdheadID(),
                                'updatedTime' => $dsOrdhead->getValue('updatedTime'),
                                'sequenceNo'  => $dsOrdline->getValue("sequenceNo")
                            )
                        );
                    // common to comment and item lines
                    $urlAddLine =
                        $this->buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'      => CTSALESORDER_ACT_ADD_ORDLINE,
                                'ordheadID'   => $this->getOrdheadID(),
                                'updatedTime' => $dsOrdhead->getValue('updatedTime'),
                                'sequenceNo'  => ($dsOrdline->getValue("sequenceNo") + 1)    // new line below current
                            )
                        );
                    $urlMoveLineUp =
                        $this->buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'      => CTSALESORDER_ACT_MOVE_ORDLINE_UP,
                                'ordheadID'   => $this->getOrdheadID(),
                                'updatedTime' => $dsOrdhead->getValue('updatedTime'),
                                'sequenceNo'  => $dsOrdline->getValue("sequenceNo")
                            )
                        );
                    $urlMoveLineDown =
                        $this->buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'      => CTSALESORDER_ACT_MOVE_ORDLINE_DOWN,
                                'ordheadID'   => $this->getOrdheadID(),
                                'updatedTime' => $dsOrdhead->getValue('updatedTime'),
                                'sequenceNo'  => $dsOrdline->getValue("sequenceNo")
                            )
                        );
                    $urlDeleteLine =
                        $this->buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'      => CTSALESORDER_ACT_DELETE_ORDLINE,
                                'ordheadID'   => $this->getOrdheadID(),
                                'updatedTime' => $dsOrdhead->getValue('updatedTime'),
                                'sequenceNo'  => $dsOrdline->getValue("sequenceNo")
                            )
                        );
                    $salesOrderLineDesc =
                        '<A href="' . $urlEditLine . '">' . Controller::htmlDisplayText(
                            $dsOrdline->getValue("description")
                        ) . '</A>';
                } //	if ( !$readOnly && !$restrictedView ){
                else {
                    $salesOrderLineDesc = Controller::htmlDisplayText($dsOrdline->getValue("description"));
                }

                // for javascript message remove all " and ' chars
                $removeDescription = str_replace(
                    '"',
                    '',
                    $dsOrdline->getValue("description")
                );
                $removeDescription = str_replace(
                    '\'',
                    '',
                    $removeDescription
                );
                $this->template->set_var(
                    array(
                        'salesOrderLineDesc' => $salesOrderLineDesc,
                        'description'        => $dsOrdline->getValue("description"),
                        'qtyOrdered'         => $dsOrdline->getValue("qtyOrdered"),
                        'lineType'           => $dsOrdline->getValue("lineType"),
                        'partNo'             => Controller::htmlDisplayText($dsOrdline->getValue("partNo")),
                        'sequenceNo'         => $dsOrdline->getValue("sequenceNo"),
                        'orderLineChecked'   => ($this->dsSelectedOrderLine->search(
                            'sequenceNo',
                            $dsOrdline->getValue("sequenceNo")
                        )) ? CT_CHECKED : '',
                        'urlMoveLineUp'      => $urlMoveLineUp,
                        'urlMoveLineDown'    => $urlMoveLineDown,
                        'removeDescription'  => $removeDescription,
                        'urlEditLine'        => $urlEditLine,
                        'urlDeleteLine'      => $urlDeleteLine,
                        'urlAddLine'         => $urlAddLine
                    )
                );
                if ($dsOrdline->getValue("lineType") == "I") {                    // Item line needs all these fields
                    $curSaleTotal = $dsOrdline->getValue("curUnitSale") * $dsOrdline->getValue("qtyOrdered");
                    $curCostTotal = $dsOrdline->getValue("curUnitCost") * $dsOrdline->getValue("qtyOrdered");
                    $curProfit = $curSaleTotal - $curCostTotal;
                    if ($curCostTotal != 0) {
                        $percProfit = $curProfit * (100 / $curCostTotal);
                    } else {
                        $percProfit = 100;
                    }
                    if ($dsOrdline->getValue("webSiteURL") != '') {
                        $supplierName = '<A HREF="' . $dsOrdline->getValue('webSiteURL') . '" target="_blank">' .
                            Controller::htmlDisplayText($dsOrdline->getValue('supplierName')) . '</A>';
                    } else {
                        $supplierName = Controller::htmlDisplayText($dsOrdline->getValue("supplierName"));
                    }

                    if (!$restrictedView) {

                        $this->template->set_var(
                            array(
                                'stockcat'                => $dsOrdline->getValue("stockcat"),
                                'renewalIcon'             => $renewalIcon,
                                'lineSupplierName'        => $supplierName,
                                'curUnitCost'             => $dsOrdline->getValue("curUnitCost"),
                                'curCostTotal'            => Controller::formatNumber($curCostTotal),
                                'curUnitSale'             => $dsOrdline->getValue("curUnitSale"),
                                'curSaleTotal'            => Controller::formatNumber($curSaleTotal),
                                'curProfit'               => Controller::formatNumber($curProfit),
                                'percProfit'              => Controller::formatNumber(
                                    $percProfit,
                                    1
                                ),
                                'orderLineProfitClass'    => ($curProfit < 0) ? CTSALESORDER_CLS_ORDER_LINE_LOSS : CTSALESORDER_CLS_ORDER_LINE_ITEM,
                                'orderLineSaleTotalClass' => ($curSaleTotal < 0) ? CTSALESORDER_CLS_ORDER_LINE_LOSS : CTSALESORDER_CLS_ORDER_LINE_ITEM,
                                'orderLineCostTotalClass' => ($curCostTotal < 0) ? CTSALESORDER_CLS_ORDER_LINE_LOSS : CTSALESORDER_CLS_ORDER_LINE_ITEM
                                //,
                            )
                        );
                        $curSaleGrandTotal += $curSaleTotal;
                        $curProfitGrandTotal += $curProfit;
                        $curCostGrandTotal += $curCostTotal;

                        if (!$readOnly) {

                            $urlUpdateItemPrice =
                                $this->buildLink(
                                    $_SERVER['PHP_SELF'],
                                    array(
                                        'action'      => 'updateItemPrice',
                                        'ordheadID'   => $this->getOrdheadID(),
                                        'itemID'      => $dsOrdline->getValue("itemID"),
                                        'curUnitCost' => $dsOrdline->getValue("curUnitCost"),
                                        'curUnitSale' => $dsOrdline->getValue("curUnitSale")
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
                    'orderLines',
                    'orderLineBlock',
                    true
                );
                $this->template->set_var(
                    'salesOrderAddIcon',
                    ''
                ); // clears for next time
                $this->template->set_var(
                    'salesOrderLineIcons',
                    ''
                ); // clears for next time
                $this->template->set_var(
                    'salesOrderLineUpdateItemPriceIcon',
                    ''
                ); // clears for next time
            } while ($dsOrdline->fetchNext());
        }
        // END OF ORDER LINES SECTION

        // Order totals
        if ($curCostGrandTotal != 0) {
            $percProfitGrandTotal = $curProfitGrandTotal * (100 / $curCostGrandTotal);
        } else {
            $percProfitGrandTotal = 0;
        }
        $this->template->set_var(
            array(
                'curSaleGrandTotal'     => Controller::formatNumber($curSaleGrandTotal),
                'curCostGrandTotal'     => Controller::formatNumber($curCostGrandTotal),
                'curProfitGrandTotal'   => Controller::formatNumber($curProfitGrandTotal),
                'percProfitGrandTotal'  => Controller::formatNumber(
                    $percProfitGrandTotal,
                    1
                ),
                'orderTotalProfitClass' => ($curProfitGrandTotal < 0) ? CTSALESORDER_CLS_ORDER_TOTAL_LOSS : CTSALESORDER_CLS_ORDER_TOTAL_ITEM,
                'orderTotalSaleClass'   => ($curSaleGrandTotal < 0) ? CTSALESORDER_CLS_ORDER_TOTAL_LOSS : CTSALESORDER_CLS_ORDER_TOTAL_ITEM,
                'orderTotalCostClass'   => ($curCostGrandTotal < 0) ? CTSALESORDER_CLS_ORDER_TOTAL_LOSS : CTSALESORDER_CLS_ORDER_TOTAL_ITEM
            )
        );
        // End of order totals

        if (!$restrictedView) {

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
                ($orderType == 'Q') &
                ($dsOrdline->rowCount() > 0)
            ) {
                if (($this->getSalutation() == '') & (!$this->getFormError())) {
                    $this->setSalutation('Dear ' . $dsDeliveryContact->getValue('firstName'));
                }
                if (($this->getIntroduction() == '') & (!$this->getFormError())) {
                    if ($dsOrdhead->getValue('quotationIntroduction')) {
                        $this->setIntroduction($dsOrdhead->getValue('quotationIntroduction'));
                    } else {
                        $this->setIntroduction(CTSALESORDER_TXT_INTRODUCTION);
                    }
                }
                if (($this->getEmailSubject() == '') & (!$this->getFormError())) {
                    if ($dsOrdhead->getValue('quotationSubject')) {
                        $this->setEmailSubject($dsOrdhead->getValue('quotationSubject'));
                    }
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
            } // if ($orderType=='Q') &	($dsOrdline->rowCount() > 0 )

            if ($thereAreQuoteDocuments) {
                $this->dsQuotation->initialise();
                $this->template->set_block(
                    'SalesOrderDisplayQuotes',
                    'quotationBlock',
                    'quotations'
                );
                while ($this->dsQuotation->fetchNext()) {
                    $displayQuoteDocURL =
                        $this->buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'      => CTSALESORDER_ACT_DISPLAY_QUOTE_DOC,
                                'quotationID' => $this->dsQuotation->getValue("quotationID")
                            )
                        );
                    $quoteSent = ($this->dsQuotation->getValue("sentDateTime") != '0000-00-00 00:00:00');
                    if (!$quoteSent) {
                        $sendQuoteDocURL =
                            $this->buildLink(
                                $_SERVER['PHP_SELF'],
                                array(
                                    'action'      => CTSALESORDER_ACT_SEND_QUOTE_DOC,
                                    'quotationID' => $this->dsQuotation->getValue("quotationID")
                                )
                            );
                        $deleteQuoteDocURL =
                            $this->buildLink(
                                $_SERVER['PHP_SELF'],
                                array(
                                    'action'      => CTSALESORDER_ACT_DELETE_QUOTE_DOC,
                                    'quotationID' => $this->dsQuotation->getValue("quotationID")
                                )
                            );
                        $txtDelete = CTSALESORDER_TXT_DELETE;
                        $txtSendQuote = CTSALESORDER_TXT_SEND;
                        $quoteSentDateTime = 'Not sent';
                    } else {
                        $sendQuoteDocURL = '';
                        $deleteQuoteDocURL = '';
                        $txtDelete = '';
                        $txtSendQuote = '';
                        $quoteSentDateTime = date(
                            "j/n/Y H:i:s",
                            strtotime($this->dsQuotation->getValue("sentDateTime"))
                        );
                    }
                    $this->template->set_var(
                        array(
                            'displayQuoteDocURL' => $displayQuoteDocURL,
                            'sendQuoteDocURL'    => $sendQuoteDocURL,
                            'deleteQuoteDocURL'  => $deleteQuoteDocURL,
                            'txtSendQuote'       => $txtSendQuote,
                            'txtDelete'          => $txtDelete,
                            'quoteVersionNo'     => $this->dsQuotation->getValue("versionNo"),
                            'quoteSentDateTime'  => $quoteSentDateTime,
                            'quoteUserName'      => $this->dsQuotation->getValue("userName"),
                            'documentType'       => $this->dsQuotation->getValue("documentType")
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
                    $this->buildLink(
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
                $requiredByDateValue = "";
                if ($project->fetchNext() && $project->getValue(DBEProject::commenceDate)) {
                    $requiredByDateValue = Controller::dateYMDtoDMY($project->getValue(DBEProject::commenceDate));
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

            $this->template->parse(
                'salesOrderDisplayUpdateLines',
                'SalesOrderDisplayUpdateLines',
                true
            );
            if ($dsOrdline->rowCount() == 0) {
                $this->setSequenceNo(1);
            } else {
                $this->setSequenceNo($dsOrdline->getValue("sequenceNo") + 1);
            }
            $this->buSalesOrder->initialiseNewOrdline(
                $this->getOrdheadID(),
                $this->getSequenceNo(),
                $this->dsOrdline
            );
            $_SESSION['urlReferer'] =                    // so called functions know where to come back to
                $this->buildLink(
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
            $this->template->parse(
                'salesOrderDisplayNewLine',
                'SalesOrderDisplayNewLine',
                true
            );
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
        if (($orderType == 'P') OR ($orderType == 'C')) {
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
            (!common_isAnInternalStockLocation($dsOrdhead->getValue('customerID')))
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
     */
    function displaySalesOrderHeader(&$dsOrdhead)
    {
        $title = $this->getTypeDescription($dsOrdhead->getValue('type'));
        if ($dsOrdhead->getValue('type') != 'Q') {
            $title .= ' Sales Order';
        }
        $this->setPageTitle($title);
        $originalQuoteURL =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => CTCNC_ACT_DISP_SALESORDER,
                    'ordheadID' => $dsOrdhead->getValue("quotationOrdheadID")
                )
            );

        $customerDisplayURL =
            $this->buildLink(
                'Customer.php',
                array(
                    'action'     => 'dispEdit',
                    'customerID' => $dsOrdhead->getValue("customerID")
                )
            );
        $this->template->set_var(
            array(
                'customerDisplayURL' => $customerDisplayURL,
                'ordheadID'          => $dsOrdhead->getValue('ordheadID'),
                'fromOrdheadID'      => $this->getFromOrdheadID(),
                'customerID'         => $dsOrdhead->getValue('customerID'),
                'customerName'       => $dsOrdhead->getValue('customerName'),
                'date'               => strftime(
                    "%d/%m/%Y",
                    strtotime($dsOrdhead->getValue('date'))
                ),
                'requestedDate'      => ($dsOrdhead->getValue('requestedDate') != '0000-00-00' ? $dsOrdhead->getValue(
                    'requestedDate'
                ) : 'N/A'),
                'promisedDate'       => ($dsOrdhead->getValue('promisedDate') != '0000-00-00' ? $dsOrdhead->getValue(
                    'promisedDate'
                ) : 'N/A'),
                'expectedDate'       => ($dsOrdhead->getValue('expectedDate') != '0000-00-00' ? $dsOrdhead->getValue(
                    'expectedDate'
                ) : 'N/A'),
                'quotationOrdheadID' => substr(
                    $dsOrdhead->getValue("quotationOrdheadID"),
                    0,
                    30
                ),
                'originalQuoteURL'   => $originalQuoteURL,
                'custPORef'          => $dsOrdhead->getValue('custPORef'),
                'partInvoiceChecked' => $this->getChecked($dsOrdhead->getValue('partInvoice')),
                'addItemChecked'     => $this->getChecked($dsOrdhead->getValue('addItem')),
                'addCustomerItem'    => ($dsOrdhead->getValue('addItem') == 'Y') ? 'Yes' : 'No',
                'vat'                => $dsOrdhead->getValue('vatCode') . ' ' . $dsOrdhead->getValue('vatRate'),
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
            $payMethodSelected = ($dsOrdhead->getValue("paymentTermsID") == $dbePaymentTerms->getValue(
                'paymentTermsID'
            ) ? CT_SELECTED : '');
            $this->template->set_var(
                array(
                    'payMethodSelected' => $payMethodSelected,
                    'paymentTermsID'    => $dbePaymentTerms->getValue('paymentTermsID'),
                    'payMethodDesc'     => $dbePaymentTerms->getValue('description')
                )
            );
            $this->template->parse(
                'payMethods',
                'payMethodBlock',
                true
            );
        }// foreach
    } // End function Display Sales Order Header

    /**
     * This function deals with the case where another user has updated the order we are attempting
     * to update.
     * $pageDate has been POSTED from the page and indicates the version of the record we intend to change
     * if, however, the orderDate is different then another user has already updated the record and so
     * we must not. Instead, we display a message and load the latest version of the record.
     * @access private
     */
    function checkUpdatedByAnotherUser($pageDate,
                                       $orderDate
    )
    {
        $this->setMethodName('checkUpdatedByAnotherUser');
        if ($pageDate <> $orderDate) {
            $this->setFormErrorMessage(
                '** ANOTHER USER HAS UPDATED THE ORDER **. Your action was abandoned and the latest version is now shown on this page'
            );
            $this->displayOrder();
            exit;
        }
    }

    /**
     * Edit/Add Order Line
     * @access private
     */
    function editOrderLine()
    {
        $this->setMethodName('editOrderLine');
        if ($this->getOrdheadID() == '') {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return;
        }
        if (!$this->buSalesOrder->getOrdheadByID(
            $this->getOrdheadID(),
            $dsOrdhead
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDER_NOT_FND);
            return;
        }
        $this->checkUpdatedByAnotherUser(
            $_REQUEST['updatedTime'],
            $dsOrdhead->getValue('updatedTime')
        );
        if (($dsOrdhead->getValue('type') != 'Q') & ($dsOrdhead->getValue('type') != 'I')) {
            $this->displayFatalError(CTSALESORDER_MSG_MUST_BE_QUOTE_OR_INITIAL);
            return;
        }
        if ($this->getSequenceNo() == '') {
            $this->displayFatalError(CTSALESORDER_MSG_SEQNO_NOT_PASSED);
            return;
        }
        if (!$this->formError) {
            if ($this->getAction() == CTSALESORDER_ACT_EDIT_ORDLINE) {
                if (!$this->buSalesOrder->getOrdlineByIDSeqNo(
                    $this->getOrdheadID(),
                    $this->getSequenceNo(),
                    $this->dsOrdline
                )) {
                    $this->displayFatalError(CTSALESORDER_MSG_ORDLINE_NOT_FND);
                    return;
                }
            } else {
                $this->buSalesOrder->initialiseNewOrdline(
                    $this->getOrdheadID(),
                    $this->getSequenceNo(),
                    $this->dsOrdline
                );
            }
        }
        $this->setTemplateFiles(
            array(
                'SalesOrderHeadDisplay' => 'SalesOrderHeadDisplay.inc',
                'SalesOrderLineEdit'    => 'SalesOrderLineEdit.inc',
                'SalesOrderLineEditJS'  => 'SalesOrderLineEditJS.inc' // javascript
            )
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

    function orderLineForm(&$dsOrdhead,
                           $parentPage = 'SalesOrderLineEdit'
    )
    {
        // Lines
        if ($this->dsOrdline->getValue("lineType") != "I") {                    // Comment line
            $this->template->set_var(
                array(
                    'stockcat'     => '',
                    'itemID'       => '',
                    'description'  => htmlspecialchars($this->dsOrdline->getValue("description")),
                    'supplierName' => '',
                    'supplierID'   => '',
                    'qtyOrdered'   => '',
                    'curUnitCost'  => '',
                    'curUnitSale'  => ''
                )
            );
        } else {                                                                                                // Item line
            $this->template->set_var(
                array(
                    'stockcat'              => $this->dsOrdline->getValue("stockcat"),
                    'itemID'                => $this->dsOrdline->getValue("itemID"),
                    'description'           => htmlspecialchars($this->dsOrdline->getValue("description")),
                    'supplierName'          => htmlspecialchars($this->dsOrdline->getValue("supplierName")),
                    'supplierID'            => $this->dsOrdline->getValue("supplierID"),
                    'qtyOrdered'            => $this->dsOrdline->getValue("qtyOrdered"),
                    'curUnitCost'           => $this->dsOrdline->getValue("curUnitCost"),
                    'curUnitSale'           => $this->dsOrdline->getValue("curUnitSale"),
                    'renewalCustomerItemID' => $this->dsOrdline->getValue("renewalCustomerItemID")
                )
            );
        }
        if (($this->formError) & ($parentPage == 'SalesOrderLineEdit')) {
            $this->template->set_var(
                array(
                    'descriptionMessage'  => $this->dsOrdline->getValue("descriptionMessage"),
                    'supplierNameMessage' => $this->dsOrdline->getValue("supplierNameMessage"),
                    'qtyOrderedMessage'   => $this->dsOrdline->getValue("qtyOrderedMessage"),
                    'curUnitCostMessage'  => $this->dsOrdline->getValue("curUnitCostMessage"),
                    'curUnitSaleMessage'  => $this->dsOrdline->getValue("curUnitSaleMessage")
                )
            );
        }
        if ($this->getAction() == CTSALESORDER_ACT_EDIT_ORDLINE) {
            $urlSubmit =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSALESORDER_ACT_UPDATE_ORDLINE
                    )
                );
        } else {
            $urlSubmit =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSALESORDER_ACT_INSERT_ORDLINE
                    )
                );
        }
        $urlCancel = $this->getDisplayOrderURL();
        $urlItemPopup =
            $this->buildLink(
                CTCNC_PAGE_ITEM,
                array(
                    'action'  => CTCNC_ACT_DISP_ITEM_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $urlItemEdit =
            $this->buildLink(
                CTCNC_PAGE_ITEM,
                array(
                    'action'  => CTCNC_ACT_ITEM_EDIT,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $urlSupplierPopup =
            $this->buildLink(
                CTCNC_PAGE_SUPPLIER,
                array(
                    'action'  => CTCNC_ACT_DISP_SUPPLIER_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $urlSupplierEdit =
            $this->buildLink(
                CTCNC_PAGE_SUPPLIER,
                array(
                    'action'  => CTCNC_ACT_SUPPLIER_EDIT,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

        $this->template->set_var(
            array(
                'sequenceNo'       => $this->dsOrdline->getValue("sequenceNo"),
                'ordheadID'        => $this->dsOrdline->getValue("ordheadID"),
                'urlSubmit'        => $urlSubmit,
                'urlItemPopup'     => $urlItemPopup,
                'urlItemEdit'      => $urlItemEdit,
                'urlSupplierPopup' => $urlSupplierPopup,
                'urlSupplierEdit'  => $urlSupplierEdit,
                'urlCancel'        => $urlCancel,
                'updatedTime'      => $dsOrdhead->getValue('updatedTime')
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
                    "S" => "Sales Order"
                );
        }

        $this->template->set_block(
            $parentPage,
            'lineTypeBlock',
            'lineTypes'
        );
        foreach ($lineTypeArray as $key => $value) {
            $lineTypeSelected = ($this->dsOrdline->getValue("lineType") == $key) ? CT_SELECTED : '';
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
        // pasting lines from another Sales Order
        if ($_REQUEST['ordline'][1]['lineType'] == 'S') {
            $this->pasteLinesFromSO();
            exit;
        }
        $this->dsOrdline = new DataSet($this);
        $dbeOrdline = new DBEOrdline($this);
        $this->dsOrdline->copyColumnsFrom($dbeOrdline);
        $this->dsOrdline->addColumn(
            'descriptionMessage',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsOrdline->addColumn(
            'supplierName',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsOrdline->addColumn(
            'supplierNameMessage',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsOrdline->addColumn(
            'qtyOrderedMessage',
            DA_STRING,
            DA_NOT_NULL
        );
        $this->dsOrdline->addColumn(
            'curUnitCostMessage',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->dsOrdline->addColumn(
            'curUnitSaleMessage',
            DA_STRING,
            DA_ALLOW_NULL
        );
        if ($_REQUEST['ordline'][1]['lineType'] == "I") {                    // Item line
            $this->dsOrdline->setNull(
                'itemID',
                DA_NOT_NULL
            );
            $this->dsOrdline->setNull(
                'supplierID',
                DA_NOT_NULL
            );
            $this->dsOrdline->setNull(
                'qtyOrdered',
                DA_NOT_NULL
            );
            $this->dsOrdline->setNull(
                'curUnitCost',
                DA_NOT_NULL
            );
            $this->dsOrdline->setNull(
                'curUnitSale',
                DA_NOT_NULL
            );
            $this->dsOrdline->setNull(
                'supplierName',
                DA_NOT_NULL
            );
            $this->dsOrdline->setNull(
                'description',
                DA_NOT_NULL
            );
        } else {                                                                                                        // Comment line
            $this->dsOrdline->setNull(
                'itemID',
                DA_ALLOW_NULL
            );
            $this->dsOrdline->setNull(
                'qtyOrdered',
                DA_ALLOW_NULL
            );
            $this->dsOrdline->setNull(
                'curUnitCost',
                DA_ALLOW_NULL
            );
            $this->dsOrdline->setNull(
                'curUnitSale',
                DA_ALLOW_NULL
            );
            $this->dsOrdline->setNull(
                'supplierName',
                DA_ALLOW_NULL
            );
            $this->dsOrdline->setNull(
                'supplierID',
                DA_ALLOW_NULL
            );
            $this->dsOrdline->setNull(
                'description',
                DA_NOT_NULL
            );
        }
        $this->formError = !$this->dsOrdline->populateFromArray($_REQUEST['ordline']);

        $this->setOrdheadID($this->dsOrdline->getValue('ordheadID'));
        if (!$this->buSalesOrder->getOrdheadByID(
            $this->getOrdheadID(),
            $dsOrdhead
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDER_NOT_FND);
            return;
        }
        $this->checkUpdatedByAnotherUser(
            $_REQUEST['updatedTime'],
            $dsOrdhead->getValue('updatedTime')
        );

        // Validate Item line
        if ($this->formError) {                    // Form error so redisplay edit form
            if ($_REQUEST['action'] == CTSALESORDER_ACT_INSERT_ORDLINE) {
                $_REQUEST['action'] = CTSALESORDER_ACT_ADD_ORDLINE;
            } else {
                $_REQUEST['action'] = CTSALESORDER_ACT_UPDATE_ORDLINE;
            }
            $this->setSequenceNo($this->dsOrdline->getValue('sequenceNo'));
            $this->editOrderLine();
            exit;
        }
        if ($_REQUEST['action'] == CTSALESORDER_ACT_INSERT_ORDLINE) {
            $this->buSalesOrder->insertNewOrderLine($this->dsOrdline);
        } else {
            $this->buSalesOrder->updateOrderLine($this->dsOrdline);
        }
        header('Location: ' . $this->getDisplayOrderURL());
    }

    /**
     * Paste lines from another Sales Order onto the end of this one
     * $_REQUEST['ordline'][1]['description'] holds ordheadID of order to paste from
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function pasteLinesFromSO()
    {
        $this->setMethodName('pasteLinesFromSO');
        $this->setOrdheadID($_REQUEST['ordline'][1]['ordheadID']);
        if (!is_numeric($_REQUEST['ordline'][1]['description'])) {
            $this->setFormErrorMessage('Sales order number must be numeric');
            $this->displayOrder();
            return;
        }
        if (!$this->buSalesOrder->getOrdheadByID(
            $_REQUEST['ordline'][1]['description'],
            $dsOrdhead
        )) {
            $this->setFormErrorMessage('The sales order you are trying to paste from does not exist');
            $this->displayOrder();
            return;
        }
        $this->buSalesOrder->pasteLinesFromOrder(
            $_REQUEST['ordline'][1]['description'],
            $this->getOrdheadID()
        );
        $this->displayOrder();
        exit;
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
        $this->moveOrderLineValidation();
        $this->buSalesOrder->moveOrderLineUp(
            $this->getOrdheadID(),
            $this->getSequenceNo()
        );
        header('Location: ' . $this->getDisplayOrderURL());
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
        $this->moveOrderLineValidation();
        $this->buSalesOrder->moveOrderLineDown(
            $this->getOrdheadID(),
            $this->getSequenceNo()
        );
        header('Location: ' . $this->getDisplayOrderURL());
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
        if (!$this->buSalesOrder->getOrdheadByID(
            $this->getOrdheadID(),
            $dsOrdhead
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDER_NOT_FND);
        }
        $this->moveOrderLineValidation();
        $this->buSalesOrder->deleteOrderLine(
            $this->getOrdheadID(),
            $this->getSequenceNo()
        );
        header('Location: ' . $this->getDisplayOrderURL());
    }

    function moveOrderLineValidation()
    {
        if ($this->getOrdheadID() == '') {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
        }
        if (!$this->buSalesOrder->getOrdheadByID(
            $this->getOrdheadID(),
            $dsOrdhead
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDER_NOT_FND);
        }
        $this->checkUpdatedByAnotherUser(
            $_REQUEST['updatedTime'],
            $dsOrdhead->getValue('updatedTime')
        );
        if ($this->getSequenceNo() == '') {
            $this->displayFatalError(CTSALESORDER_MSG_SEQNO_NOT_PASSED);
        }
    }
    /*
		$url =
			$this->buildLink(
				$_SERVER['PHP_SELF'],
				array(
					'action'=>CTCNC_ACT_DISP_SALESORDER,
					'ordheadID'=>$this->getOrdheadID()
				)
			);
		header('Location: '. $url);
		exit;
*/
    /**
     * Update order address
     * @access private
     */
    function updateAddress()
    {
        $this->setMethodName('updateAddress');
        if ($this->getOrdheadID() == '') {
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
        if (($dsOrdhead->getValue('type') != 'Q') & ($dsOrdhead->getValue('type') != 'I')) {
            $this->displayFatalError(CTSALESORDER_MSG_MUST_BE_QUOTE_OR_INITIAL);
            return;
        }
        $this->checkUpdatedByAnotherUser(
            $_REQUEST['updatedTime'],
            $dsOrdhead->getValue('updatedTime')
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

    /**
     * Update order contact
     * @access private
     */
    function updateContact()
    {
        $this->setMethodName('updateContact');
        if ($this->getOrdheadID() == '') {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return;
        }
        if ($this->getContactID() == '') {
            $this->displayFatalError(CTSALESORDER_MSG_CONTACTID_NOT_PASSED);
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
        $this->checkUpdatedByAnotherUser(
            $_REQUEST['updatedTime'],
            $dsOrdhead->getValue('updatedTime')
        );
        if (($dsOrdhead->getValue('type') != 'Q') & ($dsOrdhead->getValue('type') != 'I')) {
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

    /**
     * upload a quote document from local client
     * @access private
     */
    function uploadQuoteDoc()
    {
        $this->setMethodName('uploadQuoteDoc');
        if ($this->getOrdheadID() == '') {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return FALSE;
        }
        if ($_FILES['quoteFile']['name'] == '') {
            $this->setQuoteFileMessage('You must specify a document to load');
        }
        if ($_FILES['quoteFile']['name'] != '') {                // User has sent a file
            if (!is_uploaded_file($_FILES['quoteFile']['tmp_name'])) {                    // Possible hack?
                $this->setQuoteFileMessage(CTPROJECT_MSG_DOCUMENT_NOT_LOADED);
            }
            if ($_FILES['quoteFile']['size'] == 0) {
                $this->setQuoteFileMessage(CTPROJECT_MSG_DOCUMENT_NOT_LOADED);
            }
        }
        if ($_FILES['userfile']['size'] > CTPROJECT_MAX_DOCUMENT_FILE_SIZE) {
            $this->setQuoteFileMessage(CTPROJECT_MSG_DOCUMENT_TOO_BIG);
        }
        if ($this->formError) {
            $this->displayOrder();                // redisplay with error message(s)
            exit;
        }
        // Insert into database
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
            'versionNo',
            $versionNo
        );
        $this->dsQuotation->setValue(
            'ordheadID',
            $dsOrdhead->getValue('ordheadID')
        );
        $this->dsQuotation->setValue(
            'userID',
            $this->userID
        );
        $this->dsQuotation->setValue(
            'sentDateTime',
            date('0000-00-00 00:00:00')
        );
        $this->dsQuotation->setValue(
            'salutation',
            $this->getSalutation()
        );
        $this->dsQuotation->setValue(
            'fileExtension',
            $extension
        );
        $this->dsQuotation->setValue(
            'documentType',
            'quotation'
        );
        $this->dsQuotation->post();
        $this->buSalesOrder->insertQuotation($this->dsQuotation);
        header('Location: ' . $this->getDisplayOrderURL());
        exit;
    }

    /**
     * generate a PDF quote.
     * @access private
     */
    function generateQuoteDoc()
    {
        $this->setMethodName('generateQuoteDoc');
        if (count($this->postVars['selectedOrderLine']) == 0) {
            $this->setLinesMessage(CTSALESORDER_MSG_NO_LINES);
            $this->displayOrder();
            return FALSE;
        } else {
            $this->setSelectedOrderLines($this->postVars['selectedOrderLine']);
        }
        if ($this->getSalutation() == '') {
            $this->setUserMessage(CTSALESORDER_MSG_SELECT_SALUTATION);
            $this->displayOrder();
            return FALSE;
        }
        if ($this->getIntroduction() == '') {
            $this->setUserMessage(CTSALESORDER_MSG_SELECT_INTRODUCTION);
            $this->displayOrder();
            return FALSE;
        }
        if ($this->getOrdheadID() == '') {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return FALSE;
        }
        //$this->buildQuote($quoteFile, $versionNo);

        $buPDFSalesQuote = new BUPDFSalesQuote($this);
        try {
            $buPDFSalesQuote->generate(
                $this->getOrdheadID(),
                $this->getSalutation(),
                $this->getIntroduction(),
                $this->getEmailSubject(),
                $this->dsSelectedOrderLine
            );
            header('Location: ' . $this->getDisplayOrderURL());
        } catch (\Exception $exception) {
            $this->setUserMessage($exception->getMessage());
            $this->displayOrder();
            return FALSE;
        }
    }

    /**
     * generate a PDF order form.
     * @access private
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
        if ($this->getOrdheadID() == '') {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return FALSE;
        }
        $this->buildOrderForm();
        header('Location: ' . $this->getDisplayOrderURL());
        exit;
    }

    function buildOrderForm()
    {
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
            $this->dsUser->getValue('name'),
            $this->getOrdheadID() . '/' . $versionNo,
            'CNC Ltd',
            'Customer Order Form',
            'A4'
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
        $firstName = $dsDeliveryContact->getValue('firstName');
        $buPDF->printStringAt(
            130,
            $dsDeliveryContact->getValue('title') . ' ' . $firstName{0} . ' ' . $dsDeliveryContact->getValue('lastName')
        );
        $buPDF->CR();
        $buPDF->printStringAt(
            130,
            $dsOrdhead->getValue('customerName')
        );
        $buPDF->CR();
        $buPDF->printStringAt(
            130,
            $dsOrdhead->getValue('delAdd1')
        );
        if ($dsOrdhead->getValue('delAdd2') != '') {
            $buPDF->CR();
            $buPDF->printStringAt(
                130,
                $dsOrdhead->getValue('delAdd2')
            );
        }
        if ($dsOrdhead->getValue('delAdd3') != '') {
            $buPDF->CR();
            $buPDF->printStringAt(
                130,
                $dsOrdhead->getValue('delAdd3')
            );
        }
        $buPDF->CR();
        $buPDF->printStringAt(
            130,
            $dsOrdhead->getValue('delTown')
        );
        if ($dsOrdhead->getValue('delCounty') != '') {
            $buPDF->CR();
            $buPDF->printStringAt(
                130,
                $dsOrdhead->getValue('delCounty')
            );
        }
        $buPDF->CR();
        $buPDF->printStringAt(
            130,
            $dsOrdhead->getValue('delPostcode')
        );
        $buPDF->CR();
        $buPDF->printString($this->dsUser->getValue('firstName') . ' ' . $this->dsUser->getValue('lastName'));
        $buPDF->CR();
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);
        $buPDF->printString($dsHeader->getValue('name'));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue('add1'));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue('add2'));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue('add3'));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue('town'));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue('county'));
        $buPDF->CR();
        $buPDF->printString($dsHeader->getValue('postcode'));
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString(date('l, jS F Y'));
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('Dear ' . $this->dsUser->getValue('firstName') . ',');
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
                'sequenceNo',
                $dsOrdline->getValue("sequenceNo")
            )) {
                if ($dsOrdline->getValue('lineType') == "I") {
                    $buPDF->printStringRJAt(
                        28,
                        $dsOrdline->getValue('qtyOrdered')
                    );
                    if ($dsOrdline->getValue('description') != '') {
                        $buPDF->printStringAt(
                            40,
                            $dsOrdline->getValue('description')
                        );
                    } else {
                        $buPDF->printStringAt(
                            40,
                            $dsOrdline->getValue('itemDescription')
                        );
                    }
                    $buPDF->printStringRJAt(
                        150,
                        Controller::formatNumberCur($dsOrdline->getValue('curUnitSale'))
                    );
                    $total = ($dsOrdline->getValue('curUnitSale') * $dsOrdline->getValue('qtyOrdered'));
                    $grand_total += $total;
                    $buPDF->printStringRJAt(
                        173,
                        Controller::formatNumberCur($total)
                    );
                    if ($dsOrdline->getValue(
                            'itemID'
                        ) != 0) {                        // some item lines in old system did not have a related item record
                        $this->buItem->getItemByID(
                            $dsOrdline->getValue('itemID'),
                            $dsItem
                        );
                    }
                } else {
                    $buPDF->printStringAt(
                        40,
                        $dsOrdline->getValue('description')
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
        $buPDF->printString(
            'Please fax back to 0845 0700 584 to confirm your order or email a scanned copy to sales@cnc-ltd.co.uk'
        );
        $buPDF->endPage();
        // End of second page
        $buPDF->close();

        // Insert into database
        $this->dsQuotation = new DataSet($this);
        $this->dsQuotation->copyColumnsFrom($this->buSalesOrder->dbeQuotation);
        $this->dsQuotation->setUpdateModeInsert();
        $this->dsQuotation->setValue(
            'versionNo',
            $versionNo
        );
        $this->dsQuotation->setValue(
            'ordheadID',
            $dsOrdhead->getValue('ordheadID')
        );
        $this->dsQuotation->setValue(
            'userID',
            $this->userID
        );
        $this->dsQuotation->setValue(
            'sentDateTime',
            date('0000-00-00 00:00:00')
        );
        $this->dsQuotation->setValue(
            'salutation',
            $this->getSalutation()
        );
        $this->dsQuotation->setValue(
            'emailSubject',
            $this->getEmailSubject()
        );
        $this->dsQuotation->setValue(
            'documentType',
            'order form'
        );
        $this->dsQuotation->setValue(
            'fileExtension',
            'pdf'
        );
        $this->dsQuotation->post();
        $this->buSalesOrder->insertQuotation($this->dsQuotation);
    }

    /**
     * send quote.
     * @access private
     */
    function sendQuoteDoc()
    {
        $this->setMethodName('sendQuoteDoc');
        if ($this->getQuotationID() == '') {
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
        $updateDB = TRUE;
        // if this is a PDF file then send an email to the customer else simply st the sent date.
        if ($this->dsQuotation->getValue('fileExtension') == 'pdf') {

            $buPDFSalesQuote = new BUPDFSalesQuote($this);
            $updateDB = $buPDFSalesQuote->sendPDFEmailQuote($this->getQuotationID());

        }
        if ($updateDB) {
            $this->dsQuotation->setUpdateModeUpdate();
            $this->dsQuotation->setValue(
                'sentDateTime',
                date('Y-m-d H:i:s')
            );
            $this->dsQuotation->post();
            $this->buSalesOrder->insertQuotation($this->dsQuotation);
        }
        $this->setOrdheadID($this->dsQuotation->getValue('ordheadID'));
        header('Location: ' . $this->getDisplayOrderURL());
        exit;
    }

    /**
     * genarate a CSV of the selected lines.
     * @access private
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
        if ($this->getOrdheadID() == '') {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return FALSE;
        }

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
                'sequenceNo',
                $dsOrdline->getValue("sequenceNo")
            )) {

                if ($dsOrdline->getValue('itemDescription') != '') {
                    $description = $dsOrdline->getValue('itemDescription');                // from item table
                } else {
                    $description = $dsOrdline->getValue('description');
                }

                if ($dsOrdline->getValue('lineType') == "I") { // item line

                    $qtyOrdered = number_format(
                        $dsOrdline->getValue('qtyOrdered'),
                        2
                    );
                    $unitSale = number_format(
                        $dsOrdline->getValue('curUnitSale'),
                        2
                    );
                    /*
					if we have item notes then add them to the description
					*/
                    if ($dsOrdline->getValue('itemID') != 0) {// some item lines do not have a related item record

                        $this->buItem->getItemByID(
                            $dsOrdline->getValue('itemID'),
                            $dsItem
                        );

                        if ($dsItem->getValue('notes')) {
                            $description .= "\n" . str_replace(
                                    chr(13),
                                    '',
                                    $dsItem->getValue('notes')
                                );
                        }
                    }

                } else {
                    // comment line
                    $qtyOrdered = '';
                    $unitSale = '';
                }

                print    '"' . $qtyOrdered . '","' . $description . '","' . $unitSale . "\"\n";


            }
        }

        $this->pageClose();
        exit;
    }

    /**
     * delete a quote document
     * @access private
     */
    function deleteQuoteDoc()
    {
        $this->setMethodName('deleteQuoteDoc');
        if ($this->getQuotationID() == '') {
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
        $this->setOrdheadID($this->dsQuotation->getValue('ordheadID'));
        $quoteFile =
            'quotes/' .
            $this->dsQuotation->getValue('ordheadID') . '_' . $this->dsQuotation->getValue('versionNo') . '.' .
            $this->dsQuotation->getValue('fileExtension');
        $this->buSalesOrder->deleteQuotationDoc($this->getQuotationID());
        unlink($quoteFile);
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
        if ($this->getQuotationID() == '') {
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
        $quoteFile = $this->dsQuotation->getValue('ordheadID') . '_' . $this->dsQuotation->getValue('versionNo');
        if ($this->dsQuotation->getValue('fileExtension') == '') {
            $quoteFile .= '.pdf';
        } else {
            $quoteFile .= '.' . $this->dsQuotation->getValue(
                    'fileExtension'
                );        // if no extension in DB then assume PDF
        }
        switch ($this->dsQuotation->getValue('fileExtension')) {
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
     */
    function deleteOrder()
    {
        $this->setMethodName('deleteOrder');
        $this->buSalesOrder->deleteOrder($this->getOrdheadID());
        if (isset($_REQUEST['urlCallback'])) {
            $url = $_REQUEST['urlCallback'];
        } else {
            if ($_SESSION['urlReferer'] != '') {
                $url = $_SESSION['urlReferer'];
                $_SESSION['urlReferer'] = '';
            } else {
                $url =
                    $this->buildLink(
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
     * Get and parse order type drop-down selector
     * @access private
     */
    function parseOrderTypeSelector($orderType)
    {
        foreach ($this->orderTypeArray as $key => $value) {
            $orderTypeSelected = ($orderType == $key) ? CT_SELECTED : '';
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

    function parseUserSelector($userID)
    {
        $this->buSalesOrder->getSalesUsers($dsUser);

        while ($dsUser->fetchNext()) {
            $userSelected = ($userID == $dsUser->getValue('userID')) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    'userSelected' => $userSelected,
                    'userID'       => $dsUser->getValue('userID'),
                    'userName'     => $dsUser->getValue('name')
                )
            );
            $this->template->parse(
                'users',
                'userBlock',
                true
            );
        }
    }

    /**
     * Get and parse user drop-down selector
     * @access private
     */
    function parseSiteSelector($siteNo,
                               &$dsSite,
                               $blockVar,
                               $block
    )
    {
        while ($dsSite->fetchNext()) {
            $siteSelected = ($dsSite->getValue(DBESite::siteNo) == $siteNo) ? CT_SELECTED : '';
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
    }

    /**
     * Get and parse contact drop-down selector
     * @access private
     */
    function parseContactSelector($contactID,
                                  &$dsContact,
                                  $blockVar,
                                  $block
    )
    {
        while ($dsContact->fetchNext()) {
            $contactSelected = ($dsContact->getValue('contactID') == $contactID) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    $block . 'Selected'  => $contactSelected,
                    $block . 'ContactID' => $dsContact->getValue('contactID'),
                    $block . 'FirstName' => $dsContact->getValue('firstName'),
                    $block . 'LastName'  => $dsContact->getValue('lastName')
                )
            );
            $this->template->parse(
                $blockVar,
                $block,
                true
            );
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
     */
    function convertToOrder()
    {
        if ($this->getOrdheadID() == '') {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return;
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
    }

    function insertFromOrder()
    {
        if ($this->getOrdheadID() == '') {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return;
        }
        if ($this->getFromOrdheadID() == '') {
            $this->setLinesMessage('No From Sales Order entered');
            $this->displayOrder();
            return false;
        }

        if (!$this->buSalesOrder->getOrdheadByID(
            $this->getFromOrdheadID(),
            $dsOrdhead,
            $dsOrdline
        )) {
            $this->setLinesMessage('From Sales Order not found');
            $this->displayOrder();
            return false;
        }

        if (count($this->postVars['selectedOrderLine']) != 1) {
            $this->setLinesMessage('Select a line before which to insert order');
            $this->displayOrder();
            return FALSE;
        }
        $this->buSalesOrder->pasteLinesFromOrder(
            $this->getFromOrdheadID(),
            $this->getOrdheadID(),
            false,
            $this->postVars['selectedOrderLine'][0]
        );
        header('Location: ' . $this->getDisplayOrderURL());
        exit;
    }


    /**
     * Delete order lines
     *    Deletes selected lines from order
     * @access private
     */
    function deleteLines()
    {
        if ($this->getOrdheadID() == '') {
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

    function changeSupplier()
    {
        if ($this->getOrdheadID() == '') {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return;
        }
        if ($this->getUpdateSupplierID() == '') {
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

    /**
     * Update order header details
     * @access private
     */
    function updateHeader()
    {
        $this->setMethodName('updateHeader');
        if ($this->getOrdheadID() == '') {
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return;
        }
        if (!$this->buSalesOrder->getOrdheadByID(
            $this->getOrdheadID(),
            $dsOrdhead
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDER_NOT_FND);
            return;
        }
        $this->checkUpdatedByAnotherUser(
            $_REQUEST['updatedTime'],
            $dsOrdhead->getValue('updatedTime')
        );

        if (($dsOrdhead->getValue('type') != 'Q') & ($dsOrdhead->getValue('type') != 'I')) {
            $this->displayFatalError(CTSALESORDER_MSG_MUST_BE_QUOTE_OR_INITIAL);
            return;
        }
        $this->buSalesOrder->updateHeader(
            $this->getOrdheadID(),
            $_REQUEST['form']['custPORef'],
            $_REQUEST['form']['paymentTermsID'],
            $_REQUEST['form']['partInvoice'] == 'Y' ? 'Y' : 'N',
            $_REQUEST['form']['addItem'] == 'Y' ? 'Y' : 'N'
        );
        header('Location: ' . $this->getDisplayOrderURL());
        exit;
    }

    /**
     * Update order lines details
     * This is when the Update button has been clicked from the SalesOrderDisplay screen
     * and processes an array of order line information:
     * Qty, Unit Cost and Unit Sale.
     * @access private
     */
    function updateLines()
    {
        $this->setMethodName('updateLines');
        if ($_REQUEST['ordheadID'] == '') {
            //if ($this->getOrdheadID()==''){
            $this->displayFatalError(CTSALESORDER_MSG_ORDHEADID_NOT_PASSED);
            return;
        } else {
            $this->setOrdheadID($_REQUEST['ordheadID']);
        }
        if (!$this->buSalesOrder->getOrdheadByID(
            $this->getOrdheadID(),
            $dsOrdhead
        )) {
            $this->displayFatalError(CTSALESORDER_MSG_ORDER_NOT_FND);
            return;
        }
        $this->checkUpdatedByAnotherUser(
            $_REQUEST['updatedTime'],
            $dsOrdhead->getValue('updatedTime')
        );
        if (($dsOrdhead->getValue('type') != 'Q') & ($dsOrdhead->getValue('type') != 'I')) {
            $this->displayFatalError(CTSALESORDER_MSG_MUST_BE_QUOTE_OR_INITIAL);
            return;
        }
        $dbeJOrdline = new DBEJOrdline($this);
        $this->dsOrdline = new DataSet($this);
        $this->dsOrdline->copyColumnsFrom($dbeJOrdline);
        if (!$this->dsOrdline->populateFromArray($_REQUEST['ordline'])) {
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
     * send an emailed PDF order confirmation to delivery contact.
     * @access private
     */
    function sendOrderConfirmation()
    {
        $this->setMethodName('sendOrderConfirmation');
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
        $this->setSalutation('Dear ' . $dsDeliveryContact->getValue('firstName'));
        $tempFile = tempnam(
            '/tmp',
            'CNF'
        );                // temporary disk file
        $buPDF = new BUPDF(
            $this,
            $tempFile,
            $this->dsUser->getValue('name'),
            $this->getOrdheadID() . '/' . $versionNo,
            'CNC Ltd',
            'Order Confirmation',
            'A4'
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
        $firstName = $dsDeliveryContact->getValue('firstName');
        $buPDF->printString(
            $dsDeliveryContact->getValue('title') . ' ' . $firstName{0} . ' ' . $dsDeliveryContact->getValue('lastName')
        );
        $buPDF->CR();
        $buPDF->printString($dsOrdhead->getValue('customerName'));
        $buPDF->CR();
        $buPDF->printString($dsOrdhead->getValue('delAdd1'));
        if ($dsOrdhead->getValue('delAdd2') != '') {
            $buPDF->CR();
            $buPDF->printString($dsOrdhead->getValue('delAdd2'));
        }
        if ($dsOrdhead->getValue('delAdd3') != '') {
            $buPDF->CR();
            $buPDF->printString($dsOrdhead->getValue('delAdd3'));
        }
        $buPDF->CR();
        $buPDF->printString($dsOrdhead->getValue('delTown'));
        if ($dsOrdhead->getValue('delCounty') != '') {
            $buPDF->CR();
            $buPDF->printString($dsOrdhead->getValue('delCounty'));
        }
        $buPDF->CR();
        $buPDF->printString($dsOrdhead->getValue('delPostcode'));
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString(date('l, jS F Y'));
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString($this->getSalutation() . ',');
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('Following receipt of your official order,');
        if ($dsOrdhead->getValue('custPORef') != '') {
            $buPDF->printString(' (Ref: ' . $dsOrdhead->getValue('custPORef') . '),');
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
            if ($dsOrdline->getValue('lineType') == "I") {
                if ($dsOrdline->getValue('description') != '') {
                    $buPDF->printStringAt(
                        40,
                        $dsOrdline->getValue('description')
                    );
                } else {
                    $buPDF->printStringAt(
                        40,
                        $dsOrdline->getValue('itemDescription')
                    );
                }
                $buPDF->printStringRJAt(
                    30,
                    Controller::formatNumber(
                        $dsOrdline->getValue('qtyOrdered'),
                        2
                    )
                );
                /*
Do not print zero sale values
*/
                if ($dsOrdline->getValue('curUnitSale') != 0) {
                    $buPDF->printStringRJAt(
                        150,
                        Controller::formatNumberCur($dsOrdline->getValue('curUnitSale'))
                    );
                    $total = ($dsOrdline->getValue('curUnitSale') * $dsOrdline->getValue('qtyOrdered'));
                    $buPDF->printStringRJAt(
                        170,
                        Controller::formatNumberCur($total)
                    );
                    $grandTotal += $total;
                }
                if ($dsOrdline->getValue(
                        'itemID'
                    ) != 0) {                        // some item lines in old system did not have a related item record
                    $this->buItem->getItemByID(
                        $dsOrdline->getValue('itemID'),
                        $dsItem
                    );
                    /*
now that the notes are in a text field we need to split the lines up for the PDF printing
*/
                    if ($dsItem->getValue('notes') != '') {
                        $buPDF->setFontSize(8);
                        $buPDF->setFont();
                        $notesArray = explode(
                            chr(13) . chr(10),
                            $dsItem->getValue('notes')
                        );
                        foreach ($notesArray as $noteLine) {
                            if (trim($noteLine) != '') {                    // ignore blank lines
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
                    $dsOrdline->getValue('description')
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
        if ($this->dsUser->getValue('signatureFilename') != '') {
            $buPDF->placeImageAt(
                IMAGES_DIR . '/' . $this->dsUser->getValue('signatureFilename'),
                'PNG',
                10,
                35
            );
        }
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString($this->dsUser->getValue('firstName') . ' ' . $this->dsUser->getValue('lastName'));
        $buPDF->CR();
        $buPDF->setBoldOn();
        $buPDF->setFont();
        $buPDF->printString($this->dsUser->getValue('jobTitle'));
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('E. & O. E.');
        $buPDF->endPage();
        $buPDF->close();
        $senderEmail = $this->dsUser->getValue('username') . '@cnc-ltd.co.uk';
        $senderName = $this->dsUser->getValue('firstName') . ' ' . $this->dsUser->getValue('lastName');
        // Send email with attachment
        $message = '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
        $message .= $this->getSalutation();
        $message .= '<o:p></o:p></span></font></p>';
        $message .= '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
        $message .= 'Please find attached confirmation of your recent order.';
        $message .= '<o:p></o:p></span></font></p>';
        $subject = 'Your confirmation ' . $dsOrdhead->getValue('ordheadID');
        $filename = $dsOrdhead->getValue('ordheadID') . '.pdf';
        $mime_boundary = "----=_NextPart_" . md5(time());
        unset($headers);
        $headers .= "From: " . $senderName . " <" . $senderEmail . ">\r\n";
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
            $dsOrdhead->getValue('delContactEmail'),
            $subject,
            $mime_message,
            $headers
        );
        unlink($tempFile);
        header('Location: ' . $this->getDisplayOrderURL());
    }

    /**
     * @access private
     */
    function getDisplayOrderURL()
    {
        return $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'    => CTCNC_ACT_DISP_SALESORDER,
                'ordheadID' => $this->getOrdheadID()
            )
        );

    }

    function updateItemPrice()
    {
        $this->setMethodName('updateItemPrice');

        $dbeItem = new DBEItem($this);
        $dbeItem->getRow($_REQUEST['itemID']);
        $dbeItem->setValue(
            'curUnitSale',
            $_REQUEST['curUnitSale']
        );
        $dbeItem->setValue(
            'curUnitCost',
            $_REQUEST['curUnitCost']
        );
        $dbeItem->updateRow();

        header('Location: ' . $this->getDisplayOrderURL());

    }

    /*
  Store selected SO lines in session var then redirect to serviceRequest()
  */
    function serviceRequestFromLines()
    {
        if (count($this->postVars['selectedOrderLine']) == 0) {
            $this->setLinesMessage(CTSALESORDER_MSG_NO_LINES);
            $this->displayOrder();
            return FALSE;
        } else {
            $_SESSION['selectedOrderLine'] = $this->postVars['selectedOrderLine'];
            $redirectUrl =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => 'serviceRequest',
                        'ordheadID' => $this->getOrdheadID()
                    )
                );
            header('Location: ' . $redirectUrl);
        }

    }

    function serviceRequest()
    {
        $this->setMethodName('serviceRequest');

        //$this->dsSelectedOrderLine
        $buActivity = new BUActivity($this);

        if ($this->getOrdheadID() != '') {
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
            'etaDate',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsInput->addColumn(
            'serviceRequestCustomerItemID',
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsInput->addColumn(
            'serviceRequestPriority',
            DA_INTEGER,
            DA_NOT_NULL
        );
        $dsInput->addColumn(
            'serviceRequestText',
            DA_STRING,
            DA_ALLOW_NULL
        );
        /*
    get existing values
    */
        if (
            $dsOrdhead->getValue('serviceRequestText') &&
            $dsInput->getValue('serviceRequestText') == ''                 // not set yet
        ) {
            $dsInput->setValue(
                'serviceRequestText',
                $dsOrdhead->getValue('serviceRequestText')
            );
            $dsInput->setValue(
                'serviceRequestCustomerItemID',
                $dsOrdhead->getValue('serviceRequestCustomerItemID')
            );
            $dsInput->setValue(
                'serviceRequestPriority',
                $dsOrdhead->getValue('serviceRequestPriority')
            );
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $formError = !$dsInput->populateFromArray($_REQUEST['inputForm']);

            if ($dsInput->getValue('serviceRequestCustomerItemID') == 99) {
                $formError = true;
            }

            if ($dsInput->getValue('serviceRequestPriority') == 0) {
                $formError = true;
            }

            if (!$formError) {

                if ($dsInput->getValue('etaDate') != '') {

                    $buActivity->createSalesServiceRequest(
                        $this->getOrdheadID(),
                        $dsInput,
                        $_SESSION['selectedOrderLine']
                    );
                    unset($_SESSION['selectedOrderLine']);
                } else {
                    $this->buSalesOrder->updateServiceRequestDetails(
                        $this->getOrdheadID(),
                        $dsInput->getValue('serviceRequestCustomerItemID'),
                        $dsInput->getValue('serviceRequestPriority'),
                        $dsInput->getValue('serviceRequestText')
                    );
                }
                /*
        redirect to order
        */
                header('Location: ' . $this->getDisplayOrderURL());

                //echo '<script language="javascript">window.close()</script>;';
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

            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => 'serviceRequest',
                    'ordheadID' => $this->getOrdheadID()
                )
            );

        $this->template->set_var(
            array(
                'etaDate'        => Controller::dateYMDtoDMY($dsInput->getValue('etaDate')),
                'etaDateMessage' => $dsInput->getMessage('etaDate'),

                'serviceRequestText'                  => $dsInput->getValue('serviceRequestText'),
                'serviceRequestPriorityMessage'       => $dsInput->getMessage('serviceRequestPriority'),
                'serviceRequestCustomerItemIDMessage' => $dsInput->getMessage('serviceRequestCustomerItemID'),

                'urlSubmit' => $urlSubmit
            )
        );

        $this->contractDropdown(
            $dsOrdhead->getValue('customerID'),
            $dsInput->getValue('serviceRequestCustomerItemID'),
            'SalesOrderServiceRequest',
            'contractBlock'
        );

        $this->priorityDropdown(
            $dsInput->getValue('serviceRequestPriority'),
            'SalesOrderServiceRequest',
            'priorityBlock',
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
                    'standardTextContent'     => htmlentities($dbeStandardText->getValue('stt_text')),
                    'standardTextDescription' => $dbeStandardText->getValue('stt_desc')
                )
            );
            $this->template->parse(
                'rows',
                $block,
                true
            );
        }

    }

    function contractDropdown(
        $customerID,
        $serviceRequestCustomerItemID,
        $templateName = 'SalesOrderVisitRequest',
        $blockName = 'contractBlock'
    )
    {

        $includeExpired = false;

        $buCustomerItem = new BUCustomerItem($this);
        $buCustomerItem->getContractsByCustomerID(
            $customerID,
            $dsContract,
            $includeExpired
        );

        if ($serviceRequestCustomerItemID == '99') {
            $this->template->set_var(
                array(
                    'noContractSelected' => CT_SELECTED
                )
            );
        }
        if ($serviceRequestCustomerItemID == '') {
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
                    "customerItemID"
                )) ? CT_SELECTED : '';

            $this->template->set_var(
                array(
                    'contractSelected'             => $contractSelected,
                    'serviceRequestCustomerItemID' => $dsContract->getValue("customerItemID"),
                    'contractDescription'          => $dsContract->getValue("itemDescription")
                )
            );
            $this->template->parse(
                'contracts',
                $blockName,
                true
            );
        }

    } // end contractDropdown

    function priorityDropdown(
        $serviceRequestPriority,
        $templateName = 'SalesOrderVisitRequest',
        $blockName = 'priorityBlock',
        $buActivity
    )
    {

        $this->template->set_block(
            $templateName,
            $blockName,
            'priorities'
        );

        foreach ($buActivity->priorityArray as $priority => $priorityDescription) {

            $prioritySelected = ($serviceRequestPriority == $priority) ? CT_SELECTED : '';

            $this->template->set_var(
                array(
                    'prioritySelected'    => $contractSelected,
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

    } // end contractDropdown

    function getCurrentDocumentsLink($customerID,
                                     &$buCustomer
    )
    {

        if (!$buCustomer) {
            $buCustomer = new BUCustomer($this);
        }

        if ($buCustomer->customerFolderExists($customerID)) {

            $currentDocumentsPath = $buCustomer->checkCurrentDocumentsFolderExists($customerID);

            $currentDocumentsLink = '<a href="file:' . $currentDocumentsPath . '" target="_blank" title="Documentation">Documentation</a>';
        } else {
            $currentDocumentsLink = '';
        }

        return $currentDocumentsLink;

    }

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
        $buSalesOrderDocument->getDocumentsByOrdheadID(
            $ordheadID,
            $dsSalesOrderDocument
        );

        $urlAddDocument =
            $this->buildLink(
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
                $this->buildLink(
                    'SalesOrderDocument.php',
                    array(
                        'action'               => 'edit',
                        'salesOrderDocumentID' => $dsSalesOrderDocument->getValue('salesOrderDocumentID')
                    )
                );

            $urlViewFile =
                $this->buildLink(
                    'SalesOrderDocument.php',
                    array(
                        'action'               => 'viewFile',
                        'salesOrderDocumentID' => $dsSalesOrderDocument->getValue('salesOrderDocumentID')
                    )
                );

            $urlDeleteDocument =
                $this->buildLink(
                    'SalesOrderDocument.php',
                    array(
                        'action'               => 'delete',
                        'salesOrderDocumentID' => $dsSalesOrderDocument->getValue('salesOrderDocumentID')
                    )
                );

            if ($dsSalesOrderDocument->getValue("createdDate") != '0000-00-00 00:00:00') {
                $createdDate = date_format(
                    date_create($dsSalesOrderDocument->getValue("createdDate")),
                    'd/m/Y H:i:s'
                );
            } else {
                $createdDate = '';

            }

            $this->template->set_var(
                array(
                    'description'       => $dsSalesOrderDocument->getValue("description"),
                    'filename'          => $dsSalesOrderDocument->getValue("filename"),
                    'createdDate'       => $createdDate,
                    'urlViewFile'       => $urlViewFile,
                    'urlEditDocument'   => $urlEditDocument,
                    'urlDeleteDocument' => $urlDeleteDocument
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
    } // end function documents

}// end of class
?>
