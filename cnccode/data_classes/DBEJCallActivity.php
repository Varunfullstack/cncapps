<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 30/03/2018
 * Time: 11:10
 */

/*
* Call activity join
* @authors Karim Ahmed
* @access public
*/

class DBEJCallActivity extends DBECallActivity
{
    var $fromString;

    const itemID = "itemID";
    const activityType = "activityType";
    const requireCheckFlag = "requireCheckFlag";
    const onSiteFlag = "onSiteFlag";
    const allowExpensesFlag = "allowExpensesFlag";
    const travelFlag = "travelFlag";
    const allowSCRFlag = "allowSCRFlag";
    const userID = "userID";
    const userName = "userName";
    const userAccount = "userAccount";
    const durationMinutes = "durationMinutes";
    const siteDesc = "siteDesc";
    const customerID = "customerID";
    const customerName = "customerName";
    const contactFirstName = "contactFirstName";
    const contactName = "contactName";
    const contactNotes = "contactNotes";
    const techNotes = "techNotes";
    const contactEmail = "contactEmail";
    const workStartedEmailFlag = "workStartedEmailFlag";
    const autoCloseEmailFlag = "autoCloseEmailFlag";
    const dateEngineer = "dateEngineer";
    const contractCustomerItemID = "contractCustomerItemID";
    const contractItemID = "contractItemID";
    const contractResponseTime = "contractResponseTime";
    const contractDescription = "contractDescription";
    const activityTypeCost = "activityTypeCost";
    const curValueFlag = "curValueFlag";
    const projectDescription = "projectDescription";
    const completedName = "completedName";
    const priority = "priority";
    const problemHideFromCustomerFlag = "problemHideFromCustomerFlag";
    const problemStatus = "problemStatus";
    const requestAwaitingCustomerResponseFlag = "requestAwaitingCustomerResponseFlag";
    const internalNotes = "internalNotes";
    const completeDate = "completeDate";
    const alarmDate = "alarmDate";
    const alarmTime = "alarmTime";
    const rootCauseID = "rootCauseID";
    const rootCauseDescription = "rootCauseDescription";
    const projectID = "projectID";
    const linkedSalesOrderID = "linkedSalesOrderID";
    const totalActivityDurationHours = "totalActivityDurationHours";
    const criticalFlag = "criticalFlag";
    const hdLimitMinutes = "hdLimitMinutes";
    const esLimitMinutes = "esLimitMinutes";
    const imLimitMinutes = "imLimitMinutes";
    const hdPauseCount = "hdPauseCount";
    const allocatedUserID = "allocatedUserID";
    const queueNo = "queueNo";

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
        $this->addColumn(
            self::itemID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "cat_itemno"
        );
        $this->addColumn(
            self::activityType,
            DA_STRING,
            DA_ALLOW_NULL,
            "cat_desc"
        );
        $this->addColumn(
            self::requireCheckFlag,
            DA_YN,
            DA_NOT_NULL,
            "cat_req_check_flag"
        );
        $this->addColumn(
            self::onSiteFlag,
            DA_YN,
            DA_NOT_NULL,
            "cat_on_site_flag"
        );
        $this->addColumn(
            self::allowExpensesFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "cat_allow_exp_flag"
        );
        $this->addColumn(
            self::travelFlag,
            DA_YN,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::allowSCRFlag,
            DA_YN,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::userID,
            DA_ID,
            DA_ALLOW_NULL,
            "consultant.cns_consno"
        );
        $this->addColumn(
            self::userName,
            DA_STRING,
            DA_ALLOW_NULL,
            "CONCAT(consultant.firstName,' ',consultant.lastName)"
        );
        $this->addColumn(
            self::userAccount,
            DA_STRING,
            DA_ALLOW_NULL,
            "consultant.cns_logname"
        );
        $this->addColumn(
            self::durationMinutes,
            DA_STRING,
            DA_ALLOW_NULL,
            "( TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime) ) / 60"
        );
        $this->addColumn(
            self::siteDesc,
            DA_STRING,
            DA_ALLOW_NULL,
            "CONCAT(add_add1,' ',add_add2,' ',add_town)"
        );
        $this->addColumn(
            self::customerID,
            DA_STRING,
            DA_ALLOW_NULL,
            "problem.pro_custno"
        );
        $this->addColumn(
            self::customerName,
            DA_STRING,
            DA_ALLOW_NULL,
            "cus_name"
        );
        $this->addColumn(
            self::contactFirstName,
            DA_STRING,
            DA_ALLOW_NULL,
            "con_first_name"
        );
        $this->addColumn(
            self::contactName,
            DA_STRING,
            DA_ALLOW_NULL,
            "CONCAT(con_first_name,' ',con_last_name)"
        );
        $this->addColumn(
            self::contactNotes,
            DA_STRING,
            DA_ALLOW_NULL,
            "con_notes"
        );
        $this->addColumn(
            self::techNotes,
            DA_STRING,
            DA_ALLOW_NULL,
            "cus_tech_notes"
        );
        $this->addColumn(
            self::contactEmail,
            DA_STRING,
            DA_ALLOW_NULL,
            "con_email"
        );
        $this->addColumn(
            self::workStartedEmailFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "con_work_started_email_flag"
        );
        $this->addColumn(
            self::autoCloseEmailFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "con_auto_close_email_flag"
        );
        $this->addColumn(
            self::dateEngineer,
            DA_STRING,
            DA_ALLOW_NULL,
            "CONCAT(DATE_FORMAT(caa_date,'%e/%c/%y'), ' - ', consultant.firstName,' ',consultant.lastName)"
        );
        $this->addColumn(
            self::contractCustomerItemID,
            DA_ID,
            DA_ALLOW_NULL,
            "problem.pro_contract_cuino"
        );
        $this->addColumn(
            self::contractItemID,
            DA_ID,
            DA_ALLOW_NULL,
            "contractitem.itm_itemno"
        );
        $this->addColumn(
            self::contractResponseTime,
            DA_STRING,
            DA_ALLOW_NULL,
            "contractitem.contractResponseTime"
        );
        $this->addColumn(
            self::contractDescription,
            DA_STRING,
            DA_ALLOW_NULL,
            "if (contractitem.itm_desc IS NULL, 'T&M', contractitem.itm_desc)"
        );
        $this->addColumn(
            self::activityTypeCost,
            DA_STRING,
            DA_ALLOW_NULL,
            "activity_type_item.itm_sstk_price"
        );                // per hour cost of this activity
        $this->addColumn(
            self::curValueFlag,
            DA_YN,
            DA_ALLOW_NULL
        );        // is it a Value Type activity?
        $this->addColumn(
            self::projectDescription,
            DA_STRING,
            DA_ALLOW_NULL,
            "if (project.description IS NULL, 'None', project.description)"
        );
        $this->addColumn(
            self::completedName,
            DA_STRING,
            DA_ALLOW_NULL,
            "completed_user.cns_logname"
        );
        $this->addColumn(
            self::priority,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "problem.pro_priority"
        );
        $this->addColumn(
            self::problemHideFromCustomerFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "problem.pro_hide_from_customer_flag"
        );
        $this->addColumn(
            self::problemStatus,
            DA_STRING,
            DA_ALLOW_NULL,
            "problem.pro_status"
        );
        $this->addColumn(
            self::requestAwaitingCustomerResponseFlag,
            DA_STRING,
            DA_ALLOW_NULL,
            "problem.pro_awaiting_customer_response_flag"
        );
        $this->addColumn(
            self::internalNotes,
            DA_MEMO,
            DA_ALLOW_NULL,
            "problem.pro_internal_notes"
        );
        $this->addColumn(
            self::completeDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "problem.pro_complete_date"
        );
        $this->addColumn(
            self::alarmDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "problem.pro_alarm_date"
        );
        $this->addColumn(
            self::alarmTime,
            DA_TIME,
            DA_ALLOW_NULL,
            "problem.pro_alarm_time"
        );
        $this->addColumn(
            self::rootCauseID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "problem.pro_rootcauseno"
        );
        $this->addColumn(
            self::rootCauseDescription,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "rootcause.rtc_desc"
        );
        $this->addColumn(
            self::projectID,
            DA_ID,
            DA_ALLOW_NULL,
            "problem.pro_projectno"
        );
        $this->addColumn(
            self::linkedSalesOrderID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "problem.pro_linked_ordno"
        );
        $this->addColumn(
            self::totalActivityDurationHours,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "problem.pro_total_activity_duration_hours"
        );
        $this->addColumn(
            self::criticalFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "problem.pro_critical_flag"
        );
        $this->addColumn(
            self::hdLimitMinutes,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "problem.pro_hd_limit_minutes"
        );
        $this->addColumn(
            self::esLimitMinutes,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "problem.pro_es_limit_minutes"
        );
        $this->addColumn(
            self::imLimitMinutes,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "problem.pro_im_limit_minutes"
        );
        $this->addColumn(
            self::allocatedUserID,
            DA_ID,
            DA_NOT_NULL,
            "problem.pro_consno"
        );
        $this->addColumn(
            self::queueNo,
            DA_INTEGER,
            DA_NOT_NULL,
            "problem.pro_queue_no"
        );

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

