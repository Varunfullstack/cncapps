<?php /*
* VAT table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBEVat extends DBCNCEntity
{
    const vatRateT0 = "vatRateT0";
    const vatRateT1 = "vatRateT1";
    const vatRateT2 = "vatRateT2";
    const vatRateT3 = "vatRateT3";
    const vatRateT4 = "vatRateT4";
    const vatRateT5 = "vatRateT5";
    const vatRateT6 = "vatRateT6";
    const vatRateT7 = "vatRateT7";
    const vatRateT8 = "vatRateT8";
    const vatRateT9 = "vatRateT9";

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
        $this->setTableName("Vat");
        $this->addColumn(self::vatRateT0, DA_STRING, DA_NOT_NULL, "vat_rate_t0");
        $this->addColumn(self::vatRateT1, DA_STRING, DA_NOT_NULL, "vat_rate_t1");
        $this->addColumn(self::vatRateT2, DA_STRING, DA_NOT_NULL, "vat_rate_t2");
        $this->addColumn(self::vatRateT3, DA_STRING, DA_NOT_NULL, "vat_rate_t3");
        $this->addColumn(self::vatRateT4, DA_STRING, DA_NOT_NULL, "vat_rate_t4");
        $this->addColumn(self::vatRateT5, DA_STRING, DA_NOT_NULL, "vat_rate_t5");
        $this->addColumn(self::vatRateT6, DA_STRING, DA_NOT_NULL, "vat_rate_t6");
        $this->addColumn(self::vatRateT7, DA_STRING, DA_NOT_NULL, "vat_rate_t7");
        $this->addColumn(self::vatRateT8, DA_STRING, DA_NOT_NULL, "vat_rate_t8");
        $this->addColumn(self::vatRateT9, DA_STRING, DA_NOT_NULL, "vat_rate_t9");
// 		There is no PK for this table
        $this->setAddColumnsOff();
    }

    // Note: no PK
    function getRow($pkValue = null)
    {
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName()
        );
        return (parent::getRow());
    }

    // Not allowed:
    function insertRow()
    {
    }

    function deleteRow($pkValue = null)
    {
    }

    // for now...
    function updateRow()
    {
    }
}

?>