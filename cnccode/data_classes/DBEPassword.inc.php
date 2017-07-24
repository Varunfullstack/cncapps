<?php /*
* AnswerType table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEPassword extends DBEntity{
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
		$this->setTableName("password");
 		$this->addColumn("passwordID", DA_ID, DA_NOT_NULL, "pas_passwordno");
    $this->addColumn("customerID", DA_ID, DA_NOT_NULL, "pas_custno");
    $this->addColumn("username", DA_STRING, DA_ALLOW_NULL, "pas_username");
 		$this->addColumn("service", DA_STRING, DA_ALLOW_NULL, "pas_service");
    $this->addColumn("password", DA_STRING, DA_ALLOW_NULL, "pas_password");
    $this->addColumn("notes", DA_STRING, DA_ALLOW_NULL, "pas_notes");
 		$this->setAddColumnsOff();
		$this->setPK(0);
	}
}
?>
