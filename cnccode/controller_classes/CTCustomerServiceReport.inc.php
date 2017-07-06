<?php
/**
* Customer Service Report controller class
* CNC Ltd
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg['path_ct'].'/CTCNC.inc.php');
require_once($cfg['path_bu'].'/BUCustomerServiceReport.inc.php');
require_once($cfg['path_bu'].'/BUCustomerNew.inc.php');
require_once($cfg['path_dbe'].'/DSForm.inc.php');


class CTCustomerServiceReport extends CTCNC {
	var $dsPrintRange ='';
	var $dsSearchForm='';
	var $dsResults='';
	
	function CTCustomerServiceReport($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		$this->constructor($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
	}
	function constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		parent::constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg, "", "", "", "");
		$this->buCustomerServiceReport=new BUCustomerServiceReport($this);
		$this->dsSearchForm = new DSForm($this);
		$this->dsResults = new DataSet($this);
	}
	/**
	* Route to function based upon action passed
	*/
	function defaultAction()
	{
		switch ($_REQUEST['action']){

			case CTCNC_ACT_SEARCH:
				$this->search();
				break;
			default:
				$this->displaySearchForm();
				break;
		}
	}
	function search(){

		$this->setMethodName('search');

		$this->buCustomerServiceReport->initialiseSearchForm($this->dsSearchForm);

		if ( isset( $_REQUEST['searchForm'] ) == 'POST' ) {
			if (!$this->dsSearchForm->populateFromArray($_REQUEST['searchForm'])){
				$this->setFormErrorOn();
				$this->displaySearchForm(); //redisplay with errors
				exit;
			}

		}

		if ( $_REQUEST['CSV'] ){
			$limit = false;						// no row count limit
		}
		else{
			$limit = true;
		}
		
		if ( $this->dsSearchForm->getValue('fromDate') == '' ){
			$this->dsSearchForm->setUpdateModeUpdate();
			$this->dsSearchForm->setValue('fromDate', date( 'Y-m-d' , strtotime("-1 year")  )  );						
			$this->dsSearchForm->post();
		}
		if ( !$this->dsSearchForm->getValue('toDate') ){
			$this->dsSearchForm->setUpdateModeUpdate();
			$this->dsSearchForm->setValue('toDate', date( 'Y-m-d' )  );					
			$this->dsSearchForm->post();
		}

		$this->dsResults =
		
			$this->buCustomerServiceReport->search(
				$this->dsSearchForm
			);

		$this->displaySearchForm();
		exit;
	}
	/**
	* Display search form
	* @access private
	*/
	function displaySearchForm()
	{
		$dsSearchForm = & $this->dsSearchForm; // ref to global

		$this->setMethodName('displaySearchForm');

		$this->setTemplateFiles	(
			array(
				'CustomerServiceReport' =>  'CustomerServiceReport.inc'
			)
		);

		$urlSubmit = $this->buildLink(
			$_SERVER['PHP_SELF'],
			array(
				'action' => CTCNC_ACT_SEARCH
			)
		);

		$this->setPageTitle('Customer Service Report');

		if ($dsSearchForm->rowCount() == 0){
			$this->buCustomerServiceReport->initialiseSearchForm($dsSearchForm);
		}

		$this->template->set_block('CustomerServiceReport','resultBlock', 'results');
	
		while ($this->dsResults->fetchNext()){

			if ( $this->dsResults->getValue( 'Activities' ) == 0 AND $this->dsResults->getValue( 'OnSite' ) == 0 ){
			
				$activities = 'NO ACTIVITY LOGGED';
				$onSite = '';
			
			}
			else{

				$activities = $this->dsResults->getValue( 'Activities' );
				$onSite = $this->dsResults->getValue( 'OnSite' );

			}
			
			$this->template->set_var(
				array(
					'CustomerName'	=>	$this->dsResults->getValue( 'CustomerName' ),
					'Activities'	=> 		$activities,
					'OnSite'=>					$onSite
				)
			);

			
			$this->template->parse('results', 'resultBlock', true);
		}

		$this->template->set_var(
			array(
				'formError' 				=> $this->formError,
				'fromDate' 					=> Controller::dateYMDtoDMY($dsSearchForm->getValue('fromDate')),
				'fromDateMessage' 	=> $dsSearchForm->getMessage('fromDate'),
				'toDate' 						=> Controller::dateYMDtoDMY($dsSearchForm->getValue('toDate')),
				'toDateMessage' 		=> $dsSearchForm->getMessage('toDate'),
				'urlSubmit' 				=> $urlSubmit,
			)
		);


		$this->template->parse('CONTENTS', 	'CustomerServiceReport', true);
		$this->parsePage();
	} // end function displaySearchForm

}// end of class
?>