<?php /*
* Call activity thread table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEActivityThread extends DBEntity
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
        parent::__construct($owner);
        $this->setTableName("activitythread");
        $this->addColumn("activityThreadID", DA_ID, DA_NOT_NULL, "att_activitythreadno");
        $this->addColumn("customerID", DA_INTEGER, DA_ALLOW_NULL, "att_custno");
        $this->setAddColumnsOff();
        $this->setPK(0);
    }
}

?>