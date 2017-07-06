<?php
/**
* Technical Report controller class
* CNC Ltd
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg['path_ct'].'/CTCNC.inc.php');
require_once($cfg['path_bu'].'/BUTechnicalReport.inc.php');
require_once($cfg['path_dbe'].'/DSForm.inc.php');

require_once("Mail.php");
require_once("Mail/mime.php");
// Actions
define('CTCustomerActivityExport_ACT_DISPLAY_ACTIVITY', 'displayActivity');

class CTCustomerActivityExport extends CTCNC {
	var $dsPrintRange ='';
	var $dsSearchForm='';
	
	function CTCustomerActivityExport($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		$this->constructor($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
	}
	function constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		parent::constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg, "", "", "", "");
		$this->buActivity=new BUActivity($this);
		$this->dsSearchForm = new DSForm($this);
		$this->dsTAndM = new DataSet($this);
		$this->dsServerCare = new DataSet($this);
		$this->dsServiceDesk = new DataSet($this);
		$this->dsStaff = new DataSet($this);
		$this->dsSite = new DataSet($this);
		$this->dsCallActivity = new DSForm($this);
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
	/**
	* Display search form
	* @access private
	*/
	function displaySearchForm()
	{
		$dsSearchForm = & $this->dsSearchForm; // ref to global

		$this->setMethodName('displaySearchForm');

		if ( $_POST ){
			
			$this->buActivity->initialiseCustomerActivityMonthForm($this->dsSearchForm);
	
	
			if (!$this->dsSearchForm->populateFromArray($_REQUEST['activity'])){
				$this->setFormErrorOn();
			}
			else{
	
				if ( !$this->dsSearchForm->getValue( 'customerID' ) ){
					$this->setFormErrorMessage( 'Please Enter A Customer' );
				}
				else{
	
					$activityCountByTechnicianCategory =
						$this->buTechnicalReport->getActivityCountByTechnicianCategory();

					$immediateProblemFixCountByTechnician =
						$this->buTechnicalReport->getImmediateProblemFixCountByTechnician();
						
						
				
				}			
			}
			
		}//end if ( $_POST )
		
		$this->setTemplateFiles	(
			array(
				'CustomerActivityExport' =>  'CustomerActivityExport.inc'
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

		$this->setPageTitle('Customer Activity Export');
		
		$this->dsSearchForm->initialise();
		$this->dsServiceDesk->initialise();
		$this->dsTAndM->initialise();
		$this->dsServerCare->initialise();

		if ($dsSearchForm->rowCount() == 0){
			$this->buActivity->initialiseSearchForm($dsSearchForm);
		}

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

		if ( $this->dsServiceDesk->rowCount() ){
			$this->template->set_block( 'CustomerActivityExport', 'rowBlock', 'rows' );
				
			$rowCount = 0;
			$maxHours = 0;
			
			while ( $this->dsServiceDesk->fetchNext() ){
	
				$rowCount++;
				
				$this->dsTAndM->fetchNext();
				$this->dsServerCare->fetchNext();
	
				$monthName = $this->dsServiceDesk->getValue('monthName') . "-" . $this->dsServiceDesk->getValue('year');
				
				$totalServerCareHours += $this->dsServerCare->getValue('hours');
				$totalServiceDeskHours += $this->dsServiceDesk->getValue('hours');
				$totalTAndMHours += $this->dsTAndM->getValue('hours');
	
				$monthNames[] = $monthName;
				$serviceDesk[$monthName] = $this->dsServiceDesk->getValue('hours');
				$serverCare[$monthName] = $this->dsServerCare->getValue('hours');
				$tAndM[$monthName] = $this->dsTAndM->getValue('hours');
				
				if ( $this->dsServiceDesk->getValue('hours') > $maxHours){
					$maxHours = $this->dsServiceDesk->getValue('hours'); 
				}
				if ( $this->dsServerCare->getValue('hours') > $maxHours){
					$maxHours = $this->dsServerCare->getValue('hours'); 
				}
				if ( $this->dsTAndM->getValue('hours') > $maxHours){
					$maxHours = $this->dsTAndM->getValue('hours'); 
				}
				
				$this->template->set_var(
					array(
						'monthName' 		=>	$monthName,
						'serverCareHours'	=>	number_format( $this->dsServerCare->getValue('hours') , 2 ), 
						'serviceDeskHours'	=>	number_format( $this->dsServiceDesk->getValue('hours'), 2 ), 
						'tAndMHours'		=>	number_format($this->dsTAndM->getValue('hours'), 2)
					)
	
				);
					
				$this->template->parse('rows', 'rowBlock', true);
					
			} // end while
	
			$this->template->set_var(
				array(
					'aveServerCareHours'	=>	number_format( $totalServerCareHours / $rowCount , 2 ), 
					'aveServiceDeskHours'	=>	number_format( $totalServiceDeskHours / $rowCount, 2 ), 
					'aveTAndMHours'			=>	number_format( $totalTAndMHours / $rowCount, 2)
				)
	
			);
	
			$dataString = false;
			
			foreach ($monthNames AS $key => $monthName){
				if ( $dataString ){
					$dataString .= '|';
				}
				$dataString .= number_format($serverCare[$monthName],2) . ',' . number_format($serviceDesk[$monthName], 2) . ',' . number_format($tAndM[$monthName],2); 
			}

			$urlChart =
				"http://chart.apis.google.com/chart?
				cht=bvg&
				chd=t:". $dataString . "&
				chds=0," . $maxHours . "&
				chs=600x500&
				chxt=x,y&
				chxl=
				0:|ServerCare|ServiceDesk|T and M|&
				chxr=1,0," . $maxHours . "&
				chco=FF0000,0000FF,00FF00&
				chdl=" . implode( $monthNames , '|' ) . "&
				chbh=10,0,10";		
	
			$this->template->set_var(
				array(
					'urlChart'	=>	$urlChart
				)
			); 
/*
 * Site
 */
			$this->template->set_block( 'CustomerActivityExport', 'siteBlock', 'sites' );
				
			while ( $this->dsSite->fetchNext() ){
	
				$this->template->set_var(
					array(
						'siteName' 		=>	$this->dsSite->getValue('year'),
						'siteHours'		=>	number_format($this->dsSite->getValue('hours'), 2)
					)
	
				);
					
				$this->template->parse('sites', 'siteBlock', true);
					
			} // end while
	
/*
 * Staff
 */
			$this->template->set_block( 'CustomerActivityExport', 'staffBlock', 'staff' );
				
			while ( $this->dsStaff->fetchNext() ){
	
				$this->template->set_var(
					array(
						'contactName' 		=>	$this->dsStaff->getValue('year'),
						'contactHours'		=>	number_format($this->dsStaff->getValue('hours'), 2)
					)
	
				);
					
				$this->template->parse('staff', 'staffBlock', true);
					
			} // end while
			
		} // if row count
		$this->template->parse('CONTENTS', 	'CustomerActivityExport', true);
		$this->parsePage();
	} // end function displaySearchForm
	
}// end of class
?>