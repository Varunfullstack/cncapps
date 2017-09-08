<?php
/*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBECallActivity extends DBEntity
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
        $this->setTableName("callactivity");
        $this->addColumn("callActivityID", DA_ID, DA_NOT_NULL, "caa_callactivityno");
// 		$this->addColumn("customerID", DA_ID, DA_NOT_NULL, "caa_custno");
        $this->addColumn("siteNo", DA_INTEGER, DA_NOT_NULL, "caa_siteno");
        $this->addColumn("contactID", DA_INTEGER, DA_NOT_NULL, "caa_contno");        // customer contact
        $this->addColumn("sequenceNo", DA_INTEGER, DA_ALLOW_NULL, "caa_item");
        $this->addColumn("callActTypeID", DA_ID, DA_NOT_NULL, "caa_callacttypeno");
        $this->addColumn("problemID", DA_ID, DA_ALLOW_NULL, 'callactivity.caa_problemno');
        $this->addColumn("date", DA_DATE, DA_NOT_NULL, "caa_date");
        $this->addColumn("startTime", DA_TIME, DA_NOT_NULL, "caa_starttime");
        $this->addColumn("endTime", DA_TIME, DA_ALLOW_NULL, "caa_endtime");
        $this->addColumn("status", DA_STRING, DA_NOT_NULL, "caa_status");
        $this->addColumn("expenseExportFlag", DA_YN, DA_NOT_NULL, "caa_expexport_flag");
        $this->addColumn("reason", DA_TEXT, DA_ALLOW_NULL);
        $this->addColumn("internalNotes", DA_TEXT, DA_ALLOW_NULL);
        $this->addColumn("curValue", DA_FLOAT, DA_ALLOW_NULL);
        $this->addColumn("statementYearMonth", DA_STRING, DA_ALLOW_NULL);
        $this->addColumn("customerItemID", DA_ID, DA_ALLOW_NULL, "caa_cuino");    // Customer Item
        $this->addColumn("underContractFlag", DA_YN, DA_ALLOW_NULL, "caa_under_contract");
        $this->addColumn("authorisedFlag", DA_YN, DA_ALLOW_NULL, "caa_authorised");
        $this->addColumn("userID", DA_ID, DA_NOT_NULL, "caa_consno");
// 		$this->addColumn("overtimeExportedFlag", DA_YN, DA_NULL, "caa_ot_exp_flag");
        $this->addColumn("serverGuard", DA_YN, DA_ALLOW_NULL, "caa_serverguard");
        $this->addColumn("parentCallActivityID", DA_ID, DA_ALLOW_NULL, "caa_parent_callactivityno");
        $this->addColumn("awaitingCustomerResponseFlag", DA_YN, DA_ALLOW_NULL, "caa_awaiting_customer_response_flag");
        $this->addColumn("loggingErrorFlag", DA_YN, DA_ALLOW_NULL, "caa_logging_error_flag");
        $this->addColumn("escalationID", DA_ID, DA_ALLOW_NULL);
        $this->addColumn("escalationAcceptedFlag", DA_YN, DA_ALLOW_NULL);
        $this->addColumn("hideFromCustomerFlag", DA_YN, DA_ALLOW_NULL, 'caa_hide_from_customer_flag');

        $this->addColumn("secondsiteErrorServer", DA_STRING, DA_ALLOW_NULL, 'caa_secondsite_error_server');

        $this->addColumn("secondsiteErrorCustomerItemID", DA_INTEGER, DA_ALLOW_NULL, 'caa_secondsite_error_cuino');

        $this->setPK(0);
        $this->setAddColumnsOff();
        $this->db->connect();
    }

    function changeProblemID($fromProblemID, $toProblemID)
    {

        $query =
            "UPDATE " . $this->getTableName() .
            " SET " . $this->getDBColumnName('problemID') . " = ? " .
            " WHERE " . $this->getDBColumnName('problemID') . " = ?";


        $parameters = [
            [
                'type' => 'i',
                'value' => $toProblemID
            ],
            [
                'type' => 'i',
                'value' => $fromProblemID
            ],

        ];
        /**
         * @var mysqli_result $result
         */
        $result = $this->db->prepareQuery($query, $parameters);

    }


    function countRowsByCustomerSiteNo($customerID, $siteNo)
    {
        $this->setQueryString(
            "SELECT COUNT(*) FROM " . $this->getTableName() .
            " JOIN problem ON pro_problemno = caa_problemno 
			  WHERE pro_custno =" . $customerID .
            " AND " . $this->getDBColumnName('siteNo') . "=" . $siteNo
        );
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $this->resetQueryString();
                return ($this->getDBColumnValue(0));
            }
        }
    }

    function countTravelRowsForTodayByCustomerSiteNoEngineer(
        $customerID,
        $siteNo,
        $userID,
        $date
    )
    {
        $this->setQueryString(
            "SELECT COUNT(*) FROM " . $this->getTableName() .
            " JOIN problem ON pro_problemno = caa_problemno " .
            " WHERE pro_custno =" . $customerID .
            " AND " . $this->getDBColumnName('siteNo') . "=" . $siteNo .
            " AND " . $this->getDBColumnName('callActTypeID') . "=" . CONFIG_TRAVEL_ACTIVITY_TYPE_ID .
            " AND " . $this->getDBColumnName('date') . "= '" . $date . "'" .
            " AND " . $this->getDBColumnName('userID') . "=" . $userID
        );
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $this->resetQueryString();
                return ($this->getDBColumnValue(0));
            }
        }
    }

    function countEngineerRowsByProblem($problemID)
    {
        $this->setQueryString(
            "SELECT COUNT(*) FROM " . $this->getTableName() .
            " WHERE caa_problemno =" . $problemID .
            " AND " . $this->getDBColumnName('userID') . "<>" . USER_SYSTEM .
            " AND " . $this->getDBColumnName('callActTypeID') . "<>" . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID
        );
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $this->resetQueryString();
                return ($this->getDBColumnValue(0));
            }
        }
    }

    function countSendEmailActivities($problemID)
    {
        $this->setQueryString(
            "SELECT COUNT(*) FROM " . $this->getTableName() .
            " JOIN callacttype ON cat_callacttypeno = caa_callacttypeno" .
            " WHERE caa_problemno =" . $problemID .
            " AND customerEmailFlag = 'Y'"
        );

        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $this->resetQueryString();
                return ($this->getDBColumnValue(0));
            }
        }
    }

    function setAllActivitiesToAuthorisedByProblemID($problemID)
    {
        $this->setQueryString(
            "UPDATE
        callactivity
       SET
        caa_status = 'A'
       WHERE
        caa_problemno = $problemID"
        );
        $this->runQuery();
    }
}

