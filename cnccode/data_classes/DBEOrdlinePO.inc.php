<?php /*
* Ordline purchase order rows
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEOrdlinePO extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function __construct(&$owner){
		$this->constructor($owner);
	}
	/**
	* constructor
	* @access public
	* @return void
	* @param  void
	*/
	function constructor(&$owner){
		parent::__construct($owner);
		$this->setTableName("Ordline");
 		$this->addColumn("supplierID", DA_ID, DA_ALLOW_NULL, "odl_suppno");
 		$this->addColumn("stockcat", DA_STRING, DA_ALLOW_NULL, "odl_stockcat");
 		$this->addColumn("itemID", DA_ID, DA_ALLOW_NULL, "odl_itemno");
 		$this->addColumn("curUnitCost", DA_FLOAT, DA_ALLOW_NULL, "odl_d_unit");
 		$this->addColumn("qtyOrdered", DA_FLOAT, DA_ALLOW_NULL, "odl_qty_ord");
 		$this->setAddColumnsOff();
 	}
	/**
	* get all rows ready for generation of purchase orders
	* @access public
	* @return bool
	*/
	function getRows($ordheadID)
	{
		$this->setMethodName("getRows");
		if ($ordheadID == ''){
			$this->raiseError('ordheadID not set');
		}
		$this->setQueryString(
			"SELECT DISTINCT  odl_suppno, odl_stockcat, ".
			"odl_itemno, odl_d_unit, SUM(odl_qty_ord) ".
      "FROM ordline ".
      "WHERE odl_ordno = ".$ordheadID.
      " AND odl_type = 'I' " .
			"GROUP BY odl_suppno, odl_stockcat, ".
			"odl_itemno, odl_d_unit ".
      "ORDER BY odl_suppno, odl_item_no"
    );
    return(parent::getRows());
	}
}
?>