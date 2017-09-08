<?php /*
* Future Action table holds rows for internal email reminders to do stuff
* rows deleted as email sent
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEFutureAction extends DBEntity
{
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
        $this->setTableName("future_action");
        $this->addColumn("futureActionID", DA_ID, DA_NOT_NULL);
        $this->addColumn("date", DA_DATE, DA_NOT_NULL);
        $this->addColumn("callActivityID", DA_ID, DA_NOT_NULL);
        $this->addColumn("furtherActionID", DA_ID, DA_NOT_NULL);
        $this->addColumn("engineerName", DA_STRING, DA_NOT_NULL);
        $this->addColumn("dateCreated", DA_DATE, DA_NOT_NULL);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getRowsDueToday()
    {
        $this->setMethodName('getRowsDueToday');
        $statement =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE date <= CURDATE()";
        $this->setQueryString($statement);
        $ret = (parent::getRows());
    }

    function deleteRowsByCallActivityID($callActivityID)
    {
        $this->setMethodName('deleteRowsByCallActivityID');

        $statement =
            "DELETE FROM " . $this->getTableName() .
            " WHERE callActivityID = $callActivityID";
        $this->setQueryString($statement);
        $ret = (parent::runQuery());
    }
}

?>