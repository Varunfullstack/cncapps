<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 22/11/2018
 * Time: 11:27
 */

require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEStaffAppraisalQuestionnaireAnswer extends DBEntity
{
    const id = "id";
    const questionnaireID = "questionnaireID";
    const staffMemberID = "staffMemberID";
    const managerID = "managerID";
    const startedAt = "startedAt";
    const staffCompleted = "staffCompleted";
    const managerCompleted = "managerCompleted";
    const sickDaysThisYear = "sickDaysThisYear";
    const proposedSalary = "proposedSalary";
    const proposedBonus = "proposedBonus";
    const teamLeaderComments = "teamLeaderComments";
    const managerComments = "managerComments";

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
        $this->setTableName("staffAppraisalQuestionnaireAnswer");
        $this->addColumn(
            self::id,
            DA_ID,
            DA_NOT_NULL
        );

        $this->addColumn(
            self::questionnaireID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::staffMemberID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::managerID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::startedAt,
            DA_DATETIME,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::staffCompleted,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            false
        );

        $this->addColumn(
            self::managerCompleted,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            false
        );

        $this->addColumn(
            self::sickDaysThisYear,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::proposedSalary,
            DA_INTEGER,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::proposedBonus,
            DA_INTEGER,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::teamLeaderComments,
            DA_TEXT,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::managerComments,
            DA_TEXT,
            DA_ALLOW_NULL
        );

        $this->setAddColumnsOff();
        $this->setPK(0);
    }

    public function getRowByQuestionnaireAndStaff($questionnaireID,
                                                  int $staffID
    )
    {
        $query = "select " . $this->getDBColumnNamesAsString(
            ) . " from " . $this->tableName . " where " . $this->getDBColumnName(
                self::questionnaireID
            ) . " = " . $questionnaireID . " and " . $this->getDBColumnName(self::staffMemberID) . " = " . $staffID;
        $this->setQueryString($query);

        $this->getRow();
    }
}