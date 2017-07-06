<?
/*
* Activity Categories
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBESector extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBESector(&$owner){
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
		$this->setTableName("sector");
 		$this->addColumn("sectorID", DA_ID, DA_NOT_NULL, 'sec_sectorno');
 		$this->addColumn("description", DA_STRING, DA_NOT_NULL, 'sec_desc');
		$this->setPK(0);							
 		$this->setAddColumnsOff();
	}
}
?>