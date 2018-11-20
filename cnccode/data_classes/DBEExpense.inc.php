<?php /*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEExpense extends DBEntity
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
        $this->setTableName("expense");
        $this->addColumn(
            "expenseID",
            DA_ID,
            DA_NOT_NULL,
            "exp_expenseno"
        );
        $this->addColumn(
            "expenseTypeID",
            DA_ID,
            DA_NOT_NULL,
            "exp_expensetypeno"
        );
        $this->addColumn(
            "callActivityID",
            DA_ID,
            DA_NOT_NULL,
            "exp_callactivityno"
        );
        $this->addColumn(
            "mileage",
            DA_INTEGER,
            DA_ALLOW_NULL,
            "exp_mileage"
        );
        $this->addColumn(
            "value",
            DA_FLOAT,
            DA_ALLOW_NULL,
            "exp_value"
        );
        $this->addColumn(
            "vatFlag",
            DA_YN,
            DA_NOT_NULL,
            "exp_vat_flag"
        );
        $this->addColumn(
            "exportedFlag",
            DA_YN,
            DA_NOT_NULL,
            "exp_exported_flag"
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

/*
* Call activity join
* @authors Karim Ahmed
* @access public
*/

class DBEJExpense extends DBEExpense
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
        $this->setAddColumnsOn();
        $this->addColumn(
            "expenseType",
            DA_STRING,
            DA_ALLOW_NULL,
            "ext_desc"
        );
        $this->addColumn(
            "userID",
            DA_ID,
            DA_ALLOW_NULL,
            "caa_consno"
        );
        $this->addColumn(
            "expenseExportedFlag",
            DA_YN,
            DA_ALLOW_NULL,
            "caa_expexport_flag"
        );
        $this->addColumn(
            "mileageFlag",
            DA_YN,
            DA_ALLOW_NULL,
            "ext_mileage_flag"
        );        // indicates whether this is a mileage type
        $this->addColumn(
            "defaultVatFlag",
            DA_YN,
            DA_ALLOW_NULL,
            "ext_vat_flag"
        );            // default VAT included in value flag
        $this->setAddColumnsOff();
    }

    function getRow($expenseID = '')
    {
        if ($expenseID != '') {
            $this->setPKValue($expenseID);
        }
        $this->setQueryString(
            "SELECT " .
            $this->getDBColumnNamesAsString() .
            " FROM " .
            $this->getTableName() .
            " JOIN " .
            " expensetype ON exp_expensetypeno = ext_expensetypeno" .
            " JOIN " .
            " callactivity ON caa_callactivityno = exp_callactivityno" .
            " JOIN " .
            " problem ON pro_problemno = caa_problemno" .
            " JOIN " .
            " consultant ON cns_consno = caa_consno" .
            " JOIN " .
            " customer ON cus_custno = pro_custno" .
            " JOIN " .
            " address ON add_custno = pro_custno" .
            " AND add_siteno = caa_siteno" .
            " WHERE " . $this->getPKWhere()
        );
        return (parent::getRow());
    }

    function getRowsByCallActivityID($callActivityID)
    {
        $this->setQueryString(
            "SELECT " .
            $this->getDBColumnNamesAsString() .
            " FROM " .
            $this->getTableName() .
            " JOIN " .
            " expensetype ON exp_expensetypeno = ext_expensetypeno" .
            " JOIN " .
            " callactivity ON caa_callactivityno = exp_callactivityno" .
            " JOIN " .
            " problem ON pro_problemno = caa_problemno" .
            " JOIN " .
            " consultant ON cns_consno = caa_consno" .
            " JOIN " .
            " customer ON cus_custno = pro_custno" .
            " JOIN " .
            " address ON add_custno = pro_custno" .
            " AND add_siteno = caa_siteno" .
            " WHERE caa_callactivityno =" . $callActivityID
        );
        return (parent::getRows());
    }

    public function getTotalExpensesForSalesOrder($salesOrderID)
    {
        $query = "SELECT 
  SUM(exp_value) AS total
FROM
  expense 
  JOIN callactivity 
    ON expense.`exp_callactivityno` = callactivity.`caa_callactivityno` 
    JOIN problem ON callactivity.`caa_problemno` = problem.`pro_problemno`
    WHERE pro_linked_ordno = $salesOrderID";

        $this->db->query($query);
        $this->db->next_record(MYSQLI_ASSOC);
        return $this->db->Record['total'];

    }
}

?>