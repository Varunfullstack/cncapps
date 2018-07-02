<?php /*
* Customer type table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBELeadStatus extends DBEntity
{

    const leadStatusID = "leadStatusID";
    const description = "description";

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
        $this->setTableName("leadstatus");
        $this->addColumn(
            self::leadStatusID,
            DA_ID,
            DA_NOT_NULL,
            "lst_leadstatusno"
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL,
            "lst_desc"
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>