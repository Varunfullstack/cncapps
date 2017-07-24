<?php /*
* Secutity application table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBESecurityApp extends DBEntity{
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
		$this->setTableName("securityapp");
 		$this->addColumn("securityAppID", DA_ID, DA_NOT_NULL);
 		$this->addColumn("description", DA_STRING, DA_NOT_NULL);
 		$this->addColumn("backupFlag", DA_STRING, DA_NOT_NULL);
 		$this->addColumn("emailAVFlag", DA_STRING, DA_NOT_NULL);
 		$this->addColumn("serverAVFlag", DA_STRING, DA_NOT_NULL);
 		$this->setAddColumnsOff();
 		$this->setPK(0);
	}
}
?>