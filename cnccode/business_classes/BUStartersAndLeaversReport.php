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

            $this->updateTotalRow($totalRow, $row);

        }

        $toReturn[] = $totalRow;


        return $toReturn;
    }

    function createTotalRow($row)
    {
        return [
            'customerName'       => $row['customerName'],
            "type"               => "Total",
            "quantity"           => $row['quantity'],
            "avgDuration"        => $row['avgDuration'],
            "totalDuration"      => $row['totalDuration'],
            "avgOpenHours"       => $row['avgOpenHours'],
            "maxDuration"        => $row['maxDuration'],
            "maxOpenHours"       => $row['maxOpenHours'],
            "minDuration"        => $row['minDuration'],
            "minOpenHours"       => $row['minOpenHours'],
            "avgCost"            => $row['avgCost'],
            "totalCost"          => $row['totalCost'],
            "avgCustomerContact" => $row['avgCustomerContact'],
            "avgRemoteSupport"   => $row['avgRemoteSupport'],
            "avgActivities"      => $row['avgActivities'],
            "count"              => 1
        ];
    }

    function updateTotalRow($totalRow, $row)
    {
        $totalRow['quantity'] += $row['quantity'];
        $totalRow['count']++;
        $totalRow['avgDuration'] = ($totalRow['avgDuration'] + $row['avgDuration']) / $totalRow['count'];
        $totalRow['totalDuration'] += $row['totalDuration'];
        $totalRow["avgOpenHours"] = ($totalRow['avgOpenHours'] + $row['avgOpenHours']) / $totalRow['count'];
        $totalRow["maxDuration"] = $row['maxDuration'] > $totalRow['maxDuration'] ? $row['maxDuration'] : $totalRow['maxDuration'];
        $totalRow["maxOpenHours"] = $row['maxOpenHours'] > $totalRow['maxOpenHours'] ? $row['maxOpenHours'] : $totalRow['maxOpenHours'];
        $totalRow["minDuration"] = $row['minDuration'] < $totalRow['minDuration'] ? $row['minDuration'] : $totalRow['minDuration'];
        $totalRow["minOpenHours"] = $row['minOpenHours'] < $totalRow['minOpenHours'] ? $row['minOpenHours'] : $totalRow['minOpenHours'];
        $totalRow["avgCost"] = ($totalRow["avgCost"] + $row['avgCost']) / $totalRow['count'];
        $totalRow["totalCost"] += $row['totalCost'];
        $totalRow["avgCustomerContact"] = ($totalRow["avgCustomerContact"] + $row['avgCustomerContact']) / $totalRow["count"];
        $totalRow["avgRemoteSupport"] = ($totalRow["avgRemoteSupport"] + $row['avgRemoteSupport']) / $totalRow['count'];
        $totalRow["avgActivities"] = ($totalRow["avgActivities"] + $row['avgActivities']) / $totalRow['count'];
        return $totalRow;
    }
}