/*
* Call activity join
* @authors Karim Ahmed
* @access public
*/

class DBEJCallActivity extends DBECallActivity
{
    var $fromString;

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
        $this->addColumn("itemID", DA_INTEGER, DA_ALLOW_NULL, "cat_itemno");
        $this->addColumn("activityType", DA_STRING, DA_ALLOW_NULL, "cat_desc");
        $this->addColumn("requireCheckFlag", DA_YN, DA_NOT_NULL, "cat_req_check_flag");
        $this->addColumn("onSiteFlag", DA_YN, DA_NOT_NULL, "cat_on_site_flag");
        $this->addColumn("allowExpensesFlag", DA_YN, DA_ALLOW_NULL, "cat_allow_exp_flag");
        $this->addColumn("travelFlag", DA_YN, DA_ALLOW_NULL);
        $this->addColumn("allowSCRFlag", DA_YN, DA_ALLOW_NULL);
        $this->addColumn("userID", DA_ID, DA_ALLOW_NULL, "consultant.cns_consno");
        $this->addColumn("userName", DA_STRING, DA_ALLOW_NULL, "CONCAT(consultant.firstName,' ',consultant.lastName)");
        $this->addColumn("userAccount", DA_STRING, DA_ALLOW_NULL, "consultant.cns_logname");
        $this->addColumn("durationMinutes", DA_STRING, DA_ALLOW_NULL, "( TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime) ) / 60");
        $this->addColumn("siteDesc", DA_STRING, DA_ALLOW_NULL, "CONCAT(add_add1,' ',add_add2,' ',add_town)");
        $this->addColumn("customerID", DA_STRING, DA_ALLOW_NULL, "problem.pro_custno");
        $this->addColumn("customerName", DA_STRING, DA_ALLOW_NULL, "cus_name");
        $this->addColumn("contactFirstName", DA_STRING, DA_ALLOW_NULL, "con_first_name");
        $this->addColumn("contactName", DA_STRING, DA_ALLOW_NULL, "CONCAT(con_first_name,' ',con_last_name)");
        $this->addColumn("contactNotes", DA_STRING, DA_ALLOW_NULL, "con_notes");
        $this->addColumn("techNotes", DA_STRING, DA_ALLOW_NULL, "cus_tech_notes");
        $this->addColumn("contactEmail", DA_STRING, DA_ALLOW_NULL, "con_email");
        $this->addColumn("workStartedEmailFlag", DA_YN, DA_ALLOW_NULL, "con_work_started_email_flag");
        $this->addColumn("autoCloseEmailFlag", DA_YN, DA_ALLOW_NULL, "con_auto_close_email_flag");
        $this->addColumn("dateEngineer", DA_STRING, DA_ALLOW_NULL, "CONCAT(DATE_FORMAT(caa_date,'%e/%c/%y'), ' - ', consultant.firstName,' ',consultant.lastName)");
        $this->addColumn("contractCustomerItemID", DA_ID, DA_ALLOW_NULL, "problem.pro_contract_cuino");
        $this->addColumn("contractItemID", DA_ID, DA_ALLOW_NULL, "contractitem.itm_itemno");
        $this->addColumn("contractResponseTime", DA_STRING, DA_ALLOW_NULL, "contractitem.contractResponseTime");
        $this->addColumn("contractDescription", DA_STRING, DA_ALLOW_NULL, "if (contractitem.itm_desc IS NULL, 'T&M', contractitem.itm_desc)");
        $this->addColumn("activityTypeCost", DA_STRING, DA_ALLOW_NULL, "activity_type_item.itm_sstk_price");                // per hour cost of this activity
        $this->addColumn("curValueFlag", DA_YN, DA_ALLOW_NULL);        // is it a Value Type activity?
        $this->addColumn("projectDescription", DA_STRING, DA_ALLOW_NULL, "if (project.description IS NULL, 'None', project.description)");
        $this->addColumn("completedName", DA_STRING, DA_ALLOW_NULL, "completed_user.cns_logname");
        $this->addColumn("priority", DA_INTEGER, DA_ALLOW_NULL, "problem.pro_priority");
        $this->addColumn("problemHideFromCustomerFlag", DA_YN, DA_ALLOW_NULL, "problem.pro_hide_from_customer_flag");
        $this->addColumn("problemStatus", DA_STRING, DA_ALLOW_NULL, "problem.pro_status");
        $this->addColumn("requestAwaitingCustomerResponseFlag", DA_STRING, DA_ALLOW_NULL, "problem.pro_awaiting_customer_response_flag");
        $this->addColumn("internalNotes", DA_MEMO, DA_ALLOW_NULL, "problem.pro_internal_notes");
        $this->addColumn("completeDate", DA_DATE, DA_ALLOW_NULL, "problem.pro_complete_date");
        $this->addColumn("alarmDate", DA_DATE, DA_ALLOW_NULL, "problem.pro_alarm_date");
        $this->addColumn("alarmTime", DA_TIME, DA_ALLOW_NULL, "problem.pro_alarm_time");
        $this->addColumn("rootCauseID", DA_INTEGER, DA_ALLOW_NULL, "problem.pro_rootcauseno");
        $this->addColumn("rootCauseDescription", DA_INTEGER, DA_ALLOW_NULL, "rootcause.rtc_desc");
        $this->addColumn("projectID", DA_ID, DA_ALLOW_NULL, "problem.pro_projectno");
        $this->addColumn("linkedSalesOrderID", DA_INTEGER, DA_ALLOW_NULL, "problem.pro_linked_ordno");

