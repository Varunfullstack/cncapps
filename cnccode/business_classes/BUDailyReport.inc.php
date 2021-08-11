<?php
/**
 * management reports business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 *
 * NOTE: calls to BUMail::putInQueue with 5th parameter true sends email to users flagged SDManager
 */

use CNCLTD\DailyReport\ContactsWithOpenServiceRequests;
use CNCLTD\Utils;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

global $cfg;
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BUMail.inc.php");
require_once($cfg["path_gc"] . "/Controller.inc.php");
require_once($cfg["path_func"] . "/Common.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");
require_once($cfg["path_bu"] . "/BUHeader.inc.php");


class BUDailyReport extends Business
{

    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    function getOutstandingReportAvailableYears()
    {
        $query  = "SELECT  DISTINCT YEAR(date) AS year  FROM  sevenDayersPerformanceLog";
        $result = $this->db->query($query);
        return array_map(function ($item) { return $item['year']; }, $result->fetch_all(MYSQLI_ASSOC));
    }

    function getOutstandingReportPerformanceDataForYear($year)
    {
        $query  = "SELECT
  avg(olderThan7Days) as olderThan7DaysAvg,
  avg(target) as targetAvg,
  `month`
FROM
  (SELECT
    MONTH(`date`) AS `month`,
    olderThan7Days,
    target
  FROM
    sevenDayersPerformanceLog where year(`date`) = '$year'
  ) t
GROUP BY t.month;
";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    function getOutstandingReportPerformanceDataBetweenDates(DateTime $startDate, DateTime $endDate)
    {
        $query           = "SELECT *  FROM sevenDayersPerformanceLog where `date` between ? and ?";
        $startDateString = $startDate->format(DATE_MYSQL_DATE);
        $endDateString   = $endDate->format(DATE_MYSQL_DATE);
        $statement       = $this->db->prepare($query);
        $statement->bind_param('ss', $startDateString, $endDateString);
        $statement->execute();
        $result = $statement->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    function fixedIncidents($daysAgo, $generateLog = false)
    {
        $this->setMethodName('fixedIncidents');
        $fixedRequests = $this->getFixedRequests($daysAgo);
        $row           = $fixedRequests->fetch_row();
        $requests      = 0;
        if ($row) {
            $requests = 1;
            $template = new Template (
                EMAIL_TEMPLATE_DIR, "remove"
            );
            $template->set_file(
                'page',
                'ServiceFixedReportEmail.inc.html'
            );
            $template->set_block(
                'page',
                'requestBlock',
                'requests'
            );
            /* csv file template */
            $csvTemplate = new Template (
                EMAIL_TEMPLATE_DIR, "remove"
            );
            $csvTemplate->set_file(
                'page',
                'ServiceFixedReportEmail.inc.csv'
            );
            $csvTemplate->set_block(
                'page',
                'requestBlock',
                'requests'
            );
            $controller = new Controller(
                '', $nothing, $nothing, $nothing, $nothing
            );
            do {

                $urlRequest  = $controller->buildLink(
                    SITE_URL . '/SRActivity.php',
                    array(
                        'serviceRequestId' => $row[1],
                        "action"           => "displayActivity"
                    )
                );
                $description = substr(
                    Utils::stripEverything($row[3]),
                    0,
                    50
                );
                $template->setVar(
                    array(
                        'customer'          => $row[0],
                        'serviceRequestID'  => $row[1],
                        'fixedBy'           => $row[2],
                        'description'       => $description,
                        'durationHours'     => $row[4],
                        'timeSpentHours'    => $row[5],
                        'responseTimeHours' => $row[6],
                        'fixTimeHours'      => $row[7],
                        'contract'          => $row[8],
                        'urlRequest'        => $urlRequest
                    )
                );
                $template->parse(
                    'requests',
                    'requestBlock',
                    true
                );
                $csvTemplate->setVar(
                    array(
                        'customer'          => $row[0],
                        'serviceRequestID'  => $row[1],
                        'fixedBy'           => $row[2],
                        'description'       => $description,
                        'durationHours'     => $row[4],
                        'timeSpentHours'    => $row[5],
                        'responseTimeHours' => $row[6],
                        'fixTimeHours'      => $row[7],
                        'contract'          => $row[8]
                    )
                );
                $csvTemplate->parse(
                    'requests',
                    'requestBlock',
                    true
                );
                $requests++;
            } while ($row = $fixedRequests->fetch_row());
            $template->setVar(
                [
                    'totalRequests' => $requests - 1
                ]
            );
            $template->parse(
                'output',
                'page',
                true
            );
            $body = $template->get_var('output');
            $csvTemplate->parse(
                'output',
                'page',
                true
            );
            $csvFileString = $csvTemplate->get_var('output');
            $this->sendByEmailTo(
                'fixedyesterday@' . CONFIG_PUBLIC_DOMAIN,
                'Service requests fixed yesterday',
                $body,
                $csvFileString
            );
            echo $body;

        }
        if ($generateLog) {
            $date  = (new DateTime('yesterday'))->format(DATE_MYSQL_DATE);
            $query = "INSERT INTO sevenDayersPerformanceLog (date, totalClosedSRs) VALUES ('$date', $requests)  ON DUPLICATE KEY UPDATE totalClosedSRs = $requests;";
            $this->db->query($query);
        }
    }

    function getFixedRequests($daysAgo = 1)
    {
        $sql = "SELECT 
        cus_name AS `customer`,
        pro_problemno AS `requestID`,
        cns_name AS `fixedBy`,
        (SELECT 
          reason 
        FROM
          callactivity 
        WHERE caa_problemno = pro_problemno 
          AND caa_callacttypeno = 51
        LIMIT 1) AS `description`,
        TIMEDIFF(pro_fixed_date, pro_date_raised) AS `openHours`,
        pro_total_activity_duration_hours AS `timeSpentHours`,
        pro_responded_hours AS `responseHours`,
        pro_working_hours AS `cncOfficeHours`,
        IFNULL (itm_desc,'T&M') AS `contract`
    FROM
        problem 
        JOIN customer 
          ON cus_custno = pro_custno 
        JOIN consultant 
          ON pro_fixed_consno = cns_consno 
        LEFT JOIN custitem
          ON pro_contract_cuino = cui_cuino
        LEFT JOIN item
          ON cui_itemno = itm_itemno
        
      WHERE
        pro_status IN ( 'F', 'C' )
        AND
          DATE(pro_fixed_date) = DATE(
          DATE_SUB(NOW(), INTERVAL " . $daysAgo . " DAY)
        ) 
      ORDER BY customer,
        pro_problemno ";
        return $this->db->query($sql);
    } // end function outstandingIncidents

    function sendByEmailTo($toEmail,
                           $subject,
                           $body,
                           $attachment = false,
                           $senderEmail = CONFIG_SALES_EMAIL
    )
    {

        $buMail            = new BUMail($this);
        $hdrs              = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );
        $cssToInlineStyles = new CssToInlineStyles();
        $body              = $cssToInlineStyles->convert($body);
        $buMail->mime->setHTMLBody($body);
        if ($attachment) {
            $buMail->mime->addAttachment(
                $attachment,
                'text/plain',
                'report.csv',
                false
            );
        }
        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body        = $buMail->mime->get($mime_params);
        $hdrs        = $buMail->mime->headers($hdrs);
        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body      // to SD Managers
        );
        echo "SENT";

    } // end function

