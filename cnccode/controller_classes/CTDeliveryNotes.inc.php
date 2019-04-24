<?php
/**
 * Delivery notes controller class
 * NOTE: This isn't REALLY a controller class but is used so that CTSalesOrder and CTDespatch
 * can share the code for displaying the list of delivery notes
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/BaseObject.inc.php");

class CTDeliveryNotes extends BaseObject
{
    public $_buDespatch;
    public $_ordheadID;
    /** @var CTCNC */
    public $owner;

    /**
     * CTDeliveryNotes constructor.
     * @param CTCNC $owner
     * @param $ordheadID
     * @param BUDespatch $buDespatch
     */
    function __construct(&$owner, $ordheadID, &$buDespatch)
    {
        parent::__construct($owner);
        $this->owner = $owner;
        $this->_buDespatch = $buDespatch;
        $this->_ordheadID = $ordheadID;
    }

    /**
     * @throws Exception
     */
    function execute()
    {
        $dsDeliveryNote = new DataSet($this);
        $this->_buDespatch->getDeliveryNotesByOrdheadID($this->_ordheadID, $dsDeliveryNote);
        $dsDeliveryNote->initialise();
        if ($dsDeliveryNote->fetchNext()) {
            $this->owner->template->set_block('DespatchDisplayNotes', 'noteBlock', 'notes');
            do {
                $displayNoteDocURL = $this->owner->buildLink(
                    CTCNC_PAGE_DESPATCH,
                    array(
                        'action'         => CTCNC_ACT_DISPLAY_DEL_NOTE_DOC,
                        'deliveryNoteID' => $dsDeliveryNote->getValue(DBEDeliveryNote::deliveryNoteID)
                    )
                );
                $dateTime = date("j/n/Y H:i:s", strtotime($dsDeliveryNote->getValue(DBEDeliveryNote::dateTime)));
                $this->owner->template->set_var(
                    array(
                        'noteNo'            => $dsDeliveryNote->getValue(DBEDeliveryNote::noteNo),
                        'dateTime'          => $dateTime,
                        'displayNoteDocURL' => $displayNoteDocURL
                    )
                );
                $this->owner->template->parse('notes', 'noteBlock', true);
            } while ($dsDeliveryNote->fetchNext());
            $this->owner->template->parse('despatchDisplayNotes', 'DespatchDisplayNotes', true);
        }
    }
}
