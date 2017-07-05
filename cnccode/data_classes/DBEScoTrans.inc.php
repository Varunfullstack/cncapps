<?
/*
* Customer table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"]."/DBEntity.inc.php");
class DBEScoTrans extends DBEntity{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEScoTrans(&$owner){
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
		$this->setTableName("ScoTrans");
 		$this->addColumn("ScoTransID", DA_ID, DA_NOT_NULL);
 		$this->addColumn("Statement", DA_STRING, DA_NOT_NULL);
 		$this->setPK(0);
 		$this->setAddColumnsOff();
 	}					// set by dbentity that is using this dbe
	/**
	* Run statement that has no result
	* @access public
	* @return void
	* @param  void
	*/
	function executeStatement($statement){
		$this->setMethodName('executeStatement');
		$this->setQueryString($statement);
		$ret = $this->runQuery();
		$this->resetQueryString();
		return $ret;
 	}
}
?>