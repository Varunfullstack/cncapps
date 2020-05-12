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

        $stmt = $this->db->prepare(
            "
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

        $stmt = $this->db->prepare(
            "
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
        $dbeCustomer = new DBECustomer($this);
        if (!$orderAlpha) {
            $orderByColumn = $dbeCustomer->getDBColumnName(DBECustomer::becameCustomerDate);
        } else {
            $orderByColumn = $dbeCustomer->getDBColumnName(DBECustomer::name);
        }


        $sql = "
      select
        {$dbeCustomer->getDBColumnName(DBECustomer::customerID)} as customerID,
        {$dbeCustomer->getDBColumnName(DBECustomer::name)}  as customerName
        from {$dbeCustomer->getTableName()}
        where {$dbeCustomer->getDBColumnName(DBECustomer::leadStatusId)} = {$leadStatusID}
      ORDER BY {$orderByColumn}";

        return $this->db->query($sql);

    }

}// End of class
?>