<?php
/**
 * Call activity business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_gc"] . "/Controller.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

class BUPopulateLostCustomerField extends Business
{

    /**
     * Constructor
     * @access Public
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    function update()
    {
        $sql =
            "SELECT  cus_custno, MAX(odh_date) AS `lastOrderDate`
            FROM ordhead
            JOIN customer ON cus_custno = odh_custno
            WHERE cus_prospect = 'N'
            AND odh_type = 'C'
            AND cus_became_customer_date is not null
            GROUP BY odh_custno";

        $result = $this->db->query($sql);

        $results = $result->fetch_all(MYSQLI_ASSOC);

        foreach ($results as $rowArray) {

            $row = (object)$rowArray;
            $sql =
                "SELECT COUNT(*) AS `orderCount`
              FROM ordhead
              WHERE odh_custno = " . $row->cus_custno . "
              AND odh_type = 'C'";

            $result1 = $this->db->query($sql);
            $row1 = $result1->fetch_object();

            if (
                $row->lastOrderDate < date(
                    CONFIG_MYSQL_DATETIME,
                    strtotime('- 9 months')
                ) &&
                $row1->orderCount >= 4
            ) {

                // update table
                $sql =
                    "UPDATE
                  customer
              SET
                  cus_dropped_customer_date = '" . $row->lastOrderDate . "'
              WHERE
                  cus_custno = " . $row->cus_custno . "
                  AND cus_dropped_customer_date is null ";

                if (!$this->db->query($sql)) {
                    echo "Error";
                }

            }

        }

    }

} // End of class
?>
