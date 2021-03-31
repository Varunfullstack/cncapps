<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 22/01/2019
 * Time: 11:46
 */

use CNCLTD\Utils;

global $cfg;
require_once ($cfg['path_gc']) . '/Controller.inc.php';

class BUHome
{


    function getFixedAndReopenData()
    {
        global $db;
        /** @var mysqli_result $query */
        $query          = $db->query(
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
        $dailyFixed     = $query->fetch_assoc();
        $sql            = "SELECT 
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
        $query          = $db->query($sql);
        $weeklyFixed    = $query->fetch_assoc();
        $query          = $db->query(
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
        $dailyReopened  = $query->fetch_assoc();
        $query          = $db->query(
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
        return [
            "dailyHdReopened"     => number_format($dailyReopened['hdReopened']),
            "dailyEscReopened"    => number_format($dailyReopened['escReopened']),
            "dailyImtReopened"    => number_format($dailyReopened['imtReopened']),
            "dailyTotalReopened"  => number_format($dailyReopened['totalReopened']),
            "dailyHdFixed"        => number_format($dailyFixed['hdFixed']),
            "dailyEscFixed"       => number_format($dailyFixed['escFixed']),
            "dailyImtFixed"       => number_format($dailyFixed['imtFixed']),
            "dailyTotalFixed"     => number_format($dailyFixed['totalFixed']),
            "weeklyHdReopened"    => number_format($weeklyReopened['hdReopened']),
            "weeklyEscReopened"   => number_format($weeklyReopened['escReopened']),
            "weeklyImtReopened"   => number_format($weeklyReopened['imtReopened']),
            "weeklyTotalReopened" => number_format($weeklyReopened['totalReopened']),
            "weeklyHdFixed"       => number_format($weeklyFixed['hdFixed']),
            "weeklyEscFixed"      => number_format($weeklyFixed['escFixed']),
            "weeklyImtFixed"      => number_format($weeklyFixed['imtFixed']),
            "weeklyTotalFixed"    => number_format($weeklyFixed['totalFixed']),
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
        AND callactivity.`caa_consno` = engineer.`cns_consno` LIMIT 1)   )
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
        ) <= (5 * 60) LIMIT 1)   )
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
        $result         = $db->query(
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
        AND callactivity.`caa_consno` = engineer.`cns_consno` limit 1)   ,0)
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
        ) <= (5 * 60) limit 1),0   )
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
  AND problem.raiseTypeId=3
  and problem.pro_priority < 4
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
        $totalRaised    = 0;
        $totalAttempted = 0;
        $totalAchieved  = 0;
        $data           = [
            "engineers"      => [],
            "totalRaised"    => 0,
            "totalAttempted" => 0
        ];
        while ($row = $result->fetch_assoc()) {
            $data["engineers"][] = [
                'name'                  => $row['name'],
                'firstTimeFix'          => $row['firstTimeFix'],
                'attemptedFirstTimeFix' => $row['attemptedFirstTimeFix'],
                'totalRaised'           => $row['totalRaised']
            ];
            $totalRaised         += $row['totalRaised'];
            $totalAttempted      += $row['attemptedFirstTimeFix'];
            $totalAchieved       += $row['firstTimeFix'];
        }
        $monthlyFigures                          = $this->getRunningMonthFirstTimeFixedFigures();
        $data['firstTimeFixAttemptedPct']        = $totalRaised > 0 ? round(
            ($totalAttempted / $totalRaised) * 100
        ) : 'N/A';
        $data['firstTimeFixAchievedPct']         = $totalRaised > 0 ? round(
            ($totalAchieved / $totalRaised) * 100
        ) : 'N/A';
        $data['phonedThroughRequests']           = $totalRaised;
        $data['monthlyFirstTimeFixAttemptedPct'] = $monthlyFigures['totalRaised'] ? round(
            ($monthlyFigures['attemptedFirstTimeFix'] / $monthlyFigures['totalRaised']) * 100
        ) : 'N/A';
        $data['monthlyFirstTimeFixAchievedPct']  = $monthlyFigures['totalRaised'] > 0 ? round(
            ($monthlyFigures['firstTimeFix'] / $monthlyFigures['totalRaised']) * 100
        ) : 'N/A';
        $data['monthlyPhonedThroughRequests']    = $monthlyFigures['totalRaised'];
        return $data;

    }

    function updateAll()
    {
        global $db;
        $db->query(
            "INSERT INTO user_time_log (
  userID,
  teamLevel,
  loggedDate,
  dayHours,
  startedTime,
  loggedHours
)
SELECT
  consultant.`cns_consno`,
  team.`level`,
  CURRENT_DATE,
  consultant.`standardDayHours`,
  MIN(callactivity.`caa_starttime`),
  0
fROM
  consultant
  JOIN team
    ON team.`teamID` = consultant.`teamID`
  JOIN callactivity
    ON callactivity.`caa_consno` = consultant.`cns_consno`
    AND callactivity.`caa_date` = CURRENT_DATE
    AND callactivity.`caa_starttime` IS NOT NULL
    AND callactivity.`caa_endtime` IS NOT NULL
  LEFT JOIN user_time_log
    ON user_time_log.`userID` = callactivity.`caa_consno`
    AND loggedDate = CURRENT_DATE
WHERE user_time_log.`userTimeLogID` IS NULL
  AND consultant.`activeFlag` = 'Y'
  AND cns_consno <> 67
  AND cns_consno IS NOT NULL
  GROUP BY consultant.`cns_consno`"
        );
        $firstTimeFix   = $this->getFirstTimeFixData();
        $fixedAndReopen = $this->getFixedAndReopenData();
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
    }

}