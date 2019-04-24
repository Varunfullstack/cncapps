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
    /** @var DBEQuestionnaire */
    public $dbeQuestionnaire;
    /** @var DBEQuestion */
    public $dbeQuestion;
    /** @var DBEAnswerType */
    public $dbeAnswerType;
    /** @var DBEAnswer */
    public $dbeAnswer;
    /** @var DBEJQuestion */
    public $dbeJQuestion;

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

    /**
     * @param $dsData
     * @return bool
     */
    function updateQuestionnaire(&$dsData)
    {
        $this->setMethodName('updateQuestionnaire');
        $this->updateDataAccessObject($dsData, $this->dbeQuestionnaire);
        return TRUE;
    }

    /**
     * @param $ID
     * @param $dsResults
     * @return bool
     */
    function getQuestionnaireByID($ID, &$dsResults)
    {
        $this->dbeQuestionnaire->setPKValue($ID);
        $this->dbeQuestionnaire->getRow();
        return ($this->getData($this->dbeQuestionnaire, $dsResults));
    }

    /**
     * @param $dsResults
     * @return bool
     */
    function getAll(&$dsResults)
    {
        $this->dbeQuestionnaire->getRows('description');
        return ($this->getData($this->dbeQuestionnaire, $dsResults));
    }

    /**
     *    canDeleteQuestionnaire
     * Only allowed if this questionnaire has no answers
     * @param $ID
     * @return bool
     */
    function canDelete($ID)
    {
        $dbeQuestion = new DBEQuestion($this);
        $dbeQuestion->setValue(DBEQuestion::questionnaireID, $ID);
        return $dbeQuestion->countRowsByColumn(DBEQuestion::questionnaireID) < 1;
    }

    /**
     * @param $dsData
     * @return bool
     */
    function updateQuestion(&$dsData)
    {
        $this->setMethodName('updateQuestion');
        $this->updateDataAccessObject($dsData, $this->dbeQuestion);
        return TRUE;
    }

    /**
     * @param $ID
     * @param $dsResults
     * @return bool
     */
    function getQuestionByID($ID, &$dsResults)
    {
        $this->dbeQuestion->setPKValue($ID);
        $this->dbeQuestion->getRow();
        return ($this->getData($this->dbeQuestion, $dsResults));
    }

    /**
     * @param $questionnaireID
     * @param $dsResults
     * @return bool
     */
    function getAllQuestions($questionnaireID, &$dsResults)
    {
        $this->dbeJQuestion->getRowsByQuestionnaireID($questionnaireID);

        return ($this->getData($this->dbeJQuestion, $dsResults));
    }

    /**
     * @param $ID
     * @return bool
     */
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
     * @param $ID
     * @return bool
     */
    function canDeleteQuestion($ID)
    {
        $dbeAnswer = new DBEAnswer($this);
        $dbeAnswer->setValue(DBEAnswer::questionID, $ID);
        return $dbeAnswer->countRowsByColumn(DBEAnswer::questionID) < 1;
    }
}
