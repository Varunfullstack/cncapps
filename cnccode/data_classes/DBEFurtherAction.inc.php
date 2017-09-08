<?php /*
* Further Action table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEFurtherAction extends DBEntity
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
        $this->setTableName("further_action");
        $this->addColumn("furtherActionID", DA_ID, DA_NOT_NULL);
        $this->addColumn("description", DA_STRING, DA_NOT_NULL);
        $this->addColumn("emailAddress", DA_STRING, DA_ALLOW_NULL);
        $this->addColumn("requireDate", DA_YN_FLAG, DA_ALLOW_NULL);
        $this->addColumn("emailBody", DA_MEMO, DA_ALLOW_NULL);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>