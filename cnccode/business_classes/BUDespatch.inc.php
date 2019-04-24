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
require_once($cfg["path_dbe"] . "/DBEDeliveryMethod.inc.php");
require_once($cfg["path_dbe"] . "/DBEPaymentTerms.inc.php");
require_once($cfg["path_dbe"] . "/DBEDeliveryNote.inc.php");
require_once($cfg["path_dbe"] . "/DBEOrdline.inc.php");
require_once($cfg["path_dbe"] . "/DBEPorhead.inc.php");

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
     * @param $customerID
     * @param $ordheadID
     * @param $dsResults
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
        if ($ordheadID) {
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

    /**
     * @param $ordheadID
     * @param DataSet $dsOrdline
     * @return bool
     */
    function getLinesByID($ordheadID,
                          &$dsOrdline
    )
    {
        $this->setMethodName('getLinesByID');
        $ret = FALSE;
        if (!$ordheadID) {
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
     * @param DataSet $dsOrdline
     * @param DSForm $dsDespatch
     * @return bool
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

    /**
     * @param $ordheadID
     * @param DataSet|DBEJOrdline $dsOrdline
     * @param DSForm $dsDespatch
     */
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

    /**
     * @param $ordheadID
     * @param DataSet $dsDeliveryNote
     * @return bool
     */
    function getDeliveryNotesByOrdheadID($ordheadID,
                                         &$dsDeliveryNote
    )
    {
        $this->setMethodName('getDeliveryNotesByOrdheadID');
        if (!$ordheadID) {
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
            DBEDeliveryNote::dateTime,
            SORT_DESC
        );
        return $ret;
    }

    function getDeliveryNoteByID($ID,
                                 &$dsDeliveryNote
    )
    {
        $this->setMethodName('getDeliveryNoteByID');
        if (!$ID) {
            $this->raiseError('deliveryNoteID not passed');
        }
        $dbeDeliveryNote = new DBEDeliveryNote($this);
        return ($this->getDatasetByPK(
            $ID,
            $dbeDeliveryNote,
            $dsDeliveryNote
        ));
    }

    function countNonReceivedPOsByOrdheadID($ID)
    {
        $dbePorhead = new DBEPorhead($this);

        return $dbePorhead->countNonReceievedRowsByOrdheadID($ID);
    }

}
