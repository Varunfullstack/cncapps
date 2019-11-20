<?php /*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEExpense extends DBEntity
{
    const expenseID = "expenseID";
    const expenseTypeID = "expenseTypeID";
    const callActivityID = "callActivityID";
    const mileage = "mileage";
    const value = "value";
    const vatFlag = "vatFlag";
    const exportedFlag = "exportedFlag";
    const dateSubmitted = "dateSubmitted";
    const approvedDate = "approvedDate";
    const approvedBy = "approvedBy";
    const deniedReason = "deniedReason";

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
        $this->setTableName("expense");
        $this->addColumn(
            self::expenseID,
            DA_ID,
            DA_NOT_NULL,
            "exp_expenseno"
        );
        $this->addColumn(
            self::expenseTypeID,
            DA_ID,
            DA_NOT_NULL,
            "exp_expensetypeno"
        );
        $this->addColumn(
            self::callActivityID,
            DA_ID,
            DA_NOT_NULL,
            "exp_callactivityno"
        );
        $this->addColumn(
            self::mileage,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "exp_mileage"
        );
        $this->addColumn(
            self::value,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "exp_value"
        );
        $this->addColumn(
            self::vatFlag,
            DA_YN,
            DA_NOT_NULL,
            "exp_vat_flag"
        );
        $this->addColumn(
            self::exportedFlag,
            DA_YN,
            DA_NOT_NULL,
            "exp_exported_flag"
        );

        $this->addColumn(self::dateSubmitted, DA_DATETIME, DA_ALLOW_NULL);
        $this->addColumn(self::approvedDate, DA_DATETIME, DA_ALLOW_NULL);
        $this->addColumn(self::approvedBy, DA_ID, DA_ALLOW_NULL);
        $this->addColumn(self::deniedReason, DA_TEXT, DA_ALLOW_NULL);

        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    public function getNotExportedRowsForUser(int $userID)
    {
        $userID = $this->escapeValue($userID);
        $this->queryString = "SELECT
  " . $this->getDBColumnNamesAsString() . "
FROM
  " . $this->getTableName() . "
  LEFT JOIN `callactivity`
    ON `callactivity`.`caa_callactivityno` = " . $this->getDBColumnName(self::callActivityID) . "
  LEFT JOIN consultant
    ON callactivity.`caa_consno` = consultant.`cns_consno`
WHERE (callactivity.`caa_consno` = $userID OR consultant.`expenseApproverID` = $userID) AND " . $this->getDBColumnName(
                self::exportedFlag
            ) . " <> 'Y'";

        $this->getRows();
    }
}

?>