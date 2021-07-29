<?php /**
 * Call expense type business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Business\BUActivity;
use Twig\Environment;

global $cfg;
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_bu"] . "/BUMail.inc.php");
require_once($cfg["path_dbe"] . "/DBEUser.inc.php");
require_once($cfg["path_dbe"] . "/DBETeam.inc.php");
require_once($cfg["path_func"] . "/Common.inc.php");

class BUUser extends Business
{
    /** @var DBEUser */
    public $dbeUser;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeUser = new DBEUser($this);
    }

    function updateUser(&$dsData)
    {
        $this->setMethodName('updateUser');
        $this->updateDataAccessObject(
            $dsData,
            $this->dbeUser
        );
        return TRUE;
    }

    /**
     * Get all users
     * @parameter DataSet &$dsResults results
     * @param $dsResults
     * @return bool : Success
     * @access public
     */
    function getAllUsers(&$dsResults)
    {
        $this->setMethodName('getAllUsers');
        $dbeUser = new DBEUser($this);
        $dbeUser->getActiveUsers();
        return ($this->getData(
            $dbeUser,
            $dsResults
        ));
    }

    /**
     * Get one users
     * @parameter integer $userID user
     * @parameter DataSet &$dsResults results
     * @param $userID
     * @param $dsResults
     * @return bool : Success
     * @access public
     */
    function getUserByID($userID,
                         &$dsResults
    )
    {
        $this->setMethodName('getUserByID');
        $dbeUser = new DBEUser($this);
        return ($this->getDatasetByPK(
            $userID,
            $dbeUser,
            $dsResults
        ));
    }

    /**
     * Delete one user
     * @param $ID
     * @return bool
     */
    function deleteUser($ID)
    {
        $this->setMethodName('deleteUser');
        if ($this->canDeleteUser()) {
            return $this->dbeUser->deleteRow($ID);
        } else {
            return FALSE;
        }
    }

    /**
     *    canDeleteUser
     * Only allowed if type has no activities
     * @param $id
     * @return bool
     */
    function canDeleteUser($id)
    {
        if ($id) {
            return false;
        }
        return FALSE;
    }

    function isSdManager($ID)
    {
        $this->dbeUser->getRow($ID);
        return ($this->dbeUser->getValue(DBEUser::receiveSdManagerEmailFlag) == 'Y');
    }

    /**
     * Create a record on user_time_log which indicates the user has not logged any time today
     *
     * @param $userID
     * @param $startDate
     * @param $days
     * @param string $sickTime
     * @param DBEUser|DataSet $reporter
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    function setUserAbsent($userID,
                           $startDate,
                           $days,
                           $sickTime,
                           $reporter
    )
    {
        global $db;
        $db->query(
            "SELECT
        team.level as teamLevel,
        consultant.standardDayHours
        
      FROM
        consultant
        JOIN team ON team.teamID = consultant.teamID
      WHERE
        cns_consno = $userID"
        );
        $db->next_record();
        $teamLevel        = $db->Record['teamLevel'];
        $standardDayHours = $db->Record['standardDayHours'];
        $thisYearBh       = common_getUKBankHolidays(date('Y'));
        $nextYearBh       = common_getUKBankHolidays(date('Y') + 1);
        if ($sickTime != 'F') {
            $days = 1;
        }
        $bankHolidays = array_merge(
            $thisYearBh,
            $nextYearBh
        );
        /*
        Create a record with zero hours logged for each workday
        */
        $dayCount       = 0;
        $loggedDayCount = 0;    // count of days that have been applied
        while ($loggedDayCount < $days) {

            $uDateToTry = strtotime($startDate . ' +' . $dayCount . ' day'); //UNIX
            $dayOfWeek  = date(
                'N',
                $uDateToTry
            );
            $dateToTry  = date(
                'Y-m-d',
                $uDateToTry
            );
            // Exclude bank holidays and weekends
            if (!in_array(
                    $dateToTry,
                    $bankHolidays
                ) & $dayOfWeek < 6) {

                $loggedDayCount++;
                $this->logAbsentDate(
                    $userID,
                    $teamLevel,
                    $standardDayHours,
                    $dateToTry,
                    $sickTime
                );
            }
            $dayCount++;
        }
        /** @var Environment */ global $twig;
        $dbeUser = new DBEUser($this);
        $dbeUser->getRow($userID);
        $subject = 'Staff Member ' . $dbeUser->getValue(DBEUser::name) . ' has been reported as sick';
        $body    = $twig->render('@internal/userReportedSickEmail.html.twig', [
            "staffName"      => $dbeUser->getValue(
                DBEUser::name
            ),
            "reporterName"   => $reporter->getValue(
                DBEUser::name
            ),
            "sickStartDate"  => DateTime::createFromFormat(
                DATE_MYSQL_DATE,
                $startDate
            )->format('d-m-Y'),
            "days"           => $days,
            "moreThanOneDay" => $days > 1,
            "isHalfDay"      => $sickTime !== 'F',
            "sickTime"       => $sickTime == 'A' ? 'morning' : 'afternoon'
        ]);
        $emailTo = "sicknessalert@" . CONFIG_PUBLIC_DOMAIN;
        $hdrs    = array(
            'From'         => CONFIG_SUPPORT_EMAIL,
            'To'           => $emailTo,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );
        $mime    = new Mail_mime();
        $mime->setHTMLBody($body);
        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body        = $mime->get($mime_params);
        $hdrs        = $mime->headers($hdrs);
        $buMail      = new BUMail($this);
        $buMail->putInQueue(
            CONFIG_SUPPORT_EMAIL,
            $emailTo,
            $hdrs,
            $body
        );
    }

    function logAbsentDate($userID,
                           $teamLevel,
                           $standardDayHours,
                           $date,
                           $sickTime
    )
    {
        global $db;
        $sql = "replace INTO user_time_log
        (
        `userID`,
        `teamLevel`,
        `loggedDate`,
        `loggedHours`,
        `dayHours`,
        `startedTime`,
         sickTime
        ) 
      VALUES 
        (
          ?,?,?,0,?,'00:00:00',?
        )";
        return $db->preparedQuery($sql, [
            ["value" => $userID, "type" => "i"],
            ["value" => $teamLevel, "type" => "i"],
            ["value" => $date, "type" => "s"],
            ["value" => $standardDayHours, "type" => "d"],
            ["value" => $sickTime, "type" => "s"],
        ]);
    }

    function logHalfHoliday($userID, $date)
    {
        global $db;
        $db->preparedQuery("insert into userHalfHolidays values(?,?)", [
            [
                "type"  => "i",
                "value" => $userID,
            ],
            [
                "type"  => "s",
                "value" => $date,
            ]
        ]);
        $buActivity = new BUActivity($this);
        $buActivity->updateTotalUserLoggedHours($userID, $date);
    }

    function userTimeHasBeenLogged($ID)
    {
        global $db;
        $db->query(
            "SELECT
        COUNT(*)
        
      FROM
        user_time_log
      WHERE
        userID = $ID
        AND loggedDate = DATE( NOW() )"
        );
        $db->next_record();
        return $db->Record[0];
    }

    function teamMembersPerformanceData($teamLevel,
                                        $hideExcluded = true
    )
    {
        global $db;
        $query = "SELECT 
                      userID,
                      user_time_log.loggedDate AS loggedDate,
                      loggedHours,
                      cncLoggedHours,
                      holiday,
                      holidayHours,       
                      CONCAT(
                        consultant.`firstName`,
                        ' ',
                        LEFT(consultant.`lastName`, 1)
                      ) AS userLabel 
                    FROM
                      user_time_log 
                      INNER JOIN 
                        (SELECT DISTINCT 
                          loggedDate 
                        FROM
                          user_time_log 
                        ORDER BY loggedDate DESC 
                        LIMIT 10) AS limited 
                        ON user_time_log.`loggedDate` = limited.loggedDate 
                      LEFT JOIN `consultant` 
                        ON userID = consultant.`cns_consno` 
                    WHERE teamLevel = ?  AND not isBankHoliday(user_time_log.loggedDate)
                      ";
        if ($hideExcluded) {
            $query .= ' and consultant.excludeFromStatsFlag <> "Y"';
        }
        $query     .= " ORDER BY userID,
                      user_time_log.loggedDate ASC";
        $statement = $db->preparedQuery($query, [["type" => "i", "value" => $teamLevel]]);
        $rows      = [];
        while ($row = $statement->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * @param $engineerID
     * @param DateTimeInterface $startDate
     * @param DateTimeInterface $endDate
     * @return array
     */
    function getEngineerDetailedData($engineerID,
                                     DateTimeInterface $startDate,
                                     DateTimeInterface $endDate
    )
    {
        global $db;
        $query = "
        SELECT 
  getLoggedTimeAvg (
    user_time_log.`userID`,
    user_time_log.`loggedDate`,
    20
  ) AS monthAvg,
  getLoggedTimeTotal (
    user_time_log.`userID`,
    user_time_log.`loggedDate`,
    20
  ) AS monthTotal,
  getToLogHours (
    user_time_log.`userID`,
    user_time_log.`loggedDate`,
    20
  ) AS monthToLog,
  getLoggedTimeAvg (
    user_time_log.`userID`,
    user_time_log.`loggedDate`,
    5
  ) AS fiveDaysAvg,              
  getLoggedTimeTotal (
    user_time_log.`userID`,
    user_time_log.`loggedDate`,
    5
  ) AS fiveDaysTotal,
  getToLogHours (
    user_time_log.`userID`,
    user_time_log.`loggedDate`,
    5
  ) AS fiveDaysToLog,
  loggedDate,
  user_time_log.`loggedHours`,
  user_time_log.`cncLoggedHours`,
  user_time_log.holidayHours,
  holiday,
  userID,
  CASE
    team.`level`
    WHEN 1 
    THEN 
    (SELECT 
      hed_hd_team_target_log_percentage 
    FROM
      headert 
    LIMIT 1) 
    WHEN 2 
    THEN 
    (SELECT 
      hed_es_team_target_log_percentage 
    FROM
      headert 
    LIMIT 1) 
    WHEN 3 
    THEN 
    (SELECT 
      hed_im_team_target_log_percentage 
    FROM
      headert 
    LIMIT 1) 
      when 5 then (select projectTeamTargetLogPercentage from headert)
    ELSE 0 
  END AS target 
FROM
  user_time_log 
  LEFT JOIN consultant 
    ON cns_consno = userID 
  LEFT JOIN team 
    ON `consultant`.`teamID` = team.`teamID`
WHERE userID = $engineerID 
  AND loggedDate >= '" . $startDate->format('Y-m-d') . "' 
  AND loggedDate <= '" . $endDate->format('Y-m-d') . "' 
  AND not isBankHoliday(loggedDate)
ORDER BY user_time_log.`loggedDate` DESC 
        ";
        $db->query($query);
        $rows = [];
        while ($db->next_record(1)) {
            $rows[] = $db->Record;
        }
        return $rows;
    }

    /*
    Activity logging performance for past number of days by user
    */
    function getUserPerformanceByUser($userID,
                                      $days = 8
    )
    {
        global $db;
        $db->query(
            "SELECT 
        cns_name,
        teamLevel,
        SUM( loggedHours+ cncLoggedHours ) AS loggedHours,
        SUM( dayHours ) AS dayHours,
        ( SUM( loggedHours+cncLoggedHours ) / SUM( dayHours ) ) * 100 AS performancePercentage
        
      FROM
        user_time_log 
        JOIN consultant 
          ON cns_consno = userID 
      WHERE
          loggedDate >= DATE_SUB( DATE( NOW() ), INTERVAL $days DAY )
          AND loggedDate < DATE( NOW() )
          AND userID = $userID 
          AND not isBankHoliday(loggedDate)
      GROUP BY cns_consno"
        );
        $db->next_record();
        return $db->Record;

    }

    function getUserPerformanceByUserBetweenDates($userId, DateTimeInterface $startDate, DateTimeInterface $endDate)
    {
        global $db;
        $statement = $db->preparedQuery("SELECT 
        cns_name,
        teamLevel,
        SUM( loggedHours + cncLoggedHours ) AS loggedHours,
        SUM( dayHours ) AS dayHours,
        ( SUM( loggedHours+cncLoggedHours ) / SUM( dayHours ) ) * 100 AS performancePercentage,
        sum(sickTime in ('A','P')) as halfSickDays,
        sum(sickTime = 'F') as fullSickDays
      FROM
        user_time_log 
        JOIN consultant 
          ON cns_consno = userID 
      WHERE
          loggedDate >= ? and loggedDate <= ?
          AND userID = ?    
          AND not isBankHoliday(loggedDate)
      GROUP BY cns_consno", [
                                                                      [
                                                                          "type"  => 's',
                                                                          "value" => $startDate->format(
                                                                              DATE_MYSQL_DATE
                                                                          ),
                                                                      ],
                                                                      [
                                                                          "type"  => 's',
                                                                          "value" => $endDate->format(DATE_MYSQL_DATE),
                                                                      ],
                                                                      [
                                                                          "type"  => 'i',
                                                                          "value" => $userId,
                                                                      ],
                                                                  ]);
        return $statement->fetch_array(MYSQLI_ASSOC);
    }

    public function getLevelByUserID($userID)
    {
        if ($userID) {
            $this->dbeUser->getRow($userID);
            $dbeTeam = new DBETeam($this);
            $dbeTeam->getRow($this->dbeUser->getValue(DBEUser::teamID));
            $ret = $dbeTeam->getValue(DBETeam::level);
        } else {
            $ret = 0;
        }
        return $ret;
    }

    function getUsersByTeamLevel($teamLevel)
    {
        global $db;
        $query = "SELECT 
        c.cns_consno,
        CONCAT( SUBSTR(c.firstName, 1, 1), SUBSTR(c.`lastName`,1, 1) ) AS initials,
        concat(c.firstName, ' ', c.lastName) as userName
      FROM
        consultant c
        JOIN team t ON c.teamID = t.teamID
      WHERE
        t.level = $teamLevel
        AND c.`activeFlag` = 'Y'
        and c.excludeFromStatsFlag <> 'Y'
      ORDER BY
        firstName, lastName";
        $db->query($query);
        $ret = array();
        while ($db->next_record()) {
            $ret[] = $db->Record;
        }
        return $ret;

    }

}