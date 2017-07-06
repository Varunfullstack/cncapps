<?php
/**
* Customer Sales report business class
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg["path_gc"]."/Business.inc.php");
require_once($cfg["path_dbe"]."/CNCMysqli.inc.php");

class BUTechnicalReport extends Business{

	/**
	* Constructor
	* @access Public
	*/
	function BUTechnicalReport(&$owner){
		$this->constructor($owner);
	}
	function constructor(&$owner){
		parent::constructor($owner);
		
		$this->db = new CNCMysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	}
	

	function getImmediateProblemFixCountByTechnician()
	{

		$sql = "
			SELECT
				cns_name AS `Technician`
				COUNT(*) AS rowCount
				
			FROM
        callactivity
	      LEFT JOIN consultant 
			    ON callactivity.caa_consno = consultant.cns_consno
			        
			WHERE
				MONTH(caa_date) = MONTH( CURDATE() )
				and YEAR(caa_date) = YEAR( CURDATE() )
			        
			GROUP BY cns_consno";
			
		return $this->db->query( $sql )->fetch_object()->report;
		
	}
}// End of class
?>