<?php
/*
* Staff available table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEStaffAvailable extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEStaffAvailable(&$owner){
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
		$this->setTableName("staffavailable");
 		$this->addColumn("staffAvailableID", DA_ID, DA_NOT_NULL);
 		$this->addColumn("userID", DA_ID, DA_NOT_NULL);
 		$this->addColumn("date", DA_DATE, DA_NOT_NULL);
 		$this->addColumn("am", DA_FLOAT, DA_NOT_NULL);
 		$this->addColumn("pm", DA_FLOAT, DA_NOT_NULL);
 		$this->setPK(0);
 		$this->setAddColumnsOff();
 	}
 	/**
 	 * Get all service user records for today
 	 *
 	 * @return unknown
	function getRowsToday(){

		$this->setQueryString(
			"SELECT ".$this->getDBColumnNamesAsString() .
			" FROM ".$this->getTableName() .
			" JOIN consultant on userID = cns_consno" .
			" WHERE date = CURDATE()"
		);

		return($this->getRows());
	}
 	 */
	/**
	 * Get a specific user record for today
	 *
	 * @param unknown_type $userID
	 * @return unknown
	 */
	function getUserRecordForToday( $userID ){

	
		$this->setQueryString(
			"SELECT ".$this->getDBColumnNamesAsString() .
			" FROM ".$this->getTableName() .
			" WHERE date = CURDATE()" . 
			" AND userID = " . $userID
		);

		return($this->getRow());
	}
}

?>