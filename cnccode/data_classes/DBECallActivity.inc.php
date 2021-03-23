<?php
/*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBECallActivity extends DBEntity
{

    const callActivityID                = "callActivityID";
    const siteNo                        = "siteNo";
    const contactID                     = "contactID";
    const sequenceNo                    = "sequenceNo";
    const callActTypeID                 = "callActTypeID";
    const problemID                     = "problemID";
    const date                          = "date";
    const startTime                     = "startTime";
    const endTime                       = "endTime";
    const status                        = "status";
    const expenseExportFlag             = "expenseExportFlag";
    const reason                        = "reason";
    const curValue                      = "curValue";
    const statementYearMonth            = "statementYearMonth";
    const customerItemID                = "customerItemID";
    const underContractFlag             = "underContractFlag";
    const authorisedFlag                = "authorisedFlag";
    const userID                        = "userID";
    const serverGuard                   = "serverGuard";
    const parentCallActivityID          = "parentCallActivityID";
    const awaitingCustomerResponseFlag  = "awaitingCustomerResponseFlag";
    const loggingErrorFlag              = "loggingErrorFlag";
    const escalationAcceptedFlag        = "escalationAcceptedFlag";
    const hideFromCustomerFlag          = "hideFromCustomerFlag";
    const secondsiteErrorServer         = "secondsiteErrorServer";
    const secondsiteErrorCustomerItemID = "secondsiteErrorCustomerItemID";
    const salesRequestStatus            = 'salesRequestStatus';
    const overtimeApprovedDate          = "overtimeApprovedDate";
    const overtimeApprovedBy            = "overtimeApprovedBy";
    const overtimeDeniedReason          = "overtimeDeniedReason";
    const overtimeExportedFlag          = 'overtimeExportedFlag';
    const isSalesRequestSR              = 'isSalesRequestSR';
    const requestType                   = 'requestType';
    const submitAsOvertime              = "submitAsOvertime";
    const overtimeDurationApproved      = "overtimeDurationApproved";
    const customerSummary               = "customerNotes";
    const cncNextAction                 = "cncNextAction";

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
            DA_ALLOW_NULL,
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
            self::problemID,
            DA_ID,
            DA_ALLOW_NULL,
            'callactivity.caa_problemno'
        );
        $this->addColumn(
            self::date,
            DA_DATE,
            DA_NOT_NULL,
            "caa_date"
        );
        $this->addColumn(
            self::startTime,
            DA_TIME,
            DA_NOT_NULL,
            "caa_starttime"
        );
        $this->addColumn(
            self::endTime,
            DA_TIME,
            DA_ALLOW_NULL,
            "caa_endtime"
        );
        /**
         * Status can be one of
         * A => Authorized
         * C => Checked
         * O => Open
         * F => ?
         */
        $this->addColumn(
            self::status,
            DA_STRING,
            DA_NOT_NULL,
            "caa_status"
        );
        $this->addColumn(
            self::expenseExportFlag,
            DA_YN,
            DA_NOT_NULL,
            "caa_expexport_flag"
        );
        $this->addColumn(
            self::reason,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::curValue,
            DA_FLOAT,
            DA_NOT_NULL,
            null,
            '0.00'
        );
        $this->addColumn(
            self::statementYearMonth,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::customerItemID,
            DA_ID,
            DA_ALLOW_NULL,
            "caa_cuino"
        );    // Customer Item
        $this->addColumn(
            self::underContractFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "caa_under_contract"
        );
        $this->addColumn(
            self::authorisedFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "caa_authorised"
        );
        $this->addColumn(
            self::userID,
            DA_ID,
            DA_NOT_NULL,
            "caa_consno"
        );
        $this->addColumn(
            self::serverGuard,
            DA_YN,
            DA_ALLOW_NULL,
            "caa_serverguard"
        );
        $this->addColumn(
            self::parentCallActivityID,
            DA_ID,
            DA_ALLOW_NULL,
            "caa_parent_callactivityno"
        );
        $this->addColumn(
            self::awaitingCustomerResponseFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "caa_awaiting_customer_response_flag"
        );
        $this->addColumn(
            self::loggingErrorFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "caa_logging_error_flag"
        );
        $this->addColumn(
            self::escalationAcceptedFlag,
            DA_YN,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::hideFromCustomerFlag,
            DA_YN,
            DA_ALLOW_NULL,
            'caa_hide_from_customer_flag'
        );
        $this->addColumn(
            self::secondsiteErrorServer,
            DA_STRING,
            DA_ALLOW_NULL,
            'caa_secondsite_error_server'
        );
        $this->addColumn(
            self::secondsiteErrorCustomerItemID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            'caa_secondsite_error_cuino'
        );
        $this->addColumn(
            self::salesRequestStatus,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(self::overtimeApprovedDate, DA_DATETIME, DA_ALLOW_NULL);
        $this->addColumn(self::overtimeApprovedBy, DA_ID, DA_ALLOW_NULL);
        $this->addColumn(self::overtimeDeniedReason, DA_STRING, DA_ALLOW_NULL);
        $this->addColumn(self::overtimeExportedFlag, DA_YN, DA_NOT_NULL, 'caa_ot_exp_flag', 'N');
        $this->addColumn(
            self::isSalesRequestSR,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            0
        );
        $this->addColumn(
            self::requestType,
            DA_ID,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::submitAsOvertime,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            0
        );
        $this->addColumn(
            self::overtimeDurationApproved,
            DA_FLOAT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::customerSummary,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::cncNextAction,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
        $this->db->connect();
    }

    function changeProblemID($fromProblemID,
                             $toProblemID
    )
    {

        $query      = "UPDATE " . $this->getTableName() . " SET " . $this->getDBColumnName(
                self::problemID
            ) . " = ? " . " WHERE " . $this->getDBColumnName(self::problemID) . " = ?";
        $parameters = [
            [
                'type'  => 'i',
                'value' => $toProblemID
            ],
            [
                'type'  => 'i',
                'value' => $fromProblemID
            ],
        ];
        return $this->db->prepareQuery(
            $query,
            $parameters
        );

    }


    function countRowsByCustomerSiteNo($customerID,
                                       $siteNo
    )
    {
        $this->setQueryString(
            "SELECT COUNT(*) FROM " . $this->getTableName() . " JOIN problem ON pro_problemno = caa_problemno 
			  WHERE pro_custno =" . $customerID . " AND " . $this->getDBColumnName(self::siteNo) . "=" . $siteNo
        );
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $this->resetQueryString();
                return ($this->getDBColumnValue(0));
            }
        }
        return 0;
    }

    function countTravelRowsForTodayByCustomerSiteNoEngineer($customerID,
                                                             $siteNo,
                                                             $userID,
                                                             $date
    )
    {
        $this->setQueryString(
            "SELECT COUNT(*) FROM " . $this->getTableName(
            ) . " JOIN problem ON pro_problemno = caa_problemno " . " WHERE pro_custno =" . $customerID . " AND " . $this->getDBColumnName(
                self::siteNo
            ) . "=" . $siteNo . " AND " . $this->getDBColumnName(
                self::callActTypeID
            ) . "=" . CONFIG_TRAVEL_ACTIVITY_TYPE_ID . " AND " . $this->getDBColumnName(
                self::date
            ) . "= '" . $date . "'" . " AND " . $this->getDBColumnName(self::userID) . "=" . $userID
        );
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $this->resetQueryString();
                return ($this->getDBColumnValue(0));
            }
        }
        return 0;
    }

    function countEngineerRowsByProblem($problemID)
    {
        $this->setQueryString(
            "SELECT COUNT(*) FROM " . $this->getTableName(
            ) . " WHERE caa_problemno =" . $problemID . " AND " . $this->getDBColumnName(
                self::userID
            ) . "<>" . USER_SYSTEM . " AND " . $this->getDBColumnName(
                self::callActTypeID
            ) . "<>" . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID
        );
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $this->resetQueryString();
                return ($this->getDBColumnValue(0));
            }
        }
        return 0;
    }

    function countSendEmailActivities($problemID)
    {
        $this->setQueryString(
            "SELECT COUNT(*) FROM " . $this->getTableName(
            ) . " JOIN callacttype ON cat_callacttypeno = caa_callacttypeno" . " WHERE caa_problemno =" . $problemID . " AND customerEmailFlag = 'Y'"
        );
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $this->resetQueryString();
                return ($this->getDBColumnValue(0));
            }
        }
        return 0;
    }

    function setAllActivitiesToAuthorisedByProblemID($problemID)
    {
        $this->setQueryString(
            "UPDATE
        callactivity
       SET
        caa_status = 'A'
       WHERE
        caa_problemno = $problemID "
        );
        $this->runQuery();
    }

    public function getUnapprovedOvertime()
    {
        $this->queryString = "SELECT
  " . $this->getDBColumnNamesAsString() . "
FROM
  " . $this->getTableName() . "
  LEFT JOIN consultant
    ON callactivity.`caa_consno` = consultant.`cns_consno`
    left join callacttype on callacttype.cat_callacttypeno = callactivity.caa_callacttypeno
      JOIN headert
    ON headert.`headerID` = 1
  where " . $this->getDBColumnName(
                self::overtimeApprovedDate
            ) . " is null and " . $this->getDBColumnName(
                self::overtimeDeniedReason
            ) . " is null AND " . $this->getDBColumnName(
                self::overtimeExportedFlag
            ) . " <> 'Y'
            and consultant.autoApproveExpenses
           and caa_endtime AND caa_endtime IS NOT NULL AND
      (caa_status = 'C'
    OR caa_status = 'A')
  and submitAsOvertime
  AND (
    (
      caa_callacttypeno = 22
      AND (
        isBankHoliday (caa_date)
        or
        WEEKDAY(caa_date) IN (5,6)
        OR
        caa_starttime < overtimeStartTime 
        OR `caa_endtime` > `overtimeEndTime`
        )
    )
    OR caa_callacttypeno <> 22
  )
  AND (caa_endtime <> caa_starttime)
  AND callacttype.engineerOvertimeFlag = 'Y'";
        return $this->getRows();
    }
}