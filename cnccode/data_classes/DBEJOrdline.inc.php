<?php /*
* Ordline to supplier join
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"]."/DBEOrdline.inc.php");
class DBEJOrdline extends DBEOrdline{
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
 		$this->setAddColumnsOn();
 		$this->addColumn("supplierName", DA_STRING, DA_ALLOW_NULL, "sup_name");
 		$this->addColumn("webSiteURL", DA_STRING, DA_ALLOW_NULL, "sup_web_site_url");
 		$this->addColumn("itemDescription", DA_STRING, DA_ALLOW_NULL, "itm_desc");
 		$this->addColumn("renewalTypeID", DA_ID, DA_ALLOW_NULL);
 		$this->addColumn("partNo", DA_STRING, DA_ALLOW_NULL, "itm_unit_of_sale");
 		$this->setPK(0);
 		$this->setAddColumnsOff();
 	}
	/**
	* Return rows from DB by column value
	* @access public
	* @return bool Success
	*/
	function getRowsByColumn($column){
		$this->setMethodName("getRowsByColumn");
		if ($column==''){
			$this->raiseError('Column not passed');
			return FALSE;
		}
		$ixColumn=$this->columnExists($column);
		if ($ixColumn==DA_OUT_OF_RANGE){
			$this->raiseError("Column ". $column. " out of range");
			return DA_OUT_OF_RANGE;
		}
		$this->setQueryString(
			"SELECT ".$this->getDBColumnNamesAsString().
			" FROM ".$this->getTableName()." LEFT JOIN supplier ON ".$this->getDBColumnName('supplierID')."=sup_suppno".
			" LEFT JOIN item ON ".$this->getDBColumnName('itemID')."=itm_itemno".
			" WHERE ".$this->getDBColumnName($ixColumn)."=".$this->getFormattedValue($ixColumn)//.
//			" ORDER BY ".$this->getDBColumnName($ixColumn)
		);
		return($this->getRows());
	}
	function getRenewalRowByOrdheadID(){
		if ($this->getValue('ordheadID')==''){
			$this->raiseError('ordheadID not set');
		}

		$this->setQueryString(
			"SELECT ".$this->getDBColumnNamesAsString() .
			" FROM ".$this->getTableName()." LEFT JOIN supplier ON ".$this->getDBColumnName('supplierID')."=sup_suppno".
			" LEFT JOIN item ON ".$this->getDBColumnName('itemID')."=itm_itemno".
			" WHERE ".$this->getDBColumnName('ordheadID')."=".$this->getFormattedValue('ordheadID').
			" AND odl_qty_desp > 0" .
			" AND odl_renewal_flag = 'Y'" .
			" AND " . $this->getDBColumnName('renewalPromptedDate') . " < CURDATE()". 
			" ORDER BY " . $this->getDBColumnName('sequenceNo') .
			" LIMIT 0,1"
			
		);

		return(parent::getRows());
	}
	function getRowByOrdheadIDSequenceNo(){
		if ($this->getValue('ordheadID')==''){
			$this->raiseError('ordheadID not set');
		}

		$query =
			"SELECT ".$this->getDBColumnNamesAsString().
			" FROM ".$this->getTableName()." LEFT JOIN supplier ON ".$this->getDBColumnName('supplierID')."=sup_suppno".
			" LEFT JOIN item ON ".$this->getDBColumnName('itemID')."=itm_itemno".
			" WHERE ".$this->getDBColumnName('ordheadID')."=".$this->getFormattedValue('ordheadID').
			" AND ".$this->getDBColumnName('sequenceNo')."=".$this->getFormattedValue('sequenceNo');
				
		$this->setQueryString( $query );

		return(parent::getRow());
	}
}
?>