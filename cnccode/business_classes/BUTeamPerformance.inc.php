<?php
/**
* Contract profit analysis by customer
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg["path_gc"]."/Business.inc.php");
require_once($cfg["path_bu"]."/BUHeader.inc.php");

class BUTeamPerformance extends Business{
  
  private $connection;

	function BUTeamPerformance( &$owner ){
		$this->constructor( $owner );
	}
	function constructor( &$owner ){
		parent::constructor( $owner );
		
    $this->connection = new PDO('mysql:host=' . DB_HOST .';dbname=' . DB_NAME .';charset=utf8', DB_USER, DB_PASSWORD);
	}
  function initialiseSearchForm(&$dsData)
  {
    $dsData = new DSForm($this);
    $dsData->addColumn('year', DA_STRING, DA_ALLOW_NULL);
    $dsData->setValue('year', '');
  }
  /*
  Update the monthly team performance figures for each team
  */  
  function update( $year, $month )
  {
    /* get current header fields */
    
    $buHeader = new BUHeader( $this );
    
    $buHeader->getHeader( $dsHeader );
    /* row for each level */
    
    /*
    Helpdesk team (team level 3)
    */
    $hdTeamTotal = $this->getCount( $year, $month, 1 );
    
    $hdTeamWithinSla = $this->getCount( $year, $month, 1, true );
    
    $hdTeamFixAverageHours = $this->getFixAverageHours( $year, $month, 1 );
    
    if ( $hdTeamTotal > 0 ){
      $hdTeamActualSlaPercentage = $hdTeamWithinSla / $hdTeamTotal * 100;
    }
    else{
      $hdTeamActualSlaPercentage = 100;
      
    }
    /*
    Escalations team (team level 3)
    */
    $esTeamTotal = $this->getCount( $year, $month, 2 );
    
    $esTeamWithinSla = $this->getCount( $year, $month, 2, true );
    
    $esTeamFixAverageHours = $this->getFixAverageHours( $year, $month, 2 );
    
    if ( $esTeamTotal > 0 ){
      $esTeamActualSlaPercentage = $esTeamWithinSla / $esTeamTotal * 100;
    }
    else{
      $esTeamActualSlaPercentage = 100;
      
    }
    /*
    Implementations team (team level 3)
    */
    $imTeamTotal = $this->getCount( $year, $month, 3 );
    
    $imTeamWithinSla = $this->getCount( $year, $month, 3, true );
    
    $imTeamFixAverageHours = $this->getFixAverageHours( $year, $month, 3 );
    
    if ( $imTeamTotal > 0 ){
      $imTeamActualSlaPercentage = $imTeamWithinSla / $imTeamTotal * 100;
    }
    else{
      $imTeamActualSlaPercentage = 100;
      
    }

    $record =
      array(
        'year'                        => $year,
        'month'                       => $month,
        'hdTeamTargetSlaPercentage'   => $dsHeader->getValue('hdTeamTargetSlaPercentage'),
        'hdTeamTargetFixHours'        => $dsHeader->getValue('hdTeamTargetFixHours'),
        'hdTeamTargetFixQtyPerMonth'  => $dsHeader->getValue('hdTeamTargetFixQtyPerMonth'),
        'hdTeamActualSlaPercentage'   => $hdTeamActualSlaPercentage,
        'hdTeamActualFixHours'        => $hdTeamFixAverageHours,
        'hdTeamActualFixQtyPerMonth'  => $this->getFixCount( $year, $month, 1 ),
        
        'esTeamTargetSlaPercentage'   => $dsHeader->getValue('esTeamTargetSlaPercentage'),
        'esTeamTargetFixHours'        => $dsHeader->getValue('esTeamTargetFixHours'),
        'esTeamTargetFixQtyPerMonth'  => $dsHeader->getValue('esTeamTargetFixQtyPerMonth'),
        'esTeamActualSlaPercentage'   => $esTeamActualSlaPercentage,
        'esTeamActualFixHours'        => $esTeamFixAverageHours,
        'esTeamActualFixQtyPerMonth'  => $this->getFixCount( $year, $month, 2 ),

        'imTeamTargetSlaPercentage'   => $dsHeader->getValue('imTeamTargetSlaPercentage'),
        'imTeamTargetFixHours'        => $dsHeader->getValue('imTeamTargetFixHours'),
        'imTeamTargetFixQtyPerMonth'  => $dsHeader->getValue('imTeamTargetFixQtyPerMonth'),
        'imTeamActualSlaPercentage'   => $imTeamActualSlaPercentage,
        'imTeamActualFixHours'        => $imTeamFixAverageHours,
        'imTeamActualFixQtyPerMonth'  => $this->getFixCount( $year, $month, 3 )
    );
    
    $this->updatePerformanceRecord( $record );
  }

  function updatePerformanceRecord( $record )
  {
    $sqlSetColumnString = $this->getSqlSetColumnString( $record );

    $existingRecord = $this->getPerformanceRecord( $record[ 'month'], $record[ 'year'] );

    $record = $this->convertArrayToPlaceholders( $record );

    if ( $existingRecord ){

      $record[ ':teamPerformanceID' ] = $existingRecord[ 'teamPerformanceID' ];

      $sql =
        "UPDATE
          team_performance
        SET ".
          $sqlSetColumnString .
        " WHERE
          teamPerformanceID = :teamPerformanceID";
        
      $statement = $this->connection->prepare( $sql );

      $statement->execute( $record );
      print_r($statement->errorInfo());
      
    }
    else{
      $sql =
        "INSERT INTO
          team_performance
        SET".
          $sqlSetColumnString;
        
      $statement = $this->connection->prepare( $sql );

      $statement->execute( $record );
      
      print_r($statement->errorInfo());
    }
  }

  function getPerformanceRecord( $month, $year )
  {
    $month = ( integer )$month ; //strip leading zero
    
    $sql =
      "SELECT
        *
      FROM
        team_performance
      WHERE
        month = ?
        AND year = ?";
      
    $statement = $this->connection->prepare( $sql );
    $statement->execute( array( $month, $year ) );
    return $statement->fetch();
    
      
  }

  function getCount( $year, $month, $teamLevel, $resolvedWithinSla = false )
  {
    $sql =
      "SELECT
        COUNT(*)
      FROM
        problem
        JOIN consultant ON pro_started_consno = cns_consno
        JOIN team ON team.`teamID` = consultant.`teamID`        
      WHERE
        pro_status = 'C'
        AND pro_custno <> " . CONFIG_INTERNAL_CUSTOMERID . 
        " AND team.`level` = ?
        AND pro_priority < 5
        AND MONTH(pro_complete_date) = ? AND YEAR( pro_complete_date) = ?";
        
    if( $resolvedWithinSla ){        
      $sql .= " AND pro_sla_response_hours >= pro_responded_hours";
    }
    
    $statement = $this->connection->prepare( $sql );

    $statement->execute( array( $teamLevel, $month, $year ) );
    
    return $statement->fetchColumn();
  }
  
  function getFixCount( $year, $month, $teamLevel )
  {
    $sql =
      "SELECT
        COUNT(*)
      FROM
        problem
        JOIN consultant ON pro_fixed_consno = cns_consno
        JOIN team ON team.`teamID` = consultant.`teamID`        
      WHERE
        pro_status = 'C'
        AND pro_custno <> " . CONFIG_INTERNAL_CUSTOMERID . 
        " AND team.`level` = ?
        AND pro_priority < 5
        AND MONTH(pro_complete_date) = ? AND YEAR( pro_complete_date) = ?";
    
    $statement = $this->connection->prepare( $sql );

    $statement->execute( array( $teamLevel, $month, $year ) );
    
    return $statement->fetchColumn();
  }

  function getFixAverageHours( $year, $month, $teamLevel )
  {
    $sql =
    "SELECT
      AVG( pro_working_hours )
    FROM
      problem
      JOIN consultant ON pro_fixed_consno = cns_consno
      JOIN team ON team.`teamID` = consultant.`teamID`
    WHERE
      pro_status = 'C'
      AND pro_custno <> " . CONFIG_INTERNAL_CUSTOMERID . 
      " AND team.`level` = ?
      AND pro_priority < 5
      AND MONTH(pro_complete_date) = ? AND YEAR( pro_complete_date) = ?";

    $statement = $this->connection->prepare( $sql );

    $statement->execute( array( $teamLevel, $month, $year ) );
    
    $ret = $statement->fetchColumn();
    
    if ( is_null( $ret )){
      $ret = 0;
    } 
    return $ret;
  }  
  private function convertArrayToPlaceholders( $array )
  {
    $ret = array();
    
    foreach ( $array as $key => $value ){
      $ret[ ':' . $key  ] = $value;  
    } 
    return $ret;
  }

  private function getSqlSetColumnString( $array )
  {
    $string = '';
    
    foreach ( $array as $key => $value ){
      if ( $string ){
        $line = ',';
      }
      $line .= '`'. $key . '` = :' . $key;

      $string .= $line;  
    } 
    return $string;
  }

  function getRecordsByYear( $year )
  {
    $sql =
      "SELECT
        *
      FROM
        team_performance
      WHERE
        year = ?";
      
    $statement = $this->connection->prepare( $sql );
    
    $statement->execute( array( $year ) );
    
    return $statement->fetchAll(); // an array of all records for year
      
  }
  
  function getQuarterlyRecordsByYear( $year )
  {
    $sql =
      "SELECT
        year,
          CASE
            WHEN MONTH BETWEEN 1 AND 3 THEN 1
            WHEN MONTH BETWEEN 4 AND 6 THEN 2
            WHEN MONTH BETWEEN 7 AND 9 THEN 3
            WHEN MONTH BETWEEN 10 AND 12 THEN 4
          END
         AS quarter,
          AVG(`hdTeamTargetSlaPercentage`) as hdTeamTargetSlaPercentage,
          AVG(`hdTeamTargetFixHours`) as hdTeamTargetFixHours,
          SUM(`hdTeamTargetFixQtyPerMonth`) as hdTeamTargetFixQty,
          AVG(`hdTeamActualSlaPercentage`) as hdTeamActualSlaPercentage,
          AVG(`hdTeamActualFixHours`) as hdTeamActualFixHours,
          SUM(`hdTeamActualFixQtyPerMonth`) as hdTeamActualFixQty,

          AVG(`esTeamTargetSlaPercentage`) as esTeamTargetSlaPercentage,
          AVG(`esTeamTargetFixHours`) as esTeamTargetFixHours,
          SUM(`esTeamTargetFixQtyPerMonth`) as esTeamTargetFixQty,
          AVG(`esTeamActualSlaPercentage`) as esTeamActualSlaPercentage,
          AVG(`esTeamActualFixHours`) as esTeamActualFixHours,
          SUM(`esTeamActualFixQtyPerMonth`) as esTeamActualFixQty,

          AVG(`imTeamTargetSlaPercentage`) as imTeamTargetSlaPercentage,
          AVG(`imTeamTargetFixHours`) as imTeamTargetFixHours,
          SUM(`imTeamTargetFixQtyPerMonth`) as imTeamTargetFixQty,
          AVG(`imTeamActualSlaPercentage`) as imTeamActualSlaPercentage,
          AVG(`imTeamActualFixHours`) as imTeamActualFixHours,
          SUM(`imTeamActualFixQtyPerMonth`) as imTeamActualFixQty
        FROM
          `team_performance`
        WHERE
           YEAR = ?
         GROUP BY
         
         CONCAT( YEAR,
          CASE
            WHEN MONTH BETWEEN 1 AND 3 THEN 1
            WHEN MONTH BETWEEN 4 AND 6 THEN 2
            WHEN MONTH BETWEEN 7 AND 9 THEN 3
            WHEN MONTH BETWEEN 10 AND 12 THEN 4
          END
        )";
      
    $statement = $this->connection->prepare( $sql );
    
    $statement->execute( array( $year ) );
    
    return $statement->fetchAll(); // an array of all records for year
      
  }

  function setHistoricStartedByUsers()
  {
    $sql =
    
      "SELECT * from problem
      WHERE
      pro_date_raised >= '2014-01-01'
      AND
      pro_started_consno IS NULL";

    $statement = $this->connection->prepare( $sql );
    
    $statement->execute();
    
    $activity_connection = new PDO('mysql:host=' . DB_HOST .';dbname=' . DB_NAME .';charset=utf8', DB_USER, DB_PASSWORD);

    $update_connection = new PDO('mysql:host=' . DB_HOST .';dbname=' . DB_NAME .';charset=utf8', DB_USER, DB_PASSWORD);

    $sql =
      "UPDATE
        problem
      SET
        pro_started_consno = ?
      WHERE
        pro_problemno = ?";

    $updateStatement = $update_connection->prepare( $sql );
           
    while ( $result = $statement->fetch() ){
      /*
      Get 1st activity not of initial or escalated type
      */
      $sql = 
        "SELECT
          caa_consno
        FROM
          callactivity
        WHERE
          caa_problemno = ?
          
        AND
          caa_callacttypeno not in ( ?, ? )
        
        ORDER BY
          caa_date
          
        LIMIT 1";
      
      $get_act_statement = $activity_connection->prepare( $sql );

      $get_act_statement->execute( array( $result[ 'pro_problemno' ], CONFIG_INITIAL_ACTIVITY_TYPE_ID, CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID ) );
      
      if ( $cons_result = $get_act_statement->fetch() ){
        
        $updateStatement->execute( array( $cons_result[ 'caa_consno' ], $result[ 'pro_problemno' ] ) );
        
        echo 'SR: ' . $result[ 'pro_problemno' ] . ' to '. $cons_result[ 'caa_consno' ] . '<br/>';
      
      }
      
      
    }
    
  }
  
}//End of class
?>