        $this->addColumn("totalActivityDurationHours", DA_FLOAT, DA_ALLOW_NULL, "problem.pro_total_activity_duration_hours");

        $this->addColumn("criticalFlag", DA_YN, DA_ALLOW_NULL, "problem.pro_critical_flag");
        $this->addColumn("hdRemainHours", DA_FLOAT, DA_ALLOW_NULL, "problem.pro_hd_remain_hours");
        $this->addColumn("esRemainHours", DA_FLOAT, DA_ALLOW_NULL, "problem.pro_es_remain_hours");
        $this->addColumn("imRemainHours", DA_FLOAT, DA_ALLOW_NULL, "problem.pro_im_remain_hours");
        $this->addColumn("hdPauseCount", DA_YN, DA_ALLOW_NULL, "problem.pro_hd_pause_count");
        $this->addColumn("allocatedUserID", DA_ID, DA_NOT_NULL, "problem.pro_consno");

        $this->addColumn("queueNo", DA_INTEGER, DA_NOT_NULL, "problem.pro_queue_no");

        $this->setAddColumnsOff();

        $this->fromString =
            $this->getTableName() .
            " LEFT JOIN " .
            " callacttype ON caa_callacttypeno = cat_callacttypeno" .
            " LEFT JOIN " .
            " item AS activity_type_item ON cat_itemno = activity_type_item.itm_itemno" .
            " LEFT JOIN " .
            " consultant ON consultant.cns_consno = caa_consno" .
            " LEFT JOIN " .
            " problem as problem ON problem.pro_problemno = callactivity.caa_problemno" .
            " LEFT JOIN " .
            " customer ON cus_custno = pro_custno" .
            " LEFT JOIN " .
            " address ON add_custno = pro_custno" .
            " AND add_siteno = caa_siteno" .
            " LEFT JOIN " .
            " custitem AS contract ON problem.pro_contract_cuino = contract.cui_cuino" .
            " LEFT JOIN " .
            " item AS contractitem ON contract.cui_itemno = contractitem.itm_itemno" .
            " LEFT JOIN " .
            " project ON problem.pro_projectno = project.projectID" .
            " LEFT JOIN " .
            " contact ON con_contno = caa_contno" .
            " LEFT JOIN " .
            " consultant as completed_user ON callactivity.caa_completed_consno = completed_user.cns_consno" .
            " LEFT JOIN " .
            " rootcause ON rootcause.rtc_rootcauseno = problem.pro_rootcauseno";
    }

    function getRow($callActivityID = '')
    {
        if ($callActivityID != '') {
            $this->setPKValue($callActivityID);
        }
        $this->setQueryString(
            "SELECT " .
            $this->getDBColumnNamesAsString() .
            " FROM " . $this->fromString .
            " WHERE " . $this->getPKWhere()
        );

        return (parent::getRow());
    }

    function getIncompleteRows()
    {
        $this->setQueryString(
            "SELECT " .
            $this->getDBColumnNamesAsString() .
            " FROM " . $this->fromString .
            " WHERE caa_completed_consno = 0" .
            " AND caa_date > '2007-02-01'" .                // ignore activities from old call system
            " AND caa_date <= NOW() AND callactivity.caa_problemno <> 0"                                // ignore future activities
        );
        return (parent::getRows());
    }

    function getRowsInIdArray($IDArray)
    {

        foreach ($IDArray as $val):
            $IDs .= "," . $val;
        endforeach;

        $IDs = substr($IDs, 1); // trim comma


        $this->setQueryString(
            "SELECT " .
            $this->getDBColumnNamesAsString() .
            " FROM " . $this->fromString .
            " WHERE caa_callactivityno IN  (" . $IDs . ") AND callactivity.caa_problemno <> 0" .
            " ORDER BY caa_problemno, caa_consno"
        );
        return (parent::getRows());
    }

    function getRowsByDateRange($startDate, $endDate)
    {
        $this->setQueryString(
            "SELECT " .
            $this->getDBColumnNamesAsString() .
            " FROM " . $this->fromString .
            " WHERE caa_date >= '" . mysqli_real_escape_string($this->db->link_id(), $startDate) . "'" .
            " AND caa_date <= '" . mysqli_real_escape_string($this->db->link_id(), $endDate) . "' AND callactivity.caa_problemno <> 0"
        );

        return (parent::getRows());
    }

    /*
      function getRowsByProjectID( $projectID, $includeTravel = false ){

          $query =
              "SELECT ".
                  $this->getDBColumnNamesAsString() .
              " FROM " . $this->fromString .
               " WHERE callactivity.projectID = '" . mysql_escape_string($projectID). "'";

          if ( !$includeTravel ){
              $query .= " AND travelFlag <> 'Y'  AND callactivity.caa_problemno <> 0";
          }

          $query .= " ORDER BY projectID, caa_date, caa_starttime";

          $this->setQueryString( $query );

          return (parent::getRows());
      }
    */
    function getRowsByProblemID(
        $problemID,
        $includeTravel = false,
        $includeOperationalTasks = true,
        $descendingDate = false,
        $fromDate = false, // limits the number of activities returned
        $includeServerGuardUpdates = true
    )
    {

        $query =
            "SELECT " .
            $this->getDBColumnNamesAsString() .
            " FROM " . $this->fromString .
            " WHERE callactivity.caa_problemno = '" . mysqli_real_escape_string($this->db->link_id(), $problemID) . "' AND callactivity.caa_problemno <> 0";

        if (!$includeTravel) {           // isnull in case this is an incomplete activity with no call activity set yet
            $query .= " AND ( travelFlag <> 'Y' OR ISNULL(travelFlag) )";
        }

        if (!$includeOperationalTasks) {
            $query .= " AND ( caa_callacttypeno <>  " . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . " )";
        }

        if (!$includeServerGuardUpdates) {           // isnull in case this is an incomplete activity with no call activity set yet
            $query .= " AND ( caa_callacttypeno <> " . CONFIG_SERVER_GUARD_UPDATE_ACTIVITY_TYPE_ID . " )";
        }

        if ($fromDate) {
            $query .= " AND caa_date > DATE( '" . $fromDate . "')";
        }

        if ($descendingDate) {
            $query .= " ORDER BY caa_date DESC, caa_starttime DESC";
        } else {
            $query .= " ORDER BY caa_date, caa_starttime";
        }

        $this->setQueryString($query);

        return (parent::getRows());

    }

    function countRowsByCustomerSiteNo($customerID, $siteNo)
    {
        $this->setQueryString(
            "SELECT COUNT(*) FROM " . $this->getTableName() .
            " JOIN problem ON pro_problemno = caa_problemno" .
            " WHERE pro_custno =" . $customerID .
            " AND " . $this->getDBColumnName('siteNo') . "=" . $siteNo
        );
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $this->resetQueryString();
                return ($this->getDBColumnValue(0));
            }
        }
    }
}

?>
