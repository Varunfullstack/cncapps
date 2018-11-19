<?php /*
* Questionnaire table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEStaffAppraisalAnswer extends DBEntity
{

    const answerID = "answerID";
    const questionID = "questionID";
    const staffAnswer = "staffAnswer";
    const staffMemberId = "staffMemberId";
    const managerId = "managerId";
    const managerAnswer = "managerAnswer";
    const managerNote = "managerNote";

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
        $this->setTableName("staffAppraisalAnswer");
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
            self::staffAnswer,
            DA_STRING,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::staffMemberId,
            DA_ID,
            DA_NOT_NULL
        );

        $this->addColumn(
            self::managerId,
            DA_ID,
            DA_NOT_NULL
        );

        $this->addColumn(
            self::managerAnswer,
            DA_STRING,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::managerNote,
            DA_STRING,
            DA_ALLOW_NULL
        );


        $this->setAddColumnsOff();
        $this->setPK(0);
    }
}

?>
