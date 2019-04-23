<?php /*
* Stockcat table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEStockcat extends DBEntity
{
    const stockcat = "stockcat";
    const description = "description";
    const salNom = "salNom";
    const purCust = "purCust";
    const purSalesStk = "purSalesStk";
    const purMaintStk = "purMaintStk";
    const purAsset = "purAsset";
    const purOper = "purOper";
    const serialReqFlag = "serialReqFlag";
    const postMovement = "postMovement";

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
        $this->setTableName("stockcat");
        $this->addColumn(self::stockcat, DA_STRING, DA_NOT_NULL, "stc_stockcat");
        $this->addColumn(self::description, DA_STRING, DA_ALLOW_NULL, "stc_desc");
        $this->addColumn(self::salNom, DA_STRING, DA_ALLOW_NULL, "stc_sal_nom");
        $this->addColumn(self::purCust, DA_STRING, DA_ALLOW_NULL, "stc_pur_cust");
        $this->addColumn(self::purSalesStk, DA_STRING, DA_ALLOW_NULL, "stc_pur_sales_stk");
        $this->addColumn(self::purMaintStk, DA_STRING, DA_ALLOW_NULL, "stc_pur_maint_stk");
        $this->addColumn(self::purAsset, DA_STRING, DA_ALLOW_NULL, "stc_pur_ecc_asset");
        $this->addColumn(self::purOper, DA_STRING, DA_ALLOW_NULL, "stc_pur_ecc_oper");
        $this->addColumn(self::serialReqFlag, DA_YN, DA_NOT_NULL, "stc_serial_req");
        $this->addColumn(self::postMovement, DA_YN, DA_NOT_NULL, "stc_post_movement");
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}
