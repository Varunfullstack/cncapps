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
            "questionnaireID",
            DA_ID,
            DA_NOT_NULL,
            "qur_questionnaireno"
        );
        $this->addColumn(
            "description",
            DA_STRING,
            DA_NOT_NULL,
            "qur_desc"
        );
        $this->addColumn(
            "intro",
            DA_MEMO,
            DA_NOT_NULL,
            "qur_intro"
        );
        $this->addColumn(
            "thankYou",
            DA_MEMO,
            DA_NOT_NULL,
            "qur_thank_you"
        );
        $this->addColumn(
            "rating1Desc",
            DA_STRING,
            DA_NOT_NULL,
            "qur_rating_1_desc"
        );
        $this->addColumn(
            "rating5Desc",
            DA_STRING,
            DA_NOT_NULL,
            "qur_rating_5_desc"
        );
        $this->addColumn(
            "nameRequired",
            DA_STRING,
            DA_YN,
            "qur_name_required"
        );
        $this->setAddColumnsOff();
        $this->setPK(0);
    }
}

?>
