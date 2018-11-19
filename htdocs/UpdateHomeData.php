<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 27/09/2018
 * Time: 12:06
 */

require_once("config.inc.php");
GLOBAL $cfg;
require_once($cfg['path_ct'] . '/CTHome.inc.php');

function getFixedAndReopenData()
{
    global $db;
    /** @var mysqli_result $query */
    $query = $db->query(
        "SELECT 
              SUM(fixer.`teamID` = 1) AS hdFixed,
              SUM(fixer.teamID	= 2) AS escFixed,
              SUM(fixer.teamID = 4) AS imtFixed ,
              SUM(fixer.`teamID` IN (1,2,4)) AS totalFixed
            FROM
              problem 
              LEFT JOIN consultant fixer 
                ON problem.`pro_fixed_consno` = fixer.`cns_consno` 
            WHERE DATE(problem.`pro_fixed_date`) = CURRENT_DATE 
              AND pro_status = 'F' 
              AND problem.`pro_custno` <> 282
              AND fixer.`cns_consno` <> 67
              GROUP BY DATE(problem.pro_fixed_date)"
    );

    $dailyFixed = $query->fetch_assoc();

    $sql = "SELECT 
  SUM(fixer.`teamID` = 1) AS hdFixed,
  SUM(fixer.teamID = 2) AS escFixed,
  SUM(fixer.teamID = 4) AS imtFixed,
  SUM(fixer.`teamID` IN (1, 2, 4)) AS totalFixed 
FROM
  problem 
  LEFT JOIN consultant fixer 
    ON problem.`pro_fixed_consno` = fixer.`cns_consno` 
WHERE week( problem.`pro_fixed_date`, 7) = WEEK(CURRENT_DATE, 7) 
  AND EXTRACT(
    YEAR FROM problem.`pro_fixed_date`
  ) = EXTRACT(YEAR FROM CURRENT_DATE) 
  AND pro_status = 'F' 
  AND problem.`pro_custno` <> 282 
  AND fixer.`cns_consno` <> 67";

    $query = $db->query($sql);

    $weeklyFixed = $query->fetch_assoc();
    $query = $db->query(
        "SELECT 
              SUM(teamID = 1) AS hdReopened,
              SUM(teamID = 2) AS escReopened,
              SUM(teamID = 4) AS imtReopened,
              SUM(teamID IN (1, 2, 4)) AS totalReopened
            FROM
              (SELECT 
                pro_problemno,
                reopener.teamID,
                MAX(fixedActivity.created) 
              FROM
                problem 
                JOIN callactivity fixedActivity 
                  ON fixedActivity.caa_problemno = problem.pro_problemno 
                  AND fixedActivity.caa_callacttypeno = 57 
                JOIN consultant reopener 
                  ON fixedActivity.`caa_consno` = reopener.`cns_consno` 
              WHERE problem.`pro_custno` <> 282 
                AND problem.`pro_reopened_flag` = 'Y' 
                AND reopener.`cns_consno` <> 67 
                AND problem.pro_reopened_date = CURRENT_DATE 
              GROUP BY pro_problemno) test "
    );

    $dailyReopened = $query->fetch_assoc();

    $query = $db->query(
        "SELECT 
              SUM(teamID = 1) AS hdReopened,
              SUM(teamID = 2) AS escReopened,
              SUM(teamID = 4) AS imtReopened,
              SUM(teamID IN (1, 2, 4)) AS totalReopened
            FROM
              (SELECT 
                pro_problemno,
                reopener.teamID,
                MAX(fixedActivity.created) 
              FROM
                problem 
                JOIN callactivity fixedActivity 
                  ON fixedActivity.caa_problemno = problem.pro_problemno 
                  AND fixedActivity.caa_callacttypeno = 57 
                JOIN consultant reopener 
                  ON fixedActivity.`caa_consno` = reopener.`cns_consno` 
              WHERE problem.`pro_custno` <> 282 
                AND problem.`pro_reopened_flag` = 'Y' 
                AND reopener.`cns_consno` <> 67 
                AND WEEK(problem.`pro_reopened_date`, 7) = WEEK(CURRENT_DATE, 7) 
                  AND EXTRACT(
                    YEAR FROM problem.`pro_reopened_date`
                  ) = EXTRACT(YEAR FROM CURRENT_DATE) 
              GROUP BY pro_problemno) test "
    );

    $weeklyReopened = $query->fetch_assoc();
    return
        [
            "dailyHdReopened"     => Controller::formatNumber(
                $dailyReopened['hdReopened'],
                0
            ),
            "dailyEscReopened"    => Controller::formatNumber(
                $dailyReopened['escReopened'],
                0
            ),
            "dailyImtReopened"    => Controller::formatNumber(
                $dailyReopened['imtReopened'],
                0
            ),
            "dailyTotalReopened"  => Controller::formatNumber(
                $dailyReopened['totalReopened'],
                0
            ),
            "dailyHdFixed"        => Controller::formatNumber(
                $dailyFixed['hdFixed'],
                0
            ),
            "dailyEscFixed"       => Controller::formatNumber(
                $dailyFixed['escFixed'],
                0
            ),
            "dailyImtFixed"       => Controller::formatNumber(
                $dailyFixed['imtFixed'],
                0
            ),
            "dailyTotalFixed"     => Controller::formatNumber(
                $dailyFixed['totalFixed'],
                0
            ),
            "weeklyHdReopened"    => Controller::formatNumber(
                $weeklyReopened['hdReopened'],
                0
            ),
            "weeklyEscReopened"   => Controller::formatNumber(
                $weeklyReopened['escReopened'],
                0
            ),
            "weeklyImtReopened"   => Controller::formatNumber(
                $weeklyReopened['imtReopened'],
                0
            ),
            "weeklyTotalReopened" => Controller::formatNumber(
                $weeklyReopened['totalReopened'],
                0
            ),
            "weeklyHdFixed"       => Controller::formatNumber(
                $weeklyFixed['hdFixed'],
                0
            ),
            "weeklyEscFixed"      => Controller::formatNumber(
                $weeklyFixed['escFixed'],
                0
            ),
            "weeklyImtFixed"      => Controller::formatNumber(
                $weeklyFixed['imtFixed'],
                0
            ),
            "weeklyTotalFixed"    => Controller::formatNumber(
                $weeklyFixed['totalFixed'],
                0
            ),


        ];
}

