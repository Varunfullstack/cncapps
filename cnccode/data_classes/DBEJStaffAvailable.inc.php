<?php
/*
* Staff available join
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"]."/DBEStaffAvailable.inc.php");
class DBEJStaffAvailable extends DBEStaffAvailable{
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
 		$this->setAddColumnsOn();
 		$this->addColumn("firstName", DA_STRING, DA_ALLOW_NULL, "firstName");
 		$this->addColumn("lastName", DA_STRING, DA_ALLOW_NULL, "lastName");
 		$this->setPK(0);
 		$this->setAddColumnsOff();
 	}
	function getRowsToday(){

		$this->setQueryString(
			"SELECT ".$this->getDBColumnNamesAsString() .
			" FROM ".$this->getTableName() .
			" JOIN consultant on cns_consno = userID" .
			" WHERE date = CURDATE()" .
			" AND cns_perms LIKE '%" . PHPLIB_PERM_TECHNICAL . "%'"
			);

			return($this->getRows());
	}
}

?>