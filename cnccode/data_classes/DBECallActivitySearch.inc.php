<?php /*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBECallActivitySearch extends DBEntity
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
        $this->addColumn("siteNo", DA_INTEGER, DA_NOT_NULL, "caa_siteno");
        $this->addColumn("contactID", DA_INTEGER, DA_NOT_NULL, "caa_contno");        // customer contact
        $this->addColumn("sequenceNo", DA_INTEGER, DA_ALLOW_NULL, "caa_item");
        $this->addColumn("callActTypeID", DA_ID, DA_NOT_NULL, "caa_callacttypeno");
        $this->addColumn("activityType", DA_STRING, DA_NOT_NULL, "cat_desc");
        $this->addColumn("projectID", DA_ID, DA_NOT_NULL, "problem.pro_projectno");
        $this->addColumn("problemID", DA_ID, DA_NOT_NULL, "caa_problemno");
        $this->addColumn("userID", DA_ID, DA_NOT_NULL, "caa_consno");
        $this->addColumn("userName", DA_STRING, DA_NOT_NULL, "consultant.cns_name");
        $this->addColumn("date", DA_DATE, DA_NOT_NULL, "caa_date");
        $this->addColumn("startTime", DA_DATETIME, DA_NOT_NULL, "caa_starttime");
        $this->addColumn("endTime", DA_DATETIME, DA_ALLOW_NULL, "caa_endtime");
        $this->addColumn("status", DA_STRING, DA_NOT_NULL, "caa_status");
        $this->addColumn("problemStatus", DA_STRING, DA_NOT_NULL, "pro_status");
        $this->addColumn("reason", DA_TEXT, DA_ALLOW_NULL);
        $this->addColumn("internalNotes", DA_TEXT, DA_ALLOW_NULL);
        $this->addColumn("curValue", DA_FLOAT, DA_ALLOW_NULL);
        $this->addColumn("priority", DA_FLOAT, DA_ALLOW_NULL, 'pro_priority');
        $this->addColumn("statementYearMonth", DA_STRING, DA_ALLOW_NULL);
        $this->addColumn("projectDescription", DA_STRING, DA_ALLOW_NULL, 'project.description');
        $this->addColumn("duration", DA_FLOAT, DA_ALLOW_NULL, "TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime)");
        $this->addColumn("salePrice", DA_FLOAT, DA_ALLOW_NULL, "activityItem.itm_sstk_price");
        $this->addColumn("customerID", DA_ID, DA_ALLOW_NULL, "cus_custno");
        $this->addColumn("customerName", DA_STRING, DA_ALLOW_NULL, "cus_name");
        $this->addColumn("postcode", DA_STRING, DA_NOT_NULL, "add_postcode");
        $this->addColumn("contactName", DA_STRING, DA_ALLOW_NULL, "CONCAT(con_first_name, ' ', con_last_name)");
        $this->addColumn("customerItemID", DA_ID, DA_ALLOW_NULL, "caa_cuino");    // Customer Item
        $this->addColumn("customerID", DA_ID, DA_NOT_NULL, "pro_custno");
        $this->addColumn("contractCustomerItemID", DA_ID, DA_ALLOW_NULL, "pro_contract_cuino");
        $this->addColumn("contractDescription", DA_STRING, DA_ALLOW_NULL, "IFNULL(contractitem.itm_desc, 'T & M')");
        $this->addColumn("underContractFlag", DA_YN, DA_ALLOW_NULL, "caa_under_contract");
        $this->addColumn("allowExpensesFlag", DA_YN, DA_ALLOW_NULL, "cat_allow_exp_flag");
        $this->addColumn("allowSCRFlag", DA_YN, DA_ALLOW_NULL);
        $this->addColumn("activityType", DA_STRING, DA_ALLOW_NULL, 'cat_desc');
        $this->addColumn("slaResponseHours", DA_INTEGER, DA_ALLOW_NULL, "pro_sla_response_hours");
        $this->addColumn("respondedHours", DA_INTEGER, DA_ALLOW_NULL, "pro_responded_hours");
        $this->addColumn("workingHours", DA_INTEGER, DA_ALLOW_NULL, "pro_working_hours");
        $this->addColumn("activityDurationHours", DA_INTEGER, DA_ALLOW_NULL, "pro_total_activity_duration_hours");
        $this->addColumn("rootCause", DA_STRING, DA_ALLOW_NULL, "rootcause.rtc_desc");
        $this->addColumn("fixEngineer",
                         DA_STRING,
                         DA_ALLOW_NULL,
                         "CONCAT( fix_consultant.firstName, ' ', fix_consultant.lastName )");
        $this->addColumn("activityCount",
                         DA_INTEGER,
                         DA_ALLOW_NULL,
                         "( SELECT COUNT(*) FROM callactivity AS cac WHERE cac.caa_problemno = callactivity.caa_problemno )");
        $this->addColumn("linkedSalesOrderID", DA_INTEGER, DA_ALLOW_NULL, "pro_linked_ordno");
        $this->addColumn("managementReviewReason", DA_MEMO, DA_ALLOW_NULL, "pro_management_review_reason");

        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    private function testSpentTimeSearchString($timeSpentString)
    {
        if ($timeSpentString == '') {
            return true;
        }

        return preg_match("/((\d+(\.?\d+)?)|=|<|<=|>|>=|<>)\s*(\d+(\.?\d+)?)/", $timeSpentString);
    }

    function getRowsBySearchCriteria(
        $callActivityID,
        $problemID,
        $customerID,
        $userID,
        $status,
        $rootCauseID,
        $priority,
        $activityText,
        $serviceRequestSpentTime,
        $individualActivitySpentTime,
        $fromDate,
        $toDate,
        $contractCustomerItemID,
        $callActTypeID,
        $linkedSalesOrderID,
        $managementReviewOnly = 'N',
        $breachedSlaOption = '',
        $sortColumn = false,
        $sortDirection = 'ASC',
        $limit = true
    )
    {
        $this->setMethodName('getRowsBySearchCriteria');
        $statement =
            "SELECT " . $this->getDBColumnNamesAsString() .
            "
            FROM
          callactivity 
          LEFT JOIN callacttype 
            ON caa_callacttypeno = cat_callacttypeno 
          LEFT JOIN custitem 
            ON callactivity.caa_cuino = custitem.cui_cuino 
          LEFT JOIN problem 
            ON problem.pro_problemno = callactivity.caa_problemno 
          LEFT JOIN item 
            ON custitem.cui_itemno = item.itm_itemno 
          LEFT JOIN project 
            ON project.projectID = problem.pro_projectno  
          LEFT JOIN item AS activityItem
            ON callacttype.cat_itemno = activityItem.itm_itemno 
          LEFT JOIN custitem AS contract 
            ON problem.pro_contract_cuino = contract.cui_cuino 
          LEFT JOIN consultant 
            ON callactivity.caa_consno = consultant.cns_consno 
          LEFT JOIN address 
            ON problem.pro_custno = add_custno 
            AND caa_siteno = add_siteno 
          LEFT JOIN contact 
            ON callactivity.caa_contno = contact.con_contno 
          LEFT JOIN item AS contractitem 
            ON contract.cui_itemno = contractitem.itm_itemno 
          LEFT JOIN contract AS warranty 
            ON custitem.cui_man_contno = warranty.cnt_contno
          LEFT JOIN rootcause
            ON problem.pro_rootcauseno = rootcause.rtc_rootcauseno
          LEFT JOIN consultant AS fix_consultant
            ON problem.pro_fixed_consno = fix_consultant.cns_consno
          INNER JOIN customer 
            ON problem.pro_custno = customer.cus_custno";

        $statement .=
            " WHERE 1=1";

        $whereParameters = false;

        if ($callActivityID) {
            if (strpos($callActivityID, ',')) {
                $whereParameters .=
                    " AND caa_callactivityno IN (" . $callActivityID . ")";

            } else {
                $whereParameters .=
                    " AND caa_callactivityno = " . $callActivityID;
            }
        }
        if ($problemID) {
            if (strpos($problemID, ',')) {
                $whereParameters .=
                    " AND caa_problemno IN (" . $problemID . ")";

            } else {
                $whereParameters .=
                    " AND caa_problemno = " . $problemID;
            }
        }

        if ($customerID != '' AND $customerID != 0) {
            $whereParameters = $whereParameters .
                " AND " . $this->getDBColumnName('customerID') . "=" . $customerID;
        }

        if ($linkedSalesOrderID != '' AND $linkedSalesOrderID != 0) {
            $whereParameters = $whereParameters .
                " AND " . $this->getDBColumnName('linkedSalesOrderID') . "=" . $linkedSalesOrderID;
        }

        if ($userID != '' AND $userID != 0) {
            $whereParameters = $whereParameters .
                " AND " . $this->getDBColumnName('userID') . "=" . $userID;
        }

        if ($managementReviewOnly == 'Y') {
            $whereParameters = $whereParameters .
                " AND " . $this->getDBColumnName('managementReviewReason') . "<> ''";
        }

        if ($activityText != '') {
            $whereParameters .=
                " AND ( MATCH (reason)
					AGAINST ('" . mysqli_real_escape_string($this->db->link_id(), $activityText) . "' IN BOOLEAN MODE)
          OR MATCH (pro_internal_notes)
          AGAINST ('" . mysqli_real_escape_string($this->db->link_id(), $activityText) . "' IN BOOLEAN MODE) )";
        }

        if ($serviceRequestSpentTime != '' && $this->testSpentTimeSearchString($serviceRequestSpentTime)) {

            if (preg_match('/^\d/', $serviceRequestSpentTime) === 1) {
                $serviceRequestSpentTime = '= ' . $serviceRequestSpentTime;
            }

            $whereParameters .=
                " and pro_total_activity_duration_hours " . mysqli_real_escape_string($this->db->link_id(),
                                                                                      $serviceRequestSpentTime);
        }

        if ($individualActivitySpentTime != '' && $this->testSpentTimeSearchString($individualActivitySpentTime)) {
            if (preg_match('/^\d/', $individualActivitySpentTime) === 1) {
                $individualActivitySpentTime = '= ' . $individualActivitySpentTime;
            }

            $whereParameters .=
                " and ((TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime))/3600) " . mysqli_real_escape_string($this->db->link_id(),
                                                                                                                   $individualActivitySpentTime);
        }


        switch ($status) {
            case 'INITIAL':
                $whereParameters .=
                    " AND pro_status = 'I'";
                break;
            case 'CUSTOMER':
                $whereParameters .=
                    " AND pro_awaiting_customer_response_flag = 'Y'
            AND pro_status = 'P'";
                break;
            case 'CNC':
                $whereParameters .=
                    " AND pro_awaiting_customer_response_flag = 'N'
            AND pro_status = 'P'";
                break;
            /*
            Checked T&M on Fixed SRs which are due for completion
            */
            case 'CHECKED_T_AND_M':
                $whereParameters .=
                    " AND caa_status = 'C' AND " . $this->getDBColumnName('contractCustomerItemID') . "= 0
            AND pro_status = 'F'
            AND pro_complete_date <= now()
            AND caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID;
                break;
            /*
            Checked Non-T&M on Fixed SRs which are due for completion
            */
            case 'CHECKED_NON_T_AND_M':
                $whereParameters .=
                    " AND caa_status = 'C' AND " . $this->getDBColumnName('contractCustomerItemID') . "<> 0

            AND pro_status = 'F'

            AND pro_complete_date <= now()
            
            AND " . $this->getDBColumnName('activityDurationHours') . "> 
            
            (
              SELECT
                hed_sr_autocomplete_threshold_hours
              FROM
                headert
            )
            
            AND caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID;
                break;

            case 'UNCHECKED':              // UnChecked
                $whereParameters .=
                    " AND caa_status = 'O'
            AND caa_endtime <> ''";
                break;
            case 'FIXED':
                $whereParameters .=
                    " AND pro_status ='F'";
                break;
            case 'NOT_FIXED':
                $whereParameters .=
                    " AND ( pro_status ='I' OR pro_status ='P' )";
                break;
            case 'COMPLETED':
                $whereParameters .=
                    " AND pro_status ='C'";
                break;
        }
        // Contract Type:

        if ($contractCustomerItemID == '') {                        // Time and materials
            $whereParameters .=
                " AND " . $this->getDBColumnName('contractCustomerItemID') . " = 0";
        } elseif ($contractCustomerItemID != 99) {        // anything other than All
            $whereParameters .=
                " AND " . $this->getDBColumnName('contractCustomerItemID') . " = $contractCustomerItemID";
        }

        if ($fromDate != '') {
            $whereParameters .=
                " AND caa_date >= '" . $fromDate . "'";
        }

        if ($toDate != '') {
            $whereParameters .=
                " AND caa_date <= '" . $toDate . "'";
        }

        if ($callActTypeID != '') {
            $whereParameters .=
                " AND caa_callacttypeno = " . $callActTypeID;
        }

        switch ($breachedSlaOption) {
            case 'B':
                $whereParameters .=
                    " AND pro_responded_hours > pro_sla_response_hours";
                break;
            case 'N':
                $whereParameters .=
                    " AND pro_responded_hours <= pro_sla_response_hours";
                break;
        }

        if ($rootCauseID != '') {
            $whereParameters .=
                " AND pro_rootcauseno = " . $rootCauseID;
        }

        if ($priority != '') {
            $whereParameters .=
                " AND problem.pro_priority = '" . $priority . "'";
        }

        if ($whereParameters) {
            $statement .= $whereParameters;
        }

        if ($sortColumn) {
            $statement .= " ORDER BY " . $this->getDBColumnName($sortColumn) . " " . $sortDirection;
        } else {
            $statement .= " ORDER BY callactivity.caa_date DESC, callactivity.caa_starttime DESC, callactivity.caa_consno";
        }

        if ($limit) {
            $statement .= " LIMIT 0, 150";
        }
        $this->setQueryString($statement);
        $ret = (parent::getRows());
        return $ret;
    }
}

?>
