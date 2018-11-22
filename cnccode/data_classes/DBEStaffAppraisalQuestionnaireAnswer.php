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
    const staffMemberId = "staffMemberId";
    const managerId = "managerId";
    const startedAt = "startedAt";
    const completed = "completed";
    const sickDaysThisYear = "sickDaysThisYear";
    const proposedSalary = "proposedSalary";

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
            self::startedAt,
            DA_DATETIME,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::completed,
            DA_INTEGER,
            DA_NOT_NULL
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


        $this->setAddColumnsOff();
        $this->setPK(0);
    }

}