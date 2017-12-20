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

        if ($row) {

            $template = new Template (EMAIL_TEMPLATE_DIR, "remove");
            $template->set_file('page', 'ServiceFixedReportEmail.inc.html');
            $template->set_block('page', 'requestBlock', 'requests');

            /* csv file template */
            $csvTemplate = new Template (EMAIL_TEMPLATE_DIR, "remove");
            $csvTemplate->set_file('page', 'ServiceFixedReportEmail.inc.csv');
            $csvTemplate->set_block('page', 'requestBlock', 'requests');

            $controller = new Controller(
                '',
                $nothing,
                $nothing,
                $nothing,
                $nothing,
                null,
                null,
                null,
                null
            );

            do {

                $urlRequest =
                    $controller->buildLink(
                        'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php',
                        array(
                            'problemID' => $row[1],
                            'action' => 'displayLastActivity'
                        )
                    );

                $description = substr(common_stripEverything($row[3]), 0, 50);

                $template->setVar(
                    array(
                        'customer' => $row[0],
                        'serviceRequestID' => $row[1],
                        'fixedBy' => $row[2],
                        'description' => $description,
                        'durationHours' => $row[4],
                        'timeSpentHours' => $row[5],
                        'responseTimeHours' => $row[6],
                        'fixTimeHours' => $row[7],
                        'contract' => $row[8],
                        'urlRequest' => $urlRequest
                    )
                );

                $template->parse('requests', 'requestBlock', true);

                $csvTemplate->setVar(
                    array(
                        'customer' => $row[0],
                        'serviceRequestID' => $row[1],
                        'fixedBy' => $row[2],
                        'description' => $description,
                        'durationHours' => $row[4],
                        'timeSpentHours' => $row[5],
                        'responseTimeHours' => $row[6],
                        'fixTimeHours' => $row[7],
                        'contract' => $row[8]
                    )
                );

                $csvTemplate->parse('requests', 'requestBlock', true);

            } while ($row = $fixedRequests->fetch_row());

            $template->parse('output', 'page', true);

            $body = $template->get_var('output');

            $csvTemplate->parse('output', 'page', true);

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
     */
    function outstandingIncidents($daysAgo, $priorityFiveOnly = false)
    {

        $this->setMethodName('outstandingIncidents');

        $outstandingRequests = $this->getOustandingRequests($daysAgo, $priorityFiveOnly);

        if ($row = $outstandingRequests->fetch_row()) {

            $template = new Template (EMAIL_TEMPLATE_DIR, "remove");

            $template->set_file('page', 'ServiceOutstandingReportEmail.inc.html');

            $template->set_block('page', 'requestBlock', 'requests');

            $csvTemplate = new Template (EMAIL_TEMPLATE_DIR, "remove");

            $csvTemplate->set_file('page', 'ServiceOutstandingReportEmail.inc.csv');

            $csvTemplate->set_block('page', 'requestBlock', 'requests');

            $controller = new Controller(
                '',
                $nothing,
                $nothing,
                $nothing,
                $nothing,
                null,
                null,
                null,
                null
            );

            do {
                $urlRequest =
                    $controller->buildLink(
                        'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php',
                        array(
                            'problemID' => $row[1],
                            'action' => 'displayLastActivity'
                        )
                    );

                $template->setVar(
                    array(
                        'customer' => $row[0],
                        'serviceRequestID' => $row[1],
                        'assignedTo' => $row[2],
                        'description' => substr(common_stripEverything($row[3]), 0, 50),
                        'durationHours' => $row[4],
                        'timeSpentHours' => $row[5],
                        'lastUpdatedDate' => $row[6],
                        'priority' => $row[7],
                        'teamName' => $row[8],
                        'urlRequest' => $urlRequest
                    )
                );

                $csvTemplate->setVar(
                    array(
                        'customer' => $row[0],
                        'serviceRequestID' => $row[1],
                        'assignedTo' => $row[2],
                        'description' => str_replace(',', '', substr(common_stripEverything($row[3]), 0, 50)),
                        'durationHours' => $row[4],
                        'timeSpentHours' => $row[5],
                        'lastUpdatedDate' => $row[6],
                        'priority' => $row[7],
                        'teamName' => $row[8],
                    )
                );

                $template->parse('requests', 'requestBlock', true);
                $csvTemplate->parse('requests', 'requestBlock', true);

            } while ($row = $outstandingRequests->fetch_row());

            $template->setVar(
                array(
                    'daysAgo' => $daysAgo
                )
            );

            $template->parse('output', 'page', true);
            $body = $template->get_var('output');

            $csvTemplate->parse('output', 'page', true);
            $csvFile = $csvTemplate->get_var('output');

            if ($priorityFiveOnly) {
                $subject = 'Priority 5';
            } else {
                $subject = 'Priority 1-4';
            }

            $subject .= ' SRs Outstanding For ' . $daysAgo . ' Days';

            $this->sendByEmailTo(
                'sropenfordays@' . CONFIG_PUBLIC_DOMAIN,
                $subject,
                $body,
                $csvFile
            );

            echo $body;

        }

    } // end function outstandingIncidents

    function focActivities($daysAgo)
    {

        $this->setMethodName('focActivities');

        $activities = $this->getFocActivities($daysAgo);

        if ($row = $activities->fetch_row()) {

            $template = new Template (EMAIL_TEMPLATE_DIR, "remove");

            $template->set_file('page', 'ServiceFocReportEmail.inc.html');

            $template->set_block('page', 'activityBlock', 'activities');

            $controller = new Controller(
                '',
                $nothing,
                $nothing,
                $nothing,
                $nothing,
                null,
                null,
                null,
                null
            );
            do {

                $urlRequest =
                    $controller->buildLink(
                        'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php',
                        array(
                            'problemID' => $row[1],
                            'action' => 'displayLastActivity'
                        )
                    );

                $urlActivity =
                    $controller->buildLink(
                        'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php',
                        array(
                            'callActivityID' => $row[2],
                            'action' => 'displayActivity'
                        )
                    );
                $template->setVar(
                    array(
                        'customer' => $row[0],
                        'serviceRequestID' => $row[1],
                        'activityID' => $row[2],
                        'technician' => $row[3],
                        'hours' => number_format($row[4], 2),
                        'contract' => $row[5],
                        'category' => $row[6],
                        'urlRequest' => $urlRequest,
                        'urlActivity' => $urlActivity
                    )
                );

                $template->parse('activities', 'activityBlock', true);

            } while ($row = $activities->fetch_row());

            $template->parse('output', 'page', true);

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

            $template = new Template (EMAIL_TEMPLATE_DIR, "remove");

            $template->set_file('page', 'ServicePrepayOverValueReportEmail.inc.html');

            $template->set_block('page', 'activityBlock', 'activities');

            $controller = new Controller(
                '',
                $nothing,
                $nothing,
                $nothing,
                $nothing,
                null,
                null,
                null,
                null
            );

            do {

                $urlRequest =
                    $controller->buildLink(
                        'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php',
                        array(
                            'problemID' => $row[1],
                            'action' => 'displayLastActivity'
                        )
                    );

                $urlActivity =
                    $controller->buildLink(
                        'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php',
                        array(
                            'callActivityID' => $row[2],
                            'action' => 'displayActivity'
                        )
                    );
                $template->setVar(
                    array(
                        'customer' => $row[0],
                        'serviceRequestID' => $row[1],
                        'activityID' => $row[2],
                        'value' => number_format($row[3], 2),
                        'technician' => $row[4],
                        'urlRequest' => $urlRequest,
                        'urlActivity' => $urlActivity,
                        'contract' => $row[8]
                    )
                );

                $template->parse('activities', 'activityBlock', true);

            } while ($row = $activities->fetch_row());

            $template->parse('output', 'page', true);

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

    function getOustandingRequests($daysAgo = 1, $priorityFiveOnly = false)
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
          AND caa_callacttypeno = 51) AS `description`,
          
        DATEDIFF(NOW(),pro_date_raised ) AS `openDays`,
        pro_total_activity_duration_hours AS `timeSpentHours`,

        last.caa_date as lastUpdatedDate,
        
        pro_priority as `priority`,
        team.name AS teamName
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
        
          DATE(pro_date_raised) <=DATE(
          DATE_SUB(NOW(), INTERVAL $daysAgo DAY)) 
          AND pro_status NOT IN ('F', 'C')";

        if ($priorityFiveOnly) {
            $sql .= " AND pro_priority = 5";
        } else {
            $sql .= " AND pro_priority < 5";
        }

        $sql .= "
        /*
        Exclude SRs with open future activities
        */
        AND
          (
            SELECT
              COUNT(*)
            FROM
              callactivity
            WHERE
              caa_problemno = pro_problemno
              AND caa_date > DATE( NOW() )
              AND caa_endtime = ''
          ) = 0
        
          
        
      ORDER BY customer,
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

    function sendByEmailTo($toEmail, $subject, $body, $attachment = false)
    {

        $buMail = new BUMail($this);

        $senderEmail = CONFIG_SALES_EMAIL;

        $hdrs = array(
            'From' => $senderEmail,
            'Subject' => $subject,
            'Date' => date("r")
        );

        $buMail->mime->setHTMLBody($body);

        if ($attachment) {
            $buMail->mime->addAttachment($attachment, 'text/plain', 'report.csv', false);
        }

        $body = $buMail->mime->get();

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body,
            true      // to SD Managers
        );

        echo "SENT";

    }

    public function p5IncidentsWithoutSalesOrders()
    {
        $this->setMethodName('outstandingIncidents');

        $outstandingRequests = $this->getP5IncidentsWithoutSalesOrders();

        if ($row = $outstandingRequests->fetch_row()) {

            $template = new Template (EMAIL_TEMPLATE_DIR, "remove");

            $template->set_file('page', 'P5NoSalesReportEmail.inc.html');

            $template->set_block('page', 'requestBlock', 'requests');

            $csvTemplate = new Template (EMAIL_TEMPLATE_DIR, "remove");

            $csvTemplate->set_file('page', 'P5NoSalesReportEmail.inc.csv');

            $csvTemplate->set_block('page', 'requestBlock', 'requests');

            $controller = new Controller(
                '',
                $nothing,
                $nothing,
                $nothing,
                $nothing,
                null,
                null,
                null,
                null
            );

            do {
                $urlRequest =
                    $controller->buildLink(
                        'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php',
                        array(
                            'problemID' => $row[1],
                            'action' => 'displayLastActivity'
                        )
                    );

                $template->setVar(
                    array(
                        'customer' => $row[0],
                        'serviceRequestID' => $row[1],
                        'assignedTo' => $row[2],
                        'description' => substr(common_stripEverything($row[3]), 0, 50),
                        'durationHours' => $row[4],
                        'timeSpentHours' => $row[5],
                        'lastUpdatedDate' => $row[6],
                        'priority' => $row[7],
                        'teamName' => $row[8],
                        'urlRequest' => $urlRequest
                    )
                );

                $csvTemplate->setVar(
                    array(
                        'customer' => $row[0],
                        'serviceRequestID' => $row[1],
                        'assignedTo' => $row[2],
                        'description' => str_replace(',', '', substr(common_stripEverything($row[3]), 0, 50)),
                        'durationHours' => $row[4],
                        'timeSpentHours' => $row[5],
                        'lastUpdatedDate' => $row[6],
                        'priority' => $row[7],
                        'teamName' => $row[8],
                    )
                );

                $template->parse('requests', 'requestBlock', true);
                $csvTemplate->parse('requests', 'requestBlock', true);

            } while ($row = $outstandingRequests->fetch_row());

            $template->parse('output', 'page', true);
            $body = $template->get_var('output');

            $csvTemplate->parse('output', 'page', true);
            $csvFile = $csvTemplate->get_var('output');

            $subject = 'Priority 5 Requests with no sales Order';

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

            $template = new Template (EMAIL_TEMPLATE_DIR, "remove");

            $template->set_file('page', 'P5WithSalesAndContractReportEmail.inc.html');

            $template->set_block('page', 'requestBlock', 'requests');

            $csvTemplate = new Template (EMAIL_TEMPLATE_DIR, "remove");

            $csvTemplate->set_file('page', 'P5WithSalesAndContractReportEmail.inc.csv');

            $csvTemplate->set_block('page', 'requestBlock', 'requests');

            $controller = new Controller(
                '',
                $nothing,
                $nothing,
                $nothing,
                $nothing,
                null,
                null,
                null,
                null
            );

            do {
                $urlRequest =
                    $controller->buildLink(
                        'http://' . $_SERVER ['HTTP_HOST'] . '/Activity.php',
                        array(
                            'problemID' => $row[1],
                            'action' => 'displayLastActivity'
                        )
                    );

                $template->setVar(
                    array(
                        'customer' => $row[0],
                        'serviceRequestID' => $row[1],
                        'description' => substr(common_stripEverything($row[2]), 0, 50),
                        'urlRequest' => $urlRequest
                    )
                );

                $csvTemplate->setVar(
                    array(
                        'customer' => $row[0],
                        'serviceRequestID' => $row[1],
                        'description' => str_replace(',', '', substr(common_stripEverything($row[2]), 0, 50)),
                    )
                );

                $template->parse('requests', 'requestBlock', true);
                $csvTemplate->parse('requests', 'requestBlock', true);

            } while ($row = $outstandingRequests->fetch_row());

            $template->parse('output', 'page', true);
            $body = $template->get_var('output');

            $csvTemplate->parse('output', 'page', true);
            $csvFile = $csvTemplate->get_var('output');

            $subject = 'Priority 5 Requests with sales Order and Contract Assigned';

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
}