<?
/*
* Item type table access
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"]."/DBCNCEntity.inc.php");
class DBERenQuotationType extends DBCNCEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBERenQuotationType(&$owner){
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
		$this->setTableName("renquotationtype");
 		$this->addColumn("renQuotationTypeID", DA_ID, DA_NOT_NULL);
 		$this->addColumn("description", DA_STRING, DA_NOT_NULL);
 		$this->addColumn("addInstallationCharge", DA_YN, DA_NOT_NULL);
 		$this->setPK(0);
 		$this->setAddColumnsOff();
 	}
}
?>