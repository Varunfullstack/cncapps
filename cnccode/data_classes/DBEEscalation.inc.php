<?
/*
* Escalations
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEEscalation extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEEscalation(&$owner){
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
		$this->setTableName("escalation");
 		$this->addColumn("escalationID", DA_ID, DA_NOT_NULL);
 		$this->addColumn("description", DA_STRING, DA_NOT_NULL);
    $this->addColumn("activeFlag", DA_YN, DA_NOT_NULL);
		$this->setPK(0);							
 		$this->setAddColumnsOff();
	}
}
?>