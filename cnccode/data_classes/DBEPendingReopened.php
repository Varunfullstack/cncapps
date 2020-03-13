<?php /*
* Questionnaire table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEPendingReopened extends DBEntity
{
    const id = "id";
    const problemID = "problemID";
    const contactID = "contactID";
    const reason = "reason";

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
        $this->setTableName("pendingReopened");
        $this->addColumn(
            self::id,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::problemID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::contactID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::reason,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->setAddColumnsOff();
        $this->setPK(0);
    }

    public function getRowsForSR($problemID)
    {
        $this->queryString = "select {$this->getDBColumnNamesAsString()} from {$this->tableName} where {$this->getDBColumnName(self::problemID)} = $problemID";
        return $this->getRows();
    }
}

?>
