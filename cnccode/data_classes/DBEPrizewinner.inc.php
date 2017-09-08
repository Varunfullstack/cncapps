<?php /*
* Questionnaire table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEPrizewinner extends DBEntity
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
        $this->setTableName("prizewinner");
        $this->addColumn("prizewinnerID", DA_ID, DA_NOT_NULL, "prz_prizewinnerno");
        $this->addColumn("yearMonth", DA_TEXT, DA_NOT_NULL, "prz_yearmonth");
        $this->addColumn("contactID", DA_ID, DA_NOT_NULL, "prz_contno");
        $this->addColumn("approvedFlag", DA_YN, DA_NOT_NULL, "prz_approved_flag");

        $this->setAddColumnsOff();
        $this->setPK(0);
    }
}

?>