function getRunningMonthFirstTimeFixedFigures()
{
    $query = "SELECT 
  SUM(
    COALESCE(
      (SELECT 
        1 
      FROM
        callactivity 
      WHERE callactivity.caa_problemno = problem.pro_problemno 
        AND callactivity.caa_callacttypeno = 8 
        AND TIME_TO_SEC(
          TIMEDIFF(
            callactivity.caa_starttime,
            initial.caa_endtime
          )
        ) <= (5 * 60) 
        AND callactivity.`caa_consno` = engineer.`cns_consno` LIMIT 1),
      0
    )
  ) AS attemptedFirstTimeFix,
  SUM(
    COALESCE(
      (SELECT 
        1 
      FROM
        problem test 
        JOIN callactivity initial 
          ON initial.caa_problemno = test.pro_problemno 
          AND initial.caa_callacttypeno = 51 
        JOIN callactivity remoteSupport 
          ON remoteSupport.caa_problemno = test.pro_problemno 
          AND remoteSupport.caa_callacttypeno = 8 
        JOIN callactivity fixedActivity 
          ON fixedActivity.caa_problemno = test.pro_problemno 
          AND fixedActivity.caa_callacttypeno = 57 
      WHERE test.pro_problemno = problem.`pro_problemno` 
        AND (test.pro_status = 'F' OR test.pro_status = 'C')
        AND remoteSupport.caa_consno = engineer.`cns_consno` 
        AND fixedActivity.caa_consno = engineer.`cns_consno` 
        AND TIME_TO_SEC(
          TIMEDIFF(
            remoteSupport.caa_starttime,
            initial.caa_endtime
          )
        ) <= (5 * 60) 
        AND TIME_TO_SEC(
          TIMEDIFF(
            fixedActivity.caa_starttime,
            remoteSupport.caa_endtime
          )
        ) <= (5 * 60) LIMIT 1),
      0
    )
  ) AS firstTimeFix,
  SUM(1) AS totalRaised
FROM
  problem 
  JOIN callactivity initial 
    ON initial.caa_problemno = problem.pro_problemno 
    AND initial.caa_callacttypeno = 51 
  JOIN consultant engineer 
    ON initial.`caa_consno` = engineer.`cns_consno` 
WHERE problem.`pro_custno` <> 282 
  AND 
  (SELECT 
    COUNT(item.`itm_itemno`) 
  FROM
    custitem 
    JOIN item 
      ON cui_itemno = itm_itemno 
  WHERE custitem.`cui_custno` = pro_custno 
    AND itm_servercare_flag = 'Y' 
    AND itm_desc LIKE '%ServiceDesk%' 
    AND cui_expiry_date >= NOW() 
    AND renewalStatus <> 'D' 
    AND declinedFlag <> 'Y') > 0 
  AND EXTRACT( YEAR_MONTH FROM initial.caa_date)  = EXTRACT( YEAR_MONTH FROM CURRENT_DATE )
  AND engineer.`teamID` = 1";

    global $db;

    $result = $db->query($query);

    return $result->fetch_assoc();
}

