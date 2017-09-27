<?php /**
 * MonthlyReport business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");
require_once($cfg["path_dbe"] . "/DBEServiceDeskReport.inc.php");


class BUMonthlyReport extends Business
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

    function getAll()
    {
        $this->setMethodName('getAll');

        $sql =
            "SELECT
        sdr_servicedeskreportno,
        sdr_year_month
      FROM
        servicedeskreport";

        return $this->db->query($sql);
    }

    function getMonthlyReportByID($ID, &$dsResults)
    {
        $dbeServiceDeskReport = new DBEServiceDeskReport($this);
        $dbeServiceDeskReport->setPKValue($ID);
        $dbeServiceDeskReport->getRow();

        $this->getData($dbeServiceDeskReport, $dsResults);
    }

    function reportExistsByPeriod($period)
    {
        $dbeServiceDeskReport = new DBEServiceDeskReport($this);
        $dbeServiceDeskReport->setValue('yearMonth', $period);
        return ($dbeServiceDeskReport->getRowByColumn('yearMonth'));
    }

    function getIncidentCount($periodType = 'M', $period = false, $priority = false, $measure = false)
    {
        $sql =
            "SELECT
        COUNT(*) AS `count`
      FROM
        problem
      WHERE";

        if ($periodType == 'M') {
            $sql .= " DATE_FORMAT( pro_date_raised , '%Y%m')  = '$period'";
        } else {
            $sql .= " pro_date_raised BETWEEN '" . $period['start'] . "' AND '" . $period['end'] . "'";
        }

        if ($priority) {
            $sql .= " AND pro_priority = $priority";
        }

        if ($measure) {
            if ($measure == 'R') {
                $sql .= " AND pro_working_hours < 6";
            } else {
                $sql .= " AND pro_responded_hours < 1";

            }
        }
        $row = $this->db->query($sql)->fetch_row();

        return $row[0];
    }

    function getRequestsOutsideOla($yearMonth)
    {
        $sql =
            "SELECT
        pro_problemno,
        cus_name,
        pro_breach_comment
      FROM
        problem
        JOIN customer ON cus_custno = pro_custno
      WHERE
        DATE_FORMAT( pro_date_raised , '%Y%m')  = '$yearMonth'
        AND pro_responded_hours >= 1
        AND pro_priority < 3";

        return $this->db->query($sql);

    }

    function getRootCauses($yearMonth)
    {
        $sql =
            "SELECT
        rtc_desc,
        COUNT(*) as count
      FROM
        rootcause
        JOIN problem ON rtc_rootcauseno = pro_rootcauseno
      WHERE
        DATE_FORMAT( pro_date_raised , '%Y%m')  = '$yearMonth'
      GROUP BY
        pro_rootcauseno
      ORDER BY
        COUNT DESC";

        return $this->db->query($sql);

    }

    function updateMonthlyReport(&$dsServiceDeskReport)
    {
        $dbeServiceDeskReport = new DBEServiceDeskReport($this);

        $this->updateDataaccessObject($dsServiceDeskReport, $dbeServiceDeskReport);

        return $dbeServiceDeskReport->getPKValue();

    }

    function updateBreachComment($problemID, $comment)
    {
        $sql =
            "UPDATE
        problem
      SET
        pro_breach_comment = ?
      WHERE
        pro_problemno = $problemID";

        $parameters = [
            [
                'type' => 's',
                'value' => $comment
            ]
        ];

        /**
         * @var mysqli_result $result
         */
        $result = $this->db->prepareQuery($sql, $parameters);
        return $result->fetch_array();
    }

    function emailLink($link)
    {

        $buMail = new BUMail($this);

        $toEmail = CONFIG_SALES_EMAIL;

        $subject = 'Monthly Report';

        $senderEmail = CONFIG_SALES_EMAIL;

        $hdrs = array(
            'From' => $senderEmail,
            'To' => $toEmail,
            'Subject' => $subject,
            'Date' => date("r")
        );

        $body = '<A HREF="' . $link . '">Monthly Report</A>';

        $buMail->mime->setHTMLBody($body);

        $body = $buMail->mime->get();

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body,
            true
        );

        echo "SENT";

    } // send email

    function getManagerComments($yearMonth)
    {
        $sql =
            "SELECT
        pro_problemno,
        cus_name,
        pro_manager_comment
      FROM
        problem
        JOIN customer ON cus_custno = pro_custno
      WHERE
        DATE_FORMAT( pro_date_raised , '%Y%m')  = '$yearMonth'
        AND pro_manager_comment > ''";

        return $this->db->query($sql);

    }
}// End of class
?>