<?php /*
* Call activity problem table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"]."/DBEProblem.inc.php");
class DBEJProblem extends DBEProblem {
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function __construct(&$owner, $pkID = false){
		$this->constructor($owner, $pkID );
	}
	/**
	* constructor
	* @access public
	* @return void
	* @param  void
	*/
	function constructor(&$owner, $pkID = false )
	{
    parent::__construct($owner, $pkID );
 		$this->setAddColumnsOn();
 		$this->addColumn("customerName", DA_STRING, DA_ALLOW_NULL, "cus_name");
    $this->addColumn("specialAttentionFlag", DA_STRING, DA_ALLOW_NULL, "cus_special_attention_flag");
    $this->addColumn("specialAttentionEndDate", DA_DATE, DA_ALLOW_NULL, "cus_special_attention_end_date");
    $this->addColumn("dateRaisedDMY", DA_STRING, DA_ALLOW_NULL, "DATE_FORMAT(pro_date_raised, '%d/%m/%Y')");
    $this->addColumn("timeRaised", DA_STRING, DA_ALLOW_NULL, "TIME_FORMAT(pro_date_raised, '%H:%i')");
    $this->addColumn("totalActivityHours", DA_STRING, DA_ALLOW_NULL, "pro_total_activity_duration_hours");
    $this->addColumn("hoursElapsed", DA_STRING, DA_ALLOW_NULL, "TIME_FORMAT(TIMEDIFF(NOW(), pro_date_raised), '%H:%i')");
    $this->addColumn("engineerName", DA_STRING, DA_ALLOW_NULL, "CONCAT(consultant.firstName,' ',consultant.lastName)");
    $this->addColumn("teamID", DA_ID, DA_ALLOW_NULL, "consultant.teamID");
    $this->addColumn("engineerLogname", DA_STRING, DA_ALLOW_NULL, "consultant.cns_logname");
    $this->addColumn("engineerInitials", DA_STRING, DA_ALLOW_NULL, "CONCAT(SUBSTRING(consultant.firstName, 1, 1) ,SUBSTRING(consultant.lastName, 1, 1 ) )");
    $this->addColumn("slaDueHours", DA_DATETIME, DA_ALLOW_NULL, "TIME_FORMAT( TIMEDIFF( pro_working_hours, pro_sla_response_hours ), '%H:%i')");

    $this->addColumn("callActivityID", DA_INTEGER, DA_ALLOW_NULL, 'initial.caa_callactivityno');
    $this->addColumn("serverGuard", DA_YN, DA_ALLOW_NULL, 'initial.caa_serverguard' );
    $this->addColumn("reason", DA_STRING, DA_ALLOW_NULL, 'initial.reason');

    $this->addColumn("lastCallActivityID", DA_INTEGER, DA_ALLOW_NULL, 'last.caa_callactivityno');
    $this->addColumn("lastServerGuard", DA_YN, DA_ALLOW_NULL, 'last.caa_serverguard' );
    $this->addColumn("lastReason", DA_STRING, DA_ALLOW_NULL, 'last.reason');

    $this->addColumn("lastCallActTypeID", DA_INTEGER, DA_ALLOW_NULL, "last.caa_callacttypeno");
    $this->addColumn("lastStartTime", DA_INTEGER, DA_ALLOW_NULL, "last.caa_starttime");
    $this->addColumn("lastUserID", DA_INTEGER, DA_ALLOW_NULL, "last.caa_consno");
    $this->addColumn("lastDate", DA_INTEGER, DA_ALLOW_NULL, "last.caa_date");
    $this->addColumn("lastAwaitingCustomerResponseFlag", DA_INTEGER, DA_ALLOW_NULL, "last.caa_awaiting_customer_response_flag");
    
    $this->addColumn("dashboardSortColumn", DA_FLOAT, DA_ALLOW_NULL, "pro_sla_response_hours - pro_working_hours ");

 		$this->setAddColumnsOff();
		$this->setPK(0);
	}
  /**
  * put your comment there...
  * 
  * @param mixed $status
  * @param mixed $future TRUE= ONLY return future alarmed requests
  * @return bool
  */
	function getRowsByStatus( $status = false, $includeAutomaticallyFixed = false )
	{
    $sql =
			"SELECT ".$this->getDBColumnNamesAsString().
				" FROM ".$this->getTableName().
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
    if ( $status ){
      $sql .= 
				" AND pro_status = '" . $status . "'";
    }
    /* Exclude future dated */        
    $sql .= " AND CONCAT( pro_alarm_date, ' ', pro_alarm_time ) <= NOW()";

    if ( $status == 'F' && !$includeAutomaticallyFixed ){
      $sql .= " AND last.caa_consno <> " . USER_SYSTEM;
    }
    
    $sql .= " ORDER BY pro_alarm_date, pro_alarm_time";

    $this->setQueryString( $sql );
    
		return(parent::getRows());
	}

  function getFutureRows()
  {
    $sql =
      "SELECT ".$this->getDBColumnNamesAsString().
        " FROM ".$this->getTableName().
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

    $this->setQueryString( $sql );
    
    return(parent::getRows());
  }

  /*
  Get Awaiting and In-progress SRs by Queue
  
  */
  function getRowsByQueueNo( $queueNo, $unassignedOnly = false )
  {
    $sql =
      "SELECT ".$this->getDBColumnNamesAsString().
        " FROM ".$this->getTableName().
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
  
    if ( $unassignedOnly ){
      $sql .= " AND pro_consno = 0";
      
    }
    else{
      $sql .= " AND pro_consno <> 0";
    }
    $this->setQueryString( $sql );
    
    return(parent::getRows());
  }

  function getRow( $pkID )
  {
    $this->setPKValue( $pkID );
    
    $sql =
      "SELECT ".$this->getDBColumnNamesAsString().
        " FROM ".$this->getTableName().
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
    
    $this->setQueryString( $sql );

    return(parent::getRow());
  }
  /**
  * @param mixed $customerID
  * @return bool
  */
  function getActiveProblemsByCustomer( $customerID )
  {
    $sql =
      "SELECT DISTINCT ".$this->getDBColumnNamesAsString().
        " FROM ".$this->getTableName().
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
                        
    $this->setQueryString( $sql );
    
    return(parent::getRows());
  }
}
?>
