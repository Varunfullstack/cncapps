<?php /*
* Questionnaire table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEStaffAppraisalQuestionAnswer extends DBEntity
{

    const answerID = "answerID";
    const questionID = "questionID";
    const questionnaireAnswerID = "questionnaireAnswerID";
    const staffAnswer = "staffAnswer";
    const managerAnswer = "managerAnswer";
    const managerComment = "managerComment";

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
        $this->setTableName("staffAppraisalQuestionAnswer");
        $this->addColumn(
            self::answerID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::questionID,
            DA_ID,
            DA_NOT_NULL
        );

        $this->addColumn(
            self::questionnaireAnswerID,
            DA_ID,
            DA_NOT_NULL
        );

        $this->addColumn(
            self::staffAnswer,
            DA_STRING,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::managerAnswer,
            DA_STRING,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::managerComment,
            DA_STRING,
            DA_ALLOW_NULL
        );


        $this->setAddColumnsOff();
        $this->setPK(0);
    }

    public function getRowByIDAndQuestionnaireAnswerID($questionID,
                                                       $questionnaireAnswerID
    )
    {
        $query = "select " . $this->getDBColumnNamesAsString() .
            " from " . $this->getTableName() . " where " . $this->getDBColumnName(
                self::questionID
            ) . " =  $questionID and " . $this->getDBColumnName(
                self::questionnaireAnswerID
            ) . " = $questionnaireAnswerID";
        $this->setQueryString($query);
        $this->getRow();
    }
}

?>
