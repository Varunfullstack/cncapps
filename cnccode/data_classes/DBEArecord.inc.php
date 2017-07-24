<?php /*
* AnswerType table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEArecord extends DBEntity
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
        $this->setTableName("arecord");
        $this->addColumn("arecordID", DA_ID, DA_NOT_NULL, "are_arecordno");
        $this->addColumn("customerItemID", DA_ID, DA_NOT_NULL, "are_custitemno");
        $this->addColumn("name", DA_STRING, DA_NOT_NULL, "are_name");
        $this->addColumn("type", DA_STRING, DA_NOT_NULL, "are_type");
        $this->addColumn("function", DA_STRING, DA_NOT_NULL, "are_function");
        $this->addColumn("destinationIp", DA_STRING, DA_NOT_NULL, "are_destination_ip");
        $this->setAddColumnsOff();
        $this->setPK(0);
    }
}

?>
