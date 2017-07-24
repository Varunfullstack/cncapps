<?php /*
* Item type table access
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"]."/DBCNCEntity.inc.php");
class DBEItemType extends DBCNCEntity{
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
		$this->setTableName("itemtype");
 		$this->addColumn("itemTypeID", DA_ID, DA_NOT_NULL, "ity_itemtypeno");
 		$this->addColumn("description", DA_STRING, DA_NOT_NULL, "ity_desc");
 		$this->addColumn("stockcat", DA_STRING, DA_NOT_NULL, "ity_stockcat");
 		$this->setPK(0);
 		$this->setAddColumnsOff();
 	}
}
?>