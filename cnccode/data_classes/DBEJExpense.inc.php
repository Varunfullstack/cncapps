<?php


/*
* Call activity join
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBEExpense.inc.php");

class DBEJExpense extends DBEExpense
{
    const expenseType = "expenseType";
    const userID = "userID";
    const expenseExportedFlag = "expenseExportedFlag";
    const mileageFlag = "mileageFlag";
    const defaultVatFlag = "defaultVatFlag";

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
        $this->addColumn(
            self::expenseType,
            DA_STRING,
            DA_ALLOW_NULL,
            "ext_desc"
        );
        $this->addColumn(
            self::userID,
            DA_ID,
            DA_ALLOW_NULL,
            "caa_consno"
        );
        $this->addColumn(
            self::expenseExportedFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "caa_expexport_flag"
        );
        $this->addColumn(
            self::mileageFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "ext_mileage_flag"
        );        // indicates whether this is a mileage type
        $this->addColumn(
            self::defaultVatFlag,
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
