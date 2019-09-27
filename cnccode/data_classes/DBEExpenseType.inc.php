<?php /*
* Expense type table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEExpenseType extends DBEntity
{
    const expenseTypeID = "expenseTypeID";
    const description = "description";
    const mileageFlag = "mileageFlag";
    const vatFlag = "vatFlag";
    const taxable = 'taxable';
    const approvalRequired = "approvalRequired";
    const receiptRequired = "receiptRequired";

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
        $this->setTableName("expensetype");
        $this->addColumn(
            self::expenseTypeID,
            DA_ID,
            DA_NOT_NULL,
            "ext_expensetypeno"
        );
        $this->addColumn(
            self::description,
            DA_STRING,
            DA_NOT_NULL,
            "ext_desc"
        );
        $this->addColumn(
            self::mileageFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "ext_mileage_flag"
        );
        $this->addColumn(
            self::vatFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "ext_vat_flag"
        );
        $this->addColumn(
            self::taxable,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            0
        );
        $this->addColumn(
            self::approvalRequired,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            0
        );
        $this->addColumn(
            self::receiptRequired,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            0
        );

        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>