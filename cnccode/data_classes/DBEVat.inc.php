<?php /*
* VAT table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"]."/DBCNCEntity.inc.php");
class DBEVat extends DBCNCEntity{
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
		$this->setTableName("Vat");
 		$this->addColumn("vatRateT0", DA_STRING, DA_NOT_NULL, "vat_rate_t0");
 		$this->addColumn("vatRateT1", DA_STRING, DA_NOT_NULL, "vat_rate_t1");
 		$this->addColumn("vatRateT2", DA_STRING, DA_NOT_NULL, "vat_rate_t2");
 		$this->addColumn("vatRateT3", DA_STRING, DA_NOT_NULL, "vat_rate_t3");
 		$this->addColumn("vatRateT4", DA_STRING, DA_NOT_NULL, "vat_rate_t4");
 		$this->addColumn("vatRateT5", DA_STRING, DA_NOT_NULL, "vat_rate_t5");
 		$this->addColumn("vatRateT6", DA_STRING, DA_NOT_NULL, "vat_rate_t6");
 		$this->addColumn("vatRateT7", DA_STRING, DA_NOT_NULL, "vat_rate_t7");
 		$this->addColumn("vatRateT8", DA_STRING, DA_NOT_NULL, "vat_rate_t8");
 		$this->addColumn("vatRateT9", DA_STRING, DA_NOT_NULL, "vat_rate_t9");
// 		There is no PK for this table
 		$this->setAddColumnsOff();
 	}
	// Note: no PK
	function getRow(){
		$this->setQueryString(
			"SELECT ".$this->getDBColumnNamesAsString().
			" FROM ".$this->getTableName()
		);
		return (parent::getRow());
	}
	// Not allowed:
	function insertRow(){
	}
	function deleteRow(){
	}
	// for now...
	function updateRow(){
	}
}
?>