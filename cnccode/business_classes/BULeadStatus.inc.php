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
    }

    function getBecameCustomerCounts()
    {

        $valueArray = array();

        $stmt = $this->db->prepare("
      SELECT
        count(*)
        FROM customer
        WHERE YEAR(cus_became_customer_date) = ?"
        );

        $stmt->bind_param('s', $year);

        $yearNow = date('Y');

        $yearsToInclude = 20;

        for ($year = $yearNow; $year >= ($yearNow - $yearsToInclude); $year--) {

            $stmt->execute();

            $stmt->bind_result($count);

            $stmt->fetch();

            $valueArray[$year] = $count;

        }

        return $valueArray;


    }

    function getDroppedCustomerCounts()
    {

        $valueArray = array();

        $stmt = $this->db->prepare("
      SELECT
        count(*)
        FROM customer
        WHERE YEAR(cus_dropped_customer_date) = ?"
        );

        $stmt->bind_param('s', $year);

        $yearNow = date('Y');

        $yearsToInclude = 20;

        for ($year = $yearNow; $year >= ($yearNow - $yearsToInclude); $year--) {

            $stmt->execute();

            $stmt->bind_result($count);

            $stmt->fetch();

            $valueArray[$year] = $count;

        }

        return $valueArray;


    }

    function getLeadsByStatus($leadStatusID, $orderAlpha = false)
    {
        if (!$orderAlpha) {
            $orderByColumn = 'cus_became_customer_date';
        } else {
            $orderByColumn = 'cus_name';
        }

        $sql = "
      select
        cus_custno as customerID,
        cus_name  as customerName
        from customer
        where cus_leadstatusno = $leadStatusID
      ORDER BY $orderByColumn";

        return $this->db->query($sql);

    }

}// End of class
?>