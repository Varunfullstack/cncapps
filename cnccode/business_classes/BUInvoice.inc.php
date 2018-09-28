<?php /**
 * Invoice business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
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
    var $dbeInvhead = "";
    var $dbeJInvhead = "";
    var $dbeJInvline = "";
    var $buSageExport = '';

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeInvhead = new DBEInvhead($this);
        $this->dbeJInvhead = new DBEJInvhead($this);
        $this->dbeJInvline = new DBEJInvline($this);
        $this->buSageExport = new BUSageExport($this);
    }

    /**
     * initialise values for input of date range
     * @return DataSet &$dsData results
     * @access public
     */
    function initialiseDataset(&$dsData)
    {
        $this->setMethodName('initialiseDataset');
        $dsData = new DSForm($this);
        $dsData->addColumn(
            'customerID',
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'startDate',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'endDate',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'startInvheadID',
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'endInvheadID',
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsData->setValue(
            'startDate',
            ''
        );
        $dsData->setValue(
            'endDate',
            ''
        );
        $dsData->setValue(
            'startInvheadID',
            ''
        );
        $dsData->setValue(
            'endInvheadID',
            ''
        );
    }

    function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn(
            'printedFlag',
            DA_YN,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'startDate',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'endDate',
            DA_DATE,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'invheadID',
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'ordheadID',
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'customerID',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'customerName',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->addColumn(
            'invoiceType',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->setValue(
            'printedFlag',
            'N'
        );
        $dsData->setValue(
            'startDate',
            ''
        );
        $dsData->setValue(
            'endDate',
            ''
        );
        $dsData->setValue(
            'invheadID',
            ''
        );
        $dsData->setValue(
            'ordheadID',
            ''
        );
        $dsData->setValue(
            'customerID',
            ''
        );
        $dsData->setValue(
            'invoiceType',
            ''
        );
        $dsData->setValue(
            'customerName',
            ''
        );
    }

    function search(&$dsSearchForm,
                    &$dsResults
    )
    {
        $dsSearchForm->initialise();
        $dsSearchForm->fetchNext();
        if ($dsSearchForm->getValue('invheadID') != '') {
            $this->getDatasetByPK(
                $dsSearchForm->getValue('invheadID'),
                $this->dbeJInvhead,
                $dsResults
            );
        } else {
            $this->dbeJInvhead->getRowsBySearchCriteria(
                trim($dsSearchForm->getValue('customerID')),
                trim($dsSearchForm->getValue('ordheadID')),
                trim($dsSearchForm->getValue('printedFlag')),
                trim($dsSearchForm->getValue('startDate')),
                trim($dsSearchForm->getValue('endDate')),
                trim($dsSearchForm->getValue('invoiceType'))
            );
            $this->dbeJInvhead->initialise();
            $dsResults = $this->dbeJInvhead;
        }
        return $ret;
    }

    function getPrintedInvoicesByRange(
        $customerID,
        $startDate,
        $endDate,
        $startInvheadID,
        $endInvheadID,
        &$dsResults
    )
    {
        $this->setMethodName('getPrintedInvoicesByRange');
        if ($endDate == '') {
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

    function getInvoiceLines($invheadID,
                             &$dsResults
    )
    {
        $this->setMethodName('getInvoiceLines');
        if ($invheadID == '') {
            $this->raiseError('invheadID not passed');
        }
        $this->dbeJInvline->setValue(
            'invheadID',
            $invheadID
        );
        $this->dbeJInvline->getRowsByColumn(
            'invheadID',
            'sequenceNo'
        );
        return ($this->getData(
            $this->dbeJInvline,
            $dsResults
        ));
    }

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

            if ($dsDespatch->getValue('qtyToDespatch') > 0) {

                if ($dsOrdline->getValue('lineType') == 'I') {

                    $totalValue += $dsDespatch->getValue('qtyToDespatch') * $dsOrdline->getValue('curUnitSale');

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
            if ($dsDespatch->getValue('qtyToDespatch') == 0) {
                continue;
            }
            $sequenceNo++;
            $dbeInvline->setValue(
                'invheadID',
                $invheadID
            );
            $dbeInvline->setValue(
                'sequenceNo',
                $sequenceNo
            );
            $dbeInvline->setValue(
                'ordSequenceNo',
                $dsOrdline->getValue('sequenceNo')
            );
            $dbeInvline->setValue(
                'lineType',
                $dsOrdline->getValue('lineType')
            );
            $dbeInvline->setValue(
                'itemID',
                $dsOrdline->getValue('itemID')
            );
            $dbeInvline->setValue(
                'description',
                $dsOrdline->getValue('description')
            );
            $dbeInvline->setValue(
                'qty',
                $dsDespatch->getValue('qtyToDespatch')
            );
            $dbeInvline->setValue(
                'curUnitSale',
                $dsOrdline->getValue('curUnitSale')
            );
            $dbeInvline->setValue(
                'curUnitCost',
                $dsOrdline->getValue('curUnitCost')
            );
            $dbeInvline->setValue(
                'stockcat',
                $dsOrdline->getValue('stockcat')
            );
            $dbeInvline->insertRow();
        }
        unset($dbeInvline);
        return $invheadID;
    }

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
            $dsOrdhead->getValue('vatCode')
        );

        $this->dbeInvhead->setValue(
            DBEInvhead::directDebit,
            $dsOrdhead->getValue(DBEOrdhead::directDebit)
        );

        $dbeVat = new DBEVat($this);
        $dbeVat->getRow();
        $vatCode = $dsOrdhead->getValue('vatCode');
        $vatRate = $dbeVat->getValue((integer)$vatCode[1]); // use second part of code as column no

        $this->dbeInvhead->setValue(
            'vatRate',
            $vatRate
        );

        $this->dbeInvhead->setValue(
            'intPORef',
            $dsOrdhead->getValue('quotationOrdheadID')
        );
        $this->dbeInvhead->setValue(
            'custPORef',
            $dsOrdhead->getValue('custPORef')
        );
        $this->dbeInvhead->setValue(
            'debtorCode',
            $dsOrdhead->getValue('debtorCode')
        );
        $this->dbeInvhead->setValue(
            'source',
            'S'
        );// sales (consultancy no longer used)
        $this->dbeInvhead->setValue(
            'vatOnly',
            'N'
        );// no longer used
        $this->dbeInvhead->setValue(
            'datePrinted',
            '0000-00-00'
        );
        $this->dbeInvhead->insertRow();
        return $this->dbeInvhead->getPKValue();
    }

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

            if ($dsOrdline->getValue('lineType') == 'I') {

                $totalValue += $dsOrdline->getValue('qtyOrdered') * $dsOrdline->getValue('curUnitSale');

            }

        }

        if ($totalValue == 0) {
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
                'invheadID',
                $invheadID
            );
            $dbeInvline->setValue(
                'sequenceNo',
                $sequenceNo
            );
            $dbeInvline->setValue(
                'ordSequenceNo',
                $dsOrdline->getValue('sequenceNo')
            );
            $dbeInvline->setValue(
                'lineType',
                $dsOrdline->getValue('lineType')
            );
            $dbeInvline->setValue(
                'itemID',
                $dsOrdline->getValue('itemID')
            );
            $dbeInvline->setValue(
                'description',
                $dsOrdline->getValue('description')
            );
            $dbeInvline->setValue(
                'qty',
                $dsOrdline->getValue('qtyOrdered')
            );
            $dbeInvline->setValue(
                'curUnitSale',
                $dsOrdline->getValue('curUnitSale')
            );
            $dbeInvline->setValue(
                'curUnitCost',
                $dsOrdline->getValue('curUnitCost')
            );
            $dbeInvline->setValue(
                'stockcat',
                $dsOrdline->getValue('stockcat')
            );
            $dbeInvline->insertRow();
        }
        unset($dbeInvline);
        return $invheadID;
    }

    function countInvoicesByOrdheadID($ID)
    {
        $this->setMethodName('countInvoicesByOrdheadID');
        if ($ID == '') {
            $this->raiseError('ordheadID not passed');
        }
        $this->dbeInvhead->setValue(
            'ordheadID',
            $ID
        );
        return ($this->dbeInvhead->countRowsByColumn('ordheadID'));
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

    function getUnprintedInvoiceValues(&$dsResults)
    {
        $this->setMethodName('getUnprintedInvoiceValues');
        $dbeInvoiceTotals = new DBEInvoiceTotals($this);
        $dbeInvoiceTotals->getRow('I');
        return ($this->getData(
            $dbeInvoiceTotals,
            $dsResults
        ));
    }

    function createNewInvoice($customerID,
                              $type = 'I'
    )
    {
        $dbeInvhead = &$this->dbeInvhead;
        $buCustomer = new BUCustomer($this);
        $buCustomer->getCustomerByID(
            $customerID,
            $dsCustomer
        );
        $buSite = new BUSite($this);
        $buSite->getSiteByID(
            $customerID,
            $dsCustomer->getValue(DBECustomer::invoiceSiteNo),
            $dsSite
        );
        $buContact = new BUContact($this);
        $buContact->getContactByID(
            $dsSite->getValue(DBESite::invoiceContactID),
            $dsContact
        );
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);
        $dbeInvhead->setValue(
            'invheadID',
            0
        ); // for new number
        $dbeInvhead->setValue(
            'customerID',
            $customerID
        );
        $dbeInvhead->setValue(
            'siteNo',
            $dsCustomer->getValue(DBECustomer::invoiceSiteNo)
        );
        $dbeInvhead->setValue(
            'ordheadID',
            ''
        );
        $dbeInvhead->setValue(
            'type',
            $type
        );    // Invoice/Credit Note
        $dbeInvhead->setValue(
            'add1',
            $dsSite->getValue(DBESite::add1)
        );
        $dbeInvhead->setValue(
            'add2',
            $dsSite->getValue(DBESite::add2)
        );
        $dbeInvhead->setValue(
            'add3',
            $dsSite->getValue(DBESite::add3)
        );
        $dbeInvhead->setValue(
            'town',
            $dsSite->getValue(DBESite::town)
        );
        $dbeInvhead->setValue(
            'county',
            $dsSite->getValue(DBESite::county)
        );
        $dbeInvhead->setValue(
            'postcode',
            $dsSite->getValue(DBESite::postcode)
        );
        $dbeInvhead->setValue(
            'contactID',
            $dsSite->getValue(DBESite::invoiceContactID)
        );
        $dbeInvhead->setValue(
            'contactName',
            $dsContact->getValue('firstName') . ' ' . $dsContact->getValue('lastName')
        );
        $dbeInvhead->setValue(
            'paymentTermsID',
            CONFIG_PAYMENT_TERMS_30_DAYS
        );    // default
        $vatCode = $dsHeader->getValue('stdVATCode');
        $dbeInvhead->setValue(
            'vatCode',
            $vatCode
        );
        $dbeVat = new DBEVat($this);
        $dbeVat->getRow();
        $vatRate = $dbeVat->getValue((integer)$vatCode[1]); // get 2nd part of code and use as column no
        unset($dbeVat);
        $dbeInvhead->setValue(
            'vatRate',
            $vatRate
        );
        $dbeInvhead->setValue(
            'intPORef',
            ''
        );
        $dbeInvhead->setValue(
            'custPORef',
            ''
        );
        $dbeInvhead->setValue(
            'debtorCode',
            $dsSite->getValue(DBESite::debtorCode)
        );
        $dbeInvhead->setValue(
            'source',
            'S'
        );// sales (consultancy no longer used)
        $dbeInvhead->setValue(
            'vatOnly',
            'N'
        );// no longer used
        $dbeInvhead->setValue(
            'datePrinted',
            '0000-00-00'
        ); // not printed
        $dbeInvhead->insertRow();
        return ($dbeInvhead->getPKValue());
    }

    function createNewCreditNote($customerID)
    {
        return ($this->createNewInvoice(
            $customerID,
            'C'
        ));
    }

    function getInvoiceByID($invheadID,
                            &$dsInvhead,
                            &$dsInvline
    )
    {
        if ($invheadID == '') {
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
            'invheadID',
            $invheadID
        );
        $dbeJInvline->getRowsByColumn('invheadID');
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
        if ($invheadID == '') {
            $this->raiseError('invheadID not passed');
        }
        $dbeJInvhead = new DBEJInvhead($this);
        return ($this->getDatasetByPK(
            $invheadID,
            $dbeJInvhead,
            $dsInvhead
        ));
    }

    function getLinesByID($invheadID,
                          &$dsInvline
    )
    {
        $this->setMethodName('getLinesByID');
        if ($invheadID == '') {
            $this->raiseError('invheadID not passed');
        }
        $dbeJInvline = new DBEJInvline($this);
        $dbeJInvline->setValue(
            'invheadID',
            $invheadID
        );
        $dbeJInvline->getRowsByColumn('invheadID');
        $this->getData(
            $dbeJInvline,
            $dsInvline
        );
        return TRUE;
    }

    function getInvlineByIDSeqNo($invheadID,
                                 $sequenceNo,
                                 &$dsInvline
    )
    {
        $this->setMethodName('getInvlineByIDSeqNo');
        $ret = FALSE;
        if ($invheadID == '') {
            $this->raiseError('invoice ID not passed');
        }
        if ($sequenceNo == '') {
            $this->raiseError('sequenceNo not passed');
        }
        $dbeJInvline = new DBEJInvline($this);
        $dbeJInvline->setValue(
            'invheadID',
            $invheadID
        );
        $dbeJInvline->setValue(
            'sequenceNo',
            $sequenceNo
        );
        $dbeJInvline->getRowByInvheadIDSequenceNo();
        return ($this->getData(
            $dbeJInvline,
            $dsInvline
        ));
    }

    /**
     * Initialise new ordline dataset row
     * This DOES NOT change the database
     * @parameter dateset $dsInvline
     * @return bool : Success
     * @access public
     */
    function initialiseNewOrdline($invheadID,
                                  $sequenceNo,
                                  &$dsInvline
    )
    {
        $this->setMethodName('initialiseNewOrdline');
        if ($invheadID == '') {
            $this->raiseError('invheadID not passed');
        }
        if ($sequenceNo == '') {
            $this->raiseError('sequenceNo not passed');
        }
        $dbeJInvline = new DBEJInvline($this);
        $dsInvline = new DSForm($this);
        $dsInvline->copyColumnsFrom($dbeJInvline);
        $dsInvline->setAllowEmpty('itemID');
        $dsInvline->setUpdateModeInsert();
        $dsInvline->setValue(
            'invheadID',
            $invheadID
        );
        $dsInvline->setValue(
            'itemID',
            ''
        );
        $dsInvline->setValue(
            'sequenceNo',
            $sequenceNo
        );
        $dsInvline->setValue(
            'qtyInvoiceed',
            1
        );    // default 1
        $dsInvline->setValue(
            'qtyReceived',
            0
        );
        $dsInvline->setValue(
            'qtyInvoiced',
            0
        );
        $dsInvline->setValue(
            'curUnitCost',
            0
        );
        $dsInvline->setValue(
            'expectedDate',
            date(
                'Y-m-d',
                strtotime('+ 3 days')
            )
        ); // today + 3 days
        $dsInvline->post();
    }

    function moveLineUp($invheadID,
                        $sequenceNo
    )
    {
        if ($invheadID == '') {
            $this->raiseError('invheadID not passed');
        }
        if ($sequenceNo == '') {
            $this->raiseError('sequenceNo not passed');
        }
        if ($sequenceNo == 1) {
            return;
        }
        $dbeInvline = new DBEInvline($this);
        $dbeInvline->setValue(
            'invheadID',
            $invheadID
        );
        $dbeInvline->setValue(
            'sequenceNo',
            $sequenceNo
        );
        $dbeInvline->moveRow('UP');
    }

    function moveLineDown($invheadID,
                          $sequenceNo
    )
    {
        if ($invheadID == '') {
            $this->raiseError('invheadID not passed');
        }
        if ($sequenceNo == '') {
            $this->raiseError('sequenceNo not passed');
        }
        $dbeInvline = new DBEInvline($this);
        if ($dbeInvline->getMaxSequenceNo($invheadID) > $sequenceNo) {
            $dbeInvline->setValue(
                'invheadID',
                $invheadID
            );
            $dbeInvline->setValue(
                'sequenceNo',
                $sequenceNo
            );
            $dbeInvline->moveRow('DOWN');
        }
    }

    function deleteLine($invheadID,
                        $sequenceNo
    )
    {
        if ($invheadID == '') {
            $this->raiseError('invheadID not passed');
        }
        if ($sequenceNo == '') {
            $this->raiseError('sequenceNo not passed');
        }
        $dbeInvline = new DBEInvline($this);
        $dbeInvline->setValue(
            'invheadID',
            $invheadID
        );
        $dbeInvline->setValue(
            'sequenceNo',
            $sequenceNo
        );
        $dbeInvline->deleteRow();
        $dbeInvline->setValue(
            'invheadID',
            $invheadID
        );
        $dbeInvline->setValue(
            'sequenceNo',
            $sequenceNo
        );
        $dbeInvline->shuffleRowsUp();
    }

    function updateHeader(
        $invheadID,
        $custPORef,
        $paymentTermsID
    )
    {
        $this->setMethodName(' updateHeader');
        if ($invheadID == '') {
            $this->raiseError('invheadID not passed');
        }
        $this->dbeInvhead->getRow($invheadID); //existing values
        $this->dbeInvhead->setValue(
            'custPORef',
            $custPORef
        );
        $this->dbeInvhead->setValue(
            'paymentTermsID',
            $paymentTermsID
        );
        $this->dbeInvhead->updateRow();
        return TRUE;
    }

    function updateAddress(
        $invheadID,
        $siteNo
    )
    {
        $this->setMethodName('updateAddress');
        if ($invheadID == '') {
            $this->raiseError('invheadID not passed');
        }

        $dbeInvhead = &$this->dbeInvhead;

        $dbeInvhead->getRow($invheadID); //existing values

        $buSite = new BUSite($this);
        $buSite->getSiteByID(
            $dbeInvhead->getValue('customerID'),
            (int)$siteNo,
            $dsSite
        );

        $buContact = new BUContact($this);
        if (!$buContact->getContactByID(
            $dsSite->getValue(DBESite::invoiceContactID),
            $dsContact
        )) {
            $this->raiseError('contact not found');
        }

        $dbeInvhead->setValue(
            'siteNo',
            $siteNo
        );
        $dbeInvhead->setValue(
            'add1',
            $dsSite->getValue(DBESite::add1)
        );
        $dbeInvhead->setValue(
            'add2',
            $dsSite->getValue(DBESite::add2)
        );
        $dbeInvhead->setValue(
            'add3',
            $dsSite->getValue(DBESite::add3)
        );
        $dbeInvhead->setValue(
            'town',
            $dsSite->getValue(DBESite::town)
        );
        $dbeInvhead->setValue(
            'county',
            $dsSite->getValue(DBESite::county)
        );
        $dbeInvhead->setValue(
            'postcode',
            $dsSite->getValue(DBESite::postcode)
        );
        $dbeInvhead->setValue(
            'contactID',
            $dsSite->getValue(DBESite::invoiceContactID)
        );
        $dbeInvhead->setValue(
            'contactName',
            $dsContact->getValue('firstName') . ' ' . $dsContact->getValue('lastName')
        );

        $dbeInvhead->updateRow();
        return TRUE;
    }

    function updateContact($invheadID,
                           $contactID
    )
    {
        $this->setMethodName('updateContact');
        if ($invheadID == '') {
            $this->raiseError('invheadID not passed');
        }
        if ($contactID == '') {
            $this->raiseError('contactID not passed');
        }

        $dbeInvhead = &$this->dbeInvhead;
        $buContact = new BUContact($this);
        $buContact->getContactByID(
            $contactID,
            $dsContact
        );
        $dbeInvhead->getRow($invheadID); //existing values
        $dbeInvhead->setValue(
            'contactID',
            $contactID
        );
        $dbeInvhead->setValue(
            'contactName',
            $dsContact->getValue('firstName') . ' ' . $dsContact->getValue('lastName')
        );
        $dbeInvhead->updateRow();
        return TRUE;
    }

    function deleteInvoice($invheadID)
    {
        $this->setMethodName('deleteInvoice');
        if ($invheadID == '') {
            $this->raiseError('invheadID not passed');
        }
        $dbeInvhead = &$this->dbeInvhead;
        $dbeInvline = new DBEInvline($this);
        if (!$dbeInvhead->getRow($invheadID)) {
            $this->raiseError('invoice not found');
        }
        $dbeInvhead->deleteRow();
        $dbeInvline->setValue(
            'invheadID',
            $invheadID
        );
        return ($dbeInvline->deleteRowsByInvheadID());
    }

    /**
     * Insert new ordline dataset row
     * This changes the database
     * @parameter dateset $dsOrdline
     * @return bool : Success
     * @access public
     */
    function insertNewLine(&$dsInvline)
    {
        $this->setMethodName('insertNewInvline');
//count rows
        $dsInvline->fetchNext();
        $dbeInvline = new DBEInvline($this);
        $dbeInvline->setValue(
            'invheadID',
            $dsInvline->getValue('invheadID')
        );
        if ($dbeInvline->countRowsByColumn('invheadID') > 0) {
            // shuffle down existing rows before inserting new one
            $dbeInvline->setValue(
                'invheadID',
                $dsInvline->getValue('invheadID')
            );
            $dbeInvline->setValue(
                'sequenceNo',
                $dsInvline->getValue('sequenceNo')
            );
            $dbeInvline->shuffleRowsDown();
        }
        $ret = ($this->updateLine(
            $dsInvline,
            "I"
        ));
    }

    function getInvoiceValue($invheadID)
    {
        $dbeInvline = new DBEInvline($this);
        $dbeInvline->setValue(
            'invheadID',
            $invheadID
        );
        $dbeInvline->getRowsByColumn('invheadID');
        $value = 0;
        while ($dbeInvline->fetchNext()) {
            if ($dbeInvline->getValue('lineType') == 'I') {
                $value += $dbeInvline->getValue('qty') * $dbeInvline->getValue('curUnitSale');
            }
        }
        return $value;
    }

    /**
     * update one invoice line
     * @param DSForm $dsInvline Record
     * @return Bool Success
     */
    function updateLine(&$dsInvline,
                        $action = "U"
    )
    {
        $this->setMethodName('updateLine');
        $dbeInvhead = new DBEInvhead($this);
        $dbeInvhead->setPKValue($dsInvline->getValue('invheadID'));
        if (!$dbeInvhead->getRow()) {
            $this->raiseError('invoice header not found');
        }
        // ordline fields
        $dbeInvline = new DBEInvline($this);
        $dbeInvline->setValue(
            'lineType',
            $dsInvline->getValue('lineType')
        );
        $dbeInvline->setValue(
            'qty',
            $dsInvline->getValue('qty')
        );
        $dbeInvline->setValue(
            'curUnitSale',
            $dsInvline->getValue('curUnitSale')
        );
        $dbeInvline->setValue(
            'curUnitCost',
            $dsInvline->getValue('curUnitCost')
        );
        $dbeInvline->setValue(
            'invheadID',
            $dsInvline->getValue('invheadID')
        );
        $dbeInvline->setValue(
            'sequenceNo',
            $dsInvline->getValue('sequenceNo')
        );
        if ($dsInvline->getValue('lineType') == 'I') {
            $dbeInvline->setValue(
                'itemID',
                $dsInvline->getValue('itemID')
            );
            $dbeInvline->setValue(
                'stockcat',
                $dsInvline->getValue('stockcat')
            );
        }
        $dbeInvline->setValue(
            'description',
            $dsInvline->getValue('description')
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
     * @return bool : Success
     * @access public
     */
    function initialiseNewInvline($invheadID,
                                  $sequenceNo,
                                  &$dsInvline
    )
    {
        $this->setMethodName('initialiseNewInvline');
        if ($invheadID == '') {
            $this->raiseError('invheadID not passed');
        }
        if ($sequenceNo == '') {
            $this->raiseError('sequenceNo not passed');
        }
        $dbeJInvline = new DBEJInvline($this);
        $dsInvline = new DSForm($this);
        $dsInvline->copyColumnsFrom($dbeJInvline);
        $dsInvline->setUpdateModeInsert();
        $dsInvline->setValue(
            'invheadID',
            $invheadID
        );
        $dsInvline->setValue(
            'itemID',
            ''
        );
        $dsInvline->setValue(
            'sequenceNo',
            $sequenceNo
        );
        $dsInvline->setValue(
            'lineType',
            'I'
        );    // default item line
        $dsInvline->setValue(
            'qty',
            1
        );    // default 1
        $dsInvline->post();
    }

    function getCustomersWithoutInvoiceContact($dateToUse)
    {
        $buCustomer = new BUCustomer($this);

        $this->getUnprintedInvoices($dsInvhead);

        $list = false;

        while ($dsInvhead->fetchNext()) {

            if (!$buCustomer->getInvoiceContactsByCustomerID(
                $dsInvhead->getValue('customerID'),
                $dsContact
            )) {

                $buCustomer->getCustomerByID(
                    $dsInvhead->getValue('customerID'),
                    $dsCustomer
                );

                $list[] = $dsCustomer->getValue(DBECustomer::name);

            }

        }

        return $list;

    }


    function printDirectDebitInvoices($dateToUse,
                                      $privateKey
    )
    {
        if ($dateToUse == '') {
            $dateToUse = date('Y-m-d');    // use today if blank
        }

        $dbeInvhead = new DBEInvhead($this);

        $buCustomer = new BUCustomer($this);
        $dsInvhead = new DataSet($this);
        $this->getUnprintedInvoices(
            $dsInvhead,
            true
        );

        $senderEmail = CONFIG_SALES_EMAIL;
        $senderName = 'CNC Sales';
        $subject = 'Sales Invoice(s)';

        $invoiceNumbers = array();

        $bankData = [];
        while ($dsInvhead->fetchNext()) {
            $dbeInvhead->getRow($dsInvhead->getValue('invheadID'));

            $invoiceNumbers[] = $dsInvhead->getValue('invheadID');

            /*
            * generate PDF Invoice
            */
            $buPdfInvoice = new BUPDFInvoice(
                $this,
                $this
            );
            $buPdfInvoice->_dateToUse = $dateToUse;
            $pdfFileName = $buPdfInvoice->generateFile($dsInvhead);
            $fileSize = filesize($pdfFileName);
            /*
            Save PDF file into BLOB field on database
            */
            $dbeInvhead->setValue(
                'pdfFile',
                fread(
                    fopen(
                        $pdfFileName,
                        'rb'
                    ),
                    $fileSize
                )
            );

            $dbeInvhead->setValue(
                'datePrinted',
                $dateToUse
            );

            $dbeInvhead->updateRow();

            unset($buPdfInvoice);
            /*
            Attach invoice to email
            */
            $fileName = $dsInvhead->getValue('invheadID') . '.pdf';

            $buMail = new BUMail($this);

            $buMail->mime->addAttachment(
                $pdfFileName,
                'Application/pdf',
                $fileName
            );

            unlink($pdfFileName); // delete temp file


            $template = new Template (
                EMAIL_TEMPLATE_DIR,
                "remove"
            );

            $template->set_file(
                'page',
                'DirectDebitInvoiceEmail.html'
            );

            $dsContact = new DataSet($this);
            $buCustomer->getInvoiceContactsByCustomerID(
                $dsInvhead->getValue('customerID'),
                $dsContact
            );

            $dsCustomer = new DataSet($this);
            $buCustomer->getCustomerByID(
                $dsInvhead->getValue('customerID'),
                $dsCustomer
            );

            $dsSite = new DataSet($this);
            $buCustomer->getSiteByCustomerIDSiteNo(
                $dsInvhead->getValue('customerID'),
                $dsCustomer->getValue(DBECustomer::invoiceSiteNo),
                $dsSite
            );

            $paymentDate = $this->calculateDirectDebitPaymentDate(
                DateTime::createFromFormat(
                    'Y-m-d',
                    $dateToUse
                )
            )->format('d M Y');
            $invoiceValue = $this->getInvoiceValue($dsInvhead->getValue('invheadID'));

            if ($dsCustomer->getValue(DBECustomer::sortCode)) {

                openssl_private_decrypt(
                    base64_decode($dsCustomer->getValue(DBECustomer::sortCode)),
                    $unEncryptedSortCode,
                    $privateKey,
                    OPENSSL_PKCS1_OAEP_PADDING
                );
            }

            if ($dsCustomer->getValue(DBECustomer::accountNumber)) {

                openssl_private_decrypt(
                    base64_decode($dsCustomer->getValue(DBECustomer::accountNumber)),
                    $unEncryptedAccountNumber,
                    $privateKey,
                    OPENSSL_PKCS1_OAEP_PADDING
                );
            }


            $bankRow = [
                $unEncryptedSortCode,
                $dsCustomer->getValue(DBECustomer::accountName),
                $unEncryptedAccountNumber,
                $invoiceValue,
                $dsInvhead->getValue(DBEInvhead::invheadID),
                $dsInvhead->getValue(DBEInvhead::transactionType)
            ];

            $bankData[] = $bankRow;
            while ($dsContact->fetchNext()) {

                $contactName = $dsContact->getValue(DBEContact::firstName) . ' ' . $dsContact->getValue(
                        DBEContact::lastName
                    );
                $template->setVar(
                    [
                        "contactName"  => $contactName,
                        "companyName"  => $dsCustomer->getValue(DBECustomer::name),
                        "addressLine1" => $dsSite->getValue(DBESite::add1),
                        "town"         => $dsSite->getValue(DBESite::town),
                        "county"       => $dsSite->getValue(DBESite::county),
                        "postCode"     => $dsSite->getValue(DBESite::postcode),
                        "date"         => (new DateTime())->format('d M Y'),
                        "invoiceNo"    => $dsInvhead->getValue('invheadID'),
                        "paymentDate"  => $paymentDate,
                        "totalAmount"  => $invoiceValue
                    ]
                );

                $template->parse(
                    'output',
                    'page',
                    true
                );

                $buMail->mime->setHTMLBody($template->get_var('output'));
                $toEmail = $dsContact->getValue('email');
                $hdrs = array(
                    'From'    => $senderName . " <" . $senderEmail . ">",
                    'To'      => $toEmail,
                    'Subject' => $subject
                );

                $mime_params = array(
                    'text_encoding' => '7bit',
                    'text_charset'  => 'UTF-8',
                    'html_charset'  => 'UTF-8',
                    'head_charset'  => 'UTF-8'
                );
                $body = $buMail->mime->get($mime_params);
                $hdrs = $buMail->mime->headers($hdrs);

                $buMail->putInQueue(
                    $senderEmail,
                    $toEmail,
                    $hdrs,
                    $body
                );

            }
        }

        $this->buSageExport->generateSageSalesDataByInvoiceNumbers($invoiceNumbers);


        $senderEmail = CONFIG_SALES_EMAIL;
        $toEmail = CONFIG_SALES_EMAIL;
        $senderName = 'CNC Sales';
        $subject = 'Sage Import Files';

        $buMail = new BUMail($this);
        $hdrs = array(
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
        $body = $buMail->mime->get($mime_params);
        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );

        return count($invoiceNumbers);
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

    public function calculateDirectDebitPaymentDate(DateTime $date)
    {
        $lastYearBh = common_getUKBankHolidays($date->format('Y') - 1);
        $thisYearBh = common_getUKBankHolidays($date->format('Y'));
        $nextYearBh = common_getUKBankHolidays((int)$date->format('Y') + 1);

        $bankHolidays = array_merge(
            $lastYearBh,
            $thisYearBh,
            $nextYearBh
        );
        $dateCloned = clone $date;
        $counter = 0;
        while ($counter < 5) {
            $dateCloned->add(new \DateInterval('P1D'));

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

    /**
     * This method generates PDF invoices then emails them to customers
     *
     * One email per customer. Email body contains summary.
     *
     * @param string $dateToUse
     * @return int
     */
    function printUnprintedInvoices($dateToUse)
    {

        if ($dateToUse == '') {
            $dateToUse = date('Y-m-d');    // use today if blank
        }

        $dbeInvhead = new DBEInvhead($this);

        $buCustomer = new BUCustomer($this);

        $this->getUnprintedInvoices($dsInvhead);

        $invoiceCount = 0;

        $lastCustomerID = -1;

        $senderEmail = CONFIG_SALES_EMAIL;
        $senderName = 'CNC Sales';
        $subject = 'Sales Invoice(s)';

        $invoiceNumbers = array();

        while ($dsInvhead->fetchNext()) {

            $invoiceCount++;

            if ($dsInvhead->getValue('customerID') != $lastCustomerID) {

                if ($lastCustomerID != -1) {

                    // send email when customer changes
                    $template->setVar(
                        array(
                            'totalValue' => common_numberFormat($totalValue)
                        )
                    );

                    $template->parse(
                        'output',
                        'page',
                        true
                    );

                    $buMail->mime->setHTMLBody($template->get_var('output'));
                    $mime_params = array(
                        'text_encoding' => '7bit',
                        'text_charset'  => 'UTF-8',
                        'html_charset'  => 'UTF-8',
                        'head_charset'  => 'UTF-8'
                    );
                    $body = $buMail->mime->get($mime_params);
                    $hdrs = $buMail->mime->headers($hdrs);

                    $buMail->putInQueue(
                        $senderEmail,
                        $toEmail,
                        $hdrs,
                        $body
                    );

                }
                /*
                * Start new customer email
                */
                $buMail = new BUMail($this);

                $totalValue = 0;

                $buCustomer->getInvoiceContactsByCustomerID(
                    $dsInvhead->getValue('customerID'),
                    $dsContact
                );

                $invoiceContactEmailList = '';

                while ($dsContact->fetchNext()) {

                    if ($invoiceContactEmailList) {
                        $invoiceContactEmailList .= ',';
                    }

                    $invoiceContactEmailList .= $dsContact->getValue('email');

                }

                $toEmail = $invoiceContactEmailList;

                $hdrs = array(
                    'From'    => $senderName . " <" . $senderEmail . ">",
                    'To'      => $toEmail,
                    'Subject' => $subject
                );

                $template = new Template (
                    EMAIL_TEMPLATE_DIR,
                    "remove"
                );


                $template->set_file(
                    'page',
                    'SalesInvoiceEmail.inc.html'
                );

                $template->set_block(
                    'page',
                    'invoiceBlock',
                    'invoices'
                );


            }
            $lastCustomerID = $dsInvhead->getValue('customerID');

            $dbeInvhead->getRow($dsInvhead->getValue('invheadID'));

            $invoiceNumbers[] = $dsInvhead->getValue('invheadID');

            /*
            * generate PDF Invoice
            */
            $buPdfInvoice = new BUPDFInvoice(
                $this,
                $this
            );
            $buPdfInvoice->_dateToUse = $dateToUse;
            $pdfFileName = $buPdfInvoice->generateFile($dsInvhead);
            $fileSize = filesize($pdfFileName);
            /*
            Save PDF file into BLOB field on database
            */
            $dbeInvhead->setValue(
                'pdfFile',
                fread(
                    fopen(
                        $pdfFileName,
                        'rb'
                    ),
                    $fileSize
                )
            );

            $dbeInvhead->setValue(
                'datePrinted',
                $dateToUse
            );

            $dbeInvhead->updateRow();

            unset($buPdfInvoice);
            /*
            Attach invoice to email
            */
            $fileName = $dsInvhead->getValue('invheadID') . '.pdf';

            $buMail->mime->addAttachment(
                $pdfFileName,
                'Application/pdf',
                $fileName
            );

            unlink($pdfFileName); // delete temp file
            /*
            Add line to email body
            */
            $invoiceValue = $this->getInvoiceValue($dsInvhead->getValue('invheadID'));

            $totalValue += $invoiceValue;

            $template->setVar(
                array(
                    'invheadID'    => $dsInvhead->getValue('invheadID'),
                    'paymentTerms' => $dsInvhead->getValue('paymentTerms'),
                    'value'        => common_numberFormat($invoiceValue)
                )
            );

            $template->parse(
                'invoices',
                'invoiceBlock',
                true
            );

        }
        /**
         * finish last one
         *
         */
        if ($invoiceCount) {
            $template->setVar(
                array(
                    'totalValue' => common_numberFormat($totalValue)
                )
            );

            $template->parse(
                'output',
                'page',
                true
            );
            $buMail->mime->setHTMLBody($template->get_var('output'));
            $mime_params = array(
                'text_encoding' => '7bit',
                'text_charset'  => 'UTF-8',
                'html_charset'  => 'UTF-8',
                'head_charset'  => 'UTF-8'
            );
            $body = $buMail->mime->get($mime_params);
            $hdrs = $buMail->mime->headers($hdrs);

            $buMail->putInQueue(
                $senderEmail,
                $toEmail,
                $hdrs,
                $body
            );
        }

        $this->buSageExport->generateSageSalesDataByInvoiceNumbers($invoiceNumbers);

        $toEmail = CONFIG_SALES_MANAGER_EMAIL;

        $senderEmail = CONFIG_SALES_EMAIL;
        $senderName = 'CNC Sales';
        $subject = 'Sage Import Files';

        $buMail = new BUMail($this);
        $hdrs = array(
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
        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body = $buMail->mime->get($mime_params);
        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
        return $invoiceCount;

    }

    function trialPrintUnprintedInvoices($dateToUse,
                                         $directDebit = false
    )
    {

        if ($dateToUse == '') {
            $dateToUse = date('Y-m-d');    // use today if blank
        }

        $dbeInvhead = new DBEInvhead($this);

        $this->getUnprintedInvoices(
            $dsInvhead,
            $directDebit
        );

        $invoiceCount = $dsInvhead->rowCount;

        $buPdfInvoice = new BUPDFInvoice(
            $this,
            $this
        );

        $buPdfInvoice->_dateToUse = $dateToUse;

        return $buPdfInvoice->generateBatchFile($dsInvhead);

    }

    function printInvoices($startDate,
                           $endDate
    )
    {

        $dbeInvhead = new DBEInvhead($this);

        $this->getPrintedInvoicesByDateRange(
            $startDate,
            $endDate,
            $dsInvhead
        );

        $buPdfInvoice = new BUPDFInvoice(
            $this,
            $this
        );

        return $buPdfInvoice->generateBatchFile($dsInvhead);

    }

    function populate2010PdfField()
    {
        $dbeInvhead = new DBEInvhead($this);

        $this->dbeJInvhead->getPrintedRowsByDateRange(
            '2010-01-01',
            '2010-12-31'
        );

        $this->getData(
            $this->dbeJInvhead,
            $dsInvhead
        );

        while ($dsInvhead->fetchNext()) {

            $buPdfInvoice = new BUPDFInvoice(
                $this,
                $this
            );
            $buPdfInvoice->_dateToUse = $dateToUse;
            $pdfFileName = $buPdfInvoice->generateFile($dsInvhead);
            $fileSize = filesize($pdfFileName);
            /*
            Save PDF file into BLOB field on database
            */
            $dbeInvhead->getRow($dsInvhead->getValue('invheadID'));

            $dbeInvhead->setValue(
                'pdfFile',
                fread(
                    fopen(
                        $pdfFileName,
                        'rb'
                    ),
                    $fileSize
                )
            );

            $dbeInvhead->updateRow();

            unset($buPdfInvoice);
        }
    }

    function regeneratePdfInvoice($invoiceID)
    {
        $dbeInvhead = new DBEInvhead($this);
        $dbeInvhead->getRow($invoiceID);
        $this->dbeJInvhead->setValue(
            'invheadID',
            $invoiceID
        );
        $this->dbeJInvhead->getRow();

        $buPdfInvoice = new BUPDFInvoice(
            $this,
            $this
        );
        $buPdfInvoice->_dateToUse = $dbeInvhead->getValue('datePrinted');

        $pdfFileName = $buPdfInvoice->generateFile($this->dbeJInvhead);
        $fileSize = filesize($pdfFileName);

        $fileString = fread(
            fopen(
                $pdfFileName,
                'rb'
            ),
            $fileSize
        );
        $dbeInvhead->setValue(
            'pdfFile',
            $fileString
        );

        $dbeInvhead->updateRow();

        unset($buPdfInvoice);
        unlink($pdfFileName); // delete temp file


        return $fileString;

    }
}// End of class
?>