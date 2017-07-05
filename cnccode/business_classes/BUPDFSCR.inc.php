<?
/**
* PDF SCR Generation business class
*
* Generates a PDF SCR report.
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg['path_bu'].'/BUPDF.inc.php');
define('BUPDFSRC_NUMBER_OF_LINES', 30);
// print column positions
define('BUPDFSRC_DETAILS_COL', 12);
define('BUPDFSRC_SERIAL_NO_COL', 138);
define('BUPDFSRC_PURCHASE_DATE', 175);
// box dimensions
define('BUPDFSRC_DETAILS_BOX_WIDTH', 80);
define('BUPDFSRC_SERIAL_NO_BOX_WIDTH', 50);	// used for cost box too
define('BUPDFSRC_DETAILS_BOX_LEFT_EDGE', 11);
define('BUPDFSRC_SERIAL_NO_BOX_LEFT_EDGE',		// relative to other boxes
	BUPDFSRC_DETAILS_BOX_LEFT_EDGE +
	BUPDFSRC_DETAILS_BOX_WIDTH
);
define('BUPDFSRC_PURCHASE_DATE_BOX_LEFT_EDGE',
	BUPDFSRC_SERIAL_NO_BOX_LEFT_EDGE +
	BUPDFSRC_SERIAL_NO_BOX_WIDTH
);
class BUPDFSCR extends BaseObject{
	var $_buPDF='';					// BUPDF object
	var $_buSite='';
	var $_buActivity='';
	var $_buCustomerItem='';
	var $_dsCallActivity='';
	var $_dsSite='';
	var $_titleLine='';
	var $_hasServerCareContract='';
	var $_params=array();
	var $_pdfFile;
	/**
	* Constructor
	*
	*/
	function BUPDFSCR(
		&$owner,
		$activityID,
		&$params
	){
		$this->constructor($owner, $callActivityID, $params);
	}
	function constructor(&$owner, $callActivityID, $params){//, &$dsContact){
		$this->BaseObject($owner);
		$this->setMethodName('constructor');
		$this->_buSite = new BUSite($this);
		$this->_buActivity = new BUActivity( $this );
		$this->_buCustomerItem = new BUCustomerItem( $this );

		$this->_buActivity->getActivityByID($_REQUEST['callActivityID'], $this->_dsCallActivity);
		$this->_buSite->getSiteByID(
			$this->_dsCallActivity->getValue( 'customerID' ), 
			$this->_dsCallActivity->getValue( 'siteNo' ),
			$this->_dsSite
		);
		$this->_params = $params;

		$this->_hasServerCareContract = $this->_buCustomerItem->customerHasValidServerCareContract($this->_dsCallActivity->getValue( 'customerID'));

	}
	/**
	* Use the parameters passed in constructor to get list of invoices and generate a PDF file on
	* disk.
	* If no invoices are found then return FALSE
	*	@return String PDF disk file name or FALSE
	*/
	function generateFile(){
		$this->_pdfFile = SCR_DIR . '/' . $this->_dsCallActivity->getValue('customerID') . '_' . $this->_dsCallActivity->getValue('callActivityID') . '.pdf';
		$this->_buPDF= new BUPDF(
			$this,
			$this->_pdfFile,
			'CNC',
			date('d/m/Y'),
			'CNC Ltd',
			'Service Call Report',
			'A4'
		);
		$this->produceReport();
		$this->_buPDF->close();
		return $this->_pdfFile;
	}
	function produceReport(){
		// local refs
		$dsCallActivity = & $this->_dsCallActivity;
		$params = & $this->_params;
		$dsSite = & $this->_dsSite;
		$this->noteHead();

		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'New Equipment Installed And Parts Used');

		$this->_buPDF->CR();
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();

		if ( $params['newSerialNumbers'] ){
			$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, $params['newSerialNumbers']);
		}
		else{
			$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'None' );
		}
		$this->_buPDF->CR();
		$this->_buPDF->CR();

		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'Reason');

		$this->_buPDF->CR();
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();

		$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, common_HTMLToText( $this->_dsCallActivity->getValue('reason')) );

		$this->_buPDF->CR();
		$this->_buPDF->CR();

		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'Action Taken');

		$this->_buPDF->CR();
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();

		$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, common_HTMLToText( $this->_dsCallActivity->getValue('action') ) );

		$this->_buPDF->CR();
		$this->_buPDF->CR();

		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'Final Status');

		$this->_buPDF->CR();
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();

		$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, common_HTMLToText( $this->_dsCallActivity->getValue('finalStatus') ) );
	
