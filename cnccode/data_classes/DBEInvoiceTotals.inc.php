<?php /*
* Return invoice totals
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEInvoiceTotals extends DBEntity
{
    const count = "count";
    const costValue = "costValue";
    const saleValue = "saleValue";

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
        $this->setTableName("invhead");
        $this->addColumn(
            self::count,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::costValue,
            DA_FLOAT,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::saleValue,
            DA_FLOAT,
            DA_NOT_NULL
        );
        $this->setAddColumnsOff();
        $this->db->connect();
    }

    /**
     * Count and Sales and Cost Values of unprinted credit notes or invoices
     * @param string $type I=Invoices C=Credit Notes
     * @param bool $directDebit
     * @return Boolean Success
     * @access public
     */
    function getRow($type = 'I',
                    $directDebit = false
    )
    {
        $this->setQueryString(
            "SELECT" .
            " COUNT( DISTINCT inl_invno ) ," .
            "SUM( inl_qty * inl_cost_price ) ," .
            "SUM( inl_qty * inl_unit_price )" .
            " FROM " . $this->getTableName() .
            " JOIN invline ON inh_invno =inl_invno" .
            " WHERE inh_type='" . mysqli_real_escape_string(
                $this->db->link_id(),
                $type
            ) . "'" .
            " AND inh_date_printed is null" .
            " AND inl_unit_price IS NOT NULL" .
            " AND inl_line_type = 'I' 
              AND directDebitFlag " . ($directDebit ? " = 'Y' " : " <> 'Y' ")
        );
        return (parent::getRow());
    }

    /**
     * Totals of Sales and Cost Values of printed credit notes and invoices
     * @return Boolean Success
     * @access public
     */
    function getCurrentMonthTotals()
    {
        $this->setQueryString(
            "SELECT" .
            " '1' as count," .
            " SUM( inl_qty * inl_cost_price ) as costPrice," .
            "SUM( inl_qty * inl_unit_price ) as salePrice" .
            " FROM " . $this->getTableName() .
            " JOIN invline ON inh_invno =inl_invno" .
            " WHERE inh_date_printed >= CONCAT( YEAR(NOW()), '-', MONTH(NOW()), '-01' )" .
            " AND inl_unit_price IS NOT NULL" .
            " AND inl_line_type = 'I'"
        );
        return (parent::getRow());
    }

    /**
     * Totals of Sales and Cost Values of unprinted credit notes and invoices
     * @return Boolean Success
     * @access public
     */
    function getUnprintedTotals()
    {
        $this->setQueryString(
            "SELECT" .
            " '1' as count," .
            " SUM( inl_qty * inl_cost_price ) as costPrice," .
            "SUM( inl_qty * inl_unit_price ) as salePrice" .
            " FROM " . $this->getTableName() .
            " JOIN invline ON inh_invno =inl_invno" .
            " WHERE inh_date_printed is null " .
            " AND inl_unit_price IS NOT NULL" .
            " AND inl_line_type = 'I'"
        );
        return (parent::getRow());
    }
}
