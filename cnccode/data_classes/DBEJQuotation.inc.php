<?
/*
* Quotation table join to user table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"]."/DBEQuotation.inc.php");
class DBEJQuotation extends DBEQuotation{
	/**
	* calls constructor()
	* @access public
	* @return void
	* @param  void
	* @see constructor()
	*/
	function DBEJQuotation(&$owner){
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
 		$this->setAddColumnsOn();
 		$this->addColumn("userName", DA_STRING, DA_NOT_NULL, 'cns_name');
 		$this->setAddColumnsOff();
 	}
	/**
	* Return rows by ordheadID
	* @access public
	* @return bool Success
	*/
	function getRowsByOrdheadID(){
		$this->setMethodName("getRowsByOrdheadID");
		if ($this->getValue('ordheadID')==''){
			$this->raiseError('ordheadID not set');
		}
		$this->setQueryString(
			'SELECT '.$this->getDBColumnNamesAsString().
			' FROM '.$this->getTableName().' LEFT JOIN consultant ON '.$this->getTableName().'.'.$this->getDBColumnName('userID').'=consultant.cns_consno'.
			' WHERE '.$this->getTableName().'.'.$this->getDBColumnName('ordheadID').'='.$this->getFormattedValue('ordheadID')
		);
		return(parent::getRows());
	}
}
?>