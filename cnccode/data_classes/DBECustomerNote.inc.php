<?php /*
* Customer note table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBECustomerNote extends DBEntity
{
    const customerNoteID = "customerNoteID";
    const customerID = "customerID";
    const created = "created";
    const modifiedUserID = "modifiedUserID";
    const details = "details";
    const createdUserID = "createdUserID";
    const orderID = "orderID";
    const modifiedAt = 'modifiedAt';

    /**
     * calls constructor()
     * @access public
     * @param void
     * @return void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("customernote");
        $this->addColumn(self::customerNoteID, DA_ID, DA_NOT_NULL, "cno_customernoteno");
        $this->addColumn(self::customerID, DA_ID, DA_NOT_NULL, "cno_custno");
        $this->addColumn(self::created, DA_DATETIME, DA_NOT_NULL, "cno_created");
        $this->addColumn(self::modifiedUserID, DA_ID, DA_NOT_NULL, "cno_modified_consno");
        $this->addColumn(self::details, DA_MEMO, DA_ALLOW_NULL, "cno_details");
        $this->addColumn(self::createdUserID, DA_ID, DA_NOT_NULL, "cno_created_consno");
        $this->addColumn(self::orderID, DA_ID, DA_ALLOW_NULL, "cno_ordno");
        $this->addColumn(self::modifiedAt, DA_DATETIME, DA_ALLOW_NULL, "cno_modified");
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>