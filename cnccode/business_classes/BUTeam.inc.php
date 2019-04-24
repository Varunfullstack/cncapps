<?php /**
 * Call further action business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBETeam.inc.php");
require_once($cfg["path_dbe"] . "/DBEUser.inc.php");

class BUTeam extends Business
{
    /** @var DBETeam */
    public $dbeTeam;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeTeam = new DBETeam($this);
    }

    /**
     * @param $dsData
     * @return bool
     */
    function updateTeam(&$dsData)
    {
        $this->setMethodName('updateTeam');
        $this->updateDataAccessObject($dsData, $this->dbeTeam);
        return TRUE;
    }

    /**
     * @param $ID
     * @param $dsResults
     * @return bool
     */
    function getTeamByID($ID, &$dsResults)
    {
        $this->dbeTeam->setPKValue($ID);
        $this->dbeTeam->getRow();
        return ($this->getData($this->dbeTeam, $dsResults));
    }

    function getAll()
    {
        global $db;

        $sql =
            "SELECT
        t.teamID,
        t.name,
        t.teamRoleID,
        t.level,
        t.activeFlag,
        tr.name AS teamRoleName         
      FROM
        team t
        JOIN team_role tr ON tr.teamRoleID = t.teamRoleID
      ORDER BY
        t.name";

        $db->query($sql);
        $ret = array();
        while ($db->next_record()) {
            $ret[] = $db->Record;
        }

        return ($ret);
    }

    function deleteTeam($ID)
    {
        $this->setMethodName('deleteTeam');
        if ($this->canDelete($ID)) {
            return $this->dbeTeam->deleteRow($ID);
        } else {
            return FALSE;
        }
    }

    function getTeamRoles()
    {
        global $db;

        $sql =
            "SELECT
        teamRoleID,
        name
      FROM
        team_role
      ORDER BY
        name";

        $db->query($sql);
        $ret = array();
        while ($db->next_record()) {
            $ret[] = $db->Record;
        }

        return ($ret);
    }

    /**
     *    canDelete
     * @param $ID
     * @return bool
     */
    function canDelete($ID)
    {
        $dbeUser = new DBEUser($this);
        $dbeUser->setValue(DBEJUser::teamID, $ID);
        return $dbeUser->countRowsByColumn(DBEJUser::teamID) < 1;
    }
}
