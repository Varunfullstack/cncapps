<?
/*
* Questionnaire table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEQuestionnaire extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEQuestionnaire(&$owner){
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
		$this->setTableName("questionnaire");
 		$this->addColumn("questionnaireID", DA_ID, DA_NOT_NULL, "qur_questionnaireno");
 		$this->addColumn("description", DA_STRING, DA_NOT_NULL, "qur_desc");
    $this->addColumn("intro", DA_MEMO, DA_NOT_NULL, "qur_intro");
    $this->addColumn("thankYou", DA_MEMO, DA_NOT_NULL, "qur_thank_you");
    $this->addColumn("rating1Desc", DA_STRING, DA_NOT_NULL, "qur_rating_1_desc");
    $this->addColumn("rating5Desc", DA_STRING, DA_NOT_NULL, "qur_rating_5_desc");
    $this->addColumn("nameRequired", DA_STRING, DA_YN, "qur_name_required");
 		$this->setAddColumnsOff();
		$this->setPK(0);
	}
}
?>
