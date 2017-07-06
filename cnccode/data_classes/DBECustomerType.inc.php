<?
/*
* Customer type table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBECustomerType extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBECustomerType(&$owner){
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
		$this->setTableName("custype");
 		$this->addColumn("customerTypeID", DA_ID, DA_NOT_NULL, "cty_ctypeno");
 		$this->addColumn("description", DA_STRING, DA_NOT_NULL, "cty_desc");
 		$this->setPK(0);
 		$this->setAddColumnsOff();
 	}
}
?>