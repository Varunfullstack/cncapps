<?php /*
* Question table join to answertype table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBEQuestion.inc.php");

class DBEJQuestion extends DBEQuestion
{
    const answerType = "answerType";

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
        $this->setAddColumnsOn();
        $this->addColumn(self::answerType, DA_STRING, DA_NOT_NULL, 'ant_desc');
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
            ) . '.' . $this->getDBColumnName('answerTypeID') . '=answertype.ant_answertypeno' .
            ' WHERE que_questionnaireno = ' . $questionnaireID .
            ' ORDER BY que_weight'
        );
        return (parent::getRows());
    }
}
