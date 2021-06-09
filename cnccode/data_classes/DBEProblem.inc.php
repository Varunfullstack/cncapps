<?php /*
* Call activity problem table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEProblem extends DBEntity
{
    const problemID                        = "problemID";
    const customerID                       = "customerID";
    const priority                         = "priority";
    const userID                           = "userID";
    const status                           = "status";
    const dateRaised                       = "dateRaised";
    const fixedUserID                      = "fixedUserID";
    const fixedDate                        = "fixedDate";
    const respondedHours                   = "respondedHours";
    const workingHours                     = "workingHours";
    const sentSlaAlertFlag                 = "sentSlaAlertFlag";
    const completionAlertCount             = "completionAlertCount";
    const completeDate                     = "completeDate";
    const hideFromCustomerFlag             = "hideFromCustomerFlag";
    const alarmDate                        = "alarmDate";
    const alarmTime                        = "alarmTime";
    const totalActivityDurationHours       = "totalActivityDurationHours";
    const totalTravelActivityDurationHours = "totalTravelActivityDurationHours";
    const chargeableActivityDurationHours  = "chargeableActivityDurationHours";
    const slaResponseHours                 = "slaResponseHours";
    const escalatedFlag                    = "escalatedFlag";
    const escalatedUserID                  = "escalatedUserID";
    const reopenedFlag                     = "reopenedFlag";
    const contractCustomerItemID           = "contractCustomerItemID";
    const contactID                        = "contactID";
    const rejectedUserID                   = "rejectedUserID";
    const doNextFlag                       = "doNextFlag";
    const rootCauseID                      = "rootCauseID";
    const workingHoursAlertSentFlag        = "workingHoursAlertSentFlag";
    const awaitingCustomerResponseFlag     = "awaitingCustomerResponseFlag";
    const workingHoursCalculatedToTime     = "workingHoursCalculatedToTime";
    const monitorAgentName                 = "monitorAgentName";
    const monitorName                      = "monitorName";
    const projectID                        = "projectID";
    const linkedSalesOrderID               = "linkedSalesOrderID";
    const criticalFlag                     = "criticalFlag";
    const queueNo                          = "queueNo";
    const hdLimitMinutes                   = "hdLimitHours";
    const esLimitMinutes                   = "esLimitHours";
    const smallProjectsTeamLimitMinutes    = "imLimitHours";
    const projectTeamLimitMinutes          = 'projectTeamLimitMinutes';
    const hdTimeAlertFlag                  = "hdTimeAlertFlag";
    const esTimeAlertFlag                  = "esTimeAlertFlag";
    const smallProjectsTeamTimeAlertFlag   = "imTimeAlertFlag";
    const hdPauseCount                     = "hdPauseCount";
    const managementReviewReason           = "managementReviewReason";
    const startedUserID                    = "startedUserID";
    const reopenedDate                     = "reopenedDate";
    const authorisedBy                     = "authorisedBy";
    const openHours                        = "openHours";
    const projectTeamTimeAlertFlag         = "projectTeamTimeAlertFlag";
    const raiseTypeId                      = "raiseTypeId";
    const salesRequestAssignedUserId       = "salesRequestAssignedUserId";
    const emailSubjectSummary              = "emailSubjectSummary";
    const assetName                        = "assetName";
    const assetTitle                       = "assetTitle";
    const repeatProblem                    = "repeatProblem";
    const notFirstTimeFixReason            = "notFirstTimeFixReason";
    const emptyAssetReason                 = "emptyAssetReason";
    const holdForQA                        = "holdForQA";
    const taskList                         = "taskList";
    const taskListUpdatedBy                = "taskListUpdatedBy";
    const taskListUpdatedAt                = "taskListUpdatedAt";
    const prePayChargeApproved             = "prepayChargeApproved";

    /**
     * calls constructor()
     * @access public
     *
     * @param $owner
     * @param bool $pkID
     * @internal param $void
     * @see constructor()
     */
    function __construct(&$owner,
                         $pkID = false
    )
    {
        parent::__construct($owner);
        $this->setTableName("problem");
        $this->addColumn(
            self::problemID,
            DA_ID,
            DA_NOT_NULL,
            "pro_problemno"
        );
        $this->addColumn(
            self::customerID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "pro_custno"
        );
        $this->addColumn(
            self::priority,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "pro_priority"
        );
        $this->addColumn(
            self::userID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "pro_consno"
        );
        $this->addColumn(
            self::status,
            DA_STRING,
            DA_ALLOW_NULL,
            "pro_status"
        );
        $this->addColumn(
            self::dateRaised,
            DA_DATETIME,
            DA_ALLOW_NULL,
            "pro_date_raised"
        );
        $this->addColumn(
            self::fixedUserID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "pro_fixed_consno"
        );
        $this->addColumn(
            self::fixedDate,
            DA_DATETIME,
            DA_ALLOW_NULL,
            "pro_fixed_date"
        );
        $this->addColumn(
            self::respondedHours,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "pro_responded_hours",
            0.00
        );
        $this->addColumn(
            self::workingHours,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "pro_working_hours",
            0.00
        );
        $this->addColumn(
            self::sentSlaAlertFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "pro_sent_sla_alert_flag"
        );
        $this->addColumn(
            self::completionAlertCount,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "pro_completion_alert_count",
            0
        );
        $this->addColumn(
            self::completeDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "pro_complete_date"
        );
        $this->addColumn(
            self::hideFromCustomerFlag,
            DA_STRING,
            DA_ALLOW_NULL,
            "pro_hide_from_customer_flag",
            'N'
        );
        $this->addColumn(
            self::alarmDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "pro_alarm_date"
        );
        $this->addColumn(
            self::alarmTime,
            DA_TIME,
            DA_ALLOW_NULL,
            "pro_alarm_time"
        );
        $this->addColumn(
            self::totalActivityDurationHours,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "pro_total_activity_duration_hours",
            0.00
        );
        $this->addColumn(
            self::totalTravelActivityDurationHours,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "pro_total_travel_activity_duration_hours",
            0.00
        );
        $this->addColumn(
            self::chargeableActivityDurationHours,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "pro_chargeable_activity_duration_hours",
            0.00
        );
        $this->addColumn(
            self::slaResponseHours,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "pro_sla_response_hours",
            0.00
        );
        $this->addColumn(
            self::escalatedFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "pro_escalated_flag"
        );
        $this->addColumn(
            self::escalatedUserID,
            DA_ID,
            DA_ALLOW_NULL,
            "pro_escalated_consno"
        );
        $this->addColumn(
            self::reopenedFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "pro_reopened_flag"
        );
        $this->addColumn(
            self::reopenedDate,
            DA_DATE,
            DA_ALLOW_NULL,
            'pro_reopened_date'
        );
        $this->addColumn(
            self::contractCustomerItemID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "pro_contract_cuino"
        );
        $this->addColumn(
            self::contactID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "pro_contno"
        );
        $this->addColumn(
            self::rootCauseID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "pro_rootcauseno"
        );
        $this->addColumn(
            self::workingHoursAlertSentFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "pro_working_hours_alert_sent_flag"
        );
        $this->addColumn(
            self::awaitingCustomerResponseFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "pro_awaiting_customer_response_flag"
        );
        $this->addColumn(
            self::workingHoursCalculatedToTime,
            DA_DATETIME,
            DA_ALLOW_NULL,
            "pro_working_hours_calculated_to_time"
        );
        $this->addColumn(
            self::monitorAgentName,
            DA_STRING,
            DA_ALLOW_NULL,
            "pro_monitor_agent_name"
        );
        $this->addColumn(
            self::monitorName,
            DA_STRING,
            DA_ALLOW_NULL,
            "pro_monitor_name"
        );
        $this->addColumn(
            self::projectID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "pro_projectno"
        );
        $this->addColumn(
            self::linkedSalesOrderID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "pro_linked_ordno"
        );
        $this->addColumn(
            self::criticalFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "pro_critical_flag"
        );
        $this->addColumn(
            self::queueNo,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "pro_queue_no"
        );
        $this->addColumn(
            self::hdLimitMinutes,
            DA_INTEGER,
            DA_NOT_NULL,
            "pro_hd_limit_minutes"
        ); // Helpdesk team remaining hours
        $this->addColumn(
            self::esLimitMinutes,
            DA_INTEGER,
            DA_NOT_NULL,
            "pro_es_limit_minutes"
        );
        $this->addColumn(
            self::smallProjectsTeamLimitMinutes,
            DA_INTEGER,
            DA_NOT_NULL,
            "pro_im_limit_minutes"
        );
        $this->addColumn(
            self::projectTeamLimitMinutes,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::hdTimeAlertFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "pro_hd_time_alert_flag"
        );
        $this->addColumn(
            self::esTimeAlertFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "pro_es_time_alert_flag"
        );
        $this->addColumn(
            self::smallProjectsTeamTimeAlertFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "pro_im_time_alert_flag"
        );
        $this->addColumn(
            self::projectTeamTimeAlertFlag,
            DA_YN,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::hdPauseCount,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "pro_hd_pause_count"
        );
        $this->addColumn(
            self::managementReviewReason,
            DA_MEMO,
            DA_ALLOW_NULL,
            "pro_management_review_reason"
        );
        $this->addColumn(
            self::startedUserID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "pro_started_consno"
        );
        $this->addColumn(
            self::authorisedBy,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::openHours,
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::raiseTypeId,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::salesRequestAssignedUserId,
            DA_ID,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::emailSubjectSummary,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::assetName,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::assetTitle,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::repeatProblem,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::notFirstTimeFixReason,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::emptyAssetReason,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::holdForQA,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            false
        );
        $this->addColumn(
            self::taskList,
            DA_TEXT,
            DA_ALLOW_NULL,
        );
        $this->addColumn(
            self::taskListUpdatedBy,
            DA_ID,
            DA_ALLOW_NULL,
        );
        $this->addColumn(
            self::taskListUpdatedAt,
            DA_DATETIME,
            DA_ALLOW_NULL,
        );
        $this->addColumn(
            self::prePayChargeApproved,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            0
        );
        $this->setAddColumnsOff();
        $this->setPK(0);
        if ($pkID) {
            $this->getRow($pkID);
        }
    }

    public function deleteRow($pkValue = '')
    {
        parent::deleteRow($pkValue);
        global $db;
        $query = "delete feedbacktoken from feedbacktoken left join problem on problem.pro_problemno = feedbacktoken.serviceRequestId where problem.pro_problemno is null";
        $db->query($query);

    }

    public static function statusFromDB($dbValue)
    {
        $matches = [
            "I" => "Logged",
            "P" => "In Progress",
            "F" => "Fixed",
            "C" => "Closed"
        ];
        return $matches[$dbValue];
    }

    public function getManagementReviews($customerID,
                                         DateTimeInterface $startDate,
                                         DateTimeInterface $endDate
    )
    {

        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " WHERE " . $this->getDBColumnName(
                self::customerID
            ) . ' = ' . $customerID . " AND " . $this->getDBColumnName(
                self::completeDate
            ) . " BETWEEN '" . $startDate->format('Y-m-d') . "' AND '" . $endDate->format(
                'Y-m-d'
            ) . "' AND " . $this->getDBColumnName(self::managementReviewReason) . "<> ''"
        );
        return parent::getRows();
    }

    public function getToCheckCriticalFlagSRs()
    {
        $this->setQueryString(
            "SELECT {$this->getDBColumnNamesAsString()} FROM {$this->getTableName(
            )} WHERE {$this->getDBColumnName(self::status)} in ('I','P') AND {$this->getDBColumnName(
                self::priority
            )} in (1,2,3)"
        );
        return parent::getRows();
    }

    public function getUnstartedServiceRequestsForDeletion($search)
    {
        if (!$search) {
            throw new Exception('Search must not be null or empty');
        }
        $escapedSearch = mysqli_real_escape_string($this->db->link_id(), $search);
        $this->setQueryString(
            "SELECT {$this->getDBColumnNamesAsString()} FROM {$this->getTableName()} 
                JOIN callactivity `initial`
    ON initial.caa_problemno = pro_problemno
    AND initial.caa_callacttypeno = 51 
    and initial.reason like '%{$escapedSearch}%'
    and (select count(caa_callactivityno) from callactivity where caa_problemno = pro_problemno) = 1
WHERE {$this->getDBColumnName(self::linkedSalesOrderID)} is null and  {$this->getDBColumnName(self::status)} = 'I' "
        );
        return parent::getRows();
    }

}
