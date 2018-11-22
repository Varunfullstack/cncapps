<?php /*
* Questionnaire table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEQuestionnaire extends DBEntity
{
    const logo = "logo";

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
        $this->setTableName("questionnaire");
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
        $this->addColumn(
            self::logo,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->setAddColumnsOff();
        $this->setPK(0);
    }
}

?>
