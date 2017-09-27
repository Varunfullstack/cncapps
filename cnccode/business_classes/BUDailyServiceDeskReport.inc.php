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
require_once($cfg ["path_bu"] . "/BUMail.inc.php");
require_once($cfg ["path_bu"] . "/BUActivity.inc.php");
require_once($cfg ["path_func"] . "/Common.inc.php");

class BUDailyServiceDeskReport extends Business
{
    private $buActivity;
    public $nonSlaLoggedToday;
    public $slaLoggedToday;
    public $fixedToday;
    public $inProgress;
    public $respondedWihinSla;
    public $respondedOverSla;
    public $totalSlaResponseHours;
    public $totalSlaFixHours;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);

        $this->buActivity = new BUActivity($this);

        $sql =
            "SELECT COUNT(*) AS count
        FROM
          problem
        WHERE
          DATE(pro_date_raised) = CURDATE()
        AND pro_priority < 4;";
        //AND pro_sla_response_hours > 0;";

        $this->slaLoggedToday = $this->db->query($sql)->fetch_object()->count;

        $sql =
            "SELECT COUNT(*) AS count
        FROM
          problem
        WHERE
          DATE(pro_date_raised) = CURDATE()
          AND pro_priority = 4;";
        //AND pro_sla_response_hours = 0;";

        $this->nonSlaLoggedToday = $this->db->query($sql)->fetch_object()->count;

        $sql =
            "SELECT COUNT(*) AS count
        FROM
          problem
        WHERE
          pro_status = 'F'
          AND DATE(pro_fixed_date) = CURDATE()
          AND pro_priority < 5;";

        $this->fixedToday = $this->db->query($sql)->fetch_object()->count;

        $sql =
            "SELECT COUNT(*) AS count
        FROM
          problem
        WHERE
          pro_status = 'F'
          AND DATE(pro_fixed_date) = CURDATE()
          AND pro_priority < 4;";
        //AND pro_sla_response_hours > 0";

        $this->slaFixedToday = $this->db->query($sql)->fetch_object()->count;

        $sql =
            "SELECT COUNT(*) AS count
        FROM
          problem
        WHERE
          pro_status = 'P'
          AND pro_priority < 5;";


        $this->inProgress = $this->db->query($sql)->fetch_object()->count;

        /*
                  AND pro_responded_hours <= pro_sla_response_hours
         changed to 1 hour as per GL
         */
        $sql =
            "SELECT COUNT(*) AS count
        FROM
          problem
        WHERE
          pro_priority < 4
          AND pro_responded_hours <= pro_sla_response_hours
          AND DATE(pro_date_raised) = CURDATE()";
        //         pro_sla_response_hours > 0

        $this->respondedWithinSla = $this->db->query($sql)->fetch_object()->count;
        /*
                  AND pro_responded_hours > pro_sla_response_hours
        */
        $sql =
            "SELECT COUNT(*) AS count
        FROM
          problem
        WHERE
          pro_priority < 4
          AND pro_responded_hours > pro_sla_response_hours
          AND DATE(pro_date_raised) = CURDATE()";
        //pro_sla_response_hours > 0

        $this->respondedOverSla = $this->db->query($sql)->fetch_object()->count;

        $sql =
            "SELECT SUM(pro_responded_hours) AS total
        FROM
          problem
        WHERE
          pro_priority < 4
          AND DATE(pro_date_raised) = CURDATE()";
//          pro_sla_response_hours > 0

        $this->totalSlaResponseHours = $this->db->query($sql)->fetch_object()->total;

        $sql =
            "SELECT SUM(pro_working_hours) AS total
        FROM
          problem
        WHERE
          pro_status = 'F'
          AND pro_priority < 4
          AND DATE(pro_fixed_date) = CURDATE()";

