<?
/*
* Location table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBELocation extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBELocation(&$owner){
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
		$this->setTableName("Location");
 		$this->addColumn("locationID", DA_ID, DA_NOT_NULL, "loc_locno");
 		$this->addColumn("description", DA_STRING, DA_NOT_NULL, "loc_desc");
		$this->setPK(0);
 		$this->setAddColumnsOff();
	}
}
?>