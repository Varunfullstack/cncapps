<?php /*
* Customer table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBECustomer extends DBCNCEntity
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
        $this->setTableName("Customer");
        $this->addColumn("customerID", DA_ID, DA_NOT_NULL, "cus_custno");
        $this->addColumn("name", DA_STRING, DA_NOT_NULL, "cus_name");
        $this->addColumn("invSiteNo", DA_ID, DA_ALLOW_NULL, "cus_inv_siteno");
        $this->addColumn("delSiteNo", DA_ID, DA_ALLOW_NULL, "cus_del_siteno"); // have to be strings so zero sites don't go empty
        $this->addColumn("mailshotFlag", DA_YN, DA_NOT_NULL, "cus_mailshot");
        $this->addColumn("createDate", DA_DATE, DA_NOT_NULL, "cus_create_date");
        $this->addColumn("referredFlag", DA_YN, DA_ALLOW_NULL, "cus_referred");
        $this->addColumn("pcxFlag", DA_YN, DA_ALLOW_NULL, "cus_pcx");
        $this->addColumn("customerTypeID", DA_ID, DA_NOT_NULL, "cus_ctypeno");
        $this->addColumn("prospectFlag", DA_YN, DA_NOT_NULL, "cus_prospect");
        $this->addColumn("othersEmailMainFlag", DA_YN_FLAG, DA_NOT_NULL, "cus_others_email_main_flag");
        $this->addColumn("workStartedEmailMainFlag", DA_YN_FLAG, DA_NOT_NULL, "cus_work_started_email_main_flag");
        $this->addColumn("autoCloseEmailMainFlag", DA_YN_FLAG, DA_NOT_NULL, "cus_auto_close_email_main_flag");
        $this->addColumn("gscTopUpAmount", DA_FLOAT, DA_NOT_NULL);                        // amount to top up general support contract by
        $this->addColumn("reviewDate", DA_DATE, DA_ALLOW_NULL);
        $this->addColumn("reviewTime", DA_TIME, DA_ALLOW_NULL);
        $this->addColumn("reviewAction", DA_STRING, DA_ALLOW_NULL);
        $this->addColumn("reviewUserID", DA_ID, DA_ALLOW_NULL);
        $this->addColumn("sectorID", DA_ID, DA_NOT_NULL, "cus_sectorno");
        $this->addColumn("becameCustomerDate", DA_DATE, DA_ALLOW_NULL, 'cus_became_customer_date');
        $this->addColumn("droppedCustomerDate", DA_DATE, DA_ALLOW_NULL, 'cus_dropped_customer_date');
        $this->addColumn("leadStatusID", DA_ID, DA_ALLOW_NULL, 'cus_leadstatusno');
        $this->addColumn("techNotes", DA_STRING, DA_ALLOW_NULL, 'cus_tech_notes');
        $this->addColumn("specialAttentionFlag", DA_YN_FLAG, DA_NOT_NULL, "cus_special_attention_flag");
        $this->addColumn("specialAttentionEndDate", DA_DATE, DA_ALLOW_NULL, 'cus_special_attention_end_date');
        $this->addColumn("support24HourFlag", DA_YN_FLAG, DA_NOT_NULL, "cus_support_24_hour_flag");
        $this->addColumn("slaP1", DA_INTEGER, DA_NOT_NULL, "cus_sla_p1");
        $this->addColumn("slaP2", DA_INTEGER, DA_NOT_NULL, "cus_sla_p2");
        $this->addColumn("slaP3", DA_INTEGER, DA_NOT_NULL, "cus_sla_p3");
        $this->addColumn("slaP4", DA_INTEGER, DA_NOT_NULL, "cus_sla_p4");
        $this->addColumn("slaP5", DA_INTEGER, DA_NOT_NULL, "cus_sla_p5");
        $this->addColumn("sendContractEmail", DA_INTEGER, DA_NOT_NULL, "cus_send_contract_email");
        $this->addColumn("sendTandcEmail", DA_INTEGER, DA_NOT_NULL, "cus_send_tandc_email");
        $this->addColumn("lastReviewMeetingDate", DA_DATE, DA_ALLOW_NULL, 'cus_last_review_meeting_date');
        $this->addColumn("reviewMeetingFrequencyMonths", DA_INTEGER, DA_ALLOW_NULL, 'cus_review_meeting_frequency_months');
        $this->addColumn("accountManagerUserID", DA_ID, DA_ALLOW_NULL, "cus_account_manager_consno");

        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    /**
     * Get rows by operative and date
     * @access public
     * @return bool Success
     */
    function getRowsByNameMatch()
    {
        $this->setMethodName("getRowsByNameMatch");
        $ret = FALSE;
        if ($this->getValue('name') == '') {
            $this->raiseError('name not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName('name') . " LIKE " . $this->getFormattedLikeValue('name') .
            " ORDER BY " . $this->getDBColumnName('name')
        );
        $ret = (parent::getRows());
        return $ret;
    }

    function getReviewList($userID, $sortColumn = false)
    {

        $this->setMethodName("getReviewList");

        $ret = FALSE;
        $queryString =

            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            "	WHERE			
				( reviewDate IS NOT NULL and reviewDate <> '0000-00-00' )
				and reviewDate <= CURDATE()";

        if ($userID) {
            $queryString .= "
				AND reviewUserID = " . $userID;
        }
        if ($sortColumn) {
            $queryString .= " order by $sortColumn";

        } else {
            $queryString .= "
				order by
					reviewDate, reviewTime";
        }

        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }

    function getRenewalRequests()
    {
        $this->setMethodName("getRenewalRequests");

        $ret = FALSE;
        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName('sendContractEmail') . " <> ''";

        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }

    function getTandcRequests()
    {

        $this->setMethodName("getTandcRequests");

        $ret = FALSE;
        $queryString =

            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName('sendTandcEmail') . " <> ''";

        $this->setQueryString($queryString);
        $ret = (self::getRows());
        return $ret;
    }
}

?>