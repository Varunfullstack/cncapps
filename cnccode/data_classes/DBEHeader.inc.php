<?php /*
* System Header table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEHeader extends DBEntity
{
    const RemoteSupportMinWarnHours = "RemoteSupportMinWarnHours";

    /**
     * calls constructor()
     * @access public
     * @return void
     * @param  void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("Headert");
        $this->addColumn("headerID", DA_ID, DA_NOT_NULL);
        $this->addColumn("name", DA_STRING, DA_NOT_NULL, "hed_name");
        $this->addColumn("add1", DA_STRING, DA_NOT_NULL, "hed_add1");
        $this->addColumn("add2", DA_STRING, DA_ALLOW_NULL, "hed_add2");
        $this->addColumn("add3", DA_STRING, DA_ALLOW_NULL, "hed_add3");
        $this->addColumn("town", DA_STRING, DA_ALLOW_NULL, "hed_town");
        $this->addColumn("county", DA_STRING, DA_ALLOW_NULL, "hed_county");
        $this->addColumn("postcode", DA_STRING, DA_ALLOW_NULL, "hed_postcode");
        $this->addColumn("phone", DA_STRING, DA_ALLOW_NULL, "hed_phone");
        $this->addColumn("fax", DA_STRING, DA_ALLOW_NULL, "hed_fax");
        $this->addColumn("goodsContact", DA_STRING, DA_ALLOW_NULL, "hed_goods_contact");
        $this->addColumn("stdVATCode", DA_STRING, DA_NOT_NULL, "hed_std_vatcode");
        $this->addColumn("billingStartTime", DA_TIME, DA_NOT_NULL, "hed_bill_starttime");
        $this->addColumn("billingEndTime", DA_TIME, DA_NOT_NULL, "hed_bill_endtime");
        $this->addColumn("helpdeskStartTime", DA_TIME, DA_NOT_NULL, "hed_hd_starttime");
        $this->addColumn("helpdeskEndTime", DA_TIME, DA_NOT_NULL, "hed_hd_endtime");
        $this->addColumn("projectStartTime", DA_TIME, DA_NOT_NULL, "hed_pro_starttime");
        $this->addColumn("projectEndTime", DA_TIME, DA_NOT_NULL, "hed_pro_endtime");
        $this->addColumn("gscItemID", DA_ID, DA_NOT_NULL, "hed_gensup_itemno");
        $this->addColumn("portalPin", DA_STRING, DA_NOT_NULL, "hed_portal_pin");
        $this->addColumn("portal24HourPin", DA_STRING, DA_NOT_NULL, "hed_portal_24_hour_pin");
        //$this->addColumn("otAdjustHour", DA_FLOAT, DA_NOT_NULL, "hed_ot_adjust_hour");
        $this->addColumn("mailshot1FlagDef", DA_YN, DA_NOT_NULL, "hed_mailflg1_def");
        $this->addColumn("mailshot2FlagDef", DA_YN, DA_NOT_NULL, "hed_mailflg2_def");
        $this->addColumn("mailshot3FlagDef", DA_YN, DA_NOT_NULL, "hed_mailflg3_def");
        $this->addColumn("mailshot4FlagDef", DA_YN, DA_NOT_NULL, "hed_mailflg4_def");
        $this->addColumn("mailshot5FlagDef", DA_YN, DA_NOT_NULL, "hed_mailflg5_def");
        $this->addColumn("mailshot6FlagDef", DA_YN, DA_NOT_NULL, "hed_mailflg6_def");
        $this->addColumn("mailshot7FlagDef", DA_YN, DA_NOT_NULL, "hed_mailflg7_def");
        $this->addColumn("mailshot8FlagDef", DA_YN, DA_NOT_NULL, "hed_mailflg8_def");
        $this->addColumn("mailshot9FlagDef", DA_YN, DA_NOT_NULL, "hed_mailflg9_def");
        $this->addColumn("mailshot10FlagDef", DA_YN, DA_NOT_NULL, "hed_mailflg10_def");
        $this->addColumn("mailshot1FlagDesc", DA_YN, DA_NOT_NULL, "hed_mailflg1_desc");
        $this->addColumn("mailshot2FlagDesc", DA_YN, DA_NOT_NULL, "hed_mailflg2_desc");
        $this->addColumn("mailshot3FlagDesc", DA_YN, DA_NOT_NULL, "hed_mailflg3_desc");
        $this->addColumn("mailshot4FlagDesc", DA_YN, DA_NOT_NULL, "hed_mailflg4_desc");
        $this->addColumn("mailshot5FlagDesc", DA_YN, DA_NOT_NULL, "hed_mailflg5_desc");
        $this->addColumn("mailshot6FlagDesc", DA_YN, DA_NOT_NULL, "hed_mailflg6_desc");
        $this->addColumn("mailshot7FlagDesc", DA_YN, DA_NOT_NULL, "hed_mailflg7_desc");
        $this->addColumn("mailshot8FlagDesc", DA_YN, DA_NOT_NULL, "hed_mailflg8_desc");
        $this->addColumn("mailshot9FlagDesc", DA_YN, DA_NOT_NULL, "hed_mailflg9_desc");
        $this->addColumn("mailshot10FlagDesc", DA_YN, DA_NOT_NULL, "hed_mailflg10_desc");
        $this->addColumn("helpDeskProblems", DA_MEMO, DA_NOT_NULL, "hed_helpdesk_problems");
        $this->addColumn("hourlyLabourCost", DA_FLOAT, DA_NOT_NULL, "hed_hourly_labour_cost");
        $this->addColumn("highActivityAlertCount", DA_FLOAT, DA_NOT_NULL, "hed_high_activity_alert_count");
        $this->addColumn("priority1Desc", DA_STRING, DA_NOT_NULL, "hed_priority_1_desc");
        $this->addColumn("priority2Desc", DA_STRING, DA_NOT_NULL, "hed_priority_2_desc");
        $this->addColumn("priority3Desc", DA_STRING, DA_NOT_NULL, "hed_priority_3_desc");
        $this->addColumn("priority4Desc", DA_STRING, DA_NOT_NULL, "hed_priority_4_desc");
        $this->addColumn("priority5Desc", DA_STRING, DA_NOT_NULL, "hed_priority_5_desc");
        $this->addColumn("allowedClientIpPattern", DA_STRING, DA_NOT_NULL, "hed_allowed_client_ip_pattern");
        $this->addColumn("hdTeamLimitHours", DA_FLOAT, DA_NOT_NULL, "hed_hd_team_limit_hours");
        $this->addColumn("esTeamLimitHours", DA_FLOAT, DA_NOT_NULL, "hed_es_team_limit_hours");
        $this->addColumn("imTeamLimitHours", DA_FLOAT, DA_NOT_NULL, "hed_im_team_limit_hours");
        $this->addColumn("hdTeamTargetLogPercentage", DA_INTEGER, DA_NOT_NULL, "hed_hd_team_target_log_percentage");
        $this->addColumn("esTeamTargetLogPercentage", DA_FLOAT, DA_NOT_NULL, "hed_es_team_target_log_percentage");
        $this->addColumn("imTeamTargetLogPercentage", DA_FLOAT, DA_NOT_NULL, "hed_im_team_target_log_percentage");
        $this->addColumn("hdTeamTargetSlaPercentage", DA_INTEGER, DA_NOT_NULL, "hed_hd_team_target_sla_percentage");
        $this->addColumn("esTeamTargetSlaPercentage", DA_FLOAT, DA_NOT_NULL, "hed_es_team_target_sla_percentage");
        $this->addColumn("imTeamTargetSlaPercentage", DA_INTEGER, DA_NOT_NULL, "hed_im_team_target_sla_percentage");
        $this->addColumn("hdTeamTargetFixHours", DA_INTEGER, DA_NOT_NULL, "hed_hd_team_target_fix_hours");
        $this->addColumn("esTeamTargetFixHours", DA_FLOAT, DA_NOT_NULL, "hed_es_team_target_fix_hours");
        $this->addColumn("imTeamTargetFixHours", DA_FLOAT, DA_NOT_NULL, "hed_im_team_target_fix_hours");
        $this->addColumn("hdTeamTargetFixQtyPerMonth", DA_INTEGER, DA_NOT_NULL, "hed_hd_team_target_fix_qty_per_month");
        $this->addColumn("esTeamTargetFixQtyPerMonth", DA_INTEGER, DA_NOT_NULL, "hed_es_team_target_fix_qty_per_month");
        $this->addColumn("imTeamTargetFixQtyPerMonth", DA_INTEGER, DA_NOT_NULL, "hed_im_team_target_fix_qty_per_month");
        $this->addColumn("srAutocompleteThresholdHours", DA_FLOAT, DA_NOT_NULL, "hed_sr_autocomplete_threshold_hours");
        $this->addColumn("srPromptContractThresholdHours", DA_FLOAT, DA_NOT_NULL, "hed_sr_prompt_contract_threshold_hours");
        $this->addColumn("remoteSupportWarnHours", DA_FLOAT, DA_NOT_NULL, "hed_remote_support_warn_hours");
        $this->addColumn(self::RemoteSupportMinWarnHours, DA_FLOAT, DA_NOT_NULL, 'hed_remote_support_min_warn_hours');
        $this->addColumn("customerContactWarnHours", DA_FLOAT, DA_NOT_NULL, "hed_customer_contact_warn_hours");
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

class DBEJHeader extends DBEHeader
{
    /**
     * calls constructor()
     * @access public
     * @return void
     * @param  void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setAddColumnsOn();
        $this->addColumn("gscItemDescription", DA_STRING, DA_NOT_NULL, "itm_desc");
        $this->setAddColumnsOff();
    }

    function getRow()
    {
        $this->setMethodName("getRow");
        $ret = FALSE;
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN item ON hed_gensup_itemno = itm_itemno"
        );
        return (parent::getRow());
    }
}

?>