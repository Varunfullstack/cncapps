<?
/*
* Call activity thread table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEActivityThread extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEActivityThread(&$owner){
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
		$this->setTableName("activitythread");
 		$this->addColumn("activityThreadID", DA_ID, DA_NOT_NULL, "att_activitythreadno");
 		$this->addColumn("customerID", DA_INTEGER, DA_ALLOW_NULL, "att_custno");
 		$this->setAddColumnsOff();
		$this->setPK(0);
	}
}
?>