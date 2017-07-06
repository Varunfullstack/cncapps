<?php
/*
* Standard Text Type table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEStandardTextType extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEStandardTextType(&$owner){
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
		$this->setTableName("standardtexttype");
 		$this->addColumn("standardTextTypeID", DA_ID, DA_NOT_NULL, 'sty_standardtexttypeno');
    $this->addColumn("description", DA_STRING, DA_NOT_NULL, 'sty_desc');
 		$this->setPK(0);
 		$this->setAddColumnsOff();
	}
}
?>