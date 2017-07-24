<?php /*
* Questionnaire table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEAnswer extends DBEntity
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
        $this->setTableName("answer");
        $this->addColumn("answerID", DA_ID, DA_NOT_NULL, "ans_answerno");
        $this->addColumn("questionID", DA_ID, DA_NOT_NULL, "ans_questionno");
        $this->addColumn("problemID", DA_ID, DA_NOT_NULL, "ans_problemno");
        $this->addColumn("answer", DA_STRING, DA_NOT_NULL, "ans_answer");
        $this->addColumn("name", DA_STRING, DA_NOT_NULL, "ans_name");
        $this->addColumn("date", DA_DATE, DA_NOT_NULL, "ans_date");
        $this->setAddColumnsOff();
        $this->setPK(0);
    }
}

?>
