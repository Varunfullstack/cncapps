<?php
/**
* Daily Helpdesk Report controller class
* CNC Ltd
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg['path_ct'].'/CTCNC.inc.php');
require_once($cfg['path_bu'].'/BUHelpdeskReport.inc.php');
require_once($cfg['path_func'].'/Common.inc.php');
//require_once( APPLICATION_DIR .'/maxchart/GoogleChart.php');
require_once("Mail.php");
require_once("Mail/mime.php");

class CTHelpdeskReport extends CTCNC {
	var $dsActivtyEngineer='';
	var $page='';
	function CTHelpdeskReport($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		$this->constructor($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
	}
	function constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		parent::constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg, "", "", "", "");
		$this->buHelpdeskReport=new BUHelpdeskReport($this);
	}
	/**
	* Route to function based upon action passed
	*/
	function defaultAction()
	{
		switch ($_REQUEST['action']){

			case 'TopTenCustomer':
				$this->topTenCustomer();
				$this->parsePage();
				break;

			case 'TopTenProblem':
				$this->topTenProblem();
				$this->parsePage();
				break;

			case 'StatusReport':
				$this->statusReport();
				$this->parsePage();
				break;
				
			default:
				$this->page = $this->allInOne();
				break;
		}
	}
	/**
	* @access private
	*/
	function statusReport()
	{
		$this->setMethodName('displayReport');

		$this->setTemplateFiles('HelpdeskReport', 'HelpdeskReport.inc');

		$this->setPageTitle("Help Desk Report");

		$this->template->set_var(
	
			array(
				'totalActivityCount'			=> Controller::htmlDisplayText( $this->buHelpdeskReport->getTotalActivityCount() ),
				'serverGuardActivityCount'		=> Controller::htmlDisplayText( $this->buHelpdeskReport->getServerguardActivityCount() ),
				'outstandingActivityCount'		=> Controller::htmlDisplayText( $this->buHelpdeskReport->getOutstandingActivityCount() ),
				'helpDeskProblems'				=> Controller::formatForHTML($this->buHelpdeskReport->getHelpDeskProblems())

			)
		);

		if ( $result = $this->buHelpdeskReport->getStaffAvailability() ){
			$this->template->set_block( 'HelpdeskReport', 'availablityBlock', 'staffAvailables' );
			
			while ( $available = $result->fetch_object() ) {

				
				$this->template->set_var(
					array(
						'engineer' 			=> Controller::htmlDisplayText( $available->engineer ),
						'amChecked'			=> $available->am == 0.5 ? CT_CHECKED : '',
						'pmChecked'			=> $available->pm == 0.5 ? CT_CHECKED : ''
					)
				);

				$this->template->parse('staffAvailables', 'availablityBlock', true);
			
			}			
		}
		
		if ( $result = $this->buHelpdeskReport->getVisits() ){

			while ( $visit = $result->fetch_object() ) {

				$this->template->set_block( 'HelpdeskReport', 'visitBlock', 'visits' );
				
				$this->template->set_var(
					array(
						'visitEngineer' 	=> Controller::htmlDisplayText( $visit->engineer ),
						'visitCustomer' 	=> Controller::htmlDisplayText( $visit->customer ),
						'visitTimeOfDay' 	=> Controller::htmlDisplayText( $visit->timeOfDay )
					)
				);

				$this->template->parse('visits', 'visitBlock', true);
			
			}			
		}
		
		$this->template->parse("CONTENTS", "HelpdeskReport");	
		
//		return $this->template->get("CONTENTS");
//		$this->parsePage();
		
	} // end function displayReport

	/**
	* @access private
	*/
	function topTenCustomer()
	{
		$this->setMethodName('topTenCustomer');

//		$this->setHTMLFmt( CT_HTML_FMT_PRINTER );
		
		$this->setTemplateFiles('HelpdeskReportTopTenCustomers', 'HelpdeskReportTopTenCustomers.inc');

		$this->setPageTitle("Top Ten Customers");

		if ( $result = $this->buHelpdeskReport->getTopTenCustomers( $_REQUEST['today'] ) ){

			$this->template->set_block( 'HelpdeskReportTopTenCustomers', 'customerBlock','customers' );

			$minHours = 99999;
			$minActivities = 99999;
			$maxHours = 0;
			$maxActivities = 0;

			while ( $customer = $result->fetch_object() ) {

			
				$customers[] = $customer->customer;
				$hours[] = $customer->hours;
				$activities[] = $customer->activities;

				if ( $customer->hours > $maxHours ){
				
					$maxHours = $customer->hours;
					
				}

				if ( $customer->activities > $maxActivities ){
				
					$maxActivities = $customer->activities;
					
				}

				if ( $customer->hours < $minHours ){
				
					$minHours = $customer->hours;
					
				}

				if ( $customer->activities < $minActivities ){
				
					$minActivities = $customer->activities;
					
				}
					
				$this->template->set_var(
					array(
						'periodDescription' => $this->buHelpdeskReport->getPeriodDescription( $_REQUEST['today'] ),
						'customer' 		=> Controller::htmlDisplayText( $customer->customer ),
						'activities' 	=> Controller::htmlDisplayText( $customer->activities ),
						'hours' 			=> common_numberFormat( $customer->hours )
					)
				);

				$this->template->parse('customers', 'customerBlock', true);
			
			}			

		}
/*
The chart
*/
		$urlCustomerChart =
			"http://chart.apis.google.com/chart?
			cht=bhg&
			chd=t:". implode( $hours, ',' ) . "|" . implode( $activities, ',' ) . "&
			chds=0," . $maxActivities . "&
			chs=600x400&
			chxt=y,x&
			chxl=
			0:|". urlencode( implode( array_reverse( $customers ), '|' ) ) . "|&
			chxr=1,0," . $maxActivities . "&
			chco=FF0000,0000FF&
			chdl=Hours|Activities&
			chbh=10,0,10";
	
		$this->template->set_var(
			array(
				'urlCustomerChart' => $urlCustomerChart
			)
		);

		$this->template->parse("CONTENTS", "HelpdeskReportTopTenCustomers");	

//		return $this->template->get("CONTENTS");
		//		$this->parsePage();

	} // end function displayReport

	/**
	* @access private
	*/
	function topTenProblem()
	{
		$this->setMethodName('topTenProblem');

//		$this->setHTMLFmt( CT_HTML_FMT_PRINTER );
		
		$this->setTemplateFiles('HelpdeskReportTopTenProblems', 'HelpdeskReportTopTenProblems.inc');

		$this->setPageTitle("Top Ten Problems");

		if ( $result = $this->buHelpdeskReport->getTopTenProblems( $_REQUEST['today'] ) ){

			$this->template->set_block( 'HelpdeskReportTopTenProblems', 'problemBlock','problems' );

			$minValue = 0;
			$maxValue = 0;
			$data = false;
			$ylabel = false;
			
			while ( $problem = $result->fetch_object() ) {
			
				$data[] = $problem->hours;
				$ylabel[] = $problem->category;

	
				if ( $problem->hours > $maxValue ){
				
					$maxValue = $problem->hours;
					
				}
											
				$this->template->set_var(
					array(
						'periodDescription' => $this->buHelpdeskReport->getPeriodDescription( $_REQUEST['today'] ) ,
						'category' 		=> Controller::htmlDisplayText( $problem->category ),
						'hours' 			=> common_numberFormat( $problem->hours )
					)
				);

				$this->template->parse('problems', 'problemBlock', true);
			
			}			

		}
/*
The chart
*/
		$urlChart =
			"http://chart.apis.google.com/chart?
			cht=bhg&
			chd=t:". implode( $data, ',' ) . "&
			chds=0," . $maxValue . ",0&
			chs=400x300&
			chxt=y,x&
			chxl=
			0:|". implode( array_reverse( $ylabel ), '|' ) . "|&
			chxr=1,0," . $maxValue . "&
			chco=FF0000&
			chbh=10,0,10";
	
	
		$this->template->set_var(
			array(
				'urlChart' => $urlChart
			)
		);
		
		$this->template->parse("CONTENTS", "HelpdeskReportTopTenProblems");	
	
		return $this->template->get("CONTENTS");
	//	$this->parsePage();
		
	} // end function displayReport

	function allInOne()
	{
		$this->setMethodName('allInOne');

		//$this->setHTMLFmt( CT_HTML_FMT_PRINTER );
		
		$this->setTemplateFiles(
			array(
				'HelpdeskReport'				=> 'HelpdeskReport.inc',
				'HelpdeskReportStatus'			=> 'HelpdeskReportStatus.inc',
				'HelpdeskReportTopTenCustomers'	=> 'HelpdeskReportTopTenCustomers.inc',
				'HelpdeskReportTopTenProblems'	=> 'HelpdeskReportTopTenProblems.inc'
			)
		);

		$this->setPageTitle("Help Desk Report");
		
		$counts = $this->buHelpdeskReport->getOutstandingActivityCounts();

		$this->template->set_var(
	
			array(
				'totalActivityCount'			=> Controller::htmlDisplayText( $this->buHelpdeskReport->getTotalActivityCount() ),
				'serverGuardActivityCount'		=> Controller::htmlDisplayText( $this->buHelpdeskReport->getServerguardActivityCount() ),
				'helpDeskOSServiceDeskCount'	=> Controller::htmlDisplayText( $counts->helpDeskOSServiceDeskCount),
				'helpDeskOSServerCareCount'		=> Controller::htmlDisplayText( $counts->helpDeskOSServerCareCount),
				'helpDeskOSPrePayCount'			=> Controller::htmlDisplayText( $counts->helpDeskOSPrePayCount),
				'helpDeskOSEscalationCount'		=> Controller::htmlDisplayText( $counts->helpDeskOSEscalationCount),
				'helpDeskOSCustResponseCount'	=> Controller::htmlDisplayText( $counts->helpDeskOSCustResponseCount),
				'helpDeskProblems'				=> Controller::formatForHTML($this->buHelpdeskReport->getHelpDeskProblems())

			)
		);

		if ( $_REQUEST['today'] == 1 ){

			if ( $result = $this->buHelpdeskReport->getStaffAvailability() ){
				$this->template->set_block( 'HelpdeskReportStatus', 'availablityBlock', 'staffAvailables' );
				
				while ( $available = $result->fetch_object() ) {
	
					$this->template->set_var(
						array(
							'engineer' 			=> Controller::htmlDisplayText( $available->engineer ),
							'amChecked'			=> $available->am == 0.5 ? CT_CHECKED : '',
							'pmChecked'			=> $available->pm == 0.5 ? CT_CHECKED : ''
						)
					);
	
					$this->template->parse('staffAvailables', 'availablityBlock', true);
				
				}			
			}
			
			if ( $result = $this->buHelpdeskReport->getVisits() ){
	
				$this->template->set_block( 'HelpdeskReportStatus', 'visitBlock', 'visits' );
				
				while ( $visit = $result->fetch_object() ) {
					
					$this->template->set_var(
						array(
							'visitEngineer' 	=> Controller::htmlDisplayText( $visit->engineer ),
							'visitCustomer' 	=> Controller::htmlDisplayText( $visit->customer ),
							'visitDate' 		=> Controller::htmlDisplayText( $visit->date ),
							'visitTimeOfDay' 	=> Controller::htmlDisplayText( $visit->timeOfDay )
						)
					);
	
					$this->template->parse('visits', 'visitBlock', true);
				
				}			
			}
		
		} // end if $_REQUEST['today'] == 1
		
		$customers = array();
		$hours = array();
		$activities = array();
		
		if ( $result = $this->buHelpdeskReport->getTopTenCustomers( $_REQUEST['today'] ) ){

			$this->template->set_block( 'HelpdeskReportTopTenCustomers', 'customerBlock','customers' );

			$minHours = 99999;
			$minActivities = 99999;
			$maxHours = 0;
			$maxActivities = 0;

			while ( $customer = $result->fetch_object() ) {

			
				$customers[] = $customer->customer;
				$hours[] = $customer->hours;
				$activities[] = $customer->activities;

				if ( $customer->hours > $maxHours ){
				
					$maxHours = $customer->hours;
					
				}

				if ( $customer->activities > $maxActivities ){
				
					$maxActivities = $customer->activities;
					
				}

				if ( $customer->hours < $minHours ){
				
					$minHours = $customer->hours;
					
				}

				if ( $customer->activities < $minActivities ){
				
					$minActivities = $customer->activities;
					
				}
					
				$this->template->set_var(
					array(
						'periodDescription' => $this->buHelpdeskReport->getPeriodDescription( $_REQUEST['today'] ),
						'customer' 		=> Controller::htmlDisplayText( $customer->customer ),
						'activities' 	=> Controller::htmlDisplayText( $customer->activities ),
						'hours' 			=> common_numberFormat( $customer->hours )
					)
				);

				$this->template->parse('customers', 'customerBlock', true);
			
			}			

		}
/*
The chart
*/
		$urlChart =
			"http://chart.apis.google.com/chart?
			cht=bhg&
			chd=t:". implode( $hours, ',' ) . "|" . implode( $activities, ',' ) . "&
			chds=0," . $maxActivities . "&
			chs=600x400&
			chxt=y,x&
			chxl=
			0:|". urlencode( implode( array_reverse( $customers ), '|' ) ) . "|&
			chxr=1,0," . $maxActivities . "&
			chco=FF0000,0000FF&
			chdl=Hours|Activities&
			chbh=10,0,10";
	
		$this->template->set_var(
			array(
				'urlCustomerChart' => $urlChart
			)
		);
/*
 * top ten problems
 */
	
		if ( $result = $this->buHelpdeskReport->getTopTenProblems( $_REQUEST['today'] ) ){

			$this->template->set_block( 'HelpdeskReportTopTenProblems', 'problemBlock','problems' );

			$minValue = 0;
			$maxValue = 0;
			$data = false;
			$ylabel = false;
			
			while ( $problem = $result->fetch_object() ) {
			
				$data[] = $problem->hours;
				$ylabel[] = $problem->category;

	
				if ( $problem->hours > $maxValue ){
				
					$maxValue = $problem->hours;
					
				}
											
				$this->template->set_var(
					array(
						'periodDescription' => $this->buHelpdeskReport->getPeriodDescription( $_REQUEST['today'] ) ,
						'category' 		=> Controller::htmlDisplayText( $problem->category ),
						'hours' 			=> common_numberFormat( $problem->hours )
					)
				);

				$this->template->parse('problems', 'problemBlock', true);
			
			}			

		}
/*
The chart
*/
		$urlChart =
			"http://chart.apis.google.com/chart?
			cht=bhg&
			chd=t:". implode( $data, ',' ) . "&
			chds=0," . $maxValue . ",0&
			chs=400x300&
			chxt=y,x&
			chxl=
			0:|". implode( array_reverse( $ylabel ), '|' ) . "|&
			chxr=1,0," . $maxValue . "&
			chco=FF0000&
			chbh=10,0,10";
	
	
		$this->template->set_var(
			array(
				'urlProblemChart' => $urlChart
			)
		);
		

		if ( $_REQUEST[ 'today' ] == 1 ){
			$this->template->parse('helpdeskReportStatus', 	'HelpdeskReportStatus', true);
		}
		$this->template->parse('helpdeskReportTopTenCustomers', 'HelpdeskReportTopTenCustomers', true);
		$this->template->parse('helpdeskReportTopTenProblems', 'HelpdeskReportTopTenProblems', true);

		$this->template->parse("CONTENTS", "HelpdeskReport", true);	

//		$this->template->parse("CONTENTS", "page");
		
//		return $this->template->finish($this->template->get_var('CONTENTS'));
		$this->parsePage();
		
		
	}
}// end of class
?>