/*
		$this->_buPDF->CR();
		$this->_buPDF->CR();

		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'System Documentation');

		$this->_buPDF->CR();
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();

		$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'Changes Required? ' . ( $params['areDocumentChangesRequired'] ? $params['areDocumentChangesRequired'] : 'No' ) );
		$this->_buPDF->CR();
		
		if ( $params['areDocumentChangesRequired'] == 'Yes' ){
			$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'Changes Completed? ' . ( $params['areDocumentChangesCompleted'] ? $params['areDocumentChangesCompleted'] : 'No' ) );
			$this->_buPDF->CR();
		}	
*/
		/*
		Only need rest if no server care contract
		*/
		$numberOfServers = count( $params['disk1Name'] );
		
		if ( !$this->_hasServerCareContract ){
		
			for ( $serverNumber = 1 ; $serverNumber <= $numberOfServers ; $serverNumber++ ){

				$this->_buPDF->CR();
				$this->_buPDF->setBoldUnderlineOn();
				$this->_buPDF->setFontSize(12);
				$this->_buPDF->setFont();
				$this->_buPDF->CR();
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'Server: ' . $params['serverName'][$serverNumber] );
				$this->_buPDF->CR();
					
				$this->_buPDF->CR();
				$this->_buPDF->setBoldOn();
				$this->_buPDF->setFontSize(10);
				$this->_buPDF->setFont();
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'Disk Usage');
				$this->_buPDF->CR();
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL + 10, 'Name');
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL + 30, 'Total Size');
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL + 50, 'Free Space');
				$this->_buPDF->CR();
				$this->_buPDF->setBoldOff();
				$this->_buPDF->setFont();
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL + 10, $params['disk1Name'][$serverNumber]);
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL + 30, $params['disk1Total'][$serverNumber]);
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL + 50,  $params['disk1Free'][$serverNumber]);
				$this->_buPDF->CR();
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL + 10, $params['disk2Name'][$serverNumber]);
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL + 30, $params['disk2Total'][$serverNumber]);
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL + 50,  $params['disk2Free'][$serverNumber]);
				$this->_buPDF->CR();
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL + 10, $params['disk3Name'][$serverNumber]);
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL + 30, $params['disk3Total'][$serverNumber]);
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL + 50,  $params['disk3Free'][$serverNumber]);
	
				$this->_buPDF->CR();
				$this->_buPDF->CR();
				$this->_buPDF->setBoldOn();
				$this->_buPDF->setFont();
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'Anti-virus: Server');
				$this->_buPDF->CR();
				$this->_buPDF->setBoldOff();
				$this->_buPDF->setFont();
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'Application:');
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL + 30, $params['antiVirusServerApp'][$serverNumber]);
				$this->_buPDF->CR();
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'Current DAT:');
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL + 30, $params['antiVirusServerDAT'][$serverNumber]);
				$this->_buPDF->CR();
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'Current Engine:');
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL + 30, $params['antiVirusServerEng'][$serverNumber]);
				$this->_buPDF->CR();
				$this->_buPDF->CR();
				$this->_buPDF->setBoldOn();
				$this->_buPDF->setFont();
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'Anti-virus: Email');
				$this->_buPDF->CR();
				$this->_buPDF->setBoldOff();
				$this->_buPDF->setFont();
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'Application:');
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL + 30, $params['antiVirusEmailApp'][$serverNumber]);
				$this->_buPDF->CR();
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'Current DAT:');
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL + 30, $params['antiVirusEmailDAT'][$serverNumber]);
				$this->_buPDF->CR();
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'Current Engine:');
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL + 30, $params['antiVirusEmailEng'][$serverNumber]);
				$this->_buPDF->CR();
				$this->_buPDF->CR();
				$this->_buPDF->setBoldOn();
				$this->_buPDF->setFont();
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'Backup');
				$this->_buPDF->CR();
				$this->_buPDF->setBoldOff();
				$this->_buPDF->setFont();
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'Application:');
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL + 30, $params['backupApp'][$serverNumber]);
				$this->_buPDF->CR();
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'Last Result:');
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL + 30, $params['backupLastResult'][$serverNumber]);
	
				$this->_buPDF->CR();
				$this->_buPDF->CR();
				$this->_buPDF->setBoldOn();
				$this->_buPDF->setFont();
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'RAID Array');
		
				$this->_buPDF->CR();
				$this->_buPDF->setBoldOff();
				$this->_buPDF->setFont();
		
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'Healthy? ' . ( $params['isRaidArrayHealthy'][$serverNumber] ? $params['isRaidArrayHealthy'][$serverNumber] : 'No' ) );
	
				$this->_buPDF->CR();
				$this->_buPDF->CR();
				$this->_buPDF->setBoldOn();
				$this->_buPDF->setFont();
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'UPS');
		
				$this->_buPDF->CR();
				$this->_buPDF->setBoldOff();
				$this->_buPDF->setFont();
		
				$this->_buPDF->printStringAt(BUPDFSRC_DETAILS_COL, 'Online? ' . ( $params['isUPSOnline'][$serverNumber] ? $params['isUPSOnline'][$serverNumber] : 'No' ) );
	
			} // end has server contract
		
		}
		
		$this->_buPDF->printStringAt(85, $footerText);
		$this->_buPDF->endPage();
	}
	/**
	*	Output the header.
	* This gets called once at the start of each page.
	* Where a statement spans pages it gets called many times for the same statement.
	*
	* @access private
	*/
	function noteHead(){
		$dsCallActivity = & $this->_dsCallActivity;
		$dsSite = & $this->_dsSite;
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
		$this->_buPDF->printString('Service Call ' . $dsCallActivity->getValue('callActivityID') );
		$this->_buPDF->setFontSize(10);
		$this->_buPDF->setFont();
		$this->_buPDF->CR();
		$this->_buPDF->CR();
		$firstAddLine = $this->_buPDF->getYPos();	// remember this line no
		$this->_buPDF->printString($dsCallActivity->getValue('customerName'));
		$this->_buPDF->CR();
		$this->_buPDF->setFontSize(8);
		$this->_buPDF->setFont();
		$this->_buPDF->printString($dsSite->getValue('add1'));
		if ($dsSite->getValue('add2')!=''){
			$this->_buPDF->CR();
			$this->_buPDF->printString($dsSite->getValue('add2'));
		}
		if ($dsSite->getValue('add3')!=''){
			$this->_buPDF->CR();
			$this->_buPDF->printString($dsSite->getValue('add3'));
		}
		$this->_buPDF->CR();
		$this->_buPDF->printString($dsSite->getValue('town'));
		if ($dsSite->getValue('county')!=''){
			$this->_buPDF->CR();
			$this->_buPDF->printString($dsSite->getValue('county'));
		}
		$this->_buPDF->CR();
		$this->_buPDF->printString($dsSite->getValue('postcode'));
		$this->_buPDF->CR();
		$this->_buPDF->CR();

		$this->_buPDF->setFontSize(10);
		$this->_buPDF->setFont();
		$this->_buPDF->moveYTo($firstAddLine);	//move back up the page
		$this->_buPDF->CR();
		$this->_buPDF->printStringRJAt(BUPDFSRC_SERIAL_NO_COL, 'IT Support Line');
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringAt(BUPDFSRC_PURCHASE_DATE_BOX_LEFT_EDGE, CONFIG_IT_SUPPORT_PHONE);
		$this->_buPDF->CR();
		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringRJAt(BUPDFSRC_SERIAL_NO_COL, 'Phone System Support Line');
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringAt(BUPDFSRC_PURCHASE_DATE_BOX_LEFT_EDGE, CONFIG_PHONE_SYSTEM_SUPPORT_PHONE);
		$this->_buPDF->CR();
		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringRJAt(BUPDFSRC_SERIAL_NO_COL, 'Date');
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringAt(BUPDFSRC_PURCHASE_DATE_BOX_LEFT_EDGE, date("l jS F Y", strtotime($dsCallActivity->getValue('date'))));
		$this->_buPDF->CR();
		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringRJAt(BUPDFSRC_SERIAL_NO_COL, 'Arrival');
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringAt(BUPDFSRC_PURCHASE_DATE_BOX_LEFT_EDGE, $dsCallActivity->getValue('startTime'));
		$this->_buPDF->CR();
		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringRJAt(BUPDFSRC_SERIAL_NO_COL, 'Departure');
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringAt(BUPDFSRC_PURCHASE_DATE_BOX_LEFT_EDGE, $dsCallActivity->getValue('endTime'));
		$this->_buPDF->CR();
		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringRJAt(BUPDFSRC_SERIAL_NO_COL, 'Your Contact');
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringAt(BUPDFSRC_PURCHASE_DATE_BOX_LEFT_EDGE,  $dsCallActivity->getValue('contactName'));
		$this->_buPDF->CR();
		$this->_buPDF->setBoldOn();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringRJAt(BUPDFSRC_SERIAL_NO_COL, 'Engineer');
		$this->_buPDF->setBoldOff();
		$this->_buPDF->setFont();
		$this->_buPDF->printStringAt(BUPDFSRC_PURCHASE_DATE_BOX_LEFT_EDGE,  $dsCallActivity->getValue('userName'));
		$this->_buPDF->CR();
		$this->_buPDF->CR();
	}
}// End of class
?>