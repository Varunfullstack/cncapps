<?php
global $cfg;
require_once($cfg["path_dbe"] . "/DBEProblem.inc.php");

class DBEJProblem extends DBEProblem
{
    const customerName                     = "customerName";
    const specialAttentionFlag             = "specialAttentionFlag";
    const specialAttentionEndDate          = "specialAttentionEndDate";
    const dateRaisedDMY                    = "dateRaisedDMY";
    const timeRaised                       = "timeRaised";
    const totalActivityHours               = "totalActivityHours";
    const hoursElapsed                     = "hoursElapsed";
    const engineerName                     = "engineerName";
    const teamID                           = "teamID";
    const engineerLogname                  = "engineerLogname";
    const engineerInitials                 = "engineerInitials";
    const slaDueHours                      = "slaDueHours";
    const callActivityID                   = "callActivityID";
    const serverGuard                      = "serverGuard";
    const reason                           = "reason";
    const lastCallActivityID               = "lastCallActivityID";
    const lastServerGuard                  = "lastServerGuard";
    const lastReason                       = "lastReason";
    const lastCallActTypeID                = "lastCallActTypeID";
    const lastStartTime                    = "lastStartTime";
    const lastUserID                       = "lastUserID";
    const lastDate                         = "lastDate";
    const lastAwaitingCustomerResponseFlag = "lastAwaitingCustomerResponseFlag";
    const dashboardSortColumn              = "dashboardSortColumn";
    const hoursRemainingForSLA             = 'hoursRemaining';
    const specialAttentionContactFlag      = "specialAttentionContactFlag";
    const referredFlag                     = "referredFlag";
    const lastEndTime                      = "lastEndTime";
    const alarmDateTime                    = "alarmDateTime";
    const QUEUE_TEAM_ID                    = 'queueTeamId';
    const FIXED_DATE                       = "fixedDate";
    const ENGINEER_FIXED_NAME              = 'engineerFixedName';
    const FIXED_TEAM_ID                    = 'fixedTeamId';
    const IS_FIX_SLA_BREACHED              = 'isFixSLABreached';
    const contactName                      = 'contactName';
    const contactID                        = 'contactID';
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
            DA_STRING,
            DA_ALLOW_NULL,
            "last.caa_starttime"
        );
        $this->addColumn(
            self::lastEndTime,
            DA_STRING,
            DA_ALLOW_NULL,
            "last.caa_endtime"
        );
        $this->addColumn(
            self::lastUserID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "last.caa_consno"
        );
        $this->addColumn(
            self::lastDate,
            DA_DATE,
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
            self::hoursRemainingForSLA,
            DA_FLOAT,
            DA_ALLOW_NULL,
            'pro_working_hours - pro_sla_response_hours'
        );
        $this->addColumn(
            self::specialAttentionContactFlag,
            DA_YN_FLAG,
            DA_ALLOW_NULL,
            '(select contact.specialAttentionContactFlag from contact where con_contno = initial.caa_contno)'
        );
        $this->addColumn(
            self::referredFlag,
            DA_STRING,
            DA_ALLOW_NULL,
            'customer.cus_referred'
        );
        $this->addColumn(
            self::alarmDateTime,
            DA_DATETIME,
            DA_ALLOW_NULL,
            "            CONCAT(
                pro_alarm_date,
                ' ',
                COALESCE(CONCAT(pro_alarm_time,':00'), '00:00:00')
            )"
        );
        $this->addColumn(
            self::FIXED_DATE,
            DA_STRING,
            DA_ALLOW_NULL,
            "fixed.caa_date"
        );
        $this->addColumn(
            self::ENGINEER_FIXED_NAME,
            DA_STRING,
            DA_ALLOW_NULL,
            "fixedEngineer.cns_name"
        );
        $this->addColumn(
            self::FIXED_TEAM_ID,
            DA_STRING,
            DA_ALLOW_NULL,
            'fixedTeam.teamID'
        );
        $this->addColumn(
            self::QUEUE_TEAM_ID,
            DA_STRING,
            DA_ALLOW_NULL,
            'queueTeam.teamID'
        );
        $this->addColumn(
            self::IS_FIX_SLA_BREACHED,
            DA_BOOLEAN,
            DA_ALLOW_NULL,
            'CASE 
	WHEN `pro_priority` = 1 THEN customer.`slaP1PenaltiesAgreed` && customer.`slaFixHoursP1` - problem.pro_working_hours <= 0
	WHEN `pro_priority` = 2 THEN customer.`slaP2PenaltiesAgreed` && customer.`slaFixHoursP2` - problem.pro_working_hours <= 0
	WHEN `pro_priority` = 3 THEN customer.`slaP3PenaltiesAgreed` && customer.`slaFixHoursP3` - problem.pro_working_hours <= 0
	else 0
	END'
        );
        $this->addColumn(
          self::contactName,
          DA_STRING,
          DA_ALLOW_NULL,
          "(select  concat(con_first_name,' ',con_last_name) contactName from contact where con_contno = initial.caa_contno) contactName"
        );
        $this->addColumn(
          self::contactID,
          DA_STRING,
          DA_ALLOW_NULL,
          "(select  con_contno  from contact where con_contno = initial.caa_contno) contactID"
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
        $sql = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " LEFT JOIN customer ON cus_custno = pro_custno
           LEFT JOIN consultant ON cns_consno = pro_consno

          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID . " 
          
            JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              AND ca.caa_callacttypeno <> " . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . "
            )
            left join callactivity fixed 
            on fixed.caa_problemno = pro_problemno and fixed.caa_callacttypeno = " . CONFIG_FIXED_ACTIVITY_TYPE_ID . " 
            left join consultant fixedEngineer on fixed.caa_consno = fixedEngineer.cns_consno
            left join team fixedTeam on fixedEngineer.teamID = fixedTeam.teamID 
            left join team queueTeam on queueTeam.level = pro_queue_no
        WHERE 1=1";
        if ($status) {
            $sql .= " AND pro_status = '" . $status . "'";
        }
        /* Exclude future dated */
        $sql .= " AND (pro_alarm_date is null or CONCAT( pro_alarm_date, ' ', pro_alarm_time ) <= NOW())";
        if ($status == 'F' && !$includeAutomaticallyFixed) {
            $sql .= " AND last.caa_consno <> " . USER_SYSTEM;
        }
        $sql .= " ORDER BY pro_alarm_date, pro_alarm_time";
        $this->setQueryString($sql);
        return (parent::getRows());
    }

    function getFutureRows()
    {
        $sql = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " LEFT JOIN customer ON cus_custno = pro_custno
           LEFT JOIN consultant ON cns_consno = pro_consno

          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID . " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              AND ca.caa_callacttypeno <> " . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . "
            )
            left join callactivity fixed 
            on fixed.caa_problemno = pro_problemno and fixed.caa_callacttypeno = " . CONFIG_FIXED_ACTIVITY_TYPE_ID . " 
            left join consultant fixedEngineer on fixed.caa_consno = fixedEngineer.cns_consno
            left join team fixedTeam on fixedEngineer.teamID = fixedTeam.teamID 
            left join team queueTeam on queueTeam.level = pro_queue_no
        WHERE pro_status IN ( 'I', 'P' )
          AND CONCAT( pro_alarm_date, ' ', coalesce(pro_alarm_time, '00:00:00') )  > NOW()
      ORDER BY pro_alarm_date, pro_alarm_time";
        $this->setQueryString($sql);
        return (parent::getRows());
    }

    function getSLAWarningRows()
    {
        $sql = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " LEFT JOIN customer ON cus_custno = pro_custno
           LEFT JOIN consultant ON cns_consno = pro_consno
           
          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID . " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              AND ca.caa_callacttypeno <> " . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . "
            )
            left join callactivity fixed 
            on fixed.caa_problemno = pro_problemno and fixed.caa_callacttypeno = " . CONFIG_FIXED_ACTIVITY_TYPE_ID . " 
            left join consultant fixedEngineer on fixed.caa_consno = fixedEngineer.cns_consno
            left join team fixedTeam on fixedEngineer.teamID = fixedTeam.teamID 
            left join team queueTeam on queueTeam.level = pro_queue_no
        WHERE pro_status IN ( 'I', 'P' )
          AND (
             CASE problem.`pro_priority`
            WHEN 1 THEN customer.slaFixHoursP1
            WHEN 2 THEN customer.slaFixHoursP2
            WHEN 3 THEN customer.slaFixHoursP3
            WHEN 4 THEN customer.slaFixHoursP4
            end - pro_working_hours <= (select fixSLABreachWarningHours from headert where headerID = 1)           
          )
      ORDER BY pro_alarm_date, pro_alarm_time";
        $this->setQueryString($sql);
        return (parent::getRows());
    }

    function getAlarmReachedRows()
    {
        $sql = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " LEFT JOIN customer ON cus_custno = pro_custno
           LEFT JOIN consultant ON cns_consno = pro_consno

          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID . " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              AND ca.caa_callacttypeno <> " . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . "
            )
            left join callactivity fixed 
            on fixed.caa_problemno = pro_problemno and fixed.caa_callacttypeno = " . CONFIG_FIXED_ACTIVITY_TYPE_ID . " 
            left join consultant fixedEngineer on fixed.caa_consno = fixedEngineer.cns_consno
            left join team fixedTeam on fixedEngineer.teamID = fixedTeam.teamID 
            left join team queueTeam on queueTeam.level = pro_queue_no
        WHERE pro_status IN ( 'I', 'P' )
          AND CONCAT( pro_alarm_date, ' ', coalesce(pro_alarm_time, '00:00:00') )  < NOW()
      ORDER BY pro_alarm_date, pro_alarm_time";
        $this->setQueryString($sql);
        return (parent::getRows());
    }


    /*
      Get Awaiting, In-progress and future SRs by Queue

      */
    function getRowsByQueueNoWithFuture($queueNo)
    {
        $sql = "SELECT {$this->getDBColumnNamesAsString()}, pro_alarm_date is not null as hasAlarmDate,
             consultant.cns_consno is not null as isAssigned
             FROM {$this->getTableName()}
             LEFT JOIN customer ON cus_custno = pro_custno
           LEFT JOIN consultant ON cns_consno = pro_consno

          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID . " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              and ca.caa_callacttypeno NOT IN(43,55,59,60,61)
            ) 
            left join callactivity fixed 
            on fixed.caa_problemno = pro_problemno and fixed.caa_callacttypeno = " . CONFIG_FIXED_ACTIVITY_TYPE_ID . " 
            left join consultant fixedEngineer on fixed.caa_consno = fixedEngineer.cns_consno
            left join team fixedTeam on fixedEngineer.teamID = fixedTeam.teamID 
            left join team queueTeam on queueTeam.level = pro_queue_no
        WHERE pro_status IN( 'I', 'P' )

          AND pro_queue_no = $queueNo
          order by hasAlarmDate asc, CONCAT( pro_alarm_date, ' ', coalesce(concat(pro_alarm_time,':00') , '00:00:00') ) asc,   isAssigned asc, {$this->getDBColumnName(self::hoursRemainingForSLA)} desc
          ";
        $this->setQueryString($sql);
        return (parent::getRows());
    }

    function getRow($pkID)
    {
        $this->setPKValue($pkID);
        $sql = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " LEFT JOIN customer ON cus_custno = pro_custno
           LEFT JOIN consultant ON cns_consno = pro_consno

          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID . " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              and ca.caa_callacttypeno NOT IN(43,55,59,60,61)
            ) 
            left join callactivity fixed 
            on fixed.caa_problemno = pro_problemno and fixed.caa_callacttypeno = " . CONFIG_FIXED_ACTIVITY_TYPE_ID . " 
            left join consultant fixedEngineer on fixed.caa_consno = fixedEngineer.cns_consno
            left join team fixedTeam on fixedEngineer.teamID = fixedTeam.teamID 
            left join team queueTeam on queueTeam.level = pro_queue_no
            
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
        $this->setAddColumnsOn();
        $this->addColumn(
            "contactName",
            DA_STRING,
            true,
            "concat(contact.con_first_name, ' ', contact.con_last_name)"
        );
        $this->addColumn(
            "contactId",
            DA_STRING,
            true,
            "initial.caa_contno"
        );
        $sql = "SELECT DISTINCT {$this->getDBColumnNamesAsString()}
             FROM {$this->getTableName()}
             LEFT JOIN customer ON cus_custno = pro_custno
          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID . " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              and ca.caa_callacttypeno NOT IN(43,55,59,60,61)
            ) 
           LEFT JOIN consultant ON cns_consno = pro_consno
           left join contact on contact.con_contno = initial.caa_contno
           left join callactivity fixed 
            on fixed.caa_problemno = pro_problemno and fixed.caa_callacttypeno = " . CONFIG_FIXED_ACTIVITY_TYPE_ID . " 
            left join consultant fixedEngineer on fixed.caa_consno = fixedEngineer.cns_consno
            left join team fixedTeam on fixedEngineer.teamID = fixedTeam.teamID 
            left join team queueTeam on queueTeam.level = pro_queue_no
        WHERE
          pro_custno = $customerID
          AND pro_status <> 'C'";
        $sql .= " ORDER BY pro_date_raised DESC";              // in progress
        $this->setQueryString($sql);
        return (parent::getRows());
    }

    function getProblemsByContactID($contactID)
    {
        $sql = "SELECT DISTINCT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " LEFT JOIN customer ON cus_custno = pro_custno
          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID . " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              and ca.caa_callacttypeno NOT IN(43,55,59,60,61)
            ) 
           LEFT JOIN consultant ON cns_consno = pro_consno
           left join callactivity fixed 
            on fixed.caa_problemno = pro_problemno and fixed.caa_callacttypeno = " . CONFIG_FIXED_ACTIVITY_TYPE_ID . " 
            left join consultant fixedEngineer on fixed.caa_consno = fixedEngineer.cns_consno
            left join team fixedTeam on fixedEngineer.teamID = fixedTeam.teamID 
            left join team queueTeam on queueTeam.level = pro_queue_no
        WHERE
          initial.caa_contno = " . $contactID . " and pro_date_raised >= date(now() - interval 3 month) 
         ";
        $sql .= " ORDER BY pro_date_raised DESC";              // in progress
        $this->setQueryString($sql);
        return (parent::getRows());
    }

    public function getP1byCustomerIdLast30Days($customerID)
    {
        $sql = "SELECT DISTINCT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " LEFT JOIN customer ON cus_custno = pro_custno
          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID . " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              and (pro_status in ('F','C') and ca.caa_callacttypeno = " . CONFIG_FIXED_ACTIVITY_TYPE_ID . " or (ca.caa_callacttypeno in (4,8) and ca.caa_hide_from_customer_flag = 'N') )
              and ca.caa_callacttypeno NOT IN(43,55,59,60,61)
            ) 
           LEFT JOIN consultant ON cns_consno = pro_consno
           left join callactivity fixed 
            on fixed.caa_problemno = pro_problemno and fixed.caa_callacttypeno = " . CONFIG_FIXED_ACTIVITY_TYPE_ID . " 
            left join consultant fixedEngineer on fixed.caa_consno = fixedEngineer.cns_consno
            left join team fixedTeam on fixedEngineer.teamID = fixedTeam.teamID 
            left join team queueTeam on queueTeam.level = pro_queue_no
        WHERE
          pro_custno = $customerID" . " AND cast(" . $this->getDBColumnName(
                self::dateRaised
            ) . " as date)  BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()" . " and " . $this->getDBColumnName(
                self::hideFromCustomerFlag
            ) . " <> 'Y'" . " and " . $this->getDBColumnName(self::priority) . " = 1";
        $sql .= " ORDER BY pro_date_raised DESC";
        $this->setQueryString($sql);
        return parent::getRows();
    }

    public function getStartersSRByCustomerIDLast12Months($customerID)
    {
        $sql = "SELECT DISTINCT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " LEFT JOIN customer ON cus_custno = pro_custno
          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID . " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              and ca.caa_callacttypeno NOT IN(43,55,59,60,61)
            ) 
           LEFT JOIN consultant ON cns_consno = pro_consno
           left join callactivity fixed 
            on fixed.caa_problemno = pro_problemno and fixed.caa_callacttypeno = " . CONFIG_FIXED_ACTIVITY_TYPE_ID . " 
            left join consultant fixedEngineer on fixed.caa_consno = fixedEngineer.cns_consno
            left join team fixedTeam on fixedEngineer.teamID = fixedTeam.teamID 
            left join team queueTeam on queueTeam.level = pro_queue_no
        WHERE
          pro_custno = $customerID" . " AND cast(" . $this->getDBColumnName(
                self::dateRaised
            ) . " as date) BETWEEN CURDATE() - INTERVAL 12 month AND CURDATE()" . " and " . $this->getDBColumnName(
                self::hideFromCustomerFlag
            ) . " <> 'Y'" . " and " . $this->getDBColumnName(self::rootCauseID) . " = 58";
        $sql .= " ORDER BY pro_date_raised DESC";
        $this->setQueryString($sql);
        return parent::getRows();
    }

    public function getStartersSRByCustomerIDInDateRange($customerID,
                                                         DateTimeInterface $startDate,
                                                         DateTimeInterface $endDate
    )
    {
        $sql = "SELECT DISTINCT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " LEFT JOIN customer ON cus_custno = pro_custno
          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID . " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              and ca.caa_callacttypeno NOT IN(43,55,59,60,61)
            ) 
           LEFT JOIN consultant ON cns_consno = pro_consno
           left join callactivity fixed 
            on fixed.caa_problemno = pro_problemno and fixed.caa_callacttypeno = " . CONFIG_FIXED_ACTIVITY_TYPE_ID . " 
            left join consultant fixedEngineer on fixed.caa_consno = fixedEngineer.cns_consno
            left join team fixedTeam on fixedEngineer.teamID = fixedTeam.teamID 
            left join team queueTeam on queueTeam.level = pro_queue_no
        WHERE
          pro_custno = $customerID" . " AND cast(" . $this->getDBColumnName(
                self::dateRaised
            ) . " as date) BETWEEN '" . $startDate->format('Y-m-d') . "' AND  '" . $endDate->format(
                'Y-m-d'
            ) . "' " . " and " . $this->getDBColumnName(
                self::hideFromCustomerFlag
            ) . " <> 'Y'" . " and " . $this->getDBColumnName(self::rootCauseID) . " = 58";
        $sql .= " ORDER BY pro_date_raised DESC";
        $this->setQueryString($sql);
        return parent::getRows();
    }

    public function getLeaversSRByCustomerIDLast12Months($customerID)
    {
        $sql = "SELECT DISTINCT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " LEFT JOIN customer ON cus_custno = pro_custno
          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID . " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              and ca.caa_callacttypeno NOT IN(43,55,59,60,61)
            ) 
           LEFT JOIN consultant ON cns_consno = pro_consno
           left join callactivity fixed 
            on fixed.caa_problemno = pro_problemno and fixed.caa_callacttypeno = " . CONFIG_FIXED_ACTIVITY_TYPE_ID . " 
            left join consultant fixedEngineer on fixed.caa_consno = fixedEngineer.cns_consno
            left join team fixedTeam on fixedEngineer.teamID = fixedTeam.teamID 
            left join team queueTeam on queueTeam.level = pro_queue_no
        WHERE
          pro_custno = $customerID" . " AND cast(" . $this->getDBColumnName(
                self::dateRaised
            ) . " as date) BETWEEN CURDATE() - INTERVAL 12 month AND CURDATE()" . " and " . $this->getDBColumnName(
                self::hideFromCustomerFlag
            ) . " <> 'Y'" . " and " . $this->getDBColumnName(self::rootCauseID) . " = 62";
        $sql .= " ORDER BY pro_date_raised DESC";
        $this->setQueryString($sql);
        return parent::getRows();
    }

    public function getLeaversSRByCustomerIDInDateRange($customerID,
                                                        DateTimeInterface $startDate,
                                                        DateTimeInterface $endDate
    )
    {
        $sql = "SELECT DISTINCT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " LEFT JOIN customer ON cus_custno = pro_custno
          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID . " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              and ca.caa_callacttypeno NOT IN(43,55,59,60,61)
            ) 
           LEFT JOIN consultant ON cns_consno = pro_consno
           left join callactivity fixed 
            on fixed.caa_problemno = pro_problemno and fixed.caa_callacttypeno = " . CONFIG_FIXED_ACTIVITY_TYPE_ID . " 
            left join consultant fixedEngineer on fixed.caa_consno = fixedEngineer.cns_consno
            left join team fixedTeam on fixedEngineer.teamID = fixedTeam.teamID 
            left join team queueTeam on queueTeam.level = pro_queue_no
        WHERE
          pro_custno = $customerID" . " AND cast(" . $this->getDBColumnName(
                self::dateRaised
            ) . " as date) BETWEEN '" . $startDate->format('Y-m-d') . "' AND  '" . $endDate->format(
                'Y-m-d'
            ) . "' " . " and " . $this->getDBColumnName(
                self::hideFromCustomerFlag
            ) . " <> 'Y'" . " and " . $this->getDBColumnName(self::rootCauseID) . " = 62";
        $sql .= " ORDER BY pro_date_raised DESC";
        $this->setQueryString($sql);
        return parent::getRows();
    }

    public function getDashBoardRows($limit = 10,
                                     $orderBy = 'shortestSLARemaining',
                                     $isP5 = false,
                                     $showHelpDesk = true,
                                     $showEscalation = true,
                                     $showSmallProjects = true,
                                     $showProjects = true
    )
    {

        $includeFixed = "";
        $isHoldForQA  = $orderBy == "holdForQA";
        if ($isHoldForQA) {
            $includeFixed = ",'F'";

        }
        $sql = "SELECT " . $this->getDBColumnNamesAsString() . ', ' . $this->getDBColumnName(
                self::workingHours
            ) . ' - ' . $this->getDBColumnName(
                self::slaResponseHours
            ) . ' as hoursRemaining' . " FROM " . $this->getTableName() . " LEFT JOIN customer ON cus_custno = pro_custno
           LEFT JOIN consultant ON cns_consno = pro_consno
           left join team on consultant.teamID = team.teamID
          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID . " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              and ca.caa_callacttypeno NOT IN(43,55,59,60,61)
            ) 
            left join callactivity fixed 
            on fixed.caa_problemno = pro_problemno and fixed.caa_callacttypeno = " . CONFIG_FIXED_ACTIVITY_TYPE_ID . " 
            left join consultant fixedEngineer on fixed.caa_consno = fixedEngineer.cns_consno
            left join team fixedTeam on fixedEngineer.teamID = fixedTeam.teamID 
            left join team queueTeam on queueTeam.level = pro_queue_no 
        WHERE {$this->getDBColumnName(                self::status            ) } in ('I','P'$includeFixed) and pro_queue_no <> 7 and (consultant.cns_consno is null or not consultant.excludeFromSDManagerDashboard) ";
        if (!$showHelpDesk) {
            $sql .= ' and pro_queue_no <> 1 ';
        }
        if (!$showEscalation) {
            $sql .= ' and pro_queue_no <> 2 ';
        }
        if (!$showSmallProjects) {
            $sql .= ' and pro_queue_no <> 3 ';
        }
        if (!$showProjects) {
            $sql .= ' and pro_queue_no <> 5 ';
        }
        if ($isP5) {
            $sql .= 'and ' . $this->getDBColumnName(
                    self::priority
                ) . ' = 5 and  team.' . DBETeam::level . " <= 3";
        } else {
            if (!$isHoldForQA) {
                $sql .= 'and ' . $this->getDBColumnName(
                        self::priority
                    ) . ' <= 4 and ' . $this->getDBColumnName(
                        self::priority
                    ) . ' > 0 ';
            }
        }
        switch ($orderBy) {

            case 'shortestSLAFixRemaining':
            {
                $sql .= ' AND
  CASE 
  WHEN `pro_priority` = 1 THEN customer.`slaP1PenaltiesAgreed`
  WHEN `pro_priority` = 2 THEN customer.`slaP2PenaltiesAgreed`
  WHEN `pro_priority` = 3 THEN customer.`slaP3PenaltiesAgreed`
  END
  AND 
  CASE 
	WHEN `pro_priority` = 1 THEN customer.`slaFixHoursP1`
	WHEN `pro_priority` = 2 THEN customer.`slaFixHoursP2`
	WHEN `pro_priority` = 3 THEN customer.`slaFixHoursP3`
	END - problem.pro_working_hours 
	AND 
  CASE
	WHEN `pro_priority` = 1 THEN customer.`slaFixHoursP1`
	WHEN `pro_priority` = 2 THEN customer.`slaFixHoursP2`
	WHEN `pro_priority` = 3 THEN customer.`slaFixHoursP3`
	END - problem.pro_working_hours <= (SELECT headert.`fixSLABreachWarningHours` FROM headert LIMIT 1)
  ORDER BY CASE 
	WHEN `pro_priority` = 1 THEN customer.`slaFixHoursP1`
	WHEN `pro_priority` = 2 THEN customer.`slaFixHoursP2`
	WHEN `pro_priority` = 3 THEN customer.`slaFixHoursP3`
	END - problem.pro_working_hours ASC';
                break;
            }
            case 'shortestSLARemaining':
            {

                $sql .= ' and ' . $this->getDBColumnName(
                        self::status
                    ) . ' = "I" and initial.caa_date < date(NOW() + interval 1  day) order by hoursRemaining desc';
                break;
            }
            case 'currentOpenP1Requests':
                $sql .= ' and ' . $this->getDBColumnName(
                        self::priority
                    ) . ' = 1 ';
                $sql .= ' order by pro_sla_response_hours - pro_working_hours asc';
                break;
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
            case 'critical':
                $sql .= " and  " . $this->getDBColumnName(self::criticalFlag) . " = 'Y' order by hoursRemaining desc ";
                break;
            case 'currentOpenSRs':
                $sql .= " and last.caa_endtime is null AND (
    (
      last.caa_date = CURDATE()
      AND last.caa_starttime <= TIME(NOW())
    )
    OR last.caa_date < CURDATE()
  )  order by hoursRemaining desc";
                break;
            case "holdForQA":
                $sql .= " and holdForQA=1";

        }
        $sql .= ' limit ' . $limit;
