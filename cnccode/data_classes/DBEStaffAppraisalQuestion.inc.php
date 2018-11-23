<?php /*
* Question table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEStaffAppraisalQuestion extends DBEntity
{

    const id = "id";
    const questionnaireID = "questionnaireID";
    const answerTypeID = "answerTypeID";
    const description = "description";
    const activeFlag = "activeFlag";
    const requiredFlag = "requiredFlag";
    const orderSequence = "orderSequence";

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
        $this->setTableName("staffAppraisalQuestion");
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
            self::answerTypeID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::activeFlag,
            DA_YN,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::requiredFlag,
            DA_YN,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::orderSequence,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->setAddColumnsOff();
        $this->setPK(0);
    }

    public function getRowsForQuestionnaire($questionnaireID)
    {
        $query = "select " . $this->getDBColumnNamesAsString() . " from " . $this->getTableName(
            ) . " where " . $this->getDBColumnName(
                self::questionnaireID
            ) . " = $questionnaireID order by " . $this->getDBColumnName(self::orderSequence) . " asc";
        $this->setQueryString($query);
        $this->getRows();
    }
}

?>
