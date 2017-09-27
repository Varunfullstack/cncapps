<?php
/**
 * Customer Sales report business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

class BUTechnicalReport extends Business
{

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    function getImmediateProblemFixCountByTechnician()
    {

        $sql = "
			SELECT
				cns_name AS `Technician`,
				COUNT(*) AS rowCount
				
			FROM
        callactivity
	      LEFT JOIN consultant 
			    ON callactivity.caa_consno = consultant.cns_consno
			        
			WHERE
				MONTH(caa_date) = MONTH( CURDATE() )
				AND YEAR(caa_date) = YEAR( CURDATE() )
			        
			GROUP BY cns_consno";

        return $this->db->query($sql)->fetch_object()->report;
    }
}// End of class
?>