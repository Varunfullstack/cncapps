<?php /*
* Questionnaire table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEQuestionnaire extends DBEntity
{
    const logo = "logo";
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
     * @param void
     * @return void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("questionnaire");
        $this->addColumn(
            self::questionnaireID,
            DA_ID,
            DA_NOT_NULL,
            "qur_questionnaireno"
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL,
            "qur_desc"
        );
        $this->addColumn(
            self::intro,
            DA_MEMO,
            DA_NOT_NULL,
            "qur_intro"
        );
        $this->addColumn(
            self::thankYou,
            DA_MEMO,
            DA_NOT_NULL,
            "qur_thank_you"
        );
        $this->addColumn(
            self::rating1Desc,
            DA_STRING,
            DA_NOT_NULL,
            "qur_rating_1_desc"
        );
        $this->addColumn(
            self::rating5Desc,
            DA_STRING,
            DA_NOT_NULL,
            "qur_rating_5_desc"
        );
        $this->addColumn(
            self::nameRequired,
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

