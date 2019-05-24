<?php /*
* Purchase invoice table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEPurchaseInv extends DBEntity
{
    const pinlineID = "pinlineID";
    const type = "type";
    const accRef = "accRef";
    const nomRef = "nomRef";
    const dept = "dept";
    const date = "date";
    const ref = "ref";
    const details = "details";
    const netAmnt = "netAmnt";
    const taxCode = "taxCode";
    const taxAmnt = "taxAmnt";
    const printed = "printed";

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
        $this->setTableName('pinline');
        $this->addColumn(self::pinlineID, DA_ID, DA_NOT_NULL, 'pin_pinno');
        $this->addColumn(self::type, DA_STRING, DA_NOT_NULL, 'pin_type');
        $this->addColumn(self::accRef, DA_STRING, DA_NOT_NULL, 'pin_ac_ref');
        $this->addColumn(self::nomRef, DA_STRING, DA_NOT_NULL, 'pin_nom_ref');
        $this->addColumn(self::dept, DA_STRING, DA_NOT_NULL, 'pin_dept');
        $this->addColumn(self::date, DA_STRING, DA_NOT_NULL, 'pin_date');
        $this->addColumn(self::ref, DA_STRING, DA_NOT_NULL, 'pin_ref');
        $this->addColumn(self::details, DA_STRING, DA_NOT_NULL, 'pin_details');
        $this->addColumn(self::netAmnt, DA_FLOAT, DA_NOT_NULL, 'pin_net_amnt');
        $this->addColumn(self::taxCode, DA_STRING, DA_NOT_NULL, 'pin_tax_code');
        $this->addColumn(self::taxAmnt, DA_FLOAT, DA_NOT_NULL, 'pin_tax_amnt');
        $this->addColumn(self::printed, DA_YN, DA_NOT_NULL, 'pin_printed');
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function countRowsBySupplierInvNo($accRef, $ref)
    {
        //TODO: sort this issue out!
        $this->setQueryString(
            "SELECT COUNT(*)" .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(self::accRef) . "=" . mysqli_real_escape_string(
                $this->db->link_id(),
                $accRef
            ) .
            " AND " . $this->getDBColumnName(self::ref) . "='" . mysqli_real_escape_string(
                $this->db->link_id(),
                $ref
            ) . "'"
        );
        if ($this->runQuery()) {
            if ($this->nextRecord()) {
                $this->resetQueryString();
                return ($this->getDBColumnValue(0));
            }
        }
        return false;
    }

    function getUnprintedRowsByMonth($year, $month)
    {
        $this->setMethodName('getRowsByMonth');
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE DATE_FORMAT(" . $this->getDBColumnName(self::date) . ",'%Y%m') <= '" . $year . str_pad(
                $month,
                2,
                '0',
                STR_PAD_LEFT
            ) . "'" .
            " AND " . $this->getDBColumnName(self::printed) . " = 'N'"
        );
        return parent::getRows();
    }

    /**
     * Set the printed flag on for given row
     * @param $pkValue
     * @return bool
     */
    function setPrintedOn($pkValue)
    {
        $this->setMethodName('setPrintedOn');
        $this->setPKValue($pkValue);
        $this->setQueryString(
            "UPDATE " . $this->getTableName() .
            " SET  " . $this->getDBColumnName(self::printed) . " = 'Y'" .
            " WHERE " . $this->getPKDBName() . "=" . $this->getPKValue()
        );
        return parent::updateRow();
    }
}
