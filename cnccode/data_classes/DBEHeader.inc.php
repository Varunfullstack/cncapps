<?
/*
* System Header table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEHeader extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEHeader(&$owner){
		$this->constructor($owner);
	}
	/**
	* constructor
	* @access public
	* @return void
	* @param  void
	*/
	function constructor(&$owner){
		parent::constructor($owner);
		$this->setTableName("Headert");
 		$this->addColumn("Name", DA_STRING, DA_NOT_NULL, "hed_name");
 		$this->addColumn("Add1", DA_STRING, DA_NOT_NULL, "hed_add1");
 		$this->addColumn("Add2", DA_STRING, DA_ALLOW_NULL, "hed_add2");
 		$this->addColumn("Add3", DA_STRING, DA_ALLOW_NULL, "hed_add3");
 		$this->addColumn("Town", DA_STRING, DA_ALLOW_NULL, "hed_town");
 		$this->addColumn("County", DA_STRING, DA_ALLOW_NULL, "hed_county");
 		$this->addColumn("Postcode", DA_STRING, DA_ALLOW_NULL, "hed_postcode");
 		$this->addColumn("Phone", DA_STRING, DA_ALLOW_NULL, "hed_phone");
 		$this->addColumn("Fax", DA_STRING, DA_ALLOW_NULL, "hed_fax");
 		$this->addColumn("GoodsContact", DA_STRING, DA_ALLOW_NULL, "hed_goods_contact");
 		$this->addColumn("SalesStkSupplierID", DA_ID, DA_NOT_NULL, "hed_sstk_suppno");
 		$this->addColumn("MaintStkSupplierID", DA_ID, DA_NOT_NULL, "hed_mstk_suppno");
 		$this->addColumn("StdVATCode", DA_STRING, DA_NOT_NULL, "hed_std_vatcode");
 		$this->addColumn("CarriageStockcat", DA_STRING, DA_NOT_NULL, "hed_car_stockcat");
 		$this->addColumn("NextPorheadID", DA_ID, DA_NOT_NULL, "hed_next_porno");
 		$this->addColumn("NextItemID", DA_ID, DA_NOT_NULL, "hed_next_itemno");
 		$this->addColumn("NextInvheadID", DA_ID, DA_NOT_NULL, "hed_next_invno");
 		$this->addColumn("SalesStockLocno", DA_ID, DA_NOT_NULL, "hed_sstk_locno");
 		$this->addColumn("MaintStockLocno", DA_ID, DA_NOT_NULL, "hed_mstk_locno");
 		$this->addColumn("CNCAssettLocno", DA_ID, DA_NOT_NULL, "hed_ecc_ass_locno");
 		$this->addColumn("CNCOppLocno", DA_ID, DA_NOT_NULL, "hed_ecc_op_locno");
 		$this->addColumn("InvoicePrinter", DA_STRING, DA_NOT_NULL, "hed_invoice_prt");
 		$this->addColumn("POrderPrinter", DA_STRING, DA_NOT_NULL, "hed_porder_prt");
 		$this->addColumn("PLaserPrinter", DA_STRING, DA_NOT_NULL, "hed_plaser_prt");
 		$this->addColumn("LLaserPrinter", DA_STRING, DA_NOT_NULL, "hed_llaser_prt");
 		$this->addColumn("SystemPrinter", DA_STRING, DA_NOT_NULL, "hed_system_prt");
 		$this->addColumn("AuditPrinter", DA_STRING, DA_NOT_NULL, "hed_audit_prt");
 		$this->addColumn("BillStartTime", DA_TIME, DA_NOT_NULL, "hed_bill_starttime");
 		$this->addColumn("BillEndTime", DA_TIME, DA_NOT_NULL, "hed_bill_endtime");
    $this->addColumn("HdStartTime", DA_TIME, DA_NOT_NULL, "hed_hd_starttime");
    $this->addColumn("HdEndTime", DA_TIME, DA_NOT_NULL, "hed_hd_endtime");
    $this->addColumn("ProStartTime", DA_TIME, DA_NOT_NULL, "hed_pro_starttime");
    $this->addColumn("ProHoursEndTime", DA_TIME, DA_NOT_NULL, "hed_pro_endtime");
    $this->addColumn("portalPin", DA_STRING, DA_NOT_NULL, "hed_portal_pin");
    $this->addColumn("portal24HourPin", DA_STRING, DA_NOT_NULL, "hed_portal_24_hour_pin");
 		$this->addColumn("GSCItemID", DA_ID, DA_NOT_NULL, "hed_gensup_itemno");
 		$this->addColumn("NextInvSchedno", DA_INTEGER, DA_NOT_NULL, "hed_next_schedno");
 		$this->addColumn("OTAdjustHour", DA_FLOAT, DA_NOT_NULL, "hed_ot_adjust_hour");
 		$this->addColumn("Mailshot1FlagDef", DA_YN_FLAG, DA_NOT_NULL, "hed_mailflg1_def");
 		$this->addColumn("Mailshot2FlagDef", DA_YN_FLAG, DA_NOT_NULL, "hed_mailflg2_def");
 		$this->addColumn("Mailshot3FlagDef", DA_YN_FLAG, DA_NOT_NULL, "hed_mailflg3_def");
 		$this->addColumn("Mailshot4FlagDef", DA_YN_FLAG, DA_NOT_NULL, "hed_mailflg4_def");
 		$this->addColumn("Mailshot5FlagDef", DA_YN_FLAG, DA_NOT_NULL, "hed_mailflg5_def");
 		$this->addColumn("Mailshot6FlagDef", DA_YN_FLAG, DA_NOT_NULL, "hed_mailflg6_def");
 		$this->addColumn("Mailshot7FlagDef", DA_YN_FLAG, DA_NOT_NULL, "hed_mailflg7_def");
 		$this->addColumn("Mailshot8FlagDef", DA_YN_FLAG, DA_NOT_NULL, "hed_mailflg8_def");
 		$this->addColumn("Mailshot9FlagDef", DA_YN_FLAG, DA_NOT_NULL, "hed_mailflg9_def");
 		$this->addColumn("Mailshot10FlagDef", DA_YN_FLAG, DA_NOT_NULL, "hed_mailflg10_def");
 		$this->addColumn("Mailshot1FlagDesc", DA_YN_FLAG, DA_NOT_NULL, "hed_mailflg1_desc");
 		$this->addColumn("Mailshot2FlagDesc", DA_YN_FLAG, DA_NOT_NULL, "hed_mailflg2_desc");
 		$this->addColumn("Mailshot3FlagDesc", DA_YN_FLAG, DA_NOT_NULL, "hed_mailflg3_desc");
 		$this->addColumn("Mailshot4FlagDesc", DA_YN_FLAG, DA_NOT_NULL, "hed_mailflg4_desc");
 		$this->addColumn("Mailshot5FlagDesc", DA_YN_FLAG, DA_NOT_NULL, "hed_mailflg5_desc");
 		$this->addColumn("Mailshot6FlagDesc", DA_YN_FLAG, DA_NOT_NULL, "hed_mailflg6_desc");
 		$this->addColumn("Mailshot7FlagDesc", DA_YN_FLAG, DA_NOT_NULL, "hed_mailflg7_desc");
 		$this->addColumn("Mailshot8FlagDesc", DA_YN_FLAG, DA_NOT_NULL, "hed_mailflg8_desc");
 		$this->addColumn("Mailshot9FlagDesc", DA_YN_FLAG, DA_NOT_NULL, "hed_mailflg9_desc");
 		$this->addColumn("Mailshot10FlagDesc", DA_YN_FLAG, DA_NOT_NULL, "hed_mailflg10_desc");
    $this->addColumn("HourlyLabourCost", DA_FLOAT, DA_NOT_NULL, "hed_hourly_labour_cost");
    $this->addColumn("Priority1Desc", DA_STRING, DA_NOT_NULL, "hed_priority_1_desc");
    $this->addColumn("Priority2Desc", DA_STRING, DA_NOT_NULL, "hed_priority_2_desc");
    $this->addColumn("Priority3Desc", DA_STRING, DA_NOT_NULL, "hed_priority_3_desc");
    $this->addColumn("Priority4Desc", DA_STRING, DA_NOT_NULL, "hed_priority_4_desc");
    $this->addColumn("Priority5Desc", DA_STRING, DA_NOT_NULL, "hed_priority_5_desc");
 		$this->setPK(0);
 		$this->setAddColumnsOff();
 	}
	/**
	* Get rows by operative and date
	* @access public
	* @return bool Success
	*/
	function getRow(){
		$this->setMethodName("getRow");
		$ret=FALSE;
		$this->setQueryString(
				"SELECT ".$this->getDBColumnNamesAsString().
   			" FROM ".$this->getTableName()
		);
		return(parent::getRow());
	}
}
?>