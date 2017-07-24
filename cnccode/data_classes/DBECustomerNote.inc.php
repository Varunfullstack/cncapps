<?php /*
* Customer note table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBECustomerNote extends DBEntity
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
        $this->setTableName("customernote");
        $this->addColumn("customerNoteID", DA_ID, DA_NOT_NULL, "cno_customernoteno");
        $this->addColumn("customerID", DA_ID, DA_NOT_NULL, "cno_custno");
        $this->addColumn("created", DA_DATETIME, DA_NOT_NULL, "cno_created");
        $this->addColumn("modifiedUserID", DA_ID, DA_NOT_NULL, "cno_modified_consno");
        $this->addColumn("details", DA_MEMO, DA_ALLOW_NULL, "cno_details");
        $this->addColumn("createdUserID", DA_ID, DA_NOT_NULL, "cno_created_consno");
        $this->addColumn("orderID", DA_ID, DA_ALLOW_NULL, "cno_ordno");
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>