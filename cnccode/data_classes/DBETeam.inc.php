<?php /*
* Teams
* @authors Karim Ahmed
* @access public
*/

require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBETeam extends DBEntity
{
    /**
     * calls constructor()
     * @access public
     * @return void
     * @param  void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        $this->constructor($owner);
    }

    /**
     * constructor
     * @access public
     * @return void
     * @param  void
     */
    function constructor(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("team");
        $this->addColumn("teamID", DA_ID, DA_NOT_NULL);
        $this->addColumn("name", DA_STRING, DA_NOT_NULL);
        $this->addColumn("teamRoleID", DA_ID, DA_NOT_NULL);
        $this->addColumn("level", DA_INTEGER, DA_NOT_NULL);
        $this->addColumn("activeFlag", DA_YN, DA_NOT_NULL);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>