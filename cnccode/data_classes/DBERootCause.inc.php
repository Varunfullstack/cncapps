<?php /*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBERootCause extends DBEntity{
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
		$this->setTableName("rootcause");
 		$this->addColumn("rootCauseID", DA_ID, DA_NOT_NULL, "rtc_rootcauseno");
 		$this->addColumn("description", DA_STRING, DA_NOT_NULL, "rtc_desc");
    $this->addColumn("longDescription", DA_STRING, DA_NOT_NULL, "rtc_long_desc");
 		$this->setAddColumnsOff();
		$this->setPK(0);
	}
}
?>
