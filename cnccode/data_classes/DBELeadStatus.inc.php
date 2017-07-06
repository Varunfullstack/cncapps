<?
/*
* Customer type table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBELeadStatus extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEleadStatus(&$owner){
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
		$this->setTableName("leadstatus");
 		$this->addColumn("leadStatusID", DA_ID, DA_NOT_NULL, "lst_leadstatusno");
 		$this->addColumn("description", DA_STRING, DA_NOT_NULL, "lst_desc");
 		$this->setPK(0);
 		$this->setAddColumnsOff();
 	}
}
?>