<?php /**
 * Sales Order controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUSalesOrder.inc.php');
require_once($cfg['path_bu'] . '/BUNotepad.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg["path_bu"] . "/BUPDF.inc.php");
// Parameters
define('CTSALESORDER_VAL_NONE_SELECTED', -1);
// Actions
define('CTSALESORDER_ACT_DISP_SEARCH', 'dispSearch');
define('CTSALESORDER_ACT_SEARCH', 'search');
define('CTSALESORDER_ACT_DISP_ORDER', 'display');
define('CTSALESORDER_ACT_DISP_TEST_EDIT', 'testEdit');
define('CTSALESORDER_ACT_GENERATE_QUOTE', 'generateQuote');
define('CTSALESORDER_ACT_UPLOAD_QUOTE', 'uploadQuote');
define('CTSALESORDER_ACT_SEND_QUOTE', 'sendQuote');
define('CTSALESORDER_ACT_DELETE_QUOTE', 'deleteQuote');
define('CTSALESORDER_ACT_DISPLAY_QUOTE', 'displayQuote');
define('CTSALESORDER_ACT_DELETE_QUOTE_DOC', 'deleteQuoteDoc');
// Messages
define('CTSALESORDER_MSG_CUSTTRING_REQ', 'Please enter customer to search for');
define('CTSALESORDER_MSG_NONE_FND', 'No customers found');
define('CTSALESORDER_MSG_CUS_NOT_FND', 'Customer not found');
define('CTSALESORDER_MSG_SELECT_USER', 'User?');
define('CTSALESORDER_MSG_SELECT_SALUTATION', 'Salutation?');
define('CTSALESORDER_MSG_SELECT_INTRODUCTION', 'Introduction?');
define('CTSALESORDER_MSG_NO_LINES', 'Select lines to include');
define('CTSALESORDER_MSG_USER_NOT_FND', 'User not found');
define('CTSALESORDER_MSG_PROBLEM_SENDING_QUOTE', 'Quote could not be sent');
define('CTSALESORDER_MSG_QUOTEID_NOT_PASSED', 'quotationID not passed');
define('CTSALESORDER_MSG_QUOTE_NOT_FOUND', 'Quote not found');
define('CTSALESORDER_ORDHEADID_NOT_PASSED', 'ordheadID not passed');
define('CTSALESORDER_ORDER_NOT_FND', 'Order not found');
define('CTSALESORDER_NOT_NUMERIC', 'Must be a number');
define('CTSALESORDER_INVALID_DATE', 'Invalid date');
define('CTSALESORDER_TO_DATE_SMALLER', 'End date must be greater than start date');
define('CTSALESORDER_CLS_FORM_ERROR', 'formError');
define('CTSALESORDER_CLS_ORDER_LINE_ITEM', 'orderLineItem');
define('CTSALESORDER_CLS_ORDER_LINE_COMMENT', 'orderLineComment');
define('CTSALESORDER_CLS_ORDER_LINE_LOSS', 'orderLineLoss');
define('CTSALESORDER_CLS_ORDER_TOTAL_ITEM', 'orderTotalItem');
define('CTSALESORDER_CLS_ORDER_TOTAL_LOSS', 'orderTotalLoss');
// Notepad key types
define('CTSALESORDER_NOTEPAD_ITEM', 'IT');
define('CTSALESORDER_TXT_INTRODUCTION',
       'With reference to our recent telephone conversation I have great pleasure in providing you with the following prices:');
//define('CTSALESORDER_TXT_EMAIL_SUBJECT_START', 'Your Quotation Ref:');
define('CTSALESORDER_TXT_SEND', 'Send');
define('CTSALESORDER_TXT_DELETE', 'Delete');

class CTSalesOrder extends CTCNC
{
    var $customerID = '';
    var $customerString = '';                                            // Used when searching for an order by string
    var $buCustomer = '';
    var $dsQuotation = '';
    var $buNotepad = '';
    var $buSalesOrder = '';
    var $customerStringMessage = '';
    var $quoteFileMessage = '';
    var $userMessage = '';
    var $uploadUserMessage = '';
    var $ordheadIDMessage = '';
    var $fromDateMessage = '';
    var $toDateMessage = '';
    var $dsCustomer = '';
    var $dsContact = '';
    var $dsSite = '';
    var $siteNo = '';
    var $dsOrder = '';
    var $ordheadID = '';
    var $quotationID = '';
    var $orderType = '';
    var $custPORef = '';
    var $lineText = '';
    var $fromDate = '';
    var $toDate = '';
    var $salutation = '';
    var $dsUser = '';
    var $introduction = '';
    var $dsSelectedOrderLine = '';
    var $orderTypeArray = array(
        "I" => "Initial",
        "Q" => "Quotation",
        "P" => "Part Despatched",
        "C" => "Completed",
        "B" => "Both Initial & Part Despatched"
    );

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        if (!self::canAccess($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buCustomer = new BUCustomer($this);
        $this->buSalesOrder = new BUSalesOrder($this);
        $this->buNotepad = new BUNotepad($this);
        $this->dsOrder = new Dataset($this);
        $this->dsSelectedOrderLine = new Dataset($this);
        $this->dsSelectedOrderLine->addColumn('sequenceNo', DA_INTEGER, DA_ALLOW_NULL);
        $this->dsQuotation = new DataSet($this);
        $this->dsQuotation->copyColumnsFrom($this->buSalesOrder->dbeQuotation);
    }

    function initialProcesses()
    {
        $this->retrieveHTMLVars();
    }

    function setCustomerID($customerID)
    {
        $this->setNumericVar('customerID', $customerID);
    }

    function getCustomerID()
    {
        return $this->customerID;
    }

    function setQuotationID($quotationID)
    {
        $this->setNumericVar('quotationID', $quotationID);
    }

    function getQuotationID()
    {
        return $this->quotationID;
    }

    function setOrdheadID($ordheadID)
    {
        $this->ordheadID = $ordheadID;
    }

    function getOrdheadID()
    {
        return $this->ordheadID;
    }

    function setUserID($userID)
    {
        $this->userID = $userID;
    }

    function getUserID()
    {
        return $this->userID;
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
            $dateArray = explode('/', $dateDMY);
            return ($dateArray[2] . '-' . str_pad($dateArray[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($dateArray[0],
                                                                                                       2,
                                                                                                       '0',
                                                                                                       STR_PAD_LEFT));
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

    function getYN($flag)
    {
        return ($flag == 'Y' ? $flag : 'N');
    }

    function getChecked($flag)
    {
        return ($flag == 'N' ? '' : CT_CHECKED);
    }

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
        while (list($key, $value) = each($array)) {
            $this->dsSelectedOrderLine->setUpdateModeInsert();
            $this->dsSelectedOrderLine->setValue('sequenceNo', $value);
            $this->dsSelectedOrderLine->post();
        }
        return TRUE;
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case CTSALESORDER_ACT_SEARCH:
                $this->search();
                break;
            case CTSALESORDER_ACT_DISP_ORDER:
                $this->displayOrder();
                break;
            case CTSALESORDER_ACT_SEND_QUOTE:
                $this->sendQuote();
                break;
            case CTSALESORDER_ACT_DELETE_QUOTE:
                $this->deleteQuote();
                break;
            case CTSALESORDER_ACT_DELETE_QUOTE_DOC:
                $this->deleteQuoteDoc();
                break;
            case CTSALESORDER_ACT_GENERATE_QUOTE:
                $this->generateQuote();
                break;
            case CTSALESORDER_ACT_UPLOAD_QUOTE:
                $this->uploadQuote();
                break;
            case CTSALESORDER_ACT_DISPLAY_QUOTE:
                $this->displayQuote();
                break;
            default:
                $this->displaySearchForm();
                break;
        }
    }

    /**
     * Display the initial form that prompts the employee for details
     * @access private
     */
    function displaySearchForm()
    {
        $this->setMethodName('displaySearchForm');
        $this->setTemplateFiles(
            array(
                'SalesOrderSearch' => 'SalesOrderSearch.inc',
                'OrderTypeSelector' => 'OrderTypeSelector.inc'
            )
        );
// Parameters
        $this->setPageTitle("Sales Order System");
        $submitURL = $this->buildLink($_SERVER['PHP_SELF'], array('action' => CTSALESORDER_ACT_SEARCH));
        $clearURL = $this->buildLink($_SERVER['PHP_SELF'], array());
        $customerPopupURL =
            $this->buildLink(
                'Customer.php',
                array(
                    'action' => CTCNC_ACT_DISP_CUST_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $this->dsOrder->initialise();

        $this->template->set_block('OrderTypeSelector', 'orderTypeBlock', 'orderTypes');
        $this->parseOrderTypeSelector($this->getOrderType());
        $this->template->parse('orderTypeSelector', 'OrderTypeSelector', true);

        if ($this->dsOrder->rowCount() > 0) {
            $this->template->set_block('SalesOrderSearch', 'orderBlock', 'orders');
            while ($this->dsOrder->fetchNext()) {
                $customerURL =
                    $this->buildLink(
                        CTCNC_PAGE_CUSTOMER,
                        array(
                            'action' => CTCNC_ACT_DISP_EDIT,
                            'customerID' => $this->dsOrder->getValue("customerID")
                        )
                    );
                $orderURL =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTSALESORDER_ACT_DISP_ORDER,
                            'ordheadID' => $this->dsOrder->getValue("ordheadID")
                        )
                    );
                if ($this->dsOrder->getValue("type") == 'Q') {
                    $quoteDeleteURL =
                        $this->buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action' => CTSALESORDER_ACT_DELETE_QUOTE,
                                'ordheadID' => $this->dsOrder->getValue("ordheadID")
                            )
                        );
                    $quoteDeleteText = CTSALESORDER_TXT_DELETE;
                } else {
                    $quoteDeleteURL = '';
                    $quoteDeleteText = '';
                }
                $this->template->set_var(
                    array(
                        'listCustomerName' => $this->dsOrder->getValue("customerName"),
                        'listCustomerURL' => $customerURL,
                        'listOrderURL' => $orderURL,
                        'listOrdheadID' => $this->dsOrder->getValue("ordheadID"),
                        'listOrderType' => $this->getTypeDescription($this->dsOrder->getValue("type")),
                        'listOrderDate' => strftime("%d/%m/%Y", strtotime($this->dsOrder->getValue("date"))),
                        'listCustPORef' => $this->dsOrder->getValue("custPORef"),
                        'listQuoteDeleteURL' => $quoteDeleteURL,
                        'listQuoteDeleteText' => $quoteDeleteText
                    )
                );
                $this->template->parse('orders', 'orderBlock', true);
            } // end while
        } // end if
        if ($this->getCustomerID() != '') {
            $this->buCustomer->getCustomerByID($this->getCustomerID(), $dsCustomer);
            $this->setCustomerString($dsCustomer->getValue('Name'));
        }
        $this->template->set_var(
            array(
                'customerString' => $this->getCustomerString(),
                'customerStringMessage' => $this->getCustomerStringMessage(),
                'toDateMessage' => $this->getToDateMessage(),
                'fromDateMessage' => $this->getFromDateMessage(),
                'customerID' => $this->getCustomerID(),
                'ordheadID' => $this->getOrdheadID(),
                'fromDate' => $this->getFromDate(),
                'toDate' => $this->getToDate(),
                'ordheadIDMessage' => $this->getOrdheadIDMessage(),
                'custPORef' => $this->getCustPORef(),
                'lineText' => $this->getLineText(),
                'submitURL' => $submitURL,
                'clearURL' => $clearURL,
                'customerPopupURL' => $customerPopupURL,
            )
        );
        $this->template->parse('CONTENTS', 'SalesOrderSearch', true);
        $this->parsePage();
    }

    /**
     * Search for customers usng customerString
     * @access private
     */
    function search()
    {
        $this->setMethodName('search');
        $this->setCustomerID($this->postVars["customerID"]); // Have to do this because I couldn't use Javascript to set form[customerID]
        if (
            (!is_numeric($this->getOrdheadID())) &
            ($this->getOrdheadID() != '')
        ) {
            $this->setOrdheadIDMessage(CTSALESORDER_NOT_NUMERIC);
        }
        if (($this->getFromDate() != '') && (!$this->isValidDate($this->getFromDate()))) {
            $this->setFromDateMessage(CTSALESORDER_INVALID_DATE);
        }
        if (($this->getToDate() != '') && (!$this->isValidDate($this->getToDate()))) {
            $this->setToDateMessage(CTSALESORDER_INVALID_DATE);
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
            $this->dsOrder
        );
        if (($this->dsOrder->rowCount() == 0) | ($this->dsOrder->rowCount() > 1)) {
            $this->setAction(CTSALESORDER_ACT_DISP_SEARCH);        // Zero or many found to redisplay search form
        } else {
            $this->dsOrder->fetchNext();
            $this->setOrdheadID($this->dsOrder->getValue('ordheadID'));
            $this->setAction(CTSALESORDER_ACT_DISP_ORDER);        // One found so straight to display
        }
        $this->defaultAction();
    }

    /**
     * Display one order
     * @access private
     */
    function displayOrder()
    {
        $this->setMethodName('displayOrder');
        if ($this->getOrdheadID() == '') {
            $this->displayFatalError(CTSALESORDER_ORDHEADID_NOT_PASSED);
            return;
        }
        if (!$this->buSalesOrder->getOrderWithCustomerName($this->getOrdheadID(),
                                                           $dsOrdhead,
                                                           $dsOrdline,
                                                           $dsDeliveryContact)) {
            $this->displayFatalError(CTSALESORDER_ORDER_NOT_FND);
            return;
        }
        $this->setTemplateFiles(
            array(
                'SalesOrderDisplay' => 'SalesOrderDisplay.inc',
                'SalesOrderDisplayQuotes' => 'SalesOrderDisplayQuotes.inc',
                'UserSelector' => 'UserSelector.inc'
            )
        );
// Parameters
        $title = $this->getTypeDescription($dsOrdhead->getValue('type'));
        if ($dsOrdhead->getValue('type') != 'Q') {
            $title .= ' Sales Order';
        }
        $this->setPageTitle($title);
        $generateQuoteURL =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTSALESORDER_ACT_GENERATE_QUOTE
                )
            );
        $uploadQuoteURL =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTSALESORDER_ACT_UPLOAD_QUOTE
                )
            );
        $originalQuoteURL =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => $this->getAction(),
                    'ordheadID' => $dsOrdhead->getValue("quotationOrdheadID")
                )
            );
        $this->template->set_var(
            array(
                'ordheadID' => $dsOrdhead->getValue('ordheadID'),
                'customerID' => $dsOrdhead->getValue('customerID'),
                'customerName' => $dsOrdhead->getValue('customerName'),
                'date' => strftime("%d/%m/%Y", strtotime($dsOrdhead->getValue('date'))),
                'requestedDate' => ($dsOrdhead->getValue('requestedDate') != '0000-00-00' ? $dsOrdhead->getValue('requestedDate') : 'N/A'),
                'promisedDate' => ($dsOrdhead->getValue('promisedDate') != '0000-00-00' ? $dsOrdhead->getValue('promisedDate') : 'N/A'),
                'expectedDate' => ($dsOrdhead->getValue('expectedDate') != '0000-00-00' ? $dsOrdhead->getValue('expectedDate') : 'N/A'),
                'originalQuoteURL' => $originalQuoteURL,
                'custPORef' => $dsOrdhead->getValue('custPORef'),
                'payMethod' => ($dsOrdhead->getValue('payMethod') == 'F') ? '30 Days' : $dsOrdhead->getValue('payMethod'),
                'partInvoice' => ($dsOrdhead->getValue('partInvoice') == 'Y') ? 'Yes' : 'No',
                'addCustomerItem' => ($dsOrdhead->getValue('addItem') == 'Y') ? 'Yes' : 'No',
                'vat' => $dsOrdhead->getValue('vatCode') . ' ' . $dsOrdhead->getValue('vatRate'),
                'invContact' => $dsOrdhead->getValue('invContactSalutation') . ' ' . $dsOrdhead->getValue('invContactName'),
                'invContactPhone' => $dsOrdhead->getValue('invContactPhone'),
                'invSitePhone' => $dsOrdhead->getValue('invSitePhone'),
                'invContactFax' => $dsOrdhead->getValue('invContactFax'),
                'invContactEmail' => $dsOrdhead->getValue('invContactEmail'),
                'invAdd1' => $dsOrdhead->getValue('invAdd1'),
                'invAdd2' => $dsOrdhead->getValue('invAdd2'),
                'invAdd3' => $dsOrdhead->getValue('invAdd3'),
                'invTown' => $dsOrdhead->getValue('invTown'),
                'invCounty' => $dsOrdhead->getValue('invCounty'),
                'invPostcode' => $dsOrdhead->getValue('invPostcode'),
                'delContact' => $dsOrdhead->getValue('delContactSalutation') . ' ' . $dsOrdhead->getValue('delContactName'),
                'delContactPhone' => $dsOrdhead->getValue('delContactPhone'),
                'delSitePhone' => $dsOrdhead->getValue('delSitePhone'),
                'delContactFax' => $dsOrdhead->getValue('delContactFax'),
                'delContactEmail' => $dsOrdhead->getValue('delContactEmail'),
                'delAdd1' => $dsOrdhead->getValue('delAdd1'),
                'delAdd2' => $dsOrdhead->getValue('delAdd2'),
                'delAdd3' => $dsOrdhead->getValue('delAdd3'),
                'delTown' => $dsOrdhead->getValue('delTown'),
                'delCounty' => $dsOrdhead->getValue('delCounty'),
                'delPostcode' => $dsOrdhead->getValue('delPostcode')
            )
        );

        if ($dsOrdline->fetchNext()) {
            $this->template->set_block('SalesOrderDisplay', 'orderLineBlock', 'orderLines');
            $curSaleGrandTotal = 0;
            $curProfitGrandTotal = 0;
            $percProfitGrandTotal = 0;
            $curCostGrandTotal = 0;
            do {
                if ($dsOrdline->getValue("lineType") != "I") {                    // Comment line
                    $this->template->set_var(
                        array(
                            'stockcat' => '',
                            'description' => $dsOrdline->getValue("description"),
                            'supplierName' => '',
                            'qtyOrdered' => '',
                            'curUnitCost' => '',
                            'curCostTotal' => '',
                            'curUnitSale' => '',
                            'curSaleTotal' => '',
                            'curProfit' => '',
                            'percProfit' => '',
                            'orderLineProfitClass' => CTSALESORDER_CLS_ORDER_LINE_COMMENT,
                            'orderLineSaleTotalClass' => CTSALESORDER_CLS_ORDER_LINE_COMMENT,
                            'orderLineCostTotalClass' => CTSALESORDER_CLS_ORDER_LINE_COMMENT,
                            'orderLineClass' => CTSALESORDER_CLS_ORDER_LINE_COMMENT,
                            'sequenceNo' => $dsOrdline->getValue("sequenceNo"),
                            'orderLineChecked' => ($this->dsSelectedOrderLine->search('sequenceNo',
                                                                                      $dsOrdline->getValue("sequenceNo"))) ? CT_CHECKED : ''
                        )
                    );
                } else {                                                                                                // Item line
                    $curSaleTotal = $dsOrdline->getValue("curUnitSale") * $dsOrdline->getValue("qtyOrdered");
                    $curCostTotal = $dsOrdline->getValue("curUnitCost") * $dsOrdline->getValue("qtyOrdered");
                    $curProfit = $curSaleTotal - $curCostTotal;
                    if ($curCostTotal != 0) {
                        $percProfit = $curProfit * (100 / $curCostTotal);
                    } else {
                        $percProfit = 0;
                    }
                    $this->template->set_var(
                        array(
                            'stockcat' => $dsOrdline->getValue("stockcat"),
                            'description' => $dsOrdline->getValue("description"),
                            'supplierName' => $dsOrdline->getValue("supplierName"),
                            'qtyOrdered' => $dsOrdline->getValue("qtyOrdered"),
                            'curUnitCost' => $dsOrdline->getValue("curUnitCost"),
                            'curCostTotal' => number_format($curCostTotal, 2, '.', ''),
                            'curUnitSale' => $dsOrdline->getValue("curUnitSale"),
                            'curSaleTotal' => number_format($curSaleTotal, 2, '.', ''),
                            'curProfit' => number_format($curProfit, 2, '.', ''),
                            'percProfit' => number_format($percProfit, 1, '.', ''),
                            'orderLineProfitClass' => ($curProfit < 0) ? CTSALESORDER_CLS_ORDER_LINE_LOSS : CTSALESORDER_CLS_ORDER_LINE_ITEM,
                            'orderLineSaleTotalClass' => ($curSaleTotal < 0) ? CTSALESORDER_CLS_ORDER_LINE_LOSS : CTSALESORDER_CLS_ORDER_LINE_ITEM,
                            'orderLineCostTotalClass' => ($curCostTotal < 0) ? CTSALESORDER_CLS_ORDER_LINE_LOSS : CTSALESORDER_CLS_ORDER_LINE_ITEM,
                            'orderLineClass' => CTSALESORDER_CLS_ORDER_LINE_ITEM,
                            'sequenceNo' => $dsOrdline->getValue("sequenceNo"),
                            'orderLineChecked' => ($this->dsSelectedOrderLine->search('sequenceNo',
                                                                                      $dsOrdline->getValue("sequenceNo"))) ? CT_CHECKED : ''
                        )
                    );
                    $curSaleGrandTotal += $curSaleTotal;
                    $curProfitGrandTotal += $curProfit;
                    $curCostGrandTotal += $curCostTotal;
                }
                $this->template->parse('orderLines', 'orderLineBlock', true);
            } while ($dsOrdline->fetchNext());
        }
        if ($curCostGrandTotal != 0) {
            $percProfitGrandTotal = $curProfitGrandTotal * (100 / $curCostGrandTotal);
        } else {
            $percProfitGrandTotal = 0;
        }
        $this->template->set_var(
            array(
                'curSaleGrandTotal' => number_format($curSaleGrandTotal, 2, '.', ''),
                'curCostGrandTotal' => number_format($curCostGrandTotal, 2, '.', ''),
                'curProfitGrandTotal' => number_format($curProfitGrandTotal, 2, '.', ''),
                'percProfitGrandTotal' => number_format($percProfitGrandTotal, 1, '.', ''),
                'orderTotalProfitClass' => ($curProfitGrandTotal < 0) ? CTSALESORDER_CLS_ORDER_TOTAL_LOSS : CTSALESORDER_CLS_ORDER_TOTAL_ITEM,
                'orderTotalSaleClass' => ($curSaleGrandTotal < 0) ? CTSALESORDER_CLS_ORDER_TOTAL_LOSS : CTSALESORDER_CLS_ORDER_TOTAL_ITEM,
                'orderTotalCostClass' => ($curCostGrandTotal < 0) ? CTSALESORDER_CLS_ORDER_TOTAL_LOSS : CTSALESORDER_CLS_ORDER_TOTAL_ITEM
            )
        );
        if ($dsOrdhead->getValue('type') == 'Q') {                                                            // Quote so display send/sent quotes section
            $this->template->set_block('UserSelector', 'userBlock', 'users');
            $this->parseUserSelector($this->getUserID());
            $this->template->parse('userSelector', 'UserSelector', true);
            if (($this->getSalutation() == '') & (!$this->getFormError())) {
                $this->setSalutation('Dear ' . $dsDeliveryContact->getValue('FirstName'));
            }
            if (($this->getIntroduction() == '') & (!$this->getFormError())) {
                $this->setIntroduction(CTSALESORDER_TXT_INTRODUCTION);
            }
            /*
                      if (($this->getEmailSubject()=='')&(!$this->getFormError())){
                            $this->setEmailSubject(CTSALESORDER_TXT_EMAIL_SUBJECT_START . ' ' . $this->getOrdheadID().'_'.$versionNo)
                        }
            */
            $this->template->set_var(
                array(
                    'salutation' => $this->getSalutation(),
                    'userMessage' => $this->getUserMessage(),
                    'uploadUserMessage' => $this->getUploadUserMessage(),
                    'quoteFileMessage' => $this->getQuoteFileMessage(),
                    'introduction' => $this->getIntroduction(),
                    'emailSubject' => $this->getEmailSubject(),
                    'generateQuoteURL' => $generateQuoteURL,
                    'uploadQuoteURL' => $uploadQuoteURL
                )
            );
            unset($this->dsQuotation);
            $this->buSalesOrder->getQuotationsByOrdheadID($this->getOrdheadID(), $this->dsQuotation);
            $this->dsQuotation->initialise();
            if ($this->dsQuotation->fetchNext()) {
                $this->template->set_block('SalesOrderDisplayQuotes', 'quotationBlock', 'quotations');
                do {
                    $displayQuoteURL =
                        $this->buildLink($_SERVER['PHP_SELF'],
                                         array(
                                             'action' => CTSALESORDER_ACT_DISPLAY_QUOTE,
                                             'quotationID' => $this->dsQuotation->getValue("quotationID")
                                         )
                        );
                    $quoteSent = ($this->dsQuotation->getValue("sentDateTime") != '0000-00-00 00:00:00');
                    if (!$quoteSent) {
                        $sendQuoteURL =
                            $this->buildLink($_SERVER['PHP_SELF'],
                                             array(
                                                 'action' => CTSALESORDER_ACT_SEND_QUOTE,
                                                 'quotationID' => $this->dsQuotation->getValue("quotationID")
                                             )
                            );
                        $deleteQuoteURL =
                            $this->buildLink($_SERVER['PHP_SELF'],
                                             array(
                                                 'action' => CTSALESORDER_ACT_DELETE_QUOTE_DOC,
                                                 'quotationID' => $this->dsQuotation->getValue("quotationID")
                                             )
                            );
                        $txtDelete = CTSALESORDER_TXT_DELETE;
                        $txtSend = CTSALESORDER_TXT_SEND;
                        $quoteSentDateTime = 'Not sent';
                    } else {
                        $sendQuoteURL = '';
                        $deleteQuoteURL = '';
                        $txtDelete = '';
                        $txtSend = '';
                        $quoteSentDateTime = date("j/n/Y H:i:s",
                                                  strtotime($this->dsQuotation->getValue("sentDateTime")));
                    }
                    $this->template->set_var(
                        array(
                            'displayQuoteURL' => $displayQuoteURL,
                            'sendQuoteURL' => $sendQuoteURL,
                            'deleteQuoteURL' => $deleteQuoteURL,
                            'txtSend' => $txtSend,
                            'txtDelete' => $txtDelete,
                            'quoteVersionNo' => $this->dsQuotation->getValue("versionNo"),
                            'quoteSentDateTime' => $quoteSentDateTime,
                            'quoteUserName' => $this->dsQuotation->getValue("userName")
                        )
                    );
                    $this->template->parse('quotations', 'quotationBlock', true);
                } while ($this->dsQuotation->fetchNext());
            }
            $this->template->parse('salesOrderDisplayQuotes', 'SalesOrderDisplayQuotes', true);
        }
        if ($curCostGrandTotal != 0) {
            $percProfitGrandTotal = $curProfitGrandTotal * (100 / $curCostGrandTotal);
        } else {
            $percProfitGrandTotal = 0;
        }
        $this->template->set_var(
            array(
                'curSaleGrandTotal' => number_format($curSaleGrandTotal, 2, '.', ''),
                'curCostGrandTotal' => number_format($curCostGrandTotal, 2, '.', ''),
                'curProfitGrandTotal' => number_format($curProfitGrandTotal, 2, '.', ''),
                'percProfitGrandTotal' => number_format($percProfitGrandTotal, 1, '.', ''),
                'orderTotalProfitClass' => ($curProfitGrandTotal < 0) ? CTSALESORDER_CLS_ORDER_TOTAL_LOSS : CTSALESORDER_CLS_ORDER_TOTAL_ITEM,
                'orderTotalSaleClass' => ($curSaleGrandTotal < 0) ? CTSALESORDER_CLS_ORDER_TOTAL_LOSS : CTSALESORDER_CLS_ORDER_TOTAL_ITEM,
                'orderTotalCostClass' => ($curCostGrandTotal < 0) ? CTSALESORDER_CLS_ORDER_TOTAL_LOSS : CTSALESORDER_CLS_ORDER_TOTAL_ITEM
            )
        );
        $this->template->parse('CONTENTS', 'SalesOrderDisplay', true);
        $this->parsePage();
    }

    /**
     * upload a quote document from local client
     * @access private
     */
    function uploadQuote()
    {
        $this->setMethodName('uploadQuote');
        if ($this->getUserID() == '') {
            $this->setUploadUserMessage(CTSALESORDER_MSG_SELECT_USER);
            $this->displayOrder();
            return FALSE;
        }
        if (!$this->buSalesOrder->getUserByID($this->getUserID(), $this->dsUser)) {
            $this->displayFatalError(CTSALESORDER_MSG_USER_NOT_FND);
            return FALSE;
        }
        if ($this->getOrdheadID() == '') {
            $this->displayFatalError(CTSALESORDER_ORDHEADID_NOT_PASSED);
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
        if (!$this->buSalesOrder->getOrderWithCustomerName($this->getOrdheadID(),
                                                           $dsOrdhead,
                                                           $dsOrdline,
                                                           $dsDeliveryContact)) {
            $this->displayFatalError(CTSALESORDER_ORDER_NOT_FND);
        }
        $versionNo = $this->buSalesOrder->getNextQuoteVersion($this->getOrdheadID());
        $quoteFile = $GLOBALS['cfg']['quote_path'] . '/' . $this->getOrdheadID() . '_' . $versionNo;//.'.pdf';
        $extension = substr($_FILES['quoteFile']['name'], strpos($_FILES['quoteFile']['name'], '.') + 1, 3);
        move_uploaded_file($_FILES['quoteFile']['tmp_name'], $quoteFile . '.' . $extension); // use original extension
//		$this->dsQuotation = new DataSet($this);
//		$this->dsQuotation->copyColumnsFrom($this->buSalesOrder->dbeQuotation);
        $this->dsQuotation->setUpdateModeInsert();
        $this->dsQuotation->setValue('versionNo', $versionNo);
        $this->dsQuotation->setValue('ordheadID', $dsOrdhead->getValue('ordheadID'));
        $this->dsQuotation->setValue('userID', $this->getUserID());
        $this->dsQuotation->setValue('sentDateTime', date('0000-00-00 00:00:00'));
        $this->dsQuotation->setValue('salutation', $this->getSalutation());
        $this->dsQuotation->setValue('fileExtension', $extension);
        $this->dsQuotation->post();
        $this->buSalesOrder->insertQuotation($this->dsQuotation);
        $this->displayOrder();                // redisplay with error message(s)
        /*
                $urlNext =
                    $this->buildLink(
                        CTNISBET_PHP_PROJECT,
                        array(
                            'action' => CTPROJECT_ACT_DOCUMENT_LIST
                        )
                    );
                header('Location: ' . $urlNext);
        */
    }

    /**
     * generate a PDF quote.
     * @access private
     */
    function generateQuote()
    {
        $this->setMethodName('generateQuote');
        if (count($this->postVars['selectedOrderLine']) == 0) {
            $this->setUserMessage(CTSALESORDER_MSG_NO_LINES);
            $this->displayOrder();
            return FALSE;
        } else {
            $this->setSelectedOrderLines($this->postVars['selectedOrderLine']);
        }
//		print_r($this->postVars['selectedOrderLine'])."<BR/>";
        if ($this->getUserID() == '') {
            $this->setUserMessage(CTSALESORDER_MSG_SELECT_USER);
            $this->displayOrder();
            return FALSE;
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
        if (!$this->buSalesOrder->getUserByID($this->getUserID(), $this->dsUser)) {
            $this->displayFatalError(CTSALESORDER_MSG_USER_NOT_FND);
            return FALSE;
        }
        if ($this->getOrdheadID() == '') {
            $this->displayFatalError(CTSALESORDER_ORDHEADID_NOT_PASSED);
            return FALSE;
        }
        $this->buildQuote($quoteFile, $versionNo);
        $this->displayOrder();
    }

    /**
     * send quote.
     * @access private
     */
    function sendQuote()
    {
        $this->setMethodName('sendQuote');
        if ($this->getQuotationID() == '') {
            $this->displayFatalError(CTSALESORDER_MSG_QUOTEID_NOT_PASSED);
            return;
        }
        if (!$this->buSalesOrder->getQuoteByID($this->getQuotationID(), $this->dsQuotation)) {
            $this->displayFatalError(CTSALESORDER_MSG_QUOTE_NOT_FOUND);
            return;
        }
        $this->dsQuotation->fetchNext();
        if (!$this->buSalesOrder->getUserByID($this->dsQuotation->getValue('userID'), $this->dsUser)) {
            $this->displayFatalError(CTSALESORDER_MSG_USER_NOT_FND);
            return FALSE;
        }
        $updateDB = TRUE;
        // if this is a PDF file then send an email to the customer else simply st the sent date.
        if ($this->dsQuotation->getValue('fileExtension') == 'pdf') {
            $updateDB = $this->sendPDFEmailQuote();
        }
        if ($updateDB) {
            $this->dsQuotation->setUpdateModeUpdate();
            $this->dsQuotation->setValue('sentDateTime', date('Y-m-d H:i:s'));
            $this->dsQuotation->post();
            $this->buSalesOrder->insertQuotation($this->dsQuotation);
        }
        $this->setOrdheadID($this->dsQuotation->getValue('ordheadID'));
        $this->displayOrder();
    }

    /**
     * send a PDF quote.
     * @access private
     */
    function sendPDFEmailQuote()
    {
        if (!$this->buSalesOrder->getOrderWithCustomerName($this->dsQuotation->getValue('ordheadID'),
                                                           $dsOrdhead,
                                                           $dsOrdline,
                                                           $dsDeliveryContact)) {
            $this->displayFatalError(CTSALESORDER_ORDER_NOT_FND);
        }
        $quoteFile = 'quotes/' . $this->dsQuotation->getValue('ordheadID') . '_' . $this->dsQuotation->getValue('versionNo') . '.pdf';
        // Send email with attachment
        $message = '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
        $message .= $this->dsQuotation->getValue('salutation');
        $message .= '<o:p></o:p></span></font></p>';
        $message .= '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
        $message .= 'Quotation attached.';
        $message .= '<o:p></o:p></span></font></p>';
        $message .= '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
        $message .= 'Best regards, ' . $this->dsUser->getValue('firstName');
        $message .= '<o:p></o:p></span></font></p>';
        $message .= '<p class=MsoNormal><font size=2 color=navy face=Arial><span style=\'font-size:10.0pt;color:black\'>';
        $message .= $this->dsUser->getValue('firstName') . ' ' . $this->dsUser->getValue('lastName') . ' - ' . $this->dsUser->getValue('jobTitle');
        $message .= '<BR/>Computer & Network Consultants Ltd';
        $message .= '<o:p></o:p></span></font></p>';
        $message .= '<p class=MsoNormal><font size=2 face=Arial><span lang=EN-GB style=\'font-size:10.0pt;mso-ansi-language:EN-GB\'>E-Mail:';
        $message .= '<a href="mailto:grahaml@cnc-ltd.co.uk">' . $this->dsUser->getValue('username') . '@cnc-ltd.co.uk</a><br>';

        $subject = 'Your quote ' . $dsOrdhead->getValue('ordheadID') . '/' . $this->dsQuotation->getValue('versionNo') . ' ' . $this->dsQuotation->getValue('emailSubject');
        $filename = $dsOrdhead->getValue('ordheadID') . '_' . $this->dsQuotation->getValue('versionNo') . '.pdf';
        $mime_boundary = "----=_NextPart_" . md5(time());
        unset($headers);
        $headers .= "From: " . $this->dsUser->getValue('username') . "@cnc-ltd.co.uk\r\n";
        $headers .= "CC: " . $this->dsUser->getValue('username') . "@cnc-ltd.co.uk\r\n";
//   $headers .= 'Subject: '.$subject.'\r\n';
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
        $mime_message .= chunk_split(base64_encode(file_get_contents($quoteFile)),
                                     60) . "\r\n"; // split attachment to email
        $mime_message .= "\r\n--$mime_boundary--";
        return (mail($dsOrdhead->getValue('delContactEmail'), $subject, $mime_message, $headers));
    }

    /**
     * delete a quote.
     * @access private
     */
    function deleteQuote()
    {
        $this->setMethodName('deleteQuote');
        if ($this->getQuotationID() == '') {
            $this->displayFatalError(CTSALESORDER_MSG_QUOTEID_NOT_PASSED);
            return;
        }
        if (!$this->buSalesOrder->getQuoteByID($this->getQuotationID(), $this->dsQuotation)) {
            $this->displayFatalError(CTSALESORDER_MSG_QUOTE_NOT_FOUND);
            return;
        }
        $this->setOrdheadID($this->dsQuotation->getValue('ordheadID'));
        $quoteFile = 'quotes/' . $this->dsQuotation->getValue('ordheadID') . '_' . $this->dsQuotation->getValue('versionNo') . '.pdf';
        $this->buSalesOrder->deleteQuotation($this->getQuotationID());
        unlink($quoteFile);
        $this->displayOrder();
    }

    function buildQuote()
    {
        if (!$this->buSalesOrder->getOrderWithCustomerName($this->getOrdheadID(),
                                                           $dsOrdhead,
                                                           $dsOrdline,
                                                           $dsDeliveryContact)) {
            $this->displayFatalError(CTSALESORDER_ORDER_NOT_FND);
        }
        $versionNo = $this->buSalesOrder->getNextQuoteVersion($this->getOrdheadID());
        $quoteFile = $GLOBALS['cfg']['quote_path'] . '/' . $this->getOrdheadID() . '_' . $versionNo . '.pdf';
        $buPDF = new BUPDF(
            $this,
            $quoteFile,
            $this->dsUser->getValue('name'),
            $this->getOrdheadID() . '/' . $versionNo,
            'CNC Ltd',
            'Quotation',
            'A4'
        );
        $buPDF->startPage();
        $buPDF->placeImageAt($GLOBALS['cfg']['cnclogo_path'], 'JPEG', 90, 110);
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
        $buPDF->printString('Quotation: ' . $this->getOrdheadID() . '/' . $versionNo);
        $buPDF->setFontSize(10);
        $buPDF->setBoldOff();
        $buPDF->setFont();
//		$buPDF->printString('Our ref: '.	$this->getOrdheadID().'/'.$versionNo);
        $buPDF->CR();
        $buPDF->CR();
        $firstName = $dsDeliveryContact->getValue('FirstName');
        $buPDF->printString($dsDeliveryContact->getValue('Title') . ' ' . $firstName{0} . ' ' . $dsDeliveryContact->getValue('LastName'));
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
        $buPDF->printString($this->getIntroduction());
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->setBoldOn();
        $buPDF->setFont();
        $buPDF->printStringRJAt(30, 'Qty');
        $buPDF->printStringAt(40, 'Details');
        $buPDF->printStringRJAt(150, 'Unit');
        $buPDF->printStringRJAt(170, 'Total');
        $buPDF->setBoldOff();
        $buPDF->setFont();
        $buPDF->CR();
        $grandTotal = 0;
        while ($dsOrdline->fetchNext()) {
            if ($this->dsSelectedOrderLine->search('sequenceNo', $dsOrdline->getValue("sequenceNo"))) {
                if ($dsOrdline->getValue('lineType') == "I") {
                    if ($dsOrdline->getValue('itemDescription') != '') {
                        $buPDF->printStringAt(40, $dsOrdline->getValue('itemDescription'));
                    } else {
                        $buPDF->printStringAt(40, $dsOrdline->getValue('description'));
                    }
                    $buPDF->printStringRJAt(30, $dsOrdline->getValue('qtyOrdered'));
                    $buPDF->printStringRJAt(150, '?' . number_format($dsOrdline->getValue('curUnitSale'), 2, '.', ','));
                    $total = ($dsOrdline->getValue('curUnitSale') * $dsOrdline->getValue('qtyOrdered'));
                    $buPDF->printStringRJAt(170, '?' . number_format($total, 2, '.', ','));
                    $grandTotal += $total;
                    $this->buNotepad->getNotes(CTSALESORDER_NOTEPAD_ITEM, $dsOrdline->getValue('itemID'), $dsNotepad);
                    if ($dsNotepad->fetchNext()) {
                        $buPDF->setFontSize(8);
                        $buPDF->setFont();
                        do {
                            if (trim($dsNotepad->getValue('noteText')) != '') {
                                $buPDF->CR();
                                $buPDF->printStringAt(40, $dsNotepad->getValue('noteText'));
                            }
                        } while ($dsNotepad->fetchNext());
                        $buPDF->setFontSize(10);
                        $buPDF->setFont();
                    }
                } else {
                    $buPDF->printStringAt(40, $dsOrdline->getValue('description')); // comment line
                }
                $buPDF->CR();
            }
        }
        $buPDF->setBoldOn();
        $buPDF->setFont();
        $buPDF->printStringRJAt(150, 'Grand Total');
        $buPDF->printStringRJAt(170, '?' . number_format($grandTotal, 2, '.', ','));
        $buPDF->setBoldOff();
        $buPDF->setFont();
        $buPDF->CR();
        $buPDF->CR();
        $buPDF->printString('All prices are subject to VAT at the standard rate and are valid for 7 days from the date shown above.');
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
            $buPDF->placeImageAt(IMAGES_DIR . '/' . $this->dsUser->getValue('signatureFilename'), 'PNG', 10, 35);
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
        // Insert into database
        $this->dsQuotation = new DataSet($this);
        $this->dsQuotation->copyColumnsFrom($this->buSalesOrder->dbeQuotation);
        $this->dsQuotation->setUpdateModeInsert();
        $this->dsQuotation->setValue('versionNo', $versionNo);
        $this->dsQuotation->setValue('ordheadID', $dsOrdhead->getValue('ordheadID'));
        $this->dsQuotation->setValue('userID', $this->getUserID());
        $this->dsQuotation->setValue('sentDateTime', date('0000-00-00 00:00:00'));
        $this->dsQuotation->setValue('salutation', $this->getSalutation());
        $this->dsQuotation->setValue('emailSubject', $this->getEmailSubject());
        $this->dsQuotation->setValue('fileExtension', 'pdf');
        $this->dsQuotation->post();
        $this->buSalesOrder->insertQuotation($this->dsQuotation);
    }

    /**
     * display PDF quote
     * @access private
     */
    function displayQuote()
    {
        $this->setMethodName('displayQuote');
        if ($this->getQuotationID() == '') {
            $this->displayFatalError(CTSALESORDER_MSG_QUOTEID_NOT_PASSED);
            return;
        }
        if (!$this->buSalesOrder->getQuoteByID($this->getQuotationID(), $this->dsQuotation)) {
            $this->displayFatalError(CTSALESORDER_MSG_QUOTE_NOT_FOUND);
            return;
        }
        $quoteFile = $this->dsQuotation->getValue('ordheadID') . '_' . $this->dsQuotation->getValue('versionNo');
        if ($this->dsQuotation->getValue('fileExtension') == '') {
            $quoteFile .= '.pdf';
        } else {
            $quoteFile .= '.' . $this->dsQuotation->getValue('fileExtension');        // if no extension in DB then assume PDF
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
     * Get and parse order type drop-down selector
     * @access private
     */
    function parseOrderTypeSelector($orderType)
    {
        foreach ($this->orderTypeArray as $key => $value) {
            $orderTypeSelected = ($orderType == $key) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    'orderTypeSelected' => $orderTypeSelected,
                    'orderType' => $key,
                    'orderTypeDescription' => $value
                )
            );
            $this->template->parse('orderTypes', 'orderTypeBlock', true);
        }
    }

    /**
     * Get and parse user drop-down selector
     * @access private
     */
    function parseUserSelector($userID)
    {
        $this->buSalesOrder->getAllUsers($dsUser);
        while ($dsUser->fetchNext()) {
            $userSelected = ($dsUser->getValue('userID') == $userID) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    'userSelected' => $userSelected,
                    'userID' => $dsUser->getValue('userID'),
                    'userName' => $dsUser->getValue('firstName') . ' ' . $dsUser->getValue('lastName')
                )
            );
            $this->template->parse('users', 'userBlock', true);
        }
    }

    function parsePage()
    {
        if ($this->getAction() == CTSALESORDER_ACT_DISP_SEARCH) {
            $urlLogo = '';
        } else {
            $urlLogo =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSALESORDER_ACT_DISP_SEARCH
                    )
                );
        }
        $this->template->set_var('urlLogo', $urlLogo);
        parent::parsePage();
    }
}// end of class
?>