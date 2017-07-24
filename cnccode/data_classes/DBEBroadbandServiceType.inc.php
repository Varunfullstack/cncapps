<?php /*
* Call activity thread table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEBroadbandServiceType extends DBEntity
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
        $this->setTableName("broadbandservicetype");
        $this->addColumn("broadbandServiceTypeID", DA_ID, DA_NOT_NULL);
        $this->addColumn("description", DA_TEXT, DA_NOT_NULL);
        $this->setAddColumnsOff();
        $this->setPK(0);
    }

}

?>