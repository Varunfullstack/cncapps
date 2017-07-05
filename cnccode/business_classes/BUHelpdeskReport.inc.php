<?php
/**
* Helpdesk report business class
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg["path_gc"]."/Business.inc.php");
require_once($cfg["path_dbe"]."/CNCMysqli.inc.php");

class BUHelpdeskReport extends Business{

	/**
	* Constructor
	* @access Public
	*/
	function BUActivityProfitReport(&$owner){
		$this->constructor($owner);
	}
	function constructor(&$owner){
		parent::constructor($owner);
		
		$this->db = new CNCMysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	}
	function getTotalActivityCount( $today = true )
	{

		$sql = 
			"
			select
				count(*) as `activityCount`
			from callactivity
			where caa_date = curdate();
			";
			
		return $this->db->query( $sql )->fetch_object()->activityCount;

	} // end getTotalActivities

	function getServerguardActivityCount( $today = true )
	{

		$sql = 
			"
			select
			  count(*) as `activityCount`
			from callactivity
			where caa_date = curdate()
			    and caa_serverguard = 'Y';
			";
			
		return $this->db->query( $sql )->fetch_object()->activityCount;

	} // end getTotalActivities
	function getOutstandingActivityCounts()
	{
		$sql = 
			"
			select
			  hed_helpdesk_os_service_desk_count as `helpDeskOSServiceDeskCount`,
			  hed_helpdesk_os_servercare_count as `helpDeskOSServerCareCount`,
			  hed_helpdesk_os_prepay_count as `helpDeskOSPrePayCount`,
			  hed_helpdesk_os_escalation_count as `helpDeskOSEscalationCount`,
			  hed_helpdesk_os_cust_response_count as `helpDeskOSCustResponseCount`
			from headert;
			";

		return $this->db->query( $sql )->fetch_object();
	
	}
	
	function getHelpDeskProblems()
	{
		$sql = 
			"
			select
			  hed_helpdesk_problems as `helpDeskProblems`
			from headert;
			";
			
		return $this->db->query( $sql )->fetch_object()->helpDeskProblems;
		
	}
	/**
	 * Gets all visits due from tomorrow until the next workday (not weekend or bank holiday)
	 *
	 * @return unknown
	 */
	function getVisits()
	{
		$sql = 
			"
			select	
				cns_name as `engineer`,
				cus_name as `customer`,
				date_format( caa_date, '%e/%c/%Y' ) as `date`,
				case  
					when caa_starttime < '12:00' then 'AM' 
					ELSE 'PM' 
				end as `timeOfDay`
			from
				callactivity
        JOIN problem ON pro_problemno = caa_problemno
				JOIN callacttype on caa_callacttypeno = cat_callacttypeno
				JOIN customer on cus_custno = pro_custno
				JOIN consultant on cns_consno = caa_consno
			where
				caa_date BETWEEN DATE_ADD( CURDATE(), INTERVAL 1 DAY ) AND 
				(
					select
						date_field
					from
						date_xref
					where
						date_field > CURDATE()
						and
							is_bank_holiday = 'N'
						and
							DAYOFWEEK(date_field) NOT IN(1,7)
						order by
							date_field
						
					limit 0,1
				)
				and caa_endtime = ''
				and allowSCRFlag = 'Y';
			";

		
		return $this->db->query( $sql );

	} // end getVisitsTomorrow


	function getStaffAvailability()
	{

		$sql = 
			"
			select	
				cns_name as `engineer`,
				am,
				pm
			from
				staffAvailable
				JOIN consultant on cns_consno = userID
			where
				date = curdate()
				and
					( am > 0 or pm > 0 )
 				and
 					cns_perms LIKE '%" . PHPLIB_PERM_SERVICE . "%'
					";

		return $this->db->query( $sql );

	} // end getStaffAvailablilty
	
	
	function getTopTenCustomers( $today = true )
	{

		$sql = 
			"
			select
				cus_name as customer,
				count(*) as activities,
				SUM( (TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC( caa_starttime )) /3600) as hours
			from
				callactivity
        JOIN problem ON pro_problemno = caa_problemno
        JOIN customer on cus_custno = pro_custno
			where";
			
			if ($today) {
				$sql .= 
					"
					caa_date = curdate()
					";
			}
			else{
				$sql .= 
					"
					MONTH(caa_date) = MONTH(curdate())
					AND YEAR(caa_date) = YEAR(curdate())
					";
			}
			
			$sql .= 
				"
				group by
					Customer
				order by
					Hours desc
				limit 0,10;
				";

			return $this->db->query( $sql );

	}
		function getPeriodDescription( $period )
		{
			if ( $period ){
				return 'Today';
			}
			else{
				return 'This Month';
			}
		}
}// End of class
?>