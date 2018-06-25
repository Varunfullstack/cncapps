<?php /*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBECallActEngineer extends DBEntity
{
    const callActEngineerID = "callActEngineerID";
    const callActivityID = "callActivityID";
    const sequenceNo = "sequenceNo";
    const userID = "userID";
    const expenseExportedFlag = "expenseExportedFlag";
    const overtimeExportedFlag = "overtimeExportedFlag";

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
        $this->setTableName("CallActEngineer");
        $this->addColumn(
            self::callActEngineerID,
            DA_ID,
            DA_NOT_NULL,
            "cae_callactengno"
        );
        $this->addColumn(
            self::callActivityID,
            DA_ID,
            DA_NOT_NULL,
            "cae_callactivityno"
        );
        $this->addColumn(
            self::sequenceNo,
            DA_INTEGER,
            DA_NOT_NULL,
            "cae_item"
        );
        $this->addColumn(
            self::userID,
            DA_ID,
            DA_NOT_NULL,
            "cae_consno"
        );
        $this->addColumn(
            self::expenseExportedFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "cae_expn_exp_flag "
        );            // whether expenses have been exported
        $this->addColumn(
            self::overtimeExportedFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "cae_ot_exp_flag "
        );                // whether overtime info exported
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function deleteRowsByCallActivityID()
    {
        $this->setMethodName('deleteRowsByCallActivityID');
        $this->setQueryString(
            "DELETE FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::callActivityID) . ' = ' . $this->getFormattedValue(
                self::callActivityID
            )
        );
        $ret = $this->runQuery();
        $this->resetQueryString();
        return $ret;

    }
}

?>