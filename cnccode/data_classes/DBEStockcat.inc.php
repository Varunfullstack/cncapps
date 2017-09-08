<?php /*
* Stockcat table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEStockcat extends DBEntity
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
        $this->setTableName("stockcat");
        $this->addColumn("stockcat", DA_STRING, DA_NOT_NULL, "stc_stockcat");
        $this->addColumn("description", DA_STRING, DA_ALLOW_NULL, "stc_desc");
        $this->addColumn("salNom", DA_STRING, DA_ALLOW_NULL, "stc_sal_nom");
        $this->addColumn("purCust", DA_STRING, DA_ALLOW_NULL, "stc_pur_cust");
        $this->addColumn("purSalesStk", DA_STRING, DA_ALLOW_NULL, "stc_pur_sales_stk");
        $this->addColumn("purMaintStk", DA_STRING, DA_ALLOW_NULL, "stc_pur_maint_stk");
        $this->addColumn("purAsset", DA_STRING, DA_ALLOW_NULL, "stc_pur_ecc_asset");
        $this->addColumn("purOper", DA_STRING, DA_ALLOW_NULL, "stc_pur_ecc_oper");
        $this->addColumn("serialReqFlag", DA_YN, DA_NOT_NULL, "stc_serial_req");
        $this->addColumn("postMovement", DA_YN, DA_NOT_NULL, "stc_post_movement");
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>