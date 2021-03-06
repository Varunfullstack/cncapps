<?php

require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BUHeader.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");
require_once($cfg["path_bu"] . '/BUItem.inc.php');

class BUStartersAndLeaversReport extends Business
{
    const searchFormCustomerID = 'customerID';
    const searchFormStartDate = 'startDate';
    const searchFormEndDate = 'endDate';

    public function __construct(&$owner) { parent::__construct($owner); }

    function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn(self::searchFormCustomerID, DA_ID, DA_ALLOW_NULL);
        $dsData->addColumn(self::searchFormStartDate, DA_DATE, DA_ALLOW_NULL);
        $dsData->addColumn(self::searchFormEndDate, DA_DATE, DA_ALLOW_NULL);
        $dsData->setValue(self::searchFormCustomerID, null);
        $dsData->setValue(self::searchFormStartDate, null);
        $dsData->setValue(self::searchFormEndDate, null);
    }

    public function getReportData(DSForm $searchForm)
    {
        $custParams = [];
        $custNoQuery = "";
        if ($searchForm->getValue(self::searchFormCustomerID)) {
            $custNoQuery = " and pro_custno = ? ";
            $custParams = [["type" => "i", "value" => $searchForm->getValue(self::searchFormCustomerID)]];
        }
        $today = new DateTime();
        if ($searchForm->getValue(self::searchFormStartDate) && $searchForm->getValue(self::searchFormEndDate)) {
            $dateQuery = " and CAST(problem.`pro_date_raised` AS DATE) BETWEEN ? AND ? ";
            $dateParams = [
                ["type" => "s", "value" => $searchForm->getValue(self::searchFormStartDate)],
                ["type" => "s", "value" => $searchForm->getValue(self::searchFormEndDate)]
            ];
        } elseif ($searchForm->getValue(self::searchFormStartDate)) {
            $dateQuery = " and cast(problem.pro_date_raised as date) between ? and  ? ";
            $dateParams = [
                ["type" => "s", "value" => $searchForm->getValue(self::searchFormStartDate)],
                ["type" => 's', "value" => $today->format(DATE_MYSQL_DATE)]
            ];
        } elseif ($searchForm->getValue(self::searchFormEndDate)) {
            $dateQuery = " and cast(problem.pro_date_raised as date) between <=  ? ";
            $dateParams = [
                ["type" => "s", "value" => $searchForm->getValue(self::searchFormEndDate)],
            ];
        } else {
            $dateQuery = " and cast(problem.pro_date_raised as date) <=  ? ";
            $dateParams = [
                ["type" => 's', "value" => $today->format(DATE_MYSQL_DATE)]
            ];
        }
        $params = array_merge($custParams, $dateParams);

        $query = " 
select * from (
SELECT
       customer.`cus_name` as customerName,
'starters' as type,
  SUM(`pro_rootcauseno` = 58) AS quantity,
  AVG(pro_total_activity_duration_hours) AS avgDuration,
  sum(pro_total_activity_duration_hours) as totalDuration,
  AVG(getOpenHours(pro_problemno)) AS avgOpenHours,
  MAX(pro_total_activity_duration_hours) AS maxDuration,
  MAX(getOpenHours(pro_problemno)) AS maxOpenHours,
  MIN(pro_total_activity_duration_hours) AS minDuration,
  MIN(getOpenHours(pro_problemno)) AS minOpenHours,
  AVG(pro_total_activity_duration_hours * (SELECT hed_hourly_labour_cost FROM headert LIMIT 1)) AS avgCost,
  SUM(pro_total_activity_duration_hours * (SELECT hed_hourly_labour_cost FROM headert LIMIT 1)) AS totalCost,
  AVG((SELECT COUNT(*) FROM callactivity WHERE callactivity.`caa_problemno` = problem.`pro_problemno` AND callactivity.`caa_callacttypeno`= 11)) AS avgCustomerContact,
  AVG((SELECT COUNT(*) FROM callactivity WHERE callactivity.`caa_problemno` = problem.`pro_problemno` AND callactivity.`caa_callacttypeno` = 8)) AS avgRemoteSupport,
  AVG((SELECT COUNT(*) FROM callactivity WHERE callactivity.`caa_problemno` = problem.`pro_problemno` AND callactivity.`caa_callacttypeno` IN (8,11,18))) AS avgActivities
FROM
  problem
  LEFT JOIN customer
    ON problem.`pro_custno` = customer.`cus_custno`
WHERE true $custNoQuery $dateQuery
   AND pro_rootcauseno  = 58 AND `pro_status` IN ('F', 'C')  group by pro_custno UNION 
  SELECT
         customer.`cus_name` as customerName,
  'leavers' as type,
  SUM(`pro_rootcauseno` = 62) AS quantity,
  AVG(pro_total_activity_duration_hours) AS avgDuration,
  sum(pro_total_activity_duration_hours) as totalDuration,
  AVG(getOpenHours(pro_problemno)) AS avgOpenHours,
  MAX(pro_total_activity_duration_hours) AS maxDuration,
  MAX(getOpenHours(pro_problemno)) AS maxOpenHours,
  MIN(pro_total_activity_duration_hours) AS minDuration,
  MIN(getOpenHours(pro_problemno)) AS minOpenHours,
  AVG(pro_total_activity_duration_hours * (SELECT hed_hourly_labour_cost FROM headert LIMIT 1)) AS avgCost,
  SUM(pro_total_activity_duration_hours * (SELECT hed_hourly_labour_cost FROM headert LIMIT 1)) AS totalCost,
  AVG((SELECT COUNT(*) FROM callactivity WHERE callactivity.`caa_problemno` = problem.`pro_problemno` AND callactivity.`caa_callacttypeno` = 11)) AS avgCustomerContact,
  AVG((SELECT COUNT(*) FROM callactivity WHERE callactivity.`caa_problemno` = problem.`pro_problemno` AND callactivity.`caa_callacttypeno` = 8)) AS avgRemoteSupport,
  AVG((SELECT COUNT(*) FROM callactivity WHERE callactivity.`caa_problemno` = problem.`pro_problemno` AND callactivity.`caa_callacttypeno` IN (8,11,18))) AS avgActivities
FROM
  problem
   LEFT JOIN customer
    ON problem.`pro_custno` = customer.`cus_custno`
WHERE true $custNoQuery $dateQuery AND pro_rootcauseno  =  62 AND `pro_status` IN ('F', 'C')  group by pro_custno) t order by customerName 
 ";
        $statement = $this->db->prepare($query);
        if (!$statement) {
            throw new Exception($this->db->error);
        }
        $values = [];
        $dataTypes = "";
        foreach ($params as $param) {
            $dataTypes .= $param['type'];
            $values[] = $param['value'];
        }

        $refArray = [
            $dataTypes . $dataTypes,
        ];

        $refArray = array_merge($refArray, $values, $values);
        call_user_func_array(
            [$statement, 'bind_param'],
            $refArray
        );
        if (!$statement->execute()) {
            throw new Exception($statement->error);
        }
        $result = $statement->get_result();

        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $previousCustomer = null;
        $toReturn = [];
        $totalRow = null;
        foreach ($rows as $row) {
            $toReturn[] = $row;
            if ($row['customerName'] !== $previousCustomer) {
                if ($previousCustomer) {
                    $toReturn[] = $totalRow;
                }
                $totalRow = $this->createTotalRow($row);
            }

            $totalRow = $this->updateTotalRow($totalRow, $row);
            $previousCustomer = $row['customerName'];
        }

        $toReturn[] = $totalRow;


        return $toReturn;
    }

    function createTotalRow($row)
    {
        return [
            'customerName'         => $row['customerName'],
            "type"                 => "Total",
            "quantity"             => 0,
            "avgDuration"          => 0,
            "totalDuration"        => 0,
            "maxDuration"          => 0,
            "minDuration"          => INF,
            "totalOpenHours"       => 0,
            "avgOpenHours"         => 0,
            "maxOpenHours"         => 0,
            "minOpenHours"         => INF,
            "avgCost"              => 0,
            "totalCost"            => 0,
            "avgCustomerContact"   => 0,
            "avgRemoteSupport"     => 0,
            "avgActivities"        => 0,
            "totalCustomerContact" => 0,
            "totalRemoteSupport"   => 0,
            "totalActivities"      => 0,
            "count"                => 0
        ];
    }

    function updateTotalRow($totalRow, $row)
    {

        $totalRow['quantity'] += $row['quantity'];
        $totalRow['count']++;
        $totalRow['totalDuration'] += $row['totalDuration'];
        $totalRow['avgDuration'] = $totalRow['totalDuration'] / $totalRow['quantity'];
        $totalRow["maxDuration"] = $row['maxDuration'] > $totalRow['maxDuration'] ? $row['maxDuration'] : $totalRow['maxDuration'];
        $totalRow["minDuration"] = $row['minDuration'] < $totalRow['minDuration'] ? $row['minDuration'] : $totalRow['minDuration'];
        $totalRow["totalOpenHours"] += ($row['avgOpenHours'] * $row['quantity']);
        $totalRow["avgOpenHours"] = $totalRow['totalOpenHours'] / $totalRow['quantity'];
        $totalRow["maxOpenHours"] = $row['maxOpenHours'] > $totalRow['maxOpenHours'] ? $row['maxOpenHours'] : $totalRow['maxOpenHours'];
        $totalRow["minOpenHours"] = $row['minOpenHours'] < $totalRow['minOpenHours'] ? $row['minOpenHours'] : $totalRow['minOpenHours'];
        $totalRow["totalCost"] += $row['totalCost'];
        $totalRow["avgCost"] = $totalRow['totalCost'] / $totalRow['quantity'];
        $totalRow['totalCustomerContact'] += ($row['avgCustomerContact'] * $row['quantity']);
        $totalRow["avgCustomerContact"] = $totalRow['totalCustomerContact'] / $totalRow["quantity"];
        $totalRow['totalRemoteSupport'] += ($row['avgRemoteSupport'] * $row['quantity']);
        $totalRow["avgRemoteSupport"] = $totalRow['totalRemoteSupport'] / $totalRow['quantity'];
        $totalRow['totalActivities'] += ($row['avgActivities'] * $row['quantity']);
        $totalRow["avgActivities"] = $totalRow['totalActivities'] / $totalRow['quantity'];
        return $totalRow;
    }
}