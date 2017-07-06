<?
/**
* PDF System Build Report business class
*
* Generates a PDF delivery note.
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg['path_bu'].'/BUPDF.inc.php');
define('BUPDFSYS_NUMBER_OF_LINES', 30);
// print column positions
define('BUPDFSYS_DETAILS_COL', 12);
define('BUPDFSYS_QTY_ORDERED_COL', 159);
define('BUPDFSYS_QTY_DELIVERED_COL', 194);
// box dimensions
define('BUPDFSYS_DETAILS_BOX_WIDTH', 116.5);
define('BUPDFSYS_QTY_ORDERED_BOX_WIDTH', 35);	// used for cost box too
define('BUPDFSYS_DETAILS_BOX_LEFT_EDGE', 11);
define('BUPDFSYS_QTY_ORDERED_BOX_LEFT_EDGE',		// relative to other boxes
	BUPDFSYS_DETAILS_BOX_LEFT_EDGE +
	BUPDFSYS_DETAILS_BOX_WIDTH
);
define('BUPDFSYS_QTY_DELIVERED_BOX_LEFT_EDGE',
	BUPDFSYS_QTY_ORDERED_BOX_LEFT_EDGE +
	BUPDFSYS_QTY_ORDERED_BOX_WIDTH
);
class BUPDFSystemBuild extends BaseObject{
	var $_buPDF='';					// BUPDF object
	var $_dsOrdhead='';
	var $_dsOrdline='';
	var $_dsContact='';
	/**
	* Constructor
	*
	*/
	function BUPDFSystemBuild(&$owner, &$dsOrdhead, &$dsOrdline, $dsContact){
		$this->constructor($owner, $dsOrdhead, $dsOrdline, $dsContact);
	}
	function constructor(&$owner, &$dsOrdhead, &$dsOrdline, &$dsContact){
		$this->BaseObject($owner);
		$this->setMethodName('constructor');
		$this->_dsOrdhead = $dsOrdhead;
		$this->_dsOrdline = $dsOrdline;
		$this->_dsContact = $dsContact;
	}
	/**
	* Generate the report file
	*	@return String PDF disk file name or FALSE
	*/
	function generateFile(){
		$this->_dsOrdhead->initialise();
		$this->_dsOrdhead->fetchNext();
		$pdfFile = tempnam('/tmp', 'SYS');			// temporary disk file
		$this->_buPDF= new BUPDF(
			$this,
			$pdfFile,
			'CNC',
			date('d/m/Y'),
			'CNC Ltd',
			'System Build Report',
			'A4'
		);
		$this->generateReport();
		$this->_buPDF->close();
		return $pdfFile;
	}
	function generateReport(){
		// local refs
		$dsOrdhead = & $this->_dsOrdhead;
		$dsOrdline = & $this->_dsOrdline;
		$dsContact = & $this->_dsContact;
		$this->reportHead();
		$this->_buPDF->CR();
		$lineCount = 0;
		$dsOrdline->initialise();
		while ($dsOrdline->fetchNext()){
			$lineCount ++;
			if ($lineCount > BUPDFSYS_NUMBER_OF_LINES - 4){
				$this->_buPDF->printStringAt(BUPDFSYS_DETAILS_COL, 'Continued on next page...');
				$this->reportHead();
				$this->_buPDF->printStringAt(BUPDFSYS_DETAILS_COL, '... continued from previous page');
				$this->_buPDF->CR();
				$lineCount = 2;
			}
			if ($dsOrdline->getValue('lineType')=="I"){
				if ($dsOrdline->getValue('itemDescription')!=''){
					$this->_buPDF->printStringAt(BUPDFSYS_DETAILS_COL, $dsOrdline->getValue('itemDescription'));
				}
				else{
					$this->_buPDF->printStringAt(BUPDFSYS_DETAILS_COL, $dsOrdline->getValue('description'));
				}
				$this->_buPDF->printStringRJAt(BUPDFSYS_QTY_ORDERED_COL, number_format($dsOrdline->getValue('qtyOrdered'), 2, '.', ','));
			}
			else{
				$this->_buPDF->printStringAt(BUPDFSYS_DETAILS_COL, $dsOrdline->getValue('description')); // comment line
			}
			$this->_buPDF->CR();
		}
		// need something conditional about outstanding qty here *****
		$this->_buPDF->moveYTo((BUPDFSYS_NUMBER_OF_LINES - 6.5) * $this->_buPDF->getFontSize());
		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		$this->_buPDF->moveYTo($this->_titleLine + (BUPDFSYS_NUMBER_OF_LINES * $this->_buPDF->getFontSize()/2));
		$this->_buPDF->box(BUPDFSYS_QTY_ORDERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFSYS_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->box(BUPDFSYS_QTY_DELIVERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFSYS_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->printStringRJAt(BUPDFSYS_QTY_ORDERED_COL, 'Completed By');
		$this->_buPDF->CR();
		$this->_buPDF->box(BUPDFSYS_QTY_ORDERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFSYS_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->box(BUPDFSYS_QTY_DELIVERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFSYS_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->printStringRJAt(BUPDFSYS_QTY_ORDERED_COL, 'Date');
		$this->_buPDF->CR();
		$this->_buPDF->box(BUPDFSYS_QTY_ORDERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFSYS_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->box(BUPDFSYS_QTY_DELIVERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFSYS_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->printStringRJAt(BUPDFSYS_QTY_ORDERED_COL, 'Despatched By');
		$this->_buPDF->CR();
		$this->_buPDF->box(BUPDFSYS_QTY_ORDERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFSYS_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->box(BUPDFSYS_QTY_DELIVERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFSYS_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->printStringRJAt(BUPDFSYS_QTY_ORDERED_COL, 'Date');
		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		$this->_buPDF->CR();
		$this->_buPDF->CR();
		$this->_buPDF->CR();
		$this->_buPDF->setFontSize(8);
		$this->_buPDF->setFont();
		$this->_buPDF->endPage();
	}
	/**
	*	Output the report header.
	* This gets called once at the start of each page.
	*
	* @access private
	*/
	function reportHead(){
		$dsOrdhead = & $this->_dsOrdhead;
		$dsContact = & $this->_dsContact;
		$dsDeliveryMethod = & $this->_dsDeliveryMethod;
		$this->_buPDF->startPage();
		$this->_buPDF->placeImageAt( $GLOBALS['cfg']['cnclogo_path'], 'JPEG', 90, 110);
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
		$this->_buPDF->printString('System Build Report');
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
		$this->_buPDF->box(BUPDFSYS_QTY_ORDERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFSYS_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->box(BUPDFSYS_QTY_DELIVERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFSYS_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->printStringRJAt(BUPDFSYS_QTY_ORDERED_COL, 'Sales Order');
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringAt(BUPDFSYS_QTY_DELIVERED_BOX_LEFT_EDGE, $dsOrdhead->getValue('ordheadID'));
		$this->_buPDF->CR();
		$this->_buPDF->box(BUPDFSYS_QTY_ORDERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFSYS_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->box(BUPDFSYS_QTY_DELIVERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFSYS_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringRJAt(BUPDFSYS_QTY_ORDERED_COL, 'Date');
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringAt(BUPDFSYS_QTY_DELIVERED_BOX_LEFT_EDGE, date('d/m/Y'));
		$this->_buPDF->CR();
		$this->_buPDF->box(BUPDFSYS_QTY_ORDERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFSYS_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->box(BUPDFSYS_QTY_DELIVERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFSYS_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringRJAt(BUPDFSYS_QTY_ORDERED_COL, 'Customer Order');
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringAt(BUPDFSYS_QTY_DELIVERED_BOX_LEFT_EDGE, substr($dsOrdhead->getValue('custPORef'),0,17));
		$this->_buPDF->CR();
		$this->_buPDF->setFont();
		for ($i=0 ; $i < 4 ; $i++){
			$this->_buPDF->box(BUPDFSYS_QTY_ORDERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFSYS_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
			$this->_buPDF->box(BUPDFSYS_QTY_DELIVERED_BOX_LEFT_EDGE, $this->_buPDF->getYPos(),BUPDFSYS_QTY_ORDERED_BOX_WIDTH, $this->_buPDF->getFontSize()/2);
			$this->_buPDF->CR();
		}
		$this->_titleLine = $this->_buPDF->getYPos();
		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		// box around all detail headings
		$this->_buPDF->box(
			BUPDFSYS_DETAILS_BOX_LEFT_EDGE,
			$this->_buPDF->getYPos(),
			BUPDFSYS_DETAILS_BOX_WIDTH + (BUPDFSYS_QTY_ORDERED_BOX_WIDTH * 2),
			$this->_buPDF->getFontSize()/2
		);
		// Around details
		$this->_buPDF->box(
			BUPDFSYS_DETAILS_BOX_LEFT_EDGE,
			$this->_buPDF->getYPos(),
			BUPDFSYS_DETAILS_BOX_WIDTH,
			(BUPDFSYS_NUMBER_OF_LINES)*($this->_buPDF->getFontSize()/2)
		);
		// Box around the Unit Price
		$this->_buPDF->box(
			BUPDFSYS_DETAILS_BOX_LEFT_EDGE + BUPDFSYS_DETAILS_BOX_WIDTH,
			$this->_buPDF->getYPos(),
			BUPDFSYS_QTY_ORDERED_BOX_WIDTH,
			(BUPDFSYS_NUMBER_OF_LINES)*($this->_buPDF->getFontSize()/2)
		);
		// Box around the Cost
		$this->_buPDF->box(
			BUPDFSYS_DETAILS_BOX_LEFT_EDGE + BUPDFSYS_DETAILS_BOX_WIDTH + BUPDFSYS_QTY_ORDERED_BOX_WIDTH,
			$this->_buPDF->getYPos(),
			BUPDFSYS_QTY_ORDERED_BOX_WIDTH,
			(BUPDFSYS_NUMBER_OF_LINES)*($this->_buPDF->getFontSize()/2)
		);
		$this->_buPDF->printStringAt(BUPDFSYS_DETAILS_COL, 'Details');
		$this->_buPDF->printStringRJAt(BUPDFSYS_QTY_ORDERED_COL - 5, 'Qty');
		$this->_buPDF->printStringRJAt(BUPDFSYS_QTY_DELIVERED_COL - 6, 'Test Done');
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();
		$this->_buPDF->CR();
		$grandTotal=0;
	}
}// End of class
?>