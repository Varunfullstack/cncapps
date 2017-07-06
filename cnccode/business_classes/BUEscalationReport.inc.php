<?php
/**
* Escalation Report business class
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg["path_gc"]."/Business.inc.php");
require_once($cfg["path_dbe"]."/CNCMysqli.inc.php");

class BUEscalationReport extends Business{
	/**
	* Constructor
	* @access Public
	*/
  private $db;

	function BUEscalationReport(&$owner){
		$this->constructor($owner);
	}
  
	function constructor(&$owner){
		parent::constructor($owner);
    $this->db = new CNCMysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

	}
  
	public function initialiseSearchForm(&$dsData)
	{
		$dsData = new DSForm($this);
		$dsData->addColumn('fromDate', DA_DATE, DA_ALLOW_NULL);
		$dsData->addColumn('toDate', DA_DATE, DA_ALLOW_NULL);
	}
  
  public function getTeamReport( $dsSearchForm ){
    
    $fromDate = $dsSearchForm->getValue('fromDate');
    $toDate = $dsSearchForm->getValue('toDate');
    
    $teamReport = $this->getTeamData( $fromDate, $toDate );
    
    $html = '<table name="team" width="100%" border="1">';
        
    foreach( $teamReport as $key => $reportRow ){
      
      if ( $key == 0 ){
        $html .= '<thead><tr>';
        foreach ( $reportRow as $value ){
          $html .= '<th>' . $value . '</th>';
        }
        $html .= '</tr></thead><tbody>';
      }
      else{
        $html .= '<tr>';
        foreach ( $reportRow as $value ){
          $html .= '<td>' . $value . '</td>';
        }
        $html .= '</tr>';
      }
    }
    $html .= '</tbody></table>';
    
    return $html;
  }
  
  public function getTechnicianReport( $dsSearchForm ){
    
    $fromDate = $dsSearchForm->getValue('fromDate');
    $toDate = $dsSearchForm->getValue('toDate');
    
    $technicianReport = $this->getTechnicianData( $fromDate, $toDate );    
    
    $html = '<table name="technician" width="100%" border="1">';
        
    foreach( $technicianReport as $key => $reportRow ){
      
      if ( $key == 0 ){
        $html .= '<thead><tr>';
        foreach ( $reportRow as $value ){
          $html .= '<th>' . $value . '</th>';
        }
        $html .= '</tr></thead><tbody>';
      }
      else{
        $html .= '<tr>';
        foreach ( $reportRow as $value ){
          $html .= '<td>' . $value . '</td>';
        }
        $html .= '</tr>';
      }
    }
    $html .= '</tbody></table>';
    
    return $html;
  }

	public function getTeamData( $fromDate, $toDate )
	{
    /*
    Team Report--------------
    */
    $teamReport = array();
    
    $teams = $this->getTeams();
      
    /* 1st row: Table headings */
    $resultRow[] = 'Team';
    $resultRow[] = 'Fixed';

    $escalations = $this->getEscalations();

    foreach ( $escalations as $escalation ){

      $resultRow[] = $escalation[ 'description' ];

    }
    $teamReport[] = $resultRow;
    
    /* Remaining: 1 row per team */

    foreach ( $teams as $team ){
      $resultRow = array();

      $resultRow[]    = $team[ 'name' ];
      $resultRow[]  = $this->getTeamFixedCount( $team[ 'teamID' ], $fromDate, $toDate );
      
      foreach ( $escalations as $escalation ){

        $resultRow[]  = $this->getTeamEscalationCount( $team[ 'teamID' ], $escalation[ 'escalationID'], $fromDate, $toDate );
      }
      $teamReport[] = $resultRow;
    }        
    return $teamReport;    
	} // end function search
  
  public function getTechnicianData( $fromDate, $toDate )
  {
    $resultRow = array();
    $technicianReport = array();
    
    /* 1st row: Table headings */
    $resultRow[] = 'Technician';
    $resultRow[] = 'Team';
    $resultRow[] = 'Fixed';

    $escalations = $this->getEscalations();

    foreach ( $escalations as $escalation ){

      $resultRow[] = $escalation[ 'description' ];
    }
    $technicianReport[] = $resultRow;

    $technicians = $this->getTechnicians();

    /* Remaining: 1 row per technician */

    foreach ( $technicians as $technician ){
      $resultRow = array();

      $resultRow[]    = $technician[ 'firstName' ] . ' ' . $technician[ 'lastName' ];

      $resultRow[]    = $technician[ 'level' ];

      $resultRow[]  = $this->getTechnicianFixedCount( $technician[ 'cns_consno' ], $fromDate, $toDate );
      
      foreach ( $escalations as $escalation ){

        $resultRow[]  = $this->getTechnicianEscalationCount( $technician[ 'cns_consno' ], $escalation[ 'escalationID'], $fromDate, $toDate );

      }
      $technicianReport[] = $resultRow;
    }        

    return $technicianReport;
    
  } // end get technician data
  
  function getEscalations(){

    $sql ="
      SELECT
        escalation.escalationID,
        escalation.description
      FROM
        escalation
      ORDER BY
        description";
        
    $ret = array();
    
    $result = $this->db->query( $sql );

    while( $row = $result->fetch_array( MYSQLI_ASSOC ) ){
      $ret[] = $row;
    }
    return $ret; 
  }	

  function getTeams(){

    $sql ="
      SELECT
        team.teamID,
        team.name
      FROM
        team
      ORDER BY
        level";
        
    $ret = array();
    
    $result = $this->db->query( $sql );

    while( $row = $result->fetch_array( MYSQLI_ASSOC ) ){
      $ret[] = $row;
    }
    return $ret; 
  }  

  function getTeamFixedCount( $teamID, $fromDate, $toDate )
  {
    $query = "
      SELECT
        COUNT(*) as count
      FROM
        problem
        JOIN consultant ON cns_consno = pro_fixed_consno
      WHERE
        consultant.teamID = $teamID
        AND pro_date_raised between '$fromDate' AND '$toDate'";
        
        
    return $this->db->query( $query )->fetch_object()->count;
  }

  function getTeamEscalationCount( $teamID, $escalationID, $fromDate, $toDate )
  {
    $query = "
      SELECT
        COUNT(*) as count
      FROM
        callactivity
        JOIN problem ON caa_problemno = pro_problemno
        JOIN consultant ON cns_consno = caa_consno
      WHERE
        consultant.teamID = $teamID
        AND callactivity.escalationID = $escalationID
        AND callactivity.escalationAcceptedFlag = 'Y'
        AND pro_date_raised between '$fromDate' AND '$toDate'";
        
        
    return $this->db->query( $query )->fetch_object()->count;
  }

  function getTechnicians(){

    $sql ="
      SELECT
        consultant.cns_consno,
        consultant.firstName,
        consultant.lastName,
        team.level
      FROM
        consultant
        JOIN team ON consultant.teamID = team.teamID
      WHERE
        consultant.activeFlag = 'Y'
        AND cns_consno NOT IN ( 1, 2, 52, 30, 44, 58, 80  )

      ORDER BY
        team.level, consultant.lastName";
        
    $ret = array();
    
    $result = $this->db->query( $sql );

    while( $row = $result->fetch_array( MYSQLI_ASSOC ) ){
      $ret[] = $row;
    }
    return $ret; 
  }  

  function getTechnicianFixedCount( $technicianID, $fromDate, $toDate )
  {
    $query = "
      SELECT
        COUNT(*) as count
      FROM
        problem
        JOIN consultant ON cns_consno = pro_fixed_consno
      WHERE
        consultant.cns_consno = $technicianID
        AND pro_date_raised between '$fromDate' AND '$toDate'";
        
        
    return $this->db->query( $query )->fetch_object()->count;
  }

  function getTechnicianEscalationCount( $technicianID, $escalationID, $fromDate, $toDate )
  {
    $query = "
      SELECT
        COUNT(*) as count
      FROM
        callactivity
        JOIN problem ON caa_problemno = pro_problemno
        JOIN consultant ON cns_consno = caa_consno
      WHERE
        consultant.cns_consno = $technicianID
        AND callactivity.escalationID = $escalationID
        AND callactivity.escalationAcceptedFlag = 'Y'
        AND pro_date_raised between '$fromDate' AND '$toDate'";
        
        
    return $this->db->query( $query )->fetch_object()->count;
  }

}// End of class
?>