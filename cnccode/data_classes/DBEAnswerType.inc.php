<?php /*
* AnswerType table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEAnswerType extends DBEntity
{

    const answerOptions = "answerOptions";
    const description = "description";
    const answerTypeID = "answerTypeID";
    const needsOptions = "needsOptions";

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
        $this->setTableName("answertype");
        $this->addColumn(
            self::answerTypeID,
            DA_ID,
            DA_NOT_NULL,
            "ant_answertypeno"
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL,
            "ant_desc"
        );
        $this->addColumn(
            self::answerOptions,
            DA_STRING,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::needsOptions,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->setAddColumnsOff();
        $this->setPK(0);
    }

    public function getConfigurableAnswerTypes()
    {
        $query = "select " . $this->getDBColumnNamesAsString() . " from " . $this->getTableName(
            ) . " where " . $this->getDBColumnName(self::needsOptions) . " = 1";

        $this->setQueryString($query);

        $ret = (parent::getRows());
        return $ret;
    }
}

?>
