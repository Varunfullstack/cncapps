<?php /*
* Future Action join
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEJFutureAction extends DBEntity{
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
 		$this->addColumn("futureActionID", DA_ID, DA_NOT_NULL,"future_action.futureActionID");
 		$this->addColumn("date", DA_DATE, DA_NOT_NULL,"future_action.date");
 		$this->addColumn("callActivityID", DA_ID, DA_NOT_NULL,"future_action.callActivityID");
 		$this->addColumn("furtherActionID", DA_ID, DA_NOT_NULL, "future_action.furtherActionID");
 		$this->addColumn("description", DA_STRING, DA_NOT_NULL, "further_action.description");
 		$this->addColumn("engineerName", DA_STRING, DA_NOT_NULL, "future_action.engineerName");
 		$this->setAddColumnsOff();
	}
	function getRowsByCallActivityID( $callActivityID ){
 		$this->setMethodName('getRowsByCallActivityID');
		$statement=
			"SELECT " . $this->getDBColumnNamesAsString().
			" FROM future_action" . 
			" JOIN further_action ON further_action.furtherActionID = future_action.furtherActionID".
			" WHERE future_action.callActivityID = " . $callActivityID;
		$this->setQueryString($statement);
		$ret=(parent::getRows());
	}
}
?>