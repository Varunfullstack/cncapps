<?php
/**
 * Call activity business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ["path_gc"] . "/Business.inc.php");

class BUCurrentActivityReport extends Business
{

    /**
     * Constructor
     * @access Public
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    function getProblems($status, $futureOnly = false)
    {
        $queryString = "
      SELECT
        pro_problemno AS problemID,
        pro_custno AS customerID,
        pro_priority AS priority,
        pro_consno AS userID,
        pro_status AS status,
        pro_date_raised AS dateRaised,
        pro_fixed_consno AS fixedUserID,
        pro_fixed_date AS fixedDate,
        pro_responded_hours AS responsedHours,
        pro_working_hours AS workingHours,
        pro_sent_sla_alert_flag AS sendSlaAlertFlag,
        pro_internal_notes AS internalNotes,
        pro_completion_alert_count AS completionAlertCount,
        pro_complete_date AS completionDate,
        pro_hide_from_customer_flag AS hideFromCustomerFlag,
        pro_alarm_date AS alarmDate,
        pro_alarm_time AS alarmTime,
        pro_total_activity_duration_hours AS totalActivityDurationHours,
        pro_chargeable_activity_duration_hours AS chargeableActivityDurationHours,
        pro_sla_response_hours AS slaResponseHours,
        pro_escalated_flag AS escalatedFlag,
        pro_escalated_consno AS escalatedUserID,
        pro_reopened_flag AS reopenedFlag,
        pro_contract_cuino AS contractCustomerItemID,
        pro_contno AS contactID,
        pro_technician_weighting AS technicianWeighting,
        pro_rejected_consno AS rejectedUserID,
        pro_do_next_flag AS doNextFlag,
        pro_rootcauseno AS rootCauseID,
        pro_working_hours_alert_sent_flag AS workingHoursAlertSentFlag,
        pro_awaiting_customer_response_flag AS awaitingCustomerResponseFlag,
        pro_working_hours_calculated_to_time AS proWorkingHoursCalculatedToTime,
        pro_total_activity_duration_hours AS totalActivityHours,
        cus_name AS customerName,
        DATE_FORMAT(pro_date_raised, '%d/%m/%Y') AS dateRaisedDMY,
        TIME_FORMAT(pro_date_raised, '%H:%i') AS timeRaised,
        TIME_FORMAT(TIMEDIFF(NOW(), pro_date_raised), '%H:%i') AS hoursElapsed,
        CONCAT(consultant.firstName,' ',consultant.lastName) AS engineerName,
        CONCAT(SUBSTRING(consultant.firstName, 1, 1),SUBSTRING(consultant.lastName, 1, 1 ) ) AS engineerInitials
      FROM problem
        LEFT JOIN customer
          ON cus_custno = pro_custno
        LEFT JOIN consultant
          ON cns_consno = pro_consno
      WHERE pro_status NOT IN ('F', 'C')";


        $queryString .= " ORDER BY pro_consno, pro_do_next_flag DESC, pro_alarm_date, pro_alarm_time";

        return $this->db->query($queryString)->fetch_all(MYSQLI_ASSOC); // one associative array

    }
} // End of class
?>
