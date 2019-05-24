<?php /*
* Teams
* @authors Karim Ahmed
* @access public
*/

require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBETeam extends DBEntity
{

    const teamID = "teamID";
    const name = "name";
    const teamRoleID = "teamRoleID";
    const level = "level";
    const activeFlag = "activeFlag";


    /**
     * calls constructor()
     * @access public
     * @param void
     * @return void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("team");
        $this->addColumn(
            self::teamID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::name,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::teamRoleID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::level,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::activeFlag,
            DA_YN,
            DA_NOT_NULL
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>