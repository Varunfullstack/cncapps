<?php /**
 * Despatch business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BUSalesOrder.inc.php");
require_once($cfg["path_bu"] . "/BUInvoice.inc.php");
require_once($cfg["path_bu"] . "/BUActivity.inc.php");
require_once($cfg["path_bu"] . "/BUContact.inc.php");
require_once($cfg["path_bu"] . "/BUPDFDeliveryNote.inc.php");
require_once($cfg["path_dbe"] . "/DBEDeliveryMethod.inc.php");
require_once($cfg["path_dbe"] . "/DBEPaymentTerms.inc.php");
require_once($cfg["path_dbe"] . "/DBEDeliveryNote.inc.php");
require_once($cfg["path_dbe"] . "/DBEOrdline.inc.php");

class BUDespatch extends Business
{
    const despatchSequenceNo = 'sequenceNo';
    const despatchQtyToDespatch = 'qtyToDespatch';

    /** @var DBEOrdline */
    public $dbeOrdline;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
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
        &$dsResults
    )
    {
        $this->setMethodName('search');
        $dbeJOrdhead = new DBEJOrdhead($this);
        if ($ordheadID != '') {
            $ret = $dbeJOrdhead->getDespatchRowByOrdheadID($ordheadID);
        } else {
            $ret = $dbeJOrdhead->getDespatchRows($customerID);
        }
        $this->getData(
            $dbeJOrdhead,
            $dsResults
        );
        return $ret;
    }

    function getLinesByID($ordheadID,
                          &$dsOrdline
    )
    {
        $this->setMethodName('getLinesByID');
        $ret = FALSE;
        if ($ordheadID == '') {
            $this->raiseError('ordheadID not passed');
        } else {
            $dbeJOrdline = new DBEJOrdline($this);
            $dbeJOrdline->setValue(
                DBEJOrdline::ordheadID,
                $ordheadID
            );
            $dbeJOrdline->getRowsByColumn(DBEJOrdline::ordheadID);
            $ret = ($this->getData(
                $dbeJOrdline,
                $dsOrdline
            ));
            $dsOrdline->columnSort(DBEOrdline::sequenceNo);
        }
        return $ret;
    }

    /**
     * Return a dataset of despatch qtys for this set of order lines
     */
    function getInitialDespatchQtys(&$dsOrdline,
                                    &$dsDespatch
    )
    {
        $this->setMethodName('getInitialDespatchQtys');
        $this->initialiseDespatchDataset($dsDespatch);
        $dsOrdline->initialise();
        while ($dsOrdline->fetchNext()) {
            $dsDespatch->setUpdateModeInsert();
            $dsDespatch->setValue(
                self::despatchSequenceNo,
                $dsOrdline->getValue(DBEJOrdline::sequenceNo)
            );
            $dsDespatch->setValue(
                self::despatchQtyToDespatch,
                0
            );    // comment line
            $dsDespatch->post();
        }
        return TRUE;
    }

    function initialiseDespatchDataset(&$dsDespatch)
    {
        $dsDespatch = new DataSet($this);
        $dsDespatch->addColumn(
            self::despatchSequenceNo,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $dsDespatch->addColumn(
            self::despatchQtyToDespatch,
            DA_FLOAT,
            DA_ALLOW_NULL
        );
    }

    function getAllDeliveryMethods(&$dsResults)
    {
        $this->setMethodName('getAllDeliveryMethods');
        $dbeDeliveryMethod = new DBEDeliveryMethod($this);
        $dbeDeliveryMethod->getRows();
        return ($this->getData(
            $dbeDeliveryMethod,
            $dsResults
        ));
    }

    function despatch($ordheadID,
                      $deliveryMethodID,
                      &$dsDespatch,
                      $onlyCreateDespatchNote = true
    )
    {
        $this->setMethodName('despatch');
        $this->dbeOrdline = new DBEOrdline($this);
        $dsDespatch->initialise();
        $buSalesOrder = new BUSalesOrder($this);
        $buSalesOrder->getOrdheadByID(
            $ordheadID,
            $dsOrdhead
        );
        $partInvoice = ($dsOrdhead->getValue(DBEJOrdhead::partInvoice) == 'Y');
        $this->getLinesByID(
            $ordheadID,
            $dsOrdline
        );

// Check Whether The Order Will Be Complete Following This Despatch
        $fullyDespatched = TRUE;
        while ($dsDespatch->fetchNext()) {
            $dsOrdline->fetchNext();
            if ($dsOrdline->getValue(DBEOrdline::lineType) == 'I') {
                $qtyOutstanding = $dsOrdline->getValue(DBEOrdline::qtyOrdered) - $dsOrdline->getValue(
                        DBEOrdline::qtyDespatched
                    );
                if ($qtyOutstanding - $dsDespatch->getValue(self::despatchQtyToDespatch) != 0) {
                    $fullyDespatched = FALSE;
                    break;
                }
            }
        }
        /*
        * Update ordline (except if $onlyCreateDespatchNote )
        */
        $ordlineUpdated = FALSE;
        $dsDespatch->initialise();
        $dsOrdline->initialise();

        while ($dsDespatch->fetchNext()) {

            $dsOrdline->fetchNext();

            $qtyToDespatch = $dsDespatch->getValue(self::despatchQtyToDespatch);

            if ($qtyToDespatch <= 0) {
                continue;
            }
            if (
                $dsOrdline->getValue(DBEOrdline::lineType) == 'I' // exclude comment lines
            ) {

                if (!$onlyCreateDespatchNote) {

                    $this->updateOrdline(
                        $ordheadID,
                        $dsOrdline,
                        $dsDespatch
                    );
                }

                $ordlineUpdated = TRUE;
            }
        }
        /*
        * update order status and create invoices(optionally) if order updated and we are not
        * just creating a despactch note.
        */
        if (!$onlyCreateDespatchNote && $ordlineUpdated) {

            $dbeOrdhead = new DBEOrdhead($this);
            $dbeOrdhead->getRow($ordheadID);
            if ($fullyDespatched) {
                $dbeOrdhead->setValue(
                    DBEOrdhead::type,
                    'C'
                );
            } else {
                $dbeOrdhead->setValue(
                    DBEOrdhead::type,
                    'P'
                );
            }
            $dbeOrdhead->updateRow();
            unset($dbeOrdhead);

            $invheadID = false;

            // do we generate invoices for these payment terms?
            $dbePaymentTerms = new DBEPaymentTerms($this);
            $dbePaymentTerms->getRow($dsOrdhead->getValue(DBEJOrdhead::paymentTermsID));

            if ($dbePaymentTerms->getValue(DBEPaymentTerms::generateInvoiceFlag) == 'Y') {
                // Last despatch for this non part-invoice order so generate invoice for whole
                if (!$partInvoice AND $fullyDespatched) {
                    $buInvoice = new BUInvoice($this);
                    $invheadID = $buInvoice->createInvoiceFromOrder(
                        $dsOrdhead,
                        $dsOrdline
                    );
                    unset($buInvoice);
                }
                if ($partInvoice) {
                    $buInvoice = new BUInvoice($this);
                    $invheadID = $buInvoice->createInvoiceFromDespatch(
                        $dsOrdhead,
                        $dsOrdline,
                        $dsDespatch
                    );
                    unset($buInvoice);
                }
            } // end

        } // !$onlyCreateDespatchNote && $ordlineUpdated

        /*
        * If the item despatched is a GSC contract/topup then update the GSC balance on the customer table
        */
        if (
            !$onlyCreateDespatchNote &&
            (
                $dsOrdline->getValue(DBEOrdline::itemID) == CONFIG_DEF_PREPAY_ITEMID OR
                $dsOrdline->getValue(DBEOrdline::itemID) == CONFIG_DEF_PREPAY_TOPUP_ITEMID)
        ) {
            // create an activity row

            $buActivity = new BUActivity($this);
            $buActivity->createTopUpActivity(
                $dsOrdhead->getValue(DBEOrdhead::customerID),
                $dsOrdline->getValue(DBEOrdline::curTotalSale),
                $invheadID
            );
        }

        $dbeDeliveryMethod = new DBEDeliveryMethod($this);
        $this->getDatasetByPK(
            $deliveryMethodID,
            $dbeDeliveryMethod,
            $dsDeliveryMethod
        );

        $deliveryNoteFile = FALSE;

        if ($dsDeliveryMethod->getValue(DBEDeliveryMethod::sendNoteFlag) == 'Y') {

            if ($ordlineUpdated) {

                $buContact = new BUContact($this);

                $buContact->getContactByID(
                    $dsOrdhead->getValue(DBEOrdhead::delContactID),
                    $dsContact
                );

                $deliveryNoteFile = $this->createDeliveryNote(
                    $dsOrdhead,
                    $dsOrdline,
                    $dsDespatch,
                    $dsContact,
                    $dsDeliveryMethod,
                    $fullyDespatched
                );
            }
        }
        unset($dbeDeliveryMethod);
        return $deliveryNoteFile;
    }

    function createDeliveryNote(
        &$dsOrdhead,
        &$dsOrdline,
        &$dsDespatch,
        &$dsContact,
        &$dsDeliveryMethod,
        $fullyDespatched
    )
    {
        // create record on delivery note table
        $dbeDeliveryNote = new DBEDeliveryNote($this);
        $dbeDeliveryNote->setValue(
            DBEDeliveryNote::ordheadID,
            $dsOrdhead->getValue(DBEOrdhead::ordheadID)
        );
        $noteNo = $dbeDeliveryNote->getNextNoteNo();

        $dbeDeliveryNote->setPKValue(null);
        $dbeDeliveryNote->setValue(
            DBEDeliveryNote::ordheadID,
            $dsOrdhead->getValue(DBEOrdhead::ordheadID)
        );
        $dbeDeliveryNote->setValue(
            DBEDeliveryNote::noteNo,
            $noteNo
        );
        $dbeDeliveryNote->setValue(
            DBEDeliveryNote::dateTime,
            date('Y-m-d H:i:s')
        );
        $dbeDeliveryNote->insertRow();
        $buPDFDeliveryNote = new BUPDFDeliveryNote(
            $this,
            $dsOrdhead,
            $dsOrdline,
            $dsDespatch,
            $dsContact,
            $dsDeliveryMethod,
            $noteNo,
            $fullyDespatched
        );
        return ($buPDFDeliveryNote->generateFile()); // the file path is returned
    }

    function updateOrdline($ordheadID,
                           &$dsOrdline,
                           &$dsDespatch
    )
    {
        $dbeOrdline = &$this->dbeOrdline;
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
            DBEOrdline::qtyDespatched,
            $dsOrdline->getValue(DBEOrdline::qtyDespatched) + $dsDespatch->getValue(self::despatchQtyToDespatch)
        );
        $dbeOrdline->setValue(
            DBEOrdline::qtyLastDespatched,
            $dsDespatch->getValue(self::despatchQtyToDespatch)
        );
        $dbeOrdline->setValue(
            DBEOrdline::description,
            $dsOrdline->getValue(DBEOrdline::description)
        );
        $dbeOrdline->updateRow();
    }

    function getDeliveryNotesByOrdheadID($ordheadID,
                                         &$dsDeliveryNote
    )
    {
        $this->setMethodName('getDeliveryNotesByOrdheadID');
        $ret = FALSE;
        if ($ordheadID == '') {
            $this->raiseError('ordheadID not passed');
        }
        $dbeDeliveryNote = new DBEDeliveryNote($this);
        $dbeDeliveryNote->setValue(
            DBEDeliveryNote::ordheadID,
            $ordheadID
        );
        $dbeDeliveryNote->getRowsByColumn(DBEDeliveryNote::ordheadID);
        $ret = ($this->getData(
            $dbeDeliveryNote,
            $dsDeliveryNote
        ));
        $dsDeliveryNote->columnSort(
            'dateTime',
            SORT_DESC
        );
        return $ret;
    }

    function getDeliveryNoteByID($ID,
                                 &$dsDeliveryNote
    )
    {
        $this->setMethodName('getDeliveryNoteByID');
        if ($ID == '') {
            $this->raiseError('deliveryNoteID not passed');
        }
        $dbeDeliveryNote = new DBEDeliveryNote($this);
        return ($this->getDatasetByPK(
            $ID,
            $dbeDeliveryNote,
            $dsDeliveryNote
        ));
    }

    function countNonReceievedPOsByOrdheadID($ID)
    {
        $dbePorhead = new DBEPorhead($this);
        require_once($GLOBALS["cfg"]["path_dbe"] . "/DBEPorhead.inc.php");
        return $dbePorhead->countNonReceievedRowsByOrdheadID($ID);
    }

}// End of class
?>