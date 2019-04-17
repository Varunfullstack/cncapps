<?php /*
* Questionnaire table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEAnswer extends DBEntity
{
    const answerID = "answerID";
    const questionID = "questionID";
    const problemID = "problemID";
    const answer = "answer";
    const name = "name";
    const date = "date";

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
        $this->setTableName("answer");
        $this->addColumn(
            self::answerID,
            DA_ID,
            DA_NOT_NULL,
            "ans_answerno"
        );
        $this->addColumn(
            self::questionID,
            DA_ID,
            DA_NOT_NULL,
            "ans_questionno"
        );
        $this->addColumn(
            self::problemID,
            DA_ID,
            DA_NOT_NULL,
            "ans_problemno"
        );
        $this->addColumn(
            self::answer,
            DA_STRING,
            DA_NOT_NULL,
            "ans_answer"
        );
        $this->addColumn(
            self::name,
            DA_STRING,
            DA_NOT_NULL,
            "ans_name"
        );
        $this->addColumn(
            self::date,
            DA_DATE,
            DA_NOT_NULL,
            "ans_date"
        );
        $this->setAddColumnsOff();
        $this->setPK(0);
    }
}

?>
