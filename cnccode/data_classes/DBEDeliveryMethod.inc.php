<?php /*
* Order delivery method table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEDeliveryMethod extends DBEntity
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
        $this->setTableName("delivery");
        $this->addColumn("deliveryMethodID", DA_ID, DA_NOT_NULL, "del_delno");
        $this->addColumn("description", DA_STRING, DA_NOT_NULL, "del_desc");
        $this->addColumn("sendNoteFlag", DA_YN, DA_NOT_NULL, "del_send_note");
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>