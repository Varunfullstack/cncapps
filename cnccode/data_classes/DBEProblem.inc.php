<?
/*
* Call activity problem table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEProblem extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEProblem(&$owner, $pkID = false){
		$this->constructor($owner);
    if ( $pkID ){
      $this->getRow( $pkID );
    }
	}
	/**
	* constructor
	* @access public
	* @return void
	* @param  void
	*/
	function constructor(&$owner){
		parent::constructor($owner);
		$this->setTableName("problem");
 		$this->addColumn("problemID", DA_ID, DA_NOT_NULL, "pro_problemno");
 		$this->addColumn("customerID", DA_INTEGER, DA_ALLOW_NULL, "pro_custno");
 		$this->addColumn("priority", DA_INTEGER, DA_ALLOW_NULL, "pro_priority");
 		$this->addColumn("userID", DA_INTEGER, DA_ALLOW_NULL, "pro_consno");
 		$this->addColumn("status", DA_STRING, DA_ALLOW_NULL, "pro_status");
 		$this->addColumn("dateRaised", DA_INTEGER, DA_ALLOW_NULL, "pro_date_raised");
 		$this->addColumn("fixedUserID", DA_INTEGER, DA_ALLOW_NULL, "pro_fixed_consno");
    $this->addColumn("fixedDate", DA_DATE, DA_ALLOW_NULL, "pro_fixed_date");
    $this->addColumn("respondedHours", DA_FLOAT, DA_ALLOW_NULL, "pro_responded_hours");
    $this->addColumn("workingHours", DA_FLOAT, DA_ALLOW_NULL, "pro_working_hours");
    $this->addColumn("sentSlaAlertFlag", DA_YN, DA_ALLOW_NULL, "pro_sent_sla_alert_flag");
    $this->addColumn("internalNotes", DA_MEMO, DA_ALLOW_NULL, "pro_internal_notes");
    $this->addColumn("completionAlertCount", DA_INTEGER, DA_ALLOW_NULL, "pro_completion_alert_count");
    $this->addColumn("completionAlertCount", DA_INTEGER, DA_ALLOW_NULL, "pro_completion_alert_count");
    $this->addColumn("completeDate", DA_DATE, DA_ALLOW_NULL, "pro_complete_date");
    $this->addColumn("hideFromCustomerFlag", DA_STRING, DA_ALLOW_NULL, "pro_hide_from_customer_flag");
    $this->addColumn("alarmDate", DA_DATE, DA_ALLOW_NULL, "pro_alarm_date");    
    $this->addColumn("alarmTime", DA_TIME, DA_ALLOW_NULL, "pro_alarm_time");
    $this->addColumn("totalActivityDurationHours", DA_FLOAT, DA_ALLOW_NULL, "pro_total_activity_duration_hours");
   
    $this->addColumn("totalTravelActivityDurationHours", DA_FLOAT, DA_ALLOW_NULL, "pro_total_travel_activity_duration_hours");
   
    $this->addColumn("chargableActivityDurationHours", DA_FLOAT, DA_ALLOW_NULL, "pro_chargeable_activity_duration_hours");
    $this->addColumn("slaResponseHours", DA_FLOAT, DA_ALLOW_NULL, "pro_sla_response_hours");
    $this->addColumn("escalatedFlag", DA_YN, DA_ALLOW_NULL, "pro_escalated_flag");
    $this->addColumn("escalatedUserID", DA_INTEGER, DA_ALLOW_NULL, "pro_escalated_consno");
    $this->addColumn("reopenedFlag", DA_YN, DA_ALLOW_NULL, "pro_reopened_flag");
    $this->addColumn("contractCustomerItemID", DA_INTEGER, DA_ALLOW_NULL, "pro_contract_cuino");

    $this->addColumn("contactID", DA_INTEGER, DA_ALLOW_NULL, "pro_contno");

    $this->addColumn("technicianWeighting", DA_INTEGER, DA_ALLOW_NULL, "pro_technician_weighting");

    $this->addColumn("rejectedUserID", DA_INTEGER, DA_ALLOW_NULL, "pro_rejected_consno");
    $this->addColumn("doNextFlag", DA_YN, DA_ALLOW_NULL, "pro_do_next_flag");
    $this->addColumn("rootCauseID", DA_INTEGER, DA_ALLOW_NULL, "pro_rootcauseno");
    $this->addColumn("workingHoursAlertSentFlag", DA_YN, DA_ALLOW_NULL, "pro_working_hours_alert_sent_flag");
    $this->addColumn("awaitingCustomerResponseFlag", DA_YN, DA_ALLOW_NULL, "pro_awaiting_customer_response_flag");
    $this->addColumn("workingHoursCalculatedToTime", DA_YN, DA_ALLOW_NULL, "pro_working_hours_calculated_to_time");
    $this->addColumn("monitorAgentName", DA_STRING, DA_ALLOW_NULL, "pro_monitor_agent_name");
    $this->addColumn("monitorName", DA_STRING, DA_ALLOW_NULL, "pro_monitor_name");
    $this->addColumn("projectID", DA_INTEGER, DA_ALLOW_NULL, "pro_projectno");
    $this->addColumn("linkedSalesOrderID", DA_INTEGER, DA_ALLOW_NULL, "pro_linked_ordno");
    $this->addColumn("criticalFlag", DA_YN, DA_ALLOW_NULL, "pro_critical_flag");
    $this->addColumn("queueNo", DA_INTEGER, DA_ALLOW_NULL, "pro_queue_no");
    $this->addColumn("hdRemainHours", DA_FLOAT, DA_NOT_NULL, "pro_hd_remain_hours"); // Helpdesk team remaining hours
    $this->addColumn("esRemainHours", DA_FLOAT, DA_NOT_NULL, "pro_es_remain_hours");     $this->addColumn("imRemainHours", DA_FLOAT, DA_NOT_NULL, "pro_im_remain_hours");     $this->addColumn("hdTimeAlertFlag", DA_YN, DA_ALLOW_NULL, "pro_hd_time_alert_flag");
    $this->addColumn("esTimeAlertFlag", DA_YN, DA_ALLOW_NULL, "pro_es_time_alert_flag");
    $this->addColumn("imTimeAlertFlag", DA_YN, DA_ALLOW_NULL, "pro_im_time_alert_flag");
    $this->addColumn("hdPauseCount", DA_INTEGER, DA_ALLOW_NULL, "pro_hd_pause_count");
    $this->addColumn("managementReviewReason", DA_MEMO, DA_ALLOW_NULL, "pro_management_review_reason");
    $this->addColumn("startedUserID", DA_INTEGER, DA_ALLOW_NULL, "pro_started_consno");
  $this->setAddColumnsOff();
		$this->setPK(0);
	}
  
  public function getManagementReviews( $customerID, $startYearMonth, $endYearMonth )
  {
    $this->setQueryString(
    
      "SELECT " . $this->getDBColumnNamesAsString() .
      " FROM " . $this->getTableName() .
      " WHERE " . $this->getDBColumnName( 'customerID' ) . ' = ' . $customerID .
      " AND " . $this->getDBColumnName( 'completeDate' ) . " BETWEEN '" . $startYearMonth . "-01' AND '" . $endYearMonth . "-31'
      AND " . $this->getDBColumnName( 'managementReviewReason' ) . "<> ''"
    );
    
    return parent::getRows();
  }
}
?>