<?php /*
* Call activity problem table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBEProblem.inc.php");

class DBEJProblem extends DBEProblem
{
    const customerName = "customerName";
    const specialAttentionFlag = "specialAttentionFlag";
    const specialAttentionEndDate = "specialAttentionEndDate";
    const dateRaisedDMY = "dateRaisedDMY";
    const timeRaised = "timeRaised";
    const totalActivityHours = "totalActivityHours";
    const hoursElapsed = "hoursElapsed";
    const engineerName = "engineerName";
    const teamID = "teamID";
    const engineerLogname = "engineerLogname";
    const engineerInitials = "engineerInitials";
    const slaDueHours = "slaDueHours";
    const callActivityID = "callActivityID";
    const serverGuard = "serverGuard";
    const reason = "reason";
    const lastCallActivityID = "lastCallActivityID";
    const lastServerGuard = "lastServerGuard";
    const lastReason = "lastReason";
    const lastCallActTypeID = "lastCallActTypeID";
    const lastStartTime = "lastStartTime";
    const lastUserID = "lastUserID";
    const lastDate = "lastDate";
    const lastAwaitingCustomerResponseFlag = "lastAwaitingCustomerResponseFlag";
    const dashboardSortColumn = "dashboardSortColumn";
    const hoursRemaining = 'hoursRemaining';


    /**
     * calls constructor()
     * @access public
     *
     * @param $owner
     * @param bool $pkID
     * @internal param $void
     * @see constructor()
     */
    function __construct(&$owner,
                         $pkID = false
    )
    {
        parent::__construct(
            $owner,
            $pkID
        );
        $this->setAddColumnsOn();
        $this->addColumn(
            self::customerName,
            DA_STRING,
            DA_ALLOW_NULL,
            "cus_name"
        );
        $this->addColumn(
            self::specialAttentionFlag,
            DA_STRING,
            DA_ALLOW_NULL,
            "cus_special_attention_flag"
        );
        $this->addColumn(
            self::specialAttentionEndDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "cus_special_attention_end_date"
        );
        $this->addColumn(
            self::dateRaisedDMY,
            DA_STRING,
            DA_ALLOW_NULL,
            "DATE_FORMAT(pro_date_raised, '%d/%m/%Y')"
        );
        $this->addColumn(
            self::timeRaised,
            DA_STRING,
            DA_ALLOW_NULL,
            "TIME_FORMAT(pro_date_raised, '%H:%i')"
        );
        $this->addColumn(
            self::totalActivityHours,
            DA_STRING,
            DA_ALLOW_NULL,
            "pro_total_activity_duration_hours"
        );
        $this->addColumn(
            self::hoursElapsed,
            DA_STRING,
            DA_ALLOW_NULL,
            "TIME_FORMAT(TIMEDIFF(NOW(), pro_date_raised), '%H:%i')"
        );
        $this->addColumn(
            self::engineerName,
            DA_STRING,
            DA_ALLOW_NULL,
            "CONCAT(consultant.firstName,' ',consultant.lastName)"
        );
        $this->addColumn(
            self::teamID,
            DA_ID,
            DA_ALLOW_NULL,
            "consultant.teamID"
        );
        $this->addColumn(
            self::engineerLogname,
            DA_STRING,
            DA_ALLOW_NULL,
            "consultant.cns_logname"
        );
        $this->addColumn(
            self::engineerInitials,
            DA_STRING,
            DA_ALLOW_NULL,
            "CONCAT(SUBSTRING(consultant.firstName, 1, 1) ,SUBSTRING(consultant.lastName, 1, 1 ) )"
        );
        $this->addColumn(
            self::slaDueHours,
            DA_DATETIME,
            DA_ALLOW_NULL,
            "TIME_FORMAT( TIMEDIFF( pro_working_hours, pro_sla_response_hours ), '%H:%i')"
        );
        $this->addColumn(
            self::callActivityID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            'initial.caa_callactivityno'
        );
        $this->addColumn(
            self::serverGuard,
            DA_YN,
            DA_ALLOW_NULL,
            'initial.caa_serverguard'
        );
        $this->addColumn(
            self::reason,
            DA_STRING,
            DA_ALLOW_NULL,
            'initial.reason'
        );
        $this->addColumn(
            self::lastCallActivityID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            'last.caa_callactivityno'
        );
        $this->addColumn(
            self::lastServerGuard,
            DA_YN,
            DA_ALLOW_NULL,
            'last.caa_serverguard'
        );
        $this->addColumn(
            self::lastReason,
            DA_STRING,
            DA_ALLOW_NULL,
            'last.reason'
        );
        $this->addColumn(
            self::lastCallActTypeID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "last.caa_callacttypeno"
        );
        $this->addColumn(
            self::lastStartTime,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "last.caa_starttime"
        );
        $this->addColumn(
            self::lastUserID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "last.caa_consno"
        );
        $this->addColumn(
            self::lastDate,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "last.caa_date"
        );
        $this->addColumn(
            self::lastAwaitingCustomerResponseFlag,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "last.caa_awaiting_customer_response_flag"
        );
        $this->addColumn(
            self::dashboardSortColumn,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "pro_sla_response_hours - pro_working_hours "
        );
        $this->addColumn(
            self::hoursRemaining,
            DA_FLOAT,
            DA_ALLOW_NULL,
            'pro_working_hours - pro_sla_response_hours'
        );

        $this->setAddColumnsOff();
        $this->setPK(0);
    }

    /**
     * put your comment there...
     *
     * @param mixed $status
     * @param bool $includeAutomaticallyFixed
     * @return bool
     * @internal param mixed $future TRUE= ONLY return future alarmed requests
     */
    function getRowsByStatus($status = false,
                             $includeAutomaticallyFixed = false
    )
    {
        $sql =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN customer ON cus_custno = pro_custno
           LEFT JOIN consultant ON cns_consno = pro_consno

          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID .

            " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              AND ca.caa_callacttypeno <> " . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . "
            )
        WHERE 1=1";
        if ($status) {
            $sql .=
                " AND pro_status = '" . $status . "'";
        }
        /* Exclude future dated */
        $sql .= " AND CONCAT( pro_alarm_date, ' ', pro_alarm_time ) <= NOW()";

        if ($status == 'F' && !$includeAutomaticallyFixed) {
            $sql .= " AND last.caa_consno <> " . USER_SYSTEM;
        }

        $sql .= " ORDER BY pro_alarm_date, pro_alarm_time";

        $this->setQueryString($sql);

        return (parent::getRows());
    }

    function getFutureRows()
    {
        $sql =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN customer ON cus_custno = pro_custno
           LEFT JOIN consultant ON cns_consno = pro_consno

          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID .

            " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              AND ca.caa_callacttypeno <> " . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . "
            )
        
        WHERE pro_status IN ( 'I', 'P' )
        
          AND CONCAT( pro_alarm_date, ' ', pro_alarm_time )  > NOW()
      
      ORDER BY pro_alarm_date, pro_alarm_time";

        $this->setQueryString($sql);

        return (parent::getRows());
    }

    /*
    Get Awaiting and In-progress SRs by Queue

    */
    function getRowsByQueueNo($queueNo,
                              $unassignedOnly = false
    )
    {
        $sql =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN customer ON cus_custno = pro_custno
           LEFT JOIN consultant ON cns_consno = pro_consno

          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID .

            " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              AND ca.caa_callacttypeno <> " . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . "
            ) 

        WHERE pro_status IN( 'I', 'P' )

          AND pro_queue_no = $queueNo
        
          AND CONCAT( pro_alarm_date , ' ' , pro_alarm_time ) <= NOW()";

        if ($unassignedOnly) {
            $sql .= " AND pro_consno = 0";

        } else {
            $sql .= " AND pro_consno <> 0";
        }

        $this->setQueryString($sql);

        return (parent::getRows());
    }

    function getRow($pkID)
    {
        $this->setPKValue($pkID);

        $sql =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN customer ON cus_custno = pro_custno
           LEFT JOIN consultant ON cns_consno = pro_consno

          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID .

            " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              AND ca.caa_callacttypeno <> " . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . "
            ) 
            
        WHERE " . $this->getPKWhere();

        $this->setQueryString($sql);

        return (parent::getRow());
    }

    /**
     * @param mixed $customerID
     * @return bool
     */
    function getActiveProblemsByCustomer($customerID)
    {
        $sql =
            "SELECT DISTINCT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN customer ON cus_custno = pro_custno
          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID .

            " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              AND ca.caa_callacttypeno <> " . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . "
            ) 
           LEFT JOIN consultant ON cns_consno = pro_consno
        WHERE
          pro_custno = $customerID
          AND pro_status <> 'C'";

        $sql .= " ORDER BY pro_date_raised DESC";              // in progress

        $this->setQueryString($sql);

        return (parent::getRows());
    }

    function getProblemsByContactID($contactID)
    {
        $sql =
            "SELECT DISTINCT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN customer ON cus_custno = pro_custno
          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID .

            " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              AND ca.caa_callacttypeno <> " . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . "
            ) 
           LEFT JOIN consultant ON cns_consno = pro_consno
        WHERE
          initial.caa_contno = " . $contactID . " and pro_date_raised >= date(now() - interval 3 month) 
         ";

        $sql .= " ORDER BY pro_date_raised DESC";              // in progress
        $this->setQueryString($sql);

        return (parent::getRows());
    }

    public function getP1byCustomerIdLast30Days($customerID)
    {
        $sql =
            "SELECT DISTINCT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN customer ON cus_custno = pro_custno
          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID .

            " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              AND ca.caa_callacttypeno <> " . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . "
            ) 
           LEFT JOIN consultant ON cns_consno = pro_consno
        WHERE
          pro_custno = $customerID" .
            " AND cast(" . $this->getDBColumnName(
                self::dateRaised
            ) . " as date)  BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()" .
            " and " . $this->getDBColumnName(self::hideFromCustomerFlag) . " <> 'Y'" .
            " and " . $this->getDBColumnName(self::priority) . " = 1";

        $sql .= " ORDER BY pro_date_raised DESC";

        $this->setQueryString($sql);

        return parent::getRows();
    }

    public function getStartersSRByCustomerIDLast12Months($customerID)
    {
        $sql =
            "SELECT DISTINCT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN customer ON cus_custno = pro_custno
          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID .

            " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              AND ca.caa_callacttypeno <> " . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . "
            ) 
           LEFT JOIN consultant ON cns_consno = pro_consno
        WHERE
          pro_custno = $customerID" .
            " AND cast(" . $this->getDBColumnName(
                self::dateRaised
            ) . " as date) BETWEEN CURDATE() - INTERVAL 12 month AND CURDATE()" .
            " and " . $this->getDBColumnName(self::hideFromCustomerFlag) . " <> 'Y'" .
            " and " . $this->getDBColumnName(self::rootCauseID) . " = 58";

        $sql .= " ORDER BY pro_date_raised DESC";
        $this->setQueryString($sql);

        return parent::getRows();
    }

    public function getStartersSRByCustomerIDInDateRange($customerID,
                                                         DateTimeInterface $startDate,
                                                         DateTimeInterface $endDate
    )
    {
        $sql =
            "SELECT DISTINCT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN customer ON cus_custno = pro_custno
          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID .

            " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              AND ca.caa_callacttypeno <> " . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . "
            ) 
           LEFT JOIN consultant ON cns_consno = pro_consno
        WHERE
          pro_custno = $customerID" .
            " AND cast(" . $this->getDBColumnName(self::dateRaised) . " as date) BETWEEN '" .
            $startDate->format('Y-m-d') . "' AND  '" . $endDate->format('Y-m-d')
            . "' " .
            " and " . $this->getDBColumnName(self::hideFromCustomerFlag) . " <> 'Y'" .
            " and " . $this->getDBColumnName(self::rootCauseID) . " = 58";

        $sql .= " ORDER BY pro_date_raised DESC";
        $this->setQueryString($sql);

        return parent::getRows();
    }

    public function getLeaversSRByCustomerIDLast12Months($customerID)
    {
        $sql =
            "SELECT DISTINCT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN customer ON cus_custno = pro_custno
          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID .

            " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              AND ca.caa_callacttypeno <> " . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . "
            ) 
           LEFT JOIN consultant ON cns_consno = pro_consno
        WHERE
          pro_custno = $customerID" .
            " AND cast(" . $this->getDBColumnName(
                self::dateRaised
            ) . " as date) BETWEEN CURDATE() - INTERVAL 12 month AND CURDATE()" .
            " and " . $this->getDBColumnName(self::hideFromCustomerFlag) . " <> 'Y'" .
            " and " . $this->getDBColumnName(self::rootCauseID) . " = 62";

        $sql .= " ORDER BY pro_date_raised DESC";

        $this->setQueryString($sql);

        return parent::getRows();
    }

    public function getLeaversSRByCustomerIDInDateRange($customerID,
                                                        DateTimeInterface $startDate,
                                                        DateTimeInterface $endDate
    )
    {
        $sql =
            "SELECT DISTINCT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN customer ON cus_custno = pro_custno
          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID .

            " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              AND ca.caa_callacttypeno <> " . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . "
            ) 
           LEFT JOIN consultant ON cns_consno = pro_consno
        WHERE
          pro_custno = $customerID" .
            " AND cast(" . $this->getDBColumnName(self::dateRaised) . " as date) BETWEEN '" .
            $startDate->format('Y-m-d')
            . "' AND  '" .
            $endDate->format('Y-m-d')
            . "' " .
            " and " . $this->getDBColumnName(self::hideFromCustomerFlag) . " <> 'Y'" .
            " and " . $this->getDBColumnName(self::rootCauseID) . " = 62";

        $sql .= " ORDER BY pro_date_raised DESC";

        $this->setQueryString($sql);

        return parent::getRows();
    }

    public function getDashBoardRows($limit = 10,
                                     $orderBy = 'shortestSLARemaining',
                                     $isP5 = false,
                                     $showHelpDesk = true,
                                     $showEscalation = true,
                                     $showImplementation = true
    )
    {
        $sql =
            "SELECT " . $this->getDBColumnNamesAsString() . ', ' . $this->getDBColumnName(
                self::workingHours
            ) . ' - ' . $this->getDBColumnName(self::slaResponseHours) . ' as hoursRemaining' .
            " FROM " . $this->getTableName() .
            " LEFT JOIN customer ON cus_custno = pro_custno
           LEFT JOIN consultant ON cns_consno = pro_consno

          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID .
            " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              AND ca.caa_callacttypeno <> " . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . "
            ) 
            
        WHERE " . $this->getDBColumnName(self::customerID) . ' <> 282  and ' . $this->getDBColumnName(
                self::status
            ) . ' in ("I","P") ';

        if (!$showHelpDesk) {
            $sql .= ' and pro_queue_no <> 1 ';
        }

        if (!$showEscalation) {
            $sql .= ' and pro_queue_no <> 2 ';
        }

        if (!$showImplementation) {
            $sql .= ' and pro_queue_no <> 3 ';
        }

        if ($isP5) {
            $sql .= 'and ' . $this->getDBColumnName(
                    self::priority
                ) . ' = 5 ';
        } else {
            $sql .= 'and ' . $this->getDBColumnName(
                    self::priority
                ) . ' <= 4 and ' . $this->getDBColumnName(
                    self::priority
                ) . ' > 0 ';
        }

        switch ($orderBy) {
            case 'shortestSLARemaining':
                {

                    $sql .= ' and ' . $this->getDBColumnName(
                            self::status
                        ) . ' = "I" order by hoursRemaining desc';
                    break;
                }

            case 'oldestUpdatedSR':
                {
                    $sql .= ' order by last.caa_date asc, last.caa_starttime desc';
                    break;
                }
            case 'mostHoursLogged':
                {
                    $sql .= ' order by ' . $this->getDBColumnName(self::totalActivityDurationHours) . ' desc';
                    break;
                }
            case 'longestOpenSR':
                {
                    $sql .= ' order by hoursRemaining desc';
                    break;
                }
        }

        $sql .= ' limit ' . $limit;

        $this->setQueryString($sql);

        return (parent::getRow());
    }

    public function getDashBoardEngineersInSRRows($engineersMaxCount = 3,
                                                  $pastHours = 24,
                                                  $limit = 5,
                                                  $isP5 = false,
                                                  $showHelpDesk = true,
                                                  $showEscalation = true,
                                                  $showImplementation = true
    )
    {
        $sql =
            "SELECT " . $this->getDBColumnNamesAsString() . ', ' . $this->getDBColumnName(
                self::workingHours
            ) . ' - ' . $this->getDBColumnName(self::slaResponseHours) . ' as hoursRemaining' .
            " FROM " . $this->getTableName() .
            " LEFT JOIN customer ON cus_custno = pro_custno
           LEFT JOIN consultant ON cns_consno = pro_consno

          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID .
            " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              AND ca.caa_callacttypeno <> " . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . "
            ) 
            
        WHERE " . $this->getDBColumnName(self::customerID) . ' <> 282  and ' . $this->getDBColumnName(
                self::status
            ) . " in ('I','P') ";

        if (!$showHelpDesk) {
            $sql .= ' and pro_queue_no <> 1 ';
        }

        if (!$showEscalation) {
            $sql .= ' and pro_queue_no <> 2 ';
        }

        if (!$showImplementation) {
            $sql .= ' and pro_queue_no <> 3 ';
        }


        if ($isP5) {
            $sql .= ' and ' . $this->getDBColumnName(
                    self::priority
                ) . ' = 5 ';
        } else {
            $sql .= ' and ' . $this->getDBColumnName(
                    self::priority
                ) . ' <= 4 and ' . $this->getDBColumnName(
                    self::priority
                ) . ' > 0 ';
        }


        $sql .= " and pro_problemno IN 
  (SELECT 
    test.pro_problemno 
  FROM
    (SELECT 
      pro_problemno,
      SUM(1) AS engineers 
    FROM
      (SELECT DISTINCT 
        caa_consno,
        pro_problemno 
      FROM
        problem 
        JOIN callactivity 
          ON problem.`pro_problemno` = caa_problemno 
          AND caa_callacttypeno IN (18, 8) 
      WHERE pro_status IN ('I', 'P') 
        AND caa_date > DATE(
          DATE_SUB(CURRENT_DATE, INTERVAL $pastHours HOUR)
        ) 
        OR (
          caa_date = DATE(
            DATE_SUB(CURRENT_DATE, INTERVAL $pastHours HOUR)
          ) 
          AND caa_starttime >= DATE_FORMAT(
            DATE_SUB(CURRENT_DATE, INTERVAL $pastHours HOUR),
            ' % H:%i'
          )
        )) engineersProblems 
    GROUP BY pro_problemno 
    ORDER BY engineers DESC) test 
  WHERE test.engineers >= $engineersMaxCount) ";

        $sql .= " limit $limit";
        $this->setQueryString($sql);

        return (parent::getRow());

    }
}

?>
