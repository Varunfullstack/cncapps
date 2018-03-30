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



?>
