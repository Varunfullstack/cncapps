<?
/**
* PDF Delivery Note Generation business class
*
* Generates a PDF delivery note.
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg['path_bu'].'/BUPDF.inc.php');
define('BUPDFDEL_NUMBER_OF_LINES', 30);
// print column positions
define('BUPDFDEL_DETAILS_COL', 12);
define('BUPDFDEL_QTY_ORDERED_COL', 159);
define('BUPDFDEL_QTY_DELIVERED_COL', 194);
// box dimensions
define('BUPDFDEL_DETAILS_BOX_WIDTH', 116.5);
define('BUPDFDEL_QTY_ORDERED_BOX_WIDTH', 35);	// used for cost box too
define('BUPDFDEL_DETAILS_BOX_LEFT_EDGE', 11);
define('BUPDFDEL_QTY_ORDERED_BOX_LEFT_EDGE',		// relative to other boxes
	BUPDFDEL_DETAILS_BOX_LEFT_EDGE +
	BUPDFDEL_DETAILS_BOX_WIDTH
);
define('BUPDFDEL_QTY_DELIVERED_BOX_LEFT_EDGE',
	BUPDFDEL_QTY_ORDERED_BOX_LEFT_EDGE +
	BUPDFDEL_QTY_ORDERED_BOX_WIDTH
);
class BUPDFDeliveryNote extends BaseObject{
	var $_buPDF='';					// BUPDF object
	var $_dsOrdhead='';
	var $_dsOrdline='';
	var $_dsDespatch='';
	var $_dsContact='';
	var $_dsDeliveryMethod='';
	var $_noteNo='';
	var $_fullyDespatched='';
	var $_titleLine='';
	/**
	* Constructor
	*
	*/
	function BUPDFDeliveryNote(&$owner, &$dsOrdhead, &$dsOrdline, &$dsDespatch, &$dsContact, &$dsDeliveryMethod, $noteNo, $fullyDespatched){
		$this->constructor($owner, $dsOrdhead, $dsOrdline, $dsDespatch, $dsContact, $dsDeliveryMethod, $noteNo, $fullyDespatched);
	}
	function constructor(&$owner, &$dsOrdhead, &$dsOrdline, &$dsDespatch, &$dsContact, &$dsDeliveryMethod,$noteNo, $fullyDespatched){
		$this->BaseObject($owner);
		$this->setMethodName('constructor');
		$this->_dsOrdhead = $dsOrdhead;
		$this->_dsOrdline = $dsOrdline;
		$this->_dsDespatch = $dsDespatch;
		$this->_dsContact = $dsContact;
		$this->_dsDeliveryMethod = $dsDeliveryMethod;
		$this->_noteNo = $noteNo;
		$this->_fullyDespatched = $fullyDespatched;
	}
	/**
	* Use the parameters passed in constructor to get list of invoices and generate a PDF file on
	* disk.
	* If no invoices are found then return FALSE
	*	@return String PDF disk file name or FALSE
	*/
	function generateFile(){
		$this->_dsOrdhead->initialise();
		$this->_dsOrdhead->fetchNext();
		$pdfFile = DELIVERY_NOTES_DIR.'/'.$this->_dsOrdhead->getValue('ordheadID').'_'.$this->_noteNo.'.pdf';
		$this->_buPDF= new BUPDF(
			$this,
			$pdfFile,
			'CNC',
			date('d/m/Y'),
			'CNC Ltd',
			'Delivery Note',
			'A4'
		);
		$this->produceNote();
		$this->_buPDF->close();
		return $pdfFile;
	}
	function produceNote(){
		// local refs
		$dsOrdhead = & $this->_dsOrdhead;
		$dsOrdline = & $this->_dsOrdline;
		$dsDespatch = & $this->_dsDespatch;
		$dsContact = & $this->_dsContact;
		$dsDeliveryMethod = & $this->_dsDeliveryMethod;
		$this->noteHead();
		$this->_buPDF->CR();
		$lineCount = 0;
		$dsOrdline->initialise();
		$dsDespatch->initialise();
		while ($dsOrdline->fetchNext()){
			$dsDespatch->fetchNext();
			if ($dsDespatch->getValue('qtyToDespatch') == 0) { // nothing on this line
				continue;
			}
			$lineCount ++;
			if ($lineCount > BUPDFDEL_NUMBER_OF_LINES - 4){ // can't be bothered to find out why -4 !
				$this->_buPDF->printStringAt(BUPDFDEL_DETAILS_COL, 'Continued on next page...');
				$this->noteHead();
				$this->_buPDF->printStringAt(BUPDFDEL_DETAILS_COL, '... continued from previous page');
				$this->_buPDF->CR();
				$lineCount = 2;
			}
			if ($dsOrdline->getValue('lineType')=="I"){
				if (
					($dsOrdline->getValue('itemDescription')!='') AND
					($dsOrdline->getValue('stockcat')!='G')
				){
					$this->_buPDF->printStringAt(BUPDFDEL_DETAILS_COL, $dsOrdline->getValue('itemDescription'));
				}
				else{
					$this->_buPDF->printStringAt(BUPDFDEL_DETAILS_COL, $dsOrdline->getValue('description'));
				}
				$this->_buPDF->printStringRJAt(BUPDFDEL_QTY_ORDERED_COL, number_format($dsOrdline->getValue('qtyOrdered'), 2, '.', ','));
				$this->_buPDF->printStringRJAt(BUPDFDEL_QTY_DELIVERED_COL, number_format($dsDespatch->getValue('qtyToDespatch'), 2, '.', ',') );
			}
			else{
				$this->_buPDF->printStringAt(BUPDFDEL_DETAILS_COL, $dsOrdline->getValue('description')); // comment line
			}
			$this->_buPDF->CR();
		}
		// need something conditional about outstanding qty here *****
		$this->_buPDF->moveYTo((BUPDFDEL_NUMBER_OF_LINES - 6.5) * $this->_buPDF->getFontSize());
		if ($this->_fullyDespatched){
			$endMessage = 'Order Completed, Thank You for Your Order.';
		}
		else{
			$endMessage = 'Back Order To Follow, Thank You for Your Order.';
		}
		$this->_buPDF->printStringAt(BUPDFDEL_DETAILS_COL, $endMessage);
		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		$this->_buPDF->moveYTo($this->_titleLine + (BUPDFDEL_NUMBER_OF_LINES * $this->_buPDF->getFontSize()/2));
		$this->_buPDF->box(BUPDFDEL_QTY_ORDERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFDEL_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->box(BUPDFDEL_QTY_DELIVERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFDEL_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->printStringRJAt(BUPDFDEL_QTY_ORDERED_COL, 'Signed');
		$this->_buPDF->CR();
		$this->_buPDF->box(BUPDFDEL_QTY_ORDERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFDEL_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->box(BUPDFDEL_QTY_DELIVERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFDEL_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->printStringRJAt(BUPDFDEL_QTY_ORDERED_COL, 'Print');
		$this->_buPDF->CR();
		$this->_buPDF->box(BUPDFDEL_QTY_ORDERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFDEL_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->box(BUPDFDEL_QTY_DELIVERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFDEL_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->printStringRJAt(BUPDFDEL_QTY_ORDERED_COL, 'Date');
		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		$this->_buPDF->CR();
		$this->_buPDF->CR();
		$this->_buPDF->CR();
		$this->_buPDF->setFontSize(8);
		$this->_buPDF->setFont();
		$this->_buPDF->printString('GOODS REMAIN THE PROPERTY OF COMPUTER & NETWORK CONSULTANTS LTD UNTIL PAID FOR IN FULL');
		$this->_buPDF->endPage();
	}
	/**
	*	Output the invoice header.
	* This gets called once at the start of each page.
	* Where an invoice spans pages it gets called many times for the same invoice.
	*
	* @access private
	*/
	function noteHead(){
		$dsOrdhead = & $this->_dsOrdhead;
		$dsContact = & $this->_dsContact;
		$dsDeliveryMethod = & $this->_dsDeliveryMethod;
		$this->_buPDF->startPage();
//		$this->_buPDF->placeImageAt( $GLOBALS['cfg']['cnclogo_path'], 'JPEG', 90, 110);
    $this->_buPDF->placeImageAt( $GLOBALS['cfg']['cnclogo_path'], 'PNG', 142, 38);

		$this->_buPDF->setFontSize(6);
		$this->_buPDF->setFontFamily(BUPDF_FONT_ARIAL);
		$this->_buPDF->setFont();
		$this->_buPDF->CR();
		$this->_buPDF->CR();
		$this->_buPDF->CR();
		$this->_buPDF->CR();
		$this->_buPDF->CR();
		$this->_buPDF->CR();
		$this->_buPDF->CR();
		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFontSize(20);
		$this->_buPDF->setFont();
		$this->_buPDF->CR();
		$this->_buPDF->printString('Delivery Note');
		$this->_buPDF->setFontSize(10);
		$this->_buPDF->setFont();
		$this->_buPDF->CR();
		$this->_buPDF->CR();
		$firstAddLine = $this->_buPDF->getYPos();	// remember this line no
		$this->_buPDF->printString($dsOrdhead->getValue('customerName'));
		$this->_buPDF->CR();
		$this->_buPDF->setFontSize(8);
		$this->_buPDF->setFont();
		$this->_buPDF->printString($dsOrdhead->getValue('delAdd1'));
		if ($dsOrdhead->getValue('delAdd2')!=''){
			$this->_buPDF->CR();
			$this->_buPDF->printString($dsOrdhead->getValue('delAdd2'));
		}
		if ($dsOrdhead->getValue('delAdd3')!=''){
			$this->_buPDF->CR();
			$this->_buPDF->printString($dsOrdhead->getValue('delAdd3'));
		}
		$this->_buPDF->CR();
		$this->_buPDF->printString($dsOrdhead->getValue('delTown'));
		if ($dsOrdhead->getValue('delCounty')!=''){
			$this->_buPDF->CR();
			$this->_buPDF->printString($dsOrdhead->getValue('delCounty'));
		}
		$this->_buPDF->CR();
		$this->_buPDF->printString($dsOrdhead->getValue('delPostcode'));
		$this->_buPDF->CR();
		$this->_buPDF->CR();
		$this->_buPDF->setFontSize(10);
		$this->_buPDF->setFont();
		$this->_buPDF->printString(
			'F.A.O. '.
			$dsContact->getValue('title').' '.
			$dsContact->getValue('firstName').' '.
			$dsContact->getValue('lastName')
		);
		$faoLine = $this->_buPDF->getYPos();
		$this->_buPDF->moveYTo($firstAddLine);	//move back up the page
		$this->_buPDF->CR();
		$this->_buPDF->box(BUPDFDEL_QTY_ORDERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFDEL_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->box(BUPDFDEL_QTY_DELIVERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFDEL_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->printStringRJAt(BUPDFDEL_QTY_ORDERED_COL, 'Note No');
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringAt(BUPDFDEL_QTY_DELIVERED_BOX_LEFT_EDGE, $dsOrdhead->getValue('ordheadID').'/'.$this->_noteNo);
		$this->_buPDF->CR();
		$this->_buPDF->box(BUPDFDEL_QTY_ORDERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFDEL_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->box(BUPDFDEL_QTY_DELIVERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFDEL_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringRJAt(BUPDFDEL_QTY_ORDERED_COL, 'Date');
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringAt(BUPDFDEL_QTY_DELIVERED_BOX_LEFT_EDGE, date('d/m/Y'));
		$this->_buPDF->CR();
		$this->_buPDF->box(BUPDFDEL_QTY_ORDERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFDEL_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->box(BUPDFDEL_QTY_DELIVERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFDEL_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringRJAt(BUPDFDEL_QTY_ORDERED_COL, 'Delivery');
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringAt(BUPDFDEL_QTY_DELIVERED_BOX_LEFT_EDGE, $dsDeliveryMethod->getValue('description'));
		$this->_buPDF->CR();
		$this->_buPDF->box(BUPDFDEL_QTY_ORDERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFDEL_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->box(BUPDFDEL_QTY_DELIVERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFDEL_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringRJAt(BUPDFDEL_QTY_ORDERED_COL, 'CNC Order No');
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringAt(BUPDFDEL_QTY_DELIVERED_BOX_LEFT_EDGE, $dsOrdhead->getValue('customerID') . '/' . $dsOrdhead->getValue('ordheadID'));
		$this->_buPDF->CR();
		$this->_buPDF->box(BUPDFDEL_QTY_ORDERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFDEL_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->box(BUPDFDEL_QTY_DELIVERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFDEL_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->CR();
		$this->_buPDF->box(BUPDFDEL_QTY_ORDERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFDEL_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->box(BUPDFDEL_QTY_DELIVERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFDEL_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringRJAt(BUPDFDEL_QTY_ORDERED_COL, 'Customer Order');
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringAt(BUPDFDEL_QTY_DELIVERED_BOX_LEFT_EDGE, substr($dsOrdhead->getValue('custPORef'),0,17));
		$this->_buPDF->CR();
		// empty box
		$this->_buPDF->box(BUPDFDEL_QTY_ORDERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFDEL_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->box(BUPDFDEL_QTY_DELIVERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFDEL_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->CR();
		$this->_titleLine = $this->_buPDF->getYPos();
		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		// box around all detail headings
		$this->_buPDF->box(
			BUPDFDEL_DETAILS_BOX_LEFT_EDGE,
			$this->_buPDF->getYPos(),
			BUPDFDEL_DETAILS_BOX_WIDTH + (BUPDFDEL_QTY_ORDERED_BOX_WIDTH * 2),
			$this->_buPDF->getFontSize()/2
		);
		// Around details
		$this->_buPDF->box(
			BUPDFDEL_DETAILS_BOX_LEFT_EDGE,
			$this->_buPDF->getYPos(),
			BUPDFDEL_DETAILS_BOX_WIDTH,
			(BUPDFDEL_NUMBER_OF_LINES)*($this->_buPDF->getFontSize()/2)
		);
		// Box around the Unit Price
		$this->_buPDF->box(
			BUPDFDEL_DETAILS_BOX_LEFT_EDGE + BUPDFDEL_DETAILS_BOX_WIDTH,
			$this->_buPDF->getYPos(),
			BUPDFDEL_QTY_ORDERED_BOX_WIDTH,
			(BUPDFDEL_NUMBER_OF_LINES)*($this->_buPDF->getFontSize()/2)
		);
		// Box around the Cost
		$this->_buPDF->box(
			BUPDFDEL_DETAILS_BOX_LEFT_EDGE + BUPDFDEL_DETAILS_BOX_WIDTH + BUPDFDEL_QTY_ORDERED_BOX_WIDTH,
			$this->_buPDF->getYPos(),
			BUPDFDEL_QTY_ORDERED_BOX_WIDTH,
			(BUPDFDEL_NUMBER_OF_LINES)*($this->_buPDF->getFontSize()/2)
		);
		$this->_buPDF->printStringAt(BUPDFDEL_DETAILS_COL, 'Details');
		$this->_buPDF->printStringRJAt(BUPDFDEL_QTY_ORDERED_COL - 5, 'Qty Ordered');
		$this->_buPDF->printStringRJAt(BUPDFDEL_QTY_DELIVERED_COL - 6, 'Qty Delivered');
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();
		$this->_buPDF->CR();
		$grandTotal=0;
	}
}// End of class
?>