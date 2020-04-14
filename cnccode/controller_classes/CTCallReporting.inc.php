<?php
/**
 * Standard Text controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');

class CTCallReporting extends CTCNC
{
    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->action) {
            case 'getData':
                $startDateObj = new DateTime();
                $startDate = $startDateObj->format(DATE_MYSQL_DATE);
                if (@$_REQUEST['startDate']) {
                    $startDateObj = DateTime::createFromFormat('d/m/Y', @$_REQUEST['startDate']);
                    if ($startDateObj) {
                        $startDate = $startDateObj->format(DATE_MYSQL_DATE);
                    }
                }

                $endDateObj = new DateTime();
                $endDate = $endDateObj->format(DATE_MYSQL_DATE);
                if (@$_REQUEST['endDate']) {
                    $endDateObj = DateTime::createFromFormat('d/m/Y', @$_REQUEST['endDate']);
                    if ($endDateObj) {
                        $endDate = $endDateObj->format(DATE_MYSQL_DATE);
                    }
                }

                $baseQuery =
                    "
                SELECT
  cus_name AS customerName,
  SUM(callType = 'Outbound') AS callsOut,
  SUM(callType = 'Inbound') AS callsIn,
  b.*
FROM
  callJournal
  JOIN customer
    ON customer.`cus_custno` = calljournal.`customerId`
  LEFT JOIN
    (SELECT
      pro_custno AS customerId,
      SUM(
        callactivity.`caa_callacttypeno` = 51
      ) AS totalSRsRaised,
      SUM(
        callactivity.`caa_callacttypeno` = 51
        AND problem.pro_hide_from_customer_flag <> 'Y'
      ) AS standardSRs,
      SUM(
        callactivity.`caa_callacttypeno` = 51
        AND problem.pro_hide_from_customer_flag = 'Y'
      ) AS proactiveSRs,
      SUM(
        callactivity.`caa_callacttypeno` IN (8, 11, 18, 51)
      ) AS activitiesRaised
    FROM
      callactivity
      LEFT JOIN problem
        ON callactivity.`caa_problemno` = problem.`pro_problemno`
    WHERE callactivity.`caa_date` BETWEEN ?
      AND ?
    GROUP BY problem.`pro_custno`) b
    ON b.customerId = callJournal.`customerId`
WHERE startDateTime BETWEEN ?
  AND ?
  AND callJournal.customerId <> 0
  AND callJournal.customerId IS NOT NULL
                ";
                $offset = $_REQUEST['start'];
                $limit = $_REQUEST['length'];

                $queryEnd = " GROUP BY callJournal.customerId";

                $parameters = [
                    ["type" => "s", "value" => $startDate],
                    ["type" => "s", "value" => $endDate],
                    ["type" => "s", "value" => $startDate],
                    ["type" => "s", "value" => $endDate],
                ];

                /** @var dbSweetcode $db */
                global $db;
                $countResult = $db->preparedQuery(
                    $baseQuery . $queryEnd,
                    $parameters
                );
                $totalCount = $countResult->num_rows;

                $search = $_REQUEST['search']['value'];
                $filteredCount = $totalCount;
                if ($search) {
                    $baseQuery .= " and cus_name like ? ";
                    $parameters[] = ["type" => "s", "value" => "%" . $search . "%"];

                    $countResult = $db->preparedQuery(
                        $baseQuery . $queryEnd,
                        $parameters
                    );
                    $filteredCount = $countResult->num_rows;

                }

                $baseQuery .= $queryEnd;
                $columns = $_REQUEST['columns'];
                $order = @$_REQUEST['order'];
                $orderItems = [];
                foreach ($order as $orderItem) {
                    $orderItems[] = mysqli_real_escape_string(
                        $db->link_id(),
                        "{$columns[$orderItem['column']]['name']} {$orderItem['dir']}"
                    );
                }
                if (count($orderItems)) {
                    $baseQuery .= " order by " . implode(', ', $orderItems);
                }
                $baseQuery .= " limit ?, ?";
                $parameters[] = ["type" => "i", "value" => $offset];
                $parameters[] = ["type" => "i", "value" => $limit];
                $result = $db->preparedQuery(
                    $baseQuery,
                    $parameters
                );
                $overtimes = $result->fetch_all(MYSQLI_ASSOC);
                echo json_encode(
                    [
                        "draw"            => $_REQUEST['draw'],
                        "recordsTotal"    => $totalCount,
                        "recordsFiltered" => $filteredCount,
                        "data"            => $overtimes
                    ],
                    JSON_NUMERIC_CHECK
                );
                return;
            default:
                $this->displayList();
        }
    }

    /**
     * Display list of types
     * @access private
     * @throws Exception
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Call Reporting');
        $this->setTemplateFiles(
            array('CallReporting' => 'CallReporting')
        );

        $this->template->parse('CONTENTS', 'CallReporting', true);
        $this->parsePage();
    }
}// end of class
