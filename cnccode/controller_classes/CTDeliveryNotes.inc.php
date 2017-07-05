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
require_once($cfg["path_gc"]."/BaseObject.inc.php");
class CTDeliveryNotes extends BaseObject{
	function CTDeliveryNotes(&$owner, $ordheadID, &$buDespatch){
		$this->constructor($owner, $ordheadID, $buDespatch);
	}
	function constructor(&$owner, $ordheadID, &$buDespatch){
		parent::BaseObject($owner);
		$this->_buDespatch = $buDespatch;
		$this->_ordheadID = $ordheadID;
	}
	function execute()
	{
		$this->_buDespatch->getDeliveryNotesByOrdheadID($this->_ordheadID, $dsDeliveryNote);
		$dsDeliveryNote->initialise();
		if($dsDeliveryNote->fetchNext()){
			$this->owner->template->set_block('DespatchDisplayNotes','noteBlock', 'notes');
			do{
				$displayNoteDocURL =
					$this->owner->buildLink(
						CTCNC_PAGE_DESPATCH,
						array(
							'action'=>CTCNC_ACT_DISPLAY_DEL_NOTE_DOC,
							'deliveryNoteID' => $dsDeliveryNote->getValue("deliveryNoteID")
						)
					);
				$dateTime = date("j/n/Y H:i:s",strtotime($dsDeliveryNote->getValue("dateTime")));
				$this->owner->template->set_var(
					array(
						'noteNo' => $dsDeliveryNote->getValue("noteNo"),
						'dateTime' => $dateTime,
						'displayNoteDocURL' => $displayNoteDocURL
					)
				);
				$this->owner->template->parse('notes', 'noteBlock', true);
			}while ($dsDeliveryNote->fetchNext());
			$this->owner->template->parse('despatchDisplayNotes', 	'DespatchDisplayNotes', true);
		}
	}
}// end of class
?>