<?php /*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBECallActivitySearch extends DBEntity
{
    const callActivityID = "callActivityID";
    const siteNo = "siteNo";
    const contactID = "contactID";
    const sequenceNo = "sequenceNo";
    const callActTypeID = "callActTypeID";
    const activityType = "activityType";
    const projectID = "projectID";
    const problemID = "problemID";
    const userID = "userID";
    const userName = "userName";
    const date = "date";
    const startTime = "startTime";
    const endTime = "endTime";
    const status = "status";
    const problemStatus = "problemStatus";
    const reason = "reason";
    const internalNotes = "serviceRequestInternalNote.content";
    const curValue = "curValue";
    const priority = "priority";
    const statementYearMonth = "statementYearMonth";
    const projectDescription = "projectDescription";
    const duration = "duration";
    const salePrice = "salePrice";
    const customerID = "customerID";
    const customerName = "customerName";
    const postcode = "postcode";
    const contactName = "contactName";
    const customerItemID = "customerItemID";
    const contractCustomerItemID = "contractCustomerItemID";
    const contractDescription = "contractDescription";
    const underContractFlag = "underContractFlag";
    const slaResponseHours = "slaResponseHours";
    const respondedHours = "respondedHours";
    const workingHours = "workingHours";
    const activityDurationHours = "activityDurationHours";
    const rootCause = "rootCause";
    const fixEngineer = "fixEngineer";
    const activityCount = "activityCount";
    const linkedSalesOrderID = "linkedSalesOrderID";
    const managementReviewReason = "managementReviewReason";
    const salesOrderID = 'salesOrderID';

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
        $this->setTableName("callactivity");
        $this->addColumn(
            self::callActivityID,
            DA_ID,
            DA_NOT_NULL,
            "caa_callactivityno"
        );
        $this->addColumn(
            self::siteNo,
            DA_INTEGER,
            DA_NOT_NULL,
            "caa_siteno"
        );
        $this->addColumn(
            self::contactID,
            DA_INTEGER,
            DA_NOT_NULL,
            "caa_contno"
        );        // customer contact
        $this->addColumn(
            self::sequenceNo,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "caa_item"
        );
        $this->addColumn(
            self::callActTypeID,
            DA_ID,
            DA_NOT_NULL,
            "caa_callacttypeno"
        );
        $this->addColumn(
            self::activityType,
            DA_STRING,
            DA_NOT_NULL,
            "cat_desc"
        );
        $this->addColumn(
            self::projectID,
            DA_ID,
            DA_NOT_NULL,
            "problem.pro_projectno"
        );
        $this->addColumn(
            self::problemID,
            DA_ID,
            DA_NOT_NULL,
            "caa_problemno"
        );
        $this->addColumn(
            self::userID,
            DA_ID,
            DA_NOT_NULL,
            "caa_consno"
        );
        $this->addColumn(
            self::userName,
            DA_STRING,
            DA_NOT_NULL,
            "consultant.cns_name"
        );
        $this->addColumn(
            self::date,
            DA_DATE,
            DA_NOT_NULL,
            "caa_date"
        );
        $this->addColumn(
            self::startTime,
            DA_DATETIME,
            DA_NOT_NULL,
            "caa_starttime"
        );
        $this->addColumn(
            self::endTime,
            DA_DATETIME,
            DA_ALLOW_NULL,
            "caa_endtime"
        );
        $this->addColumn(
            self::status,
            DA_STRING,
            DA_NOT_NULL,
            "caa_status"
        );
        $this->addColumn(
            self::problemStatus,
            DA_STRING,
            DA_NOT_NULL,
            "pro_status"
        );
        $this->addColumn(
            self::reason,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::internalNotes,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::curValue,
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::priority,
            DA_FLOAT,
            DA_ALLOW_NULL,
            'pro_priority'
        );
        $this->addColumn(
            self::statementYearMonth,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::projectDescription,
            DA_STRING,
            DA_ALLOW_NULL,
            'project.description'
        );
        $this->addColumn(
            self::duration,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime)"
        );
        $this->addColumn(
            self::salePrice,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "activityItem.itm_sstk_price"
        );
        $this->addColumn(
            self::customerID,
            DA_ID,
            DA_ALLOW_NULL,
            "cus_custno"
        );
        $this->addColumn(
            self::customerName,
            DA_STRING,
            DA_ALLOW_NULL,
            "cus_name"
        );
        $this->addColumn(
            self::postcode,
            DA_STRING,
            DA_NOT_NULL,
            "add_postcode"
        );
        $this->addColumn(
            self::contactName,
            DA_STRING,
            DA_ALLOW_NULL,
            "CONCAT(con_first_name, ' ', con_last_name)"
        );
        $this->addColumn(
            self::customerItemID,
            DA_ID,
            DA_ALLOW_NULL,
            "caa_cuino"
        );    // Customer Item
        $this->addColumn(
            self::contractCustomerItemID,
            DA_ID,
            DA_ALLOW_NULL,
            "pro_contract_cuino"
        );
        $this->addColumn(
            self::contractDescription,
            DA_STRING,
            DA_ALLOW_NULL,
            "IFNULL(contractitem.itm_desc, 'T & M')"
        );
        $this->addColumn(
            self::underContractFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "caa_under_contract"
        );
        $this->addColumn(
            self::slaResponseHours,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "pro_sla_response_hours"
        );
        $this->addColumn(
            self::respondedHours,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "pro_responded_hours"
        );
        $this->addColumn(
            self::workingHours,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "pro_working_hours"
        );
        $this->addColumn(
            self::activityDurationHours,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "pro_total_activity_duration_hours"
        );
        $this->addColumn(
            self::rootCause,
            DA_STRING,
            DA_ALLOW_NULL,
            "rootcause.rtc_desc"
        );
        $this->addColumn(
            self::fixEngineer,
            DA_STRING,
            DA_ALLOW_NULL,
            "CONCAT( fix_consultant.firstName, ' ', fix_consultant.lastName )"
        );
        $this->addColumn(
            self::activityCount,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "( SELECT COUNT(*) FROM callactivity AS cac WHERE cac.caa_problemno = callactivity.caa_problemno )"
        );
        $this->addColumn(
            self::linkedSalesOrderID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "pro_linked_ordno"
        );
        $this->addColumn(
            self::managementReviewReason,
            DA_MEMO,
            DA_ALLOW_NULL,
            "pro_management_review_reason"
        );

        $this->addColumn(
            self::salesOrderID,
            DA_ID,
            DA_ALLOW_NULL,
            'pro_linked_ordno'
        );

        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getRowsBySearchCriteria(
        $callActivityID = null,
        $problemID = null,
        $customerID = null,
        $userID = null,
        $status = null,
        $rootCauseID = null,
        $priority = null,
        $activityText = null,
        $serviceRequestSpentTime = null,
        $individualActivitySpentTime = null,
        $fromDate = null,
        $toDate = null,
        $contractCustomerItemID = null,
        $callActTypeID = null,
        $linkedSalesOrderID = null,
        $managementReviewOnly = 'N',
        $breachedSlaOption = '',
        $sortColumn = false,
        $sortDirection = 'ASC',
        $limit = true,
        $fixSLAStatus = null,
        $overFixSLAWorkingHours = null
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
          left join serviceRequestInternalNote
            on serviceRequestInternalNote.serviceRequestId = problem.pro_problemno
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
            if (strpos(
                $callActivityID,
                ','
            )) {
                $whereParameters .=
                    " AND caa_callactivityno IN (" . $callActivityID . ")";

            } else {
                $whereParameters .=
                    " AND caa_callactivityno = " . $callActivityID;
            }
        }

        if ($overFixSLAWorkingHours) {
            $statement .= " and caa_problemno in (select pro_problemno
                        from problem
                                 join callactivity initial ON initial.`caa_problemno` = problem.`pro_problemno` AND
                                                              initial.`caa_callacttypeno` = 51
                                 join callactivity fixed
                                      on fixed.caa_problemno = problem.pro_problemno and fixed.caa_callacttypeno = 57
                        where pro_status in ('F', 'C')
                          and timestampdiff(HOUR, concat(initial.caa_date, ' ', initial.caa_starttime, ':00'),
                                            concat(fixed.caa_date, ' ', fixed.caa_starttime, ':00')
                                  ) >
                              CASE problem.`pro_priority`
                                  WHEN 1 THEN customer.slaFixHoursP1
                                  WHEN 2 THEN customer.slaFixHoursP2
                                  WHEN 3 THEN customer.slaFixHoursP3
                                  WHEN 4 THEN customer.slaFixHoursP4
                                  END
)";
        }

        if ($problemID) {
            if (strpos(
                $problemID,
                ','
            )) {
                $whereParameters .=
                    " AND caa_problemno IN (" . $problemID . ")";

            } else {
                $whereParameters .=
                    " AND caa_problemno = " . $problemID;
            }
        }

        if ($customerID != '' and $customerID != 0) {
            $whereParameters = $whereParameters .
                " AND " . $this->getDBColumnName(self::customerID) . "=" . $customerID;
        }

        if ($linkedSalesOrderID != '' and $linkedSalesOrderID != 0) {
            $whereParameters = $whereParameters .
                " AND " . $this->getDBColumnName(self::linkedSalesOrderID) . "=" . $linkedSalesOrderID;
        }

        if ($userID != '' and $userID != 0) {
            $whereParameters = $whereParameters .
                " AND " . $this->getDBColumnName(self::userID) . "=" . $userID;
        }

        if ($managementReviewOnly == 'Y') {
            $whereParameters = $whereParameters .
                " AND " . $this->getDBColumnName(self::managementReviewReason) . "<> ''";
        }

        if ($activityText != '') {
            $search = str_replace("@", " ", $activityText);
            $whereParameters .=
                " AND ( MATCH (reason)
					AGAINST ('" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $search
                ) . "' IN BOOLEAN MODE))";
        }

        if ($serviceRequestSpentTime != '' && $this->testSpentTimeSearchString($serviceRequestSpentTime)) {

            if (preg_match(
                    '/^\d/',
                    $serviceRequestSpentTime
                ) === 1) {
                $serviceRequestSpentTime = '= ' . $serviceRequestSpentTime;
            }

            $whereParameters .=
                " and pro_total_activity_duration_hours " . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $serviceRequestSpentTime
                );
        }

        if ($individualActivitySpentTime != '' && $this->testSpentTimeSearchString($individualActivitySpentTime)) {
            if (preg_match(
                    '/^\d/',
                    $individualActivitySpentTime
                ) === 1) {
                $individualActivitySpentTime = '= ' . $individualActivitySpentTime;
            }

            $whereParameters .=
                " and ((TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime))/3600) " . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $individualActivitySpentTime
                );
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
                    " AND caa_status = 'C' AND " . $this->getDBColumnName(self::contractCustomerItemID) . " is null
            AND pro_status = 'F'
            AND pro_complete_date <= now()
            AND holdForQA =0
            AND caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID;
                break;
            /*
            Checked Non-T&M on Fixed SRs which are due for completion
            */
            case 'CHECKED_NON_T_AND_M':
                $whereParameters .=
                    " AND caa_status = 'C' AND " . $this->getDBColumnName(self::contractCustomerItemID) . " is not null 
            AND pro_status = 'F'
            AND pro_complete_date <= now()
            AND holdForQA =0
            AND " . $this->getDBColumnName(self::activityDurationHours) . " > 
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
            AND caa_endtime <> '' and caa_endtime is not null ";
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
            case 'FIXED_OR_COMPLETED':
                $whereParameters .= " and pro_status in ('F','C') ";
                break;
            case "HOLD_FOR_QA":
                $whereParameters .= " AND caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID." AND holdForQA =1 ";
        }
        // Contract Type:

        if ($contractCustomerItemID == '') {                        // Time and materials
            $whereParameters .=
                " AND " . $this->getDBColumnName(self::contractCustomerItemID) . " is null";
        } elseif ($contractCustomerItemID != 99) {        // anything other than All
            $whereParameters .=
                " AND " . $this->getDBColumnName(self::contractCustomerItemID) . " = $contractCustomerItemID";
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

        if ($fixSLAStatus == 'B') {
            $whereParameters .= " and  pro_priority <> 5
  and pro_working_hours > case pro_priority
                              when 1 then slaFixHoursP1
                              when 2 then slaFixHoursP2
                              when 3 then slaFixHoursP3
                              when 4 then slaFixHoursP4
                              else 0 end  ";
        } elseif ($fixSLAStatus == 'N') {
            $whereParameters .= " and pro_priority <> 5
  and pro_working_hours <= case pro_priority
                              when 1 then slaFixHoursP1
                              when 2 then slaFixHoursP2
                              when 3 then slaFixHoursP3
                              when 4 then slaFixHoursP4
                              else 0 end ";
        }

        if ($breachedSlaOption == 'B') {
            $whereParameters .=
                " AND (
    (
      pro_status = 'I'
      AND pro_sla_response_hours - pro_working_hours <= 0
    )
    OR (
      `pro_responded_hours` > pro_sla_response_hours
    )
  )";
        } elseif ($breachedSlaOption == 'N') {
            $whereParameters .=
                " AND pro_responded_hours <= pro_sla_response_hours";
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

    private function testSpentTimeSearchString($timeSpentString)
    {
        if ($timeSpentString == '') {
            return true;
        }

        return preg_match(
            "/((\d+(\.?\d+)?)|=|<|<=|>|>=|<>)\s*(\d+(\.?\d+)?)/",
            $timeSpentString
        );
    }
}