//        var_dump($sql);
        $this->setQueryString($sql);
        return (parent::getRows());
    }

    public function getDashBoardEngineersInSRRows($engineersMaxCount = 3,
                                                  $pastHours = 24,
                                                  $limit = 5,
                                                  $isP5 = false,
                                                  $showHelpDesk = true,
                                                  $showEscalation = true,
                                                  $showSmallProjects = true,
                                                  $showProjects = true
    )
    {
        $sql = "SELECT " . $this->getDBColumnNamesAsString() . ', ' . $this->getDBColumnName(
                self::workingHours
            ) . ' - ' . $this->getDBColumnName(
                self::slaResponseHours
            ) . ' as hoursRemaining' . " FROM " . $this->getTableName() . " LEFT JOIN customer ON cus_custno = pro_custno
           LEFT JOIN consultant ON cns_consno = pro_consno

          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID . " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              and ca.caa_callacttypeno NOT IN(43,55,59,60,61)
            ) 
            left join callactivity fixed 
            on fixed.caa_problemno = pro_problemno and fixed.caa_callacttypeno = " . CONFIG_FIXED_ACTIVITY_TYPE_ID . " 
            left join consultant fixedEngineer on fixed.caa_consno = fixedEngineer.cns_consno
            left join team fixedTeam on fixedEngineer.teamID = fixedTeam.teamID 
            left join team queueTeam on queueTeam.level = pro_queue_no
            
        WHERE " . $this->getDBColumnName(self::customerID) . ' <> 282  and ' . $this->getDBColumnName(
                self::status
            ) . " in ('I','P') ";
        if (!$showHelpDesk) {
            $sql .= ' and pro_queue_no <> 1 ';
        }
        if (!$showEscalation) {
            $sql .= ' and pro_queue_no <> 2 ';
        }
        if (!$showSmallProjects) {
            $sql .= ' and pro_queue_no <> 3 ';
        }
        if (!$showProjects) {
            $sql .= ' and pro_queue_no <> 5 ';
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

    public function getOpenRowsByContactID($contactID)
    {
        $sql = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " LEFT JOIN customer ON cus_custno = pro_custno
          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID . " JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              and ca.caa_callacttypeno NOT IN(43,55,59,60,61)
            ) 
           LEFT JOIN consultant ON cns_consno = pro_consno
           left join callactivity fixed 
            on fixed.caa_problemno = pro_problemno and fixed.caa_callacttypeno = " . CONFIG_FIXED_ACTIVITY_TYPE_ID . " 
            left join consultant fixedEngineer on fixed.caa_consno = fixedEngineer.cns_consno
            left join team fixedTeam on fixedEngineer.teamID = fixedTeam.teamID 
            left join team queueTeam on queueTeam.level = pro_queue_no
        WHERE
          pro_contno = $contactID
          AND pro_status <> 'C'";
        $sql .= " ORDER BY pro_date_raised DESC";              // in progress
        $this->setQueryString($sql);
        return (parent::getRows());
    }

    /**
     * put your comment there...
     *
     * @param mixed $status
     * @param bool $includeAutomaticallyFixed
     * @return bool
     * @internal param mixed $future TRUE= ONLY return future alarmed requests
     */
    function getCustomerOpenRows($customerID)
    {
        $sql = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " LEFT JOIN customer ON cus_custno = pro_custno
           LEFT JOIN consultant ON cns_consno = pro_consno

          JOIN callactivity `initial`
            ON initial.caa_problemno = pro_problemno AND initial.caa_callacttypeno = " . CONFIG_INITIAL_ACTIVITY_TYPE_ID . " 
          
            JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              AND ca.caa_callacttypeno <> " . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . "
            )
            left join callactivity fixed 
            on fixed.caa_problemno = pro_problemno and fixed.caa_callacttypeno = " . CONFIG_FIXED_ACTIVITY_TYPE_ID . " 
            left join consultant fixedEngineer on fixed.caa_consno = fixedEngineer.cns_consno
            left join team fixedTeam on fixedEngineer.teamID = fixedTeam.teamID 
            left join team queueTeam on queueTeam.level = pro_queue_no
        WHERE 1=1";
        $sql .= " AND pro_custno=$customerID";
        $sql .= " AND pro_status <> 'C' and pro_status <> 'F' ";
        $sql .= " ORDER BY pro_alarm_date, pro_alarm_time";
        $this->setQueryString($sql);
        return (parent::getRows());
    }

    function isSpecialAttention()
    {
        $isSpecialAttentionCustomer = $this->getValue(self::specialAttentionFlag) == 'Y';
        $isSpecialAttentionExpired  = $this->getValue(self::specialAttentionEndDate) < date('Y-m-d');
        $isSpecialAttentionContact  = $this->getValue(self::specialAttentionContactFlag) == 'Y';
        if (($isSpecialAttentionCustomer && !$isSpecialAttentionExpired) || $isSpecialAttentionContact) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    function isRequestBeingWorkedOn()
    {
        $dateString       = $this->getValue(DBEJProblem::lastDate);
        $timeString       = $this->getValue(DBEJProblem::lastStartTime);
        $activityDateTime = DateTime::createFromFormat('Y-m-d H:i', "$dateString $timeString");
        if ($activityDateTime > (new DateTime())) {
            return false;
        }
        return !$this->getValue(DBEJProblem::lastEndTime);
    }

    function isSLABreached()
    {
        $status           = $this->getValue(DBEJProblem::status);
        $priority         = $this->getValue(DBEJProblem::priority);
        $slaResponseHours = $this->getValue(DBEJProblem::slaResponseHours);
        $workingHours     = $this->getValue(DBEJProblem::workingHours);
        $respondedHours   = $this->getValue(DBEJProblem::respondedHours);
        if ($slaResponseHours == 0) {
            $slaResponseHours = 1;
        }
        if ($priority == 5) {
            return false;
        }
        if ($status != 'I' && $respondedHours <= $slaResponseHours) {
            return false;
        }
        $percentageSLA = ($workingHours / $slaResponseHours);
        if ($status == 'I' && $percentageSLA < 1) {
            return false;
        }
        return true;
    }

    function isOnHold()
    {
        return $this->getValue(DBEJProblem::awaitingCustomerResponseFlag) == 'Y';
    }

    function getDateTime(): ?DateTimeInterface
    {
        $dateTime = DateTime::createFromFormat(
            DATE_MYSQL_DATETIME,
            "{$this->lastDate()} {$this->properTime()}"
        );
        if (!$dateTime) {
            return null;
        }
        return $dateTime;
    }

    function lastDate()
    {
        return $this->getValue(self::lastDate);
    }

    private function properTime()
    {
        return $this->getValue(self::lastStartTime) ? "{$this->getValue(self::lastStartTime)}:00" : null;
    }

    public function alarmDateTime(): ?DateTimeInterface
    {
        if (!$this->getValue(self::alarmDateTime)) {
            return null;
        }
        $dateTime = DateTime::createFromFormat(
            DATE_MYSQL_DATETIME,
            $this->getValue(DBEJProblem::alarmDateTime)
        );
        if (!$dateTime) {
            return null;
        }
        return $dateTime;
    }

    public function isWorkHidden()
    {
        return $this->getValue(DBECustomer::referredFlag) == 'Y';
    }
}

?>
