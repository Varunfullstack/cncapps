<?php
/**
* Customer Activity Report controller class
* CNC Ltd
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg['path_ct'].'/CTCNC.inc.php');
require_once($cfg['path_bu'].'/BUQuotationConversionReport.inc.php');
require_once($cfg['path_bu'].'/BUCustomerNew.inc.php');
require_once($cfg['path_dbe'].'/DSForm.inc.php');

require_once("Mail.php");
require_once("Mail/mime.php");
// Actions
class CTQuotationConversionReport extends CTCNC {
	var $dsSearchForm='';
	
	function CTQuotationConversionReport($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		$this->constructor($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
	}
	function constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		parent::constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg, "", "", "", "");
		$this->buQuotationConversionReport = new BUQuotationConversionReport($this);
		$this->dsSearchForm = new DSForm($this);
    $this->buQuotationConversionReport->initialiseSearchForm( $this->dsSearchForm );
	}
	/**
	* Route to function based upon action passed
	*/
	function defaultAction()
	{
		switch ($_REQUEST['action']){

			default:
				$this->displaySearchForm();
				break;
		}
	}

	function displaySearchForm()
	{
		$dsSearchForm = & $this->dsSearchForm; // ref to global

		$this->setMethodName('displaySearchForm');
    
    $quotationConversionData = array();

		if ( $_POST ){
			
			if (!$this->dsSearchForm->populateFromArray($_REQUEST['search'])){
				$this->setFormErrorOn();
			}
			else{
	
				$quotationConversionData =
          $this->buQuotationConversionReport->getConversionData(
						$this->dsSearchForm->getValue( 'fromDate' ),
						$this->dsSearchForm->getValue( 'toDate' ),
						$this->dsSearchForm->getValue( 'customerID' )
					);
			}
			
		}//end if ( $_POST )
		
		$this->setTemplateFiles	(
			array(
				'QuotationConversionReport' =>  'QuotationConversionReport.inc'
			)
		);


		$urlCustomerPopup = $this->buildLink(
				CTCNC_PAGE_CUSTOMER,
				array(
					'action' => CTCNC_ACT_DISP_CUST_POPUP,
					'htmlFmt' => CT_HTML_FMT_POPUP
				)
			);

		$urlSubmit = $this->buildLink(
			$_SERVER['PHP_SELF'],
			array(
				'action' => CTCNC_ACT_SEARCH
			)
		);

		$this->setPageTitle('Quotation Conversion Report');
		
		$dsSearchForm->initialise();

		if ($dsSearchForm->getValue('customerID') != 0){
			$buCustomer = new BUCustomer($this);
			$buCustomer->getCustomerByID($dsSearchForm->getValue('customerID'), $dsCustomer);
			$customerString = $dsCustomer->getValue('name');
		}

		$this->template->set_var(
			array(
				'formError' 		=> $this->formError,
				'customerID' 		=> $dsSearchForm->getValue('customerID'),
				'customerIDMessage' => $dsSearchForm->getMessage('customerID'),
				'customerString' 	=> $customerString,
				'fromDate' 			=> Controller::dateYMDtoDMY($dsSearchForm->getValue('fromDate')),
				'fromDateMessage' 	=> $dsSearchForm->getMessage('fromDate'),
				'toDate' 			=> Controller::dateYMDtoDMY($dsSearchForm->getValue('toDate')),
				'toDateMessage' 	=> $dsSearchForm->getMessage('toDate'),
				'urlCustomerPopup' 	=> $urlCustomerPopup,
				'urlSubmit' 		=> $urlSubmit
			)
		);

		if ( count( $quotationConversionData ) > 0 ) {

			$this->template->set_block( 'QuotationConversionReport', 'rowBlock', 'rows' );
				
			$rowCount = 0;
			$maxHours = 0;
			
			foreach ( $quotationConversionData as $row ){
        
        if ( $row[ 'quoteCount' ] != 0 ){
          $percentage = $row[ 'conversionCount' ] / $row[ 'quoteCount' ] * 100;
        }
        else{
          $percentage = 0;
        }
				
				$this->template->set_var(
					array(
						'month' 		        =>	$row[ 'month' ],
            'year'              =>  $row[ 'year' ],
            'quoteCount'        =>  $row[ 'quoteCount' ],
            'conversionCount'   =>  $row[ 'conversionCount' ],
            'conversionPercentage'   =>  number_format( $percentage , 2 )
          )
	
				);
					
				$this->template->parse('rows', 'rowBlock', true);
					
			} // end while
	
		} // if row count

		$this->template->parse('CONTENTS', 	'QuotationConversionReport', true);
		$this->parsePage();
	} // end function displaySearchForm
	
}// end of class
?>