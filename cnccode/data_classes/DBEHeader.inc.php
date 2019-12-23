<?php /*
* System Header table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEHeader extends DBEntity
{
    const headerID = "headerID";
    const name = "name";
    const add1 = "add1";
    const add2 = "add2";
    const add3 = "add3";
    const town = "town";
    const county = "county";
    const postcode = "postcode";
    const phone = "phone";
    const fax = "fax";
    const goodsContact = "goodsContact";
    const stdVATCode = "stdVATCode";
    const billingStartTime = "billingStartTime";
    const billingEndTime = "billingEndTime";
    const helpdeskStartTime = "helpdeskStartTime";
    const helpdeskEndTime = "helpdeskEndTime";
    const projectStartTime = "projectStartTime";
    const projectEndTime = "projectEndTime";
    const gscItemID = "gscItemID";
    const portalPin = "portalPin";
    const portal24HourPin = "portal24HourPin";
    const mailshot2FlagDef = "mailshot2FlagDef";
    const mailshot3FlagDef = "mailshot3FlagDef";
    const mailshot4FlagDef = "mailshot4FlagDef";
    const mailshot8FlagDef = "mailshot8FlagDef";
    const mailshot9FlagDef = "mailshot9FlagDef";
    const mailshot11FlagDef = "mailshot11FlagDef";
    const mailshot2FlagDesc = "mailshot2FlagDesc";
    const mailshot3FlagDesc = "mailshot3FlagDesc";
    const mailshot4FlagDesc = "mailshot4FlagDesc";
    const mailshot8FlagDesc = "mailshot8FlagDesc";
    const mailshot9FlagDesc = "mailshot9FlagDesc";
    const mailshot11FlagDesc = "mailshot11FlagDesc";
    const hourlyLabourCost = "hourlyLabourCost";
    const highActivityAlertCount = "highActivityAlertCount";
    const priority1Desc = "priority1Desc";
    const priority2Desc = "priority2Desc";
    const priority3Desc = "priority3Desc";
    const priority4Desc = "priority4Desc";
    const priority5Desc = "priority5Desc";
    const allowedClientIpPattern = "allowedClientIpPattern";
    const hdTeamLimitMinutes = "hdTeamLimitHours";
    const esTeamLimitMinutes = "esTeamLimitHours";
    const imTeamLimitMinutes = "imTeamLimitHours";
    const hdTeamTargetLogPercentage = "hdTeamTargetLogPercentage";
    const esTeamTargetLogPercentage = "esTeamTargetLogPercentage";
    const imTeamTargetLogPercentage = "imTeamTargetLogPercentage";
    const hdTeamTargetSlaPercentage = "hdTeamTargetSlaPercentage";
    const esTeamTargetSlaPercentage = "esTeamTargetSlaPercentage";
    const imTeamTargetSlaPercentage = "imTeamTargetSlaPercentage";
    const hdTeamTargetFixHours = "hdTeamTargetFixHours";
    const esTeamTargetFixHours = "esTeamTargetFixHours";
    const imTeamTargetFixHours = "imTeamTargetFixHours";
    const hdTeamTargetFixQtyPerMonth = "hdTeamTargetFixQtyPerMonth";
    const esTeamTargetFixQtyPerMonth = "esTeamTargetFixQtyPerMonth";
    const imTeamTargetFixQtyPerMonth = "imTeamTargetFixQtyPerMonth";
    const srAutocompleteThresholdHours = "srAutocompleteThresholdHours";
    const srPromptContractThresholdHours = "srPromptContractThresholdHours";
    const remoteSupportWarnHours = "remoteSupportWarnHours";
    const customerContactWarnHours = "customerContactWarnHours";
    const RemoteSupportMinWarnHours = "RemoteSupportMinWarnHours";
    const ImplementationTeamMinutesInADay = "imTeamMinutesInADay";
    const backupTargetSuccessRate = "backupTargetSuccessRate";
    const backupReplicationTargetSuccessRate = "backupReplicationTargetSuccessRate";
    const customerReviewMeetingText = "customerReviewMeetingText";
    const serviceDeskNotification24hBegin = "serviceDeskNotification24hBegin";
    const serviceDeskNotification24hEnd = "serviceDeskNotification24hEnd";
    const srStartersLeaversAutoCompleteThresholdHours = "srStartersLeaversAutoCompleteThresholdHours";
    const SDDashboardEngineersInSRInPastHours = "SDDashboardEngineersInSRInPastHours";
    const secondSiteReplicationAdditionalDelayAllowance = "secondSiteReplicationAdditionalDelayAllowance";
    const SDDashboardEngineersInSREngineersMaxCount = "SDDashboardEngineersInSREngineersMaxCount";
    const projectCommenceNotification = "projectCommenceNotification";
    const OSSupportDatesThresholdDays = "OSSupportDatesThresholdDays";
    const closingSRBufferMinutes = "closingSRBufferMinutes";
    const sevenDayerAmberDays = "sevenDayerAmberDays";
    const sevenDayerRedDays = "sevenDayerRedDays";
    const office365MailboxYellowWarningThreshold = "office365MailboxYellowWarningThreshold";
    const office365MailboxRedWarningThreshold = "office365MailboxRedWarningThreshold";
    const autoCriticalP1Hours = "autoCriticalP1Hours";
    const autoCriticalP2Hours = "autoCriticalP2Hours";
    const autoCriticalP3Hours = "autoCriticalP3Hours";
    const sevenDayerTarget = "sevenDayerTarget";
    const minimumOvertimeMinutesRequired = "minimumOvertimeMinutesRequired";
    const expensesNextProcessingDate = "expensesNextProcessingDate";
    const daysInAdvanceExpensesNextMonthAlert = "daysInAdvanceExpensesNextMonthAlert";
    const closureReminderDays = "closureReminderDays";
    const solarwindsPartnerName = "solarwindsPartnerName";
    const solarwindsUsername = "solarwindsUsername";
    const solarwindsPassword = "solarwindsPassword";

    /**
     * calls constructor()
     * @access public
     * @param void
     * @return void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("Headert");
        $this->addColumn(
            self::headerID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::name,
            DA_STRING,
            DA_NOT_NULL,
            "hed_name"
        );
        $this->addColumn(
            self::add1,
            DA_STRING,
            DA_NOT_NULL,
            "hed_add1"
        );
        $this->addColumn(
            self::add2,
            DA_STRING,
            DA_ALLOW_NULL,
            "hed_add2"
        );
        $this->addColumn(
            self::add3,
            DA_STRING,
            DA_ALLOW_NULL,
            "hed_add3"
        );
        $this->addColumn(
            self::town,
            DA_STRING,
            DA_ALLOW_NULL,
            "hed_town"
        );
        $this->addColumn(
            self::county,
            DA_STRING,
            DA_ALLOW_NULL,
            "hed_county"
        );
        $this->addColumn(
            self::postcode,
            DA_STRING,
            DA_ALLOW_NULL,
            "hed_postcode"
        );
        $this->addColumn(
            self::phone,
            DA_STRING,
            DA_ALLOW_NULL,
            "hed_phone"
        );
        $this->addColumn(
            self::fax,
            DA_STRING,
            DA_ALLOW_NULL,
            "hed_fax"
        );
        $this->addColumn(
            self::goodsContact,
            DA_STRING,
            DA_ALLOW_NULL,
            "hed_goods_contact"
        );
        $this->addColumn(
            self::stdVATCode,
            DA_STRING,
            DA_NOT_NULL,
            "hed_std_vatcode"
        );
        $this->addColumn(
            self::billingStartTime,
            DA_TIME,
            DA_NOT_NULL,
            "hed_bill_starttime"
        );
        $this->addColumn(
            self::billingEndTime,
            DA_TIME,
            DA_NOT_NULL,
            "hed_bill_endtime"
        );
        $this->addColumn(
            self::helpdeskStartTime,
            DA_TIME,
            DA_NOT_NULL,
            "hed_hd_starttime"
        );
        $this->addColumn(
            self::helpdeskEndTime,
            DA_TIME,
            DA_NOT_NULL,
            "hed_hd_endtime"
        );
        $this->addColumn(
            self::projectStartTime,
            DA_TIME,
            DA_NOT_NULL,
            "hed_pro_starttime"
        );
        $this->addColumn(
            self::projectEndTime,
            DA_TIME,
            DA_NOT_NULL,
            "hed_pro_endtime"
        );
        $this->addColumn(
            self::gscItemID,
            DA_ID,
            DA_NOT_NULL,
            "hed_gensup_itemno"
        );
        $this->addColumn(
            self::portalPin,
            DA_STRING,
            DA_NOT_NULL,
            "hed_portal_pin"
        );
        $this->addColumn(
            self::portal24HourPin,
            DA_STRING,
            DA_NOT_NULL,
            "hed_portal_24_hour_pin"
        );
        $this->addColumn(
            self::mailshot2FlagDef,
            DA_YN,
            DA_NOT_NULL,
            "hed_mailflg2_def"
        );
        $this->addColumn(
            self::mailshot3FlagDef,
            DA_YN,
            DA_NOT_NULL,
            "hed_mailflg3_def"
        );
        $this->addColumn(
            self::mailshot4FlagDef,
            DA_YN,
            DA_NOT_NULL,
            "hed_mailflg4_def"
        );
        $this->addColumn(
            self::mailshot8FlagDef,
            DA_YN,
            DA_NOT_NULL,
            "hed_mailflg8_def"
        );
        $this->addColumn(
            self::mailshot9FlagDef,
            DA_YN,
            DA_NOT_NULL,
            "hed_mailflg9_def"
        );
        $this->addColumn(
            self::mailshot11FlagDef,
            DA_YN,
            DA_NOT_NULL,
            "hed_mailflg11_def"
        );
        $this->addColumn(
            self::mailshot2FlagDesc,
            DA_TEXT,
            DA_NOT_NULL,
            "hed_mailflg2_desc"
        );
        $this->addColumn(
            self::mailshot3FlagDesc,
            DA_TEXT,
            DA_NOT_NULL,
            "hed_mailflg3_desc"
        );
        $this->addColumn(
            self::mailshot4FlagDesc,
            DA_STRING,
            DA_NOT_NULL,
            "hed_mailflg4_desc"
        );
        $this->addColumn(
            self::mailshot8FlagDesc,
            DA_STRING,
            DA_NOT_NULL,
            "hed_mailflg8_desc"
        );
        $this->addColumn(
            self::mailshot9FlagDesc,
            DA_STRING,
            DA_NOT_NULL,
            "hed_mailflg9_desc"
        );
        $this->addColumn(
            self::mailshot11FlagDesc,
            DA_STRING,
            DA_NOT_NULL,
            "hed_mailflg11_desc"
        );
        $this->addColumn(
            self::hourlyLabourCost,
            DA_FLOAT,
            DA_NOT_NULL,
            "hed_hourly_labour_cost"
        );
        $this->addColumn(
            self::highActivityAlertCount,
            DA_FLOAT,
            DA_NOT_NULL,
            "hed_high_activity_alert_count"
        );
        $this->addColumn(
            self::priority1Desc,
            DA_STRING,
            DA_NOT_NULL,
            "hed_priority_1_desc"
        );
        $this->addColumn(
            self::priority2Desc,
            DA_STRING,
            DA_NOT_NULL,
            "hed_priority_2_desc"
        );
        $this->addColumn(
            self::priority3Desc,
            DA_STRING,
            DA_NOT_NULL,
            "hed_priority_3_desc"
        );
        $this->addColumn(
            self::priority4Desc,
            DA_STRING,
            DA_NOT_NULL,
            "hed_priority_4_desc"
        );
        $this->addColumn(
            self::priority5Desc,
            DA_STRING,
            DA_NOT_NULL,
            "hed_priority_5_desc"
        );
        $this->addColumn(
            self::allowedClientIpPattern,
            DA_STRING,
            DA_NOT_NULL,
            "hed_allowed_client_ip_pattern"
        );
        $this->addColumn(
            self::hdTeamLimitMinutes,
            DA_FLOAT,
            DA_NOT_NULL,
            "hed_hd_team_limit_minutes"
        );
        $this->addColumn(
            self::esTeamLimitMinutes,
            DA_FLOAT,
            DA_NOT_NULL,
            "hed_es_team_limit_minutes"
        );
        $this->addColumn(
            self::imTeamLimitMinutes,
            DA_FLOAT,
            DA_NOT_NULL,
            "hed_im_team_limit_minutes"
        );
        $this->addColumn(
            self::hdTeamTargetLogPercentage,
            DA_INTEGER,
            DA_NOT_NULL,
            "hed_hd_team_target_log_percentage"
        );
        $this->addColumn(
            self::esTeamTargetLogPercentage,
            DA_FLOAT,
            DA_NOT_NULL,
            "hed_es_team_target_log_percentage"
        );
        $this->addColumn(
            self::imTeamTargetLogPercentage,
            DA_FLOAT,
            DA_NOT_NULL,
            "hed_im_team_target_log_percentage"
        );
        $this->addColumn(
            self::hdTeamTargetSlaPercentage,
            DA_INTEGER,
            DA_NOT_NULL,
            "hed_hd_team_target_sla_percentage"
        );
        $this->addColumn(
            self::esTeamTargetSlaPercentage,
            DA_FLOAT,
            DA_NOT_NULL,
            "hed_es_team_target_sla_percentage"
        );
        $this->addColumn(
            self::imTeamTargetSlaPercentage,
            DA_INTEGER,
            DA_NOT_NULL,
            "hed_im_team_target_sla_percentage"
        );
        $this->addColumn(
            self::hdTeamTargetFixHours,
            DA_FLOAT,
            DA_NOT_NULL,
            "hed_hd_team_target_fix_hours"
        );
        $this->addColumn(
            self::esTeamTargetFixHours,
            DA_FLOAT,
            DA_NOT_NULL,
            "hed_es_team_target_fix_hours"
        );
        $this->addColumn(
            self::imTeamTargetFixHours,
            DA_FLOAT,
            DA_NOT_NULL,
            "hed_im_team_target_fix_hours"
        );
        $this->addColumn(
            self::hdTeamTargetFixQtyPerMonth,
            DA_INTEGER,
            DA_NOT_NULL,
            "hed_hd_team_target_fix_qty_per_month"
        );
        $this->addColumn(
            self::esTeamTargetFixQtyPerMonth,
            DA_INTEGER,
            DA_NOT_NULL,
            "hed_es_team_target_fix_qty_per_month"
        );
        $this->addColumn(
            self::imTeamTargetFixQtyPerMonth,
            DA_INTEGER,
            DA_NOT_NULL,
            "hed_im_team_target_fix_qty_per_month"
        );
        $this->addColumn(
            self::srAutocompleteThresholdHours,
            DA_FLOAT,
            DA_NOT_NULL,
            "hed_sr_autocomplete_threshold_hours"
        );
        $this->addColumn(
            self::srPromptContractThresholdHours,
            DA_FLOAT,
            DA_NOT_NULL,
            "hed_sr_prompt_contract_threshold_hours"
        );
        $this->addColumn(
            self::remoteSupportWarnHours,
            DA_FLOAT,
            DA_NOT_NULL,
            "hed_remote_support_warn_hours"
        );
        $this->addColumn(
            self::customerContactWarnHours,
            DA_FLOAT,
            DA_NOT_NULL,
            "hed_customer_contact_warn_hours"
        );
        $this->addColumn(
            self::RemoteSupportMinWarnHours,
            DA_FLOAT,
            DA_NOT_NULL,
            'hed_remote_support_min_warn_hours'
        );
        $this->addColumn(
            self::ImplementationTeamMinutesInADay,
            DA_INTEGER,
            DA_NOT_NULL,
            'hed_im_team_minutes_in_a_day'
        );
        $this->addColumn(
            self::backupTargetSuccessRate,
            DA_INTEGER,
            DA_NOT_NULL,
            "hed_backup_target_success_rate"
        );
        $this->addColumn(
            self::backupReplicationTargetSuccessRate,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::customerReviewMeetingText,
            DA_STRING,
            DA_ALLOW_NULL,
            "customer_review_meeting_text"
        );
        $this->addColumn(
            self::serviceDeskNotification24hBegin,
            DA_TIME,
            DA_NOT_NULL,
            "hed_sd_notification_24_7_begin"
        );
        $this->addColumn(
            self::serviceDeskNotification24hEnd,
            DA_TIME,
            DA_NOT_NULL,
            "hed_sd_notification_24_7_end"
        );
        $this->addColumn(
            self::srStartersLeaversAutoCompleteThresholdHours,
            DA_FLOAT,
            DA_NOT_NULL,
            'hed_srStartersLeaversAutoCompleteThresholdHours'
        );
        $this->addColumn(
            self::SDDashboardEngineersInSREngineersMaxCount,
            DA_INTEGER,
            DA_NOT_NULL,
            'hed_sd_dashboard_engineers_in_sr_engineers_max_count'
        );
        $this->addColumn(
            self::SDDashboardEngineersInSRInPastHours,
            DA_INTEGER,
            DA_NOT_NULL,
            'hed_sd_dashboard_engineers_in_sr_in_past_hours'
        );
        $this->addColumn(
            self::secondSiteReplicationAdditionalDelayAllowance,
            DA_INTEGER,
            DA_NOT_NULL,
            'secondSiteReplicationAdditionalDelayAllowance'
        );

        $this->addColumn(
            self::projectCommenceNotification,
            DA_INTEGER,
            DA_NOT_NULL
        );

        $this->addColumn(
            self::OSSupportDatesThresholdDays,
            DA_INTEGER,
            DA_NOT_NULL
        );

        $this->addColumn(
            self::closingSRBufferMinutes,
            DA_INTEGER,
            DA_NOT_NULL
        );

        $this->addColumn(
            self::office365MailboxYellowWarningThreshold,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::office365MailboxRedWarningThreshold,
            DA_INTEGER,
            DA_NOT_NULL
        );

        $this->addColumn(self::sevenDayerAmberDays, DA_INTEGER, DA_NOT_NULL);
        $this->addColumn(self::sevenDayerTarget, DA_INTEGER, DA_NOT_NULL);
        $this->addColumn(self::sevenDayerRedDays, DA_INTEGER, DA_NOT_NULL);
        $this->addColumn(self::autoCriticalP1Hours, DA_FLOAT, DA_NOT_NULL);
        $this->addColumn(self::autoCriticalP2Hours, DA_FLOAT, DA_NOT_NULL);
        $this->addColumn(self::autoCriticalP3Hours, DA_FLOAT, DA_NOT_NULL);
        $this->addColumn(self::minimumOvertimeMinutesRequired, DA_INTEGER, DA_NOT_NULL);
        $this->addColumn(self::expensesNextProcessingDate, DA_DATE, DA_ALLOW_NULL);
        $this->addColumn(self::daysInAdvanceExpensesNextMonthAlert, DA_INTEGER, DA_NOT_NULL);
        $this->addColumn(self::closureReminderDays, DA_INTEGER, DA_NOT_NULL);
        $this->addColumn(self::solarwindsPartnerName, DA_TEXT, DA_NOT_NULL);
        $this->addColumn(self::solarwindsUsername, DA_TEXT, DA_NOT_NULL);
        $this->addColumn(self::solarwindsPassword, DA_TEXT, DA_NOT_NULL);

        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>