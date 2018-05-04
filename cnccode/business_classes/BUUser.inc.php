<?php /**
 * Call expense type business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEUser.inc.php");
require_once($cfg["path_dbe"] . "/DBETeam.inc.php");
require_once($cfg["path_func"] . "/Common.inc.php");

class BUUser extends Business
{
    var $dbeUser = "";

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
        $this->updateDataaccessObject($dsData, $this->dbeUser);
        return TRUE;
    }

    /**
     * Get all users
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getAllUsers(&$dsResults)
    {
        $this->setMethodName('getAllUsers');
        $dbeUser = new DBEUser($this);
        $dbeUser->getRows();
        return ($this->getData($dbeUser, $dsResults));
    }

    /**
     * Get one users
     * @parameter integer $userID user
     * @parameter DataSet &$dsResults results
     * @return bool : Success
     * @access public
     */
    function getUserByID($userID, &$dsResults)
    {
        $this->setMethodName('getUserByID');
        $dbeUser = new DBEUser($this);
        return ($this->getDatasetByPK($userID, $dbeUser, $dsResults));
    }

    /**
     * Delete one user
     */
    function deleteUser($ID)
    {
        $this->setMethodName('deleteUser');
        if ($this->canDeleteUser($ID)) {
            return $this->dbeUser->deleteRow($ID);
        } else {
            return FALSE;
        }
    }

    /**
     *    canDeleteUser
     * Only allowed if type has no activities
     */
    function canDeleteUser($ID)
    {
        /*
                $dbeExpense = new DBEExpense($this);
                // validate no activities of this type
                $dbeExpense->setValue('expenseTypeID', $ID);
                if ( $dbeExpense->countRowsByColumn('expenseTypeID') < 1 ){
                    return TRUE;
                }
                else{
                    return FALSE;
                }
        */
        return FALSE;
    }

    function isSdManager($ID)
    {
        $this->dbeUser->getRow($ID);
        if ($this->dbeUser->getValue('receiveSdManagerEmailFlag') == 'Y') {
            return true;
        } else {
            return false;
        }
    }

    /*
    Create a record on user_time_log which indicates the user has not
    logged any time today
    */
    function setUserAbsent($userID, $startDate, $days)
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
        $teamLevel = $db->Record['teamLevel'];
        $standardDayHours = $db->Record['standardDayHours'];

        $thisYearBh = common_getUKBankHolidays(date('Y'));
        $nextYearBh = common_getUKBankHolidays(date('Y') + 1);

        $bankHolidays =
            array_merge(
                $thisYearBh,
                $nextYearBh
            );
        /*
        Create a record with zero hours logged for each workday
        */
        $dayCount = 0;

        $loggedDayCount = 0;    // count of days that have been applied

        while ($loggedDayCount < $days) {

            $dayCount++;

            $uDateToTry = strtotime($startDate . ' +' . $dayCount . ' day'); //UNIX

            $dayOfWeek = date('N', $uDateToTry);

            $dateToTry = date('Y-m-d', $uDateToTry);

            // Exclude bank holidays and weekends

            if (!in_array($dateToTry, $bankHolidays) & $dayOfWeek < 6) {

                $loggedDayCount++;

                $this->logAbsentDate($userID, $teamLevel, $standardDayHours, $dateToTry);
            }
        }

    }

    function logAbsentDate($userID, $teamLevel, $standardDayHours, $date)
    {
        global $db;

        $sql =
            "replace INTO user_time_log
        (
        `userID`,
        `teamLevel`,
        `loggedDate`,
        `loggedHours`,
        `dayHours`,
        `startedTime` 
        ) 
      VALUES 
        (
          " . $userID . ",
          " . $teamLevel . ",
          '" . $date . "',
          0,
          " . $standardDayHours . ",
          '00:00:00'
        )";
        $db->query($sql);
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

    /*
    Activity logging performance for past number of days by user
    */
    function getUserPerformanceByUser($userID, $days = 8)
    {
        global $db;

        $db->query(
            "SELECT 
        cns_name,
        teamLevel,
        SUM( loggedHours ) AS loggedHours,
        SUM( dayHours ) AS dayHours,
        ( SUM( loggedHours ) / SUM( dayHours ) ) * 100 AS performancePercentage
        
      FROM
        user_time_log 
        JOIN consultant 
          ON cns_consno = userID 

      WHERE
          loggedDate >= DATE_SUB( DATE( NOW() ), INTERVAL $days DAY )
          AND loggedDate < DATE( NOW() )
          AND userID = $userID    
      GROUP BY cns_consno"
        );

        $db->next_record();

        return $db->Record;

    }

    public function getLevelByUserID($userID)
    {
        if ($userID) {
            $this->dbeUser->getRow($userID);

            $dbeTeam = new DBETeam($this);
            $dbeTeam->getRow($this->dbeUser->getValue('teamID'));
            $ret = $dbeTeam->getValue('level');
        } else {
            $ret = 0;
        }


        return $ret;
    }

    function getUsersByTeamLevel($teamLevel)
    {
        global $db;

        $db->query(
            "SELECT 
        c.cns_consno,
        CONCAT( SUBSTR(c.firstName, 1, 1), SUBSTR(c.`lastName`,1, 1) ) AS initials
        
      FROM
        consultant c
        JOIN team t ON c.teamID = t.teamID

      WHERE
        t.level = $teamLevel
        AND c.`activeFlag` = 'Y'

      ORDER BY
        firstName, lastName"
        );

        $ret = array();

        while ($db->next_record()) {
            $ret[] = $db->Record;
        }

        return $ret;

    }

}// End of class
?>