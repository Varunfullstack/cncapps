<?php /*
* Question table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEQuestion extends DBEntity
{
    const questionID = "questionID";
    const questionnaireID = "questionnaireID";
    const answerTypeID = "answerTypeID";
    const description = "description";
    const activeFlag = "activeFlag";
    const requiredFlag = "requiredFlag";
    const weight = "weight";
    const multiOptions = "multiOptions";
    const multiChoice = "multiChoice";

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
        $this->setTableName("question");
        $this->addColumn(self::questionID, DA_ID, DA_NOT_NULL, "que_questionno");
        $this->addColumn(self::questionnaireID, DA_ID, DA_NOT_NULL, "que_questionnaireno");
        $this->addColumn(self::answerTypeID, DA_ID, DA_NOT_NULL, "que_answertypeno");
        $this->addColumn(self::description, DA_STRING, DA_NOT_NULL, "que_desc");
        $this->addColumn(self::activeFlag, DA_YN, DA_NOT_NULL, "que_active_flag");
        $this->addColumn(self::requiredFlag, DA_YN, DA_NOT_NULL, "que_required_flag");
        $this->addColumn(self::weight, DA_INTEGER, DA_NOT_NULL, "que_weight");
        $this->addColumn(self::multiOptions, DA_JSON_ARRAY, DA_ALLOW_NULL);
        $this->addColumn(self::multiChoice, DA_BOOLEAN, DA_NOT_NULL, null, 0);
        $this->setAddColumnsOff();
        $this->setPK(0);
    }
}
