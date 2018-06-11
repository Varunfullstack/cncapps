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
require_once($cfg["path_dbe"] . "/DBEOrdlinePO.inc.php"); // for Puchase orders
require_once($cfg["path_dbe"] . "/DBEQuotation.inc.php");
require_once($cfg["path_dbe"] . "/DBEJQuotation.inc.php");
require_once($cfg["path_dbe"] . "/DBEVat.inc.php");
require_once($cfg["path_bu"] . "/BUCustomerNew.inc.php");
require_once($cfg["path_bu"] . "/BURenewal.inc.php");
require_once($cfg["path_bu"] . "/BURenQuotation.inc.php");
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUItem.inc.php');
require_once($cfg['path_bu'] . '/BUInvoice.inc.php');
require_once($cfg["path_dbe"] . "/DBEPorhead.inc.php");

class BUSalesOrder extends Business
{
    var $dbeQuotation = "";
    var $dbeJQuotation = "";

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
        if ($ordheadID != '') {
            $ret = ($this->getDatasetByPK($ordheadID, $dbeJOrdhead, $dsResults));
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
        return $ret;
    }

    function getOrderWithCustomerName($ordheadID, &$dsOrdhead, &$dsJOrdline, &$dsDeliveryContact)
    {
        $this->setMethodName('getOrderWithCustomerName');
        $ret = FALSE;
        if ($ordheadID == '') {
            $this->raiseError('order ID not passed');
        } else {
            $dbeJOrdline = new DBEJOrdline($this);
            $dbeJOrdhead = new DBEJOrdhead($this);
            $ret = ($this->getDatasetByPK($ordheadID, $dbeJOrdhead, $dsOrdhead));
            if (!$ret) {
                $this->raiseError('order not found');
            }
            $dbeJOrdline->setValue('ordheadID', $ordheadID);
            $dbeJOrdline->getRowsByColumn('ordheadID', 'sequenceNo');
            $this->getData($dbeJOrdline, $dsJOrdline);
            $dsJOrdline->sortAscending('sequenceNo');
            $buCustomer = new BUCustomer($this);
            $buCustomer->getContactByID($dsOrdhead->getValue('delContactID'), $dsDeliveryContact);
        }
        return $ret;
    }

    function getOrderByOrdheadID($ordheadID, &$dsOrdhead, &$dsOrdline)
    {
        $this->setMethodName('getOrderByOrdheadID');
        $ret = FALSE;
        if ($ordheadID == '') {
            $this->raiseError('order ID not passed');
        } else {
            $dbeOrdline = new DBEOrdline($this);
            $dbeOrdhead = new DBEOrdhead($this);
            $ret = ($this->getDatasetByPK($ordheadID, $dbeOrdhead, $dsOrdhead));
            if (!$ret) {
                $this->raiseError('order not found');
            }

            $dbeOrdline->setValue('ordheadID', $ordheadID);
            $dbeOrdline->getRowsByColumn('ordheadID', 'sequenceNo');
            $this->getData($dbeOrdline, $dsOrdline);
        }

        return $ret;
    }

    function getOrdheadByID($ordheadID, &$dsOrdhead)
    {
        $this->setMethodName('getOrdheadByID');
        $ret = FALSE;
        if ($ordheadID == '') {
            $this->raiseError('order ID not passed');
        } else {
            $dbeJOrdhead = new DBEJOrdhead($this);
            return ($this->getDatasetByPK($ordheadID, $dbeJOrdhead, $dsOrdhead));
        }
    }

    function getOrdlineByIDSeqNo($ordheadID, $sequenceNo, &$dsOrdline)
    {
        $this->setMethodName('getOrdlineByIDSeqNo');
        $ret = FALSE;

        if ($ordheadID == '') {
            $this->raiseError('order ID not passed');
        }
        if ($sequenceNo == '') {
            $this->raiseError('sequenceNo not passed');
        }
        $dbeJOrdline = new DBEJOrdline($this);
        $dbeJOrdline->setValue('ordheadID', $ordheadID);
        $dbeJOrdline->setValue('sequenceNo', $sequenceNo);

        $dbeJOrdline->getRowByOrdheadIDSequenceNo();
        return ($this->getData($dbeJOrdline, $dsOrdline));
    }

    /**
     * Send email to order delivery contact from cnc user as PDF attachment
     * @parameter Integer $ordheadID Order ID
     * @parameter Integer $userID CNC User
     * @return bool : Successfully sent
     * @access public
     */
    function insertQuotation(&$dsData)
    {
        $this->setMethodName('insertQuotation');
        $this->updateDataaccessObject($dsData, $this->dbeQuotation);
        return $dsData->getValue('quotationID');
    }

    function deleteQuotationDoc($quotationID)
    {
        $this->setMethodName('deleteQuotationDoc');
        if ($quotationID == '') {
            $this->raiseError('quotationID not passed');
        }
        $this->dbeQuotation->setPKValue($quotationID);
        return ($this->dbeQuotation->deleteRow());
    }

    /**
     * Get next quotation version to use for given sales order no
     * @parameter Integer $ordheadID Order ID
     * @return integer version no
     * @access public
     */
    function getNextQuoteVersion($ordheadID)
    {
        $this->setMethodName('getNextQuoteVersion');
        $this->dbeQuotation->setValue('ordheadID', $ordheadID);
        return ($this->dbeQuotation->getNextVersionNo());
    }

    /**
     * Get sent quotations for order
     * @parameter Integer $ordheadID Order ID
     * @return DataSet Quotations
     * @access public
     */
    function getQuotationsByOrdheadID($ordheadID, &$dsResults)
    {
        $this->setMethodName('getQuotationsByOrdheadID');
        $ret = FALSE;
        if ($ordheadID == '') {
            $this->raiseError('order ID not passed');
        } else {
            $this->dbeJQuotation->setValue('ordheadID', $ordheadID);
            $this->dbeJQuotation->getRowsByOrdheadID();
            $ret = ($this->getData($this->dbeJQuotation, $dsResults));
            //$dsResults->sortAscending('versionNo');
        }
        return $ret;
    }

