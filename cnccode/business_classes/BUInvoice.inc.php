<?php /**
 * Invoice business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;

use CNCLTD\Email\AttachmentCollection;
use CNCLTD\TwigDTOs\DirectDebitInvoiceDTO;
use CNCLTD\TwigDTOs\SalesInvoiceEmailDTO;

require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEInvhead.inc.php");
require_once($cfg["path_dbe"] . "/DBEJInvhead.inc.php");
require_once($cfg["path_dbe"] . "/DBEInvline.inc.php");
require_once($cfg["path_dbe"] . "/DBEJInvline.inc.php");
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_bu'] . '/BUSalesOrder.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_bu'] . '/BUSite.inc.php');
require_once($cfg['path_bu'] . '/BUSageExport.inc.php');
require_once($cfg['path_bu'] . '/BUContact.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUPDFInvoice.inc.php');
require_once($cfg['path_dbe'] . '/DBEInvoiceTotals.inc.php');

class BUInvoice extends Business
{
    const searchFormCustomerID     = 'customerID';
    const searchFormStartDate      = 'startDate';
    const searchFormEndDate        = 'endDate';
    const searchFormStartInvheadID = 'startInvheadID';
    const searchFormEndInvheadID   = 'endInvheadID';
    const searchFormPrintedFlag    = 'printedFlag';
    const searchFormInvheadID      = 'invheadID';
    const searchFormOrdheadID      = 'ordheadID';
    const searchFormCustomerName   = 'customerName';
    const searchFormInvoiceType    = 'invoiceType';
    /** @var DBEInvhead|DataSet */
    public $dbeInvhead;
    /** @var DBEJInvhead */
    public $dbeJInvhead;
    /** @var DBEJInvline */
    public $dbeJInvline;
    /** @var BUSageExport */
    public $buSageExport;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeInvhead   = new DBEInvhead($this);
        $this->dbeJInvhead  = new DBEJInvhead($this);
        $this->dbeJInvline  = new DBEJInvline($this);
        $this->buSageExport = new BUSageExport($this);
    }

    /**
     * initialise values for input of date range
     * @param DataSet $dsData
     * @return void $dsData results
     * @access public
     */
    function initialiseDataset(&$dsData)
    {
        $this->setMethodName('initialiseDataset');
        $dsData = new DSForm($this);
        $dsData->addColumn(
            self::searchFormCustomerID,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormStartDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormEndDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormStartInvheadID,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormEndInvheadID,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
    }

    function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn(
            self::searchFormPrintedFlag,
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormStartDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormEndDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormInvheadID,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormOrdheadID,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormCustomerID,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormCustomerName,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            self::searchFormInvoiceType,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->setValue(
            self::searchFormPrintedFlag,
            'N'
        );
    }

    /**
     * @param DataSet $dsSearchForm
     * @param DataSet $dsResults
     */
    function search(&$dsSearchForm,
                    &$dsResults
    )
    {
        $dsSearchForm->initialise();
        $dsSearchForm->fetchNext();
        if ($dsSearchForm->getValue(self::searchFormInvheadID)) {
            $this->getDatasetByPK(
                $dsSearchForm->getValue(self::searchFormInvheadID),
                $this->dbeJInvhead,
                $dsResults
            );
        } else {
            $this->dbeJInvhead->getRowsBySearchCriteria(
                trim($dsSearchForm->getValue(self::searchFormCustomerID)),
                trim($dsSearchForm->getValue(self::searchFormOrdheadID)),
                trim($dsSearchForm->getValue(self::searchFormPrintedFlag)),
                trim($dsSearchForm->getValue(self::searchFormStartDate)),
                trim($dsSearchForm->getValue(self::searchFormEndDate)),
                trim($dsSearchForm->getValue(self::searchFormInvoiceType))
            );
            $this->dbeJInvhead->initialise();
            $dsResults = $this->dbeJInvhead;
        }
    }

    function getPrintedInvoicesByRange($customerID,
                                       $startDate,
                                       $endDate,
                                       $startInvheadID,
                                       $endInvheadID,
                                       &$dsResults
    )
    {
        $this->setMethodName('getPrintedInvoicesByRange');
        if (!$endDate) {
            $endDate = $startDate;
        }
        $this->dbeJInvhead->getPrintedRowsByRange(
            $customerID,
            $startDate,
            $endDate,
            $startInvheadID,
            $endInvheadID
        );
        return ($this->getData(
            $this->dbeJInvhead,
            $dsResults
        ));
    }

    /**
     * @param DataSet $dsOrdhead
     * @param DataSet $dsOrdline
     * @param DataSet $dsDespatch
     * @return bool|string
     */
    function createInvoiceFromDespatch(&$dsOrdhead,
                                       &$dsOrdline,
                                       &$dsDespatch
    )
    {
        $this->setMethodName('createInvoiceFromDespatch');
        /*
        If there is no value then do not create an invoice
        */
        $dsDespatch->initialise();
        $dsOrdline->initialise();
        $totalValue = 0;
        while ($dsDespatch->fetchNext()) {

            $dsOrdline->fetchNext();
            if ($dsDespatch->getValue(BUDespatch::despatchQtyToDespatch) > 0) {

                if ($dsOrdline->getValue(DBEOrdline::lineType) == 'I') {

                    $totalValue += $dsDespatch->getValue(BUDespatch::despatchQtyToDespatch) * $dsOrdline->getValue(
                            DBEOrdline::curUnitSale
                        );

                }

            }

        }
        if ($totalValue == 0) {
            return false;
        }
        $invheadID = $this->generateInvHeaderFromOrder($dsOrdhead);
        // invoice lines
        $dsDespatch->initialise();
        $dsOrdline->initialise();
        $sequenceNo = 0;
        $dbeInvline = new DBEInvline($this);
        while ($dsDespatch->fetchNext()) {
            $dsOrdline->fetchNext();
            if ($dsDespatch->getValue(BUDespatch::despatchQtyToDespatch) == 0) {
                continue;
            }
            $sequenceNo++;
            $dbeInvline->setValue(
                DBEInvline::invheadID,
                $invheadID
            );
            $dbeInvline->setValue(
                DBEInvline::sequenceNo,
                $sequenceNo
            );
            $dbeInvline->setValue(
                DBEInvline::ordSequenceNo,
                $dsOrdline->getValue(DBEOrdline::sequenceNo)
            );
            $dbeInvline->setValue(
                DBEInvline::lineType,
                $dsOrdline->getValue(DBEOrdline::lineType)
            );
            $dbeInvline->setValue(
                DBEInvline::itemID,
                $dsOrdline->getValue(DBEOrdline::itemID)
            );
            $dbeInvline->setValue(
                DBEInvline::description,
                $dsOrdline->getValue(DBEOrdline::description)
            );
            $dbeInvline->setValue(
                DBEInvline::qty,
                $dsDespatch->getValue(BUDespatch::despatchQtyToDespatch)
            );
            $dbeInvline->setValue(
                DBEInvline::curUnitSale,
                $dsOrdline->getValue(DBEOrdline::curUnitSale)
            );
            $dbeInvline->setValue(
                DBEInvline::curUnitCost,
                $dsOrdline->getValue(DBEOrdline::curUnitCost)
            );
            $dbeInvline->setValue(
                DBEInvline::stockcat,
                $dsOrdline->getValue(DBEOrdline::stockcat)
            );
            $dbeInvline->insertRow();
        }
        unset($dbeInvline);
        return $invheadID;
    }

    /**
     * @param DataSet $dsOrdhead
     * @return string
     */
    function generateInvHeaderFromOrder(&$dsOrdhead)
    {
        $this->setMethodName('generateInvHeaderFromOrder');
        $dsOrdhead->initialise();
        $dsOrdhead->fetchNext();
        // Invoice header
        $this->dbeInvhead->setValue(
            DBEInvhead::invheadID,
            0
        ); // for new number
        $this->dbeInvhead->setValue(
            DBEInvhead::customerID,
            $dsOrdhead->getValue(DBEOrdhead::customerID)
        );
        $this->dbeInvhead->setValue(
            DBEInvhead::transactionType,
            $dsOrdhead->getValue(DBEOrdhead::transactionType)
        );
        $this->dbeInvhead->setValue(
            DBEInvhead::siteNo,
            $dsOrdhead->getValue(DBEOrdhead::invSiteNo)
        );
        $this->dbeInvhead->setValue(
            DBEInvhead::ordheadID,
            $dsOrdhead->getValue(DBEOrdhead::ordheadID)
        );
        $this->dbeInvhead->setValue(
            DBEInvhead::type,
            'I'
        );    // Invoice
        $this->dbeInvhead->setValue(
            DBEInvhead::add1,
            $dsOrdhead->getValue(DBEOrdhead::invAdd1)
        );
        $this->dbeInvhead->setValue(
            DBEInvhead::add2,
            $dsOrdhead->getValue(DBEOrdhead::invAdd2)
        );
        $this->dbeInvhead->setValue(
            DBEInvhead::add3,
            $dsOrdhead->getValue(DBEOrdhead::invAdd3)
        );
        $this->dbeInvhead->setValue(
            DBEInvhead::town,
            $dsOrdhead->getValue(DBEOrdhead::invTown)
        );
        $this->dbeInvhead->setValue(
            DBEInvhead::county,
            $dsOrdhead->getValue(DBEOrdhead::invCounty)
        );
        $this->dbeInvhead->setValue(
            DBEInvhead::postcode,
            $dsOrdhead->getValue(DBEOrdhead::invPostcode)
        );
        $this->dbeInvhead->setValue(
            DBEInvhead::contactID,
            $dsOrdhead->getValue(DBEOrdhead::invContactID)
        );
        $this->dbeInvhead->setValue(
            DBEInvhead::contactName,
            $dsOrdhead->getValue(DBEOrdhead::invContactName)
        );
        $this->dbeInvhead->setValue(
            DBEInvhead::salutation,
            $dsOrdhead->getValue(DBEOrdhead::invContactSalutation)
        );
        $this->dbeInvhead->setValue(
            DBEInvhead::paymentTermsID,
            $dsOrdhead->getValue(DBEOrdhead::paymentTermsID)
        );
        $this->dbeInvhead->setValue(
            DBEInvhead::vatCode,
            $dsOrdhead->getValue(DBEOrdhead::vatCode)
        );
        $this->dbeInvhead->setValue(
            DBEInvhead::directDebitFlag,
            $dsOrdhead->getValue(DBEOrdhead::directDebitFlag)
        );
        $dbeVat = new DBEVat($this);
        $dbeVat->getRow();
        $vatCode = $dsOrdhead->getValue(DBEOrdhead::vatCode);
        $vatRate = $dbeVat->getValue((integer)$vatCode[1]); // use second part of code as column no
        $this->dbeInvhead->setValue(
            DBEInvhead::vatRate,
            $vatRate
        );
        $this->dbeInvhead->setValue(
            DBEInvhead::intPORef,
            $dsOrdhead->getValue(DBEOrdhead::quotationOrdheadID)
        );
        $this->dbeInvhead->setValue(
            DBEInvhead::custPORef,
            $dsOrdhead->getValue(DBEOrdhead::custPORef)
        );
        $this->dbeInvhead->setValue(
            DBEInvhead::debtorCode,
            $dsOrdhead->getValue(DBEOrdhead::debtorCode)
        );
        $this->dbeInvhead->setValue(
            DBEInvhead::source,
            'S'
        );// sales (consultancy no longer used)
        $this->dbeInvhead->setValue(
            DBEInvhead::vatOnly,
            'N'
        );// no longer used
        $this->dbeInvhead->setValue(
            DBEInvhead::datePrinted,
            null
        );
        $this->dbeInvhead->insertRow();
        return $this->dbeInvhead->getPKValue();
    }

    /**
     * @param DataSet $dsOrdhead
     * @param DataSet $dsOrdline
     * @return bool|string
     */
    function createInvoiceFromOrder(&$dsOrdhead,
                                    &$dsOrdline
    )
    {

        $this->setMethodName('createInvoiceFromOrder');
        /*
        If there is no value then do not create an invoice
        */
        $totalValue = 0;
        $dsOrdline->initialise();
        while ($dsOrdline->fetchNext()) {

            if ($dsOrdline->getValue(DBEOrdline::lineType) == 'I') {

                $totalValue += $dsOrdline->getValue(DBEOrdline::qtyOrdered) * $dsOrdline->getValue(
                        DBEOrdline::curUnitSale
                    );

            }

        }
        if ($totalValue == 0) {
            ?>
            <div>
                total value is 0, no invoice gets generated!
            </div>
            <?php
            return false;
        }
        $invheadID = $this->generateInvHeaderFromOrder($dsOrdhead);
        // invoice lines
        $dsOrdline->initialise();
        $sequenceNo = 0;
        $dbeInvline = new DBEInvline($this);
        while ($dsOrdline->fetchNext()) {
            $sequenceNo++;
            $dbeInvline->setValue(
                DBEInvline::invheadID,
                $invheadID
            );
            $dbeInvline->setValue(
                DBEInvline::sequenceNo,
                $sequenceNo
            );
            $dbeInvline->setValue(
                DBEInvline::ordSequenceNo,
                $dsOrdline->getValue(DBEOrdline::sequenceNo)
            );
            $dbeInvline->setValue(
                DBEInvline::lineType,
                $dsOrdline->getValue(DBEOrdline::lineType)
            );
            $dbeInvline->setValue(
                DBEInvline::itemID,
                $dsOrdline->getValue(DBEOrdline::itemID)
            );
            $dbeInvline->setValue(
                DBEInvline::description,
                $dsOrdline->getValue(DBEOrdline::description)
            );
            $dbeInvline->setValue(
                DBEInvline::qty,
                $dsOrdline->getValue(DBEOrdline::qtyOrdered)
            );
            $dbeInvline->setValue(
                DBEInvline::curUnitSale,
                $dsOrdline->getValue(DBEOrdline::curUnitSale)
            );
            $dbeInvline->setValue(
                DBEInvline::curUnitCost,
                $dsOrdline->getValue(DBEOrdline::curUnitCost)
            );
            $dbeInvline->setValue(
                DBEInvline::stockcat,
                $dsOrdline->getValue(DBEOrdline::stockcat)
            );
            $dbeInvline->insertRow();
        }
        unset($dbeInvline);
        return $invheadID;
    }

    function countInvoicesByOrdheadID($ID)
    {
        $this->setMethodName('countInvoicesByOrdheadID');
        if (!$ID) {
            $this->raiseError('ordheadID not passed');
        }
        $this->dbeInvhead->setValue(
            DBEInvhead::ordheadID,
            $ID
        );
        return ($this->dbeInvhead->countRowsByColumn(DBEInvhead::ordheadID));
    }

    function getUnprintedCreditNoteValues(&$dsResults)
    {
        $this->setMethodName('getUnprintedCreditNoteValues');
        $dbeInvoiceTotals = new DBEInvoiceTotals($this);
        $dbeInvoiceTotals->getRow('C');
        return ($this->getData(
            $dbeInvoiceTotals,
            $dsResults
        ));
    }

    function getUnprintedInvoiceValues(&$dsResults,
                                       $directDebit = false
    )
    {
        $this->setMethodName('getUnprintedInvoiceValues');
        $dbeInvoiceTotals = new DBEInvoiceTotals($this);
        $dbeInvoiceTotals->getRow(
            'I',
            $directDebit
        );
        return ($this->getData(
            $dbeInvoiceTotals,
            $dsResults
        ));
    }

    function createNewCreditNote($customerID)
    {
        return ($this->createNewInvoice(
            $customerID,
            'C'
        ));
    }

    /**
     * @param $customerID
     * @param string $type
     * @return string
     */
    function createNewInvoice($customerID,
                              $type = 'I'
    )
    {
        $dbeInvhead = &$this->dbeInvhead;
        $buCustomer = new BUCustomer($this);
        $dsCustomer = new DataSet($this);
        $buCustomer->getCustomerByID(
            $customerID,
            $dsCustomer
        );
        $buSite = new BUSite($this);
        $dsSite = new DataSet($this);
        $buSite->getSiteByID(
            $customerID,
            $dsCustomer->getValue(DBECustomer::invoiceSiteNo),
            $dsSite
        );
        $buContact = new BUContact($this);
        $dsContact = new DataSet($this);
        $buContact->getContactByID(
            $dsSite->getValue(DBESite::invoiceContactID),
            $dsContact
        );
        $buHeader = new BUHeader($this);
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);
        $dbeInvhead->setValue(
            DBEInvhead::invheadID,
            0
        ); // for new number
        $dbeInvhead->setValue(
            DBEInvhead::customerID,
            $customerID
        );
        $dbeInvhead->setValue(
            DBEInvhead::siteNo,
            $dsCustomer->getValue(DBECustomer::invoiceSiteNo)
        );
        $dbeInvhead->setValue(
            DBEInvhead::ordheadID,
            null
        );
        $dbeInvhead->setValue(
            DBEInvhead::type,
            $type
        );    // Invoice/Credit Note
        $dbeInvhead->setValue(
            DBEInvhead::add1,
            $dsSite->getValue(DBESite::add1)
        );
        $dbeInvhead->setValue(
            DBEInvhead::add2,
            $dsSite->getValue(DBESite::add2)
        );
        $dbeInvhead->setValue(
            DBEInvhead::add3,
            $dsSite->getValue(DBESite::add3)
        );
        $dbeInvhead->setValue(
            DBEInvhead::town,
            $dsSite->getValue(DBESite::town)
        );
        $dbeInvhead->setValue(
            DBEInvhead::county,
            $dsSite->getValue(DBESite::county)
        );
        $dbeInvhead->setValue(
            DBEInvhead::postcode,
            $dsSite->getValue(DBESite::postcode)
        );
        $dbeInvhead->setValue(
            DBEInvhead::contactID,
            $dsSite->getValue(DBESite::invoiceContactID)
        );
        $dbeInvhead->setValue(
            DBEInvhead::contactName,
            $dsContact->getValue(DBEContact::firstName) . ' ' . $dsContact->getValue(DBEContact::lastName)
        );
        $dbeInvhead->setValue(
            DBEInvhead::paymentTermsID,
            CONFIG_PAYMENT_TERMS_30_DAYS
        );    // default
        $vatCode = $dsHeader->getValue(DBEHeader::stdVATCode);
        $dbeInvhead->setValue(
            DBEInvhead::vatCode,
            $vatCode
        );
        $dbeVat = new DBEVat($this);
        $dbeVat->getRow();
        $vatRate = $dbeVat->getValue((integer)$vatCode[1]); // get 2nd part of code and use as column no
        unset($dbeVat);
        $dbeInvhead->setValue(
            DBEInvhead::vatRate,
            $vatRate
        );
        $dbeInvhead->setValue(
            DBEInvhead::intPORef,
            null
        );
        $dbeInvhead->setValue(
            DBEInvhead::custPORef,
            null
        );
        $dbeInvhead->setValue(
            DBEInvhead::debtorCode,
            $dsSite->getValue(DBESite::debtorCode)
        );
        $dbeInvhead->setValue(
            DBEInvhead::source,
            'S'
        );// sales (consultancy no longer used)
        $dbeInvhead->setValue(
            DBEInvhead::vatOnly,
            'N'
        );// no longer used
        $dbeInvhead->setValue(
            DBEInvhead::datePrinted,
            null
        ); // not printed
        $dbeInvhead->insertRow();
        return ($dbeInvhead->getPKValue());
    }

    function getInvoiceByID($invheadID,
                            &$dsInvhead,
                            &$dsInvline
    )
    {
        if (!$invheadID) {
            $this->raiseError('invheadID not passed');
        }
        $dbeJInvhead = new DBEJInvhead($this);
        if (!$this->getDatasetByPK(
            $invheadID,
            $dbeJInvhead,
            $dsInvhead
        )) {
            $this->raiseError('Invoice not found');
        }
        $dbeJInvline = new DBEJInvline($this);
        $dbeJInvline->setValue(
            DBEJInvline::invheadID,
            $invheadID
        );
        $dbeJInvline->getRowsByColumn(DBEJInvline::invheadID);
        $this->getData(
            $dbeJInvline,
            $dsInvline
        );
        return TRUE;
    }

    function getInvoiceHeaderByID($invheadID,
                                  &$dsInvhead
    )
    {
        $this->setMethodName('getInvoiceHeaderByID');
        if (!$invheadID) {
            $this->raiseError('invheadID not passed');
        }
        $dbeJInvhead = new DBEJInvhead($this);
        return ($this->getDatasetByPK(
            $invheadID,
            $dbeJInvhead,
            $dsInvhead
        ));
    }

    /**
     * @param $invheadID
     * @param $sequenceNo
     * @param $dsInvline
     * @return bool
     */
    function getInvlineByIDSeqNo($invheadID,
                                 $sequenceNo,
                                 &$dsInvline
    )
    {
        $this->setMethodName('getInvlineByIDSeqNo');
        if (!$invheadID) {
            $this->raiseError('invoice ID not passed');
        }
        if (!$sequenceNo) {
            $this->raiseError('sequenceNo not passed');
        }
        $dbeJInvline = new DBEJInvline($this);
        $dbeJInvline->setValue(
            DBEJInvline::invheadID,
            $invheadID
        );
        $dbeJInvline->setValue(
            DBEJInvline::sequenceNo,
            $sequenceNo
        );
        $dbeJInvline->getRowByInvheadIDSequenceNo();
        return ($this->getData(
            $dbeJInvline,
            $dsInvline
        ));
    }

    function moveLineUp($invheadID,
                        $sequenceNo
    )
    {
        if (!$invheadID) {
            $this->raiseError('invheadID not passed');
        }
        if (!$sequenceNo) {
            $this->raiseError('sequenceNo not passed');
        }
        if ($sequenceNo == 1) {
            return;
        }
        $dbeInvline = new DBEInvline($this);
        $dbeInvline->setValue(
            DBEInvline::invheadID,
            $invheadID
        );
        $dbeInvline->setValue(
            DBEInvline::sequenceNo,
            $sequenceNo
        );
        $dbeInvline->moveRow('UP');
    }

    function moveLineDown($invheadID,
                          $sequenceNo
    )
    {
        if (!$invheadID) {
            $this->raiseError('invheadID not passed');
        }
        if (!$sequenceNo) {
            $this->raiseError('sequenceNo not passed');
        }
        $dbeInvline = new DBEInvline($this);
        if ($dbeInvline->getMaxSequenceNo($invheadID) > $sequenceNo) {
            $dbeInvline->setValue(
                DBEInvline::invheadID,
                $invheadID
            );
            $dbeInvline->setValue(
                DBEInvline::sequenceNo,
                $sequenceNo
            );
            $dbeInvline->moveRow('DOWN');
        }
    }

    function deleteLine($invheadID,
                        $sequenceNo
    )
    {
        if (!$invheadID) {
            $this->raiseError('invheadID not passed');
        }
        if (!$sequenceNo) {
            $this->raiseError('sequenceNo not passed');
        }
        $dbeInvline = new DBEInvline($this);
        $dbeInvline->setValue(
            DBEInvline::invheadID,
            $invheadID
        );
        $dbeInvline->setValue(
            DBEInvline::sequenceNo,
            $sequenceNo
        );
        $dbeInvline->deleteRow();
        $dbeInvline->setValue(
            DBEInvline::invheadID,
            $invheadID
        );
        $dbeInvline->setValue(
            DBEInvline::sequenceNo,
            $sequenceNo
        );
        $dbeInvline->shuffleRowsUp();
    }

    function updateHeader($invheadID,
                          $custPORef,
                          $paymentTermsID
    )
    {
        $this->setMethodName(' updateHeader');
        if (!$invheadID) {
            $this->raiseError('invheadID not passed');
        }
        $this->dbeInvhead->getRow($invheadID); //existing values
        $this->dbeInvhead->setValue(
            DBEInvhead::custPORef,
            $custPORef
        );
        $this->dbeInvhead->setValue(
            DBEInvhead::paymentTermsID,
            $paymentTermsID
        );
        $this->dbeInvhead->updateRow();
        return TRUE;
    }

    function updateAddress($invheadID,
                           $siteNo
    )
    {
        $this->setMethodName('updateAddress');
        if (!$invheadID) {
            $this->raiseError('invheadID not passed');
        }
        $dbeInvhead = &$this->dbeInvhead;
        $dbeInvhead->getRow($invheadID); //existing values
        $buSite = new BUSite($this);
        $dsSite = new DataSet($this);
        $buSite->getSiteByID(
            $dbeInvhead->getValue(DBEInvhead::customerID),
            (int)$siteNo,
            $dsSite
        );
        $buContact = new BUContact($this);
        $dsContact = new DataSet($this);
        if (!$buContact->getContactByID(
            $dsSite->getValue(DBESite::invoiceContactID),
            $dsContact
        )) {
            $this->raiseError('contact not found');
        }
        $dbeInvhead->setValue(
            DBEInvhead::siteNo,
            $siteNo
        );
        $dbeInvhead->setValue(
            DBEInvhead::add1,
            $dsSite->getValue(DBESite::add1)
        );
        $dbeInvhead->setValue(
            DBEInvhead::add2,
            $dsSite->getValue(DBESite::add2)
        );
        $dbeInvhead->setValue(
            DBEInvhead::add3,
            $dsSite->getValue(DBESite::add3)
        );
        $dbeInvhead->setValue(
            DBEInvhead::town,
            $dsSite->getValue(DBESite::town)
        );
        $dbeInvhead->setValue(
            DBEInvhead::county,
            $dsSite->getValue(DBESite::county)
        );
        $dbeInvhead->setValue(
            DBEInvhead::postcode,
            $dsSite->getValue(DBESite::postcode)
        );
        $dbeInvhead->setValue(
            DBEInvhead::contactID,
            $dsSite->getValue(DBESite::invoiceContactID)
        );
        $dbeInvhead->setValue(
            DBEInvhead::contactName,
            $dsContact->getValue(DBEContact::firstName) . ' ' . $dsContact->getValue(DBEContact::lastName)
        );
        $dbeInvhead->updateRow();
        return TRUE;
    }

    function updateContact($invheadID,
                           $contactID
    )
    {
        $this->setMethodName('updateContact');
        if (!$invheadID) {
            $this->raiseError('invheadID not passed');
        }
        if (!$contactID) {
            $this->raiseError('contactID not passed');
        }
        $dbeInvhead = &$this->dbeInvhead;
        $buContact  = new BUContact($this);
        $dsContact  = new DataSet($this);
        $buContact->getContactByID(
            $contactID,
            $dsContact
        );
        $dbeInvhead->getRow($invheadID); //existing values
        $dbeInvhead->setValue(
            DBEInvhead::contactID,
            $contactID
        );
        $dbeInvhead->setValue(
            DBEInvhead::contactName,
            $dsContact->getValue(DBEContact::firstName) . ' ' . $dsContact->getValue(DBEContact::lastName)
        );
        $dbeInvhead->updateRow();
        return TRUE;
    }

    function deleteInvoice($invheadID)
    {
        $this->setMethodName('deleteInvoice');
        if (!$invheadID) {
            $this->raiseError('invheadID not passed');
        }
        $dbeInvhead = &$this->dbeInvhead;
        $dbeInvline = new DBEInvline($this);
        if (!$dbeInvhead->getRow($invheadID)) {
            $this->raiseError('invoice not found');
        }
        $dbeInvhead->deleteRow();
        $dbeInvline->setValue(
            DBEInvhead::invheadID,
            $invheadID
        );
        return ($dbeInvline->deleteRowsByInvheadID());
    }

    /**
     * Insert new ordline dataset row
     * This changes the database
     * @param DataSet|DSForm $dsInvline
     * @return void : Success
     * @access public
     */
    function insertNewLine(&$dsInvline)
    {
        $this->setMethodName('insertNewInvline');
//count rows
        $dsInvline->fetchNext();
        $dbeInvline = new DBEInvline($this);
        $dbeInvline->setValue(
            DBEInvline::invheadID,
            $dsInvline->getValue(DBEInvline::invheadID)
        );
        if ($dbeInvline->countRowsByColumn(DBEInvline::invheadID) > 0) {
            // shuffle down existing rows before inserting new one
            $dbeInvline->setValue(
                DBEInvline::invheadID,
                $dsInvline->getValue(DBEInvline::invheadID)
            );
            $dbeInvline->setValue(
                DBEInvline::sequenceNo,
                $dsInvline->getValue(DBEInvline::sequenceNo)
            );
            $dbeInvline->shuffleRowsDown();
        }
        $this->updateLine(
            $dsInvline,
            "I"
        );
    }

    /**
     * update one invoice line
     * @param DSForm $dsInvline Record
     * @param string $action
     */
    function updateLine(&$dsInvline,
                        $action = "U"
    )
    {
        $this->setMethodName('updateLine');
        $dbeInvhead = new DBEInvhead($this);
        $dbeInvhead->setPKValue($dsInvline->getValue(DBEInvline::invheadID));
        if (!$dbeInvhead->getRow()) {
            $this->raiseError('invoice header not found');
        }
        // ordline fields
        $dbeInvline = new DBEInvline($this);
        $dbeInvline->setValue(
            DBEInvline::lineType,
            $dsInvline->getValue(DBEInvline::lineType)
        );
        $dbeInvline->setValue(
            DBEInvline::qty,
            $dsInvline->getValue(DBEInvline::qty)
        );
        $dbeInvline->setValue(
            DBEInvline::curUnitSale,
            $dsInvline->getValue(DBEInvline::curUnitSale)
        );
        $dbeInvline->setValue(
            DBEInvline::curUnitCost,
            $dsInvline->getValue(DBEInvline::curUnitCost)
        );
        $dbeInvline->setValue(
            DBEInvline::invheadID,
            $dsInvline->getValue(DBEInvline::invheadID)
        );
        $dbeInvline->setValue(
            DBEInvline::sequenceNo,
            $dsInvline->getValue(DBEInvline::sequenceNo)
        );
        if ($dsInvline->getValue(DBEInvline::lineType) == 'I') {
            $dbeInvline->setValue(
                DBEInvline::itemID,
                $dsInvline->getValue(DBEInvline::itemID)
            );
            $dbeInvline->setValue(
                DBEInvline::stockcat,
                $dsInvline->getValue(DBEInvline::stockcat)
            );
        }
        $dbeInvline->setValue(
            DBEInvline::description,
            $dsInvline->getValue(DBEInvline::description)
        );
        if ($action == "U") {
            $dbeInvline->updateRow();
        } else {
            $dbeInvline->insertRow();
        }
    }

    /**
     * Initialise new ordline dataset row
     * This DOES NOT change the database
     * @parameter dateset $dsOrdline
     * @access public
     * @param $invheadID
     * @param $sequenceNo
     * @param $dsInvline
     */
    function initialiseNewInvline($invheadID,
                                  $sequenceNo,
                                  &$dsInvline
    )
    {
        $this->setMethodName('initialiseNewInvline');
        if (!$invheadID) {
            $this->raiseError('invheadID not passed');
        }
        if (!$sequenceNo) {
            $this->raiseError('sequenceNo not passed');
        }
        $dbeJInvline = new DBEJInvline($this);
        $dsInvline   = new DSForm($this);
        $dsInvline->copyColumnsFrom($dbeJInvline);
        $dsInvline->setUpdateModeInsert();
        $dsInvline->setValue(
            DBEInvline::invheadID,
            $invheadID
        );
        $dsInvline->setValue(
            DBEInvline::itemID,
            null
        );
        $dsInvline->setValue(
            DBEInvline::sequenceNo,
            $sequenceNo
        );
        $dsInvline->setValue(
            DBEInvline::lineType,
            'I'
        );    // default item line
        $dsInvline->setValue(
            DBEInvline::qty,
            1
        );    // default 1
        $dsInvline->post();
    }

    function getCustomersWithoutInvoiceContact()
    {
        $buCustomer = new BUCustomer($this);
        $dsInvhead  = new DataSet($this);
        $this->getUnprintedInvoices($dsInvhead);
        $list = [];
        while ($dsInvhead->fetchNext()) {

            if (!$buCustomer->getInvoiceContactsByCustomerID(
                $dsInvhead->getValue(DBEInvhead::customerID),
                $dsContact
            )) {
                $dsCustomer = new DataSet($this);
                $buCustomer->getCustomerByID(
                    $dsInvhead->getValue(DBEInvhead::customerID),
                    $dsCustomer
                );
                $list[] = $dsCustomer->getValue(DBECustomer::name);

            }

        }
        return $list;

    }

    /**
     * Get unprinted Invoice rows
     * @param $dsResults
     * @param bool $directDebit
     * @return bool &$dsResults results
     * @access public
     */
    function getUnprintedInvoices(&$dsResults,
                                  $directDebit = false
    )
    {
        $this->setMethodName('getUnprintedInvoices');
        $this->dbeJInvhead->getUnprintedRows($directDebit);
        return ($this->getData(
            $this->dbeJInvhead,
            $dsResults
        ));
    }

    function printDirectDebitInvoices(string $dateToUse,
                                      DateTimeImmutable $collectionDate,
                                      $privateKey
    )
    {
        if (!$dateToUse) {
            $dateToUse = date('Y-m-d');    // use today if blank
        }
        $dbeInvhead                                   = new DBEInvhead($this);
        $buCustomer                                   = new BUCustomer($this);
        $invoiceWithAllDirectDebitServicesPerCustomer = new DataSet($this);
        $this->getUnprintedInvoices(
            $invoiceWithAllDirectDebitServicesPerCustomer,
            true
        );
        $subject        = 'CNC Direct Debit Billing Notice And Invoice Attached';
        $invoiceNumbers = array();
        $buMail         = new BUMail($this);
        $bankData       = [];
        while ($invoiceWithAllDirectDebitServicesPerCustomer->fetchNext()) {
            $invoiceNumbers[]         = $invoiceWithAllDirectDebitServicesPerCustomer->getValue(DBEInvhead::invheadID);
            $generatedInvoiceFilePath = $this->generateAndStorePDFForInvoice(
                $dbeInvhead,
                $invoiceWithAllDirectDebitServicesPerCustomer,
                $dateToUse
            );
            $fileName                 = "{$invoiceWithAllDirectDebitServicesPerCustomer->getValue(DBEInvhead::invheadID)}.pdf";
            $attachments              = new AttachmentCollection();
            $attachments->add(
                $generatedInvoiceFilePath,
                'Application/pdf',
                $fileName,
                true
            );
            $dsCustomer = new DataSet($this);
            $buCustomer->getCustomerByID(
                $invoiceWithAllDirectDebitServicesPerCustomer->getValue(DBEInvhead::customerID),
                $dsCustomer
            );
            $dsSite = new DataSet($this);
            $buCustomer->getSiteByCustomerIDSiteNo(
                $invoiceWithAllDirectDebitServicesPerCustomer->getValue(DBEInvhead::customerID),
                $dsCustomer->getValue(DBECustomer::invoiceSiteNo),
                $dsSite
            );
            $invoiceTotal        = $this->getInvoiceTotal(
                $invoiceWithAllDirectDebitServicesPerCustomer->getValue(DBEInvhead::invheadID)
            );
            $unEncryptedSortCode = null;
            if ($dsCustomer->getValue(DBECustomer::sortCode)) {
                openssl_private_decrypt(
                    base64_decode($dsCustomer->getValue(DBECustomer::sortCode)),
                    $unEncryptedSortCode,
                    $privateKey,
                    OPENSSL_PKCS1_OAEP_PADDING
                );
            }
            $unEncryptedAccountNumber = null;
            if ($dsCustomer->getValue(DBECustomer::accountNumber)) {
                openssl_private_decrypt(
                    base64_decode($dsCustomer->getValue(DBECustomer::accountNumber)),
                    $unEncryptedAccountNumber,
                    $privateKey,
                    OPENSSL_PKCS1_OAEP_PADDING
                );
            }
            $vatValue    = $invoiceTotal * ($dbeInvhead->getValue(DBEInvhead::vatRate) / 100);
            $totalAmount = $invoiceTotal + $vatValue;
            $bankRow     = [
                $unEncryptedSortCode,
                $dsCustomer->getValue(DBECustomer::accountName),
                $unEncryptedAccountNumber,
                number_format(
                    $totalAmount,
                    2
                ),
                $invoiceWithAllDirectDebitServicesPerCustomer->getValue(DBEInvhead::invheadID),
                $invoiceWithAllDirectDebitServicesPerCustomer->getValue(DBEInvhead::transactionType)
            ];
            $bankData[]  = $bankRow;
            $dsContact   = new DataSet($this);
            $buCustomer->getInvoiceContactsByCustomerID(
                $invoiceWithAllDirectDebitServicesPerCustomer->getValue(DBEInvhead::customerID),
                $dsContact
            );
            $buHeader = new BUHeader($this);
            /** @var DBEHeader $dsHeader */
            $dsHeader = null;
            $buHeader->getHeader($dsHeader);
            while ($dsContact->fetchNext()) {
                $directDebitInvoiceEmailDTO = new DirectDebitInvoiceDTO(
                    $dsContact->getValue(DBEContact::firstName) . " " . $dsContact->getValue(DBEContact::lastName),
                    $dsCustomer->getValue(DBECustomer::name),
                    $dsSite->getValue(DBESite::add1),
                    $dsSite->getValue(DBESite::town),
                    $dsSite->getValue(DBESite::county),
                    $dsSite->getValue(DBESite::postcode),
                    (new DateTime())->format('d M Y'),
                    $invoiceWithAllDirectDebitServicesPerCustomer->getValue(DBEInvhead::invheadID),
                    $collectionDate->format('d M Y'),
                    $totalAmount
                );
                global $twig;
                $body    = $twig->render(
                    '@customerFacing/DirectDebitInvoice/DirectDebitInvoice.html.twig',
                    ["data" => $directDebitInvoiceEmailDTO]
                );
                $toEmail = $dsContact->getValue(DBEContact::email);
                $buMail->sendEmailWithAttachments($body, $subject, $toEmail, $attachments, 'accounts@cnc-ltd.co.uk');
            }
        }
        $this->sendDirectDebitSageSalesEmail($invoiceNumbers, $bankData);
        return count($invoiceNumbers);
    }

    /**
     * @param DBEInvhead $dbeInvhead
     * @param DataSet $invoiceWithAllDirectDebitServicesPerCustomer
     * @param string|null $dateToUse
     * @return String
     */
    private function generateAndStorePDFForInvoice(DBEInvhead $dbeInvhead,
                                                   DataSet $invoiceWithAllDirectDebitServicesPerCustomer,
                                                   ?string $dateToUse
    ): string
    {
        $dbeInvhead->getRow($invoiceWithAllDirectDebitServicesPerCustomer->getValue(DBEInvhead::invheadID));
        $buPdfInvoice             = new BUPDFInvoice(
            $this, $this
        );
        $buPdfInvoice->_dateToUse = $dateToUse;
        $pdfFileName              = $buPdfInvoice->generateFile($invoiceWithAllDirectDebitServicesPerCustomer);
        $fileSize                 = filesize($pdfFileName);
        $dbeInvhead->setValue(
            DBEInvhead::pdfFile,
            fread(
                fopen(
                    $pdfFileName,
                    'rb'
                ),
                $fileSize
            )
        );
        $dbeInvhead->setValue(
            DBEInvhead::datePrinted,
            $dateToUse
        );
        $dbeInvhead->updateRow();
        return $pdfFileName;
    }

    public function calculateDirectDebitPaymentDate(DateTime $date)
    {
        $lastYearBh   = common_getUKBankHolidays($date->format('Y') - 1);
        $thisYearBh   = common_getUKBankHolidays($date->format('Y'));
        $nextYearBh   = common_getUKBankHolidays((int)$date->format('Y') + 1);
        $bankHolidays = array_merge(
            $lastYearBh,
            $thisYearBh,
            $nextYearBh
        );
        $dateCloned   = clone $date;
        $counter      = 0;
        while ($counter < 5) {
            $dateCloned->add(new DateInterval('P1D'));
            if (in_array(
                    $dateCloned->format('Y-m-d'),
                    $bankHolidays
                ) || $dateCloned->format('N') > 5) {
                continue; // ignore holidays
            }
            $counter++;
        }
        return $dateCloned;
    }

    function getInvoiceTotal($invheadID)
    {
        $dbeInvline = new DBEInvline($this);
        $dbeInvline->setValue(
            DBEInvline::invheadID,
            $invheadID
        );
        $dbeInvline->getRowsByColumn(DBEInvline::invheadID);
        $value = 0;
        while ($dbeInvline->fetchNext()) {
            if ($dbeInvline->getValue(DBEInvline::lineType) == 'I') {
                $value += $dbeInvline->getValue(DBEInvline::qty) * $dbeInvline->getValue(DBEInvline::curUnitSale);
            }
        }
        return $value;
    }

    /**
     * @param array $invoiceNumbers
     * @param array $bankData
     */
    private function sendDirectDebitSageSalesEmail(array $invoiceNumbers, array $bankData): void
    {
        $this->buSageExport->generateSageSalesDataByInvoiceNumbers($invoiceNumbers);
        $senderEmail = CONFIG_SALES_EMAIL;
        $toEmail     = CONFIG_SALES_EMAIL;
        $senderName  = 'CNC Sales';
        $subject     = 'Sage Import Files';
        $buMail      = new BUMail($this);
        $hdrs        = array(
            'From'    => $senderName . " <" . $senderEmail . ">",
            'To'      => $toEmail,
            'Subject' => $subject
        );
        $buMail->mime->setTXTBody('Sage import files from invoice run attached.');
        $fileName = SAGE_EXPORT_DIR . '/sales.csv';
        $buMail->mime->addAttachment(
            $fileName,
            'Text/csv',
            'sales.csv'
        );
        $fileName = SAGE_EXPORT_DIR . '/trans.csv';
        $buMail->mime->addAttachment(
            $fileName,
            'Text/csv',
            'trans.csv'
        );
        $data = $this->generateBankExport($bankData);
        $buMail->mime->addAttachment(
            $data,
            'Text/csv',
            'bankExport.csv',
            false
        );
        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body        = $buMail->mime->get($mime_params);
        $hdrs        = $buMail->mime->headers($hdrs);
        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    }

    public function generateBankExport($bankData)
    {
        $fd = fopen(
            'php://temp/maxmemory:1048576',
            'w'
        );
        if ($fd === FALSE) {
            die('Failed to open temporary file');
        }
        $headers = $bankData[0];
        array_shift($bankData);
        $records = $bankData;
        fputcsv(
            $fd,
            $headers
        );
        foreach ($records as $record) {
            fputcsv(
                $fd,
                $record
            );
        }
        rewind($fd);
        $csv = stream_get_contents($fd);
        fclose($fd); //
        return $csv;
    }

    /**
     * This method generates PDF invoices then emails them to customers
     *
     * One email per customer. Email body contains summary.
     *
     * @param string|null $dateToUse
     * @return int
     */
    function printUnprintedInvoices(?string $dateToUse)
    {

        if (!$dateToUse) {
            $dateToUse = date('Y-m-d');    // use today if blank
        }
        $dbeInvhead = new DBEInvhead($this);
        $buCustomer = new BUCustomer($this);
        $dsInvhead  = new DataSet($this);
        $this->getUnprintedInvoices($dsInvhead);
        $invoiceCount   = 0;
        $subject        = 'CNC Sales Invoice(s)';
        $invoiceNumbers = array();
        /** @var BUMail $buMail */
        $buMail = new BUMail($this);
        /** @var SalesInvoiceEmailDTO[] $invoiceEmails */
        $invoiceEmails = [];
        while ($dsInvhead->fetchNext()) {
            $invoiceCount++;
            $customerId = $dsInvhead->getValue(DBEInvhead::customerID);
            if (!key_exists($customerId, $invoiceEmails)) {
                $invoiceEmails[$customerId] = new SalesInvoiceEmailDTO();
                $dsContact                  = new DataSet($this);
                $buCustomer->getInvoiceContactsByCustomerID(
                    $customerId,
                    $dsContact
                );
                while ($dsContact->fetchNext()) {
                    $invoiceEmails[$customerId]->addEmail($dsContact->getValue(DBEContact::email));
                }
            }
            $invoiceId = $dsInvhead->getValue(DBEInvhead::invheadID);
            $dbeInvhead->getRow($invoiceId);
            $invoiceNumbers[] = $invoiceId;
            $invoiceTotal     = $this->getInvoiceTotal($invoiceId);
            $invoiceEmails[$customerId]->addInvoice($invoiceId, $invoiceTotal);
            /*
            * generate PDF Invoice
            */
            $buPdfInvoice             = new BUPDFInvoice(
                $this, $this
            );
            $buPdfInvoice->_dateToUse = $dateToUse;
            $pdfFileName              = $buPdfInvoice->generateFile($dsInvhead);
            $fileSize                 = filesize($pdfFileName);
            /*
            Save PDF file into BLOB field on database
            */
            $dbeInvhead->setValue(
                DBEInvhead::pdfFile,
                fread(
                    fopen(
                        $pdfFileName,
                        'rb'
                    ),
                    $fileSize
                )
            );
            $dbeInvhead->setValue(
                DBEInvhead::datePrinted,
                $dateToUse
            );
            $dbeInvhead->updateRow();
            unset($buPdfInvoice);
            $fileName = $invoiceId . '.pdf';
            $invoiceEmails[$customerId]->addPDFInvoiceAttachment($pdfFileName, $fileName);
        }
        if ($invoiceCount) {
            global $twig;
            foreach ($invoiceEmails as $invoiceEmail) {
                $body = $twig->render(
                    '@customerFacing/SalesInvoice/SalesInvoice.html.twig',
                    ["data" => $invoiceEmail]
                );
                $buMail->sendEmailWithAttachments(
                    $body,
                    $subject,
                    $invoiceEmail->getEmails(),
                    $invoiceEmail->getAttachments(),
                    CONFIG_SALES_EMAIL
                );
            }
        }
        $this->sendSageSalesEmail($invoiceNumbers);
        return $invoiceCount;

    }

    /**
     * @param array $invoiceNumbers
     */
    public function sendSageSalesEmail(array $invoiceNumbers): void
    {
        $buMail = new BUMail($this);
        $this->buSageExport->generateSageSalesDataByInvoiceNumbers($invoiceNumbers);
        $subject     = 'Sage Import Files';
        $attachments = new AttachmentCollection();
        $fileName    = SAGE_EXPORT_DIR . '/sales.csv';
        $attachments->add(
            $fileName,
            'Text/csv',
            'sales.csv',
            true
        );
        $fileName = SAGE_EXPORT_DIR . '/trans.csv';
        $attachments->add(
            $fileName,
            'Text/csv',
            'trans.csv',
            true
        );
        $buMail->sendEmailWithAttachments(
            'Sage import files from invoice run attached.',
            $subject,
            CONFIG_SALES_MANAGER_EMAIL,
            $attachments,
            CONFIG_SALES_EMAIL
        );
    }

    function getInvoiceLines($invheadID,
                             &$dsResults
    )
    {
        $this->setMethodName('getInvoiceLines');
        if (!$invheadID) {
            $this->raiseError('invheadID not passed');
        }
        $this->dbeJInvline->setValue(
            DBEJInvline::invheadID,
            $invheadID
        );
        $this->dbeJInvline->getRowsByColumn(
            DBEJInvline::invheadID
        );
        return ($this->getData(
            $this->dbeJInvline,
            $dsResults
        ));
    }

    function trialPrintUnprintedInvoices($dateToUse,
                                         $directDebit = false
    )
    {

        if (!$dateToUse) {
            $dateToUse = date('Y-m-d');    // use today if blank
        }
        $this->getUnprintedInvoices(
            $dsInvhead,
            $directDebit
        );
        $buPdfInvoice             = new BUPDFInvoice(
            $this, $this
        );
        $buPdfInvoice->_dateToUse = $dateToUse;
        return $buPdfInvoice->generateBatchFile($dsInvhead);

    }

    function regeneratePdfInvoice($invoiceID)
    {
        $dbeInvhead = new DBEInvhead($this);
        $dbeInvhead->getRow($invoiceID);
        $this->dbeJInvhead->setValue(
            DBEInvhead::invheadID,
            $invoiceID
        );
        $this->dbeJInvhead->getRow();
        $buPdfInvoice             = new BUPDFInvoice(
            $this, $this
        );
        $buPdfInvoice->_dateToUse = $dbeInvhead->getValue(DBEInvhead::datePrinted);
        $pdfFileName              = $buPdfInvoice->generateFile($this->dbeJInvhead);
        $fileSize                 = filesize($pdfFileName);
        $fileString               = fread(
            fopen(
                $pdfFileName,
                'rb'
            ),
            $fileSize
        );
        $dbeInvhead->setValue(
            DBEInvhead::pdfFile,
            $fileString
        );
        $dbeInvhead->updateRow();
        unset($buPdfInvoice);
        unlink($pdfFileName); // delete temp file
        return $fileString;

    }
}// End of class
?>