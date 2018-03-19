<?php /*
* Customer table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBECustomer extends DBCNCEntity
{

    const customerID = "customerID";
    const name = "name";
    const invSiteNo = "invSiteNo";
    const delSiteNo = "delSiteNo";
    const mailshotFlag = "mailshotFlag";
    const createDate = "createDate";
    const referredFlag = "referredFlag";
    const pcxFlag = "pcxFlag";
    const customerTypeID = "customerTypeID";
    const prospectFlag = "prospectFlag";
    const othersEmailMainFlag = "othersEmailMainFlag";
    const workStartedEmailMainFlag = "workStartedEmailMainFlag";
    const autoCloseEmailMainFlag = "autoCloseEmailMainFlag";
    const gscTopUpAmount = "gscTopUpAmount";
    const reviewDate = "reviewDate";
    const reviewTime = "reviewTime";
    const reviewAction = "reviewAction";
    const reviewUserID = "reviewUserID";
    const sectorID = "sectorID";
    const becameCustomerDate = "becameCustomerDate";
    const droppedCustomerDate = "droppedCustomerDate";
    const leadStatusID = "leadStatusID";
    const techNotes = "techNotes";
    const specialAttentionFlag = "specialAttentionFlag";
    const specialAttentionEndDate = "specialAttentionEndDate";
    const support24HourFlag = "support24HourFlag";
    const slaP1 = "slaP1";
    const slaP2 = "slaP2";
    const slaP3 = "slaP3";
    const slaP4 = "slaP4";
    const slaP5 = "slaP5";
    const sendContractEmail = "sendContractEmail";
    const sendTandcEmail = "sendTandcEmail";
    const lastReviewMeetingDate = "lastReviewMeetingDate";
    const reviewMeetingFrequencyMonths = "reviewMeetingFrequencyMonths";
    const accountManagerUserID = "accountManagerUserID";

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
        $this->addColumn(self::customerID, DA_ID, DA_NOT_NULL, "cus_custno");
        $this->addColumn(self::name, DA_STRING, DA_NOT_NULL, "cus_name");
        $this->addColumn(self::invSiteNo, DA_ID, DA_ALLOW_NULL, "cus_inv_siteno");
        $this->addColumn(self::delSiteNo, DA_ID, DA_ALLOW_NULL, "cus_del_siteno"); // have to be strings so zero sites don't go empty
        $this->addColumn(self::mailshotFlag, DA_YN, DA_NOT_NULL, "cus_mailshot");
        $this->addColumn(self::createDate, DA_DATE, DA_NOT_NULL, "cus_create_date");
        $this->addColumn(self::referredFlag, DA_YN, DA_ALLOW_NULL, "cus_referred");
        $this->addColumn(self::pcxFlag, DA_YN, DA_ALLOW_NULL, "cus_pcx");
        $this->addColumn(self::customerTypeID, DA_ID, DA_NOT_NULL, "cus_ctypeno");
        $this->addColumn(self::prospectFlag, DA_YN, DA_NOT_NULL, "cus_prospect");
        $this->addColumn(self::othersEmailMainFlag, DA_YN_FLAG, DA_NOT_NULL, "cus_others_email_main_flag");
        $this->addColumn(self::workStartedEmailMainFlag, DA_YN_FLAG, DA_NOT_NULL, "cus_work_started_email_main_flag");
        $this->addColumn(self::autoCloseEmailMainFlag, DA_YN_FLAG, DA_NOT_NULL, "cus_auto_close_email_main_flag");
        $this->addColumn(self::gscTopUpAmount, DA_FLOAT, DA_NOT_NULL);                        // amount to top up general support contract by
        $this->addColumn(self::reviewDate, DA_DATE, DA_ALLOW_NULL);
        $this->addColumn(self::reviewTime, DA_TIME, DA_ALLOW_NULL);
        $this->addColumn(self::reviewAction, DA_STRING, DA_ALLOW_NULL);
        $this->addColumn(self::reviewUserID, DA_ID, DA_ALLOW_NULL);
        $this->addColumn(self::sectorID, DA_ID, DA_NOT_NULL, "cus_sectorno");
        $this->addColumn(self::becameCustomerDate, DA_DATE, DA_ALLOW_NULL, 'cus_became_customer_date');
        $this->addColumn(self::droppedCustomerDate, DA_DATE, DA_ALLOW_NULL, 'cus_dropped_customer_date');
        $this->addColumn(self::leadStatusID, DA_ID, DA_ALLOW_NULL, 'cus_leadstatusno');
        $this->addColumn(self::techNotes, DA_STRING, DA_ALLOW_NULL, 'cus_tech_notes');
        $this->addColumn(self::specialAttentionFlag, DA_YN_FLAG, DA_NOT_NULL, "cus_special_attention_flag");
        $this->addColumn(self::specialAttentionEndDate, DA_DATE, DA_ALLOW_NULL, 'cus_special_attention_end_date');
        $this->addColumn(self::support24HourFlag, DA_YN_FLAG, DA_NOT_NULL, "cus_support_24_hour_flag");
        $this->addColumn(self::slaP1, DA_INTEGER, DA_NOT_NULL, "cus_sla_p1");
        $this->addColumn(self::slaP2, DA_INTEGER, DA_NOT_NULL, "cus_sla_p2");
        $this->addColumn(self::slaP3, DA_INTEGER, DA_NOT_NULL, "cus_sla_p3");
        $this->addColumn(self::slaP4, DA_INTEGER, DA_NOT_NULL, "cus_sla_p4");
        $this->addColumn(self::slaP5, DA_INTEGER, DA_NOT_NULL, "cus_sla_p5");
        $this->addColumn(self::sendContractEmail, DA_INTEGER, DA_NOT_NULL, "cus_send_contract_email");
        $this->addColumn(self::sendTandcEmail, DA_INTEGER, DA_NOT_NULL, "cus_send_tandc_email");
        $this->addColumn(self::lastReviewMeetingDate, DA_DATE, DA_ALLOW_NULL, 'cus_last_review_meeting_date');
        $this->addColumn(self::reviewMeetingFrequencyMonths, DA_INTEGER, DA_ALLOW_NULL, 'cus_review_meeting_frequency_months');
        $this->addColumn(self::accountManagerUserID, DA_ID, DA_ALLOW_NULL, "cus_account_manager_consno");

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