    /**
     * Get all users
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getAllUsers(&$dsResults)
    {
        $this->setMethodName('getAllUsers');
        $dbeUser = new DBEUser($this);
        $dbeUser->getRows();
        return ($this->getData($dbeUser, $dsResults));
    }

    /**
     * Get one users
     * @parameter integer $userID user
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getUserByID($userID, &$dsResults)
    {
        $this->setMethodName('getUserByID');
        $dbeUser = new DBEUser($this);
        return ($this->getDatasetByPK($userID, $dbeUser, $dsResults));
    }

    /**
     * Get one quote
     * @parameter integer $quoteID quoteID
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getQuoteByID($quoteID, &$dsResults)
    {
        $this->setMethodName('getQuoteByID');
        return ($this->getDatasetByPK($quoteID, $this->dbeQuotation, $dsResults));
    }

    /**
     * Delete quote and all associated quote documents
     * @parameter integer $quoteID quoteID
     * @return bool : Success
     * @access public
     */
    function deleteOrder($ordheadID)
    {
        $this->setMethodName('deleteOrder');
        // Validation
        if ($ordheadID == '') {
            $this->raiseError('ordheadID not passed');
        }
        if (!$this->getOrdheadByID($ordheadID, $dsOrdhead)) {
            $this->raiseError('Order not found');
            exit;
        }

        if (
            ($dsOrdhead->getValue('type') <> 'I') &
            ($dsOrdhead->getValue('type') <> 'Q')
        ) {
            $this->raiseError('Must be quote or initial order');
            exit;
        }

        if (
            ($orderType <> 'Q') &&
            $this->countPurchaseOrders($dsOrdhead->getValue('ordheadID')) > 0
        ) {
            $this->raiseError('Cannot delete order because purchase orders exist');
            exit;
        }

        if ($orderType <> 'Q') {
            $buInvoice = new BUInvoice($this);
            if ($buInvoice->countInvoicesByOrdheadID($dsOrdhead->getValue('ordheadID')) > 0) {
                $this->raiseError('Cannot delete order because invoices exist');
                exit;
            }
        }

        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdline = new DBEOrdline($this);
        $dbeOrdhead->setPKValue($ordheadID);
        $dbeOrdhead->deleteRow();
        $dbeOrdline->setValue('ordheadID', $ordheadID);
        $dbeOrdline->deleteRowsByOrderID();

        // delete any quote rows and associated documents
        $this->dbeQuotation->setValue('ordheadID', $ordheadID);
        $this->dbeQuotation->getRowsByColumn('ordheadID');
        $this->getData($this->dbeQuotation, $dsQuotation);
        // delete quote docs from DB and quote directory
        while ($dsQuotation->fetchNext()) {
            $quoteFile =
                'quotes/' .
                $dsQuotation->getValue('ordheadID') . '_' . $dsQuotation->getValue('versionNo') . '.' .
                $dsQuotation->getValue('fileExtension');
            $this->deleteQuotationDoc($dsQuotation->getValue('quotationID'));
            unlink($quoteFile);
        }
        return TRUE;
    }

    /**
     * Initialse fields for new order
     * @parameter integer $customerID
     * @return bool : Success
     * @access public
     */
    function InitialiseOrder(&$dsOrdhead, &$dsOrdline, &$dsCustomer)
    {
        // simply call with type set to I!
        $this->initialiseQuote($dsOrdhead, $dsOrdline, $dsCustomer, 'I');
        $buCustomer = new BUCustomer($this);
        $buCustomer->setProspectFlagOff($dsOrdhead->getValue('customerID'));
    }

    /**
     * Initialse fields for new quote
     * @parameter integer $customerID
     * @return bool : Success
     * @access public
     */
    function initialiseQuote(&$dsOrdhead, &$dsOrdline, &$dsCustomer, $type = 'Q')
    {

        $this->setMethodName('initialiseQuote');
        $dsOrdhead = new DataSet($this);
        $dbeOrdhead = new DBEOrdhead($this);
        $dsOrdhead->copyColumnsFrom($dbeOrdhead);
        $dsOrdhead->setUpdateModeInsert();
        $dsOrdhead->addColumn('customerName', DA_STRING, DA_NOT_NULL);
        $dsOrdhead->setValue('customerName', $dsCustomer->getValue(DBECustomer::name));
        $dsOrdhead->setValue('customerID', $dsCustomer->getValue(DBECustomer::customerID));
        $dsOrdhead->setValue('type', $type);
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);
        $dsHeader->fetchNext();
        $vatCode = $dsHeader->getValue('stdVATCode');
        $dsOrdhead->setValue('vatCode', $vatCode);
        $dbeVat = new DBEVat($this);
        $dbeVat->getRow();
        $vatRate = $dbeVat->getValue((integer)$vatCode[1]); // get 2nd part of code and use as column no
        $dsOrdhead->setValue('vatRate', $vatRate);
        $dsOrdhead->setValue('date', date('Y-m-d'));

