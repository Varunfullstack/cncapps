<?php /*
* Customer table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBECustomer extends DBCNCEntity
{
    const CustomerID = "CustomerID";
    const Name = "Name";
    const RegNo = "RegNo";
    const InvoiceSiteNo = "InvoiceSiteNo";
    const DeliverSiteNo = "DeliverSiteNo";
    const MailshotFlag = "MailshotFlag";
    const CreateDate = "CreateDate";
    const ReferredFlag = "ReferredFlag";
    const PCXFlag = "PCXFlag";
    const CustomerTypeID = "CustomerTypeID";
    const ProspectFlag = "ProspectFlag";
//    const OthersEmailMainFlag = "OthersEmailMainFlag";
//    const WorkStartedEmailMainFlag = "WorkStartedEmailMainFlag";
//    const AutoCloseEmailMainFlag = "AutoCloseEmailMainFlag";
    const GSCTopUpAmount = "GSCTopUpAmount";
    const modifyDate = "modifyDate";
    const modifyUserID = "modifyUserID";
    const noOfPCs = "noOfPCs";
    const noOfServers = "noOfServers";
    const noOfSites = "noOfSites";
    const comments = "comments";
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
    const reviewMeetingFrequencyMonths = "reviewMeetingFrequencyMonths";
    const lastReviewMeetingDate = "lastReviewMeetingDate";
    const reviewMeetingEmailSentFlag = "reviewMeetingEmailSentFlag";
    const accountManagerUserID = "accountManagerUserID";
    const CustomerLeadStatusID = "CustomerLeadStatusID";
    const DateMeetingConfirmed = 'DateMeetingConfirmed';
    const MeetingDateTime = 'MeetingDateTime';
    const InviteSent = 'InviteSent';
    const ReportProcessed = 'ReportProcessed';
    const ReportSent = 'ReportSent';
    const CrmComments = 'CrmComments';
    const CompanyBackground = 'CompanyBackground';
    const DecisionMakerBackground = 'DecisionMakerBackground';
    const OpportunityDeal = 'OpportunityDeal';
    const Rating = 'Rating';


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
        $this->addColumn(self::CustomerID, DA_ID, DA_NOT_NULL, "cus_custno");
        $this->addColumn(self::Name, DA_STRING, DA_NOT_NULL, "cus_name");
        $this->addColumn(self::RegNo, DA_STRING, DA_NOT_NULL, "cus_reg_no");
        $this->addColumn(self::InvoiceSiteNo, DA_ID, DA_ALLOW_NULL, "cus_inv_siteno");
        $this->addColumn(self::DeliverSiteNo, DA_ID, DA_ALLOW_NULL, "cus_del_siteno"); // have to be strings so zero sites don't go empty
        $this->addColumn(self::MailshotFlag, DA_YN_FLAG, DA_NOT_NULL, "cus_mailshot");
        $this->addColumn(self::CreateDate, DA_DATE, DA_NOT_NULL, "cus_create_date");
        $this->addColumn(self::ReferredFlag, DA_YN_FLAG, DA_ALLOW_NULL, "cus_referred");
        $this->addColumn(self::PCXFlag, DA_YN_FLAG, DA_ALLOW_NULL, "cus_pcx");
        $this->addColumn(self::CustomerTypeID, DA_ID, DA_NOT_NULL, "cus_ctypeno");
        $this->addColumn(self::ProspectFlag, DA_YN_FLAG, DA_NOT_NULL, "cus_prospect");
//        $this->addColumn(self::OthersEmailMainFlag, DA_YN_FLAG, DA_NOT_NULL, "cus_others_email_main_flag");
//        $this->addColumn(self::WorkStartedEmailMainFlag, DA_YN_FLAG, DA_NOT_NULL, "cus_work_started_email_main_flag");
//        $this->addColumn(self::AutoCloseEmailMainFlag, DA_YN_FLAG, DA_NOT_NULL, "cus_auto_close_email_main_flag");
        $this->addColumn(self::GSCTopUpAmount, DA_FLOAT, DA_NOT_NULL);                        // amount to top up general support contract by
        $this->addColumn(self::modifyDate, DA_DATETIME, DA_ALLOW_NULL);                        // amount to
        $this->addColumn(self::modifyUserID, DA_INTEGER, DA_ALLOW_NULL);
        $this->addColumn(self::noOfPCs, DA_INTEGER, DA_ALLOW_NULL);
        $this->addColumn(self::noOfServers, DA_INTEGER, DA_ALLOW_NULL);
        $this->addColumn(self::noOfSites, DA_INTEGER, DA_ALLOW_NULL);
        $this->addColumn(self::comments, DA_MEMO, DA_ALLOW_NULL);
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
        $this->addColumn(self::reviewMeetingFrequencyMonths, DA_INTEGER, DA_ALLOW_NULL, 'cus_review_meeting_frequency_months');
        $this->addColumn(self::lastReviewMeetingDate, DA_DATE, DA_ALLOW_NULL, 'cus_last_review_meeting_date');
        $this->addColumn(self::reviewMeetingEmailSentFlag, DA_YN, DA_ALLOW_NULL, 'cus_review_meeting_email_sent_flag');
        $this->addColumn(self::accountManagerUserID, DA_ID, DA_ALLOW_NULL, "cus_account_manager_consno");
        $this->addColumn(self::CustomerLeadStatusID, DA_ID, DA_ALLOW_NULL, "customer_lead_status_id");
        $this->addColumn(self::DateMeetingConfirmed, DA_DATE, DA_ALLOW_NULL, 'date_meeting_confirmed');
        $this->addColumn(self::MeetingDateTime, DA_DATETIME, DA_ALLOW_NULL, 'meeting_datetime');
        $this->addColumn(self::InviteSent, DA_BOOLEAN, DA_NOT_NULL, "invite_sent");
        $this->addColumn(self::ReportProcessed, DA_BOOLEAN, DA_NOT_NULL, "report_processed");
        $this->addColumn(self::ReportSent, DA_BOOLEAN, DA_NOT_NULL, "report_sent");
        $this->addColumn(self::CrmComments, DA_STRING, DA_ALLOW_NULL, "crm_comments");
        $this->addColumn(self::CompanyBackground, DA_STRING, DA_ALLOW_NULL, "company_background");
        $this->addColumn(self::DecisionMakerBackground, DA_STRING, DA_ALLOW_NULL, "decision_maker_background");
        $this->addColumn(self::OpportunityDeal, DA_STRING, DA_ALLOW_NULL, "opportunity_deal");
        $this->addColumn(self::Rating, DA_INTEGER, DA_ALLOW_NULL, "rating");
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    /**
     * Get rows by search criteria
     * @access public
     * @param $contact
     * @param $phoneNo
     * @param $name
     * @param $address
     * @param $newCustomerFromDate
     * @param $newCustomerToDate
     * @param $droppedCustomerFromDate
     * @param $droppedCustomerToDate
     * @return bool Success
     */
    function getRowsByNameMatch(
        $contact,
        $phoneNo,
        $name,
        $address,
        $newCustomerFromDate,
        $newCustomerToDate,
        $droppedCustomerFromDate,
        $droppedCustomerToDate
    )
    {

        $this->setMethodName("getRowsByNameMatch");
        $ret = FALSE;
        if ($contact == '' & $phoneNo == '' & $name == '' & $address == '' & $newCustomerFromDate == '' & $newCustomerToDate == '' & $droppedCustomerFromDate == '' & $droppedCustomerToDate == '') {
            $this->raiseError('Either contact, phone, customer name, address or dates must be set');
        }
        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName();

        if ($address != '' OR $phoneNo != '') {
            $queryString .=
                " INNER JOIN address ON cus_custno = add_custno";
        }

        if ($contact != '' OR $phoneNo != '') {
            $queryString .=
                " INNER JOIN contact ON cus_custno = con_custno";
        }

        $queryString .= " WHERE 1=1";

        if ($address != '') {
            $queryString .=
                " AND (add_town LIKE '%" . $address . "%'" .
                " OR add_add1 LIKE '%" . $address . "%'" .
                " OR add_add2 LIKE '%" . $address . "%'" .
                " OR add_add3 LIKE '%" . $address . "%'" .
                " OR add_postcode LIKE '" . $address . "%'" .
                " OR add_county LIKE '%" . $address . "%')";
        }

        if ($contact != '') {
            $queryString .=
                " AND (con_first_name LIKE '%" . $contact . "%'" .
                " OR con_last_name LIKE '%" . $contact . "%')";
        }

        if ($phoneNo != '') {
            $queryString .=
                " AND (con_phone LIKE '%" . $phoneNo . "%'" .
                " OR con_mobile_phone LIKE '%" . $phoneNo . "%'" .
                " OR add_phone LIKE '%" . $phoneNo . "%')";
        }

        if ($newCustomerFromDate != '') {
            $queryString .=
                " AND " . $this->getDBColumnName('becameCustomerDate') . ">='" . mysqli_real_escape_string($this->db->link_id(), $newCustomerFromDate) . "'";
        }
        if ($newCustomerToDate != '') {
            $queryString .=
                " AND " . $this->getDBColumnName('becameCustomerDate') . "<='" . mysqli_real_escape_string($this->db->link_id(), $newCustomerToDate) . "'";
        }

        if ($droppedCustomerFromDate != '') {
            $queryString .=
                " AND " . $this->getDBColumnName('droppedCustomerDate') . ">='" . mysqli_real_escape_string($this->db->link_id(), $droppedCustomerFromDate) . "'";
        }
        if ($droppedCustomerToDate != '') {
            $queryString .=
                " AND " . $this->getDBColumnName('droppedCustomerDate') . "<='" . mysqli_real_escape_string($this->db->link_id(), $droppedCustomerToDate) . "'";
        }

        if ($name != '') {
            $queryString .= " AND " . $this->getDBColumnName('Name') . " LIKE '%" . $name . "%'";
        }

        $queryString .= " GROUP BY " . $this->getDBColumnName('CustomerID') . " ORDER BY " . $this->getDBColumnName('Name');

        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }

    /**
     * Returns next prospect row to be reviewed
     *
     * @access public
     * @return bool Success
     */
    function getReviewProspectRow($prospect = true)
    {
        $this->setMethodName("getReviewProspectRow");
        $ret = FALSE;

        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " where cus_mailshot = 'Y'
				AND ( reviewDate IS NULL OR reviewDate = '0000-00-00' )
				AND ( select count(*) from invhead where inh_custno = cus_custno and inh_date_printed > DATE_SUB(CURDATE() ,INTERVAL 6 MONTH ) ) = 0";

        $queryString .= ' LIMIT 0,1';

        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }

    /**
     * Count review rows
     *
     * As function above but returns count row rows
     *
     * @access public
     * @return bool Success
     */
    function countReviewRows()
    {
        $this->setMethodName("countReviewRows");
        $ret = FALSE;
        $queryString =
            "SELECT COUNT(*)
			 FROM " . $this->getTableName();

        $queryString .=
            ' where cus_mailshot = "Y"';

        $queryString .=
            '
			and ((
				reviewDate IS NULL
				and ( select count(*) from invhead where inh_custno = cus_custno and inh_date_printed > DATE_SUB(CURDATE() ,INTERVAL 6 MONTH ) ) = 0
			)
			OR
			(
				( reviewDate IS NOT NULL and reviewDate <> "0000-00-00" )
				and reviewDate <= CURDATE()
			))';

        $this->setQueryString($queryString);

        $this->runQuery();
        $this->fetchNext();
        $this->resetQueryString();

        return $this->getDBColumnValue(0);
    }

    /**
     * Returns list of customers with 24 hour support
     *
     * @access public
     * @return bool Success
     */
    function get24HourSupportCustomers()
    {
        $this->setMethodName("get24HourSupportCustomers");

        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " where cus_support_24_hour_flag = 'Y'
      ORDER BY cus_name";

        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }

    /**
     * Returns list of customers with special attention set
     *
     * @access public
     * @return bool Success
     */
    function getSpecialAttentionCustomers()
    {
        $this->setMethodName("getSpecialAttentionCustomers");

        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " where cus_special_attention_flag = 'Y'
       AND cus_special_attention_end_date > NOW()
      ORDER BY cus_name";

        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }
}

?>