<?php /**
 * Sales and Cost Values of initial and part despatched Sales Orders
 * @return Boolean Success
 * @access public
 * @authors Karim Ahmed
 * @access public
 */
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBESalesOrderTotals extends DBEntity
{
    const costValue = 'costValue';
    const saleValue = 'saleValue';

    /**
     * calls constructor()
     * @access public
     * @param void
     * @return void
     * @see constructor()
     */
    function __construct($owner)
    {
        parent::__construct($owner);
        $this->setTableName("ordhead");
        $this->addColumn(self::costValue, DA_FLOAT, DA_NOT_NULL);
        $this->addColumn(self::saleValue, DA_FLOAT, DA_NOT_NULL);
        $this->setAddColumnsOff();
    }

    /**
     * @return Boolean Success
     * @access public
     */
    function getRow()
    {
        $this->setQueryString(
            "SELECT " .
            "SUM( (odl_qty_ord - odl_qty_desp) * odl_d_unit )," .
            "SUM( (odl_qty_ord - odl_qty_desp) * odl_e_unit )" .
            " FROM " . $this->getTableName() .
            " INNER JOIN ordline ON odh_ordno = odl_ordno" .
            " WHERE odh_type IN ('I','P')" .
            " AND odl_type = 'I'" .
            " AND odl_qty_ord IS NOT NULL AND odl_qty_desp IS NOT NULL AND odl_d_unit IS NOT NULL" .
            " AND odl_e_unit IS NOT NULL"
        );
        return (parent::getRow());
    }
}
