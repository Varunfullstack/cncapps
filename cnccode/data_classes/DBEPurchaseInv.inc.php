<?
/*
* Purchase invoice table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEPurchaseInv extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEPurchaseInv(&$owner){
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
		$this->setTableName('pinline');
 		$this->addColumn('pinlineID', DA_ID, DA_NOT_NULL, 'pin_pinno');
 		$this->addColumn('type', DA_STRING, DA_NOT_NULL, 'pin_type');
 		$this->addColumn('accRef', DA_STRING, DA_NOT_NULL, 'pin_ac_ref');
 		$this->addColumn('nomRef', DA_STRING, DA_NOT_NULL, 'pin_nom_ref');
 		$this->addColumn('dept', DA_STRING, DA_NOT_NULL, 'pin_dept');
 		$this->addColumn('date', DA_STRING, DA_NOT_NULL, 'pin_date');
 		$this->addColumn('ref', DA_STRING, DA_NOT_NULL, 'pin_ref');
 		$this->addColumn('details', DA_STRING, DA_NOT_NULL, 'pin_details');
 		$this->addColumn('netAmnt', DA_FLOAT, DA_NOT_NULL, 'pin_net_amnt');
 		$this->addColumn('taxCode', DA_STRING, DA_NOT_NULL, 'pin_tax_code');
 		$this->addColumn('taxAmnt', DA_FLOAT, DA_NOT_NULL, 'pin_tax_amnt');
 		$this->addColumn('printed', DA_YN, DA_NOT_NULL, 'pin_printed');
 		$this->setPK(0);
 		$this->setAddColumnsOff();
	}
	function countRowsBySupplierInvNo($accRef, $ref)
	{
		$this->setQueryString(
			"SELECT COUNT(*)".
			" FROM " . $this->getTableName().
			" WHERE " . $this->getDBColumnName('accRef') . "=" . mysql_escape_string($accRef) .
			" AND " . $this->getDBColumnName('ref') . "='" . mysql_escape_string($ref) . "'"
		);
		if ($this->runQuery()){
			if($this->nextRecord()){
				$this->resetQueryString();
				return ($this->getDBColumnValue(0));
			}
		}
	}
	function getUnprintedRowsByMonth($year, $month)
	{
		$this->setMethodName('getRowsByMonth');
		$this->setQueryString(
			"SELECT ".$this->getDBColumnNamesAsString().
			" FROM " . $this->getTableName().
			" WHERE DATE_FORMAT(" . $this->getDBColumnName('date') . ",'%Y%m') <= '" . $year . str_pad($month, 2, '0',STR_PAD_LEFT) . "'".
			" AND ". $this->getDBColumnName('printed') . " = 'N'"
		);
		return parent::getRows();
	}
	/**
	* Set the printed flag on for given row
	*/
	function setPrintedOn($pkValue)
	{
		$this->setMethodName('setPrintedOn');
		$this->setPKValue($pkValue);
		$this->setQueryString(
			"UPDATE " . $this->getTableName() .
				" SET  " . $this->getDBColumnName('printed'). " = 'Y'".
			" WHERE " . $this->getPKDBName() . "=" . $this->getPKValue()
		);
		return parent::updateRow();
	}
}
?>