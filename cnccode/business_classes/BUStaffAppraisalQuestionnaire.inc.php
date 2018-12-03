<?php /**
 * Questionnaire business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEStaffAppraisalQuestionnaire.inc.php");
require_once($cfg["path_dbe"] . "/DBEStaffAppraisalQuestion.inc.php");
require_once($cfg["path_dbe"] . "/DBEJStaffAppraisalQuestion.inc.php");
require_once($cfg["path_dbe"] . "/DBEAnswerType.inc.php");
require_once($cfg["path_dbe"] . "/DBEStaffAppraisalQuestionAnswer.inc.php");
require_once($cfg["path_dbe"] . "/DBEStaffAppraisalQuestionnaireAnswer.php");

class BUStaffAppraisalQuestionnaire extends Business
{
    /** @var DBEStaffAppraisalQuestionnaire */
    public $dbeQuestionnaire;
    /** @var DBEStaffAppraisalQuestion */
    public $dbeQuestion;
    /** @var DBEAnswerType */
    public $dbeAnswerType;
    /** @var DBEStaffAppraisalQuestionAnswer */
    public $dbeAnswer;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeQuestionnaire = new DBEStaffAppraisalQuestionnaire($this);
        $this->dbeQuestion = new DBEStaffAppraisalQuestion($this);
        $this->dbeJQuestion = new DBEJStaffAppraisalQuestion($this);
        $this->dbeAnswerType = new DBEAnswerType($this);
        $this->dbeAnswer = new DBEStaffAppraisalQuestionAnswer($this);
    }

    function updateQuestionnaire(&$dsData)
    {
        $this->setMethodName('updateQuestionnaire');
        $this->updateDataaccessObject(
            $dsData,
            $this->dbeQuestionnaire
        );
        return TRUE;
    }

    function getQuestionnaireByID($ID,
                                  &$dsResults
    )
    {
        $this->dbeQuestionnaire->setPKValue($ID);
        $this->dbeQuestionnaire->getRow();
        return ($this->getData(
            $this->dbeQuestionnaire,
            $dsResults
        ));
    }

    function getAll(&$dsResults)
    {
        $this->dbeQuestionnaire->getRows('description');

        return ($this->getData(
            $this->dbeQuestionnaire,
            $dsResults
        ));
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

        $dbeQuestion = new DBEStaffAppraisalQuestion($this);

        $dbeQuestion->setValue(
            'questionnaireID',
            $ID
        );

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
        $this->updateDataaccessObject(
            $dsData,
            $this->dbeQuestion
        );
        return TRUE;
    }

    function getQuestionByID($ID,
                             &$dsResults
    )
    {
        $this->dbeQuestion->setPKValue($ID);
        $this->dbeQuestion->getRow();
        return ($this->getData(
            $this->dbeQuestion,
            $dsResults
        ));
    }

    function getAllQuestions($questionnaireID,
                             &$dsResults
    )
    {
        $this->dbeJQuestion->getRowsByQuestionnaireID($questionnaireID);

        return ($this->getData(
            $this->dbeJQuestion,
            $dsResults
        ));
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

        $dbeAnswer = new DBEStaffAppraisalQuestionAnswer($this);

        $dbeAnswer->setValue(
            'questionID',
            $ID
        );

        if ($dbeAnswer->countRowsByColumn('questionID') < 1) {

            $ret = false;


        } else {

            $ret = FALSE;

        }

        return $ret;

    }

    public function getStats($managerID)
    {

        global $db;
        $db->query(
            "SELECT * FROM StaffAppraisalQuestionnaire LEFT JOIN 

(SELECT 
       questionnaireID,
  SUM(IF(NOT a.staffCompleted, 1, 0)) AS staffPending,
  SUM(a.`staffCompleted`) AS managerPending,
  SUM(a.`managerCompleted`) AS completed
FROM
  StaffAppraisalQuestionnaireAnswer a 
WHERE a.managerID = $managerID
GROUP BY a.`questionnaireID`) stats ON stats.questionnaireID = id"
        );

        $stats = [];

        while ($db->next_record(MYSQLI_ASSOC)) {
            $stats[] = [
                "id"             => $db->Record['id'],
                "description"    => $db->Record['description'],
                "staffPending"   => $db->Record['staffPending'],
                "managerPending" => $db->Record['managerPending'],
                'completed'      => $db->Record['completed'],
                'dateSent'       => $db->Record['dateSent']
            ];

        };
        return $stats;
    }

    public function getManagerData($managerID,
                                   $type,
                                   $questionnaireID
    )
    {
        global $db;
        $db->query(
            "SELECT 
  * 
FROM
  (SELECT 
    questionnaireID,
    a.`staffMemberID`,
    consultant.`firstName`,
    consultant.`lastName`,
    IF(
      NOT staffCompleted,
      'staffPending',
      IF(
        NOT `managerCompleted`,
        'managerPending',
        'completed'
      )
    ) AS STATUS 
  FROM
    StaffAppraisalQuestionnaireAnswer a 
    LEFT JOIN `consultant` ON consultant.`cns_consno` = staffMemberID
  WHERE a.managerID = $managerID and questionnaireID = $questionnaireID) test 
WHERE test.status = '$type' order by firstName, lastName"
        );

        $stats = [];

        while ($db->next_record(MYSQLI_ASSOC)) {
            $stats[] = [
                "questionnaireID" => $db->Record['questionnaireID'],
                "staffMemberID"   => $db->Record['staffMemberID'],
                "firstName"       => $db->Record['firstName'],
                "lastName"        => $db->Record['lastName'],
                'status'          => $db->Record['status']
            ];

        };
        return $stats;
    }
}// End of class
?>