<?php
/**
 * Service desk report business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_gc"] . "/Controller.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");
require_once($cfg ["path_func"] . "/Common.inc.php");

class BUHelpdeskManagerDashboard extends Business
{

    private $db; // database connection

    public $customerID = false;

    public $startDate;

    public $endDate;

    public $period;

    private $startDateOneYearAgo;

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

    function setPeriod($period)
    {
        $this->year = substr($period, 0, 4);
        $this->month = substr($period, 5, 2);
        $this->period = $period;

        $endDateUnix = strtotime($period . 'last day next month');
        $startDateUnix = strtotime($period);

        $this->startDate = date('Y-m-d', $startDateUnix);

        $this->endDate = date('Y-m-d', $endDateUnix);
    }

    function getCountIncidents($parameters)
    {
        $sql =
            "SELECT
          COUNT(DISTINCT pro_problemno) AS count,
          GROUP_CONCAT( pro_problemno ) AS idList
        FROM
          problem";

        $sql .= $this->buildWhereClause($parameters);

        return $this->db->query($sql)->fetch_object();

    }

    function getTotalResponseTime($parameters)
    {
        $sql =
            "SELECT
          SUM( pro_responded_hours ) AS hours
        FROM
          problem";

        $sql .= $this->buildWhereClause($parameters);

        return $this->db->query($sql)->fetch_object()->hours;
    }

    function getTotalFixTime($parameters)
    {
        $sql =
            "SELECT
          SUM( pro_working_hours ) AS hours
        FROM
          problem";

        $sql .= $this->buildWhereClause($parameters);

        return $this->db->query($sql)->fetch_object()->hours;
    }

    function buildWhereClause(
        $parameters
    )
    {
        $whereString = ' WHERE 1=1';

        if ($parameters['respondedWithinSla'] == 'Y') {
            $whereString .= " AND pro_responded_hours <= 1";
        }

        if ($parameters['withinSla'] == 'Y') {
            $whereString .= " AND(
                                 ( pro_working_hours <= 0.75 AND pro_status='I' ) OR
                                 ( pro_working_hours / 6 <= 0.75 AND pro_status='P' )
                              )";
        }

        if ($parameters['approachingSla'] == 'Y') {
            $whereString .= " AND(
                                ( pro_working_hours > 0.75 AND  pro_status='I' AND pro_working_hours <= 1 ) OR
                                ( pro_working_hours / 6 > 0.75 AND pro_status='P' AND pro_working_hours <= 6  )
                              )";
        }

        if ($parameters['exceededSla'] == 'Y') {
            $whereString .= " AND(
                                ( pro_working_hours > 1 AND  pro_status='I' ) OR
                                ( pro_working_hours > 6 AND pro_status='P'  )
                              )";
        }

        if ($parameters['notPriority4']) {

            $whereString .= " AND pro_priority < 4";

        }

        if ($parameters['priority']) {

            $whereString .= " AND pro_priority = " . $parameters['priority'];

        }

        if (isset($parameters['underContract'])) {
            if ($parameters['underContract'] == 'Y') {
                $whereString .= " AND pro_contract_cuino <> 0";
            } else {
                $whereString .= " AND pro_contract_cuino = 0";
            }
        }

        if (isset($parameters['notFixed'])) {
            if ($parameters['notFixed'] == 'Y') {
                $whereString .= " AND pro_status IN ('I','P')";
            }
        }

        if (isset($parameters['fixed'])) {
            if ($parameters['fixed'] == 'Y') {
                $whereString .= " AND pro_status IN ('C','F')";
            }
        }

        if (isset($parameters['completed'])) {
            if ($parameters['completed'] == 'Y') {
                $whereString .= " AND pro_status = 'C'";
            }
        }

        if (isset($parameters['inLastMonth'])) {
            if ($parameters['inLastMonth'] == 'Y') {
                $whereString .= " AND DATE(pro_date_raised) >= DATE_SUB( NOW(), INTERVAL 1 MONTH )";
            }
        }

        return $whereString;
    }

    function getPercent($fraction, $total)
    {
        if ($total > 0) {
            return number_format(($fraction / $total) * 100, 2);
        } else {
            return '';
        }

    }

    function getAve($total, $divisor)
    {
        if ($divisor > 0) {
            return number_format($total / $divisor, 2);
        } else {
            return '';
        }

    }
} // End of class
?>