//          AND pro_sla_response_hours > 0

        $this->totalSlaFixHours = $this->db->query($sql)->fetch_object()->total;

        $sql =
            "
        SELECT
          hed_helpdesk_problems AS `helpDeskProblems`
        FROM headert;
        ";

        $this->problemNotes = $this->db->query($sql)->fetch_object()->helpDeskProblems;
    }

    function getPercentWithinSLA()
    {

        if ($this->slaLoggedToday) {
            return ($this->respondedWithinSla / $this->slaLoggedToday) * 100;
        } else {
            return 0;
        }
    }

    function getPercentOverSLA()
    {

        if ($this->slaLoggedToday) {
            return ($this->respondedOverSla / $this->slaLoggedToday) * 100;
        } else {
            return 0;
        }

    }

    function getAverageReponseHours()
    {
        if ($this->slaLoggedToday) {
            return $this->totalSlaResponseHours / $this->slaLoggedToday;
        } else {
            return 0;
        }
    }

    function getAverageFixHours()
    {

        if ($this->slaFixedToday) {
            return $this->totalSlaFixHours / $this->slaFixedToday;
        } else {
            return 0;
        }

    }

    function getVisitsTomorrow()
    {

        $sql =
            "SELECT 
          caa_problemno AS problemID,
          concat(firstName, ' ', lastName) AS userName,
          cus_name AS customerName,
          date_format(caa_date, '%d/%m/%Y' ) AS date,
          if( caa_starttime > '12:00', 'PM', 'AM' ) AS amPm
        FROM
          callactivity
          JOIN problem ON pro_problemno = caa_problemno
          JOIN customer ON cus_custno = pro_custno
          JOIN consultant ON cns_consno = caa_consno
        WHERE
          caa_date = date_add( CURDATE(), INTERVAL 1 DAY )
        ORDER BY
          caa_consno, caa_starttime
          ";

        return $this->db->query($sql);

    }

    function getProblemsRaisedToday()
    {
        $sql =
            "SELECT
          caa_problemno AS problemID,
          pro_date_raised AS dateRaised,
          pro_priority AS priority,
          cus_name AS customerName,
          pro_status AS status,
          SUM( TIME_TO_SEC(caa_endtime) - TIME_TO_SEC(caa_starttime) ) / 3600 AS hoursSpent
        FROM callactivity
          JOIN problem ON pro_problemno = caa_problemno
          JOIN customer ON cus_custno = pro_custno
        WHERE
          DATE(pro_date_raised) = CURDATE()
          AND pro_priority < 5
        GROUP BY caa_problemno";

        return $this->db->query($sql);
    }

    function getTopTenRootCausesToday()
    {
        $sql =
            "SELECT
            rtc_desc AS rootCause,
            COUNT( * ) AS issueCount
          FROM problem
            JOIN rootcause
              ON pro_rootcauseno = rootcause.rtc_rootcauseno
          WHERE DATE(pro_date_raised) = CURDATE()
          AND pro_priority < 5
          GROUP BY pro_rootcauseno
          ORDER BY issueCount DESC";

        return $this->db->query($sql);
    }

    function getDetailsByProblemID($problemID)
    {
        $sql =
            "SELECT
          reason as details
        FROM
          callactivity
        WHERE
          caa_problemno = $problemID
        ORDER BY
          caa_date, caa_starttime
        LIMIT 0,1
        ";

        return $this->db->query($sql)->fetch_object()->details;

    }

    function produceReport($sendReport = true)
    {
        global $cfg;

        $buMail = new BUMail($this);

        $senderEmail = CONFIG_SUPPORT_EMAIL;
        $senderName = 'CNC Support Department';

        $toEmail = 'HelpdeskUpdate@' . CONFIG_PUBLIC_DOMAIN;

        $template = new Template ($cfg["path_templates"], "remove");
        $template->set_file('page', 'DailyServiceDeskReport.inc.html');

        $template->set_var(
            array(
                'slaLoggedToday' => $this->slaLoggedToday,
                'nonSlaLoggedToday' => $this->nonSlaLoggedToday,
                'fixedToday' => $this->fixedToday,
                'inProgress' => $this->inProgress,
                'percentWithinSLA' => common_numberFormat($this->getPercentWithinSLA()),
                'percentOverSLA' => common_numberFormat($this->getPercentOverSLA()),
                'averageResponseHours' => common_numberFormat($this->getAverageReponseHours()),
                'averageFixHours' => common_numberFormat($this->getAverageFixHours()),
                'notes' => str_replace("\n", "<BR/>", $this->problemNotes),
                'reportDate' => date('d/m/Y')
            )
        );

        $template->set_block('page', 'rootCauseBlock', 'rootCauses');

        $results = $this->getTopTenRootCausesToday();

        while ($row = $results->fetch_object()) {

            $template->set_var(
                array(
                    'rootCause' => $row->rootCause,
                    'issueCount' => $row->issueCount
                )
            );

            $template->parse('rootCauses', 'rootCauseBlock', true);

        }


        $template->set_block('page', 'visitBlock', 'visits');

        $visits = $this->getVisitsTomorrow();

        $hasVisits = false;

        while ($visit = $visits->fetch_object()) {

            $hasVisits = true;

            $urlRequest = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayServiceRequest&problemID=' . $visit->problemID;

            $template->set_var(
                array(
                    'userName' => $visit->userName,
                    'customerName' => $visit->customerName,
                    'date' => $visit->date,
                    'problemID' => $visit->problemID,
                    'urlRequest' => $urlRequest,
                    'amPm' => $visit->amPm
                )
            );

            $template->parse('visits', 'visitBlock', true);

        }

        if (!$hasVisits) {
            $template->set_var('visits', 'None');
        }

        $template->set_block('page', 'priorityOneBlock', 'priorityOnes');

        $results = $this->getProblemsRaisedToday();

        while ($result = $results->fetch_object()) {

            if ($result->hoursSpent >= 2 OR $result->priority == 1) {

                if ($result->hoursSpent >= 2) {
                    $reasonForListing = round($result->hoursSpent, 0) . ' hours spent';
                } else {
                    $reasonForListing = 'URGENT!';
                }

                $urlRequest = 'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php?action=displayServiceRequest&problemID=' . $result->problemID;

                $template->set_var(
                    array(
                        'customerName' => $result->customerName,
                        'problemID' => $result->problemID,
                        'reasonForListing' => $reasonForListing,
                        'problemStatus' => $this->buActivity->problemStatusArray[$result->status],
                        'urlRequest' => $urlRequest,
                        'details' => substr(strip_tags($this->getDetailsByProblemID($result->problemID)), 0, 250)
                    )
                );
                $template->parse('priorityOnes', 'priorityOneBlock', true);
            }

        }

        $template->parse('output', 'page', true);

        $body = $template->get_var('output');

        $hdrs = array(
            'From' => $senderEmail,
            'To' => $toEmail,
            'Subject' => 'Daily Service Desk Report',
            'Date' => date("r")
        );

        echo $body;

        if ($sendReport) {
            $buMail->mime->setHTMLBody($body);

            $body = $buMail->mime->get();

            $hdrs = $buMail->mime->headers($hdrs);

            $buMail->putInQueue(
                $senderEmail,
                $toEmail,
                $hdrs,
                $body
            );
        }
    }
} // End of class
?>
