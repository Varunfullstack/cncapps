<?php /*
* Questionnaire table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEStaffAppraisalQuestionnaire extends DBEntity
{
    const id = "id";
    const description = "description";

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
        $this->setTableName("staffAppraisalQuestionnaire");
        $this->addColumn(
            self::id,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL
        );

        $this->setAddColumnsOff();
        $this->setPK(0);
    }
}

?>
