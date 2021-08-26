<?php /*
* Customer table
* @authors Karim Ahmed
* @access public
*/
global $cfg;

use CNCLTD\Data\DBConnect;

require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBECustomer extends DBCNCEntity
{
    const customerID                   = "customerID";
    const name                         = "name";
    const regNo                        = "regNo";
    const invoiceSiteNo                = "invoiceSiteNo";
    const deliverSiteNo                = "deliverSiteNo";
    const createDate                   = "createDate";
    const referredFlag                 = "referredFlag";
    const customerTypeID               = "customerTypeID";
    const gscTopUpAmount               = "gscTopUpAmount";
    const modifyDate                   = "modifyDate";
    const modifyUserID                 = "modifyUserID";
    const noOfPCs                      = "noOfPCs";
    const noOfServers                  = "noOfServers";
    const comments                     = "comments";
    const reviewDate                   = "reviewDate";
    const reviewAction                 = "reviewAction";
    const reviewUserID                 = "reviewUserID";
    const sectorID                     = "sectorID";
    const becameCustomerDate           = "becameCustomerDate";
    const droppedCustomerDate          = "droppedCustomerDate";
    const leadStatusId                 = "leadStatusId";
    const techNotes                    = "techNotes";
    const specialAttentionFlag         = "specialAttentionFlag";
    const specialAttentionEndDate      = "specialAttentionEndDate";
    const support24HourFlag            = "support24HourFlag";
    const slaP1                        = "slaP1";
    const slaP2                        = "slaP2";
    const slaP3                        = "slaP3";
    const slaP4                        = "slaP4";
    const slaP5                        = "slaP5";
    const sendContractEmail            = "sendContractEmail";
    const sendTandcEmail               = "sendTandcEmail";
    const lastReviewMeetingDate        = "lastReviewMeetingDate";
    const reviewMeetingFrequencyMonths = "reviewMeetingFrequencyMonths";
    const accountManagerUserID         = "accountManagerUserID";
    const reviewMeetingEmailSentFlag   = "reviewMeetingEmailSentFlag";
    
    const crmComments                  = 'crmComments';
    const companyBackground            = 'companyBackground';
    const decisionMakerBackground      = 'decisionMakerBackground';
    const opportunityDeal              = 'opportunityDeal';
    const lastContractSent             = 'lastContractSent';
    const primaryMainContactID         = 'primaryMainContactID';
    const sortCode                     = 'sortCode';
    const accountName                  = 'accountName';
    const accountNumber                = 'accountNumber';
    const activeDirectoryName          = "activeDirectoryName";
    const reviewMeetingBooked          = 'reviewMeetingBooked';
    const licensedOffice365Users       = 'licensedOffice365Users';
    const websiteURL                   = "websiteURL";
    const slaFixHoursP1                = "slaFixHoursP1";
    const slaFixHoursP2                = "slaFixHoursP2";
    const slaFixHoursP3                = "slaFixHoursP3";
    const slaFixHoursP4                = "slaFixHoursP4";
    const slaP1PenaltiesAgreed         = "slaP1PenaltiesAgreed";
    const slaP2PenaltiesAgreed         = "slaP2PenaltiesAgreed";
    const slaP3PenaltiesAgreed         = "slaP3PenaltiesAgreed";
    const streamOneEmail               = "streamOneEmail";
    const lastUpdatedDateTime          = "lastUpdatedDateTime";
    const inclusiveOOHCallOuts         = "inclusiveOOHCallOuts";
    const eligiblePatchManagement      = "eligiblePatchManagement";
    const excludeFromWebrootChecks     = "excludeFromWebrootChecks";
    const genNotes                     = "genNotes";
    const statementContactId = "statementContactId";
    const meetingDateTime              = 'meetingDateTime';
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
        $this->setTableName("Customer");
        $this->addColumn(
            self::customerID,
            DA_ID,
            DA_NOT_NULL,
            "cus_custno"
        );
        $this->addColumn(
            self::name,
            DA_STRING,
            DA_NOT_NULL,
            "cus_name"
        );
        $this->addColumn(
            self::regNo,
            DA_STRING,
            DA_NOT_NULL,
            "cus_reg_no"
        );
        $this->addColumn(
            self::invoiceSiteNo,
            DA_ID,
            DA_ALLOW_NULL,
            "cus_inv_siteno"
        );
        $this->addColumn(
            self::deliverSiteNo,
            DA_ID,
            DA_ALLOW_NULL,
            "cus_del_siteno"
        ); // have to be strings so zero sites don't go empty
         
        $this->addColumn(
            self::createDate,
            DA_DATE,
            DA_NOT_NULL,
            "cus_create_date"
        );
        $this->addColumn(
            self::referredFlag,
            DA_BOOLEAN,
            DA_ALLOW_NULL,
            "isReferred",
            false
        );
        $this->addColumn(
            self::customerTypeID,
            DA_ID,
            DA_NOT_NULL,
            "cus_ctypeno"
        );
        $this->addColumn(
            self::gscTopUpAmount,
            DA_FLOAT,
            DA_NOT_NULL,
            null,
            '0.0'
        );                        // amount to top up general support contract by
        $this->addColumn(
            self::modifyDate,
            DA_DATETIME,
            DA_ALLOW_NULL
        );                        // amount to
        $this->addColumn(
            self::modifyUserID,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::noOfPCs,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::noOfServers,
            DA_INTEGER,
            DA_ALLOW_NULL,
            null,
            0
        );
        $this->addColumn(
            self::comments,
            DA_MEMO,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::reviewDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
      
        $this->addColumn(
            self::reviewAction,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::reviewUserID,
            DA_ID,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::sectorID,
            DA_ID,
            DA_NOT_NULL,
            "cus_sectorno"
        );
        $this->addColumn(
            self::becameCustomerDate,
            DA_DATE,
            DA_ALLOW_NULL,
            'cus_became_customer_date'
        );
        $this->addColumn(
            self::droppedCustomerDate,
            DA_DATE,
            DA_ALLOW_NULL,
            'cus_dropped_customer_date'
        );
        $this->addColumn(
            self::leadStatusId,
            DA_ID,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::techNotes,
            DA_STRING,
            DA_ALLOW_NULL,
            'cus_tech_notes'
        );
        $this->addColumn(
            self::genNotes,
            DA_TEXT,
            DA_ALLOW_NULL,
            "cus_notes"
        );
        $this->addColumn(
            self::specialAttentionFlag,
            DA_YN_FLAG,
            DA_NOT_NULL,
            "cus_special_attention_flag"
        );
        $this->addColumn(
            self::specialAttentionEndDate,
            DA_DATE,
            DA_ALLOW_NULL,
            'cus_special_attention_end_date'
        );
        $this->addColumn(
            self::support24HourFlag,
            DA_YN_FLAG,
            DA_NOT_NULL,
            "cus_support_24_hour_flag"
        );
        $this->addColumn(
            self::slaP1,
            DA_FLOAT,
            DA_NOT_NULL,
            "cus_sla_p1"
        );
        $this->addColumn(
            self::slaP2,
            DA_FLOAT,
            DA_NOT_NULL,
            "cus_sla_p2"
        );
        $this->addColumn(
            self::slaP3,
            DA_FLOAT,
            DA_NOT_NULL,
            "cus_sla_p3"
        );
        $this->addColumn(
            self::slaP4,
            DA_FLOAT,
            DA_NOT_NULL,
            "cus_sla_p4"
        );
        $this->addColumn(
            self::slaP5,
            DA_FLOAT,
            DA_NOT_NULL,
            "cus_sla_p5"
        );
        $this->addColumn(self::slaFixHoursP1, DA_FLOAT, DA_ALLOW_NULL);
        $this->addColumn(self::slaFixHoursP2, DA_FLOAT, DA_ALLOW_NULL);
        $this->addColumn(self::slaFixHoursP3, DA_FLOAT, DA_ALLOW_NULL);
        $this->addColumn(self::slaFixHoursP4, DA_FLOAT, DA_ALLOW_NULL);
        $this->addColumn(
            self::sendContractEmail,
            DA_STRING,
            DA_ALLOW_NULL,
            "cus_send_contract_email"
        );
        $this->addColumn(
            self::sendTandcEmail,
            DA_STRING,
            DA_ALLOW_NULL,
            "cus_send_tandc_email"
        );
        $this->addColumn(
            self::lastReviewMeetingDate,
            DA_DATE,
            DA_ALLOW_NULL,
            'cus_last_review_meeting_date'
        );
        $this->addColumn(
            self::reviewMeetingFrequencyMonths,
            DA_INTEGER,
            DA_ALLOW_NULL,
            'cus_review_meeting_frequency_months'
        );
        $this->addColumn(
            self::reviewMeetingEmailSentFlag,
            DA_YN,
            DA_ALLOW_NULL,
            'cus_review_meeting_email_sent_flag'
        );
        $this->addColumn(
            self::accountManagerUserID,
            DA_ID,
            DA_ALLOW_NULL,
            "cus_account_manager_consno"
        );
           
        $this->addColumn(
            self::crmComments,
            DA_STRING,
            DA_ALLOW_NULL,
            "crm_comments"
        );
        $this->addColumn(
            self::companyBackground,
            DA_STRING,
            DA_ALLOW_NULL,
            "company_background"
        );
        $this->addColumn(
            self::decisionMakerBackground,
            DA_STRING,
            DA_ALLOW_NULL,
            "decision_maker_background"
        );
        $this->addColumn(
            self::opportunityDeal,
            DA_STRING,
            DA_ALLOW_NULL,
            "opportunity_deal"
        );
        
        $this->addColumn(
            self::lastContractSent,
            DA_TEXT,
            DA_ALLOW_NULL,
            "lastContractSent"
        );
        $this->addColumn(
            self::primaryMainContactID,
            DA_ID,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::sortCode,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::accountName,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::accountNumber,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::activeDirectoryName,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::reviewMeetingBooked,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            0
        );
        $this->addColumn(
            self::licensedOffice365Users,
            DA_INTEGER,
            DA_NOT_NULL,
            null,
            0
        );
        $this->addColumn(
            self::websiteURL,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::slaP1PenaltiesAgreed,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            false
        );
        $this->addColumn(
            self::slaP2PenaltiesAgreed,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            false
        );
        $this->addColumn(
            self::slaP3PenaltiesAgreed,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            false
        );
        $this->addColumn(
            self::streamOneEmail,
            DA_TEXT,
            DA_ALLOW_NULL,
            "streamOneEmail"
        );
        $this->addColumn(
            self::lastUpdatedDateTime,
            DATE_MYSQL_DATETIME,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::inclusiveOOHCallOuts,
            DA_INTEGER,
            DA_NOT_NULL,
            null,
            0
        );
        $this->addColumn(
            self::statementContactId,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::eligiblePatchManagement,
            DA_INTEGER,
            DA_NOT_NULL,
            null,
            0
        );
        $this->addColumn(
            self::excludeFromWebrootChecks,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            0
        );
        $this->addColumn(
            self::meetingDateTime,
            DA_DATE,
            DA_ALLOW_NULL,
            'meeting_datetime'
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    public function updateRow()
    {
        $this->setValue(DBECustomer::lastUpdatedDateTime, (new DateTime())->format(DATE_MYSQL_DATETIME));
        return parent::updateRow();
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
    function getRowsByNameMatch($contact = null,
                                $phoneNo = null,
                                $name = null,
                                $address = null,
                                $newCustomerFromDate = null,
                                $newCustomerToDate = null,
                                $droppedCustomerFromDate = null,
                                $droppedCustomerToDate = null
    )
    {

        $this->setMethodName("getRowsByNameMatch");
//        if (!$contact && !$phoneNo && !$address && !$newCustomerFromDate && !$newCustomerToDate && !$droppedCustomerFromDate && !$droppedCustomerToDate) {
//            $this->raiseError('Either contact, phone, customer name, address or dates must be set');
//        }
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName();
        if ($address || $phoneNo) {
            $queryString .= " INNER JOIN address ON cus_custno = add_custno";
        }
        if ($contact or $phoneNo) {
            $queryString .= " INNER JOIN contact ON cus_custno = con_custno";
        }
        $queryString .= " WHERE 1=1";
        if ($address) {
            $queryString .= " AND (add_town LIKE '%" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $address
                ) . "%'" . " OR add_add1 LIKE '%" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $address
                ) . "%'" . " OR add_add2 LIKE '%" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $address
                ) . "%'" . " OR add_add3 LIKE '%" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $address
                ) . "%'" . " OR add_postcode LIKE '" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $address
                ) . "%'" . " OR add_county LIKE '%" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $address
                ) . "%')";
        }
        if ($contact) {
            $queryString .= " AND (con_first_name LIKE '%" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $contact
                ) . "%'" . " OR con_last_name LIKE '%" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $contact
                ) . "%')";
        }
        if ($phoneNo) {
            $queryString .= " AND (con_phone LIKE '%" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $phoneNo
                ) . "%'" . " OR con_mobile_phone LIKE '%" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $phoneNo
                ) . "%'" . " OR add_phone LIKE '%" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $phoneNo
                ) . "%')";
        }
        if ($newCustomerFromDate) {
            $queryString .= " AND " . $this->getDBColumnName(
                    self::becameCustomerDate
                ) . ">='" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $newCustomerFromDate
                ) . "'";
        }
        if ($newCustomerToDate) {
            $queryString .= " AND " . $this->getDBColumnName(
                    self::becameCustomerDate
                ) . "<='" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $newCustomerToDate
                ) . "'";
        }
        if ($droppedCustomerFromDate) {
            $queryString .= " AND " . $this->getDBColumnName(
                    self::droppedCustomerDate
                ) . ">='" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $droppedCustomerFromDate
                ) . "'";
        }
        if ($droppedCustomerToDate) {
            $queryString .= " AND " . $this->getDBColumnName(
                    self::droppedCustomerDate
                ) . "<='" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $droppedCustomerToDate
                ) . "'";
        }
        if ($name) {
            $queryString .= " AND " . $this->getDBColumnName(self::name) . " LIKE '%" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $name
                ) . "%'";
        }
        $queryString .= " GROUP BY " . $this->getDBColumnName(self::customerID) . " ORDER BY " . $this->getDBColumnName(
                self::name
            );
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }

    function getCustomerByName($name)
    {
        if (!$name) {
            return $this;
        }
        $this->setMethodName("getCustomerByName");
        $name        = mysqli_real_escape_string($this->db->link_id(), $name);        
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " where 
        cus_name like '{$name}'
        and not {$this->getDBColumnName(DBECustomer::referredFlag)} 
        and {$this->getDBColumnName(DBECustomer::becameCustomerDate)} is not null and {$this->getDBColumnName(DBECustomer::droppedCustomerDate)} is null
        LIMIT 1";
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }
    function getGEnNotes($customer){
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " where 
        cus_custno = ".$customer;

    }
    /**
     * Returns list of customers with 24 hour support
     *
     * @access public
     * @param bool $onlyCurrentCustomers
     * @return bool Success
     */
    function get24HourSupportCustomers($onlyCurrentCustomers = false)
    {
        $this->setMethodName("get24HourSupportCustomers");
        $onlyCurrentCustomersCondition = "";
        if ($onlyCurrentCustomers) {
            $onlyCurrentCustomersCondition = " and {$this->getDBColumnName(self::droppedCustomerDate)} is null  and {$this->getDBColumnName(self::becameCustomerDate)} is not null ";
        }
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " where cus_support_24_hour_flag = 'Y'
    {$onlyCurrentCustomersCondition}  ORDER BY cus_name";
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
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " where cus_special_attention_flag = 'Y'
       AND cus_special_attention_end_date > NOW()
      ORDER BY cus_name";
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }

    /**
     * Returns list of customers with special attention set
     *
     * @access public
     * @param bool $ignoreProspects
     * @return bool Success
     */
    function getActiveCustomers($ignoreProspects = false)
    {
        $this->setMethodName("getSpecialAttentionCustomers");
        $queryString = "SELECT {$this->getDBColumnNamesAsString()} FROM {$this->getTableName()} where not {$this->getDBColumnName(DBECustomer::referredFlag)}";
        if ($ignoreProspects) {
            $queryString .= " and {$this->getDBColumnName(DBECustomer::becameCustomerDate)} is not null and {$this->getDBColumnName(DBECustomer::droppedCustomerDate)} is null ";
        }
        $queryString .= " order by {$this->getDBColumnName(DBECustomer::name)} ";
        $this->setQueryString($queryString);
        return $this->getRows();
    }

    function getReviewList($userID,
                           $sortColumn = false
    )
    {
        $this->setMethodName("getReviewList");
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . "	WHERE			
				reviewDate IS NOT NULL and reviewDate <= CURDATE()";
        if ($userID) {
            $queryString .= "
				AND reviewUserID = " . $userID;
        }
        if ($sortColumn) {
            $queryString .= " order by $sortColumn";

        } else {
            $queryString .= "
				order by
					reviewDate";
        }
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }

    function getRenewalRequests()
    {
        $this->setMethodName("getRenewalRequests");
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " WHERE " . $this->getDBColumnName(self::sendContractEmail) . " <> ''";
        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }

    function getTandcRequests()
    {

        $this->setMethodName("getTandcRequests");
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " WHERE " . $this->getDBColumnName(self::sendTandcEmail) . " <> ''";
        $this->setQueryString($queryString);
        $ret = (self::getRows());
        return $ret;
    }

    function getReviewMeetingCustomers()
    {
        $this->setMethodName('getReviewMeetingCustomers');
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " WHERE " . $this->getDBColumnName(
                self::lastReviewMeetingDate
            ) . ' is not null and not ' . $this->getDBColumnName(self::referredFlag) . '  ';
        $this->setQueryString($queryString);
        $ret = (self::getRows());
        return $ret;


    }

    /**
     * @param $email
     * @return $this|null
     */
    public function getCustomerByStreamOneEmail($email)
    {
        $this->setMethodName('getCustomerByStreamOneEmail');
        if (!$email) {
            throw new Exception('Email is mandatory');
        }
        $escapedEmail = mysqli_real_escape_string($this->db->link_id(), trim($email));
        $queryString  = "SELECT {$this->getDBColumnNamesAsString()} FROM {$this->getTableName()} WHERE {$this->getDBColumnName(self::streamOneEmail)} like '%{$escapedEmail}%' ";
        $this->setQueryString($queryString);
        self::getRows();
        if (!self::rowCount()) {
            return null;
        }
        $this->fetchNext();
        return $this;
    }

    public function getBreachedSpecialAttentionCustomers()
    {
        $this->setMethodName('getBreachedSpecialAttentionCustomers');
        $queryString = "SELECT {$this->getDBColumnNamesAsString()} FROM {$this->getTableName()} WHERE {$this->getDBColumnName(self::specialAttentionFlag)} = 'Y' and {$this->getDBColumnName(self::specialAttentionEndDate)}  <= current_date() ";
        $this->setQueryString($queryString);
        $ret = (self::getRows());
        return $ret;
    }

    public function getCustomerSiteAddress($customerID, $siteID)
    {
        $query   = "select * from address where add_custno=:custID and (:siteID is null or add_siteno=:siteID)";
        $address = DBConnect::fetchOne($query, ["siteID" => $siteID, "custID" => $customerID]);
        return $address;
    }
}

?>
