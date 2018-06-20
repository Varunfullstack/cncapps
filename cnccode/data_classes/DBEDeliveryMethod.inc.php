<?php /*
* Order delivery method table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEDeliveryMethod extends DBEntity
{
    const deliveryMethodID = "deliveryMethodID";
    const description = "description";
    const sendNoteFlag = "sendNoteFlag";

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
        $this->addColumn(
            self::deliveryMethodID,
            DA_ID,
            DA_NOT_NULL,
            "del_delno"
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL,
            "del_desc"
        );
        $this->addColumn(
            self::sendNoteFlag,
            DA_YN,
            DA_NOT_NULL,
            "del_send_note"
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>