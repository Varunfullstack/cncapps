<?php
/**
 * Invoice controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUInvoice.inc.php');
require_once($cfg['path_bu'] . '/BUPDFInvoice.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEStockcat.inc.php');
require_once($cfg['path_dbe'] . '/DBEPaymentTerms.inc.php');
// Messages
define('CTINVOICE_MSG_NONE_FND', 'No Invoices found');
define('CTINVOICE_MSG_INVOICE_NOT_FND', 'Invoice not found');
define('CTINVOICE_MSG_INVHEADID_NOT_PASSED', 'InvheadID not passed');
define('CTINVOICE_MSG_INVOICE_ARRAY_NOT_PASSED', 'Invoice array not passed');
define('CTINVOICE_MSG_MUST_BE_UNPRINTED', 'Must be a non-printed invoice');
define('CTINVOICE_MSG_SEQNO_NOT_PASSED', 'SequenceNo not passed');
define('CTINVOICE_MSG_LINE_NOT_FND', 'Invoice line not found');
// Actions
define('CTINVOICE_ACT_INVOICE_INSERT', 'insertInvoice');
define('CTINVOICE_ACT_INVOICE_UPDATE', 'updateInvoice');
define('CTINVOICE_ACT_PRINT_ONE_INVOICE', 'printOneInvoice');
define('CTINVOICE_ACT_DELETE_INVOICE', 'deleteInvoice');
define('CTINVOICE_ACT_CREATE_NEW_INVOICE', 'createInvoice');
define('CTINVOICE_ACT_CREATE_NEW_CREDIT', 'createCredit');
define('CTINVOICE_ACT_DISP_SEARCH', 'dispSearch');
define('CTINVOICE_ACT_UPDATE_HEADER', 'updateInvhead');
define('CTINVOICE_ACT_UPDATE_CONTACT', 'updateContact');
define('CTINVOICE_ACT_UPDATE_ADDRESS', 'updateAddress');
define('CTINVOICE_ACT_UNPRINTED_GENERATE', 'generateUnprinted');
// Action on lines
define('CTINVOICE_ACT_ADD_LINE', 'addLine');
define('CTINVOICE_ACT_EDIT_LINE', 'editLine');
define('CTINVOICE_ACT_UPDATE_LINE', 'updateLine');
define('CTINVOICE_ACT_INSERT_LINE', 'insertLine');
define('CTINVOICE_ACT_MOVE_LINE_UP', 'moveLineUp');
define('CTINVOICE_ACT_MOVE_LINE_DOWN', 'moveLineDown');
define('CTINVOICE_ACT_DELETE_LINE', 'deleteLine');
// Page text
define('CTINVOICE_TXT_NEW_INVOICE', 'Create Invoice');
define('CTINVOICE_TXT_UPDATE_INVOICE', 'Update Invoice');
define('CTINVOICE_TXT_PRINT_INVOICES', 'Print Invoices');

class CTInvoice extends CTCNC
{
    var $dsPrintRange = '';
    var $dsSearchForm = '';
    var $dsSearchResults = '';
    var $buInvoice = '';
    var $invoiceTypeArray = array(
        "I" => "Invoice",
        "C" => "Credit Note"
    );

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
    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "sales",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buInvoice = new BUInvoice($this);
        $this->dsInvline = new DSForm($this);
        $this->dsInvline->copyColumnsFrom($this->buInvoice->dbeJInvline);
        $this->dsSearchForm = new DSForm($this);
        $this->dsSearchResults = new DSForm($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_SALES);
        switch ($_REQUEST['action']) {
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
            default:
                $this->displaySearchForm();
                break;
        }
    }

    /**
     * Display search form
     * @access private
     */
    function displaySearchForm()
    {
        $dsSearchForm = &$this->dsSearchForm; // ref to global
        $dsSearchResults = &$this->dsSearchResults; // ref to global
        $this->setMethodName('displaySearchForm');
        $urlCustomerPopup = $this->buildLink(
            CTCNC_PAGE_CUSTOMER,
            array(
                'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );

        $urlCreateInvoice = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTINVOICE_ACT_CREATE_NEW_INVOICE
            )
        );
        $urlCreateCreditNote = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTINVOICE_ACT_CREATE_NEW_CREDIT
            )
        );
        $urlSubmit = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTCNC_ACT_SEARCH
            )
        );
        $this->setPageTitle('Invoices');
        $this->setTemplateFiles('InvoiceSearch', 'InvoiceSearch.inc');
        if ($dsSearchForm->rowCount() == 0) {
            $this->buInvoice->initialiseSearchForm($dsSearchForm);
        }
        if ($dsSearchForm->getValue('customerID') != '') {
            $buCustomer = new BUCustomer($this);
            $buCustomer->getCustomerByID($dsSearchForm->getValue('customerID'), $dsCustomer);
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }
        $this->template->set_var(
            array(
                'customerID'          => $dsSearchForm->getValue('customerID'),
                'customerString'      => $customerString,
                'ordheadID'           => Controller::htmlDisplayText($dsSearchForm->getValue('ordheadID')),
                'invheadID'           => Controller::dateYMDtoDMY($dsSearchForm->getValue('invheadID')),
                'ordheadIDMessage'    => Controller::htmlDisplayText($dsSearchForm->getMessage('ordheadID')),
                'invheadIDMessage'    => Controller::htmlDisplayText($dsSearchForm->getMessage('invheadID')),
                'printedFlagChecked'  => $this->getChecked($dsSearchForm->getValue('printedFlag')),
                'startDate'           => Controller::dateYMDtoDMY($dsSearchForm->getValue('startDate')),
                'startDateMessage'    => Controller::htmlDisplayText($dsSearchForm->getMessage('startDate')),
                'endDate'             => Controller::dateYMDtoDMY($dsSearchForm->getValue('endDate')),
                'endDateMessage'      => Controller::htmlDisplayText($dsSearchForm->getMessage('endDate')),
                'urlCreateInvoice'    => $urlCreateInvoice,
                'urlCreateCreditNote' => $urlCreateCreditNote,
                'urlCustomerPopup'    => $urlCustomerPopup,
                'urlSubmit'           => $urlSubmit
            )
        );
        $this->template->set_block('InvoiceSearch', 'invoiceTypeBlock', 'invoiceTypes');
        $this->parseInvoiceTypeSelector($dsSearchForm->getValue('invoiceType'));
        // display results
        $dsSearchResults->initialise();
        if ($dsSearchResults->rowCount() > 0) {
            $this->template->set_block('InvoiceSearch', 'invoiceBlock', 'invoices');
            $typeCol = $dsSearchResults->columnExists('type');
            $customerNameCol = $dsSearchResults->columnExists('customerName');
            $custPORefCol = $dsSearchResults->columnExists('custPORef');
            $invheadIDCol = $dsSearchResults->columnExists('invheadID');
            $ordheadIDCol = $dsSearchResults->columnExists('ordheadID');
            while ($dsSearchResults->fetchNext()) {
                $invoiceURL =
                    $this->buildLink(
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
                $this->template->parse('invoices', 'invoiceBlock', true);
            }
        }
        $this->template->parse('CONTENTS', 'InvoiceSearch', true);
        $this->parsePage();
    }

    function search()
    {
        $this->setMethodName('invoiceSearch');
        $this->buInvoice->initialiseSearchForm($this->dsSearchForm);
        if ($_REQUEST['ordheadID'] != '') {                    // just search by ordheadID
            $this->dsSearchForm->setUpdateModeInsert();
            $this->dsSearchForm->setValue('ordheadID', $_REQUEST['ordheadID']);
            $this->dsSearchForm->post();
        } else {
            if (!$this->dsSearchForm->populateFromArray($_REQUEST['invoice'])) {
                $this->setFormErrorOn();
                $this->displaySearchForm(); //redisplay with errors
                exit;
            }
        }
        $this->buInvoice->search(
            $this->dsSearchForm,
            $this->dsSearchResults
        );
        if ($this->dsSearchResults->rowCount() == 1) {
            $this->dsSearchResults->fetchNext();
            $this->redirectToDisplay($this->dsSearchResults->getValue('invheadID'));
        }
        $this->displaySearchForm(); // show results
    }

    /**
     * Display form to allow print of unprinted invoices
     * @access private
     */
    function printUnprinted()
    {
        $this->setMethodName('printUnprinted');


        $urlSubmit = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTINVOICE_ACT_UNPRINTED_GENERATE
            )
        );
        $this->setPageTitle('Unprinted Invoices');
        $this->setTemplateFiles('InvoicePrintUnprinted', 'InvoicePrintUnprinted.inc');
        $this->buInvoice->getUnprintedInvoiceValues($dsInvoiceValues);
        $this->buInvoice->getUnprintedCreditNoteValues($dsCreditValues);
        if ($dsInvoiceValues->getValue('count') + $dsCreditValues->getValue('count') == 0) {
            $this->setFormErrorMessage('There aren\'t currently any unprinted invoices');
            $this->parsePage();
            exit;
        }
        if (!$this->getFormError()) {
            $this->buInvoice->initialiseDataset($this->dsPrintRange); // we reuse this form
        }

        // the field is named startDate because we are reusing the printRange form
        $this->template->set_var(
            array(
                'invoiceCount'     => Controller::htmlDisplayText($dsInvoiceValues->getValue('count')),
                'invoiceSale'      => Controller::htmlDisplayText($dsInvoiceValues->getValue('saleValue')),
                'invoiceCost'      => Controller::htmlDisplayText($dsInvoiceValues->getValue('costValue')),
                'creditCount'      => Controller::htmlDisplayText($dsCreditValues->getValue('count')),
                'creditSale'       => Controller::htmlDisplayText($dsCreditValues->getValue('saleValue')),
                'creditCost'       => Controller::htmlDisplayText($dsCreditValues->getValue('costValue')),
                'startDate'        => Controller::dateYMDtoDMY($this->dsPrintRange->getValue('startDate')),
                'startDateMessage' => Controller::htmlDisplayText($this->dsPrintRange->getMessage('startDate')),
                'urlSubmit'        => $urlSubmit
            )
        );
        $this->template->parse('CONTENTS', 'InvoicePrintUnprinted', true);
        $this->parsePage();
    }

    function printUnprintedGenerate()
    {

        $this->buInvoice->initialiseDataset($this->dsPrintRange);

        if (!$this->dsPrintRange->populateFromArray($_REQUEST['invoice'])) {
            $this->setFormErrorOn();
            $this->printUnprinted(); //redisplay with errors
        }

        if ($list = $this->buInvoice->getCustomersWithoutInvoiceContact($this->dsPrintRange->getValue('startDate'))) {

            $this->setFormErrorMessage('These customers have no invoice contact set: ' . implode(',', $list));
            $this->setFormErrorOn();
            $this->printUnprinted(); //redisplay with errors
            exit;

        }

        if (isset($_REQUEST['Trial'])) {

            $this->trialPrintUnprintedGenerate();
            exit;

        }

        $this->setMethodName('printUnprintedGenerate');

        // generate PDF invoices:
        $invoiceCount = $this->buInvoice->printUnprintedInvoices(
            $this->dsPrintRange->getValue('startDate')
        );

        if ($invoiceCount == 0) {


            $this->setFormErrorMessage('There aren\'t any Un-sent invoices');

        } else {

            $this->setFormErrorMessage($invoiceCount . 'Invoices Sent');


        }

        $this->printUnprinted(); //redisplay
    }

    function trialPrintUnprintedGenerate()
    {
        $this->setMethodName('trialPrintUnprintedGenerate');
        // generate PDF invoices:
        $buInvoice = new BUInvoice($this);

        $pdfFile =
            $buInvoice->trialPrintUnprintedInvoices(
                $this->dsPrintRange->getValue('startDate')
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
            $this->setFormErrorMessage('There aren\'t any unprinted invoices');
            $this->printUnprinted(); //redisplay with errors
        }
    }

    /**
     * Creates new invoice/credit note using customerID set
     */
    function createInvoice()
    {
        if ($_REQUEST['customerID'] == '') {
            $this->displayFatalError('customerID not passed');
        }
        if ($_REQUEST['action'] == CTINVOICE_ACT_CREATE_NEW_INVOICE) {
            $invheadID = $this->buInvoice->createNewInvoice($_REQUEST['customerID']);
        } else {
            $invheadID = $this->buInvoice->createNewCreditNote($_REQUEST['customerID']);
        }
        $this->redirectToDisplay($invheadID);
        exit;
    }

    /**
     * Generate one invoice
     * @access private
     */
    function printOneInvoice()
    {
        $this->buInvoice->getInvoiceByID($_REQUEST['invheadID'], $dsInvhead, $dsInvline);

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename=' . $_REQUEST['invheadID'] . '.pdf;');
        header('Content-Transfer-Encoding: binary');
        echo $dsInvhead->getValue('pdfFile');
        exit();
    }

    /**
     * ReGenerate pdf invoice
     * @access private
     */
    function regeneratePdf()
    {

        $pdfFile = $this->buInvoice->regeneratePdfInvoice($_REQUEST['invheadID']);

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename=' . $_REQUEST['invheadID'] . '.pdf;');
        header('Content-Transfer-Encoding: binary');
        echo $pdfFile;
        exit();
    }

    /**
     * Display form to allow selection of date range for which to produce invoices
     * @access private
     */
    function invoiceReprint()
    {
        $this->setMethodName('invoiceReprint');
        $urlSubmit = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTCNC_ACT_INVOICE_REPRINT_GENERATE
            )
        );
        $this->setPageTitle('Reprint Invoices');
        $this->setTemplateFiles('InvoiceReprint', 'InvoiceReprint.inc');
        if (!$this->getFormError()) {
            $this->buInvoice->initialiseDataset($this->dsPrintRange);
        }

        $urlCustomerPopup = $this->buildLink(
            CTCNC_PAGE_CUSTOMER,
            array(
                'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );

        if ($this->dsPrintRange->getValue('customerID') != '') {
            $buCustomer = new BUCustomer($this);
            $buCustomer->getCustomerByID($this->dsPrintRange->getValue('customerID'), $dsCustomer);
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }

        $this->template->set_var(
            array(
                'customerID'            => $this->dsPrintRange->getValue('customerID'),
                'customerString'        => $customerString,
                'urlCustomerPopup'      => $urlCustomerPopup,
                'startDate'             => Controller::dateYMDtoDMY($this->dsPrintRange->getValue('startDate')),
                'startDateMessage'      => Controller::htmlDisplayText($this->dsPrintRange->getMessage('startDate')),
                'endDate'               => Controller::dateYMDtoDMY($this->dsPrintRange->getValue('endDate')),
                'endDateMessage'        => Controller::htmlDisplayText($this->dsPrintRange->getMessage('endDate')),
                'startInvheadID'        => Controller::dateYMDtoDMY($this->dsPrintRange->getValue('startInvheadID')),
                'startInvheadIDMessage' => Controller::htmlDisplayText($this->dsPrintRange->getMessage('startInvheadID')),
                'endInvheadID'          => Controller::dateYMDtoDMY($this->dsPrintRange->getValue('endInvheadID')),
                'endInvheadIDMessage'   => Controller::htmlDisplayText($this->dsPrintRange->getMessage('endInvheadID')),
                'urlSubmit'             => $urlSubmit
            )
        );
        $this->template->parse('CONTENTS', 'InvoiceReprint', true);
        $this->parsePage();
    }

    function invoiceReprintGenerate()
    {
        $this->setMethodName('invoiceReprintGenerate');
        $this->buInvoice->initialiseDataset($this->dsPrintRange);
        if (!$this->dsPrintRange->populateFromArray($_REQUEST['invoice'])) {
            $this->setFormErrorOn();
            $this->invoiceReprint(); //redisplay with errors
            exit;
        }
        if ($this->dsPrintRange->getValue('startDate') .
            $this->dsPrintRange->getValue('endDate') .
            $this->dsPrintRange->getValue('customerID') .
            $this->dsPrintRange->getValue('startInvheadID') .
            $this->dsPrintRange->getValue('endInvheadID')
            == ''
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
                $this->dsPrintRange->getValue('customerID'),
                $this->dsPrintRange->getValue('startDate'),
                $this->dsPrintRange->getValue('endDate'),
                $this->dsPrintRange->getValue('startInvheadID'),
                $this->dsPrintRange->getValue('endInvheadID')
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
     * Display invoice header and lines
     * @access private
     */
    function displayInvoice()
    {
        $this->setMethodName('displayInvoice');
        $dsInvhead = &$this->dsInvhead;
        $dsInvline = &$this->dsInvline;
        if (!$this->formError) {
            if ($_REQUEST['invheadID'] == '') {
                $this->displayFatalError(CTINVOICE_MSG_INVHEADID_NOT_PASSED);
                return;
            }
            $this->buInvoice->getInvoiceByID($_REQUEST['invheadID'], $dsInvhead, $dsInvline);
            $dsInvhead->fetchNext();
        } else {    // if we are redisplaying header then only need lines
            $dsInvhead->initialise();
            $dsInvhead->fetchNext();
            $this->buInvoice->getLinesByID($dsInvhead->getValue('invheadID'), $dsInvline);
        }
        $invheadID = $dsInvhead->getValue('invheadID');
        $invoiceType = $dsInvhead->getValue('type');
        $datePrinted = $dsInvhead->getValue('datePrinted');
        $urlUpdateHeader =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => CTINVOICE_ACT_UPDATE_HEADER,
                    'invheadID' => $invheadID
                )
            );
        $urlContactPopup =
            $this->buildLink(
                CTCNC_PAGE_CONTACT,
                array(
                    'action'     => CTCNC_ACT_CONTACT_POPUP,
                    'customerID' => $dsInvhead->getValue('customerID'),
                    'htmlFmt'    => CT_HTML_FMT_POPUP
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
        $urlSiteEdit =
            $this->buildLink(
                CTCNC_PAGE_SITE,
                array(
                    'action'  => CTCNC_ACT_SITE_EDIT,
                    'htmlFmt' => CT_HTML_FMT_POPUP
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
        $urlUpdateContact =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTINVOICE_ACT_UPDATE_CONTACT
                )
            );
        $urlUpdateAddress =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTINVOICE_ACT_UPDATE_ADDRESS
                )
            );
        $urlDeleteInvoice =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'    => CTINVOICE_ACT_DELETE_INVOICE,
                    'invheadID' => $invheadID
                )
            );

        $txtDeleteInvoice = 'Delete';

        $this->setPageTitle($this->invoiceTypeArray[$dsInvhead->getValue('type')]);
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
        if ($dsInvhead->getValue('ordheadID') != 0) {
            $dbeOrdhead = new DBEJOrdhead($this);
            $dbeOrdhead->setValue('ordheadID', $dsInvhead->getValue('ordheadID'));
            if ($dbeOrdhead->getRow()) {
                $urlSalesOrder =
                    $this->buildLink(
                        CTCNC_PAGE_SALESORDER,
                        array(
                            'action'    => CTCNC_ACT_DISP_SALESORDER,
                            'ordheadID' => $dsInvhead->getValue('ordheadID')
                        )
                    );
                $txtSalesOrder = 'Sales Order';
            }
            unset($dbeOrdhead);
        }
        // if there are lines then allow print
        if (
            ($datePrinted != '0000-00-00') &
            ($dsInvline->rowCount() > 0)
        ) {
            $urlPrint =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => CTINVOICE_ACT_PRINT_ONE_INVOICE,
                        'invheadID' => $invheadID
                    )
                );
            $txtPrint = 'Print';

            $urlRegeneratePdf =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'    => 'regeneratePdf',
                        'invheadID' => $invheadID
                    )
                );
            $txtRegeneratePdf = 'Regenerate PDF';
        }
        $urlHome =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTCNC_ACT_DISPLAY_SEARCH_FORM
                )
            );
        $ordheadID = ($dsInvhead->getValue('ordheadID') != 0 ? $dsInvhead->getValue('ordheadID') : '');
        $this->template->set_var(
            array(
                'customerID'       => $dsInvhead->getValue('customerID'),
                'type'             => $dsInvhead->getValue('type'),
                'invheadID'        => $invheadID,
                'contactID'        => $dsInvhead->getValue('contactID'),
                'contactName'      => Controller::htmlInputText($dsInvhead->getValue('firstName') . ' ' . $dsInvhead->getValue('lastName')),
                'datePrinted'      => Controller::dateYMDtoDMY($datePrinted),
                'custPORef'        => Controller::htmlInputText($dsInvhead->getValue('custPORef')),
                'customerName'     => Controller::htmlDisplayText($dsInvhead->getValue('customerName')),
                'vatCode'          => Controller::htmlDisplayText($dsInvhead->getValue('vatCode')),
                'vatRate'          => Controller::htmlDisplayText($dsInvhead->getValue('vatRate')),
                'add1'             => Controller::htmlDisplayText($dsInvhead->getValue('add1')),
                'add2'             => Controller::htmlDisplayText($dsInvhead->getValue('add2')),
                'add3'             => Controller::htmlDisplayText($dsInvhead->getValue('add3')),
                'town'             => Controller::htmlDisplayText($dsInvhead->getValue('town')),
                'county'           => Controller::htmlDisplayText($dsInvhead->getValue('county')),
                'postcode'         => Controller::htmlDisplayText($dsInvhead->getValue('postcode')),
                'siteNo'           => $dsInvhead->getValue('siteNo'),
                'DISABLED'         => $disabled,
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
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => CTINVOICE_ACT_ADD_LINE,
                        'invheadID'  => $invheadID,
                        'sequenceNo' => 1
                    )
                );
            $this->template->set_var('urlAddLine', $urlAddLine);
            $this->template->parse('salesInvoiceLineIcons', 'AddFirstLineIcon', true);
        }
        if ($dsInvline->rowCount() == 0) {                // no lines yet so need way of adding first
            $urlAddLine =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => CTINVOICE_ACT_ADD_LINE,
                        'invheadID'  => $invheadID,
                        'sequenceNo' => 1
                    )
                );
            $this->template->set_var('urlAddLine', $urlAddLine);
            $this->template->parse('salesOrderLineIcons', 'AddFirstLineIcon', true);
        }
        if ($dsInvline->rowCount() > 0) {
            $this->template->set_block('InvoiceDisplay', 'invoiceLineBlock', 'invoiceLines');
            $curSaleGrandTotal = 0;
            $curCostGrandTotal = 0;
            while ($dsInvline->fetchNext()) {
                $sequenceNo = $dsInvline->getValue("sequenceNo");
                $itemDescription = $dsInvline->getValue('description');
                if ($dsInvline->getValue("lineType") == 'C') {            // comment
                    $this->template->set_var(
                        array(
                            'itemID'         => '',
                            'qty'            => '',
                            'curUnitCost'    => '',
                            'curUnitSale'    => '',
                            'curCostTotal'   => '',
                            'curSaleTotal'   => '',
                            'orderLineClass' => 'orderLineComment'
                        )
                    );
                } else {
                    $curSaleTotal = $dsInvline->getValue("curUnitSale") * $dsInvline->getValue("qty");
                    $curCostTotal = $dsInvline->getValue("curUnitCost") * $dsInvline->getValue("qty");
                    $curSaleGrandTotal += $curSaleTotal;
                    $curCostGrandTotal += $curCostTotal;
                    $this->template->set_var(
                        array(
                            'itemID'         => $dsInvline->getValue('itemID'),
                            'qty'            => Controller::formatNumber($dsInvline->getValue('qty')),
                            'curUnitCost'    => Controller::formatNumber($dsInvline->getValue('curUnitCost')),
                            'curCostTotal'   => Controller::formatNumber($curCostTotal),
                            'curUnitSale'    => Controller::formatNumber($dsInvline->getValue('curUnitSale')),
                            'curSaleTotal'   => Controller::formatNumber($curSaleTotal),
                            'orderLineClass' => 'orderLineClass'
                        )
                    );
                }
                $urlEditLine =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => CTINVOICE_ACT_EDIT_LINE,
                            'invheadID'  => $invheadID,
                            'sequenceNo' => $sequenceNo
                        )
                    );
                // common to comment and item lines
                $urlAddLine =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => CTINVOICE_ACT_ADD_LINE,
                            'invheadID'  => $invheadID,
                            'sequenceNo' => ($sequenceNo + 1)    // new line below current
                        )
                    );
                $urlMoveLineUp =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => CTINVOICE_ACT_MOVE_LINE_UP,
                            'invheadID'  => $invheadID,
                            'sequenceNo' => $sequenceNo
                        )
                    );
                $urlMoveLineDown =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => CTINVOICE_ACT_MOVE_LINE_DOWN,
                            'invheadID'  => $invheadID,
                            'sequenceNo' => $sequenceNo
                        )
                    );
                $urlDeleteLine =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => CTINVOICE_ACT_DELETE_LINE,
                            'invheadID'  => $invheadID,
                            'sequenceNo' => $sequenceNo
                        )
                    );
                // for javascript message remove all " and ' chars
                $removeDescription = str_replace('"', '', $itemDescription);
                $removeDescription = str_replace('\'', '', $removeDescription);
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
                $this->template->parse('salesOrderLineIcons', 'SalesOrderLineIcons', true);
                $lineDescription =
                    '<A href="' . $urlEditLine . '">' . Controller::htmlDisplayText($itemDescription) . '</A>';
                $this->template->set_var('lineDescription', $lineDescription);
                $this->template->parse('invoiceLines', 'invoiceLineBlock', true);
                $this->template->set_var('salesOrderLineIcons', ''); // clear for next line
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
        $this->template->set_block('InvoiceDisplay', 'payMethodBlock', 'payMethods');
        while ($dbePaymentTerms->fetchNext()) {
            $payMethodSelected = ($dsInvhead->getValue("paymentTermsID") == $dbePaymentTerms->getValue('paymentTermsID') ? CT_SELECTED : '');
            $this->template->set_var(
                array(
                    'payMethodSelected' => $payMethodSelected,
                    'paymentTermsID'    => $dbePaymentTerms->getValue('paymentTermsID'),
                    'payMethodDesc'     => $dbePaymentTerms->getValue('description')
                )
            );
            $this->template->parse('payMethods', 'payMethodBlock', true);
        }// foreach

        $this->template->parse('invoiceSiteEditJS', 'InvoiceSiteEditJS', true);
        $this->template->parse('invoiceHeadDisplay', 'InvoiceHeadDisplay', true);
        $this->template->parse('CONTENTS', 'InvoiceDisplay', true);
        $this->parsePage();
    }

    /**
     * Edit/Add Invoice Line
     * @access private
     */
    function editLine()
    {
        $this->setMethodName('editLine');
        $this->setPageTitle('Invoice - Edit Line');
        if ($_REQUEST['invheadID'] == '') {
            $this->displayFatalError(CTINVOICE_MSG_INVHEADID_NOT_PASSED);
            return;
        }
        if (!$this->buInvoice->getInvoiceHeaderByID($_REQUEST['invheadID'], $this->dsInvhead)) {
            $this->displayFatalError(CTINVOICE_MSG_INVOICE_NOT_FND);
            return;
        }
        if ($_REQUEST['sequenceNo'] == '') {
            $this->displayFatalError(CTINVOICE_MSG_SEQNO_NOT_PASSED);
            return;
        }
        if (!$this->formError) {
            if ($_REQUEST['action'] == CTINVOICE_ACT_EDIT_LINE) {
                if (!$this->buInvoice->getInvlineByIDSeqNo($_REQUEST['invheadID'],
                                                           $_REQUEST['sequenceNo'],
                                                           $this->dsInvline)) {
                    $this->displayFatalError(CTINVOICE_MSG_LINE_NOT_FND);
                    return;
                }
            } else {
                $this->buInvoice->initialiseNewInvline($_REQUEST['invheadID'],
                                                       $_REQUEST['sequenceNo'],
                                                       $this->dsInvline);
            }
        }
        $this->setTemplateFiles(
            array(
                'InvoiceLineEdit' => 'InvoiceLineEdit.inc'//,
                //				'InvoiceLineEditJS' =>  'InvoiceLineEditJS.inc' // javascript
            )
        );
        $this->invoiceLineForm();
        $this->template->parse('invoiceLineEditJS', 'InvoiceLineEditJS', true);
        $this->template->parse('CONTENTS', 'InvoiceLineEdit', true);
        $this->parsePage();
    }

    function invoiceLineForm($parentPage = 'InvoiceLineEdit')
    {
        $this->template->set_var(
            array(
'customerName'       => $this->dsInvhead->getValue("customerName"),
'itemID'             => $this->dsInvline->getValue("itemID"),
'description'        => htmlspecialchars($this->dsInvline->getValue("description")),
'descriptionMessage' => $this->dsInvline->getMessage("description"),
'qty'                => $this->dsInvline->getValue("qty"),
'qtyMessage'         => $this->dsInvline->getMessage("qty"),
'curUnitCost'        => $this->dsInvline->getValue("curUnitCost"),
'curUnitSale'        => $this->dsInvline->getValue("curUnitSale"),
'curUnitCostMessage' => $this->dsInvline->getMessage("curUnitCost"),
'curUnitSaleMessage' => $this->dsInvline->getMessage("curUnitSale")
            )
        );
        if ($_REQUEST['action'] == CTINVOICE_ACT_EDIT_LINE) {
            $urlSubmit =
                $this->buildLink($_SERVER['PHP_SELF'],
                                 array(
                                     'action' => CTINVOICE_ACT_UPDATE_LINE
                                 )
                );
        } else {
            $urlSubmit =
                $this->buildLink($_SERVER['PHP_SELF'],
                                 array(
                                     'action' => CTINVOICE_ACT_INSERT_LINE
                                 )
                );
        }
        $urlCancel =
            $this->buildLink($_SERVER['PHP_SELF'],
                             array(
                                 'invheadID' => $this->dsInvhead->getValue('invheadID'),
                                 'action'    => CTCNC_ACT_DISPLAY_INVOICE
                             )
            );
        $this->template->set_var(
            array(
                'sequenceNo'    => $this->dsInvline->getValue("sequenceNo"),
                'ordSequenceNo' => $this->dsInvline->getValue("ordSequenceNo"),
                'invheadID'     => $this->dsInvline->getValue("invheadID"),
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
        $this->template->set_block($parentPage, 'lineTypeBlock', 'lineTypes');
        foreach ($lineTypeArray as $key => $value) {
            $lineTypeSelected = ($this->dsInvline->getValue("lineType") == $key) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    'lineTypeSelected' => $lineTypeSelected,
                    'lineType'         => $key,
                    'lineTypeDesc'     => $value
                )
            );
            $this->template->parse('lineTypes', 'lineTypeBlock', true);
        }

        // Stock category selector
        $dbeStockcat = new DBEStockcat($this);
        $dbeStockcat->getRows();
        $this->template->set_block($parentPage, 'stockcatBlock', 'stockcats');
        while ($dbeStockcat->fetchNext()) {
            $stockcat = $dbeStockcat->getValue('stockcat');
            $stockcatSelected = ($this->dsInvline->getValue("stockcat") == $stockcat) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    'stockcatSelected' => $stockcatSelected,
                    'stockcat'         => $stockcat,
                    'stockcatDesc'     => $stockcat . ' (' . $dbeStockcat->getValue('description') . ')'
                )
            );
            $this->template->parse('stockcats', 'stockcatBlock', true);
        }
    }// end function invoiceLineForm()

    /**
     * Update/Insert order line
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function updateLine()
    {
        $this->setMethodName('updateLine');
        // set item line required fields
        if ($_REQUEST['invline'][1]['lineType'] == "I") {
            $this->dsInvline->setNull('itemID', DA_NOT_NULL);
            $this->dsInvline->setNull('qty', DA_NOT_NULL);
            $this->dsInvline->setNull('curUnitCost', DA_NOT_NULL);
            $this->dsInvline->setNull('curUnitSale', DA_NOT_NULL);
            $this->dsInvline->setNull('description', DA_NOT_NULL);
        } else {                                                                                                        // Comment line
            $this->dsInvline->setNull('itemID', DA_ALLOW_NULL);
            $this->dsInvline->setNull('qty', DA_ALLOW_NULL);
            $this->dsInvline->setNull('curUnitCost', DA_ALLOW_NULL);
            $this->dsInvline->setNull('curUnitSale', DA_ALLOW_NULL);
            $this->dsInvline->setNull('description', DA_NOT_NULL);
        }
        $this->formError = !$this->dsInvline->populateFromArray($_REQUEST['invline']);
        // Validate Item line
        if ($this->formError) {                    // Form error so redisplay edit form
            if ($_REQUEST['action'] == CTINVOICE_ACT_INSERT_LINE) {
                $_REQUEST['action'] = CTINVOICE_ACT_ADD_LINE;
            } else {
                $_REQUEST['action'] = CTINVOICE_ACT_EDIT_LINE;
            }
            $_REQUEST['invheadID'] = $this->dsInvline->getValue('invheadID');
            $_REQUEST['sequenceNo'] = $this->dsInvline->getValue('sequenceNo');
            $this->editLine();
            exit;
        }
        if ($_REQUEST['action'] == CTINVOICE_ACT_INSERT_LINE) {
            $this->buInvoice->insertNewLine($this->dsInvline);
        } else {
            $this->buInvoice->updateLine($this->dsInvline, 'U');
        }
        $this->redirectToDisplay($this->dsInvline->getValue('invheadID'));
    }

    /**
     * Move order line up
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function moveLineUp()
    {
        $this->setMethodName('moveLineUp');
        $this->buInvoice->moveLineUp($_REQUEST['invheadID'], $_REQUEST['sequenceNo']);
        $this->redirectToDisplay($_REQUEST['invheadID']);
    }

    /**
     * Move order line down
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function moveLineDown()
    {
        $this->setMethodName('moveLineDown');
        $this->buInvoice->moveLineDown($_REQUEST['invheadID'], $_REQUEST['sequenceNo']);
        $this->redirectToDisplay($_REQUEST['invheadID']);
    }

    /**
     * Delete invoice line
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function deleteLine()
    {
        $this->setMethodName('deleteLine');
        $this->buInvoice->deleteLine($_REQUEST['invheadID'], $_REQUEST['sequenceNo']);
        $this->redirectToDisplay($_REQUEST['invheadID']);
    }

    /**
     * Delete Invoice
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function deleteInvoice()
    {
        $this->setMethodName('deleteInvoice');
        $this->buInvoice->getInvoiceHeaderByID($_REQUEST['invheadID'], $dsInvhead);
        $this->buInvoice->deleteInvoice($_REQUEST['invheadID']);
        if ($dsInvhead->getValue('ordheadID') <> '') {
            if ($this->buInvoice->countInvoicesByOrdheadID($dsInvhead->getValue('ordheadID')) > 0) {
                $urlNext =                        // there is still one or more invoices so display it/them
                    $this->buildLink($_SERVER['PHP_SELF'],
                                     array(
                                         'action'    => CTCNC_ACT_SEARCH,
                                         'ordheadID' => $dsInvhead->getValue('ordheadID')
                                         // if this is set then will show
                                     )                                                                                                                    // remaining invoices for SO
                    );
            } else {                                        // no more invoices for order so display order
                $urlNext =
                    $this->buildLink(
                        CTCNC_PAGE_SALESORDER,
                        array(
                            'action'    => CTCNC_ACT_DISP_SALESORDER,
                            'ordheadID' => $dsInvhead->getValue('ordheadID')
                        )
                    );
            }
        } else {                                        // not attached to sales order so display invoice search page
            $urlNext =
                $this->buildLink($_SERVER['PHP_SELF'],
                                 array(
                                     'action' => CTINVOICE_ACT_DISP_SEARCH
                                 )
                );
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
        $this->buInvoice->updateHeader(
            $_REQUEST['invheadID'],
            $_REQUEST['custPORef'],
            $_REQUEST['paymentTermsID']
        );
        $this->redirectToDisplay($_REQUEST['invheadID']);
    }

    /**
     * Update invoice address
     * @access private
     */
    function updateAddress()
    {
        $this->setMethodName('updateAddress');
        $this->buInvoice->updateAddress($_REQUEST['invheadID'], $_REQUEST['siteNo']);
        $this->redirectToDisplay($_REQUEST['invheadID']);
    }

    /**
     * Update order contact
     * @access private
     */
    function updateContact()
    {
        $this->setMethodName('updateContact');
        $this->buInvoice->updateContact($_REQUEST['invheadID'], $_REQUEST['contactID']);
        $this->redirectToDisplay($_REQUEST['invheadID']);
    }

    /**
     * Redirect to invoice display
     * @access private
     */
    function redirectToDisplay($invheadID)
    {
        $urlNext =
            $this->buildLink(
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
     * Get and parse invoice type drop-down selector
     * @access private
     */
    function parseInvoiceTypeSelector($invoiceType)
    {
        foreach ($this->invoiceTypeArray as $key => $value) {
            $invoiceTypeSelected = ($invoiceType == $key) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    'invoiceTypeSelected'    => $invoiceTypeSelected,
                    'invoiceType'            => $key,
                    'invoiceTypeDescription' => $value
                )
            );
            $this->template->parse('invoiceTypes', 'invoiceTypeBlock', true);
        }
    }

    function parsePage()
    {
        $urlLogo = '';
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