function getFirstTimeFixData()
{
    global $db;

    $result = $db->query(
        "SELECT 
  CONCAT(
    engineer.`firstName`,
    ' ',
    engineer.`lastName`
  ) AS name,
  SUM(
    COALESCE(
      (SELECT 
        1 
      FROM
        callactivity 
      WHERE callactivity.caa_problemno = problem.pro_problemno 
        AND callactivity.caa_callacttypeno = 8 
        AND TIME_TO_SEC(
          TIMEDIFF(
            callactivity.caa_starttime,
            initial.caa_endtime
          )
        ) <= (5 * 60) 
        AND callactivity.`caa_consno` = engineer.`cns_consno` limit 1),
      0
    )
  ) AS attemptedFirstTimeFix,
  SUM(
    COALESCE(
      (SELECT 
        1 
      FROM
        problem test 
        JOIN callactivity initial 
          ON initial.caa_problemno = test.pro_problemno 
          AND initial.caa_callacttypeno = 51 
        JOIN callactivity remoteSupport 
          ON remoteSupport.caa_problemno = test.pro_problemno 
          AND remoteSupport.caa_callacttypeno = 8 
        JOIN callactivity fixedActivity 
          ON fixedActivity.caa_problemno = test.pro_problemno 
          AND fixedActivity.caa_callacttypeno = 57 
      WHERE test.pro_problemno = problem.`pro_problemno` 
        AND (test.pro_status = 'F' OR test.pro_status = 'C')
        AND remoteSupport.caa_consno = engineer.`cns_consno` 
        AND fixedActivity.caa_consno = engineer.`cns_consno` 
        AND TIME_TO_SEC(
          TIMEDIFF(
            remoteSupport.caa_starttime,
            initial.caa_endtime
          )
        ) <= (5 * 60) 
        AND TIME_TO_SEC(
          TIMEDIFF(
            fixedActivity.caa_starttime,
            remoteSupport.caa_endtime
          )
        ) <= (5 * 60) limit 1),
      0
    )
  ) AS firstTimeFix,
  SUM(1) AS totalRaised  
FROM
  problem 
  JOIN callactivity initial 
    ON initial.caa_problemno = problem.pro_problemno 
    AND initial.caa_callacttypeno = 51 
  JOIN consultant engineer 
    ON initial.`caa_consno` = engineer.`cns_consno` 
WHERE problem.`pro_custno` <> 282 
  AND 
  (SELECT 
    COUNT(item.`itm_itemno`) 
  FROM
    custitem 
    JOIN item 
      ON cui_itemno = itm_itemno 
  WHERE custitem.`cui_custno` = pro_custno 
    AND itm_servercare_flag = 'Y' 
    AND itm_desc LIKE '%ServiceDesk%' 
    AND cui_expiry_date >= NOW() 
    AND renewalStatus <> 'D' 
    AND declinedFlag <> 'Y') > 0 
  AND initial.caa_date = CURRENT_DATE 
  AND engineer.`teamID` = 1 
GROUP BY engineer.`cns_consno`  order by engineer.firstName"
    );

    $totalRaised = 0;
    $totalAttempted = 0;
    $totalAchieved = 0;
    $data = [
        "engineers"      => [],
        "totalRaised"    => 0,
        "totalAttempted" => 0,
        "totalAchieved"  => 0
    ];

    while ($row = $result->fetch_assoc()) {
        $data["engineers"][] = [
            'name'                  => $row['name'],
            'firstTimeFix'          => $row['firstTimeFix'],
            'attemptedFirstTimeFix' => $row['attemptedFirstTimeFix'],
            'totalRaised'           => $row['totalRaised']
        ];


        $totalRaised += $row['totalRaised'];
        $totalAttempted += $row['attemptedFirstTimeFix'];
        $totalAchieved += $row['firstTimeFix'];
    }

    $monthlyFigures = getRunningMonthFirstTimeFixedFigures();

    $data['firstTimeFixAttemptedPct'] = $totalRaised > 0 ? round(
        ($totalAttempted / $totalRaised) * 100
    ) : 'N/A';
    $data['firstTimeFixAchievedPct'] = $totalRaised > 0 ? round(
        ($totalAchieved / $totalRaised) * 100
    ) : 'N/A';
    $data['phonedThroughRequests'] = $totalRaised;
    $data['monthlyFirstTimeFixAttemptedPct'] = $monthlyFigures['totalRaised'] ? round(
        ($monthlyFigures['attemptedFirstTimeFix'] / $monthlyFigures['totalRaised']) * 100
    ) : 'N/A';
    $data['monthlyFirstTimeFixAchievedPct'] = $monthlyFigures['totalRaised'] > 0 ? round(
        ($monthlyFigures['firstTimeFix'] / $monthlyFigures['totalRaised']) * 100
    ) : 'N/A';
    $data['monthlyPhonedThroughRequests'] = $monthlyFigures['totalRaised'];
    return $data;

}

$firstTimeFix = getFirstTimeFixData();
$fixedAndReopen = getFixedAndReopenData();

$db->preparedQuery(
    "update homeData set firstTimeFix = ? ,fixedAndReopenData = ?",
    [
        [
            "type"  => "s",
            "value" => json_encode($firstTimeFix)
        ],
        [
            "type"  => "s",
            "value" => json_encode($fixedAndReopen)
        ]
    ]
);

?>