<?php
/**
* Incident Analysis Report business class
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg["path_gc"]."/Business.inc.php");
require_once($cfg["path_dbe"]."/CNCMysqli.inc.php");

class BUIncidentAnalysisReport extends Business{

	function BUIncidentAnalysisReport( &$owner ){
		$this->constructor( $owner );
	}
	function constructor( &$owner ){
		parent::constructor( $owner );
		
		$this->db = new CNCMysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
	}
  function initialiseSearchForm(&$dsData)
  {
    $dsData = new DSForm($this);
    $dsData->addColumn('fromDate', DA_DATE, DA_ALLOW_NULL);
    $dsData->addColumn('toDate', DA_DATE, DA_ALLOW_NULL);
  }
	function search( $dsSearchForm )
	{
    $fromDate = $dsSearchForm->getValue( 'fromDate');
    $toDate = $dsSearchForm->getValue( 'toDate');

    $sql =
      "SELECT 
          YEAR( pro_date_raised) AS `year`,
          MONTH( pro_date_raised ) AS `month`,
          COUNT(*) AS `incidentsTotalCount`,
          SUM( pro_total_activity_duration_hours ) AS `activityTotalHours`,
          SUM( pro_working_hours ) / COUNT(*) AS `fixAverageHours`,
          SUM( pro_responded_hours ) / COUNT(*) AS `responseAverageHours`
        FROM
          problem
        WHERE
          pro_date_raised BETWEEN '$fromDate' AND '$toDate'
        GROUP BY
          YEAR( pro_date_raised), MONTH( pro_date_raised )";
    return $this->db->query( $sql );
  }
	
}//End of class
?>