        $IDs = substr(
            $IDs,
            1
        ); // trim comma

        $query = "SELECT " .
            $this->getDBColumnNamesAsString() .
            " FROM " . $this->fromString .
            " WHERE caa_callactivityno IN  (" . $IDs . ") AND callactivity.caa_problemno <> 0" .
            " ORDER BY caa_problemno, caa_consno";
        $this->setQueryString($query);
        return (parent::getRows());
    }

    function getRowsByDateRange($startDate,
                                $endDate
    )
    {
        $this->setQueryString(
            "SELECT " .
            $this->getDBColumnNamesAsString() .
            " FROM " . $this->fromString .
            " WHERE caa_date >= '" . mysqli_real_escape_string(
                $this->db->link_id(),
                $startDate
            ) . "'" .
            " AND caa_date <= '" . mysqli_real_escape_string(
                $this->db->link_id(),
                $endDate
            ) . "' AND callactivity.caa_problemno <> 0"
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
        $fromDate = false,
        // limits the number of activities returned
        $includeServerGuardUpdates = true
    )
    {

        $query =
            "SELECT " .
            $this->getDBColumnNamesAsString() .
            " FROM " . $this->fromString .
            " WHERE callactivity.caa_problemno = '" . mysqli_real_escape_string(
                $this->db->link_id(),
                $problemID
            ) . "' AND callactivity.caa_problemno <> 0";

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

    function countRowsByCustomerSiteNo($customerID,
                                       $siteNo
    )
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

    public function getPendingSalesRequestRows()
    {
        $query =
            "SELECT " .
            $this->getDBColumnNamesAsString() .
            " FROM " . $this->fromString .
            " WHERE callactivity.caa_status = 'O' and caa_callacttypeno = 43";
        $this->setQueryString($query);
        return (parent::getRows());
    }
}