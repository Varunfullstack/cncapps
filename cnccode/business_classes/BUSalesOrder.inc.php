<?php /**
 * Sales Order business class
 * This new one uses data classes with new lower-case column names
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEJOrdhead.inc.php");
require_once($cfg["path_dbe"] . "/DBEJOrdline.inc.php");
require_once($cfg["path_dbe"] . "/DBEOrdhead.inc.php");
require_once($cfg["path_dbe"] . "/DBEOrdline.inc.php");
require_once($cfg["path_dbe"] . "/DBEOrdlinePO.inc.php"); // for Purchase orders
require_once($cfg["path_dbe"] . "/DBEQuotation.inc.php");
require_once($cfg["path_dbe"] . "/DBEJQuotation.inc.php");
require_once($cfg["path_dbe"] . "/DBEVat.inc.php");
require_once($cfg["path_bu"] . "/BUCustomer.inc.php");
require_once($cfg["path_bu"] . "/BURenewal.inc.php");
require_once($cfg["path_bu"] . "/BURenQuotation.inc.php");
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUItem.inc.php');
require_once($cfg['path_bu'] . '/BUInvoice.inc.php');
require_once($cfg["path_dbe"] . "/DBEPorhead.inc.php");

class BUSalesOrder extends Business
{

    const DBEOrdheadCustomerName = "customerName";
    /** @var DBEQuotation */
    public $dbeQuotation;
    /** @var DBEJQuotation */
    public $dbeJQuotation;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeQuotation = new DBEQuotation($this);
        $this->dbeJQuotation = new DBEJQuotation($this);
    }

    /**
     * Get ordhead rows whose names match the search string or, if the string is numeric, try to select by customerID
     * @parameter String $nameSearchString String to match against or numeric customerID
     * @parameter DataSet &$dsResults results
     * @param $customerID
     * @param $ordheadID
     * @param $orderType
     * @param $custPORef
     * @param $lineText
     * @param $fromDate
     * @param $toDate
     * @param $userID
     * @param $dsResults
     * @return bool : One or more rows
     * @access public
     */
    function search(
        $customerID,
        $ordheadID,
        $orderType,
        $custPORef,
        $lineText,
        $fromDate,
        $toDate,
        $userID,
        &$dsResults
    )
    {
        $this->setMethodName('search');
        $dbeJOrdhead = new DBEJOrdhead($this);
        if ($ordheadID) {
            return ($this->getDatasetByPK(
                $ordheadID,
                $dbeJOrdhead,
                $dsResults
            ));
        } else {
            $dbeJOrdhead->getRowsBySearchCriteria(
                $customerID,
                trim($orderType),
                trim($custPORef),
                trim($lineText),
                trim($fromDate),
                trim($toDate),
                $userID
            );
            $dbeJOrdhead->initialise();
            $dsResults = $dbeJOrdhead;
        }
        return true;
    }

    /**
     * @param $ordheadID
     * @param DataSet $dsOrdhead
     * @param DataSet $dsJOrdline
     * @param $dsDeliveryContact
     * @return bool
     */
    function getOrderWithCustomerName($ordheadID,
                                      &$dsOrdhead,
                                      &$dsJOrdline,
                                      &$dsDeliveryContact
    )
    {
        $this->setMethodName('getOrderWithCustomerName');
        if (!$ordheadID) {
            $this->raiseError('order ID not passed');
            return false;
        }
        $dbeJOrdline = new DBEJOrdline($this);
        $dbeJOrdhead = new DBEJOrdhead($this);
        $ret = ($this->getDatasetByPK(
            $ordheadID,
            $dbeJOrdhead,
            $dsOrdhead
        ));
        if (!$ret) {
            $this->raiseError('order not found');
        }
        $dbeJOrdline->setValue(
            DBEJOrdline::ordheadID,
            $ordheadID
        );
        $dbeJOrdline->getRowsByColumn(
            DBEJOrdline::ordheadID
        );
        $this->getData(
            $dbeJOrdline,
            $dsJOrdline
        );
        $dsJOrdline->sortAscending(DBEJOrdline::sequenceNo);
        $buCustomer = new BUCustomer($this);
        $buCustomer->getContactByID(
            $dsOrdhead->getValue(DBEOrdhead::delContactID),
            $dsDeliveryContact
        );
        return true;
    }

    function getOrderByOrdheadID($ordheadID,
                                 &$dsOrdhead,
                                 &$dsOrdline
    )
    {
        $this->setMethodName('getOrderByOrdheadID');
        $ret = FALSE;
        if (!$ordheadID) {
            $this->raiseError('order ID not passed');
        } else {
            $dbeOrdline = new DBEOrdline($this);
            $dbeOrdhead = new DBEOrdhead($this);
            $ret = ($this->getDatasetByPK(
                $ordheadID,
                $dbeOrdhead,
                $dsOrdhead
            ));
            if (!$ret) {
                $this->raiseError('order not found');
            }

            $dbeOrdline->setValue(
                DBEOrdline::ordheadID,
                $ordheadID
            );
            $dbeOrdline->getRowsByColumn(
                DBEOrdline::ordheadID,
                DBEOrdline::sequenceNo
            );
            $this->getData(
                $dbeOrdline,
                $dsOrdline
            );
        }

        return $ret;
    }

    function getOrdheadByID($ordheadID,
                            &$dsOrdhead
    )
    {
        $this->setMethodName('getOrdheadByID');
        if (!$ordheadID) {
            $this->raiseError('order ID not passed');
            return false;
        } else {
            $dbeJOrdhead = new DBEJOrdhead($this);
            return ($this->getDatasetByPK(
                $ordheadID,
                $dbeJOrdhead,
                $dsOrdhead
            ));
        }
    }

    function getOrdlineByIDSeqNo($ordheadID,
                                 $sequenceNo,
                                 &$dsOrdline
    )
    {
        $this->setMethodName('getOrdlineByIDSeqNo');

        if (!$ordheadID) {
            $this->raiseError('order ID not passed');
        }
        if (!$sequenceNo) {
            $this->raiseError('sequenceNo not passed');
        }
        $dbeJOrdline = new DBEJOrdline($this);
        $dbeJOrdline->setValue(
            DBEOrdline::ordheadID,
            $ordheadID
        );
        $dbeJOrdline->setValue(
            DBEOrdline::sequenceNo,
            $sequenceNo
        );

        $dbeJOrdline->getRowByOrdheadIDSequenceNo();
        return ($this->getData(
            $dbeJOrdline,
            $dsOrdline
        ));
    }

    /**
     * Send email to order delivery contact from cnc user as PDF attachment
     * @parameter Integer $ordheadID Order ID
     * @parameter Integer $userID CNC User
     * @param DataSet $dsData
     * @return bool : Successfully sent
     * @access public
     */
    function insertQuotation(&$dsData)
    {
        $this->setMethodName('insertQuotation');
        $this->updateDataAccessObject(
            $dsData,
            $this->dbeQuotation
        );
        return $dsData->getValue(DBEQuotation::quotationID);
    }

    function deleteQuotationDoc($quotationID)
    {
        $this->setMethodName('deleteQuotationDoc');
        if (!$quotationID) {
            $this->raiseError('quotationID not passed');
        }
        $this->dbeQuotation->setPKValue($quotationID);
        return ($this->dbeQuotation->deleteRow());
    }

    /**
     * Get next quotation version to use for given sales order no
     * @parameter Integer $ordheadID Order ID
     * @param $ordheadID
     * @return integer version no
     * @access public
     */
    function getNextQuoteVersion($ordheadID)
    {
        $this->setMethodName('getNextQuoteVersion');
        $this->dbeQuotation->setValue(
            DBEQuotation::ordheadID,
            $ordheadID
        );
        return ($this->dbeQuotation->getNextVersionNo());
    }

    /**
     * Get sent quotations for order
     * @parameter Integer $ordheadID Order ID
     * @param $ordheadID
     * @param $dsResults
     * @return bool Quotations
     * @access public
     */
    function getQuotationsByOrdheadID($ordheadID,
                                      &$dsResults
    )
    {
        $this->setMethodName('getQuotationsByOrdheadID');

        if (!$ordheadID) {
            $this->raiseError('order ID not passed');
            return false;
        } else {
            $this->dbeJQuotation->setValue(
                DBEQuotation::ordheadID,
                $ordheadID
            );
            $this->dbeJQuotation->getRowsByOrdheadID();
            return ($this->getData(
                $this->dbeJQuotation,
                $dsResults
            ));
        }

    }

    /**
     * Get all users
     * @parameter DataSet &$dsResults results
     * @param $dsResults
     * @return bool : Success
     * @access public
     */
    function getAllUsers(&$dsResults)
    {
        $this->setMethodName('getAllUsers');
        $dbeUser = new DBEUser($this);
        $dbeUser->getRows();
        return ($this->getData(
            $dbeUser,
            $dsResults
        ));
    }

    /**
     * Get one users
     * @parameter integer $userID user
     * @parameter DataSet &$dsResults results
     * @param $userID
     * @param $dsResults
     * @return bool : Success
     * @access public
     */
    function getUserByID($userID,
                         &$dsResults
    )
    {
        $this->setMethodName('getUserByID');
        $dbeUser = new DBEUser($this);
        return ($this->getDatasetByPK(
            $userID,
            $dbeUser,
            $dsResults
        ));
    }

    /**
     * Get one quote
     * @parameter integer $quoteID quoteID
     * @parameter DataSet &$dsResults results
     * @param $quoteID
     * @param $dsResults
     * @return bool : Success
     * @access public
     */
    function getQuoteByID($quoteID,
                          &$dsResults
    )
    {
        $this->setMethodName('getQuoteByID');
        return ($this->getDatasetByPK(
            $quoteID,
            $this->dbeQuotation,
            $dsResults
        ));
    }

    /**
     * Delete quote and all associated quote documents
     * @parameter integer $quoteID quoteID
     * @param $ordheadID
     * @return bool : Success
     * @access public
     */
    function deleteOrder($ordheadID)
    {
        $this->setMethodName('deleteOrder');
        // Validation
        if (!$ordheadID) {
            $this->raiseError('ordheadID not passed');
        }

        $dsOrdhead = new DataSet($this);
        if (!$this->getOrdheadByID(
            $ordheadID,
            $dsOrdhead
        )) {
            $this->raiseError('Order not found');
            exit;
        }

        $orderType = $dsOrdhead->getValue(DBEOrdhead::type);

        if (
            ($orderType <> 'I') &
            ($orderType <> 'Q')
        ) {
            $this->raiseError('Must be quote or initial order');
            exit;
        }

        if (
            ($orderType <> 'Q') &&
            $this->countPurchaseOrders($dsOrdhead->getValue(DBEOrdhead::ordheadID)) > 0
        ) {
            $this->raiseError('Cannot delete order because purchase orders exist');
            exit;
        }

        if ($orderType <> 'Q') {
            $buInvoice = new BUInvoice($this);
            if ($buInvoice->countInvoicesByOrdheadID($dsOrdhead->getValue(DBEOrdhead::ordheadID)) > 0) {
                $this->raiseError('Cannot delete order because invoices exist');
                exit;
            }
        }

        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdline = new DBEOrdline($this);
        $dbeOrdhead->setPKValue($ordheadID);
        $dbeOrdhead->deleteRow();
        $dbeOrdline->setValue(
            DBEOrdline::ordheadID,
            $ordheadID
        );
        $dbeOrdline->deleteRowsByOrderID();

        // delete any quote rows and associated documents
        $this->dbeQuotation->setValue(
            DBEQuotation::ordheadID,
            $ordheadID
        );
        $this->dbeQuotation->getRowsByColumn(DBEQuotation::ordheadID);
        $dsQuotation = new DataSet($this);
        $this->getData(
            $this->dbeQuotation,
            $dsQuotation
        );
        // delete quote docs from DB and quote directory
        while ($dsQuotation->fetchNext()) {
            $quoteFile =
                'quotes/' .
                $dsQuotation->getValue(DBEQuotation::ordheadID) . '_' . $dsQuotation->getValue(
                    DBEQuotation::versionNo
                ) . '.' .
                $dsQuotation->getValue(DBEQuotation::fileExtension);
            $this->deleteQuotationDoc($dsQuotation->getValue(DBEQuotation::quotationID));
            unlink($quoteFile);
        }
        return TRUE;
    }

    /**
     * Initialise fields for new order
     * @parameter integer $customerID
     * @param DataSet $dsOrdhead
     * @param $dsOrdline
     * @param $dsCustomer
     * @param bool $directDebit
     * @param string $transactionType
     * @return void : Success
     * @access public
     */
    function initialiseOrder(&$dsOrdhead,
                             &$dsOrdline,
                             &$dsCustomer,
                             $directDebit = false,
                             $transactionType = "01"
    )
    {
        // simply call with type set to I!
        $this->initialiseQuote(
            $dsOrdhead,
            $dsOrdline,
            $dsCustomer,
            'I',
            $directDebit,
            $transactionType
        );
        $buCustomer = new BUCustomer($this);
        $buCustomer->setProspectFlagOff($dsOrdhead->getValue(DBEOrdhead::customerID));
    }

    /**
     * Initialise fields for new quote
     * @parameter integer $customerID
     * @param DataSet $dsOrdhead
     * @param DataSet $dsOrdline
     * @param DataSet $dsCustomer
     * @param string $type
     * @param bool $directDebit
     * @param string $transactionType
     * @return void : Success
     * @access public
     */
    function initialiseQuote(&$dsOrdhead,
                             &$dsOrdline,
                             &$dsCustomer,
                             $type = 'Q',
                             $directDebit = false,
                             $transactionType = "01"
    )
    {
        $this->setMethodName('initialiseQuote');
        $dsOrdhead = new DataSet($this);
        $dbeOrdhead = new DBEOrdhead($this);
        $dsOrdhead->copyColumnsFrom($dbeOrdhead);
        $dsOrdhead->setUpdateModeInsert();
        $dsOrdhead->addColumn(
            self::DBEOrdheadCustomerName,
            DA_STRING,
            DA_NOT_NULL
        );

        $dsOrdhead->setValue(
            DBEOrdhead::transactionType,
            $transactionType
        );

        $dsOrdhead->setValue(
            self::DBEOrdheadCustomerName,
            $dsCustomer->getValue(DBECustomer::name)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::customerID,
            $dsCustomer->getValue(DBECustomer::customerID)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::type,
            $type
        );
        $buHeader = new BUHeader($this);
        $dsHeader = new DataSet($this);
        $buHeader->getHeader($dsHeader);
        $dsHeader->fetchNext();
        $vatCode = $dsHeader->getValue(DBEHeader::stdVATCode);
        $dsOrdhead->setValue(
            DBEOrdhead::vatCode,
            $vatCode
        );
        $dbeVat = new DBEVat($this);
        $dbeVat->getRow();
        $vatRate = $dbeVat->getValue((integer)$vatCode[1]); // get 2nd part of code and use as column no
        $dsOrdhead->setValue(
            DBEOrdhead::vatRate,
            $vatRate
        );
        $dsOrdhead->setValue(
            DBEOrdhead::date,
            date('Y-m-d')
        );

        if ($type == 'Q') {
            $dsOrdhead->setValue(
                DBEOrdhead::quotationCreateDate,
                date('Y-m-d')
            );
        }
        // If the order customer is an internal stock location then we set pay method to no invoice
        if (
            ($dsOrdhead->getValue(DBEOrdhead::customerID) == CONFIG_SALES_STOCK_CUSTOMERID) OR
            ($dsOrdhead->getValue(DBEOrdhead::customerID) == CONFIG_MAINT_STOCK_CUSTOMERID)
        ) {
            $dsOrdhead->setValue(
                DBEOrdhead::paymentTermsID,
                CONFIG_PAYMENT_TERMS_NO_INVOICE
            );    // switch to paymentTermsID
        } else {

            if ($directDebit) {
                echo '<div>direct debit payment term</div>';
                $dsOrdhead->setValue(
                    DBEOrdhead::paymentTermsID,
                    CONFIG_PAYMENT_TERMS_DIRECT_DEBIT
                );    // switch to paymentTermsID
            } else {

                $dsOrdhead->setValue(
                    DBEOrdhead::paymentTermsID,
                    CONFIG_PAYMENT_TERMS_30_DAYS
                );    // switch to paymentTermsID
            }

        }
        $dsOrdhead->setValue(
            DBEOrdhead::partInvoice,
            'Y'
        );
        $dsOrdhead->setValue(
            DBEOrdhead::addItem,
            'Y'
        );
        $dsOrdhead->setValue(
            DBEOrdhead::requestedDate,
            null
        );
        $dsOrdhead->setValue(
            DBEOrdhead::promisedDate,
            null
        );
        $dsOrdhead->setValue(
            DBEOrdhead::expectedDate,
            null
        );

        $dsOrdhead->setValue(
            DBEOrdhead::directDebitFlag,
            $directDebit ? 'Y' : 'N'
        );

        $dsOrdhead->setValue(
            DBEOrdhead::invSiteNo,
            $dsCustomer->getValue(DBECustomer::invoiceSiteNo)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::updatedTime,
            date('Y-m-d H:i:s')
        );
        $this->setInvoiceSiteAndContact(
            $dsCustomer->getValue(DBECustomer::customerID),
            $dsCustomer->getValue(DBECustomer::invoiceSiteNo),
            $dsOrdhead
        );
        $dsOrdhead->setValue(
            DBEOrdhead::delSiteNo,
            $dsCustomer->getValue(DBECustomer::deliverSiteNo)
        );
        $this->setDeliverySiteAndContact(
            $dsCustomer->getValue(DBECustomer::customerID),
            $dsCustomer->getValue(DBECustomer::deliverSiteNo),
            $dsOrdhead
        );
        $dsOrdhead->post();

        $this->updateDataAccessObject(
            $dsOrdhead,
            $dbeOrdhead
        );
        $dsOrdline = new DataSet($this);
        $dbeOrdline = new DBEOrdline($this);
        $dsOrdline->copyColumnsFrom($dbeOrdline);
    }

    /**
     * @param $customerID
     * @param $siteNo
     * @param DataSet $dsOrdhead
     */
    function setInvoiceSiteAndContact($customerID,
                                      $siteNo,
                                      &$dsOrdhead
    )
    {
        $this->setMethodName('setInvoiceSiteAndContact');
        $buCustomer = new BUCustomer($this);

        $dsSite = new DataSet($this);
        $buCustomer->getSiteByCustomerIDSiteNo(
            $customerID,
            $siteNo,
            $dsSite
        );
        $dsOrdhead->setValue(
            DBEOrdhead::invSiteNo,
            $siteNo
        );
        $dsOrdhead->setValue(
            DBEOrdhead::invAdd1,
            $dsSite->getValue(DBESite::add1)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::invAdd2,
            $dsSite->getValue(DBESite::add2)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::invAdd3,
            $dsSite->getValue(DBESite::add3)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::invTown,
            $dsSite->getValue(DBESite::town)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::invCounty,
            $dsSite->getValue(DBESite::county)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::invPostcode,
            $dsSite->getValue(DBESite::postcode)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::invSitePhone,
            $dsSite->getValue(DBESite::phone)
        );
        $dsContact = new DataSet($this);

        $buCustomer->getContactByID(
            $dsSite->getValue(DBESite::invoiceContactID),
            $dsContact
        );
        $dsOrdhead->setValue(
            DBEOrdhead::invContactID,
            $dsContact->getValue(DBEContact::contactID)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::invContactName,
            $dsContact->getValue(DBEContact::lastName)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::invContactSalutation,
            $dsContact->getValue(DBEContact::firstName)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::invContactPhone,
            $dsContact->getValue(DBEContact::phone)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::invContactFax,
            $dsContact->getValue(DBEContact::fax)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::invContactEmail,
            $dsContact->getValue(DBEContact::email)
        );
    }

    /**
     * @param $customerID
     * @param $siteNo
     * @param DataSet $dsOrdhead
     */
    function setDeliverySiteAndContact($customerID,
                                       $siteNo,
                                       &$dsOrdhead
    )
    {
        $this->setMethodName('setDeliverSiteAndContact');
        $buCustomer = new BUCustomer($this);

        $dsSite = new DataSet($this);
        $buCustomer->getSiteByCustomerIDSiteNo(
            $customerID,
            $siteNo,
            $dsSite
        );
        $dsOrdhead->setValue(
            DBEOrdhead::delSiteNo,
            $siteNo
        );
        $dsOrdhead->setValue(
            DBEOrdhead::delAdd1,
            $dsSite->getValue(DBESite::add1)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::delAdd2,
            $dsSite->getValue(DBESite::add2)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::delAdd3,
            $dsSite->getValue(DBESite::add3)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::delTown,
            $dsSite->getValue(DBESite::town)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::delCounty,
            $dsSite->getValue(DBESite::county)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::delPostcode,
            $dsSite->getValue(DBESite::postcode)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::delSitePhone,
            $dsSite->getValue(DBESite::phone)
        );
        $dsContact = new DataSet($this);

        $buCustomer->getContactByID(
            $dsSite->getValue(DBESite::deliverContactID),
            $dsContact
        );
        $dsOrdhead->setValue(
            DBEOrdhead::delContactID,
            $dsContact->getValue(DBEContact::contactID)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::delContactName,
            $dsContact->getValue(DBEContact::lastName)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::delContactSalutation,
            $dsContact->getValue(DBEContact::firstName)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::delContactPhone,
            $dsContact->getValue(DBEContact::phone)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::delContactFax,
            $dsContact->getValue(DBEContact::fax)
        );
        $dsOrdhead->setValue(
            DBEOrdhead::delContactEmail,
            $dsContact->getValue(DBEContact::email)
        );
    }

    function updateInvoiceAddress($ordheadID,
                                  $siteNo
    )
    {
        $this->setMethodName('updateInvoiceAddress');
        if (!$ordheadID) {
            $this->raiseError('ordheadID not passed');
        }
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setPKValue($ordheadID);
        $dbeOrdhead->getRow();
        $buCustomer = new BUCustomer($this);

        $dsSite = new DataSet($this);
        $buCustomer->getSiteByCustomerIDSiteNo(
            $dbeOrdhead->getValue(DBEOrdhead::customerID),
            $siteNo,
            $dsSite
        );
        $dbeOrdhead->setUpdateModeUpdate();
        $dbeOrdhead->setValue(
            DBEOrdhead::invSiteNo,
            $siteNo
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::invAdd1,
            $dsSite->getValue(DBESite::add1)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::invAdd2,
            $dsSite->getValue(DBESite::add2)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::invAdd3,
            $dsSite->getValue(DBESite::add3)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::invTown,
            $dsSite->getValue(DBESite::town)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::invCounty,
            $dsSite->getValue(DBESite::county)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::invPostcode,
            $dsSite->getValue(DBESite::postcode)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::invSitePhone,
            $dsSite->getValue(DBESite::phone)
        );

        $dsContact = new DataSet($this);

        $buCustomer->getContactByID(
            $dsSite->getValue(DBESite::invoiceContactID),
            $dsContact
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::invContactID,
            $dsContact->getValue(DBEContact::contactID)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::invContactName,
            $dsContact->getValue(DBEContact::lastName)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::invContactSalutation,
            $dsContact->getValue(DBEContact::firstName)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::invContactPhone,
            $dsContact->getValue(DBEContact::phone)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::invContactFax,
            $dsContact->getValue(DBEContact::fax)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::invContactEmail,
            $dsContact->getValue(DBEContact::email)
        );
        $dbeOrdhead->post();
        return TRUE;
    }

    function updateDeliveryAddress($ordheadID,
                                   $siteNo
    )
    {
        $this->setMethodName('updateDeliveryAddress');
        if (!$ordheadID) {
            $this->raiseError('ordheadID not passed');
        }
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setPKValue($ordheadID);
        $dbeOrdhead->getRow();
        $buCustomer = new BUCustomer($this);

        $dsSite = new DataSet($this);

        $buCustomer->getSiteByCustomerIDSiteNo(
            $dbeOrdhead->getValue(DBEOrdhead::customerID),
            $siteNo,
            $dsSite
        );
        $dbeOrdhead->setUpdateModeUpdate();
        $dbeOrdhead->setValue(
            DBEOrdhead::delSiteNo,
            $siteNo
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::delAdd1,
            $dsSite->getValue(DBESite::add1)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::delAdd2,
            $dsSite->getValue(DBESite::add2)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::delAdd3,
            $dsSite->getValue(DBESite::add3)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::delTown,
            $dsSite->getValue(DBESite::town)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::delCounty,
            $dsSite->getValue(DBESite::county)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::delPostcode,
            $dsSite->getValue(DBESite::postcode)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::delSitePhone,
            $dsSite->getValue(DBESite::phone)
        );
        $dsContact = new DataSet($this);
        $buCustomer->getContactByID(
            $dsSite->getValue(DBESite::deliverContactID),
            $dsContact
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::delContactID,
            $dsContact->getValue(DBEContact::contactID)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::delContactName,
            $dsContact->getValue(DBEContact::lastName)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::delContactSalutation,
            $dsContact->getValue(DBEContact::firstName)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::delContactPhone,
            $dsContact->getValue(DBEContact::phone)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::delContactFax,
            $dsContact->getValue(DBEContact::fax)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::delContactEmail,
            $dsContact->getValue(DBEContact::email)
        );
        $dbeOrdhead->post();
        return TRUE;
    }

    function updateInvoiceContact($ordheadID,
                                  $contactID
    )
    {
        $this->setMethodName('updateInvoiceContact');
        if (!$ordheadID) {
            $this->raiseError('ordheadID not passed');
        }
        if (!$contactID) {
            $this->raiseError('contactID not passed');
        }
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setPKValue($ordheadID);
        $dbeOrdhead->getRow();
        $dbeOrdhead->setUpdateModeUpdate();
        $buCustomer = new BUCustomer($this);

        $dsContact = new DataSet($this);
        $buCustomer->getContactByID(
            $contactID,
            $dsContact
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::invContactID,
            $dsContact->getValue(DBEContact::contactID)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::invContactName,
            $dsContact->getValue(DBEContact::lastName)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::invContactSalutation,
            $dsContact->getValue(DBEContact::firstName)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::invContactPhone,
            $dsContact->getValue(DBEContact::phone)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::invContactFax,
            $dsContact->getValue(DBEContact::fax)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::invContactEmail,
            $dsContact->getValue(DBEContact::email)
        );
        $dbeOrdhead->post();
        return TRUE;
    }

    function updateDeliveryContact($ordheadID,
                                   $contactID
    )
    {
        $this->setMethodName('updateDeliveryContact');
        if (!$ordheadID) {
            $this->raiseError('ordheadID not passed');
        }
        if (!$contactID) {
            $this->raiseError('contactID not passed');
        }
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setPKValue($ordheadID);
        $dbeOrdhead->getRow();
        $dbeOrdhead->setUpdateModeUpdate();
        $buCustomer = new BUCustomer($this);
        $dsContact = new DataSet($this);
        $buCustomer->getContactByID(
            $contactID,
            $dsContact
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::delContactID,
            $dsContact->getValue(DBEContact::contactID)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::delContactName,
            $dsContact->getValue(DBEContact::lastName)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::delContactSalutation,
            $dsContact->getValue(DBEContact::firstName)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::delContactPhone,
            $dsContact->getValue(DBEContact::phone)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::delContactFax,
            $dsContact->getValue(DBEContact::fax)
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::delContactEmail,
            $dsContact->getValue(DBEContact::email)
        );
        $dbeOrdhead->post();
        return TRUE;
    }

    /**
     * @param DataSet $dsOrdline
     * @param string $action
     */
    function updateOrderLine(&$dsOrdline,
                             $action = "U"
    )
    {
        $this->setMethodName('updateOrderLine');
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setPKValue($dsOrdline->getValue(DBEOrdline::ordheadID));
        if (!$dbeOrdhead->getRow()) {
            $this->raiseError('order header not found');
        }
        // ordline fields
        $dbeOrdline = new DBEOrdline($this);

        $dbeOrdline->setValue(
            DBEOrdline::lineType,
            $dsOrdline->getValue(DBEOrdline::lineType)
        );
        $dbeOrdline->setValue(
            DBEOrdline::qtyOrdered,
            $dsOrdline->getValue(DBEOrdline::qtyOrdered)
        );
        $dbeOrdline->setValue(
            DBEOrdline::curUnitSale,
            $dsOrdline->getValue(DBEOrdline::curUnitSale)
        );
        $dbeOrdline->setValue(
            DBEOrdline::curUnitCost,
            $dsOrdline->getValue(DBEOrdline::curUnitCost)
        );
        $dbeOrdline->setValue(
            DBEOrdline::supplierID,
            $dsOrdline->getValue(DBEOrdline::supplierID)
        );
        $dbeOrdline->setValue(
            DBEOrdline::renewalCustomerItemID,
            $dsOrdline->getValue(DBEOrdline::renewalCustomerItemID)
        );
        $dbeOrdline->setValue(
            DBEOrdline::ordheadID,
            $dsOrdline->getValue(DBEOrdline::ordheadID)
        );
        $dbeOrdline->setValue(
            DBEOrdline::sequenceNo,
            $dsOrdline->getValue(DBEOrdline::sequenceNo)
        );
        $dbeOrdline->setValue(
            DBEOrdline::customerID,
            $dbeOrdhead->getValue(DBEOrdhead::customerID)
        );
        $dbeOrdline->setValue(
            DBEOrdline::qtyDespatched,
            0
        );
        $dbeOrdline->setValue(
            DBEOrdline::qtyLastDespatched,
            0
        );
        if ($dsOrdline->getValue(DBEOrdline::lineType) == 'I') {
            $dbeOrdline->setValue(
                DBEOrdline::itemID,
                $dsOrdline->getValue(DBEOrdline::itemID)
            );
            $buItem = new BUItem($this);
            $dsItem = new DataSet($this);
            if ($buItem->getItemByID(
                $dsOrdline->getValue(DBEOrdline::itemID),
                $dsItem
            )) {
                $dbeOrdline->setValue(
                    DBEOrdline::stockcat,
                    $dsItem->getValue(DBEItem::stockcat)
                );
            }
            $dbeOrdline->setValue(
                DBEOrdline::description,
                $dsOrdline->getValue(DBEOrdline::description)
            );
            $dbeOrdline->setValue(
                DBEOrdline::curTotalCost,
                $dsOrdline->getValue(DBEOrdline::curUnitCost) * $dsOrdline->getValue(DBEOrdline::qtyOrdered)
            );
            $dbeOrdline->setValue(
                DBEOrdline::curTotalSale,
                $dsOrdline->getValue(DBEOrdline::curUnitSale) * $dsOrdline->getValue(DBEOrdline::qtyOrdered)
            );
        } else {
            $dbeOrdline->setValue(
                DBEOrdline::qtyOrdered,
                0
            );
            $dbeOrdline->setValue(
                DBEOrdline::curUnitCost,
                0
            );
            $dbeOrdline->setValue(
                DBEOrdline::curUnitSale,
                0
            );
            $dbeOrdline->setValue(
                DBEOrdline::curTotalCost,
                0
            );
            $dbeOrdline->setValue(
                DBEOrdline::curTotalSale,
                0
            );
            $dbeOrdline->setValue(
                DBEOrdline::description,
                $dsOrdline->getValue(DBEOrdline::description)
            );
        }
        if ($action == "U") {
            $dbeOrdline->updateRow();
        } else {
            $dbeOrdline->insertRow();
        }
        $dbeOrdhead->setUpdatedTime();
    }


    /**
     * @param $ordheadID
     * @param $sequenceNo
     * @param $dsOrdline
     */
    function initialiseNewOrdline($ordheadID,
                                  $sequenceNo,
                                  &$dsOrdline
    )
    {
        $this->setMethodName('initialiseNewOrdline');
        if (!$ordheadID) {
            $this->raiseError('ordheadID not passed');
        }
        if (!$sequenceNo) {
            $this->raiseError('sequenceNo not passed');
        }
        $dbeJOrdline = new DBEJOrdline($this);
        $dsOrdline = new DataSet($this);
        $dsOrdline->copyColumnsFrom($dbeJOrdline);
        $dsOrdline->setUpdateModeInsert();
        $dsOrdline->setValue(
            DBEOrdline::ordheadID,
            $ordheadID
        );
        $dsOrdline->setValue(
            DBEOrdline::itemID,
            null
        );
        $dsOrdline->setValue(
            DBEOrdline::supplierID,
            null
        );
        $dsOrdline->setValue(
            DBEOrdline::sequenceNo,
            $sequenceNo
        );
        $dsOrdline->setValue(
            DBEOrdline::lineType,
            'I'
        );    // default item line
        $dsOrdline->setValue(
            DBEOrdline::qtyOrdered,
            1
        );    // default 1
        $dsOrdline->post();
    }

    /**
     * @param DataSet $dsOrdline
     */
    function insertNewOrderLine(&$dsOrdline)
    {
        $this->setMethodName('insertNewOrdline');
//count rows
        $dsOrdline->fetchNext();
        $dbeOrdline = new DBEOrdline($this);
        $dbeOrdline->setValue(
            DBEOrdline::ordheadID,
            $dsOrdline->getValue(DBEOrdline::ordheadID)
        );
        if ($dbeOrdline->countRowsByColumn(DBEOrdline::ordheadID) > 0) {
            // shuffle down existing rows before inserting new one
            $dbeOrdline->setValue(
                DBEOrdline::ordheadID,
                $dsOrdline->getValue(DBEOrdline::ordheadID)
            );
            $dbeOrdline->setValue(
                DBEOrdline::sequenceNo,
                $dsOrdline->getValue(DBEOrdline::sequenceNo)
            );
            $dbeOrdline->shuffleRowsDown();
        }
        $this->updateOrderLine(
            $dsOrdline,
            "I"
        );
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setPKValue($dsOrdline->getValue(DBEOrdline::ordheadID));
        $dbeOrdhead->setUpdatedTime();
    }

    function moveOrderLineUp($ordheadID,
                             $sequenceNo
    )
    {
        if (!$ordheadID) {
            $this->raiseError('ordheadID not passed');
        }
        if (!$sequenceNo) {
            $this->raiseError('sequenceNo not passed');
        }
        if ($sequenceNo == 1) {
            return;
        }
        $dbeOrdline = new DBEOrdline($this);
        $dbeOrdline->setValue(
            DBEOrdline::ordheadID,
            $ordheadID
        );
        $dbeOrdline->setValue(
            DBEOrdline::sequenceNo,
            $sequenceNo
        );
        $dbeOrdline->moveRow('UP');
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setPKValue($ordheadID);
        $dbeOrdhead->setUpdatedTime();
    }

    function moveOrderLineDown($ordheadID,
                               $sequenceNo
    )
    {
        if (!$ordheadID) {
            $this->raiseError('ordheadID not passed');
        }
        if (!$sequenceNo) {
            $this->raiseError('sequenceNo not passed');
        }
        $dbeOrdline = new DBEOrdline($this);
        $dbeOrdline->setValue(
            DBEOrdline::ordheadID,
            $ordheadID
        );
        $dbeOrdline->setValue(
            DBEOrdline::sequenceNo,
            $sequenceNo
        );
        $dbeOrdline->moveRow('DOWN');
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setPKValue($ordheadID);
        $dbeOrdhead->setUpdatedTime();
    }

    function deleteOrderLine($ordheadID,
                             $sequenceNo
    )
    {
        if (!$ordheadID) {
            $this->raiseError('ordheadID not passed');
        }

        if (!$sequenceNo) {
            $sequenceNo = 0;
        }


        $dbeOrdline = new DBEOrdline($this);
        $dbeOrdline->setValue(
            DBEOrdline::ordheadID,
            $ordheadID
        );
        $dbeOrdline->setValue(
            DBEOrdline::sequenceNo,
            $sequenceNo
        );
        $dbeOrdline->deleteRow();
        $dbeOrdline->setValue(
            DBEOrdline::ordheadID,
            $ordheadID
        );
        $dbeOrdline->setValue(
            DBEOrdline::sequenceNo,
            $sequenceNo
        );
        $dbeOrdline->shuffleRowsUp();
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setPKValue($ordheadID);
        $dbeOrdhead->setUpdatedTime();
    }

    /**
     * Converts a quote into an order
     * @param $ordheadID
     * @param $convertToOrder
     * @param DataSet $dsSelectedOrderLine
     * @return bool|float|int|string
     */
    function convertQuoteToOrder($ordheadID,
                                 $convertToOrder,
                                 &$dsSelectedOrderLine
    )
    {
        $this->setMethodName('convertQuoteToOrder');
        if (!$ordheadID) {
            $this->raiseError('ordheadID not passed');
        }
        if (!is_a(
            $dsSelectedOrderLine,
            'DataSet'
        )) {
            $this->raiseError('orderLines object not passed');
        }
        $dsOrdhead = new DataSet($this);
        $dsOrdline = new DataSet($this);
        if (!$this->getOrderWithCustomerName(
            $ordheadID,
            $dsOrdhead,
            $dsOrdline,
            $dsContact
        )) {
            $this->raiseError('Quote not found');
        }
        $dbeOrdline = new DBEOrdline($this);
        $dbeOrdhead = new DBEOrdhead($this);
        $dsOrdhead->setPK(0);
        $dsOrdhead->fetchNext();
        // if all lines selected AND convert flag set so change quote status to initial
        $originalNo = $dsOrdhead->getValue(DBEOrdhead::ordheadID);

        if (
            ($dsOrdline->rowCount() == $dsSelectedOrderLine->rowCount()) and
            $convertToOrder                                // Flag indicates to convert not copy
        ) {
            $dsOrdhead->setUpdateModeUpdate();
            $dsOrdhead->setValue(
                DBEOrdhead::quotationOrdheadID,
                $originalNo
            );
            $dsOrdhead->setValue(
                DBEOrdhead::type,
                'I'
            ); // all lines selected so convert to initial order
            $dsOrdhead->setValue(
                DBEOrdhead::date,
                date('Y-m-d')
            );
            $dsOrdhead->post();
            $this->updateDataAccessObject(
                $dsOrdhead,
                $dbeOrdhead
            );
            $ret = $ordheadID;
        } else {        // create new initial order (leaving quote intact)
            $dsOrdhead->setUpdateModeUpdate();
            $dsOrdhead->setValue(
                DBEOrdhead::ordheadID,
                0
            );
            $dsOrdhead->setValue(
                DBEOrdhead::type,
                'I'
            );
            $dsOrdhead->setValue(
                DBEOrdhead::quotationOrdheadID,
                $originalNo
            );
            $dsOrdhead->setValue(
                DBEOrdhead::date,
                date('Y-m-d')
            );
            $dsOrdhead->post();
            $this->updateDataAccessObject(
                $dsOrdhead,
                $dbeOrdhead
            );    // create new order header
            $newOrdheadID = $dsOrdhead->getValue(DBEOrdhead::ordheadID);
            // Add selected lines to new order
            $sequenceNo = 0;
            $dsNewOrdline = new DataSet($this);
            $dsNewOrdline->copyColumnsFrom($dsOrdline);
            while ($dsOrdline->fetchNext()) {
                if ($dsSelectedOrderLine->search(
                    DBEOrdline::sequenceNo,
                    $dsOrdline->getValue(DBEOrdline::sequenceNo)
                )) {
                    $sequenceNo++;
                    $dsNewOrdline->setUpdateModeInsert();
                    $dsNewOrdline->row = $dsOrdline->row;
                    $dsNewOrdline->setValue(
                        DBEOrdline::ordheadID,
                        $newOrdheadID
                    );
                    $dsNewOrdline->setValue(
                        DBEOrdline::sequenceNo,
                        $sequenceNo
                    );
                    $dsNewOrdline->post();
                }
            }
            $dbeOrdline->replicate($dsNewOrdline);
            /*
            Copy documents to new order
            */
            $buSalesOrderDocument = new BUSalesOrderDocument($this);
            $buSalesOrderDocument->copyDocumentsToOrder(
                $ordheadID,
                $newOrdheadID
            );

            $ret = $newOrdheadID;
        }
        $buCustomer = new BUCustomer($this);
        $buCustomer->setProspectFlagOff($dsOrdhead->getValue(DBEOrdhead::customerID));

        /*
         * Now we need to go through the lines looking for any quotation or domain renewals
         * If found, update the renewal record accordingly.
         */
        $this->getOrderByOrdheadID(
            $ret,
            $dsOrdhead,
            $dsOrdline
        );
        $buRenQuotation = null;
        while ($dsOrdline->fetchNext()) {

            if ($dsOrdline->getValue(DBEOrdline::renewalCustomerItemID)) {

                /*
                 * Only updates renewal if found on one of these renewal tables
                 */
                if (!$buRenQuotation) {
                    $buRenQuotation = new BURenQuotation($this);
                }

                $buRenQuotation->processQuotationRenewal(
                    $dsOrdline->getValue(DBEOrdline::renewalCustomerItemID),
                    $convertToOrder
                );

            }

        }

        return $ret;
    }


    /**
     * @param $ordheadID
     * @param $supplierID
     * @param DataSet $dsSelectedOrderLine
     */
    function changeSupplier($ordheadID,
                            $supplierID,
                            &$dsSelectedOrderLine
    )
    {
        $this->setMethodName('changeSupplier');

        if (!is_a(
            $dsSelectedOrderLine,
            'DataSet'
        )) {
            $this->raiseError('orderLines object not passed');
        }

        if (!$this->getOrderWithCustomerName(
            $ordheadID,
            $dsOrdhead,
            $dsOrdline,
            $dsContact
        )) {
            $this->raiseError('Order not found');
        }

        $dbeOrdline = new DBEOrdline($this);

        $dsSelectedOrderLine->initialise();

        while ($dsSelectedOrderLine->fetchNext()) {
            $dbeOrdline->getRowBySequence(
                $ordheadID,
                $dsSelectedOrderLine->getValue(DBEOrdline::sequenceNo)
            );
            if ($dbeOrdline->getValue(DBEOrdline::lineType) != 'C') {
                $dbeOrdline->setValue(
                    DBEOrdline::supplierID,
                    $supplierID
                );
                $dbeOrdline->updateRow();
            }
        }
    }// end changeSupplier

    /**
     * Create a duplicate quotation from an existing sales order
     * NOTE: The name of this function is misleading and has NOTHING to do with renewals!
     * @param $ordheadID
     * @return bool|float|int|string
     */
    function createRenewalQuote($ordheadID)
    {
        $this->setMethodName('createRenewalQuote');
        if (!$ordheadID) {
            $this->raiseError('ordheadID not passed');
        }
        $dsOrdhead = new DataSet($this);
        $dsOrdline = new DataSet($this);
        if (!$this->getOrderWithCustomerName(
            $ordheadID,
            $dsOrdhead,
            $dsOrdline,
            $dsContact
        )) {
            $this->raiseError('Quote not found');
        }
        $dbeOrdline = new DBEOrdline($this);
        $dbeOrdhead = new DBEOrdhead($this);
        $dsOrdhead->setPK(0);
        $dsOrdhead->fetchNext();


        $dsOrdhead->setUpdateModeUpdate();
        $dsOrdhead->setValue(
            DBEOrdhead::ordheadID,
            0
        );
        $dsOrdhead->setValue(
            DBEOrdhead::type,
            'Q'
        );
        $dsOrdhead->setValue(
            DBEOrdhead::date,
            date('Y-m-d')
        );
        $dsOrdhead->post();
        $this->updateDataAccessObject(
            $dsOrdhead,
            $dbeOrdhead
        );    // create new order header
        $newOrdheadID = $dsOrdhead->getValue(DBEOrdhead::ordheadID);
        // Add selected lines to new order
        $sequenceNo = 0;
        $dsNewOrdline = new DataSet($this);
        $dsNewOrdline->copyColumnsFrom($dsOrdline);
        while ($dsOrdline->fetchNext()) {
            $sequenceNo++;
            $dsNewOrdline->setUpdateModeInsert();
            $dsNewOrdline->row = $dsOrdline->row;
            $dsNewOrdline->setValue(
                DBEOrdline::ordheadID,
                $newOrdheadID
            );
            $dsNewOrdline->setValue(
                DBEOrdline::sequenceNo,
                $sequenceNo
            );
            $dsNewOrdline->setValue(
                DBEOrdline::qtyDespatched,
                0
            );
            $dsNewOrdline->setValue(
                DBEOrdline::qtyLastDespatched,
                0
            );
            $dsNewOrdline->post();
        }
        $dbeOrdline->replicate($dsNewOrdline);
        return $newOrdheadID;
    }

    /**
     * Delete multiple Order Lines
     * @param $ordheadID
     * @param DataSet $dsSelectedOrderLine
     * @return bool
     */
    function deleteLines($ordheadID,
                         &$dsSelectedOrderLine
    )
    {
        $this->setMethodName('deleteLines');
        if (!$ordheadID) {
            $this->raiseError('ordheadID not passed');
        }
        if (!is_a(
            $dsSelectedOrderLine,
            'DataSet'
        )) {
            $this->raiseError('orderLines object not passed');
        }
        if (!$this->getOrderWithCustomerName(
            $ordheadID,
            $dsOrdhead,
            $dsOrdline,
            $dsContact
        )) {
            $this->raiseError('Order/Quote not found');
        }
        $dsSelectedOrderLine->initialise();
        /*
            The reason for deletedCount is that, as a line is deleted, all the sequenceNos of lines beyond it are decreased by
            one. Therefore, we need to apply this adjustment to any subsequent deletions.
        */
        $deletedCount = 0;
        while ($dsSelectedOrderLine->fetchNext()) {
            $this->deleteOrderLine(
                $ordheadID,
                $dsSelectedOrderLine->getValue(DBEOrdline::sequenceNo) - $deletedCount
            );
            $deletedCount++;
        }
        return TRUE;
    }

    function updateHeader(
        $ordheadID,
        $custPORef,
        $paymentTermsID,
        $partInvoice,
        $addItem
    )
    {
        if (!$ordheadID) {
            $this->raiseError('ordheadID not passed');
        }
        $dbeOrdhead = new DBEOrdhead($this);
        if (!$dbeOrdhead->getRow($ordheadID)) {
            $this->raiseError('order not found');
        }
        $dbeOrdhead->setUpdateModeUpdate();
        $dbeOrdhead->setValue(
            DBEOrdhead::custPORef,
            $custPORef
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::paymentTermsID,
            $paymentTermsID
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::partInvoice,
            $partInvoice
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::addItem,
            $addItem
        );
        $dbeOrdhead->post();
    }

    function updateServiceRequestDetails(
        $ordheadID,
        $serviceRequestCustomerItemID,
        $serviceRequestPriority,
        $serviceRequestText
    )
    {
        if (!$ordheadID) {
            $this->raiseError('ordheadID not passed');
        }
        $dbeOrdhead = new DBEOrdhead($this);
        if (!$dbeOrdhead->getRow($ordheadID)) {
            $this->raiseError('order not found');
        }
        $dbeOrdhead->setUpdateModeUpdate();
        $dbeOrdhead->setValue(
            DBEOrdhead::serviceRequestCustomerItemID,
            $serviceRequestCustomerItemID
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::serviceRequestPriority,
            $serviceRequestPriority
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::serviceRequestText,
            $serviceRequestText
        );
        $dbeOrdhead->post();
    }

    function deleteServiceRequestDetails($ordheadID)
    {
        if (!$ordheadID) {
            $this->raiseError('ordheadID not passed');
        }
        $dbeOrdhead = new DBEOrdhead($this);
        if (!$dbeOrdhead->getRow($ordheadID)) {
            $this->raiseError('order not found');
        }
        $dbeOrdhead->setUpdateModeUpdate();
        $dbeOrdhead->setValue(
            DBEOrdhead::serviceRequestCustomerItemID,
            null
        );
        $dbeOrdhead->setValue(
            DBEOrdhead::serviceRequestText,
            null
        );
        $dbeOrdhead->post();
    }

    /**
     * get list of order lines for given order
     * amalgamate same item lines onto one
     * sort by supplier/sequence no
     * exclude comment lines
     * @param $ordheadID
     * @param $dsOrdline
     * @return bool
     */
    function getOrderItemsForPO($ordheadID,
                                &$dsOrdline
    )
    {
        if (!$ordheadID) {
            $this->raiseError('ordheadID not passed');
        }
        $dbeOrdhead = new DBEOrdhead($this);
        if (!$dbeOrdhead->getRow($ordheadID)) {
            $this->raiseError('order not found');
        }
        $dbeOrdlinePO = new DBEOrdlinePO($this);
        $dbeOrdlinePO->getRows($ordheadID);
        return ($this->getData(
            $dbeOrdlinePO,
            $dsOrdline
        ));
    }

    function countPurchaseOrders($ordheadID)
    {
        $this->setMethodName('countPurchaseOrders');
        $dbePorhead = new DBEPorhead($this);
        $dbePorhead->setValue(
            DBEPorhead::ordheadID,
            $ordheadID
        );
        return ($dbePorhead->countRowsByColumn(DBEPorhead::ordheadID));
    }

    function countLinkedServiceRequests($ordheadID)
    {
        $this->setMethodName('countLinkedServiceRequests');
        $dbeProblem = new DBEProblem($this);
        $dbeProblem->setValue(
            DBEProblem::linkedSalesOrderID,
            $ordheadID
        );
        return ($dbeProblem->countRowsByColumn(DBEProblem::linkedSalesOrderID));
    }

    function getLinkedServiceRequestID($ordheadID)
    {
        $this->setMethodName('getLinkedServiceRequestID');
        $dbeProblem = new DBEProblem($this);
        $dbeProblem->setValue(
            DBEProblem::linkedSalesOrderID,
            $ordheadID
        );
        $dbeProblem->getRowByColumn(DBEProblem::linkedSalesOrderID);
        return $dbeProblem->getValue(DBEProblem::problemID);
    }

    /**
     * Update all sales order lines with given qty, cost and sale values
     * @parameter dataset $dsOrdline dataset
     * @param $ordheadID
     * @param DataSet $dsOrdline
     * @return void : Success
     * @access public
     */
    function updateOrderLineValues($ordheadID,
                                   &$dsOrdline
    )
    {
        $this->setMethodName('updateOrderLineValues');
        $dbeOrdhead = new DBEOrdhead($this);
        if (!$dbeOrdhead->getRow($ordheadID)) {
            $this->raiseError('order header not found');
        }
        // ordline fields
        $dbeOrdline = new DBEOrdline($this);
        $dsOrdline->initialise();
        while ($dsOrdline->fetchNext()) {
            $dbeOrdline->setValue(
                DBEOrdline::ordheadID,
                $ordheadID
            );
            $dbeOrdline->setValue(
                DBEOrdline::sequenceNo,
                $dsOrdline->getValue(DBEOrdline::sequenceNo)
            );
            $dbeOrdline->getRow();
            $dbeOrdline->setValue(
                DBEOrdline::qtyOrdered,
                $dsOrdline->getValue(DBEOrdline::qtyOrdered)
            );
            $dbeOrdline->setValue(
                DBEOrdline::curUnitSale,
                $dsOrdline->getValue(DBEOrdline::curUnitSale)
            );
            $dbeOrdline->setValue(
                DBEOrdline::curUnitCost,
                $dsOrdline->getValue(DBEOrdline::curUnitCost)
            );
            $dbeOrdline->setValue(
                DBEOrdline::curUnitCost,
                $dsOrdline->getValue(DBEOrdline::curUnitCost)
            );
            $dbeOrdline->setValue(
                DBEOrdline::curTotalCost,
                $dsOrdline->getValue(DBEOrdline::curUnitCost) * $dsOrdline->getValue(DBEOrdline::qtyOrdered)
            );
            $dbeOrdline->setValue(
                DBEOrdline::curTotalSale,
                $dsOrdline->getValue(DBEOrdline::curUnitSale) * $dsOrdline->getValue(DBEOrdline::qtyOrdered)
            );
            // this is to get around a bug I just found where the string does not get escaped!!!
            $dbeOrdline->setValue(
                DBEOrdline::description,
                $dbeOrdline->getValue(DBEOrdline::description)
            );
            $dbeOrdline->updateRow();
        }
        $dbeOrdhead->setUpdatedTime();
    }

    /**
     * Called from BUPurchaseInv in order to force direct delivery sales order to completed when
     * all purchase orders have been authorised.
     * @param $ordheadID
     * @return bool
     */
    function setStatusCompleted($ordheadID)
    {
        $this->setMethodName('setStatusCompleted');
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setPKValue($ordheadID);
        $dbeOrdhead->setStatusCompleted();
        $dbeOrdline = new DBEOrdline($this);
        $dbeOrdline->setValue(
            DBEOrdline::ordheadID,
            $ordheadID
        );
//			$dbeOrdline->setRowsToDespatched();
        return TRUE;
    }

    /**
     * copy lines from one sales order and paste them to the end of another
     * @param $fromOrdheadID
     * @param $toOrdheadID
     * @param bool $keepRenewals
     * @param bool $sequenceNo
     * @return bool
     */
    function pasteLinesFromOrder($fromOrdheadID,
                                 $toOrdheadID,
                                 $keepRenewals = false,
                                 $sequenceNo = false
    )
    {
        $this->setMethodName('pasteLinesFromOrder');
        $dbeFromOrdline = new DBEOrdline($this);
        $colCount = $dbeFromOrdline->colCount();
        $dbeToOrdline = new DBEOrdline($this);

        $dbeFromOrdline->setValue(
            DBEOrdline::ordheadID,
            $fromOrdheadID
        );
        $dbeFromOrdline->getRowsByColumn(
            DBEOrdline::ordheadID,
            DBEOrdline::sequenceNo
        );

        $dbeToOrdline->setValue(
            DBEOrdline::ordheadID,
            $toOrdheadID
        );
        $dbeToOrdline->getRowsByColumn(
            DBEOrdline::ordheadID,
            DBEOrdline::sequenceNo
        );
        $dbeToOrdline->resetQueryString();

        if (!$sequenceNo) {
            $sequenceNo = $dbeToOrdline->rowCount(); // so we paste after the last row
        } else {
            /*
            Shuffle up lines past $sequenceNo
            */
            $fromOrderLineCount = $dbeFromOrdline->rowCount();

            $db = $GLOBALS['db'];
            $statement =
                "UPDATE
          ordline
        SET
          odl_item_no = odl_item_no + $fromOrderLineCount
        WHERE
          odl_ordno = $toOrdheadID
          AND odl_item_no >= $sequenceNo";

            $db->query($statement);

            $sequenceNo--;

        }

        while ($dbeFromOrdline->fetchNext()) {
            $sequenceNo++;
            for ($i = 0; $i < $colCount; $i++) {
                $dbeToOrdline->setValueNoCheckByColumnNumber(
                    $i,
                    $dbeFromOrdline->getValueNoCheckByColumnNumber($i)
                );
            }
            $dbeToOrdline->setValue(
                DBEOrdline::ordheadID,
                $toOrdheadID
            );
            $dbeToOrdline->setValue(
                DBEOrdline::qtyDespatched,
                0
            );
            $dbeToOrdline->setValue(
                DBEOrdline::qtyLastDespatched,
                0
            );
            $dbeToOrdline->setValue(
                DBEOrdline::sequenceNo,
                $sequenceNo
            );
            if (!$keepRenewals) {
                $dbeToOrdline->setValue(
                    DBEOrdline::renewalCustomerItemID,
                    0
                );
            }
            $dbeToOrdline->insertRow();
        }
        return TRUE;
    }

    public function getSalesUsers(&$dsResults)
    {
        $dbeUser = new DBEUser($this);
        $dbeUser->getRowsInGroup('sales');
        return ($this->getData(
            $dbeUser,
            $dsResults
        ));

    }

    /**
     * Where many lines exist with identical description and rates, summarise onto one line
     *
     * @param mixed $ordheadID
     */
    function consolidateSalesOrderLines($ordheadID)
    {
        $db = $GLOBALS['db'];
        /*
        Get a list of existing order line numbers
        */
        $statement =
            "SELECT GROUP_CONCAT(`odl_ordlineno`) AS ordlinenos
FROM ordline
WHERE odl_ordno = $ordheadID
  AND (odl_itemno = 1502 OR odl_itemno = 1503)";

        $db->query($statement);
        $db->next_record();
        $oldOrdlinenos = $db->Record['ordlinenos'];
        /*
        Insert new, summarised order lines
        */
        $statement =
            "INSERT INTO ordline

        SELECT
          0,
          `odl_type`,
          `odl_ordno`,
          `odl_item_no`,
          `odl_custno`,
          `odl_itemno`,
          `odl_stockcat`,
          `odl_desc`,
          SUM( odl_qty_ord ),
          SUM(`odl_qty_desp`),
          SUM(`odl_qty_last_desp`),
          `odl_suppno`,
          `odl_d_unit`,
          SUM( `odl_d_total` ),
          `odl_e_unit`,
          SUM( `odl_e_total` ),
          `odl_renewal_cuino` 

        FROM
          ordline

        WHERE
          odl_ordno = $ordheadID
          AND (odl_itemno = 1502 OR odl_itemno = 1503)
        GROUP BY
          odl_desc, odl_e_unit, odl_d_unit
          
        ORDER BY odl_item_no";

        $db->query($statement);
        /*
        Remove original order lines
        */
        $statement = "DELETE FROM ordline WHERE odl_ordlineno IN (" . $oldOrdlinenos . ")";

        $db->query($statement);
    }

    public function notifyPurchaseOrderCompletion(DBEPorhead $purchaseOrderHeader)
    {
        // we need to find out what is the related sales order first

        $salesOrderID = $purchaseOrderHeader->getValue(DBEPorhead::ordheadID);

        $purchaseOrdersForSalesOrder = new DBEPorhead($this);

        $purchaseOrdersForSalesOrder->setValue(
            DBEPorhead::ordheadID,
            $salesOrderID
        );
        $purchaseOrdersForSalesOrder->getRowsByColumn(DBEPorhead::ordheadID);

        $shouldNotify = true;
        echo '<div>We are pulling all the purchase orders for the sales order: ' . $salesOrderID . '</div>';
        while ($purchaseOrdersForSalesOrder->fetchNext()) {
            echo '<div>We are looking at purchase order with ID: ' . $purchaseOrdersForSalesOrder->getValue(
                    DBEPorhead::porheadID
                ) . '</div>';
            if ($purchaseOrdersForSalesOrder->getValue(DBEPorhead::porheadID) == $purchaseOrderHeader->getValue(
                    DBEPorhead::porheadID
                )) {
                echo '<div> This is the same as the one we are processing</div>';
                continue;
            }

            if ($purchaseOrdersForSalesOrder->getValue(DBEPorhead::completionNotifiedFlag) == 'N') {
                $shouldNotify = false;
                echo '<div>We have found another purchase order that is not completed yet..so we cannot create the activity</div>';
                break;
            }
        }

        // we need to now find the associated SR, if there's more than one we only care about the one with the smallest ID
        $problemID = $this->getLinkedServiceRequestID($salesOrderID);

        if ($problemID && $shouldNotify) {
            $buActivity = new BUActivity($this);
            $buActivity->createPurchaseOrderCompletedSalesActivity($problemID);
        }
        $purchaseOrderHeader->setValue(
            DBEPorhead::completionNotifiedFlag,
            'Y'
        );

        $purchaseOrderHeader->updateRow();

    }

}