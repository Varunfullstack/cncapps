<?php
/**
 * management reports business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 *
 * NOTE: calls to BUMail::putInQueue with 5th parameter true sends email to users flagged SDManager
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BUMail.inc.php");
require_once($cfg["path_gc"] . "/Controller.inc.php");
require_once($cfg["path_func"] . "/Common.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

class BUDailyReport extends Business
{

    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    function fixedIncidents($daysAgo)
    {
        $this->setMethodName('fixedIncidents');

        $fixedRequests = $this->getFixedRequests($daysAgo);
        $row = $fixedRequests->fetch_row();
        $requests = 0;
        if ($row) {
            $requests = 1;
            $template = new Template (
                EMAIL_TEMPLATE_DIR,
                "remove"
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
                EMAIL_TEMPLATE_DIR,
                "remove"
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

                $urlRequest =
                    $controller->buildLink(
                        'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php',
                        array(
                            'problemID' => $row[1],
                            'action'    => 'displayLastActivity'
                        )
                    );

                $description = substr(
                    common_stripEverything($row[3]),
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
                    'totalRequests' => $requests
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

    }

    /**
     * Customer
     * Incident No (Link)
     * Details
     * Assigned technician
     * Engineering time spent
     * Time since logged (days)
     *
     * @param mixed $daysAgo
     * @param bool $priorityFiveOnly
     * @param bool $onScreen
     * @param bool $dashboard
     */
    function outstandingIncidents($daysAgo,
                                  $priorityFiveOnly = false,
                                  $onScreen = false,
                                  $dashboard = false
    )
    {

        $this->setMethodName('outstandingIncidents');

        $outstandingRequests = $this->getOustandingRequests(
            $daysAgo,
            $priorityFiveOnly
        );
        $totalRequests = 0;
        $openFor = 0;
        if ($row = $outstandingRequests->fetch_row()) {

            $template = new Template (
                EMAIL_TEMPLATE_DIR,
                "remove"
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
                EMAIL_TEMPLATE_DIR,
                "remove"
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

            do {
                $urlRequest =
                    $controller->buildLink(
                        'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php',
                        array(
                            'problemID' => $row[1],
                            'action'    => 'displayLastActivity'
                        )
                    );

                $template->setVar(
                    array(
                        'customer'         => $row[0],
                        'serviceRequestID' => $row[1],
                        'assignedTo'       => $row[2],
                        'description'      => substr(
                            common_stripEverything($row[3]),
                            0,
                            50
                        ),
                        'durationHours'    => $row[4],
                        'timeSpentHours'   => $row[5],
                        'lastUpdatedDate'  => $row[6],
                        'priority'         => $row[7],
                        'teamName'         => $row[8],
                        'awaiting'         => $row[10] == 'I' ? 'Not Started' : ($row[9] == 'Y' ? 'Customer' : 'CNC'),
                        'urlRequest'       => $urlRequest
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
                                common_stripEverything($row[3]),
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
            $csvFile = $csvTemplate->get_var('output');
            $select = "";
            if ($dashboard) {

                $select = '<span>Select Days:</span><select onchange="changeDays()">';

                foreach ([0, 1, 2, 3, 4, 5, 6, 7] as $day) {

                    $selected = $daysAgo == $day ? 'selected' : '';

                    $select .= '<option ' . $selected . ' value="' . $day . '">' . $day . '</option>';

                }
                $select .= '</select>';


            }

            $template->setVar(
                array(
                    'daysAgo'            => $daysAgo,
                    'totalRequests'      => $totalRequests,
                    'avgDays'            => $totalRequests ? number_format(
                        $openFor / $totalRequests,
                        1
                    ) : 'N/A',
                    'selectDaysSelector' => $select,
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

    } // end function outstandingIncidents

    function focActivities($daysAgo)
    {

        $this->setMethodName('focActivities');

        $activities = $this->getFocActivities($daysAgo);

        if ($row = $activities->fetch_row()) {

            $template = new Template (
                EMAIL_TEMPLATE_DIR,
                "remove"
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

                $urlRequest =
                    $controller->buildLink(
                        'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php',
                        array(
                            'problemID' => $row[1],
                            'action'    => 'displayLastActivity'
                        )
                    );

                $urlActivity =
                    $controller->buildLink(
                        'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php',
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

    } // end function

    function prepayOverValue($daysAgo)
    {
        $this->setMethodName('focActivities');

        $activities = $this->getPrePayActivitiesOverValue($daysAgo);

        if ($row = $activities->fetch_row()) {

            $template = new Template (
                EMAIL_TEMPLATE_DIR,
                "remove"
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

                $urlRequest =
                    $controller->buildLink(
                        'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php',
                        array(
                            'problemID' => $row[1],
                            'action'    => 'displayLastActivity'
                        )
                    );

                $urlActivity =
                    $controller->buildLink(
                        'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php',
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


    } // end function

    function getFixedRequests($daysAgo = 1)
    {
        $sql =
            "SELECT 
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
    } // end function

    function getOustandingRequests($daysAgo = 1,
                                   $priorityFiveOnly = false
    )
    {
        $sql =
            "SELECT 
        cus_name AS `customer`,
        pro_problemno AS `requestID`,
        cns_name AS `assignedTo`,
        (SELECT 
          reason 
        FROM
          callactivity 
        WHERE caa_problemno = pro_problemno 
          AND caa_callacttypeno = 51 limit 1) AS `description`,
        DATEDIFF(NOW(),pro_date_raised ) AS `openDays`,
        pro_total_activity_duration_hours AS `timeSpentHours`,
        last.caa_date as lastUpdatedDate,
        pro_priority as `priority`,
        team.name AS teamName,
        pro_awaiting_customer_response_flag,
        pro_status
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

        $sql .= "      ORDER BY customer,
        pro_problemno";

        return $this->db->query($sql);
    }

    function getFocActivities($daysAgo = 1)
    {
        $sql =
            "SELECT
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

    function getPrePayActivitiesOverValue($daysAgo = 1)
    {
        $sql =
            "SELECT
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

    function sendByEmailTo($toEmail,
                           $subject,
                           $body,
                           $attachment = false,
                           $senderEmail = CONFIG_SALES_EMAIL
    )
    {

        $buMail = new BUMail($this);

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $cssToInlineStyles = new \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles();
        $body = $cssToInlineStyles->convert($body);

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
        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body      // to SD Managers
        );

        echo "SENT";

    }

    public function p5IncidentsWithoutSalesOrders()
    {
        $this->setMethodName('outstandingIncidents');

        $outstandingRequests = $this->getP5IncidentsWithoutSalesOrders();

        if ($row = $outstandingRequests->fetch_row()) {

            $template = new Template (
                EMAIL_TEMPLATE_DIR,
                "remove"
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
                EMAIL_TEMPLATE_DIR,
                "remove"
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

            $title = "P5 SRs with no SO";

            do {
                $urlRequest =
                    $controller->buildLink(
                        'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php',
                        array(
                            'problemID' => $row[1],
                            'action'    => 'displayLastActivity'
                        )
                    );

                $template->setVar(
                    array(
                        'customer'         => $row[0],
                        'serviceRequestID' => $row[1],
                        'assignedTo'       => $row[2],
                        'description'      => substr(
                            common_stripEverything($row[3]),
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
                                common_stripEverything($row[3]),
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
                  cus_name AS `customer`,
                  pro_problemno AS `requestID`,
                  cns_name AS `assignedTo`,
                  reason,
                  DATEDIFF(NOW(), pro_date_raised) AS `openDays`
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
                  AND pro_linked_ordno = 0
                  AND pro_custno != 282";
        return $this->db->query($sql);
    }

    public function p5WithSalesOrderAndContractAssigned()
    {
        $this->setMethodName('outstandingIncidents');

        $outstandingRequests = $this->getP5WithSalesOrdersAndContractAssigned();

        if ($row = $outstandingRequests->fetch_row()) {

            $template = new Template (
                EMAIL_TEMPLATE_DIR,
                "remove"
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
                EMAIL_TEMPLATE_DIR,
                "remove"
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

            $title = "P5 SRs with SO and not T&M";

            $controller = new Controller(
                '', $nothing, $nothing, $nothing, $nothing
            );

            do {
                $urlRequest =
                    $controller->buildLink(
                        'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php',
                        array(
                            'problemID' => $row[1],
                            'action'    => 'displayLastActivity'
                        )
                    );

                $template->setVar(
                    array(
                        'customer'         => $row[0],
                        'serviceRequestID' => $row[1],
                        'description'      => substr(
                            common_stripEverything($row[2]),
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
                                common_stripEverything($row[2]),
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
        $contactsData = [];

        while ($row = $contactOpenSRReportData->fetch_assoc()) {
            if (!isset($contactsData[$row['contactID']])) {
                $contactsData[$row['contactID']] = [
                    "name"            => $row['contactName'],
                    "email"           => $row['contactEmail'],
                    "customerName"    => $row['customerName'],
                    "serviceRequests" => []
                ];
            }
            $contactsData[$row['contactID']]['serviceRequests'][] = $row;
        }

        foreach ($contactsData as $contactID => $contactsDatum) {

            $template = new Template (
                EMAIL_TEMPLATE_DIR,
                "remove"
            );

            $template->set_file(
                'page',
                'DailySROpenReportEmail.html'
            );

            $template->set_var(
                'contactName',
                $contactsDatum['name']
            );

            $template->set_block(
                'page',
                'openSRBlock',
                'openSR'
            );


            foreach ($contactsDatum['serviceRequests'] as $SR) {
                $urlRequest = "https://www.cnc-ltd.co.uk/view/?serviceid=" . $SR['id'];

                $template->setVar(
                    array(
                        "srLinkToPortal" => $urlRequest,
                        "srNumber"       => $SR['id'],
                        "srRaisedByName" => $SR['raisedBy'] . ($onScreen ? ('( ' . $contactsDatum['customerName'] . ' )') : ''),
                        "srRaisedOnDate" => (new \DateTime($SR['raisedOn']))->format('d-m-Y h:i'),
                        "srStatus"       => $SR['status'],
                        "srDetails"      => $this->getFirstLinesDetails(
                            $SR['details'],
                            150
                        ),
                    )
                );


                $template->parse(
                    'openSR',
                    'openSRBlock',
                    true
                );

            }

            $template->parse(
                'output',
                'page',
                true
            );
            $body = $template->get_var('output');

            $subject = "Open Service Request Report - " . (new DateTime())->format('Y-m-d');

            if (!$onScreen) {
                $this->sendByEmailTo(
                    $contactsDatum['email'],
                    $subject,
                    $body,
                    null,
                    'customerReports@cnc-ltd.co.uk'
                );
            }

            echo '<br><div>Sent to Email ' . $contactsDatum['email'] . '</div><br>';
            echo $body;

        }

    }

    private function getFirstLinesDetails($details,
                                          $maxCharacters
    )
    {
        $details = strip_tags($details);
        $details = preg_replace(
            "!\s+!",
            ' ',
            $details
        );

        $lines = preg_split(
            "/\./",
            $details
        );
        $result = "";
        $counter = 0;

        do {
            if ($counter) {
                $result .= '.';
            }
            $result .= $lines[$counter];
            $counter++;
        } while ($counter < count($lines) && strlen($result) < $maxCharacters);
        return $result;
    }

    private function getContactOpenSRReportData()
    {
        $sql = "SELECT
problem.pro_problemno AS id,
CONCAT_WS(
' ',
reporter.con_first_name,
reporter.con_last_name
) AS raisedBy,
pro_date_raised AS raisedOn,
IF(
problem.pro_awaiting_customer_response_flag = 'Y',
'Awaiting Customer',
'In Progress'
) AS status,
callactivity.reason AS details,
contact.con_first_name AS contactName,
contact.con_email AS contactEmail,
contact.con_contno AS contactID,
customer.cus_name AS customerName
FROM
problem
INNER JOIN contact
ON contact.con_custno = problem.pro_custno
AND contact.con_mailflag11 = 'Y'
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

        return $result;

    }
}