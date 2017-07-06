<?php	
/**
 * Call activity business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once ($cfg ["path_gc"] . "/Business.inc.php");

class BUCurrentActivityReport extends Business {
  
	/**
	 * Constructor
	 * @access Public
	 */
	function BUCurrentActivityReport(&$owner) {
		$this->constructor ( $owner );
	}
	function constructor(&$owner) {
		parent::constructor ( $owner );
	}
  function getProblems( $status, $futureOnly = false )
  {
    require_once($cfg["path_dbe"]."/CNCMysqli.inc.php");

    $db = new CNCMysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $queryString = "
      SELECT
        pro_problemno as problemID,
        pro_custno as customerID,
        pro_priority as priority,
        pro_consno as userID,
        pro_status as status,
        pro_date_raised as dateRaised,
        pro_fixed_consno as fixedUserID,
        pro_fixed_date as fixedDate,
        pro_responded_hours as responsedHours,
        pro_working_hours as workingHours,
        pro_sent_sla_alert_flag as sendSlaAlertFlag,
        pro_internal_notes as internalNotes,
        pro_completion_alert_count as completionAlertCount,
        pro_complete_date as completionDate,
        pro_hide_from_customer_flag as hideFromCustomerFlag,
        pro_alarm_date as alarmDate,
        pro_alarm_time as alarmTime,
        pro_total_activity_duration_hours as totalActivityDurationHours,
        pro_chargeable_activity_duration_hours as chargeableActivityDurationHours,
        pro_sla_response_hours as slaResponseHours,
        pro_escalated_flag as escalatedFlag,
        pro_escalated_consno as escalatedUserID,
        pro_reopened_flag as reopenedFlag,
        pro_contract_cuino as contractCustomerItemID,
        pro_contno as contactID,
        pro_technician_weighting as technicianWeighting,
        pro_rejected_consno as rejectedUserID,
        pro_do_next_flag as doNextFlag,
        pro_rootcauseno as rootCauseID,
        pro_working_hours_alert_sent_flag as workingHoursAlertSentFlag,
        pro_awaiting_customer_response_flag as awaitingCustomerResponseFlag,
        pro_working_hours_calculated_to_time as proWorkingHoursCalculatedToTime,
        pro_total_activity_duration_hours as totalActivityHours,
        cus_name as customerName,
        DATE_FORMAT(pro_date_raised, '%d/%m/%Y') as dateRaisedDMY,
        TIME_FORMAT(pro_date_raised, '%H:%i') as timeRaised,
        TIME_FORMAT(TIMEDIFF(NOW(), pro_date_raised), '%H:%i') as hoursElapsed,
        CONCAT(consultant.firstName,' ',consultant.lastName) as engineerName,
        CONCAT(SUBSTRING(consultant.firstName, 1, 1),SUBSTRING(consultant.lastName, 1, 1 ) ) as engineerInitials
      FROM problem
        LEFT JOIN customer
          ON cus_custno = pro_custno
        LEFT JOIN consultant
          ON cns_consno = pro_consno
      WHERE pro_status NOT IN ('F', 'C')";

    
    $queryString .= " ORDER BY pro_consno, pro_do_next_flag DESC, pro_alarm_date, pro_alarm_time";
    
    return $db->query( $sql )->fetch_all( MYSQLI_ASSOC ); // one associative array
        
  }  
} // End of class
?>