    /**
     * Customer
     * Details
     * Assigned technician
     * Engineering time spent
     * Time since logged (days)
     *
     * @param mixed $daysAgo
     * @param bool $priorityFiveOnly
     * @param bool $onScreen
     * @param bool $dashboard
     * @param bool $generateLog
     * @param null $selectedYear
     * @return mixed
     * @throws Exception
     */
    function outstandingIncidents($daysAgo,
                                  $priorityFiveOnly = false,
                                  $onScreen = false,
                                  $dashboard = false,
                                  $generateLog = false,
                                  $selectedYear = null
    )
    {

        $this->setMethodName('outstandingIncidents');
        $outstandingRequests = $this->getOutstandingRequests(
            $daysAgo,
            $priorityFiveOnly
        );
        $totalRequests       = 0;
        $openFor             = 0;
        if ($row = $outstandingRequests->fetch_row()) {

            $template = new Template (
                EMAIL_TEMPLATE_DIR, "remove"
            );
            $template->set_file(
                'page',
                'ServiceOutstandingReportEmail.inc.html'
            );
            $template->set_block(
                'page',
                'requestBlock',
                'requests'
            );
            $csvTemplate = new Template (
                EMAIL_TEMPLATE_DIR, "remove"
            );
            $csvTemplate->set_file(
                'page',
                'ServiceOutstandingReportEmail.inc.csv'
            );
            $csvTemplate->set_block(
                'page',
                'requestBlock',
                'requests'
            );
            $controller = new Controller(
                '', $nothing, $nothing, $nothing, $nothing
            );
            $buHeader   = new BUHeader($this);
            $dsHeader   = new DataSet($this);
            $buHeader->getHeader($dsHeader);
            do {
                $urlRequest = $controller->buildLink(
                    SITE_URL . '/SRActivity.php',
                    [
                        "action"           => "displayActivity",
                        'serviceRequestId' => $row[1]
                    ]
                );
                $template->setVar(
                    array(
                        'customer'            => $row[0],
                        'serviceRequestID'    => $row[1],
                        'assignedTo'          => $row[2],
                        'description'         => substr(
                            Utils::stripEverything($row[3]),
                            0,
                            50
                        ),
                        'durationHours'       => $row[4],
                        'timeSpentHours'      => $row[5],
                        'lastUpdatedDate'     => $row[6] ? Controller::dateYMDtoDMY($row[6]) : null,
                        'lastUpdatedDateSort' => $row[6],
                        'priority'            => $row[7],
                        'teamName'            => $row[8],
                        'awaiting'            => $row[10] == 'I' ? 'Not Started' : ($row[9] == 'Y' ? 'Customer' : 'CNC'),
                        'urlRequest'          => $urlRequest,
                        'rowClass'            => $row[4] >= $dsHeader->getValue(
                            DBEHeader::sevenDayerRedDays
                        ) ? 'red-row' : ($row[4] >= $dsHeader->getValue(
                            DBEHeader::sevenDayerAmberDays
                        ) ? 'amber-row' : ''),
                        'amberThreshold'      => $dsHeader->getValue(DBEHeader::sevenDayerAmberDays),
                        'redThreshold'        => $dsHeader->getValue(DBEHeader::sevenDayerRedDays)
                    )
                );
                $csvTemplate->setVar(
                    array(
                        'customer'         => $row[0],
                        'serviceRequestID' => $row[1],
                        'assignedTo'       => $row[2],
                        'description'      => str_replace(
                            ',',
                            '',
                            substr(
                                Utils::stripEverything($row[3]),
                                0,
                                50
                            )
                        ),
                        'durationHours'    => $row[4],
                        'timeSpentHours'   => $row[5],
                        'lastUpdatedDate'  => $row[6],
                        'priority'         => $row[7],
                        'teamName'         => $row[8],
                        'awaiting'         => $row[10] == 'I' ? 'Not Started' : ($row[9] == 'Y' ? 'Customer' : 'CNC'),
                    )
                );
                $template->parse(
                    'requests',
                    'requestBlock',
                    true
                );
                $csvTemplate->parse(
                    'requests',
                    'requestBlock',
                    true
                );
                $totalRequests++;
                $openFor += $row[4];

            } while ($row = $outstandingRequests->fetch_row());
            $csvTemplate->parse(
                'output',
                'page',
                true
            );
            $csvFile     = $csvTemplate->get_var('output');
            $select      = "";
            $performance = "";
            if ($dashboard) {

                $select = '<div style="width: 150px;display: inline-block">Select Days:</div><select onchange="changeDays()">';
                foreach ([0, 1, 2, 3, 4, 5, 6, 7] as $day) {

                    $selected = $daysAgo == $day ? 'selected' : '';
                    $select   .= '<option ' . $selected . ' value="' . $day . '">' . $day . '</option>';

                }
                $select       .= '</select>';
                $query        = "SELECT  DISTINCT YEAR(date) AS YEAR  FROM  sevenDayersPerformanceLog";
                $result       = $this->db->query($query);
                $data         = $result->fetch_all(MYSQLI_ASSOC);
                $selectedYear = $selectedYear ? $selectedYear : (new DateTime())->format('Y');
                $performance  = '<script> function yearChanged(){
                 let url = new URL(location.href);
                 url.searchParams.set("selectedYear", this.event.target.value);
                 location.href = url.toString();
} </script>   
                <select name="searchYear" id="yearSelector" onchange="yearChanged()">
                   ';
                foreach ($data as $datum) {
                    $performance .= '<option value="' . $datum['YEAR'] . '"';
                    if ($datum['YEAR'] == $selectedYear) {
                        $performance .= " selected='selected' ";
                    }
                    $performance .= '>' . $datum['YEAR'] . '</option>';
                }
                $performance .= '
                </select>
<table id="team-performance">
    <thead>
    <tr>
        <th>&nbsp;</th>
        <th>Jan</th>
        <th>Feb</th>
        <th>Mar</th>
        <th>Apr</th>
        <th>May</th>
        <th>Jun</th>
        <th>Jul</th>
        <th>Aug</th>
        <th>Sep</th>
        <th>Oct</th>
        <th>Nov</th>
        <th>Dec</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <th>Average Number of 7 Dayers</th>
        <td class="success">98.5</td>
        <td class="success">98.4</td>
        <td class="success">98.2</td>
        <td class="fail">97.7</td>
        <td class="success">98.7</td>
        <td class="success">98.2</td>
        <td class="fail">97.9</td>
        <td class="success">98.2</td>
        <td class="success">N/A</td>
        <td class="success">N/A</td>
        <td class="success">N/A</td>
        <td class="success">N/A</td>
    </tr>
    <tr>
     <th>Target</th>
        <td class="success">98.5</td>
        <td class="success">98.4</td>
        <td class="success">98.2</td>
        <td class="fail">97.7</td>
        <td class="success">98.7</td>
        <td class="success">98.2</td>
        <td class="fail">97.9</td>
        <td class="success">98.2</td>
        <td class="success">N/A</td>
        <td class="success">N/A</td>
        <td class="success">N/A</td>
        <td class="success">N/A</td>
        </tr>
    </tbody>
</table><br>';

            }
            $avgDays = $totalRequests ? number_format(
                $openFor / $totalRequests,
                1
            ) : null;
            if ($generateLog) {
                $date = (new DateTime('yesterday'))->format(DATE_MYSQL_DATE);
                // we don't have an entry for today ..so create it
                $query = "insert into sevenDayersPerformanceLog(date, olderThan7Days, averageAgeDays, target) values ('$date', $totalRequests,$avgDays," . $dsHeader->getValue(
                        DBEHeader::sevenDayerTarget
                    ) . ") on duplicate key update olderThan7Days = $totalRequests, averageAgeDays = $avgDays, target =  " . $dsHeader->getValue(
                        DBEHeader::sevenDayerTarget
                    );
                $this->db->query($query);

            }
            $avgDays = $totalRequests ? number_format($openFor / $totalRequests, 1) : null;
            $template->setVar(
                array(
                    'daysAgo'            => $daysAgo,
                    'totalRequests'      => $totalRequests,
                    'avgDays'            => $avgDays === null ? 'N/A' : $avgDays,
                    'selectDaysSelector' => $select,
                    'performance'        => $performance,
                    'isDashboard'        => $dashboard ? 'true' : 'false'
                )
            );
            if (!$onScreen) {
                if ($priorityFiveOnly) {
                    $subject = 'Priority 5';
                } else {
                    $subject = 'Priority 1-4';
                }
                $subject .= ' SRs Outstanding For ' . $daysAgo . ' Days';
                $template->parse(
                    'output',
                    'page',
                    true
                );
                $body = $template->get_var('output');
                $this->sendByEmailTo(
                    'sropenfordays@' . CONFIG_PUBLIC_DOMAIN,
                    $subject,
                    $body,
                    $csvFile
                );
            } else {
                $csvLink = '';
                if (!$dashboard) {
                    $csvLink = '<a href="data:text/csv;charset=utf-8;base64,' . base64_encode(
                            $csvFile
                        ) . '" download="outstanding.csv">Download CSV</a>';
                }
                $template->setVar(
                    [
                        'csvLink' => $csvLink
                    ]
                );
                $template->parse(
                    'output',
                    'page',
                    true
                );
                $body = $template->get_var('output');
            }
            if ($dashboard) {
                return $body;
            } else {
                echo $body;
            }
        }

    } // end function

    function getOutstandingRequests($daysAgo = 1,
                                    $priorityFiveOnly = false,
                                    $hd = true,
                                    $es = true,
                                    $sp = true,
                                    $p = true
    )
    {
        $sql = "SELECT 
        cus_name AS `customer`,
        pro_problemno AS `requestID`,
        cns_name AS `assignedTo`,
        emailSubjectSummary AS `description`,
        DATEDIFF(NOW(),pro_date_raised ) AS `openDays`,
        pro_total_activity_duration_hours AS `timeSpentHours`,
        last.caa_date as lastUpdatedDate,
        pro_priority as `priority`,
        team.name AS teamName,
        pro_awaiting_customer_response_flag,
        pro_status,
        pro_queue_no queueNo
      FROM
        problem 
        JOIN customer 
          ON cus_custno = pro_custno 
        LEFT JOIN consultant 
          ON pro_consno = cns_consno 
        LEFT JOIN team
          ON team.teamID = consultant.teamID
        JOIN callactivity `last`
            ON last.caa_problemno = pro_problemno AND last.caa_callactivityno =
              (
              SELECT
                MAX( ca.caa_callactivityno )
              FROM callactivity ca
              WHERE ca.caa_problemno = pro_problemno
              AND ca.caa_callacttypeno <> " . CONFIG_OPERATIONAL_ACTIVITY_TYPE_ID . "
            )
        WHERE
          DATE(pro_date_raised) <= DATE(
          DATE_SUB(NOW(), INTERVAL $daysAgo DAY)) 
          AND pro_status NOT IN ('F', 'C')";
        if ($priorityFiveOnly) {
            $sql .= " AND pro_priority = 5";
        } else {
            $sql .= " AND pro_priority < 5";
        }
        if (!$hd) $sql .= " AND pro_queue_no <>1   ";
        if (!$es) $sql .= " AND pro_queue_no <>2   ";
        if (!$sp) $sql .= " AND pro_queue_no <>3   ";
        if (!$p) $sql .= " AND pro_queue_no <>5  ";
        $sql .= "      ORDER BY customer,
        pro_problemno";
        return $this->db->query($sql);
    }

    function focActivities($daysAgo)
    {

        $this->setMethodName('focActivities');
        $activities = $this->getFocActivities($daysAgo);
        if ($row = $activities->fetch_row()) {

            $template = new Template (
                EMAIL_TEMPLATE_DIR, "remove"
            );
            $template->set_file(
                'page',
                'ServiceFocReportEmail.inc.html'
            );
            $template->set_block(
                'page',
                'activityBlock',
                'activities'
            );
            $controller = new Controller(
                '', $nothing, $nothing, $nothing, $nothing
            );
            do {

                $urlRequest  = $controller->buildLink(
                    SITE_URL . '/SRActivity.php',
                    array(
                        'serviceRequestId' => $row[1],
                        'action'           => 'displayActivity'
                    )
                );
                $urlActivity = $controller->buildLink(
                    SITE_URL . '/SRActivity.php',
                    array(
                        'callActivityID' => $row[2],
                        'action'         => 'displayActivity'
                    )
                );
                $template->setVar(
                    array(
                        'customer'         => $row[0],
                        'serviceRequestID' => $row[1],
                        'activityID'       => $row[2],
                        'technician'       => $row[3],
                        'hours'            => number_format(
                            $row[4],
                            2
                        ),
                        'contract'         => $row[5],
                        'category'         => $row[6],
                        'urlRequest'       => $urlRequest,
                        'urlActivity'      => $urlActivity
                    )
                );
                $template->parse(
                    'activities',
                    'activityBlock',
                    true
                );

            } while ($row = $activities->fetch_row());
            $template->parse(
                'output',
                'page',
                true
            );
            $body = $template->get_var('output');
            $this->sendByEmailTo(
                'focyesterday@' . CONFIG_PUBLIC_DOMAIN,
                'FOC activities logged yesterday',
                $body
            );
            echo $body;

        }

    }

    function getFocActivities($daysAgo = 1)
    {
        $sql = "SELECT
          cus_name AS `customer`,
          caa_problemno AS `requestID`,
          caa_callactivityno AS `activityID`,
          cns_name AS `engineer`,
          ( TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC( caa_starttime ) ) / 3600 AS `hours`,
          ci.itm_desc AS `contract`,
          callacttype.cat_desc AS `category`
          
        FROM
          callactivity 
          JOIN callacttype ON caa_callacttypeno = cat_callacttypeno
          JOIN item cat ON cat.itm_itemno = cat_itemno
          JOIN problem ON caa_problemno = pro_problemno
          LEFT JOIN custitem ON cui_cuino = pro_contract_cuino
          LEFT JOIN item ci ON ci.itm_itemno = cui_itemno
          JOIN customer ON cus_custno = pro_custno
          JOIN consultant ON caa_consno = cns_consno
        WHERE
          DATE(caa_date) = DATE( DATE_SUB( NOW(), INTERVAL " . $daysAgo . " DAY ))
          AND cat.itm_sstk_price = 0 
          AND caa_starttime <> caa_endtime
          AND travelFlag = 'N'
        HAVING
          hours >= .5
        ORDER BY
          cus_name, caa_problemno";
        return $this->db->query($sql);
    }

    function prepayOverValue($daysAgo)
    {
        $this->setMethodName('focActivities');
        $activities = $this->getPrePayActivitiesOverValue($daysAgo);
        if ($row = $activities->fetch_row()) {

            $template = new Template (
                EMAIL_TEMPLATE_DIR, "remove"
            );
            $template->set_file(
                'page',
                'ServicePrepayOverValueReportEmail.inc.html'
            );
            $template->set_block(
                'page',
                'activityBlock',
                'activities'
            );
            $controller = new Controller(
                '', $nothing, $nothing, $nothing, $nothing
            );
            do {

                $urlRequest  = $controller->buildLink(
                    SITE_URL . '/SRActivity.php',
                    array(
                        'serviceRequestId' => $row[1],
                    )
                );
                $urlActivity = $controller->buildLink(
                    SITE_URL . '/SRActivity.php',
                    array(
                        'callActivityID' => $row[2],
                        'action'         => 'displayActivity'
                    )
                );
                $template->setVar(
                    array(
                        'customer'         => $row[0],
                        'serviceRequestID' => $row[1],
                        'activityID'       => $row[2],
                        'value'            => number_format(
                            $row[3],
                            2
                        ),
                        'technician'       => $row[4],
                        'urlRequest'       => $urlRequest,
                        'urlActivity'      => $urlActivity,
                        'contract'         => $row[8]
                    )
                );
                $template->parse(
                    'activities',
                    'activityBlock',
                    true
                );

            } while ($row = $activities->fetch_row());
            $template->parse(
                'output',
                'page',
                true
            );
            $body = $template->get_var('output');
            echo $body;
            $this->sendByEmailTo(
                CONFIG_SALES_MANAGER_EMAIL,
                'Pre-pay activities logged yesterday over GBP 100 in value',
                $body
            );

        }


    }

    function getPrePayActivitiesOverValue($daysAgo = 1)
    {
        $sql = "SELECT
          cus_name AS `customer`,
          caa_problemno AS `requestID`,
          caa_callactivityno AS `activityID`,
          ( ( TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC( caa_starttime ) ) / 3600 ) * 70 AS Cost,
           cns_name AS `engineer`

        FROM
          callactivity 
          JOIN callacttype ON caa_callacttypeno = cat_callacttypeno
          JOIN item `at` ON at.itm_itemno = callacttype.cat_itemno
          JOIN problem ON caa_problemno = pro_problemno
          JOIN custitem ON cui_cuino = pro_contract_cuino
          JOIN item ON item.itm_itemno = cui_itemno
          JOIN customer ON cus_custno = pro_custno
          JOIN consultant ON caa_consno = cns_consno

        WHERE
          DATE(caa_date) = DATE( DATE_SUB( NOW(), INTERVAL " . $daysAgo . " DAY ) )
          AND item.itm_itemtypeno = 57 
          AND at.itm_sstk_price > 0
          AND travelFlag = 'N'
        HAVING
         Cost >= 100
        ORDER BY
          cus_name, caa_problemno";
        return $this->db->query($sql);
    }

    public function p5IncidentsWithoutSalesOrders()
    {
        $this->setMethodName('outstandingIncidents');
        $outstandingRequests = $this->getP5IncidentsWithoutSalesOrders();
        if ($row = $outstandingRequests->fetch_row()) {

            $template = new Template (
                EMAIL_TEMPLATE_DIR, "remove"
            );
            $template->set_file(
                'page',
                'P5NoSalesReportEmail.inc.html'
            );
            $template->set_block(
                'page',
                'requestBlock',
                'requests'
            );
            $csvTemplate = new Template (
                EMAIL_TEMPLATE_DIR, "remove"
            );
            $csvTemplate->set_file(
                'page',
                'P5NoSalesReportEmail.inc.csv'
            );
            $csvTemplate->set_block(
                'page',
                'requestBlock',
                'requests'
            );
            $controller = new Controller(
                '', $nothing, $nothing, $nothing, $nothing
            );
            $title      = "P5 SRs with no SO";
            do {
                $urlRequest = $controller->buildLink(
                    SITE_URL . '/SRActivity.php',
                    array(
                        'serviceRequestId' => $row[1],
                        "action"           => "displayActivity"
                    )
                );
                $template->setVar(
                    array(
                        'customer'         => $row[0],
                        'serviceRequestID' => $row[1],
                        'assignedTo'       => $row[2],
                        'description'      => substr(
                            Utils::stripEverything($row[3]),
                            0,
                            50
                        ),
                        'durationHours'    => $row[4],
                        'urlRequest'       => $urlRequest,
                        'title'            => $title
                    )
                );
                $csvTemplate->setVar(
                    array(
                        'customer'         => $row[0],
                        'serviceRequestID' => $row[1],
                        'assignedTo'       => $row[2],
                        'description'      => str_replace(
                            ',',
                            '',
                            substr(
                                Utils::stripEverything($row[3]),
                                0,
                                50
                            )
                        ),
                        'durationHours'    => $row[4],
                    )
                );
                $template->parse(
                    'requests',
                    'requestBlock',
                    true
                );
                $csvTemplate->parse(
                    'requests',
                    'requestBlock',
                    true
                );

            } while ($row = $outstandingRequests->fetch_row());
            $template->parse(
                'output',
                'page',
                true
            );
            $body = $template->get_var('output');
            $csvTemplate->parse(
                'output',
                'page',
                true
            );
            $csvFile = $csvTemplate->get_var('output');
            $subject = $title;
            $this->sendByEmailTo(
                ' nosalesorder@' . CONFIG_PUBLIC_DOMAIN,
                $subject,
                $body,
                $csvFile
            );
            echo $body;

        }
    }

    private function getP5IncidentsWithoutSalesOrders()
    {
        $sql = "SELECT
                cus_name AS customer,
                pro_problemno AS requestID,
                cns_name AS assignedTo,
                reason,
                DATEDIFF(NOW(), pro_date_raised) AS openDays
                FROM
                problem
                LEFT JOIN customer
                ON cus_custno = pro_custno
                LEFT JOIN consultant
                ON pro_consno = cns_consno
                LEFT JOIN callactivity
                ON caa_problemno = pro_problemno AND caa_callacttypeno = 51
                WHERE pro_priority = 5
                AND pro_status != 'C'
                AND pro_linked_ordno is null
                AND pro_custno != 282
                AND pro_queue_no !=4
                AND pro_queue_no !=7";
        return $this->db->query($sql);
    }

    public function p5WithSalesOrderAndContractAssigned()
    {
        $this->setMethodName('outstandingIncidents');
        $outstandingRequests = $this->getP5WithSalesOrdersAndContractAssigned();
        if ($row = $outstandingRequests->fetch_row()) {

            $template = new Template (
                EMAIL_TEMPLATE_DIR, "remove"
            );
            $template->set_file(
                'page',
                'P5WithSalesAndContractReportEmail.inc.html'
            );
            $template->set_block(
                'page',
                'requestBlock',
                'requests'
            );
            $csvTemplate = new Template (
                EMAIL_TEMPLATE_DIR, "remove"
            );
            $csvTemplate->set_file(
                'page',
                'P5WithSalesAndContractReportEmail.inc.csv'
            );
            $csvTemplate->set_block(
                'page',
                'requestBlock',
                'requests'
            );
            $title      = "P5 SRs with SO and not T&M";
            $controller = new Controller(
                '', $nothing, $nothing, $nothing, $nothing
            );
            do {
                $urlRequest = $controller->buildLink(
                    SITE_URL . '/SRActivity.php',
                    array(
                        'serviceRequestId' => $row[1],
                        "action"           => "displayActivity"
                    )
                );
                $template->setVar(
                    array(
                        'customer'         => $row[0],
                        'serviceRequestID' => $row[1],
                        'description'      => substr(
                            Utils::stripEverything($row[2]),
                            0,
                            50
                        ),
                        'urlRequest'       => $urlRequest,
                        'title'            => $title
                    )
                );
                $csvTemplate->setVar(
                    array(
                        'customer'         => $row[0],
                        'serviceRequestID' => $row[1],
                        'description'      => str_replace(
                            ',',
                            '',
                            substr(
                                Utils::stripEverything($row[2]),
                                0,
                                50
                            )
                        ),
                    )
                );
                $template->parse(
                    'requests',
                    'requestBlock',
                    true
                );
                $csvTemplate->parse(
                    'requests',
                    'requestBlock',
                    true
                );

            } while ($row = $outstandingRequests->fetch_row());
            $template->parse(
                'output',
                'page',
                true
            );
            $body = $template->get_var('output');
            $csvTemplate->parse(
                'output',
                'page',
                true
            );
            $csvFile = $csvTemplate->get_var('output');
            $subject = $title;
            $this->sendByEmailTo(
                ' nosalesorder@' . CONFIG_PUBLIC_DOMAIN,
                $subject,
                $body,
                $csvFile
            );
            echo $body;

        }
    }

    private function getP5WithSalesOrdersAndContractAssigned()
    {
        $sql = "SELECT 
  cus_name AS `customer`,
  pro_problemno AS `requestID`,
  reason
FROM
  problem 
  LEFT JOIN customer 
    ON cus_custno = pro_custno
  LEFT JOIN consultant 
    ON pro_consno = cns_consno 
  LEFT JOIN callactivity 
    ON caa_problemno = pro_problemno 
    AND caa_callacttypeno = 51 
WHERE pro_priority = 5 
  AND pro_status = 'F' 
  AND pro_linked_ordno IS NOT NULL 
  AND pro_linked_ordno <> 0 
  AND pro_custno != 282 
  AND pro_contract_cuino <> 0 
  AND pro_contract_cuino IS NOT NULL";
        return $this->db->query($sql);
    }

    public function contactOpenSRReport($onScreen = false)
    {
        $this->setMethodName('contactOpenSRReport');
        $contactOpenSRReportData = $this->getContactOpenSRReportData();
        global $twig;
        foreach ($contactOpenSRReportData as $contactWithOpenServiceRequests) {

            $body    = $twig->render(
                '@customerFacing/OpenServiceRequestReport/OpenServiceRequestReport.html.twig',
                [
                    "data"     => $contactWithOpenServiceRequests,
                    "onScreen" => $onScreen
                ]
            );
            $subject = "Open Service Request Report - " . (new DateTime())->format('Y-m-d');
            if (!$onScreen) {
                $this->sendByEmailTo(
                    $contactWithOpenServiceRequests->getContactEmail(),
                    $subject,
                    $body,
                    null,
                    'customerReports@' . CONFIG_PUBLIC_DOMAIN
                );
            }
            echo '<br><div>Sent to Email ' . $contactWithOpenServiceRequests->getContactEmail() . '</div><br>';
            echo $body;
        }
    }

    /**
     * @return ContactsWithOpenServiceRequests
     * @throws Exception
     */
    private function getContactOpenSRReportData()
    {
        $sql    = "SELECT
problem.pro_problemno AS id,
CONCAT_WS(
' ',
reporter.con_first_name,
reporter.con_last_name
) AS raisedBy,
pro_date_raised AS raisedOn,
IF(
problem.pro_awaiting_customer_response_flag = 'Y',
'On Hold',
'In Progress'
) AS status,
problem.emailSubjectSummary AS details,
contact.con_first_name AS contactName,
contact.con_email AS contactEmail,
contact.con_contno AS contactID,
customer.cus_name AS customerName
FROM
problem
INNER JOIN contact
ON contact.con_custno = problem.pro_custno
AND contact.con_mailflag11 = 'Y'
AND contact.active = '1'
LEFT JOIN callactivity
ON callactivity.caa_problemno = problem.pro_problemno
AND callactivity.caa_callacttypeno = 51
LEFT JOIN contact AS reporter
ON problem.pro_contno = reporter.con_contno
LEFT JOIN customer ON problem.pro_custno = customer.cus_custno
WHERE problem.pro_status <> 'C'
AND problem.pro_status <> 'F'
AND problem.pro_hide_from_customer_flag <> 'Y'
AND problem.pro_priority >= 1 AND problem.pro_priority <= 4
AND (contact.supportLevel = 'Main' OR reporter.con_contno = contact.con_contno )
ORDER BY pro_date_raised";
        $result = $this->db->query($sql);
        if (!$result) {
            throw  new Exception($this->db->error);
        }
        $contactOpenSRReportData = new ContactsWithOpenServiceRequests();
        while ($row = $result->fetch_assoc()) {
            $contactOpenSRReportData->add(
                $row['contactID'],
                $row['contactName'],
                $row['contactEmail'],
                $row['customerName'],
                $row['id'],
                $row['raisedBy'],
                $row['raisedOn'],
                $row['status'],
                $row['details']
            );
        }
        return $contactOpenSRReportData;

    }
}