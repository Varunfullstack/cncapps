<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 30/03/2018
 * Time: 11:10
 */

use CNCLTD\Data\DBConnect;

global $cfg;
require_once __DIR__ . '/DBECallActivity.inc.php';

/*
* Call activity join
* @authors Karim Ahmed
* @access public
*/

class DBEJCallActivity extends DBECallActivity
{
    const itemID                              = "itemID";
    const activityType                        = "activityType";
    const requireCheckFlag                    = "requireCheckFlag";
    const onSiteFlag                          = "onSiteFlag";
    const travelFlag                          = "travelFlag";
    const userName                            = "userName";
    const userAccount                         = "userAccount";
    const durationMinutes                     = "durationMinutes";
    const siteDesc                            = "siteDesc";
    const customerID                          = "customerID";
    const customerName                        = "customerName";
    const contactFirstName                    = "contactFirstName";
    const contactName                         = "contactName";
    const contactNotes                        = "contactNotes";
    const techNotes                           = "techNotes";
    const contactEmail                        = "contactEmail";
    const autoCloseEmailFlag                  = "autoCloseEmailFlag";
    const dateEngineer                        = "dateEngineer";
    const contractCustomerItemID              = "contractCustomerItemID";
    const contractItemID                      = "contractItemID";
    const contractResponseTime                = "contractResponseTime";
    const contractDescription                 = "contractDescription";
    const activityTypeCost                    = "activityTypeCost";
    const curValueFlag                        = "curValueFlag";
    const projectDescription                  = "projectDescription";
    const completedName                       = "completedName";
    const priority                            = "priority";
    const problemHideFromCustomerFlag         = "problemHideFromCustomerFlag";
    const problemStatus                       = "problemStatus";
    const requestAwaitingCustomerResponseFlag = "requestAwaitingCustomerResponseFlag";
    const completeDate                        = "completeDate";
    const alarmDate                           = "alarmDate";
    const alarmTime                           = "alarmTime";
    const rootCauseID                         = "rootCauseID";
    const rootCauseDescription                = "rootCauseDescription";
    const projectID                           = "projectID";
    const linkedSalesOrderID                  = "linkedSalesOrderID";
    const totalActivityDurationHours          = "totalActivityDurationHours";
    const criticalFlag                        = "criticalFlag";
    const hdLimitMinutes                      = "hdLimitMinutes";
    const esLimitMinutes                      = "esLimitMinutes";
    const imLimitMinutes                      = "imLimitMinutes";
    const hdPauseCount                        = "hdPauseCount";
    const allocatedUserID                     = "allocatedUserID";
    const queueNo                             = "queueNo";
    const caaConsno                           = "caa_consno";
    const assetName                           = "assetName";
    const assetTitle                          = "assetTitle";
    const emptyAssetReason                    = "emptyAssetReason";
    const emailSubjectSummary                 = "emailSubjectSummary";
    const prePayChargeApproved                = "prePayChargeApproved";
    const automateMachineID                   = "automateMachineID";
    var $fromString;

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
            self::travelFlag,
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
            DA_ID,
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
            self::emailSubjectSummary,
            DA_STRING,
            DA_ALLOW_NULL,
            "problem.emailSubjectSummary"
        );
        $this->addColumn(
            self::requestAwaitingCustomerResponseFlag,
            DA_STRING,
            DA_ALLOW_NULL,
            "problem.pro_awaiting_customer_response_flag"
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
            DA_TEXT,
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
        $this->addColumn(
            self::caaConsno,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "caa_consno"
        );
        $this->addColumn(
            self::assetName,
            DA_TEXT,
            DA_ALLOW_NULL,
            "problem.assetName"
        );
        $this->addColumn(
            self::assetTitle,
            DA_TEXT,
            DA_ALLOW_NULL,
            "problem.assetTitle"
        );
        $this->addColumn(
            self::emptyAssetReason,
            DA_TEXT,
            DA_ALLOW_NULL,
            "problem.emptyAssetReason"
        );
        $this->addColumn(
            self::prePayChargeApproved,
            DA_BOOLEAN,
            DA_NOT_NULL,
            "problem.prePayChargeApproved"
        );
        $this->addColumn(
            self::automateMachineID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "problem.automateMachineID"
        );
        
        $this->setAddColumnsOff();
        $this->fromString = $this->getTableName(
            ) . " LEFT JOIN " . " callacttype ON caa_callacttypeno = cat_callacttypeno" . " LEFT JOIN " . " item AS activity_type_item ON cat_itemno = activity_type_item.itm_itemno" . " LEFT JOIN " . " consultant ON consultant.cns_consno = caa_consno" . " LEFT JOIN " . " problem as problem ON problem.pro_problemno = callactivity.caa_problemno" . " LEFT JOIN " . " customer ON cus_custno = pro_custno" . " LEFT JOIN " . " address ON add_custno = pro_custno" . " AND add_siteno = caa_siteno" . " LEFT JOIN " . " custitem AS contract ON problem.pro_contract_cuino = contract.cui_cuino" . " LEFT JOIN " . " item AS contractitem ON contract.cui_itemno = contractitem.itm_itemno" . " LEFT JOIN " . " project ON problem.pro_projectno = project.projectID" . " LEFT JOIN " . " contact ON con_contno = caa_contno" . " LEFT JOIN " . " consultant as completed_user ON callactivity.caa_completed_consno = completed_user.cns_consno" . " LEFT JOIN " . " rootcause ON rootcause.rtc_rootcauseno = problem.pro_rootcauseno" . " LEFT JOIN  " . " team ON consultant.teamID = team.teamID";
    }

    function getRow($callActivityID = null)
    {
        if ($callActivityID) {
            $this->setPKValue($callActivityID);
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString(
            ) . " FROM " . $this->fromString . " WHERE " . $this->getPKWhere()
        );
        return (parent::getRow());
    }

    function getIncompleteRows()
    {
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString(
            ) . " FROM " . $this->fromString . " WHERE caa_completed_consno = 0" . " AND caa_date > '2007-02-01'" .                // ignore activities from old call system
            " AND caa_date <= NOW() AND callactivity.caa_problemno <> 0"                                // ignore future activities
        );
        return (parent::getRows());
    }

    function getRowsInIdArray($IDArray)
    {
        $IDs = "";
        foreach ($IDArray as $val):
            $IDs .= "," . $val;
        endforeach;
        $IDs   = substr(
            $IDs,
            1
        ); // trim comma
        $query = "SELECT " . $this->getDBColumnNamesAsString(
            ) . " FROM " . $this->fromString . " WHERE caa_callactivityno IN  (" . $IDs . ") AND callactivity.caa_problemno <> 0" . " ORDER BY caa_problemno, caa_consno";
        $this->setQueryString($query);
        return (parent::getRows());
    }

    function getRowsByDateRange($startDate,
                                $endDate
    )
    {
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString(
            ) . " FROM " . $this->fromString . " WHERE caa_date >= '" . mysqli_real_escape_string(
                $this->db->link_id(),
                $startDate
            ) . "'" . " AND caa_date <= '" . mysqli_real_escape_string(
                $this->db->link_id(),
                $endDate
            ) . "' AND callactivity.caa_problemno <> 0"
        );
        return (parent::getRows());
    }

    function getLastActionableActivityByProblemID($problemId)
    {
        $query = "SELECT " . $this->getDBColumnNamesAsString(
            ) . " FROM " . $this->fromString . " WHERE callactivity.caa_problemno = " . mysqli_real_escape_string(
                $this->db->link_id(),
                $problemId
            ) . " and caa_callacttypeno not in (59,60,61)";
        $query .= " ORDER BY caa_date desc, caa_starttime desc limit 1";
        $this->setQueryString($query);
        if (!$this->getRows()) {
            return false;
        }
        if (!$this->fetchNext()) {
            return false;
        }
        return $this;
    }

    /**
     * @param $problemID
     * @param bool $includeTravel
     * @param bool $includeOperationalTasks
     * @param bool $descendingDate
     * @param bool $fromDate
     * @param bool $includeServerGuardUpdates
     * @param null $activityType
     * @param null $activityStatus
     * @return bool
     */
    function getRowsByProblemID($problemID,
                                $includeTravel = false,
                                $includeOperationalTasks = true,
                                $descendingDate = false,
                                $fromDate = false,
                                $includeServerGuardUpdates = true,
                                $activityType = null,
                                $activityStatus = null
    )
    {

        $query = "SELECT " . $this->getDBColumnNamesAsString(
            ) . " FROM " . $this->fromString . " WHERE callactivity.caa_problemno = '" . mysqli_real_escape_string(
                $this->db->link_id(),
                $problemID
            ) . "' AND callactivity.caa_problemno is not null";
        if (!$includeTravel) {           // isnull in case this is an incomplete activity with no call activity set yet
            $query .= " AND ( travelFlag <> 'Y' OR ISNULL(travelFlag) )";
        }
        if (!$includeOperationalTasks) {
            $query .= " AND ( not caa_callacttypeno <=>  " . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . " )";
        }
        if (!$includeServerGuardUpdates) {           // isnull in case this is an incomplete activity with no call activity set yet
            $query .= " AND (not caa_callacttypeno <=> " . CONFIG_SERVER_GUARD_UPDATE_ACTIVITY_TYPE_ID . " )";
        }
        if ($fromDate) {
            $query .= " AND caa_date > DATE( '" . $fromDate . "')";
        }
        if ($activityType) {
            $query .= ' and caa_callacttypeno = ' . $activityType;
        }
        if ($activityStatus) {
            $query .= ' and caa_status = "' . $activityStatus . '"';
        }
        if ($descendingDate) {
            $query .= " ORDER BY caa_callacttypeno = 51 asc, caa_date DESC,caa_starttime DESC";
        } else {
            $query .= " ORDER BY caa_callacttypeno = 51 desc ,caa_date, caa_starttime";
        }
        $this->setQueryString($query);
        return (parent::getRows());
    }

    function countRowsByCustomerSiteNo($customerID,
                                       $siteNo
    )
    {
        $this->setQueryString(
            "SELECT COUNT(*) FROM " . $this->getTableName(
            ) . " JOIN problem ON pro_problemno = caa_problemno" . " WHERE pro_custno =" . $customerID . " AND " . $this->getDBColumnName(
                self::siteNo
            ) . "=" . $siteNo
        );
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $this->resetQueryString();
                return ($this->getDBColumnValue(0));
            }
        }
        return 0;
    }

    /**
     * @param bool $showHelpDesk
     * @param bool $showEscalations
     * @param bool $showSmallProjects
     * @param bool $showProjects
     * @param int $limit
     * @return bool
     */
    function getPendingChangeRequestRows($showHelpDesk = false,
                                         $showEscalations = false,
                                         $showSmallProjects = false,
                                         $showProjects = false,
                                         $limit = 0
    )
    {


        $query = "SELECT " . $this->getDBColumnNamesAsString(
            ) . " FROM " . $this->fromString . " WHERE callactivity.caa_status = 'O' and caa_callacttypeno = 59 ";
        if (!$showHelpDesk) {
            $query .= " and pro_queue_no <> 1 ";
        }
        if (!$showEscalations) {
            $query .= " and  pro_queue_no <> 2 ";
        }
        if (!$showSmallProjects) {
            $query .= " and pro_queue_no <> 3 ";
        }
        if (!$showProjects) {
            $query .= " and pro_queue_no <> 5 ";
        }
        if ($limit > 0) {
            $query .= " limit $limit";
        }
        $this->setQueryString($query);
        return (parent::getRows());
    }

    function getPendingTimeRequestRows($showHelpDesk = false,
                                       $showEscalation = false,
                                       $showSmallProjects = false,
                                       $showProjects = false,
                                       $limit = 0
    )
    {
        $query = "SELECT " . $this->getDBColumnNamesAsString(
            ) . " FROM " . $this->fromString . " WHERE callactivity.caa_status = 'O' and caa_callacttypeno = 61 ";
        if (!$showHelpDesk) {
            $query .= " and team.teamID <> 1 ";
        }
        if (!$showEscalation) {
            $query .= " and  team.teamID <> 2 ";
        }
        if (!$showSmallProjects) {
            $query .= " and team.teamID <> 4 ";
        }
        if (!$showProjects) {
            $query .= " and team.teamID <> 5 ";
        }
        if ($limit > 0) {
            $query .= " limit $limit";
        }
        $this->setQueryString($query);
        return (parent::getRows());
    }

    public function getPendingSalesRequestRows($showHelpDesk = false,
                                               $showEscalation = false,
                                               $showSmallProjects = false,
                                               $showProjects = false,
                                               $limit = 0
    )
    {
        $query = "SELECT
            customer.cus_name AS customerName,
            callactivity.`caa_problemno` AS problemID,
            problem.`pro_linked_ordno` AS linkedSalesOrderId, 
            callactivity.`reason` AS requestBody,
            consultant.cns_name AS requestedBy,
            CONCAT(callactivity.`caa_date`,' ',callactivity.`caa_starttime`,':00') AS requestedDateTime,
            callactivity.`caa_callactivityno` AS callActivityID,            
            standardtext.`stt_desc` AS `type`,
            problem.`salesRequestAssignedUserId`,
            standardtext.salesRequestDoNotNotifySalesOption
            FROM
            callactivity 
            LEFT JOIN standardtext ON callactivity.`requestType` = standardtext.`stt_standardtextno`
            LEFT JOIN problem ON callactivity.`caa_problemno` = problem.`pro_problemno`
            LEFT JOIN customer ON problem.`pro_custno` = customer.`cus_custno`
            LEFT JOIN consultant ON callactivity.`caa_consno` = consultant.cns_consno
            LEFT JOIN   team ON consultant.teamID = team.teamID
            WHERE callactivity.salesRequestStatus = 'O'
            AND caa_callacttypeno = 43 ";
        if (!$showHelpDesk) {
            $query .= " and team.teamID <> 1 ";
        }
        if (!$showEscalation) {
            $query .= " and  team.teamID <> 2 ";
        }
        if (!$showSmallProjects) {
            $query .= " and team.teamID <> 4 ";
        }
        if (!$showProjects) {
            $query .= " and team.teamID <> 5 ";
        }
        if ($limit > 0) {
            $query .= " limit $limit";
        }
        return DBConnect::fetchAll($query, []);
    }
}