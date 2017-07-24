<?php /*
* Question table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEQuestion extends DBEntity{
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
		$this->setTableName("question");
 		$this->addColumn("questionID", DA_ID, DA_NOT_NULL, "que_questionno");
    $this->addColumn("questionnaireID", DA_ID, DA_NOT_NULL, "que_questionnaireno");
    $this->addColumn("answerTypeID", DA_ID, DA_NOT_NULL, "que_answertypeno");
 		$this->addColumn("description", DA_STRING, DA_NOT_NULL, "que_desc");
    $this->addColumn("activeFlag", DA_YN, DA_NOT_NULL, "que_active_flag");
    $this->addColumn("requiredFlag", DA_YN, DA_NOT_NULL, "que_required_flag");
    $this->addColumn("weight", DA_INTEGER, DA_NOT_NULL, "que_weight");
 		$this->setAddColumnsOff();
		$this->setPK(0);
	}
}
?>
