<?
/*
* Call activity thread table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEBroadbandServiceType extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEBroadbandServiceType(&$owner){
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
		$this->setTableName("broadbandservicetype");
 		$this->addColumn("broadbandServiceTypeID", DA_ID, DA_NOT_NULL);
 		$this->addColumn("description", DA_TEXT, DA_NOT_NULL);
 		$this->setAddColumnsOff();
		$this->setPK(0);
	}
}
?>