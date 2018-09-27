<?php /*
* Teams
* @authors Karim Ahmed
* @access public
*/

require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBETeam extends DBEntity
{

    const TeamID = "teamID";
    const Name = "name";
    const TeamRoleID = "teamRoleID";
    const Level = "level";
    const ActiveFlag = "activeFlag";


    /**
     * calls constructor()
     * @access public
     * @return void
     * @param  void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("team");
        $this->addColumn(self::TeamID, DA_ID, DA_NOT_NULL);
        $this->addColumn(self::Name, DA_STRING, DA_NOT_NULL);
        $this->addColumn(self::TeamRoleID, DA_ID, DA_NOT_NULL);
        $this->addColumn(self::Level, DA_INTEGER, DA_NOT_NULL);
        $this->addColumn(self::ActiveFlag, DA_YN, DA_NOT_NULL);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>