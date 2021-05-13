<?php
/**
 * Invoice controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg['path_bu'] . '/BUInvoice.inc.php');
require_once($cfg['path_bu'] . '/BUPDFInvoice.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEStockcat.inc.php');
require_once($cfg['path_dbe'] . '/DBEPaymentTerms.inc.php');
// Messages
define(
    'CTINVOICE_MSG_NONE_FND',
    'No Invoices found'
);
define(
    'CTINVOICE_MSG_INVOICE_NOT_FND',
    'Invoice not found'
);
define(
    'CTINVOICE_MSG_INVHEADID_NOT_PASSED',
    'InvheadID not passed'
);
define(
    'CTINVOICE_MSG_INVOICE_ARRAY_NOT_PASSED',
    'Invoice array not passed'
);
define(
    'CTINVOICE_MSG_MUST_BE_UNPRINTED',
    'Must be a non-printed invoice'
);
define(
    'CTINVOICE_MSG_SEQNO_NOT_PASSED',
    'SequenceNo not passed'
);
define(
    'CTINVOICE_MSG_LINE_NOT_FND',
    'Invoice line not found'
);
// Actions
define(
    'CTINVOICE_ACT_INVOICE_INSERT',
    'insertInvoice'
);
define(
    'CTINVOICE_ACT_INVOICE_UPDATE',
    'updateInvoice'
);
define(
    'CTINVOICE_ACT_PRINT_ONE_INVOICE',
    'printOneInvoice'
);
define(
    'CTINVOICE_ACT_DELETE_INVOICE',
    'deleteInvoice'
);
define(
    'CTINVOICE_ACT_CREATE_NEW_INVOICE',
    'createInvoice'
);
define(
    'CTINVOICE_ACT_CREATE_NEW_CREDIT',
    'createCredit'
);
define(
    'CTINVOICE_ACT_DISP_SEARCH',
    'dispSearch'
);
define(
    'CTINVOICE_ACT_UPDATE_HEADER',
    'updateInvhead'
);
define(
    'CTINVOICE_ACT_UPDATE_CONTACT',
    'updateContact'
);
define(
    'CTINVOICE_ACT_UPDATE_ADDRESS',
    'updateAddress'
);
define(
    'CTINVOICE_ACT_UNPRINTED_GENERATE',
    'generateUnprinted'
);
// Action on lines
define(
    'CTINVOICE_ACT_ADD_LINE',
    'addLine'
);
define(
    'CTINVOICE_ACT_EDIT_LINE',
    'editLine'
);
define(
    'CTINVOICE_ACT_UPDATE_LINE',
    'updateLine'
);
define(
    'CTINVOICE_ACT_INSERT_LINE',
    'insertLine'
);
define(
    'CTINVOICE_ACT_MOVE_LINE_UP',
    'moveLineUp'
);
define(
    'CTINVOICE_ACT_MOVE_LINE_DOWN',
    'moveLineDown'
);
define(
    'CTINVOICE_ACT_DELETE_LINE',
    'deleteLine'
);
// Page text
define(
    'CTINVOICE_TXT_NEW_INVOICE',
    'Create Invoice'
);
define(
    'CTINVOICE_TXT_UPDATE_INVOICE',
    'Update Invoice'
);
define(
    'CTINVOICE_TXT_PRINT_INVOICES',
    'Print Invoices'
);

class CTInvoice extends CTCNC
{
    const custPORef = "custPORef";
    /** @var DSForm */
    public $dsPrintRange;
    public $dsSearchForm;
    public $dsSearchResults;
    public $buInvoice;
    public $invoiceTypeArray = array(
        "I" => "Invoice",
        "C" => "Credit Note"
    );
    /**
     * @var DSForm
     */
    private $dsInvline;
    /** @var DataSet */
    private $dsInvhead;

    /**
     * Dataset for Invoice record storage.
     *
     * @param $requestMethod
     * @param $postVars
     * @param $getVars
     * @param $cookieVars
     * @param $cfg
     * @internal param DSForm $
     * @access  private
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
        $roles = SALES_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(701);
        $this->buInvoice = new BUInvoice($this);
        $this->dsInvline = new DSForm($this);
        $this->dsInvline->copyColumnsFrom($this->buInvoice->dbeJInvline);
        $this->dsSearchForm = new DSForm($this);
        $this->dsSearchResults = new DSForm($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->checkPermissions(SALES_PERMISSION);
        switch ($this->getAction()) {
            case CTCNC_ACT_SEARCH:
                $this->search();
                break;
            case CTCNC_ACT_DISPLAY_SEARCH_FORM:
                $this->displaySearchForm();
                break;
            case CTINVOICE_ACT_CREATE_NEW_INVOICE:
            case CTINVOICE_ACT_CREATE_NEW_CREDIT:
                $this->createInvoice();
                break;
            case CTCNC_ACT_DISPLAY_INVOICE:
                $this->displayInvoice();
                break;
            case CTCNC_ACT_INVOICE_REPRINT:
                $this->invoiceReprint();
                break;
            case CTCNC_ACT_INVOICE_REPRINT_GENERATE:
                $this->invoiceReprintGenerate();
                break;
            case CTINVOICE_ACT_DELETE_INVOICE:
                $this->deleteInvoice();
                break;
            case CTINVOICE_ACT_UPDATE_ADDRESS:
                $this->updateAddress();
                break;
            case CTINVOICE_ACT_UPDATE_CONTACT:
                $this->updateContact();
                break;
            case CTINVOICE_ACT_ADD_LINE:
            case CTINVOICE_ACT_EDIT_LINE:
                $this->editLine();
                break;
            case CTINVOICE_ACT_UPDATE_LINE:
            case CTINVOICE_ACT_INSERT_LINE:
                $this->updateLine();
                break;
            case CTINVOICE_ACT_MOVE_LINE_UP:
                $this->moveLineUp();
                break;
            case CTINVOICE_ACT_MOVE_LINE_DOWN:
                $this->moveLineDown();
                break;
            case CTINVOICE_ACT_DELETE_LINE:
                $this->deleteLine();
                break;
            case CTINVOICE_ACT_PRINT_ONE_INVOICE:
                $this->printOneInvoice();
                break;
            case 'regeneratePdf':
                $this->regeneratePdf();
                break;
            case CTCNC_ACT_INVOICE_PRINT_UNPRINTED:
                $this->printUnprinted();
                break;
            case CTINVOICE_ACT_UNPRINTED_GENERATE:
                $this->printUnprintedGenerate();
                break;
            case CTINVOICE_ACT_UPDATE_HEADER:
                $this->updateHeader();
                break;
            case 'previewDirectDebit':
                $this->previewDirectDebit();
                break;
            case 'sendDirectDebitInvoices';
                $response = ["status" => "ok"];
                try {
                    $response['invoiceCount'] = $this->sendDirectDebitInvoices();
                } catch (Exception $exception) {
                    $response['status'] = "error";
                    $response['error'] = $exception->getMessage();
                    http_response_code(400);
                }
                echo json_encode($response);
                break;
            default:
                $this->displaySearchForm();
                break;
        }
    }

    /**
     * @throws Exception
     */
    function search()
    {
        $this->setMethodName('invoiceSearch');
        $this->buInvoice->initialiseSearchForm($this->dsSearchForm);
        if (!$this->getParam('ordheadID')) {
            if ($this->getParam('invoice') && !$this->dsSearchForm->populateFromArray($this->getParam('invoice'))) {
                $this->setFormErrorOn();
                $this->displaySearchForm(); //redisplay with errors
                exit;
            }
        } else {                    // just search by ordheadID
            $this->dsSearchForm->setUpdateModeInsert();
            $this->dsSearchForm->setValue(
                BUInvoice::searchFormOrdheadID,
                $this->getParam('ordheadID')
            );
            $this->dsSearchForm->post();
        }
        $this->buInvoice->search(
            $this->dsSearchForm,
            $this->dsSearchResults
        );
        if ($this->dsSearchResults->rowCount() == 1) {
            $this->dsSearchResults->fetchNext();
            $this->redirectToDisplay($this->dsSearchResults->getValue(BUInvoice::searchFormInvheadID));
        }
        $this->displaySearchForm(); // show results
    }

    /**
     * Display search form
     * @access private
     * @throws Exception
     */
    function displaySearchForm()
    {
        $dsSearchForm = &$this->dsSearchForm; // ref to global
        $dsSearchResults = &$this->dsSearchResults; // ref to global
        $this->setMethodName('displaySearchForm');
        $urlCustomerPopup = Controller::buildLink(
            CTCNC_PAGE_CUSTOMER,
            array(
                'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );

        $urlCreateInvoice = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTINVOICE_ACT_CREATE_NEW_INVOICE
            )
        );
        $urlCreateCreditNote = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTINVOICE_ACT_CREATE_NEW_CREDIT
            )
        );
        $urlSubmit = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTCNC_ACT_SEARCH
            )
        );
        $this->setPageTitle('Invoices');
        $this->setTemplateFiles(
            'InvoiceSearch',
            'InvoiceSearch.inc'
        );
        if ($dsSearchForm->rowCount() == 0) {
            $this->buInvoice->initialiseSearchForm($dsSearchForm);
        }
        $customerString = null;
        if ($dsSearchForm->getValue(BUInvoice::searchFormCustomerID)) {
            $buCustomer = new BUCustomer($this);
            $dsCustomer = new DataSet($this);
            $buCustomer->getCustomerByID(
                $dsSearchForm->getValue(BUInvoice::searchFormCustomerID),
                $dsCustomer
            );
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }
        $this->template->set_var(
            array(
                'customerID'          => $dsSearchForm->getValue(BUInvoice::searchFormCustomerID),
                'customerString'      => $customerString,
                'ordheadID'           => Controller::htmlDisplayText(
                    $dsSearchForm->getValue(BUInvoice::searchFormOrdheadID)
                ),
                'invheadID'           => $dsSearchForm->getValue(BUInvoice::searchFormInvheadID),
                'ordheadIDMessage'    => Controller::htmlDisplayText(
                    $dsSearchForm->getMessage(BUInvoice::searchFormOrdheadID)
                ),
                'invheadIDMessage'    => Controller::htmlDisplayText(
                    $dsSearchForm->getMessage(BUInvoice::searchFormInvheadID)
                ),
                'printedFlagChecked'  => $this->getChecked($dsSearchForm->getValue(BUInvoice::searchFormPrintedFlag)),
                'startDate'           => $dsSearchForm->getValue(BUInvoice::searchFormStartDate),
                'startDateMessage'    => Controller::htmlDisplayText(
                    $dsSearchForm->getMessage(BUInvoice::searchFormStartDate)
                ),
                'endDate'             => $dsSearchForm->getValue(BUInvoice::searchFormEndDate),
                'endDateMessage'      => Controller::htmlDisplayText(
                    $dsSearchForm->getMessage(BUInvoice::searchFormEndDate)
                ),
                'urlCreateInvoice'    => $urlCreateInvoice,
                'urlCreateCreditNote' => $urlCreateCreditNote,
                'urlCustomerPopup'    => $urlCustomerPopup,
                'urlSubmit'           => $urlSubmit
            )
        );
        $this->template->set_block(
            'InvoiceSearch',
            'invoiceTypeBlock',
            'invoiceTypes'
        );
        $this->parseInvoiceTypeSelector($dsSearchForm->getValue(BUInvoice::searchFormInvoiceType));
        // display results
        $dsSearchResults->initialise();
        if ($dsSearchResults->rowCount() > 0) {
            $this->template->set_block(
                'InvoiceSearch',
                'invoiceBlock',
                'invoices'
            );
            $typeCol = $dsSearchResults->columnExists(DBEInvhead::type);
            $customerNameCol = $dsSearchResults->columnExists(DBEJInvhead::customerName);
            $custPORefCol = $dsSearchResults->columnExists(DBEInvhead::custPORef);
            $invheadIDCol = $dsSearchResults->columnExists(DBEJInvhead::invheadID);
            $ordheadIDCol = $dsSearchResults->columnExists(DBEJInvhead::ordheadID);
            while ($dsSearchResults->fetchNext()) {
                $invoiceURL =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'    => CTCNC_ACT_DISPLAY_INVOICE,
                            'invheadID' => $dsSearchResults->getValue($invheadIDCol)
                        )
                    );
                $customerName = $dsSearchResults->getValue($customerNameCol);
                $this->template->set_var(
                    array(
                        'listCustomerName' => $customerName,
                        'listInvoiceURL'   => $invoiceURL,
                        'listInvheadID'    => $dsSearchResults->getValue($invheadIDCol),
                        'listOrdheadID'    => $dsSearchResults->getValue($ordheadIDCol),
                        'listInvoiceType'  => $this->invoiceTypeArray[$dsSearchResults->getValue($typeCol)],
                        'listCustomerRef'  => $dsSearchResults->getValue($custPORefCol)
                    )
                );
                $this->template->parse(
                    'invoices',
                    'invoiceBlock',
                    true
                );
            }
        }
        $this->template->parse(
            'CONTENTS',
            'InvoiceSearch',
            true
        );
        $this->parsePage();
    }

    /**
     * Get and parse invoice type drop-down selector
     * @access private
     * @param $invoiceType
     */
    function parseInvoiceTypeSelector($invoiceType)
    {
        foreach ($this->invoiceTypeArray as $key => $value) {
            $invoiceTypeSelected = ($invoiceType == $key) ? CT_SELECTED : null;
            $this->template->set_var(
                array(
                    'invoiceTypeSelected'    => $invoiceTypeSelected,
                    'invoiceType'            => $key,
                    'invoiceTypeDescription' => $value
                )
            );
            $this->template->parse(
                'invoiceTypes',
                'invoiceTypeBlock',
                true
            );
        }
    }

    function parsePage()
    {
        $this->template->set_var(
            array(
                'urlLogo' => null,
                'txtHome' => 'Home'
            )
        );
        parent::parsePage();
    }

    /**
     * Redirect to invoice display
     * @access private
     * @param $invheadID
     * @throws Exception
     */
    function redirectToDisplay($invheadID)
    {
        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'invheadID' => $invheadID,
                    'action'    => CTCNC_ACT_DISPLAY_INVOICE
                )
            );
        header('Location: ' . $urlNext);
        exit;
    }

    /**
     * Creates new invoice/credit note using customerID set
     * @throws Exception
     */
    function createInvoice()
    {
        if (!$this->getParam('customerID')) {
            $this->displayFatalError('customerID not passed');
        }
        if ($this->getAction() == CTINVOICE_ACT_CREATE_NEW_INVOICE) {
            $invheadID = $this->buInvoice->createNewInvoice($this->getParam('customerID'));
        } else {
            $invheadID = $this->buInvoice->createNewCreditNote($this->getParam('customerID'));
        }
        $this->redirectToDisplay($invheadID);
        exit;
    }

    /**
     * Display invoice header and lines
     * @access private
     * @throws Exception
     */
    function displayInvoice()
    {
        $this->setMethodName('displayInvoice');
        $dsInvhead = &$this->dsInvhead;
        $dsInvline = &$this->dsInvline;
        $urlSalesOrder = null;
        $txtSalesOrder = null;
        $urlPrint = null;
        $txtPrint = null;
        $urlRegeneratePdf = null;
        $txtRegeneratePdf = null;

        if (!$this->formError) {
            if (!$this->getParam('invheadID')) {
                $this->displayFatalError(CTINVOICE_MSG_INVHEADID_NOT_PASSED);
                return;
            }
            $this->buInvoice->getInvoiceByID(
                $this->getParam('invheadID'),
                $dsInvhead,
                $dsInvline
            );
            $dsInvhead->fetchNext();
        } else {    // if we are re-displaying header then only need lines
            $dsInvhead->initialise();
            $dsInvhead->fetchNext();
            $this->buInvoice->getInvoiceLines(
                $dsInvhead->getValue(DBEInvhead::invheadID),
                $dsInvline
            );
        }
        $invheadID = $dsInvhead->getValue(DBEInvhead::invheadID);
        $datePrinted = $dsInvhead->getValue(DBEInvhead::datePrinted);
        $urlUpdateHeader =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => CTINVOICE_ACT_UPDATE_HEADER,
                    'invheadID' => $invheadID
                )
            );
        $urlContactPopup =
            Controller::buildLink(
                CTCNC_PAGE_CONTACT,
                array(
                    'action'     => CTCNC_ACT_CONTACT_POPUP,
                    'customerID' => $dsInvhead->getValue(DBEInvhead::customerID),
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
        $urlSiteEdit =
            Controller::buildLink(
                CTCNC_PAGE_SITE,
                array(
                    'action'  => CTCNC_ACT_SITE_EDIT,
                    'htmlFmt' => CT_HTML_FMT_POPUP
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
        $urlUpdateContact =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTINVOICE_ACT_UPDATE_CONTACT
                )
            );
        $urlUpdateAddress =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTINVOICE_ACT_UPDATE_ADDRESS
                )
            );
        $urlDeleteInvoice =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => CTINVOICE_ACT_DELETE_INVOICE,
                    'invheadID' => $invheadID
                )
            );

        $txtDeleteInvoice = 'Delete';

        $this->setPageTitle($this->invoiceTypeArray[$dsInvhead->getValue(DBEInvhead::type)]);
        $this->setTemplateFiles(
            array(
                'InvoiceDisplay'      => 'InvoiceDisplay.inc',
                'InvoiceSiteEditJS'   => 'InvoiceSiteEditJS.inc',
                'InvoiceHeadDisplay'  => 'InvoiceHeadDisplay.inc',
                'SalesOrderLineIcons' => 'SalesOrderLineIcons.inc',
                'AddFirstLineIcon'    => 'AddFirstLineIcon.inc'
            )
        );
        // link to Sales Order
        if ($dsInvhead->getValue(DBEInvhead::ordheadID) != 0) {
            $dbeOrdhead = new DBEJOrdhead($this);
            if ($dbeOrdhead->getRow($dsInvhead->getValue(DBEInvhead::ordheadID))) {
                $urlSalesOrder =
                    Controller::buildLink(
                        CTCNC_PAGE_SALESORDER,
                        array(
                            'action'    => CTCNC_ACT_DISP_SALESORDER,
                            'ordheadID' => $dsInvhead->getValue(DBEInvhead::ordheadID)
                        )
                    );
                $txtSalesOrder = 'Sales Order';
            }
            unset($dbeOrdhead);
        }
        // if there are lines then allow print
        if (
            $datePrinted &&
            ($dsInvline->rowCount() > 0)
        ) {
            $urlPrint =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => CTINVOICE_ACT_PRINT_ONE_INVOICE,
                        'invheadID' => $invheadID
                    )
                );
            $txtPrint = 'Print';

            $urlRegeneratePdf =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => 'regeneratePdf',
                        'invheadID' => $invheadID
                    )
                );
            $txtRegeneratePdf = 'Regenerate PDF';
        }
        $urlHome =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTCNC_ACT_DISPLAY_SEARCH_FORM
                )
            );
        $this->template->set_var(
            array(
                'customerID'       => $dsInvhead->getValue(DBEInvhead::customerID),
                'type'             => $dsInvhead->getValue(DBEInvhead::type),
                'invheadID'        => $invheadID,
                'contactID'        => $dsInvhead->getValue(DBEInvhead::contactID),
                'contactName'      => Controller::htmlInputText(
                    $dsInvhead->getValue(DBEJInvhead::firstName) . ' ' . $dsInvhead->getValue(DBEJInvhead::lastName)
                ),
                'datePrinted'      => Controller::dateYMDtoDMY($datePrinted),
                'custPORef'        => Controller::htmlInputText($dsInvhead->getValue(DBEInvhead::custPORef)),
                'customerName'     => Controller::htmlDisplayText($dsInvhead->getValue(DBEJInvhead::customerName)),
                'vatCode'          => Controller::htmlDisplayText($dsInvhead->getValue(DBEInvhead::vatCode)),
                'vatRate'          => Controller::htmlDisplayText($dsInvhead->getValue(DBEInvhead::vatRate)),
                'add1'             => Controller::htmlDisplayText($dsInvhead->getValue(DBEInvhead::add1)),
                'add2'             => Controller::htmlDisplayText($dsInvhead->getValue(DBEInvhead::add2)),
                'add3'             => Controller::htmlDisplayText($dsInvhead->getValue(DBEInvhead::add3)),
                'town'             => Controller::htmlDisplayText($dsInvhead->getValue(DBEInvhead::town)),
                'county'           => Controller::htmlDisplayText($dsInvhead->getValue(DBEInvhead::county)),
                'postcode'         => Controller::htmlDisplayText($dsInvhead->getValue(DBEInvhead::postcode)),
                'siteNo'           => $dsInvhead->getValue(DBEInvhead::siteNo),
                'urlSitePopup'     => $urlSitePopup,
                'urlSiteEdit'      => $urlSiteEdit,
                'urlContactPopup'  => $urlContactPopup,
                'urlContactEdit'   => $urlContactEdit,
                'urlUpdateContact' => $urlUpdateContact,
                'urlUpdateAddress' => $urlUpdateAddress,
                'urlUpdateHeader'  => $urlUpdateHeader,
                'urlDeleteInvoice' => $urlDeleteInvoice,
                'txtDeleteInvoice' => $txtDeleteInvoice,
                'urlHome'          => $urlHome,
                'urlSalesOrder'    => $urlSalesOrder,
                'txtSalesOrder'    => $txtSalesOrder,
                'urlPrint'         => $urlPrint,
                'txtPrint'         => $txtPrint,
                'urlRegeneratePdf' => $urlRegeneratePdf,
                'txtRegeneratePdf' => $txtRegeneratePdf
            )
        );
        if ($dsInvline->rowCount() == 0) {                // no lines yet so need way of adding first
            $urlAddLine =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => CTINVOICE_ACT_ADD_LINE,
                        'invheadID'  => $invheadID,
                        'sequenceNo' => 1
                    )
                );
            $this->template->set_var(
                'urlAddLine',
                $urlAddLine
            );
            $this->template->parse(
                'salesInvoiceLineIcons',
                'AddFirstLineIcon',
                true
            );
        }
        if ($dsInvline->rowCount() == 0) {                // no lines yet so need way of adding first
            $urlAddLine =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => CTINVOICE_ACT_ADD_LINE,
                        'invheadID'  => $invheadID,
                        'sequenceNo' => 1
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
        if ($dsInvline->rowCount() > 0) {
            $this->template->set_block(
                'InvoiceDisplay',
                'invoiceLineBlock',
                'invoiceLines'
            );
            $curSaleGrandTotal = 0;
            $curCostGrandTotal = 0;
            while ($dsInvline->fetchNext()) {
                $sequenceNo = $dsInvline->getValue(DBEInvline::sequenceNo);
                $itemDescription = $dsInvline->getValue(DBEInvline::description);
                if ($dsInvline->getValue(DBEInvline::lineType) == 'C') {            // comment
                    $this->template->set_var(
                        array(
                            'itemID'         => null,
                            'qty'            => null,
                            'curUnitCost'    => null,
                            'curUnitSale'    => null,
                            'curCostTotal'   => null,
                            'curSaleTotal'   => null,
                            'orderLineClass' => 'orderLineComment',
                            'sequenceNo'     => $dsInvline->getValue(DBEInvline::sequenceNo),
                        )
                    );
                } else {
                    $curSaleTotal = $dsInvline->getValue(DBEInvline::curUnitSale) * $dsInvline->getValue(
                            DBEInvline::qty
                        );
                    $curCostTotal = $dsInvline->getValue(DBEInvline::curUnitCost) * $dsInvline->getValue(
                            DBEInvline::qty
                        );
                    $curSaleGrandTotal += $curSaleTotal;
                    $curCostGrandTotal += $curCostTotal;
                    $this->template->set_var(
                        array(
                            'itemID'         => $dsInvline->getValue(DBEInvline::itemID),
                            'qty'            => Controller::formatNumber($dsInvline->getValue(DBEInvline::qty)),
                            'curUnitCost'    => Controller::formatNumber($dsInvline->getValue(DBEInvline::curUnitCost)),
                            'curCostTotal'   => Controller::formatNumber($curCostTotal),
                            'curUnitSale'    => Controller::formatNumber($dsInvline->getValue(DBEInvline::curUnitSale)),
                            'curSaleTotal'   => Controller::formatNumber($curSaleTotal),
                            'orderLineClass' => 'orderLineClass',
                            'sequenceNo'     => $dsInvline->getValue(DBEInvline::sequenceNo),
                        )
                    );
                }
                $urlEditLine =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => CTINVOICE_ACT_EDIT_LINE,
                            'invheadID'  => $invheadID,
                            'sequenceNo' => $sequenceNo
                        )
                    );
                // common to comment and item lines
                $urlAddLine =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => CTINVOICE_ACT_ADD_LINE,
                            'invheadID'  => $invheadID,
                            'sequenceNo' => ($sequenceNo + 1)    // new line below current
                        )
                    );
                $urlMoveLineUp =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => CTINVOICE_ACT_MOVE_LINE_UP,
                            'invheadID'  => $invheadID,
                            'sequenceNo' => $sequenceNo
                        )
                    );
                $urlMoveLineDown =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => CTINVOICE_ACT_MOVE_LINE_DOWN,
                            'invheadID'  => $invheadID,
                            'sequenceNo' => $sequenceNo
                        )
                    );
                $urlDeleteLine =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => CTINVOICE_ACT_DELETE_LINE,
                            'invheadID'  => $invheadID,
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
                    '<A onclick="lineModal(\''.$urlEditLine.'&htmlFmt=popup\')"  >' . Controller::htmlDisplayText($itemDescription) . '</A>';
                $this->template->set_var(
                    'lineDescription',
                    $lineDescription
                );
                $this->template->parse(
                    'invoiceLines',
                    'invoiceLineBlock',
                    true
                );
                $this->template->set_var(
                    'salesOrderLineIcons',
                    null
                ); // clear for next line
            }//$dsInvoice->fetchNext()

            // Grand totals
            $this->template->set_var(
                array(
                    'curCostGrandTotal' => Controller::formatNumber($curCostGrandTotal),
                    'curSaleGrandTotal' => Controller::formatNumber($curSaleGrandTotal)
                )
            );
        } //	if ($dsInvline->rowCount() > 0){

        // do payment method
        $dbePaymentTerms = new DBEPaymentTerms($this);
        $dbePaymentTerms->getRows();
        $this->template->set_block(
            'InvoiceDisplay',
            'payMethodBlock',
            'payMethods'
        );
        while ($dbePaymentTerms->fetchNext()) {
            $payMethodSelected = ($dsInvhead->getValue(DBEInvhead::paymentTermsID) == $dbePaymentTerms->getValue(
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

        $this->template->parse(
            'invoiceSiteEditJS',
            'InvoiceSiteEditJS',
            true
        );
        $this->template->parse(
            'invoiceHeadDisplay',
            'InvoiceHeadDisplay',
            true
        );
        $this->template->parse(
            'CONTENTS',
            'InvoiceDisplay',
            true
        );
        $this->parsePage();
    }

    /**
     * Display form to allow selection of date range for which to produce invoices
     * @access private
     * @throws Exception
     */
    function invoiceReprint()
    {
        $this->setMenuId(704);
        $this->setMethodName('invoiceReprint');
        $urlSubmit = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTCNC_ACT_INVOICE_REPRINT_GENERATE
            )
        );
        $this->setPageTitle('Reprint Invoices');
        $this->setTemplateFiles(
            'InvoiceReprint',
            'InvoiceReprint.inc'
        );
        if (!$this->getFormError()) {
            $this->buInvoice->initialiseDataset($this->dsPrintRange);
        }

        $urlCustomerPopup = Controller::buildLink(
            CTCNC_PAGE_CUSTOMER,
            array(
                'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $customerString = null;
        if ($this->dsPrintRange->getValue(BUInvoice::searchFormCustomerID)) {
            $buCustomer = new BUCustomer($this);
            $dsCustomer = new DataSet($this);
            $buCustomer->getCustomerByID(
                $this->dsPrintRange->getValue(BUInvoice::searchFormCustomerID),
                $dsCustomer
            );
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }

        $this->template->set_var(
            array(
                'customerID'            => $this->dsPrintRange->getValue(BUInvoice::searchFormCustomerID),
                'customerString'        => $customerString,
                'urlCustomerPopup'      => $urlCustomerPopup,
                'startDate'             => $this->dsPrintRange->getValue(BUInvoice::searchFormStartDate),
                'startDateMessage'      => Controller::htmlDisplayText(
                    $this->dsPrintRange->getMessage(BUInvoice::searchFormStartDate)
                ),
                'endDate'               => $this->dsPrintRange->getValue(BUInvoice::searchFormEndDate),
                'endDateMessage'        => Controller::htmlDisplayText(
                    $this->dsPrintRange->getMessage(BUInvoice::searchFormEndDate)
                ),
                'startInvheadID'        => $this->dsPrintRange->getValue(BUInvoice::searchFormStartInvheadID),
                'startInvheadIDMessage' => Controller::htmlDisplayText(
                    $this->dsPrintRange->getMessage(BUInvoice::searchFormStartInvheadID)
                ),
                'endInvheadID'          => $this->dsPrintRange->getValue(BUInvoice::searchFormEndInvheadID),
                'endInvheadIDMessage'   => Controller::htmlDisplayText(
                    $this->dsPrintRange->getMessage(BUInvoice::searchFormEndInvheadID)
                ),
                'urlSubmit'             => $urlSubmit
            )
        );
        $this->template->parse(
            'CONTENTS',
            'InvoiceReprint',
            true
        );
        $this->parsePage();
    }

    /**
     * @throws Exception
     */
    function invoiceReprintGenerate()
    {
        $this->setMethodName('invoiceReprintGenerate');
        $this->buInvoice->initialiseDataset($this->dsPrintRange);
        if (!$this->dsPrintRange->populateFromArray($this->getParam('invoice'))) {
            $this->setFormErrorOn();
            $this->invoiceReprint(); //redisplay with errors
            exit;
        }
        if (!($this->dsPrintRange->getValue(BUInvoice::searchFormStartDate) .
            $this->dsPrintRange->getValue(BUInvoice::searchFormEndDate) .
            $this->dsPrintRange->getValue(BUInvoice::searchFormCustomerID) .
            $this->dsPrintRange->getValue(BUInvoice::searchFormStartInvheadID) .
            $this->dsPrintRange->getValue(BUInvoice::searchFormEndInvheadID))
        ) {
            $this->setFormErrorMessage('Please use parameters');
            $this->invoiceReprint(); //redisplay with errors
            exit;
        }
        // generate PDF invoices:
        $buPDFInvoice = new BUPDFInvoice(
            $this,
            $this->buInvoice
        );
        $pdfFile =
            $buPDFInvoice->reprintInvoicesByRange(
                $this->dsPrintRange->getValue(BUInvoice::searchFormCustomerID),
                $this->dsPrintRange->getValue(BUInvoice::searchFormStartDate),
                $this->dsPrintRange->getValue(BUInvoice::searchFormEndDate),
                $this->dsPrintRange->getValue(BUInvoice::searchFormStartInvheadID),
                $this->dsPrintRange->getValue(BUInvoice::searchFormEndInvheadID)
            );
        if ($pdfFile != FALSE) {
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename=invoices.pdf;');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($pdfFile));
            readfile($pdfFile);
            unlink($pdfFile);
            exit();
        } else {
            $this->setFormErrorMessage('No invoices found - try changing the search parameters');
            $this->invoiceReprint(); //redisplay with errors
        }
    }

    /**
     * Delete Invoice
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function deleteInvoice()
    {
        $this->setMethodName('deleteInvoice');
        $this->buInvoice->getInvoiceHeaderByID(
            $this->getParam('invheadID'),
            $this->dsInvhead
        );
        $this->buInvoice->deleteInvoice($this->getParam('invheadID'));
        if ($this->dsInvhead->getValue(DBEInvhead::ordheadID)) {
            if ($this->buInvoice->countInvoicesByOrdheadID($this->dsInvhead->getValue(DBEInvhead::ordheadID)) > 0) {
                $urlNext =                        // there is still one or more invoices so display it/them
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'    => CTCNC_ACT_SEARCH,
                            'ordheadID' => $this->dsInvhead->getValue(DBEInvhead::ordheadID)
                            // if this is set then will show
                        )                                                                                                                    // remaining invoices for SO
                    );
            } else {                                        // no more invoices for order so display order
                $urlNext =
                    Controller::buildLink(
                        CTCNC_PAGE_SALESORDER,
                        array(
                            'action'    => CTCNC_ACT_DISP_SALESORDER,
                            'ordheadID' => $this->dsInvhead->getValue(DBEInvhead::ordheadID)
                        )
                    );
            }
        } else {                                        // not attached to sales order so display invoice search page
            $urlNext =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTINVOICE_ACT_DISP_SEARCH
                    )
                );
        }
        header('Location: ' . $urlNext);
    }

    /**
     * Update invoice address
     * @access private
     * @throws Exception
     */
    function updateAddress()
    {
        $this->setMethodName('updateAddress');
        $this->buInvoice->updateAddress(
            $this->getParam('invheadID'),
            $this->getParam('siteNo')
        );
        $this->redirectToDisplay($this->getParam('invheadID'));
    }

    /**
     * Update order contact
     * @access private
     * @throws Exception
     */
    function updateContact()
    {
        $this->setMethodName('updateContact');
        $this->buInvoice->updateContact(
            $this->getParam('invheadID'),
            $this->getParam('contactID')
        );
        $this->redirectToDisplay($this->getParam('invheadID'));
    }

    /**
     * Edit/Add Invoice Line
     * @access private
     * @throws Exception
     */
    function editLine()
    {
        $this->setMethodName('editLine');
        $this->setPageTitle('Invoice - Edit Line');
        if (!$this->getParam('invheadID')) {
            $this->displayFatalError(CTINVOICE_MSG_INVHEADID_NOT_PASSED);
            return;
        }
        if (!$this->buInvoice->getInvoiceHeaderByID(
            $this->getParam('invheadID'),
            $this->dsInvhead
        )) {
            $this->displayFatalError(CTINVOICE_MSG_INVOICE_NOT_FND);
            return;
        }

        if (!$this->formError) {
            if ($this->getAction() == CTINVOICE_ACT_EDIT_LINE) {
                if (!$this->getParam('sequenceNo')) {
                    $this->displayFatalError(CTINVOICE_MSG_SEQNO_NOT_PASSED);
                    return;
                }
                if (!$this->buInvoice->getInvlineByIDSeqNo(
                    $this->getParam('invheadID'),
                    $this->getParam('sequenceNo'),
                    $this->dsInvline
                )) {
                    $this->displayFatalError(CTINVOICE_MSG_LINE_NOT_FND);
                    return;
                }
            } else {
                $lines = new DataSet($this);
                $this->buInvoice->getInvoiceLines($this->getParam('invheadID'), $lines);
                $sequenceNo = 1;
                while ($lines->fetchNext()) {
                    $sequenceNo = $lines->getValue(DBEInvline::sequenceNo);
                }
                $this->buInvoice->initialiseNewInvline(
                    $this->getParam('invheadID'),
                    $sequenceNo + 1,
                    $this->dsInvline
                );
            }
        }
        $this->setTemplateFiles(
            array(
                'InvoiceLineEdit' => 'InvoiceLineEdit.inc'//,
                //				'InvoiceLineEditJS' =>  'InvoiceLineEditJS.inc' // javascript
            )
        );
        //$this->template->setVar('isPopup', $this->getParam('htmlFmt') ? 'true' : 'false');
        $this->invoiceLineForm();
        $this->template->parse(
            'invoiceLineEditJS',
            'InvoiceLineEditJS',
            true
        );
        $this->template->parse(
            'CONTENTS',
            'InvoiceLineEdit',
            true
        );
        $this->parsePage();
    }

    /**
     * @param string $parentPage
     * @throws Exception
     */
    function invoiceLineForm($parentPage = 'InvoiceLineEdit')
    {
        $this->template->set_var(
            array(
                'customerName'       => $this->dsInvhead->getValue(DBEJInvhead::customerName),
                'itemID'             => $this->dsInvline->getValue(DBEInvline::itemID),
                'description'        => htmlspecialchars($this->dsInvline->getValue(DBEInvline::description)),
                'descriptionMessage' => $this->dsInvline->getMessage(DBEInvline::description),
                'qty'                => $this->dsInvline->getValue(DBEInvline::qty),
                'qtyMessage'         => $this->dsInvline->getMessage(DBEInvline::qty),
                'curUnitCost'        => $this->dsInvline->getValue(DBEInvline::curUnitCost),
                'curUnitSale'        => $this->dsInvline->getValue(DBEInvline::curUnitSale),
                'curUnitCostMessage' => $this->dsInvline->getMessage(DBEInvline::curUnitCost),
                'curUnitSaleMessage' => $this->dsInvline->getMessage(DBEInvline::curUnitSale)
            )
        );
        if ($this->getAction() == CTINVOICE_ACT_EDIT_LINE) {
            $urlSubmit =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTINVOICE_ACT_UPDATE_LINE
                    )
                );
        } else {
            $urlSubmit =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTINVOICE_ACT_INSERT_LINE
                    )
                );
        }
        $urlCancel =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'invheadID' => $this->dsInvhead->getValue(DBEInvhead::invheadID),
                    'action'    => CTCNC_ACT_DISPLAY_INVOICE
                )
            );
        $this->template->set_var(
            array(
                'sequenceNo'    => $this->dsInvline->getValue(DBEInvline::sequenceNo),
                'ordSequenceNo' => $this->dsInvline->getValue(DBEInvline::ordSequenceNo),
                'invheadID'     => $this->dsInvline->getValue(DBEInvline::invheadID),
                'urlSubmit'     => $urlSubmit,
                'urlCancel'     => $urlCancel
            )
        );
        // Line Type selector
        $lineTypeArray =
            array(
                "I" => "Item",
                "C" => "Comment",
            );
        $this->template->set_block(
            $parentPage,
            'lineTypeBlock',
            'lineTypes'
        );
        foreach ($lineTypeArray as $key => $value) {
            $lineTypeSelected = ($this->dsInvline->getValue(DBEInvline::lineType) == $key) ? CT_SELECTED : null;
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

        // Stock category selector
        $dbeStockcat = new DBEStockcat($this);
        $dbeStockcat->getRows();
        $this->template->set_block(
            $parentPage,
            'stockcatBlock',
            'stockcats'
        );
        while ($dbeStockcat->fetchNext()) {
            $stockcat = $dbeStockcat->getValue(DBEStockcat::stockcat);
            $stockcatSelected = ($this->dsInvline->getValue(DBEInvline::stockcat) == $stockcat) ? CT_SELECTED : null;
            $this->template->set_var(
                array(
                    'stockcatSelected' => $stockcatSelected,
                    'stockcat'         => $stockcat,
                    'stockcatDesc'     => $stockcat . ' (' . $dbeStockcat->getValue(DBEStockcat::description) . ')'
                )
            );
            $this->template->parse(
                'stockcats',
                'stockcatBlock',
                true
            );
        }
    }

    /**
     * Update/Insert order line
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function updateLine()
    {
        $this->setMethodName('updateLine');
        // set item line required fields
        if ($this->getParam('invline')[1]['lineType'] == "I") {
            $this->dsInvline->setNull(
                DBEInvline::itemID,
                DA_ALLOW_NULL
            );
            $this->dsInvline->setNull(
                DBEInvline::qty,
                DA_NOT_NULL
            );
            $this->dsInvline->setNull(
                DBEInvline::curUnitCost,
                DA_NOT_NULL
            );
            $this->dsInvline->setNull(
                DBEInvline::curUnitSale,
                DA_NOT_NULL
            );
            $this->dsInvline->setNull(
                DBEInvline::description,
                DA_NOT_NULL
            );
        } else {                                                                                                        // Comment line
            $this->dsInvline->setNull(
                DBEInvline::itemID,
                DA_ALLOW_NULL
            );
            $this->dsInvline->setNull(
                DBEInvline::qty,
                DA_ALLOW_NULL
            );
            $this->dsInvline->setNull(
                DBEInvline::curUnitCost,
                DA_ALLOW_NULL
            );
            $this->dsInvline->setNull(
                DBEInvline::curUnitSale,
                DA_ALLOW_NULL
            );
            $this->dsInvline->setNull(
                DBEInvline::description,
                DA_NOT_NULL
            );
        }
        $this->formError = !$this->dsInvline->populateFromArray($this->getParam('invline'));
        // Validate Item line
        if ($this->formError) {                    // Form error so redisplay edit form
            if ($this->getAction() == CTINVOICE_ACT_INSERT_LINE) {
                $this->setAction(CTINVOICE_ACT_ADD_LINE);
            } else {
                $this->setAction(CTINVOICE_ACT_EDIT_LINE);
            }
            $this->setParam('invheadID', $this->dsInvline->getValue(DBEInvline::invheadID));
            $this->setParam('sequenceNo', $this->dsInvline->getValue(DBEInvline::sequenceNo));
            $this->editLine();
            exit;
        }
        if ($this->getAction() == CTINVOICE_ACT_INSERT_LINE) {
            $this->buInvoice->insertNewLine($this->dsInvline);
        } else {
            $this->buInvoice->updateLine(
                $this->dsInvline,
                'U'
            );
        }
        echo "<script>window.close();  window.opener.location.reload();</script>";

        //$this->redirectToDisplay($this->dsInvline->getValue(DBEInvline::invheadID));
    }

    /**
     * Move order line up
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function moveLineUp()
    {
        $this->setMethodName('moveLineUp');
        $this->buInvoice->moveLineUp(
            $this->getParam('invheadID'),
            $this->getParam('sequenceNo')
        );
        $this->redirectToDisplay($this->getParam('invheadID'));
    }// end function invoiceLineForm()

    /**
     * Move order line down
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function moveLineDown()
    {
        $this->setMethodName('moveLineDown');
        $this->buInvoice->moveLineDown(
            $this->getParam('invheadID'),
            $this->getParam('sequenceNo')
        );
        $this->redirectToDisplay($this->getParam('invheadID'));
    }

    /**
     * Delete invoice line
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function deleteLine()
    {

        $jsonData = $this->getJSONData();

        if (empty($jsonData['invheadID'])) {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, 'Invoice Head Id required');
        }

        if (empty($jsonData['sequenceNo'])) {
            throw new \CNCLTD\Exceptions\JsonHttpException(400, 'Sequence No Required');
        }

        $this->setMethodName('deleteLine');
        $this->buInvoice->deleteLine(
            $jsonData['invheadID'],
            $jsonData['sequenceNo']
        );
        echo json_encode(["status" => "ok"]);
    }

    /**
     * Generate one invoice
     * @access private
     */
    function printOneInvoice()
    {
        $dsInvhead = new DataSet($this);
        $this->buInvoice->getInvoiceByID(
            $this->getParam('invheadID'),
            $dsInvhead,
            $dsInvline
        );

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename=' . $this->getParam('invheadID') . '.pdf;');
        header('Content-Transfer-Encoding: binary');
        echo $dsInvhead->getValue(DBEInvhead::pdfFile);
        exit();
    }

    /**
     * ReGenerate pdf invoice
     * @access private
     */
    function regeneratePdf()
    {

        $pdfFile = $this->buInvoice->regeneratePdfInvoice($this->getParam('invheadID'));

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename=' . $this->getParam('invheadID') . '.pdf;');
        header('Content-Transfer-Encoding: binary');
        echo $pdfFile;
        exit();
    }

    /**
     * Display form to allow print of unprinted invoices
     * @access private
     * @throws Exception
     */
    function printUnprinted()
    {
        $this->setMethodName('printUnprinted');
        $this->setMenuId(703);

        $urlSubmit = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTINVOICE_ACT_UNPRINTED_GENERATE
            )
        );
        $this->setPageTitle('Unprinted Invoices');
        $this->setTemplateFiles(
            'InvoicePrintUnprinted',
            'InvoicePrintUnprinted.inc'
        );
        $dsInvoiceValues = new DataSet($this);
        $dsCreditValues = new DataSet($this);
        $dsDirectDebitInvoiceValues = new DataSet($this);
        $this->buInvoice->getUnprintedInvoiceValues($dsInvoiceValues);
        $this->buInvoice->getUnprintedCreditNoteValues($dsCreditValues);
        $this->buInvoice->getUnprintedInvoiceValues(
            $dsDirectDebitInvoiceValues,
            true
        );
        $normalInvoiceDisable = false;
        if (!($dsInvoiceValues->getValue(DBEInvoiceTotals::count) + $dsCreditValues->getValue(
                DBEInvoiceTotals::count
            ))) {
            $normalInvoiceDisable = true;
        }

        $this->buInvoice->initialiseDataset($this->dsPrintRange); // we reuse this form

        // the field is named startDate because we are reusing the printRange form
        $this->template->set_var(
            array(
                'invoiceCount'            => Controller::htmlDisplayText(
                    $dsInvoiceValues->getValue(DBEInvoiceTotals::count)
                ),
                'invoiceSale'             => Controller::htmlDisplayText(
                    $dsInvoiceValues->getValue(DBEInvoiceTotals::saleValue)
                ),
                'invoiceCost'             => Controller::htmlDisplayText(
                    $dsInvoiceValues->getValue(DBEInvoiceTotals::costValue)
                ),
                'creditCount'             => Controller::htmlDisplayText(
                    $dsCreditValues->getValue(DBEInvoiceTotals::count)
                ),
                'creditSale'              => Controller::htmlDisplayText(
                    $dsCreditValues->getValue(DBEInvoiceTotals::saleValue)
                ),
                'creditCost'              => Controller::htmlDisplayText(
                    $dsCreditValues->getValue(DBEInvoiceTotals::costValue)
                ),
                'startDate'               => $this->dsPrintRange->getValue(BUInvoice::searchFormStartDate),
                'startDateMessage'        => Controller::htmlDisplayText(
                    $this->dsPrintRange->getMessage(BUInvoice::searchFormStartDate)
                ),
                'normalInvoiceDisabled'   => $normalInvoiceDisable ? "disabled" : null,
                'urlSubmit'               => $urlSubmit,
                'directDebitInvoiceCount' => Controller::htmlDisplayText(
                    $dsDirectDebitInvoiceValues->getValue(DBEInvoiceTotals::count)
                ),
                'directDebitInvoiceSale'  => Controller::htmlDisplayText(
                    $dsDirectDebitInvoiceValues->getValue(DBEInvoiceTotals::saleValue)
                ),
                'directDebitInvoiceCost'  => Controller::htmlDisplayText(
                    $dsDirectDebitInvoiceValues->getValue(DBEInvoiceTotals::costValue)
                ),
            )
        );


        $this->template->parse(
            'CONTENTS',
            'InvoicePrintUnprinted',
            true
        );
        $this->parsePage();
    }

    /**
     * @throws Exception
     */
    function printUnprintedGenerate()
    {

        $this->buInvoice->initialiseDataset($this->dsPrintRange);

        if (!$this->dsPrintRange->populateFromArray($this->getParam('invoice'))) {
            $this->setFormErrorOn();
            $this->printUnprinted(); //redisplay with errors
        }
        $list = $this->buInvoice->getCustomersWithoutInvoiceContact();
        if (count($list)) {

            $this->setFormErrorMessage(
                'These customers have no invoice contact set: ' . implode(
                    ',',
                    $list
                )
            );
            $this->setFormErrorOn();
            $this->printUnprinted(); //redisplay with errors
            exit;

        }

        if ($this->getParam('Trial')) {

            $this->trialPrintUnprintedGenerate();
            exit;

        }

        $this->setMethodName('printUnprintedGenerate');

        // generate PDF invoices:
        $invoiceCount = $this->buInvoice->printUnprintedInvoices(
            $this->dsPrintRange->getValue(BUInvoice::searchFormStartDate)
        );

        if (!$invoiceCount) {
            $this->setFormErrorMessage("There aren't any Un-sent invoices");
        } else {
            $this->setFormErrorMessage($invoiceCount . 'Invoices Sent');
        }

        $this->printUnprinted(); //redisplay
    }

    /**
     * @throws Exception
     */
    function trialPrintUnprintedGenerate()
    {
        $this->setMethodName('trialPrintUnprintedGenerate');
        // generate PDF invoices:
        $buInvoice = new BUInvoice($this);

        $pdfFile =
            $buInvoice->trialPrintUnprintedInvoices(
                $this->dsPrintRange->getValue(BUInvoice::searchFormStartDate)
            );
        if ($pdfFile != FALSE) {
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename=invoices.pdf;');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($pdfFile));
            readfile($pdfFile);
            unlink($pdfFile);
            exit();
        } else {
            $this->setFormErrorMessage("There aren't any unprinted invoices");
            $this->printUnprinted(); //redisplay with errors
        }
    }

    /**
     * Update order header details
     * @access private
     * @throws Exception
     */
    function updateHeader()
    {
        $this->setMethodName('updateHeader');
        $this->buInvoice->updateHeader(
            $this->getParam('invheadID'),
            $this->getParam('custPORef'),
            $this->getParam('paymentTermsID')
        );
        $this->redirectToDisplay($this->getParam('invheadID'));
    }

    private function previewDirectDebit()
    {
        // I need to pull the direct debits
        $buInvoice = new BUInvoice($this);

        $pdfFile =
            $buInvoice->trialPrintUnprintedInvoices(
                date('Y-m-01'),
                true
            );
        if ($pdfFile) {
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename=invoices.pdf;');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($pdfFile));
            readfile($pdfFile);
            unlink($pdfFile);
            exit;
        }
        http_response_code(400);
    }

    /**
     * @throws Exception
     */
    private function sendDirectDebitInvoices()
    {
        $buInvoice = new BUInvoice($this);

        $keyData = file_get_contents('c:\\keys\\privkey.pem');

        if (!isset($_POST['passphrase'])) {
            throw new Exception('Secure Passphrase not provided');
        }
        $key = openssl_pkey_get_private(
            $keyData,
            $_POST['passphrase']
        );

        if (!$key) {
            throw new Exception('Passphrase not valid');
        }

        if(!isset($_POST['collectionDate'])){
            throw new Exception('Passphrase not valid');
        }

        $collectionDateString = $_POST['collectionDate'];
        $collectionDate = DateTimeImmutable::createFromFormat('Y-m-d',$collectionDateString);
        if(!$collectionDate){
            throw new Exception('Collection date format is not YYYY-MM-DD');
        }

        // generate PDF invoices:
        $invoiceCount = $buInvoice->printDirectDebitInvoices(
            date('Y-m-01'),
            $collectionDate,
            $key
        );

        if ($invoiceCount == 0) {
            throw new Exception('There were no invoices to send');
        }
        return $invoiceCount;
    }

}
