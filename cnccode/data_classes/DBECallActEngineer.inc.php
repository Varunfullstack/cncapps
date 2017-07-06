<?
/*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBECallActEngineer extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBECallActEngineer(&$owner){
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
		$this->setTableName("CallActEngineer");
 		$this->addColumn("callActEngineerID", DA_ID, DA_NOT_NULL, "cae_callactengno");
 		$this->addColumn("callActivityID", DA_ID, DA_NOT_NULL, "cae_callactivityno");
 		$this->addColumn("sequenceNo", DA_INTEGER, DA_NOT_NULL, "cae_item");
 		$this->addColumn("userID", DA_ID, DA_NOT_NULL, "cae_consno");
 		$this->addColumn("expenseExportedFlag", DA_YN, DA_ALLOW_NULL, "cae_expn_exp_flag ");			// whether expenses have been exported
 		$this->addColumn("overtimeExportedFlag", DA_YN, DA_ALLOW_NULL, "cae_ot_exp_flag ");				// whether overtime info exported
		$this->setPK(0);
 		$this->setAddColumnsOff();
	}
	function deleteRowsByCallActivityID(){
		$this->setMethodName('deleteRowsByCallActivityID');
		$this->setQueryString(
			"DELETE FROM ". $this->getTableName().
			" WHERE ".$this->getDBColumnName('callActivityID'). ' = '.$this->getFormattedValue('callActivityID')
		);
		$ret=$this->runQuery();
		$this->resetQueryString();
		return $ret;
		
	}
}
?>