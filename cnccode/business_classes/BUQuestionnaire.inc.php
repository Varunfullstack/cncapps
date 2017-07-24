<?php /**
 * Questionnaire business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEQuestionnaire.inc.php");
require_once($cfg["path_dbe"] . "/DBEQuestion.inc.php");
require_once($cfg["path_dbe"] . "/DBEJQuestion.inc.php");
require_once($cfg["path_dbe"] . "/DBEAnswerType.inc.php");
require_once($cfg["path_dbe"] . "/DBEAnswer.inc.php");

class BUQuestionnaire extends Business
{
    var $dbeQuestionnaire = "";
    var $dbeQuestion = "";
    var $dbeAnswerType = "";
    var $dbeAnswer = "";

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeQuestionnaire = new DBEQuestionnaire($this);
        $this->dbeQuestion = new DBEQuestion($this);
        $this->dbeJQuestion = new DBEJQuestion($this);
        $this->dbeAnswerType = new DBEAnswerType($this);
        $this->dbeAnswer = new DBEAnswer($this);
    }

    function updateQuestionnaire(&$dsData)
    {
        $this->setMethodName('updateQuestionnaire');
        $this->updateDataaccessObject($dsData, $this->dbeQuestionnaire);
        return TRUE;
    }

    function getQuestionnaireByID($ID, &$dsResults)
    {
        $this->dbeQuestionnaire->setPKValue($ID);
        $this->dbeQuestionnaire->getRow();
        return ($this->getData($this->dbeQuestionnaire, $dsResults));
    }

    function getAll(&$dsResults)
    {
        $this->dbeQuestionnaire->getRows('description');

        return ($this->getData($this->dbeQuestionnaire, $dsResults));
    }

    function deleteQuestionnaire($ID)
    {
        $this->setMethodName('deleteQuestionnaire');
        if ($this->canDeleteQuestionnaire($ID)) {
            return $this->dbeQuestionnaire->deleteRow($ID);
        } else {
            return FALSE;
        }
    }

    /**
     *    canDeleteQuestionnaire
     * Only allowed if this questionnaire has no answers
     */
    function canDelete($ID)
    {

        $dbeQuestion = new DBEQuestion($this);

        $dbeQuestion->setValue('questionnaireID', $ID);

        if ($dbeQuestion->countRowsByColumn('questionnaireID') < 1) {

            $ret = false;


        } else {

            $ret = FALSE;

        }

        return $ret;

    }

    function updateQuestion(&$dsData)
    {
        $this->setMethodName('updateQuestion');
        $this->updateDataaccessObject($dsData, $this->dbeQuestion);
        return TRUE;
    }

    function getQuestionByID($ID, &$dsResults)
    {
        $this->dbeQuestion->setPKValue($ID);
        $this->dbeQuestion->getRow();
        return ($this->getData($this->dbeQuestion, $dsResults));
    }

    function getAllQuestions($questionnaireID, &$dsResults)
    {
        $this->dbeJQuestion->getRowsByQuestionnaireID($questionnaireID);

        return ($this->getData($this->dbeJQuestion, $dsResults));
    }

    function deleteQuestion($ID)
    {
        $this->setMethodName('deleteQuestion');
        if ($this->canDeleteQuestion($ID)) {
            return $this->dbeQuestion->deleteRow($ID);
        } else {
            return FALSE;
        }
    }

    /**
     *  canDeleteQuestion
     * Only allowed if this question has no answers
     */
    function canDeleteQuestion($ID)
    {

        $dbeAnswer = new DBEAnswer($this);

        $dbeAnswer->setValue('questionID', $ID);

        if ($dbeAnswer->countRowsByColumn('questionID') < 1) {

            $ret = false;


        } else {

            $ret = FALSE;

        }

        return $ret;

    }
}// End of class
?>