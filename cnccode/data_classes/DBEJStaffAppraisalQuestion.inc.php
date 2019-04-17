<?php /*
* Question table join to answertype table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBEStaffAppraisalQuestion.inc.php");

class DBEJStaffAppraisalQuestion extends DBEStaffAppraisalQuestion
{
    const answerTypeName = "answerTypeName";

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
        $this->setAddColumnsOn();
        $this->addColumn(
            self::answerTypeName,
            DA_STRING,
            DA_NOT_NULL,
            'ant_desc'
        );
        $this->setAddColumnsOff();
    }

    /**
     * Return rows by ordheadID
     * @access public
     * @param $questionnaireID
     * @return bool Success
     */
    function getRowsByQuestionnaireID($questionnaireID)
    {
        $this->setMethodName("getRowsByQuestionnaireID");
        $this->setQueryString(
            'SELECT ' . $this->getDBColumnNamesAsString() .
            ' FROM ' . $this->getTableName() . ' LEFT JOIN answertype ON ' . $this->getTableName(
            ) . '.' . $this->getDBColumnName(self::answerTypeID) . '=answertype.ant_answertypeno' .
            ' WHERE ' . $this->getTableName() . '.' . $this->getDBColumnName(
                self::questionnaireID
            ) . ' = ' . $questionnaireID .
            ' ORDER BY ' . $this->getTableName() . '.' . $this->getDBColumnName(self::orderSequence)
        );
        return (parent::getRows());
    }
}