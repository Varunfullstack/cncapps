<?php /*
* Questionnaire table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEStaffAppraisalQuestionnaire extends DBEntity
{
    const questionnaireID = "questionnaireID";
    const description = "description";
    const intro = "intro";
    const thankYou = "thankYou";
    const rating1Desc = "rating1Desc";
    const rating5Desc = "rating5Desc";
    const nameRequired = "nameRequired";

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
            self::questionnaireID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::intro,
            DA_MEMO,
            DA_NOT_NULL
        );

        $this->setAddColumnsOff();
        $this->setPK(0);
    }
}

?>