        if ($type == 'Q') {
            $dsOrdhead->setValue('quotationCreateDate', date('Y-m-d'));
        }
        // If the order customer is an internal stock location then we set pay method to no invoice
        if (
            ($dsOrdhead->getValue('customerID') == CONFIG_SALES_STOCK_CUSTOMERID) OR
            ($dsOrdhead->getValue('customerID') == CONFIG_MAINT_STOCK_CUSTOMERID)
        ) {
//	 		$dsOrdhead->setValue('payMethod', 'N'); // switch to paymentTermsID
            $dsOrdhead->setValue('paymentTermsID', CONFIG_PAYMENT_TERMS_NO_INVOICE);    // switch to paymentTermsID
        } else {
// 			$dsOrdhead->setValue('payMethod', 'F');	// switch to paymentTermsID
            $dsOrdhead->setValue('paymentTermsID', CONFIG_PAYMENT_TERMS_30_DAYS);    // switch to paymentTermsID
        }
        $dsOrdhead->setValue('partInvoice', 'Y');
        $dsOrdhead->setValue('addItem', 'Y');
        $dsOrdhead->setValue('requestedDate', '0000-00-00');
        $dsOrdhead->setValue('promisedDate', '0000-00-00');
        $dsOrdhead->setValue('expectedDate', '0000-00-00');
        $dsOrdhead->setValue('invSiteNo', $dsCustomer->getValue(DBECustomer::invoiceSiteNo));
        $dsOrdhead->setValue('updatedTime', date('Y-m-d H:i:s'));
        $this->setInvoiceSiteAndContact(
            $dsCustomer->getValue(DBECustomer::customerID),
            $dsCustomer->getValue(DBECustomer::invoiceSiteNo),
            $dsOrdhead
        );
        $dsOrdhead->setValue('delSiteNo', $dsCustomer->getValue(DBECustomer::deliverSiteNo));
        $this->setDeliverySiteAndContact(
            $dsCustomer->getValue(DBECustomer::customerID),
            $dsCustomer->getValue(DBECustomer::deliverSiteNo),
            $dsOrdhead
        );
        $dsOrdhead->post();
        $this->updateDataaccessObject($dsOrdhead, $dbeOrdhead);
        $dsOrdline = new DataSet($this);
        $dbeOrdline = new DBEOrdline($this);
        $dsOrdline->copyColumnsFrom($dbeOrdline);
    }

    function setInvoiceSiteAndContact($customerID, $siteNo, &$dsOrdhead)
    {
        $this->setMethodName('setInvoiceSiteAndContact');
        $buCustomer = new BUCustomer($this);

        $buCustomer->getSiteByCustomerIDSiteNo($customerID, $siteNo, $dsSite);
        $dsOrdhead->setValue('invSiteNo', $siteNo);
        $dsOrdhead->setValue('invAdd1', $dsSite->getValue(DBESite::add1));
        $dsOrdhead->setValue('invAdd2', $dsSite->getValue(DBESite::add2));
        $dsOrdhead->setValue('invAdd3', $dsSite->getValue(DBESite::add3));
        $dsOrdhead->setValue('invTown', $dsSite->getValue(DBESite::town));
        $dsOrdhead->setValue('invCounty', $dsSite->getValue(DBESite::county));
        $dsOrdhead->setValue('invPostcode', $dsSite->getValue(DBESite::postcode));
        $dsOrdhead->setValue('invSitePhone', $dsSite->getValue(DBESite::phone));
        $buCustomer->getContactByID($dsSite->getValue(DBESite::invoiceContactID), $dsContact);
        $dsOrdhead->setValue('invContactID', $dsContact->getValue('contactID'));
        $dsOrdhead->setValue('invContactName', $dsContact->getValue('lastName'));
        $dsOrdhead->setValue('invContactSalutation', $dsContact->getValue('firstName'));
        $dsOrdhead->setValue('invContactPhone', $dsContact->getValue('phone'));
        $dsOrdhead->setValue('invContactFax', $dsContact->getValue('fax'));
        $dsOrdhead->setValue('invContactEmail', $dsContact->getValue('email'));
    }

    function setDeliverySiteAndContact($customerID, $siteNo, &$dsOrdhead)
    {
        $this->setMethodName('setDeliverSiteAndContact');
        $buCustomer = new BUCustomer($this);

        $buCustomer->getSiteByCustomerIDSiteNo($customerID, $siteNo, $dsSite);
        $dsOrdhead->setValue('delSiteNo', $siteNo);
        $dsOrdhead->setValue('delAdd1', $dsSite->getValue(DBESite::add1));
        $dsOrdhead->setValue('delAdd2', $dsSite->getValue(DBESite::add2));
        $dsOrdhead->setValue('delAdd3', $dsSite->getValue(DBESite::add3));
        $dsOrdhead->setValue('delTown', $dsSite->getValue(DBESite::town));
        $dsOrdhead->setValue('delCounty', $dsSite->getValue(DBESite::county));
        $dsOrdhead->setValue('delPostcode', $dsSite->getValue(DBESite::postcode));
        $dsOrdhead->setValue('delSitePhone', $dsSite->getValue(DBESite::phone));
        $buCustomer->getContactByID($dsSite->getValue(DBESite::deliverContactID), $dsContact);
        $dsOrdhead->setValue('delContactID', $dsContact->getValue('contactID'));
        $dsOrdhead->setValue('delContactName', $dsContact->getValue('lastName'));
        $dsOrdhead->setValue('delContactSalutation', $dsContact->getValue('firstName'));
        $dsOrdhead->setValue('delContactPhone', $dsContact->getValue('phone'));
        $dsOrdhead->setValue('delContactFax', $dsContact->getValue('fax'));
        $dsOrdhead->setValue('delContactEmail', $dsContact->getValue('email'));
    }

    function updateInvoiceAddress($ordheadID, $siteNo)
    {
        $this->setMethodName('updateInvoiceAddress');
        if ($ordheadID == '') {
            $this->raiseError('ordheadID not passed');
        }
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setPKValue($ordheadID);
        $dbeOrdhead->getRow();
        $buCustomer = new BUCustomer($this);

        $buCustomer->getSiteByCustomerIDSiteNo(
            $dbeOrdhead->getValue('customerID'),
            $siteNo,
            $dsSite
        );
        $dbeOrdhead->setUpdateModeUpdate();
        $dbeOrdhead->setValue('invSiteNo', $siteNo);
        $dbeOrdhead->setValue('invAdd1', $dsSite->getValue(DBESite::add1));
        $dbeOrdhead->setValue('invAdd2', $dsSite->getValue(DBESite::add2));
        $dbeOrdhead->setValue('invAdd3', $dsSite->getValue(DBESite::add3));
        $dbeOrdhead->setValue('invTown', $dsSite->getValue(DBESite::town));
        $dbeOrdhead->setValue('invCounty', $dsSite->getValue(DBESite::county));
        $dbeOrdhead->setValue('invPostcode', $dsSite->getValue(DBESite::postcode));
        $dbeOrdhead->setValue('invSitePhone', $dsSite->getValue(DBESite::phone));
        $buCustomer->getContactByID($dsSite->getValue(DBESite::invoiceContactID), $dsContact);
        $dbeOrdhead->setValue('invContactID', $dsContact->getValue('contactID'));
        $dbeOrdhead->setValue('invContactName', $dsContact->getValue('lastName'));
        $dbeOrdhead->setValue('invContactSalutation', $dsContact->getValue('firstName'));
        $dbeOrdhead->setValue('invContactPhone', $dsContact->getValue('phone'));
        $dbeOrdhead->setValue('invContactFax', $dsContact->getValue('fax'));
        $dbeOrdhead->setValue('invContactEmail', $dsContact->getValue('email'));
        $dbeOrdhead->post();
        return TRUE;
    }

    function updateDeliveryAddress($ordheadID, $siteNo)
    {
        $this->setMethodName('updateDeliveryAddress');
        if ($ordheadID == '') {
            $this->raiseError('ordheadID not passed');
        }
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setPKValue($ordheadID);
        $dbeOrdhead->getRow();
        $buCustomer = new BUCustomer($this);

        $buCustomer->getSiteByCustomerIDSiteNo(
            $dbeOrdhead->getValue('customerID'),
            $siteNo,
            $dsSite
        );
        $dbeOrdhead->setUpdateModeUpdate();
        $dbeOrdhead->setValue('delSiteNo', $siteNo);
        $dbeOrdhead->setValue('delAdd1', $dsSite->getValue(DBESite::add1));
        $dbeOrdhead->setValue('delAdd2', $dsSite->getValue(DBESite::add2));
        $dbeOrdhead->setValue('delAdd3', $dsSite->getValue(DBESite::add3));
        $dbeOrdhead->setValue('delTown', $dsSite->getValue(DBESite::town));
        $dbeOrdhead->setValue('delCounty', $dsSite->getValue(DBESite::county));
        $dbeOrdhead->setValue('delPostcode', $dsSite->getValue(DBESite::postcode));
        $dbeOrdhead->setValue('delSitePhone', $dsSite->getValue(DBESite::phone));
        $buCustomer->getContactByID($dsSite->getValue(DBESite::deliverContactID), $dsContact);
        $dbeOrdhead->setValue('delContactID', $dsContact->getValue('contactID'));
        $dbeOrdhead->setValue('delContactName', $dsContact->getValue('lastName'));
        $dbeOrdhead->setValue('delContactSalutation', $dsContact->getValue('firstName'));
        $dbeOrdhead->setValue('delContactPhone', $dsContact->getValue('phone'));
        $dbeOrdhead->setValue('delContactFax', $dsContact->getValue('fax'));
        $dbeOrdhead->setValue('delContactEmail', $dsContact->getValue('email'));
        $dbeOrdhead->post();
        return TRUE;
    }

    function updateInvoiceContact($ordheadID, $contactID)
    {
        $this->setMethodName('updateInvoiceContact');
        if ($ordheadID == '') {
            $this->raiseError('ordheadID not passed');
        }
        if ($contactID == '') {
            $this->raiseError('contactID not passed');
        }
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setPKValue($ordheadID);
        $dbeOrdhead->getRow();
        $dbeOrdhead->setUpdateModeUpdate();
        $buCustomer = new BUCustomer($this);
        $buCustomer->getContactByID($contactID, $dsContact);
        $dbeOrdhead->setValue('invContactID', $dsContact->getValue('contactID'));
        $dbeOrdhead->setValue('invContactName', $dsContact->getValue('lastName'));
        $dbeOrdhead->setValue('invContactSalutation', $dsContact->getValue('firstName'));
        $dbeOrdhead->setValue('invContactPhone', $dsContact->getValue('phone'));
        $dbeOrdhead->setValue('invContactFax', $dsContact->getValue('fax'));
        $dbeOrdhead->setValue('invContactEmail', $dsContact->getValue('email'));
        $dbeOrdhead->post();
        return TRUE;
    }

    function updateDeliveryContact($ordheadID, $contactID)
    {
        $this->setMethodName('updateDeliveryContact');
        if ($ordheadID == '') {
            $this->raiseError('ordheadID not passed');
        }
        if ($contactID == '') {
            $this->raiseError('contactID not passed');
        }
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setPKValue($ordheadID);
        $dbeOrdhead->getRow();
        $dbeOrdhead->setUpdateModeUpdate();
        $buCustomer = new BUCustomer($this);
        $buCustomer->getContactByID($contactID, $dsContact);
        $dbeOrdhead->setValue('delContactID', $dsContact->getValue('contactID'));
        $dbeOrdhead->setValue('delContactName', $dsContact->getValue('lastName'));
        $dbeOrdhead->setValue('delContactSalutation', $dsContact->getValue('firstName'));
        $dbeOrdhead->setValue('delContactPhone', $dsContact->getValue('phone'));
        $dbeOrdhead->setValue('delContactFax', $dsContact->getValue('fax'));
        $dbeOrdhead->setValue('delContactEmail', $dsContact->getValue('email'));
        $dbeOrdhead->post();
        return TRUE;
    }

    /**
     * Update sales order line
     * @parameter integer $quoteID quoteID
     * @return bool : Success
     * @access public
     */
    function updateOrderLine(&$dsOrdline, $action = "U")
    {
        $this->setMethodName('updateOrderLine');
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setPKValue($dsOrdline->getValue('ordheadID'));
        if (!$dbeOrdhead->getRow()) {
            $this->raiseError('order header not found');
        }
        // ordline fields
        $dbeOrdline = new DBEOrdline($this);

        $dbeOrdline->setValue('lineType', $dsOrdline->getValue('lineType'));
        $dbeOrdline->setValue('qtyOrdered', $dsOrdline->getValue('qtyOrdered'));
        $dbeOrdline->setValue('curUnitSale', $dsOrdline->getValue('curUnitSale'));
        $dbeOrdline->setValue('curUnitCost', $dsOrdline->getValue('curUnitCost'));
        $dbeOrdline->setValue('supplierID', $dsOrdline->getValue('supplierID'));
        $dbeOrdline->setValue('renewalCustomerItemID', $dsOrdline->getValue('renewalCustomerItemID'));
        $dbeOrdline->setValue('ordheadID', $dsOrdline->getValue('ordheadID'));
        $dbeOrdline->setValue('sequenceNo', $dsOrdline->getValue('sequenceNo'));
        $dbeOrdline->setValue('customerID', $dbeOrdhead->getValue('customerID'));
        $dbeOrdline->setValue('qtyDespatched', 0);
        $dbeOrdline->setValue('qtyLastDespatched', 0);
        if ($dsOrdline->getValue('lineType') == 'I') {
            $dbeOrdline->setValue('itemID', $dsOrdline->getValue('itemID'));
            $buItem = new BUItem($this);
            if ($buItem->getItemByID($dsOrdline->getValue('itemID'), $dsItem)) {
                $dbeOrdline->setValue('stockcat', $dsItem->getValue('stockcat'));
            }
            $dbeOrdline->setValue('description', $dsOrdline->getValue('description'));
            $dbeOrdline->setValue('curTotalCost',
                                  $dsOrdline->getValue('curUnitCost') * $dsOrdline->getValue('qtyOrdered')
            );
            $dbeOrdline->setValue('curTotalSale',
                                  $dsOrdline->getValue('curUnitSale') * $dsOrdline->getValue('qtyOrdered')
            );
        } else {
            $dbeOrdline->setValue('qtyOrdered', 0);
            $dbeOrdline->setValue('curUnitCost', 0);
            $dbeOrdline->setValue('curUnitSale', 0);
            $dbeOrdline->setValue('curTotalCost', 0);
            $dbeOrdline->setValue('curTotalSale', 0);
            $dbeOrdline->setValue('description', $dsOrdline->getValue('description'));
        }
        if ($action == "U") {
            $dbeOrdline->updateRow();
        } else {
            $dbeOrdline->insertRow();
        }
        $dbeOrdhead->setUpdatedTime();
    }

    /**
     * Initialise new ordline dataset row
     * This DOES NOT change the database
     * @parameter dateset $dsOrdline
     * @return bool : Success
     * @access public
     */
    function initialiseNewOrdline($ordheadID, $sequenceNo, &$dsOrdline)
    {
        $this->setMethodName('initialiseNewOrdline');
        if ($ordheadID == '') {
            $this->raiseError('ordheadID not passed');
        }
        if ($sequenceNo == '') {
            $this->raiseError('sequenceNo not passed');
        }
        $dbeJOrdline = new DBEJOrdline($this);
        $dsOrdline = new DataSet($this);
        $dsOrdline->copyColumnsFrom($dbeJOrdline);
        $dsOrdline->setUpdateModeInsert();
        $dsOrdline->setValue('ordheadID', $ordheadID);
        $dsOrdline->setValue('itemID', '');
        $dsOrdline->setValue('supplierID', '');
        $dsOrdline->setValue('sequenceNo', $sequenceNo);
        $dsOrdline->setValue('lineType', 'I');    // default item line
        $dsOrdline->setValue('qtyOrdered', 1);    // default 1
        $dsOrdline->post();
    }

    /**
     * Insert new ordline dataset row
     * This changes the database
     * @parameter dateset $dsOrdline
     * @return bool : Success
     * @access public
     */
    function insertNewOrderLine(&$dsOrdline)
    {
        $this->setMethodName('insertNewOrdline');
//count rows
        $dsOrdline->fetchNext();
        $dbeOrdline = new DBEOrdline($this);
        $dbeOrdline->setValue('ordheadID', $dsOrdline->getValue('ordheadID'));
        if ($dbeOrdline->countRowsByColumn('ordheadID') > 0) {
            // shuffle down existing rows before inserting new one
            $dbeOrdline->setValue('ordheadID', $dsOrdline->getValue('ordheadID'));
            $dbeOrdline->setValue('sequenceNo', $dsOrdline->getValue('sequenceNo'));
            $dbeOrdline->shuffleRowsDown();
        }
        $ret = ($this->updateOrderLine($dsOrdline, "I"));
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setPKValue($dsOrdline->getValue('ordheadID'));
        $dbeOrdhead->setUpdatedTime();
    }

    function moveOrderLineUp($ordheadID, $sequenceNo)
    {
        if ($ordheadID == '') {
            $this->raiseError('ordheadID not passed');
        }
        if ($sequenceNo == '') {
            $this->raiseError('sequenceNo not passed');
        }
        if ($sequenceNo == 1) {
            return;
        }
        $dbeOrdline = new DBEOrdline($this);
        $dbeOrdline->setValue('ordheadID', $ordheadID);
        $dbeOrdline->setValue('sequenceNo', $sequenceNo);
        $dbeOrdline->moveRow('UP');
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setPKValue($ordheadID);
        $dbeOrdhead->setUpdatedTime();
    }

    function moveOrderLineDown($ordheadID, $sequenceNo)
    {
        if ($ordheadID == '') {
            $this->raiseError('ordheadID not passed');
        }
        if ($sequenceNo == '') {
            $this->raiseError('sequenceNo not passed');
        }
        $dbeOrdline = new DBEOrdline($this);
        $dbeOrdline->setValue('ordheadID', $ordheadID);
        $dbeOrdline->setValue('sequenceNo', $sequenceNo);
        $dbeOrdline->moveRow('DOWN');
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setPKValue($ordheadID);
        $dbeOrdhead->setUpdatedTime();
    }

    function deleteOrderLine($ordheadID, $sequenceNo)
    {
        if ($ordheadID == '') {
            $this->raiseError('ordheadID not passed');
        }

        if ($sequenceNo == '') {
            $sequenceNo = 0;
        }


        $dbeOrdline = new DBEOrdline($this);
        $dbeOrdline->setValue('ordheadID', $ordheadID);
        $dbeOrdline->setValue('sequenceNo', $sequenceNo);
        $dbeOrdline->deleteRow();
        $dbeOrdline->setValue('ordheadID', $ordheadID);
        $dbeOrdline->setValue('sequenceNo', $sequenceNo);
        $dbeOrdline->shuffleRowsUp();
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setPKValue($ordheadID);
        $dbeOrdhead->setUpdatedTime();
    }

    /**
     * Converts a quote into an order
     */
    function convertQuoteToOrder($ordheadID, $convertToOrder, &$dsSelectedOrderLine)
    {
        $this->setMethodName('convertQuoteToOrder');
        if ($ordheadID == '') {
            $this->raiseError('ordheadID not passed');
        }
        if (!is_a($dsSelectedOrderLine, 'DataSet')) {
            $this->raiseError('orderLines object not passed');
        }
        if (!$this->getOrderWithCustomerName($ordheadID, $dsOrdhead, $dsOrdline, $dsContact)) {
            $this->raiseError('Quote not found');
        }
        $dbeOrdline = new DBEOrdline($this);
        $dbeOrdhead = new DBEOrdhead($this);
        $dsOrdhead->setPK(0);
        $dsOrdhead->fetchNext();
        // if all lines selected AND convert flag set so change quote status to initial
        $originalNo = $dsOrdhead->getValue('ordheadID');

        if (
            ($dsOrdline->rowCount() == $dsSelectedOrderLine->rowCount()) and
            $convertToOrder                                // Flag indicates to convert not copy
        ) {
            $dsOrdhead->setUpdateModeUpdate();
            $dsOrdhead->setValue('quotationOrdheadID', $originalNo);
            $dsOrdhead->setValue('type', 'I'); // all lines selected so convert to initial order
            $dsOrdhead->setValue('date', date('Y-m-d'));
            $dsOrdhead->post();
            $this->updateDataaccessObject($dsOrdhead, $dbeOrdhead);
            $ret = $ordheadID;
        } else {        // create new initial order (leaving quote intact)
            $dsOrdhead->setUpdateModeUpdate();
            $dsOrdhead->setValue('ordheadID', 0);
            $dsOrdhead->setValue('type', 'I');
            $dsOrdhead->setValue('quotationOrdheadID', $originalNo);
            $dsOrdhead->setValue('date', date('Y-m-d'));
            $dsOrdhead->post();
            $this->updateDataaccessObject($dsOrdhead, $dbeOrdhead);    // create new order header
            $newOrdheadID = $dsOrdhead->getValue('ordheadID');
            // Add selected lines to new order
            $sequenceNo = 0;
            $dsNewOrdline = new DataSet($this);
            $dsNewOrdline->copyColumnsFrom($dsOrdline);
            while ($dsOrdline->fetchNext()) {
                if ($dsSelectedOrderLine->search('sequenceNo', $dsOrdline->getValue("sequenceNo"))) {
                    $sequenceNo++;
                    $dsNewOrdline->setUpdateModeInsert();
                    $dsNewOrdline->row = $dsOrdline->row;
                    $dsNewOrdline->setValue('ordheadID', $newOrdheadID);
                    $dsNewOrdline->setValue('sequenceNo', $sequenceNo);
                    $dsNewOrdline->post();
                }
            }
            $dbeOrdline->replicate($dsNewOrdline);
            /*
            Copy documents to new order
            */
            $buSalesOrderDocument = new BUSalesOrderDocument($this);
            $buSalesOrderDocument->copyDocumentsToOrder($ordheadID, $newOrdheadID);

            $ret = $newOrdheadID;
        }
        $buCustomer = new BUCustomer($this);
        $buCustomer->setProspectFlagOff($dsOrdhead->getValue('customerID'));

        /*
         * Now we need to go through the lines looking for any quotation or domain renwals
         * If found, update the renewal record accordingly.
         */
        $this->getOrderByOrdheadID($ret, $dsOrdhead, $dsOrdline);

        while ($dsOrdline->fetchNext()) {

            if ($dsOrdline->getValue('renewalCustomerItemID')) {

                /*
                 * Only updates renewal if found on one of these renewal tables
                 */
                if (!$buRenQuotation) {
                    $buRenQuotation = new BURenQuotation($this);
                }

                $buRenQuotation->processQuotationRenewal($dsOrdline->getValue('renewalCustomerItemID'));

            }

        }

        return $ret;
    }


    function changeSupplier($ordheadID, $supplierID, &$dsSelectedOrderLine)
    {
        $this->setMethodName('changeSupplier');

        if (!is_a($dsSelectedOrderLine, 'DataSet')) {
            $this->raiseError('orderLines object not passed');
        }

        if (!$this->getOrderWithCustomerName($ordheadID, $dsOrdhead, $dsOrdline, $dsContact)) {
            $this->raiseError('Order not found');
        }

        $dbeOrdline = new DBEOrdline($this);

        $dsSelectedOrderLine->initialise();

        while ($dsSelectedOrderLine->fetchNext()) {
            $dbeOrdline->getRowBySequence($ordheadID, $dsSelectedOrderLine->getValue('sequenceNo'));
            if ($dbeOrdline->getValue('lineType') != 'C') {
                $dbeOrdline->setValue('supplierID', $supplierID);
                $dbeOrdline->updateRow();
            }
        }
    }// end changeSupplier

    /**
     * Create a duplicate quotation from an existing sales order
     * NOTE: The name of this function is missleading and has NOTHING to do with renewals!
     *
     */
    function createRenewalQuote($ordheadID)
    {
        $this->setMethodName('createRenewalQuote');
        if ($ordheadID == '') {
            $this->raiseError('ordheadID not passed');
        }
        if (!$this->getOrderWithCustomerName($ordheadID, $dsOrdhead, $dsOrdline, $dsContact)) {
            $this->raiseError('Quote not found');
        }
        $dbeOrdline = new DBEOrdline($this);
        $dbeOrdhead = new DBEOrdhead($this);
        $dsOrdhead->setPK(0);
        $dsOrdhead->fetchNext();

        $originalNo = $dsOrdhead->getValue('ordheadID');
        $dsOrdhead->setUpdateModeUpdate();
        $dsOrdhead->setValue('ordheadID', 0);
        $dsOrdhead->setValue('type', 'Q');
        $dsOrdhead->setValue('originalOrdheadID', $originalNo);
        $dsOrdhead->setValue('date', date('Y-m-d'));
        $dsOrdhead->post();
        $this->updateDataaccessObject($dsOrdhead, $dbeOrdhead);    // create new order header
        $newOrdheadID = $dsOrdhead->getValue('ordheadID');
        // Add selected lines to new order
        $sequenceNo = 0;
        $dsNewOrdline = new DataSet($this);
        $dsNewOrdline->copyColumnsFrom($dsOrdline);
        while ($dsOrdline->fetchNext()) {
            $sequenceNo++;
            $dsNewOrdline->setUpdateModeInsert();
            $dsNewOrdline->row = $dsOrdline->row;
            $dsNewOrdline->setValue('ordheadID', $newOrdheadID);
            $dsNewOrdline->setValue('sequenceNo', $sequenceNo);
            $dsNewOrdline->setValue('qtyDespatched', 0);
            $dsNewOrdline->setValue('qtyLastDespatched', 0);
            $dsNewOrdline->post();
        }
        $dbeOrdline->replicate($dsNewOrdline);
        return $newOrdheadID;
    }

    /**
     * Delete multiple Order Lines
     */
    function deleteLines($ordheadID, &$dsSelectedOrderLine)
    {
        $this->setMethodName('deleteLines');
        if ($ordheadID == '') {
            $this->raiseError('ordheadID not passed');
        }
        if (!is_a($dsSelectedOrderLine, 'DataSet')) {
            $this->raiseError('orderLines object not passed');
        }
        if (!$this->getOrderWithCustomerName($ordheadID, $dsOrdhead, $dsOrdline, $dsContact)) {
            $this->raiseError('Order/Quote not found');
        }
        $dsSelectedOrderLine->initialise();
        /*
            The reason for deletedCount is that, as a line is deleted, all the sequenceNos of lines beyond it are decreased by
            one. Therefore, we need to apply this adjustment to any subsequent deletions.
        */
        $deletedCount = 0;
        while ($dsSelectedOrderLine->fetchNext()) {
            $this->deleteOrderLine($ordheadID, $dsSelectedOrderLine->getValue('sequenceNo') - $deletedCount);
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
        if ($ordheadID == '') {
            $this->raiseError('ordheadID not passed');
        }
        $dbeOrdhead = new DBEOrdhead($this);
        if (!$dbeOrdhead->getRow($ordheadID)) {
            $this->raiseError('order not found');
        }
        $dbeOrdhead->setUpdateModeUpdate();
        $dbeOrdhead->setValue('custPORef', $custPORef);
        $dbeOrdhead->setValue('paymentTermsID', $paymentTermsID);
        $dbeOrdhead->setValue('partInvoice', $partInvoice);
        $dbeOrdhead->setValue('addItem', $addItem);
        $dbeOrdhead->post();
    }

    function updateServiceRequestDetails(
        $ordheadID,
        $serviceRequestCustomerItemID,
        $serviceRequestPriority,
        $serviceRequestText
    )
    {
        if ($ordheadID == '') {
            $this->raiseError('ordheadID not passed');
        }
        $dbeOrdhead = new DBEOrdhead($this);
        if (!$dbeOrdhead->getRow($ordheadID)) {
            $this->raiseError('order not found');
        }
        $dbeOrdhead->setUpdateModeUpdate();
        $dbeOrdhead->setValue('serviceRequestCustomerItemID', $serviceRequestCustomerItemID);
        $dbeOrdhead->setValue('serviceRequestPriority', $serviceRequestPriority);
        $dbeOrdhead->setValue('serviceRequestText', $serviceRequestText);
        $dbeOrdhead->post();
    }

    function deleteServiceRequestDetails($ordheadID)
    {
        if ($ordheadID == '') {
            $this->raiseError('ordheadID not passed');
        }
        $dbeOrdhead = new DBEOrdhead($this);
        if (!$dbeOrdhead->getRow($ordheadID)) {
            $this->raiseError('order not found');
        }
        $dbeOrdhead->setUpdateModeUpdate();
        $dbeOrdhead->setValue('serviceRequestCustomerItemID', '');
        $dbeOrdhead->setValue('servicePriority', '');
        $dbeOrdhead->setValue('serviceRequestText', '');
        $dbeOrdhead->post();
    }

    /**
     * get list of order lines for given order
     * amalgamate same item lines onto one
     * sort by supplier/sequence no
     * exclude comment lines
     */
    function getOrderItemsForPO($ordheadID, &$dsOrdline)
    {
        if ($ordheadID == '') {
            $this->raiseError('ordheadID not passed');
        }
        $dbeOrdhead = new DBEOrdhead($this);
        if (!$dbeOrdhead->getRow($ordheadID)) {
            $this->raiseError('order not found');
        }
        $dbeOrdlinePO = new DBEOrdlinePO($this);
        $dbeOrdlinePO->getRows($ordheadID);
        return ($this->getData($dbeOrdlinePO, $dsOrdline));
    }

    function countPurchaseOrders($ordheadID)
    {
        $this->setMethodName('countPurchaseOrders');
        $dbePorhead = new DBEPorhead($this);
        $dbePorhead->setValue('ordheadID', $ordheadID);
        return ($dbePorhead->countRowsByColumn('ordheadID'));
    }

    function countLinkedServiceRequests($ordheadID)
    {
        $this->setMethodName('countLinkedServiceRequests');
        $dbeProblem = new DBEProblem($this);
        $dbeProblem->setValue('linkedSalesOrderID', $ordheadID);
        return ($dbeProblem->countRowsByColumn('linkedSalesOrderID'));
    }

    function getLinkedServiceRequestID($ordheadID)
    {
        $this->setMethodName('getLinkedServiceRequestID');
        $dbeProblem = new DBEProblem($this);
        $dbeProblem->setValue('linkedSalesOrderID', $ordheadID);
        $dbeProblem->getRowByColumn('linkedSalesOrderID');
        return $dbeProblem->getValue('problemID');
    }

    /**
     * Update all sales order lines with given qty, cost and sale values
     * @parameter dataset $dsOrdline dataset
     * @return bool : Success
     * @access public
     */
    function updateOrderLineValues($ordheadID, &$dsOrdline)
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
            $dbeOrdline->setValue('ordheadID', $ordheadID);
            $dbeOrdline->setValue('sequenceNo', $dsOrdline->getValue('sequenceNo'));
            $dbeOrdline->getRow();
            $dbeOrdline->setValue('qtyOrdered', $dsOrdline->getValue('qtyOrdered'));
            $dbeOrdline->setValue('curUnitSale', $dsOrdline->getValue('curUnitSale'));
            $dbeOrdline->setValue('curUnitCost', $dsOrdline->getValue('curUnitCost'));
            $dbeOrdline->setValue('curUnitCost', $dsOrdline->getValue('curUnitCost'));
            $dbeOrdline->setValue('curTotalCost',
                                  $dsOrdline->getValue('curUnitCost') * $dsOrdline->getValue('qtyOrdered')
            );
            $dbeOrdline->setValue('curTotalSale',
                                  $dsOrdline->getValue('curUnitSale') * $dsOrdline->getValue('qtyOrdered')
            );
            // this is to get around a bug I just found where the string does not get escaped!!!
            $dbeOrdline->setValue('description', $dbeOrdline->getValue('description'));
            $dbeOrdline->updateRow();
        }
        $dbeOrdhead->setUpdatedTime();
    }

    /**
     * Called from BUPurchaseInv in order to force direct delivery sales order to completed when
     * all purchase orders have been authorised.
     */
    function setStatusCompleted($ordheadID)
    {
        $this->setMethodName('setStatusCompleted');
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdhead->setPKValue($ordheadID);
        $dbeOrdhead->setStatusCompleted();
        $dbeOrdline = new DBEOrdline($this);
        $dbeOrdline->setValue('ordheadID', $ordheadID);
//			$dbeOrdline->setRowsToDespatched();
        return TRUE;
    }

    /**
     * copy lines from one sales order and paste them to the end of another
     */
    function pasteLinesFromOrder($fromOrdheadID, $toOrdheadID, $keepRenewals = false, $sequenceNo = false)
    {
        $this->setMethodName('pasteLinesFromOrder');
        $dbeFromOrdline = new DBEOrdline($this);
        $colCount = $dbeFromOrdline->colCount();
        $dbeToOrdline = new DBEOrdline($this);

        $dbeFromOrdline->setValue('ordheadID', $fromOrdheadID);
        $dbeFromOrdline->getRowsByColumn('ordheadID', 'sequenceNo');

        $dbeToOrdline->setValue('ordheadID', $toOrdheadID);
        $dbeToOrdline->getRowsByColumn('ordheadID', 'sequenceNo');
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
                $dbeToOrdline->setValueNoCheckByColumnNumber($i, $dbeFromOrdline->getValueNoCheckByColumnNumber($i));
            }
            $dbeToOrdline->setValue('ordheadID', $toOrdheadID);
            $dbeToOrdline->setValue('qtyDespatched', 0);
            $dbeToOrdline->setValue('qtyLastDespatched', 0);
            $dbeToOrdline->setValue('sequenceNo', $sequenceNo);
            if (!$keepRenewals) {
                $dbeToOrdline->setValue('renewalCustomerItemID', 0);
            }
            $dbeToOrdline->insertRow();
        }
        return TRUE;
    }

    public function getSalesUsers(&$dsResults)
    {
        $dbeUser = new DBEUser($this);
        $dbeUser->getRowsInGroup('sales');
        return ($this->getData($dbeUser, $dsResults));

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
        Get a list of existing ordlinenos
        */
        $statement =
            "SELECT
        GROUP_CONCAT(`odl_ordlineno`) AS ordlinenos

      FROM
        ordline

      WHERE
        odl_ordno = " . $ordheadID;

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

        GROUP BY
          odl_desc, odl_e_unit, odl_d_unit
          
        ORDER BY odl_item_no";

        $db->query($statement);
        /*
        Remove original order lines
        */
        $statement =
            "DELETE FROM
        ordline

      WHERE
        odl_ordlineno IN (" . $oldOrdlinenos . ")";

        $db->query($statement);
    }

}// End of class
?>