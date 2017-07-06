<?
/*
* Renewal type table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBERenewalType extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBERenewalType(&$owner){
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
		$this->setTableName("renewaltype");
 		$this->addColumn("renewalTypeID", DA_ID, DA_NOT_NULL);
 		$this->addColumn("description", DA_STRING, DA_NOT_NULL);
		$this->setPK(0);
 		$this->setAddColumnsOff();
	}
}
?>