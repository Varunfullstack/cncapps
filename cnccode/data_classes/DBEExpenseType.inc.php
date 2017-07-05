<?
/*
* Expense type table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEExpenseType extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEExpenseType(&$owner){
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
		$this->setTableName("expensetype");
 		$this->addColumn("expenseTypeID", DA_ID, DA_NOT_NULL, "ext_expensetypeno");
 		$this->addColumn("description", DA_STRING, DA_NOT_NULL, "ext_desc");
 		$this->addColumn("mileageFlag", DA_YN, DA_ALLOW_NULL, "ext_mileage_flag");		
 		$this->addColumn("vatFlag", DA_YN, DA_ALLOW_NULL, "ext_vat_flag");		
		$this->setPK(0);
 		$this->setAddColumnsOff();
	}
}
?>