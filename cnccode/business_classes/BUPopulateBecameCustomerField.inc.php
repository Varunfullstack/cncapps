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

class BUPopulateBecameCustomerField extends Business
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

    function update()
    {
        $db = new CNCMysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $db1 = new CNCMysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $dbUpdate = new CNCMysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql =
            "SELECT  cus_custno, MIN(odh_date) AS `becameCustomerDate`
            FROM ordhead
            JOIN customer ON cus_custno = odh_custno
            WHERE cus_prospect = 'N'
            AND odh_type = 'C'
            GROUP BY odh_custno";

        $result = $db->query($sql);

        while ($row = $result->fetch_object()) {

            $sql =
                "SELECT COUNT(*) AS `orderCount`
              FROM ordhead
              WHERE odh_custno = " . $row->cus_custno . "
              AND odh_type = 'C'";

            $result1 = $db1->query($sql);
            $row1 = $result1->fetch_object();

            if (
                $row1->orderCount >= 4
            ) {

                // update table
                $sql =
                    "UPDATE
                  customer
              SET
                  cus_became_customer_date = '" . $row->becameCustomerDate . "'
              WHERE
                  cus_custno = " . $row->cus_custno;

                if (!$dbUpdate->query($sql)) {
                    echo "Error";
                }
            }

        }

    }

} // End of class
?>
