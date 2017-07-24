<?php
/**
 * Helpdesk report business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

class BULeadStatus extends Business
{

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->db = new CNCMysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    }

    function getBecameCustomerCounts()
    {

        $valueArray = array();

        $stmt = $this->db->prepare("
      SELECT
        count(*) AS `customerCount`
        FROM callactivity
        WHERE YEAR(caa_date) = ?"
        );

        $stmt->bind_param($year);

        $yearNow = date('Y');

        $yearsToInclude = 10;

        for ($year = $yearNow; $year--; $year >= $yearNow - $yearsToInclude) {


            $stmt->execute();

            $valueArray[$yearNow] = $stmt->customerCount;

        }

        return $valueArray;


    }


}// End of class
?>