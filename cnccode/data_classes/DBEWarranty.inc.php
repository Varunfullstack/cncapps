<?php /*
* Warranty table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBEWarranty extends DBCNCEntity
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
        $this->setTableName("contract");
        $this->addColumn("warrantyID", DA_ID, DA_NOT_NULL, "cnt_contno");
        $this->addColumn("description", DA_STRING, DA_NOT_NULL, "cnt_desc");
        $this->addColumn("years", DA_INTEGER, DA_ALLOW_NULL, "cnt_years");
        $this->addColumn("manufacturerID", DA_ID, DA_ALLOW_NULL, "cnt_manno");
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>